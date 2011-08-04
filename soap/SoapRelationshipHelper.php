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

require_once('soap/SoapError.php');

function check_for_relationship($relationships, $module){
	foreach($relationships as $table=>$rel){
		if( $rel['rhs_key'] == $module){
			return $table;

		}
	}
	return false;
}

/*
 * takes in two modules and returns the relationship information about them
 *
 */

function retrieve_relationships_properties($module_1, $module_2, $relationship_name = ""){
	
	$rs = new Relationship();
	$query =  "SELECT * FROM $rs->table_name WHERE ((lhs_module = '$module_1' AND rhs_module='$module_2') OR (lhs_module = '$module_2' AND rhs_module='$module_1'))";
	if(!empty($relationship_name) && isset($relationship_name)){
		$query .= " AND relationship_name = '$relationship_name'";
	}
	$result = $rs->db->query($query);

	return $rs->db->fetchByAssoc($result);
}




/*
 * retireves relationships between two modules
 * This will return all viewable relationships between two modules
 * module_query is a filter on the first module
 * related_module_query is a filter on the second module
 * relationship_query is a filter on the relationship between them
 * show_deleted is if deleted items should be shown or not
 *
 */
function retrieve_relationships($module_name,  $related_module, $relationship_query, $show_deleted, $offset, $max_results){
	global  $beanList, $beanFiles, $dictionary, $current_user;
	$error = new SoapError();
	$result_list = array();
	if(empty($beanList[$module_name]) || empty($beanList[$related_module])){

		$error->set_error('no_module');
		return array('result'=>$result_list, 'error'=>$error->get_soap_array());
	}

	$result = retrieve_relationship_query($module_name,  $related_module, $relationship_query, $show_deleted, $offset, $max_results);

	if(empty($result['module_1'])){

		$error->set_error('no_relationship_support');
		return array('result'=>$result_list, 'error'=>$error->get_soap_array());
	}
	$query = $result['query'];
	$module_1 = $result['module_1'];
	$table = $result['join_table'];

	$class_name = $beanList[$module_1];
	require_once($beanFiles[$class_name]);
	$mod = new $class_name();

	$count_query = str_replace('rt.*', 'count(*)', $query);
	$result = $mod->db->query($count_query);
	$row = $mod->db->fetchByAssoc($result);
	$total_count = $row['count(*)'];

	if($max_results != '-99'){
		$result = $mod->db->limitQuery($query, $offset, $max_results);
	}else{
		$result = $mod->db->query($query);
	}
	while($row = $mod->db->fetchByAssoc($result)){

		$result_list[] = $row;
	}

	return array('table_name'=>$table, 'result'=>$result_list, 'total_count'=>$total_count, 'error'=>$error->get_soap_array());
}

/*
 * retireves modified relationships between two modules
 * This will return all viewable relationships between two modules
 * module_query is a filter on the first module
 * related_module_query is a filter on the second module
 * relationship_query is a filter on the relationship between them
 * show_deleted is if deleted items should be shown or not
 *
 */
function retrieve_modified_relationships($module_name, $related_module, $relationship_query, $show_deleted, $offset, $max_results, $select_fields = array(), $relationship_name = ''){
	global  $beanList, $beanFiles, $dictionary, $current_user;
	$error = new SoapError();
	$result_list = array();
	if(empty($beanList[$module_name]) || empty($beanList[$related_module])){

		$error->set_error('no_module');
		return array('result'=>$result_list, 'error'=>$error->get_soap_array());
	}

	$row = retrieve_relationships_properties($module_name, $related_module, $relationship_name);

	if(empty($row)){

		$error->set_error('no_relationship_support');
		return array('result'=>$result_list, 'error'=>$error->get_soap_array());
	}

	$table = $row['join_table'];
	$has_join = true;
	if(empty($table)){
		//return array('table_name'=>$table, 'result'=>$result_list, 'error'=>$error->get_soap_array());
		$table = $row['rhs_table'];
		$module_1 = $row['lhs_module'];
		$mod_key = $row['lhs_key'];
		$module_2 = $row['rhs_module'];
		$mod2_key = $row['rhs_key'];
		$has_join = false;
	}
	else{
		$module_1 = $row['lhs_module'];
		$mod_key = $row['join_key_lhs'];
		$module_2 = $row['rhs_module'];
		$mod2_key = $row['join_key_rhs'];
	}



	$class_name = $beanList[$module_1];
	require_once($beanFiles[$class_name]);
	$mod = new $class_name();

	$mod2_name = $beanList[$module_2];
	require_once($beanFiles[$mod2_name]);
	$mod2 = new $mod2_name();
	$table_alias = 'rt';
	if($has_join == false){
		$table_alias = 'm1';
	}

	if(isset($select_fields) && !empty($select_fields)){
		$index = 0;
		$field_select ='';
		foreach($select_fields as $field){
			if($field == "id"){
				$field_select .= "DISTINCT m1.".$field;
			}
			else{
				if(strpos($field, ".") == false){
					$field_select .= "m1.".$field;
				}
				else{
					$field_select .= $field;
				}
			}
			if($index < (count($select_fields) - 1))
			{
				$field_select .= ",";
				$index++;
			}
		}//end foreach
		$query = "SELECT $field_select FROM $table $table_alias ";
	}
	else{
		$query = "SELECT rt.* FROM  $table $table_alias ";
	}

	if($has_join == false){
		$query .= " inner join $mod->table_name m2 on $table_alias.$mod2_key = m2.id AND m2.id = '$current_user->id'";
	}
	else{
		$query .= " inner join $mod->table_name m1 on rt.$mod_key = m1.id ";
		$query .= " inner join $mod2->table_name m2 on rt.$mod2_key = m2.id AND m2.id = '$current_user->id'";
	}

	if(!empty($relationship_query)){
		$query .= ' WHERE ' . string_format($relationship_query, array($table_alias));
	}

	if($max_results != '-99'){
		$result = $mod->db->limitQuery($query, $offset, $max_results);
	}else{
		$result = $mod->db->query($query);
	}
	while($row = $mod->db->fetchByAssoc($result)){
		$result_list[] = $row;
	}

	return array('table_name'=>$table, 'result'=>$result_list, 'total_count'=>$total_count, 'error'=>$error->get_soap_array());
}

/*
 * retireves relationships between two modules
 * This will return all viewable relationships between two modules
 * module_query is a filter on the first module
 * related_module_query is a filter on the second module
 * relationship_query is a filter on the relationship between them
 * show_deleted is if deleted items should be shown or not
 *
 */
function clear_relationships($module_name,  $related_module){
	global  $beanList, $beanFiles, $dictionary;
	$result_list = array();
	if(empty($beanList[$module_name]) || empty($beanList[$related_module])){


		return false;
	}

	$row = retrieve_relationships_properties($module_name, $related_module);
	if(empty($row)){

		return false;
	}

	if($module_name == $row['lhs_module']){
		$module_1 = $module_name;
		$module_2 = $related_module;
	}else{
		$module_2 = $module_name;
		$module_1 = $related_module;
	}
	$table = $row['join_table'];
	$class_name = $beanList[$module_1];
	require_once($beanFiles[$class_name]);
	$mod = new $class_name();
	$clear_query = "DELTE * FROM  $table WHERE 1=1";
	$result = $mod->db->query($clear_query);
	return true;
}

function server_save_relationships($list, $from_date, $to_date){
	require_once('include/utils/db_utils.php');
	global  $beanList, $beanFiles;
	$from_date = db_convert("'".$from_date."'", 'datetime');
	$to_date = db_convert("'".$to_date."'", 'datetime');
	global $sugar_config;
	$db = DBManagerFactory::getInstance();

	$ids = array();
	$add = 0;
	$modify = 0;
	$deleted = 0;

	foreach($list as $record)
	{
		$insert = '';
		$insert_values = '';
		$update = '';
		$select_values	= '';
		$args = array();

		$id = $record['id'];

		$table_name = $record['module_name'];
		$resolve = 1;

		foreach($record['name_value_list'] as $name_value){
			$name = $name_value['name'];

			if($name == 'date_modified'){
					$value = $to_date;
			}else{
					$value = db_convert("'".$name_value['value'] . "'", 'varchar');
			}
			if($name != 'resolve'){
			if(empty($insert)){
				$insert = '('	.$name;
				$insert_values = '('	.$value;
				if($name != 'date_modified' && $name != 'id' ){
					$select_values = $name ."=$value";
				}
				if($name != 'id'){
					$update = $name ."=$value";
				}
			}else{
				$insert .= ', '	.$name;
				$insert_values .= ', '	.$value;
				if(empty($update)){
					$update .= $name."=$value";
				}else{
					$update .= ','.$name."=$value";
				}

				if($name != 'date_modified' && $name != 'id' ){
					if(empty($select_values)){
						$select_values = $name ."=$value";
					}else{
						$select_values .= ' AND '.$name ."=$value";
					}
				}
			}
			}else{
				$resolve = $value;
			}




		}
		//ignore resolve for now server always wins
		$resolve = 1;
		$insert = "INSERT INTO $table_name $insert) VALUES $insert_values)";
		$update = "UPDATE $table_name SET $update WHERE id=";
		$delete = "DELETE FROM $table_name WHERE id=";
		$select_by_id_date = "SELECT id FROM $table_name WHERE id ='$id' AND date_modified > $from_date AND date_modified<= $to_date";
		$select_by_id = "SELECT id FROM $table_name WHERE id ='$id'";
		$select_by_values = "SELECT id FROM $table_name WHERE $select_values";
		$updated = false;


		$result = $db->query($select_by_id_date);
		//see if we have a matching id in the date_range
		if(!($row = $db->fetchByAssoc($result))){
			//if not lets check if we have one that matches the values

			$result = $db->query($select_by_values);
			if(!($row = $db->fetchByAssoc($result))){

				$result = $db->query($select_by_id);
				if($row = $db->fetchByAssoc($result)){

					$db->query($update ."'".$row['id']."'" );
					$ids[] = $row['id'];
					$modify++;
				}else{
					$db->query($insert);
					$add++;
					$ids[] = $row['id'];
				}
			}
	}

	}
	return array('add'=>$add, 'modify'=>$modify, 'ids'=>$ids);
}

/*
 *
 * gets the from statement from a query without the order by and without the select
 *
 */
function get_from_statement($query){
	$query = explode('FROM', $query);
	if(sizeof($query) == 1){
		$query = explode('from', $query[0]);
	}
	$query = explode( 'ORDER BY',$query[1]);

	return ' FROM ' . $query[0];

}

function retrieve_relationship_query($module_name,  $related_module, $relationship_query, $show_deleted, $offset, $max_results){
	global  $beanList, $beanFiles, $dictionary, $current_user;
	$error = new SoapError();
	$result_list = array();
	if(empty($beanList[$module_name]) || empty($beanList[$related_module])){

		$error->set_error('no_module');
		return array('query' =>"", 'module_1'=>"", 'join_table' =>"", 'error'=>$error->get_soap_array());
	}

	$row = retrieve_relationships_properties($module_name, $related_module);
	if(empty($row)){

		$error->set_error('no_relationship_support');
		return array('query' =>"", 'module_1'=>"", 'join_table' =>"", 'error'=>$error->get_soap_array());
	}

	$module_1 = $row['lhs_module'];
	$mod_key = $row['join_key_lhs'];
	$module_2 = $row['rhs_module'];
	$mod2_key = $row['join_key_rhs'];

	$table = $row['join_table'];
	if(empty($table)){
		return array('query' =>"", 'module_1'=>"", 'join_table' =>"", 'error'=>$error->get_soap_array());
	}
	$class_name = $beanList[$module_1];
	require_once($beanFiles[$class_name]);
	$mod = new $class_name();

	$mod2_name = $beanList[$module_2];
	require_once($beanFiles[$mod2_name]);
	$mod2 = new $mod2_name();
	$query = "SELECT rt.* FROM  $table rt ";
	$query .= " inner join $mod->table_name m1 on rt.$mod_key = m1.id ";
	$query .= " inner join $mod2->table_name m2 on rt.$mod2_key = m2.id  ";


	if(!empty($relationship_query)){
		$query .= ' WHERE ' . $relationship_query;
	}

	return array('query' =>$query, 'module_1'=>$module_1, 'join_table' => $table, 'error'=>$error->get_soap_array());
}

// Returns name of 'link' field between two given modules
function get_module_link_field($module_1, $module_2) {
	global $beanList, $beanFiles;

	// check to make sure both modules exist
	if (empty($beanList[$module_1]) || empty($beanList[$module_2])) {
		return FALSE;
	}

	$class_1 = $beanList[$module_1];
	require_once($beanFiles[$class_1]);

	$obj_1 = new $class_1();

	// loop through link fields of $module_1, checking for a link to $module_2
	foreach ($obj_1->get_linked_fields() as $linked_field) {
		$obj_1->load_relationship($linked_field['name']);
		$field = $linked_field['name'];

		if (empty($obj_1->$field)) {
			continue;
		}

		if ($obj_1->$field->getRelatedModuleName() == $module_2) {
			return $field;
		}
	}

	return FALSE;
}

// Retrieves array of ids for records of $get_module linked to $from_module by $get_id
// Example: to retrieve list of Contacts associated to Account X: $return = get_linked_records("Contacts", "Accounts", "X");
function get_linked_records($get_module, $from_module, $get_id) {
	global $beanList, $beanFiles;

	// instantiate and retrieve $from_module
	$from_class = $beanList[$from_module];
	require_once($beanFiles[$from_class]);
	$from_mod = new $from_class();
	$from_mod->retrieve($get_id);

	$field = get_module_link_field($from_module, $get_module);
	if ($field === FALSE) {
		return FALSE;
	}

	$from_mod->load_relationship($field);
	$id_arr = $from_mod->$field->get();
	
	//bug: 38065 
	if ($get_module == 'EmailAddresses') {
		$emails = $from_mod->emailAddress->addresses;
		$email_arr = array();
		foreach ($emails as $email) {
			$email_arr[] = $email['email_address_id'];
		}
		return $email_arr;
	}

	return $id_arr;
}

?>