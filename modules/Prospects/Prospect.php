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

require_once('include/SugarObjects/templates/person/Person.php');

require_once('include/SugarObjects/templates/person/Person.php');

class Prospect extends Person {
    var $field_name_map;
	// Stored fields
	var $id;
	var $name = '';
	var $date_entered;
	var $date_modified;
	var $modified_user_id;
	var $assigned_user_id;
	var $created_by;
	var $created_by_name;
	var $modified_by_name;
	var $description;
	var $salutation;
	var $first_name;
	var $last_name;
	var $full_name;
	var $title;
	var $department;
	var $birthdate;
	var $do_not_call;
	var $phone_home;
	var $phone_mobile;
	var $phone_work;
	var $phone_other;
	var $phone_fax;
	var $email1;
	var $email2;
	var $email_and_name1;
	var $assistant;
	var $assistant_phone;
	var $email_opt_out;
	var $primary_address_street;
	var $primary_address_city;
	var $primary_address_state;
	var $primary_address_postalcode;
	var $primary_address_country;
	var $alt_address_street;
	var $alt_address_city;
	var $alt_address_state;
	var $alt_address_postalcode;
	var $alt_address_country;
	var $tracker_key;
	var $lead_id;
	var $account_name;
	var $assigned_real_user_name;
	// These are for related fields
	var $assigned_user_name;
	var $module_dir = 'Prospects';
	var $table_name = "prospects";
	var $object_name = "Prospect";
	var $new_schema = true;
	var $emailAddress;
	
	var $importable = true;
    // This is used to retrieve related fields from form posts.
	var $additional_column_fields = Array('assigned_user_name');


	function Prospect() {
		parent::Person();
	}

    function create_export_query(&$order_by, &$where, $relate_link_join='')
    {
        $custom_join = $this->custom_fields->getJOIN(true, true,$where);
		if($custom_join)
				$custom_join['join'] .= $relate_link_join;
                         $query = "SELECT
                                prospects.*,email_addresses.email_address email_address,
                                users.user_name as assigned_user_name ";
						if($custom_join){
   							$query .= $custom_join['select'];
 						}
						 $query .= " FROM prospects ";
                         $query .= "LEFT JOIN users
	                                ON prospects.assigned_user_id=users.id ";

						//join email address table too.
						$query .=  ' LEFT JOIN  email_addr_bean_rel on prospects.id = email_addr_bean_rel.bean_id and email_addr_bean_rel.bean_module=\'Prospects\' and email_addr_bean_rel.primary_address=1 and email_addr_bean_rel.deleted=0';
						$query .=  ' LEFT JOIN email_addresses on email_addresses.id = email_addr_bean_rel.email_address_id ' ;
						
						if($custom_join){
  							$query .= $custom_join['join'];
						}

		$where_auto = " prospects.deleted=0 ";

                if($where != "")
                        $query .= "where ($where) AND ".$where_auto;
                else
                        $query .= "where ".$where_auto;

                if(!empty($order_by))
                        $query .= " ORDER BY $order_by";

                return $query;
        }

    
	function fill_in_additional_list_fields() 
	{
		parent::fill_in_additional_list_fields();
		$this->_create_proper_name_field();		
		$this->email_and_name1 = $this->full_name." &lt;".$this->email1."&gt;";
	}

	function fill_in_additional_detail_fields() 
	{
		parent::fill_in_additional_list_fields();
		$this->_create_proper_name_field();
   	}

	function get_list_view_data() {
		global $current_user;
		$this->_create_proper_name_field();
		$temp_array = $this->get_list_view_array();
		$temp_array["ENCODED_NAME"] = $this->full_name;
		$temp_array["FULL_NAME"] = $this->full_name;
		$temp_array["EMAIL1"] = $this->emailAddress->getPrimaryAddress($this);
		$this->email1 = $temp_array['EMAIL1'];
		$temp_array["EMAIL1_LINK"] = $current_user->getEmailLink('email1', $this, '', '', 'ListView');

    	return $temp_array;
	}

	/**
		builds a generic search based on the query string using or
		do not include any $this-> because this is called on without having the class instantiated
	*/
	function build_generic_where_clause ($the_query_string)
	{
		$where_clauses = Array();
		$the_query_string = $GLOBALS['db']->quote($the_query_string);

		array_push($where_clauses, "prospects.last_name like '$the_query_string%'");
		array_push($where_clauses, "prospects.first_name like '$the_query_string%'");
		array_push($where_clauses, "prospects.assistant like '$the_query_string%'");

		if (is_numeric($the_query_string))
		{
			array_push($where_clauses, "prospects.phone_home like '%$the_query_string%'");
			array_push($where_clauses, "prospects.phone_mobile like '%$the_query_string%'");
			array_push($where_clauses, "prospects.phone_work like '%$the_query_string%'");
			array_push($where_clauses, "prospects.phone_other like '%$the_query_string%'");
			array_push($where_clauses, "prospects.phone_fax like '%$the_query_string%'");
			array_push($where_clauses, "prospects.assistant_phone like '%$the_query_string%'");
		}

		$the_where = "";
		foreach($where_clauses as $clause)
		{
			if($the_where != "") $the_where .= " or ";
			$the_where .= $clause;
		}


		return $the_where;
	}

    function converted_prospect($prospectid, $contactid, $accountid, $opportunityid){
    	$query = "UPDATE prospects set  contact_id=$contactid, account_id=$accountid, opportunity_id=$opportunityid where  id=$prospectid and deleted=0";
		$this->db->query($query,true,"Error converting prospect: ");
		//todo--status='Converted', converted='1',
    }
     function bean_implements($interface){
		switch($interface){
			case 'ACL':return true;
		}
		return false;
	}

    /**
     *  This method will be used by Mail Merge in order to retieve the targets as specified in the query
     *  @param query String - this is the query which contains the where clause for the query
     */
    function retrieveTargetList($query, $fields, $offset = 0, $limit= -99, $max = -99, $deleted = 0, $module = ''){
        global  $beanList, $beanFiles;
        $module_name = $this->module_dir;

        if(empty($module)){
            $pattern = '/AND related_type = #(.*)#/i';
            if(preg_match($pattern, $query, $matches) && count($matches) > 1){
                $module_name = $matches[1];
                $query = preg_replace($pattern, "", $query);
            }
             $GLOBALS['log']->debug("PROSPECT QUERY: ".$query);
        }
        $GLOBALS['log']->debug(var_export($matches, true));
        $count = count($fields);
        $index = 1;
        $sel_fields = "";
        if(!empty($fields)){
            foreach($fields as $field){
                if($field == 'id'){
                	$sel_fields .= 'prospect_lists_prospects.id id';
                }else{
                	$sel_fields .= $module_name.".".$field;
                }
                if($index < $count){
                    $sel_fields .= ",";  
                }
                $index++;
            }
        }

        $module_name = ucfirst($module_name);
        $class_name = $beanList[$module_name];
        require_once($beanFiles[$class_name]);
        $seed = new $class_name();
        if(empty($sel_fields)){
            $sel_fields = $seed->table_name.'.*';
        }
        $select = "SELECT ".$sel_fields." FROM ".$seed->table_name;
        $select .= " INNER JOIN prospect_lists_prospects ON prospect_lists_prospects.related_id = ".$seed->table_name.".id";
        $select .= " INNER JOIN prospect_lists ON prospect_lists_prospects.prospect_list_id = prospect_lists.id";
        $select .= " INNER JOIN prospect_list_campaigns ON prospect_list_campaigns.prospect_list_id = prospect_lists.id";
        $select .= " INNER JOIN campaigns on campaigns.id = prospect_list_campaigns.campaign_id";
        $select .= " WHERE prospect_list_campaigns.deleted = 0";
        $select .= " AND prospect_lists_prospects.deleted = 0";
        $select .= " AND prospect_lists.deleted = 0";
        if (!empty($query)) {
            $select .= " AND ".$query;
        }

        return $this->process_list_query($select, $offset, $limit, $max, $query);
    }

    /**
     *  Given an id, looks up in the prospect_lists_prospects table
     *  and retrieve the correct type for this id
     */
    function retrieveTarget($id){
        $query = "SELECT related_id, related_type FROM prospect_lists_prospects WHERE id = '".$id."'";
        $result = $this->db->query($query);
        if(($row = $this->db->fetchByAssoc($result))){
             global  $beanList, $beanFiles; 
             $module_name = $row['related_type'];
             $class_name = $beanList[$module_name];
             require_once($beanFiles[$class_name]);
             $seed = new $class_name();
             return $seed->retrieve($row['related_id']);        
        }else{
            return null;   
        }
    }

    
	function get_unlinked_email_query($type=array()) {
		
		return get_unlinked_email_query($type, $this);
	}
}
