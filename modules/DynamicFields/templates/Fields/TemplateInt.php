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

require_once('modules/DynamicFields/templates/Fields/TemplateRange.php');

class TemplateInt extends TemplateRange
{
	
	function __construct(){
		parent::__construct();
		$this->vardef_map['autoinc_next'] = 'autoinc_next';
		$this->vardef_map['autoinc_start'] = 'autoinc_start';
		$this->vardef_map['auto_increment'] = 'auto_increment';
	}
	
	var $type = 'int';
	function get_html_edit(){
		$this->prepare();
		return "<input type='text' name='". $this->name. "' id='".$this->name."' title='{" . strtoupper($this->name) ."_HELP}' size='".$this->size."' maxlength='".$this->len."' value='{". strtoupper($this->name). "}'>";
	}
	
	function populateFromPost(){
		parent::populateFromPost();
		if (isset($this->auto_increment))
		{
		    $this->auto_increment = $this->auto_increment == "true" || $this->auto_increment === true;
		}
	}
	
    function get_field_def(){
		$vardef = parent::get_field_def();
		$vardef['disable_num_format'] = isset($this->disable_num_format) ? $this->disable_num_format : $this->ext3;//40005
		if(!empty($this->ext2)){
		    $min = (!empty($this->ext1))?$this->ext1:0;
		    $max = $this->ext2;
		    $vardef['validation'] = array('type' => 'range', 'min' => $min, 'max' => $max);
		}
		if(!empty($this->auto_increment))
		{
			$vardef['auto_increment'] = $this->auto_increment;
			if ((empty($this->autoinc_next)) && isset($this->module) && isset($this->module->table_name))
			{
				global $db;
                $helper = $db->gethelper();
                $auto = $helper->getAutoIncrement($this->module->table_name, $this->name);
                $this->autoinc_next = $vardef['autoinc_next'] = $auto;
			}
		}
		return $vardef;
    }

    function get_db_type(){
	switch($GLOBALS['db']->dbType){
		case 'oci8': return ' NUMBER ';
		case 'mysql': return  (!empty($this->len) && $this->len <= 11 && $this->len > 0)? ' INT(' .$this->len . ')' : ' INT(11) ';	
		default: return ' INT ';
	}
}	
	
    function save($df){
        $next = false;
		if (!empty($this->auto_increment) && (!empty($this->autoinc_next) || !empty($this->autoinc_start)) && isset($this->module))
        {
            if (!empty($this->autoinc_start) && $this->autoinc_start > $this->autoinc_next)
			{
				$this->autoinc_next = $this->autoinc_start;
			}
			if(isset($this->module->table_name)){
				global $db;
	            $helper = $db->gethelper();
	            //Check that the new value is greater than the old value
	            $oldNext = $helper->getAutoIncrement($this->module->table_name, $this->name);
	            if ($this->autoinc_next > $oldNext)
	            {
	                $helper->setAutoIncrementStart($this->module->table_name, $this->name, $this->autoinc_next);
				}
			}
			$next = $this->autoinc_next;
			$this->autoinc_next = false;
        }
		parent::save($df);
		if ($next)
		  $this->autoinc_next = $next;
    }
}


?>
