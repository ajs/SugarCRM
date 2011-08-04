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



global $db;
function displayAdminError($errorString){
	$output = '<p class="error">' . $errorString .'</p>';
		echo $output;
}

if(isset($_SESSION['rebuild_relationships'])){
	displayAdminError(translate('MSG_REBUILD_RELATIONSHIPS', 'Administration'));
}

if(isset($_SESSION['rebuild_extensions'])){
	displayAdminError(translate('MSG_REBUILD_EXTENSIONS', 'Administration'));
}

if (empty($license)){
	$license=new Administration();
	$license=$license->retrieveSettings('license');
}



if(!empty($_SESSION['HomeOnly'])){
	displayAdminError(translate('FATAL_LICENSE_ALTERED', 'Administration'));
}

if(isset($license) && !empty($license->settings['license_msg_all'])){
	displayAdminError(base64_decode($license->settings['license_msg_all']));	
}
if ( (strpos($_SERVER["SERVER_SOFTWARE"],'Microsoft-IIS') !== false) && (php_sapi_name() == 'cgi-fcgi') && (ini_get('fastcgi.logging') != '0') ) {
    displayAdminError(translate('LBL_FASTCGI_LOGGING', 'Administration'));
}
if(is_admin($current_user)){
if(!empty($_SESSION['COULD_NOT_CONNECT'])){
	displayAdminError(translate('LBL_COULD_NOT_CONNECT', 'Administration') . ' '. $timedate->to_display_date_time($_SESSION['COULD_NOT_CONNECT']));		
}
if(!empty($_SESSION['EXCEEDING_OC_LICENSES']) && $_SESSION['EXCEEDING_OC_LICENSES'] == true){
    displayAdminError(translate('LBL_EXCEEDING_OC_LICENSES', 'Administration'));
}
if(isset($license) && !empty($license->settings['license_msg_admin'])){
    // UUDDLRLRBA
	$GLOBALS['log']->fatal(base64_decode($license->settings['license_msg_admin']));
    //displayAdminError(base64_decode($license->settings['license_msg_admin']));
	return;
}

//No SMTP server is set up Error.
$smtp_error = false;
$admin = new Administration();
$admin->retrieveSettings();

//If sendmail has been configured by setting the config variable ignore this warning
$sendMailEnabled = isset($sugar_config['allow_sendmail_outbound']) && $sugar_config['allow_sendmail_outbound'];

if(trim($admin->settings['mail_smtpserver']) == '' && !$sendMailEnabled) {
    if($admin->settings['notify_on']) {
        $smtp_error = true;
    }
}

if($smtp_error) {
    displayAdminError(translate('WARN_NO_SMTP_SERVER_AVAILABLE_ERROR','Administration'));
}

 if(!empty($dbconfig['db_host_name']) || $sugar_config['sugar_version'] != $sugar_version ){
       		displayAdminError(translate('WARN_REPAIR_CONFIG', 'Administration'));
        }

        if( !isset($sugar_config['installer_locked']) || $sugar_config['installer_locked'] == false ){
        	displayAdminError(translate('WARN_INSTALLER_LOCKED', 'Administration'));
		}



		






        if(empty($GLOBALS['sugar_config']['admin_access_control'])){
			if(isset($_SESSION['invalid_versions'])){
				$invalid_versions = $_SESSION['invalid_versions'];
				foreach($invalid_versions as $invalid){
					displayAdminError(translate('WARN_UPGRADE', 'Administration'). $invalid['name'] .translate('WARN_UPGRADE2', 'Administration'));
				}
			}
		
			if (isset($_SESSION['available_version'])){
				if($_SESSION['available_version'] != $sugar_version)
				{
					displayAdminError(translate('WARN_UPGRADENOTE', 'Administration').$_SESSION['available_version_description']);
				}
			}
        }

//		if (!isset($_SESSION['dst_fixed']) || $_SESSION['dst_fixed'] != true) {
//			$qDst = "SELECT count(*) AS dst FROM versions WHERE name = 'DST Fix'";
//			$rDst = $db->query($qDst);
//			$rowsDst = $db->fetchByAssoc($rDst);
//			if($rowsDst['dst'] > 0) {
//				$_SESSION['dst_fixed'] = true;
//			} else {
//				$_SESSION['dst_fixed'] = false;
//				displayAdminError($app_strings['LBL_DST_NEEDS_FIXIN']);
//			}
//
//		}

		if(isset($_SESSION['administrator_error']))
		{
			// Only print DB errors once otherwise they will still look broken
			// after they are fixed.
			displayAdminError($_SESSION['administrator_error']);
		}

		unset($_SESSION['administrator_error']);
}

?>
