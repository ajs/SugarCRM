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

* Description: This file handles the Data base functionality for the application.
* It acts as the DB abstraction layer for the application. It depends on helper classes
* which generate the necessary SQL. This sql is then passed to PEAR DB classes.
* The helper class is chosen in DBManagerFactory, which is driven by 'db_type' in 'dbconfig' under config.php.
*
* All the functions in this class will work with any bean which implements the meta interface.
* The passed bean is passed to helper class which uses these functions to generate correct sql.
*
* The meta interface has the following functions:
* getTableName()                Returns table name of the object.
* getFieldDefinitions()         Returns a collection of field definitions in order.
* getFieldDefintion(name)       Return field definition for the field.
* getFieldValue(name)           Returns the value of the field identified by name.
*                               If the field is not set, the function will return boolean FALSE.
* getPrimaryFieldDefinition()   Returns the field definition for primary key
*
* The field definition is an array with the following keys:
*
* name      This represents name of the field. This is a required field.
* type      This represents type of the field. This is a required field and valid values are:
*           �   int
*           �   long
*           �   varchar
*           �   text
*           �   date
*           �   datetime
*           �   double
*           �   float
*           �   uint
*           �   ulong
*           �   time
*           �   short
*           �   enum
* length    This is used only when the type is varchar and denotes the length of the string.
*           The max value is 255.
* enumvals  This is a list of valid values for an enum separated by "|".
*           It is used only if the type is �enum�;
* required  This field dictates whether it is a required value.
*           The default value is �FALSE�.
* isPrimary This field identifies the primary key of the table.
*           If none of the fields have this flag set to �TRUE�,
*           the first field definition is assume to be the primary key.
*           Default value for this field is �FALSE�.
* default   This field sets the default value for the field definition.
*
*
* Portions created by SugarCRM are Copyright (C) SugarCRM, Inc.
* All Rights Reserved.
* Contributor(s): ______________________________________..
********************************************************************************/

//Technically we can port all the functions in the latest bean to this file
// that is what PEAR is doing anyways.

require_once('include/database/MysqlManager.php');

class MysqliManager extends MysqlManager
{
    /**
     * @see DBManager::$dbType
     */
    public $dbType = 'mysql';

    /**
     * @see DBManager::$backendFunctions
     */
    protected $backendFunctions = array(
        'free_result'        => 'mysqli_free_result',
        'close'              => 'mysqli_close',
        'row_count'          => 'mysqli_num_rows',
        'affected_row_count' => 'mysqli_affected_rows',
        );

    /**
     * @see DBManager::checkError()
     */
    public function checkError(
        $msg = '',
        $dieOnError = false
        )
    {
        if (DBManager::checkError($msg, $dieOnError))
            return true;

        $userMsg = inDeveloperMode()?"$msg: ":"";

        if (mysqli_errno($this->getDatabase())){
            if($this->dieOnError || $dieOnError){
                $GLOBALS['log']->fatal("$msg: MySQL error ".mysqli_errno($this->database).": ".mysqli_error($this->database));
                sugar_die ($userMsg.$GLOBALS['app_strings']['ERR_DB_FAIL']);
            }
            else{
                $this->last_error = $userMsg."MySQL error ".mysqli_errno($this->database).": ".mysqli_error($this->database);
                $GLOBALS['log']->error("$msg: MySQL error ".mysqli_errno($this->database).": ".mysqli_error($this->database));

            }
            return true;
        }
        return false;
    }

    /**
     * @see MysqlManager::query()
     */
    public function query(
        $sql,
        $dieOnError = false,
        $msg = '',
        $suppress = false,
        $autofree = false
        )
    {
        static $queryMD5 = array();

        parent::countQuery($sql);
        $GLOBALS['log']->info('Query:' . $sql);
        $this->checkConnection();
        //$this->freeResult();
        $this->query_time = microtime(true);
        $this->lastsql = $sql;
        if ($suppress==true){
        }
        else {
            $result = mysqli_query($this->database,$sql);
        }
        $md5 = md5($sql);

        if (empty($queryMD5[$md5]))
        	$queryMD5[$md5] = true;

        $this->lastmysqlrow = -1;
        $this->query_time = microtime(true) - $this->query_time;
        $GLOBALS['log']->info('Query Execution Time:'.$this->query_time);

        // This is some heavy duty debugging, leave commented out unless you need this:
        /*
        $bt = debug_backtrace();
        for ( $i = count($bt) ; $i-- ; $i > 0 ) {
            if ( strpos('MysqliManager.php',$bt[$i]['file']) === false ) {
                $line = $bt[$i];
            }
        }

        $GLOBALS['log']->fatal("${line['file']}:${line['line']} ${line['function']} \nQuery: $sql\n");
        */


		$this->checkError($msg.' Query Failed: ' . $sql, $dieOnError);
        if($autofree)
            $this->lastResult[] =& $result;

        return $result;
    }

    /**
     * @see DBManager::getFieldsArray()
     */
    public function getFieldsArray(
        &$result,
        $make_lower_case = false
        )
    {
        $field_array = array();

        if (!isset($result) || empty($result))
            return 0;

        $i = 0;
        while ($i < mysqli_num_fields($result)) {
            $meta = mysqli_fetch_field_direct($result, $i);
            if (!$meta)
                return 0;

            if($make_lower_case == true)
                $meta->name = strtolower($meta->name);

            $field_array[] = $meta->name;

            $i++;
        }

        return $field_array;
    }

    /**
     * @see DBManager::fetchByAssoc()
     */
    public function fetchByAssoc(
        &$result,
        $rowNum = -1,
        $encode = true
        )
    {
        if (!$result)
            return false;

        if ($result && $rowNum > -1) {
            if ($this->getRowCount($result) > $rowNum)
                mysqli_data_seek($result, $rowNum);
            $this->lastmysqlrow = $rowNum;
        }

        $row = mysqli_fetch_assoc($result);

        if ($encode && $this->encode && is_array($row))
            return array_map('to_html', $row);

        if($row == null) $row = false; //Make sure MySQLi driver results are consistent with other database drivers
        return $row;
    }

    /**
     * @see DBManager::quote()
     */
    public function quote(
        $string,
        $isLike = true
        )
    {
        return mysqli_escape_string($this->getDatabase(),DBManager::quote($string));
    }

    /**
     * @see DBManager::quoteForEmail()
     */
    public function quoteForEmail(
        $string,
        $isLike = true
        )
    {
        return mysqli_escape_string($this->getDatabase(),$string);
    }

    /**
     * @see DBManager::connect()
     */
    public function connect(
        array $configOptions = null,
        $dieOnError = false
        )
    {
        global $sugar_config;

        if (is_null($configOptions))
            $configOptions = $sugar_config['dbconfig'];

        if(!isset($this->database)) {

   	        //mysqli connector has a separate parameter for port.. We need to separate it out from the host name
			$dbhost=$configOptions['db_host_name'];
	        $dbport=null;
	        $pos=strpos($configOptions['db_host_name'],':');
	        if ($pos !== false) {
	        	$dbhost=substr($configOptions['db_host_name'],0,$pos);
	        	$dbport=substr($configOptions['db_host_name'],$pos+1);
	        }

        	$this->database = mysqli_connect($dbhost,$configOptions['db_user_name'],$configOptions['db_password'],$configOptions['db_name'],$dbport);
        	if(empty($this->database)) {
        	    $GLOBALS['log']->fatal("Could not connect to DB server ".$dbhost." as ".$configOptions['db_user_name'].". port " .$dbport . ": " . mysqli_connect_error());
                sugar_die($GLOBALS['app_strings']['ERR_NO_DB']);
        	}
        }
        if(!@mysqli_select_db($this->database,$configOptions['db_name'])) {
            $GLOBALS['log']->fatal( "Unable to select database {$configOptions['db_name']}: " . mysqli_connect_error());
            sugar_die($GLOBALS['app_strings']['ERR_NO_DB']);
        }

        // cn: using direct calls to prevent this from spamming the Logs
        mysqli_query($this->database,"SET CHARACTER SET utf8"); // no quotes around "[charset]"
        mysqli_query($this->database,"SET NAMES 'utf8'");

        if($this->checkError('Could Not Connect', $dieOnError))
            $GLOBALS['log']->info("connected to db");
    }
}

?>
