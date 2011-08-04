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

 * Description:  Defines the English language pack for the base application.
 * Portions created by SugarCRM are Copyright (C) SugarCRM, Inc.
 * All Rights Reserved.
 * Contributor(s): ______________________________________..
 ********************************************************************************/

$mod_strings = array (
		  //Labels for methods in the TrackerReporter.php file that are shown in TrackerDashlet
		  'ShowActiveUsers'      => 'Show Active Users',
		  'ShowLastModifiedRecords' => 'Last 10 Modified Records',
		  'ShowTopUser' => 'Top User',
		  'ShowMyModuleUsage' => 'My Module Usage',
		  'ShowMyWeeklyActivities' => 'My Weekly Activity',
		  'ShowTop3ModulesUsed' => 'My Top 3 Modules Used',
		  'ShowLoggedInUserCount' => 'Active User Count',
		  'ShowMyCumulativeLoggedInTime' => 'My Cumulative Login Time (This Week)',
		  'ShowUsersCumulativeLoggedInTime' => 'Users Cumulative Login Time (This Week)',
		  
		  //Column header mapping
		  'action' => 'Action',
		  'active_users' => 'Active User Count',
		  'date_modified' => 'Date of Last Action',
		  'different_modules_accessed' => 'Number Of Modules Accessed',
		  'first_name' => 'First Name',
		  'item_id' => 'ID',
		  'item_summary' => 'Name',
		  'last_action' => 'Last Action Date/Time',
		  'last_name' => 'Last Name',
		  'module_name' => 'Module Name',
		  'records_modified' => 'Total Records Modified',
		  'top_module' => 'Top Module Accessed',
		  'total_count' => 'Total Page Views',
		  'total_login_time' => 'Time (hh:mm:ss)',
		  'user_name' => 'Username',
		  'users' => 'Users',
		  
		  //Administration related labels
		  'LBL_ENABLE' => 'Enabled',
		  'LBL_MODULE_NAME_TITLE' => 'Trackers',
		  'LBL_MODULE_NAME' => 'Trackers',
		  'LBL_TRACKER_SETTINGS' => 'Tracker Settings',
		  'LBL_TRACKER_QUERIES_DESC' => 'Tracker Queries',
		  'LBL_TRACKER_QUERIES_HELP' => 'Track SQL statements when "Log slow queries" is enabled and the query execution time exceeds the "Slow query time threshold" value',
		  'LBL_TRACKER_PERF_DESC' => 'Tracker Performance',
		  'LBL_TRACKER_PERF_HELP' => 'Track database roundtrips, files accessed and memory usage',
		  'LBL_TRACKER_SESSIONS_DESC' => 'Tracker Sessions',
		  'LBL_TRACKER_SESSIONS_HELP' => 'Track active users and users&rsquo; session information',
		  'LBL_TRACKER_DESC' => 'Tracker Actions',
		  'LBL_TRACKER_HELP' => 'Track user&rsquo;s page views (modules and records accessed) and record saves',
		  'LBL_TRACKER_PRUNE_INTERVAL' => 'Number of days of Tracker data to store when Scheduler prunes the tables',
		  'LBL_TRACKER_PRUNE_RANGE' => 'Number of days',
);
?>