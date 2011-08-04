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

 * Description:
 * Portions created by SugarCRM are Copyright (C) SugarCRM, Inc. All Rights
 * Reserved. Contributor(s): ______________________________________..
 * *******************************************************************************/

// LEGACY for old versions - emulating upload.php
// aw: make this better for later versions.
//refreshing mod_strings
global $mod_strings;

$curr_lang = 'en_us';
if(isset($GLOBALS['current_language']) && ($GLOBALS['current_language'] != null)){
    $curr_lang = $GLOBALS['current_language'];
}
$mod_strings = return_module_language($curr_lang, 'UpgradeWizard',true);

function check_php($sys_php_version = '')
{
    $min_considered_php_version = '5.2.1';

    $supported_php_versions = array (
    '5.2.1', '5.2.2', '5.2.3', '5.2.4', '5.2.5', '5.2.6', '5.2.8', '5.3.0'
    );
    //Find out what Database the system is using.
    global $sugar_config;
    $dbType = '';
    if (isset ($sugar_config['dbconfig']) && isset ($sugar_config['dbconfig']['db_type'])) {
        $dbType = $sugar_config['dbconfig']['db_type'];
    }

    // invalid versions above the $min_considered_php_version,
    // should be mutually exclusive with $supported_php_versions

    // SugarCRM prohibits install on PHP 5.2.x on all platforms
    $invalid_php_versions = array('5.2.7');

    // default unsupported
    $retval = 0;

    // versions below $min_considered_php_version are invalid
    if(1 == version_compare($sys_php_version, $min_considered_php_version, '<')) {
        $retval = -1;
    }

    // supported version check overrides default unsupported
    foreach($supported_php_versions as $ver) {
        if(1 == version_compare($sys_php_version, $ver, 'eq') || strpos($sys_php_version,$ver) !== false) {
            $retval = 1;
            break;
        }
    }

    // invalid version check overrides default unsupported
    foreach($invalid_php_versions as $ver) {
        if(1 == version_compare($sys_php_version, $ver, 'eq') || strpos($sys_php_version,$ver) !== false) {
            $retval = -1;
            break;
        }
    }

    //allow a redhat distro to install, regardless of version.  We are assuming the redhat naming convention is followed
    //and the php version contains 'rh' characters
    if(strpos($sys_php_version, 'rh') !== false) {
        $retval = 1;
    }

    return $retval;
}



$curr_lang = 'en_us';
if(isset($GLOBALS['current_language']) && ($GLOBALS['current_language'] != null)){
	$curr_lang = $GLOBALS['current_language'];
}
return_module_language($curr_lang, 'UpgradeWizard');

logThis('at preflight.php');
//set the upgrade progress status.
set_upgrade_progress('preflight','in_progress');
$php_warnings = '';
if (version_compare(phpversion(),'5.2.0') >=0) {
	$stop = true; // flag to show "next"
	if(isset($_SESSION['files'])) {
		unset($_SESSION['files']);
	}

	$errors = preflightCheck();

	$php_version = constant('PHP_VERSION');
    if(check_php($php_version) == -1)
    {
        $phpVersion = "<b><span class=stop>{$mod_strings['ERR_CHECKSYS_PHP_INVALID_VER']} {$php_version} </span></b>";
        $error_txt = '<span class="error">'.$phpVersion.'</span>';
        if(count($errors) == 0)
        $errors[] = '';
        $errors[] = $error_txt;
        logThis($error_txt);
    }
	$diffs = '';
	$schema = '';
	$anyScriptChanges = '';
	$db =& DBManagerFactory::getInstance();

	//Quickcreatedefs on the basis of editviewdefs
    if(substr($sugar_version,0,1) >= 5){
    	updateQuickCreateDefs();
	}
	upgradeSugarCache($_SESSION['install_file']);

	if((count($errors) == 1)) { // only diffs
		logThis('file preflight check passed successfully.');
		$stop = false;
		$out  = $mod_strings['LBL_UW_PREFLIGHT_TESTS_PASSED']."<BR><BR><font color='red'>".$mod_strings['LBL_UW_PREFLIGHT_TESTS_PASSED3']."</font>";
		$stop = false;

		$disableEmail = (empty($current_user->email1)) ? 'DISABLED' : 'CHECKED';

		if(count($errors['manual']) > 0) {
			$preserveFiles = array();

		$diffs =<<<eoq
			<script type="text/javascript" language="Javascript">
				function preflightToggleAll(cb) {
					var checkAll = false;
					var form = document.getElementById('diffs');

					if(cb.checked == true) {
						checkAll = true;
					}

					for(i=0; i<form.elements.length; i++) {
						if(form.elements[i].type == 'checkbox') {
							form.elements[i].checked = checkAll;
						}
					}
					return;
				}
			</script>

			<table cellpadding='0' cellspacing='0' border='0'>
				<tr>
					<td valign='top'>
						<input type='checkbox' name='addTask' id='addTask' CHECKED>
					</td>
					<td valign='top'>
						{$mod_strings['LBL_UW_PREFLIGHT_ADD_TASK']}
					</td>
				</tr>
				<tr>
					<td valign='top'>
						<input type='checkbox' name='addEmail' id='addEmail' $disableEmail>
					</td>
					<td valign='top'>
						{$mod_strings['LBL_UW_PREFLIGHT_EMAIL_REMINDER']}
					</td>
				</tr>
			</table>

			<form name='diffs' id='diffs'>
			<p><a href='javascript:void(0); toggleNwFiles("diffsHide");'>{$mod_strings['LBL_UW_SHOW_DIFFS']}</a></p>
			<div id='diffsHide' style='display:none'>
				<table cellpadding='0' cellspacing='0' border='0'>
					<tr>
						<td valign='top' colspan='2'>
							{$mod_strings['LBL_UW_PREFLIGHT_FILES_DESC']}
							<br />&nbsp;
						</td>
					</tr>
					<tr>
						<td valign='top' colspan='2'>
							<input type='checkbox' onchange='preflightToggleAll(this);'>&nbsp;<i><b>{$mod_strings['LBL_UW_PREFLIGHT_TOGGLE_ALL']}</b></i>
							<br />&nbsp;
						</td>
					</tr>
eoq;
		foreach($errors['manual'] as $diff) {
			$diff = clean_path($diff);
			$_SESSION['files']['manual'][] = $diff;

			$checked = (isAutoOverwriteFile($diff)) ? 'CHECKED' : '';

			if(empty($checked)) {
				$preserveFiles[] = $diff;
			}

			$diffs .= "<tr><td valign='top'>";
			$diffs .= "<input type='checkbox' name='diff_files[]' value='{$diff}' $checked>";
			$diffs .= "</td><td valign='top'>";
			$diffs .= str_replace(getcwd(), '.', $diff);
			$diffs .= "</td></tr>";
		}
		$diffs .= "</table>";
		$diffs .= "</div></p>";
		$diffs .= "</form>";

		// list preserved files (templates, etc.)
		$preserve = '';
		foreach($preserveFiles as $pf) {
			if(empty($preserve)) {
				$preserve .= "<table cellpadding='0' cellspacing='0' border='0'><tr><td><b>";
				$preserve .= $mod_strings['LBL_UW_PREFLIGHT_PRESERVE_FILES'];
				$preserve .= "</b></td></tr>";
			}
			$preserve .= "<tr><td valign='top'><i>".str_replace(getcwd(), '.', $pf)."</i></td></tr>";
		}
		if(!empty($preserve)) {
			$preserve .= '</table><br>';
		}
		$diffs = $preserve.$diffs;
	} else { // NO FILE DIFFS REQUIRED
		$diffs = $mod_strings['LBL_UW_PREFLIGHT_NO_DIFFS'];
	}
} else {
	logThis('*** ERROR: found too many preflight errors - displaying errors and stopping execution.');
	$out = "<b>{$mod_strings['ERR_UW_PREFLIGHT_ERRORS']}:</b><hr />";
	$out .= "<span class='error'>";

	foreach($errors as $error) {
		if(is_array($error)) { // manual diff files
			continue;
		} else {
			$out .= "{$error}<br />";
		}
	}
	$out .= "</span><br />";
}

$diffs ='';

///////////////////////////////////////////////////////////////////////////////
////	SCHEMA SCRIPT HANDLING
	logThis('starting schema preflight check...');
	//Check the current and target versions and store them in session variables
	if(!isset($sugar_db_version) || empty($sugar_db_version)) {
		include('./sugar_version.php');
	}
	if(!isset($manifest['version']) || empty($manifest['version'])) {
		include($_SESSION['unzip_dir'].'/manifest.php');
	}
	$current_version = substr(preg_replace("#[^0-9]#", "", $sugar_db_version),0,3);
	$targetVersion =  substr(preg_replace("#[^0-9]#", "", $manifest['version']),0,3);

	//save the versions as session variables
	$_SESSION['current_db_version'] = $current_version;
	$_SESSION['target_db_version']  = $targetVersion;
	$_SESSION['upgrade_from_flavor']  = $manifest['name'];
	// aw: BUG 10161: check flavor conversion sql files
	$sqlFile = ''; // cn: bug
	if($current_version == $targetVersion) {

		if(preg_match('/(.*?)([^0])$/', $current_version, $matches))
		{
			$current_version = $matches[1].'0';
		}

		switch($manifest['name']){
			case 'SugarCE to SugarPro':
				$sqlFile = $current_version.'_ce_to_pro_'.$db->dbType;
				break;
			case 'SugarCE to SugarEnt':
				$sqlFile = $current_version.'_ce_to_ent_'.$db->dbType;
				break;
            case 'SugarCE to SugarCorp':
				$sqlFile = $current_version.'_ce_to_corp_'.$db->dbType;
				break;
            case 'SugarCE to SugarUlt':
				$sqlFile = $current_version.'_ce_to_ult_'.$db->dbType;
				break;
			case 'SugarPro to SugarEnt':
				$sqlFile = $current_version.'_pro_to_ent_'.$db->dbType;
				break;
			default:
				break;
		}
	} else {
		$sqlFile = $current_version.'_to_'.$targetVersion.'_'.$db->dbType;
	}

	$newTables = array();

	if($db->dbType == 'oci8') {
	} else {
		$sqlScript = $_SESSION['unzip_dir'].'/scripts/'.$sqlFile.'.sql';
	}

	logThis('looking for schema script at: '.$sqlScript);
	if(is_file($sqlScript)) {
		logThis('found schema upgrade script: '.$sqlScript);

		logThis('schema preflight using MySQL');
		if(function_exists('sugar_fopen')){
			$fp = sugar_fopen($sqlScript, 'r');
		}
		else{
			$fp = fopen($sqlScript, 'r');
		}
		$contents = stream_get_contents($fp);
	    $anyScriptChanges =$contents;

		// remove __uw_temp tables
		//testCleanUp($db->dbType);
		fclose($fp);

		$customTables = getCustomTables($db->dbType);
		if ( !empty($customTables) ) {
			$_SESSION['alterCustomTableQueries'] = alterCustomTables($db->dbType, $customTables);
		} else {
			$_SESSION['alterCustomTableQueries'] = false;
		}

		$schema  = "<p><a href='javascript:void(0); toggleNwFiles(\"schemashow\");'>{$mod_strings['LBL_UW_SHOW_SCHEMA']}</a>";
		$schema .= "<div id='schemashow' style='display:none;'>";
		$schema .= "<textarea readonly cols='80' rows='10'>{$contents}</textarea>";
		$schema .= "</div></p>";

		if(!empty($sqlErrors)) {
			$stop = true;
			$out = "<b class='error'>{$mod_strings['ERR_UW_PREFLIGHT_ERRORS']}:</b> ";
			$out .= "<a href='javascript:void(0);toggleNwFiles(\"sqlErrors\");'>{$mod_strings['LBL_UW_SHOW_SQL_ERRORS']}</a><div id='sqlErrors' style='display:none'>";
			foreach($sqlErrors as $sqlError) {
				$out .= "<br><span class='error'>{$sqlError}</span>";
			}
			$out .= "</div><hr />";
		}
} else {
	$customTableSchema = '';
	logThis('no schema script found - all schema preflight skipped');
}
	logThis('schema preflight done.');
////	END SCHEMA SCRIPT HANDLING
///////////////////////////////////////////////////////////////////////////////
//php 521 suggestion
	$php_521 = '';
	if(version_compare(phpversion(),'5.2.1') < 0){
		$php_521=$mod_strings['LBL_CURRENT_PHP_VERSION'].phpversion().$mod_strings['LBL_RECOMMENDED_PHP_VERSION'];
	}
	if(empty($mod_strings['LBL_UPGRADE_TAKES_TIME_HAVE_PATIENCE'])){
		$mod_strings['LBL_UPGRADE_TAKES_TIME_HAVE_PATIENCE'] = 'Upgrade may take some time';
	}

$style_for_out = empty($out)?'style=\'display:none\'':'';
$style_for_dif = empty($diffs)?'style=\'display:none\'':'';
$style_for_schemaChange = empty($schema)?'style=\'display:none\'':'';

$final =<<<eoq
<table cellpadding="3" cellspacing="0" border="0">
    <tr {$style_for_out}>
        <td colspan="2" align="left" valign="top">
            <br>{$out}
        </td>
    </tr>
    <tr {$style_for_dif}>
        <td align="left" valign="top">
            <b>{$mod_strings['LBL_UW_MANUAL_MERGE']}</b>
        </td>
        <td align="left" valign="top">
            {$diffs}
        </td>
    </tr>
    <tr>
        <td colspan="3">&nbsp;</td>
    </tr>
    <tr {$style_for_schemaChange}>
        <td align="left" valign="top">
            <b>{$mod_strings['LBL_UW_SCHEMA_CHANGE']}</b>
        </td>
        <td align="left" valign="top">
            {$schema}
        </td>
    </tr>
    <tr {$style_for_schemaChange}>
        <td>
        </td>
        <td valign="top">
            <div>
            <b>{$mod_strings['LBL_UW_DB_METHOD']}</b><br />
            <select name="schema_change" id="select_schema_change" onchange="checkSqlStatus(false);">
                <option value="sugar">{$mod_strings['LBL_UW_DB_CHOICE1']}</option>
                <option value="manual">{$mod_strings['LBL_UW_DB_CHOICE2']}</option>
            </select>
            </div>
            <div id='show_sql_run' style='display:none'>
                <input type='checkbox' name='sql_run' id='sql_run' onmousedown='checkSqlStatus(true);'> {$mod_strings['LBL_UW_SQL_RUN']}
            </div>
        </td>
    </tr>
</table>

eoq;

$form5 =<<<eoq5
<br>
<div id="upgradeDiv" style="display:none">
    <table cellspacing="0" cellpadding="0" border="0">
        <tr><td>
           <p><img src='modules/UpgradeWizard/processing.gif'></p>
        </td></tr>
     </table>
 </div>

eoq5;

	$uwMain = $final.$form5;

	//set the upgrade progress status.
	set_upgrade_progress('preflight','done');
}
else{
	$stop = true;
	if(empty($mod_strings['LBL_INCOMPATIBLE_PHP_VERSION'])){
		$mod_strings['LBL_INCOMPATIBLE_PHP_VERSION'] = 'Php version 5.2.1 or above is required.';
	}

$php_verison_warning =<<<eoq
	<table cellpadding="3" cellspacing="0" border="0">
		<tr>
			<th colspan="2" align="left">
				<span class='error'><b>{$mod_strings['LBL_INCOMPATIBLE_PHP_VERSION']}</b></span>
			</th>
		</tr>
	</table>
eoq;
	$php_warnings = $php_verison_warning;
}
//Add the backward compatibility check as well.

//Php Backward compatibility checks
if(ini_get("zend.ze1_compatibility_mode")) {
	$stop = true;
	if(empty($mod_strings['LBL_BACKWARD_COMPATIBILITY_ON'])){
		$mod_strings['LBL_BACKWARD_COMPATIBILITY_ON'] = 'Php Backward Compatibility mode is turned on. Set zend.ze1_compatibility_mode to Off for proceeding further';
	}

$php_compatibility_warning =<<<eoq
	<table cellpadding="3" cellspacing="0" border="0">
		<tr>
			<th colspan="2" align="left">
				<span class='error'><b>{$mod_strings['LBL_BACKWARD_COMPATIBILITY_ON']}</b></span>
			</th>
		</tr>
	</table>
eoq;
$php_warnings .= $php_compatibility_warning;
}
if($php_warnings != null){
	$uwMain = $php_warnings;
}

$GLOBALS['top_message'] = "<b>{$mod_strings['LBL_UW_PREFLIGHT_TESTS_PASSED2']}</b>";
$showBack		= false;
$showCancel		= true;
$showRecheck	= true;
$showNext		= ($stop) ? false : true;

$stepBack		= $_REQUEST['step'] - 1;
$stepNext		= $_REQUEST['step'] + 1;
$stepCancel		= -1;
$stepRecheck	= $_REQUEST['step'];

$_SESSION['step'][$steps['files'][$_REQUEST['step']]] = ($stop) ? 'failed' : 'success';
?>