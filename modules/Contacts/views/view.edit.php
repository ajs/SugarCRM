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

 * Description: This file is used to override the default Meta-data EditView behavior
 * to provide customization specific to the Contacts module.
 * Portions created by SugarCRM are Copyright (C) SugarCRM, Inc.
 * All Rights Reserved.
 * Contributor(s): ______________________________________..
 ********************************************************************************/

require_once('include/MVC/View/views/view.edit.php');

class ContactsViewEdit extends ViewEdit 
{   
 	public function __construct()
 	{
 		parent::ViewEdit();
 		$this->useForSubpanel = true;
 		$this->useModuleQuickCreateTemplate = true;
 	}
 	
 	/**
 	 * @see SugarView::display()
	 * 
 	 * We are overridding the display method to manipulate the sectionPanels.
 	 * If portal is not enabled then don't show the Portal Information panel.
 	 */
 	public function display() 
 	{
        $this->ev->process();
		if ( !empty($_REQUEST['contact_name']) && !empty($_REQUEST['contact_id'])
            && $this->ev->fieldDefs['report_to_name']['value'] == ''
            && $this->ev->fieldDefs['reports_to_id']['value'] == '') { 
            $this->ev->fieldDefs['report_to_name']['value'] = $_REQUEST['contact_name'];
            $this->ev->fieldDefs['reports_to_id']['value'] = $_REQUEST['contact_id'];
        }
        $admin = new Administration();
		$admin->retrieveSettings();
		if(empty($admin->settings['portal_on']) || !$admin->settings['portal_on']) {
		   unset($this->ev->sectionPanels[strtoupper('lbl_portal_information')]);
		} else {
		   echo '<script src="modules/Contacts/Contact.js"></script>';
		   echo '<script language="javascript">';
		   echo 'addToValidateComparison(\'EditView\', \'portal_password\', \'varchar\', false, SUGAR.language.get(\'app_strings\', \'ERR_SQS_NO_MATCH_FIELD\') + SUGAR.language.get(\'Contacts\', \'LBL_PORTAL_PASSWORD\'), \'portal_password1\');';	
           echo 'addToValidateVerified(\'EditView\', \'portal_name_verified\', \'bool\', false, SUGAR.language.get(\'app_strings\', \'ERR_EXISTING_PORTAL_USERNAME\'));';
           echo 'YAHOO.util.Event.on(\'portal_name\', \'blur\', validatePortalName);';
		   echo 'YAHOO.util.Event.on(\'portal_name\', \'keydown\', handleKeyDown);';
		   echo '</script>';
		}
			
		echo $this->ev->display($this->showTitle);
 	}	
}