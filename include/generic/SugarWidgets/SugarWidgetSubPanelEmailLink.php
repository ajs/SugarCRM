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




require_once('include/generic/SugarWidgets/SugarWidgetField.php');

class SugarWidgetSubPanelEmailLink extends SugarWidgetField {

	function displayList(&$layout_def) {
		global $current_user;
		global $beanList;
		global $focus;
		global $sugar_config;
		global $locale;
		
		if(isset($layout_def['varname'])) {
			$key = strtoupper($layout_def['varname']);
		} else {
			$key = $this->_get_column_alias($layout_def);
			$key = strtoupper($key);
		}
		$value = $layout_def['fields'][$key];
		
		

			if(isset($_REQUEST['action'])) $action = $_REQUEST['action'];
			else $action = '';

			if(isset($_REQUEST['module'])) $module = $_REQUEST['module'];
			else $module = '';

			if(isset($_REQUEST['record'])) $record = $_REQUEST['record'];
			else $record = '';

			if (!empty($focus->name)) {
				$name = $focus->name;
			} else {
				if( !empty($focus->first_name) && !empty($focus->last_name)) {
					$name = $locale->getLocaleFormattedName($focus->first_name, $focus->last_name);
					}
				if(empty($name)) {
					$name = '*';
				}
			}
							
			$userPref = $current_user->getPreference('email_link_type');
			$defaultPref = $sugar_config['email_default_client'];
			if($userPref != '') {
				$client = $userPref;
			} else {
				$client = $defaultPref;
			}
			
			if($client == 'sugar')
			{				
			    $fullComposeUrl = 'load_id='.$layout_def['fields']['ID']
                                . '&load_module='. $this->layout_manager->defs['module_name']
                                . '&parent_type='.$this->layout_manager->defs['module_name']
                                . '&parent_id='.$layout_def['fields']['ID'];

                if(isset($layout_def['fields']['FULL_NAME'])){
                   $fullComposeUrl .= '&parent_name='.urlencode($layout_def['fields']['FULL_NAME']);
                }
                $fullComposeUrl .= '&return_module='.$module.'&return_action='.$action.'&return_id='.$record;
                require_once('modules/Emails/EmailUI.php');
                $eUi = new EmailUI();
                $j_quickComposeOptions = $eUi->generateComposePackageForQuickCreateFromComposeUrl($fullComposeUrl);

                $link = "<a href='javascript:void(0);' onclick='SUGAR.quickCompose.init($j_quickComposeOptions);'>";
			} else {
				$link = '<a href="mailto:' . $value .'" >';
			}

			return $link.$value.'</a>';
		
	}
} // end class def
?>




















