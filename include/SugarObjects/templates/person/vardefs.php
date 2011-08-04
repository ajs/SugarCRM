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

$vardefs =array(
'fields'=> array(
'salutation' =>
		array (
			'name' => 'salutation',
			'vname' => 'LBL_SALUTATION',
			'type' => 'enum',
			'options' => 'salutation_dom',
			'massupdate' => false,
			'len' => '255',
			'comment' => 'Contact salutation (e.g., Mr, Ms)'            
		),
'first_name' =>
		array (
			'name' => 'first_name',
			'vname' => 'LBL_FIRST_NAME',
			'type' => 'varchar',
			'len' => '100',
			'unified_search' => true,
			'comment' => 'First name of the contact',
            'merge_filter' => 'selected',     
            
		),
	'last_name' =>
		array (
			'name' => 'last_name',
			'vname' => 'LBL_LAST_NAME',
			'type' => 'varchar',
			'len' => '100',
			'unified_search' => true, 
			'comment' => 'Last name of the contact',
            'merge_filter' => 'selected',
            'required'=>true,
            'importable' => 'required',
		),
	'name' =>
		array (
			'name' => 'name',
			'rname' => 'name',
			'vname' => 'LBL_NAME',
			'type' => 'name',
			'fields' => array('first_name', 'last_name'),
			'sort_on' => 'last_name',
			'source' => 'non-db',
			'group'=>'last_name',
			'len' => '255',
			'db_concat_fields'=> array(0=>'first_name', 1=>'last_name'),
            'importable' => 'false',
		),
	'full_name' =>
		array (
			'name' => 'full_name',
			'rname' => 'full_name',
			'vname' => 'LBL_NAME',
			'type' => 'fullname',
			'fields' => array('first_name', 'last_name'),
			'sort_on' => 'last_name',
			'source' => 'non-db',
			'group'=>'last_name',
			'len' => '510',
			'db_concat_fields'=> array(0=>'first_name', 1=>'last_name'),
			'studio' => array('listview' => false),
		),
	'title' =>
		array (
			'name' => 'title',
			'vname' => 'LBL_TITLE',
			'type' => 'varchar',
			'len' => '100',
			'comment' => 'The title of the contact'
		),
	'department' =>
		array (
			'name' => 'department',
			'vname' => 'LBL_DEPARTMENT',
			'type' => 'varchar',
			'len' => '255',
			'comment' => 'The department of the contact',
            'merge_filter' => 'enabled',
		),
		'do_not_call' =>
		array (
			'name' => 'do_not_call',
			'vname' => 'LBL_DO_NOT_CALL',
			'type' => 'bool',
			'default' => '0',
			'audited'=>true,
			'comment' => 'An indicator of whether contact can be called'
		),
	'phone_home' =>
		array (
			'name' => 'phone_home',
			'vname' => 'LBL_HOME_PHONE',
			'type' => 'phone',
			'dbType' => 'varchar',
			'len' => 100,
			'unified_search' => true, 
			'comment' => 'Home phone number of the contact',
            'merge_filter' => 'enabled',
		),
	'phone_mobile' =>
		array (
			'name' => 'phone_mobile',
			'vname' => 'LBL_MOBILE_PHONE',
			'type' => 'phone',
			'dbType' => 'varchar',
			'len' => 100,
			'unified_search' => true,
			'comment' => 'Mobile phone number of the contact',
            'merge_filter' => 'enabled',
		),
	'phone_work' =>
		array (
			'name' => 'phone_work',
			'vname' => 'LBL_OFFICE_PHONE',
			'type' => 'phone',
			'dbType' => 'varchar',
			'len' => 100,
			'audited'=>true,
			'unified_search' => true,
			'comment' => 'Work phone number of the contact',
            'merge_filter' => 'enabled',
		),
	'phone_other' =>
		array (
			'name' => 'phone_other',
			'vname' => 'LBL_OTHER_PHONE',
			'type' => 'phone',
			'dbType' => 'varchar',
			'len' => 100,
			'unified_search' => true,
			'comment' => 'Other phone number for the contact',
            'merge_filter' => 'enabled',
		),
	'phone_fax' =>
		array (
			'name' => 'phone_fax',
			'vname' => 'LBL_FAX_PHONE',
			'type' => 'phone',
			'dbType' => 'varchar',
			'len' => 100,
			'unified_search' => true,
			'comment' => 'Contact fax number',
            'merge_filter' => 'enabled',
		),
	'email1' => 
		array(
			'name'		=> 'email1',
			'vname'		=> 'LBL_EMAIL_ADDRESS',
			'type'		=> 'varchar',
			'function'	=> array(
				'name'		=> 'getEmailAddressWidget',
				'returns'	=> 'html'),
			'source'	=> 'non-db',
			'group'=>'email1',
            'merge_filter' => 'enabled',
		    'studio' => array('editField' => true),
		),
	'email2' => 
		array(
			'name'		=> 'email2',
			'vname'		=> 'LBL_OTHER_EMAIL_ADDRESS',
			'type'		=> 'varchar',
			'function'	=> array(
				'name'		=> 'getEmailAddressWidget',
				'returns'	=> 'html'),
			'source'	=> 'non-db',
			'group'=>'email2',
            'merge_filter' => 'enabled',
		    'studio' => 'false',
		),
    'invalid_email' => 
		array(
			'name'		=> 'invalid_email',
			'vname'     => 'LBL_INVALID_EMAIL',
			'source'	=> 'non-db',
			'type'		=> 'bool',
		    'massupdate' => false,
		    'studio' => 'false',
		),    
    'email_opt_out' => 
		array(
			'name'		=> 'email_opt_out',
			'vname'     => 'LBL_EMAIL_OPT_OUT',
			'source'	=> 'non-db',
			'type'		=> 'bool',
		    'massupdate' => false,
			'studio'=>'false',
		),
	'primary_address_street' =>
		array (
			'name' => 'primary_address_street',
			'vname' => 'LBL_PRIMARY_ADDRESS_STREET',
			'type' => 'varchar',
			'len' => '150',
			'group'=>'primary_address',
			'comment' => 'Street address for primary address',
            'merge_filter' => 'enabled',
		),
	'primary_address_street_2' =>
		array (
			'name' => 'primary_address_street_2',
			'vname' => 'LBL_PRIMARY_ADDRESS_STREET_2',
			'type' => 'varchar',
			'len' => '150',
			'source' => 'non-db',
		),
	'primary_address_street_3' =>
		array (
			'name' => 'primary_address_street_3',
			'vname' => 'LBL_PRIMARY_ADDRESS_STREET_3',
			'type' => 'varchar',
			'len' => '150',
			'source' => 'non-db',
		),		
	'primary_address_city' =>
		array (
			'name' => 'primary_address_city',
			'vname' => 'LBL_PRIMARY_ADDRESS_CITY',
			'type' => 'varchar',
			'len' => '100',
			'group'=>'primary_address',
			'comment' => 'City for primary address',
            'merge_filter' => 'enabled',
		),
	'primary_address_state' =>
		array (
			'name' => 'primary_address_state',
			'vname' => 'LBL_PRIMARY_ADDRESS_STATE',
			'type' => 'varchar',
			'len' => '100',
			'group'=>'primary_address',
			'comment' => 'State for primary address',
            'merge_filter' => 'enabled',
		),
	'primary_address_postalcode' =>
		array (
			'name' => 'primary_address_postalcode',
			'vname' => 'LBL_PRIMARY_ADDRESS_POSTALCODE',
			'type' => 'varchar',
			'len' => '20',
			'group'=>'primary_address',
			'comment' => 'Postal code for primary address',
            'merge_filter' => 'enabled',
            
		),
	'primary_address_country' =>
		array (
			'name' => 'primary_address_country',
			'vname' => 'LBL_PRIMARY_ADDRESS_COUNTRY',
			'type' => 'varchar',
			'group'=>'primary_address',
			'comment' => 'Country for primary address',
            'merge_filter' => 'enabled',
		),
	'alt_address_street' =>
		array (
			'name' => 'alt_address_street',
			'vname' => 'LBL_ALT_ADDRESS_STREET',
			'type' => 'varchar',
			'len' => '150',
			'group'=>'alt_address',
			'comment' => 'Street address for alternate address',
            'merge_filter' => 'enabled',
		),
	'alt_address_street_2' =>
		array (
			'name' => 'alt_address_street_2',
			'vname' => 'LBL_ALT_ADDRESS_STREET_2',
			'type' => 'varchar',
			'len' => '150',
			'source' => 'non-db',
		),
	'alt_address_street_3' =>
		array (
			'name' => 'alt_address_street_3',
			'vname' => 'LBL_ALT_ADDRESS_STREET_3',
			'type' => 'varchar',
			'len' => '150',
			'source' => 'non-db',
		),			
	'alt_address_city' =>
		array (
			'name' => 'alt_address_city',
			'vname' => 'LBL_ALT_ADDRESS_CITY',
			'type' => 'varchar',
			'len' => '100',
			'group'=>'alt_address',
			'comment' => 'City for alternate address',
            'merge_filter' => 'enabled',
		),
	'alt_address_state' =>
		array (
			'name' => 'alt_address_state',
			'vname' => 'LBL_ALT_ADDRESS_STATE',
			'type' => 'varchar',
			'len' => '100',
			'group'=>'alt_address',
			'comment' => 'State for alternate address',
            'merge_filter' => 'enabled',
		),
	'alt_address_postalcode' =>
		array (
			'name' => 'alt_address_postalcode',
			'vname' => 'LBL_ALT_ADDRESS_POSTALCODE',
			'type' => 'varchar',
			'len' => '20',
			'group'=>'alt_address',
			'comment' => 'Postal code for alternate address',
            'merge_filter' => 'enabled',
		),
	'alt_address_country' =>
		array (
			'name' => 'alt_address_country',
			'vname' => 'LBL_ALT_ADDRESS_COUNTRY',
			'type' => 'varchar',
			'group'=>'alt_address',
			'comment' => 'Country for alternate address',
            'merge_filter' => 'enabled',
		),
		'assistant' =>
		array (
			'name' => 'assistant',
			'vname' => 'LBL_ASSISTANT',
			'type' => 'varchar',
			'len' => '75',
			'unified_search' => true,
			'comment' => 'Name of the assistant of the contact',
            'merge_filter' => 'enabled',
		),
	'assistant_phone' =>
		array (
			'name' => 'assistant_phone',
			'vname' => 'LBL_ASSISTANT_PHONE',
			'type' => 'phone',
			'dbType' => 'varchar',
			'len' => 100,
			'group'=>'assistant',
			'unified_search' => true,
			'comment' => 'Phone number of the assistant of the contact',
            'merge_filter' => 'enabled',
		),
	'email_addresses_primary' => 
		array (
            'name' => 'email_addresses_primary',
            'type' => 'link',
            'relationship' => strtolower($object_name).'_email_addresses_primary',
            'source' => 'non-db',
            'vname' => 'LBL_EMAIL_ADDRESS_PRIMARY',
            'duplicate_merge' => 'disabled',
		),
    'email_addresses' =>
		array (
            'name' => 'email_addresses',
            'type' => 'link',
            'relationship' => strtolower($object_name).'_email_addresses',
            'source' => 'non-db',
            'vname' => 'LBL_EMAIL_ADDRESSES',
            'reportable'=>false,
            'unified_search' => true,
            'rel_fields' => array('primary_address' => array('type'=>'bool')),
        ),
), 
'relationships'=>array(
    strtolower($module).'_email_addresses' => 
    array(
        'lhs_module'=> $module, 'lhs_table'=> strtolower($module), 'lhs_key' => 'id',
        'rhs_module'=> 'EmailAddresses', 'rhs_table'=> 'email_addresses', 'rhs_key' => 'id',
        'relationship_type'=>'many-to-many',
        'join_table'=> 'email_addr_bean_rel', 'join_key_lhs'=>'bean_id', 'join_key_rhs'=>'email_address_id', 
        'relationship_role_column'=>'bean_module',
        'relationship_role_column_value'=>$module
    ),
    strtolower($module).'_email_addresses_primary' => 
    array('lhs_module'=> $module, 'lhs_table'=> strtolower($module), 'lhs_key' => 'id',
        'rhs_module'=> 'EmailAddresses', 'rhs_table'=> 'email_addresses', 'rhs_key' => 'id',
        'relationship_type'=>'many-to-many',
        'join_table'=> 'email_addr_bean_rel', 'join_key_lhs'=>'bean_id', 'join_key_rhs'=>'email_address_id', 
        'relationship_role_column'=>'primary_address', 
        'relationship_role_column_value'=>'1'
    ),
)
);
?>
