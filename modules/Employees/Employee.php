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

 * Description: TODO:  To be written.
 * Portions created by SugarCRM are Copyright (C) SugarCRM, Inc.
 * All Rights Reserved.
 * Contributor(s): ______________________________________..
 ********************************************************************************/


require_once('include/SugarObjects/templates/person/Person.php');

// Employee is used to store customer information.
class Employee extends Person {
	// Stored fields
	var $name = '';
	var $id;
	var $is_admin;
	var $first_name;
	var $last_name;
	var $full_name;
	var $user_name;
	var $title;
	var $description;
	var $department;
	var $reports_to_id;
	var $reports_to_name;
	var $phone_home;
	var $phone_mobile;
	var $phone_work;
	var $phone_other;
	var $phone_fax;
	var $email1;
	var $email2;
	var $address_street;
	var $address_city;
	var $address_state;
	var $address_postalcode;
	var $address_country;
	var $date_entered;
	var $date_modified;
	var $modified_user_id;
	var $created_by;
	var $created_by_name;
	var $modified_by_name;
	var $status;
	var $messenger_id;
	var $messenger_type;
	var $employee_status;
	var $error_string;
	
	var $module_dir = "Employees";


	var $table_name = "users";

	var $object_name = "Employee";
	var $user_preferences;

	var $encodeFields = Array("first_name", "last_name", "description");

	// This is used to retrieve related fields from form posts.
	var $additional_column_fields = Array('reports_to_name');

    

	var $new_schema = true;

	function Employee() {
		parent::Person();
		//$this->setupCustomFields('Employees');
		$this->emailAddress = new SugarEmailAddress();
	}
	
    
	function get_summary_text() {
        $this->_create_proper_name_field();
        return $this->name;	
    }


	function fill_in_additional_list_fields() {
		$this->fill_in_additional_detail_fields();
	}

	function fill_in_additional_detail_fields()
	{
		global $locale;
		$query = "SELECT u1.first_name, u1.last_name from users u1, users u2 where u1.id = u2.reports_to_id AND u2.id = '$this->id' and u1.deleted=0";
		$result =$this->db->query($query, true, "Error filling in additional detail fields") ;

		$row = $this->db->fetchByAssoc($result);
		$GLOBALS['log']->debug("additional detail query results: $row");

		if($row != null)
		{
			$this->reports_to_name = stripslashes($locale->getLocaleFormattedName($row['first_name'], $row['last_name']));
		}
		else
		{
			$this->reports_to_name = '';
		}
	}

	function retrieve_employee_id($employee_name)
	{
		$query = "SELECT id from users where user_name='$user_name' AND deleted=0";
		$result  = $this->db->query($query, false,"Error retrieving employee ID: ");
		$row = $this->db->fetchByAssoc($result);
		return $row['id'];
	}

	/**
	 * @return -- returns a list of all employees in the system.
	 * Portions created by SugarCRM are Copyright (C) SugarCRM, Inc..
	 * All Rights Reserved..
	 * Contributor(s): ______________________________________..
	 */
	function verify_data()
	{
		//none of the checks from the users module are valid here since the user_name and
		//is_admin_on fields are not editable.
		return TRUE;
	}

	function get_list_view_data(){
		
        global $current_user;
		$this->_create_proper_name_field(); // create proper NAME (by combining first + last)
		$user_fields = $this->get_list_view_array();
		// Copy over the reports_to_name
		if ( isset($GLOBALS['app_list_strings']['messenger_type_dom'][$this->messenger_type]) )
            $user_fields['MESSENGER_TYPE'] = $GLOBALS['app_list_strings']['messenger_type_dom'][$this->messenger_type];
		if ( isset($GLOBALS['app_list_strings']['employee_status_dom'][$this->employee_status]) )
            $user_fields['EMPLOYEE_STATUS'] = $GLOBALS['app_list_strings']['employee_status_dom'][$this->employee_status];
		$user_fields['REPORTS_TO_NAME'] = $this->reports_to_name;
		$user_fields['NAME'] = empty($this->name) ? '' : $this->name;
		$user_fields['EMAIL1'] = $this->emailAddress->getPrimaryAddress($this,$this->id,'Users');
		$this->email1 = $user_fields['EMAIL1'];
        $user_fields['EMAIL1_LINK'] = $current_user->getEmailLink('email1', $this, '', '', 'ListView');
		return $user_fields;
	}

	function list_view_parse_additional_sections(&$list_form, $xTemplateSection){
		return $list_form;
	}


	function create_export_query($order_by, $where) {
		include('modules/Employees/field_arrays.php');
		
		$cols = '';
		foreach($fields_array['Employee']['export_fields'] as $field) {
			$cols .= (empty($cols)) ? '' : ', ';
			$cols .= $field;
		}
		
		$query = "SELECT {$cols} FROM users ";

		$where_auto = " users.deleted = 0";

		if($where != "")
			$query .= " WHERE $where AND " . $where_auto;
		else
			$query .= " WHERE " . $where_auto;

		if($order_by != "")
			$query .= " ORDER BY $order_by";
		else
			$query .= " ORDER BY users.user_name";

		return $query;
	}
	
	//use parent class
	/**
	 * Generate the name field from the first_name and last_name fields.
	 */
	/*
	function _create_proper_name_field() {
        global $locale;
        $full_name = $locale->getLocaleFormattedName($this->first_name, $this->last_name);
        $this->name = $full_name;
        $this->full_name = $full_name; 
	}
	*/
	
	function preprocess_fields_on_save(){		
		parent::preprocess_fields_on_save();	
				
	}
}

?>
