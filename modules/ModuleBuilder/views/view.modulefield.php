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

require_once('modules/ModuleBuilder/MB/AjaxCompose.php');
require_once('modules/DynamicFields/FieldViewer.php');

class ViewModulefield extends SugarView
{
    /**
	 * @see SugarView::_getModuleTitleParams()
	 */
	protected function _getModuleTitleParams($browserTitle = false)
	{
	    global $mod_strings;
	    
    	return array(
    	   translate('LBL_MODULE_NAME','Administration'),
    	   ModuleBuilderController::getModuleTitle(),
    	   );
    }

	function display()
	{
        $ac = $this->fetch();
        echo $ac->getJavascript();
    }

    function fetch(
        $ac = false
        )
    {
        $fv = new FieldViewer();
        if(empty($_REQUEST['field'])&& !empty($_REQUEST['name']))$_REQUEST['field'] = $_REQUEST['name'];
        $field_name = '';
        if(!empty($this->view_object_map['field_name']))
            $field_name = $this->view_object_map['field_name'];
        elseif(!empty($_REQUEST['field']))
            $field_name = $_REQUEST['field'];
        else
            $field_name = '';

        $action = 'saveField'; // tyoung bug 17606: default action is to save as a dynamic field; but for standard OOB
                               // fields we override this so don't create a new dynamic field instead of updating the existing field

        $isClone = false;
        if(!empty($this->view_object_map['is_clone']) && $this->view_object_map['is_clone'])
            $isClone = true;
		/*
		$field_types =  array('varchar'=>'YourField', 'int'=>'Integer', 'float'=>'Decimal','bool'=>'Checkbox','enum'=>'DropDown',
				'date'=>'Date', 'phone' => 'Phone', 'currency' => 'Currency', 'html' => 'HTML', 'radioenum' => 'Radio',
				'relate' => 'Relate', 'address' => 'Address', 'text' => 'TextArea', 'url' => 'Link');
		*/
		$field_types = $GLOBALS['mod_strings']['fieldTypes'];
		if (isset($field_types['encrypt']))
		  unset($field_types['encrypt']);
        $field_name_exceptions = array(
            //bug 22264: Field name must not be an SQL keyword.
            //Taken from SQL Server's list of reserved keywords; http://msdn.microsoft.com/en-us/library/aa238507(SQL.80).aspx
            'ADD','EXCEPT','PERCENT','ALL','EXEC','PLAN','ALTER','EXECUTE','PRECISION','AND','EXISTS','PRIMARY',
            'ANY','EXIT','PRINT','AS','FETCH','PROC','ASC','FILE','PROCEDURE','AUTHORIZATION','FILLFACTOR','PUBLIC',
            'BACKUP','FOR','RAISERROR','BEGIN','FOREIGN','READ','BETWEEN','FREETEXT','READTEXT','BREAK','FREETEXTTABLE',
            'RECONFIGURE','BROWSE','FROM','REFERENCES','BULK','FULL','REPLICATION','BY','FUNCTION','RESTORE',
            'CASCADE','GOTO','RESTRICT','CASE','GRANT','RETURN','CHECK','GROUP','REVOKE','CHECKPOINT','HAVING','RIGHT','CLOSE',
            'HOLDLOCK','ROLLBACK','CLUSTERED','IDENTITY','ROWCOUNT','COALESCE','IDENTITY_INSERT','ROWGUIDCOL','COLLATE','IDENTITYCOL',
            'RULE','COLUMN','IF','SAVE','COMMIT','IN','SCHEMA','COMPUTE','INDEX','SELECT','CONSTRAINT','INNER','SESSION_USER',
            'CONTAINS','INSERT','SET','CONTAINSTABLE','INTERSECT','SETUSER','CONTINUE','INTO','SHUTDOWN','CONVERT','IS','SOME',
            'CREATE','JOIN','STATISTICS','CROSS','KEY','SYSTEM_USER','CURRENT','KILL','TABLE','CURRENT_DATE','LEFT','TEXTSIZE',
            'CURRENT_TIME','LIKE','THEN','CURRENT_TIMESTAMP','LINENO','TO','CURRENT_USER','LOAD','TOP','CURSOR','NATIONAL','TRAN',
            'DATABASE','NOCHECK','TRANSACTION','DBCC','NONCLUSTERED','TRIGGER','DEALLOCATE','NOT','TRUNCATE','DECLARE','NULL','TSEQUAL',
            'DEFAULT','NULLIF','UNION','DELETE','OF','UNIQUE','DENY','OFF','UPDATE','DESC','OFFSETS','UPDATETEXT',
            'DISK','ON','USE','DISTINCT','OPEN','USER','DISTRIBUTED','OPENCONNECTOR','VALUES','DOUBLE','OPENQUERY','VARYING',
            'DROP','OPENROWSET','VIEW','DUMMY','OPENXML','WAITFOR','DUMP','OPTION','WHEN','ELSE','OR','WHERE',
            'END','ORDER','WHILE','ERRLVL','OUTER','WITH','ESCAPE','OVER','WRITETEXT',
            //Mysql Keywords from http://dev.mysql.com/doc/refman/5.0/en/reserved-words.html (those not in MSSQL's list)
			'ANALYZE', 'ASENSITIVE', 'BEFORE', 'BIGINT', 'BINARY', 'BOTH', 'CALL', 'CHANGE', 'CHARACTER',
			'CONDITION', 'DATABASES', 'DAY_HOUR', 'DAY_MICROSECOND', 'DAY_MINUTE', 'DAY_SECOND', 'DEC', 'DECIMAL', 'DELAYED',
			'DESCRIBE', 'DETERMINISTIC', 'DISTINCTROW', 'DIV', 'DUAL', 'EACH', 'ELSEIF', 'ENCLOSED', 'ESCAPED', 'EXPLAIN',
			'FALSE', 'FLOAT', 'FLOAT4', 'FLOAT8', 'FORCE', 'FULLTEXT', 'HIGH_PRIORITY', 'HOUR_MICROSECOND', 'HOUR_MINUTE',
			'HOUR_SECOND', 'IGNORE', 'INFILE', 'INOUT', 'INSENSITIVE', 'INT', 'INT1', 'INT2', 'INT3', 'INT4', 'INT8',
			'INTEGER', 'ITERATE', 'KEYS', 'LEADING', 'LEAVE', 'LIMIT', 'LINES', 'LOCALTIME', 'LOCALTIMESTAMP', 'LOCK',
			'LONGBLOB', 'LONGTEXT', 'LOOP', 'LOW_PRIORITY', 'MATCH', 'MEDIUMBLOB', 'MEDIUMINT', 'MEDIUMTEXT', 'MIDDLEINT',
			'MINUTE_MICROSECOND', 'MINUTE_SECOND', 'MOD', 'MODIFIES', 'NATURAL', 'NO_WRITE_TO_BINLOG', 'NUMERIC', 'OPTIMIZE',
			'OPTIONALLY', 'OUT', 'OUTFILE', 'PURGE', 'READS', 'REAL', 'REGEXP', 'RELEASE', 'RENAME', 'REPEAT', 'REPLACE',
			'REQUIRE', 'RLIKE', 'SCHEMAS', 'SECOND_MICROSECOND', 'SENSITIVE', 'SEPARATOR', 'SHOW', 'SMALLINT', 'SONAME',
			'SPATIAL', 'SPECIFIC', 'SQL', 'SQLEXCEPTION', 'SQLSTATE', 'SQLWARNING', 'SQL_BIG_RESULT', 'SQL_CALC_FOUND_ROWS',
			'SQL_SMALL_RESULT', 'SSL', 'STARTING', 'STRAIGHT_JOIN', 'TERMINATED', 'TINYBLOB', 'TINYINT', 'TINYTEXT',
			'TRAILING', 'TRUE', 'UNDO', 'UNLOCK', 'UNSIGNED', 'USAGE', 'USING', 'UTC_DATE', 'UTC_TIME', 'UTC_TIMESTAMP',
			'VARBINARY', 'VARCHARACTER', 'WRITE', 'XOR', 'YEAR_MONTH', 'ZEROFILL', 'CONNECTION', 'LABEL', 'UPGRADE',
			//Oracle datatypes
            'DATE','VARCHAR','VARCHAR2','NVARCHAR2','CHAR','NCHAR','NUMBER','PLS_INTEGER','BINARY_INTEGER','LONG','TIMESTAMP',
			'INTERVAL','RAW','ROWID','UROWID','MLSLABEL','CLOB','NCLOB','BLOB','BFILE','XMLTYPE',
			//SugarCRM reserved
			'ID', 'ID_C',
			);

        if(! isset($_REQUEST['view_package']) || $_REQUEST['view_package'] == 'studio' || empty ( $_REQUEST [ 'view_package' ] ) ) {
            $module = new stdClass;
            $moduleName = $_REQUEST['view_module'];

            global $beanList;

            $objectName = $beanList[$moduleName];
            $className = $objectName;
            if($objectName == 'aCase') // Bug 17614 - renamed aCase as Case in vardefs for backwards compatibililty with 451 modules
                $objectName = 'Case';
			
			$module = new $className();
            
            VardefManager::loadVardef($moduleName, $objectName,true);
            global $dictionary;
            $module->mbvardefs->vardefs =  $dictionary[$objectName];
			
//          $GLOBALS['log']->debug('vardefs from dictionary = '.print_r($module->mbvardefs->vardefs,true));
            $module->name = $moduleName;
            if(!$ac){
                $ac = new AjaxCompose();
            }
            $vardef = (!empty($module->mbvardefs->vardefs['fields'][$field_name]))? $module->mbvardefs->vardefs['fields'][$field_name]: array();
            if($isClone){
                unset($vardef['name']);
            }
          
            if(empty($vardef['name'])){
                if(!empty($_REQUEST['type']))
                    $vardef['type'] = $_REQUEST['type'];
                    $fv->ss->assign('hideLevel', 0);
            }elseif(isset($vardef['custom_module'])){
                $fv->ss->assign('hideLevel', 2);
            }else{
                $action = 'saveSugarField'; // tyoung - for OOB fields we currently only support modifying the label
                $fv->ss->assign('hideLevel', 3);
            }
            if($isClone && isset($vardef['type']) && $vardef['type'] == 'datetime'){
            	$vardef['type'] = 'datetimecombo';
            }
            
			require_once ('modules/DynamicFields/FieldCases.php') ;
            $tf = get_widget ( empty($vardef [ 'type' ]) ?  "" : $vardef [ 'type' ]) ;
            $tf->module = $module;
            $tf->populateFromRow($vardef);
			$vardef = array_merge($vardef, $tf->get_field_def());
            
            //          $GLOBALS['log']->debug('vardefs after loading = '.print_r($vardef,true));
           
            
            //Check if autoincrement fields are allowed
            $allowAutoInc = true;
            foreach($module->field_defs as $field => $def)
            {
            	if (!empty($def['type']) && $def['type'] == "int" && !empty($def['auto_increment'])) {
            	   $allowAutoInc = false;
            	   break;
            	}
            }
            $fv->ss->assign( 'allowAutoInc', $allowAutoInc);   

            $GLOBALS['log']->warn('view.modulefield: hidelevel '.$fv->ss->get_template_vars('hideLevel')." ".print_r($vardef,true));
            if(!empty($vardef['vname'])){
                $fv->ss->assign('lbl_value', htmlentities(translate($vardef['vname'], $moduleName), ENT_QUOTES, 'UTF-8'));
            }
            $fv->ss->assign('module', $module);
            if(empty($module->mbvardefs->vardefs['fields']['parent_name']) || (isset($vardef['type']) && $vardef['type'] == 'parent'))
				$field_types['parent'] = $GLOBALS['mod_strings']['parent'];

            $edit_or_add = 'editField' ;

        } else
        {
            require_once('modules/ModuleBuilder/MB/ModuleBuilder.php');
            $mb = new ModuleBuilder();
            $module =& $mb->getPackageModule($_REQUEST['view_package'], $_REQUEST['view_module']);
            $package =& $mb->packages[$_REQUEST['view_package']];
            $module->getVardefs();
            if(!$ac){
                $ac = new AjaxCompose();
            }
            $vardef = (!empty($module->mbvardefs->vardefs['fields'][$field_name]))? $module->mbvardefs->vardefs['fields'][$field_name]: array();
            if($isClone){
                unset($vardef['name']);
            }

            if(empty($vardef['name'])){
                if(!empty($_REQUEST['type']))$vardef['type'] = $_REQUEST['type'];
                    $fv->ss->assign('hideLevel', 0);
            }else{
                if(!empty($module->mbvardefs->vardef['fields'][$vardef['name']])){
                    $fv->ss->assign('hideLevel', 1);
                }elseif(isset($vardef['custom_module'])){
                    $fv->ss->assign('hideLevel', 2);
                }else{
                    $fv->ss->assign('hideLevel', 3); // tyoung bug 17350 - effectively mark template derived fields as readonly
                }
            }

			require_once ('modules/DynamicFields/FieldCases.php') ;
            $tf = get_widget ( empty($vardef [ 'type' ]) ?  "" : $vardef [ 'type' ]) ;
            $tf->module = $module;
            $tf->populateFromRow($vardef);
            $vardef = array_merge($vardef, $tf->get_field_def());
			
			

            $fv->ss->assign('module', $module);
            $fv->ss->assign('package', $package);
            $fv->ss->assign('MB','1');

            if(isset($vardef['vname']))
                $fv->ss->assign('lbl_value', htmlentities($module->getLabel('en_us',$vardef['vname']), ENT_QUOTES, 'UTF-8'));
			if(empty($module->mbvardefs->vardefs['fields']['parent_name']) || (isset($vardef['type']) && $vardef['type'] == 'parent'))
				$field_types['parent'] = $GLOBALS['mod_strings']['parent'];

            $edit_or_add = 'mbeditField';
        }

        if($_REQUEST['action'] == 'RefreshField'){
        	require_once('modules/DynamicFields/FieldCases.php');
            $field = get_widget($_POST['type']);
            $field->populateFromPost();
            $vardef = $field->get_field_def();
            $vardef['options'] = $_REQUEST['new_dropdown'];
            $fv->ss->assign('lbl_value', htmlentities($_REQUEST['labelValue'], ENT_QUOTES, 'UTF-8'));
        }

        foreach(array("formula", "default", "comments", "help") as $toEscape)
		{
			if (!empty($vardef[$toEscape]) && is_string($vardef[$toEscape])) {
	        	$vardef[$toEscape] = htmlentities($vardef[$toEscape], ENT_QUOTES, 'UTF-8');
	        }
		}
		
        if(!empty($vardef['studio']) && is_array($vardef['studio']) && !empty($vardef['studio']['no_duplicate']) && $vardef['studio']['no_duplicate'] == true) {
            $fv->ss->assign('no_duplicate', true);
        }

        $fv->ss->assign('action',$action);
        $fv->ss->assign('isClone', ($isClone ? 1 : 0));
        $json = getJSONobj();

        $fv->ss->assign('field_name_exceptions', $json->encode($field_name_exceptions));
        ksort($field_types);
        $fv->ss->assign('field_types',$field_types);
        $fv->ss->assign('importable_options', $GLOBALS['app_list_strings']['custom_fields_importable_dom']);
        $fv->ss->assign('duplicate_merge_options', $GLOBALS['app_list_strings']['custom_fields_merge_dup_dom']);

        $triggers = array () ;
        $existing_field_names = array () ;
        foreach ( $module->mbvardefs->vardefs['fields'] as $field )
        {
        	if ($field [ 'type' ] == 'enum' || $field [ 'type'] == 'multienum' )
        	{
        		$triggers [] = $field [ 'name' ] ;
        	}
        	
        	if (!isset($field['source']) || $field['source'] != 'non-db') {
        		if(preg_match('/^(.*?)(_c)?$/', $field['name'], $matches))
        		{
        			$existing_field_names [] = strtoupper($matches[1]);	
        		}
        	}
        }
        
        $fv->ss->assign('triggers',$triggers);
        $fv->ss->assign('existing_field_names', $json->encode($existing_field_names));
        $fv->ss->assign('mod_strings',$GLOBALS['mod_strings']);

		// jchi #24880
		// end
        $layout = $fv->getLayout($vardef);

        $fv->ss->assign('fieldLayout', $layout);
        if(empty($vardef['type']))
            $vardef['type'] = 'varchar';
        $fv->ss->assign('vardef', $vardef);


        if(empty($_REQUEST['field'])){
            $edit_or_add = 'addField';
        }

        $fv->ss->assign('help_group', $edit_or_add);
        $body = $fv->ss->fetch('modules/ModuleBuilder/tpls/MBModule/field.tpl');
        $ac->addSection('east', translate('LBL_SECTION_FIELDEDITOR','ModuleBuilder'), $body );
        return $ac;
    }
}