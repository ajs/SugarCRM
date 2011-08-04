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

 * Description:  Defines the base class for all data entities used throughout the
 * application.  The base class including its methods and variables is designed to
 * be overloaded with module-specific methods and variables particular to the
 * module's base entity class.
 * Portions created by SugarCRM are Copyright (C) SugarCRM, Inc.
 * All Rights Reserved.
 *******************************************************************************/

require_once('modules/DynamicFields/DynamicField.php');

/**
 * SugarBean is the base class for all business objects in Sugar.  It implements
 * the primary functionality needed for manipulating business objects: create,
 * retrieve, update, delete.  It allows for searching and retrieving list of records.
 * It allows for retrieving related objects (e.g. contacts related to a specific account).
 *
 * In the current implementation, there can only be one bean per folder.
 * Naming convention has the bean name be the same as the module and folder name.
 * All bean names should be singular (e.g. Contact).  The primary table name for
 * a bean should be plural (e.g. contacts).
 *
 */
class SugarBean
{
    /**
     * A pointer to the database helper object DBHelper
     *
     * @var DBHelper
     */
    var $db;

	/**
	 * When createing a bean, you can specify a value in the id column as
	 * long as that value is unique.  During save, if the system finds an
	 * id, it assumes it is an update.  Setting new_with_id to true will
	 * make sure the system performs an insert instead of an update.
	 *
	 * @var BOOL -- default false
	 */
	var $new_with_id = false;


	/**
	 * Disble vardefs.  This should be set to true only for beans that do not have varders.  Tracker is an example
	 *
	 * @var BOOL -- default false
	 */
    var $disable_vardefs = false;


    /**
     * holds the full name of the user that an item is assigned to.  Only used if notifications
     * are turned on and going to be sent out.
     *
     * @var String
     */
    var $new_assigned_user_name;

	/**
	 * An array of booleans.  This array is cleared out when data is loaded.
	 * As date/times are converted, a "1" is placed under the key, the field is converted.
	 *
	 * @var Array of booleans
	 */
	var $processed_dates_times = array();

	/**
	 * Whether to process date/time fields for storage in the database in GMT
	 *
	 * @var BOOL
	 */
	var $process_save_dates =true;

    /**
     * This signals to the bean that it is being saved in a mass mode.
     * Examples of this kind of save are import and mass update.
     * We turn off notificaitons of this is the case to make things more efficient.
     *
     * @var BOOL
     */
    var $save_from_post = true;

	/**
	 * When running a query on related items using the method: retrieve_by_string_fields
	 * this value will be set to true if more than one item matches the search criteria.
	 *
	 * @var BOOL
	 */
	var $duplicates_found = false;

	/**
	 * The DBManager instance that was used to load this bean and should be used for
	 * future database interactions.
	 *
	 * @var DBManager
	 */
	var $dbManager;

	/**
	 * true if this bean has been deleted, false otherwise.
	 *
	 * @var BOOL
	 */
	var $deleted = 0;

    /**
     * Should the date modified column of the bean be updated during save?
     * This is used for admin level functionality that should not be updating
     * the date modified.  This is only used by sync to allow for updates to be
     * replicated in a way that will not cause them to be replicated back.
     *
     * @var BOOL
     */
    var $update_date_modified = true;

    /**
     * Should the modified by column of the bean be updated during save?
     * This is used for admin level functionality that should not be updating
     * the modified by column.  This is only used by sync to allow for updates to be
     * replicated in a way that will not cause them to be replicated back.
     *
     * @var BOOL
     */
    var $update_modified_by = true;

    /**
     * Setting this to true allows for updates to overwrite the date_entered
     *
     * @var BOOL
     */
    var $update_date_entered = false;

    /**
     * This allows for seed data to be created without using the current uesr to set the id.
     * This should be replaced by altering the current user before the call to save.
     *
     * @var unknown_type
     */
    //TODO This should be replaced by altering the current user before the call to save.
    var $set_created_by = true;

    var $team_set_id;

    /**
     * The database table where records of this Bean are stored.
     *
     * @var String
     */
    var $table_name = '';

    /**
    * This is the singular name of the bean.  (i.e. Contact).
    *
    * @var String
    */
    var $object_name = '';

    /** Set this to true if you query contains a sub-select and bean is converting both select statements
    * into count queries.
    */
    var $ungreedy_count=false;

    /**
    * The name of the module folder for this type of bean.
    *
    * @var String
    */
    var $module_dir = '';
    var $field_name_map;
    var $field_defs;
    var $custom_fields;
    var $column_fields = array();
    var $list_fields = array();
    var $additional_column_fields = array();
    var $relationship_fields = array();
    var $current_notify_user;
    var $fetched_row=false;
    var $layout_def;
    var $force_load_details = false;
    var $optimistic_lock = false;
    var $disable_custom_fields = false;
    var $number_formatting_done = false;
    var $process_field_encrypted=false;
    /*
    * The default ACL type
    */
    var $acltype = 'module';


    var $additional_meta_fields = array();

    /**
     * Set to true in the child beans if the module supports importing
     */
    var $importable = false;

    /**
    * Set to true in the child beans if the module use the special notification template
    */
    var $special_notification = false;

    /**
     * Set to true if the bean is being dealt with in a workflow
     */
    var $in_workflow = false;

    /**
     *
     * By default it will be true but if any module is to be kept non visible
     * to tracker, then its value needs to be overriden in that particular module to false.
     *
     */
    var $tracker_visibility = true;

    /**
     * Used to pass inner join string to ListView Data.
     */
    var $listview_inner_join = array();

    /**
     * Set to true in <modules>/Import/views/view.step4.php if a module is being imported
     */
    var $in_import = false;
    /**
     * Constructor for the bean, it performs following tasks:
     *
     * 1. Initalized a database connections
     * 2. Load the vardefs for the module implemeting the class. cache the entries
     *    if needed
     * 3. Setup row-level security preference
     * All implementing classes  must call this constructor using the parent::SugarBean() class.
     *
     */
    function SugarBean()
    {
        global  $dictionary, $current_user;
        static $loaded_defs = array();
        $this->db = DBManagerFactory::getInstance();

        $this->dbManager = DBManagerFactory::getInstance();
        if((false == $this->disable_vardefs && empty($loaded_defs[$this->object_name])) || !empty($GLOBALS['reload_vardefs']))
        {
            VardefManager::loadVardef($this->module_dir, $this->object_name);

            // build $this->column_fields from the field_defs if they exist
            if (!empty($dictionary[$this->object_name]['fields'])) {
                foreach ($dictionary[$this->object_name]['fields'] as $key=>$value_array) {
                    $column_fields[] = $key;
                    if(!empty($value_array['required']) && !empty($value_array['name'])) {
                        $this->required_fields[$value_array['name']] = 1;
                    }
                }
                $this->column_fields = $column_fields;
            }

            //setup custom fields
            if(!isset($this->custom_fields) &&
                empty($this->disable_custom_fields))
            {
                $this->setupCustomFields($this->module_dir);
            }
            //load up field_arrays from CacheHandler;
            if(empty($this->list_fields))
                $this->list_fields = $this->_loadCachedArray($this->module_dir, $this->object_name, 'list_fields');
            if(empty($this->column_fields))
                $this->column_fields = $this->_loadCachedArray($this->module_dir, $this->object_name, 'column_fields');
            if(empty($this->required_fields))
                $this->required_fields = $this->_loadCachedArray($this->module_dir, $this->object_name, 'required_fields');

            if(isset($GLOBALS['dictionary'][$this->object_name]) && !$this->disable_vardefs)
            {
                $this->field_name_map = $dictionary[$this->object_name]['fields'];
                $this->field_defs =	$dictionary[$this->object_name]['fields'];

                if(!empty($dictionary[$this->object_name]['optimistic_locking']))
                {
                    $this->optimistic_lock=true;
                }
            }
            $loaded_defs[$this->object_name]['column_fields'] =& $this->column_fields;
            $loaded_defs[$this->object_name]['list_fields'] =& $this->list_fields;
            $loaded_defs[$this->object_name]['required_fields'] =& $this->required_fields;
            $loaded_defs[$this->object_name]['field_name_map'] =& $this->field_name_map;
            $loaded_defs[$this->object_name]['field_defs'] =& $this->field_defs;
        }
        else
        {
            $this->column_fields =& $loaded_defs[$this->object_name]['column_fields'] ;
            $this->list_fields =& $loaded_defs[$this->object_name]['list_fields'];
            $this->required_fields =& $loaded_defs[$this->object_name]['required_fields'];
            $this->field_name_map =& $loaded_defs[$this->object_name]['field_name_map'];
            $this->field_defs =& $loaded_defs[$this->object_name]['field_defs'];
            $this->added_custom_field_defs = true;

            if(!isset($this->custom_fields) &&
                empty($this->disable_custom_fields))
            {
                $this->setupCustomFields($this->module_dir, false);
            }
            if(!empty($dictionary[$this->object_name]['optimistic_locking']))
            {
                $this->optimistic_lock=true;
            }
        }

        if($this->bean_implements('ACL') && !empty($GLOBALS['current_user'])){
            $this->acl_fields = (isset($dictionary[$this->object_name]['acl_fields']) && $dictionary[$this->object_name]['acl_fields'] === false)?false:true;
        }
        $this->populateDefaultValues();
    }


    /**
     * Returns the object name. If object_name is not set, table_name is returned.
     *
     * All implementing classes must set a value for the object_name variable.
     *
     * @param array $arr row of data fetched from the database.
     * @return  nothing
     *
     */
    function getObjectName()
    {
        if ($this->object_name)
            return $this->object_name;

        // This is a quick way out. The generated metadata files have the table name
        // as the key. The correct way to do this is to override this function
        // in bean and return the object name. That requires changing all the beans
        // as well as put the object name in the generator.
        return $this->table_name;
    }

    /**
     * Returns a list of fields with their definitions that have the audited property set to true.
     * Before calling this function, check whether audit has been enabled for the table/module or not.
     * You would set the audit flag in the implemting module's vardef file.
     *
     * @return an array of
     * @see is_AuditEnabled
     *
     * Internal function, do not override.
     */
    function getAuditEnabledFieldDefinitions()
    {
        $aclcheck = $this->bean_implements('ACL');
        $is_owner = $this->isOwner($GLOBALS['current_user']->id);
        if (!isset($this->audit_enabled_fields))
        {

            $this->audit_enabled_fields=array();
            foreach ($this->field_defs as $field => $properties)
            {

                if (
                (
                !empty($properties['Audited']) || !empty($properties['audited']))
                )
                {

                    $this->audit_enabled_fields[$field]=$properties;
                }
            }

        }
        return $this->audit_enabled_fields;
    }

    /**
     * Return true if auditing is enabled for this object
     * You would set the audit flag in the implemting module's vardef file.
     *
     * @return boolean
     *
     * Internal function, do not override.
     */
    function is_AuditEnabled()
    {
        global $dictionary;
        if (isset($dictionary[$this->getObjectName()]['audited']))
        {
            return $dictionary[$this->getObjectName()]['audited'];
        }
        else
        {
            return false;
        }
    }



    /**
     * Returns the name of the audit table.
     * Audit table's name is based on implementing class' table name.
     *
     * @return String Audit table name.
     *
     * Internal function, do not override.
     */
    function get_audit_table_name()
    {
        return $this->getTableName().'_audit';
    }

    /**
     * If auditing is enabled, create the audit table.
     *
     * Function is used by the install scripts and a repair utility in the admin panel.
     *
     * Internal function, do not override.
     */
    function create_audit_table()
    {
        global $dictionary;
        $table_name=$this->get_audit_table_name();

        require('metadata/audit_templateMetaData.php');

        $fieldDefs = $dictionary['audit']['fields'];
        $indices = $dictionary['audit']['indices'];
        // '0' stands for the first index for all the audit tables
        $indices[0]['name'] = 'idx_' . strtolower($this->getTableName()) . '_' . $indices[0]['name'];
        $indices[1]['name'] = 'idx_' . strtolower($this->getTableName()) . '_' . $indices[1]['name'];
        $engine = null;
        if(isset($dictionary['audit']['engine'])) {
            $engine = $dictionary['audit']['engine'];
        } else if(isset($dictionary[$this->getObjectName()]['engine'])) {
            $engine = $dictionary[$this->getObjectName()]['engine'];
        }

        $sql=$this->dbManager->helper->createTableSQLParams($table_name, $fieldDefs, $indices, $engine);

        $msg = "Error creating table: ".$table_name. ":";
        $this->dbManager->query($sql,true,$msg);
    }

    /**
     * Returns the implementing class' table name.
     *
     * All implementing classes set a value for the table_name variable. This value is returned as the
     * table name. If not set, table name is extracted from the implementing module's vardef.
     *
     * @return String Table name.
     *
     * Internal function, do not override.
     */
    function getTableName()
    {
        global $dictionary;
        if(isset($this->table_name))
        {
            return $this->table_name;
        }
        return $dictionary[$this->getObjectName()]['table'];
    }

    /**
     * Returns field definitions for the implementing module.
     *
     * The definitions were loaded in the constructor.
     *
     * @return Array Field definitions.
     *
     * Internal function, do not override.
     */
    function getFieldDefinitions()
    {
        return $this->field_defs;
    }

    /**
     * Returns index definitions for the implementing module.
     *
     * The definitions were loaded in the constructor.
     *
     * @return Array Index definitions.
     *
     * Internal function, do not override.
     */
    function getIndices()
    {
        global $dictionary;
        if(isset($dictionary[$this->getObjectName()]['indices']))
        {
            return $dictionary[$this->getObjectName()]['indices'];
        }
        return array();
    }

    /**
     * Returns field definition for the requested field name.
     *
     * The definitions were loaded in the constructor.
     *
     * @param string field name,
     * @return Array Field properties or boolean false if the field doesn't exist
     *
     * Internal function, do not override.
     */
    function getFieldDefinition($name)
    {
        if ( !isset($this->field_defs[$name]) )
            return false;

        return $this->field_defs[$name];
    }

    /**
     * Returnss  definition for the id field name.
     *
     * The definitions were loaded in the constructor.
     *
     * @return Array Field properties.
     *
     * Internal function, do not override.
     */
    function getPrimaryFieldDefinition()
    {
        $def = $this->getFieldDefinition("id");
        if (!$def)
            $def = $this->getFieldDefinition(0);
        return $def;
    }
    /**
     * Returns the value for the requested field.
     *
     * When a row of data is fetched using the bean, all fields are created as variables in the context
     * of the bean and then fetched values are set in these variables.
     *
     * @param string field name,
     * @return varies Field value.
     *
     * Internal function, do not override.
     */
    function getFieldValue($name)
    {
        if (!isset($this->$name)){
            return FALSE;
        }
        if($this->$name === TRUE){
            return 1;
        }
        if($this->$name === FALSE){
            return 0;
        }
        return $this->$name;
    }

    /**
     * Basically undoes the effects of SugarBean::populateDefaultValues(); this method is best called right after object
     * initialization.
     */
    public function unPopulateDefaultValues()
    {
        if ( !is_array($this->field_defs) )
            return;

        foreach ($this->field_defs as $field => $value) {
		    if( !empty($this->$field)
                  && ((isset($value['default']) && $this->$field == $value['default']) || (!empty($value['display_default']) && $this->$field == $value['display_default']))
                    ) {
                $this->$field = null;
                continue;
            }
            if(!empty($this->$field) && !empty($value['display_default']) && in_array($value['type'], array('date', 'datetime', 'datetimecombo')) &&
            $this->$field == $this->parseDateDefault($value['display_default'], ($value['type'] != 'date'))) {
                $this->$field = null;
            }
        }
    }

    /**
     * Create date string from default value
     * like '+1 month'
     * @param string $value
     * @param bool $time Should be expect time set too?
     * @return string
     */
    protected function parseDateDefault($value, $time = false)
    {
        global $timedate;
        if($time) {
            $dtAry = explode('&', $value, 2);
            $dateValue = $timedate->getNow(true)->modify($dtAry[0]);
            if(!empty($dtAry[1])) {
                $timeValue = $timedate->fromString($dtAry[1]);
                $dateValue->setTime($timeValue->hour, $timeValue->min, $timeValue->sec);
            }
            return $timedate->asUser($dateValue);
        } else {
            return $timedate->asUserDate($timedate->getNow(true)->modify($value));
        }
    }

    function populateDefaultValues($force=false){
        if ( !is_array($this->field_defs) )
            return;
        foreach($this->field_defs as $field=>$value){
            if((isset($value['default']) || !empty($value['display_default'])) && ($force || empty($this->$field))){
                $type = $value['type'];

                switch($type){
                    case 'date':
                        if(!empty($value['display_default'])){
                            $this->$field = $this->parseDateDefault($value['display_default']);
                        }
                        break;
                   case 'datetime':
                   case 'datetimecombo':
                        if(!empty($value['display_default'])){
                            $this->$field = $this->parseDateDefault($value['display_default'], true);
                        }
                        break;
                    case 'multienum':
                        if(empty($value['default']) && !empty($value['display_default']))
                            $this->$field = $value['display_default'];
                        else
                            $this->$field = $value['default'];
                        break;
                    default:
                        if ( isset($value['default']) && $value['default'] !== '' ) {
                            $this->$field = htmlentities($value['default'], ENT_QUOTES, 'UTF-8');
                        } else {
                            $this->$field = '';
                        }
                } //switch
            }
        } //foreach
    }


    /**
     * Removes relationship metadata cache.
     *
     * Every module that has relationships defined with other modules, has this meta data cached.  The cache is
     * stores in 2 locations: relationships table and file system. This method clears the cache from both locations.
     *
     * @param string $key  module whose meta cache is to be cleared.
     * @param string $db database handle.
     * @param string $tablename table name
     * @param string $dictionary vardef for the module
     * @param string $module_dir name of subdirectory where module is installed.
     *
     * @return Nothing
     * @static
     *
     * Internal function, do not override.
     */
    function removeRelationshipMeta($key,$db,$tablename,$dictionary,$module_dir)
    {
        //load the module dictionary if not supplied.
        if ((!isset($dictionary) or empty($dictionary)) && !empty($module_dir))
        {
            $filename='modules/'. $module_dir . '/vardefs.php';
            if(file_exists($filename))
            {
                include($filename);
            }
        }
        if (!is_array($dictionary) or !array_key_exists($key, $dictionary))
        {
            $GLOBALS['log']->fatal("removeRelationshipMeta: Metadata for table ".$tablename. " does not exist");
            display_notice("meta data absent for table ".$tablename." keyed to $key ");
        }
        else
        {
            if (isset($dictionary[$key]['relationships']))
            {
                $RelationshipDefs = $dictionary[$key]['relationships'];
                foreach ($RelationshipDefs as $rel_name)
                {
                    Relationship::delete($rel_name,$db);
                }
            }
        }
    }


    /**
     * This method has been deprecated.
     *
    * @see removeRelationshipMeta()
     * @deprecated 4.5.1 - Nov 14, 2006
     * @static
    */
    function remove_relationship_meta($key,$db,$log,$tablename,$dictionary,$module_dir)
    {
        SugarBean::removeRelationshipMeta($key,$db,$tablename,$dictionary,$module_dir);
    }


    /**
     * Populates the relationship meta for a module.
     *
     * It is called during setup/install. It is used statically to create relationship meta data for many-to-many tables.
     *
     * 	@param string $key name of the object.
     * 	@param object $db database handle.
     *  @param string $tablename table, meta data is being populated for.
     *  @param array dictionary vardef dictionary for the object.     *
     *  @param string module_dir name of subdirectory where module is installed.
     *  @param boolean $iscustom Optional,set to true if module is installed in a custom directory. Default value is false.
     *  @static
     *
     *  Internal function, do not override.
     */
    function createRelationshipMeta($key,$db,$tablename,$dictionary,$module_dir,$iscustom=false)
    {
        //load the module dictionary if not supplied.
        if (empty($dictionary) && !empty($module_dir))
        {
            if($iscustom)
            {
                $filename='custom/modules/' . $module_dir . '/Ext/Vardefs/vardefs.ext.php';
            }
            else
            {
                if ($key == 'User')
                {
                    // a very special case for the Employees module
                    // this must be done because the Employees/vardefs.php does an include_once on
                    // Users/vardefs.php
                    $filename='modules/Users/vardefs.php';
                }
                else
                {
                    $filename='modules/'. $module_dir . '/vardefs.php';
                }
            }

            if(file_exists($filename))
            {
                include($filename);
                // cn: bug 7679 - dictionary entries defined as $GLOBALS['name'] not found
                if(empty($dictionary) || !empty($GLOBALS['dictionary'][$key]))
                {
                    $dictionary = $GLOBALS['dictionary'];
                }
            }
            else
            {
                $GLOBALS['log']->debug("createRelationshipMeta: no metadata file found" . $filename);
                return;
            }
        }

        if (!is_array($dictionary) or !array_key_exists($key, $dictionary))
        {
            $GLOBALS['log']->fatal("createRelationshipMeta: Metadata for table ".$tablename. " does not exist");
            display_notice("meta data absent for table ".$tablename." keyed to $key ");
        }
        else
        {
            if (isset($dictionary[$key]['relationships']))
            {

                $RelationshipDefs = $dictionary[$key]['relationships'];

                $delimiter=',';
                global $beanList;
                $beanList_ucase=array_change_key_case  ( $beanList ,CASE_UPPER);
                foreach ($RelationshipDefs as $rel_name=>$rel_def)
                {
                    if (isset($rel_def['lhs_module']) and !isset($beanList_ucase[strtoupper($rel_def['lhs_module'])])) {
                        $GLOBALS['log']->debug('skipping orphaned relationship record ' . $rel_name . ' lhs module is missing ' . $rel_def['lhs_module']);
                        continue;
                    }
                    if (isset($rel_def['rhs_module']) and !isset($beanList_ucase[strtoupper($rel_def['rhs_module'])])) {
                        $GLOBALS['log']->debug('skipping orphaned relationship record ' . $rel_name . ' rhs module is missing ' . $rel_def['rhs_module']);
                        continue;
                    }


                    //check whether relationship exists or not first.
                    if (Relationship::exists($rel_name,$db))
                    {
                        $GLOBALS['log']->debug('Skipping, reltionship already exists '.$rel_name);
                    }
                    else
                    {
                        //	add Id to the insert statement.
                        $column_list='id';
                        $value_list="'".create_guid()."'";

                        //add relationship name to the insert statement.
                        $column_list .= $delimiter.'relationship_name';
                        $value_list .= $delimiter."'".$rel_name."'";

                        //todo check whether $rel_def is an array or not.
                        //for now make that assumption.
                        //todo specify defaults if meta not defined.
                        foreach ($rel_def as $def_key=>$value)
                        {
                            $column_list.= $delimiter.$def_key;
                            $value_list.= $delimiter."'".$value."'";
                        }

                        //create the record. todo add error check.
                        $insert_string = "INSERT into relationships (" .$column_list. ") values (".$value_list.")";
                        $db->query($insert_string, true);
                    }
                }
            }
            else
            {
                //todo
                //log informational message stating no relationships meta was set for this bean.
            }
        }
    }

    /**
     * This method has been deprecated.
    * @see createRelationshipMeta()
     * @deprecated 4.5.1 - Nov 14, 2006
     * @static
    */
    function create_relationship_meta($key,&$db,&$log,$tablename,$dictionary,$module_dir)
    {
        SugarBean::createRelationshipMeta($key,$db,$tablename,$dictionary,$module_dir);
    }


    /**
     * Loads the request relationship. This method should be called before performing any operations on the related data.
     *
     * This method searches the vardef array for the requested attribute's definition. If the attribute is of the type
     * link then it creates a similary named variable and loads the relationship definition.
     *
     * @param string $rel_name  relationship/attribute name.
     * @return nothing.
     */
    function load_relationship($rel_name)
    {
        $GLOBALS['log']->debug("SugarBean.load_relationships, Loading relationship (".$rel_name.").");

        if (empty($rel_name))
        {
            $GLOBALS['log']->error("SugarBean.load_relationships, Null relationship name passed.");
            return false;
        }
        $fieldDefs = $this->getFieldDefinitions();

        //find all definitions of type link.
        if (!empty($fieldDefs))
        {
            //if rel_name is provided, search the fieldef array keys by name.
            if (array_key_exists($rel_name, $fieldDefs))
            {
                if (array_search('link',$fieldDefs[$rel_name]) === 'type')
                {
                    //initialize a variable of type Link
                    require_once('data/Link.php');
                    $class = load_link_class($fieldDefs[$rel_name]);

                    $this->$rel_name=new $class($fieldDefs[$rel_name]['relationship'], $this, $fieldDefs[$rel_name]);

                    if (empty($this->$rel_name->_relationship->id)) {
                        unset($this->$rel_name);
                        return false;
                    }
                    return true;
                }
            }
            else
            {
                $GLOBALS['log']->debug("SugarBean.load_relationships, Error Loading relationship (".$rel_name.").");
                return false;
            }
        }

        return false;
    }

    /**
     * Loads all attributes of type link.
     *
     * Method searches the implmenting module's vardef file for attributes of type link, and for each attribute
     * create a similary named variable and load the relationship definition.
     *
     * @return Nothing
     *
     * Internal function, do not override.
     */
    function load_relationships()
    {

        $GLOBALS['log']->debug("SugarBean.load_relationships, Loading all relationships of type link.");

        $linked_fields=$this->get_linked_fields();
        require_once("data/Link.php");
        foreach($linked_fields as $name=>$properties)
        {
            $class = load_link_class($properties);

            $this->$name=new $class($properties['relationship'], $this, $properties);
        }
    }

    /**
     * Returns an array of beans of related data.
     *
     * For instance, if an account is related to 10 contacts , this function will return an array of contacts beans (10)
     * with each bean representing a contact record.
     * Method will load the relationship if not done so already.
     *
     * @param string $field_name relationship to be loaded.
     * @param string $bean name  class name of the related bean.
     * @param array $sort_array optional, unused
     * @param int $begin_index Optional, default 0, unused.
     * @param int $end_index Optional, default -1
     * @param int $deleted Optional, Default 0, 0  adds deleted=0 filter, 1  adds deleted=1 filter.
     * @param string $optional_where, Optional, default empty.
     *
     * Internal function, do not override.
     */
    function get_linked_beans($field_name,$bean_name, $sort_array = array(), $begin_index = 0, $end_index = -1,
                              $deleted=0, $optional_where="")
    {

        //if bean_name is Case then use aCase
        if($bean_name=="Case")
            $bean_name = "aCase";

        //add a references to bean_name if it doe not exist aleady.
        if (!(class_exists($bean_name)))
        {

            if (isset($GLOBALS['beanList']) && isset($GLOBALS['beanFiles']))
            {
                global $beanFiles;
            }
            else
            {

            }
            $bean_file=$beanFiles[$bean_name];
            include_once($bean_file);
        }

        $this->load_relationship($field_name);

        return $this->$field_name->getBeans(new $bean_name(), $sort_array, $begin_index, $end_index, $deleted, $optional_where);
    }

    /**
     * Returns an array of fields that are of type link.
     *
     * @return array List of fields.
     *
     * Internal function, do not override.
     */
    function get_linked_fields()
    {

        $linked_fields=array();

 //   	require_once('data/Link.php');

        $fieldDefs = $this->getFieldDefinitions();

        //find all definitions of type link.
        if (!empty($fieldDefs))
        {
            foreach ($fieldDefs as $name=>$properties)
            {
                if (array_search('link',$properties) === 'type')
                {
                    $linked_fields[$name]=$properties;
                }
            }
        }

        return $linked_fields;
    }

    /**
     * Returns an array of fields that are able to be Imported into
     * i.e. 'importable' not set to 'false'
     *
     * @return array List of fields.
     *
     * Internal function, do not override.
     */
    function get_importable_fields()
    {
        $importableFields = array();

        $fieldDefs= $this->getFieldDefinitions();

        if (!empty($fieldDefs)) {
            foreach ($fieldDefs as $key=>$value_array) {
                if ( (isset($value_array['importable'])
                        && (is_string($value_array['importable']) && $value_array['importable'] == 'false'
                            || is_bool($value_array['importable']) && $value_array['importable'] == false))
                    || (isset($value_array['type']) && $value_array['type'] == 'link')
                    || (isset($value_array['auto_increment'])
                        && ($value_array['type'] == true || $value_array['type'] == 'true')) ) {
                    // only allow import if we force it
                    if (isset($value_array['importable'])
                        && (is_string($value_array['importable']) && $value_array['importable'] == 'true'
                           || is_bool($value_array['importable']) && $value_array['importable'] == true)) {
                        $importableFields[$key]=$value_array;
                    }
                }
                else {
                    $importableFields[$key]=$value_array;
                }
            }
        }

        return $importableFields;
    }

    /**
     * Returns an array of fields that are of type relate.
     *
     * @return array List of fields.
     *
     * Internal function, do not override.
     */
    function get_related_fields()
    {

        $related_fields=array();

//    	require_once('data/Link.php');

        $fieldDefs = $this->getFieldDefinitions();

        //find all definitions of type link.
        if (!empty($fieldDefs))
        {
            foreach ($fieldDefs as $name=>$properties)
            {
                if (array_search('relate',$properties) === 'type')
                {
                    $related_fields[$name]=$properties;
                }
            }
        }

        return $related_fields;
    }

    /**
     * Returns an array of fields that are required for import
     *
     * @return array
     */
    function get_import_required_fields()
    {
        $importable_fields = $this->get_importable_fields();
        $required_fields   = array();

        foreach ( $importable_fields as $name => $properties ) {
            if ( isset($properties['importable']) && is_string($properties['importable']) && $properties['importable'] == 'required' ) {
                $required_fields[$name] = $properties;
            }
        }

        return $required_fields;
    }

    /**
     * Iterates through all the relationships and deletes all records for reach relationship.
     *
     * @param string $id Primary key value of the parent reocrd
     */
    function delete_linked($id)
    {
        $linked_fields=$this->get_linked_fields();

        foreach ($linked_fields as $name => $value)
        {
            if ($this->load_relationship($name))
            {
                $GLOBALS['log']->debug('relationship loaded');
                $this->$name->delete($id);
            }
            else
            {
                $GLOBALS['log']->error('error loading relationship');
            }
        }
    }

    /**
     * Creates tables for the module implementing the class.
     * If you override this function make sure that your code can handles table creation.
     *
     */
    function create_tables()
    {
        global $dictionary;

        $key = $this->getObjectName();
        if (!array_key_exists($key, $dictionary))
        {
            $GLOBALS['log']->fatal("create_tables: Metadata for table ".$this->table_name. " does not exist");
            display_notice("meta data absent for table ".$this->table_name." keyed to $key ");
        }
        else
        {
            if(!$this->db->tableExists($this->table_name))
            {
                $this->dbManager->createTable($this);
                    if($this->bean_implements('ACL')){
                        if(!empty($this->acltype)){
                            ACLAction::addActions($this->getACLCategory(), $this->acltype);
                        }else{
                            ACLAction::addActions($this->getACLCategory());
                        }
                    }
            }
            else
            {
                echo "Table already exists : $this->table_name<br>";
            }
            if($this->is_AuditEnabled()){
                    if (!$this->db->tableExists($this->get_audit_table_name())) {
                        $this->create_audit_table();
                    }
            }

        }
    }

    /**
     * Delete the primary table for the module implementing the class.
     * If custom fields were added to this table/module, the custom table will be removed too, along with the cache
     * entries that define the custom fields.
     *
     */
    function drop_tables()
    {
        global $dictionary;
        $key = $this->getObjectName();
        if (!array_key_exists($key, $dictionary))
        {
            $GLOBALS['log']->fatal("drop_tables: Metadata for table ".$this->table_name. " does not exist");
            echo "meta data absent for table ".$this->table_name."<br>\n";
        } else {
            if(empty($this->table_name))return;
            if ($this->db->tableExists($this->table_name))

                $this->dbManager->dropTable($this);
            if ($this->db->tableExists($this->table_name. '_cstm'))
            {
                $this->dbManager->dropTableName($this->table_name. '_cstm');
                DynamicField::deleteCache();
            }
            if ($this->db->tableExists($this->get_audit_table_name())) {
                $this->dbManager->dropTableName($this->get_audit_table_name());
            }


        }
    }


    /**
     * Loads the definition of custom fields defined for the module.
     * Local file system cache is created as needed.
     *
     * @param string $module_name setting up custom fields for this module.
     * @param boolean $clean_load Optional, default true, rebuilds the cache if set to true.
     */
    function setupCustomFields($module_name, $clean_load=true)
    {
        $this->custom_fields = new DynamicField($module_name);
        $this->custom_fields->setup($this);

    }

    /**
    * Cleans char, varchar, text, etc. fields of XSS type materials
    */
    function cleanBean() {
        foreach($this->field_defs as $key => $def) {

            if (isset($def['type'])) {
                $type=$def['type'];
            }
            if(isset($def['dbType']))
                $type .= $def['dbType'];

            if((strpos($type, 'char') !== false ||
                strpos($type, 'text') !== false ||
                $type == 'enum') &&
                !empty($this->$key)
            ) {
                $str = from_html($this->$key);
                // Julian's XSS cleaner
                $potentials = clean_xss($str, false);

                if(is_array($potentials) && !empty($potentials)) {
                    foreach($potentials as $bad) {
                        $str = str_replace($bad, "", $str);
                    }
                    $this->$key = to_html($str);
                }
            }
        }
    }

    /**
    * Implements a generic insert and update logic for any SugarBean
    * This method only works for subclasses that implement the same variable names.
    * This method uses the presence of an id field that is not null to signify and update.
    * The id field should not be set otherwise.
    *
    * @param boolean $check_notify Optional, default false, if set to true assignee of the record is notified via email.
    * @todo Add support for field type validation and encoding of parameters.
    */
    function save($check_notify = FALSE)
    {
        // cn: SECURITY - strip XSS potential vectors
        $this->cleanBean();
        // This is used so custom/3rd-party code can be upgraded with fewer issues, this will be removed in a future release
        $this->fixUpFormatting();
        global $timedate;
        global $current_user, $action;

        $isUpdate = true;
        if(empty($this->id))
        {
            $isUpdate = false;
        }

		if ( $this->new_with_id == true )
		{
			$isUpdate = false;
		}
		if(empty($this->date_modified) || $this->update_date_modified)
		{
			$this->date_modified = $GLOBALS['timedate']->nowDb();
		}

        $this->_checkOptimisticLocking($action, $isUpdate);

        if(!empty($this->modified_by_name)) $this->old_modified_by_name = $this->modified_by_name;
        if($this->update_modified_by)
        {
            $this->modified_user_id = 1;

            if (!empty($current_user))
            {
                $this->modified_user_id = $current_user->id;
                $this->modified_by_name = $current_user->user_name;
            }
        }
        if ($this->deleted != 1)
            $this->deleted = 0;
        if($isUpdate)
        {
            $query = "Update ";
        }
        else
        {
            if (empty($this->date_entered))
            {
                $this->date_entered = $this->date_modified;
            }
            if($this->set_created_by == true)
            {
                // created by should always be this user
                $this->created_by = (isset($current_user)) ? $current_user->id : "";
            }
            if( $this->new_with_id == false)
            {
                $this->id = create_guid();
            }
            $query = "INSERT into ";
        }
        if($isUpdate && !$this->update_date_entered)
        {
            unset($this->date_entered);
        }
        // call the custom business logic
        $custom_logic_arguments['check_notify'] = $check_notify;


        $this->call_custom_logic("before_save", $custom_logic_arguments);
        unset($custom_logic_arguments);

        if(isset($this->custom_fields))
        {
            $this->custom_fields->bean = $this;
            $this->custom_fields->save($isUpdate);
        }

        // use the db independent query generator
        $this->preprocess_fields_on_save();

        //construct the SQL to create the audit record if auditing is enabled.
        $dataChanges=array();
        if ($this->is_AuditEnabled())
        {
            if ($isUpdate && !isset($this->fetched_row))
            {
                $GLOBALS['log']->debug('Auditing: Retrieve was not called, audit record will not be created.');
            }
            else
            {
                $dataChanges=$this->dbManager->helper->getDataChanges($this);
            }
        }

        $this->_sendNotifications($check_notify);

        if ($this->db->dbType == "oci8")
        {
        }
        if ($this->db->dbType == 'mysql')
        {
            // write out the SQL statement.
            $query .= $this->table_name." set ";

            $firstPass = 0;

            foreach($this->field_defs as $field=>$value)
            {
                if(!isset($value['source']) || $value['source'] == 'db')
                {
                    // Do not write out the id field on the update statement.
                    // We are not allowed to change ids.
                    if($isUpdate && ('id' == $field))
                        continue;
                    //custom fields handle there save seperatley
                    if(isset($this->field_name_map) && !empty($this->field_name_map[$field]['custom_type']))
                        continue;

                    // Only assign variables that have been set.
                    if(isset($this->$field))
                    {
                        //bug: 37908 - this is to handle the issue where the bool value is false, but strlen(false) <= so it will
                        //set the default value. TODO change this code to esend all fields through getFieldValue() like DbHelper->insertSql
                        if(!empty($value['type']) && $value['type'] == 'bool'){
                            $this->$field = $this->getFieldValue($field);
                        }

                        if(strlen($this->$field) <= 0)
                        {
                            if(!$isUpdate && isset($value['default']) && (strlen($value['default']) > 0))
                            {
                                $this->$field = $value['default'];
                            }
                            else
                            {
                                $this->$field = null;
                            }
                        }
                        // Try comparing this element with the head element.
                        if(0 == $firstPass)
                            $firstPass = 1;
                        else
                            $query .= ", ";

                        if(is_null($this->$field))
                        {
                            $query .= $field."=null";
                        }
                        else
                        {
                            //added check for ints because sql-server does not like casting varchar with a decimal value
                            //into an int.
                            if(isset($value['type']) and $value['type']=='int') {
                                $query .= $field."=".$this->db->quote($this->$field);
                            } elseif ( isset($value['len']) ) {
                                $query .= $field."='".$this->db->quote($this->db->truncate(from_html($this->$field),$value['len']))."'";
                            } else {
                                $query .= $field."='".$this->db->quote($this->$field)."'";
                            }
                        }
                    }
                }
            }

            if($isUpdate)
            {
                $query = $query." WHERE ID = '$this->id'";
                $GLOBALS['log']->info("Update $this->object_name: ".$query);
            }
            else
            {
                $GLOBALS['log']->info("Insert: ".$query);
            }
            $GLOBALS['log']->info("Save: $query");
            $this->db->query($query, true);
        }
        //process if type is set to mssql
        if ($this->db->dbType == 'mssql')
        {
            if($isUpdate)
            {
                // build out the SQL UPDATE statement.
                $query = "UPDATE " . $this->table_name." SET ";
                $firstPass = 0;
                foreach($this->field_defs as $field=>$value)
                {
                    if(!isset($value['source']) || $value['source'] == 'db')
                    {
                        // Do not write out the id field on the update statement.
                        // We are not allowed to change ids.
                        if($isUpdate && ('id' == $field))
                            continue;

                        // If the field is an auto_increment field, then we shouldn't be setting it.  This was added
                        // specially for Bugs and Cases which have a number associated with them.
                        if ($isUpdate && isset($this->field_name_map[$field]['auto_increment']) &&
                            $this->field_name_map[$field]['auto_increment'] == true)
                            continue;

                        //custom fields handle their save seperatley
                        if(isset($this->field_name_map) && !empty($this->field_name_map[$field]['custom_type']))
                            continue;

                        // Only assign variables that have been set.
                        if(isset($this->$field))
                        {
                            //bug: 37908 - this is to handle the issue where the bool value is false, but strlen(false) <= so it will
                            //set the default value. TODO change this code to esend all fields through getFieldValue() like DbHelper->insertSql
                            if(!empty($value['type']) && $value['type'] == 'bool'){
                                $this->$field = $this->getFieldValue($field);
                            }

                            if(strlen($this->$field) <= 0)
                            {
                                if(!$isUpdate && isset($value['default']) && (strlen($value['default']) > 0))
                                {
                                    $this->$field = $value['default'];
                                }
                                else
                                {
                                    $this->$field = null;
                                }
                            }
                            // Try comparing this element with the head element.
                            if(0 == $firstPass)
                                $firstPass = 1;
                            else
                                $query .= ", ";

                            if(is_null($this->$field))
                            {
                                $query .= $field."=null";
                            }
                            elseif ( isset($value['len']) )
                           {
                               $query .= $field."='".$this->db->quote($this->db->truncate(from_html($this->$field),$value['len']))."'";
                           }
                           else
                            {
                                $query .= $field."='".$this->db->quote($this->$field)."'";
                            }
                        }
                    }
                }
                $query = $query." WHERE ID = '$this->id'";
                $GLOBALS['log']->info("Update $this->object_name: ".$query);
            }
            else
            {
                $colums = array();
                $values = array();
                foreach($this->field_defs as $field=>$value)
                {
                    if(!isset($value['source']) || $value['source'] == 'db')
                    {
                        // Do not write out the id field on the update statement.
                        // We are not allowed to change ids.
                        //if($isUpdate && ('id' == $field)) continue;
                        //custom fields handle there save seperatley

                        if(isset($this->field_name_map) && !empty($this->field_name_map[$field]['custom_module']))
                        continue;

                        // Only assign variables that have been set.
                        if(isset($this->$field))
                        {
                            //trim the value in case empty space is passed in.
                            //this will allow default values set in db to take effect, otherwise
                            //will insert blanks into db
                            $trimmed_field = trim($this->$field);
                            //if this value is empty, do not include the field value in statement
                            if($trimmed_field =='')
                            {
                                continue;
                            }
                            //bug: 37908 - this is to handle the issue where the bool value is false, but strlen(false) <= so it will
                            //set the default value. TODO change this code to esend all fields through getFieldValue() like DbHelper->insertSql
                            if(!empty($value['type']) && $value['type'] == 'bool'){
                                $this->$field = $this->getFieldValue($field);
                            }
                            //added check for ints because sql-server does not like casting varchar with a decimal value
                            //into an int.
                            if(isset($value['type']) and $value['type']=='int') {
                                $values[] = $this->db->quote($this->$field);
                            } elseif ( isset($value['len']) ) {
                                $values[] = "'".$this->db->quote($this->db->truncate(from_html($this->$field),$value['len']))."'";
                            } else {
                                $values[] = "'".$this->db->quote($this->$field)."'";

                            }
                            $columns[] = $field;
                        }
                    }
                }
                // build out the SQL INSERT statement.
                $query = "INSERT INTO $this->table_name (" .implode("," , $columns). " ) VALUES ( ". implode("," , $values). ')';
                $GLOBALS['log']->info("Insert: ".$query);
            }

            $GLOBALS['log']->info("Save: $query");
            $this->db->query($query, true);
        }
        if (!empty($dataChanges) && is_array($dataChanges))
        {
            foreach ($dataChanges as $change)
            {
                $this->dbManager->helper->save_audit_records($this,$change);
            }
        }


            // let subclasses save related field changes
            $this->save_relationship_changes($isUpdate);

        //If we aren't in setup mode and we have a current user and module, then we track
        if(isset($GLOBALS['current_user']) && isset($this->module_dir))
        {
            $this->track_view($current_user->id, $this->module_dir, 'save');
        }

        $this->call_custom_logic('after_save', '');

        return $this->id;
    }


    /**
     * Performs a check if the record has been modified since the specified date
     *
     * @param date $date Datetime for verification
     * @param string $modified_user_id User modified by
     */
    function has_been_modified_since($date, $modified_user_id)
    {
        global $current_user;
        if (isset($current_user))
        {
            if ($this->db->dbType == 'mssql')
                $date_modified_string = 'CONVERT(varchar(20), date_modified, 120)';
            else
                $date_modified_string = 'date_modified';

            $query = "SELECT date_modified FROM $this->table_name WHERE id='$this->id' AND modified_user_id != '$current_user->id' AND (modified_user_id != '$modified_user_id' OR $date_modified_string > " . db_convert("'".$date."'", 'datetime') . ')';
            $result = $this->db->query($query);

            if($this->db->fetchByAssoc($result))
            {
                return true;
            }
        }
        return false;
    }

    /**
    * Determines which users receive a notification
    */
    function get_notification_recipients() {
        $notify_user = new User();
        $notify_user->retrieve($this->assigned_user_id);
        $this->new_assigned_user_name = $notify_user->full_name;

        $GLOBALS['log']->info("Notifications: recipient is $this->new_assigned_user_name");

        $user_list = array($notify_user);
        return $user_list;
        /*
        //send notifications to followers, but ensure to not query for the assigned_user.
        return SugarFollowing::getFollowers($this, $notify_user);
        */
    }

    /**
    * Handles sending out email notifications when items are first assigned to users
    *
    * @param string $notify_user user to notify
    * @param string $admin the admin user that sends out the notification
    */
    function send_assignment_notifications($notify_user, $admin)
    {
        global $current_user;

        if(($this->object_name == 'Meeting' || $this->object_name == 'Call') || $notify_user->receive_notifications)
        {
            $sendToEmail = $notify_user->emailAddress->getPrimaryAddress($notify_user);
            $sendEmail = TRUE;
            if(empty($sendToEmail)) {
                $GLOBALS['log']->warn("Notifications: no e-mail address set for user {$notify_user->user_name}, cancelling send");
                $sendEmail = FALSE;
            }

            $notify_mail = $this->create_notification_email($notify_user);
            $notify_mail->setMailerForSystem();

            if(empty($admin->settings['notify_send_from_assigning_user'])) {
                $notify_mail->From = $admin->settings['notify_fromaddress'];
                $notify_mail->FromName = (empty($admin->settings['notify_fromname'])) ? "" : $admin->settings['notify_fromname'];
            } else {
                // Send notifications from the current user's e-mail (ifset)
                $fromAddress = $current_user->emailAddress->getReplyToAddress($current_user);
                $fromAddress = !empty($fromAddress) ? $fromAddress : $admin->settings['notify_fromaddress'];
                $notify_mail->From = $fromAddress;
                //Use the users full name is available otherwise default to system name
                $from_name = !empty($admin->settings['notify_fromname']) ? $admin->settings['notify_fromname'] : "";
                $from_name = !empty($current_user->full_name) ? $current_user->full_name : $from_name;
                $notify_mail->FromName = $from_name;
            }

            if($sendEmail && !$notify_mail->Send()) {
                $GLOBALS['log']->fatal("Notifications: error sending e-mail (method: {$notify_mail->Mailer}), (error: {$notify_mail->ErrorInfo})");
            } else {
                $GLOBALS['log']->fatal("Notifications: e-mail successfully sent");
            }

        }
    }

    /**
    * This function handles create the email notifications email.
    * @param string $notify_user the user to send the notification email to
    */
    function create_notification_email($notify_user) {
        global $sugar_version;
        global $sugar_config;
        global $app_list_strings;
        global $current_user;
        global $locale;
        global $beanList;
        $OBCharset = $locale->getPrecedentPreference('default_email_charset');


        require_once("include/SugarPHPMailer.php");

        $notify_address = $notify_user->emailAddress->getPrimaryAddress($notify_user);
        $notify_name = $notify_user->full_name;
        $GLOBALS['log']->debug("Notifications: user has e-mail defined");

        $notify_mail = new SugarPHPMailer();
        $notify_mail->AddAddress($notify_address,$locale->translateCharsetMIME(trim($notify_name), 'UTF-8', $OBCharset));

        if(empty($_SESSION['authenticated_user_language'])) {
            $current_language = $sugar_config['default_language'];
        } else {
            $current_language = $_SESSION['authenticated_user_language'];
        }
        $xtpl = new XTemplate(get_notify_template_file($current_language));
        if($this->module_dir == "Cases") {
            $template_name = "Case"; //we should use Case, you can refer to the en_us.notify_template.html.
        }
        else {
            $template_name = $beanList[$this->module_dir]; //bug 20637, in workflow this->object_name = strange chars.
        }

        $this->current_notify_user = $notify_user;

        if(in_array('set_notification_body', get_class_methods($this))) {
            $xtpl = $this->set_notification_body($xtpl, $this);
        } else {
            $xtpl->assign("OBJECT", $this->object_name);
            $template_name = "Default";
        }
        if(!empty($_SESSION["special_notification"]) && $_SESSION["special_notification"]) {
            $template_name = $beanList[$this->module_dir].'Special';
        }
        if($this->special_notification) {
            $template_name = $beanList[$this->module_dir].'Special';
        }
        $xtpl->assign("ASSIGNED_USER", $this->new_assigned_user_name);
        $xtpl->assign("ASSIGNER", $current_user->name);
        $port = '';

        if(isset($_SERVER['SERVER_PORT']) && $_SERVER['SERVER_PORT'] != 80 && $_SERVER['SERVER_PORT'] != 443) {
            $port = $_SERVER['SERVER_PORT'];
        }

        if (!isset($_SERVER['HTTP_HOST'])) {
            $_SERVER['HTTP_HOST'] = '';
        }

        $httpHost = $_SERVER['HTTP_HOST'];

        if($colon = strpos($httpHost, ':')) {
            $httpHost    = substr($httpHost, 0, $colon);
        }

        $parsedSiteUrl = parse_url($sugar_config['site_url']);
        $host = $parsedSiteUrl['host'];
        if(!isset($parsedSiteUrl['port'])) {
            $parsedSiteUrl['port'] = 80;
        }

        $port		= ($parsedSiteUrl['port'] != 80) ? ":".$parsedSiteUrl['port'] : '';
        $path		= !empty($parsedSiteUrl['path']) ? $parsedSiteUrl['path'] : "";
        $cleanUrl	= "{$parsedSiteUrl['scheme']}://{$host}{$port}{$path}";

        $xtpl->assign("URL", $cleanUrl."/index.php?module={$this->module_dir}&action=DetailView&record={$this->id}");
        $xtpl->assign("SUGAR", "Sugar v{$sugar_version}");
        $xtpl->parse($template_name);
        $xtpl->parse($template_name . "_Subject");

        $notify_mail->Body = from_html(trim($xtpl->text($template_name)));
        $notify_mail->Subject = from_html($xtpl->text($template_name . "_Subject"));

        // cn: bug 8568 encode notify email in User's outbound email encoding
        $notify_mail->prepForOutbound();

        return $notify_mail;
    }

    /**
    * This function is a good location to save changes that have been made to a relationship.
    * This should be overriden in subclasses that have something to save.
    *
    * @param $is_update true if this save is an update.
    */
function save_relationship_changes($is_update, $exclude=array())
    {
        $new_rel_id = false;
        $new_rel_link = false;
        //this allows us to dynamically relate modules without adding it to the relationship_fields array
        if(!empty($_REQUEST['relate_id']) && !in_array($_REQUEST['relate_to'], $exclude) && $_REQUEST['relate_id'] != $this->id){
            $new_rel_id = $_REQUEST['relate_id'];
            $new_rel_relname = $_REQUEST['relate_to'];
            if(!empty($this->in_workflow) && !empty($this->not_use_rel_in_req)) {
                $new_rel_id = $this->new_rel_id;
                $new_rel_relname = $this->new_rel_relname;
            }
            $new_rel_link = $new_rel_relname;
            //Try to find the link in this bean based on the relationship
            foreach ( $this->field_defs as $key => $def ) {
                if (isset($def['type']) && $def['type'] == 'link'
                && isset($def['relationship']) && $def['relationship'] == $new_rel_relname) {
                    $new_rel_link = $key;
                }
            }
        }

        // First we handle the preset fields listed in the fixed relationship_fields array hardcoded into the OOB beans
        // TODO: remove this mechanism and replace with mechanism exclusively based on the vardefs
        if (isset($this->relationship_fields) && is_array($this->relationship_fields))
        {
            foreach ($this->relationship_fields as $id=>$rel_name)
            {

                if(in_array($id, $exclude))continue;

                if(!empty($this->$id))
                {
                    $GLOBALS['log']->debug('save_relationship_changes(): From relationship_field array - adding a relationship record: '.$rel_name . ' = ' . $this->$id);
                    //already related the new relationship id so let's set it to false so we don't add it again using the _REQUEST['relate_i'] mechanism in a later block
                    if($this->$id == $new_rel_id){
                        $new_rel_id = false;
                    }
                    $this->load_relationship($rel_name);
                    $this->$rel_name->add($this->$id);
                    $related = true;
                }
                else
                {
                    //if before value is not empty then attempt to delete relationship
                    if(!empty($this->rel_fields_before_value[$id]))
                    {
                        $GLOBALS['log']->debug('save_relationship_changes(): From relationship_field array - attempting to remove the relationship record, using relationship attribute'.$rel_name);
                        $this->load_relationship($rel_name);
                        $this->$rel_name->delete($this->id,$this->rel_fields_before_value[$id]);
                    }
                }
            }
        }

/*      Next, we'll attempt to update all of the remaining relate fields in the vardefs that have 'save' set in their field_def
        Only the 'save' fields should be saved as some vardef entries today are not for display only purposes and break the application if saved
        If the vardef has entries for field <a> of type relate, where a->id_name = <b> and field <b> of type link
        then we receive a value for b from the MVC in the _REQUEST, and it should be set in the bean as $this->$b
*/

        foreach ( $this->field_defs as $def )
        {
           if ($def [ 'type' ] == 'relate' && isset ( $def [ 'id_name'] ) && isset ( $def [ 'link'] ) && isset ( $def[ 'save' ]) )
        {
            if (  in_array( $def['id_name'], $exclude) || in_array( $def['id_name'], $this->relationship_fields ) )
                continue ; // continue to honor the exclude array and exclude any relationships that will be handled by the relationship_fields mechanism

            if (isset( $this->field_defs[ $def [ 'link' ] ] ))
            {

                    $linkfield = $this->field_defs[$def [ 'link' ]] ;

                    if ($this->load_relationship ( $def [ 'link' ])){
                        if (!empty($this->rel_fields_before_value[$def [ 'id_name' ]]))
                        {
                            //if before value is not empty then attempt to delete relationship
                            $GLOBALS['log']->debug("save_relationship_changes(): From field_defs - attempting to remove the relationship record: {$def [ 'link' ]} = {$this->rel_fields_before_value[$def [ 'id_name' ]]}");
                            $this->$def ['link' ]->delete($this->id, $this->rel_fields_before_value[$def [ 'id_name' ]] );
                        }
                        if (!empty($this->$def['id_name']) && is_string($this->$def['id_name']))
                        {
                            $GLOBALS['log']->debug("save_relationship_changes(): From field_defs - attempting to add a relationship record - {$def [ 'link' ]} = {$this->$def [ 'id_name' ]}" );
                            $this->$def ['link' ]->add($this->$def['id_name']);
                        }
                    } else {
                        $GLOBALS['log']->fatal("Failed to load relationship {$def [ 'link' ]} while saving {$this->module_dir}");
                    }
                }
        }
        }

        // Finally, we update a field listed in the _REQUEST['*/relate_id']/_REQUEST['relate_to'] mechanism (if it hasn't already been updated above)
        if(!empty($new_rel_id)){

            if($this->load_relationship($new_rel_link)){
                $this->$new_rel_link->add($new_rel_id);

            }else{
                $lower_link = strtolower($new_rel_link);
                if($this->load_relationship($lower_link)){
                    $this->$lower_link->add($new_rel_id);

                }else{
                    require_once('data/Link.php');
                    $rel = Relationship::retrieve_by_modules($new_rel_link, $this->module_dir, $GLOBALS['db'], 'many-to-many');

                    if(!empty($rel)){
                        foreach($this->field_defs as $field=>$def){
                            if($def['type'] == 'link' && !empty($def['relationship']) && $def['relationship'] == $rel){
                                $this->load_relationship($field);
                                $this->$field->add($new_rel_id);
                                return;

                            }

                        }
                        //ok so we didn't find it in the field defs let's save it anyway if we have the relationshp

                        $this->$rel=new Link($rel, $this, array());
                        $this->$rel->add($new_rel_id);
                    }
                }

            }

        }

    }

    /**
    * This function retrieves a record of the appropriate type from the DB.
    * It fills in all of the fields from the DB into the object it was called on.
    *
    * @param $id - If ID is specified, it overrides the current value of $this->id.  If not specified the current value of $this->id will be used.
    * @return this - The object that it was called apon or null if exactly 1 record was not found.
    *
	*/

	function check_date_relationships_load()
	{
		global $disable_date_format;
		global $timedate;
		if (empty($timedate))
			$timedate=TimeDate::getInstance();

		if(empty($this->field_defs))
		{
			return;
		}
		foreach($this->field_defs as $fieldDef)
		{
			$field = $fieldDef['name'];
			if(!isset($this->processed_dates_times[$field]))
			{
				$this->processed_dates_times[$field] = '1';
				if(empty($this->$field)) continue;
				if($field == 'date_modified' || $field == 'date_entered')
				{
					$this->$field = from_db_convert($this->$field, 'datetime');
					if(empty($disable_date_format)) {
						$this->$field = $timedate->to_display_date_time($this->$field);
					}
				}
				elseif(isset($this->field_name_map[$field]['type']))
				{
					$type = $this->field_name_map[$field]['type'];

					if($type == 'relate'  && isset($this->field_name_map[$field]['custom_module']))
					{
						$type = $this->field_name_map[$field]['type'];
					}

					if($type == 'date')
					{
						$this->$field = from_db_convert($this->$field, 'date');

						if($this->$field == '0000-00-00')
						{
							$this->$field = '';
						} elseif(!empty($this->field_name_map[$field]['rel_field']))
						{
							$rel_field = $this->field_name_map[$field]['rel_field'];

							if(!empty($this->$rel_field))
							{
								$this->$rel_field=from_db_convert($this->$rel_field, 'time');
								if(empty($disable_date_format)) {
									$mergetime = $timedate->merge_date_time($this->$field,$this->$rel_field);
									$this->$field = $timedate->to_display_date($mergetime);
									$this->$rel_field = $timedate->to_display_time($mergetime);
								}
							}
						}
						else
						{
							if(empty($disable_date_format)) {
								$this->$field = $timedate->to_display_date($this->$field, false);
							}
						}
					} elseif($type == 'datetime' || $type == 'datetimecombo')
					{
						if($this->$field == '0000-00-00 00:00:00')
						{
							$this->$field = '';
						}
						else
						{
							$this->$field = from_db_convert($this->$field, 'datetime');
							if(empty($disable_date_format)) {
								$this->$field = $timedate->to_display_date_time($this->$field, true, true);
							}
						}
					} elseif($type == 'time')
					{
						if($this->$field == '00:00:00')
						{
							$this->$field = '';
						} else
						{
							//$this->$field = from_db_convert($this->$field, 'time');
							if(empty($this->field_name_map[$field]['rel_field']) && empty($disable_date_format))
							{
								$this->$field = $timedate->to_display_time($this->$field,true, false);
							}
						}
					} elseif($type == 'encrypt' && empty($disable_date_format)){
						$this->$field = $this->decrypt_after_retrieve($this->$field);
					}
				}
			}
		}
	}

    /**
     * This function processes the fields before save.
     * Interal function, do not override.
     */
    function preprocess_fields_on_save()
    {
        $GLOBALS['log']->deprecated('SugarBean.php: preprocess_fields_on_save() is deprecated');
    }

    /**
    * Removes formatting from values posted from the user interface.
     * It only unformats numbers.  Function relies on user/system prefernce for format strings.
     *
     * Internal Function, do not override.
    */
    function unformat_all_fields()
    {
        $GLOBALS['log']->deprecated('SugarBean.php: unformat_all_fields() is deprecated');
    }

    /**
    * This functions adds formatting to all number fields before presenting them to user interface.
     *
     * Internal function, do not override.
    */
    function format_all_fields()
    {
        $GLOBALS['log']->deprecated('SugarBean.php: format_all_fields() is deprecated');
    }

    function format_field($fieldDef)
        {
        $GLOBALS['log']->deprecated('SugarBean.php: format_field() is deprecated');
        }

    /**
     * Function corrects any bad formatting done by 3rd party/custom code
     *
     * This function will be removed in a future release, it is only here to assist upgrading existing code that expects formatted data in the bean
     */
    function fixUpFormatting()
    {
        global $timedate;
        static $boolean_false_values = array('off', 'false', '0', 'no');


        foreach($this->field_defs as $field=>$def)
            {
            if ( !isset($this->$field) ) {
                continue;
                }
            if ( (isset($def['source'])&&$def['source']=='non-db') || $field == 'deleted' ) {
                continue;
            }
            if ( isset($this->fetched_row[$field]) && $this->$field == $this->fetched_row[$field] ) {
                // Don't hand out warnings because the field was untouched between retrieval and saving, most database drivers hand pretty much everything back as strings.
                continue;
            }
            $reformatted = false;
            switch($def['type']) {
                case 'datetime':
                case 'datetimecombo':
                    if(empty($this->$field)) break;
                    if ($this->$field == 'NULL') {
                    	$this->$field = '';
                    	break;
                    }
                    if ( ! preg_match('/^[0-9]{4}-[0-9]{2}-[0-9]{2} [0-9]{2}:[0-9]{2}:[0-9]{2}$/',$this->$field) ) {
                        // This appears to be formatted in user date/time
                        $this->$field = $timedate->to_db($this->$field);
                        $reformatted = true;
                    }
                    break;
                case 'date':
                    if(empty($this->$field)) break;
                    if ($this->$field == 'NULL') {
                    	$this->$field = '';
                    	break;
                    }
                    if ( ! preg_match('/^[0-9]{4}-[0-9]{2}-[0-9]{2}$/',$this->$field) ) {
                        // This date appears to be formatted in the user's format
                        $this->$field = $timedate->to_db_date($this->$field, false);
                        $reformatted = true;
                    }
                    break;
                case 'time':
                    if(empty($this->$field)) break;
                    if ($this->$field == 'NULL') {
                    	$this->$field = '';
                    	break;
                    }
                    if ( preg_match('/(am|pm)/i',$this->$field) ) {
                        // This time appears to be formatted in the user's format
                        $this->$field = $timedate->fromUserTime($this->$field)->format(TimeDate::DB_TIME_FORMAT);
                        $reformatted = true;
                    }
                    break;
                case 'double':
                case 'decimal':
                case 'currency':
                case 'float':
                    if ( $this->$field === '' || $this->$field == NULL || $this->$field == 'NULL') {
                        continue;
                    }
                    if ( is_string($this->$field) ) {
                        $this->$field = (float)unformat_number($this->$field);
                        $reformatted = true;
                    }
                    break;
               case 'uint':
               case 'ulong':
               case 'long':
               case 'short':
               case 'tinyint':
               case 'int':
                    if ( $this->$field === '' || $this->$field == NULL || $this->$field == 'NULL') {
                        continue;
                    }
                    if ( is_string($this->$field) ) {
                        $this->$field = (int)unformat_number($this->$field);
                        $reformatted = true;
                    }
                   break;
               case 'bool':
                   if (empty($this->$field)) {
                       $this->$field = false;
                   } else if(true === $this->$field || 1 == $this->$field) {
                       $this->$field = true;
                   } else if(in_array(strval($this->$field), $boolean_false_values)) {
                       $this->$field = false;
                       $reformatted = true;
                   } else {
                       $this->$field = true;
                       $reformatted = true;
                   }
                   break;
               case 'encrypt':
                    $this->$field = $this->encrpyt_before_save($this->$field);
                    break;
            }
            if ( $reformatted ) {
                $GLOBALS['log']->deprecated('Formatting correction: '.$this->module_dir.'->'.$field.' had formatting automatically corrected. This will be removed in the future, please upgrade your external code');
            }
        }

    }

    /**
     * Function fetches a single row of data given the primary key value.
     *
     * The fetched data is then set into the bean. The function also processes the fetched data by formattig
     * date/time and numeric values.
     *
     * @param string $id Optional, default -1, is set to -1 id value from the bean is used, else, passed value is used
     * @param boolean $encode Optional, default true, encodes the values fetched from the database.
     * @param boolean $deleted Optional, default true, if set to false deleted filter will not be added.
     *
     * Internal function, do not override.
    */
    function retrieve($id = -1, $encode=true,$deleted=true)
    {

        $custom_logic_arguments['id'] = $id;
        $this->call_custom_logic('before_retrieve', $custom_logic_arguments);

        if ($id == -1)
        {
            $id = $this->id;
        }
        if(isset($this->custom_fields))
        {
            $custom_join = $this->custom_fields->getJOIN();
        }
        else
            $custom_join = false;

        if($custom_join)
        {
            $query = "SELECT $this->table_name.*". $custom_join['select']. " FROM $this->table_name ";
        }
        else
        {
            $query = "SELECT $this->table_name.* FROM $this->table_name ";
        }

        if($custom_join)
        {
            $query .= ' ' . $custom_join['join'];
        }
        $query .= " WHERE $this->table_name.id = '$id' ";
        if ($deleted) $query .= " AND $this->table_name.deleted=0";
        $GLOBALS['log']->debug("Retrieve $this->object_name : ".$query);
        //requireSingleResult has beeen deprecated.
        //$result = $this->db->requireSingleResult($query, true, "Retrieving record by id $this->table_name:$id found ");
        $result = $this->db->limitQuery($query,0,1,true, "Retrieving record by id $this->table_name:$id found ");
        if(empty($result))
        {
            return null;
        }

        $row = $this->db->fetchByAssoc($result, -1, $encode);
        if(empty($row))
        {
            return null;
        }

        //make copy of the fetched row for construction of audit record and for business logic/workflow
        $this->fetched_row=$row;
        $this->populateFromRow($row);

        global $module, $action;
        //Just to get optimistic locking working for this release
        if($this->optimistic_lock && $module == $this->module_dir && $action =='EditView' )
        {
            $_SESSION['o_lock_id']= $id;
            $_SESSION['o_lock_dm']= $this->date_modified;
            $_SESSION['o_lock_on'] = $this->object_name;
        }
        $this->processed_dates_times = array();
        $this->check_date_relationships_load();

        if($custom_join)
        {
            $this->custom_fields->fill_relationships();
        }

        $this->fill_in_additional_detail_fields();
        $this->fill_in_relationship_fields();
        //make a copy of fields in the relatiosnhip_fields array. these field values will be used to
        //clear relatioship.
        foreach ( $this->field_defs as $key => $def )
        {
            if ($def [ 'type' ] == 'relate' && isset ( $def [ 'id_name'] ) && isset ( $def [ 'link'] ) && isset ( $def[ 'save' ])) {
                if (isset($this->$key)) {
                    $this->rel_fields_before_value[$key]=$this->$key;
                    if (isset($this->$def [ 'id_name']))
                        $this->rel_fields_before_value[$def [ 'id_name']]=$this->$def [ 'id_name'];
                }
                else
                    $this->rel_fields_before_value[$key]=null;
           }
        }
        if (isset($this->relationship_fields) && is_array($this->relationship_fields))
        {
            foreach ($this->relationship_fields as $rel_id=>$rel_name)
            {
                if (isset($this->$rel_id))
                    $this->rel_fields_before_value[$rel_id]=$this->$rel_id;
                else
                    $this->rel_fields_before_value[$rel_id]=null;
            }
        }

        // call the custom business logic
        $custom_logic_arguments['id'] = $id;
        $custom_logic_arguments['encode'] = $encode;
        $this->call_custom_logic("after_retrieve", $custom_logic_arguments);
        unset($custom_logic_arguments);
        return $this;
    }

    /**
     * Sets value from fetched row into the bean.
     *
     * @param array $row Fetched row
     * @todo loop through vardefs instead
     * @internal runs into an issue when populating from field_defs for users - corrupts user prefs
     *
     * Internal function, do not override.
     */
    function populateFromRow($row)
    {
        $nullvalue='';
        foreach($this->field_defs as $field=>$field_value)
        {
            if($field == 'user_preferences' && $this->module_dir == 'Users')
                continue;
            $rfield = $field; // fetch returns it in lowercase only
            if(isset($row[$rfield]))
            {
                $this->$field = $row[$rfield];
                $owner = $rfield . '_owner';
                if(!empty($row[$owner])){
                    $this->$owner = $row[$owner];
                }
            }
            else
            {
                $this->$field = $nullvalue;
            }
        }
    }



    /**
    * Add any required joins to the list count query.  The joins are required if there
    * is a field in the $where clause that needs to be joined.
    *
    * @param string $query
    * @param string $where
    *
    * Internal Function, do Not override.
    */
    function add_list_count_joins(&$query, $where)
    {
        $custom_join = $this->custom_fields->getJOIN();
        if($custom_join)
        {
            $query .= $custom_join['join'];
        }

    }

    /**
    * Changes the select expression of the given query to be 'count(*)' so you
    * can get the number of items the query will return.  This is used to
    * populate the upper limit on ListViews.
     *
     * @param string $query Select query string
     * @return string count query
     *
     * Internal function, do not override.
    */
    function create_list_count_query($query)
    {
        // remove the 'order by' clause which is expected to be at the end of the query
        $pattern = '/\sORDER BY.*/is';  // ignores the case
        $replacement = '';
        $query = preg_replace($pattern, $replacement, $query);
        //handle distinct clause
        $star = '*';
        if(substr_count(strtolower($query), 'distinct')){
            if (!empty($this->seed) && !empty($this->seed->table_name ))
                $star = 'DISTINCT ' . $this->seed->table_name . '.id';
            else
                $star = 'DISTINCT ' . $this->table_name . '.id';

        }

        // change the select expression to 'count(*)'
        $pattern = '/SELECT(.*?)(\s){1}FROM(\s){1}/is';  // ignores the case
        $replacement = 'SELECT count(' . $star . ') c FROM ';

        //if the passed query has union clause then replace all instances of the pattern.
        //this is very rare. I have seen this happening only from projects module.
        //in addition to this added a condition that has  union clause and uses
        //sub-selects.
        if (strstr($query," UNION ALL ") !== false) {

    		//seperate out all the queries.
    		$union_qs=explode(" UNION ALL ", $query);
    		foreach ($union_qs as $key=>$union_query) {
        		$star = '*';
				preg_match($pattern, $union_query, $matches);
				if (!empty($matches)) {
					if (stristr($matches[0], "distinct")) {
			          	if (!empty($this->seed) && !empty($this->seed->table_name ))
			          		$star = 'DISTINCT ' . $this->seed->table_name . '.id';
			          	else
			          		$star = 'DISTINCT ' . $this->table_name . '.id';
					}
				} // if
    			$replacement = 'SELECT count(' . $star . ') c FROM ';
    			$union_qs[$key] = preg_replace($pattern, $replacement, $union_query,1);
    		}
    		$modified_select_query=implode(" UNION ALL ",$union_qs);
    	} else {
	    	$modified_select_query = preg_replace($pattern, $replacement, $query,1);
    	}

		return $modified_select_query;
    }

    /**
    * This function returns a paged list of the current object type.  It is intended to allow for
    * hopping back and forth through pages of data.  It only retrieves what is on the current page.
    *
    * @internal This method must be called on a new instance.  It trashes the values of all the fields in the current one.
    * @param string $order_by
    * @param string $where Additional where clause
    * @param int $row_offset Optaional,default 0, starting row number
    * @param init $limit Optional, default -1
    * @param int $max Optional, default -1
    * @param boolean $show_deleted Optioanl, default 0, if set to 1 system will show deleted records.
    * @return array Fetched data.
    *
    * Internal function, do not override.
    *
    */
    function get_list($order_by = "", $where = "", $row_offset = 0, $limit=-1, $max=-1, $show_deleted = 0, $singleSelect=false)
    {
        $GLOBALS['log']->debug("get_list:  order_by = '$order_by' and where = '$where' and limit = '$limit'");
        if(isset($_SESSION['show_deleted']))
        {
            $show_deleted = 1;
        }
        $order_by=$this->process_order_by($order_by, null);

        if($this->bean_implements('ACL') && ACLController::requireOwner($this->module_dir, 'list') )
        {
            global $current_user;
            $owner_where = $this->getOwnerWhere($current_user->id);

            //rrs - because $this->getOwnerWhere() can return '' we need to be sure to check for it and
            //handle it properly else you could get into a situation where you are create a where stmt like
            //WHERE .. AND ''
            if(!empty($owner_where)){
                if(empty($where)){
                    $where = $owner_where;
                }else{
                    $where .= ' AND '.  $owner_where;
                }
            }
        }
        $query = $this->create_new_list_query($order_by, $where,array(),array(), $show_deleted,'',false,null,$singleSelect);
        return $this->process_list_query($query, $row_offset, $limit, $max, $where);
    }

    /**
    * Prefixes column names with this bean's table name.
    * This call can be ignored for  mysql since it does a better job than Oracle in resolving ambiguity.
    *
    * @param string $order_by  Order by clause to be processed
    * @param string $submodule name of the module this order by clause is for
    * @return string Processed order by clause
    *
    * Internal function, do not override.
    */
    function process_order_by ($order_by, $submodule)
    {
        if (empty($order_by))
            return $order_by;
        $bean_queried = "";
        //submodule is empty,this is for list object in focus
        if (empty($submodule))
        {
            $bean_queried = &$this;
        }
        else
        {
            //submodule is set, so this is for subpanel, use submodule
            $bean_queried = $submodule;
        }
        $elements = explode(',',$order_by);
        foreach ($elements as $key=>$value)
        {
            if (strchr($value,'.') === false)
            {
                //value might have ascending and descending decorations
                $list_column = explode(' ',trim($value));
                if (isset($list_column[0]))
                {
                    $list_column_name=trim($list_column[0]);
                    if (isset($bean_queried->field_defs[$list_column_name]))
                    {
                        $source=isset($bean_queried->field_defs[$list_column_name]['source']) ? $bean_queried->field_defs[$list_column_name]['source']:'db';
                        if (empty($bean_queried->field_defs[$list_column_name]['table']) && $source=='db')
                        {
                            $list_column[0] = $bean_queried->table_name .".".$list_column[0] ;
                        }
                        if (empty($bean_queried->field_defs[$list_column_name]['table']) && $source=='custom_fields')
                        {
                            $list_column[0] = $bean_queried->table_name ."_cstm.".$list_column[0] ;
                        }
                        $value = implode($list_column,' ');
                        // Bug 38803 - Use CONVERT() function when doing an order by on ntext, text, and image fields
                        if ( $this->db->dbType == 'mssql'
                            && $source != 'non-db'
                            && in_array(
                                $this->db->getHelper()->getColumnType($this->db->getHelper()->getFieldType($bean_queried->field_defs[$list_column_name])),
                                array('ntext','text','image')
                                )
                            ) {
                        $value = "CONVERT(varchar(500),{$list_column[0]}) {$list_column[1]}";
                        }
                        // Bug 29011 - Use TO_CHAR() function when doing an order by on a clob field
                        if ( $this->db->dbType == 'oci8'
                            && $source != 'non-db'
                            && in_array(
                                $this->db->getHelper()->getColumnType($this->db->getHelper()->getFieldType($bean_queried->field_defs[$list_column_name])),
                                array('clob')
                                )
                            ) {
                        $value = "TO_CHAR({$list_column[0]}) {$list_column[1]}";
                        }
                    }
                    else
                    {
                        $GLOBALS['log']->debug("process_order_by: ($list_column[0]) does not have a vardef entry.");
                    }
                }
            }
            $elements[$key]=$value;
        }
        return implode($elements,',');

    }


    /**
    * Returns a detail object like retrieving of the current object type.
    *
    * It is intended for use in navigation buttons on the DetailView.  It will pass an offset and limit argument to the sql query.
    * @internal This method must be called on a new instance.  It overrides the values of all the fields in the current one.
    *
    * @param string $order_by
    * @param string $where Additional where clause
    * @param int $row_offset Optaional,default 0, starting row number
    * @param init $limit Optional, default -1
    * @param int $max Optional, default -1
    * @param boolean $show_deleted Optioanl, default 0, if set to 1 system will show deleted records.
    * @return array Fetched data.
    *
    * Internal function, do not override.
    */
    function get_detail($order_by = "", $where = "",  $offset = 0, $row_offset = 0, $limit=-1, $max=-1, $show_deleted = 0)
    {
        $GLOBALS['log']->debug("get_detail:  order_by = '$order_by' and where = '$where' and limit = '$limit' and offset = '$offset'");
        if(isset($_SESSION['show_deleted']))
        {
            $show_deleted = 1;
        }

        if($this->bean_implements('ACL') && ACLController::requireOwner($this->module_dir, 'list') )
        {
            global $current_user;
            $owner_where = $this->getOwnerWhere($current_user->id);

            if(empty($where))
            {
                $where = $owner_where;
            }
            else
            {
                $where .= ' AND '.  $owner_where;
            }
        }
        $query = $this->create_new_list_query($order_by, $where,array(),array(), $show_deleted, $offset);

        //Add Limit and Offset to query
        //$query .= " LIMIT 1 OFFSET $offset";

        return $this->process_detail_query($query, $row_offset, $limit, $max, $where, $offset);
    }

    /**
    * Fetches data from all related tables.
    *
    * @param object $child_seed
    * @param string $related_field_name relation to fetch data for
    * @param string $order_by Optional, default empty
    * @param string $where Optional, additional where clause
    * @return array Fetched data.
    *
    * Internal function, do not override.
    */
    function get_related_list($child_seed,$related_field_name, $order_by = "", $where = "",
    $row_offset = 0, $limit=-1, $max=-1, $show_deleted = 0)
    {
        global $layout_edit_mode;
        if(isset($layout_edit_mode) && $layout_edit_mode)
        {
            $response = array();
            $child_seed->assign_display_fields($child_seed->module_dir);
            $response['list'] = array($child_seed);
            $response['row_count'] = 1;
            $response['next_offset'] = 0;
            $response['previous_offset'] = 0;

            return $response;
        }
        $GLOBALS['log']->debug("get_related_list:  order_by = '$order_by' and where = '$where' and limit = '$limit'");
        if(isset($_SESSION['show_deleted']))
        {
            $show_deleted = 1;
        }

        $this->load_relationship($related_field_name);
        $query_array = $this->$related_field_name->getQuery(true);
        $entire_where = $query_array['where'];
        if(!empty($where))
        {
            if(empty($entire_where))
            {
                $entire_where = ' WHERE ' . $where;
            }
            else
            {
                $entire_where .= ' AND ' . $where;
            }
        }

        $query = 'SELECT '.$child_seed->table_name.'.* ' . $query_array['from'] . ' ' . $entire_where;
        if(!empty($order_by))
        {
            $query .= " ORDER BY " . $order_by;
        }

        return $child_seed->process_list_query($query, $row_offset, $limit, $max, $where);
    }


    protected static function build_sub_queries_for_union($subpanel_list, $subpanel_def, $parentbean, $order_by)
    {
        global $layout_edit_mode, $beanFiles, $beanList;
        $subqueries = array();
        foreach($subpanel_list as $this_subpanel)
        {
            if(!$this_subpanel->isDatasourceFunction() || ($this_subpanel->isDatasourceFunction()
                && isset($this_subpanel->_instance_properties['generate_select'])
                && $this_subpanel->_instance_properties['generate_select']==true))
            {
                //the custom query function must return an array with
                if ($this_subpanel->isDatasourceFunction()) {
                    $shortcut_function_name = $this_subpanel->get_data_source_name();
                    $parameters=$this_subpanel->get_function_parameters();
                    if (!empty($parameters))
                    {
                        //if the import file function is set, then import the file to call the custom function from
                        if (is_array($parameters)  && isset($parameters['import_function_file'])){
                            //this call may happen multiple times, so only require if function does not exist
                            if(!function_exists($shortcut_function_name)){
                                require_once($parameters['import_function_file']);
                            }
                            //call function from required file
                            $query_array = $shortcut_function_name($parameters);
                        }else{
                            //call function from parent bean
                            $query_array = $parentbean->$shortcut_function_name($parameters);
                        }
                    }
                    else
                    {
                        $query_array = $parentbean->$shortcut_function_name();
                    }
                }  else {
                    $related_field_name = $this_subpanel->get_data_source_name();
                    if (!$parentbean->load_relationship($related_field_name)){
                        unset ($parentbean->$related_field_name);
                        continue;
                    }
                    $query_array = $parentbean->$related_field_name->getQuery(true,array(),0,'',true, null, null, true);
                }
                $table_where = $this_subpanel->get_where();
                $where_definition = $query_array['where'];

                if(!empty($table_where))
                {
                    if(empty($where_definition))
                    {
                        $where_definition = $table_where;
                    }
                    else
                    {
                        $where_definition .= ' AND ' . $table_where;
                    }
                }

                $submodulename = $this_subpanel->_instance_properties['module'];
                $submoduleclass = $beanList[$submodulename];
                //require_once($beanFiles[$submoduleclass]);
                $submodule = new $submoduleclass();
                $subwhere = $where_definition;



                $subwhere = str_replace('WHERE', '', $subwhere);
                $list_fields = $this_subpanel->get_list_fields();
                foreach($list_fields as $list_key=>$list_field)
                {
                    if(isset($list_field['usage']) && $list_field['usage'] == 'display_only')
                    {
                        unset($list_fields[$list_key]);
                    }
                }
                if(!$subpanel_def->isCollection() && isset($list_fields[$order_by]) && isset($submodule->field_defs[$order_by])&& (!isset($submodule->field_defs[$order_by]['source']) || $submodule->field_defs[$order_by]['source'] == 'db'))
                {
                    $order_by = $submodule->table_name .'.'. $order_by;
                }
                $table_name = $this_subpanel->table_name;
                $panel_name=$this_subpanel->name;
                $params = array();
                $params['distinct'] = $this_subpanel->distinct_query();

                $params['joined_tables'] = $query_array['join_tables'];
                $params['include_custom_fields'] = !$subpanel_def->isCollection();
                $params['collection_list'] = $subpanel_def->get_inst_prop_value('collection_list');

                $subquery = $submodule->create_new_list_query('',$subwhere ,$list_fields,$params, 0,'', true,$parentbean);

                $subquery['select'] = $subquery['select']." , '$panel_name' panel_name ";
                $subquery['from'] = $subquery['from'].$query_array['join'];
                $subquery['query_array'] = $query_array;
                $subquery['params'] = $params;

                $subqueries[] = $subquery;
            }
        }
        return $subqueries;
    }

    /**
    * Constructs a query to fetch data for supanels and list views
     *
     * It constructs union queries for activities subpanel.
     *
     * @param Object $parentbean constructing queries for link attributes in this bean
     * @param string $order_by Optional, order by clause
     * @param string $sort_order Optional, sort order
     * @param string $where Optional, additional where clause
     *
     * Internal Function, do not overide.
    */
    function get_union_related_list($parentbean, $order_by = "", $sort_order='', $where = "",
    $row_offset = 0, $limit=-1, $max=-1, $show_deleted = 0, $subpanel_def)
    {
        $secondary_queries = array();
        global $layout_edit_mode, $beanFiles, $beanList;

		if(isset($_SESSION['show_deleted']))
		{
			$show_deleted = 1;
		}
		$final_query = '';
		$final_query_rows = '';
		$subpanel_list=array();
		if ($subpanel_def->isCollection())
		{
			$subpanel_def->load_sub_subpanels();
			$subpanel_list=$subpanel_def->sub_subpanels;
		}
		else
		{
			$subpanel_list[]=$subpanel_def;
		}

		$first = true;

		//Breaking the building process into two loops. The first loop gets a list of all the sub-queries.
		//The second loop merges the queries and forces them to select the same number of columns
		//All columns in a sub-subpanel group must have the same aliases
		//If the subpanel is a datasource function, it can't be a collection so we just poll that function for the and return that
		foreach($subpanel_list as $this_subpanel)
		{
			if($this_subpanel->isDatasourceFunction() && empty($this_subpanel->_instance_properties['generate_select']))
			{
				$shortcut_function_name = $this_subpanel->get_data_source_name();
				$parameters=$this_subpanel->get_function_parameters();
				if (!empty($parameters))
				{
					//if the import file function is set, then import the file to call the custom function from
					if (is_array($parameters)  && isset($parameters['import_function_file'])){
						//this call may happen multiple times, so only require if function does not exist
						if(!function_exists($shortcut_function_name)){
							require_once($parameters['import_function_file']);
						}
						//call function from required file
						$tmp_final_query =  $shortcut_function_name($parameters);
					}else{
						//call function from parent bean
						$tmp_final_query =  $parentbean->$shortcut_function_name($parameters);
					}
				}
				else
				{
					$tmp_final_query = $parentbean->$shortcut_function_name();
				}
				if(!$first)
				{
					$final_query_rows .= ' UNION ALL ( '.$parentbean->create_list_count_query($tmp_final_query, $parameters) . ' )';
					$final_query .= ' UNION ALL ( '.$tmp_final_query . ' )';
				} else {
					$final_query_rows = '(' . $parentbean->create_list_count_query($tmp_final_query, $parameters) . ')';
					$final_query = '(' . $tmp_final_query . ')';
					$first = false;
				}
			}
		}
		//If final_query is still empty, its time to build the sub-queries
		if (empty($final_query))
		{
			$subqueries = SugarBean::build_sub_queries_for_union($subpanel_list, $subpanel_def, $parentbean, $order_by);
			$all_fields = array();
			foreach($subqueries as $i => $subquery)
			{
				$query_fields = $GLOBALS['db']->helper->getSelectFieldsFromQuery($subquery['select']);
				foreach($query_fields as $field => $select)
				{
					if (!in_array($field, $all_fields))
						$all_fields[] = $field;
				}
				$subqueries[$i]['query_fields'] = $query_fields;
			}
			$first = true;
			//Now ensure the queries have the same set of fields in the same order.
			foreach($subqueries as $subquery)
			{
				$subquery['select'] = "SELECT";
				foreach($all_fields as $field)
				{
					if (!isset($subquery['query_fields'][$field]))
					{
						$subquery['select'] .= " ' ' $field,";
					}
					else
					{
						$subquery['select'] .= " {$subquery['query_fields'][$field]},";
					}
				}
				$subquery['select'] = substr($subquery['select'], 0 , strlen($subquery['select']) - 1);
				//Put the query into the final_query
				$query =  $subquery['select'] . " " . $subquery['from'] . " " . $subquery['where'];
				if(!$first)
				{
					$query = ' UNION ALL ( '.$query . ' )';
					$final_query_rows .= " UNION ALL ";
				} else {
					$query = '(' . $query . ')';
					$first = false;
				}
				$query_array = $subquery['query_array'];
				$select_position=strpos($query_array['select'],"SELECT");
				$distinct_position=strpos($query_array['select'],"DISTINCT");
				if ($select_position !== false && $distinct_position!= false)
				{
					$query_rows = "( ".substr_replace($query_array['select'],"SELECT count(",$select_position,6). ")" .  $subquery['from_min'].$query_array['join']. $subquery['where'].' )';
				}
				else
				{
					//resort to default behavior.
					$query_rows = "( SELECT count(*)".  $subquery['from_min'].$query_array['join']. $subquery['where'].' )';
                }
                if(!empty($subquery['secondary_select']))
                {

                    $subquerystring= $subquery['secondary_select'] . $subquery['secondary_from'].$query_array['join']. $subquery['where'];
                    if (!empty($subquery['secondary_where']))
                    {
                        if (empty($subquery['where']))
                        {
                            $subquerystring.=" WHERE " .$subquery['secondary_where'];
                        }
                        else
                        {
                            $subquerystring.=" AND " .$subquery['secondary_where'];
                        }
                    }
                    $secondary_queries[]=$subquerystring;
                }
                $final_query .= $query;
                $final_query_rows .= $query_rows;
            }
        }

        if(!empty($order_by))
        {
            $submodule = false;
            if(!$subpanel_def->isCollection())
            {
                $submodulename = $subpanel_def->_instance_properties['module'];
                $submoduleclass = $beanList[$submodulename];
                $submodule = new $submoduleclass();
            }
            if(!empty($submodule) && !empty($submodule->table_name))
            {
                $final_query .= " ORDER BY " .$parentbean->process_order_by($order_by, $submodule);

            }
            else
            {
                $final_query .= " ORDER BY ". $order_by . ' ';
            }
            if(!empty($sort_order))
            {
                $final_query .= ' ' .$sort_order;
            }
        }


        if(isset($layout_edit_mode) && $layout_edit_mode)
        {
            $response = array();
            if(!empty($submodule))
            {
                $submodule->assign_display_fields($submodule->module_dir);
                $response['list'] = array($submodule);
            }
            else
        {
                $response['list'] = array();
            }
            $response['parent_data'] = array();
            $response['row_count'] = 1;
            $response['next_offset'] = 0;
            $response['previous_offset'] = 0;

            return $response;
        }

        return $parentbean->process_union_list_query($parentbean, $final_query, $row_offset, $limit, $max, '',$subpanel_def, $final_query_rows, $secondary_queries);
    }


    /**
    * Returns a full (ie non-paged) list of the current object type.
    *
    * @param string $order_by the order by SQL parameter. defaults to ""
    * @param string $where where clause. defaults to ""
    * @param boolean $check_dates. defaults to false
    * @param int $show_deleted show deleted records. defaults to 0
    */
    function get_full_list($order_by = "", $where = "", $check_dates=false, $show_deleted = 0)
    {
        $GLOBALS['log']->debug("get_full_list:  order_by = '$order_by' and where = '$where'");
        if(isset($_SESSION['show_deleted']))
        {
            $show_deleted = 1;
        }
        $query = $this->create_new_list_query($order_by, $where,array(),array(), $show_deleted);
        return $this->process_full_list_query($query, $check_dates);
    }

    /**
     * Return the list query used by the list views and export button. Next generation of create_new_list_query function.
     *
     * Override this function to return a custom query.
     *
     * @param string $order_by custom order by clause
     * @param string $where custom where clause
     * @param array $filter Optioanal
     * @param array $params Optional     *
     * @param int $show_deleted Optional, default 0, show deleted records is set to 1.
     * @param string $join_type
     * @param boolean $return_array Optional, default false, response as array
     * @param object $parentbean creating a subquery for this bean.
     * @param boolean $singleSelect Optional, default false.
     * @return String select query string, optionally an array value will be returned if $return_array= true.
     */
    function create_new_list_query($order_by, $where,$filter=array(),$params=array(), $show_deleted = 0,$join_type='', $return_array = false,$parentbean=null, $singleSelect = false)
    {
        global $beanFiles, $beanList;
        $selectedFields = array();
        $secondarySelectedFields = array();
        $ret_array = array();
        $distinct = '';
        if($this->bean_implements('ACL') && ACLController::requireOwner($this->module_dir, 'list') )
        {
            global $current_user;
            $owner_where = $this->getOwnerWhere($current_user->id);
            if(empty($where))
            {
                $where = $owner_where;
            }
            else
            {
                $where .= ' AND '.  $owner_where;
            }
        }
        if(!empty($params['distinct']))
        {
            $distinct = ' DISTINCT ';
        }
        if(empty($filter))
        {
            $ret_array['select'] = " SELECT $distinct $this->table_name.* ";
        }
        else
        {
            $ret_array['select'] = " SELECT $distinct $this->table_name.id ";
        }
        $ret_array['from'] = " FROM $this->table_name ";
        $ret_array['from_min'] = $ret_array['from'];
        $ret_array['secondary_from'] = $ret_array['from'] ;
        $ret_array['where'] = '';
        $ret_array['order_by'] = '';
        //secondary selects are selects that need to be run after the primarty query to retrieve additional info on main
        if($singleSelect)
        {
            $ret_array['secondary_select']=& $ret_array['select'];
            $ret_array['secondary_from'] = & $ret_array['from'];
        }
        else
        {
            $ret_array['secondary_select'] = '';
        }
        $custom_join = false;
        if((!isset($params['include_custom_fields']) || $params['include_custom_fields']) &&  isset($this->custom_fields))
        {

            $custom_join = $this->custom_fields->getJOIN( empty($filter)? true: $filter );
            if($custom_join)
            {
                $ret_array['select'] .= ' ' .$custom_join['select'];
            }
        }
        if($custom_join)
        {
            $ret_array['from'] .= ' ' . $custom_join['join'];
        }
        $jtcount = 0;
        //LOOP AROUND FOR FIXIN VARDEF ISSUES
        require('include/VarDefHandler/listvardefoverride.php');
        $joined_tables = array();
        if(isset($params['joined_tables']))
        {
            foreach($params['joined_tables'] as $table)
            {
                $joined_tables[$table] = 1;
            }
        }

        if(!empty($filter))
        {
            $filterKeys = array_keys($filter);
            if(is_numeric($filterKeys[0]))
            {
                $fields = array();
                foreach($filter as $field)
                {
                    $field = strtolower($field);
                    //remove out id field so we don't duplicate it
                    if ( $field == 'id' && !empty($filter) ) {
                        continue;
                    }
                    if(isset($this->field_defs[$field]))
                    {
                        $fields[$field]= $this->field_defs[$field];
                    }
                    else
                    {
                        $fields[$field] = array('force_exists'=>true);
                    }
                }
            }else{
                $fields = 	$filter;
            }
        }
        else
        {
            $fields = 	$this->field_defs;
        }

        $used_join_key = array();

        foreach($fields as $field=>$value)
        {
            //alias is used to alias field names
            $alias='';
            if 	(isset($value['alias']))
            {
                $alias =' as ' . $value['alias'] . ' ';
            }

            if(empty($this->field_defs[$field]) || !empty($value['force_blank']) )
            {
                if(!empty($filter) && isset($filter[$field]['force_exists']) && $filter[$field]['force_exists'])
                {
                    if ( isset($filter[$field]['force_default']) )
                        $ret_array['select'] .= ", {$filter[$field]['force_default']} $field ";
                    else
                    //spaces are a fix for length issue problem with unions.  The union only returns the maximum number of characters from the first select statemtn.
                        $ret_array['select'] .= ", '                                                                                                                                                                                                                                                              ' $field ";
                }
                continue;
            }
            else
            {
                $data = $this->field_defs[$field];
            }

            //ignore fields that are a part of the collection and a field has been removed as a result of
            //layout customization.. this happens in subpanel customizations, use case, from the contacts subpanel
            //in opportunities module remove the contact_role/opportunity_role field.
            $process_field=true;
            if (isset($data['relationship_fields']) and !empty($data['relationship_fields']))
            {
                foreach ($data['relationship_fields'] as $field_name)
                {
                    if (!isset($fields[$field_name]))
                    {
                        $process_field=false;
                    }
                }
            }
            if (!$process_field)
            {
                continue;
            }

            if(  (!isset($data['source']) || $data['source'] == 'db') && (!empty($alias) || !empty($filter) ))
            {
                $ret_array['select'] .= ", $this->table_name.$field $alias";
                $selectedFields["$this->table_name.$field"] = true;
            }



            if($data['type'] != 'relate' && isset($data['db_concat_fields']))
            {
                $ret_array['select'] .= ", " . db_concat($this->table_name, $data['db_concat_fields']) . " as $field";
                $selectedFields[db_concat($this->table_name, $data['db_concat_fields'])] = true;
            }
            //Custom relate field or relate fields built in module builder which have no link field associated.
            if ($data['type'] == 'relate' && (isset($data['custom_module']) || isset($data['ext2']))) {
                $joinTableAlias = 'jt' . $jtcount;
                $relateJoinInfo = $this->custom_fields->getRelateJoin($data, $joinTableAlias);
                $ret_array['select'] .= $relateJoinInfo['select'];
                $ret_array['from'] .= $relateJoinInfo['from'];
                //Replace any references to the relationship in the where clause with the new alias
                //If the link isn't set, assume that search used the local table for the field
                $searchTable = isset($data['link']) ? $relateJoinInfo['rel_table'] : $this->table_name;
                $field_name = $relateJoinInfo['rel_table'] . '.' . !empty($data['name'])?$data['name']:'name';
                $where = preg_replace('/(^|[\s(])' . $field_name . '/' , '${1}' . $relateJoinInfo['name_field'], $where);
                $jtcount++;
            }
            //Parent Field
            if ($data['type'] == 'parent') {
                //See if we need to join anything by inspecting the where clause
                $match = preg_match('/(^|[\s(])parent_(\w+)_(\w+)\.name/', $where, $matches);
                if ($match) {
                    $joinTableAlias = 'jt' . $jtcount;
                    $joinModule = $matches[2];
                    $joinTable = $matches[3];
                    $localTable = $this->table_name;
                    if (!empty($data['custom_module'])) {
                        $localTable .= '_cstm';
                    }
                    global $beanFiles, $beanList, $module;
                    require_once($beanFiles[$beanList[$joinModule]]);
                    $rel_mod = new $beanList[$joinModule]();
                    $nameField = "$joinTableAlias.name";
                    if (isset($rel_mod->field_defs['name']))
                    {
                        $name_field_def = $rel_mod->field_defs['name'];
                        if(isset($name_field_def['db_concat_fields']))
                        {
                            $nameField = db_concat($joinTableAlias, $name_field_def['db_concat_fields']);
                        }
                    }
                    $ret_array['select'] .= ", $nameField {$data['name']} ";
                    $ret_array['from'] .= " LEFT JOIN $joinTable $joinTableAlias
                        ON $localTable.{$data['id_name']} = $joinTableAlias.id";
                    //Replace any references to the relationship in the where clause with the new alias
                    $where = preg_replace('/(^|[\s(])parent_' . $joinModule . '_' . $joinTable . '\.name/', '${1}' . $nameField, $where);
                    $jtcount++;
                }
            }
            if($data['type'] == 'relate' && isset($data['link']))
            {
                $this->load_relationship($data['link']);
                if(!empty($this->$data['link']))
                {
                    $params = array();
                    if(empty($join_type))
                    {
                        $params['join_type'] = ' LEFT JOIN ';
                    }
                    else
                    {
                        $params['join_type'] = $join_type;
                    }
                    if(isset($data['join_name']))
                    {
                        $params['join_table_alias'] = $data['join_name'];
                    }
                    else
                    {
                        $params['join_table_alias']	= 'jt' . $jtcount;

                    }
                    if(isset($data['join_link_name']))
                    {
                        $params['join_table_link_alias'] = $data['join_link_name'];
                    }
                    else
                    {
                        $params['join_table_link_alias'] = 'jtl' . $jtcount;
                    }
                    $join_primary = !isset($data['join_primary']) || $data['join_primary'];

                    $join = $this->$data['link']->getJoin($params, true);
                    $used_join_key[] = $join['rel_key'];
                    $rel_module = $this->$data['link']->getRelatedModuleName();
                    $table_joined = !empty($joined_tables[$params['join_table_alias']]) || (!empty($joined_tables[$params['join_table_link_alias']]) && isset($data['link_type']) && $data['link_type'] == 'relationship_info');

					//if rnanme is set to 'name', and bean files exist, then check if field should be a concatenated name
					global $beanFiles, $beanList;
					if($data['rname'] && !empty($beanFiles[$beanList[$rel_module]])) {

						//create an instance of the related bean
						require_once($beanFiles[$beanList[$rel_module]]);
						$rel_mod = new $beanList[$rel_module]();
						//if bean has first and last name fields, then name should be concatenated
						if(isset($rel_mod->field_name_map['first_name']) && isset($rel_mod->field_name_map['last_name'])){
								$data['db_concat_fields'] = array(0=>'first_name', 1=>'last_name');
						}
					}


    				if($join['type'] == 'many-to-many')
    				{
    					if(empty($ret_array['secondary_select']))
    					{
    						$ret_array['secondary_select'] = " SELECT $this->table_name.id ref_id  ";

                            if(!empty($beanFiles[$beanList[$rel_module]]) && $join_primary)
                            {
                                require_once($beanFiles[$beanList[$rel_module]]);
                                $rel_mod = new $beanList[$rel_module]();
                                if(isset($rel_mod->field_defs['assigned_user_id']))
                                {
                                    $ret_array['secondary_select'].= " , ".	$params['join_table_alias'] . ".assigned_user_id {$field}_owner, '$rel_module' {$field}_mod";

                                }
                                else
                                {
                                    if(isset($rel_mod->field_defs['created_by']))
                                    {
                                        $ret_array['secondary_select'].= " , ".	$params['join_table_alias'] . ".created_by {$field}_owner , '$rel_module' {$field}_mod";

                                    }
                                }


                            }
                        }



                        if(isset($data['db_concat_fields']))
                        {
                            $ret_array['secondary_select'] .= ' , ' . db_concat($params['join_table_alias'], $data['db_concat_fields']) . ' ' . $field;
                        }
                        else
                        {
                            if(!isset($data['relationship_fields']))
                            {
                                $ret_array['secondary_select'] .= ' , ' . $params['join_table_alias'] . '.' . $data['rname'] . ' ' . $field;
                            }
                        }
                        if(!$singleSelect)
                        {
                            $ret_array['select'] .= ", '                                                                                                                                                                                                                                                              ' $field ";
                            $ret_array['select'] .= ", '                                    '  " . $join['rel_key'] . ' ';
                        }
                        $count_used =0;
                        if($this->db->dbType != 'mysql') {//bug 26801, these codes are just used to duplicate rel_key in the select sql, or it will throw error in MSSQL and Oracle.
                            foreach($used_join_key as $used_key) {
                               if($used_key == $join['rel_key']) $count_used++;
                            }
                        }
                        if($count_used <= 1) {//27416, the $ret_array['secondary_select'] should always generate, regardless the dbtype
                            $ret_array['secondary_select'] .= ', ' . $params['join_table_link_alias'].'.'. $join['rel_key'] .' ' . $join['rel_key'];
                        }
                        if(isset($data['relationship_fields']))
                        {
                            foreach($data['relationship_fields'] as $r_name=>$alias_name)
                            {
                                if(!empty( $secondarySelectedFields[$alias_name]))continue;
                                $ret_array['secondary_select'] .= ', ' . $params['join_table_link_alias'].'.'. $r_name .' ' . $alias_name;
                                $secondarySelectedFields[$alias_name] = true;
                            }
                        }
                        if(!$table_joined)
                        {
                            $ret_array['secondary_from'] .= ' ' . $join['join']. ' AND ' . $params['join_table_alias'].'.deleted=0';
                            if (isset($data['link_type']) && $data['link_type'] == 'relationship_info' && ($parentbean instanceOf SugarBean))
                            {
                                $ret_array['secondary_where'] = $params['join_table_link_alias'] . '.' . $join['rel_key']. "='" .$parentbean->id . "'";
                            }
                        }
                    }
                    else
                    {
                        if(isset($data['db_concat_fields']))
                        {
                            $ret_array['select'] .= ' , ' . db_concat($params['join_table_alias'], $data['db_concat_fields']) . ' ' . $field;
                        }
                        else
                        {
                            $ret_array['select'] .= ' , ' . $params['join_table_alias'] . '.' . $data['rname'] . ' ' . $field;
                        }
                        if(isset($data['additionalFields'])){
                            foreach($data['additionalFields'] as $k=>$v){
                                $ret_array['select'] .= ' , ' . $params['join_table_alias'] . '.' . $k . ' ' . $v;
                            }
                        }
                        if(!$table_joined)
                        {
                            $ret_array['from'] .= ' ' . $join['join']. ' AND ' . $params['join_table_alias'].'.deleted=0';
                            if(!empty($beanList[$rel_module]) && !empty($beanFiles[$beanList[$rel_module]]))
                            {
                                require_once($beanFiles[$beanList[$rel_module]]);
                                $rel_mod = new $beanList[$rel_module]();
                                if(isset($value['target_record_key']) && !empty($filter))
                                {
                                    $selectedFields[$this->table_name.'.'.$value['target_record_key']] = true;
                                    $ret_array['select'] .= " , $this->table_name.{$value['target_record_key']} ";
                                }
                                if(isset($rel_mod->field_defs['assigned_user_id']))
                                {
                                    $ret_array['select'] .= ' , ' .$params['join_table_alias'] . '.assigned_user_id ' .  $field . '_owner';
                                }
                                else
                                {
                                    $ret_array['select'] .= ' , ' .$params['join_table_alias'] . '.created_by ' .  $field . '_owner';
                                }
                                $ret_array['select'] .= "  , '".$rel_module  ."' " .  $field . '_mod';
                            }
                        }
                    }
                    //Replace references to this table in the where clause with the new alias
                    $join_table_name = $this->$data['link']->getRelatedTableName();
                    // To fix SOAP stuff where we are trying to retrieve all the accounts data where accounts.id = ..
                    // and this code changes accounts to jt4 as there is a self join with the accounts table.
                    //Martin fix #27494
                    if(isset($data['db_concat_fields'])){
                    	$buildWhere = false;
                        if(in_array('first_name', $data['db_concat_fields']) && in_array('last_name', $data['db_concat_fields']))
                    	{
                     	   $exp = '/\(\s*?'.$data['name'].'.*?\%\'\s*?\)/';
                    	   if(preg_match($exp, $where, $matches))
                    	   {
                    	   	  $search_expression = $matches[0];
                    	   	  //Create three search conditions - first + last, first, last
                    	   	  $first_name_search = str_replace($data['name'], $params['join_table_alias'] . '.first_name', $search_expression);
                    	   	  $last_name_search = str_replace($data['name'], $params['join_table_alias'] . '.last_name', $search_expression);
							  $full_name_search = str_replace($data['name'], db_concat($params['join_table_alias'], $data['db_concat_fields']), $search_expression);
							  $buildWhere = true;
							  $where = str_replace($search_expression, '(' . $full_name_search . ' OR ' . $first_name_search . ' OR ' . $last_name_search . ')', $where);
                    	   }
                    	}

                    	if(!$buildWhere)
                    	{
	                       $db_field = db_concat($params['join_table_alias'], $data['db_concat_fields']);
	                       $where = preg_replace('/'.$data['name'].'/', $db_field, $where);
                    	}
                    }else{
                        $where = preg_replace('/(^|[\s(])' . $data['name'] . '/', '${1}' . $params['join_table_alias'] . '.'.$data['rname'], $where);
                    }
                    if(!$table_joined)
                    {
                        $joined_tables[$params['join_table_alias']]=1;
                        $joined_tables[$params['join_table_link_alias']]=1;
                    }

                    $jtcount++;
                }
            }
        }
        if(!empty($filter))
        {
            if(isset($this->field_defs['assigned_user_id']) && empty($selectedFields[$this->table_name.'.assigned_user_id']))
            {
                $ret_array['select'] .= ", $this->table_name.assigned_user_id ";
            }
            else if(isset($this->field_defs['created_by']) &&  empty($selectedFields[$this->table_name.'.created_by']))
            {
                $ret_array['select'] .= ", $this->table_name.created_by ";
            }
            if(isset($this->field_defs['system_id']) && empty($selectedFields[$this->table_name.'.system_id']))
            {
                $ret_array['select'] .= ", $this->table_name.system_id ";
            }

        }
        $where_auto = '1=1';
        if($show_deleted == 0)
        {
            $where_auto = "$this->table_name.deleted=0";
        }else if($show_deleted == 1)
        {
            $where_auto = "$this->table_name.deleted=1";
        }
        if($where != "")
            $ret_array['where'] = " where ($where) AND $where_auto";
        else
            $ret_array['where'] = " where $where_auto";
        if(!empty($order_by))
        {
            //make call to process the order by clause
            $ret_array['order_by'] = " ORDER BY ". $this->process_order_by($order_by, null);
        }
        if($singleSelect)
        {
            unset($ret_array['secondary_where']);
            unset($ret_array['secondary_from']);
            unset($ret_array['secondary_select']);
        }

        if($return_array)
        {
            return $ret_array;
        }

        return  $ret_array['select'] . $ret_array['from'] . $ret_array['where']. $ret_array['order_by'];




    }
    /**
     * Returns parent record data for objects that store relationship information
     *
     * @param array $type_info
     *
     * Interal function, do not override.
     */
    function retrieve_parent_fields($type_info)
    {
        $queries = array();
        global $beanList, $beanFiles;
        $templates = array();
        $parent_child_map = array();
        foreach($type_info as $children_info)
        {
            foreach($children_info as $child_info)
            {
                if($child_info['type'] == 'parent')
                {
                    if(empty($templates[$child_info['parent_type']]))
                    {
                        //Test emails will have an invalid parent_type, don't try to load the non-existant parent bean
                        if ($child_info['parent_type'] == 'test') {
                            continue;
                        }
                        $class = $beanList[$child_info['parent_type']];
                        // Added to avoid error below; just silently fail and write message to log
                        if ( empty($beanFiles[$class]) ) {
                            $GLOBALS['log']->error($this->object_name.'::retrieve_parent_fields() - cannot load class "'.$class.'", skip loading.');
                            continue;
                        }
                        require_once($beanFiles[$class]);
                        $templates[$child_info['parent_type']] = new $class();
                    }

                    if(empty($queries[$child_info['parent_type']]))
                    {
                        $queries[$child_info['parent_type']] = "SELECT id ";
                        $field_def = $templates[$child_info['parent_type']]->field_defs['name'];
                        if(isset($field_def['db_concat_fields']))
                        {
                            $queries[$child_info['parent_type']] .= ' , ' . db_concat($templates[$child_info['parent_type']]->table_name, $field_def['db_concat_fields']) . ' parent_name';
                        }
                        else
                        {
                            $queries[$child_info['parent_type']] .= ' , name parent_name';
                        }
                        if(isset($templates[$child_info['parent_type']]->field_defs['assigned_user_id']))
                        {
                            $queries[$child_info['parent_type']] .= ", assigned_user_id parent_name_owner , '{$child_info['parent_type']}' parent_name_mod";;
                        }else if(isset($templates[$child_info['parent_type']]->field_defs['created_by']))
                        {
                            $queries[$child_info['parent_type']] .= ", created_by parent_name_owner, '{$child_info['parent_type']}' parent_name_mod";
                        }
                        $queries[$child_info['parent_type']] .= " FROM " . $templates[$child_info['parent_type']]->table_name ." WHERE id IN ('{$child_info['parent_id']}'";
                    }
                    else
                    {
                        if(empty($parent_child_map[$child_info['parent_id']]))
                        $queries[$child_info['parent_type']] .= " ,'{$child_info['parent_id']}'";
                    }
                    $parent_child_map[$child_info['parent_id']][] = $child_info['child_id'];
                }
            }
        }
        $results = array();
        foreach($queries as $query)
        {
            $result = $this->db->query($query . ')');
            while($row = $this->db->fetchByAssoc($result))
            {
                $results[$row['id']] = $row;
            }
        }

        $child_results = array();
        foreach($parent_child_map as $parent_key=>$parent_child)
        {
            foreach($parent_child as $child)
            {
                if(isset( $results[$parent_key]))
                {
                    $child_results[$child] = $results[$parent_key];
                }
            }
        }
        return $child_results;
    }

    /**
     * Processes the list query and return fetched row.
     *
     * Internal function, do not override.
     * @param string $query select query to be processed.
     * @param int $row_offset starting position
     * @param int $limit Optioanl, default -1
     * @param int $max_per_page Optional, default -1
     * @param string $where Optional, additional filter criteria.
     * @return array Fetched data
     */
    function process_list_query($query, $row_offset, $limit= -1, $max_per_page = -1, $where = '')
    {
        global $sugar_config;
        $db = &DBManagerFactory::getInstance('listviews');
        /**
        * if the row_offset is set to 'end' go to the end of the list
        */
        $toEnd = strval($row_offset) == 'end';
        $GLOBALS['log']->debug("process_list_query: ".$query);
        if($max_per_page == -1)
        {
            $max_per_page 	= $sugar_config['list_max_entries_per_page'];
        }
        // Check to see if we have a count query available.
        if(empty($sugar_config['disable_count_query']) || $toEnd)
        {
            $count_query = $this->create_list_count_query($query);
            if(!empty($count_query) && (empty($limit) || $limit == -1))
            {
                // We have a count query.  Run it and get the results.
                $result = $db->query($count_query, true, "Error running count query for $this->object_name List: ");
                $assoc = $db->fetchByAssoc($result);
                if(!empty($assoc['c']))
                {
                    $rows_found = $assoc['c'];
                    $limit = $sugar_config['list_max_entries_per_page'];
                }
                if( $toEnd)
                {
                    $row_offset = (floor(($rows_found -1) / $limit)) * $limit;
                }
            }
        }
        else
        {
            if((empty($limit) || $limit == -1))
            {
                $limit = $max_per_page + 1;
                $max_per_page = $limit;
            }
        }

        if(empty($row_offset))
        {
            $row_offset = 0;
        }
        if(!empty($limit) && $limit != -1 && $limit != -99)
        {
            $result = $db->limitQuery($query, $row_offset, $limit,true,"Error retrieving $this->object_name list: ");
        }
        else
        {
            $result = $db->query($query,true,"Error retrieving $this->object_name list: ");
        }

        $list = Array();

        if(empty($rows_found))
        {
            $rows_found =  $db->getRowCount($result);
        }

        $GLOBALS['log']->debug("Found $rows_found ".$this->object_name."s");

        $previous_offset = $row_offset - $max_per_page;
        $next_offset = $row_offset + $max_per_page;

        $class = get_class($this);
        if($rows_found != 0 or $db->dbType != 'mysql')
        {
            //todo Bug? we should remove the magic number -99
            //use -99 to return all
            $index = $row_offset;
            while ($max_per_page == -99 || ($index < $row_offset + $max_per_page))
            {

                if(!empty($sugar_config['disable_count_query']))
                {
                    $row = $db->fetchByAssoc($result);
                }
                else
                {
                    $row = $db->fetchByAssoc($result, $index);
                }
                if (empty($row))
                {
                    break;
                }

                //instantiate a new class each time. This is because php5 passes
                //by reference by default so if we continually update $this, we will
                //at the end have a list of all the same objects
                $temp = new $class();

                foreach($this->field_defs as $field=>$value)
                {
                    if (isset($row[$field]))
                    {
                        $temp->$field = $row[$field];
                        $owner_field = $field . '_owner';
                        if(isset($row[$owner_field]))
                        {
                            $temp->$owner_field = $row[$owner_field];
                        }

                        $GLOBALS['log']->debug("$temp->object_name({$row['id']}): ".$field." = ".$temp->$field);
                    }else if (isset($row[$this->table_name .'.'.$field]))
                    {
                        $temp->$field = $row[$this->table_name .'.'.$field];
                    }
                    else
                    {
                        $temp->$field = "";
                    }
                }

                $temp->check_date_relationships_load();
                $temp->fill_in_additional_list_fields();
                if($temp->hasCustomFields()) $temp->custom_fields->fill_relationships();
                $temp->call_custom_logic("process_record");

                $list[] = $temp;

                $index++;
            }
        }
        if(!empty($sugar_config['disable_count_query']) && !empty($limit))
        {

            $rows_found = $row_offset + count($list);

            unset($list[$limit - 1]);
            if(!$toEnd)
            {
                $next_offset--;
                $previous_offset++;
            }
        }
        $response = Array();
        $response['list'] = $list;
        $response['row_count'] = $rows_found;
        $response['next_offset'] = $next_offset;
        $response['previous_offset'] = $previous_offset;
        $response['current_offset'] = $row_offset ;
        return $response;
    }

    /**
    * Returns the number of rows that the given SQL query should produce
     *
     * Internal function, do not override.
     * @param string $query valid select  query
     * @param boolean $is_count_query Optional, Default false, set to true if passed query is a count query.
     * @return int count of rows found
    */
    function _get_num_rows_in_query($query, $is_count_query=false)
    {
        $num_rows_in_query = 0;
        if (!$is_count_query)
        {
            $count_query = SugarBean::create_list_count_query($query);
        } else
            $count_query=$query;

        $result = $this->db->query($count_query, true, "Error running count query for $this->object_name List: ");
        $row_num = 0;
        $row = $this->db->fetchByAssoc($result, $row_num);
        while($row)
        {
            $num_rows_in_query += current($row);
            $row_num++;
            $row = $this->db->fetchByAssoc($result, $row_num);
        }

        return $num_rows_in_query;
    }

    /**
     * Applies pagination window to union queries used by list view and subpanels,
     * executes the query and returns fetched data.
     *
     * Internal function, do not override.
     * @param object $parent_bean
     * @param string $query query to be processed.
     * @param int $row_offset
     * @param int $limit optional, default -1
     * @param int $max_per_page Optional, default -1
     * @param string $where Custom where clause.
     * @param array $subpanel_def definition of sub-panel to be processed
     * @param string $query_row_count
     * @param array $seconday_queries
     * @return array Fetched data.
     */
    function process_union_list_query($parent_bean, $query,
    $row_offset, $limit= -1, $max_per_page = -1, $where = '', $subpanel_def, $query_row_count='', $secondary_queries = array())

    {
        $db = &DBManagerFactory::getInstance('listviews');
        /**
        * if the row_offset is set to 'end' go to the end of the list
        */
        $toEnd = strval($row_offset) == 'end';
        global $sugar_config;
        $use_count_query=false;
        $processing_collection=$subpanel_def->isCollection();

        $GLOBALS['log']->debug("process_list_query: ".$query);
        if($max_per_page == -1)
        {
            $max_per_page 	= $sugar_config['list_max_entries_per_subpanel'];
        }
        if(empty($query_row_count))
        {
            $query_row_count = $query;
        }
        $distinct_position=strpos($query_row_count,"DISTINCT");

        if ($distinct_position!= false)
        {
            $use_count_query=true;
        }
        $performSecondQuery = true;
        if(empty($sugar_config['disable_count_query']) || $toEnd)
        {
            $rows_found = $this->_get_num_rows_in_query($query_row_count,$use_count_query);
            if($rows_found < 1)
            {
                $performSecondQuery = false;
            }
            if(!empty($rows_found) && (empty($limit) || $limit == -1))
            {
                $limit = $sugar_config['list_max_entries_per_subpanel'];
            }
            if( $toEnd)
            {
                $row_offset = (floor(($rows_found -1) / $limit)) * $limit;

            }
        }
        else
        {
            if((empty($limit) || $limit == -1))
            {
                $limit = $max_per_page + 1;
                $max_per_page = $limit;
            }
        }

        if(empty($row_offset))
        {
            $row_offset = 0;
        }
        $list = array();
        $previous_offset = $row_offset - $max_per_page;
        $next_offset = $row_offset + $max_per_page;

        if($performSecondQuery)
        {
            if(!empty($limit) && $limit != -1 && $limit != -99)
            {
                $result = $db->limitQuery($query, $row_offset, $limit,true,"Error retrieving $parent_bean->object_name list: ");
            }
            else
            {
                $result = $db->query($query,true,"Error retrieving $this->object_name list: ");
            }
            if(empty($rows_found))
            {
                $rows_found =  $db->getRowCount($result);
            }

            $GLOBALS['log']->debug("Found $rows_found ".$parent_bean->object_name."s");
            if($rows_found != 0 or $db->dbType != 'mysql')
            {
                //use -99 to return all

                // get the current row
                $index = $row_offset;
                if(!empty($sugar_config['disable_count_query']))
                {
                    $row = $db->fetchByAssoc($result);
                }
                else
                {
                    $row = $db->fetchByAssoc($result, $index);
                }

                $post_retrieve = array();
                $isFirstTime = true;
                while($row)
                {
                    $function_fields = array();
                    if(($index < $row_offset + $max_per_page || $max_per_page == -99) or ($db->dbType != 'mysql'))
                    {
                        if ($processing_collection)
                        {
                            $current_bean =$subpanel_def->sub_subpanels[$row['panel_name']]->template_instance;
                            if(!$isFirstTime)
                            {
                                $class = get_class($subpanel_def->sub_subpanels[$row['panel_name']]->template_instance);
                                $current_bean = new $class();
                            }
                        } else {
                            $current_bean=$subpanel_def->template_instance;
                            if(!$isFirstTime)
                            {
                                $class = get_class($subpanel_def->template_instance);
                                $current_bean = new $class();
                            }
                        }
                        $isFirstTime = false;
                        //set the panel name in the bean instance.
                        if (isset($row['panel_name']))
                        {
                            $current_bean->panel_name=$row['panel_name'];
                        }
                        foreach($current_bean->field_defs as $field=>$value)
                        {

                            if (isset($row[$field]))
                            {
                                $current_bean->$field = $row[$field];
                                unset($row[$field]);
                            }
                            else if (isset($row[$this->table_name .'.'.$field]))
                            {
                                $current_bean->$field = $row[$current_bean->table_name .'.'.$field];
                                unset($row[$current_bean->table_name .'.'.$field]);
                            }
                            else
                            {
                                $current_bean->$field = "";
                                unset($row[$field]);
                            }
                            if(isset($value['source']) && $value['source'] == 'function')
                            {
                                $function_fields[]=$field;
                            }
                        }
                        foreach($row as $key=>$value)
                        {
                            $current_bean->$key = $value;
                        }
                        foreach($function_fields as $function_field)
                        {
                            $value = $current_bean->field_defs[$function_field];
                            $can_execute = true;
                            $execute_params = array();
                            $execute_function = array();
                            if(!empty($value['function_class']))
                            {
                                $execute_function[] = 	$value['function_class'];
                                $execute_function[] = 	$value['function_name'];
                            }
                            else
                            {
                                $execute_function	= $value['function_name'];
                            }
                            foreach($value['function_params'] as $param )
                            {
                                if (empty($value['function_params_source']) or $value['function_params_source']=='parent')
                                {
                                    if(empty($this->$param))
                                    {
                                        $can_execute = false;
                                    }
                                    else
                                    {
                                        $execute_params[] = $this->$param;
                                    }
                                } else if ($value['function_params_source']=='this')
                                {
                                    if(empty($current_bean->$param))
                                    {
                                        $can_execute = false;
                                    }
                                    else
                                    {
                                        $execute_params[] = $current_bean->$param;
                                    }
                                }
                                else
                                {
                                    $can_execute = false;
                                }

                            }
                            if($can_execute)
                            {
                                if(!empty($value['function_require']))
                                {
                                    require_once($value['function_require']);
                                }
                                $current_bean->$function_field = call_user_func_array($execute_function, $execute_params);
                            }
                        }
                        if(!empty($current_bean->parent_type) && !empty($current_bean->parent_id))
                        {
                            if(!isset($post_retrieve[$current_bean->parent_type]))
                            {
                                $post_retrieve[$current_bean->parent_type] = array();
                            }
                            $post_retrieve[$current_bean->parent_type][] = array('child_id'=>$current_bean->id, 'parent_id'=> $current_bean->parent_id, 'parent_type'=>$current_bean->parent_type, 'type'=>'parent');
                        }
                        //$current_bean->fill_in_additional_list_fields();
                        $list[$current_bean->id] = $current_bean;
                    }
                    // go to the next row
                    $index++;
                    $row = $db->fetchByAssoc($result, $index);
                }
            }
            //now handle retrieving many-to-many relationships
            if(!empty($list))
            {
                foreach($secondary_queries as $query2)
                {
                    $result2 = $db->query($query2);

                    $row2 = $db->fetchByAssoc($result2);
                    while($row2)
                    {
                        $id_ref = $row2['ref_id'];

                        if(isset($list[$id_ref]))
                        {
                            foreach($row2 as $r2key=>$r2value)
                            {
                                if($r2key != 'ref_id')
                                {
                                    $list[$id_ref]->$r2key = $r2value;
                                }
                            }
                        }
                        $row2 = $db->fetchByAssoc($result2);
                    }
                }

            }

            if(isset($post_retrieve))
            {
                $parent_fields = $this->retrieve_parent_fields($post_retrieve);
            }
            else
            {
                $parent_fields = array();
            }
            if(!empty($sugar_config['disable_count_query']) && !empty($limit))
            {
                $rows_found = $row_offset + count($list);
                if(count($list) >= $limit)
                {
                    array_pop($list);
                }
                if(!$toEnd)
                {
                    $next_offset--;
                    $previous_offset++;
                }
            }
        }
        else
        {
            $row_found 	= 0;
            $parent_fields = array();
        }
        $response = array();
        $response['list'] = $list;
        $response['parent_data'] = $parent_fields;
        $response['row_count'] = $rows_found;
        $response['next_offset'] = $next_offset;
        $response['previous_offset'] = $previous_offset;
        $response['current_offset'] = $row_offset ;
        $response['query'] = $query;

        return $response;
    }

    /**
     * Applies pagination window to select queries used by detail view,
     * executes the query and returns fetched data.
     *
     * Internal function, do not override.
     * @param string $query query to be processed.
     * @param int $row_offset
     * @param int $limit optional, default -1
     * @param int $max_per_page Optional, default -1
     * @param string $where Custom where clause.
     * @param int $offset Optional, default 0
     * @return array Fetched data.
     *
     */
    function process_detail_query($query, $row_offset, $limit= -1, $max_per_page = -1, $where = '', $offset = 0)
    {
        global $sugar_config;
        $GLOBALS['log']->debug("process_list_query: ".$query);
        if($max_per_page == -1)
        {
            $max_per_page 	= $sugar_config['list_max_entries_per_page'];
        }

        // Check to see if we have a count query available.
        $count_query = $this->create_list_count_query($query);

        if(!empty($count_query) && (empty($limit) || $limit == -1))
        {
            // We have a count query.  Run it and get the results.
            $result = $this->db->query($count_query, true, "Error running count query for $this->object_name List: ");
            $assoc = $this->db->fetchByAssoc($result);
            if(!empty($assoc['c']))
            {
                $total_rows = $assoc['c'];
            }
        }

        if(empty($row_offset))
        {
            $row_offset = 0;
        }

        $result = $this->db->limitQuery($query, $offset, 1, true,"Error retrieving $this->object_name list: ");

        $rows_found = $this->db->getRowCount($result);

        $GLOBALS['log']->debug("Found $rows_found ".$this->object_name."s");

        $previous_offset = $row_offset - $max_per_page;
        $next_offset = $row_offset + $max_per_page;

        if($rows_found != 0 or $this->db->dbType != 'mysql')
        {
            $index = 0;
            $row = $this->db->fetchByAssoc($result, $index);
            $this->retrieve($row['id']);
        }

        $response = Array();
        $response['bean'] = $this;
        if (empty($total_rows))
            $total_rows=0;
        $response['row_count'] = $total_rows;
        $response['next_offset'] = $next_offset;
        $response['previous_offset'] = $previous_offset;

        return $response;
    }

    /**
     * Processes fetched list view data
     *
     * Internal function, do not override.
     * @param string $query query to be processed.
     * @param boolean $check_date Optional, default false. if set to true date time values are processed.
     * @return array Fetched data.
     *
     */
    function process_full_list_query($query, $check_date=false)
    {

        $GLOBALS['log']->debug("process_full_list_query: query is ".$query);
        $result = $this->db->query($query, false);
        $GLOBALS['log']->debug("process_full_list_query: result is ".print_r($result,true));
        $class = get_class($this);
        $isFirstTime = true;
        $bean = new $class();

        //if($this->db->getRowCount($result) > 0){


        // We have some data.
        //while ($row = $this->db->fetchByAssoc($result)) {
        while (($row = $bean->db->fetchByAssoc($result)) != null)
        {
            if(!$isFirstTime)
            {
                $bean = new $class();
            }
            $isFirstTime = false;

            foreach($bean->field_defs as $field=>$value)
            {
                if (isset($row[$field]))
                {
                    $bean->$field = $row[$field];
                    $GLOBALS['log']->debug("process_full_list: $bean->object_name({$row['id']}): ".$field." = ".$bean->$field);
                }
                else
                {
                    $bean->$field = '';
                }
            }
            if($check_date)
            {
                $bean->processed_dates_times = array();
                $bean->check_date_relationships_load();
            }
            $bean->fill_in_additional_list_fields();
            $bean->call_custom_logic("process_record");
            $bean->fetched_row = $row;

            $list[] = $bean;
        }
        //}
        if (isset($list)) return $list;
        else return null;
    }

    /**
    * Tracks the viewing of a detail record.
    * This leverages get_summary_text() which is object specific.
    *
    * Internal function, do not override.
    * @param string $user_id - String value of the user that is viewing the record.
    * @param string $current_module - String value of the module being processed.
    * @param string $current_view - String value of the current view
	*/
	function track_view($user_id, $current_module, $current_view='')
	{
	    $trackerManager = TrackerManager::getInstance();
		if($monitor = $trackerManager->getMonitor('tracker')){
	        $monitor->setValue('date_modified', $GLOBALS['timedate']->nowDb());
	        $monitor->setValue('user_id', $user_id);
	        $monitor->setValue('module_name', $current_module);
	        $monitor->setValue('action', $current_view);
	        $monitor->setValue('item_id', $this->id);
	        $monitor->setValue('item_summary', $this->get_summary_text());
	        $monitor->setValue('visible', $this->tracker_visibility);
	        $trackerManager->saveMonitor($monitor);
		}
	}

    /**
     * Returns the summary text that should show up in the recent history list for this object.
     *
     * @return string
     */
    public function get_summary_text()
    {
        return "Base Implementation.  Should be overridden.";
    }

    /**
    * This is designed to be overridden and add specific fields to each record.
    * This allows the generic query to fill in the major fields, and then targeted
    * queries to get related fields and add them to the record.  The contact's
    * account for instance.  This method is only used for populating extra fields
    * in lists.
    */
    function fill_in_additional_list_fields(){
        if(!empty($this->field_defs['parent_name']) && empty($this->parent_name)){
            $this->fill_in_additional_parent_fields();
        }
    }

    /**
    * This is designed to be overridden and add specific fields to each record.
    * This allows the generic query to fill in the major fields, and then targeted
    * queries to get related fields and add them to the record.  The contact's
    * account for instance.  This method is only used for populating extra fields
    * in the detail form
    */
    function fill_in_additional_detail_fields()
    {
        if(!empty($this->field_defs['assigned_user_name']) && !empty($this->assigned_user_id)){

            $this->assigned_user_name = get_assigned_user_name($this->assigned_user_id);

        }
		if(!empty($this->field_defs['created_by']) && !empty($this->created_by))
		$this->created_by_name = get_assigned_user_name($this->created_by);
		if(!empty($this->field_defs['modified_user_id']) && !empty($this->modified_user_id))
		$this->modified_by_name = get_assigned_user_name($this->modified_user_id);

		if(!empty($this->field_defs['parent_name'])){
			$this->fill_in_additional_parent_fields();
		}
    }

    /**
    * This is desgined to be overridden or called from extending bean. This method
    * will fill in any parent_name fields.
    */
    function fill_in_additional_parent_fields() {

        if(!empty($this->parent_id) && !empty($this->last_parent_id) && $this->last_parent_id == $this->parent_id){
            return false;
        }else{
            $this->parent_name = '';
        }
        if(!empty($this->parent_type)) {
            $this->last_parent_id = $this->parent_id;
            $this->getRelatedFields($this->parent_type, $this->parent_id, array('name'=>'parent_name', 'document_name' => 'parent_document_name', 'first_name'=>'parent_first_name', 'last_name'=>'parent_last_name'));
            if(!empty($this->parent_first_name) || !empty($this->parent_last_name) ){
                $this->parent_name = $GLOBALS['locale']->getLocaleFormattedName($this->parent_first_name, $this->parent_last_name);
            } else if(!empty($this->parent_document_name)){
                $this->parent_name = $this->parent_document_name;
            }
        }
    }

/*
     * Fill in a link field
     */

    function fill_in_link_field( $linkFieldName )
    {
        if ($this->load_relationship($linkFieldName))
        {
            $list=$this->$linkFieldName->get();
            $this->$linkFieldName = '' ; // match up with null value in $this->populateFromRow()
            if (!empty($list))
                $this->$linkFieldName = $list[0];
        }
    }

    /**
    * Fill in fields where type = relate
    */
    function fill_in_relationship_fields(){
        if(!empty($this->relDepth)) {
            if($this->relDepth > 1)return;
        }else $this->relDepth = 0;

        foreach($this->field_defs as $field)
        {
            if(0 == strcmp($field['type'],'relate') && !empty($field['module']))
            {
                $name = $field['name'];
                if(empty($this->$name))
                {
                    // set the value of this relate field in this bean ($this->$field['name']) to the value of the 'name' field in the related module for the record identified by the value of $this->$field['id_name']
                    $related_module = $field['module'];
                    $id_name = $field['id_name'];
                    if (empty($this->$id_name)){
                       $this->fill_in_link_field($id_name);
                    }
                    if(!empty($this->$id_name) && ( $this->object_name != $related_module || ( $this->object_name == $related_module && $this->$id_name != $this->id ))){
                        if(isset($GLOBALS['beanList'][ $related_module])){
                            $class = $GLOBALS['beanList'][$related_module];

                            if(!empty($this->$id_name) && file_exists($GLOBALS['beanFiles'][$class]) && isset($this->$name)){
                                require_once($GLOBALS['beanFiles'][$class]);
                                $mod = new $class();
                                $mod->relDepth = $this->relDepth + 1;
                                $mod->retrieve($this->$id_name);
                                if (!empty($field['rname'])) {
                                    $this->$name = $mod->$field['rname'];
                                } else if (isset($mod->name)) {
                                    $this->$name = $mod->name;
                                }
                            }
                        }
                    }
                    if(!empty($this->$id_name) && isset($this->$name))
                    {
                        if(!isset($field['additionalFields']))
                           $field['additionalFields'] = array();
                        if(!empty($field['rname']))
                        {
                            $field['additionalFields'][$field['rname']]= $name;
                        }
                        else
                        {
                            $field['additionalFields']['name']= $name;
                        }
                        $this->getRelatedFields($related_module, $this->$id_name, $field['additionalFields']);
                    }
                }
            }
        }
    }

    /**
    * This is a helper function that is used to quickly created indexes when creating tables.
    */
    function create_index($query)
    {
        $GLOBALS['log']->info($query);

        $result = $this->db->query($query, true, "Error creating index:");
    }

    /**
     * This function should be overridden in each module.  It marks an item as deleted.
     *
     * If it is not overridden, then marking this type of item is not allowed
	 */
	function mark_deleted($id)
	{
		global $current_user;
		$date_modified = $GLOBALS['timedate']->nowDb();
		if(isset($_SESSION['show_deleted']))
		{
			$this->mark_undeleted($id);
		}
		else
		{
			// call the custom business logic
			$custom_logic_arguments['id'] = $id;
			$this->call_custom_logic("before_delete", $custom_logic_arguments);

            if ( isset($this->field_defs['modified_user_id']) ) {
                if (!empty($current_user)) {
                    $this->modified_user_id = $current_user->id;
                } else {
                    $this->modified_user_id = 1;
                }
                $query = "UPDATE $this->table_name set deleted=1 , date_modified = '$date_modified', modified_user_id = '$this->modified_user_id' where id='$id'";
            } else
                $query = "UPDATE $this->table_name set deleted=1 , date_modified = '$date_modified' where id='$id'";
            $this->db->query($query, true,"Error marking record deleted: ");
            $this->deleted = 1;
            $this->mark_relationships_deleted($id);

            // Take the item off the recently viewed lists
            $tracker = new Tracker();
            $tracker->makeInvisibleForAll($id);

            // call the custom business logic
            $this->call_custom_logic("after_delete", $custom_logic_arguments);
        }
    }

    /**
     * Restores data deleted by call to mark_deleted() function.
     *
     * Internal function, do not override.
    */
    function mark_undeleted($id)
    {
        // call the custom business logic
        $custom_logic_arguments['id'] = $id;
        $this->call_custom_logic("before_restore", $custom_logic_arguments);

		$date_modified = $GLOBALS['timedate']->nowDb();
		$query = "UPDATE $this->table_name set deleted=0 , date_modified = '$date_modified' where id='$id'";
		$this->db->query($query, true,"Error marking record undeleted: ");

        // call the custom business logic
        $this->call_custom_logic("after_restore", $custom_logic_arguments);
    }

   /**
    * This function deletes relationships to this object.  It should be overridden
    * to handle the relationships of the specific object.
    * This function is called when the item itself is being deleted.
    *
    * @param int $id id of the relationship to delete
    */
   function mark_relationships_deleted($id)
   {
    $this->delete_linked($id);
   }

    /**
    * This function is used to execute the query and create an array template objects
    * from the resulting ids from the query.
    * It is currently used for building sub-panel arrays.
    *
    * @param string $query - the query that should be executed to build the list
    * @param object $template - The object that should be used to copy the records.
    * @param int $row_offset Optional, default 0
    * @param int $limit Optional, default -1
    * @return array
    */
    function build_related_list($query, &$template, $row_offset = 0, $limit = -1)
    {
        $GLOBALS['log']->debug("Finding linked records $this->object_name: ".$query);
        $db = &DBManagerFactory::getInstance('listviews');

        if(!empty($row_offset) && $row_offset != 0 && !empty($limit) && $limit != -1)
        {
            $result = $db->limitQuery($query, $row_offset, $limit,true,"Error retrieving $template->object_name list: ");
        }
        else
        {
            $result = $db->query($query, true);
        }

        $list = Array();
        $isFirstTime = true;
        $class = get_class($template);
        while($row = $this->db->fetchByAssoc($result))
        {
            if(!$isFirstTime)
            {
                $template = new $class();
            }
            $isFirstTime = false;
            $record = $template->retrieve($row['id']);

            if($record != null)
            {
                // this copies the object into the array
                $list[] = $template;
            }
        }
        return $list;
    }

  /**
    * This function is used to execute the query and create an array template objects
    * from the resulting ids from the query.
    * It is currently used for building sub-panel arrays. It supports an additional
    * where clause that is executed as a filter on the results
    *
    * @param string $query - the query that should be executed to build the list
    * @param object $template - The object that should be used to copy the records.
    */
  function build_related_list_where($query, &$template, $where='', $in='', $order_by, $limit='', $row_offset = 0)
  {
    $db = &DBManagerFactory::getInstance('listviews');
    // No need to do an additional query
    $GLOBALS['log']->debug("Finding linked records $this->object_name: ".$query);
    if(empty($in) && !empty($query))
    {
        $idList = $this->build_related_in($query);
        $in = $idList['in'];
    }
    // MFH - Added Support For Custom Fields in Searches
    $custom_join="";
    if(isset($this->custom_fields)) {
        $custom_join = $this->custom_fields->getJOIN();
    }

    $query = "SELECT id ";

    if (!empty($custom_join)) {
        $query .= $custom_join['select'];
    }
    $query .= " FROM $this->table_name ";

    if (!empty($custom_join) && !empty($custom_join['join'])) {
        $query .= " " . $custom_join['join'];
    }

    $query .= " WHERE deleted=0 AND id IN $in";
    if(!empty($where))
    {
        $query .= " AND $where";
    }


    if(!empty($order_by))
    {
        $query .= "ORDER BY $order_by";
    }
    if (!empty($limit))
    {
        $result = $db->limitQuery($query, $row_offset, $limit,true,"Error retrieving $this->object_name list: ");
    }
    else
    {
        $result = $db->query($query, true);
    }

    $list = Array();
    $isFirstTime = true;
    $class = get_class($template);

    $disable_security_flag = ($template->disable_row_level_security) ? true : false;
    while($row = $db->fetchByAssoc($result))
    {
        if(!$isFirstTime)
        {
            $template = new $class();
            $template->disable_row_level_security = $disable_security_flag;
        }
        $isFirstTime = false;
        $record = $template->retrieve($row['id']);
        if($record != null)
        {
            // this copies the object into the array
            $list[] = $template;
        }
    }

    return $list;
  }

    /**
     * Constructs an comma seperated list of ids from passed query results.
     *
     * @param string @query query to be executed.
     *
     */
    function build_related_in($query)
    {
        $idList = array();
        $result = $this->db->query($query, true);
        $ids = '';
        while($row = $this->db->fetchByAssoc($result))
        {
            $idList[] = $row['id'];
            if(empty($ids))
            {
                $ids = "('" . $row['id'] . "'";
            }
            else
            {
                $ids .= ",'" . $row['id'] . "'";
            }
        }
        if(empty($ids))
        {
            $ids = "('')";
        }else{
            $ids .= ')';
        }

        return array('list'=>$idList, 'in'=>$ids);
    }

    /**
    * Optionally copies values from fetched row into the bean.
    *
    * Internal function, do not override.
    *
    * @param string $query - the query that should be executed to build the list
    * @param object $template - The object that should be used to copy the records
    * @param array $field_list List of  fields.
    * @return array
    */
    function build_related_list2($query, &$template, &$field_list)
    {
        $GLOBALS['log']->debug("Finding linked values $this->object_name: ".$query);

        $result = $this->db->query($query, true);

        $list = Array();
        $isFirstTime = true;
        $class = get_class($template);
        while($row = $this->db->fetchByAssoc($result))
        {
            // Create a blank copy
            $copy = $template;
            if(!$isFirstTime)
            {
                $copy = new $class();
            }
            $isFirstTime = false;
            foreach($field_list as $field)
            {
                // Copy the relevant fields
                $copy->$field = $row[$field];

            }

            // this copies the object into the array
            $list[] = $copy;
        }

        return $list;
    }

    /**
     * Let implementing classes to fill in row specific columns of a list view form
     *
     */
    function list_view_parse_additional_sections(&$list_form)
    {
    }

    /**
     * Assigns all of the values into the template for the list view
     */
    function get_list_view_array()
    {
        static $cache = array();
        // cn: bug 12270 - sensitive fields being passed arbitrarily in listViews
        $sensitiveFields = array('user_hash' => '');

        $return_array = Array();
        global $app_list_strings, $mod_strings;
        foreach($this->field_defs as $field=>$value){

            if(isset($this->$field)){

                // cn: bug 12270 - sensitive fields being passed arbitrarily in listViews
                if(isset($sensitiveFields[$field]))
                    continue;
                if(!isset($cache[$field]))
                    $cache[$field] = strtoupper($field);

                //Fields hidden by Dependent Fields
                if (isset($value['hidden']) && $value['hidden'] === true) {
                        $return_array[$cache[$field]] = "";

                }
                //cn: if $field is a _dom, detect and return VALUE not KEY
                //cl: empty function check for meta-data enum types that have values loaded from a function
                else if (((!empty($value['type']) && ($value['type'] == 'enum' || $value['type'] == 'radioenum') ))  && empty($value['function'])){
                    if(!empty($app_list_strings[$value['options']][$this->$field])){
                        $return_array[$cache[$field]] = $app_list_strings[$value['options']][$this->$field];
                    }
                    //nsingh- bug 21672. some modules such as manufacturers, Releases do not have a listing for select fields in the $app_list_strings. Must also check $mod_strings to localize.
                    elseif(!empty($mod_strings[$value['options']][$this->$field]))
                    {
                        $return_array[$cache[$field]] = $mod_strings[$value['options']][$this->$field];
                    }
                    else{
                        $return_array[$cache[$field]] = $this->$field;
                    }
                    //end bug 21672
// tjy: no need to do this str_replace as the changes in 29994 for ListViewGeneric.tpl for translation handle this now
//				}elseif(!empty($value['type']) && $value['type'] == 'multienum'&& empty($value['function'])){
//					$return_array[strtoupper($field)] = str_replace('^,^', ', ', $this->$field );
                }elseif(!empty($value['custom_module']) && $value['type'] != 'currency'){
//					$this->format_field($value);
                    $return_array[$cache[$field]] = $this->$field;
                }else{
                    $return_array[$cache[$field]] = $this->$field;
                }
                // handle "Assigned User Name"
                if($field == 'assigned_user_name'){
                    $return_array['ASSIGNED_USER_NAME'] = get_assigned_user_name($this->assigned_user_id);
                }
            }
        }
        return $return_array;
    }
    /**
     * Override this function to set values in the array used to render list view data.
     *
     */
    function get_list_view_data()
    {
        return $this->get_list_view_array();
    }

    /**
     * Construct where clause from a list of name-value pairs.
     *
     */
    function get_where(&$fields_array)
    {
        $where_clause = "WHERE ";
        $first = 1;
        foreach ($fields_array as $name=>$value)
        {
            if ($first)
            {
                $first = 0;
            }
            else
            {
                $where_clause .= " AND ";
            }

            $where_clause .= "$name = '".$this->db->quote($value,false)."'";
        }
        $where_clause .= " AND deleted=0";
        return $where_clause;
    }


    /**
     * Constructs a select query and fetch 1 row using this query, and then process the row
     *
     * Internal function, do not override.
     * @param array @fields_array  array of name value pairs used to construct query.
     * @param boolean $encode Optional, default true, encode fetched data.
     * @return object Instance of this bean with fetched data.
     */
    function retrieve_by_string_fields($fields_array, $encode=true)
    {
        $where_clause = $this->get_where($fields_array);
        if(isset($this->custom_fields))
        $custom_join = $this->custom_fields->getJOIN();
        else $custom_join = false;
        if($custom_join)
        {
            $query = "SELECT $this->table_name.*". $custom_join['select']. " FROM $this->table_name " . $custom_join['join'];
        }
        else
        {
            $query = "SELECT $this->table_name.* FROM $this->table_name ";
        }
        $query .= " $where_clause";
        $GLOBALS['log']->debug("Retrieve $this->object_name: ".$query);
        //requireSingleResult has beeen deprecated.
        //$result = $this->db->requireSingleResult($query, true, "Retrieving record $where_clause:");
        $result = $this->db->limitQuery($query,0,1,true, "Retrieving record $where_clause:");


        if( empty($result))
        {
            return null;
        }
        if($this->db->getRowCount($result) > 1)
        {
            $this->duplicates_found = true;
        }
        $row = $this->db->fetchByAssoc($result, -1, $encode);
        if(empty($row))
        {
            return null;
        }
        $this->fetched_row = $row;
        $this->fromArray($row);
        $this->fill_in_additional_detail_fields();
        return $this;
    }

    /**
    * This method is called during an import before inserting a bean
    * Define an associative array called $special_fields
    * the keys are user defined, and don't directly map to the bean's fields
    * the value is the method name within that bean that will do extra
    * processing for that field. example: 'full_name'=>'get_names_from_full_name'
    *
    */
    function process_special_fields()
    {
        foreach ($this->special_functions as $func_name)
        {
            if ( method_exists($this,$func_name) )
            {
                $this->$func_name();
            }
        }
    }

    /**
     * Override this function to build a where clause based on the search criteria set into bean .
     * @abstract
     */
    function build_generic_where_clause($value)
    {
    }

    function getRelatedFields($module, $id, $fields, $return_array = false){
        if(empty($GLOBALS['beanList'][$module]))return '';
        $object = $GLOBALS['beanList'][$module];
        if ($object == 'aCase') {
            $object = 'Case';
        }

        VardefManager::loadVardef($module, $object);
        if(empty($GLOBALS['dictionary'][$object]['table']))return '';
        $table = $GLOBALS['dictionary'][$object]['table'];
        $query  = 'SELECT id';
        foreach($fields as $field=>$alias){
            if(!empty($GLOBALS['dictionary'][$object]['fields'][$field]['db_concat_fields'])){
                $query .= ' ,' .db_concat($table, $GLOBALS['dictionary'][$object]['fields'][$field]['db_concat_fields']) .  ' as ' . $alias ;
            }else if(!empty($GLOBALS['dictionary'][$object]['fields'][$field]) &&
                (empty($GLOBALS['dictionary'][$object]['fields'][$field]['source']) ||
                $GLOBALS['dictionary'][$object]['fields'][$field]['source'] != "non-db"))
            {
                $query .= ' ,' .$table . '.' . $field . ' as ' . $alias;
            }
            if(!$return_array)$this->$alias = '';
        }
        if($query == 'SELECT id' || empty($id)){
            return '';
        }


        if(isset($GLOBALS['dictionary'][$object]['fields']['assigned_user_id']))
        {
            $query .= " , ".	$table  . ".assigned_user_id owner";

        }
        else if(isset($GLOBALS['dictionary'][$object]['fields']['created_by']))
        {
            $query .= " , ".	$table . ".created_by owner";

        }
        $query .=  ' FROM ' . $table . ' WHERE deleted=0 AND id=';
        $result = $GLOBALS['db']->query($query . "'$id'" );
        $row = $GLOBALS['db']->fetchByAssoc($result);
        if($return_array){
            return $row;
        }
        $owner = (empty($row['owner']))?'':$row['owner'];
        foreach($fields as $alias){
            $this->$alias = (!empty($row[$alias]))? $row[$alias]: '';
            $alias = $alias  .'_owner';
            $this->$alias = $owner;
            $a_mod = $alias  .'_mod';
            $this->$a_mod = $module;
        }


    }


    function &parse_additional_headers(&$list_form, $xTemplateSection)
    {
        return $list_form;
    }

    function assign_display_fields($currentModule)
    {
        global $timedate;
        foreach($this->column_fields as $field)
        {
            if(isset($this->field_name_map[$field]) && empty($this->$field))
            {
                if($this->field_name_map[$field]['type'] != 'date' && $this->field_name_map[$field]['type'] != 'enum')
                $this->$field = $field;
                if($this->field_name_map[$field]['type'] == 'date')
                {
                    $this->$field = $timedate->to_display_date('1980-07-09');
                }
                if($this->field_name_map[$field]['type'] == 'enum')
                {
                    $dom = $this->field_name_map[$field]['options'];
                    global $current_language, $app_list_strings;
                    $mod_strings = return_module_language($current_language, $currentModule);

                    if(isset($mod_strings[$dom]))
                    {
                        $options = $mod_strings[$dom];
                        foreach($options as $key=>$value)
                        {
                            if(!empty($key) && empty($this->$field ))
                            {
                                $this->$field = $key;
                            }
                        }
                    }
                    if(isset($app_list_strings[$dom]))
                    {
                        $options = $app_list_strings[$dom];
                        foreach($options as $key=>$value)
                        {
                            if(!empty($key) && empty($this->$field ))
                            {
                                $this->$field = $key;
                            }
                        }
                    }


                }
            }
        }
    }

    /*
    * 	RELATIONSHIP HANDLING
    */

    function set_relationship($table, $relate_values, $check_duplicates = true,$do_update=false,$data_values=null)
    {
        $where = '';

		// make sure there is a date modified
		$date_modified = $this->db->convert("'".$GLOBALS['timedate']->nowDb()."'", 'datetime');

        $row=null;
        if($check_duplicates)
        {
            $query = "SELECT * FROM $table ";
            $where = "WHERE deleted = '0'  ";
            foreach($relate_values as $name=>$value)
            {
                $where .= " AND $name = '$value' ";
            }
            $query .= $where;
            $result = $this->db->query($query, false, "Looking For Duplicate Relationship:" . $query);
            $row=$this->db->fetchByAssoc($result);
        }

        if(!$check_duplicates || empty($row) )
        {
            unset($relate_values['id']);
            if ( isset($data_values))
            {
                $relate_values = array_merge($relate_values,$data_values);
            }
            $query = "INSERT INTO $table (id, ". implode(',', array_keys($relate_values)) . ", date_modified) VALUES ('" . create_guid() . "', " . "'" . implode("', '", $relate_values) . "', ".$date_modified.")" ;

            $this->db->query($query, false, "Creating Relationship:" . $query);
        }
        else if ($do_update)
        {
            $conds = array();
            foreach($data_values as $key=>$value)
            {
                array_push($conds,$key."='".$this->db->quote($value)."'");
            }
            $query = "UPDATE $table SET ". implode(',', $conds).",date_modified=".$date_modified." ".$where;
            $this->db->query($query, false, "Updating Relationship:" . $query);
        }
    }

    function retrieve_relationships($table, $values, $select_id)
    {
        $query = "SELECT $select_id FROM $table WHERE deleted = 0  ";
        foreach($values as $name=>$value)
        {
            $query .= " AND $name = '$value' ";
        }
        $query .= " ORDER BY $select_id ";
        $result = $this->db->query($query, false, "Retrieving Relationship:" . $query);
        $ids = array();
        while($row = $this->db->fetchByAssoc($result))
        {
            $ids[] = $row;
        }
        return $ids;
    }

    // TODO: this function needs adjustment
    function loadLayoutDefs()
    {
        global $layout_defs;
        if(empty( $this->layout_def) && file_exists('modules/'. $this->module_dir . '/layout_defs.php'))
        {
            include_once('modules/'. $this->module_dir . '/layout_defs.php');
            if(file_exists('custom/modules/'. $this->module_dir . '/Ext/Layoutdefs/layoutdefs.ext.php'))
            {
                include_once('custom/modules/'. $this->module_dir . '/Ext/Layoutdefs/layoutdefs.ext.php');
            }
            if ( empty( $layout_defs[get_class($this)]))
            {
                echo "\$layout_defs[" . get_class($this) . "]; does not exist";
            }

            $this->layout_def = $layout_defs[get_class($this)];
        }
    }

    /**
    * Trigger custom logic for this module that is defined for the provided hook
    * The custom logic file is located under custom/modules/[CURRENT_MODULE]/logic_hooks.php.
    * That file should define the $hook_version that should be used.
    * It should also define the $hook_array.  The $hook_array will be a two dimensional array
    * the first dimension is the name of the event, the second dimension is the information needed
    * to fire the hook.  Each entry in the top level array should be defined on a single line to make it
    * easier to automatically replace this file.  There should be no contents of this file that are not replacable.
    *
    * $hook_array['before_save'][] = Array(1, testtype, 'custom/modules/Leads/test12.php', 'TestClass', 'lead_before_save_1');
    * This sample line creates a before_save hook.  The hooks are procesed in the order in which they
    * are added to the array.  The second dimension is an array of:
    *		processing index (for sorting before exporting the array)
    *		A logic type hook
    *		label/type
    *		php file to include
    *		php class the method is in
    *		php method to call
    *
    * The method signature for version 1 hooks is:
    * function NAME(&$bean, $event, $arguments)
    * 		$bean - $this bean passed in by reference.
    *		$event - The string for the current event (i.e. before_save)
    * 		$arguments - An array of arguments that are specific to the event.
    */
    function call_custom_logic($event, $arguments = null)
    {
        if(!isset($this->processed) || $this->processed == false){
            //add some logic to ensure we do not get into an infinite loop
            if(!empty($this->logicHookDepth[$event])) {
                if($this->logicHookDepth[$event] > 10)
                    return;
            }else
                $this->logicHookDepth[$event] = 0;

            //we have to put the increment operator here
            //otherwise we may never increase the depth for that event in the case
            //where one event will trigger another as in the case of before_save and after_save
            //Also keeping the depth per event allow any number of hooks to be called on the bean
            //and we only will return if one event gets caught in a loop. We do not increment globally
            //for each event called.
            $this->logicHookDepth[$event]++;

            //method defined in 'include/utils/LogicHook.php'

            $logicHook = new LogicHook();
            $logicHook->setBean($this);
            $logicHook->call_custom_logic($this->module_dir, $event, $arguments);
        }
    }


    /*	When creating a custom field of type Dropdown, it creates an enum row in the DB.
     A typical get_list_view_array() result will have the *KEY* value from that drop-down.
     Since custom _dom objects are flat-files included in the $app_list_strings variable,
     We need to generate a key-key pair to get the true value like so:
     ([module]_cstm->fields_meta_data->$app_list_strings->*VALUE*)*/
    function getRealKeyFromCustomFieldAssignedKey($name)
    {
        if ($this->custom_fields->avail_fields[$name]['ext1'])
        {
            $realKey = 'ext1';
        }
        elseif ($this->custom_fields->avail_fields[$name]['ext2'])
        {
            $realKey = 'ext2';
        }
        elseif ($this->custom_fields->avail_fields[$name]['ext3'])
        {
            $realKey = 'ext3';
        }
        else
        {
            $GLOBALS['log']->fatal("SUGARBEAN: cannot find Real Key for custom field of type dropdown - cannot return Value.");
            return false;
        }
        if(isset($realKey))
        {
            return $this->custom_fields->avail_fields[$name][$realKey];
        }
    }

    function bean_implements($interface)
    {
        return false;
    }
    /**
    * Check whether the user has access to a particular view for the current bean/module
    * @param $view string required, the view to determine access for i.e. DetailView, ListView...
    * @param $is_owner bool optional, this is part of the ACL check if the current user is an owner they will receive different access
    */
    function ACLAccess($view,$is_owner='not_set')
    {
        global $current_user;
        if($current_user->isAdminForModule($this->getACLCategory())) {
            return true;
        }
        $not_set = false;
        if($is_owner == 'not_set')
        {
            $not_set = true;
            $is_owner = $this->isOwner($current_user->id);
        }

        //if we don't implent acls return true
        if(!$this->bean_implements('ACL'))
        return true;
        $view = strtolower($view);
        switch ($view)
        {
            case 'list':
            case 'index':
            case 'listview':
                return ACLController::checkAccess($this->module_dir,'list', true);
            case 'edit':
            case 'save':
                if( !$is_owner && $not_set && !empty($this->id)){
                    $class = get_class($this);
                    $temp = new $class();
                    if(!empty($this->fetched_row) && !empty($this->fetched_row['id']) && !empty($this->fetched_row['assigned_user_id']) && !empty($this->fetched_row['created_by'])){
                        $temp->populateFromRow($this->fetched_row);
                    }else{
                        $temp->retrieve($this->id);
                    }
                    $is_owner = $temp->isOwner($current_user->id);
                }
            case 'popupeditview':
            case 'editview':
                return ACLController::checkAccess($this->module_dir,'edit', $is_owner, $this->acltype);
            case 'view':
            case 'detail':
            case 'detailview':
                return ACLController::checkAccess($this->module_dir,'view', $is_owner, $this->acltype);
            case 'delete':
                return ACLController::checkAccess($this->module_dir,'delete', $is_owner, $this->acltype);
            case 'export':
                return ACLController::checkAccess($this->module_dir,'export', $is_owner, $this->acltype);
            case 'import':
                return ACLController::checkAccess($this->module_dir,'import', true, $this->acltype);
        }
        //if it is not one of the above views then it should be implemented on the page level
        return true;
    }
    /**
    * Returns true of false if the user_id passed is the owner
    *
    * @param GUID $user_id
    * @return boolean
    */
    function isOwner($user_id)
    {
        //if we don't have an id we must be the owner as we are creating it
        if(!isset($this->id))
        {
            return true;
        }
        //if there is an assigned_user that is the owner
        if(isset($this->assigned_user_id))
        {
            if($this->assigned_user_id == $user_id) return true;
            return false;
        }
        else
        {
            //other wise if there is a created_by that is the owner
            if(isset($this->created_by) && $this->created_by == $user_id)
            {
                return true;
            }
        }
        return false;
    }
    /**
    * Gets there where statement for checking if a user is an owner
    *
    * @param GUID $user_id
    * @return STRING
    */
    function getOwnerWhere($user_id)
    {
        if(isset($this->field_defs['assigned_user_id']))
        {
            return " $this->table_name.assigned_user_id ='$user_id' ";
        }
        if(isset($this->field_defs['created_by']))
        {
            return " $this->table_name.created_by ='$user_id' ";
        }
        return '';
    }

    /**
    *
    * Used in order to manage ListView links and if they should
    * links or not based on the ACL permissions of the user
    *
    * @return ARRAY of STRINGS
    */
    function listviewACLHelper()
    {
        $array_assign = array();
        if($this->ACLAccess('DetailView'))
        {
            $array_assign['MAIN'] = 'a';
        }
        else
        {
            $array_assign['MAIN'] = 'span';
        }
        return $array_assign;
    }

    /**
    * returns this bean as an array
    *
    * @return array of fields with id, name, access and category
    */
    function toArray($dbOnly = false, $stringOnly = false, $upperKeys=false)
    {
        static $cache = array();
        $arr = array();

        foreach($this->field_defs as $field=>$data)
        {
            if( !$dbOnly || !isset($data['source']) || $data['source'] == 'db')
            if(!$stringOnly || is_string($this->$field))
            if($upperKeys)
            {
                                if(!isset($cache[$field])){
                                    $cache[$field] = strtoupper($field);
                                }
                $arr[$cache[$field]] = $this->$field;
            }
            else
            {
                if(isset($this->$field)){
                    $arr[$field] = $this->$field;
                }else{
                    $arr[$field] = '';
                }
            }
        }
        return $arr;
    }

    /**
    * Converts an array into an acl mapping name value pairs into files
    *
    * @param Array $arr
    */
    function fromArray($arr)
    {
        foreach($arr as $name=>$value)
        {
            $this->$name = $value;
        }
    }

    /**
     * Loads a row of data into instance of a bean. The data is passed as an array to this function
     *
     * @param array $arr row of data fetched from the database.
     * @return  nothing
     *
     * Internal function do not override.
     */
    function loadFromRow($arr)
    {
        $this->populateFromRow($arr);
        $this->processed_dates_times = array();
        $this->check_date_relationships_load();

        $this->fill_in_additional_list_fields();

        if($this->hasCustomFields())$this->custom_fields->fill_relationships();
        $this->call_custom_logic("process_record");
    }

    function hasCustomFields(){
        return !empty($GLOBALS['dictionary'][$this->object_name]['custom_fields']);
    }

   /**
    * Ensure that fields within order by clauses are properly qualified with
    * their tablename.  This qualification is a requirement for sql server support.
    *
    * @param string $order_by original order by from the query
    * @param string $qualify prefix for columns in the order by list.
    * @return  prefixed
    *
    * Internal function do not override.
    */
   function create_qualified_order_by( $order_by, $qualify)
   {	// if the column is empty, but the sort order is defined, the value will throw an error, so do not proceed if no order by is given
    if (empty($order_by))
    {
        return $order_by;
    }
    $order_by_clause = " ORDER BY ";
    $tmp = explode(",", $order_by);
    $comma = ' ';
    foreach ( $tmp as $stmp)
    {
        $stmp = (substr_count($stmp, ".") > 0?trim($stmp):"$qualify." . trim($stmp));
        $order_by_clause .= $comma . $stmp;
        $comma = ", ";
    }
    return $order_by_clause;
   }

   /**
    * Combined the contents of street field 2 thru 4 into the main field
    *
    * @param string $street_field
    */

   function add_address_streets(
       $street_field
       )
    {
        $street_field_2 = $street_field.'_2';
        $street_field_3 = $street_field.'_3';
        $street_field_4 = $street_field.'_4';
        if ( isset($this->$street_field_2)) {
            $this->$street_field .= "\n". $this->$street_field_2;
            unset($this->$street_field_2);
        }
        if ( isset($this->$street_field_3)) {
            $this->$street_field .= "\n". $this->$street_field_3;
            unset($this->$street_field_3);
        }
        if ( isset($this->$street_field_4)) {
            $this->$street_field .= "\n". $this->$street_field_4;
            unset($this->$street_field_4);
        }
        if ( isset($this->$street_field)) {
            $this->$street_field = trim($this->$street_field, "\n");
        }
    }
/**
 * Encrpyt and base64 encode an 'encrypt' field type in the bean using Blowfish. The default system key is stored in cache/Blowfish/{keytype}
 * @param STRING value -plain text value of the bean field.
 * @return string
 */
    function encrpyt_before_save($value)
    {
        require_once("include/utils/encryption_utils.php");
        return blowfishEncode(blowfishGetKey('encrypt_field'),$value);
    }

/**
 * Decode and decrypt a base 64 encoded string with field type 'encrypt' in this bean using Blowfish.
 * @param STRING value - an encrypted and base 64 encoded string.
 * @return string
 */
    function decrypt_after_retrieve($value)
    {
        require_once("include/utils/encryption_utils.php");
        return blowfishDecode(blowfishGetKey('encrypt_field'), $value);
    }

    /**
    * Moved from save() method, functionality is the same, but this is intended to handle
    * Optimistic locking functionality.
    */
    private function _checkOptimisticLocking($action, $isUpdate){
        if($this->optimistic_lock && !isset($_SESSION['o_lock_fs'])){
            if(isset($_SESSION['o_lock_id']) && $_SESSION['o_lock_id'] == $this->id && $_SESSION['o_lock_on'] == $this->object_name)
            {
                if($action == 'Save' && $isUpdate && isset($this->modified_user_id) && $this->has_been_modified_since($_SESSION['o_lock_dm'], $this->modified_user_id))
                {
                    $_SESSION['o_lock_class'] = get_class($this);
                    $_SESSION['o_lock_module'] = $this->module_dir;
                    $_SESSION['o_lock_object'] = $this->toArray();
                    $saveform = "<form name='save' id='save' method='POST'>";
                    foreach($_POST as $key=>$arg)
                    {
                        $saveform .= "<input type='hidden' name='". addslashes($key) ."' value='". addslashes($arg) ."'>";
                    }
                    $saveform .= "</form><script>document.getElementById('save').submit();</script>";
                    $_SESSION['o_lock_save'] = $saveform;
                    header('Location: index.php?module=OptimisticLock&action=LockResolve');
                    die();
                }
                else
                {
                    unset ($_SESSION['o_lock_object']);
                    unset ($_SESSION['o_lock_id']);
                    unset ($_SESSION['o_lock_dm']);
                }
            }
        }
        else
        {
            if(isset($_SESSION['o_lock_object']))	{ unset ($_SESSION['o_lock_object']); }
            if(isset($_SESSION['o_lock_id']))		{ unset ($_SESSION['o_lock_id']); }
            if(isset($_SESSION['o_lock_dm']))		{ unset ($_SESSION['o_lock_dm']); }
            if(isset($_SESSION['o_lock_fs']))		{ unset ($_SESSION['o_lock_fs']); }
            if(isset($_SESSION['o_lock_save']))		{ unset ($_SESSION['o_lock_save']); }
        }
    }

    /**
    * Send assignment notifications and invites for meetings and calls
    */
    private function _sendNotifications($check_notify){
        if($check_notify || (isset($this->notify_inworkflow) && $this->notify_inworkflow == true)){ // cn: bug 5795 - no invites sent to Contacts, and also bug 25995, in workflow, it will set the notify_on_save=true.

            $admin = new Administration();
            $admin->retrieveSettings();
            $sendNotifications = false;

            if ($admin->settings['notify_on'])
            {
                $GLOBALS['log']->info("Notifications: user assignment has changed, checking if user receives notifications");
                $sendNotifications = true;
            }
            elseif(isset($_REQUEST['send_invites']) && $_REQUEST['send_invites'] == 1)
            {
                // cn: bug 5795 Send Invites failing for Contacts
                $sendNotifications = true;
            }
            else
            {
                $GLOBALS['log']->info("Notifications: not sending e-mail, notify_on is set to OFF");
            }


            if($sendNotifications == true)
            {
                $notify_list = $this->get_notification_recipients();
                foreach ($notify_list as $notify_user)
                {
                    $this->send_assignment_notifications($notify_user, $admin);
                }
            }
        }
    }


    /**
     * Called from ImportFieldSanitize::relate(), when creating a new bean in a related module. Will
     * copies fields over from the current bean into the related. Designed to be overriden in child classes.
     *
     * @param SugarBean $newbean newly created related bean
     */
    public function populateRelatedBean(
        SugarBean $newbean
        )
    {
    }

    /**
     * Called during the import process before a bean save, to handle any needed pre-save logic when
     * importing a record
     */
    public function beforeImportSave()
    {
    }

    /**
     * Called during the import process after a bean save, to handle any needed post-save logic when
     * importing a record
     */
    public function afterImportSave()
    {
    }

    /**
     * This function is designed to cache references to field arrays that were previously stored in the
     * bean files and have since been moved to seperate files. Was previously in include/CacheHandler.php
     *
     * @deprecated
     * @param $module_dir string the module directory
     * @param $module string the name of the module
     * @param $key string the type of field array we are referencing, i.e. list_fields, column_fields, required_fields
     **/
    private function _loadCachedArray(
        $module_dir,
        $module,
        $key
        )
    {
        static $moduleDefs = array();

        $fileName = 'field_arrays.php';

        $cache_key = "load_cached_array.$module_dir.$module.$key";
        $result = sugar_cache_retrieve($cache_key);
        if(!empty($result))
        {
        	// Use SugarCache::EXTERNAL_CACHE_NULL_VALUE to store null values in the cache.
        	if($result == SugarCache::EXTERNAL_CACHE_NULL_VALUE)
        	{
        		return null;
        	}

            return $result;
        }

        if(file_exists('modules/'.$module_dir.'/'.$fileName))
        {
            // If the data was not loaded, try loading again....
            if(!isset($moduleDefs[$module]))
            {
                include('modules/'.$module_dir.'/'.$fileName);
                $moduleDefs[$module] = $fields_array;
            }
            // Now that we have tried loading, make sure it was loaded
            if(empty($moduleDefs[$module]) || empty($moduleDefs[$module][$module][$key]))
            {
                // It was not loaded....  Fail.  Cache null to prevent future repeats of this calculation
				sugar_cache_put($cache_key, SugarCache::EXTERNAL_CACHE_NULL_VALUE);
                return  null;
            }

            // It has been loaded, cache the result.
            sugar_cache_put($cache_key, $moduleDefs[$module][$module][$key]);
            return $moduleDefs[$module][$module][$key];
        }

        // It was not loaded....  Fail.  Cache null to prevent future repeats of this calculation
        sugar_cache_put($cache_key, SugarCache::EXTERNAL_CACHE_NULL_VALUE);
		return null;
	}

    /**
     * Returns the ACL category for this module; defaults to the SugarBean::$acl_category if defined
     * otherwise it is SugarBean::$module_dir
     *
     * @return string
     */
    public function getACLCategory()
    {
        return !empty($this->acl_category)?$this->acl_category:$this->module_dir;
    }

    /**
     * Returns the query used for the export functionality for a module. Override this method if you wish
     * to have a custom query to pull this data together instead
     *
     * @param string $order_by
     * @param string $where
     * @return string SQL query
     */
	public function create_export_query($order_by, $where)
	{
		return $this->create_new_list_query($order_by, $where, array(), array(), 0, '', false, $this, true);
	}
}
