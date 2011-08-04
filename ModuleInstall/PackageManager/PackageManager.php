<?php
/*********************************************************************************
 * SugarCRM Community Edition is a customer relationship management program developed by
 * SugarCRM, Inc. Copyright (C) 2004-2011 SugarCRM Inc.
 * 
 * This program is free software; you can redistribute it and/or modify it under
 * the terms of the GNU Affero General Public License version 3 as published by the
 * Free Software Foundation with the addition of the following permission added
 * to Section 15 as permitted in Section 7(a): FOR ANY PART OF THE COVERED WORK
 * IN WHICH THE COPYRIGHT IS OWNED BY SUGARCRM, SUGARCRM DISCLAIMS THE WARRANTY
 * OF NON INFRINGEMENT OF THIRD PARTY RIGHTS.
 * 
 * This program is distributed in the hope that it will be useful, but WITHOUT
 * ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS
 * FOR A PARTICULAR PURPOSE.  See the GNU Affero General Public License for more
 * details.
 * 
 * You should have received a copy of the GNU Affero General Public License along with
 * this program; if not, see http://www.gnu.org/licenses or write to the Free
 * Software Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA
 * 02110-1301 USA.
 * 
 * You can contact SugarCRM, Inc. headquarters at 10050 North Wolfe Road,
 * SW2-130, Cupertino, CA 95014, USA. or at email address contact@sugarcrm.com.
 * 
 * The interactive user interfaces in modified source and object code versions
 * of this program must display Appropriate Legal Notices, as required under
 * Section 5 of the GNU Affero General Public License version 3.
 * 
 * In accordance with Section 7(b) of the GNU Affero General Public License version 3,
 * these Appropriate Legal Notices must retain the display of the "Powered by
 * SugarCRM" logo. If the display of the logo is not reasonably feasible for
 * technical reasons, the Appropriate Legal Notices must display the words
 * "Powered by SugarCRM".
 ********************************************************************************/


define("CREDENTIAL_CATEGORY", "ml");
define("CREDENTIAL_USERNAME", "username");
define("CREDENTIAL_PASSWORD", "password");





require_once('include/nusoap/nusoap.php');

require_once('include/utils/zip_utils.php');

require_once('ModuleInstall/PackageManager/PackageManagerDisplay.php');
require_once('ModuleInstall/ModuleInstaller.php');

require_once('include/entryPoint.php');
require_once('ModuleInstall/PackageManager/PackageManagerComm.php');


 class PackageManager{
    var $soap_client;

    /**
     * Constructor: In this method we will initialize the nusoap client to point to the hearbeat server
     */
    function PackageManager(){
        $this->db = & DBManagerFactory::getInstance();
    }

    function initializeComm(){

    }

    /**
     * Obtain a promotion from SugarDepot
     * @return string   the string from the promotion
     */
    function getPromotion(){
        $name_value_list = PackageManagerComm::getPromotion();
        if(!empty($name_value_list)){
            $name_value_list = PackageManager::fromNameValueList($name_value_list);
            return $name_value_list['description'];
        }else {
           return '';
        }
    }

    /**
     * Obtain a list of category/packages/releases for use within the module loader
     */
    function getModuleLoaderCategoryPackages($category_id = ''){
    	$filter = array();
    	$filter = array('type' => "'module', 'theme', 'langpack'");
    	$filter = PackageManager::toNameValueList($filter);
    	return PackageManager::getCategoryPackages($category_id, $filter);
    }

    /**
     * Obtain the list of category_packages from SugarDepot
     * @return category_packages
     */
    function getCategoryPackages($category_id = '', $filter = array()){
         $results = PackageManagerComm::getCategoryPackages($category_id, $filter);
         PackageManagerComm::errorCheck();
         $nodes = array();

        $nodes[$category_id]['packages'] = array();
        if(!empty($results['categories'])){
	         foreach($results['categories'] as $category){
	            $mycat = PackageManager::fromNameValueList($category);
	            $nodes[$mycat['id']] = array('id' => $mycat['id'], 'label' => $mycat['name'], 'description' => $mycat['description'], 'type' => 'cat', 'parent' => $mycat['parent_id']);
	            $nodes[$mycat['id']]['packages'] = array();
	         }
        }
         if(!empty($results['packages'])){
	        $uh = new UpgradeHistory();
	         foreach($results['packages'] as $package){
	            $mypack = PackageManager::fromNameValueList($package);
	            $nodes[$mypack['category_id']]['packages'][$mypack['id']] = array('id' => $mypack['id'], 'label' => $mypack['name'], 'description' => $mypack['description'], 'category_id' => $mypack['category_id'], 'type' => 'package');
	            $releases = PackageManager::getReleases($category_id, $mypack['id'], $filter);
	            $arr_releases = array();
	            $nodes[$mypack['category_id']]['packages'][$mypack['id']]['releases'] = array();
	            if(!empty($releases['packages'])){
		            foreach($releases['packages'] as $release){
		                 $myrelease = PackageManager::fromNameValueList($release);
		                 //check to see if we already this one installed
		                 $result = $uh->determineIfUpgrade($myrelease['id_name'], $myrelease['version']);
		                 $enable = false;
		                 if($result == true || is_array($result))
		                	 $enable = true;
		                 $nodes[$mypack['category_id']]['packages'][$mypack['id']]['releases'][$myrelease['id']] = array('id' => $myrelease['id'], 'version' => $myrelease['version'], 'label' => $myrelease['description'], 'category_id' => $mypack['category_id'], 'package_id' => $mypack['id'], 'type' => 'release', 'enable' => $enable);
		           	}
	            }
	            //array_push($nodes[$mypack['category_id']]['packages'], $package_arr);
	         }
         }
         $GLOBALS['log']->debug("NODES". var_export($nodes, true));
        return $nodes;
    }

    /**
     * Get a list of categories from the SugarDepot
     * @param category_id   the category id of parent to obtain
     * @param filter        an array of filters to pass to limit the query
     * @return array        an array of categories for display on the client
     */
    function getCategories($category_id, $filter = array()){
        $nodes = array();
        $results = PackageManagerComm::getCategories($category_id, $filter);
        PackageManagerComm::errorCheck();
        if(!empty($results['categories'])){
	        foreach($results['categories'] as $category){
	            $mycat = PackageManager::fromNameValueList($category);
	            $nodes[] = array('id' => $mycat['id'], 'label' => $mycat['name'], 'description' => $mycat['description'], 'type' => 'cat', 'parent' => $mycat['parent_id']);
	        }
        }
        return $nodes;
    }

    function getPackages($category_id, $filter = array()){
        $nodes = array();
        $results = PackageManagerComm::getPackages($category_id, $filter);
        PackageManagerComm::errorCheck();
        $packages = array();
        //$xml = '';
        //$xml .= '<packages>';
        if(!empty($results['packages'])){
	        foreach($results['packages'] as $package){
	            $mypack = PackageManager::fromNameValueList($package);
	            $packages[$mypack['id']] = array('package_id' => $mypack['id'], 'name' => $mypack['name'], 'description' => $mypack['description'], 'category_id' => $mypack['category_id']);
	            $releases = PackageManager::getReleases($category_id, $mypack['id']);
	            $arr_releases = array();
	            foreach($releases['packages'] as $release){
	                 $myrelease = PackageManager::fromNameValueList($release);
	                 $arr_releases[$myrelease['id']]  = array('release_id' => $myrelease['id'], 'version' => $myrelease['version'], 'description' => $myrelease['description'], 'category_id' => $mypack['category_id'], 'package_id' => $mypack['id']);
	            }
	            $packages[$mypack['id']]['releases'] = $arr_releases;
	        }
        }
        return $packages;
    }

    function getReleases($category_id, $package_id, $filter = array()){
        $releases = PackageManagerComm::getReleases($category_id, $package_id, $filter);
        PackageManagerComm::errorCheck();
        return $releases;
    }

    /**
     * Retrieve the package as specified by the $id from the heartbeat server
     *
     * @param category_id   the category_id to which the release belongs
     * @param package_id    the package_id to which the release belongs
     * @param release_id    the release_id to download
     * @return filename - the path to which the zip file was saved
     */
    function download($category_id, $package_id, $release_id, $save_dir = ''){
        $GLOBALS['log']->debug('RELEASE _ID: '.$release_id);
        if(!empty($release_id)){
            $filename = PackageManagerComm::addDownload($category_id, $package_id, $release_id);
            if($filename){
	            $GLOBALS['log']->debug('RESULT: '.$filename);
	            PackageManagerComm::errorCheck();
	           	$filepath = PackageManagerComm::performDownload($filename, $save_dir);
	           	return $filepath;
	            /*if(!empty($result) && !empty($result['filename']) && !empty($save_dir)){
	                $GLOBALS['log']->debug('Saving Package to: '.$save_dir);
	                $GLOBALS['log']->debug('Saving package to the local file system:'.$result['filename']);
	                return write_encoded_file ($result, $save_dir);
	            }else{
	                return null;
	            }*/
            }
        }else{
            return null;
        }
    }

    /**
     * Given the Mambo username, password, and download key attempt to authenticate, if
     * successful then store these credentials
     *
     * @param username      Mambo username
     * @param password      Mambo password
     * @param systemname   the user's download key
     * @return              true if successful, false otherwise
     */
    function authenticate($username, $password, $systemname='', $terms_checked = true){
        PackageManager::setCredentials($username, $password, $systemname);
        PackageManagerComm::clearSession();
        $result = PackageManagerComm::login($terms_checked);
        if(is_array($result))
        	return $result;
       	else
        	return true;
    }

    function setCredentials($username, $password, $systemname){

        $admin = new Administration();
        $admin->retrieveSettings();
         $admin->saveSetting(CREDENTIAL_CATEGORY, CREDENTIAL_USERNAME, $username);
         $admin->saveSetting(CREDENTIAL_CATEGORY, CREDENTIAL_PASSWORD, $password);
         if(!empty($systemname)){
         	$admin->saveSetting('system', 'name', $systemname);
         }
    }

    function getCredentials(){

        $admin = new Administration();
        $admin->retrieveSettings(CREDENTIAL_CATEGORY, true);
        $credentials = array();
        $credentials['username'] = '';
        $credentials['password'] = '';
		$credentials['system_name'] = '';
        if(!empty($admin->settings[CREDENTIAL_CATEGORY.'_'.CREDENTIAL_USERNAME])){
           $credentials['username'] = $admin->settings[CREDENTIAL_CATEGORY.'_'.CREDENTIAL_USERNAME];
        }
        if(!empty($admin->settings[CREDENTIAL_CATEGORY.'_'.CREDENTIAL_USERNAME])){
           $credentials['password'] = $admin->settings[CREDENTIAL_CATEGORY.'_'.CREDENTIAL_PASSWORD];
        }
        if(!empty($admin->settings['system_name'])){
           $credentials['system_name'] = $admin->settings['system_name'];
        }
        return $credentials;
    }

    function getTermsAndConditions(){
    	return PackageManagerComm::getTermsAndConditions();

    }

    /**
     * Retrieve documentation for the given release or package
     *
     * @param package_id	the specified package to retrieve documentation
     * @param release_id	the specified release to retrieve documentation
     *
     * @return documents
     */
    function getDocumentation($package_id, $release_id){
    	 if(!empty($release_id) || !empty($package_id)){
            $documents = PackageManagerComm::getDocumentation($package_id, $release_id);
            return $documents;
        }else{
            return null;
        }
    }

    /**
     * Grab the list of installed modules and send that list to the depot.
     * The depot will then send back a list of modules that need to be updated
     */
    function checkForUpdates(){
    	$lists = $this->buildInstalledReleases(array('module'), true);
		$updates = array();
		if(!empty($lists)){
			$updates = PackageManagerComm::checkForUpdates($lists);
		}//fi
		return $updates;
    }

     ////////////////////////////////////////////////////////
     /////////// HELPER FUNCTIONS
    function toNameValueList($array){
		$list = array();
		foreach($array as $name=>$value){
			$list[] = array('name'=>$name, 'value'=>$value);
		}
		return $list;
	}

	function toNameValueLists($arrays){
		$lists = array();
		foreach($arrays as $array){
			$lists[] = PackageManager::toNameValueList($array);
		}
		return $lists;
	}

     function fromNameValueList($nvl){
        $array = array();
        foreach($nvl as $list){
            $array[$list['name']] = $list['value'];
        }
        return $array;
    }

    function buildInstalledReleases($types = array('module')){
    	//1) get list of installed modules
		$installeds = $this->getInstalled($types);
		$releases = array();
		foreach($installeds as $installed){
			$releases[] = array('name' => $installed->name, 'id_name' => $installed->id_name, 'version' => $installed->version, 'filename' => $installed->filename, 'type' => $installed->type);
		}

		$lists = array();
		$name_value_list = array();
		if(!empty($releases)){
			$lists = $this->toNameValueLists($releases);
		}//fi
		return $lists;
    }

    function buildPackageXML($package, $releases = array()){
        $xml = '<package>';
        $xml .= '<package_id>'.$package['id'].'</package_id>';
        $xml .= '<name>'.$package['name'].'</name>';
        $xml .= '<description>'.$package['description'].'</description>';
        if(!empty($releases)){
             $xml .= '<releases>';
             foreach($releases['packages'] as $release){

                 $myrelease = PackageManager::fromNameValueList($release);
                 $xml .= '<release>';
                 $xml .= '<release_id>'.$myrelease['id'].'</release_id>';
                 $xml .= '<version>'.$myrelease['version'].'</version>';
                 $xml .= '<description>'.$myrelease['description'].'</description>';
                 $xml .= '<package_id>'.$package['id'].'</package_id>';
                 $xml .= '<category_id>'.$package['category_id'].'</category_id>';
                 $xml .= '</release>';
             }
             $xml .= '</releases>';
        }
        $xml .= '</package>';
        return $xml;
    }

    //////////////////////////////////////////////////////////////////////
    /////////// INSTALL SECTION
    function extractFile( $zip_file, $file_in_zip, $base_tmp_upgrade_dir){
        $my_zip_dir = mk_temp_dir( $base_tmp_upgrade_dir );
        unzip_file( $zip_file, $file_in_zip, $my_zip_dir );
        return( "$my_zip_dir/$file_in_zip" );
    }

    function extractManifest( $zip_file,$base_tmp_upgrade_dir ) {
        global $sugar_config;
        $base_upgrade_dir       = $sugar_config['upload_dir'] . "/upgrades";
        $base_tmp_upgrade_dir   = "$base_upgrade_dir/temp";
        return( $this->extractFile( $zip_file, "manifest.php",$base_tmp_upgrade_dir ) );
    }

    function validate_manifest( $manifest ){
    // takes a manifest.php manifest array and validates contents
    global $subdirs;
    global $sugar_version;
    global $sugar_flavor;
    global $mod_strings;

    if( !isset($manifest['type']) ){
        die($mod_strings['ERROR_MANIFEST_TYPE']);
    }
    $type = $manifest['type'];
    $GLOBALS['log']->debug("Getting InstallType");
    if( $this->getInstallType( "/$type/" ) == "" ){
        $GLOBALS['log']->debug("Error with InstallType".$type);
        die($mod_strings['ERROR_PACKAGE_TYPE']. ": '" . $type . "'." );
    }
    $GLOBALS['log']->debug("Passed with InstallType");
    if( isset($manifest['acceptable_sugar_versions']) ){
            $version_ok = false;
            $matches_empty = true;
            if( isset($manifest['acceptable_sugar_versions']['exact_matches']) ){
                $matches_empty = false;
                foreach( $manifest['acceptable_sugar_versions']['exact_matches'] as $match ){
                    if( $match == $sugar_version ){
                        $version_ok = true;
                    }
                }
            }
            if( !$version_ok && isset($manifest['acceptable_sugar_versions']['regex_matches']) ){
                $matches_empty = false;
                foreach( $manifest['acceptable_sugar_versions']['regex_matches'] as $match ){
                    if( preg_match( "/$match/", $sugar_version ) ){
                        $version_ok = true;
                    }
                }
            }

            if( !$matches_empty && !$version_ok ){
                die( $mod_strings['ERROR_VERSION_INCOMPATIBLE'] . $sugar_version );
            }
        }

     if( isset($manifest['acceptable_sugar_flavors']) && sizeof($manifest['acceptable_sugar_flavors']) > 0 ){
            $flavor_ok = false;
            foreach( $manifest['acceptable_sugar_flavors'] as $match ){
                if( $match == $sugar_flavor ){
                    $flavor_ok = true;
                }
            }
            if( !$flavor_ok ){
                //die( $mod_strings['ERROR_FLAVOR_INCOMPATIBLE'] . $sugar_flavor );
            }
        }
    }

    function getInstallType( $type_string ){
        // detect file type
        global $subdirs;
        $subdirs = array('full', 'langpack', 'module', 'patch', 'theme', 'temp');


        foreach( $subdirs as $subdir ){
            if( preg_match( "#/$subdir/#", $type_string ) ){
                return( $subdir );
            }
        }
        // return empty if no match
        return( "" );
    }

    function performSetup($tempFile, $view = 'module', $display_messages = true){
        global $sugar_config;
        $base_filename = urldecode($tempFile);
        $GLOBALS['log']->debug("BaseFileName: ".$base_filename);
        $base_upgrade_dir       = $sugar_config['upload_dir'] . "/upgrades";
        $base_tmp_upgrade_dir   = "$base_upgrade_dir/temp";
        $manifest_file = $this->extractManifest( $base_filename,$base_tmp_upgrade_dir);
         $GLOBALS['log']->debug("Manifest: ".$manifest_file);
        if($view == 'module')
            $license_file = $this->extractFile($base_filename, 'LICENSE.txt', $base_tmp_upgrade_dir);
        if(is_file($manifest_file)){
            $GLOBALS['log']->debug("VALIDATING MANIFEST". $manifest_file);
            require_once( $manifest_file );
            $this->validate_manifest($manifest );
            $upgrade_zip_type = $manifest['type'];
            $GLOBALS['log']->debug("VALIDATED MANIFEST");
            // exclude the bad permutations
            if( $view == "module" ){
                if ($upgrade_zip_type != "module" && $upgrade_zip_type != "theme" && $upgrade_zip_type != "langpack"){
                    $this->unlinkTempFiles();
                    if($display_messages)
                        die($mod_strings['ERR_UW_NOT_ACCEPTIBLE_TYPE']);
                }
            }elseif( $view == "default" ){
                if($upgrade_zip_type != "patch" ){
                    $this->unlinkTempFiles();
                    if($display_messages)
                        die($mod_strings['ERR_UW_ONLY_PATCHES']);
                }
            }

            $base_filename = preg_replace( "#\\\\#", "/", $base_filename );
            $base_filename = basename( $base_filename );
            mkdir_recursive( "$base_upgrade_dir/$upgrade_zip_type" );
            $target_path = "$base_upgrade_dir/$upgrade_zip_type/$base_filename";
            $target_manifest = remove_file_extension( $target_path ) . "-manifest.php";

            if( isset($manifest['icon']) && $manifest['icon'] != "" ){
                $icon_location = $this->extractFile( $tempFile ,$manifest['icon'], $base_tmp_upgrade_dir );
                $path_parts = pathinfo( $icon_location );
                copy( $icon_location, remove_file_extension( $target_path ) . "-icon." . $path_parts['extension'] );
            }

            if( copy( $tempFile , $target_path ) ){
                copy( $manifest_file, $target_manifest );
                if($display_messages)
                    $messages = '<script>ajaxStatus.flashStatus("' .$base_filename.$mod_strings['LBL_UW_UPLOAD_SUCCESS'] . ', 5000");</script>';
            }else{
                if($display_messages)
                	$messages = '<script>ajaxStatus.flashStatus("' .$mod_strings['ERR_UW_UPLOAD_ERROR'] . ', 5000");</script>';
            }
        }//fi
        else{
            $this->unlinkTempFiles();
            if($display_messages)
                die($mod_strings['ERR_UW_NO_MANIFEST']);
        }
        if(isset($messages))
            return $messages;
    }

    function unlinkTempFiles() {
        global $sugar_config;
        @unlink($_FILES['upgrade_zip']['tmp_name']);
        @unlink(getcwd().'/'.$sugar_config['upload_dir'].$_FILES['upgrade_zip']['name']);
    }

    function performInstall($file, $silent=true){
        global $sugar_config;
        global $mod_strings;
        global $current_language;
        $base_upgrade_dir       = $sugar_config['upload_dir'] . "/upgrades";
        $base_tmp_upgrade_dir   = "$base_upgrade_dir/temp";
        if(!file_exists($base_tmp_upgrade_dir)){
            mkdir_recursive($base_tmp_upgrade_dir, true);
        }

        $GLOBALS['log']->debug("INSTALLING: ".$file);
        $mi = new ModuleInstaller();
        $mi->silent = $silent;
        $mod_strings = return_module_language($current_language, "Administration");
             $GLOBALS['log']->debug("ABOUT TO INSTALL: ".$file);
        if(preg_match("#.*\.zip\$#", $file)) {
             $GLOBALS['log']->debug("1: ".$file);
            // handle manifest.php
            $target_manifest = remove_file_extension( $file ) . '-manifest.php';
            include($target_manifest);
            $GLOBALS['log']->debug("2: ".$file);
            $unzip_dir = mk_temp_dir( $base_tmp_upgrade_dir );
            unzip($file, $unzip_dir );
            $GLOBALS['log']->debug("3: ".$unzip_dir);
            $id_name = $installdefs['id'];
			$version = $manifest['version'];
			$uh = new UpgradeHistory();
			$previous_install = array();
    		if(!empty($id_name) & !empty($version))
    			$previous_install = $uh->determineIfUpgrade($id_name, $version);
    		$previous_version = (empty($previous_install['version'])) ? '' : $previous_install['version'];
    		$previous_id = (empty($previous_install['id'])) ? '' : $previous_install['id'];

            if(!empty($previous_version)){
            	$mi->install($unzip_dir, true, $previous_version);
            }else{
            	$mi->install($unzip_dir);
            }
            $GLOBALS['log']->debug("INSTALLED: ".$file);
            $new_upgrade = new UpgradeHistory();
            $new_upgrade->filename      = $file;
            $new_upgrade->md5sum        = md5_file($file);
            $new_upgrade->type          = $manifest['type'];
            $new_upgrade->version       = $manifest['version'];
            $new_upgrade->status        = "installed";
            //$new_upgrade->author        = $manifest['author'];
            $new_upgrade->name          = $manifest['name'];
            $new_upgrade->description   = $manifest['description'];
            $new_upgrade->id_name		= $id_name;
			$serial_manifest = array();
			$serial_manifest['manifest'] = (isset($manifest) ? $manifest : '');
			$serial_manifest['installdefs'] = (isset($installdefs) ? $installdefs : '');
			$serial_manifest['upgrade_manifest'] = (isset($upgrade_manifest) ? $upgrade_manifest : '');
			$new_upgrade->manifest		= base64_encode(serialize($serial_manifest));
            //$new_upgrade->unique_key    = (isset($manifest['unique_key'])) ? $manifest['unique_key'] : '';
            $new_upgrade->save();
                    //unlink($file);
        }//fi
    }

    function performUninstall($name){
    	$uh = new UpgradeHistory();
    	$uh->name = $name;
    	$uh->id_name = $name;
    	$found = $uh->checkForExisting($uh);
    	if($found != null){

    		global $sugar_config;
	        global $mod_strings;
	        global $current_language;
	        $base_upgrade_dir       = $sugar_config['upload_dir'] . "/upgrades";
	        $base_tmp_upgrade_dir   = "$base_upgrade_dir/temp";
	    	if(!isset($GLOBALS['mi_remove_tables']))$GLOBALS['mi_remove_tables'] = true;
	    	$unzip_dir = mk_temp_dir( $base_tmp_upgrade_dir );
	    	unzip($found->filename, $unzip_dir );
	    	$mi = new ModuleInstaller();
	        $mi->silent = true;
	        $mi->uninstall( "$unzip_dir");
	        $found->delete();
	        unlink(remove_file_extension( $found->filename ) . '-manifest.php');
	        unlink($found->filename);
    	}
    }

    function getUITextForType( $type ){
        if( $type == "full" ){
            return( "Full Upgrade" );
        }
        if( $type == "langpack" ){
            return( "Language Pack" );
        }
        if( $type == "module" ){
            return( "Module" );
        }
        if( $type == "patch" ){
            return( "Patch" );
        }
        if( $type == "theme" ){
            return( "Theme" );
        }
    }

    function getImageForType( $type ){

        $icon = "";
        switch( $type ){
            case "full":
                $icon = SugarThemeRegistry::current()->getImage("Upgrade", "" );
                break;
            case "langpack":
                $icon = SugarThemeRegistry::current()->getImage("LanguagePacks", "" );
                break;
            case "module":
                $icon = SugarThemeRegistry::current()->getImage("ModuleLoader", "" );
                break;
            case "patch":
                $icon = SugarThemeRegistry::current()->getImage("PatchUpgrades", "" );
                break;
            case "theme":
                $icon = SugarThemeRegistry::current()->getImage("Themes", "" );
                break;
            default:
                break;
        }
        return( $icon );
    }

    function getPackagesInStaging($view = 'module'){
        global $sugar_config;
        global $current_language;
        $uh = new UpgradeHistory();
        $base_upgrade_dir       = $sugar_config['upload_dir'] . "/upgrades";
        $base_tmp_upgrade_dir   = "$base_upgrade_dir/temp";
        $uContent = findAllFiles( "$base_upgrade_dir", array() , false, 'zip',$base_tmp_upgrade_dir);
         //other variations of zip file i.e. ZIP, zIp,zIP,Zip,ZIp,ZiP
        $extns = array( 'ZIP','ZIp','ZiP','Zip','zIP','zIp','ziP');
        foreach($extns as $extn){
        	$uContent = array_merge($uContent,findAllFiles( "$base_upgrade_dir", array() , false, $extn,$base_tmp_upgrade_dir));
        }
        $upgrade_contents = array();
        $content_values = array_values($uContent);
        $alreadyProcessed = array();
        foreach($content_values as $val){
        	if(empty($alreadyProcessed[$val])){
        		$upgrade_contents[] = $val;
        		$alreadyProcessed["$val"] = true;
        	}
        }

        $upgrades_available = 0;
        $packages = array();
        $mod_strings = return_module_language($current_language, "Administration");
        foreach($upgrade_contents as $upgrade_content)
        {
            if(!preg_match("#.*\.zip\$#", strtolower($upgrade_content)) || preg_match("#.*./zips/.*#", strtolower($upgrade_content)))
            {
                continue;
            }

            $upgrade_content = clean_path($upgrade_content);
            // Bug 22285 - fix for UNC paths
            if ( substr($upgrade_content,0,2) == '\\\\' )
                $upgrade_content = '\\\\'.$upgrade_content;
            $the_base = basename($upgrade_content);
            $the_md5 = md5_file($upgrade_content);
            $md5_matches = $uh->findByMd5($the_md5);
    		$file_install = $upgrade_content;
            if(0 == sizeof($md5_matches))
            {
                $target_manifest = remove_file_extension( $upgrade_content ) . '-manifest.php';
                require_once($target_manifest);

                $name = empty($manifest['name']) ? $upgrade_content : $manifest['name'];
                $version = empty($manifest['version']) ? '' : $manifest['version'];
                $published_date = empty($manifest['published_date']) ? '' : $manifest['published_date'];
                $icon = '';
                $description = empty($manifest['description']) ? 'None' : $manifest['description'];
                $uninstallable = empty($manifest['is_uninstallable']) ? 'No' : 'Yes';
                $type = $this->getUITextForType( $manifest['type'] );
                $manifest_type = $manifest['type'];
                $dependencies = array();
                if( isset( $manifest['dependencies']) ){
    				$dependencies    = $manifest['dependencies'];
				}

				//check dependencies first
				if(!empty($dependencies)){

					$uh = new UpgradeHistory();
					$not_found = $uh->checkDependencies($dependencies);
					if(!empty($not_found) && count($not_found) > 0){
							$file_install = 'errors_'.$mod_strings['ERR_UW_NO_DEPENDENCY']."[".implode(',', $not_found)."]";
					}//fi
				}

                if($view == 'default' && $manifest_type != 'patch')
                {
                    continue;
                }

                if($view == 'module'
                    && $manifest_type != 'module' && $manifest_type != 'theme' && $manifest_type != 'langpack')
                {
                    continue;
                }

                if(empty($manifest['icon'])){
                    $icon = $this->getImageForType( $manifest['type'] );
                }else{
                    $path_parts = pathinfo( $manifest['icon'] );
                    $icon = "<img src=\"" . remove_file_extension( $upgrade_content ) . "-icon." . $path_parts['extension'] . "\">";
                }

                $upgrades_available++;

				// uploaded file in cache/upload
		        $fileS = explode('/', $upgrade_content);
		        $c = count($fileS);
		        $fileName = (isset($fileS[$c-1]) && !empty($fileS[$c-1])) ? $fileS[$c-1] : $fileS[$c-2];
		        $upload_file = $sugar_config['upload_dir'].$fileName;

                $upgrade_content = urlencode($upgrade_content);
                $upload_content = urlencode($upload_file);
                $packages[] = array('name' => $name, 'version' => $version, 'published_date' => $published_date, 'description' => $description, 'uninstallable' =>$uninstallable, 'type' => $type, 'file_install' => fileToHash($file_install), 'file' => fileToHash($upgrade_content), 'upload_file' => $upload_content);
            }//fi
        }//rof
        return $packages;
    }

    function getLicenseFromFile($file){
        global $sugar_config;
        $base_upgrade_dir       = $sugar_config['upload_dir'] . "/upgrades";
        $base_tmp_upgrade_dir   = "$base_upgrade_dir/temp";
        $license_file = $this->extractFile($file, 'LICENSE.txt', $base_tmp_upgrade_dir);
        if(is_file($license_file)){
            $contents = file_get_contents($license_file);
            return $contents;
        }else{
            return null;
        }
    }

    /**
     * Run the query to obtain the list of installed types as specified by the type param
     *
     * @param type	an array of types you would like to search for
     * 				type options include (theme, langpack, module, patch)
     *
     * @return an array of installed upgrade_history objects
     */
    function getInstalled($types = array('module')){
    	$uh = new UpgradeHistory();
    	$in = "";
    	for($i = 0; $i < count($types); $i++){
    		$in .= "'".$types[$i]."'";
    		if(($i+1) < count($types)){
    			$in .= ",";
    		}
    	}
    	$query = "SELECT * FROM ".$uh->table_name."	 WHERE type IN (".$in.")";
    	return $uh->getList($query);
    }

    function getinstalledPackages($types = array('module', 'langpack')){
    	global $sugar_config;
    	$installeds = $this->getInstalled($types);
    	$packages = array();
    	$upgrades_installed = 0;
    	$uh = new UpgradeHistory();
        $base_upgrade_dir       = $sugar_config['upload_dir'] . "/upgrades";
        $base_tmp_upgrade_dir   = "$base_upgrade_dir/temp";
    	foreach($installeds as $installed)
		{
			$populate = false;
			$filename = from_html($installed->filename);
			$date_entered = $installed->date_entered;
			$type = $installed->type;
			$version = $installed->version;
			$uninstallable = false;
			$link = "";
			$description = $installed->description;
			$name = $installed->name;
			$enabled = true;
			$enabled_string = 'ENABLED';
			//if the name is empty then we should try to pull from manifest and populate upgrade_history_table
			if(empty($name)){
				$populate = true;
			}
			$upgrades_installed++;
			switch($type)
			{
				case "theme":
				case "langpack":
				case "module":
				case "patch":
					if($populate){
						$manifest_file = $this->extractManifest($filename, $base_tmp_upgrade_dir);
						require_once($manifest_file);
						$GLOBALS['log']->info("Filling in upgrade_history table");
						$populate = false;
						if( isset( $manifest['name'] ) ){
    						$name = $manifest['name'];
    						$installed->name = $name;
						}
						if( isset( $manifest['description'] ) ){
						    $description = $manifest['description'];
						    $installed->description = $description;
						}
						if(isset($installdefs) && isset( $installdefs['id'] ) ){
						    $id_name  = $installdefs['id'];
						    $installed->id_name = $id_name;
						}

						$serial_manifest = array();
						$serial_manifest['manifest'] = (isset($manifest) ? $manifest : '');
						$serial_manifest['installdefs'] = (isset($installdefs) ? $installdefs : '');
						$serial_manifest['upgrade_manifest'] = (isset($upgrade_manifest) ? $upgrade_manifest : '');
						$installed->manifest = base64_encode(serialize($serial_manifest));
						$installed->save();
					}else{
						$serial_manifest = unserialize(base64_decode($installed->manifest));
						$manifest = $serial_manifest['manifest'];
					}
					if(($upgrades_installed==0 || $uh->UninstallAvailable($installeds, $installed))
						&& is_file($filename) && !empty($manifest['is_uninstallable']))
					{
						$uninstallable = true;
					}
					$enabled = $installed->enabled;
					if(!$enabled)
						$enabled_string = 'DISABLED';
					$file_uninstall = $filename;
					if(!$uninstallable){
						$file_uninstall = 'UNINSTALLABLE';
						$enabled_string = 'UNINSTALLABLE';
					} else {
						$file_uninstall = fileToHash( $file_uninstall );
					}

				$packages[] = array(
				    'name' => $name,
				    'version' => $version,
				    'type' => $type,
				    'published_date' => $date_entered,
				    'description' => $description,
				    'uninstallable' =>$uninstallable,
				    'file_install' =>  $file_uninstall ,
				    'file' =>  fileToHash($filename),
				    'enabled' => $enabled_string
				);
				break;
				default:
				break;
			}

		}//rof
		return $packages;
    }
 }
?>
