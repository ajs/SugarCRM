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



class AuthenticationController {
	var $loggedIn = false; //if a user has attempted to login
	var $authenticated = false;
	var $loginSuccess = false;// if a user has successfully logged in

	/**
	 * Creates an instance of the authentication controller and loads it
	 *
	 * @param STRING $type - the authentication Controller - default to SugarAuthenticate
	 * @return AuthenticationController -
	 */
	function AuthenticationController($type = 'SugarAuthenticate') {
		if(!file_exists('modules/Users/authentication/'.$type.'/' . $type . '.php'))$type = 'SugarAuthenticate';


		if($type == 'SugarAuthenticate' && !empty($GLOBALS['system_config']->settings['system_ldap_enabled']) && empty($_SESSION['sugar_user'])){
			$type = 'LDAPAuthenticate';
		}



		require_once ('modules/Users/authentication/'.$type.'/' . $type . '.php');
		$this->authController = new $type();
	}


	/**
	 * Returns an instance of the authentication controller
	 *
	 * @param STRING $type this is the type of authetnication you want to use default is SugarAuthenticate
	 * @return an instance of the authetnciation controller
	 */
	function &getInstance($type='SugarAuthenticate'){
		static $authcontroller;
		if(empty($authcontroller)){
			$authcontroller = new AuthenticationController($type);
		}
		return $authcontroller;
	}

	/**
	 * This function is called when a user initially tries to login.
	 * It will return true if the user successfully logs in or false otherwise.
	 *
	 * @param STRING $username
	 * @param STRING $password
	 * @param ARRAY $PARAMS
	 * @return boolean
	 */
	function login($username, $password, $PARAMS = array ()) {
		//kbrill bug #13225
		$_SESSION['loginAttempts'] = (isset($_SESSION['loginAttempts']))? $_SESSION['loginAttempts'] + 1: 1;
		unset($GLOBALS['login_error']);

		if($this->loggedIn)return $this->loginSuccess;
		LogicHook::initialize()->call_custom_logic('Users', 'before_login');

		$this->loginSuccess = $this->authController->loginAuthenticate($username, $password, false, $PARAMS);
		$this->loggedIn = true;

		if($this->loginSuccess){
			//Ensure the user is authorized
			checkAuthUserStatus();

			loginLicense();
			if(!empty($GLOBALS['login_error'])){
				unset($_SESSION['authenticated_user_id']);
				$GLOBALS['log']->fatal('FAILED LOGIN: potential hack attempt');
				$this->loginSuccess = false;
				return false;
			}

			//call business logic hook
			if(isset($GLOBALS['current_user']))
				$GLOBALS['current_user']->call_custom_logic('after_login');

			// Check for running Admin Wizard
			$config = new Administration();
			$config->retrieveSettings();
		    if ( is_admin($GLOBALS['current_user']) && empty($config->settings['system_adminwizard']) && $_REQUEST['action'] != 'AdminWizard' ) {
				$GLOBALS['module'] = 'Configurator';
				$GLOBALS['action'] = 'AdminWizard';
				ob_clean();
				header("Location: index.php?module=Configurator&action=AdminWizard");
				sugar_cleanup(true);
			}

			$ut = $GLOBALS['current_user']->getPreference('ut');
			$checkTimeZone = true;
			if (is_array($PARAMS) && !empty($PARAMS) && isset($PARAMS['passwordEncrypted'])) {
				$checkTimeZone = false;
			} // if
			if(empty($ut) && $checkTimeZone && $_REQUEST['action'] != 'SetTimezone' && $_REQUEST['action'] != 'SaveTimezone' ) {
				$GLOBALS['module'] = 'Users';
				$GLOBALS['action'] = 'Wizard';
				ob_clean();
				header("Location: index.php?module=Users&action=Wizard");
				sugar_cleanup(true);
			}
		}else{
			//kbrill bug #13225
			LogicHook::initialize();
			$GLOBALS['logic_hook']->call_custom_logic('Users', 'login_failed');
			$GLOBALS['log']->fatal('FAILED LOGIN:attempts[' .$_SESSION['loginAttempts'] .'] - '. $username);
		}
		// if password has expired, set a session variable

		return $this->loginSuccess;
	}

	/**
	 * This is called on every page hit.
	 * It returns true if the current session is authenticated or false otherwise
	 * @return booelan
	 */
	function sessionAuthenticate() {

		if(!$this->authenticated){
			$this->authenticated = $this->authController->sessionAuthenticate();
		}
		if($this->authenticated){
			if(!isset($_SESSION['userStats']['pages'])){
			    $_SESSION['userStats']['loginTime'] = time();
			    $_SESSION['userStats']['pages'] = 0;
			}
			$_SESSION['userStats']['lastTime'] = time();
			$_SESSION['userStats']['pages']++;

		}
		return $this->authenticated;
	}

	/**
	 * Called when a user requests to logout. Should invalidate the session and redirect
	 * to the login page.
	 *
	 */
	function logout(){
		$GLOBALS['current_user']->call_custom_logic('before_logout');
		$this->authController->logout();
		LogicHook::initialize();
		$GLOBALS['logic_hook']->call_custom_logic('Users', 'after_logout');
	}


}
?>
