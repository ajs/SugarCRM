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




$listViewDefs['ProjectTask'] = array(
    'NAME' => array(
        'width' => '40',  
        'label' => 'LBL_LIST_NAME', 
        'link' => true,
        'default' => true,
        'sortable' => true),       
    'PROJECT_NAME' => array(
        'width' => '25',  
        'label' => 'LBL_PROJECT_NAME', 
        'id'=>'PROJECT_ID',
        'link' => true,
        'default' => true,
        'sortable' => true,
        'module'  => 'Project',
        'ACLTag' => 'PROJECT',
        'related_fields' => array('project_id')),            
    'DATE_START' => array(
        'width' => '10',  
        'label' => 'LBL_DATE_START', 
        'default' => true,
        'sortable' => true),     
    'DATE_FINISH' => array(
        'width' => '10',  
        'label' => 'LBL_DATE_FINISH', 
        'default' => true,
        'sortable' => true),                   
    'PRIORITY' => array(
        'width' => '10',  
        'label' => 'LBL_LIST_PRIORITY', 
        'default' => true,
        'sortable' => true),  
    'PERCENT_COMPLETE' => array(
        'width' => '10',  
        'label' => 'LBL_LIST_PERCENT_COMPLETE', 
        'default' => true,
        'sortable' => true),                      
	'ASSIGNED_USER_NAME' => array(
		'width' => '10', 
		'label' => 'LBL_LIST_ASSIGNED_USER_ID',
		'module' => 'Employees',
        'id' => 'ASSIGNED_USER_ID',
        'default' => true),
);

?>
