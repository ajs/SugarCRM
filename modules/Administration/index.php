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

 * Description: TODO:  To be written.
 * Portions created by SugarCRM are Copyright (C) SugarCRM, Inc.
 * All Rights Reserved.
 * Contributor(s): ______________________________________..
 ********************************************************************************/

global $currentModule;
global $current_language;
global $current_user;
global $sugar_flavor;


if (!is_admin($current_user) && !is_admin_for_any_module($current_user))
{
   sugar_die("Unauthorized access to administration.");
}

echo getClassicModuleTitle(translate('LBL_MODULE_NAME','Administration'),
                      array(translate('LBL_MODULE_NAME','Administration')), false);

//get the module links..
require('modules/Administration/metadata/adminpaneldefs.php');
global $admin_group_header;  ///variable defined in the file above.


$tab = array();
$header_image = array();
$url = array();
$label_tab = array();
$description = array();
$group = array();
$sugar_smarty = new Sugar_Smarty();
$values_3_tab = array();
$admin_group_header_tab = array();
$j=0;

foreach ($admin_group_header as $key=>$values) {
    $module_index = array_keys($values[3]);
    $addedHeaderGroups = array();
    foreach ($module_index as $mod_key=>$mod_val) {
        if(
        (!isset($addedHeaderGroups[$values[0]]))) {
            $admin_group_header_tab[]=$values;
            $group_header_value=get_form_header(translate($values[0],'Administration'),$values[1],$values[2]);
        	$group[$j][0] = '<table cellpadding="0" cellspacing="0" width="100%" class="h3Row"><tr ><td width="20%" valign="bottom"><h3>' . translate($values[0]) . '</h3></td></tr>';
        	$addedHeaderGroups[$values[0]] = 1;
        	if (isset($values[4]))
    	       $group[$j][1] = '<tr><td style="padding-top: 3px; padding-bottom: 5px;">' . translate($values[4]) . '</td></tr></table>';
    	    else
    	       $group[$j][2] = '</tr></table>';
            $colnum=0;
            $i=0;
            $fix = array_keys($values[3]);
            if(count($values[3])>1){

                //////////////////
                $tmp_array = $values[3];
                $return_array = array();
                foreach ($tmp_array as $mod => $value){
                    $keys = array_keys($value);
                    foreach ($keys as $key){
                        $return_array[$key] = $value[$key];
                    }
                }
                $values_3_tab[]= $return_array;
                $mod = $return_array;
            }
           else {
                $mod = $values[3][$fix[0]];
    	        $values_3_tab[]= $mod;
           }

            foreach ($mod as $link_idx =>$admin_option) {
                if(!empty($GLOBALS['admin_access_control_links']) && in_array($link_idx, $GLOBALS['admin_access_control_links'])){
                    continue;
                }
                $colnum+=1;
                $header_image[$j][$i]= SugarThemeRegistry::current()->getImage($admin_option[0],'alt="' .  translate($admin_option[1],'Administration') .'" border="0" align="absmiddle"');
                $url[$j][$i] = $admin_option[3];
                $label = translate($admin_option[1],'Administration');
                if(!empty($admin_option['additional_label']))$label.= ' '. $admin_option['additional_label'];
                if(!empty($admin_option[4])){
                	$label = ' <font color="red">'. $label . '</font>';
                }

                $label_tab[$j][$i]= $label;
                $description[$j][$i]= translate($admin_option[2],'Administration');
                if (($colnum % 2) == 0) {
                    $tab[$j][$i]= ($colnum % 2);
                }
                else {
                $tab[$j][$i]= 10;
                }
                $i+=1;
            }

        	//if the loop above ends with an odd entry add a blank column.
        	if (($colnum % 2) != 0) {
        	    $tab[$j][$i]= 10;
        	}
        $j+=1;
    }
  }
}

$sugar_smarty->assign('MY_FRAME',"<iframe class='teamNoticeBox' src='http://www.sugarcrm.com/crm/product/gopro/admin' width='100%' height='315px'></iframe>");
$sugar_smarty->assign("VALUES_3_TAB", $values_3_tab);
$sugar_smarty->assign("ADMIN_GROUP_HEADER", $admin_group_header_tab);
$sugar_smarty->assign("GROUP_HEADER", $group);
$sugar_smarty->assign("ITEM_HEADER_IMAGE", $header_image);
$sugar_smarty->assign("ITEM_URL", $url);
$sugar_smarty->assign("ITEM_HEADER_LABEL",$label_tab);
$sugar_smarty->assign("ITEM_DESCRIPTION", $description);
$sugar_smarty->assign("COLNUM", $tab);

echo $sugar_smarty->fetch('modules/Administration/index.tpl');
?>
