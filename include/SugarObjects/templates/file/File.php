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

 
require_once('include/SugarObjects/templates/basic/Basic.php');
require_once('include/upload_file.php');
require_once('include/formbase.php');

class File extends Basic
{
	public $file_url;
	public $file_url_noimage;

    function File(){
		parent::Basic();
	}

	/**
	 * @see SugarBean::save()
	 */
	public function save($check_notify=false)
	{
		if (!empty($this->uploadfile)) {
			$this->filename = $this->uploadfile;
		}
		
		return parent::save($check_notify);
 	}

 	/**
	 * @see SugarBean::fill_in_additional_detail_fields()
	 */
	public function fill_in_additional_detail_fields()
 	{
		global $app_list_strings;
		global $img_name;
		global $img_name_bare;
		
		$this->uploadfile = $this->filename;
		
		// Bug 41453 - Make sure we call the parent method as well
		parent::fill_in_additional_detail_fields();
		
		if (!$this->file_ext) {
			$img_name = SugarThemeRegistry::current()->getImageURL(strtolower($this->file_ext)."_image_inline.gif");
			$img_name_bare = strtolower($this->file_ext)."_image_inline";
		}
		
		//set default file name.
		if (!empty ($img_name) && file_exists($img_name)) {
			$img_name = $img_name_bare;
		} 
		else {
			$img_name = "def_image_inline"; //todo change the default image.
		}
		$this->file_url_noimage = basename(UploadFile::get_url($this->filename, $this->id));
		
		if(!empty($this->status_id)) {
	       $this->status = $app_list_strings['document_status_dom'][$this->status_id];
	    }
	}
	
	/**
	 * @see SugarBean::retrieve()
	 */
	public function retrieve($id = -1, $encode=true) 
	{
		$ret_val = parent::retrieve($id, $encode);
	
		$this->name = $this->document_name;
		
		return $ret_val;
	}
}
