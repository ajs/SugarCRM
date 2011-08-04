{*
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

 ********************************************************************************/
*}

	<!-- Begin Campaign Diagnostic Link -->	
	{$CAMPAIGN_DIAGNOSTIC_LINK}
	<!-- End Campaign Diagnostic Link -->
	
	<table width="100%" border="0" cellspacing="0" cellpadding="0">
		<tr>
		<td  colspan="3"><h3>{$MOD.LBL_WIZ_NEWSLETTER_TITLE_STEP1} </h3></div></td>
		<td colspan="1">&nbsp;</td>
		</tr>
		<tr><td class="datalabel" colspan="3">{$MOD.LBL_WIZARD_HEADER_MESSAGE}<br></td><td>&nbsp;</td></tr>
		<tr><td class="datalabel" colspan="4">&nbsp;</td></tr>
		<tr>
		<td width="17%" scope="row"><span sugar='slot1'>{$MOD.LBL_NAME} <span class="required">{$APP.LBL_REQUIRED_SYMBOL}</span></span sugar='slot'></td>
		<td width="33%" ><span sugar='slot1b'><input id='name' name='wiz_step1_name' title='{$MOD.LBL_NAME}' {$DISABLED} tabindex='1' size='50' maxlength='50' type="text" value="{$CAMP_NAME}" ></span sugar='slot'></td>
		<td width="15%" scope="row"><span sugar='slot2'>{$APP.LBL_ASSIGNED_TO}</span sugar='slot'></td>
		<td width="35%" ><span sugar='slot2b'><input class="sqsEnabled" tabindex="2" autocomplete="off" id="assigned_user_name" name="wiz_step1_assigned_user_name"  title='{$APP.LBL_ASSIGNED_TO}' type="text" value="{$ASSIGNED_USER_NAME}"><input id='assigned_user_id' name='wiz_step1_assigned_user_id' type="hidden" value="{$ASSIGNED_USER_ID}" />
		<input title="{$APP.LBL_SELECT_BUTTON_TITLE}" accessKey="{$APP.LBL_SELECT_BUTTON_KEY}" type="button" tabindex='2' class="button" value='{$APP.LBL_SELECT_BUTTON_LABEL}' name=btn1
				onclick='open_popup("Users", 600, 400, "", true, false, {$encoded_users_popup_request_data});' /></span sugar='slot'>
		</td>
		</tr>
		<tr>
		<td width="15%" scope="row"><span sugar='slot3'>{$MOD.LBL_CAMPAIGN_STATUS} <span class="required">{$APP.LBL_REQUIRED_SYMBOL}</span></span sugar='slot'></td>
		<td width="35%" ><span sugar='slot3b'><select tabindex='1' id='status' name='wiz_step1_status' title='{$MOD.LBL_CAMPAIGN_STATUS}'>{$STATUS_OPTIONS}</select></span sugar='slot'></td>
		</tr>
		<tr>
		<td scope="row"><span sugar='slot5'>{$MOD.LBL_CAMPAIGN_START_DATE} </span sugar='slot'></td>
		<td ><span sugar='slot5b'><input id='start_date' name='wiz_step1_start_date' title='{$MOD.LBL_CAMPAIGN_START_DATE}' onblur="parseDate(this, '{$CALENDAR_DATEFORMAT}');"  type="text" tabindex='1' size='11' maxlength='10' value="{$CAMP_START_DATE}"> <img src="{sugar_getimagepath file='jscalendar.gif'}" alt="{$APP.LBL_ENTER_DATE}" id="start_date_trigger" align="absmiddle"> <span class="dateFormat">{$USER_DATEFORMAT}</span></span sugar='slot'></td>
		<td scope="row"><span sugar='slot6'>{$MOD.LBL_CAMPAIGN_TYPE} </td>
		<td><span sugar='slot6b'><{$SHOULD_TYPE_BE_DISABLED} id='campaign_type' title='{$MOD.LBL_CAMPAIGN_TYPE}' name='wiz_step1_campaign_type' >{$CAMPAIGN_TYPE_OPTIONS}</select></span sugar='slot'></td>
		</tr>
		<tr>
		<td scope="row"><span sugar='slot7'>{$MOD.LBL_CAMPAIGN_END_DATE} <span class="required">{$APP.LBL_REQUIRED_SYMBOL}</span></span sugar='slot'></td>
		<td ><span sugar='slot7b'><input id='end_date' name='wiz_step1_end_date' title='{$MOD.LBL_CAMPAIGN_END_DATE}' onblur="parseDate(this, '{$CALENDAR_DATEFORMAT}');"  type="text" tabindex='1' size='11' maxlength='10' value="{$CAMP_END_DATE}"> <img src="{sugar_getimagepath file='jscalendar.gif'}" alt="{$APP.LBL_ENTER_DATE}" id="end_date_trigger" align="absmiddle"> <span class="dateFormat">{$USER_DATEFORMAT}</span></span sugar='slot'></td>
		<td scope="row"><span sugar='slot8'>{$FREQUENCY_LABEL} </span sugar='slot'></td>
		<td><span sugar='slot8b'><{$HIDE_FREQUENCY_IF_NEWSLETTER} tabindex='1' id='frequency' name='wiz_step1_frequency' title='{$MOD.LBL_CAMPAIGN_FREQUENCY}'>{$FREQ_OPTIONS}</select></span sugar='slot'></td>
		</tr>
		<tr>
		<td width="15%" scope="row"><span sugar='slot9'>&nbsp;</span></span sugar='slot'></td>
		<td width="35%" ><span sugar='slot9b'>&nbsp;</span sugar='slot'></td>
		<td scope="row"><span sugar='slot10'>&nbsp;</span sugar='slot'></td>
		<td><span sugar='slot10b'>&nbsp;</span sugar='slot'></td>
		<tr>
		</tr>
		<td valign="top" scope="row"><span sugar='slot10'>{$MOD.LBL_CAMPAIGN_CONTENT}</span sugar='slot'></td>
		<td colspan="3"><span sugar='slot10a'><textarea id='wiz_content' name='wiz_step1_content' title='{$MOD.LBL_CAMPAIGN_CONTENT}' tabindex='3' cols="110" rows="5">{$CONTENT}</textarea></span sugar='slot'></td>
		</tr>
		<tr>
		<td scope="row">&nbsp;</td>
		<td>&nbsp;</td>
		<td scope="row">&nbsp;</td>
		<td>&nbsp;</td>
		</tr>
	</table><p>

	{literal}
	<script type="text/javascript">
		Calendar.setup ({{/literal}
			inputField : "start_date", ifFormat : "{$CALENDAR_DATEFORMAT}", showsTime : false, button : "start_date_trigger", singleClick : true, step : 1, weekNumbers:false
			{literal}
		});
		
		Calendar.setup ({{/literal}
			inputField : "end_date", ifFormat : "{$CALENDAR_DATEFORMAT}", showsTime : false, button : "end_date_trigger", singleClick : true, step : 2, weekNumbers:false
		{literal}
		});
	

    /*
     * this is the custom validation script that will validate the fields on step1 of wizard
     */
    
    function validate_step1(){
        //loop through and check for empty strings ('  ')
        requiredTxt = SUGAR.language.get('app_strings', 'ERR_MISSING_REQUIRED_FIELDS');
        var stepname = 'wiz_step_1_';
        var has_error = 0;
        var fields = new Array();
        fields[0] = 'name'; 
        fields[1] = 'status';
        fields[2] = 'end_date';
        
        var field_value = ''; 
        for (i=0; i < fields.length; i++){
            if(document.getElementById(fields[i]) !=null){
                field_value = trim(document.getElementById(fields[i]).value);
                if(field_value.length<1){
                //throw error if string is empty            
                add_error_style('wizform', fields[i], requiredTxt +' ' +document.getElementById(fields[i]).title );
                has_error = 1;
                }
            }
        }
        if(has_error == 1){
            //error has been thrown, return false
            return false;
        }
        //add fields to validation and call generic validation script 
        if(validate['wizform']!='undefined'){delete validate['wizform']};
        addToValidate('wizform', 'name', 'alphanumeric', true,  document.getElementById('name').title);
        addToValidate('wizform', 'status', 'alphanumeric', true,  document.getElementById('status').title);
        addToValidate('wizform', 'end_date', 'date', true,  document.getElementById('end_date').title);
        addToValidate('wizform', 'start_date', 'date', false,  document.getElementById('start_date').title);
        addToValidate('wizform', 'currency_id', 'alphanumeric', false,  document.getElementById('currency_id').title);


        return check_form('wizform');
    }    





	</script>
	{/literal}

