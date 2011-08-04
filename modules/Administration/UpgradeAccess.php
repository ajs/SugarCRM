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




global $mod_strings;
global $sugar_config;

$ignoreCase = (substr_count(strtolower($_SERVER['SERVER_SOFTWARE']), 'apache/2') > 0)?'(?i)':'';
$htaccess_file   = getcwd() . "/.htaccess";
$contents = '';
$restrict_str = <<<EOQ

# BEGIN SUGARCRM RESTRICTIONS
RedirectMatch 403 {$ignoreCase}.*\.log$
RedirectMatch 403 {$ignoreCase}/+not_imported_.*\.txt
RedirectMatch 403 {$ignoreCase}/+(soap|cache|xtemplate|data|examples|include|log4php|metadata|modules)/+.*\.(php|tpl)
RedirectMatch 403 {$ignoreCase}/+emailmandelivery\.php
RedirectMatch 403 {$ignoreCase}/+cache/+upload
RedirectMatch 403 {$ignoreCase}/+cache/+diagnostic
RedirectMatch 403 {$ignoreCase}/+files\.md5$
# END SUGARCRM RESTRICTIONS
EOQ;

if(file_exists($htaccess_file)){
    $fp = fopen($htaccess_file, 'r');
    $skip = false;
    while($line = fgets($fp)){

    	if(preg_match("/\s*#\s*BEGIN\s*SUGARCRM\s*RESTRICTIONS/i", $line))$skip = true;
        if(!$skip)$contents .= $line;
        if(preg_match("/\s*#\s*END\s*SUGARCRM\s*RESTRICTIONS/i", $line))$skip = false;
    }
}
$status =  file_put_contents($htaccess_file, $contents . $restrict_str);
if( !$status ){
    echo '<p>' . $mod_strings['LBL_HT_NO_WRITE'] . '<span class=stop>$htaccess_file</span></p>\n';
    echo '<p>' . $mod_strings['LBL_HT_NO_WRITE_2'] . '</p>\n';
    echo "$redirect_str";
}


// cn: bug 9365 - security for filesystem
$uploadDir='';
$uploadHta='';

if (empty($GLOBALS['sugar_config']['upload_dir'])) {
    $GLOBALS['sugar_config']['upload_dir']='cache/upload/';
}
$uploadDir = getcwd()."/".$sugar_config['upload_dir'];
if(file_exists($uploadDir)){
	$uploadHta = $uploadDir.".htaccess";
}
else{
	mkdir_recursive($uploadDir);
	if(is_dir($uploadDir)){
		$uploadHta = $uploadDir.".htaccess";
	}
}

$denyAll =<<<eoq
<Directory>
	Order Deny,Allow
	Deny from all
</Directory>
eoq;

if(file_exists($uploadHta) && filesize($uploadHta)) {
	// file exists, parse to make sure it is current
	if(is_writable($uploadHta) && ($fpUploadHta = @sugar_fopen($uploadHta, "r+"))) {
		$oldHtaccess = file_get_contents($uploadHta);
		// use a different regex boundary b/c .htaccess uses the typicals
		if(!preg_match("=".$denyAll."=", $oldHtaccess)) {
			$oldHtaccess .= $denyAll;
		}

		rewind($fpUploadHta);
		fwrite($fpUploadHta, $oldHtaccess);
		ftruncate($fpUploadHta, ftell($fpUploadHta));
		fclose($fpUploadHta);
	} else {
		$htaccess_failed = true;
	}
} else {
	// no .htaccess yet, create a fill
	if($fpUploadHta = @sugar_fopen($uploadHta, "w")) {
		fputs($fpUploadHta, $denyAll);
		fclose($fpUploadHta);
	} else {
		$htaccess_failed = true;
	}
}




include('modules/Versions/ExpectedVersions.php');


global $expect_versions;

if (isset($expect_versions['htaccess'])) {
        $version = new Version();
        $version->retrieve_by_string_fields(array('name'=>'htaccess'));

        $version->name = $expect_versions['htaccess']['name'];
        $version->file_version = $expect_versions['htaccess']['file_version'];
        $version->db_version = $expect_versions['htaccess']['db_version'];
        $version->save();
}

/* Commenting out as this shows on upgrade screen
 * echo "\n" . $mod_strings['LBL_HT_DONE']. "<br />\n";
*/

?>