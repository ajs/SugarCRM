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

 * Description: This file is used to override the default Meta-data EditView behavior
 * to provide customization specific to the Calls module.
 * Portions created by SugarCRM are Copyright (C) SugarCRM, Inc.
 * All Rights Reserved.
 * Contributor(s): ______________________________________..
 ********************************************************************************/

require_once('include/MVC/View/views/view.edit.php');

class DocumentsViewEdit extends ViewEdit
{
 	/**
	 * @see SugarView::display()
	 */
	public function display()
 	{
		global $app_list_strings, $mod_strings;
		/*
		$this->bean->category_name = $app_list_strings['document_category_dom'][$this->bean->category_id];
	    $this->bean->subcategory_name = $app_list_strings['document_subcategory_dom'][$this->bean->subcategory_id];
	    if(isset($this->bean->status_id)) {
	    $this->bean->status = $app_list_strings['document_status_dom'][$this->bean->status_id];
	    }
        $this->bean->related_doc_name = Document::get_document_name($this->bean->related_doc_id);
        $this->bean->related_doc_rev_number = DocumentRevision::get_document_revision_name($this->bean->related_doc_rev_id);
        $this->bean->save_file = basename($this->bean->file_url_noimage);
        */
		$load_signed=false;
		if ((isset($_REQUEST['load_signed_id']) && !empty($_REQUEST['load_signed_id']))) {

			$load_signed=true;
			if (isset($_REQUEST['record'])) {
				$this->bean->related_doc_id=$_REQUEST['record'];
			}
			if (isset($_REQUEST['selected_revision_id'])) {
				$this->bean->related_doc_rev_id=$_REQUEST['selected_revision_id'];
			}

			$this->bean->id=null;
			$this->bean->document_name=null;
			$this->bean->filename=null;
			$this->bean->is_template=0;
		} //if

		if (!empty($this->bean->id)) {
			$this->ss->assign("FILE_OR_HIDDEN", "hidden");
			if (!$this->ev->isDuplicate) {
				$this->ss->assign("DISABLED", "disabled");
			}
		} else {	    
			$this->bean->revision = 1;
		    $this->ss->assign("FILE_OR_HIDDEN", "file");
		}

		$popup_request_data = array(
			'call_back_function' => 'document_set_return',
			'form_name' => 'EditView',
			'field_to_name_array' => array(
				'id' => 'related_doc_id',
				'document_name' => 'related_document_name',
				),
			);
		$json = getJSONobj();
		$this->ss->assign('encoded_document_popup_request_data', $json->encode($popup_request_data));


		//get related document name.
		if (!empty($this->bean->related_doc_id)) {
			$this->ss->assign("RELATED_DOCUMENT_NAME",Document::get_document_name($this->bean->related_doc_id));
			$this->ss->assign("RELATED_DOCUMENT_ID",$this->bean->related_doc_id);
			if (!empty($this->bean->related_doc_rev_id)) {
				$this->ss->assign("RELATED_DOCUMENT_REVISION_OPTIONS", get_select_options_with_id(DocumentRevision::get_document_revisions($this->bean->related_doc_id), $this->bean->related_doc_rev_id));
			} else {
				$this->ss->assign("RELATED_DOCUMENT_REVISION_OPTIONS", get_select_options_with_id(DocumentRevision::get_document_revisions($this->bean->related_doc_id), ''));
			}
		} else {
			$this->ss->assign("RELATED_DOCUMENT_REVISION_DISABLED", "disabled");
		}


		//set parent information in the form.
		if (isset($_REQUEST['parent_id'])) {
			$this->ss->assign("PARENT_ID",$_REQUEST['parent_id']);
		} //if

		if (isset($_REQUEST['parent_name'])) {
			$this->ss->assign("PARENT_NAME", $_REQUEST['parent_name']);

			if (!empty($_REQUEST['parent_type'])) {
				switch (strtolower($_REQUEST['parent_type'])) {

					case "contracts" :
						$this->ss->assign("LBL_PARENT_NAME",$mod_strings['LBL_CONTRACT_NAME']);
						break;

					//todo remove leads case.
					case "leads" :
						$this->ss->assign("LBL_PARENT_NAME",$mod_strings['LBL_CONTRACT_NAME']);
						break;
				} //switch
			} //if
		} //if

		if (isset($_REQUEST['parent_type'])) {
			$this->ss->assign("PARENT_TYPE",$_REQUEST['parent_type']);
		}

		if ($load_signed) {
			$this->ss->assign("RELATED_DOCUMENT_REVISION_DISABLED", "disabled");
			$this->ss->assign("RELATED_DOCUMENT_BUTTON_AVAILABILITY", "hidden");
			$this->ss->assign("LOAD_SIGNED_ID",$_REQUEST['load_signed_id']);
		} else {
			$this->ss->assign("RELATED_DOCUMENT_BUTTON_AVAILABILITY", "button");
		} //if-else

 		parent::display();
 	}

	/**
	 * @see SugarView::_getModuleTitleParams()
	 */
	protected function _getModuleTitleParams($browserTitle = false)
	{
    	$params = array();
    	$params[] = $this->_getModuleTitleListParam($browserTitle);
    	if(!empty($this->bean->id)){
			$params[] = "<a href='index.php?module={$this->module}&action=DetailView&record={$this->bean->id}'>".$this->bean->document_name."</a>";
			$params[] = $GLOBALS['app_strings']['LBL_EDIT_BUTTON_LABEL'];
		}else{
			$params[] = $GLOBALS['app_strings']['LBL_CREATE_BUTTON_LABEL'];
		}

		return $params;
    }
}
