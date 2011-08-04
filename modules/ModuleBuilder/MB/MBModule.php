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

define ( 'MB_TEMPLATES', 'include/SugarObjects/templates' ) ;
define ( 'MB_IMPLEMENTS', 'include/SugarObjects/implements' ) ;
require_once ('modules/ModuleBuilder/MB/MBVardefs.php') ;
require_once ('modules/ModuleBuilder/MB/MBRelationship.php') ;
require_once ('modules/ModuleBuilder/MB/MBLanguage.php') ;

class MBModule
{
    public $name = '' ;
    public $config = array (
    'assignable' => 1 , 'acl' => 1 , 'has_tab' => 1 , 'studio' => 1 , 'audit' => 1 ) ;
    public $mbpublicdefs ;
    public $errors = array ( ) ;
    public $path = '' ;
    public $implementable = array (
    'has_tab' => 'Navigation Tab' ) ;
    public $always_implement = array ( 'assignable' => 'Assignable' , 'acl' => 'Access Controls' , 'studio' => 'Studio Support' , 'audit' => 'Audit Table' ) ;
    public $iTemplate = array (
    'assignable' ) ;

    public $config_md5 = null ;

    function __construct ($name , $path , $package , $package_key)
    {
        global $mod_strings;
    	$this->config [ 'templates' ] = array ( 'basic' => 1 ) ;

        $this->name = MBModule::getDBName ( $name ) ;
        $this->key_name = $package_key . '_' . $name ;
        $this->package = $package ;
        $this->package_key = $package_key ;
        $this->package_path = $path ;

        $this->implementable = array (
        'has_tab' => !empty($mod_strings[ 'LBL_NAV_TAB' ]) ? $mod_strings[ 'LBL_NAV_TAB' ] : false) ;
        $this->path = $this->getModuleDir () ;
        //		$this->mbrelationship = new MBRelationship($this->name, $this->path, $this->key_name);
        $this->relationships = new UndeployedRelationships ( $this->path ) ;
        $this->mbvardefs = new MBVardefs ( $this->name, $this->path, $this->key_name ) ;

        $this->load () ;
    }

    function getDBName ($name)
    {
        return preg_replace ( "/[^\w]+/", "_", $name ) ;
    }

    function getModuleName()
    {
        return $this->name;
    }

    function getPackageName()
    {
        return $this->package;
    }

    function getRelationships()
    {
        return $this->relationships;
    }

    /**
     * Loads the module based on the module name
     *
     */
    function load ()
    {
        if (file_exists ( $this->path . '/config.php' ))
        {
            include ($this->path . '/config.php') ;
            $this->config = $config ;
        }
        $label = (! empty ( $this->config [ 'label' ] )) ? $this->config [ 'label' ] : $this->name ;
        $this->mblanguage = new MBLanguage ( $this->name, $this->path, $label, $this->key_name ) ;
        foreach ( $this->iTemplate as $temp )
        {
            if (! empty ( $this->config [ $temp ] ))
            {
                $this->mbvardefs->iTemplates [ $temp ] = 1 ;
                $this->mblanguage->iTemplates [ $temp ] = $temp ;
            }
        }
        $this->mbvardefs->templates = $this->config [ 'templates' ] ;
        $this->mblanguage->templates = $this->config [ 'templates' ] ;
        $this->mbvardefs->load () ;
        $this->mblanguage->load () ;

    }

    function addTemplate ($template)
    {
        $this->config [ 'templates' ] [ $template ] = 1 ;
    }

    function getModuleDir ()
    {
        return $this->package_path . '/modules/' . $this->name ;
    }

    function removeTemplate ($template)
    {
        unset ( $this->config [ 'templates' ] [ $template ] ) ;
    }

    function getVardefs ($by_group = false)
    {
        $this->mbvardefs->updateVardefs ( $by_group ) ;
        return $this->mbvardefs->getVardefs () ;
    }

    function addField ($vardef)
    {
        $this->mbvardefs->addFieldVardef ( $vardef ) ;
    }

    function addFieldObject ($field)
    {
        $vardef = $field->get_field_def () ;
		$this->mbvardefs->mergeVardefs();
		$existingVardefs = $this->mbvardefs->getVardefs () ;
		//Merge with the existing vardef if it already exists
		if(!empty($existingVardefs['fields'][$vardef['name']])){
			$vardef = array_merge( $existingVardefs['fields'][$vardef['name']], $vardef);
		}
        if (! empty ( $vardef [ 'source' ] ) && $vardef [ 'source' ] == 'custom_fields')
            unset ( $vardef [ 'source' ] ) ;

	    $this->mbvardefs->load();
        $this->addField ( $vardef ) ;
        $this->mbvardefs->save();
    }

    function deleteField ($name)
    {
        $this->mbvardefs->deleteField ( $name ) ;
    }

    function fieldExists ($name = '' , $type = '')
    {
        $vardefs = $this->mbvardefs->getVardef () ;
        if (! empty ( $vardefs ))
        {
            if (empty ( $type ) && empty ( $name ))
                return false ; else if (empty ( $type ))
                return ! empty ( $vardefs [ 'fields' ] [ $name ] ) ; else if (empty ( $name ))
            {
                foreach ( $vardefs [ 'fields' ] as $def )
                {
                    if ($def [ 'type' ] == $type)
                        return true ;
                }
                return false ;
            } else
                return (! empty ( $vardefs [ 'fields' ] [ $name ] ) && ($vardefs [ 'fields' ] [ $name ] [ 'type' ] == $type)) ;
        } else
        {
            return false ;
        }
    }

    function getModStrings ($language = 'en_us')
    {
        return $this->mblanguage->getModStrings ( $language ) ;
    }

    function setModStrings ($language = 'en_us' , $mod_strings)
    {
        $language .= '.lang.php' ;
        $this->mblanguage->strings [ $language ] = $mod_strings ;
    }

	function setLabel ($language = 'en_us' , $key , $value)
    {
    	$language .= '.lang.php' ;
        $this->mblanguage->strings [ $language ] [ $key ] = $value ;
        //Ensure this key exists in all languages
        foreach ($this->mblanguage->strings as $lang => $values) {
        	if (empty($values[$key])) {
        		$this->mblanguage->strings[$lang][$key] = $value;
        	}
        }
    }

    function deleteLabel ($language = 'en_us' , $key)
    {
   		foreach ($this->mblanguage->strings as $lang => $values) {
        	if (!empty($values[$key])) {
        		unset($this->mblanguage->strings[$lang][$key]);
        	}
        }
    }

    /**
     * Required for an MB module to work with Dynamic fields
     */
	function addLabel ( $displayLabel)
    {
        $this->setLabel('en_us', $this->getDBName($displayLabel, false), translate($displayLabel));
        $this->save();
    }

    function getLabel ($language = 'en_us' , $key)
    {
        $language .= '.lang.php' ;
        if (empty ( $this->mblanguage->strings [ $language ] [ $key ] ))
        {

            return '' ;
        }
        return $this->mblanguage->strings [ $language ] [ $key ] ;

    }

    function getAppListStrings ($language = 'en_us')
    {
        return $this->mblanguage->getAppListStrings ( $language ) ;
    }

    function setAppListStrings ($language = 'en_us' , $app_list_strings)
    {
        $language .= '.lang.php' ;
        $this->mblanguage->appListStrings [ $language ] = $app_list_strings ;
    }

    function setDropDown ($language = 'en_us' , $key , $value)
    {
        $language .= '.lang.php' ;
        $this->mblanguage->appListStrings [ $language ] [ $key ] = $value ;
    }

    function deleteDropDown ($language = 'en_us' , $key)
    {
        $language .= '.lang.php' ;
        unset ( $this->mblanguage->appListStrings [ $language ] [ $key ] ) ;
    }

    function save ()
    {
        $this->path = $this->getModuleDir () ;
        if (mkdir_recursive ( $this->path ))
        {

            $this->setConfigMD5 () ;
            $old_config_md5 = $this->config_md5 ;
            $this->saveConfig () ;
            $this->getVardefs () ;
            $this->mbvardefs->save ( $this->key_name ) ;
            //           $this->mbrelationship->save ( $this->key_name ) ;
            $this->relationships->save () ;
            $this->copyMetaData () ;
            $this->copyDashlet () ;
            $this->copyViews() ;
            if (0 != strcmp ( $old_config_md5, $this->config_md5 ))
            {
                $this->mblanguage->reload () ;
            }
            $this->mblanguage->label = $this->config [ 'label' ] ;
            //pass in the key_name incase it has changed mblanguage will check if it is different and handle it accordingly
            $this->mblanguage->save ( $this->key_name ) ;

            if (! file_exists ( $this->package_path . "/icons/icon_" . ucfirst ( $this->key_name ) . ".gif" ))
            {
                $this->createIcon () ;
            }
            $this->errors = array_merge ( $this->errors, $this->mbvardefs->errors ) ;

        }
    }

    function copyDashlet() {
    	$templates = array_reverse ( $this->config [ 'templates' ], true ) ;
        foreach ( $templates as $template => $a )
        {
            if (file_exists ( MB_TEMPLATES . '/' . $template . '/Dashlets/Dashlet' ))
            {

            	$this->copyMetaRecursive ( MB_TEMPLATES . '/' . $template . '/Dashlets/Dashlet', $this->path . '/Dashlets/' . $this->key_name . 'Dashlet/' ) ;

            }
        }
    }

    function copyViews() {
        $templates = array_reverse ( $this->config [ 'templates' ], true ) ;
        foreach ( $templates as $template => $a )
        {
            if (file_exists ( MB_TEMPLATES . '/' . $template . '/views' ))
            {
                $this->copyMetaRecursive ( MB_TEMPLATES . '/' . $template . '/views', $this->path . '/views/' ) ;
            }
        }
    }

    function copyCustomFiles ( $from , $to )
    {
    	$d = dir ( $from ) ;
        while ( $filename = $d->read () )
        {
        	if (substr ( $filename, 0, 1 ) == '.')
            	continue ;
           	if ( $filename != 'metadata' && $filename != 'Dashlets' && $filename != 'relationships' && $filename != 'language' && $filename != 'config.php' && $filename != 'relationships.php' && $filename != 'vardefs.php' )
           		copy_recursive ( "$from/$filename" , "$to/$filename" ) ;
        }
    }

    function copyMetaData ()
    {
        $templates = array_reverse ( $this->config [ 'templates' ], true ) ;
        foreach ( $templates as $template => $a )
        {
            if (file_exists ( MB_TEMPLATES . '/' . $template . '/metadata' ))
            {
                $this->copyMetaRecursive ( MB_TEMPLATES . '/' . $template . '/metadata', $this->path . '/metadata/' ) ;

            }
        }
    }

    function copyMetaRecursive ($from , $to , $overwrite = false)
    {
        if (! file_exists ( $from ))
            return ;
        if (is_dir ( $from ))
        {
            $findArray = array ( '<module_name>' , '<_module_name>' , '<MODULE_NAME>' , '<object_name>' , '<_object_name>' , '<OBJECT_NAME>' );
            $replaceArray = array ( $this->key_name , strtolower ( $this->key_name ) , strtoupper ( $this->key_name ) ,
            						$this->key_name , strtolower ( $this->key_name ) , strtoupper ( $this->key_name ) );
        	mkdir_recursive ( $to ) ;
            $d = dir ( $from ) ;
            while ( $e = $d->read () )
            {
                if (substr ( $e, 0, 1 ) == '.')
                    continue ;
                $nfrom = $from . '/' . $e ;
                $nto = $to . '/' . str_replace ( 'm-n-', $this->key_name, $e ) ;
                if (is_dir ( $nfrom ))
                {
                    $this->copyMetaRecursive ( $nfrom, $nto, $overwrite ) ;
                } else
                {
                    if ($overwrite || ! file_exists ( $nto ))
                    {
                        $contents = file_get_contents ( $nfrom ) ;
                        $contents = str_replace ( $findArray, $replaceArray, $contents ) ;
                        $fw = sugar_fopen ( $nto, 'w' ) ;
                        fwrite ( $fw, $contents ) ;
                        fclose ( $fw ) ;
                    }
                }
            }

        }
    }

    function saveConfig ()
    {
        $header = file_get_contents ( 'modules/ModuleBuilder/MB/header.php' ) ;
        if (! write_array_to_file ( 'config', $this->config, $this->path . '/config.php', 'w', $header ))
        {
            $this->errors [] = 'Could not save config to ' . $this->path . '/config.php' ;
        }
        $this->setConfigMD5 () ;
    }

    function setConfigMD5 ()
    {
        if (file_exists ( $this->path . '/config.php' ))
            $this->config_md5 = md5 ( base64_encode ( serialize ( $this->config ) ) ) ;
    }

    function build ($basepath)
    {
        $path = $basepath . '/modules/' . $this->key_name ;
        if (mkdir_recursive ( $path ))
        {
            $this->createClasses ( $path ) ;
            if( $this->config['importable'] || in_array ( 'person', array_keys($this->config[ 'templates' ]) ) )
                $this->createMenu ( $path ) ;
            $this->copyCustomFiles ( $this->path , $path ) ;
            $this->copyMetaRecursive ( $this->path . '/metadata/', $path . '/metadata/', true ) ;
            $this->copyMetaRecursive ( $this->path . '/Dashlets/' . $this->key_name . 'Dashlet/',
            						   $path . '/Dashlets/' . $this->key_name . 'Dashlet/', true ) ;
            $this->relationships->build ( $basepath ) ;
            $this->mblanguage->build ( $path ) ;
        }
    }

    function createClasses ($path)
    {
        $class = array ( ) ;
        $class [ 'name' ] = $this->key_name ;
        $class [ 'table_name' ] = strtolower ( $class [ 'name' ] ) ;
        $class [ 'extends' ] = 'Basic' ;
        $class [ 'requires' ] [] = MB_TEMPLATES . '/basic/Basic.php' ;
        $class [ 'requires' ] = array ( ) ;
        $class [ 'audited' ] = (! empty ( $this->config [ 'audit' ] )) ? 'true' : 'false' ;
        $class [ 'acl' ] = ! empty ( $this->config [ 'acl' ] ) ;
        $class [ 'templates' ] = "'basic'" ;
        foreach ( $this->iTemplate as $template )
        {
            if (! empty ( $this->config [ $template ] ))
            {
                $class [ 'templates' ] .= ",'$template'" ;
            }
        }
        foreach ( $this->config [ 'templates' ] as $template => $a )
        {
            if ($template == 'basic')
                continue ;
            $class [ 'templates' ] .= ",'$template'" ;
            $class [ 'extends' ] = ucFirst ( $template ) ;
            $class [ 'requires' ] [] = MB_TEMPLATES . '/' . $template . '/' . ucfirst ( $template ) . '.php' ;
        }
        $class [ 'importable' ] = $this->config [ 'importable' ] ;
        $this->mbvardefs->updateVardefs () ;
        $class [ 'fields' ] = $this->mbvardefs->vardefs [ 'fields' ] ;
        $class [ 'fields_string' ] = var_export_helper ( $this->mbvardefs->vardef [ 'fields' ] ) ;
        $relationship = array ( ) ;
        $class [ 'relationships' ] = var_export_helper ( $this->mbvardefs->vardef [ 'relationships' ] ) ;
        $smarty = new Sugar_Smarty ( ) ;
        $smarty->left_delimiter = '{{' ;
        $smarty->right_delimiter = '}}' ;
        $smarty->assign ( 'class', $class ) ;
        //write sugar generated class
        $fp = sugar_fopen ( $path . '/' . $class [ 'name' ] . '_sugar.php', 'w' ) ;
        fwrite ( $fp, $smarty->fetch ( 'modules/ModuleBuilder/tpls/MBModule/Class.tpl' ) ) ;
        fclose ( $fp ) ;
        //write vardefs
        $fp = sugar_fopen ( $path . '/vardefs.php', 'w' ) ;
        fwrite ( $fp, $smarty->fetch ( 'modules/ModuleBuilder/tpls/MBModule/vardef.tpl' ) ) ;
        fclose ( $fp ) ;

        if (! file_exists ( $path . '/' . $class [ 'name' ] . '.php' ))
        {
            $fp = sugar_fopen ( $path . '/' . $class [ 'name' ] . '.php', 'w' ) ;
            fwrite ( $fp, $smarty->fetch ( 'modules/ModuleBuilder/tpls/MBModule/DeveloperClass.tpl' ) ) ;
            fclose ( $fp ) ;
        }
        if (! file_exists ( $path . '/metadata' ))
            mkdir_recursive ( $path . '/metadata' ) ;
        if (! empty ( $this->config [ 'studio' ] ))
        {
            $fp = sugar_fopen ( $path . '/metadata/studio.php', 'w' ) ;
            fwrite ( $fp, $smarty->fetch ( 'modules/ModuleBuilder/tpls/MBModule/Studio.tpl' ) ) ;
            fclose ( $fp ) ;
        } else
        {
            if (file_exists ( $path . '/metadata/studio.php' ))
                unlink ( $path . '/metadata/studio.php' ) ;
        }
    }

    function createMenu ($path)
    {
        $smarty = new Sugar_Smarty ( ) ;
        $smarty->assign ( 'moduleName', $this->key_name ) ;
        $smarty->assign ( 'showvCard', in_array ( 'person', array_keys($this->config[ 'templates' ]) ) ) ;
        $smarty->assign ( 'showimport', $this->config['importable'] );
        //write sugar generated class
        $fp = sugar_fopen ( $path . '/' . 'Menu.php', 'w' ) ;
        fwrite ( $fp, $smarty->fetch ( 'modules/ModuleBuilder/tpls/MBModule/Menu.tpl' ) ) ;
        fclose ( $fp ) ;
    }

    function addInstallDefs (&$installDefs)
    {
        $name = $this->key_name ;
        $installDefs [ 'copy' ] [] = array ( 'from' => '<basepath>/SugarModules/modules/' . $name , 'to' => 'modules/' . $name ) ;
        $installDefs [ 'beans' ] [] = array ( 'module' => $name , 'class' => $name , 'path' => 'modules/' . $name . '/' . $name . '.php' , 'tab' => $this->config [ 'has_tab' ] ) ;
        $this->relationships->addInstallDefs ( $installDefs ) ;
    }

    function getNodes ()
    {

        $lSubs = array ( ) ;
        $psubs = $this->getProvidedSubpanels () ;
        foreach ( $psubs as $sub )
        {
            $subLabel = $sub ;
            if ($subLabel == 'default')
            {
                $subLabel = $GLOBALS [ 'mod_strings' ] [ 'LBL_DEFAULT' ] ;
            }
            $lSubs [] = array ( 'name' => $subLabel , 'type' => 'list' , 'action' => 'module=ModuleBuilder&MB=true&action=editLayout&view=ListView&view_module=' . $this->name . '&view_package=' . $this->package . '&subpanel=' . $sub . '&subpanelLabel=' . $subLabel . '&local=1' ) ;
        }

        $searchSubs = array ( ) ;
        $searchSubs [] = array ( 'name' => translate('LBL_BASIC_SEARCH') , 'type' => 'list' , 'action' => "module=ModuleBuilder&MB=true&action=editLayout&view=basic_search&view_module={$this->name}&view_package={$this->package}"  ) ;
        $searchSubs [] = array ( 'name' => translate('LBL_ADVANCED_SEARCH') , 'type' => 'list' , 'action' => 'module=ModuleBuilder&MB=true&action=editLayout&view=advanced_search&view_module=' . $this->name . '&view_package=' . $this->package  ) ;
        $dashlets = array( );
        $dashlets [] = array('name' => translate('LBL_DASHLETLISTVIEW') , 'type' => 'dashlet' , 'action' => 'module=ModuleBuilder&MB=true&action=editLayout&view=dashlet&view_module=' . $this->name . '&view_package=' . $this->package );
		$dashlets [] = array('name' => translate('LBL_DASHLETSEARCHVIEW') , 'type' => 'dashletsearch' , 'action' => 'module=ModuleBuilder&MB=true&action=editLayout&view=dashletsearch&view_module=' . $this->name . '&view_package=' . $this->package );

		$popups = array( );
        $popups [] = array('name' => translate('LBL_POPUPLISTVIEW') , 'type' => 'popuplistview' , 'action' => 'module=ModuleBuilder&action=editLayout&view=popuplist&view_module=' . $this->name . '&view_package=' . $this->package );
		$popups [] = array('name' => translate('LBL_POPUPSEARCH') , 'type' => 'popupsearch' , 'action' => 'module=ModuleBuilder&action=editLayout&view=popupsearch&view_module=' . $this->name . '&view_package=' . $this->package );
		
        $layouts = array (
        	array ( 'name' => translate('LBL_EDITVIEW') , 'type' => 'edit' , 'action' => 'module=ModuleBuilder&MB=true&action=editLayout&view='.MB_EDITVIEW.'&view_module=' . $this->name . '&view_package=' . $this->package ) ,
        	array ( 'name' => translate('LBL_DETAILVIEW') , 'type' => 'detail' , 'action' => 'module=ModuleBuilder&MB=true&action=editLayout&view='.MB_DETAILVIEW.'&view_module=' . $this->name . '&view_package=' . $this->package ) ,
        	array ( 'name' => translate('LBL_LISTVIEW') , 'type' => 'list' , 'action' => 'module=ModuleBuilder&MB=true&action=editLayout&view='.MB_LISTVIEW.'&view_module=' . $this->name . '&view_package=' . $this->package ) ,
        	array ( 'name' => translate('LBL_QUICKCREATE') , 'type' => MB_QUICKCREATE,  'action' => 'module=ModuleBuilder&MB=true&action=editLayout&view='.MB_QUICKCREATE.'&view_module=' . $this->name . '&view_package=' . $this->package ) ,
        	array ( 'name' => translate('LBL_DASHLET') , 'type' => 'Folder', 'children' => $dashlets, 'action' => 'module=ModuleBuilder&MB=true&action=wizard&view=dashlet&view_module=' . $this->name . '&view_package=' . $this->package  ),
        	array ( 'name' => translate('LBL_POPUP') , 'type' => 'Folder', 'children' => $popups, 'action' => 'module=ModuleBuilder&MB=true&action=wizard&view=popup&view_module=' . $this->name . '&view_package=' . $this->package  ),
        	array ( 'name' => translate('LBL_SEARCH_FORMS') , 'action' => 'module=ModuleBuilder&MB=true&action=wizard&view=search&view_module=' . $this->name . '&view_package=' . $this->package , 'type' => 'folder' , 'children' => $searchSubs )
        	) ;

        $children = array (
        	array ( 'name' => translate('LBL_FIELDS') , 'action' => 'module=ModuleBuilder&action=modulefields&view_module=' . $this->name . '&view_package=' . $this->package ) ,
        	array ( 'name' => translate('LBL_LABELS') , 'action' => 'module=ModuleBuilder&action=modulelabels&view_module=' . $this->name . '&view_package=' . $this->package ) ,
        	array ( 'name' => translate('LBL_RELATIONSHIPS') , 'action' => 'module=ModuleBuilder&action=relationships&view_module=' . $this->name . '&view_package=' . $this->package ) ,
        	array ( 'name' => translate('LBL_LAYOUTS') , 'type' => 'Folder' , 'action' => "module=ModuleBuilder&action=wizard&view_module={$this->name}&view_package={$this->package}&MB=1" , 'children' => $layouts ) ,
        	) ;

        if (count ( $lSubs ) > 0)
        {
            $children [] = array ( 'name' => translate('LBL_AVAILABLE_SUBPANELS') , 'type' => 'folder' , 'children' => $lSubs ) ;
        }

        $nodes = array ( 'name' => $this->name , 'children' => $children , 'action' => 'module=ModuleBuilder&action=module&view_module=' . $this->name . '&view_package=' . $this->package ) ;

        return $nodes ;
    }


    function getProvidedSubpanels ()
    {
        $this->providedSubpanels = array () ;

        $subpanelDir = $this->getModuleDir () . '/metadata/subpanels/' ;
        if (file_exists ( $subpanelDir ))
        {
            $f = dir ( $subpanelDir ) ;
            require_once 'modules/ModuleBuilder/parsers/relationships/AbstractRelationships.php' ;

            while ( $g = $f->read () )
            {
                // sanity check to confirm that this is a usable subpanel...
                if (substr ( $g, 0, 1 ) != '.' && AbstractRelationships::validSubpanel ( $subpanelDir . $g ))
                {
                    $subname = str_replace ( '.php', '', $g ) ;
                    $this->providedSubpanels [ $subname ] = $subname ;
                }
            }
        }

        return $this->providedSubpanels;
    }

    function getTypes ()
    {
        $types = array ( ) ;
        $d = dir ( MB_TEMPLATES ) ;
        while ( $e = $d->read () )
        {
            if (substr ( $e, 0, 1 ) != '.')
            {
                $types [ $e ] = $e ;
            }
        }

        return $types ;
    }

    function rename ($new_name)
    {
        $old = $this->getModuleDir () ;
        $old_name = $this->key_name;
        $this->name = $new_name ;
        $this->key_name = $this->package_key . '_' . $this->name ;
        $new = $this->getModuleDir () ;
        if (file_exists ( $new ))
        {
            return false ;
        }
        $renamed = rename ( $old, $new ) ;
        if ($renamed)
        {
            $this->renameMetaData ( $new , $old_name) ;
            $this->renameLanguageFiles ( $new ) ;

        }
        return $renamed ;
    }

	function renameLanguageFiles ($new_dir , $duplicate = false)
    {

        $this->mblanguage->name = $this->name ;
        $this->mblanguage->path = $new_dir ;
        $this->mblanguage->generateAppStrings () ;
        $this->mblanguage->save ( $this->key_name, $duplicate, true) ;
    }

    function renameMetaData ($new_dir, $old_name)
    {
        $GLOBALS [ 'log' ]->debug ( 'MBModule.php->renameMetaData: new_dir=' . $new_dir ) ;
        if (! file_exists ( $new_dir ))
            return ;
        $dir = dir ( $new_dir ) ;
        while ( $e = $dir->read () )
        {
            if (substr ( $e, 0, 1 ) != '.')
            {
                if (is_dir ( $new_dir . '/' . $e ))
                {
                    $this->renameMetaData ( $new_dir . '/' . $e,  $old_name) ;
                }
                if (is_file ( $new_dir . '/' . $e ))
                {
                    $contents = file_get_contents ( $new_dir . '/' . $e ) ;
                    $contents = preg_replace ( '/(\$module_name[ ]*=[ ]*\')(.*)(\'[ ]*;)/', '$1' . $this->key_name . '$3', $contents ) ;
                    $contents = preg_replace ( '/(\$_module_name[ ]*=[ ]*\')(.*)(\'[ ]*;)/', '$1' . strtolower ( $this->key_name ) . '$3', $contents ) ;
                    $contents = preg_replace ( '/(\$MODULE_NAME[ ]*=[ ]*\')(.*)(\'[ ]*;)/', '$1' . strtoupper ( $this->key_name ) . '$3', $contents ) ;
                    $contents = preg_replace ( '/(\$object_name[ ]*=[ ]*\')(.*)(\'[ ]*;)/', '$1' . $this->key_name . '$3', $contents ) ;
                    $contents = preg_replace ( '/(\$_object_name[ ]*=[ ]*\')(.*)(\'[ ]*;)/', '$1' . strtolower ( $this->key_name ) . '$3', $contents ) ;
                    $contents = preg_replace ( '/(\$OBJECT_NAME[ ]*=[ ]*\')(.*)(\'[ ]*;)/', '$1' . strtoupper ( $this->key_name ) . '$3', $contents ) ;
                    $contents = str_replace  ( "{$old_name}_", $this->key_name . "_", $contents ) ;
                    $fp = sugar_fopen ( $new_dir . '/' . $e, 'w' ) ;
                    fwrite ( $fp, $contents ) ;
                    fclose ( $fp ) ;
                }
            }
        }

    }

    function copy ($new_name)
    {
        $old = $this->getModuleDir () ;

        $count = 0 ;
        $old_name = $this->key_name;
        $this->name = $new_name ;
        $this->key_name = $this->package_key . '_' . $this->name ;
        $new = $this->getModuleDir () ;
        while ( file_exists ( $new ) )
        {
            $count ++ ;
            $this->name = $new_name . $count ;
            $this->key_name = $this->package_key . '_' . $this->name ;
            $new = $this->getModuleDir () ;
        }

        $new = $this->getModuleDir () ;
        $copied = copy_recursive ( $old, $new ) ;

        if ($copied)
        {
            $this->renameMetaData ( $new , $old_name) ;
            $this->renameLanguageFiles ( $new, true ) ;
        }
        return $copied ;

    }

    function delete ()
    {
        return rmdir_recursive ( $this->getModuleDir () ) ;
    }

    function populateFromPost ()
    {
        foreach ( $this->implementable as $key => $value )
        {
            $this->config [ $key ] = ! empty ( $_REQUEST [ $key ] ) ;
        }
        foreach ( $this->always_implement as $key => $value )
        {
            $this->config [ $key ] = true ;
        }
        if (! empty ( $_REQUEST [ 'type' ] ))
        {
            $this->addTemplate ( $_REQUEST [ 'type' ] ) ;
        }

        if (! empty ( $_REQUEST [ 'label' ] ))
        {
            $this->config [ 'label' ] = $_REQUEST [ 'label' ] ;
        }

        $this->config [ 'importable' ] = ! empty( $_REQUEST[ 'importable' ] ) ;

    }

    function getAvailibleSubpanelDef ($panelName)
    {
        $filepath = $this->getModuleDir () . "/metadata/subpanels/{$panelName}.php" ;
        if (file_exists ( $filepath ))
        {
            include ($filepath) ;
            return $subpanel_layout ;
        }
        return array ( ) ;

    }

    function saveAvailibleSubpanelDef ($panelName , $layout)
    {
        $dir = $this->getModuleDir () . "/metadata/subpanels" ;
        $filepath = "$dir/{$panelName}.php" ;
        if (mkdir_recursive ( $dir ))
        {
            // preserve any $module_name entry if one exists
            if (file_exists ( $filepath ))
            {
                include ($filepath) ;
            }
            $module_name = (isset ( $module_name )) ? $module_name : $this->key_name ;
            $layout = "<?php\n" . '$module_name=\'' . $module_name . "';\n" . '$subpanel_layout = ' . var_export_helper ( $layout ) . ";" ;
            $GLOBALS [ 'log' ]->debug ( "About to save this file to $filepath" ) ;
            $GLOBALS [ 'log' ]->debug ( $layout ) ;
            $fw = sugar_fopen ( $filepath, 'w' ) ;
            fwrite ( $fw, $layout ) ;
            fclose ( $fw ) ;
        }
    }

    function getLocalSubpanelDef ($panelName)
    {

    }

    function createIcon ()
    {
        $icondir = $this->package_path . "/icons" ;
        mkdir_recursive ( $icondir ) ;
        $template = "" ;
        foreach ( $this->config [ 'templates' ] as $temp => $val )
            $template = $temp ;
        copy ( "themes/default/images/icon_$template.gif", "$icondir/icon_" . ucfirst ( $this->key_name ) . ".gif" ) ;
        copy ( "include/SugarObjects/templates/$template/icons/$template.gif", "$icondir/" . $this->key_name . ".gif" ) ;
        if (file_exists("include/SugarObjects/templates/$template/icons/Create$template.gif"))
        	copy ( "include/SugarObjects/templates/$template/icons/Create$template.gif", "$icondir/Create" . $this->key_name . ".gif" ) ;
        if (file_exists("include/SugarObjects/templates/$template/icons/{$template}_32.gif"))
        	copy ( "include/SugarObjects/templates/$template/icons/{$template}_32.gif", "$icondir/icon_" . $this->key_name . "_32.gif" ) ;
    }

    function removeFieldFromLayouts ( $fieldName )
    {
        // hardcoded list of types for now, as also hardcoded in a different form in getNodes
        // TODO: replace by similar mechanism to StudioModule to determine the list of available views for this module
        $views = array ( 'editview' , 'detailview' , 'listview' , 'basic_search' , 'advanced_search' , 'dashlet' , 'popuplist');
        
    	foreach ($views as $type )
        {
            $parser = ParserFactory::getParser( $type , $this->name , $this->package ) ;
            if ($parser->removeField ( $fieldName ) )
                $parser->handleSave(false) ; // don't populate from $_REQUEST, just save as is...
        }
		//Remove the fields in subpanel
        $psubs = $this->getProvidedSubpanels() ; 
        foreach ( $psubs as $sub )
        {
			$parser = ParserFactory::getParser( MB_LISTVIEW , $this->name, $this->package ,  $sub) ;
			if ($parser->removeField ( $fieldName ) )
	            $parser->handleSave(false) ; 
        }
    }

}
?>