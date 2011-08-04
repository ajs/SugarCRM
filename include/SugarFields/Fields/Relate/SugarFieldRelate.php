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

require_once('include/SugarFields/Fields/Base/SugarFieldBase.php');

class SugarFieldRelate extends SugarFieldBase {
    
    function getDetailViewSmarty($parentFieldArray, $vardef, $displayParams, $tabindex) {
        $nolink = array('Users', 'Teams');
        if(in_array($vardef['module'], $nolink)){
            $this->ss->assign('nolink', true);
        }else{
            $this->ss->assign('nolink', false);
        }
        $this->setup($parentFieldArray, $vardef, $displayParams, $tabindex);
        return $this->fetch($this->findTemplate('DetailView'));
    }
    
    function getEditViewSmarty($parentFieldArray, $vardef, $displayParams, $tabindex) {
        if(!empty($vardef['function']['returns']) && $vardef['function']['returns'] == 'html'){
            return parent::getEditViewSmarty($parentFieldArray, $vardef, $displayParams, $tabindex);
        }
        
        $call_back_function = 'set_return';
        if(isset($displayParams['call_back_function'])) {
            $call_back_function = $displayParams['call_back_function'];
        }
        $form_name = 'EditView';
        if(isset($displayParams['formName'])) {
            $form_name = $displayParams['formName'];
        }
        
        //Special Case for accounts; use the displayParams array and retrieve
        //the key and copy indexes.  'key' is the suffix of the field we are searching
        //the Account's address with.  'copy' is the suffix we are copying the addresses
        //form fields into.
        if(isset($vardef['module']) && preg_match('/Accounts/si',$vardef['module']) 
           && isset($displayParams['key']) && isset($displayParams['copy'])) {
            
            if(isset($displayParams['key']) && is_array($displayParams['key'])) {
              $database_key = $displayParams['key'];    
            } else {
              $database_key[] = $displayParams['key'];
            }
            
            if(isset($displayParams['copy']) && is_array($displayParams['copy'])) {
                $form = $displayParams['copy'];
            } else {
                $form[] = $displayParams['copy'];
            }
            
            if(count($database_key) != count($form)) {
              global $app_list_strings;
              $this->ss->trigger_error($app_list_strings['ERR_SMARTY_UNEQUAL_RELATED_FIELD_PARAMETERS']);
            } //if
            
            $copy_phone = isset($displayParams['copyPhone']) ? $displayParams['copyPhone'] : true;
            
            $field_to_name = array();
            $field_to_name['id'] = $vardef['id_name'];
            $field_to_name['name'] = $vardef['name'];
            $address_fields = array('_address_street', '_address_city', '_address_state', '_address_postalcode', '_address_country');
            $count = 0;
            foreach($form as $f) {
                foreach($address_fields as $afield) {
                    $field_to_name[$database_key[$count] . $afield] = $f . $afield;
                }
                $count++;
            }

            $popup_request_data = array(
                'call_back_function' => $call_back_function,
                'form_name' => $form_name,
                'field_to_name_array' => $field_to_name,
            );
                
            if($copy_phone) {
              $popup_request_data['field_to_name_array']['phone_office'] = 'phone_work';
            }               
        } elseif(isset($displayParams['field_to_name_array'])) {
            $popup_request_data = array(
                'call_back_function' => $call_back_function,
                'form_name' => $form_name,
                'field_to_name_array' => $displayParams['field_to_name_array'],
            );  
        } else {
            $popup_request_data = array(
                'call_back_function' => $call_back_function,
                'form_name' => $form_name,
                'field_to_name_array' => array(
                          'id' => (empty($displayParams['idName']) ? $vardef['id_name'] : ($displayParams['idName'] . '_' . $vardef['id_name'])) ,
                          ((empty($vardef['rname'])) ? 'name' : $vardef['rname']) => (empty($displayParams['idName']) ? $vardef['name'] : $displayParams['idName']),
                    ),
                );
        }
        $json = getJSONobj();
        $displayParams['popupData'] = '{literal}'.$json->encode($popup_request_data). '{/literal}';
        if(!isset($displayParams['readOnly'])) {
           $displayParams['readOnly'] = '';
        } else {
           $displayParams['readOnly'] = $displayParams['readOnly'] == false ? '' : 'READONLY';  
        }
        $this->setup($parentFieldArray, $vardef, $displayParams, $tabindex);
        return $this->fetch($this->findTemplate('EditView')); 
    }
    
    function getPopupViewSmarty($parentFieldArray, $vardef, $displayParams, $tabindex){
    	$displayParams['clearOnly'] = true;
    	return $this->getSearchViewSmarty($parentFieldArray, $vardef, $displayParams, $tabindex);
    }
    
    function getSearchViewSmarty($parentFieldArray, $vardef, $displayParams, $tabindex) {
        $call_back_function = 'set_return';
        if(isset($displayParams['call_back_function'])) {
            $call_back_function = $displayParams['call_back_function'];
        }
        $form_name = 'search_form';
        if(isset($displayParams['formName'])) {
            $form_name = $displayParams['formName'];
        }
     	if(!empty($vardef['rname']) && $vardef['rname'] == 'user_name'){
        	$displayParams['useIdSearch'] = true;
        }
        
        //Special Case for accounts; use the displayParams array and retrieve
        //the key and copy indexes.  'key' is the suffix of the field we are searching
        //the Account's address with.  'copy' is the suffix we are copying the addresses
        //form fields into.
        if(isset($vardef['module']) && preg_match('/Accounts/si',$vardef['module']) 
           && isset($displayParams['key']) && isset($displayParams['copy'])) {
            
            if(isset($displayParams['key']) && is_array($displayParams['key'])) {
              $database_key = $displayParams['key'];    
            } else {
              $database_key[] = $displayParams['key'];
            }
            
            if(isset($displayParams['copy']) && is_array($displayParams['copy'])) {
                $form = $displayParams['copy'];
            } else {
                $form[] = $displayParams['copy'];
            }
            
            if(count($database_key) != count($form)) {
              global $app_list_strings;
              $this->ss->trigger_error($app_list_strings['ERR_SMARTY_UNEQUAL_RELATED_FIELD_PARAMETERS']);
            } //if
            
            $copy_phone = isset($displayParams['copyPhone']) ? $displayParams['copyPhone'] : true;
            
            $field_to_name = array();
            $field_to_name['id'] = $vardef['id_name'];
            $field_to_name['name'] = $vardef['name'];
            $address_fields = array('_address_street', '_address_city', '_address_state', '_address_postalcode', '_address_country');
            $count = 0;
            foreach($form as $f) {
                foreach($address_fields as $afield) {
                    $field_to_name[$database_key[$count] . $afield] = $f . $afield;
                }
                $count++;
            }

            $popup_request_data = array(
                'call_back_function' => $call_back_function,
                'form_name' => $form_name,
                'field_to_name_array' => $field_to_name,
            );
                
            if($copy_phone) {
              $popup_request_data['field_to_name_array']['phone_office'] = 'phone_work';
            }               
        } elseif(isset($displayParams['field_to_name_array'])) {
            $popup_request_data = array(
                'call_back_function' => $call_back_function,
                'form_name' => $form_name,
                'field_to_name_array' => $displayParams['field_to_name_array'],
            );  
        } else {
            $popup_request_data = array(
                'call_back_function' => $call_back_function,
                'form_name' => $form_name,
                'field_to_name_array' => array(
                          'id' => $vardef['id_name'],
                          ((empty($vardef['rname'])) ? 'name' : $vardef['rname']) => $vardef['name'],
                    ),
                );
        }
        $json = getJSONobj();
        $displayParams['popupData'] = '{literal}'.$json->encode($popup_request_data). '{/literal}';
        if(!isset($displayParams['readOnly'])) {
           $displayParams['readOnly'] = '';
        } else {
           $displayParams['readOnly'] = $displayParams['readOnly'] == false ? '' : 'READONLY';  
        }
        $this->setup($parentFieldArray, $vardef, $displayParams, $tabindex);
        return $this->fetch($this->findTemplate('SearchView')); 
    }    
    
    function formatField($rawField, $vardef) {
    	if ('contact_name' == $vardef['name']){
    	    $default_locale_name_format = $GLOBALS['current_user']->getPreference('default_locale_name_format');
    	    $default_locale_name_format = trim(preg_replace('/s/i', '', $default_locale_name_format));
            $new_field = '';
    	    $names = array();
            $temp = explode(' ', $rawField);
            if ( !isset($temp[1]) ) {
                $names['f'] = '';
                $names['l'] = $temp[0];
            }
            elseif ( !empty($temp) ) {
                $names['f'] = $temp[0];
                $names['l'] = $temp[1];
            }
            for($i=0;$i<strlen($default_locale_name_format);$i++){
        	    $new_field .= array_key_exists($default_locale_name_format{$i}, $names) ? $names[$default_locale_name_format{$i}] : $default_locale_name_format{$i};
            }
    	}
    	else  $new_field = $rawField;
    	
        return $new_field;
    }
    
    /**
     * @see SugarFieldBase::importSanitize()
     */
    public function importSanitize(
        $value,
        $vardef,
        $focus,
        ImportFieldSanitize $settings
        )
    {
        if ( !isset($vardef['module']) )
            return false;
        $newbean = loadBean($vardef['module']);
        
        // Bug 38885 - If we are relating to the Users table on user_name, there's a good chance
        // that the related field data is the full_name, rather than the user_name. So to be sure
        // let's try to lookup the field the relationship is expecting to use (user_name).
        if ( $vardef['module'] == 'Users' && $vardef['rname'] == 'user_name' ) {
            $userFocus = new User;
            $userFocus->retrieve_by_string_fields(
                array($userFocus->db->concat('users',array('first_name','last_name')) => $value ));
            if ( !empty($userFocus->id) ) {
                $value = $userFocus->user_name;
            }
        }       
        
        // Bug 32869 - Assumed related field name is 'name' if it is not specified
        if ( !isset($vardef['rname']) )
            $vardef['rname'] = 'name';
        
        // Bug 27046 - Validate field against type as it is in the related field
        $rvardef = $newbean->getFieldDefinition($vardef['rname']);
        if ( isset($rvardef['type']) 
                && method_exists($this,$rvardef['type']) ) {
            $fieldtype = $rvardef['type'];
            $returnValue = $settings->$fieldtype($value,$rvardef);
            if ( !$returnValue )
                return false;
            else
                $value = $returnValue;
        }
        
        if ( isset($vardef['id_name']) ) {
            $idField = $vardef['id_name'];
            
            // Bug 24075 - clear out id field value if it is invalid
            if ( isset($focus->$idField) ) {
                $checkfocus = loadBean($vardef['module']);
                if ( $checkfocus && is_null($checkfocus->retrieve($focus->$idField)) )
                    $focus->$idField = '';
            }
            
            // be sure that the id isn't already set for this row
            if ( empty($focus->$idField)
                    && $idField != $vardef['name']
                    && !empty($vardef['rname']) 
                    && !empty($vardef['table'])) {
                // Bug 27562 - Check db_concat_fields first to see if the field name is a concat
                $relatedFieldDef = $newbean->getFieldDefinition($vardef['rname']);
                if ( isset($relatedFieldDef['db_concat_fields']) 
                        && is_array($relatedFieldDef['db_concat_fields']) )
                    $fieldname = $focus->db->concat($vardef['table'],$relatedFieldDef['db_concat_fields']);
                else
                    $fieldname = $vardef['rname'];
                // lookup first record that matches in linked table
                $query = "SELECT id 
                            FROM {$vardef['table']} 
                            WHERE {$fieldname} = '" . $focus->db->quote($value) . "'
                                AND deleted != 1";
                
                $result = $focus->db->limitQuery($query,0,1,true, "Want only a single row");
                if(!empty($result)){
                    if ( $relaterow = $focus->db->fetchByAssoc($result) )
                        $focus->$idField = $relaterow['id'];
                    elseif ( !$settings->addRelatedBean 
                            || ( $newbean->bean_implements('ACL') && !$newbean->ACLAccess('save') )
                            || ( in_array($newbean->module_dir,array('Teams','Users')) )
                            )
                        return false;
                    else {
                        // add this as a new record in that bean, then relate
                        if ( isset($relatedFieldDef['db_concat_fields']) 
                                && is_array($relatedFieldDef['db_concat_fields']) ) {
                            $relatedFieldParts = explode(' ',$value);
                            foreach ($relatedFieldDef['db_concat_fields'] as $relatedField)
                                $newbean->$relatedField = array_shift($relatedFieldParts);
                        }
                        else
                            $newbean->$vardef['rname'] = $value;
                        if ( !isset($focus->assigned_user_id) || $focus->assigned_user_id == '' )
                            $newbean->assigned_user_id = $GLOBALS['current_user']->id;
                        else
                            $newbean->assigned_user_id = $focus->assigned_user_id;
                        if ( !isset($focus->modified_user_id) || $focus->modified_user_id == '' )
                            $newbean->modified_user_id = $GLOBALS['current_user']->id;
                        else
                            $newbean->modified_user_id = $focus->modified_user_id;
                        
                        // populate fields from the parent bean to the child bean
                        $focus->populateRelatedBean($newbean);
                        
                        $newbean->save(false);
                        $focus->$idField = $newbean->id;
                        $settings->createdBeans[] = ImportFile::writeRowToLastImport(
                                $focus->module_dir,$newbean->object_name,$newbean->id);
                    }
                }
            }
        }
        
        return $value;
    }
}
