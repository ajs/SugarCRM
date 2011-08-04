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
Calendar=function(){};Calendar.getHighestZIndex=function(containerEl)
{var highestIndex=0;var currentIndex=0;var els=Array();els=containerEl?containerEl.getElementsByTagName('*'):document.getElementsByTagName('*');for(var i=0;i<els.length;i++)
{currentIndex=YAHOO.util.Dom.getStyle(els[i],"zIndex");if(!isNaN(currentIndex)&&currentIndex>highestIndex)
{highestIndex=parseInt(currentIndex);}}
return(highestIndex==Number.MAX_VALUE)?Number.MAX_VALUE:highestIndex+1;};Calendar.setup=function(params){YAHOO.util.Event.onDOMReady(function(){var Event=YAHOO.util.Event;var Dom=YAHOO.util.Dom;var dialog;var calendar;var showButton=params.button?params.button:params.buttonObj;var userDateFormat=params.ifFormat?params.ifFormat:(params.daFormat?params.daFormat:"m/d/Y");var inputField=params.inputField?params.inputField:params.inputFieldObj;var dateFormat=userDateFormat.substr(0,10);var date_field_delimiter=/([-.\\/])/.exec(dateFormat)[0];dateFormat=dateFormat.replace(/[^a-zA-Z]/g,'');var monthPos=dateFormat.search(/m/);var dayPos=dateFormat.search(/d/);var yearPos=dateFormat.search(/Y/);Event.on(Dom.get(showButton),"click",function(){if(!dialog){dialog=new YAHOO.widget.SimpleDialog("container_"+showButton,{visible:false,context:[showButton,"tl","bl"],buttons:[],draggable:false,close:true,zIndex:Calendar.getHighestZIndex(document.body)});dialog.setHeader(SUGAR.language.get('app_strings','LBL_MASSUPDATE_DATE'));var dialogBody='<p class="callnav_today"><a href="#"  id="callnav_today">'+SUGAR.language.get('app_strings','LBL_EMAIL_DATE_TODAY')+'</a></p><div id="'+showButton+'_div"></div>';dialog.setBody(dialogBody);dialog.render(document.body);Dom.addClass("container_"+showButton,"cal_panel");Event.addListener("callnav_today","click",function(){calendar.clear();var now=new Date();Dom.get(inputField).value=formatSelectedDate(now);var cellIndex=calendar.getCellIndex(now);if(cellIndex>-1)
{var cell=calendar.cells[cellIndex];Dom.addClass(cell,calendar.Style.CSS_CELL_SELECTED);}});dialog.showEvent.subscribe(function(){if(YAHOO.env.ua.ie){dialog.fireEvent("changeContent");}});Event.on(document,"click",function(e){if(!dialog)
{return;}
var el=Event.getTarget(e);var dialogEl=dialog.element;if(el!=dialogEl&&!Dom.isAncestor(dialogEl,el)&&el!=Dom.get(showButton)&&!Dom.isAncestor(Dom.get(showButton),el)){dialog.hide();calendar=null;dialog=null;}});}
if(!calendar){var navConfig={strings:{month:SUGAR.language.get('app_strings','LBL_CHOOSE_MONTH'),year:SUGAR.language.get('app_strings','LBL_ENTER_YEAR'),submit:SUGAR.language.get('app_strings','LBL_EMAIL_OK'),cancel:SUGAR.language.get('app_strings','LBL_CANCEL_BUTTON_LABEL'),invalidYear:SUGAR.language.get('app_strings','LBL_ENTER_VALID_YEAR')},monthFormat:YAHOO.widget.Calendar.SHORT,initialFocus:"year"};calendar=new YAHOO.widget.Calendar(showButton+'_div',{iframe:false,hide_blank_weeks:true,navigator:navConfig});calendar.cfg.setProperty('DATE_FIELD_DELIMITER',date_field_delimiter);calendar.cfg.setProperty('MDY_DAY_POSITION',dayPos+1);calendar.cfg.setProperty('MDY_MONTH_POSITION',monthPos+1);calendar.cfg.setProperty('MDY_YEAR_POSITION',yearPos+1);if(typeof SUGAR.language.languages['app_list_strings']!='undefined'&&SUGAR.language.languages['app_list_strings']['dom_cal_month_long']!='undefined')
{if(SUGAR.language.languages['app_list_strings']['dom_cal_month_long'].length==13)
{SUGAR.language.languages['app_list_strings']['dom_cal_month_long'].shift();}
calendar.cfg.setProperty('MONTHS_LONG',SUGAR.language.languages['app_list_strings']['dom_cal_month_long']);}
if(typeof SUGAR.language.languages['app_list_strings']!='undefined'&&typeof SUGAR.language.languages['app_list_strings']['dom_cal_day_short']!='undefined')
{if(SUGAR.language.languages['app_list_strings']['dom_cal_day_short'].length==8)
{SUGAR.language.languages['app_list_strings']['dom_cal_day_short'].shift();}
calendar.cfg.setProperty('WEEKDAYS_SHORT',SUGAR.language.languages['app_list_strings']['dom_cal_day_short']);}
var formatSelectedDate=function(selDate)
{var monthVal=selDate.getMonth()+1;if(monthVal<10)
{monthVal='0'+monthVal;}
var dateVal=selDate.getDate();if(dateVal<10)
{dateVal='0'+dateVal;}
var yearVal=selDate.getFullYear();selDate='';if(monthPos==0)
{selDate=monthVal;}
else if(dayPos==0)
{selDate=dateVal;}
else
{selDate=yearVal;}
if(monthPos==1)
{selDate+=date_field_delimiter+monthVal;}
else if(dayPos==1)
{selDate+=date_field_delimiter+dateVal;}
else
{selDate+=date_field_delimiter+yearVal;}
if(monthPos==2)
{selDate+=date_field_delimiter+monthVal;}
else if(dayPos==2)
{selDate+=date_field_delimiter+dateVal;}
else
{selDate+=date_field_delimiter+yearVal;}
return selDate;};calendar.selectEvent.subscribe(function(){var input=Dom.get(inputField);if(calendar.getSelectedDates().length>0){input.value=formatSelectedDate(calendar.getSelectedDates()[0]);if(params.comboObject)
{params.comboObject.update();}}else{input.value="";}
dialog.hide();SUGAR.util.callOnChangeListers(input);});calendar.renderEvent.subscribe(function(){dialog.fireEvent("changeContent");});}
var seldate=calendar.getSelectedDates();if(Dom.get(inputField).value.length>0){val=new Date(Dom.get(inputField).value);if(!isNaN(val.getTime()))
{calendar.cfg.setProperty("selected",Dom.get(inputField).value);seldate=Dom.get(inputField).value.split(date_field_delimiter);calendar.cfg.setProperty("pagedate",seldate[monthPos]+calendar.cfg.getProperty("DATE_FIELD_DELIMITER")+seldate[yearPos]);}}else if(seldate.length>0){calendar.cfg.setProperty("selected",seldate[0]);var month=seldate[0].getMonth()+1;var year=seldate[0].getFullYear();calendar.cfg.setProperty("pagedate",month+calendar.cfg.getProperty("DATE_FIELD_DELIMITER")+year);}
calendar.render();dialog.show();});});};