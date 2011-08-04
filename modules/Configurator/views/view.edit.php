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

 * Portions created by SugarCRM are Copyright (C) SugarCRM, Inc.
 * All Rights Reserved.
 * Contributor(s): ______________________________________..
 ********************************************************************************/

require_once('include/MVC/View/views/view.edit.php');
require_once('modules/Configurator/Forms.php');
require_once('modules/Administration/Forms.php');
require_once('modules/Configurator/Configurator.php');
require_once('include/SugarLogger/SugarLogger.php');

class ConfiguratorViewEdit extends ViewEdit
{
    /**
	 * @see SugarView::preDisplay()
	 */
	public function preDisplay()
    {
        if(!is_admin($GLOBALS['current_user']))
            sugar_die($GLOBALS['app_strings']['ERR_NOT_ADMIN']); 
    }
    
    /**
	 * @see SugarView::_getModuleTitleParams()
	 */
	protected function _getModuleTitleParams($browserTitle = false)
	{
	    global $mod_strings;
	    
    	return array(
    	   "<a href='index.php?module=Administration&action=index'>".translate('LBL_MODULE_NAME','Administration')."</a>",
    	   $mod_strings['LBL_SYSTEM_SETTINGS']
    	   );
    }
    
	/**
	 * @see SugarView::display()
	 */
	public function display()
	{
	    global $current_user, $mod_strings, $app_strings, $app_list_strings, $sugar_config, $locale;
	    
	    $configurator = new Configurator();
        $sugarConfig = SugarConfig::getInstance();
        $focus = new Administration();
        $configurator->parseLoggerSettings();
        
        $focus->retrieveSettings();
        if(!empty($_POST['restore'])){
            $configurator->restoreConfig();
        }
        
        $this->ss->assign('MOD', $mod_strings);
        $this->ss->assign('APP', $app_strings);
        $this->ss->assign('APP_LIST', $app_list_strings);
        $this->ss->assign('config', $configurator->config);
        $this->ss->assign('error', $configurator->errors);
        $this->ss->assign("AUTO_REFRESH_INTERVAL_OPTIONS", get_select_options_with_id($app_list_strings['dashlet_auto_refresh_options_admin'], isset($configurator->config['dashlet_auto_refresh_min']) ? $configurator->config['dashlet_auto_refresh_min'] : 30));
        $this->ss->assign('LANGUAGES', get_languages());
        $this->ss->assign("JAVASCRIPT",get_set_focus_js(). get_configsettings_js());
        $this->ss->assign('company_logo', SugarThemeRegistry::current()->getImageURL('company_logo.png'));
        $this->ss->assign("settings", $focus->settings);
        $this->ss->assign("mail_sendtype_options", get_select_options_with_id($app_list_strings['notifymail_sendtype'], $focus->settings['mail_sendtype']));
        if(!empty($focus->settings['proxy_on'])){
            $this->ss->assign("PROXY_CONFIG_DISPLAY", 'inline');
        }else{
            $this->ss->assign("PROXY_CONFIG_DISPLAY", 'none');
        }
        if(!empty($focus->settings['proxy_auth'])){
            $this->ss->assign("PROXY_AUTH_DISPLAY", 'inline');
        }else{
            $this->ss->assign("PROXY_AUTH_DISPLAY", 'none');
        }
        if (!empty($configurator->config['logger']['level'])) {
            $this->ss->assign('log_levels', get_select_options_with_id(  LoggerManager::getLoggerLevels(), $configurator->config['logger']['level']));
        } else {
            $this->ss->assign('log_levels', get_select_options_with_id(  LoggerManager::getLoggerLevels(), ''));
        }
        if (!empty($configurator->config['logger']['file']['suffix'])) {
            $this->ss->assign('filename_suffix', get_select_options_with_id(  SugarLogger::$filename_suffix,$configurator->config['logger']['file']['suffix']));
        } else {
            $this->ss->assign('filename_suffix', get_select_options_with_id(  SugarLogger::$filename_suffix,''));
        }
        
        echo $this->getModuleTitle(false);
        
        $this->ss->display('modules/Configurator/tpls/EditView.tpl');
        
        $javascript = new javascript();
        $javascript->setFormName("ConfigureSettings");
        $javascript->addFieldGeneric("notify_fromaddress", "email", $mod_strings['LBL_NOTIFY_FROMADDRESS'], TRUE, "");
        $javascript->addFieldGeneric("notify_subject", "varchar", $mod_strings['LBL_NOTIFY_SUBJECT'], TRUE, "");
        $javascript->addFieldGeneric("proxy_host", "varchar", $mod_strings['LBL_PROXY_HOST'], TRUE, "");
        $javascript->addFieldGeneric("proxy_port", "int", $mod_strings['LBL_PROXY_PORT'], TRUE, "");
        $javascript->addFieldGeneric("proxy_password", "varchar", $mod_strings['LBL_PROXY_PASSWORD'], TRUE, "");
        $javascript->addFieldGeneric("proxy_username", "varchar", $mod_strings['LBL_PROXY_USERNAME'], TRUE, "");
        echo $javascript->getScript();
	}
}
