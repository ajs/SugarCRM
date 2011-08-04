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

 * Description:  TODO: To be written.
 * Portions created by SugarCRM are Copyright (C) SugarCRM, Inc.
 * All Rights Reserved.
 * Contributor(s): ______________________________________..
 ********************************************************************************/

require_once('include/MVC/View/SugarView.php');
require_once('modules/EmailMan/Forms.php');

class ViewConfig extends SugarView
{
    /**
	 * @see SugarView::_getModuleTitleParams()
	 */
	protected function _getModuleTitleParams($browserTitle = false)
	{
	    global $mod_strings;

    	return array(
    	   "<a href='index.php?module=Administration&action=index'>".translate('LBL_MODULE_NAME','Administration')."</a>",
    	   translate('LBL_MASS_EMAIL_CONFIG_TITLE','Administration'),
    	   );
    }

    /**
	 * @see SugarView::preDisplay()
	 */
	public function preDisplay()
 	{
 	    global $current_user;

 	    if ( !is_admin($current_user)
 	            && !is_admin_for_module($GLOBALS['current_user'],'Emails')
 	            && !is_admin_for_module($GLOBALS['current_user'],'Campaigns') )
 	        sugar_die("Unauthorized access to administration.");
    }

    /**
	 * @see SugarView::display()
	 */
	public function display()
	{
        global $mod_strings;
        global $app_list_strings;
        global $app_strings;
        global $current_user;
        global $sugar_config;

        echo $this->getModuleTitle();
        global $currentModule;

        $focus = new Administration();
        $focus->retrieveSettings(); //retrieve all admin settings.
        $GLOBALS['log']->info("Mass Emailer(EmailMan) ConfigureSettings view");

        $this->ss->assign("MOD", $mod_strings);
        $this->ss->assign("APP", $app_strings);

        $this->ss->assign("RETURN_MODULE", "Administration");
        $this->ss->assign("RETURN_ACTION", "index");

        $this->ss->assign("MODULE", $currentModule);
        $this->ss->assign("PRINT_URL", "index.php?".$GLOBALS['request_string']);
        $this->ss->assign("HEADER", get_module_title("EmailMan", "{MOD.LBL_CONFIGURE_SETTINGS}", true));
        $this->ss->assign("notify_fromaddress", $focus->settings['notify_fromaddress']);
        $this->ss->assign("notify_send_from_assigning_user", (isset($focus->settings['notify_send_from_assigning_user']) && !empty($focus->settings['notify_send_from_assigning_user'])) ? "checked='checked'" : "");
        $this->ss->assign("notify_on", ($focus->settings['notify_on']) ? "checked='checked'" : "");
        $this->ss->assign("notify_fromname", $focus->settings['notify_fromname']);
        $this->ss->assign("notify_allow_default_outbound_on", (!empty($focus->settings['notify_allow_default_outbound']) && $focus->settings['notify_allow_default_outbound']) ? "checked='checked'" : "");

        $this->ss->assign("mail_smtptype", $focus->settings['mail_smtptype']);
        $this->ss->assign("mail_smtpserver", $focus->settings['mail_smtpserver']);
        $this->ss->assign("mail_smtpport", $focus->settings['mail_smtpport']);
        $this->ss->assign("mail_smtpuser", $focus->settings['mail_smtpuser']);
        $this->ss->assign("mail_smtpauth_req", ($focus->settings['mail_smtpauth_req']) ? "checked='checked'" : "");
        $this->ss->assign("mail_haspass", empty($focus->settings['mail_smtppass'])?0:1);
        $this->ss->assign("MAIL_SSL_OPTIONS", get_select_options_with_id($app_list_strings['email_settings_for_ssl'], $focus->settings['mail_smtpssl']));

        //Assign the current users email for the test send dialogue.
        $this->ss->assign("CURRENT_USER_EMAIL", $current_user->email1);

        $showSendMail = FALSE;
        $outboundSendTypeCSSClass = "yui-hidden";
        if(isset($sugar_config['allow_sendmail_outbound']) && $sugar_config['allow_sendmail_outbound'])
        {
            $showSendMail = TRUE;
            $app_list_strings['notifymail_sendtype']['sendmail'] = 'sendmail';
            $outboundSendTypeCSSClass = "";
        }

        $this->ss->assign("OUTBOUND_TYPE_CLASS", $outboundSendTypeCSSClass);
        $this->ss->assign("mail_sendtype_options", get_select_options_with_id($app_list_strings['notifymail_sendtype'], $focus->settings['mail_sendtype']));

        ///////////////////////////////////////////////////////////////////////////////
        ////	USER EMAIL DEFAULTS
        // editors
        $editors = $app_list_strings['dom_email_editor_option'];
        $newEditors = array();
        foreach($editors as $k => $v) {
            if($k != "") { $newEditors[$k] = $v; }
        }

        // preserve attachments
        $preserveAttachments = '';
        if(isset($sugar_config['email_default_delete_attachments']) && $sugar_config['email_default_delete_attachments'] == true) {
            $preserveAttachments = 'CHECKED';
        }
        $this->ss->assign('DEFAULT_EMAIL_DELETE_ATTACHMENTS', $preserveAttachments);
        ////	END USER EMAIL DEFAULTS
        ///////////////////////////////////////////////////////////////////////////////


        //setting to manage.
        //emails_per_run
        //tracking_entities_location_type default or custom
        //tracking_entities_location http://www.sugarcrm.com/track/

        //////////////////////////////////////////////////////////////////////////////
        ////	EMAIL SECURITY
        if(!isset($sugar_config['email_xss']) || empty($sugar_config['email_xss'])) {
            $sugar_config['email_xss'] = getDefaultXssTags();
        }

        foreach(unserialize(base64_decode($sugar_config['email_xss'])) as $k => $v) {
            $this->ss->assign($k."Checked", 'CHECKED');
        }

        //clean_xss('here');
        ////	END EMAIL SECURITY
        ///////////////////////////////////////////////////////////////////////////////

        require_once('modules/Emails/Email.php');
        $email = new Email();
        $this->ss->assign('ROLLOVER', $email->rolloverStyle);

        $this->ss->assign("JAVASCRIPT",get_validate_record_js());
        $this->ss->display('modules/EmailMan/tpls/config.tpl');
    }
}
