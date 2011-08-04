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

 * Description:  TODO: To be written.
 * Portions created by SugarCRM are Copyright (C) SugarCRM, Inc.
 * All Rights Reserved.
 * Contributor(s): ______________________________________..
 ********************************************************************************/







// Task is used to store customer information.
class Task extends SugarBean {
        var $field_name_map;

	// Stored fields
	var $id;
	var $date_entered;
	var $date_modified;
	var $assigned_user_id;
	var $modified_user_id;
	var $created_by;
	var $created_by_name;
	var $modified_by_name;
	var $description;
	var $name;
	var $status;
	var $date_due_flag;
	var $date_due;
	var $time_due;
	var $date_start_flag;
	var $date_start;
	var $time_start;
	var $priority;
	var $parent_type;
	var $parent_id;
	var $contact_id;

	var $parent_name;
	var $contact_name;
	var $contact_phone;
	var $contact_email;
	var $assigned_user_name;

//bug 28138 todo
//	var $default_task_name_values = array('Assemble catalogs', 'Make travel arrangements', 'Send a letter', 'Send contract', 'Send fax', 'Send a follow-up letter', 'Send literature', 'Send proposal', 'Send quote', 'Call to schedule meeting', 'Setup evaluation', 'Get demo feedback', 'Arrange introduction', 'Escalate support request', 'Close out support request', 'Ship product', 'Arrange reference call', 'Schedule training', 'Send local user group information', 'Add to mailing list');

	var $table_name = "tasks";

	var $object_name = "Task";
	var $module_dir = 'Tasks';

	var $importable = true;
	// This is used to retrieve related fields from form posts.
	var $additional_column_fields = Array('assigned_user_name', 'assigned_user_id', 'contact_name', 'contact_phone', 'contact_email', 'parent_name');


	function Task() {
		parent::SugarBean();
	}

	var $new_schema = true;

    function save($check_notify = FALSE)
    {
        if (empty($this->status) ) {
            $this->status = $this->getDefaultStatus();
        }
        return parent::save($check_notify);
    }

	function get_summary_text()
	{
		return "$this->name";
	}

    function create_export_query(&$order_by, &$where, $relate_link_join='')
    {
        $custom_join = $this->custom_fields->getJOIN(true, true,$where);
		if($custom_join)
				$custom_join['join'] .= $relate_link_join;
                $contact_required = stristr($where,"contacts");
                if($contact_required)
                {
                        $query = "SELECT tasks.*, contacts.first_name, contacts.last_name";
                        if($custom_join){
   							$query .= $custom_join['select'];
 						}
                        $query .= " FROM contacts, tasks ";
                        $where_auto = "tasks.contact_id = contacts.id AND tasks.deleted=0 AND contacts.deleted=0";
                }
                else
                {
                        $query = 'SELECT tasks.*';
                        if($custom_join){
   							$query .= $custom_join['select'];
 						}
                        $query .= ' FROM tasks ';
                        $where_auto = "tasks.deleted=0";
                }


				if($custom_join){
   					$query .= $custom_join['join'];
 				}

                if($where != "")
                        $query .= "where $where AND ".$where_auto;
                else
                        $query .= "where ".$where_auto;

                if($order_by != "")
                        $query .=  " ORDER BY ". $this->process_order_by($order_by, null);
                else
                        $query .= " ORDER BY tasks.name";
                return $query;

        }



	function fill_in_additional_list_fields()
	{

	}

	function fill_in_additional_detail_fields()
	{
        parent::fill_in_additional_detail_fields();
		global $app_strings;

		if (isset($this->contact_id)) {

			$contact = new Contact();
			$contact->retrieve($this->contact_id);

			if($contact->id != "") {
				$this->contact_name = $contact->full_name;
				$this->contact_name_owner = $contact->assigned_user_id;
				$this->contact_name_mod = 'Contacts';
				$this->contact_phone = $contact->phone_work;
				$this->contact_email = $contact->emailAddress->getPrimaryAddress($contact);
			} else {
				$this->contact_name_mod = '';
				$this->contact_name_owner = '';
				$this->contact_name='';
				$this->contact_email = '';
				$this->contact_id='';
			}

		}

		$this->fill_in_additional_parent_fields();
	}

	function fill_in_additional_parent_fields()
	{

		$this->parent_name = '';
		global $app_strings, $beanFiles, $beanList, $locale;
		if ( ! isset($beanList[$this->parent_type]))
		{
			$this->parent_name = '';
			return;
		}

	    $beanType = $beanList[$this->parent_type];
		require_once($beanFiles[$beanType]);
		$parent = new $beanType();

		if (is_subclass_of($parent, 'Person')) {
			$query = "SELECT first_name, last_name, assigned_user_id parent_name_owner from $parent->table_name where id = '$this->parent_id'";
		}
		else if (is_subclass_of($parent, 'File')) {
			$query = "SELECT document_name, assigned_user_id parent_name_owner from $parent->table_name where id = '$this->parent_id'";
		}
		else {

			$query = "SELECT name ";
			if(isset($parent->field_defs['assigned_user_id'])){
				$query .= " , assigned_user_id parent_name_owner ";
			}else{
				$query .= " , created_by parent_name_owner ";
			}
			$query .= " from $parent->table_name where id = '$this->parent_id'";
		}
		$result = $this->db->query($query,true," Error filling in additional detail fields: ");

		// Get the id and the name.
		$row = $this->db->fetchByAssoc($result);

		if ($row && !empty($row['parent_name_owner'])){
			$this->parent_name_owner = $row['parent_name_owner'];
			$this->parent_name_mod = $this->parent_type;
		}
		if (is_subclass_of($parent, 'Person') and $row != null)
		{
			$this->parent_name = $locale->getLocaleFormattedName(stripslashes($row['first_name']), stripslashes($row['last_name']));
		}
		else if (is_subclass_of($parent, 'File') && $row != null) {
			$this->parent_name = $row['document_name'];
		}
		elseif($row != null)
		{
			$this->parent_name = stripslashes($row['name']);
		}
		else {
			$this->parent_name = '';
		}
	}




	function get_list_view_data(){
		global $action, $currentModule, $focus, $current_module_strings, $app_list_strings, $timedate;

		$override_date_for_subpanel = false;
		if(!empty($_REQUEST['module']) && $_REQUEST['module'] !='Calendar' && $_REQUEST['module'] !='Tasks' && $_REQUEST['module'] !='Home'){
			//this is a subpanel list view, so override the due date with start date so that collections subpanel works as expected
			$override_date_for_subpanel = true;
		}
		
		$today = $timedate->nowDbDate();
		$task_fields = $this->get_list_view_array();
		$dbtime = $timedate->to_db($task_fields['DATE_DUE']);
		if($override_date_for_subpanel){
			$dbtime = $timedate->to_db($task_fields['DATE_START']);
		}

        $task_fields['TIME_DUE'] = $timedate->to_display_time($dbtime);
        $task_fields['DATE_DUE'] = $timedate->to_display_date($dbtime);


        $date_due = $task_fields['DATE_DUE'];

		if (!empty($this->priority))
			$task_fields['PRIORITY'] = $app_list_strings['task_priority_dom'][$this->priority];
		if (isset($this->parent_type))
			$task_fields['PARENT_MODULE'] = $this->parent_type;
		if ($this->status != "Completed" && $this->status != "Deferred" )
		{
			$setCompleteUrl = "<a onclick='SUGAR.util.closeActivityPanel.show(\"{$this->module_dir}\",\"{$this->id}\",\"Completed\",\"listview\",\"1\");'>";
		    $task_fields['SET_COMPLETE'] = $setCompleteUrl . SugarThemeRegistry::current()->getImage("close_inline","title=".translate('LBL_LIST_CLOSE','Tasks')." border='0'")."</a>";
		}

        $dd = $timedate->to_db_date($date_due, false);
		if ($dd < $today){
			$task_fields['DATE_DUE']= "<font class='overdueTask'>".$date_due."</font>";
			if($override_date_for_subpanel){
				$task_fields['DATE_START']= "<font class='overdueTask'>".$date_due."</font>";
			}
		}else if( $dd	== $today ){
			$task_fields['DATE_DUE'] = "<font class='todaysTask'>".$date_due."</font>";
			if($override_date_for_subpanel){
				$task_fields['DATE_START'] = "<font class='todaysTask'>".$date_due."</font>";
			}
		}else{
			$task_fields['DATE_DUE'] = "<font class='futureTask'>".$date_due."</font>";
			if($override_date_for_subpanel){
				$task_fields['DATE_START'] = "<font class='futureTask'>".$date_due."</font>";
			}
		}

		//make sure we grab the localized version of the contact name, if a contact is provided
		if (!empty($this->contact_id)) {
			global $locale;
			$query  = "SELECT first_name, last_name, salutation, title FROM contacts ";
			$query .= "WHERE id='$this->contact_id' AND deleted=0";
			$result = $this->db->limitQuery($query,0,1,true," Error filling in contact name fields: ");

			// Get the contact name.
			$row = $this->db->fetchByAssoc($result);

			if($row != null)
			{
				$this->contact_name = $locale->getLocaleFormattedName($row['first_name'], $row['last_name'], $row['salutation'], $row['title']);
			}
		}

		$task_fields['CONTACT_NAME']= $this->contact_name;
		$task_fields['TITLE'] = '';
		if (!empty($task_fields['CONTACT_NAME'])) {
			$task_fields['TITLE'] .= $current_module_strings['LBL_LIST_CONTACT'].": ".$task_fields['CONTACT_NAME'];
		}
		if (!empty($this->parent_name)) {
			$task_fields['TITLE'] .= "\n".$app_list_strings['parent_type_display'][$this->parent_type].": ".$this->parent_name;
			$task_fields['PARENT_NAME']=$this->parent_name;
		}

		return $task_fields;
	}

	function set_notification_body($xtpl, $task)
	{
		global $app_list_strings;
        global $timedate;
        $notifyUser = $task->current_notify_user;
        $prefDate = $notifyUser->getUserDateTimePreferences();
		$xtpl->assign("TASK_SUBJECT", $task->name);
		//MFH #13507
		$xtpl->assign("TASK_PRIORITY", (isset($task->priority)?$app_list_strings['task_priority_dom'][$task->priority]:""));
		$xtpl->assign("TASK_DUEDATE", $timedate->to_display_date_time($task->date_due . " " . $task->time_due,true,true,$notifyUser)." ".$prefDate['userGmt']);
		$xtpl->assign("TASK_STATUS", (isset($task->status)?$app_list_strings['task_status_dom'][$task->status]:""));
		$xtpl->assign("TASK_DESCRIPTION", $task->description);

		return $xtpl;
	}

	function bean_implements($interface){
		switch($interface){
			case 'ACL':return true;
		}
		return false;
	}
	function listviewACLHelper(){
		$array_assign = parent::listviewACLHelper();
		$is_owner = false;
		if(!empty($this->parent_name)){
			if(!empty($this->parent_name_owner)){
				global $current_user;
				$is_owner = $current_user->id == $this->parent_name_owner;
			}
		}

			if(!ACLController::moduleSupportsACL($this->parent_type) || ACLController::checkAccess($this->parent_type, 'view', $is_owner)){
				$array_assign['PARENT'] = 'a';
			}else{
				$array_assign['PARENT'] = 'span';
			}
		$is_owner = false;
		if(!empty($this->contact_name)){
			if(!empty($this->contact_name_owner)){
				global $current_user;
				$is_owner = $current_user->id == $this->contact_name_owner;
			}
		}

		if( ACLController::checkAccess('Contacts', 'view', $is_owner)){
				$array_assign['CONTACT'] = 'a';
		}else{
				$array_assign['CONTACT'] = 'span';
		}

		return $array_assign;
	}

	public function getDefaultStatus()
    {
         $def = $this->field_defs['status'];
         if (isset($def['default'])) {
             return $def['default'];
         } else {
            $app = return_app_list_strings_language($GLOBALS['current_language']);
            if (isset($def['options']) && isset($app[$def['options']])) {
                $keys = array_keys($app[$def['options']]);
                return $keys[0];
            }
        }
        return '';
    }

}
