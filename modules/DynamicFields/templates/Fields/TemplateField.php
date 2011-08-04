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

$GLOBALS['studioReadOnlyFields'] = array('date_entered'=>1, 'date_modified'=>1, 'created_by'=>1, 'id'=>1, 'modified_user_id'=>1);
class TemplateField{
	/*
		The view is the context this field will be used in
		-edit
		-list
		-detail
		-search
		*/
	var $view = 'edit';
	var $name = '';
	var $vname = '';
	var $id = '';
	var $size = '20';
	var $len = '255';
	var $required = false;
	var $default = null;
	var $default_value = null;
	var $type = 'varchar';
	var $comment = '';
	var $bean;
	var $ext1 = '';
	var $ext2 = '';
	var $ext3 = '';
	var $ext4 = '';
	var $audited= 0;
	var $massupdate = 0;
	var $importable = 'true' ;
	var $duplicate_merge=0;
	var $new_field_definition;
	var $reportable = true;
	var $label_value = '';
	var $help = '';
	var $formula = '';

	var $vardef_map = array(
		'name'=>'name',
		'label'=>'vname',
	// bug 15801 - need to ALWAYS keep default and default_value consistent as some methods/classes use one, some use another...
		'default_value'=>'default',
		'default'=>'default_value',
		'display_default'=>'default_value',
	//		'default_value'=>'default_value',
	//		'default'=>'default_value',
		'len'=>'len',
		'required'=>'required',
		'type'=>'type',
		'audited'=>'audited',
		'massupdate'=>'massupdate',
		'options'=>'ext1',
		'help'=>'help',
	    'comments'=>'comment',
	    'importable'=>'importable',
		'duplicate_merge'=>'duplicate_merge',
		'duplicate_merge_dom_value'=>'duplicate_merge_dom_value', //bug #14897
		'merge_filter'=>'merge_filter',
		'reportable' => 'reportable',
		'min'=>'ext1',
		'max'=>'ext2',
		'ext2'=>'ext2',
		'ext4'=>'ext4',
	//'disable_num_format'=>'ext3',
	    'ext3'=>'ext3',
		'label_value'=>'label_value',
	);
	/*
		HTML FUNCTIONS
		*/
	function get_html(){
		$view = $this->view;
		if(!empty($GLOBALS['studioReadOnlyFields'][$this->name]))$view = 'detail';
		switch($view){
			case 'search':return $this->get_html_search();
			case 'edit': return $this->get_html_edit();
			case 'list': return $this->get_html_list();
			case 'detail': return $this->get_html_detail();

		}
	}
	function set($values){
		foreach($values as $name=>$value){
			$this->$name = $value;
		}

	}

	function get_html_edit(){
		return 'not implemented';
	}

	function get_html_list(){
		return $this->get_html_detail();
	}

	function get_html_detail(){
		return 'not implemented';
	}

	function get_html_search(){
		return $this->get_html_edit();
	}
	function get_html_label(){

		$label =  "{MOD." .$this->vname . "}";
		if(!empty($GLOBALS['app_strings'][$this->vname])){
			$label = "{APP." .$this->label . "}";
		}
		if($this->view == 'edit' && $this->is_required()){
			$label .= '<span class="required">*</span>';
		}
		if($this->view == 'list'){
			if(isset($this->bean)){
				if(!empty($this->id)){
					$name = $this->bean->table_name . '_cstm.'. $this->name;
					$arrow = $this->bean->table_name . '_cstm_'. $this->name;
				}else{
					$name = $this->bean->table_name . '.'. $this->name;
					$arrow = $this->bean->table_name . '_'. $this->name;
				}
			}else{
				$name = $this->name;
				$arrow = $name;
			}
			$label = "<a href='{ORDER_BY}$name' class='listViewThLinkS1'>{MOD.$this->label}{arrow_start}{".$arrow."_arrow}{arrow_end}</a>";
		}
		return $label;

	}

	/*
		XTPL FUNCTIONS
		*/

	function get_xtpl($bean = false){
		if($bean)
		$this->bean = $bean;
		$view = $this->view;
		if(!empty($GLOBALS['studioReadOnlyFields'][$this->name]))$view = 'detail';
		switch($view){
			case 'search':return $this->get_xtpl_search();
			case 'edit': return $this->get_xtpl_edit();
			case 'list': return $this->get_xtpl_list();
			case 'detail': return $this->get_xtpl_detail();

		}
	}

	function get_xtpl_edit(){
		return '/*not implemented*/';
	}

	function get_xtpl_list(){
		return get_xtpl_detail();
	}

	function get_xtpl_detail(){
		return '/*not implemented*/';
	}

	function get_xtpl_search(){
		//return get_xtpl_edit();
	}

	function is_required(){
		if($this->required){
			return true;
		}
		return false;

	}




	/*
		DB FUNCTIONS
		*/

	function get_db_type(){
		switch($GLOBALS['db']->dbType){
			case 'oci8': return " varchar2($this->len)";
			case 'mssql': return !empty($GLOBALS['db']->isFreeTDS) ? " nvarchar($this->len)" : " varchar($this->len)";
			default: return " varchar($this->len)";
		}
	}

	function get_db_default($modify=false){
		$GLOBALS['log']->debug('get_db_default(): default_value='.$this->default_value);
		if (!$modify or empty($this->new_field_definition['default_value']) or $this->new_field_definition['default_value'] != $this->default_value ) {
			if(!is_null($this->default_value)){ // add a default value if it is not null - we want to set a default even if default_value is '0', which is not null, but which is empty()
				if(NULL == trim($this->default_value)){
					return " DEFAULT NULL";
				}
				else {
					return " DEFAULT '$this->default_value'";
				}
			}else{
				return '';
			}
		}
	}

	/*
	 * Return the required clause for this field
	 * Confusingly, when modifying an existing field ($modify=true) there are two exactly opposite cases:
	 * 1. if called by Studio, only $this->required is set. If set, we return "NOT NULL" otherwise we return "NULL"
	 * 2. if not called by Studio, $this->required holds the OLD value of required, and new_field_definition['required'] is the NEW
	 * So if not called by Studio we want to return NULL if required=true (because we are changing FROM this setting)
	 */

	function get_db_required($modify=false){
		//		$GLOBALS['log']->debug('get_db_required required='.$this->required." and ".(($modify)?"true":"false")." and ".print_r($this->new_field_definition,true));
		$req = "";

		if ($modify) {
			if (!empty($this->new_field_definition['required'])) {
				if ($this->required and $this->new_field_definition['required'] != $this->required) {
					$req = " NULL ";
				}
			}
			else
			{
				$req = ($this->required) ? " NOT NULL " : ''; // bug 17184 tyoung - set required correctly when modifying custom field in Studio
			}
		}
		else
		{
			if (empty($this->new_field_definition['required']) or $this->new_field_definition['required'] != $this->required ) {
				if(!empty($this->required) && $this->required){
					$req = " NOT NULL";
				}
			}
		}

		return $req;
	}

	/*	function get_db_required($modify=false){
		$GLOBALS['log']->debug('get_db_required required='.$this->required." and ".(($modify)?"true":"false")." and ".print_r($this->new_field_definition,true));
		if ($modify) {
		if (!empty($this->new_field_definition['required'])) {
		if ($this->required and $this->new_field_definition['required'] != $this->required) {
		return " null ";
		}
		return "";
		}
		}
		if (empty($this->new_field_definition['required']) or $this->new_field_definition['required'] != $this->required ) {
		if(!empty($this->required) && $this->required){
		return " NOT NULL";
		}
		}
		return '';
		}
		*/
	/**
	 * Oracle Support: do not set required constraint if no default value is supplied.
	 * In this case the default value will be handled by the application/sugarbean.
	 */
	function get_db_add_alter_table($table)
	{
		return $GLOBALS['db']->getHelper()->addColumnSQL($table, $this->get_field_def(), true);
	}

	function get_db_delete_alter_table($table)
	{
		return $GLOBALS['db']->getHelper()->dropColumnSQL(
		$table,
		$this->get_field_def()
		);
	}

	/**
	 * mysql requires the datatype caluse in the alter statment.it will be no-op anyway.
	 */
	function get_db_modify_alter_table($table){
		global $db;
		$db_default=$this->get_db_default(true);
		$db_required=$this->get_db_required(true);
		switch ($GLOBALS['db']->dbType) {

			case "mssql":
				//Bug 21772: MSSQL handles alters in strange ways. Defer to DBHelpers guidance.
				$query = $db->helper->alterColumnSQL($table, $this->get_field_def());
				return $query;
				break;

			case "mysql":
				$query="ALTER TABLE $table MODIFY $this->name " .$this->get_db_type();
				break;
			default:
				$query="ALTER TABLE $table MODIFY $this->name " .$this->get_db_type();;
				break;

		}
		if (!empty($db_default) && !empty($db_required)) {
			$query .= $db_default . $db_required ;
		} else if (!empty($db_default)) {
			$query .= $db_default;
		}
		return $query;
	}


	/*
	 * BEAN FUNCTIONS
	 *
	 */
	function get_field_def(){
		$array =  array(
			'required'=>$this->convertBooleanValue($this->required),
			'source'=>'custom_fields',
			'name'=>$this->name,
			'vname'=>$this->vname,
			'type'=>$this->type,
			'massupdate'=>$this->massupdate,
			'default'=>$this->default,
			'comments'=> (isset($this->comments)) ? $this->comments : '',
		    'help'=> (isset($this->help)) ?  $this->help : '',
		    'importable'=>$this->importable,
			'duplicate_merge'=>$this->duplicate_merge,
			'duplicate_merge_dom_value'=> isset($this->duplicate_merge_dom_value) ? $this->duplicate_merge_dom_value : $this->duplicate_merge,
			'audited'=>$this->convertBooleanValue($this->audited),
			'reportable'=>$this->convertBooleanValue($this->reportable),
		);
		if(!empty($this->len)){
			$array['len'] = $this->len;
		}
		if(!empty($this->size)){
			$array['size'] = $this->size;
		}
		$this->get_dup_merge_def($array);
		return $array;
	}

	protected function convertBooleanValue($value)
	{
		if ($value === 'true' || $value === '1' || $value === 1)
		return  true;
		else if ($value === 'false' || $value === '0' || $value === 0)
		return  false;
		else
		return $value;
	}


	/* if the field is duplicate merge enabled this function will return the vardef entry for the same.
	 */
	function get_dup_merge_def(&$def) {

		switch ($def['duplicate_merge_dom_value']) {
			case 0:
				$def['duplicate_merge']='disabled';
				break;
			case 1:
				$def['duplicate_merge']='enabled';
				break;
			case 2:
				$def['merge_filter']='enabled';
				$def['duplicate_merge']='enabled';
				break;
			case 3:
				$def['merge_filter']='selected';
				$def['duplicate_merge']='enabled';
				break;
			case 4:
				$def['merge_filter']='enabled';
				$def['duplicate_merge']='disabled';
				break;
		}

	}

	/*
		HELPER FUNCTIONS
		*/


	function prepare(){
		if(empty($this->id)){
			$this->id = $this->name;
		}
	}

	/**
	 * populateFromRow
	 * This function supports setting the values of all TemplateField instances.
	 * @param $row The Array key/value pairs from fields_meta_data table
	 */
	function populateFromRow($row=array()) {
		$fmd_to_dyn_map = array('comments' => 'comment', 'require_option' => 'required', 'label' => 'vname',
							    'mass_update' => 'massupdate', 'max_size' => 'len', 'default_value' => 'default', 'id_name' => 'ext3');
		if(!is_array($row)) {
			$GLOBALS['log']->error("Error: TemplateField->populateFromRow expecting Array");
		}
		//Bug 24189: Copy fields from FMD format to Field objects
		foreach ($fmd_to_dyn_map as $fmd_key => $dyn_key) {
			if (isset($row[$fmd_key])) {
				$this->$dyn_key = $row[$fmd_key];
			}
		}
		foreach($row as	$key=>$value) {
			$this->$key = $value;
		}
	}

	function populateFromPost(){
		foreach($this->vardef_map as $vardef=>$field){
			if(isset($_REQUEST[$vardef])){
				$this->$vardef = $_REQUEST[$vardef];
				if($vardef != $field){
					$this->$field = $this->$vardef;
				}
			}
		}
		$this->applyVardefRules();
		$GLOBALS['log']->debug('populate: '.print_r($this,true));

	}

	protected function applyVardefRules()
	{
	}

	function get_additional_defs(){
		return array();
	}

	function delete($df){
		$df->deleteField($this);
	}

	function save($df){
		//	    $GLOBALS['log']->debug('saving field: '.print_r($this,true));
		$df->addFieldObject($this);
	}

}


?>
