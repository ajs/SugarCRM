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

 * Portions created by SugarCRM are Copyright (C) SugarCRM, Inc.
 * All Rights Reserved.
 * Contributor(s): ______________________________________..
 ********************************************************************************/

require_once('modules/Users/Forms.php');
require_once('modules/Configurator/Configurator.php');

/**
 * ViewWireless_Login extends SugarWirelessView and is the login view.
 */
class ViewWizard extends SugarView
{
	/**
	 * Constructor for the view, it runs the constructor of SugarWirelessView and
	 * sets the footer option to true (it is off in the SugarWirelessView constructor)
	 */
	public function __construct()
	{
		parent::SugarView();

        $this->options['show_header'] = false;
        $this->options['show_footer'] = false;
        $this->options['show_javascript'] = false;
	}

	/**
	 * @see SugarView::display()
	 */
	public function display()
    {
        global $mod_strings, $current_user, $locale, $sugar_config, $app_list_strings, $sugar_version;

		$themeObject = SugarThemeRegistry::current();
		$css = $themeObject->getCSS();
		$this->ss->assign('SUGAR_CSS', $css);
        $favicon = $themeObject->getImageURL('sugar_icon.ico',false);
        $this->ss->assign('FAVICON_URL',getJSPath($favicon));
        $this->ss->assign('CSS', '<link rel="stylesheet" type="text/css" href="'.SugarThemeRegistry::current()->getCSSURL('wizard.css').'" />');
	    $this->ss->assign('JAVASCRIPT',user_get_validate_record_js().user_get_chooser_js().user_get_confsettings_js());
		$this->ss->assign('PRINT_URL', 'index.php?'.$GLOBALS['request_string']);
		$this->ss->assign('SKIP_WELCOME',isset($_REQUEST['skipwelcome']) && $_REQUEST['skipwelcome'] == 1);
		$this->ss->assign('ID', $current_user->id);
		$this->ss->assign('USER_NAME', $current_user->user_name);
		$this->ss->assign('FIRST_NAME', $current_user->first_name);
		$this->ss->assign('SUGAR_VERSION', $sugar_version);
		$this->ss->assign('LAST_NAME', $current_user->last_name);
		$this->ss->assign('TITLE', $current_user->title);
		$this->ss->assign('DEPARTMENT', $current_user->department);
		$this->ss->assign('REPORTS_TO_ID', $current_user->reports_to_id);
		$this->ss->assign('REPORTS_TO_NAME', $current_user->reports_to_name);
		$this->ss->assign('PHONE_HOME', $current_user->phone_home);
		$this->ss->assign('PHONE_MOBILE', $current_user->phone_mobile);
		$this->ss->assign('PHONE_WORK', $current_user->phone_work);
		$this->ss->assign('PHONE_OTHER', $current_user->phone_other);
		$this->ss->assign('PHONE_FAX', $current_user->phone_fax);
		$this->ss->assign('EMAIL1', $current_user->email1);
		$this->ss->assign('EMAIL2', $current_user->email2);
		$this->ss->assign('ADDRESS_STREET', $current_user->address_street);
		$this->ss->assign('ADDRESS_CITY', $current_user->address_city);
		$this->ss->assign('ADDRESS_STATE', $current_user->address_state);
		$this->ss->assign('ADDRESS_POSTALCODE', $current_user->address_postalcode);
		$this->ss->assign('ADDRESS_COUNTRY', $current_user->address_country);
		$configurator = new Configurator();
		if ( $configurator->config['passwordsetting']['SystemGeneratedPasswordON']
		        || $configurator->config['passwordsetting']['forgotpasswordON'] )
		    $this->ss->assign('REQUIRED_EMAIL_ADDRESS','1');
        else
            $this->ss->assign('REQUIRED_EMAIL_ADDRESS','0');

		// get javascript
        ob_start();
        $this->options['show_javascript'] = true;
        $this->renderJavascript();
        $this->options['show_javascript'] = false;
        $this->ss->assign("SUGAR_JS",ob_get_contents().$themeObject->getJS());
        ob_end_clean();

		$messenger_type = '<select tabindex="5" name="messenger_type">';
        $messenger_type .= get_select_options_with_id($app_list_strings['messenger_type_dom'], $current_user->messenger_type);
        $messenger_type .= '</select>';
        $this->ss->assign('MESSENGER_TYPE_OPTIONS', $messenger_type);
        $this->ss->assign('MESSENGER_ID', $current_user->messenger_id);

        // set default settings
        $use_real_names = $current_user->getPreference('use_real_names');
        if ( empty($use_real_names) )
            $current_user->setPreference('use_real_names', 'on');
        $current_user->setPreference('reminder_time', 1800);
        $current_user->setPreference('mailmerge_on', 'on');

		//// Timezone
        if(empty($current_user->id)) { // remove default timezone for new users(set later)
            $current_user->user_preferences['timezone'] = '';
        }

        $userTZ = $current_user->getPreference('timezone');
        if(empty($userTZ) && !$current_user->is_group && !$current_user->portal_only) {
            $userTZ = TimeDate::guessTimezone();
            $current_user->setPreference('timezone', $userTZ);
        }

        if(!$current_user->getPreference('ut')) {
			$this->ss->assign('PROMPTTZ', ' checked');
        }

        $this->ss->assign('TIMEZONE_CURRENT', $userTZ);
        $this->ss->assign('TIMEZONEOPTIONS', TimeDate::getTimezoneList());

        //// Numbers and Currency display
        require_once('modules/Currencies/ListCurrency.php');
        $currency = new ListCurrency();

        // 10/13/2006 Collin - Changed to use Localization.getConfigPreference
        // This was the problem- Previously, the "-99" currency id always assumed
        // to be defaulted to US Dollars.  However, if someone set their install to use
        // Euro or other type of currency then this setting would not apply as the
        // default because it was being overridden by US Dollars.
        $cur_id = $locale->getPrecedentPreference('currency', $current_user);
        if($cur_id) {
            $selectCurrency = $currency->getSelectOptions($cur_id);
			$this->ss->assign("CURRENCY", $selectCurrency);
        } else {
            $selectCurrency = $currency->getSelectOptions();
			$this->ss->assign("CURRENCY", $selectCurrency);
        }

        $currenciesVars = "";
        $i=0;
        foreach($locale->currencies as $id => $arrVal) {
            $currenciesVars .= "currencies[{$i}] = '{$arrVal['symbol']}';\n";
            $i++;
        }
        $currencySymbolsJs = <<<eoq
var currencies = new Object;
{$currenciesVars}
function setSymbolValue(id) {
	document.getElementById('symbol').value = currencies[id];
}
eoq;
		$this->ss->assign('currencySymbolJs', $currencySymbolsJs);


        // fill significant digits dropdown
        $significantDigits = $locale->getPrecedentPreference('default_currency_significant_digits', $current_user);
        $sigDigits = '';
        for($i=0; $i<=6; $i++) {
            if($significantDigits == $i) {
               $sigDigits .= "<option value=\"$i\" selected=\"true\">$i</option>";
            } else {
               $sigDigits .= "<option value=\"$i\">{$i}</option>";
            }
        }

		$this->ss->assign('sigDigits', $sigDigits);

		$num_grp_sep = $current_user->getPreference('num_grp_sep');
		$dec_sep = $current_user->getPreference('dec_sep');
		$this->ss->assign("NUM_GRP_SEP",(empty($num_grp_sep) ? $sugar_config['default_number_grouping_seperator'] : $num_grp_sep));
		$this->ss->assign("DEC_SEP",(empty($dec_sep) ? $sugar_config['default_decimal_seperator'] : $dec_sep));
		$this->ss->assign('getNumberJs', $locale->getNumberJs());

		//// Name display format
		$this->ss->assign('default_locale_name_format', $locale->getLocaleFormatMacro($current_user));
		$this->ss->assign('getNameJs', $locale->getNameJs());

		$this->ss->assign('TIMEOPTIONS', get_select_options_with_id($sugar_config['time_formats'], $current_user->_userPreferenceFocus->getDefaultPreference('default_time_format')));
		$this->ss->assign('DATEOPTIONS', get_select_options_with_id($sugar_config['date_formats'], $current_user->_userPreferenceFocus->getDefaultPreference('default_date_format')));
		$this->ss->assign("MAIL_SENDTYPE", get_select_options_with_id($app_list_strings['notifymail_sendtype'], $current_user->getPreference('mail_sendtype')));
		$this->ss->assign("NEW_EMAIL", $current_user->emailAddress->getEmailAddressWidgetEditView($current_user->id, $current_user->module_dir));
		$this->ss->assign('EMAIL_LINK_TYPE', get_select_options_with_id($app_list_strings['dom_email_link_type'], $current_user->getPreference('email_link_type')));

		// email smtp
		$systemOutboundEmail = new OutboundEmail();
        $systemOutboundEmail = $systemOutboundEmail->getSystemMailerSettings();
        $mail_smtpserver = $systemOutboundEmail->mail_smtpserver;
        $mail_smtptype = $systemOutboundEmail->mail_smtptype;
        $mail_smtpport = $systemOutboundEmail->mail_smtpport;
        $mail_smtpssl = $systemOutboundEmail->mail_smtpssl;
        $mail_smtpdisplay = $systemOutboundEmail->mail_smtpdisplay;
        $mail_smtpuser = "";
        $mail_smtppass = "";
        $hide_if_can_use_default = true;
        $mail_smtpauth_req=true;
        if( !$systemOutboundEmail->isAllowUserAccessToSystemDefaultOutbound() )
        {	
        
        	$mail_smtpauth_req = $systemOutboundEmail->mail_smtpauth_req;
            $userOverrideOE = $systemOutboundEmail->getUsersMailerForSystemOverride($current_user->id);
            if($userOverrideOE != null) {
                $mail_smtpuser = $userOverrideOE->mail_smtpuser;
                $mail_smtppass = $userOverrideOE->mail_smtppass;
            }
            if(!$mail_smtpauth_req && 
                (empty($systemOutboundEmail->mail_smtpserver) || empty($systemOutboundEmail->mail_smtpuser)
                 || empty($systemOutboundEmail->mail_smtppass)))
           {
                $hide_if_can_use_default = true;
            }
            else{
                $hide_if_can_use_default = false;
            }
        }
       
        $this->ss->assign("mail_smtpdisplay", $mail_smtpdisplay);
        $this->ss->assign("mail_smtpuser", $mail_smtpuser);
        $this->ss->assign("mail_smtppass", $mail_smtppass);
        $this->ss->assign('mail_smtpserver',$mail_smtpserver);
        $this->ss->assign("mail_smtpauth_req", $mail_smtpauth_req);
        $this->ss->assign('MAIL_SMTPPORT',$mail_smtpport);
        $this->ss->assign('MAIL_SMTPSSL',$mail_smtpssl);

        $this->ss->assign('HIDE_IF_CAN_USE_DEFAULT_OUTBOUND',$hide_if_can_use_default);
       
		$this->ss->display('modules/Users/tpls/wizard.tpl');
	}
}
