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

 * Description:  Base form for contact
 * Portions created by SugarCRM are Copyright (C) SugarCRM, Inc.
 * All Rights Reserved.
 * Contributor(s): ______________________________________..
 ********************************************************************************/

class ContactFormBase {

function checkForDuplicates($prefix){
	global $local_log;
    require_once('include/formbase.php');
	
	$focus = new Contact();
	$query = '';
	$baseQuery = 'SELECT id, first_name, last_name, title FROM contacts where deleted = 0 AND ';
	if(!empty($_POST[$prefix.'first_name']) && !empty($_POST[$prefix.'last_name'])){
		$query = $baseQuery ."  first_name LIKE '". $_POST[$prefix.'first_name'] . "%' AND last_name = '". $_POST[$prefix.'last_name'] ."'";
	}else{
		$query = $baseQuery ."  last_name = '". $_POST[$prefix.'last_name'] ."'";
	}

    $rows = array();
    global $db;
	$result = $db->query($query);
	while (($row = $db->fetchByAssoc($result)) != null) {
		if(!isset($rows[$row['id']])) {
		   $rows[]=$row;
		}
	}

	$count = 0;
	$emails = array();
	$emailStr = '';
	while(isset($_POST['emailAddress' . $count])) {
	      $emailStr .= ",'" . strtoupper(trim($_POST['emailAddress' . $count++])) . "'";
	} //while

	if($count > 0) {
		$emailStr = substr($emailStr, 1);
		$query = 'SELECT DISTINCT er.bean_id AS id FROM email_addr_bean_rel er, ' .
		         'email_addresses ea WHERE ea.id = er.email_address_id ' .
		         'AND ea.deleted = 0 AND er.deleted = 0 AND er.bean_module = \'Contacts\' ' .
	             'AND email_address_caps IN (' . $emailStr . ')';
		$result = $db->query($query);
		while (($row= $db->fetchByAssoc($result)) != null) {
			if(!isset($rows[$row['id']])) {
			   $query2 = "SELECT id, first_name, last_name, title FROM contacts WHERE deleted = 0 AND id = '" . $row['id'] . "'";
			   $result2 = $db->query($query2);
			   $r = $db->fetchByAssoc($result2);
			   if(isset($r['id']) && !array_key_exists('id', $r)) {
			   	  $rows[]=$r;
			   }
			} //if
		}
	} //if

    return !empty($rows) ? $rows : null;
}

function buildTableForm($rows, $mod=''){
	global $action;
	if(!empty($mod)){
	global $current_language;
	$mod_strings = return_module_language($current_language, $mod);
	}else global $mod_strings;
	global $app_strings;
	$cols = sizeof($rows[0]) * 2 + 1;
	if ($action != 'ShowDuplicates')
	{
		$form = '<table width="100%"><tr><td>'.$mod_strings['MSG_DUPLICATE']. '</td></tr><tr><td height="20"></td></tr></table>';
		$form .= "<form action='index.php' method='post' name='dupContacts'>
					<input type='hidden' name='selectedContact' value=''>";
		$form .= getPostToForm('/emailAddress(PrimaryFlag|OptOutFlag|InvalidFlag)?[0-9]*?$/', true);

	}
	else
	{
		$form = '<table width="100%"><tr><td>'.$mod_strings['MSG_SHOW_DUPLICATES']. '</td></tr><tr><td height="20"></td></tr></table>';
	}
	$form .= "<table width='100%' cellpadding='0' cellspacing='0' class='list view' border='0'><tr class='pagination'><td colspan='$cols'><table width='100%' cellspacing='0' cellpadding='0' border='0'><tr><td>";
	if ($action == 'ShowDuplicates')
	{
		$form .= "<input title='${app_strings['LBL_SAVE_BUTTON_TITLE']}' accessKey='${app_strings['LBL_SAVE_BUTTON_KEY']}' class='button' onclick=\"this.form.action.value='Save';\" type='submit' name='button' value='  ${app_strings['LBL_SAVE_BUTTON_LABEL']}  '>\n";
        if (!empty($_REQUEST['return_module']) && !empty($_REQUEST['return_action']) && !empty($_REQUEST['return_id']))
            $form .= "<input title='${app_strings['LBL_CANCEL_BUTTON_TITLE']}' accessKey='${app_strings['LBL_CANCEL_BUTTON_KEY']}' class='button' onclick=\"this.form.module.value=".$_REQUEST['return_module'].";this.form.action.value=".$_REQUEST['return_action'].";this.form.record.value=".$_REQUEST['return_id']."'\" type='submit' name='button' value='  ${app_strings['LBL_CANCEL_BUTTON_LABEL']}  '>";
        else if (!empty($_POST['return_module']) && !empty($_POST['return_action']))
            $form .= "<input title='${app_strings['LBL_CANCEL_BUTTON_TITLE']}' accessKey='${app_strings['LBL_CANCEL_BUTTON_KEY']}' class='button' onclick=\"this.form.module.value=".$_POST['return_module'].";this.form.action.value=". $_POST['return_action'].";'\" type='submit' name='button' value='  ${app_strings['LBL_CANCEL_BUTTON_LABEL']}  '>";
        else
            $form .= "<input title='${app_strings['LBL_CANCEL_BUTTON_TITLE']}' accessKey='${app_strings['LBL_CANCEL_BUTTON_KEY']}' class='button' onclick=\"this.form.action.value='ListView';\" type='submit' type='submit' name='button' value='  ${app_strings['LBL_CANCEL_BUTTON_LABEL']}  '>";
	}
	else
	{
		$form .= "<input type='submit' class='button' name='ContinueContact' value='${mod_strings['LNK_NEW_CONTACT']}'>";
	}
	$form .= "</td></tr></table></td></tr><tr>";
    if ($action != 'ShowDuplicates')
	{
		$form .= "<td scope='col'>&nbsp;</td>";
	}

	require_once('include/formbase.php');

	if(isset($_POST['return_action']) && $_POST['return_action'] == 'SubPanelViewer') {
		$_POST['return_action'] = 'DetailView';
	} 
	
	if(isset($_POST['return_action']) && $_POST['return_action'] == 'DetailView' && empty($_REQUEST['return_id'])) {
		unset($_POST['return_action']);
	}
		
   $form .= getPostToForm();
	
	if(isset($rows[0])){
		foreach ($rows[0] as $key=>$value){
			if($key != 'id'){
			   $form .= "<td scope='col' >". $mod_strings[$mod_strings['db_'.$key]]. "</td>";
			}
		}
		$form .= "</tr>";
	}
	$rowColor = 'oddListRowS1';
	foreach($rows as $row){

		$form .= "<tr class='$rowColor'>";
		if ($action != 'ShowDuplicates')
		{
			$form .= "<td width='1%' nowrap='nowrap'><a href='#' onClick=\"document.forms['dupContacts'].selectedContact.value='${row['id']}';document.forms['dupContacts'].submit() \">[${app_strings['LBL_SELECT_BUTTON_LABEL']}]</a>&nbsp;&nbsp;</td>\n";
		}
		$wasSet = false;

		foreach ($row as $key=>$value){
				if($key != 'id'){
					if(isset($_POST['popup']) && $_POST['popup']==true){
						$form .= "<td scope='row'><a  href='#' onclick=\"window.opener.location='index.php?module=Contacts&action=DetailView&record=${row['id']}'\">$value</a></td>\n";
					}
					else if(!$wasSet){
						$form .= "<td scope='row'><a target='_blank' href='index.php?module=Contacts&action=DetailView&record=${row['id']}'>$value</a></td>\n";
						$wasSet = true;
					}else{
					    $form .= "<td><a target='_blank' href='index.php?module=Contacts&action=DetailView&record=${row['id']}'>$value</a></td>\n";
					}
				}
		}

		if($rowColor == 'evenListRowS1'){
			$rowColor = 'oddListRowS1';
		}else{
			 $rowColor = 'evenListRowS1';
		}
		$form .= "</tr>";
	}
	$form .= "<tr class='pagination'><td colspan='$cols'><table width='100%' cellspacing='0' cellpadding='0' border='0'><tr><td>";
	if ($action == 'ShowDuplicates')
	{
		$form .= "<input title='${app_strings['LBL_SAVE_BUTTON_TITLE']}' accessKey='${app_strings['LBL_SAVE_BUTTON_KEY']}' class='button' onclick=\"this.form.action.value='Save';\" type='submit' name='button' value='  ${app_strings['LBL_SAVE_BUTTON_LABEL']}  '>\n";
        if (!empty($_REQUEST['return_module']) && !empty($_REQUEST['return_action']) && !empty($_REQUEST['return_id']))
            $form .= "<input title='${app_strings['LBL_CANCEL_BUTTON_TITLE']}' accessKey='${app_strings['LBL_CANCEL_BUTTON_KEY']}' class='button' onclick=\"this.form.module.value=".$_REQUEST['return_module'].";this.form.action.value=".$_REQUEST['return_action'].";this.form.record.value=".$_REQUEST['return_id']."'\" type='submit' name='button' value='  ${app_strings['LBL_CANCEL_BUTTON_LABEL']}  '>";
        else if (!empty($_POST['return_module']) && !empty($_POST['return_action']))
            $form .= "<input title='${app_strings['LBL_CANCEL_BUTTON_TITLE']}' accessKey='${app_strings['LBL_CANCEL_BUTTON_KEY']}' class='button' onclick=\"this.form.module.value=".$_POST['return_module'].";this.form.action.value=". $_POST['return_action'].";'\" type='submit' name='button' value='  ${app_strings['LBL_CANCEL_BUTTON_LABEL']}  '>";
        else
            $form .= "<input title='${app_strings['LBL_CANCEL_BUTTON_TITLE']}' accessKey='${app_strings['LBL_CANCEL_BUTTON_KEY']}' class='button' onclick=\"this.form.action.value='ListView';\" type='submit' type='submit' name='button' value='  ${app_strings['LBL_CANCEL_BUTTON_LABEL']}  '>";
    }
	else
	{
		$form .= "<input type='submit' class='button' name='ContinueContact' value='${mod_strings['LNK_NEW_CONTACT']}'></form>";
	}
    $form .= "</td></tr></table></td></tr></table>";
	return $form;





}
function getWideFormBody($prefix, $mod='',$formname='',  $contact = '', $portal = true){
	
	if(!ACLController::checkAccess('Contacts', 'edit', true)){
		return '';
	}
	
	if(empty($contact)){
		$contact = new Contact();
	}

	global $mod_strings;
	$temp_strings = $mod_strings;
	if(!empty($mod)){
		global $current_language;
		$mod_strings = return_module_language($current_language, $mod);
	}
	global $app_strings;
	global $current_user;
	global $app_list_strings;
	$primary_address_country_options = get_select_options_with_id($app_list_strings['countries_dom'], $contact->primary_address_country);
	$lbl_required_symbol = $app_strings['LBL_REQUIRED_SYMBOL'];
	$lbl_first_name = $mod_strings['LBL_FIRST_NAME'];
	$lbl_last_name = $mod_strings['LBL_LAST_NAME'];
	$lbl_phone = $mod_strings['LBL_OFFICE_PHONE'];
	$lbl_address =  $mod_strings['LBL_PRIMARY_ADDRESS'];

	if (isset($contact->assigned_user_id)) {
		$user_id=$contact->assigned_user_id;
	} else {
		$user_id = $current_user->id;
	}

	//Retrieve Email address and set email1, email2
	$sugarEmailAddress = new SugarEmailAddress();
	$sugarEmailAddress->handleLegacyRetrieve($contact);
  	if(!isset($contact->email1)){
    	$contact->email1 = '';
    }
    if(!isset($contact->email2)){
    	$contact->email2 = '';
    }
    if(!isset($contact->email_opt_out)){
    	$contact->email_opt_out = '';
    }
	$lbl_email_address = $mod_strings['LBL_EMAIL_ADDRESS'];
	$salutation_options=get_select_options_with_id($app_list_strings['salutation_dom'], $contact->salutation);
	
	if (isset($contact->lead_source)) {
		$lead_source_options=get_select_options_with_id($app_list_strings['lead_source_dom'], $contact->lead_source);
	} else {
		$lead_source_options=get_select_options_with_id($app_list_strings['lead_source_dom'], '');
	}

	$form="";


	if ($formname == 'ConvertProspect') {
		$lead_source_label = "<td scope='row'>&nbsp;</td>";
		$lead_source_field = "<td >&nbsp;</td>";
	} else {
		$lead_source_label = "<td scope='row' nowrap>${mod_strings['LBL_LEAD_SOURCE']}</td>";
		$lead_source_field = "<td ><select name='${prefix}lead_source'>$lead_source_options</select></td>";
	}


global $timedate;
$birthdate = '';
if(!empty($_REQUEST['birthdate'])){
    	$birthdate=$_REQUEST['birthdate'];
   }


$jsCalendarImage = SugarThemeRegistry::current()->getImageURL('jscalendar.gif');	
$ntc_date_format = $timedate->get_user_date_format();
$cal_dateformat = $timedate->get_cal_date_format();
$lbl_required_symbol = $app_strings['LBL_REQUIRED_SYMBOL'];

$form .= <<<EOQ
		<input type="hidden" name="${prefix}record" value="">
		<input type="hidden" name="${prefix}assigned_user_id" value='${user_id}'>
		<table border='0' celpadding="0" cellspacing="0" width='100%'>
		<tr>
		<td nowrap scope='row'>$lbl_first_name</td>
		<td scope='row'>$lbl_last_name&nbsp;<span class="required">$lbl_required_symbol</span></td>
		<td scope='row' nowrap>${mod_strings['LBL_TITLE']}</td>
		<td scope='row' nowrap>${mod_strings['LBL_DEPARTMENT']}</td>
		</tr>
		<tr>
		<td ><select name='${prefix}salutation'>$salutation_options</select>&nbsp;<input name="${prefix}first_name" type="text" value="{$contact->first_name}"></td>
		<td ><input name='${prefix}last_name' type="text" value="{$contact->last_name}"></td>
		<td  nowrap><input name='${prefix}title' type="text" value="{$contact->title}"></td>
		<td  nowrap><input name='${prefix}department' type="text" value="{$contact->department}"></td>
		</tr>
		<tr>
		<td nowrap colspan='4' scope='row'>$lbl_address</td>
		</tr>

		<tr>
		<td nowrap colspan='4' ><textarea cols='80' rows='2' name='${prefix}primary_address_street'>{$contact->primary_address_street}</textarea></td>
		</tr>

		<tr>
		<td scope='row'>${mod_strings['LBL_CITY']}</td>
		<td scope='row'>${mod_strings['LBL_STATE']}</td>
		<td scope='row'>${mod_strings['LBL_POSTAL_CODE']}</td>
		<td scope='row'>${mod_strings['LBL_COUNTRY']}</td>
		</tr>

		<tr>
		<td ><input name='${prefix}primary_address_city'  maxlength='100' value='{$contact->primary_address_city}'></td>
		<td ><input name='${prefix}primary_address_state'  maxlength='100' value='{$contact->primary_address_state}'></td>
		<td ><input name='${prefix}primary_address_postalcode'  maxlength='100' value='{$contact->primary_address_postalcode}'></td>
		<td ><input name='${prefix}primary_address_country'  maxlength='100' value='{$contact->primary_address_country}'></td>
		</tr>


		<tr>
		<td nowrap scope='row'>$lbl_phone</td>
		<td nowrap scope='row'>${mod_strings['LBL_MOBILE_PHONE']}</td>
		<td nowrap scope='row'>${mod_strings['LBL_FAX_PHONE']}</td>
		<td nowrap scope='row'>${mod_strings['LBL_HOME_PHONE']}</td>
		</tr>

		<tr>
		<td nowrap ><input name='${prefix}phone_work' type="text" value="{$contact->phone_work}"></td>
		<td nowrap ><input name='${prefix}phone_mobile' type="text" value="{$contact->phone_mobile}"></td>
		<td nowrap ><input name='${prefix}phone_fax' type="text" value="{$contact->phone_fax}"></td>
		<td nowrap ><input name='${prefix}phone_home' type="text" value="{$contact->phone_home}"></td>
		</tr>

		<tr>
		<td scope='row' nowrap>${mod_strings['LBL_OTHER_PHONE']}</td>
		$lead_source_label

		<td scope="row">${mod_strings['LBL_BIRTHDATE']}&nbsp;</td>
		</tr>


		<tr>
		<td  nowrap><input name='${prefix}phone_other' type="text" value="{$contact->phone_other}"></td>
		$lead_source_field

		<td  nowrap>
			<input name='{$prefix}birthdate' onblur="parseDate(this, '$cal_dateformat');" size='12' maxlength='10' id='${prefix}jscal_field' type="text" value="{$birthdate}">&nbsp;
			<img src="{$jsCalendarImage}" alt="{$app_strings['LBL_ENTER_DATE']}"  id="${prefix}jscal_trigger" align="absmiddle">
		</td>		
		</tr>

EOQ;

$form .= $sugarEmailAddress->getEmailAddressWidgetEditView($contact->id, $_REQUEST['action']=='ConvertLead'?'Leads':'Contacts', false, 'include/SugarEmailAddress/templates/forWideFormBodyView.tpl');

require_once('include/SugarFields/Fields/Text/SugarFieldText.php');
$sugarfield = new SugarFieldText('Text');
$description_text = $sugarfield->getClassicEditView('description', $contact->description, $prefix, true);

$form .= <<<EOQ
		<tr>
		<td nowrap colspan='4' scope='row'>${mod_strings['LBL_DESCRIPTION']}</td>
		</tr>
		<tr>
		<td nowrap colspan='4' >{$description_text}</td>
		</tr>
EOQ;



	//carry forward custom lead fields common to contacts during Lead Conversion
	$tempContact = new Contact();

	if (method_exists($contact, 'convertCustomFieldsForm')) $contact->convertCustomFieldsForm($form, $tempContact, $prefix);
	unset($tempContact);

$form .= <<<EOQ
		</table>
		
		<input type='hidden' name='${prefix}alt_address_street'  value='{$contact->alt_address_street}'>
		<input type='hidden' name='${prefix}alt_address_city' value='{$contact->alt_address_city}'><input type='hidden' name='${prefix}alt_address_state'   value='{$contact->alt_address_state}'><input type='hidden' name='${prefix}alt_address_postalcode'   value='{$contact->alt_address_postalcode}'><input type='hidden' name='${prefix}alt_address_country'  value='{$contact->alt_address_country}'>
		<input type='hidden' name='${prefix}do_not_call'  value='{$contact->do_not_call}'>
		<input type='hidden' name='${prefix}email_opt_out'  value='{$contact->email_opt_out}'>
EOQ;

	if ($portal == true){
		if (isset($contact->portal_name)) {
			$form.="<input type='hidden' name='${prefix}portal_name'  value='{$contact->portal_name}'>";
		} else {
			$form.="<input type='hidden' name='${prefix}portal_name'  value=''>";
		}
		if (isset($contact->portal_app)) {
			$form.="<input type='hidden' name='${prefix}portal_app'  value='{$contact->portal_app}'>";
		} else {
			$form.="<input type='hidden' name='${prefix}portal_app'  value=''>";
		}


		if(!empty($contact->portal_name) && !empty($contact->portal_app)){
			$form .= "<input name='${prefix}portal_active' type='hidden' size='25'  value='1' >";
		}

	    if(isset($contact->portal_password)){
	        $form.="<input type='password' name='${prefix}portal_password1'  value='{$contact->portal_password}'>";
	        $form.="<input type='password' name='${prefix}portal_password'  value='{$contact->portal_password}'>";
	        $form .= "<input name='${prefix}old_portal_password' type='hidden' size='25'  value='{$contact->portal_password}' >";
	    }else{
	        $form.="<input type='password' name='${prefix}portal_password1'  value=''>";
	        $form.="<input type='password' name='${prefix}portal_password'  value=''>";
	        $form .= "<input name='${prefix}old_portal_password' type='hidden' size='25'  value='' >";
	    }
	}
	
$form .= <<<EOQ
			<script type="text/javascript">
				Calendar.setup ({
				inputField : "{$prefix}jscal_field", daFormat : "$cal_dateformat", ifFormat : "$cal_dateformat", showsTime : false, button : "{$prefix}jscal_trigger", singleClick : true, step : 1, weekNumbers:false
				});
			</script>
EOQ;
	
	
	
	$javascript = new javascript();
	$javascript->setFormName($formname);
	$javascript->setSugarBean(new Contact());
	$javascript->addField('email1','false',$prefix);
	$javascript->addField('email2','false',$prefix);
	$javascript->addRequiredFields($prefix);

	$form .=$javascript->getScript();
	$mod_strings = $temp_strings;
	
  
	return $form;
}

function getFormBody($prefix, $mod='', $formname=''){
	if(!ACLController::checkAccess('Contacts', 'edit', true)){
		return '';
	}
global $mod_strings;
$temp_strings = $mod_strings;
if(!empty($mod)){
	global $current_language;
	$mod_strings = return_module_language($current_language, $mod);
}
		global $app_strings;
		global $current_user;
		$lbl_required_symbol = $app_strings['LBL_REQUIRED_SYMBOL'];
		$lbl_first_name = $mod_strings['LBL_FIRST_NAME'];
		$lbl_last_name = $mod_strings['LBL_LAST_NAME'];
		$lbl_phone = $mod_strings['LBL_PHONE'];
		$user_id = $current_user->id;
		$lbl_email_address = $mod_strings['LBL_EMAIL_ADDRESS'];
if ($formname == 'EmailEditView')
{
		$form = <<<EOQ
		<input type="hidden" name="${prefix}record" value="">
		<input type="hidden" name="${prefix}email2" value="">
		<input type="hidden" name="${prefix}phone_work" value="">
		<input type="hidden" name="${prefix}assigned_user_id" value='${user_id}'>
		$lbl_first_name<br>
		<input name="${prefix}first_name" type="text" value="" size=10><br>
		$lbl_last_name&nbsp;<span class="required">$lbl_required_symbol</span><br>
		<input name='${prefix}last_name' type="text" value="" size=10><br>
		$lbl_email_address&nbsp;<span class="required">$lbl_required_symbol</span><br>
		<input name='${prefix}email1' type="text" value=""><br><br>

EOQ;
}
else
{
		$form = <<<EOQ
		<input type="hidden" name="${prefix}record" value="">
		<input type="hidden" name="${prefix}email2" value="">
		<input type="hidden" name="${prefix}assigned_user_id" value='${user_id}'>
		$lbl_first_name<br>
		<input name="${prefix}first_name" type="text" value=""><br>
		$lbl_last_name&nbsp;<span class="required">$lbl_required_symbol</span><br>
		<input name='${prefix}last_name' type="text" value=""><br>
		$lbl_phone<br>
		<input name='${prefix}phone_work' type="text" value=""><br>
		$lbl_email_address<br>
		<input name='${prefix}email1' type="text" value=""><br><br>

EOQ;
}


$javascript = new javascript();
$javascript->setFormName($formname);
$javascript->setSugarBean(new Contact());
$javascript->addField('email1','false',$prefix);
$javascript->addRequiredFields($prefix);

$form .=$javascript->getScript();
$mod_strings = $temp_strings;
return $form;

}
function getForm($prefix, $mod=''){
	if(!ACLController::checkAccess('Contacts', 'edit', true)){
		return '';
	}
if(!empty($mod)){
	global $current_language;
	$mod_strings = return_module_language($current_language, $mod);
}else global $mod_strings;
global $app_strings;

$lbl_save_button_title = $app_strings['LBL_SAVE_BUTTON_TITLE'];
$lbl_save_button_key = $app_strings['LBL_SAVE_BUTTON_KEY'];
$lbl_save_button_label = $app_strings['LBL_SAVE_BUTTON_LABEL'];


$the_form = get_left_form_header($mod_strings['LBL_NEW_FORM_TITLE']);
$the_form .= <<<EOQ

		<form name="${prefix}ContactSave" onSubmit="return check_form('${prefix}ContactSave')" method="POST" action="index.php">
			<input type="hidden" name="${prefix}module" value="Contacts">
			<input type="hidden" name="${prefix}action" value="Save">
EOQ;
$the_form .= $this->getFormBody($prefix,'Contacts', "${prefix}ContactSave");
$the_form .= <<<EOQ
		<input title="$lbl_save_button_title" accessKey="$lbl_save_button_key" class="button" type="submit" name="${prefix}button" value="  $lbl_save_button_label  " >
		</form>

EOQ;
$the_form .= get_left_form_footer();
$the_form .= get_validate_record_js();

return $the_form;


}


function handleSave($prefix, $redirect=true, $useRequired=false){
	global $theme, $current_user;
	
	
	
    
	require_once('include/formbase.php');
	
	global $timedate;

	$focus = new Contact();

	if($useRequired &&  !checkRequired($prefix, array_keys($focus->required_fields))){
		return null;
	}
	
	if (!empty($_POST[$prefix.'new_reports_to_id'])) {
		$focus->retrieve($_POST[$prefix.'new_reports_to_id']);
		$focus->reports_to_id = $_POST[$prefix.'record'];
	} else {

        $focus = populateFromPost($prefix, $focus);
        if(!empty($focus->portal_password) && $focus->portal_password != $_POST[$prefix.'old_portal_password']){
            $focus->portal_password = md5($focus->portal_password);
        }
		if (!isset($_POST[$prefix.'email_opt_out'])) $focus->email_opt_out = 0;
		if (!isset($_POST[$prefix.'do_not_call'])) $focus->do_not_call = 0;

	}
	if(!$focus->ACLAccess('Save')){
			ACLController::displayNoAccess(true);
			sugar_cleanup(true);
	}
	if($_REQUEST['action'] != 'BusinessCard' && $_REQUEST['action'] != 'ConvertLead' && $_REQUEST['action'] != 'ConvertProspect')
	{
		
		if (!empty($_POST[$prefix.'sync_contact'])){
			 $focus->contacts_users_id = $current_user->id;
		}
		else{
			if (!isset($focus->users))
			{
	      	  	$focus->load_relationship('user_sync');
			}
	      	$focus->contacts_users_id = null;
			$focus->user_sync->delete($focus->id, $current_user->id);
		}
	}

	if (isset($GLOBALS['check_notify'])) {
		$check_notify = $GLOBALS['check_notify'];
	}
	else {
		$check_notify = FALSE;
	}

    
	if (empty($_POST['record']) && empty($_POST['dup_checked'])) {

		$duplicateContacts = $this->checkForDuplicates($prefix);
		if(isset($duplicateContacts)){
			$location='module=Contacts&action=ShowDuplicates';
			$get = '';
			if(isset($_POST['inbound_email_id']) && !empty($_POST['inbound_email_id'])) {
				$get .= '&inbound_email_id='.$_POST['inbound_email_id'];
			}
			
			// Bug 25311 - Add special handling for when the form specifies many-to-many relationships
			if(isset($_POST['relate_to']) && !empty($_POST['relate_to'])) {
				$get .= '&Contactsrelate_to='.$_POST['relate_to'];
			}
			if(isset($_POST['relate_id']) && !empty($_POST['relate_id'])) {
				$get .= '&Contactsrelate_id='.$_POST['relate_id'];
			}

			//add all of the post fields to redirect get string
			foreach ($focus->column_fields as $field)
			{
				if (!empty($focus->$field) && !is_object($focus->$field))
				{
					$get .= "&Contacts$field=".urlencode($focus->$field);
				}
			}

			foreach ($focus->additional_column_fields as $field)
			{
				if (!empty($focus->$field))
				{
					$get .= "&Contacts$field=".urlencode($focus->$field);
				}
			}

			if($focus->hasCustomFields()) {
				foreach($focus->field_defs as $name=>$field) {	
					if (!empty($field['source']) && $field['source'] == 'custom_fields')
					{
						$get .= "&Contacts$name=".urlencode($focus->$name);
					}			    
				}
			}			
			
			
			$emailAddress = new SugarEmailAddress();
			$get .= $emailAddress->getFormBaseURL($focus);

			
			//create list of suspected duplicate contact id's in redirect get string
			$i=0;
			foreach ($duplicateContacts as $contact)
			{
				$get .= "&duplicate[$i]=".$contact['id'];
				$i++;
			}

			//add return_module, return_action, and return_id to redirect get string
			$get .= "&return_module=";
			if(!empty($_POST['return_module'])) $get .= $_POST['return_module'];
			else $get .= "Contacts";
			$get .= "&return_action=";
			if(!empty($_POST['return_action'])) $get .= $_POST['return_action'];
			//else $get .= "DetailView";
			if(!empty($_POST['return_id'])) $get .= "&return_id=".$_POST['return_id'];
			if(!empty($_POST['popup'])) $get .= '&popup='.$_POST['popup'];
			if(!empty($_POST['create'])) $get .= '&create='.$_POST['create'];

			// for InboundEmail flow
			if(!empty($_POST['start'])) $get .= '&start='.$_POST['start'];

			//now redirect the post to modules/Contacts/ShowDuplicates.php
            if (!empty($_POST['is_ajax_call']) && $_POST['is_ajax_call'] == '1')
            {
            	ob_clean();
                $json = getJSONobj();
                $_SESSION['SHOW_DUPLICATES'] = $get;
                echo $json->encode(array('status' => 'dupe', 'get' => $location));
            } else {
                if(!empty($_POST['to_pdf'])) $location .= '&to_pdf='.$_POST['to_pdf'];
                $_SESSION['SHOW_DUPLICATES'] = $get;
                header("Location: index.php?$location");
            }
            return null;
		}
	}

	global $current_user;
	if(is_admin($current_user)){
		if (!isset($_POST[$prefix.'portal_active'])) $focus->portal_active = '0';
		//if no password is set set account to inactive for portal
		if(empty($_POST[$prefix.'portal_name']))$focus->portal_active = '0';

	}

	///////////////////////////////////////////////////////////////////////////////
	////	INBOUND EMAIL HANDLING
	///////////////////////////////////////////////////////////////////////////////
	if(isset($_REQUEST['inbound_email_id']) && !empty($_REQUEST['inbound_email_id'])) {
		// fake this case like it's already saved.
		$focus->save($check_notify);
		
		$email = new Email();
		$email->retrieve($_REQUEST['inbound_email_id']);
		$email->parent_type = 'Contacts';
		$email->parent_id = $focus->id;
		$email->assigned_user_id = $current_user->id;
		$email->status = 'read';
		$email->save();
		$email->load_relationship('contacts');
		$email->contacts->add($focus->id);

		header("Location: index.php?&module=Emails&action=EditView&type=out&inbound_email_id=".$_REQUEST['inbound_email_id']."&parent_id=".$email->parent_id."&parent_type=".$email->parent_type.'&start='.$_REQUEST['start'].'&assigned_user_id='.$current_user->id);
		exit();
	}
	////	END INBOUND EMAIL HANDLING
	///////////////////////////////////////////////////////////////////////////////

	$focus->save($check_notify);
	$return_id = $focus->id;
    
	$GLOBALS['log']->debug("Saved record with id of ".$return_id);

    if (!empty($_POST['is_ajax_call']) && $_POST['is_ajax_call'] == '1') {
        $json = getJSONobj();
        echo $json->encode(array('status' => 'success',
                                 'get' => ''));
    	$trackerManager = TrackerManager::getInstance();
        $timeStamp = TimeDate::getInstance()->nowDb();
        if($monitor = $trackerManager->getMonitor('tracker')){ 
	        $monitor->setValue('action', 'detailview');
	        $monitor->setValue('user_id', $GLOBALS['current_user']->id);
	        $monitor->setValue('module_name', 'Contacts');
	        $monitor->setValue('date_modified', $timeStamp);
	        $monitor->setValue('visible', 1);
	
	        if (!empty($this->bean->id)) {
	            $monitor->setValue('item_id', $return_id);
	            $monitor->setValue('item_summary', $focus->get_summary_text());
	        }
			$trackerManager->saveMonitor($monitor, true, true);
		}
        return null;
    }

	if(isset($_POST['popup']) && $_POST['popup'] == 'true') {
		$get = '&module=';
		if(!empty($_POST['return_module'])) $get .= $_POST['return_module'];
		else $get .= 'Contacts';
		$get .= '&action=';
		if(!empty($_POST['return_action'])) $get .= $_POST['return_action'];
		else $get .= 'Popup';
		if(!empty($_POST['return_id'])) $get .= '&return_id='.$_POST['return_id'];
		if(!empty($_POST['popup'])) $get .= '&popup='.$_POST['popup'];
		if(!empty($_POST['create'])) $get .= '&create='.$_POST['create'];
		if(!empty($_POST['to_pdf'])) $get .= '&to_pdf='.$_POST['to_pdf'];
		$get .= '&first_name=' . urlencode($focus->first_name);
		$get .= '&last_name=' . urlencode($focus->last_name);
		$get .= '&query=true';
		header("Location: index.php?$get");
		return;
	}

	if($redirect){
		$this->handleRedirect($return_id);
	}else{
		return $focus;
	}
}

function handleRedirect($return_id){
	if(isset($_POST['return_module']) && $_POST['return_module'] != "") {
		$return_module = $_POST['return_module'];
	}
	else {
		$return_module = "Contacts";
	}

	if(isset($_POST['return_action']) && $_POST['return_action'] != "") {
		if($_REQUEST['return_module'] == 'Emails') {
			$return_action = $_REQUEST['return_action'];
		}
		// if we create a new record "Save", we want to redirect to the DetailView
		elseif($_REQUEST['action'] == "Save" && $_REQUEST['return_module'] != "Home") {
			$return_action = 'DetailView';
		} else {
			// if we "Cancel", we go back to the list view.
			$return_action = $_REQUEST['return_action'];
		}
	}
	else {
		$return_action = "DetailView";
	}

	if(isset($_POST['return_id']) && $_POST['return_id'] != "") {
        $return_id = $_POST['return_id'];
	}

	header("Location: index.php?action=$return_action&module=$return_module&record=$return_id");
}

}


?>
