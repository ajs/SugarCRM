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


/**
 * Set up an array of Jobs with the appropriate metadata
 * 'jobName' => array (
 * 		'X' => 'name',
 * )
 * 'X' should be an increment of 1
 * 'name' should be the EXACT name of your function
 *
 * Your function should not be passed any parameters
 * Always  return a Boolean. If it does not the Job will not terminate itself
 * after completion, and the webserver will be forced to time-out that Job instance.
 * DO NOT USE sugar_cleanup(); in your function flow or includes.  this will
 * break Schedulers.  That function is called at the foot of cron.php
 */

/**
 * This array provides the Schedulers admin interface with values for its "Job"
 * dropdown menu.
 */
$job_strings = array (
	0 => 'refreshJobs',
	1 => 'pollMonitoredInboxes',
	2 => 'runMassEmailCampaign',
    5 => 'pollMonitoredInboxesForBouncedCampaignEmails',
	3 => 'pruneDatabase',
	4 => 'trimTracker',
	/*4 => 'securityAudit()',*/

);

/**
 * Job 0 refreshes all job schedulers at midnight
 * DEPRECATED
 */
function refreshJobs() {
	return true;
}


/**
 * Job 1
 */
function pollMonitoredInboxes() {

	$GLOBALS['log']->info('----->Scheduler fired job of type pollMonitoredInboxes()');
	global $dictionary;
	global $app_strings;
	
	
	require_once('modules/Emails/EmailUI.php');

	$ie = new InboundEmail();
	$emailUI = new EmailUI();
	$r = $ie->db->query('SELECT id, name FROM inbound_email WHERE is_personal = 0 AND deleted=0 AND status=\'Active\' AND mailbox_type != \'bounce\'');
	$GLOBALS['log']->debug('Just got Result from get all Inbounds of Inbound Emails');

	while($a = $ie->db->fetchByAssoc($r)) {
		$GLOBALS['log']->debug('In while loop of Inbound Emails');
		$ieX = new InboundEmail();
		$ieX->retrieve($a['id']);
		$mailboxes = $ieX->mailboxarray;
		foreach($mailboxes as $mbox) {
			$ieX->mailbox = $mbox;
			$newMsgs = array();
			$msgNoToUIDL = array();
			$connectToMailServer = false;
			if ($ieX->isPop3Protocol()) {
				$msgNoToUIDL = $ieX->getPop3NewMessagesToDownloadForCron();
				// get all the keys which are msgnos;
				$newMsgs = array_keys($msgNoToUIDL);
			}
			if($ieX->connectMailserver() == 'true') {
				$connectToMailServer = true;
			} // if

			$GLOBALS['log']->debug('Trying to connect to mailserver for [ '.$a['name'].' ]');
			if($connectToMailServer) {
				$GLOBALS['log']->debug('Connected to mailserver');
				if (!$ieX->isPop3Protocol()) {
					$newMsgs = $ieX->getNewMessageIds();
				}
				if(is_array($newMsgs)) {
					$current = 1;
					$total = count($newMsgs);
					require_once("include/SugarFolders/SugarFolders.php");
					$sugarFolder = new SugarFolder();
					$groupFolderId = $ieX->groupfolder_id;
					$isGroupFolderExists = false;
					$users = array();
					if ($groupFolderId != null && $groupFolderId != "") {
						$sugarFolder->retrieve($groupFolderId);
						$isGroupFolderExists = true;
					} // if
					$messagesToDelete = array();
					if ($ieX->isMailBoxTypeCreateCase()) {
						$users[] = $sugarFolder->assign_to_id;
						$distributionMethod = $ieX->get_stored_options("distrib_method", "");
						if ($distributionMethod != 'roundRobin') {
							$counts = $emailUI->getAssignedEmailsCountForUsers($users);
						} else {
							$lastRobin = $emailUI->getLastRobin($ieX);
						}
						$GLOBALS['log']->debug('distribution method id [ '.$distributionMethod.' ]');
					}
					foreach($newMsgs as $k => $msgNo) {
						$uid = $msgNo;
						if ($ieX->isPop3Protocol()) {
							$uid = $msgNoToUIDL[$msgNo];
						} else {
							$uid = imap_uid($ieX->conn, $msgNo);
						} // else
						if ($isGroupFolderExists) {
							if ($ieX->importOneEmail($msgNo, $uid)) {
								// add to folder
								$sugarFolder->addBean($ieX->email);
								if ($ieX->isPop3Protocol()) {
									$messagesToDelete[] = $msgNo;
								} else {
									$messagesToDelete[] = $uid;
								}
								if ($ieX->isMailBoxTypeCreateCase()) {
									$userId = "";
									if ($distributionMethod == 'roundRobin') {
										if (sizeof($users) == 1) {
											$userId = $users[0];
											$lastRobin = $users[0];
										} else {
											$userIdsKeys = array_flip($users); // now keys are values
											$thisRobinKey = $userIdsKeys[$lastRobin] + 1;
											if(!empty($users[$thisRobinKey])) {
												$userId = $users[$thisRobinKey];
												$lastRobin = $users[$thisRobinKey];
											} else {
												$userId = $users[0];
												$lastRobin = $users[0];
											}
										} // else
									} else {
										if (sizeof($users) == 1) {
											foreach($users as $k => $value) {
												$userId = $value;
											} // foreach
										} else {
											asort($counts); // lowest to highest
											$countsKeys = array_flip($counts); // keys now the 'count of items'
											$leastBusy = array_shift($countsKeys); // user id of lowest item count
											$userId = $leastBusy;
											$counts[$leastBusy] = $counts[$leastBusy] + 1;
										}
									} // else
									$GLOBALS['log']->debug('userId [ '.$userId.' ]');
									$ieX->handleCreateCase($ieX->email, $userId);
								} // if
							} // if
						} else {
								if($ieX->isAutoImport()) {
									$ieX->importOneEmail($msgNo, $uid);
								} else {
									/*If the group folder doesn't exist then download only those messages
									 which has caseid in message*/
									$ieX->getMessagesInEmailCache($msgNo, $uid);
									$email = new Email();
									$header = imap_headerinfo($ieX->conn, $msgNo);
									$email->name = $ieX->handleMimeHeaderDecode($header->subject);
									$email->from_addr = $ieX->convertImapToSugarEmailAddress($header->from);
									$email->reply_to_email  = $ieX->convertImapToSugarEmailAddress($header->reply_to);
									if(!empty($email->reply_to_email)) {
										$contactAddr = $email->reply_to_email;
									} else {
										$contactAddr = $email->from_addr;
									}
									$mailBoxType = $ieX->mailbox_type;
										$ieX->handleAutoresponse($email, $contactAddr);
								} // else
						} // else
						$GLOBALS['log']->debug('***** On message [ '.$current.' of '.$total.' ] *****');
						$current++;
					} // foreach
					// update Inbound Account with last robin
					if ($ieX->isMailBoxTypeCreateCase() && $distributionMethod == 'roundRobin') {
						$emailUI->setLastRobin($ieX, $lastRobin);
					} // if

				} // if
				if ($isGroupFolderExists)	 {
					$leaveMessagesOnMailServer = $ieX->get_stored_options("leaveMessagesOnMailServer", 0);
					if (!$leaveMessagesOnMailServer) {
						if ($ieX->isPop3Protocol()) {
							$ieX->deleteMessageOnMailServerForPop3(implode(",", $messagesToDelete));
						} else {
							$ieX->deleteMessageOnMailServer(implode($app_strings['LBL_EMAIL_DELIMITER'], $messagesToDelete));
						}
					}
				}
			} else {
				$GLOBALS['log']->fatal("SCHEDULERS: could not get an IMAP connection resource for ID [ {$a['id']} ]. Skipping mailbox [ {$a['name']} ].");
				// cn: bug 9171 - continue while
			} // else
		} // foreach
		imap_expunge($ieX->conn);
		imap_close($ieX->conn, CL_EXPUNGE);
	} // while

	return true;
}

/**
 * Job 2
 */
function runMassEmailCampaign() {
	if (!class_exists('LoggerManager')){
		
	}
	$GLOBALS['log'] = LoggerManager::getLogger('emailmandelivery');
	$GLOBALS['log']->debug('Called:runMassEmailCampaign');

	if (!class_exists('DBManagerFactory')){
		require('include/database/DBManagerFactory.php');
	}
	
	global $beanList;
	global $beanFiles;
	require("config.php");
	require('include/modules.php');
	if(!class_exists('AclController')) {
		require('modules/ACL/ACLController.php');
	}

	require('modules/EmailMan/EmailManDelivery.php');
	return true;
}

/**
 *  Job 3
 */
function pruneDatabase() {
	$GLOBALS['log']->info('----->Scheduler fired job of type pruneDatabase()');
	$backupDir	= $GLOBALS['sugar_config']['cache_dir'].'backups';
	$backupFile	= 'backup-pruneDatabase-GMT0_'.gmdate('Y_m_d-H_i_s', strtotime('now')).'.php';

	$db = DBManagerFactory::getInstance();
	$tables = $db->getTablesArray();

//_ppd($tables);
	if(!empty($tables)) {
		foreach($tables as $kTable => $table) {
			// find tables with deleted=1
			$qDel = 'SELECT * FROM '.$table.' WHERE deleted = 1';
			$rDel = $db->query($qDel);// OR continue; // continue if no 'deleted' column

			// make a backup INSERT query if we are deleting.
			while($aDel = $db->fetchByAssoc($rDel)) {
				// build column names
				$rCols = $db->query('SHOW COLUMNS FROM '.$table);
				$colName = array();

				while($aCols = $db->fetchByAssoc($rCols)) {
					$colName[] = $aCols['Field'];
				}

				$query = 'INSERT INTO '.$table.' (';
				$values = '';
				foreach($colName as $kC => $column) {
					$query .= $column.', ';
					$values .= '"'.$aDel[$column].'", ';
				}

				$query  = substr($query, 0, (strlen($query) - 2));
				$values = substr($values, 0, (strlen($values) - 2));
				$query .= ') VALUES ('.str_replace("'", "&#039;", $values).');';

				$queryString[] = $query;

				if(empty($colName)) {
					$GLOBALS['log']->fatal('pruneDatabase() could not get the columns for table ('.$table.')');
				}
			} // end aDel while()
			// now do the actual delete
			$db->query('DELETE FROM '.$table.' WHERE deleted = 1');
		} // foreach() tables

		// now output file with SQL
		if(!function_exists('mkdir_recursive')) {
			
		}
		if(!function_exists('write_array_to_file')) {
			
		}
		if(!file_exists($backupDir) || !file_exists($backupDir.'/'.$backupFile)) {
			// create directory if not existent
			mkdir_recursive($backupDir, false);
		}
		// write cache file

		write_array_to_file('pruneDatabase', $queryString, $backupDir.'/'.$backupFile);
		return true;
	}
	return false;
}


///**
// * Job 4
// */

//function securityAudit() {
//	// do something
//	return true;
//}

function trimTracker()
{
    global $sugar_config, $timedate;
	$GLOBALS['log']->info('----->Scheduler fired job of type trimTracker()');
	$db = DBManagerFactory::getInstance();
	
	$admin = new Administration();
	$admin->retrieveSettings('tracker');
	require('modules/Trackers/config.php');
	$trackerConfig = $tracker_config;

    require_once('include/utils/db_utils.php');
    $prune_interval = !empty($admin->settings['tracker_prune_interval']) ? $admin->settings['tracker_prune_interval'] : 30;
	foreach($trackerConfig as $tableName=>$tableConfig) {

		//Skip if table does not exist
		if(!$db->tableExists($tableName)) {
		   continue;
		}

	    $timeStamp = db_convert("'". $timedate->asDb($timedate->getNow()->get("+"+$prune_interval+" days")) ."'" ,"datetime");
		if($tableName == 'tracker_sessions') {
		   $query = "DELETE FROM $tableName WHERE date_end < $timeStamp";
		} else {
		   $query = "DELETE FROM $tableName WHERE date_modified < $timeStamp";
		}

	    $GLOBALS['log']->info("----->Scheduler is about to trim the $tableName table by running the query $query");
		$db->query($query);
	} //foreach
    return true;
}

/* Job 5
 *
 */
function pollMonitoredInboxesForBouncedCampaignEmails() {
	$GLOBALS['log']->info('----->Scheduler job of type pollMonitoredInboxesForBouncedCampaignEmails()');
	global $dictionary;
	

	$ie = new InboundEmail();
	$r = $ie->db->query('SELECT id FROM inbound_email WHERE deleted=0 AND status=\'Active\' AND mailbox_type=\'bounce\'');

	while($a = $ie->db->fetchByAssoc($r)) {
		$ieX = new InboundEmail();
		$ieX->retrieve($a['id']);
		$ieX->connectMailserver();
        $GLOBALS['log']->info("Bounced campaign scheduler connected to mail server id: {$a['id']} ");
		$newMsgs = array();
		if ($ieX->isPop3Protocol()) {
			$newMsgs = $ieX->getPop3NewMessagesToDownload();
		} else {
			$newMsgs = $ieX->getNewMessageIds();
		}

		//$newMsgs = $ieX->getNewMessageIds();
		if(is_array($newMsgs)) {
			foreach($newMsgs as $k => $msgNo) {
				$uid = $msgNo;
				if ($ieX->isPop3Protocol()) {
					$uid = $ieX->getUIDLForMessage($msgNo);
				} else {
					$uid = imap_uid($ieX->conn, $msgNo);
				} // else
                 $GLOBALS['log']->info("Bounced campaign scheduler will import message no: $msgNo");
				$ieX->importOneEmail($msgNo, $uid, false,false);
			}
		}
		imap_expunge($ieX->conn);
		imap_close($ieX->conn);
	}

	return true;
}





if (file_exists('custom/modules/Schedulers/_AddJobsHere.php')) {
	require('custom/modules/Schedulers/_AddJobsHere.php');
}
?>
