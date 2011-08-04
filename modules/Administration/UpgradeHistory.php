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





// The history of upgrades on the system
class UpgradeHistory extends SugarBean
{
    var $new_schema = true;
    var $module_dir = 'Administration';

    // Stored fields
    var $id;
    var $filename;
    var $md5sum;
    var $type;
    var $version;
    var $status;
    var $date_entered;
    var $name;
    var $description;
    var $id_name;
    var $manifest;
    var $enabled;

    var $table_name = "upgrade_history";
    var $object_name = "UpgradeHistory";
    var $column_fields = Array( "id", "filename", "md5sum", "type", "version", "status", "date_entered" );
    var $disable_custom_fields = true;

    function delete()
    {
        $this->dbManager->query( "delete from " . $this->table_name . " where id = '" . $this->id . "'" );
    }

    function UpgradeHistory()
    {
        parent::SugarBean();
        $this->disable_row_level_security = true;
    }

    function getAllOrderBy($orderBy){
        $query = "SELECT id FROM " . $this->table_name . " ORDER BY ".$orderBy;
        return $this->getList($query);
    }
    /**
     * Given a name check if it exists in the table
     * @param name    the unique key from the manifest
     * @param id      the id of the item you are comparing to
     * @return upgrade_history object if found, null otherwise
     */
    function checkForExisting($patch_to_check){
        $uh = new UpgradeHistory();
        if($patch_to_check != null){

            if(empty($patch_to_check->id_name)){
                $where = " WHERE name = '$patch_to_check->name' ";
            }else{
                $where = " WHERE id_name = '$patch_to_check->id_name' ";
            }

            if(!empty($patch_to_check->id)){
                $where .= "  AND id != '$patch_to_check->id'  ";
            }else{
                $where .= "  AND id is not null  ";
            }

            $query = "SELECT id FROM " . $this->table_name . " ". $where;

            $result = $uh->db->query($query);
            if(empty($result)){
                return null;
            }
            $row = $uh->db->fetchByAssoc($result);
            if(empty($row)){
                return null;
            }
            if(!empty($row['id'])){
                return $uh->retrieve($row['id']);
            }
        }
        return null;
    }

    /**
     * Check if this is an upgrade, if it is then return the latest version before this installation
     */
    function determineIfUpgrade($id_name, $version){
        $query = "SELECT id, version FROM " . $this->table_name . " WHERE id_name = '$id_name' ORDER BY date_entered DESC";
        $result = $this->db->query($query);
         if(empty($result)){
            return null;
         }else{
            $temp_version = 0;
            $id = '';
            while($row = $this->db->fetchByAssoc($result))
            {
                if(!$this->is_right_version_greater(explode('.', $row['version']), explode('.', $temp_version))){
                    $temp_version = $row['version'];
                    $id = $row['id'];
                }
            }//end while
            if($this->is_right_version_greater(explode('.', $temp_version), explode('.', $version), false))
                return array('id' => $id, 'version' => $temp_version);
            else
                return null;
         }
    }

    function getAll()
    {
        $query = "SELECT id FROM " . $this->table_name . " ORDER BY date_entered desc";
        return $this->getList($query);
    }

    function getList($query){
        return( parent::build_related_list( $query, $this ) );
    }

    function findByMd5( $var_md5 )
    {
        $query = "SELECT id FROM " . $this->table_name . " where md5sum = '$var_md5'";
        return( parent::build_related_list( $query, $this ) );
    }

    function UninstallAvailable($patch_list, $patch_to_check)
    {
        //before we even go through the list, let us try to see if we find a match.
        $history_object = $this->checkForExisting($patch_to_check);
        if($history_object != null){
            if((!empty($history_object->id_name) && !empty($patch_to_check->id_name) && strcmp($history_object->id_name,  $patch_to_check->id_name) == 0) || strcmp($history_object->name,  $patch_to_check->name) == 0){
                //we have found a match
                //if the patch_to_check version is greater than the found version
                return ($this->is_right_version_greater(explode('.', $history_object->version), explode('.', $patch_to_check->version)));
            }else{
                return true;
            }
        }
        //we will only go through this loop if we have not found another UpgradeHistory object
        //with a matching unique_key in the database
        foreach($patch_list as $more_recent_patch)
        {
            if($more_recent_patch->id == $patch_to_check->id)
                break;

            //we will only resort to checking the files if we cannot find the unique_keys
            //or the unique_keys do not match
            $patch_to_check_backup_path    = clean_path(remove_file_extension(from_html($patch_to_check->filename))).'-restore';
            $more_recent_patch_backup_path = clean_path(remove_file_extension(from_html($more_recent_patch->filename))).'-restore';
            if($this->foundConflict($patch_to_check_backup_path, $more_recent_patch_backup_path) &&
            ($more_recent_patch->date_entered >= $patch_to_check->date_entered)){
                return false;
            }
        }

        return true;
    }

    function foundConflict($check_path, $recent_path)
    {
        if(is_file($check_path))
        {
            if(file_exists($recent_path))
                return true;
            else
                return false;
        }
        elseif(is_dir($check_path))
        {
            $status = false;

            $d = dir( $check_path );
            while( $f = $d->read() )
            {
                if( $f == "." || $f == ".." )
                    continue;

                $status = $this->foundConflict("$check_path/$f", "$recent_path/$f");

                if($status)
                    break;
            }

            $d->close();
            return( $status );
        }

        return false;
    }

    /**
     * Given a left version and a right version, determine if the right hand side is greater
     *
     * @param left           the client sugar version
     * @param right          the server version
     *
     * return               true if the right version is greater or they are equal
     *                      false if the left version is greater
     */
    function is_right_version_greater($left, $right, $equals_is_greater = true){
        if(count($left) == 0 && count($right) == 0){
            return $equals_is_greater;
        }
        else if(count($left) == 0 || count($right) == 0){
            return true;
        }
        else if($left[0] == $right[0]){
            array_shift($left);
            array_shift($right);
            return $this->is_right_version_greater($left, $right, $equals_is_greater);
        }
        else if($left[0] < $right[0]){
           return true;
        }
        else
            return false;
    }

    /**
     * Given an array of id_names and versions, check if the dependencies are installed
     *
     * @param dependencies	an array of id_name, version to check if these dependencies are installed
     * 						on the system
     *
     * @return not_found	an array of id_names that were not found to be installed on the system
     */
    function checkDependencies($dependencies = array()){
        $not_found = array();
        foreach($dependencies as $dependent){
            $found = false;
            $query = "SELECT id FROM $this->table_name WHERE id_name = '".$dependent['id_name']."'";
            $matches = $this->getList($query);
            if(0 != sizeof($matches)){
                foreach($matches as $match){
                    if($this->is_right_version_greater(explode('.', $match->version), explode('.', $dependent['version']))){
                        $found = true;
                        break;
                    }//fi
                }//rof
            }//fi
            if(!$found){
                $not_found[] = $dependent['id_name'];
            }//fi
        }//rof
        return $not_found;
    }
    function retrieve($id = -1, $encode=true,$deleted=true) {
        return parent::retrieve($id,$encode,false);  //ignore the deleted filter. the table does not have the deleted column in it.
    }

}
?>