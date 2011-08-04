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
require_once("include/OutboundEmail/OutboundEmail.php");

class UsersController extends SugarController
{
	protected function action_delete()
	{
	    if($_REQUEST['record'] != $GLOBALS['current_user']->id && ($GLOBALS['current_user']->isAdminForModule('Users')
            ))
        {
            $u = new User();
            $u->retrieve($_REQUEST['record']);
            $u->status = 'Inactive';
            $u->deleted = 1;
            $u->employee_status = 'Terminated';
            $u->save();
            $GLOBALS['log']->info("User id: {$GLOBALS['current_user']->id} deleted user record: {$_REQUEST['record']}");

            $eapm = loadBean('EAPM');
            $eapm->delete_user_accounts($_REQUEST['record']);
            $GLOBALS['log']->info("Removing user's External Accounts");
            
            SugarApplication::redirect("index.php?module=Users&action=index");
        }
        else 
            sugar_die("Unauthorized access to administration.");
	}
	protected function action_wizard() 
	{
		$this->view = 'wizard';
	}
	
	protected function action_saveuserwizard() 
	{
	    global $current_user, $sugar_config;
	    
	    // set all of these default parameters since the Users save action will undo the defaults otherwise
	    $_POST['record'] = $current_user->id;
	    $_POST['is_admin'] = ( $current_user->is_admin ? 'on' : '' );
	    $_POST['use_real_names'] = true;
	    $_POST['should_remind'] = '1';
	    $_POST['reminder_time'] = 1800;
        $_POST['mailmerge_on'] = 'on';
        $_POST['user_theme'] = (string) SugarThemeRegistry::getDefault();
	    
	    // save and redirect to new view
	    $_REQUEST['return_module'] = 'Home';
	    $_REQUEST['return_action'] = 'index';
		require('modules/Users/Save.php');
	}
}	
?>
