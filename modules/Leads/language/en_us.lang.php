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

* Description:  Defines the English language pack for the base application.
* Portions created by SugarCRM are Copyright (C) SugarCRM, Inc.
* All Rights Reserved.
* Contributor(s): ______________________________________..
********************************************************************************/

$mod_strings = array (
    //DON'T CONVERT THESE THEY ARE MAPPINGS
    'db_last_name' => 'LBL_LIST_LAST_NAME',
    'db_first_name' => 'LBL_LIST_FIRST_NAME',
    'db_title' => 'LBL_LIST_TITLE',
    'db_email1' => 'LBL_LIST_EMAIL_ADDRESS',
    'db_account_name' => 'LBL_LIST_ACCOUNT_NAME',
    'db_email2' => 'LBL_LIST_EMAIL_ADDRESS',

    //END DON'T CONVERT
    'ERR_DELETE_RECORD' => 'en_us A record number must be specified to delete the lead.',
    'LBL_ACCOUNT_DESCRIPTION'=> 'Account Description',
    'LBL_ACCOUNT_ID'=>'Account ID',
    'LBL_ACCOUNT_NAME' => 'Account Name:',
    'LBL_ACTIVITIES_SUBPANEL_TITLE'=>'Activities',
    'LBL_ADD_BUSINESSCARD' => 'Add Business Card',
    'LBL_ADDRESS_INFORMATION' => 'Address Information',
    'LBL_ALT_ADDRESS_CITY' => 'Alt Address City',
    'LBL_ALT_ADDRESS_COUNTRY' => 'Alt Address Country',
    'LBL_ALT_ADDRESS_POSTALCODE' => 'Alt Address Postalcode',
    'LBL_ALT_ADDRESS_STATE' => 'Alt Address State',
    'LBL_ALT_ADDRESS_STREET_2' => 'Alt Address Street 2',
    'LBL_ALT_ADDRESS_STREET_3' => 'Alt Address Street 3',
    'LBL_ALT_ADDRESS_STREET' => 'Alt Address Street',
    'LBL_ALTERNATE_ADDRESS' => 'Other Address:',
    'LBL_ALT_ADDRESS' => 'Other Address:',
    'LBL_ANY_ADDRESS' => 'Any Address:',
    'LBL_ANY_EMAIL' => 'Any Email:',
    'LBL_ANY_PHONE' => 'Any Phone:',
    'LBL_ASSIGNED_TO_NAME' => 'Assigned to',
    'LBL_ASSIGNED_TO_ID' => 'Assigned User:',
    'LBL_BACKTOLEADS' => 'Back To Leads',
    'LBL_BUSINESSCARD' => 'Convert Lead',
    'LBL_CITY' => 'City:',
    'LBL_CONTACT_ID' => 'Contact ID',
    'LBL_CONTACT_INFORMATION' => 'Lead Overview',
    'LBL_CONTACT_NAME' => 'Lead Name:',
    'LBL_CONTACT_OPP_FORM_TITLE' => 'Lead-Opportunity:',
    'LBL_CONTACT_ROLE' => 'Role:',
    'LBL_CONTACT' => 'Lead:',
    'LBL_CONVERTED_ACCOUNT'=>'Converted Account:',
    'LBL_CONVERTED_CONTACT' => 'Converted Contact:',
    'LBL_CONVERTED_OPP'=>'Converted Opportunity:',
    'LBL_CONVERTED'=> 'Converted',
    'LBL_CONVERTLEAD_BUTTON_KEY' => 'V',
    'LBL_CONVERTLEAD_TITLE' => 'Convert Lead [Alt+V]',
    'LBL_CONVERTLEAD' => 'Convert Lead',
    'LBL_CONVERTLEAD_WARNING' => 'Warning: The status of the Lead you are about to convert is "Converted". Contact and/or Account records may already have been created from the Lead. If you wish to continue with converting the Lead, click Save. To go back to the Lead without converting it, click Cancel.',
    'LBL_CONVERTLEAD_WARNING_INTO_RECORD' => ' Possible Contact: ',
    'LBL_COUNTRY' => 'Country:',
    'LBL_CREATED_NEW' => 'Created a new',
	'LBL_CREATED_ACCOUNT' => 'Created a new account',
    'LBL_CREATED_CALL' => 'Created a new call',
    'LBL_CREATED_CONTACT' => 'Created a new contact',
    'LBL_CREATED_MEETING' => 'Created a new meeting',
    'LBL_CREATED_OPPORTUNITY' => 'Created a new opportunity',
    'LBL_DEFAULT_SUBPANEL_TITLE' => 'Leads',
    'LBL_DEPARTMENT' => 'Department:',
    'LBL_DESCRIPTION_INFORMATION' => 'Description Information',
    'LBL_DESCRIPTION' => 'Description:',
    'LBL_DO_NOT_CALL' => 'Do Not Call:',
    'LBL_DUPLICATE' => 'Similar Leads',
    'LBL_EMAIL_ADDRESS' => 'Email Address:',
    'LBL_EMAIL_OPT_OUT' => 'Email Opt Out:',
    'LBL_EXISTING_ACCOUNT' => 'Used an existing account',
    'LBL_EXISTING_CONTACT' => 'Used an existing contact',
    'LBL_EXISTING_OPPORTUNITY' => 'Used an existing opportunity',
    'LBL_FAX_PHONE' => 'Fax:',
    'LBL_FIRST_NAME' => 'First Name:',
    'LBL_FULL_NAME' => 'Full Name:',
    'LBL_HISTORY_SUBPANEL_TITLE'=>'History',
    'LBL_HOME_PHONE' => 'Home Phone:',
    'LBL_IMPORT_VCARD' => 'Import vCard',
    'LBL_VCARD' => 'vCard',
    'LBL_IMPORT_VCARDTEXT' => 'Automatically create a new lead by importing a vCard from your file system.',
    'LBL_INVALID_EMAIL'=>'Invalid Email:',
    'LBL_INVITEE' => 'Direct Reports',
    'LBL_LAST_NAME' => 'Last Name:',
    'LBL_LEAD_SOURCE_DESCRIPTION' => 'Lead Source Description:',
    'LBL_LEAD_SOURCE' => 'Lead Source:',
    'LBL_LIST_ACCEPT_STATUS' => 'Accept Status',
    'LBL_LIST_ACCOUNT_NAME' => 'Account Name',
    'LBL_LIST_CONTACT_NAME' => 'Lead Name',
    'LBL_LIST_CONTACT_ROLE' => 'Role',
    'LBL_LIST_DATE_ENTERED' => 'Date Created',
    'LBL_LIST_EMAIL_ADDRESS' => 'Email',
    'LBL_LIST_FIRST_NAME' => 'First Name',
    'LBL_VIEW_FORM_TITLE' => 'Lead View',    
    'LBL_LIST_FORM_TITLE' => 'Lead List',
    'LBL_LIST_LAST_NAME' => 'Last Name',
    'LBL_LIST_LEAD_SOURCE_DESCRIPTION' => 'Lead Source Description',
    'LBL_LIST_LEAD_SOURCE' => 'Lead Source',
    'LBL_LIST_MY_LEADS' => 'My Leads',
    'LBL_LIST_NAME' => 'Name',
    'LBL_LIST_PHONE' => 'Office Phone',
    'LBL_LIST_REFERED_BY' => 'Referred By',
    'LBL_LIST_STATUS' => 'Status',
    'LBL_LIST_TITLE' => 'Title',
    'LBL_MOBILE_PHONE' => 'Mobile:',
    'LBL_MODULE_NAME' => 'Leads',
    'LBL_MODULE_TITLE' => 'Leads: Home',
    'LBL_NAME' => 'Name:',
    'LBL_NEW_FORM_TITLE' => 'New Lead',
    'LBL_NEW_PORTAL_PASSWORD' => 'New Portal Password:',
    'LBL_OFFICE_PHONE' => 'Office Phone:',
    'LBL_OPP_NAME' => 'Opportunity Name:',
    'LBL_OPPORTUNITY_AMOUNT' => 'Opportunity Amount:',
    'LBL_OPPORTUNITY_ID'=>'Opportunity ID',
    'LBL_OPPORTUNITY_NAME' => 'Opportunity Name:',
    'LBL_OTHER_EMAIL_ADDRESS' => 'Other Email:',
    'LBL_OTHER_PHONE' => 'Other Phone:',
    'LBL_PHONE' => 'Phone:',
    'LBL_PORTAL_ACTIVE' => 'Portal Active:',
    'LBL_PORTAL_APP'=> 'Portal Application',
    'LBL_PORTAL_INFORMATION' => 'Portal Information',
    'LBL_PORTAL_NAME' => 'Portal Name:',
    'LBL_PORTAL_PASSWORD_ISSET' => 'Portal Password Is Set:',
    'LBL_POSTAL_CODE' => 'Postal Code:',
    'LBL_STREET' => 'Street',
    'LBL_PRIMARY_ADDRESS_CITY' => 'Primary Address City',
    'LBL_PRIMARY_ADDRESS_COUNTRY' => 'Primary Address Country',
    'LBL_PRIMARY_ADDRESS_POSTALCODE' => 'Primary Address Postalcode',
    'LBL_PRIMARY_ADDRESS_STATE' => 'Primary Address State',
    'LBL_PRIMARY_ADDRESS_STREET_2'=>'Primary Address Street 2',
    'LBL_PRIMARY_ADDRESS_STREET_3'=>'Primary Address Street 3',   
    'LBL_PRIMARY_ADDRESS_STREET' => 'Primary Address Street',
    'LBL_PRIMARY_ADDRESS' => 'Primary Address:',
    'LBL_REFERED_BY' => 'Referred By:',
    'LBL_REPORTS_TO_ID'=>'Reports To ID',
    'LBL_REPORTS_TO' => 'Reports To:',
    'LBL_SALUTATION' => 'Salutation',
    'LBL_MODIFIED'=>'Modified By',
	'LBL_MODIFIED_ID'=>'Modified By Id',
	'LBL_CREATED'=>'Created By',
	'LBL_CREATED_ID'=>'Created By Id',    
    'LBL_SEARCH_FORM_TITLE' => 'Lead Search',
    'LBL_SELECT_CHECKED_BUTTON_LABEL' => 'Select Checked Leads',
    'LBL_SELECT_CHECKED_BUTTON_TITLE' => 'Select Checked Leads',
    'LBL_STATE' => 'State:',
    'LBL_STATUS_DESCRIPTION' => 'Status Description:',
    'LBL_STATUS' => 'Status:',
    'LBL_TITLE' => 'Title:',
    'LNK_IMPORT_VCARD' => 'Create Lead From vCard',
    'LNK_LEAD_LIST' => 'View Leads',
    'LNK_NEW_ACCOUNT' => 'Create Account',
    'LNK_NEW_APPOINTMENT' => 'Create Appointment',
    'LNK_NEW_CONTACT' => 'Create Contact',
    'LNK_NEW_LEAD' => 'Create Lead',
    'LNK_NEW_NOTE' => 'Create Note',
    'LNK_NEW_TASK' => 'Create Task',
    'LNK_NEW_CASE' => 'Create Case',
    'LNK_NEW_CALL' => 'Log Call',
    'LNK_NEW_MEETING' => 'Schedule Meeting',
    'LNK_NEW_OPPORTUNITY' => 'Create Opportunity',
    'LNK_SELECT_ACCOUNT' => ' <b>OR</b> Select Account',
	'LNK_SELECT_ACCOUNTS' => ' <b>OR</b> Select Account',
    'MSG_DUPLICATE' => 'Similar leads have been found. Please check the box of any leads you would like to associate with the Records that will be created from this conversion. Once you are done, please press next.',
    'NTC_COPY_ALTERNATE_ADDRESS' => 'Copy alternate address to primary address',
    'NTC_COPY_PRIMARY_ADDRESS' => 'Copy primary address to alternate address',
    'NTC_DELETE_CONFIRMATION' => 'Are you sure you want to delete this record?',
    'NTC_OPPORTUNITY_REQUIRES_ACCOUNT' => 'Creating an opportunity requires an account.\n Please either create a new one or select an existing one.',
    'NTC_REMOVE_CONFIRMATION' => 'Are you sure you want to remove this lead from this case?',
    'NTC_REMOVE_DIRECT_REPORT_CONFIRMATION' => 'Are you sure you want to remove this record as a direct report?',
    'LBL_CAMPAIGN_LIST_SUBPANEL_TITLE'=>'Campaigns',
    'LBL_TARGET_OF_CAMPAIGNS'=>'Successful Campaign:',
    'LBL_TARGET_BUTTON_LABEL'=>'Targeted',
    'LBL_TARGET_BUTTON_TITLE'=>'Targeted',
    'LBL_TARGET_BUTTON_KEY'=>'T',
    'LBL_CAMPAIGN_ID'=>'Campaign Id',
    'LBL_CAMPAIGN' => 'Campaign:',
  	'LBL_LIST_ASSIGNED_TO_NAME' => 'Assigned User',
    'LBL_PROSPECT_LIST' => 'Prospect List',
    'LBL_CAMPAIGN_LEAD' => 'Campaigns',
    'LBL_BIRTHDATE' => 'Birthdate:',
    'LBL_THANKS_FOR_SUBMITTING_LEAD' =>'Thank You For Your Submission.',
    'LBL_SERVER_IS_CURRENTLY_UNAVAILABLE' =>'We are sorry, the server is currently unavailable, please try again later.',
    'LBL_ASSISTANT_PHONE' => 'Assistant Phone',
    'LBL_ASSISTANT' => 'Assistant',
    'LBL_REGISTRATION' => 'Registration',
    'LBL_MESSAGE' => 'Please enter your information below. Information and/or an account will be created for you pending approval.',
    'LBL_SAVED' => 'Thank you for registering. Your account will be created and someone will contact you shortly.', 
    'LBL_CLICK_TO_RETURN' => 'Return to Portal',
    'LBL_CREATED_USER' => 'Created User',
    'LBL_MODIFIED_USER' => 'Modified User',
    'LBL_CAMPAIGNS' => 'Campaigns',
    'LBL_CAMPAIGNS_SUBPANEL_TITLE' => 'Campaigns',
    'LBL_CONVERT_MODULE_NAME' => 'Module',
    'LBL_CONVERT_REQUIRED' => 'Required',
    'LBL_CONVERT_SELECT' => 'Allow Selection',
    'LBL_CONVERT_COPY' => 'Copy Data',
    'LBL_CONVERT_EDIT' => 'Edit',
    'LBL_CONVERT_DELETE' => 'Delete',
    'LBL_CONVERT_ADD_MODULE' => 'Add Module',
    'LBL_CREATE' => 'Create',
    'LBL_SELECT' => ' <b>OR</b> Select',
	'LBL_WEBSITE' => 'Website',
	'LNK_IMPORT_LEADS' => 'Import Leads',
	'LBL_NOTICE_OLD_LEAD_CONVERT_OVERRIDE' => 'Notice: The current Convert Lead screen contains custom fields. When you customize the Convert Lead screen in Studio for the first time, you will need to add custom fields to the layout, as necessary. The custom fields will not automatically appear in the layout, as they did previously.',
//Convert lead tooltips
	'LBL_MODULE_TIP' 	=> 'The module to create a new record in.',
	'LBL_REQUIRED_TIP' 	=> 'Required modules must be created or selected before the lead can be converted.',
	'LBL_COPY_TIP'		=> 'If checked, fields from the lead will be copied to fields with the same name in the newly created records.',
	'LBL_SELECTION_TIP' => 'Modules with a relate field in Contacts can be selected rather than created during the convert lead process.',
	'LBL_EDIT_TIP'		=> 'Modify the convert layout for this module.',
	'LBL_DELETE_TIP'	=> 'Remove this module from the convert layout.',
);


?>
