<?php
if(!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');
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

/*********************************************************************************

 * $Description: TODO: To be written. Portions created by SugarCRM are Copyright
 * (C) SugarCRM, Inc. All Rights Reserved. Contributor(s):
 * ______________________________________..
 * *******************************************************************************/

require_once('include/utils/zip_utils.php');

require_once('include/upload_file.php');



///////////////////////////////////////////////////////////////////////////////
////    FROM welcome.php
/**
 * returns lowercase lang encoding
 * @return string   encoding or blank on false
 */
function parseAcceptLanguage() {
    $lang = $_SERVER['HTTP_ACCEPT_LANGUAGE'];
    if(strpos($lang, ';')) {
        $exLang = explode(';', $lang);
        return strtolower(str_replace('-','_',$exLang[0]));
    } else {
        $match = array();
        if(preg_match("#\w{2}\-?\_?\w{2}#", $lang, $match)) {
            return strtolower(str_replace('-','_',$match[0]));
        }
    }
    return '';
}


///////////////////////////////////////////////////////////////////////////////
////    FROM localization.php
/**
 * copies the temporary unzip'd files to their final destination
 * removes unzip'd files from system if $uninstall=true
 * @param bool uninstall true if uninstalling a language pack
 * @return array sugar_config
 */
function commitLanguagePack($uninstall=false) {
    global $sugar_config;
    global $mod_strings;
    global $base_upgrade_dir;
    global $base_tmp_upgrade_dir;

    $errors         = array();
    $manifest       = urldecode($_REQUEST['manifest']);
    $zipFile        = urldecode($_REQUEST['zipFile']);
    $version        = "";
    $show_files     = true;
    $unzip_dir      = mk_temp_dir( $base_tmp_upgrade_dir );
    $zip_from_dir   = ".";
    $zip_to_dir     = ".";
    $zip_force_copy = array();

    if($uninstall == false && isset($_SESSION['INSTALLED_LANG_PACKS']) && in_array($zipFile, $_SESSION['INSTALLED_LANG_PACKS'])) {
        return;
    }

    // unzip lang pack to temp dir
    if(isset($zipFile) && !empty($zipFile)) {
        if(is_file($zipFile)) {
            unzip( $zipFile, $unzip_dir );
        } else {
            echo $mod_strings['ERR_LANG_MISSING_FILE'].$zipFile;
            die(); // no point going any further
        }
    }

    // filter for special to/from dir conditions (langpacks generally don't have them)
    if(isset($manifest) && !empty($manifest)) {
        if(is_file($manifest)) {
            include($manifest);
            if( isset( $manifest['copy_files']['from_dir'] ) && $manifest['copy_files']['from_dir'] != "" ){
                $zip_from_dir   = $manifest['copy_files']['from_dir'];
            }
            if( isset( $manifest['copy_files']['to_dir'] ) && $manifest['copy_files']['to_dir'] != "" ){
                $zip_to_dir     = $manifest['copy_files']['to_dir'];
            }
            if( isset( $manifest['copy_files']['force_copy'] ) && $manifest['copy_files']['force_copy'] != "" ){
                $zip_force_copy     = $manifest['copy_files']['force_copy'];
            }
            if( isset( $manifest['version'] ) ){
                $version    = $manifest['version'];
            }
        } else {
            $errors[] = $mod_strings['ERR_LANG_MISSING_FILE'].$manifest;
        }
    }


    // find name of language pack: find single file in include/language/xx_xx.lang.php
    $d = dir( "$unzip_dir/$zip_from_dir/include/language" );
    while( $f = $d->read() ){
        if( $f == "." || $f == ".." ){
            continue;
        }
        else if( preg_match("/(.*)\.lang\.php\$/", $f, $match) ){
            $new_lang_name = $match[1];
        }
    }
    if( $new_lang_name == "" ){
        die( $mod_strings['ERR_LANG_NO_LANG_FILE'].$zipFile );
    }
    $new_lang_desc = getLanguagePackName( "$unzip_dir/$zip_from_dir/include/language/$new_lang_name.lang.php" );
    if( $new_lang_desc == "" ){
        die( "No language pack description found at include/language/$new_lang_name.lang.php inside $install_file." );
    }
    // add language to available languages
    $sugar_config['languages'][$new_lang_name] = ($new_lang_desc);

    // get an array of all files to be moved
    $filesFrom = array();
    $filesFrom = findAllFiles($unzip_dir, $filesFrom);



    ///////////////////////////////////////////////////////////////////////////
    ////    FINALIZE
    if($uninstall) {
        // unlink all pack files
        foreach($filesFrom as $fileFrom) {
            //echo "deleting: ".getcwd().substr($fileFrom, strlen($unzip_dir), strlen($fileFrom))."<br>";
            @unlink(getcwd().substr($fileFrom, strlen($unzip_dir), strlen($fileFrom)));
        }

        // remove session entry
        if(isset($_SESSION['INSTALLED_LANG_PACKS']) && is_array($_SESSION['INSTALLED_LANG_PACKS'])) {
            foreach($_SESSION['INSTALLED_LANG_PACKS'] as $k => $langPack) {
                if($langPack == $zipFile) {
                    unset($_SESSION['INSTALLED_LANG_PACKS'][$k]);
                    unset($_SESSION['INSTALLED_LANG_PACKS_VERSION'][$k]);
                    unset($_SESSION['INSTALLED_LANG_PACKS_MANIFEST'][$k]);
                    $removedLang = $k;
                }
            }

            // remove language from config
            $new_langs = array();
            $old_langs = $sugar_config['languages'];
            foreach( $old_langs as $key => $value ){
                if( $key != $removedLang ){
                    $new_langs += array( $key => $value );
                }
            }
            $sugar_config['languages'] = $new_langs;
        }
    } else {
        // copy filesFrom to filesTo
        foreach($filesFrom as $fileFrom) {
            @copy($fileFrom, getcwd().substr($fileFrom, strlen($unzip_dir), strlen($fileFrom)));
        }

        $_SESSION['INSTALLED_LANG_PACKS'][$new_lang_name] = $zipFile;
        $_SESSION['INSTALLED_LANG_PACKS_VERSION'][$new_lang_name] = $version;
        $serial_manifest = array();
        $serial_manifest['manifest'] = (isset($manifest) ? $manifest : '');
        $serial_manifest['installdefs'] = (isset($installdefs) ? $installdefs : '');
        $serial_manifest['upgrade_manifest'] = (isset($upgrade_manifest) ? $upgrade_manifest : '');
        $_SESSION['INSTALLED_LANG_PACKS_MANIFEST'][$new_lang_name] = base64_encode(serialize($serial_manifest));
    }

    writeSugarConfig($sugar_config);

    return $sugar_config;
}

function commitPatch($unlink = false, $type = 'patch'){
    require_once('ModuleInstall/ModuleInstaller.php');
    require_once('include/entryPoint.php');


    global $mod_strings;
    global $base_upgrade_dir;
    global $base_tmp_upgrade_dir;
    global $db;
    $GLOBALS['db'] = $db;
    $errors = array();
    $files = array();
     global $current_user;
    $current_user = new User();
    $current_user->is_admin = '1';
    $old_mod_strings = $mod_strings;
    if(is_dir(getcwd()."/cache/upload/upgrades")) {
            $files = findAllFiles(getcwd()."/cache/upload/upgrades/$type", $files);
            $mi = new ModuleInstaller();
            $mi->silent = true;
            $mod_strings = return_module_language('en', "Administration");

            foreach($files as $file) {
                if(!preg_match("#.*\.zip\$#", $file)) {
                    continue;
                }
                // handle manifest.php
                $target_manifest = remove_file_extension( $file ) . '-manifest.php';

                include($target_manifest);

                $unzip_dir = mk_temp_dir( $base_tmp_upgrade_dir );
                unzip($file, $unzip_dir );
                if(file_exists("$unzip_dir/scripts/pre_install.php")){
                    require_once("$unzip_dir/scripts/pre_install.php");
                    pre_install();
                }
                if( isset( $manifest['copy_files']['from_dir'] ) && $manifest['copy_files']['from_dir'] != "" ){
                    $zip_from_dir   = $manifest['copy_files']['from_dir'];
                }
                $source = "$unzip_dir/$zip_from_dir";
                $dest = getcwd();
                copy_recursive($source, $dest);

                if(file_exists("$unzip_dir/scripts/post_install.php")){
                    require_once("$unzip_dir/scripts/post_install.php");
                    post_install();
                }
                $new_upgrade = new UpgradeHistory();
                $new_upgrade->filename      = $file;
                $new_upgrade->md5sum        = md5_file($file);
                $new_upgrade->type          = $manifest['type'];
                $new_upgrade->version       = $manifest['version'];
                $new_upgrade->status        = "installed";
                //$new_upgrade->author        = $manifest['author'];
                $new_upgrade->name          = $manifest['name'];
                $new_upgrade->description   = $manifest['description'];
                $serial_manifest = array();
                $serial_manifest['manifest'] = (isset($manifest) ? $manifest : '');
                $serial_manifest['installdefs'] = (isset($installdefs) ? $installdefs : '');
                $serial_manifest['upgrade_manifest'] = (isset($upgrade_manifest) ? $upgrade_manifest : '');
                $new_upgrade->manifest   = base64_encode(serialize($serial_manifest));
                $new_upgrade->save();
                unlink($file);
            }//rof
    }//fi
    $mod_strings = $old_mod_strings;
}

function commitModules($unlink = false, $type = 'module'){
    require_once('ModuleInstall/ModuleInstaller.php');
    require_once('include/entryPoint.php');


    global $mod_strings;
    global $base_upgrade_dir;
    global $base_tmp_upgrade_dir;
    global $db;
    $GLOBALS['db'] = $db;
    $errors = array();
    $files = array();
     global $current_user;
    $current_user = new User();
    $current_user->is_admin = '1';
    $old_mod_strings = $mod_strings;
    if(is_dir(getcwd()."/cache/upload/upgrades")) {
            $files = findAllFiles(getcwd()."/cache/upload/upgrades/$type", $files);
            $mi = new ModuleInstaller();
            $mi->silent = true;
            $mod_strings = return_module_language('en', "Administration");

            foreach($files as $file) {
                if(!preg_match("#.*\.zip\$#", $file)) {
                    continue;
                }
                $lic_name = 'accept_lic_'.str_replace('.', '_', urlencode(basename($file)));

                $can_install = true;
                if(isset($_REQUEST[$lic_name])){
                    if($_REQUEST[$lic_name] == 'yes'){
                        $can_install = true;
                    }else{
                        $can_install = false;
                    }
                }
                if($can_install){
                    // handle manifest.php
                    $target_manifest = remove_file_extension( $file ) . '-manifest.php';
                    if($type == 'langpack'){
                        $_REQUEST['manifest'] = $target_manifest;
                        $_REQUEST['zipFile'] = $file;
                        commitLanguagePack();
                        continue;
                    }
                    include($target_manifest);

                    $unzip_dir = mk_temp_dir( $base_tmp_upgrade_dir );
                    unzip($file, $unzip_dir );
                    $_REQUEST['install_file'] = $file;
                    $mi->install($unzip_dir);
                    $new_upgrade = new UpgradeHistory();
                    $new_upgrade->filename      = $file;
                    $new_upgrade->md5sum        = md5_file($file);
                    $new_upgrade->type          = $manifest['type'];
                    $new_upgrade->version       = $manifest['version'];
                    $new_upgrade->status        = "installed";
                   // $new_upgrade->author        = $manifest['author'];
                    $new_upgrade->name          = $manifest['name'];
                    $new_upgrade->description   = $manifest['description'];
                    $new_upgrade->id_name       = (isset($installdefs['id_name'])) ? $installdefs['id_name'] : '';
                    $serial_manifest = array();
                    $serial_manifest['manifest'] = (isset($manifest) ? $manifest : '');
                    $serial_manifest['installdefs'] = (isset($installdefs) ? $installdefs : '');
                    $serial_manifest['upgrade_manifest'] = (isset($upgrade_manifest) ? $upgrade_manifest : '');
                    $new_upgrade->manifest   = base64_encode(serialize($serial_manifest));
                    $new_upgrade->save();
                    //unlink($file);
                }//fi
            }//rof
    }//fi
    $mod_strings = $old_mod_strings;
}

/**
 * creates UpgradeHistory entries
 * @param mode string Install or Uninstall
 */
function updateUpgradeHistory() {
    if(isset($_SESSION['INSTALLED_LANG_PACKS']) && count($_SESSION['INSTALLED_LANG_PACKS']) > 0) {
        foreach($_SESSION['INSTALLED_LANG_PACKS'] as $k => $zipFile) {
            $new_upgrade = new UpgradeHistory();
            $new_upgrade->filename      = $zipFile;
            $new_upgrade->md5sum        = md5_file($zipFile);
            $new_upgrade->type          = 'langpack';
            $new_upgrade->version       = $_SESSION['INSTALLED_LANG_PACKS_VERSION'][$k];
            $new_upgrade->status        = "installed";
            $new_upgrade->manifest      = $_SESSION['INSTALLED_LANG_PACKS_MANIFEST'][$k];
            $new_upgrade->save();
        }
    }
}


/**
 * removes the installed language pack, but the zip is still in the cache dir
 */
function removeLanguagePack() {
    global $mod_strings;
    global $sugar_config;

    $errors = array();
    $manifest = urldecode($_REQUEST['manifest']);
    $zipFile = urldecode($_REQUEST['zipFile']);

    if(isset($manifest) && !empty($manifest)) {
        if(is_file($manifest)) {
            if(!unlink($manifest)) {
                $errors[] = $mod_strings['ERR_LANG_CANNOT_DELETE_FILE'].$manifest;
            }
        } else {
            $errors[] = $mod_strings['ERR_LANG_MISSING_FILE'].$manifest;
        }
        unset($_SESSION['packages_to_install'][$manifest]);
    }
    if(isset($zipFile) && !empty($zipFile)) {
        if(is_file($zipFile)) {
            if(!unlink($zipFile)) {
                $errors[] = $mod_strings['ERR_LANG_CANNOT_DELETE_FILE'].$zipFile;
            }
        } else {
            $errors[] = $mod_strings['ERR_LANG_MISSING_FILE'].$zipFile;
        }
    }
    if(count($errors > 0)) {
        echo "<p class='error'>";
        foreach($errors as $error) {
            echo "{$error}<br>";
        }
        echo "</p>";
    }

    unlinkTempFiles($manifest, $zipFile);
}



/**
 * takes the current value of $sugar_config and writes it out to config.php (sorta the same as the final step)
 * @param array sugar_config
 */
function writeSugarConfig($sugar_config) {
    ksort($sugar_config);
    $sugar_config_string = "<?php\n" .
        '// created: ' . date('Y-m-d H:i:s') . "\n" .
        '$sugar_config = ' .
        var_export($sugar_config, true) .
        ";\n?>\n";
    if(is_writable('config.php') && write_array_to_file( "sugar_config", $sugar_config, "config.php")) {
    }
}


/**
 * uninstalls the Language pack
 */
function uninstallLangPack() {
    global $sugar_config;

    // remove language from config
    $new_langs = array();
    $old_langs = $sugar_config['languages'];
    foreach( $old_langs as $key => $value ){
        if( $key != $_REQUEST['new_lang_name'] ){
            $new_langs += array( $key => $value );
        }
    }
    $sugar_config['languages'] = $new_langs;

    writeSugarConfig($sugar_config);
}

/**
 * retrieves the name of the language
 */
if ( !function_exists('getLanguagePackName') ) {
function getLanguagePackName($the_file) {
    require_once( "$the_file" );
    if( isset( $app_list_strings["language_pack_name"] ) ){
        return( $app_list_strings["language_pack_name"] );
    }
    return( "" );
}
}

function getInstalledLangPacks($showButtons=true) {
    global $mod_strings;
    global $next_step;

    $ret  = "<tr><td colspan=7 align=left>{$mod_strings['LBL_LANG_PACK_INSTALLED']}</td></tr>";
    //$ret .="<table width='100%' cellpadding='0' cellspacing='0' border='0'>";
    $ret .= "<tr>
                <td width='15%' ><b>{$mod_strings['LBL_ML_NAME']}</b></td>
                <td width='15%' ><b>{$mod_strings['LBL_ML_VERSION']}</b></td>
                <td width='15%' ><b>{$mod_strings['LBL_ML_PUBLISHED']}</b></td>
                <td width='15%' ><b>{$mod_strings['LBL_ML_UNINSTALLABLE']}</b></td>
                <td width='15%' ><b>{$mod_strings['LBL_ML_DESCRIPTION']}</b></td>
                <td width='15%' ></td>
                <td width='15%' ></td>
            </tr>\n";
    $files = array();
    $files = findAllFiles(getcwd()."/cache/upload/upgrades", $files);

    if(isset($_SESSION['INSTALLED_LANG_PACKS']) && !empty($_SESSION['INSTALLED_LANG_PACKS'])){
        if(count($_SESSION['INSTALLED_LANG_PACKS'] > 0)) {
            foreach($_SESSION['INSTALLED_LANG_PACKS'] as $file) {
                // handle manifest.php
                $target_manifest = remove_file_extension( $file ) . '-manifest.php';
                include($target_manifest);

                $name = empty($manifest['name']) ? $file : $manifest['name'];
                $version = empty($manifest['version']) ? '' : $manifest['version'];
                $published_date = empty($manifest['published_date']) ? '' : $manifest['published_date'];
                $icon = '';
                $description = empty($manifest['description']) ? 'None' : $manifest['description'];
                $uninstallable = empty($manifest['is_uninstallable']) ? 'No' : 'Yes';
                $manifest_type = $manifest['type'];

                $deletePackage = getPackButton('uninstall', $target_manifest, $file, $next_step, $uninstallable, $showButtons);
                //$ret .="<table width='100%' cellpadding='0' cellspacing='0' border='0'>";
                $ret .= "<tr>";
                $ret .= "<td width='15%' >".$name."</td>";
                $ret .= "<td width='15%' >".$version."</td>";
                $ret .= "<td width='15%' >".$published_date."</td>";
                $ret .= "<td width='15%' >".$uninstallable."</td>";
                $ret .= "<td width='15%' >".$description."</td>";
                $ret .= "<td width='15%' ></td>";
                $ret .= "<td width='15%' >{$deletePackage}</td>";
                $ret .= "</tr>";
            }
        } else {
            $ret .= "</tr><td colspan=7><i>{$mod_strings['LBL_LANG_NO_PACKS']}</i></td></tr>";
        }
    } else {
        $ret .= "</tr><td colspan=7><i>{$mod_strings['LBL_LANG_NO_PACKS']}</i></td></tr>";
    }
    return $ret;
}


function uninstallLanguagePack() {
    return commitLanguagePack(true);
}


function getSugarConfigLanguageArray($langZip) {
    global $sugar_config;

    include(remove_file_extension($langZip)."-manifest.php");
    $ret = '';
    if(isset($installdefs['id']) && isset($manifest['name'])) {
        $ret = $installdefs['id']."::".$manifest['name']."::".$manifest['version'];
    }

    return $ret;
}



///////////////////////////////////////////////////////////////////////////////
////    FROM performSetup.php
/**
 * creates the Sugar DB user (if not admin)
 */
function handleDbCreateSugarUser() {
    global $mod_strings;
    global $setup_db_database_name;
    global $setup_db_host_name;
    global $setup_db_host_instance;
    global $setup_db_admin_user_name;
    global $setup_db_admin_password;
    global $sugar_config;
    global $setup_db_sugarsales_user;
    global $setup_site_host_name;
    global $setup_db_sugarsales_password;

    echo $mod_strings['LBL_PERFORM_CREATE_DB_USER'];
    $errno = '';
    switch($_SESSION['setup_db_type']) {
        case "mysql":
            if(isset($_SESSION['mysql_type'])){
                $host_name = getHostPortFromString($setup_db_host_name);
                if(empty($host_name)){
                    $link = @mysqli_connect($setup_db_host_name, $setup_db_admin_user_name, $setup_db_admin_password);
                }else{
                    $link = @mysqli_connect($host_name[0], $setup_db_admin_user_name, $setup_db_admin_password,null,$host_name[1]);
                }
                $query  = "GRANT SELECT, INSERT, UPDATE, DELETE, CREATE, ALTER, DROP, INDEX
                            ON `{$setup_db_database_name}`.*
                            TO \"{$setup_db_sugarsales_user}\"@\"{$setup_site_host_name}\"
                            IDENTIFIED BY '{$setup_db_sugarsales_password}';";

                if(!@mysqli_query($link, $query)) {
                    $errno = mysqli_errno($link);
                    $error = mysqli_error($link);
                }

                $query  = "SET PASSWORD FOR \"{$setup_db_sugarsales_user}\"@\"{$setup_site_host_name}\" = old_password('{$setup_db_sugarsales_password}');";

                if(!@mysqli_query($link, $query)) {
                     $errno = mysqli_errno($link);
                     $error = mysqli_error($link);
                }

                if($setup_site_host_name != 'localhost') {
                    echo $mod_strings['LBL_PERFORM_CREATE_LOCALHOST'];

                    $host_name = getHostPortFromString($setup_db_host_name);
                    if(empty($host_name)){
                        $link   = @mysqli_connect($setup_db_host_name, $setup_db_admin_user_name, $setup_db_admin_password);
                    }else{
                        $link   = @mysqli_connect($host_name[0], $setup_db_admin_user_name, $setup_db_admin_password,null,$host_name[1]);
                    }

                    $query  = "GRANT SELECT, INSERT, UPDATE, DELETE, CREATE, ALTER, DROP, INDEX
                                ON `{$setup_db_database_name}`.*
                                TO \"{$setup_db_sugarsales_user}\"@\"localhost\"
                                IDENTIFIED BY '{$setup_db_sugarsales_password}';";
                    if(!@mysqli_query($link, $query)) {
                        $errno = mysqli_errno($link);
                        $error = mysqli_error($link);
                    }

                    $query = "SET PASSWORD FOR \"{$setup_db_sugarsales_user}\"@\"localhost\"\ = old_password('{$setup_db_sugarsales_password}');";

                    if(!@mysqli_query($link, $query)) {
                        $errno = mysqli_errno($link);
                        $error = mysqli_error($link);
                    }
                } // end LOCALHOST

                mysqli_close($link);

            }else{
                $link   = @mysql_connect($setup_db_host_name, $setup_db_admin_user_name, $setup_db_admin_password);
                $query  = "GRANT SELECT, INSERT, UPDATE, DELETE, CREATE, ALTER, DROP, INDEX
                            ON `{$setup_db_database_name}`.*
                            TO \"{$setup_db_sugarsales_user}\"@\"{$setup_site_host_name}\"
                            IDENTIFIED BY '{$setup_db_sugarsales_password}';";

                if(!@mysql_query($query, $link)) {
                    $errno = mysql_errno();
                    $error = mysql_error();
                }

                $query  = "SET PASSWORD FOR \"{$setup_db_sugarsales_user}\"@\"{$setup_site_host_name}\" = old_password('{$setup_db_sugarsales_password}');";

                if(!@mysql_query($query, $link)) {
                     $errno = mysql_errno();
                     $error = mysql_error();
                }

                if($setup_site_host_name != 'localhost') {
                    echo $mod_strings['LBL_PERFORM_CREATE_LOCALHOST'];

                    $link   = @mysql_connect($setup_db_host_name, $setup_db_admin_user_name, $setup_db_admin_password);
                    $query  = "GRANT SELECT, INSERT, UPDATE, DELETE, CREATE, ALTER, DROP, INDEX
                                ON `{$setup_db_database_name}`.*
                                TO \"{$setup_db_sugarsales_user}\"@\"localhost\"
                                IDENTIFIED BY '{$setup_db_sugarsales_password}';";
                    if(!@mysql_query($query, $link)) {
                        $errno = mysql_errno();
                        $error = mysql_error();
                    }

                    $query = "SET PASSWORD FOR \"{$setup_db_sugarsales_user}\"@\"localhost\"\ = old_password('{$setup_db_sugarsales_password}');";

                    if(!@mysql_query($query, $link)) {
                        $errno = mysql_errno();
                        $error = mysql_error();
                    }
                } // end LOCALHOST

                mysql_close($link);

            }
        break;

        case 'mssql':
        $setup_db_host_instance = trim($setup_db_host_instance);

        $connect_host = "";
        if (empty($setup_db_host_instance)){
            $connect_host = $setup_db_host_name ;
        }else{
            $connect_host = $setup_db_host_name . "\\" . $setup_db_host_instance;
        }
        if(isset($_SESSION['mssql_type'])){
            $link = sqlsrv_connect($connect_host , array( 'UID' => $setup_db_admin_user_name, 'PWD' => $setup_db_admin_password));
            $query = "USE " . $setup_db_database_name . ";";
            @sqlsrv_query($link,$query);

            $query = "CREATE LOGIN $setup_db_sugarsales_user WITH PASSWORD = '$setup_db_sugarsales_password'";
            if(!sqlsrv_query($link,$query)) {
                $errno = 9999;
                displayMssqlErrors('mssqlsrv', $query);
                break;
            }

            $query = "CREATE USER $setup_db_sugarsales_user  FOR LOGIN $setup_db_sugarsales_user ";
            if(!sqlsrv_query($link,$query)) {
                $errno = 9999;
                displayMssqlErrors('mssqlsrv', $query);
                break;
            }

            $query = "EXEC sp_addRoleMember 'db_ddladmin ', '$setup_db_sugarsales_user'";
            if(!sqlsrv_query($link,$query)) {
                $errno = 9999;
                displayMssqlErrors('mssqlsrv', $query);
                break;
            }

            $query = "EXEC sp_addRoleMember 'db_datareader','$setup_db_sugarsales_user'";
            if(!sqlsrv_query($link,$query)) {
                $errno = 9999;
                displayMssqlErrors('mssqlsrv', $query);
                break;
            }

            $query = "EXEC sp_addRoleMember 'db_datawriter','$setup_db_sugarsales_user'";
            if(!sqlsrv_query($link,$query)) {
                $errno = 9999;
                displayMssqlErrors('mssqlsrv', $query);
                break;
            }
        }
        else {
            $link = mssql_connect($connect_host , $setup_db_admin_user_name, $setup_db_admin_password);
            $query = "USE " . $setup_db_database_name . ";";
            @mssql_query($query);

            $query = "CREATE LOGIN $setup_db_sugarsales_user WITH PASSWORD = '$setup_db_sugarsales_password'";
            if(!@mssql_query($query)) {
                $errno = 9999;
                $error = "Error Adding Login. SQL Query: $query";
				displayMssqlErrors('mssql', $query);
				break;
            }

            $query = "CREATE USER $setup_db_sugarsales_user  FOR LOGIN $setup_db_sugarsales_user ";
            if(!@mssql_query($query)) {
                $errno = 9999;
                $error = "Error Granting Access. SQL Query: $query";
				displayMssqlErrors('mssql', $query);
				break;
            }

            $query = "EXEC sp_addRoleMember 'db_ddladmin ', '$setup_db_sugarsales_user'";
            if(!@mssql_query($query)) {
                $errno = 9999;
                $error = "Error Adding Role db_owner. SQL Query: $query";
				displayMssqlErrors('mssql', $query);
				break;
            }

            $query = "EXEC sp_addRoleMember 'db_datareader','$setup_db_sugarsales_user'";
            if(!@mssql_query($query)) {
                $errno = 9999;
                $error = "Error Adding Role db_datareader. SQL Query: $query";
				displayMssqlErrors('mssql', $query);
				break;
            }

            $query = "EXEC sp_addRoleMember 'db_datawriter','$setup_db_sugarsales_user'";
            if(!@mssql_query($query)) {
                $errno = 9999;
                $error = "Error Adding Role db_datawriter. SQL Query: $query";
				displayMssqlErrors('mssql', $query);
				break;
            }
        }
        break;
    } // end switch()
    if($errno == '')
    echo $mod_strings['LBL_PERFORM_DONE'];
}

function displayMssqlErrors($driver_type, $query) {
    global $sugar_config;
    if($driver_type =='mssqlsrv' && ($errors = sqlsrv_errors(SQLSRV_ERR_ALL) ) != null)
    {
       foreach( $errors as $error)
       {

          echo "<div style='color:red;'>";
          echo "An error occured when performing the folloing query:<br>";
          echo "$query<br>";
          echo "</div>";
          installLog("An error occured when performing the query:".$query." SQLSTATE: ".$error[ 'SQLSTATE']." message: ".$error[ 'message']);
       }
    }
	if($driver_type =='mssql')
    {
          echo "<div style='color:red;'>";
          echo "An error occured when performing the folloing query:<br>";
          echo "$query<br>";
          echo "</div>";
          installLog("An error occured when performing the query:".$query." message: ".mssql_get_last_message());
    }
}
/**
 * ensures that the charset and collation for a given database is set
 * MYSQL ONLY
 */
function handleDbCharsetCollation() {
    global $mod_strings;
    global $setup_db_database_name;
    global $setup_db_host_name;
    global $setup_db_admin_user_name;
    global $setup_db_admin_password;
    global $sugar_config;

    if($_SESSION['setup_db_type'] == 'mysql') {
        if(isset($_SESSION['mysql_type'])){
           $host_name = getHostPortFromString($setup_db_host_name);
            if(empty($host_name)){
                $link = @mysqli_connect($setup_db_host_name, $setup_db_admin_user_name, $setup_db_admin_password);

            }else{
                    $link   = @mysqli_connect($host_name[0], $setup_db_admin_user_name, $setup_db_admin_password,null,$host_name[1]);
            }

            $q1 = "ALTER DATABASE `{$setup_db_database_name}` DEFAULT CHARACTER SET utf8";
            $q2 = "ALTER DATABASE `{$setup_db_database_name}` DEFAULT COLLATE utf8_general_ci";
            @mysqli_query($link, $q1);
            @mysqli_query($link, $q2);

        }else{
            $link = @mysql_connect($setup_db_host_name, $setup_db_admin_user_name, $setup_db_admin_password);
            $q1 = "ALTER DATABASE `{$setup_db_database_name}` DEFAULT CHARACTER SET utf8";
            $q2 = "ALTER DATABASE `{$setup_db_database_name}` DEFAULT COLLATE utf8_general_ci";
            @mysql_query($q1, $link);
            @mysql_query($q2, $link);
        }
    }
}


/**
 * creates the new database
 */
function handleDbCreateDatabase() {
    global $mod_strings;
    global $setup_db_database_name;
    global $setup_db_host_name;
    global $setup_db_host_instance;
    global $setup_db_admin_user_name;
    global $setup_db_admin_password;
    global $sugar_config;

    echo "{$mod_strings['LBL_PERFORM_CREATE_DB_1']} {$setup_db_database_name} {$mod_strings['LBL_PERFORM_CREATE_DB_2']} {$setup_db_host_name}...";

    switch($_SESSION['setup_db_type']) {
        case 'mysql':
            if(isset($_SESSION['mysql_type'])){
                $host_name = getHostPortFromString($setup_db_host_name);
                if(empty($host_name)){
                    $link = @mysqli_connect($setup_db_host_name, $setup_db_admin_user_name, $setup_db_admin_password);
                }else{
                    $link   = @mysqli_connect($host_name[0], $setup_db_admin_user_name, $setup_db_admin_password,null,$host_name[1]);
                }
                $drop = 'DROP DATABASE IF EXISTS '.$setup_db_database_name;
                @mysqli_query($link, $drop);

                $query = 'CREATE DATABASE `' . $setup_db_database_name . '` CHARACTER SET utf8 COLLATE utf8_general_ci';
                @mysqli_query($link, $query);
                mysqli_close($link);

            }else{
                $link = @mysql_connect($setup_db_host_name, $setup_db_admin_user_name, $setup_db_admin_password);
                $drop = 'DROP DATABASE IF EXISTS '.$setup_db_database_name;
                @mysql_query($drop, $link);

                $query = 'CREATE DATABASE `' . $setup_db_database_name . '` CHARACTER SET utf8 COLLATE utf8_general_ci';
                @mysql_query($query, $link);
                mysql_close($link);

            }
        break;

        case 'mssql':
        $connect_host = "";
        $setup_db_host_instance = trim($setup_db_host_instance);
        if (empty($setup_db_host_instance)){
            $connect_host = $setup_db_host_name ;
        }else{
            $connect_host = $setup_db_host_name . "\\" . $setup_db_host_instance;
        }
        if(isset($_SESSION['mssql_type'])){
            $link = sqlsrv_connect($connect_host , array( 'UID' => $setup_db_admin_user_name, 'PWD' => $setup_db_admin_password));
            $setup_db_database_name = str_replace(' ', '_', $setup_db_database_name);  // remove space

            //create check to see if this is existing db
            $check = "SELECT count(name) num FROM master..sysdatabases WHERE name = N'".$setup_db_database_name."'";
            $tableCntRes = sqlsrv_query($link,$check);
            $tableCnt= sqlsrv_fetch_array($tableCntRes);

            //if this db already exists, then drop it
            if($tableCnt[0]>0){
                $drop = "DROP DATABASE $setup_db_database_name";
               @ sqlsrv_query($link,$drop);
            }

            //create db
            $query = 'create database '.$setup_db_database_name;
            @sqlsrv_query($link,$query);
            @sqlsrv_close($link);
        }
        else {
            $link = @mssql_connect($connect_host, $setup_db_admin_user_name, $setup_db_admin_password);
            $setup_db_database_name = str_replace(' ', '_', $setup_db_database_name);  // remove space

            //create check to see if this is existing db
            $check = "SELECT count(name) num FROM master..sysdatabases WHERE name = N'".$setup_db_database_name."'";
            $tableCntRes = mssql_query($check);
            $tableCnt= mssql_fetch_row($tableCntRes);

            //if this db already exists, then drop it
            if($tableCnt[0]>0){
                $drop = "DROP DATABASE $setup_db_database_name";
               @ mssql_query($drop);
            }

            //create db
            $query = 'create database '.$setup_db_database_name;
            @mssql_query($query);
            @mssql_close($link);
        }
        break;

    }
    echo $mod_strings['LBL_PERFORM_DONE'];
}


/**
 * handles creation of Log4PHP properties file
 * This function has been deprecated.  Use SugarLogger.
 */
function handleLog4Php() {
    return;
}

function installLog($entry) {
    global $mod_strings;
    $nl = '
'.gmdate("Y-m-d H:i:s").'...';
    $log = clean_path(getcwd().'/install.log');

    // create if not exists
    if(!file_exists($log)) {
        $fp = @sugar_fopen($log, 'w+'); // attempts to create file
        if(!is_resource($fp)) {
            $GLOBALS['log']->fatal('could not create the install.log file');
        }
    } else {
        $fp = @sugar_fopen($log, 'a+'); // write pointer at end of file
        if(!is_resource($fp)) {
            $GLOBALS['log']->fatal('could not open/lock install.log file');
        }
    }



    if(@fwrite($fp, $nl.$entry) === false) {
        $GLOBALS['log']->fatal('could not write to install.log: '.$entry);
    }

    if(is_resource($fp)) {
        fclose($fp);
    }
}



/**
 * takes session vars and creates config.php
 * @return array bottle collection of error messages
 */
function handleSugarConfig() {
    global $bottle;
    global $cache_dir;
    global $mod_strings;
    global $setup_db_host_name;
    global $setup_db_host_instance;
    global $setup_db_sugarsales_user;
    global $setup_db_sugarsales_password;
    global $setup_db_database_name;
    global $setup_site_host_name;
    global $setup_site_log_dir;
    global $setup_site_log_file;
    global $setup_site_session_path;
    global $setup_site_guid;
    global $setup_site_url;
    global $setup_sugar_version;
    global $sugar_config;
    global $setup_site_log_level;

    echo "<b>{$mod_strings['LBL_PERFORM_CONFIG_PHP']} (config.php)</b><br>";
    ///////////////////////////////////////////////////////////////////////////////
    ////    $sugar_config SETTINGS
    if( is_file('config.php') ){
        $is_writable = is_writable('config.php');
        // require is needed here (config.php is sometimes require'd from install.php)
        require('config.php');
    } else {
        $is_writable = is_writable('.');
    }

    // build default sugar_config and merge with new values
    $sugar_config = sugarArrayMerge(get_sugar_config_defaults(), $sugar_config);
    // always lock the installer
    $sugar_config['installer_locked'] = true;
    // we're setting these since the user was given a fair chance to change them
    $sugar_config['dbconfig']['db_host_name']       = $setup_db_host_name;
    if($_SESSION['setup_db_type'] == 'mssql') {
    $sugar_config['dbconfig']['db_host_instance']   = $setup_db_host_instance;
    }
    $sugar_config['dbconfig']['db_user_name']       = $setup_db_sugarsales_user;
    $sugar_config['dbconfig']['db_password']        = $setup_db_sugarsales_password;
    $sugar_config['dbconfig']['db_name']            = $setup_db_database_name;
    $sugar_config['dbconfig']['db_type']            = $_SESSION['setup_db_type'];
    if(isset($_SESSION['setup_db_port_num'])){
        $sugar_config['dbconfig']['db_port'] = $_SESSION['setup_db_port_num'];
    }
    $sugar_config['cache_dir']                      = $cache_dir;
    $sugar_config['default_charset']                = $mod_strings['DEFAULT_CHARSET'];
    $sugar_config['default_email_client']           = 'sugar';
    $sugar_config['default_email_editor']           = 'html';
    $sugar_config['host_name']                      = $setup_site_host_name;
    $sugar_config['import_dir']                 = $cache_dir.'import/';
    $sugar_config['js_custom_version']              = '';
    $sugar_config['use_real_names']                 = true;
    $sugar_config['log_dir']                        = $setup_site_log_dir;
    $sugar_config['log_file']                       = $setup_site_log_file;

	/*nsingh(bug 22402): Consolidate logger settings under $config['logger'] as liked by the new logger! If log4pphp exists,
		these settings will be overwritten by those in log4php.properties when the user access admin->system settings.*/
    $sugar_config['logger']	=
    	array ('level'=>$setup_site_log_level,
    	 'file' => array(
			'ext' => '.log',
			'name' => 'sugarcrm',
			'dateFormat' => '%c',
			'maxSize' => '10MB',
			'maxLogs' => 10,
			'suffix' => '%m_%Y'),
  	);
    $sugar_config['session_dir']                    = $setup_site_session_path;
    $sugar_config['site_url']                       = $setup_site_url;
    $sugar_config['sugar_version']                  = $setup_sugar_version;
    $sugar_config['tmp_dir']                        = $cache_dir.'xml/';
    $sugar_config['upload_dir']                 = $cache_dir.'upload/';
//    $sugar_config['use_php_code_json']              = returnPhpJsonStatus(); // true on error
    if( isset($_SESSION['setup_site_sugarbeet_anonymous_stats']) ){
        $sugar_config['sugarbeet']      = $_SESSION['setup_site_sugarbeet_anonymous_stats'];
    }
    $sugar_config['demoData'] = $_SESSION['demoData'];
    if( isset( $setup_site_guid ) ){
        $sugar_config['unique_key'] = $setup_site_guid;
    }
    if(empty($sugar_config['unique_key'])){
        $sugar_config['unique_key'] = md5( create_guid() );
    }
    // add installed langs to config
    // entry in upgrade_history comes AFTER table creation
    if(isset($_SESSION['INSTALLED_LANG_PACKS']) && is_array($_SESSION['INSTALLED_LANG_PACKS']) && !empty($_SESSION['INSTALLED_LANG_PACKS'])) {
        foreach($_SESSION['INSTALLED_LANG_PACKS'] as $langZip) {
            $lang = getSugarConfigLanguageArray($langZip);
            if(!empty($lang)) {
                $exLang = explode('::', $lang);
                if(is_array($exLang) && count($exLang) == 3) {
                    $sugar_config['languages'][$exLang[0]] = $exLang[1];
                }
            }
        }
    }
    if(file_exists('install/lang.config.php')){
    	include('install/lang.config.php');
    	if(!empty($config['languages'])){
    		foreach($config['languages'] as $lang=>$label){
    			$sugar_config['languages'][$lang] = $label;
    		}
    	}
    }

    ksort($sugar_config);
    $sugar_config_string = "<?php\n" .
        '// created: ' . date('Y-m-d H:i:s') . "\n" .
        '$sugar_config = ' .
        var_export($sugar_config, true) .
        ";\n?>\n";
    if($is_writable && write_array_to_file( "sugar_config", $sugar_config, "config.php")) {
        // was 'Done'
    } else {
        echo 'failed<br>';
        echo "<p>{$mod_strings['ERR_PERFORM_CONFIG_PHP_1']}</p>\n";
        echo "<p>{$mod_strings['ERR_PERFORM_CONFIG_PHP_2']}</p>\n";
        echo "<TEXTAREA  rows=\"15\" cols=\"80\">".$sugar_config_string."</TEXTAREA>";
        echo "<p>{$mod_strings['ERR_PERFORM_CONFIG_PHP_3']}</p>";

        $bottle[] = $mod_strings['ERR_PERFORM_CONFIG_PHP_4'];
    }


    //Now merge the config_si.php settings into config.php
    if(file_exists('config.php') && file_exists('config_si.php'))
    {
       require_once('modules/UpgradeWizard/uw_utils.php');
       merge_config_si_settings(false, 'config.php', 'config_si.php');
    }


    ////    END $sugar_config
    ///////////////////////////////////////////////////////////////////////////////
    return $bottle;
}
/**
 * (re)write the .htaccess file to prevent browser access to the log file
 */
function handleHtaccess(){
global $mod_strings;
$ignoreCase = (substr_count(strtolower($_SERVER['SERVER_SOFTWARE']), 'apache/2') > 0)?'(?i)':'';
$htaccess_file   = getcwd() . "/.htaccess";
$contents = '';
$restrict_str = <<<EOQ

# BEGIN SUGARCRM RESTRICTIONS
RedirectMatch 403 {$ignoreCase}.*\.log$
RedirectMatch 403 {$ignoreCase}/+not_imported_.*\.txt
RedirectMatch 403 {$ignoreCase}/+(soap|cache|xtemplate|data|examples|include|log4php|metadata|modules)/+.*\.(php|tpl)
RedirectMatch 403 {$ignoreCase}/+emailmandelivery\.php
RedirectMatch 403 {$ignoreCase}/+cache/+upload
RedirectMatch 403 {$ignoreCase}/+cache/+diagnostic
RedirectMatch 403 {$ignoreCase}/+files\.md5$
# END SUGARCRM RESTRICTIONS
EOQ;
	if(file_exists($htaccess_file)){
	 	$fp = fopen($htaccess_file, 'r');
	 	$skip = false;
	 	while($line = fgets($fp)){

	 		if(preg_match("/\s*#\s*BEGIN\s*SUGARCRM\s*RESTRICTIONS/i", $line))$skip = true;
	 		if(!$skip)$contents .= $line;
	 		if(preg_match("/\s*#\s*END\s*SUGARCRM\s*RESTRICTIONS/i", $line))$skip = false;
	 	}
	}
	$status =  file_put_contents($htaccess_file, $contents . $restrict_str);
    if( !$status ) {
        echo "<p>{$mod_strings['ERR_PERFORM_HTACCESS_1']}<span class=stop>{$htaccess_file}</span> {$mod_strings['ERR_PERFORM_HTACCESS_2']}</p>\n";
        echo "<p>{$mod_strings['ERR_PERFORM_HTACCESS_3']}</p>\n";
        echo $restrict_str;
    }
    return $status;
}

/**
 * (re)write the web.config file to prevent browser access to the log file
 */
function handleWebConfig() 
{
    if ( !isset($_SERVER['IIS_UrlRewriteModule']) ) {
        return;
    }

	global $setup_site_log_dir;
    global $setup_site_log_file;
    global $sugar_config;

    // Bug 36968 - Fallback to using $sugar_config values when we are not calling this from the installer
    if (empty($setup_site_log_file)) {
        $setup_site_log_file = $sugar_config['log_file'];
        if ( empty($sugar_config['log_file']) ) {
            $setup_site_log_file = 'sugarcrm.log';
        }
    }
    if (empty($setup_site_log_dir)) {
        $setup_site_log_dir = $sugar_config['log_dir'];
        if ( empty($sugar_config['log_dir']) ) {
            $setup_site_log_dir = '.';
        }
    }

    $prefix = $setup_site_log_dir.empty($setup_site_log_dir)?'':'/';


    $config_array = array(
    array('1'=> $prefix.str_replace('.','\\.',$setup_site_log_file).'\\.*' ,'2'=>'log_file_restricted.html'),
    array('1'=> $prefix.'install.log' ,'2'=>'log_file_restricted.html'),
    array('1'=> $prefix.'upgradeWizard.log' ,'2'=>'log_file_restricted.html'),
    array('1'=> $prefix.'emailman.log' ,'2'=>'log_file_restricted.html'),
    array('1'=>'not_imported_.*.txt' ,'2'=>'log_file_restricted.html'),
    array('1'=>'XTemplate/(.*)/(.*).php' ,'2'=>'index.php'),
    array('1'=>'data/(.*).php' ,'2'=>'index.php'),
    array('1'=>'examples/(.*).php' ,'2'=>'index.php'),
    array('1'=>'include/(.*).php' ,'2'=>'index.php'),
    array('1'=>'include/(.*)/(.*).php' ,'2'=>'index.php'),
    array('1'=>'log4php/(.*).php' ,'2'=>'index.php'),
    array('1'=>'log4php/(.*)/(.*)' ,'2'=>'index.php'),
    array('1'=>'metadata/(.*)/(.*).php' ,'2'=>'index.php'),
    array('1'=>'modules/(.*)/(.*).php' ,'2'=>'index.php'),
    array('1'=>'soap/(.*).php' ,'2'=>'index.php'),
    array('1'=>'emailmandelivery.php' ,'2'=>'index.php'),
    array('1'=>'cron.php' ,'2'=>'index.php'),
    array('1'=> $sugar_config['upload_dir'].'.*' ,'2'=>'index.php'),
    );


    $xmldoc = new XMLWriter();
    $xmldoc->openURI('web.config');
    $xmldoc->setIndent(true);
    $xmldoc->setIndentString(' ');
    $xmldoc->startDocument('1.0','UTF-8');
    $xmldoc->startElement('configuration');
    $xmldoc->startElement('system.webServer');
    $xmldoc->startElement('rewrite');
    $xmldoc->startElement('rules');
    for ($i = 0; $i < count($config_array); $i++) {
        $xmldoc->startElement('rule');
        $xmldoc->writeAttribute('name', "redirect$i");
        $xmldoc->writeAttribute('stopProcessing', 'true');
        $xmldoc->startElement('match');
        $xmldoc->writeAttribute('url', $config_array[$i]['1']);
        $xmldoc->endElement();
        $xmldoc->startElement('action');
        $xmldoc->writeAttribute('type', 'Redirect');
        $xmldoc->writeAttribute('url', $config_array[$i]['2']);
        $xmldoc->writeAttribute('redirectType', 'Found');
        $xmldoc->endElement();
        $xmldoc->endElement();
    }
    $xmldoc->endElement();
    $xmldoc->endElement();
    $xmldoc->endElement();
    $xmldoc->endElement();
    $xmldoc->endDocument();
    $xmldoc->flush();
}

/**
 * Drop old tables if table exists and told to drop it
 */
function drop_table_install( &$focus ){
    global $db;
    global $dictionary;

    $result = $db->tableExists($focus->table_name);

    if( $result ){
        $focus->drop_tables();
        $GLOBALS['log']->info("Dropped old ".$focus->table_name." table.");
        return 1;
    }
    else {
        $GLOBALS['log']->info("Did not need to drop old ".$focus->table_name." table.  It doesn't exist.");
        return 0;
    }
}

// Creating new tables if they don't exist.
function create_table_if_not_exist( &$focus ){
    global  $db;
    $table_created = false;

    // normal code follows
    $result = $db->tableExists($focus->table_name);
    if($result){
        $GLOBALS['log']->info("Table ".$focus->table_name." already exists.");
    } else {
        $focus->create_tables();
        $GLOBALS['log']->info("Created ".$focus->table_name." table.");
        $table_created = true;
    }
    return $table_created;
}



function create_default_users(){
    global $db;
    global $setup_site_admin_password;
    global $setup_site_admin_user_name;
    global $create_default_user;
    global $sugar_config;
    
	require_once('install/UserDemoData.php');

    //Create default admin user
    $user = new User();
    $user->id = 1;
    $user->new_with_id = true;
    $user->last_name = 'Administrator';
    //$user->user_name = 'admin';
    $user->user_name = $setup_site_admin_user_name;
    $user->title = "Administrator";
    $user->status = 'Active';
    $user->is_admin = true;
	$user->employee_status = 'Active';
    //$user->user_password = $user->encrypt_password($setup_site_admin_password);
    $user->user_hash = strtolower(md5($setup_site_admin_password));
    $user->email = '';
    $user->picture = UserDemoData::_copy_user_image($user->id);
    $user->save();

    // echo 'Creating RSS Feeds';
    //$feed = new Feed();
    //$feed->createRSSHomePage($user->id);


    // We need to change the admin user to a fixed id of 1.
    // $query = "update users set id='1' where user_name='$user->user_name'";
    // $result = $db->query($query, true, "Error updating admin user ID: ");

    $GLOBALS['log']->info("Created ".$user->table_name." table. for user $user->id");

    if( $create_default_user ){
        $default_user = new User();
        $default_user->last_name = $sugar_config['default_user_name'];
        $default_user->user_name = $sugar_config['default_user_name'];
        $default_user->status = 'Active';
        if( isset($sugar_config['default_user_is_admin']) && $sugar_config['default_user_is_admin'] ){
            $default_user->is_admin = true;
        }
        //$default_user->user_password = $default_user->encrypt_password($sugar_config['default_password']);
        $default_user->user_hash = strtolower(md5($sugar_config['default_password']));
        $default_user->save();
        //$feed->createRSSHomePage($user->id);
    }
}

function set_admin_password( $password ) {
    global $db;

    $user = new User();
    $encrypted_password = $user->encrypt_password($password);
    $user_hash = strtolower(md5($password));

    //$query = "update users set user_password='$encrypted_password', user_hash='$user_hash' where id='1'";
    $query = "update users set user_hash='$user_hash' where id='1'";

    $db->query($query);
}

function insert_default_settings(){
    global $db;
    global $setup_sugar_version;
    global $sugar_db_version;


    $db->query("INSERT INTO config (category, name, value) VALUES ('notify', 'fromaddress', 'do_not_reply@example.com')");
    $db->query("INSERT INTO config (category, name, value) VALUES ('notify', 'fromname', 'SugarCRM')");
    $db->query("INSERT INTO config (category, name, value) VALUES ('notify', 'send_by_default', '1')");
    $db->query("INSERT INTO config (category, name, value) VALUES ('notify', 'on', '1')");
    $db->query("INSERT INTO config (category, name, value) VALUES ('notify', 'send_from_assigning_user', '0')");
    /* cn: moved to OutboundEmail class
    $db->query("INSERT INTO config (category, name, value) VALUES ('mail', 'smtpserver', 'localhost')");
    $db->query("INSERT INTO config (category, name, value) VALUES ('mail', 'smtpport', '25')");
    $db->query("INSERT INTO config (category, name, value) VALUES ('mail', 'sendtype', 'smtp')");
    $db->query("INSERT INTO config (category, name, value) VALUES ('mail', 'smtpuser', '')");
    $db->query("INSERT INTO config (category, name, value) VALUES ('mail', 'smtppass', '')");
    $db->query("INSERT INTO config (category, name, value) VALUES ('mail', 'smtpauth_req', '0')");
    */
    $db->query("INSERT INTO config (category, name, value) VALUES ('info', 'sugar_version', '" . $sugar_db_version . "')");
    $db->query("INSERT INTO config (category, name, value) VALUES ('MySettings', 'tab', '')");
    $db->query("INSERT INTO config (category, name, value) VALUES ('portal', 'on', '0')");



    //insert default tracker settings
    $db->query("INSERT INTO config (category, name, value) VALUES ('tracker', 'Tracker', '1')");



    $db->query( "INSERT INTO config (category, name, value) VALUES ( 'system', 'skypeout_on', '1')" );

}








// Returns true if the given file/dir has been made writable (or is already
// writable).
function make_writable($file)
{

    $ret_val = false;
    if(is_file($file) || is_dir($file))
    {
        if(is_writable($file))
        {
            $ret_val = true;
        }
        else
        {
            $original_fileperms = fileperms($file);

            // add user writable permission
            $new_fileperms = $original_fileperms | 0x0080;
            @sugar_chmod($file, $new_fileperms);

            if(is_writable($file))
            {
                $ret_val = true;
            }
            else
            {
                // add group writable permission
                $new_fileperms = $original_fileperms | 0x0010;
                @chmod($file, $new_fileperms);

                if(is_writable($file))
                {
                    $ret_val = true;
                }
                else
                {
                    // add world writable permission
                    $new_fileperms = $original_fileperms | 0x0002;
                    @chmod($file, $new_fileperms);

                    if(is_writable($file))
                    {
                        $ret_val = true;
                    }
                }
            }
        }
    }

    return $ret_val;
}

function recursive_make_writable($start_file)
    {
    $ret_val = make_writable($start_file);

    if($ret_val && is_dir($start_file))
    {
        // PHP 4 alternative to scandir()
        $files = array();
        $dh = opendir($start_file);
        $filename = readdir($dh);
        while(!empty($filename))
        {
            if($filename != '.' && $filename != '..' && $filename != '.svn')
            {
                $files[] = $filename;
            }

            $filename = readdir($dh);
        }

        foreach($files as $file)
        {
            $ret_val = recursive_make_writable($start_file . '/' . $file);

            if(!$ret_val)
            {
                $_SESSION['unwriteable_module_files'][dirname($file)] = dirname($file);
                $fnl_ret_val = false;
                //break;
            }
        }
    }
            if(!$ret_val)
            {
                $unwriteable_directory = is_dir($start_file) ? $start_file : dirname($start_file);
                if($unwriteable_directory[0] == '.'){$unwriteable_directory = substr($unwriteable_directory,1);}
                $_SESSION['unwriteable_module_files'][$unwriteable_directory] = $unwriteable_directory;
                $_SESSION['unwriteable_module_files']['failed'] = true;
            }

    return $ret_val;
}

function recursive_is_writable($start_file)
{
    $ret_val = is_writable($start_file);

    if($ret_val && is_dir($start_file))
    {
        // PHP 4 alternative to scandir()
        $files = array();
        $dh = opendir($start_file);
        $filename = readdir($dh);
        while(!empty($filename))
        {
            if($filename != '.' && $filename != '..' && $filename != '.svn')
            {
                $files[] = $filename;
            }

            $filename = readdir($dh);
        }

        foreach($files as $file)
        {
            $ret_val = recursive_is_writable($start_file . '/' . $file);

            if(!$ret_val)
            {
                break;
            }
        }
    }

    return $ret_val;
}




function getMysqlVersion($link) {
    if($link) {
        if(isset($_SESSION['mysql_type'])){
            $version = mysqli_get_server_info($link);
        }else{
            if(is_resource($link)) {
                $version = mysql_get_server_info($link);
            }
        }
        return preg_replace('/[A-Za-z\-]/','',$version);
    }
    return 0;
}




// one place for form validation/conversion to boolean
function get_boolean_from_request( $field ){
    if( !isset($_REQUEST[$field]) ){
        return( false );
    }

    if( ($_REQUEST[$field] == 'on') || ($_REQUEST[$field] == 'yes') ){
        return(true);
    }
    else {
        return(false);
    }
}

function stripslashes_checkstrings($value){
   if(is_string($value)){
      return stripslashes($value);
   }
   return $value;
}


function print_debug_array( $name, $debug_array ){
    ksort( $debug_array );

    print( "$name vars:\n" );
    print( "(\n" );

    foreach( $debug_array as $key => $value ){
        if( stristr( $key, "password" ) ){
            $value = "WAS SET";
        }
        print( "    [$key] => $value\n" );
    }

    print( ")\n" );
}

function print_debug_comment(){
    if( !empty($_REQUEST['debug']) ){
        $_SESSION['debug'] = $_REQUEST['debug'];
    }

    if( !empty($_SESSION['debug']) && ($_SESSION['debug'] == 'true') ){
        print( "<!-- debug is on (to turn off, hit any page with 'debug=false' as a URL parameter.\n" );

        print_debug_array( "Session",   $_SESSION );
        print_debug_array( "Request",   $_REQUEST );
        print_debug_array( "Post",      $_POST );
        print_debug_array( "Get",       $_GET );

        print_r( "-->\n" );
    }
}

function validate_systemOptions() {
    global $mod_strings;
    $errors = array();
    switch( $_SESSION['setup_db_type'] ){
        case "mysql":
        case "mssql":
        case "oci8":
            break;
        default:
            $errors[] = "<span class='error'>".$mod_strings['ERR_DB_INVALID']."</span>";
            break;
    }
    return $errors;
}


function validate_dbConfig() {
    global $mod_strings;
    require_once('install/checkDBSettings.php');
    return checkDBSettings(true);

}

function validate_siteConfig($type){
    global $mod_strings;
   $errors = array();

   if($type=='a'){
       if(empty($_SESSION['setup_system_name'])){
            $errors[] = "<span class='error'>".$mod_strings['LBL_REQUIRED_SYSTEM_NAME']."</span>";
       }
       if($_SESSION['setup_site_url'] == ''){
          $errors[] = "<span class='error'>".$mod_strings['ERR_URL_BLANK']."</span>";
       }

       if($_SESSION['setup_site_admin_user_name'] == '') {
       	  $errors[] = "<span class='error'>".$mod_strings['ERR_ADMIN_USER_NAME_BLANK']."</span>";
       }

       if($_SESSION['setup_site_admin_password'] == ''){
          $errors[] = "<span class='error'>".$mod_strings['ERR_ADMIN_PASS_BLANK']."</span>";
       }

       if($_SESSION['setup_site_admin_password'] != $_SESSION['setup_site_admin_password_retype']){
          $errors[] = "<span class='error'>".$mod_strings['ERR_PASSWORD_MISMATCH']."</span>";
       }
   }else{
       if(!empty($_SESSION['setup_site_custom_session_path']) && $_SESSION['setup_site_session_path'] == ''){
          $errors[] = "<span class='error'>".$mod_strings['ERR_SESSION_PATH']."</span>";
       }

       if(!empty($_SESSION['setup_site_custom_session_path']) && $_SESSION['setup_site_session_path'] != ''){
          if(is_dir($_SESSION['setup_site_session_path'])){
             if(!is_writable($_SESSION['setup_site_session_path'])){
                $errors[] = "<span class='error'>".$mod_strings['ERR_SESSION_DIRECTORY']."</span>";
             }
          }
          else {
             $errors[] = "<span class='error'>".$mod_strings['ERR_SESSION_DIRECTORY_NOT_EXISTS']."</span>";
          }
       }

       if(!empty($_SESSION['setup_site_custom_log_dir']) && $_SESSION['setup_site_log_dir'] == ''){
          $errors[] = "<span class='error'>".$mod_strings['ERR_LOG_DIRECTORY_NOT_EXISTS']."</span>";
       }

       if(!empty($_SESSION['setup_site_custom_log_dir']) && $_SESSION['setup_site_log_dir'] != ''){
          if(is_dir($_SESSION['setup_site_log_dir'])){
             if(!is_writable($_SESSION['setup_site_log_dir'])) {
                $errors[] = "<span class='error'>".$mod_strings['ERR_LOG_DIRECTORY_NOT_WRITABLE']."</span>";
             }
          }
          else {
             $errors[] = "<span class='error'>".$mod_strings['ERR_LOG_DIRECTORY_NOT_EXISTS']."</span>";
          }
       }

       if(!empty($_SESSION['setup_site_specify_guid']) && $_SESSION['setup_site_guid'] == ''){
          $errors[] = "<span class='error'>".$mod_strings['ERR_SITE_GUID']."</span>";
       }
   }

   return $errors;
}


function pullSilentInstallVarsIntoSession() {
    global $mod_strings;
    global $sugar_config;


    if( file_exists('config_si.php') ){
        require_once('config_si.php');
    }
    else if( empty($sugar_config_si) ){
        die( $mod_strings['ERR_SI_NO_CONFIG'] );
    }

    $config_subset = array (
        'setup_site_url'                => isset($sugar_config['site_url']) ? $sugar_config['site_url'] : '',
        'setup_db_host_name'            => isset($sugar_config['dbconfig']['db_host_name']) ? $sugar_config['dbconfig']['db_host_name'] : '',
        'setup_db_sugarsales_user'      => isset($sugar_config['dbconfig']['db_user_name']) ? $sugar_config['dbconfig']['db_user_name'] : '',
        'setup_db_sugarsales_password'  => isset($sugar_config['dbconfig']['db_password']) ? $sugar_config['dbconfig']['db_password'] : '',
        'setup_db_database_name'        => isset($sugar_config['dbconfig']['db_name']) ? $sugar_config['dbconfig']['db_name'] : '',
        'setup_db_type'                 => isset($sugar_config['dbconfig']['db_type']) ? $sugar_config['dbconfig']['db_type'] : '',
    );
    // third array of values derived from above values
    $derived = array (
        'setup_site_admin_password_retype'      => $sugar_config_si['setup_site_admin_password'],
        'setup_db_sugarsales_password_retype'   => $config_subset['setup_db_sugarsales_password'],
    );

    $needles = array('setup_license_key_users','setup_license_key_expire_date','setup_license_key', 'setup_num_lic_oc',
                     'default_currency_iso4217', 'default_currency_name', 'default_currency_significant_digits',
                     'default_currency_symbol',  'default_date_format', 'default_time_format', 'default_decimal_seperator',
                     'default_export_charset', 'default_language', 'default_locale_name_format', 'default_number_grouping_seperator',
                     'export_delimiter');
    copyFromArray($sugar_config_si, $needles, $derived);
    $all_config_vars = array_merge( $config_subset, $sugar_config_si, $derived );

    // bug 16860 tyoung -  trim leading and trailing whitespace from license_key
    if (isset($all_config_vars['setup_license_key'])) {
    	$all_config_vars['setup_license_key'] = trim($all_config_vars['setup_license_key']);
    }

    foreach( $all_config_vars as $key => $value ){
        $_SESSION[$key] = $value;
    }
}

/**
 * given an array it will check to determine if the key exists in the array, if so
 * it will addd to the return array
 *
 * @param intput_array haystack to check
 * @param needles list of needles to search for
 * @param output_array the array to add the keys to
 */
function copyFromArray($input_array, $needles, $output_array){
    foreach($needles as $needle){
         if(isset($input_array[$needle])){
            $output_array[$needle]  = $input_array[$needle];
         }
    }
}



/**
 * handles language pack uploads - code based off of upload_file->final_move()
 * puts it into the cache/upload dir to be handed off to langPackUnpack();
 *
 * @param object file UploadFile object
 * @return bool true if successful
 */
function langPackFinalMove($file) {
    global $sugar_config;
    //."upgrades/langpack/"
    $destination = $sugar_config['upload_dir'].$file->stored_file_name;
    if(!move_uploaded_file($_FILES[$file->field_name]['tmp_name'], $destination)) {
        die ("ERROR: can't move_uploaded_file to $destination. You should try making the directory writable by the webserver");
    }
    return true;
}

function getLicenseDisplay($type, $manifest, $zipFile, $next_step, $license_file, $clean_file) {
    return PackageManagerDisplay::getLicenseDisplay($license_file, 'install.php', $next_step, $zipFile, $type, $manifest, $clean_file);
}


/**
 * creates the remove/delete form for langpack page
 * @param string type commit/remove
 * @param string manifest path to manifest file
 * @param string zipFile path to uploaded zip file
 * @param int nextstep current step
 * @return string ret <form> for this package
 */
function getPackButton($type, $manifest, $zipFile, $next_step, $uninstallable='Yes', $showButtons=true) {
    global $mod_strings;

    $button = $mod_strings['LBL_LANG_BUTTON_COMMIT'];
    if($type == 'remove') {
        $button = $mod_strings['LBL_LANG_BUTTON_REMOVE'];
    } elseif($type == 'uninstall') {
        $button = $mod_strings['LBL_LANG_BUTTON_UNINSTALL'];
    }

    $disabled = ($uninstallable == 'Yes') ? false : true;

    $ret = "<form name='delete{$zipFile}' action='install.php' method='POST'>
                <input type='hidden' name='current_step' value='{$next_step}'>
                <input type='hidden' name='goto' value='{$mod_strings['LBL_CHECKSYS_RECHECK']}'>
                <input type='hidden' name='languagePackAction' value='{$type}'>
                <input type='hidden' name='manifest' value='".urlencode($manifest)."'>
                <input type='hidden' name='zipFile' value='".urlencode($zipFile)."'>
                <input type='hidden' name='install_type' value='custom'>";
    if(!$disabled && $showButtons) {
        $ret .= "<input type='submit' value='{$button}' class='button'>";
    }
    $ret .= "</form>";
    return $ret;
}

/**
 * finds all installed languages and returns an array with the names
 * @return array langs array of installed languages
 */
function getInstalledLanguages() {
    $langDir = 'include/language/';
    $dh = opendir($langDir);

    $langs = array();
    while($file = readdir($dh)) {
        if(substr($file, -3) == 'php') {

        }
    }
}



/**
 * searches upgrade dir for lang pack files.
 *
 * @return string HTML of available lang packs
 */
function getLangPacks($display_commit = true, $types = array('langpack'), $notice_text = '') {
    global $mod_strings;
    global $next_step;
    global $base_upgrade_dir;

    if(empty($notice_text)){
        $notice_text =  $mod_strings['LBL_LANG_PACK_READY'];
    }
	$ret = "<tr><td colspan=7 align=left>{$notice_text}</td></tr>";
    //$ret .="<table width='100%' cellpadding='0' cellspacing='0' border='0'>";
    $ret .= "<tr>
                <td width='20%' ><b>{$mod_strings['LBL_ML_NAME']}</b></td>
                <td width='15%' ><b>{$mod_strings['LBL_ML_VERSION']}</b></td>
                <td width='15%' ><b>{$mod_strings['LBL_ML_PUBLISHED']}</b></td>
                <td width='15%' ><b>{$mod_strings['LBL_ML_UNINSTALLABLE']}</b></td>
                <td width='20%' ><b>{$mod_strings['LBL_ML_DESCRIPTION']}</b></td>
                <td width='7%' ></td>
                <td width='1%' ></td>
                <td width='7%' ></td>
            </tr>\n";
    $files = array();

    // duh, new installs won't have the upgrade folders
   if(!is_dir(getcwd()."/cache/upload/upgrades")) {
	    mkdir_recursive( "$base_upgrade_dir");
	}
	$subdirs = array('full', 'langpack', 'module', 'patch', 'theme', 'temp');
	foreach( $subdirs as $subdir ){
		mkdir_recursive( "$base_upgrade_dir/$subdir" );
	}

    $files = findAllFiles(getcwd()."/cache/upload/upgrades", $files);
    $hidden_input = '';
    unset($_SESSION['hidden_input']);

    foreach($files as $file) {
        if(!preg_match("#.*\.zip\$#", $file)) {
            continue;
        }

        // skip installed lang packs
        if(isset($_SESSION['INSTALLED_LANG_PACKS']) && in_array($file, $_SESSION['INSTALLED_LANG_PACKS'])) {
            continue;
        }

        // handle manifest.php
        $target_manifest = remove_file_extension( $file ) . '-manifest.php';
        $license_file = remove_file_extension( $file ) . '-license.txt';
        include($target_manifest);

        if(!empty($types)){
            if(!in_array(strtolower($manifest['type']), $types))
                continue;
        }

        $md5_matches = array();
        if($manifest['type'] == 'module'){
            $uh = new UpgradeHistory();
            $upgrade_content = clean_path($file);
            $the_base = basename($upgrade_content);
            $the_md5 = md5_file($upgrade_content);
            $md5_matches = $uh->findByMd5($the_md5);
        }

        if($manifest['type']!= 'module' || 0 == sizeof($md5_matches)){
            $name = empty($manifest['name']) ? $file : $manifest['name'];
            $version = empty($manifest['version']) ? '' : $manifest['version'];
            $published_date = empty($manifest['published_date']) ? '' : $manifest['published_date'];
            $icon = '';
            $description = empty($manifest['description']) ? 'None' : $manifest['description'];
            $uninstallable = empty($manifest['is_uninstallable']) ? 'No' : 'Yes';
            $manifest_type = $manifest['type'];
            $commitPackage = getPackButton('commit', $target_manifest, $file, $next_step);
            $deletePackage = getPackButton('remove', $target_manifest, $file, $next_step);
            //$ret .="<table width='100%' cellpadding='0' cellspacing='0' border='0'>";
            $ret .= "<tr>";
            $ret .= "<td width='20%' >".$name."</td>";
            $ret .= "<td width='15%' >".$version."</td>";
            $ret .= "<td width='15%' >".$published_date."</td>";
            $ret .= "<td width='15%' >".$uninstallable."</td>";
            $ret .= "<td width='20%' >".$description."</td>";

            if($display_commit)
                $ret .= "<td width='7%'>{$commitPackage}</td>";
            $ret .= "<td width='1%'></td>";
            $ret .= "<td width='7%'>{$deletePackage}</td>";
            $ret .= "</td></tr>";

            $clean_field_name = "accept_lic_".str_replace('.', '_', urlencode(basename($file)));

            if(is_file($license_file)){
                //rrs
                $ret .= "<tr><td colspan=6>";
                $ret .= getLicenseDisplay('commit', $target_manifest, $file, $next_step, $license_file, $clean_field_name);
                $ret .= "</td></tr>";
                $hidden_input .= "<input type='hidden' name='$clean_field_name' id='$clean_field_name' value='no'>";
            }else{
                $hidden_input .= "<input type='hidden' name='$clean_field_name' id='$clean_field_name' value='yes'>";
            }
        }//fi
    }//rof
    $_SESSION['hidden_input'] = $hidden_input;

    if(count($files) > 0 ) {
        $ret .= "</tr><td colspan=7>";
        $ret .= "<form name='commit' action='install.php' method='POST'>
                    <input type='hidden' name='current_step' value='{$next_step}'>
                    <input type='hidden' name='goto' value='Re-check'>
                    <input type='hidden' name='languagePackAction' value='commit'>
                    <input type='hidden' name='install_type' value='custom'>
                 </form>
                ";
        $ret .= "</td></tr>";
    } else {
        $ret .= "</tr><td colspan=7><i>{$mod_strings['LBL_LANG_NO_PACKS']}</i></td></tr>";
    }
    return $ret;
}

if ( !function_exists('extractFile') ) {
function extractFile( $zip_file, $file_in_zip, $base_tmp_upgrade_dir){
    $my_zip_dir = mk_temp_dir( $base_tmp_upgrade_dir );
    unzip_file( $zip_file, $file_in_zip, $my_zip_dir );
    return( "$my_zip_dir/$file_in_zip" );
}
}

if ( !function_exists('extractManifest') ) {
function extractManifest( $zip_file,$base_tmp_upgrade_dir ) {
    return( extractFile( $zip_file, "manifest.php",$base_tmp_upgrade_dir ) );
}
}

if ( !function_exists('unlinkTempFiles') ) {
function unlinkTempFiles($manifest, $zipFile) {
    global $sugar_config;

    @unlink($_FILES['language_pack']['tmp_name']);
    if(!empty($manifest))
        @unlink($manifest);
    if(!empty($zipFile)) {
        //@unlink($zipFile);
        $tmpZipFile = substr($zipFile, strpos($zipFile, 'langpack/') + 9, strlen($zipFile));
        @unlink(getcwd()."/".$sugar_config['upload_dir'].$tmpZipFile);
    }

    rmdir_recursive(getcwd()."/".$sugar_config['upload_dir']."upgrades/temp");
    sugar_mkdir(getcwd()."/".$sugar_config['upload_dir']."upgrades/temp");
}
}

function langPackUnpack($unpack_type = 'langpack', $full_file = '') {
    global $sugar_config;
    global $base_upgrade_dir;
    global $base_tmp_upgrade_dir;

    $manifest = array();
    if(!empty($full_file)){
        $tempFile = $full_file;
        $base_filename = urldecode($tempFile);
        $base_filename = preg_replace( "#\\\\#", "/", $base_filename );
        $base_filename = basename( $base_filename );
    }else{
        $tempFile = getcwd().'/'.$sugar_config['upload_dir'].$_FILES['language_pack']['name'];
        $base_filename = $_FILES['language_pack']['name'];
    }
    $manifest_file = extractManifest($tempFile, $base_tmp_upgrade_dir);
    if($unpack_type == 'module')
        $license_file = extractFile($tempFile, 'LICENSE.txt', $base_tmp_upgrade_dir);

    if(is_file($manifest_file)) {

        if($unpack_type == 'module' && is_file($license_file)){
            copy($license_file, getcwd().'/'.$sugar_config['upload_dir'].'upgrades/'.$unpack_type.'/'.remove_file_extension($base_filename)."-license.txt");
        }
        copy($manifest_file, getcwd().'/'.$sugar_config['upload_dir'].'upgrades/'.$unpack_type.'/'.remove_file_extension($base_filename)."-manifest.php");

        require_once( $manifest_file );
        validate_manifest( $manifest );
        $upgrade_zip_type = $manifest['type'];

        // exclude the bad permutations
        /*if($upgrade_zip_type != "langpack") {
            unlinkTempFiles($manifest_file, $tempFile);
            die( "You can only upload module packs, theme packs, and language packs on this page." );
        }*/

        //$base_filename = urldecode( $_REQUEST['language_pack_escaped'] );
        $base_filename = preg_replace( "#\\\\#", "/", $base_filename );
        $base_filename = basename( $base_filename );

        mkdir_recursive( "$base_upgrade_dir/$upgrade_zip_type" );
        $target_path = getcwd()."/$base_upgrade_dir/$upgrade_zip_type/$base_filename";
        $target_manifest = remove_file_extension( $target_path ) . "-manifest.php";

        if( isset($manifest['icon']) && $manifest['icon'] != "" ) {
            $icon_location = extractFile( $tempFile, $manifest['icon'], $base_tmp_upgrade_dir );
            $path_parts = pathinfo( $icon_location );
            copy( $icon_location, remove_file_extension( $target_path ) . "-icon." . $path_parts['extension'] );
        }

        // move file from cache/upload to cache/upload/langpack
        if( copy( $tempFile , $target_path ) ){
            copy( $manifest_file, $target_manifest );
            unlink($tempFile); // remove tempFile
            return "The file $base_filename has been uploaded.<br>\n";
        } else {
            unlinkTempFiles($manifest_file, $tempFile);
            return "There was an error uploading the file, please try again!<br>\n";
        }
    } else {
        die("The zip file is missing a manifest.php file.  Cannot proceed.");
    }
    unlinkTempFiles($manifest_file, '');
}

if ( !function_exists('validate_manifest') ) {
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
    if( getInstallType( "/$type/" ) == "" ){
        die($mod_strings['ERROR_PACKAGE_TYPE']. ": '" . $type . "'." );
    }

    return true; // making this a bit more relaxed since we updated the language extraction and merge capabilities

    /*
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
    }*/
}
}

if ( !function_exists('getInstallType') ) {
function getInstallType( $type_string ){
    // detect file type
    $subdirs = array('full', 'langpack', 'module', 'patch', 'theme', 'temp');
    foreach( $subdirs as $subdir ){
        if( preg_match( "#/$subdir/#", $type_string ) ){
            return( $subdir );
        }
    }
    // return empty if no match
    return( "" );
}
}



//mysqli connector has a separate parameter for port.. We need to separate it out from the host name
function getHostPortFromString($hostname=''){

    $pos=strpos($hostname,':');
    if($pos === false){
        //no need to process as string is empty or does not contain ':' delimiter
        return '';
    }

    $hostArr = explode(':', $hostname);

    return $hostArr;

}

function getLicenseContents($filename)
{
	$license_file = '';
    if(file_exists($filename) && filesize($filename) >0){
	    $license_file = file_get_contents($filename);
    }
    return $license_file;
}








///////////////////////////////////////////////////////////////////////////////
////    FROM POPULATE SEED DATA
$seed = array(
    'qa',       'dev',          'beans',
    'info',     'sales',        'support',
    'kid',      'the',          'section',
    'sugar',    'hr',           'im',
    'kid',      'vegan',        'phone',
);
$tlds = array(
    ".com", ".org", ".net", ".tv", ".cn", ".co.jp", ".us",
    ".edu", ".tw", ".de", ".it", ".co.uk", ".info", ".biz",
    ".name",
);

/**
 * creates a random, DNS-clean webaddress
 */
function createWebAddress() {
    global $seed;
    global $tlds;

    $one = $seed[rand(0, count($seed)-1)];
    $two = $seed[rand(0, count($seed)-1)];
    $tld = $tlds[rand(0, count($tlds)-1)];

    return "www.{$one}{$two}{$tld}";
}

/**
 * creates a random email address
 * @return string
 */
function createEmailAddress() {
    global $seed;
    global $tlds;

    $part[0] = $seed[rand(0, count($seed)-1)];
    $part[1] = $seed[rand(0, count($seed)-1)];
    $part[2] = $seed[rand(0, count($seed)-1)];

    $tld = $tlds[rand(0, count($tlds)-1)];

    $len = rand(1,3);

    $ret = '';
    for($i=0; $i<$len; $i++) {
        $ret .= (empty($ret)) ? '' : '.';
        $ret .= $part[$i];
    }

    if($len == 1) {
        $ret .= rand(10, 99);
    }

    return "{$ret}@example{$tld}";
}


function add_digits($quantity, &$string, $min = 0, $max = 9) {
    for($i=0; $i < $quantity; $i++) {
        $string .= mt_rand($min,$max);
    }
}

function create_phone_number() {
    $phone = "(";
    add_digits(3, $phone);
    $phone .= ") ";
    add_digits(3, $phone);
    $phone .= "-";
    add_digits(4, $phone);

    return $phone;
}

function create_date($year=null,$mnth=null,$day=null)
{
    global $timedate;
    $now = $timedate->getNow();
    if ($day==null) $day=$now->day+mt_rand(0,365);
    return $timedate->asDbDate($now->get_day_begin($day, $mnth, $year));
}

function create_current_date_time()
{
    global $timedate;
    return $timedate->nowDb();
}

function create_time($hr=null,$min=null,$sec=null)
{
    global $timedate;
    $date = TimeDate::fromTimestamp(0);
    if ($hr==null) $hr=mt_rand(6,19);
    if ($min==null) $min=(mt_rand(0,3)*15);
    if ($sec==null) $sec=0;
    return $timedate->asDbTime($date->setDate(2007, 10, 7)->setTime($hr, $min, $sec));
}

function create_past_date()
{
    global $timedate;
    $now = $timedate->getNow(true);
    $day=$now->day-mt_rand(1, 365);
    return $timedate->asUserDate($now->get_day_begin($day));
}

/**
*   This method will look for a file modules_post_install.php in the root directory and based on the
*   contents of this file, it will silently install any modules as specified in this array.
*/
function post_install_modules(){
    if(is_file('modules_post_install.php')){
        global $current_user, $mod_strings;
        $current_user = new User();
        $current_user->is_admin = '1';
        require_once('ModuleInstall/PackageManager/PackageManager.php');
        require_once('modules_post_install.php');
        //we now have the $modules_to_install array in memory
        $pm = new PackageManager();
        $old_mod_strings = $mod_strings;
        foreach($modules_to_install as $module_to_install){
            if(is_file($module_to_install)){
                $pm->performSetup($module_to_install, 'module', false);
                $file_to_install = 'cache/upload/upgrades/module/'.basename($module_to_install);
                $_REQUEST['install_file'] = $file_to_install;
                $pm->performInstall($file_to_install);
            }
        }
        $mod_strings = $old_mod_strings;
    }
}

function get_help_button_url(){
    $help_url = 'http://www.sugarcrm.com/docs/Administration_Guides/CommunityEdition_Admin_Guide_5.0/toc.html';

    return $help_url;
}

function create_db_user_creds($numChars=10){
$numChars = 7; // number of chars in the password
//chars to select from
$charBKT = "abcdefghijklmnpqrstuvwxyz123456789ABCDEFGHIJKLMNPQRSTUVWXYZ";
// seed the random number generator
srand((double)microtime()*1000000);
$password="";
for ($i=0;$i<$numChars;$i++)  // loop and create password
            $password = $password . substr ($charBKT, rand() % strlen($charBKT), 1);

return $password;

}

function addDefaultRoles($defaultRoles = array()) {
	global $db;


	foreach($defaultRoles as $roleName=>$role){
	    $ACLField = new ACLField();
        $role1= new ACLRole();
        $role1->name = $roleName;
        $role1->description = $roleName." Role";
        $role1_id=$role1->save();
        foreach($role as $category=>$actions){
            foreach($actions as $name=>$access_override){
                if($name=='fields'){
                    foreach($access_override as $field_id=>$access){
                        $ACLField->setAccessControl($category, $role1_id, $field_id, $access);
                    }
                }else{
                    $queryACL="SELECT id FROM acl_actions where category='$category' and name='$name'";
                    $result = $db->query($queryACL);
                    $actionId=$db->fetchByAssoc($result);
                    if (isset($actionId['id']) && !empty($actionId['id'])){
                        $role1->setAction($role1_id, $actionId['id'], $access_override);
                    }
                }
            }
        }
	}
}

/**
 * Fully enable SugarFeeds, enabling the user feed and all available modules that have SugarFeed data.
 */
function enableSugarFeeds()
{
    $admin = new Administration();
    $admin->saveSetting('sugarfeed','enabled','1');

    foreach ( SugarFeed::getAllFeedModules() as $module )
        SugarFeed::activateModuleFeed($module);

    check_logic_hook_file('Users','after_login', array(1, 'SugarFeed old feed entry remover', 'modules/SugarFeed/SugarFeedFlush.php', 'SugarFeedFlush', 'flushStaleEntries'));
}

/**
 * Enable the InsideView connector for the four default modules.
 */
function enableInsideViewConnector()
{
    // Load up the existing mapping and hand it to the InsideView connector to have it setup the correct logic hooks
    $mapFile = 'modules/Connectors/connectors/sources/ext/rest/insideview/mapping.php';
    if ( file_exists('custom/'.$mapFile) ) {
        require('custom/'.$mapFile);
    } else {
        require($mapFile);
    }
 
    require_once('modules/Connectors/connectors/sources/ext/rest/insideview/insideview.php');
    $source = new ext_rest_insideview();

    // $mapping is brought in from the mapping.php file above
    $source->saveMappingHook($mapping);
}