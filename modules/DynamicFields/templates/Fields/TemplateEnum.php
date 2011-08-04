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


require_once('include/utils/array_utils.php');
class TemplateEnum extends TemplateText{
    var $max_size = 100;
    var $len = 100;
    var $type='enum';
    var $ext1 = '';
    var $default_value = '';
    var $dependency ; // any dependency information

    function __construct ()
    {
    	// ensure that the field dependency information is read in from any _REQUEST
    	$this->localVardefMap = array (
    		'trigger' => 'trigger',
    		'action' => 'action' , ) ;
    	$this->vardef_map = array_merge ( $this->vardef_map , $this->localVardefMap ) ;
    }

    function populateFromPost ()
    {
    	parent::populateFromPost();
    	// now convert trigger,action pairs into a dependency array representation
    	// we expect the dependencies in the following format:
    	// trigger = [ trigger for action 1 , trigger for action 2 , ... , trigger for action n ]
    	// action = [ action 1 , action 2 , ... , action n ]

    	// check first if we have the component parts of a dependency
    	$dependencyPresent = true ;
    	foreach ( $this->localVardefMap as $def )
    	{
    		$dependencyPresent &= isset ( $this->$def ) ;
    	}

    	if ( $dependencyPresent )
    	{
    		$dependencies = array () ;

    		if ( is_array ( $this->trigger ) && is_array ( $this->action ) )
    		{
				for ( $i = 0 ; $i < count ( $this->action ) ; $i++ )
				{
					$dependencies [ $this->trigger [ $i ] ] = $this->action [ $i ] ;
				}
				$this->dependency = $dependencies ;
    		}
    		else
    		{
    			if ( ! is_array ( $this->trigger ) && ! is_array ( $this->action ) )
    				$this->dependency = array ( $this->trigger => $this->action ) ;
    		}
    		// tidy up
    		unset ( $this->trigger ) ;
    		unset ( $this->action ) ;
    	}
    }
	function get_xtpl_edit(){
		$name = $this->name;
		$value = '';
		if(isset($this->bean->$name)){
			$value = $this->bean->$name;
		}else{
			if(empty($this->bean->id)){
				$value= $this->default_value;
			}
		}
		if(!empty($this->help)){
		    $returnXTPL[strtoupper($this->name . '_help')] = translate($this->help, $this->bean->module_dir);
		}

		global $app_list_strings;
		$returnXTPL = array();
		$returnXTPL[strtoupper($this->name)] = $value;
		if(empty($this->ext1)){
			$this->ext1 = $this->options;
		}
		$returnXTPL[strtoupper('options_'.$this->name)] = get_select_options_with_id($app_list_strings[$this->ext1], $value);

		return $returnXTPL;


	}

	function get_xtpl_search(){
		$searchFor = '';
		if(!empty($_REQUEST[$this->name])){
			$searchFor = $_REQUEST[$this->name];
		}
		global $app_list_strings;
		$returnXTPL = array();
		$returnXTPL[strtoupper($this->name)] = $searchFor;
		if(empty($this->ext1)){
			$this->ext1 = $this->options;
		}
		$returnXTPL[strtoupper('options_'.$this->name)] = get_select_options_with_id(add_blank_option($app_list_strings[$this->ext1]), $searchFor);
		return $returnXTPL;

	}

	function get_db_type(){
	    if(empty($this->max_size))$this->max_size = 150;
	    switch($GLOBALS['db']->dbType){
	    	case 'oci8': return " varchar2($this->max_size)";
	    	case 'mssql': return !empty($GLOBALS['db']->isFreeTDS) ? " nvarchar($this->max_size)" : " varchar($this->max_size)";
	    	default:  return " varchar($this->max_size)";
	    }

	}



	function get_field_def(){
		$def = parent::get_field_def();
		$def['options'] = !empty($this->options) ? $this->options : $this->ext1;
		$def['default'] = !empty($this->default) ? $this->default : $this->default_value;
		$def['len'] = $this->max_size;
		$def['studio'] = 'visible';
		// this class may be extended, so only do the unserialize for genuine TemplateEnums
		if (get_class( $this ) == 'TemplateEnum' && empty($def['dependency']) )
			$def['dependency'] = isset($this->ext4)? unserialize(html_entity_decode($this->ext4)) : null ;
		return $def;
	}

	function get_xtpl_detail(){
		$name = $this->name;

		// awu: custom fields are not being displayed on the detail view because $this->ext1 is always empty, adding this to get the options
		if(empty($this->ext1)){
			if(!empty($this->options))
				$this->ext1 = $this->options;
		}

		if(isset($this->bean->$name)){
			$key = $this->bean->$name;
			global $app_list_strings;
			if(preg_match('/&amp;/s', $key)) {
			   $key = str_replace('&amp;', '&', $key);
			}
			if(isset($app_list_strings[$this->ext1])){
                if(isset($app_list_strings[$this->ext1][$key])) {
                    return $app_list_strings[$this->ext1][$key];
                }

				if(isset($app_list_strings[$this->ext1][$this->bean->$name])){
					return $app_list_strings[$this->ext1][$this->bean->$name];
				}
			}
		}
		return '';
	}

	function save($df){
		if (!empty($this->default_value) && is_array($this->default_value)) {
			$this->default_value = $this->default_value[0];
		}
		if (!empty($this->default) && is_array($this->default)) {
			$this->default = $this->default[0];
		}

		if ( get_class( $this ) == 'TemplateEnum' && isset ( $this->dependency ) )
			$this->ext4 = serialize ( $this->dependency ) ;
		parent::save($df);
	}
}


?>
