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
 * Namespace for Sugar Objects
 */
if ( typeof(SUGAR) == "undefined" )	SUGAR = {};
if ( typeof(SUGAR.themes) == "undefined" )	SUGAR.themes = {};


    	/**
    	 * Namespace for Homepage
    	 */
    	 SUGAR.sugarHome= {};
    	/**
    	 * Namespace for Subpanel Utils
    	 */
    	SUGAR.subpanelUtils= {};
    	/**
    	 * AJAX status class
    	 */
    	SUGAR.ajaxStatusClass= {};
    	/**
    	 * Tab selector utils
    	 */
    	SUGAR.tabChooser= {};
    	/**
    	 * General namespace for Sugar utils
    	 */
    	SUGAR.util= {};
    	SUGAR.savedViews= {};
    	/**
    	 * Dashlet utils
    	 */
    	SUGAR.dashlets= {};
    	SUGAR.unifiedSearchAdvanced= {};

    	SUGAR.searchForm= {};
    	SUGAR.language= {};
    	SUGAR.Studio= {};
    	SUGAR.contextMenu= {};

    	SUGAR.config= {};

var nameIndex = 0;
var typeIndex = 1;
var requiredIndex = 2;
var msgIndex = 3;
var jstypeIndex = 5;
var minIndex = 10;
var maxIndex = 11;
var altMsgIndex = 15;
var compareToIndex = 7;
var arrIndex = 12;
var operatorIndex = 13;
var allowblank = 8;
var validate = new Array();
var maxHours = 24;
var requiredTxt = 'Missing Required Field:'
var invalidTxt = 'Invalid Value:'
var secondsSinceLoad = 0;
var inputsWithErrors = new Array();
var tabsWithErrors = new Array();
var lastSubmitTime = 0;
var alertList = new Array();
var oldStartsWith = '';


function isSupportedIE() {
	var userAgent = navigator.userAgent.toLowerCase() ;

	// IE Check supports ActiveX controls
	if (userAgent.indexOf("msie") != -1 && userAgent.indexOf("mac") == -1 && userAgent.indexOf("opera") == -1) {
		var version = navigator.appVersion.match(/MSIE (.\..)/)[1] ;
		if(version >= 5.5 && version < 9) {
			return true;
		} else {
			return false;
		}
	}
}

SUGAR.isIE = isSupportedIE();
SUGAR.isIE7 = (navigator.userAgent.toLowerCase().indexOf('msie 7')!=-1);
var isSafari = (navigator.userAgent.toLowerCase().indexOf('safari')!=-1);

// escapes regular expression characters
RegExp.escape = function(text) { // http://simon.incutio.com/archive/2006/01/20/escape
  if (!arguments.callee.sRE) {
    var specials = ['/', '.', '*', '+', '?', '|','(', ')', '[', ']', '{', '}', '\\'];
    arguments.callee.sRE = new RegExp('(\\' + specials.join('|\\') + ')', 'g');
  }
  return text.replace(arguments.callee.sRE, '\\$1');
}

function addAlert(type, name,subtitle, description,time, redirect) {
	var addIndex = alertList.length;
	alertList[addIndex]= new Array();
	alertList[addIndex]['name'] = name;
	alertList[addIndex]['type'] = type;
	alertList[addIndex]['subtitle'] = subtitle;
	alertList[addIndex]['description'] = description.replace(/<br>/gi, "\n").replace(/&amp;/gi,'&').replace(/&lt;/gi,'<').replace(/&gt;/gi,'>').replace(/&#039;/gi,'\'').replace(/&quot;/gi,'"');
	alertList[addIndex]['time'] = time;
	alertList[addIndex]['done'] = 0;
	alertList[addIndex]['redirect'] = redirect;
}
function checkAlerts() {
	secondsSinceLoad += 1;
	var mj = 0;
	var alertmsg = '';
	for(mj = 0 ; mj < alertList.length; mj++) {
		if(alertList[mj]['done'] == 0) {
			if(alertList[mj]['time'] < secondsSinceLoad && alertList[mj]['time'] > -1 ) {
				alertmsg = alertList[mj]['type'] + ":" + alertList[mj]['name'] + "\n" +alertList[mj]['subtitle']+ "\n"+ alertList[mj]['description'] + "\n\n";
				alertList[mj]['done'] = 1;
				if(alertList[mj]['redirect'] == '') {
					alert(alertmsg);
				}
				else if(confirm(alertmsg)) {
					window.location = alertList[mj]['redirect'];
				}
			}
		}
	}

	setTimeout("checkAlerts()", 1000);
}

function toggleDisplay(id) {
	if(this.document.getElementById(id).style.display == 'none') {
		this.document.getElementById(id).style.display = '';
		if(this.document.getElementById(id+"link") != undefined) {
			this.document.getElementById(id+"link").style.display = 'none';
		}
		if(this.document.getElementById(id+"_anchor") != undefined)
			this.document.getElementById(id+"_anchor").innerHTML='[ - ]';
	}
	else {
		this.document.getElementById(id).style.display = 'none'
		if(this.document.getElementById(id+"link") != undefined) {
			this.document.getElementById(id+"link").style.display = '';
		}
		if(this.document.getElementById(id+"_anchor") != undefined)
			this.document.getElementById(id+"_anchor").innerHTML='[+]';
	}
}

function checkAll(form, field, value) {
	for (i = 0; i < form.elements.length; i++) {
		if(form.elements[i].name == field)
			form.elements[i].checked = value;
	}
}

function replaceAll(text, src, rep) {
	offset = text.toLowerCase().indexOf(src.toLowerCase());
	while(offset != -1) {
		text = text.substring(0, offset) + rep + text.substring(offset + src.length ,text.length);
		offset = text.indexOf( src, offset + rep.length + 1);
	}
	return text;
}

function addForm(formname) {
	validate[formname] = new Array();
}

function addToValidate(formname, name, type, required, msg) {
	if(typeof validate[formname] == 'undefined') {
		addForm(formname);
	}
	validate[formname][validate[formname].length] = new Array(name, type,required, msg);
}

function addToValidateRange(formname, name, type,required,  msg,min,max) {
	addToValidate(formname, name, type,required,  msg);
	validate[formname][validate[formname].length - 1][jstypeIndex] = 'range'
	validate[formname][validate[formname].length - 1][minIndex] = min;
	validate[formname][validate[formname].length - 1][maxIndex] = max;
}

function addToValidateIsValidDate(formname, name, type, required, msg) {
	addToValidate(formname, name, type, required, msg);
	validate[formname][validate[formname].length - 1][jstypeIndex] = 'date'
}

function addToValidateIsValidTime(formname, name, type, required, msg) {
	addToValidate(formname, name, type, required, msg);
	validate[formname][validate[formname].length - 1][jstypeIndex] = 'time'
}

function addToValidateDateBefore(formname, name, type, required, msg, compareTo) {
	addToValidate(formname, name, type,required,  msg);
	validate[formname][validate[formname].length - 1][jstypeIndex] = 'isbefore'
	validate[formname][validate[formname].length - 1][compareToIndex] = compareTo;
}

function addToValidateDateBeforeAllowBlank(formname, name, type, required, msg, compareTo, allowBlank) {
	addToValidate(formname, name, type,required,  msg);
	validate[formname][validate[formname].length - 1][jstypeIndex] = 'isbefore'
	validate[formname][validate[formname].length - 1][compareToIndex] = compareTo;
	validate[formname][validate[formname].length - 1][allowblank] = allowBlank;
}

function addToValidateBinaryDependency(formname, name, type, required, msg, compareTo) {
	addToValidate(formname, name, type, required, msg);
	validate[formname][validate[formname].length - 1][jstypeIndex] = 'binarydep';
	validate[formname][validate[formname].length - 1][compareToIndex] = compareTo;
}

function addToValidateComparison(formname, name, type, required, msg, compareTo) {
	addToValidate(formname, name, type, required, msg);
	validate[formname][validate[formname].length - 1][jstypeIndex] = 'comparison';
	validate[formname][validate[formname].length - 1][compareToIndex] = compareTo;
}

function addToValidateIsInArray(formname, name, type, required, msg, arr, operator) {
	addToValidate(formname, name, type, required, msg);
	validate[formname][validate[formname].length - 1][jstypeIndex] = 'in_array';
	validate[formname][validate[formname].length - 1][arrIndex] = arr;
	validate[formname][validate[formname].length - 1][operatorIndex] = operator;
}

function addToValidateVerified(formname, name, type, required, msg, arr, operator) {
	addToValidate(formname, name, type, required, msg);
	validate[formname][validate[formname].length - 1][jstypeIndex] = 'verified';
}

function addToValidateLessThan(formname, name, type, required, msg, max, max_field_msg) {
	addToValidate(formname, name, type, required, msg);
	validate[formname][validate[formname].length - 1][jstypeIndex] = 'less';
    validate[formname][validate[formname].length - 1][maxIndex] = max;
    validate[formname][validate[formname].length - 1][altMsgIndex] = max_field_msg;

}
function addToValidateMoreThan(formname, name, type, required, msg, min) {
	addToValidate(formname, name, type, required, msg);
	validate[formname][validate[formname].length - 1][jstypeIndex] = 'more';
    validate[formname][validate[formname].length - 1][minIndex] = min;
}


function removeFromValidate(formname, name) {
	for(i = 0; i < validate[formname].length; i++)
	{
		if(validate[formname][i][nameIndex] == name)
		{
			validate[formname].splice(i--,1); // We subtract 1 from i since the slice removed an element, and we'll skip over the next item we scan
		}
	}
}
function checkValidate(formname, name) {
    if(validate[formname]){
	    for(i = 0; i < validate[formname].length; i++){
	        if(validate[formname][i][nameIndex] == name){
	            return true;
	        }
	    }
	}
    return false;
}
var formsWithFieldLogic=null;
var formWithPrecision =null;
function addToValidateFieldLogic(formId,minFieldId, maxFieldId, defaultFieldId, lenFieldId,type,msg){
	this.formId = document.getElementById(formId);
	this.min=document.getElementById(minFieldId);
	this.max= document.getElementById(maxFieldId);
	this._default= document.getElementById(defaultFieldId);
	this.len = document.getElementById(lenFieldId);
	this.msg = msg;
	this.type= type;
}
//@params: formid- Dom id of the form containing the precision and float fields
//         valudId- Dom id of the field containing a float whose precision is to be checked.
//         precisionId- Dom id of the field containing precision value.
function addToValidatePrecision(formId, valueId, precisionId){
	this.form = document.getElementById(formId);
	this.float = document.getElementById(valueId);
	this.precision = document.getElementById(precisionId);
}

//function checkLength(value, referenceValue){
//	return value
//}

function isValidPrecision(value, precision){
	value = trim(value.toString());
	if(precision == '')
		return true;
	if(value == '')
	    return true;
	//#27021
	if( (precision == "0") ){
		if (value.indexOf(".")== -1){
			return true;
		}else{
			return false;
		}
	}
	//#27021   end
	var actualPrecision = value.substr(value.indexOf(".")+1, value.length).length;
	return actualPrecision == precision;
}
function toDecimal(original, precision) {
    precision = (precision == null) ? 2 : precision;
    num = Math.pow(10, precision);
	temp = Math.round(original*num)/num;
	if((temp * 100) % 100 == 0)
		return temp + '.00';
	if((temp * 10) % 10 == 0)
		return temp + '0';
	return temp
}

function isInteger(s) {
	if (typeof s == "string" && s == "")
        return true;
    if(typeof num_grp_sep != 'undefined' && typeof dec_sep != 'undefined')
	{
		s = unformatNumberNoParse(s, num_grp_sep, dec_sep).toString();
	}
	return parseFloat(s) == parseInt(s) && !isNaN(s);
}

function isNumeric(s) {
  if(!/^-*[0-9\.]+$/.test(s)) {
   		return false
   }
   else {
		return true;
   }
}

var date_reg_positions = {'Y': 1,'m': 2,'d': 3};
var date_reg_format = '([0-9]{4})-([0-9]{1,2})-([0-9]{1,2})'
function isDate(dtStr) {

	if(dtStr.length== 0) {
		return true;
	}

    // Check that we have numbers
	myregexp = new RegExp(date_reg_format)
	if(!myregexp.test(dtStr))
		return false

    m = '';
    d = '';
    y = '';

    var dateParts = dtStr.match(date_reg_format);
    for(key in date_reg_positions) {
        index = date_reg_positions[key];
        if(key == 'm') {
           m = dateParts[index];
        } else if(key == 'd') {
           d = dateParts[index];
        } else {
           y = dateParts[index];
        }
    }

    // Check that date is real
    var dd = new Date(y,m,0);
    // reject negative years
    if (y < 1)
        return false;
    // reject month less than 1 and greater than 12
    if (m > 12 || m < 1)
        return false;
    // reject days less than 1 or days not in month (e.g. February 30th)
    if (d < 1 || d > dd.getDate())
        return false;
    return true;
}

function getDateObject(dtStr) {
	if(dtStr.length== 0) {
		return true;
	}

	myregexp = new RegExp(date_reg_format)

	if(myregexp.exec(dtStr)) var dt = myregexp.exec(dtStr)
	else return false;

	var yr = dt[date_reg_positions['Y']];
	var mh = dt[date_reg_positions['m']];
	var dy = dt[date_reg_positions['d']];
    var dtar = dtStr.split(' ');
    if(typeof(dtar[1])!='undefined' && isTime(dtar[1])) {//if it is a timedate, we should make date1 to have time value
        var t1 = dtar[1].replace(/am/i,' AM');
        var t1 = t1.replace(/pm/i,' PM');
        //bug #37977: where time format 23.00 causes java script error
        t1=t1.replace(/\./, ':');
        date1 = new Date(Date.parse(mh+'/'+dy+ '/'+yr+' '+t1));
    }
    else
    {
        var date1 = new Date();
        date1.setFullYear(yr); // xxxx 4 char year
        date1.setMonth(mh-1); // 0-11 Bug 4048: javascript Date obj months are 0-index
        date1.setDate(dy); // 1-31
    }
	return date1;
}

function isBefore(value1, value2) {
	var d1 = getDateObject(value1);
	var d2 = getDateObject(value2);
    if(typeof(d2)=='boolean') {// if d2 is not set, we should let it pass, the d2 may not need to be set. the empty check should not be done here.
        return true;
    }
	return d2 >= d1;
}

function isValidEmail(emailStr) {
	
    if(emailStr.length== 0) {
		return true;
	}
	// cn: bug 7128, a period at the end of the string mangles checks. (switched to accept spaces and delimiters)
	var lastChar = emailStr.charAt(emailStr.length - 1);
	if(!lastChar.match(/[^\.]/i)) {
		return false;
	}
	//bug 40068, According to rules in page 6 of http://www.apps.ietf.org/rfc/rfc3696.html#sec-3,
	//first character of local part of an email address
	//should not be a period i.e. '.'

	var firstLocalChar=emailStr.charAt(0);
	if(firstLocalChar.match(/\./)){
		return false;
	}

	//bug 40068, According to rules in page 6 of http://www.apps.ietf.org/rfc/rfc3696.html#sec-3,
	//last character of local part of an email address
	//should not be a period i.e. '.'

	var pos=emailStr.lastIndexOf("@");
	var localPart = emailStr.substr(0, pos);
	var lastLocalChar=localPart.charAt(localPart.length - 1);
	if(lastLocalChar.match(/\./)){
		return false;
	}


	var reg = /@.*?;/g;
    var results;
	while ((results = reg.exec(emailStr)) != null) {
			var original = results[0];
			parsedResult = results[0].replace(';', '::;::');
			emailStr = emailStr.replace (original, parsedResult);
	}

	reg = /.@.*?,/g;
	while ((results = reg.exec(emailStr)) != null) {
			var original = results[0];
			//Check if we were using ; as a delimiter. If so, skip the commas
            if(original.indexOf("::;::") == -1) {
                var parsedResult = results[0].replace(',', '::;::');
			    emailStr = emailStr.replace (original, parsedResult);
            }
	}

	// mfh: bug 15010 - more practical implementation of RFC 2822 from http://www.regular-expressions.info/email.html, modifed to accept CAPITAL LETTERS
	//if(!/[a-zA-Z0-9!#$%&'*+/=?^_`{|}~-]+(?:\.[a-zA-Z0-9!#$%&'*+/=?^_`{|}~-]+)*@(?:[a-zA-Z0-9](?:[a-zA-Z0-9-]*[a-zA-Z0-9])?\.)+[a-zA-Z0-9](?:[a-zA-Z0-9-]*[a-zA-Z0-9])?/.test(emailStr))
	//	return false

	//bug 40068, According to rules in page 6 of http://www.apps.ietf.org/rfc/rfc3696.html#sec-3,
	//allowed special characters ! # $ % & ' * + - / = ?  ^ _ ` . { | } ~ in local part
    var emailArr = emailStr.split(/::;::/);
	for (var i = 0; i < emailArr.length; i++) {
		var emailAddress = emailArr[i];
		if (trim(emailAddress) != '') {
			if(!/^\s*[\w.%+\-&'#!\$\*=\?\^_`\{\}~\/]+@([A-Z0-9-]+\.)*[A-Z0-9-]+\.[\w-]{2,}\s*$/i.test(emailAddress) &&
			   !/^.*<[A-Z0-9._%+\-&'#!\$\*=\?\^_`\{\}~]+?@([A-Z0-9-]+\.)*[A-Z0-9-]+\.[\w-]{2,}>\s*$/i.test(emailAddress)) {

			   return false;
			} // if
		}
	} // for
	return true;
}

function isValidPhone(phoneStr) {
	if(phoneStr.length== 0) {
		return true;
	}
	if(!/^[0-9\-\(\)\s]+$/.test(phoneStr))
		return false
	return true
}
function isFloat(floatStr) {
	if(floatStr.length== 0) {
		return true;
	}
	if(!(typeof(num_grp_sep)=='undefined' || typeof(dec_sep)=='undefined')) {
		floatStr = unformatNumberNoParse(floatStr, num_grp_sep, dec_sep).toString();
    }

	return /^(-)?[0-9\.]+$/.test(floatStr);
}
function isDBName(str) {

	if(str.length== 0) {
		return true;
	}
	// must start with a letter
	if(!/^[a-zA-Z][a-zA-Z\_0-9]*$/.test(str))
		return false
	return true
}
var time_reg_format = "[0-9]{1,2}\:[0-9]{2}";
function isTime(timeStr) {
    var time_reg_format = "[0-9]{1,2}\:[0-9]{2}";
	time_reg_format = time_reg_format.replace('([ap]m)', '');
	time_reg_format = time_reg_format.replace('([AP]M)', '');
	if(timeStr.length== 0){
		return true;
	}
	//we now support multiple time formats
	myregexp = new RegExp(time_reg_format)
	if(!myregexp.test(timeStr))
		return false

	return true;
}

function inRange(value, min, max) {
    if (typeof num_grp_sep != 'undefined' && typeof dec_sep != 'undefined')
       value = unformatNumberNoParse(value, num_grp_sep, dec_sep).toString();
	return value >= min && value <= max;
}

function bothExist(item1, item2) {
	if(typeof item1 == 'undefined') { return false; }
	if(typeof item2 == 'undefined') { return false; }
	if((item1 == '' && item2 != '') || (item1 != '' && item2 == '') ) { return false; }
	return true;
}

trim = YAHOO.lang.trim;


function check_form(formname) {
	if (typeof(siw) != 'undefined' && siw
		&& typeof(siw.selectingSomething) != 'undefined' && siw.selectingSomething)
			return false;
	return validate_form(formname, '');
}

function add_error_style(formname, input, txt, flash) {
	if (typeof flash == "undefined")
		flash = true;
	try {
	inputHandle = typeof input == "object" ? input : document.forms[formname][input];
	style = get_current_bgcolor(inputHandle);

	// strip off the colon at the end of the warning strings
	if ( txt.substring(txt.length-1) == ':' )
	    txt = txt.substring(0,txt.length-1)

	// Bug 28249 - To help avoid duplicate messages for an element, strip off extra messages and
	// match on the field name itself
	requiredTxt = SUGAR.language.get('app_strings', 'ERR_MISSING_REQUIRED_FIELDS');
    invalidTxt = SUGAR.language.get('app_strings', 'ERR_INVALID_VALUE');
    nomatchTxt = SUGAR.language.get('app_strings', 'ERR_SQS_NO_MATCH_FIELD');
    matchTxt = txt.replace(requiredTxt,'').replace(invalidTxt,'').replace(nomatchTxt,'');
	
	if(inputHandle.parentNode.innerHTML.search(matchTxt) == -1) {
        errorTextNode = document.createElement('span');
        errorTextNode.className = 'required';
        errorTextNode.innerHTML = '<br />' + txt;
        if ( inputHandle.parentNode.className.indexOf('x-form-field-wrap') != -1 ) {
            inputHandle.parentNode.parentNode.appendChild(errorTextNode);
        }
        else {
            inputHandle.parentNode.appendChild(errorTextNode);
        }
        if (flash)
        	inputHandle.style.backgroundColor = "#FF0000";
        inputsWithErrors.push(inputHandle);
	}
    if (flash)
    {
		// We only need to setup the flashy-flashy on the first entry, it loops through all fields automatically
	    if ( inputsWithErrors.length == 1 ) {
	      for(wp = 1; wp <= 10; wp++) {
	        window.setTimeout('fade_error_style(style, '+wp*10+')',1000+(wp*50));
	      }
	    }
		if(typeof (window[formname + "_tabs"]) != "undefined") {
	        var tabView = window[formname + "_tabs"];
	        var parentDiv = YAHOO.util.Dom.getAncestorByTagName(inputHandle, "div");
	        if ( tabView.get ) {
	            var tabs = tabView.get("tabs");
	            for (var i in tabs) {
	                if (tabs[i].get("contentEl") == parentDiv
	                		|| YAHOO.util.Dom.isAncestor(tabs[i].get("contentEl"), inputHandle))
	                {
	                    tabs[i].get("labelEl").style.color = "red";
	                    if ( inputsWithErrors.length == 1 )
	                        tabView.selectTab(i);
	                }
	            }
	        }
		}
		window.setTimeout("inputsWithErrors[" + (inputsWithErrors.length - 1) + "].style.backgroundColor = null;", 2000);
    }

  } catch ( e ) {
      // Catch errors here so we don't allow an incomplete record through the javascript validation
  }
}

/**
 * removes all error messages for the current form
 */
function clear_all_errors() {
    for(var wp = 0; wp < inputsWithErrors.length; wp++) {
        if(typeof(inputsWithErrors[wp]) !='undefined' && typeof inputsWithErrors[wp].parentNode != 'undefined' && inputsWithErrors[wp].parentNode != null) {
            if ( inputsWithErrors[wp].parentNode.className.indexOf('x-form-field-wrap') != -1 )
            {
                inputsWithErrors[wp].parentNode.parentNode.removeChild(inputsWithErrors[wp].parentNode.parentNode.lastChild);
            }
            else
            {
                inputsWithErrors[wp].parentNode.removeChild(inputsWithErrors[wp].parentNode.lastChild);
            }
        }
	}
	if (inputsWithErrors.length == 0) return;

	if ( YAHOO.util.Dom.getAncestorByTagName(inputsWithErrors[0], "form") ) {
        var formname = YAHOO.util.Dom.getAncestorByTagName(inputsWithErrors[0], "form").getAttribute("name");
        if(typeof (window[formname + "_tabs"]) != "undefined") {
            var tabView = window[formname + "_tabs"];
            if ( tabView.get ) {
                var tabs = tabView.get("tabs");
                for (var i in tabs) {
                    tabs[i].get("labelEl").style.color = "";
                }
            }
        }
        inputsWithErrors = new Array();
    }
}

function get_current_bgcolor(input) {
	if(input.currentStyle) {// ie
		style = input.currentStyle.backgroundColor;
		return style.substring(1,7);
	}
	else {// moz
		style = '';
		styleRGB = document.defaultView.getComputedStyle(input, '').getPropertyValue("background-color");
		comma = styleRGB.indexOf(',');
		style += dec2hex(styleRGB.substring(4, comma));
		commaPrevious = comma;
		comma = styleRGB.indexOf(',', commaPrevious+1);
		style += dec2hex(styleRGB.substring(commaPrevious+2, comma));
		style += dec2hex(styleRGB.substring(comma+2, styleRGB.lastIndexOf(')')));
		return style;
	}
}

function hex2dec(hex){return(parseInt(hex,16));}
var hexDigit=new Array("0","1","2","3","4","5","6","7","8","9","A","B","C","D","E","F");
function dec2hex(dec){return(hexDigit[dec>>4]+hexDigit[dec&15]);}

function fade_error_style(normalStyle, percent) {
	errorStyle = 'c60c30';
	var r1 = hex2dec(errorStyle.slice(0,2));
	var g1 = hex2dec(errorStyle.slice(2,4));
	var b1 = hex2dec(errorStyle.slice(4,6));

	var r2 = hex2dec(normalStyle.slice(0,2));
	var g2 = hex2dec(normalStyle.slice(2,4));
	var b2 = hex2dec(normalStyle.slice(4,6));


	var pc = percent / 100;

	r= Math.floor(r1+(pc*(r2-r1)) + .5);
	g= Math.floor(g1+(pc*(g2-g1)) + .5);
	b= Math.floor(b1+(pc*(b2-b1)) + .5);

	for(var wp = 0; wp < inputsWithErrors.length; wp++) {
		inputsWithErrors[wp].style.backgroundColor = "#" + dec2hex(r) + dec2hex(g) + dec2hex(b);
	}
}

function isFieldTypeExceptFromEmptyCheck(fieldType)
{
    var results = false;
    var exemptList = ['bool','file'];
    for(var i=0;i<exemptList.length;i++)
    {
        if(fieldType == exemptList[i])
            return true;
    }
    return results;
}
function validate_form(formname, startsWith){
    requiredTxt = SUGAR.language.get('app_strings', 'ERR_MISSING_REQUIRED_FIELDS');
    invalidTxt = SUGAR.language.get('app_strings', 'ERR_INVALID_VALUE');

	if ( typeof (formname) == 'undefined')
	{
		return false;
	}
	if ( typeof (validate[formname]) == 'undefined')
	{
        disableOnUnloadEditView(document.forms[formname]);
		return true;
	}

	var form = document.forms[formname];
	var isError = false;
	var errorMsg = "";
	var _date = new Date();
	if(_date.getTime() < (lastSubmitTime + 2000) && startsWith == oldStartsWith) { // ignore submits for the next 2 seconds
		return false;
	}
	lastSubmitTime = _date.getTime();
	oldStartsWith = startsWith;

	clear_all_errors(); // remove previous error messages

	inputsWithErrors = new Array();
	for(var i = 0; i < validate[formname].length; i++){
			if(validate[formname][i][nameIndex].indexOf(startsWith) == 0){
				if(typeof form[validate[formname][i][nameIndex]]  != 'undefined'){
					var bail = false;

                    //If a field is not required and it is blank or is binarydependant, skip validation.
                    //Example of binary dependant fields would be the hour/min/meridian dropdowns in a date time combo widget, which require further processing than a blank check
                    if(!validate[formname][i][requiredIndex] && trim(form[validate[formname][i][nameIndex]].value) == '' && (typeof(validate[formname][i][jstypeIndex]) != 'undefined' && validate[formname][i][jstypeIndex]  != 'binarydep'))
                    {
                       continue;
                    }					
					
					if(validate[formname][i][requiredIndex]
						&& !isFieldTypeExceptFromEmptyCheck(validate[formname][i][typeIndex])
					){
						if(typeof form[validate[formname][i][nameIndex]] == 'undefined' || trim(form[validate[formname][i][nameIndex]].value) == ""){
							add_error_style(formname, validate[formname][i][nameIndex], requiredTxt +' ' + validate[formname][i][msgIndex]);
							isError = true;
						}
					}
					if(!bail){
						switch(validate[formname][i][typeIndex]){
						case 'email':
							if(!isValidEmail(trim(form[validate[formname][i][nameIndex]].value))){
								isError = true;
								add_error_style(formname, validate[formname][i][nameIndex], invalidTxt + " " +	validate[formname][i][msgIndex]);
							}
							 break;
						case 'time':
							if( !isTime(trim(form[validate[formname][i][nameIndex]].value))){
								isError = true;
								add_error_style(formname, validate[formname][i][nameIndex], invalidTxt + " " +	validate[formname][i][msgIndex]);
							} break;
						case 'date': if(!isDate(trim(form[validate[formname][i][nameIndex]].value))){
								isError = true;
								add_error_style(formname, validate[formname][i][nameIndex], invalidTxt + " " +	validate[formname][i][msgIndex]);
							}  break;
						case 'alpha':
							break;
						case 'DBName':
							if(!isDBName(trim(form[validate[formname][i][nameIndex]].value))){
								isError = true;
								add_error_style(formname, validate[formname][i][nameIndex], invalidTxt + " " +	validate[formname][i][msgIndex]);
							}
							break;
						case 'alphanumeric':
							break;
						case 'file':
						    if( validate[formname][i][requiredIndex] && typeof( form[validate[formname][i][nameIndex] + '_file'] ) != 'undefined' && trim( form[validate[formname][i][nameIndex] + '_file'].value) == "" && !form[validate[formname][i][nameIndex] + '_file'].disabled ) {
						          isError = true;
						          add_error_style(formname, validate[formname][i][nameIndex], requiredTxt + " " +	validate[formname][i][msgIndex]);
						      }					      
						  break;	
						case 'int':
							if(!isInteger(trim(form[validate[formname][i][nameIndex]].value))){
								isError = true;
								add_error_style(formname, validate[formname][i][nameIndex], invalidTxt + " " +	validate[formname][i][msgIndex]);
							}
							break;
						case 'currency':
						case 'float':
							if(!isFloat(trim(form[validate[formname][i][nameIndex]].value))){
								isError = true;
								add_error_style(formname, validate[formname][i][nameIndex], invalidTxt + " " +	validate[formname][i][msgIndex]);
							}
							break;
						case 'teamset_mass':
							div_element_id = formname + '_' + form[validate[formname][i][nameIndex]].name + '_operation_div';
							input_elements = YAHOO.util.Selector.query('input', document.getElementById(div_element_id));
							primary_field_id = '';
							validation_passed = false;
							replace_selected = false;

							//Loop through the option elements (replace or add currently)
							for(t in input_elements) {
								if(input_elements[t].type && input_elements[t].type == 'radio' && input_elements[t].checked == true && input_elements[t].value == 'replace') {

						           //Now find where the primary radio button is and if a value has been set
						           radio_elements = YAHOO.util.Selector.query('input[type=radio]', document.getElementById(formname + '_team_name_table'));

						           for(x in radio_elements) {
						        	   if(radio_elements[x].name != 'team_name_type') {
						        		  primary_field_id = 'team_name_collection_' + radio_elements[x].value;
						        		  if(radio_elements[x].checked) {
						        			  replace_selected = true;
						        			  if(trim(document.forms[formname].elements[primary_field_id].value) != '') {
		                                         validation_passed = true;
		                                         break;
										      }
						        		  } else if(trim(document.forms[formname].elements[primary_field_id].value) != '') {
						        			  replace_selected = true;
						        		  }
						        	   }
								   }
						        }
							}

							if(replace_selected && !validation_passed) {
						       add_error_style(formname, primary_field_id, SUGAR.language.get('app_strings', 'ERR_NO_PRIMARY_TEAM_SPECIFIED'));
						       isError = true;
							}
							break;
						case 'teamset':
							   table_element_id = formname + '_' + form[validate[formname][i][nameIndex]].name + '_table';
							   if(document.getElementById(table_element_id)) {
								   input_elements = YAHOO.util.Selector.query('input[type=radio]', document.getElementById(table_element_id));
								   has_primary = false;
								   primary_field_id = form[validate[formname][i][nameIndex]].name + '_collection_0';

								   for(t in input_elements) {
									    primary_field_id = form[validate[formname][i][nameIndex]].name + '_collection_' + input_elements[t].value;
								        if(input_elements[t].type && input_elements[t].type == 'radio' && input_elements[t].checked == true) {
								           if(document.forms[formname].elements[primary_field_id].value != '') {
								        	  has_primary = true;
								           }
								           break;
								        }
								   }

								   if(!has_primary) {
									  isError = true;
									  field_id = form[validate[formname][i][nameIndex]].name + '_collection_' + input_elements[0].value;
									  add_error_style(formname, field_id, SUGAR.language.get('app_strings', 'ERR_NO_PRIMARY_TEAM_SPECIFIED'));
								   }
							   }
						       break;
					    case 'error':
							isError = true;
                            add_error_style(formname, validate[formname][i][nameIndex], validate[formname][i][msgIndex]);
							break;
						}

						if(typeof validate[formname][i][jstypeIndex]  != 'undefined'/* && !isError*/){

							switch(validate[formname][i][jstypeIndex]){
							case 'range':
								if(!inRange(trim(form[validate[formname][i][nameIndex]].value), validate[formname][i][minIndex], validate[formname][i][maxIndex])){
									isError = true;
                                    var lbl_validate_range = SUGAR.language.get('app_strings', 'LBL_VALIDATE_RANGE');
                                    add_error_style(formname, validate[formname][i][nameIndex], validate[formname][i][msgIndex] + " value " + form[validate[formname][i][nameIndex]].value + " " + lbl_validate_range + " (" +validate[formname][i][minIndex] + " - " + validate[formname][i][maxIndex] +  ") ");
								}
							break;
							case 'isbefore':
								compareTo = form[validate[formname][i][compareToIndex]];
								if(	typeof compareTo != 'undefined'){
									if(trim(compareTo.value) != '' || (validate[formname][i][allowblank] != 'true') ) {
										date2 = trim(compareTo.value);
										date1 = trim(form[validate[formname][i][nameIndex]].value);

										if(trim(date1).length != 0 && !isBefore(date1,date2)){
										
											isError = true;
											//jc:#12287 - adding translation for the is not before message
											add_error_style(formname, validate[formname][i][nameIndex], validate[formname][i][msgIndex] + "(" + date1 + ") " + SUGAR.language.get('app_strings', 'MSG_IS_NOT_BEFORE') + ' ' +date2);
										}
									}
								}
							break;
                            case 'less':
                                value=unformatNumber(trim(form[validate[formname][i][nameIndex]].value), num_grp_sep, dec_sep);
								maximum = parseFloat(validate[formname][i][maxIndex]);
								if(	typeof maximum != 'undefined'){
									if(value>maximum) {
                                        isError = true;
                                        add_error_style(formname, validate[formname][i][nameIndex], validate[formname][i][msgIndex] +" " +SUGAR.language.get('app_strings', 'MSG_IS_MORE_THAN')+ ' ' + validate[formname][i][altMsgIndex]);
                                    }
								}
							break;
							case 'more':
                                value=unformatNumber(trim(form[validate[formname][i][nameIndex]].value), num_grp_sep, dec_sep);
								minimum = parseFloat(validate[formname][i][minIndex]);
								if(	typeof minimum != 'undefined'){
									if(value<minimum) {
                                        isError = true;
                                        add_error_style(formname, validate[formname][i][nameIndex], validate[formname][i][msgIndex] +" " +SUGAR.language.get('app_strings', 'MSG_SHOULD_BE')+ ' ' + minimum + ' ' + SUGAR.language.get('app_strings', 'MSG_OR_GREATER'));
                                    }
								}
							break;
                            case 'binarydep':
								compareTo = form[validate[formname][i][compareToIndex]];
								if( typeof compareTo != 'undefined') {
									item1 = trim(form[validate[formname][i][nameIndex]].value);
									item2 = trim(compareTo.value);
									if(!bothExist(item1, item2)) {
										isError = true;
										add_error_style(formname, validate[formname][i][nameIndex], validate[formname][i][msgIndex]);
									}
								}
							break;
							case 'comparison':
								compareTo = form[validate[formname][i][compareToIndex]];
								if( typeof compareTo != 'undefined') {
									item1 = trim(form[validate[formname][i][nameIndex]].value);
									item2 = trim(compareTo.value);
									if(!bothExist(item1, item2) || item1 != item2) {
										isError = true;
										add_error_style(formname, validate[formname][i][nameIndex], validate[formname][i][msgIndex]);
									}
								}
							break;
							case 'in_array':
								arr = eval(validate[formname][i][arrIndex]);
								operator = validate[formname][i][operatorIndex];
								item1 = trim(form[validate[formname][i][nameIndex]].value);
								if (operator.charAt(0) == 'u') {
									item1 = item1.toUpperCase();
									operator = operator.substring(1);
								} else if (operator.charAt(0) == 'l') {
									item1 = item1.toLowerCase();
									operator = operator.substring(1);
								}
								for(j = 0; j < arr.length; j++){
									val = arr[j];
									if((operator == "==" && val == item1) || (operator == "!=" && val != item1)){
										isError = true;
										add_error_style(formname, validate[formname][i][nameIndex], invalidTxt + " " +	validate[formname][i][msgIndex]);
									}
								}
							break;
							case 'verified':
							if(trim(form[validate[formname][i][nameIndex]].value) == 'false'){
							   //Fake an error so form does not submit
							   isError = true;
							}
							break;
							}
						}
					}
				}
			}
		}
/*	nsingh: BUG#15102
	Check min max default field logic.
	Can work with float values as well, but as of 10/8/07 decimal values in MB and studio don't have min and max value constraints.*/
	if(formsWithFieldLogic){
		var invalidLogic=false;
		if(formsWithFieldLogic.min && formsWithFieldLogic.max && formsWithFieldLogic._default) {
			var showErrorsOn={min:{value:'min', show:false, obj:formsWithFieldLogic.min.value},
							max:{value:'max',show:false, obj:formsWithFieldLogic.max.value},
							_default:{value:'default',show:false, obj:formsWithFieldLogic._default.value},
                              len:{value:'len', show:false, obj:parseInt(formsWithFieldLogic.len.value,10)}};

			var min = (formsWithFieldLogic.min.value !='') ? parseFloat(formsWithFieldLogic.min.value) : 'undef';
			var max  = (formsWithFieldLogic.max.value !='') ? parseFloat(formsWithFieldLogic.max.value) : 'undef';
			var _default = (formsWithFieldLogic._default.value!='')? parseFloat(formsWithFieldLogic._default.value) : 'undef';

			/*Check all lengths are <= max size.*/
			for(var i in showErrorsOn){
				if(showErrorsOn[i].value!='len' && showErrorsOn[i].obj.length > showErrorsOn.len.obj){
					invalidLogic=true;
					showErrorsOn[i].show=true;
					showErrorsOn.len.show=true;
				}
			}

			if(min!='undef' && max!='undef' && _default!='undef'){
				if(!inRange(_default,min,max)){
					invalidLogic=true;
					showErrorsOn.min.show=true;
					showErrorsOn.max.show=true;
					showErrorsOn._default.show=true;
				}
			}
			if(min!='undef' && max!= 'undef' && min > max){
				invalidLogic = true;
				showErrorsOn.min.show=true;
				showErrorsOn.max.show=true;
			}
			if(min!='undef' && _default!='undef' && _default < min){

				invalidLogic = true;
				showErrorsOn.min.show=true;
				showErrorsOn._default.show=true;
			}
			if(max!='undef' && _default !='undef' && _default>max){

				invalidLogic = true;
				showErrorsOn.max.show=true;
				showErrorsOn._default.show=true;
			}

			if(invalidLogic){
				isError=true;
				for(var error in showErrorsOn)
					if(showErrorsOn[error].show)
						add_error_style(formname,showErrorsOn[error].value, formsWithFieldLogic.msg);

			}

			else if (!isError)
				formsWithFieldLogic = null;
		}
	}
	if(formWithPrecision){
		if (!isValidPrecision(formWithPrecision.float.value, formWithPrecision.precision.value)){
			isError = true;
			add_error_style(formname, 'default', SUGAR.language.get('app_strings', 'ERR_COMPATIBLE_PRECISION_VALUE'));
		}else if(!isError){
			isError = false;
		}
	}

//END BUG# 15102

	if (isError == true) {
		var nw, ne, sw, se;
		if (self.pageYOffset) // all except Explorer
		{
			nwX = self.pageXOffset;
			seX = self.innerWidth;
			nwY = self.pageYOffset;
			seY = self.innerHeight;
		}
		else if (document.documentElement && document.documentElement.scrollTop) // Explorer 6 Strict
		{
			nwX = document.documentElement.scrollLeft;
			seX = document.documentElement.clientWidth;
			nwY = document.documentElement.scrollTop;
			seY = document.documentElement.clientHeight;
		}
		else if (document.body) // all other Explorers
		{
			nwX = document.body.scrollLeft;
			seX = document.body.clientWidth;
			nwY = document.body.scrollTop;
			seY = document.body.clientHeight;
		}

		var inView = true; // is there an error within viewport of browser
		for(var wp = 0; wp < inputsWithErrors.length; wp++) {
			var elementCoor = findElementPos(inputsWithErrors[wp]);
			if(!(elementCoor.x >= nwX && elementCoor.y >= nwY &&
				elementCoor.x <= seX && elementCoor.y <= seY)) { // if input is not within viewport
					inView = false;
					scrollToTop = elementCoor.y - 75;
					scrollToLeft = elementCoor.x - 75;
			}
			else { // on first input within viewport, don't scroll
				break;
			}
		}


		if(!inView) window.scrollTo(scrollToLeft,scrollToTop);

		return false;
	}

    disableOnUnloadEditView(form);
	return true;

}


/**
 * This array is used to remember mark status of rows in browse mode
 */
var marked_row = new Array;


/**
 * Sets/unsets the pointer and marker in browse mode
 *
 * @param   object    the table row
 * @param   interger  the row number
 * @param   string    the action calling this script (over, out or click)
 * @param   string    the default background color
 * @param   string    the color to use for mouseover
 * @param   string    the color to use for marking a row
 *
 * @return  boolean  whether pointer is set or not
 */
function setPointer(theRow, theRowNum, theAction, theDefaultColor, thePointerColor, theMarkColor) {
    var theCells = null;

    // 1. Pointer and mark feature are disabled or the browser can't get the
    //    row -> exits
    if ((thePointerColor == '' && theMarkColor == '')
        || typeof(theRow.style) == 'undefined') {
        return false;
    }

    // 2. Gets the current row and exits if the browser can't get it
    if (typeof(document.getElementsByTagName) != 'undefined') {
        theCells = theRow.getElementsByTagName('td');
    }
    else if (typeof(theRow.cells) != 'undefined') {
        theCells = theRow.cells;
    }
    else {
        return false;
    }

    // 3. Gets the current color...
    var rowCellsCnt  = theCells.length;
    var domDetect    = null;
    var currentColor = null;
    var newColor     = null;
    // 3.1 ... with DOM compatible browsers except Opera that does not return
    //         valid values with "getAttribute"
    if (typeof(window.opera) == 'undefined'
        && typeof(theCells[0].getAttribute) != 'undefined') {
        currentColor = theCells[0].getAttribute('bgcolor');
        domDetect    = true;
    }
    // 3.2 ... with other browsers
    else {
        currentColor = theCells[0].style.backgroundColor;
        domDetect    = false;
    } // end 3

    // 4. Defines the new color
    // 4.1 Current color is the default one
    if (currentColor == ''
        || (currentColor!= null && (currentColor.toLowerCase() == theDefaultColor.toLowerCase()))) {
        if (theAction == 'over' && thePointerColor != '') {
            newColor              = thePointerColor;
        }
        else if (theAction == 'click' && theMarkColor != '') {
            newColor              = theMarkColor;
            marked_row[theRowNum] = true;
        }
    }
    // 4.1.2 Current color is the pointer one
    else if (currentColor!= null && (currentColor.toLowerCase() == thePointerColor.toLowerCase())
             && (typeof(marked_row[theRowNum]) == 'undefined' || !marked_row[theRowNum])) {
        if (theAction == 'out') {
            newColor              = theDefaultColor;
        }
        else if (theAction == 'click' && theMarkColor != '') {
            newColor              = theMarkColor;
            marked_row[theRowNum] = true;
        }
    }
    // 4.1.3 Current color is the marker one
    else if (currentColor!= null && (currentColor.toLowerCase() == theMarkColor.toLowerCase())) {
        if (theAction == 'click') {
            newColor              = (thePointerColor != '')
                                  ? thePointerColor
                                  : theDefaultColor;
            marked_row[theRowNum] = (typeof(marked_row[theRowNum]) == 'undefined' || !marked_row[theRowNum])
                                  ? true
                                  : null;
        }
    } // end 4

    // 5. Sets the new color...
    if (newColor) {
        var c = null;
        // 5.1 ... with DOM compatible browsers except Opera
        if (domDetect) {
            for (c = 0; c < rowCellsCnt; c++) {
                theCells[c].setAttribute('bgcolor', newColor, 0);
            } // end for
        }
        // 5.2 ... with other browsers
        else {
            for (c = 0; c < rowCellsCnt; c++) {
                theCells[c].style.backgroundColor = newColor;
            }
        }
    } // end 5

    return true;
} // end of the 'setPointer()' function


/**
  * listbox redirection
  */
function goToUrl(selObj, goToLocation) {
    eval("document.location.href = '" + goToLocation + "pos=" + selObj.options[selObj.selectedIndex].value + "'");
}



var json_objects = new Object();

function getXMLHTTPinstance() {
	var xmlhttp = false;
	var userAgent = navigator.userAgent.toLowerCase() ;

	// IE Check supports ActiveX controls
	if (userAgent.indexOf("msie") != -1 && userAgent.indexOf("mac") == -1 && userAgent.indexOf("opera") == -1) {
		var version = navigator.appVersion.match(/MSIE (.\..)/)[1] ;
		if(version >= 5.5 ) {
			try {
				xmlhttp = new ActiveXObject("Msxml2.XMLHTTP");
			}
			catch (e) {
				try {
					xmlhttp = new ActiveXObject("Microsoft.XMLHTTP");
				}
				catch (E) {
					xmlhttp = false;
				}
			}
		}
	}

	if (!xmlhttp && typeof XMLHttpRequest!='undefined') {
		xmlhttp = new XMLHttpRequest();
	}
	return xmlhttp;
}

// NOW LOAD THE OBJECT..
var global_xmlhttp = getXMLHTTPinstance();

function http_fetch_sync(url,post_data) {
	global_xmlhttp = getXMLHTTPinstance();
	var method = 'GET';

	if(typeof(post_data) != 'undefined') method = 'POST';
	try {
		global_xmlhttp.open(method, url,false);
	}
	catch(e) {
		alert('message:'+e.message+":url:"+url);
	}
	if(method == 'POST') {
		global_xmlhttp.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
	}

	global_xmlhttp.send(post_data);

	if (SUGAR.util.isLoginPage(global_xmlhttp.responseText))
		return false;

	var args = {"responseText" : global_xmlhttp.responseText,
				"responseXML" : global_xmlhttp.responseXML,
				"request_id" : typeof(request_id) != "undefined" ? request_id : 0};
	return args;

}
// this is a GET unless post_data is defined

function http_fetch_async(url,callback,request_id,post_data) {
	var method = 'GET';
	if(typeof(post_data) != 'undefined') {
		method = 'POST';
	}

	try {
		global_xmlhttp.open(method, url,true);
	}
	catch(e) {
		alert('message:'+e.message+":url:"+url);
	}
	if(method == 'POST') {
		global_xmlhttp.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
	}
	global_xmlhttp.onreadystatechange = function() {
		if(global_xmlhttp.readyState==4) {
			if(global_xmlhttp.status == 200) {
				if (SUGAR.util.isLoginPage(global_xmlhttp.responseText))
					return false;
				var args = {"responseText" : global_xmlhttp.responseText,
							"responseXML" : global_xmlhttp.responseXML,
							"request_id" : request_id };
				callback.call(document,args);
			}
			else {
				alert("There was a problem retrieving the XML data:\n" + global_xmlhttp.statusText);
			}
		}
	}
	global_xmlhttp.send(post_data);
}

function call_json_method(module,action,vars,variable_name,callback) {
	global_xmlhttp.open("GET", "index.php?entryPoint=json&module="+module+"&action="+action+"&"+vars,true);
	global_xmlhttp.onreadystatechange=
	function() {
		if(global_xmlhttp.readyState==4) {
			if(global_xmlhttp.status == 200) {
				// cn: bug 12274 - pass through JSON.parse() to remove security envelope
				json_objects[variable_name] = JSON.parse(global_xmlhttp.responseText);

				// cn: bug 12274 - safe from CSRF, render response as expected
				var respText = JSON.parseNoSecurity(global_xmlhttp.responseText);
				var args = {responseText:respText, responseXML:global_xmlhttp.responseXML};
				callback.call(document, args);
			}
			else {
				alert("There was a problem retrieving the XML data:\n" + global_xmlhttp.statusText);
			}
		}
	}
	global_xmlhttp.send(null);
}

function insert_at_cursor(field, value) {
 //ie:
	if (document.selection) {
		field.focus();
		sel = document.selection.createRange();
		sel.text = value;
	}
 //mozilla:
	else if(field.selectionStart || field.selectionStart == '0') {
		var start_pos = field.selectionStart;
		var end_pos = field.selectionEnd;
		field.value = field.value.substring(0, start_pos) + value + field.value.substring(end_pos, field.value.length);
	}
	else {
		field.value += value;
	}
}

function checkParentType(type,button) {
	if(button == null) {
		return;
	}
	if(typeof disabledModules != 'undefined' && typeof(disabledModules[type]) != 'undefined') {
		button.disabled='disabled';
	}
	else {
		button.disabled = false;
	}
}

function parseDate(input, format) {
	date = input.value;
	format = format.replace(/%/g, '');
	sep = format.charAt(1);
	yAt = format.indexOf('Y')
	// 1-1-06 or 1-12-06 or 1-1-2006 or 1-12-2006
	if(date.match(/^\d{1,2}[\/-]\d{1,2}[\/-]\d{2,4}$/) && yAt == 4) {
		if(date.match(/^\d{1}[\/-].*$/)) date = '0' + date;
		if(date.match(/^\d{2}[\/-]\d{1}[\/-].*$/)) date = date.substring(0,3) + '0' + date.substring(3,date.length);
		if(date.match(/^\d{2}[\/-]\d{2}[\/-]\d{2}$/)) date = date.substring(0,6) + '20' + date.substring(6,date.length);
	}
	// 06-11-1 or 06-1-1
	else if(date.match(/^\d{2,4}[\/-]\d{1,2}[\/-]\d{1,2}$/)) {
		if(date.match(/^\d{2}[\/-].*$/)) date = '20' + date;
		if(date.match(/^\d{4}[\/-]\d{1}[\/-].*$/)) date = date.substring(0,5) + '0' + date.substring(5,date.length);
		if(date.match(/^\d{4}[\/-]\d{2}[\/-]\d{1}$/)) date = date.substring(0,8) + '0' + date.substring(8,date.length);
	}
	else if(date.match(/^\d{4,8}$/)) { // digits only
		digits = 0;
		if(date.match(/^\d{8}$/)) digits = 8;// match for 8 digits
		else if(date.match(/\d{6}/)) digits = 6;// match for 5 digits
		else if(date.match(/\d{4}/)) digits = 4;// match for 5 digits
		else if(date.match(/\d{5}/)) digits = 5;// match for 5 digits

		switch(yAt) {
			case 0:
				switch(digits) {
					case 4: date = '20' + date.substring(0,2) + sep + '0' + date.substring(2, 3) + sep + '0' + date.substring(3,4); break;
					case 5: date = '20' + date.substring(0,2) + sep + date.substring(2, 4) + sep + '0' + date.substring(4,5); break;
					case 6: date = '20' + date.substring(0,2) + sep + date.substring(2, 4) + sep + date.substring(4,6); break;
					case 8: date = date.substring(0,4) + sep + date.substring(4, 6) + sep + date.substring(6,8); break;
				}
				break;
			case 2:
				switch(digits) {
					case 4: date = '0' + date.substring(0,1) + sep + '20' + date.substring(1, 3) + sep + '0' + date.substring(3,4); break;
					case 5: date = date.substring(0,2) + sep + '20' + date.substring(2, 4) + sep + '0' + date.substring(4,5); break;
					case 6: date = date.substring(0,2) + sep + '20' + date.substring(2, 4) + sep + date.substring(4,6); break;
					case 8: date = date.substring(0,2) + sep + date.substring(2, 6) + sep + date.substring(6,8); break;
				}
			case 4:
				switch(digits) {
					case 4: date = '0' + date.substring(0,1) + sep + '0' + date.substring(1, 2) + sep + '20' + date.substring(2,4); break;
					case 5: date = '0' + date.substring(0,1) + sep + date.substring(1, 3) + sep + '20' + date.substring(3,5); break;
					case 6: date = date.substring(0,2) + sep + date.substring(2, 4) + sep + '20' + date.substring(4,6); break;
					case 8: date = date.substring(0,2) + sep + date.substring(2, 4) + sep + date.substring(4,8); break;
				}
				break;
		}
	}
	date = date.replace(/[\/-]/g, sep);
	input.value = date;
}

// find obj's position
function findElementPos(obj) {
    var x = 0;
    var y = 0;
    if (obj.offsetParent) {
      while (obj.offsetParent) {
        x += obj.offsetLeft;
        y += obj.offsetTop;
        obj = obj.offsetParent;
      }
    }//if offsetParent exists
    else if (obj.x && obj.y) {
      y += obj.y
      x += obj.x
    }
	return new coordinate(x, y);
}//findElementPos


// get dimensions of the browser window
function getClientDim() {
	var nwX, nwY, seX, seY;
	if (self.pageYOffset) // all except Explorer
	{
	  nwX = self.pageXOffset;
	  seX = self.innerWidth + nwX;
	  nwY = self.pageYOffset;
	  seY = self.innerHeight + nwY;
	}
	else if (document.documentElement && document.documentElement.scrollTop) // Explorer 6 Strict
	{
	  nwX = document.documentElement.scrollLeft;
	  seX = document.documentElement.clientWidth + nwX;
	  nwY = document.documentElement.scrollTop;
	  seY = document.documentElement.clientHeight + nwY;
	}
	else if (document.body) // all other Explorers
	{
	  nwX = document.body.scrollLeft;
	  seX = document.body.clientWidth + nwX;
	  nwY = document.body.scrollTop;
	  seY = document.body.clientHeight + nwY;
	}
	return {'nw' : new coordinate(nwX, nwY), 'se' : new coordinate(seX, seY)};
}

/**
* stop propagation on events
**/
function freezeEvent(e) {
	if(e) {
	  if (e.preventDefault) e.preventDefault();
	  e.returnValue = false;
	  e.cancelBubble = true;
	  if (e.stopPropagation) e.stopPropagation();
	  return false;
	}
}


/**
 * coordinate class
 **/
function coordinate(_x, _y) {
  var x = _x;
  var y = _y;
  this.add = add;
  this.sub = sub;
  this.x = x;
  this.y = y;

  function add(rh) {
    return new position(this.x + rh.x, this.y + rh.y);
  }

  function sub(rh) {
    return new position(this.x + rh.x, this.y + rh.y);
  }
}

// sends theForm via AJAX and fills in the theDiv
function sendAndRetrieve(theForm, theDiv, loadingStr) {
	function success(data) {
		document.getElementById(theDiv).innerHTML = data.responseText;
		ajaxStatus.hideStatus();
	}
	if(typeof loadingStr == 'undefined') SUGAR.language.get('app_strings', 'LBL_LOADING');
	ajaxStatus.showStatus(loadingStr);
	YAHOO.util.Connect.setForm(theForm);
	var cObj = YAHOO.util.Connect.asyncRequest('POST', 'index.php', {success: success, failure: success});
	return false;
}

//save the form and redirect
function sendAndRedirect(theForm, loadingStr, redirect_location) {
	function success(data) {
		if(redirect_location){
			location.href=redirect_location;
		}
		ajaxStatus.hideStatus();
	}
	if(typeof loadingStr == 'undefined') SUGAR.language.get('app_strings', 'LBL_LOADING');
	ajaxStatus.showStatus(loadingStr);
	YAHOO.util.Connect.setForm(theForm);
	var cObj = YAHOO.util.Connect.asyncRequest('POST', 'index.php', {success: success, failure: success});
	return false;
}

function saveForm(theForm, theDiv, loadingStr) {
	if(check_form(theForm)){
		for(i = 0; i < ajaxFormArray.length; i++){
			if(ajaxFormArray[i] == theForm){
				ajaxFormArray.splice(i, 1);
			}
		}
		return sendAndRetrieve(theForm, loadingStr, theDiv);
	}
	else
		return false;
}

// Builds a "snapshot" of the form, so we can use it to see if someone has changed it.
function snapshotForm(theForm) {
    var snapshotTxt = '';
    var elemList = theForm.elements;
    var elem;
    var elemType;

    for( var i = 0; i < elemList.length ; i++ ) {
        elem = elemList[i];
        if ( typeof(elem.type) == 'undefined' ) {
            continue;
        }

        elemType = elem.type.toLowerCase();

        snapshotTxt = snapshotTxt + elem.name;

        if ( elemType == 'text' || elemType == 'textarea' || elemType == 'password' ) {
            snapshotTxt = snapshotTxt + elem.value;
        }
        else if ( elemType == 'select' || elemType == 'select-one' || elemType == 'select-multiple' ) {
            var optionList = elem.options;
            for ( var ii = 0 ; ii < optionList.length ; ii++ ) {
                if ( optionList[ii].selected ) {
                    snapshotTxt = snapshotTxt + optionList[ii].value;
                }
            }
        }
        else if ( elemType == 'radio' || elemType == 'checkbox' ) {
            if ( elem.selected ) {
                snapshotTxt = snapshotTxt + 'checked';
            }
        }
        else if ( elemType == 'hidden' ) {
            snapshotTxt = snapshotTxt + elem.value;
        }
    }

    return snapshotTxt;
}

function initEditView(theForm) {
    if (SUGAR.util.ajaxCallInProgress()) {
    	window.setTimeout(function(){initEditView(theForm);}, 100);
    	return;
    }
    // we don't need to check if the data is changed in the search popup
    if (theForm.id == 'popup_query_form') {
    	return;
    }
	if ( typeof editViewSnapshots == 'undefined' ) {
        editViewSnapshots = new Object();
    }
    if ( typeof SUGAR.loadedForms == 'undefined' ) {
    	SUGAR.loadedForms = new Object();
    }

    // console.log('DEBUG: Adding checks for '+theForm.id);
    if ( theForm == null || theForm.id == null ) {
        // Not much we can do here.
        return;
    }
    editViewSnapshots[theForm.id] = snapshotForm(theForm);
    SUGAR.loadedForms[theForm.id] = true;

}

function onUnloadEditView(theForm) {

	var dataHasChanged = false;

    if ( typeof editViewSnapshots == 'undefined' ) { 
        // No snapshots, move along
        return;
    }

    if ( typeof theForm == 'undefined' ) {
        // Need to check all editViewSnapshots
        for ( var idx in editViewSnapshots ) {

            theForm = document.getElementById(idx);
            // console.log('DEBUG: Checking all forms '+theForm.id);
            if ( theForm == null
                 || typeof editViewSnapshots[theForm.id] == 'undefined'
                 || editViewSnapshots[theForm.id] == null
                 || !SUGAR.loadedForms[theForm.id]) {
                continue;
            }

            var snap = snapshotForm(theForm);
            if ( editViewSnapshots[theForm.id] != snap ) {
                dataHasChanged = true;
            }
        }
    } else {
        // Just need to check a single form for changes
		if ( editViewSnapshots == null  || typeof theForm.id == 'undefined' || typeof editViewSnapshots[theForm.id] == 'undefined' || editViewSnapshots[theForm.id] == null ) {
            return;
        }

        // console.log('DEBUG: Checking one form '+theForm.id);
        if ( editViewSnapshots[theForm.id] != snapshotForm(theForm) ) {
            // Data has changed.
        	dataHasChanged = true;
        }
    }

    if ( dataHasChanged == true ) {
    	return SUGAR.language.get('app_strings','WARN_UNSAVED_CHANGES');
    } else {
        return;
    }

}

function disableOnUnloadEditView(theForm) {
    // If you don't pass anything in, it disables all checking
    if ( typeof theForm == 'undefined' || typeof editViewSnapshots == 'undefined' || theForm == null || editViewSnapshots == null) {
        window.onbeforeunload = null;
        editViewSnapshots = null;

        // console.log('DEBUG: Disabling all edit view checks');

    } else {
        // Otherwise, it just disables it for this form
        if ( typeof(theForm.id) != 'undefined' && typeof(editViewSnapshots[theForm.id]) != 'undefined' ) {
            editViewSnapshots[theForm.id] = null;
        }

        // console.log('DEBUG : Disabling just checks for '+theForm.id);

    }
}

/*
* save some forms using an ajax call
* theForms - the ids of all of theh forms to save
* savingStr - the string to display when saving the form
* completeStr - the string to display when the form has been saved
*/
function saveForms( savingStr, completeStr) {
	index = 0;
	theForms = ajaxFormArray;
	function success(data) {
		var theForm = document.getElementById(ajaxFormArray[0]);
		document.getElementById('multiedit_'+theForm.id).innerHTML = data.responseText;
		var saveAllButton = document.getElementById('ajaxsaveall');
		ajaxFormArray.splice(index, 1);
		if(saveAllButton && ajaxFormArray.length <= 1){
    		saveAllButton.style.visibility = 'hidden';
    	}
		index++;
		if(index == theForms.length){
			ajaxStatus.showStatus(completeStr);
    		window.setTimeout('ajaxStatus.hideStatus();', 2000);
    		if(saveAllButton)
    			saveAllButton.style.visibility = 'hidden';
    	}


	}
	if(typeof savingStr == 'undefined') SUGAR.language.get('app_strings', 'LBL_LOADING');
	ajaxStatus.showStatus(savingStr);

	//loop through the forms saving each one
	for(i = 0; i < theForms.length; i++){
		var theForm = document.getElementById(theForms[i]);
		if(check_form(theForm.id)){
			theForm.action.value='AjaxFormSave';
			YAHOO.util.Connect.setForm(theForm);
			var cObj = YAHOO.util.Connect.asyncRequest('POST', 'index.php', {success: success, failure: success});
		}else{
			ajaxStatus.hideStatus();
		}
		lastSubmitTime = lastSubmitTime-2000;
	}
	return false;
}

// -- start sugarListView class
// js functions used for ListView
function sugarListView() {
}


sugarListView.prototype.confirm_action = function(del) {
	if (del == 1) {
		return confirm( SUGAR.language.get('app_strings', 'NTC_DELETE_CONFIRMATION_NUM') + sugarListView.get_num_selected()  + SUGAR.language.get('app_strings', 'NTC_DELETE_SELECTED_RECORDS'));
	}
	else {
		return confirm( SUGAR.language.get('app_strings', 'NTC_UPDATE_CONFIRMATION_NUM') + sugarListView.get_num_selected()  + SUGAR.language.get('app_strings', 'NTC_DELETE_SELECTED_RECORDS'));
	}

}
sugarListView.get_num_selected = function () {
	if(typeof document.MassUpdate != 'undefined') {
		the_form = document.MassUpdate;
		for(wp = 0; wp < the_form.elements.length; wp++) {
			if(typeof the_form.elements[wp].name != 'undefined' && the_form.elements[wp].name == 'selectCount[]') {
				return the_form.elements[wp].value;
			}
		}
	}
	return 0;

}
sugarListView.update_count = function(count, add) {
	if(typeof document.MassUpdate != 'undefined') {
		the_form = document.MassUpdate;
		for(wp = 0; wp < the_form.elements.length; wp++) {
			if(typeof the_form.elements[wp].name != 'undefined' && the_form.elements[wp].name == 'selectCount[]') {
				if(add)	{
					the_form.elements[wp].value = parseInt(the_form.elements[wp].value,10) + count;
					if (the_form.select_entire_list.value == 1 && the_form.show_plus.value) {
						the_form.elements[wp].value += '+';
					}
				} else {
					if (the_form.select_entire_list.value == 1 && the_form.show_plus.value) {
				        the_form.elements[wp].value = count + '+';
				    } else {
				        the_form.elements[wp].value = count;
				    }
				}
			}
		}
	}
}
sugarListView.prototype.use_external_mail_client = function(no_record_txt, module) {
	selected_records = sugarListView.get_checks_count();
	if(selected_records <1) {
		alert(no_record_txt);
        return false;
	}

    if (document.MassUpdate.select_entire_list.value == 1) {
		if (totalCount > 10) {
			alert(totalCountError);
			return;
		} // if
		select = false;
	}
	else if (document.MassUpdate.massall.checked == true)
		select = false;
	else
		select = true;
    sugarListView.get_checks();
    var ids = "";
    if(select) { // use selected items
		ids = document.MassUpdate.uid.value;
	}
	else { // use current page
		inputs = document.MassUpdate.elements;
		ar = new Array();
		for(i = 0; i < inputs.length; i++) {
			if(inputs[i].name == 'mass[]' && inputs[i].checked && typeof(inputs[i].value) != 'function') {
				ar.push(inputs[i].value);
			}
		}
		ids = ar.join(',');
	}
    YAHOO.util.Connect.asyncRequest("POST", "index.php?", {
        success: this.use_external_mail_client_callback
    }, SUGAR.util.paramsToUrl({
        module: "Emails",
        action: "Compose",
        listViewExternalClient: 1,
        action_module: module,
        uid: ids,
        to_pdf:1
    }));

	return false;
}

sugarListView.prototype.use_external_mail_client_callback = function(o)
{
    if (o.responseText)
        location.href = 'mailto:' + o.responseText;
}

sugarListView.prototype.send_form_for_emails = function(select, currentModule, action, no_record_txt,action_module,totalCount, totalCountError) {
	if (document.MassUpdate.select_entire_list.value == 1) {
		if (totalCount > 10) {
			alert(totalCountError);
			return;
		} // if
		select = false;
	}
	else if (document.MassUpdate.massall.checked == true)
		select = false;
	else
		select = true;

	sugarListView.get_checks();
	// create new form to post (can't access action property of MassUpdate form due to action input)
	var newForm = document.createElement('form');
	newForm.method = 'post';
	newForm.action = action;
	newForm.name = 'newForm';
	newForm.id = 'newForm';
	var uidTa = document.createElement('textarea');
	uidTa.name = 'uid';
	uidTa.style.display = 'none';

	if(select) { // use selected items
		uidTa.value = document.MassUpdate.uid.value;
	}
	else { // use current page
		inputs = document.MassUpdate.elements;
		ar = new Array();
		for(i = 0; i < inputs.length; i++) {
			if(inputs[i].name == 'mass[]' && inputs[i].checked && typeof(inputs[i].value) != 'function') {
				ar.push(inputs[i].value);
			}
		}
		uidTa.value = ar.join(',');
	}

	if(uidTa.value == '') {
		alert(no_record_txt);
		return false;
	}

	var selectedArray = uidTa.value.split(",");
	if(selectedArray.length > 10) {
		alert(totalCountError);
		return;
	} // if
	newForm.appendChild(uidTa);

	var moduleInput = document.createElement('input');
	moduleInput.name = 'module';
	moduleInput.type = 'hidden';
	moduleInput.value = currentModule;
	newForm.appendChild(moduleInput);

	var actionInput = document.createElement('input');
	actionInput.name = 'action';
	actionInput.type = 'hidden';
	actionInput.value = 'Compose';
	newForm.appendChild(actionInput);

	if (typeof action_module != 'undefined' && action_module!= '') {
		var actionModule = document.createElement('input');
		actionModule.name = 'action_module';
		actionModule.type = 'hidden';
		actionModule.value = action_module;
		newForm.appendChild(actionModule);
	}
	//return_info must follow this pattern."&return_module=Accounts&return_action=index"
	if (typeof return_info!= 'undefined' && return_info != '') {
		var params= return_info.split('&');
		if (params.length > 0) {
			for (var i=0;i< params.length;i++) {
				if (params[i].length > 0) {
					var param_nv=params[i].split('=');
					if (param_nv.length==2){
						returnModule = document.createElement('input');
						returnModule.name = param_nv[0];
						returnModule.type = 'hidden';
						returnModule.value = param_nv[1];
						newForm.appendChild(returnModule);
					}
				}
			}
		}
	}

	var isAjaxCall = document.createElement('input');
	isAjaxCall.name = 'ajaxCall';
	isAjaxCall.type = 'hidden';
	isAjaxCall.value = true;
	newForm.appendChild(isAjaxCall);

	var isListView = document.createElement('input');
	isListView.name = 'ListView';
	isListView.type = 'hidden';
	isListView.value = true;
	newForm.appendChild(isListView);

	var toPdf = document.createElement('input');
	toPdf.name = 'to_pdf';
	toPdf.type = 'hidden';
	toPdf.value = true;
	newForm.appendChild(toPdf);

	//Grab the Quick Compose package for the listview
    YAHOO.util.Connect.setForm(newForm);
    var callback =
	{
	  success: function(o) {
	      var resp = YAHOO.lang.JSON.parse(o.responseText);
	      var quickComposePackage = new Object();
	      quickComposePackage.composePackage = resp;
	      quickComposePackage.fullComposeUrl = 'index.php?module=Emails&action=Compose&ListView=true' +
	                                           '&uid=' + uidTa.value + '&action_module=' + action_module;

	      SUGAR.quickCompose.init(quickComposePackage);
	  }
	}

	YAHOO.util.Connect.asyncRequest('POST','index.php', callback,null);

	// awu Bug 18624: Fixing issue where a canceled Export and unselect of row will persist the uid field, clear the field
	document.MassUpdate.uid.value = '';

	return false;
}

sugarListView.prototype.send_form = function(select, currentModule, action, no_record_txt,action_module,return_info) {
	if (document.MassUpdate.select_entire_list.value == 1) {

		if(sugarListView.get_checks_count() < 1) {
		   alert(no_record_txt);
		   return false;
		}

		var href = action;
		if ( action.indexOf('?') != -1 )
			href += '&module=' + currentModule;
		else
			href += '?module=' + currentModule;

		if (return_info)
			href += return_info;
        var newForm = document.createElement('form');
        newForm.method = 'post';
        newForm.action = href;
        newForm.name = 'newForm';
        newForm.id = 'newForm';
        var postTa = document.createElement('textarea');
        postTa.name = 'current_post';
        postTa.value = document.MassUpdate.current_query_by_page.value;
        postTa.style.display = 'none';
        newForm.appendChild(postTa);
        document.MassUpdate.parentNode.appendChild(newForm);
        newForm.submit();
		return;
	}
	else if (document.MassUpdate.massall.checked == true)
		select = false;
	else
		select = true;

	sugarListView.get_checks();
	// create new form to post (can't access action property of MassUpdate form due to action input)
	var newForm = document.createElement('form');
	newForm.method = 'post';
	newForm.action = action;
	newForm.name = 'newForm';
	newForm.id = 'newForm';
	var uidTa = document.createElement('textarea');
	uidTa.name = 'uid';
	uidTa.style.display = 'none';
	uidTa.value = document.MassUpdate.uid.value;

	if(uidTa.value == '') {
		alert(no_record_txt);
		return false;
	}

	newForm.appendChild(uidTa);

	var moduleInput = document.createElement('input');
	moduleInput.name = 'module';
	moduleInput.type = 'hidden';
	moduleInput.value = currentModule;
	newForm.appendChild(moduleInput);

	var actionInput = document.createElement('input');
	actionInput.name = 'action';
	actionInput.type = 'hidden';
	actionInput.value = 'index';
	newForm.appendChild(actionInput);

	if (typeof action_module != 'undefined' && action_module!= '') {
		var actionModule = document.createElement('input');
		actionModule.name = 'action_module';
		actionModule.type = 'hidden';
		actionModule.value = action_module;
		newForm.appendChild(actionModule);
	}
	//return_info must follow this pattern."&return_module=Accounts&return_action=index"
	if (typeof return_info!= 'undefined' && return_info != '') {
		var params= return_info.split('&');
		if (params.length > 0) {
			for (var i=0;i< params.length;i++) {
				if (params[i].length > 0) {
					var param_nv=params[i].split('=');
					if (param_nv.length==2){
						returnModule = document.createElement('input');
						returnModule.name = param_nv[0];
						returnModule.type = 'hidden';
						returnModule.value = param_nv[1];
						newForm.appendChild(returnModule);
					}
				}
			}
		}
	}

	document.MassUpdate.parentNode.appendChild(newForm);

	newForm.submit();
	// awu Bug 18624: Fixing issue where a canceled Export and unselect of row will persist the uid field, clear the field
	document.MassUpdate.uid.value = '';

	return false;
}
//return a count of checked row.
sugarListView.get_checks_count = function() {
	ar = new Array();

	if(document.MassUpdate.uid.value != '') {
		oldUids = document.MassUpdate.uid.value.split(',');
		for(uid in oldUids) {
		    if(typeof(oldUids[uid]) != 'function') {
		       ar[oldUids[uid]] = 1;
		    }
		}
	}
	// build associated array of uids, associated array ensures uniqueness
	inputs = document.MassUpdate.elements;
	for(i = 0; i < inputs.length; i++) {
		if(inputs[i].name == 'mass[]') {
			ar[inputs[i].value]	= (inputs[i].checked) ? 1 : 0; // 0 of it is unchecked
	    }
	}

	// build regular array of uids
	uids = new Array();
	for(i in ar) {
		if((typeof(ar[i]) != 'function') && ar[i] == 1) {
		   uids.push(i);
		}
	}

	return uids.length;
}

// saves the checks on the current page into the uid textarea
sugarListView.get_checks = function() {
	ar = new Array();

	if(document.MassUpdate.uid.value != '') {
		oldUids = document.MassUpdate.uid.value.split(',');
		for(uid in oldUids) {
		    if(typeof(oldUids[uid]) != 'function') {
		       ar[oldUids[uid]] = 1;
		    }
		}
	}

	// build associated array of uids, associated array ensures uniqueness
	inputs = document.MassUpdate.elements;
	for(i = 0; i < inputs.length; i++) {
		if(inputs[i].name == 'mass[]') {
			ar[inputs[i].value]	= (inputs[i].checked) ? 1 : 0; // 0 of it is unchecked
		}
	}

	// build regular array of uids
	uids = new Array();
	for(i in ar) {
		if(typeof(ar[i]) != 'function' && ar[i] == 1) {
		   uids.push(i);
		}
	}

	document.MassUpdate.uid.value = uids.join(',');

	if(uids.length == 0) return false; // return false if no checks to get
	return true; // there are saved checks
}

sugarListView.prototype.order_checks = function(order,orderBy,moduleString){
	checks = sugarListView.get_checks();
	eval('document.MassUpdate.' + moduleString + '.value = orderBy');
	document.MassUpdate.lvso.value = order;
	if(typeof document.MassUpdate.massupdate != 'undefined') {
	   document.MassUpdate.massupdate.value = 'false';
	}

	//we must first clear the action of massupdate, change it to index
   document.MassUpdate.action.value = document.MassUpdate.return_action.value;
   document.MassUpdate.return_module.value='';
   document.MassUpdate.return_action.value='';
   document.MassUpdate.submit();

	return !checks;
}
sugarListView.prototype.save_checks = function(offset, moduleString) {
	checks = sugarListView.get_checks();
	eval('document.MassUpdate.' + moduleString + '.value = offset');

	if(typeof document.MassUpdate.massupdate != 'undefined') {
	   document.MassUpdate.massupdate.value = 'false';
	}

	//we must first clear the action of massupdate, change it to index
       document.MassUpdate.action.value = document.MassUpdate.return_action.value;
       document.MassUpdate.return_module.value='';
       document.MassUpdate.return_action.value='';
	   document.MassUpdate.submit();


	return !checks;
}

sugarListView.prototype.check_item = function(cb, form) {
	if(cb.checked) {
		sugarListView.update_count(1, true);
	}else{
		sugarListView.update_count(-1, true);
		if(typeof form != 'undefined' && form != null) {
			sugarListView.prototype.updateUid(cb, form);
		}
	}
}

/**#28000, remove the  unselect record id from MassUpdate.uid **/
sugarListView.prototype.updateUid = function(cb  , form){
    if(form.name == 'MassUpdate' && form.uid && form.uid.value && cb.value && form.uid.value.indexOf(cb.value) != -1){
        if(form.uid.value.indexOf(','+cb.value)!= -1){
            form.uid.value = form.uid.value.replace(','+cb.value , '');
        }else if(form.uid.value.indexOf(cb.value + ',')!= -1){
            form.uid.value = form.uid.value.replace(cb.value + ',' , '');
        }else if(form.uid.value.indexOf(cb.value)!= -1){
            form.uid.value = form.uid.value.replace(cb.value  , '');
        }
    }
}

sugarListView.prototype.check_entire_list = function(form, field, value, list_count) {
	// count number of items
	count = 0;
	document.MassUpdate.massall.checked = true;
	document.MassUpdate.massall.disabled = true;

	for (i = 0; i < form.elements.length; i++) {
		if(form.elements[i].name == field && form.elements[i].disabled == false) {
			if(form.elements[i].checked != value) count++;
				form.elements[i].checked = value;
				form.elements[i].disabled = true;
		}
	}
	document.MassUpdate.select_entire_list.value = 1;
	//if(value)
	sugarListView.update_count(list_count, false);
	//else sugarListView.update_count(-1 * count, true);
}

sugarListView.prototype.check_all = function(form, field, value, pageTotal) {
	// count number of items
	count = 0;
	document.MassUpdate.massall.checked = value;
	if (document.MassUpdate.select_entire_list &&
		document.MassUpdate.select_entire_list.value == 1)
		document.MassUpdate.massall.disabled = true;
	else
		document.MassUpdate.massall.disabled = false;

	for (i = 0; i < form.elements.length; i++) {
		if(form.elements[i].name == field && !(form.elements[i].disabled == true && form.elements[i].checked == false)) {
			form.elements[i].disabled = false;

			if(form.elements[i].checked != value)
				count++;
			form.elements[i].checked = value;
			if(!value){
				sugarListView.prototype.updateUid(form.elements[i], form);
			}
		}
	}
	if (pageTotal >= 0)
		sugarListView.update_count(pageTotal);
 	else if(value)
		sugarListView.update_count(count, true);
	else
		sugarListView.update_count(-1 * count, true);
}
sugarListView.check_all = sugarListView.prototype.check_all;
sugarListView.confirm_action = sugarListView.prototype.confirm_action;

sugarListView.prototype.check_boxes = function() {
	var inputsCount = 0;
	var checkedCount = 0;
	var existing_onload = window.onload;
	var theForm = document.MassUpdate;
	inputs_array = theForm.elements;

	if(typeof theForm.uid.value != 'undefined' && theForm.uid.value != "") {
		checked_items = theForm.uid.value.split(",");
		if (theForm.select_entire_list.value == 1)
			document.MassUpdate.massall.disabled = true;

		for(wp = 0 ; wp < inputs_array.length; wp++) {
			if(inputs_array[wp].name == "mass[]") {
				inputsCount++;
				if (theForm.select_entire_list.value == 1) {
					inputs_array[wp].checked = true;
					inputs_array[wp].disabled = true;
					checkedCount++;
				}
				else {
					for(i in checked_items) {
						if(inputs_array[wp].value == checked_items[i]) {
							checkedCount++;
							inputs_array[wp].checked = true;
						}
					}
				}
			}
		}
		if (theForm.select_entire_list.value == 0)
			sugarListView.update_count(checked_items.length);
		else
			sugarListView.update_count(0, true);

	}
	else {
		for(wp = 0 ; wp < inputs_array.length; wp++) {
			if(inputs_array[wp].name == "mass[]") {
				inputs_array[wp].checked = false;
				inputs_array[wp].disabled = false;
			}
		}
		if (document.MassUpdate.massall) {
			document.MassUpdate.massall.checked = false;
			document.MassUpdate.massall.disabled = false;
		}
		sugarListView.update_count(0)
	}
	if(checkedCount > 0 && checkedCount == inputsCount)
		document.MassUpdate.massall.checked = true;

}


/**
 * This function is used in Email Template Module's listview.
 * It will check whether the templates are used in Campaing->EmailMarketing.
 * If true, it will notify user.
 */
function check_used_email_templates() {
	var ids = document.MassUpdate.uid.value;
	var call_back = {
		success:function(r) {
			if(r.responseText != '') {
				if(!confirm(SUGAR.language.get('app_strings','NTC_TEMPLATES_IS_USED') + r.responseText)) {
					return false;
				}
			}
			document.MassUpdate.submit();
			return false;
		}
		};
	url = "index.php?module=EmailTemplates&action=CheckDeletable&from=ListView&to_pdf=1&records="+ids;
	YAHOO.util.Connect.asyncRequest('POST',url, call_back,null);

}

sugarListView.prototype.send_mass_update = function(mode, no_record_txt, del) {
	formValid = check_form('MassUpdate');
	if(!formValid && !del) return false;


	if (document.MassUpdate.select_entire_list &&
		document.MassUpdate.select_entire_list.value == 1)
		mode = 'entire';
	else
		mode = 'selected';

	var ar = new Array();

	switch(mode) {
		case 'selected':
			for(wp = 0; wp < document.MassUpdate.elements.length; wp++) {
				var reg_for_existing_uid = new RegExp('^'+RegExp.escape(document.MassUpdate.elements[wp].value)+'[\s]*,|,[\s]*'+RegExp.escape(document.MassUpdate.elements[wp].value)+'[\s]*,|,[\s]*'+RegExp.escape(document.MassUpdate.elements[wp].value)+'$|^'+RegExp.escape(document.MassUpdate.elements[wp].value)+'$');
				//when the uid is already in document.MassUpdate.uid.value, we should not add it to ar.
				if(typeof document.MassUpdate.elements[wp].name != 'undefined'
					&& document.MassUpdate.elements[wp].name == 'mass[]'
						&& document.MassUpdate.elements[wp].checked
						&& !reg_for_existing_uid.test(document.MassUpdate.uid.value)) {
							ar.push(document.MassUpdate.elements[wp].value);
				}
			}
			if(document.MassUpdate.uid.value != '') document.MassUpdate.uid.value += ',';
			document.MassUpdate.uid.value += ar.join(',');
			if(document.MassUpdate.uid.value == '') {
				alert(no_record_txt);
				return false;
			}
			if(typeof(current_admin_id)!='undefined' && document.MassUpdate.module!= 'undefined' && document.MassUpdate.module.value == 'Users' && (document.MassUpdate.is_admin.value!='' || document.MassUpdate.status.value!='')) {
				var reg_for_current_admin_id = new RegExp('^'+current_admin_id+'[\s]*,|,[\s]*'+current_admin_id+'[\s]*,|,[\s]*'+current_admin_id+'$|^'+current_admin_id+'$');
				if(reg_for_current_admin_id.test(document.MassUpdate.uid.value)) {
					//if current user is admin, we should not allow massupdate the user_type and status of himself
					alert(SUGAR.language.get('Users','LBL_LAST_ADMIN_NOTICE'));
					return false;
				}
			}
			break;
		case 'entire':
			var entireInput = document.createElement('input');
			entireInput.name = 'entire';
			entireInput.type = 'hidden';
			entireInput.value = 'index';
			document.MassUpdate.appendChild(entireInput);
			//confirm(no_record_txt);
			if(document.MassUpdate.module!= 'undefined' && document.MassUpdate.module.value == 'Users' && (document.MassUpdate.is_admin.value!='' || document.MassUpdate.status.value!='')) {
				alert(SUGAR.language.get('Users','LBL_LAST_ADMIN_NOTICE'));
				return false;
			}
			break;
	}

	if(!sugarListView.confirm_action(del))
		return false;

	if(del == 1) {
		var deleteInput = document.createElement('input');
		deleteInput.name = 'Delete';
		deleteInput.type = 'hidden';
		deleteInput.value = true;
		document.MassUpdate.appendChild(deleteInput);
		if(document.MassUpdate.module!= 'undefined' && document.MassUpdate.module.value == 'EmailTemplates') {
			check_used_email_templates();
			return false;
		}

	}

	document.MassUpdate.submit();
	return false;
}


sugarListView.prototype.clear_all = function() {
	document.MassUpdate.uid.value = '';
	document.MassUpdate.select_entire_list.value = 0;
	sugarListView.check_all(document.MassUpdate, 'mass[]', false);
	document.MassUpdate.massall.checked = false;
	document.MassUpdate.massall.disabled = false;
	sugarListView.update_count(0);
}

sListView = new sugarListView();
// -- end sugarListView class

// format and unformat numbers
function unformatNumber(n, num_grp_sep, dec_sep) {
	var x=unformatNumberNoParse(n, num_grp_sep, dec_sep);
	x=x.toString();
	if(x.length > 0) {
		return parseFloat(x);
	}
	return '';
}

function unformatNumberNoParse(n, num_grp_sep, dec_sep) {
	if(typeof num_grp_sep == 'undefined' || typeof dec_sep == 'undefined') return n;
	n = n ? n.toString() : '';
	if(n.length > 0) {
	
	    if(num_grp_sep != '')
	    {
	       num_grp_sep_re = new RegExp('\\'+num_grp_sep, 'g');
		   n = n.replace(num_grp_sep_re, '');
	    }
	    
		n = n.replace(dec_sep, '.');

        if(typeof CurrencySymbols != 'undefined') {
            // Need to strip out the currency symbols from the start.
            for ( var idx in CurrencySymbols ) {
                n = n.replace(CurrencySymbols[idx], '');
            }
        }
		return n;
	}
	return '';
}

// round parameter can be negative for decimal, precision has to be postive
function formatNumber(n, num_grp_sep, dec_sep, round, precision) {
  if(typeof num_grp_sep == 'undefined' || typeof dec_sep == 'undefined') return n;
  n = n ? n.toString() : '';
  if(n.split) n = n.split('.');
  else return n;

  if(n.length > 2) return n.join('.'); // that's not a num!
  // round
  if(typeof round != 'undefined') {
    if(round > 0 && n.length > 1) { // round to decimal
      n[1] = parseFloat('0.' + n[1]);
      n[1] = Math.round(n[1] * Math.pow(10, round)) / Math.pow(10, round);
      n[1] = n[1].toString().split('.')[1];
    }
    if(round <= 0) { // round to whole number
        n[0] = Math.round(parseInt(n[0],10) * Math.pow(10, round)) / Math.pow(10, round);
      n[1] = '';
    }
  }

  if(typeof precision != 'undefined' && precision >= 0) {
    if(n.length > 1 && typeof n[1] != 'undefined') n[1] = n[1].substring(0, precision); // cut off precision
	else n[1] = '';
    if(n[1].length < precision) {
      for(var wp = n[1].length; wp < precision; wp++) n[1] += '0';
    }
  }

  regex = /(\d+)(\d{3})/;
  while(num_grp_sep != '' && regex.test(n[0])) n[0] = n[0].toString().replace(regex, '$1' + num_grp_sep + '$2');
  return n[0] + (n.length > 1 && n[1] != '' ? dec_sep + n[1] : '');
}

// --- begin ajax status class
SUGAR.ajaxStatusClass = function() {};
SUGAR.ajaxStatusClass.prototype.statusDiv = null;
SUGAR.ajaxStatusClass.prototype.oldOnScroll = null;
SUGAR.ajaxStatusClass.prototype.shown = false; // state of the status window

// reposition the status div, top and centered
SUGAR.ajaxStatusClass.prototype.positionStatus = function() {
	this.statusDiv.style.top = document.body.scrollTop + 8 + 'px';
	statusDivRegion = YAHOO.util.Dom.getRegion(this.statusDiv);
	statusDivWidth = statusDivRegion.right - statusDivRegion.left;
	this.statusDiv.style.left = YAHOO.util.Dom.getViewportWidth() / 2 - statusDivWidth / 2 + 'px';
}

// private func, create the status div
SUGAR.ajaxStatusClass.prototype.createStatus = function(text) {
	statusDiv = document.createElement('div');
	statusDiv.className = 'dataLabel';
	statusDiv.style.background = '#ffffff';
	statusDiv.style.color = '#c60c30';
	statusDiv.style.position = 'absolute';
	statusDiv.style.opacity = .8;
	statusDiv.style.filter = 'alpha(opacity=80)';
	statusDiv.id = 'ajaxStatusDiv';
	document.body.appendChild(statusDiv);
	this.statusDiv = document.getElementById('ajaxStatusDiv');
}

// public - show the status div with text
SUGAR.ajaxStatusClass.prototype.showStatus = function(text) {
	if(!this.statusDiv) {
		this.createStatus(text);
	}
	else {
		this.statusDiv.style.display = '';
	}
	this.statusDiv.style.zIndex = 20;
	this.statusDiv.innerHTML = '&nbsp;<b>' + text + '</b>&nbsp;';
	this.positionStatus();
	if(!this.shown) {
		this.shown = true;
		this.statusDiv.style.display = '';
		if(window.onscroll) this.oldOnScroll = window.onscroll; // save onScroll
		window.onscroll = this.positionStatus;
	}
}

// public - hide it
SUGAR.ajaxStatusClass.prototype.hideStatus = function(text) {
	if(!this.shown) return;
	this.shown = false;
	if(this.oldOnScroll) window.onscroll = this.oldOnScroll;
	else window.onscroll = '';
	this.statusDiv.style.display = 'none';
}

SUGAR.ajaxStatusClass.prototype.flashStatus = function(text, time){
	this.showStatus(text);
	window.setTimeout('ajaxStatus.hideStatus();', time);
}


var ajaxStatus = new SUGAR.ajaxStatusClass();
// --- end ajax status class

/**
 * Unified Search Advanced - for global search
 */
SUGAR.unifiedSearchAdvanced = function() {
	var usa_div;
	var usa_img;
	var usa_open;
	var usa_content;
	var anim_open;
	var anim_close;

	return {
		init: function() {
			SUGAR.unifiedSearchAdvanced.usa_div = document.getElementById('unified_search_advanced_div');
			SUGAR.unifiedSearchAdvanced.usa_img = document.getElementById('unified_search_advanced_img');

			if(!SUGAR.unifiedSearchAdvanced.usa_div || !SUGAR.unifiedSearchAdvanced.usa_img) return;
			var attributes = { height: { to: 300 } };
            SUGAR.unifiedSearchAdvanced.anim_open = new YAHOO.util.Anim('unified_search_advanced_div', attributes );
			SUGAR.unifiedSearchAdvanced.anim_open.duration = 0.75;
			SUGAR.unifiedSearchAdvanced.anim_close = new YAHOO.util.Anim('unified_search_advanced_div', { height: {to: 0} } );
			SUGAR.unifiedSearchAdvanced.anim_close.duration = 0.75;
			//SUGAR.unifiedSearchAdvanced.anim_close.onComplete.subscribe(function() {SUGAR.unifiedSearchAdvanced.usa_div.style.display = 'none'});

			SUGAR.unifiedSearchAdvanced.usa_img._x = YAHOO.util.Dom.getX(SUGAR.unifiedSearchAdvanced.usa_img);
			SUGAR.unifiedSearchAdvanced.usa_img._y = YAHOO.util.Dom.getY(SUGAR.unifiedSearchAdvanced.usa_img);


			SUGAR.unifiedSearchAdvanced.usa_open = false;
			SUGAR.unifiedSearchAdvanced.usa_content = null;

		   YAHOO.util.Event.addListener('unified_search_advanced_img', 'click', SUGAR.unifiedSearchAdvanced.get_content);
		},

		get_content: function(e) 
		{
		    query_string = trim(document.getElementById('query_string').value);
		    if(query_string != '')
		    {
		    	window.location.href = 'index.php?module=Home&action=UnifiedSearch&query_string=' + query_string;
		    } else {
		        window.location.href = 'index.php?module=Home&action=UnifiedSearch&form_only=true';
		    }
	    },

		animate: function(data) {
			ajaxStatus.hideStatus();

			if(data) {
				SUGAR.unifiedSearchAdvanced.usa_content = data.responseText;
				SUGAR.unifiedSearchAdvanced.usa_div.innerHTML = SUGAR.unifiedSearchAdvanced.usa_content;
			}
			if(SUGAR.unifiedSearchAdvanced.usa_open) {
				document.UnifiedSearch.advanced.value = 'false';
				SUGAR.unifiedSearchAdvanced.anim_close.animate();
			}
			else {
				document.UnifiedSearch.advanced.value = 'true';
				SUGAR.unifiedSearchAdvanced.usa_div.style.display = '';
				YAHOO.util.Dom.setX(SUGAR.unifiedSearchAdvanced.usa_div, SUGAR.unifiedSearchAdvanced.usa_img._x - 90);
				YAHOO.util.Dom.setY(SUGAR.unifiedSearchAdvanced.usa_div, SUGAR.unifiedSearchAdvanced.usa_img._y + 15);
				SUGAR.unifiedSearchAdvanced.anim_open.animate();
			}
	      	SUGAR.unifiedSearchAdvanced.usa_open = !SUGAR.unifiedSearchAdvanced.usa_open;

			return false;
		},

		checkUsaAdvanced: function() {
			if(document.UnifiedSearch.advanced.value == 'true') {
				document.UnifiedSearchAdvanced.query_string.value = document.UnifiedSearch.query_string.value;
				document.UnifiedSearchAdvanced.submit();
				return false;
			}
			return true;
		}
};
}();
if(typeof YAHOO != 'undefined') YAHOO.util.Event.addListener(window, 'load', SUGAR.unifiedSearchAdvanced.init);


SUGAR.ui = {
	/**
	 * Toggles the header
	 */
	toggleHeader : function() {
		var h = document.getElementById('header');

		if(h != null) {
			if(h != null) {
				if(h.style.display == 'none') {
					h.style.display = '';
				} else {
					h.style.display = 'none';
				}
			}
		} else {
			alert(SUGAR.language.get("app_strings", "ERR_NO_HEADER_ID"));
		}
	}
};


/**
 * General Sugar Utils
 */
SUGAR.util = function () {
	var additionalDetailsCache;
	var additionalDetailsCalls;
	var additionalDetailsRpcCall;

	return {
		getAndRemove : function (el) {
			if (YAHOO && YAHOO.util && YAHOO.util.Dom)
				el = YAHOO.util.Dom.get(el);
			else if (typeof (el) == "string")
				el = document.getElementById(el);
			if (el && el.parentNode)
				el.parentNode.removeChild(el);

			return el;
		},
		paramsToUrl : function (params) {
			url = "";
			for (i in params) {
				url += i + "=" + params[i] + "&";
			}
			return url;
		},
	    evalScript:function(text){
			if (isSafari) {
				var waitUntilLoaded = function(){
					SUGAR.evalScript_waitCount--;
					if (SUGAR.evalScript_waitCount == 0) {
                      var headElem = document.getElementsByTagName('head')[0];
                      for ( var i = 0; i < SUGAR.evalScript_evalElem.length; i++) {
                        var tmpElem = document.createElement('script');
                        tmpElem.type = 'text/javascript';
                        tmpElem.text = SUGAR.evalScript_evalElem[i];
                        headElem.appendChild(tmpElem);
                      }
					}
				};

				var tmpElem = document.createElement('div');
				tmpElem.innerHTML = text;
				var results = tmpElem.getElementsByTagName('script');
				if (results == null) {
					// No scripts found, bail out
					return;
				}

				var headElem = document.getElementsByTagName('head')[0];
				var tmpElem = null;
				SUGAR.evalScript_waitCount = 0;
				SUGAR.evalScript_evalElem = new Array();
				for (var i = 0; i < results.length; i++) {
					if (typeof(results[i]) != 'object') {
						continue;
					};
					tmpElem = document.createElement('script');
					tmpElem.type = 'text/javascript';
					if (results[i].src != null && results[i].src != '') {
						tmpElem.src = results[i].src;
					} else {
                        // Need to defer execution of these scripts until the
                        // required javascript files are fully loaded
                        SUGAR.evalScript_evalElem[SUGAR.evalScript_evalElem.length] = results[i].text;
                        continue;
					}
					tmpElem.addEventListener('load', waitUntilLoaded);
					SUGAR.evalScript_waitCount++;
					headElem.appendChild(tmpElem);
				}
                // Add some code to handle pages without any external scripts
				SUGAR.evalScript_waitCount++;
                waitUntilLoaded();

				// Don't try and process things the IE way
				return;
			}

	        var objRegex = /<\s*script([^>]*)>((.|\s|\v|\0)*?)<\s*\/script\s*>/igm;
			var lastIndex = -1;
			var result =  objRegex.exec(text);
            while(result && result.index > lastIndex){
            	lastIndex = result.index
				try{
					var script = document.createElement('script');
                  	script.type= 'text/javascript';
                  	if(result[1].indexOf("src=") > -1){
						var srcRegex = /.*src=['"]([a-zA-Z0-9\&\/\.\?=:]*)['"].*/igm;
						var srcResult =  result[1].replace(srcRegex, '$1');
						script.src = srcResult;
                  	}else{
                  		script.text = result[2];
                  	}
                  	document.body.appendChild(script)
	              }
	              catch(e) {

                  }
                  result =  objRegex.exec(text);
			}
	    },
		/**
		 * Gets the sidebar object
		 * @return object pointer to the sidebar element
		 */
		getLeftColObj: function() {
			leftColObj = document.getElementById('leftCol');
			while(leftColObj.nodeName != 'TABLE') {
				leftColObj = leftColObj.firstChild;
			}
			leftColTable = leftColObj;
			leftColTd = leftColTable.getElementsByTagName('td')[0];
			leftColTdRegion = YAHOO.util.Dom.getRegion(leftColTd);
			leftColTd.style.width = (leftColTdRegion.right - leftColTdRegion.left) + 'px';

			return leftColTd;
		},
		/**
		 * Fills the shortcut menu placeholders w/ actual content
		 * Call this on load event
		 *
		 * @param shortcutContent Array array of content to fill in
		 */
		fillShortcuts: function(e, shortcutContent) {
			return ;
/*
            // don't do this if leftCol isn't available
            if (document.getElementById('leftCol') == undefined) { return; }

	    	spans = document.getElementById('leftCol').getElementsByTagName('span');
			hideCol = document.getElementById('HideMenu').getElementsByTagName('span');
			w = spans.length + 1;
			for(i in hideCol) {
				spans[w] = hideCol[i];
				w++;
			}
		    for(je in shortcutContent) {
		    	for(wp in spans) {
		    		if(typeof spans[wp].innerHTML != 'undefined' && spans[wp].innerHTML == ('wp_shortcut_fill_' + je)) {
		    			if(typeof spans[wp].parentNode.parentNode == 'object') {
		    				if(typeof spans[wp].parentNode.parentNode.onclick != 'undefined') {
		    					spans[wp].parentNode.parentNode.onclick = null;
		    				}
		    				// If the wp_shortcut span is contained by an A tag, replace the A with a DIV.
		    				if(spans[wp].parentNode.tagName == 'A' && !isIE) {
		    					var newDiv = document.createElement('DIV');
		    					var parentAnchor = spans[wp].parentNode;

		    					spans[wp].parentNode.parentNode.style.display = 'none';

		    					// Copy styles over to the new container div
		    					if(window.getComputedStyle) {
			    					var parentStyle = window.getComputedStyle(parentAnchor, '');
			    					for(var styleName in parentStyle) {
				    					if(typeof parentStyle[styleName] != 'function'
	   			    				    && styleName != 'display'
	   			    				    && styleName != 'borderWidth'
				    				    && styleName != 'visibility') {
				    				    	try {
						    					newDiv.style[styleName] = parentStyle[styleName];
						    				} catch(e) {
						    					// Catches .length and .parentRule, and others
						    				}
					    				}
				    				}
				    			}

			    				// Replace the A with the DIV
		    					newDiv.appendChild(spans[wp]);
		    					parentAnchor.parentNode.replaceChild(newDiv, parentAnchor);

		    					spans[wp].parentNode.parentNode.style.display = '';
		    				}
		    			}
			            spans[wp].innerHTML = shortcutContent[je]; // fill w/ content
			            if(spans[wp].style) spans[wp].style.display = '';
		    		}
		    	}
			}*/
		},
		/**
		 * Make an AJAX request.
		 *
		 * @param	url				string	resource to load
		 * @param	theDiv			string	id of element to insert loaded data into
		 * @param	postForm		string	if set, a POST request will be made to resource specified by url using the form named by postForm
		 * @param	callback		string	name of function to invoke after HTTP response is recieved
		 * @param	callbackParam	any		parameter to pass to callback when invoked
		 * @param	appendMode		bool	if true, HTTP response will be appended to the contents of theDiv, or else contents will be overriten.
		 */
	    retrieveAndFill: function(url, theDiv, postForm, callback, callbackParam, appendMode) {
			if(typeof theDiv == 'string') {
				try {
					theDiv = document.getElementById(theDiv);
				}
		        catch(e) {
					return;
				}
			}

			var success = function(data) {
				if (typeof theDiv != 'undefined' && theDiv != null)
				{
					try {
						if (typeof appendMode != 'undefined' && appendMode)
						{
							theDiv.innerHTML += data.responseText;
						}
						else
						{
							theDiv.innerHTML = data.responseText;
						}
					}
					catch (e) {
						return;
					}
				}
				if (typeof callback != 'undefined' && callback != null) callback(callbackParam);
		  	}

			if(typeof postForm == 'undefined' || postForm == null) {
				var cObj = YAHOO.util.Connect.asyncRequest('GET', url, {success: success, failure: success});
			}
			else {
				YAHOO.util.Connect.setForm(postForm);
				var cObj = YAHOO.util.Connect.asyncRequest('POST', url, {success: success, failure: success});
			}
		},
		checkMaxLength: function() { // modified from http://www.quirksmode.org/dom/maxlength.html
			var maxLength = this.getAttribute('maxlength');
			var currentLength = this.value.length;
			if (currentLength > maxLength) {
				this.value = this.value.substring(0, maxLength);
			}
			// not innerHTML
		},
		/**
		 * Adds maxlength attribute to textareas
		 */
		setMaxLength: function() { // modified from http://www.quirksmode.org/dom/maxlength.html
			var x = document.getElementsByTagName('textarea');
			for (var i=0;i<x.length;i++) {
				if (x[i].getAttribute('maxlength')) {
					x[i].onkeyup = x[i].onchange = SUGAR.util.checkMaxLength;
					x[i].onkeyup();
				}
			}
		},

		/**
		 * Retrieves additional details dynamically
		 */
		getAdditionalDetails: function(bean, id, spanId) {
			go = function() {
				oReturn = function(body, caption, width, theme) {
					var _refx = 25-width;
					return overlib(body, CAPTION, caption, STICKY, MOUSEOFF, 1000, WIDTH, width, CLOSETEXT, ('<img border=0 style="margin-left:2px; margin-right: 2px;" src=index.php?entryPoint=getImage&themeName='+SUGAR.themes.theme_name+'&imageName=close.gif>'), CLOSETITLE, SUGAR.language.get('app_strings','LBL_ADDITIONAL_DETAILS_CLOSE_TITLE'), CLOSECLICK, FGCLASS, 'olFgClass', CGCLASS, 'olCgClass', BGCLASS, 'olBgClass', TEXTFONTCLASS, 'olFontClass', CAPTIONFONTCLASS, 'olCapFontClass', CLOSEFONTCLASS, 'olCloseFontClass', REF, spanId, REFC, 'LL', REFX, _refx);
				}

				success = function(data) {
					eval(data.responseText);

					SUGAR.util.additionalDetailsCache[spanId] = new Array();
					SUGAR.util.additionalDetailsCache[spanId]['body'] = result['body'];
					SUGAR.util.additionalDetailsCache[spanId]['caption'] = result['caption'];
					SUGAR.util.additionalDetailsCache[spanId]['width'] = result['width'];
					SUGAR.util.additionalDetailsCache[spanId]['theme'] = result['theme'];
					ajaxStatus.hideStatus();
					return oReturn(SUGAR.util.additionalDetailsCache[spanId]['body'], SUGAR.util.additionalDetailsCache[spanId]['caption'], SUGAR.util.additionalDetailsCache[spanId]['width'], SUGAR.util.additionalDetailsCache[spanId]['theme']);
				}

				if(typeof SUGAR.util.additionalDetailsCache[spanId] != 'undefined')
					return oReturn(SUGAR.util.additionalDetailsCache[spanId]['body'], SUGAR.util.additionalDetailsCache[spanId]['caption'], SUGAR.util.additionalDetailsCache[spanId]['width'], SUGAR.util.additionalDetailsCache[spanId]['theme']);

				if(typeof SUGAR.util.additionalDetailsCalls[spanId] != 'undefined') // call already in progress
					return;
				ajaxStatus.showStatus(SUGAR.language.get('app_strings', 'LBL_LOADING'));
				url = 'index.php?to_pdf=1&module=Home&action=AdditionalDetailsRetrieve&bean=' + bean + '&id=' + id;
				SUGAR.util.additionalDetailsCalls[spanId] = YAHOO.util.Connect.asyncRequest('GET', url, {success: success, failure: success});

				return false;
			}
			SUGAR.util.additionalDetailsRpcCall = window.setTimeout('go()', 250);
		},
		clearAdditionalDetailsCall: function() {
			if(typeof SUGAR.util.additionalDetailsRpcCall == 'number') window.clearTimeout(SUGAR.util.additionalDetailsRpcCall);
		},
		/**
		 * A function that extends functionality from parent to child.
 		 */
		extend : function(subc, superc, overrides) {
			subc.prototype = new superc;	// set the superclass
			// overrides
			if (overrides) {
			    for (var i in overrides)	subc.prototype[i] = overrides[i];
			}
		},
		hrefURL : function(url) {
			if(SUGAR.isIE) {
				// IE needs special treatment since otherwise it would not pass Referer
				var trampoline = document.createElement('a');
				trampoline.href = url;
				document.body.appendChild(trampoline);
				trampoline.click();
				document.body.removeChild(trampoline);
			} else {
				document.location.href = url;
			}
		},

		openWindow : function(URL, windowName, windowFeatures) {
			if(SUGAR.isIE) {
				// IE needs special treatment since otherwise it would not pass Referer
				win = window.open('', windowName, windowFeatures);
				var trampoline = document.createElement('a');
				trampoline.href = URL;
				trampoline.target = windowName;
				document.body.appendChild(trampoline);
				trampoline.click();
				document.body.removeChild(trampoline);
			} else {
				win = window.open(URL, windowName, windowFeatures);
			}
			return win;
		}
	};
}(); // end util
SUGAR.util.additionalDetailsCache = new Array();
SUGAR.util.additionalDetailsCalls = new Array();
if(typeof YAHOO != 'undefined') YAHOO.util.Event.addListener(window, 'load', SUGAR.util.setMaxLength); // allow textareas to obey maxlength attrib

SUGAR.savedViews = function() {
	var selectedOrderBy;
	var selectedSortOrder;
	var displayColumns;
	var hideTabs;
	var columnsMeta; // meta data for the display columns

	return {
		setChooser: function() {

			var displayColumnsDef = new Array();
			var hideTabsDef = new Array();

		    var left_td = document.getElementById('display_tabs_td');
		    if(typeof left_td == 'undefined' || left_td == null) return; // abort!
		    var right_td = document.getElementById('hide_tabs_td');

		    var displayTabs = left_td.getElementsByTagName('select')[0];
		    var hideTabs = right_td.getElementsByTagName('select')[0];

			for(i = 0; i < displayTabs.options.length; i++) {
				displayColumnsDef.push(displayTabs.options[i].value);
			}

			if(typeof hideTabs != 'undefined') {
				for(i = 0; i < hideTabs.options.length; i++) {
			         hideTabsDef.push(hideTabs.options[i].value);
				}
			}
			if (!SUGAR.savedViews.clearColumns)
				document.getElementById('displayColumnsDef').value = displayColumnsDef.join('|');
			document.getElementById('hideTabsDef').value = hideTabsDef.join('|');
		},

		select: function(saved_search_select) {
			for(var wp = 0; wp < document.search_form.saved_search_select.options.length; wp++) {
				if(typeof document.search_form.saved_search_select.options[wp].value != 'undefined' &&
					document.search_form.saved_search_select.options[wp].value == saved_search_select) {
						document.search_form.saved_search_select.selectedIndex = wp;
						document.search_form.ss_delete.style.display = '';
						document.search_form.ss_update.style.display = '';
				}
			}
		},
		saved_search_action: function(action, delete_lang) {
			if(action == 'delete') {
				if(!confirm(delete_lang)) return;
			}
			if(action == 'save') {
				if(document.search_form.saved_search_name.value.replace(/^\s*|\s*$/g, '') == '') {
					alert(SUGAR.language.get('app_strings', 'LBL_SAVED_SEARCH_ERROR'));
					return;
				}
			}

			// This check is needed for the Activities module (Calls/Meetings/Tasks).
			if (document.search_form.saved_search_action)
			{
				document.search_form.saved_search_action.value = action;
				document.search_form.search_module.value = document.search_form.module.value;
				document.search_form.module.value = 'SavedSearch';
				// Bug 31922 - Make sure to specify that we want to hit the index view here of
				// the SavedSearch module, since the ListView doesn't have the logic to save the
				// search and redirect back
				document.search_form.action.value = 'index';
			}
			document.search_form.submit();
		},
		shortcut_select: function(selectBox, module) {
			//build url
			selecturl = 'index.php?module=SavedSearch&search_module=' + module + '&action=index&saved_search_select=' + selectBox.options[selectBox.selectedIndex].value
			//add searchFormTab to url if it is available.  This determines what tab to render
			if(typeof(document.getElementById('searchFormTab'))!='undefined'){
				selecturl = selecturl + '&searchFormTab=' + document.search_form.searchFormTab.value;
			}
			//add showSSDIV to url if it is available.  This determines whether saved search sub form should
			//be rendered open or not
			if(document.getElementById('showSSDIV') && typeof(document.getElementById('showSSDIV') !='undefined')){
				selecturl = selecturl + '&showSSDIV='+document.getElementById('showSSDIV').value;
			}
			//use created url to navigate
			document.location.href = selecturl;
		},
		handleForm: function() {
			SUGAR.tabChooser.movementCallback = function(left_side, right_side) {
				while(document.getElementById('orderBySelect').childNodes.length != 0) { // clear out order by options
					document.getElementById('orderBySelect').removeChild(document.getElementById('orderBySelect').lastChild);
				}

				var selectedIndex = 0;
				var nodeCount = -1; // need this because the counter i also includes "undefined" nodes
									// which was breaking Calls and Meetings

				for(i in left_side.childNodes) { // fill in order by options
					if(typeof left_side.childNodes[i].nodeName != 'undefined' &&
						left_side.childNodes[i].nodeName.toLowerCase() == 'option' &&
						typeof SUGAR.savedViews.columnsMeta[left_side.childNodes[i].value] != 'undefined' && // check if column is sortable
						typeof SUGAR.savedViews.columnsMeta[left_side.childNodes[i].value]['sortable'] == 'undefined' &&
						SUGAR.savedViews.columnsMeta[left_side.childNodes[i].value]['sortable'] != false) {
							nodeCount++;
							optionNode = document.createElement('option');
							optionNode.value = left_side.childNodes[i].value;
							optionNode.innerHTML = left_side.childNodes[i].innerHTML;
							document.getElementById('orderBySelect').appendChild(optionNode);
							if(optionNode.value == SUGAR.savedViews.selectedOrderBy)
								selectedIndex = nodeCount;
					}
				}
				// Firefox needs this to be set after all the option nodes are created.
				document.getElementById('orderBySelect').selectedIndex = selectedIndex;
			};
			SUGAR.tabChooser.movementCallback(document.getElementById('display_tabs_td').getElementsByTagName('select')[0]);

			// This check is needed for the Activities module (Calls/Meetings/Tasks).
			if (document.search_form.orderBy)
				document.search_form.orderBy.options.value = SUGAR.savedViews.selectedOrderBy;

			// handle direction
			if(SUGAR.savedViews.selectedSortOrder == 'DESC') document.getElementById('sort_order_desc_radio').checked = true;
			else document.getElementById('sort_order_asc_radio').checked = true;
		}
	};
}();

SUGAR.searchForm = function() {
	var url;
	return {
		// searchForm tab selector util
		searchFormSelect: function(view, previousView) {
			var module = view.split('|')[0];
			var theView = view.split('|')[1];
			// retrieve form
			var handleDisplay = function() { // hide other divs
				document.search_form.searchFormTab.value = theView;
				patt = module+"(.*)SearchForm$";
				divId=document.search_form.getElementsByTagName('div');
				// Hide all the search forms and retrive the name of the previous search tab (useful for the first load because previousView is empty)
				for (i=0;i<divId.length;i++){
					if(divId[i].id.match(module)==module){
						if(divId[i].id.match('SearchForm')=='SearchForm'){
	                        if(document.getElementById(divId[i].id).style.display == ''){
	                           previousTab=divId[i].id.match(patt)[1];
	                        }
	                        document.getElementById(divId[i].id).style.display = 'none';
	                    }
					}
				}
				// show the good search form.
				document.getElementById(module + theView + 'SearchForm').style.display = '';
                //if its not the first tab show there is a previous tab.
                if(previousView) {
                     thepreviousView=previousView.split('|')[1];
                 }
                 else{
                     thepreviousView=previousTab;
                 }
                 thepreviousView=thepreviousView.replace(/_search/, "");
                 // Process to retrieve the completed field from one tab to an other.
                 for(num in document.search_form.elements) {
                     if(document.search_form.elements[num]) {
                         el = document.search_form.elements[num];
                         pattern="^(.*)_"+thepreviousView+"$";
                         if(typeof el.type != 'undefined' && typeof el.name != 'undefined' && el.name.match(pattern)) {
                             advanced_input_name = el.name.match(pattern)[1]; // strip
                             advanced_input_name = advanced_input_name+"_"+theView.replace(/_search/, "");
                             if(typeof document.search_form[advanced_input_name] != 'undefined')  // if advanced input of same name exists
                                 SUGAR.searchForm.copyElement(advanced_input_name, el);
                         }
                     }
                 }
			}

			// if tab is not cached
			if(document.getElementById(module + theView + 'SearchForm').innerHTML == '') {
				ajaxStatus.showStatus(SUGAR.language.get('app_strings', 'LBL_LOADING'));
				var success = function(data) {
					document.getElementById(module + theView + 'SearchForm').innerHTML = data.responseText;

					SUGAR.util.evalScript(data.responseText);
					// pass script variables to global scope
					if(theView == 'saved_views') {
						if(typeof columnsMeta != 'undefined') SUGAR.savedViews.columnsMeta = columnsMeta;
						if(typeof selectedOrderBy != 'undefined') SUGAR.savedViews.selectedOrderBy = selectedOrderBy;
						if(typeof selectedSortOrder != 'undefined') SUGAR.savedViews.selectedSortOrder = selectedSortOrder;
					}

					handleDisplay();
					enableQS(true);
					ajaxStatus.hideStatus();
				}
				url = 	'index.php?module=' + module + '&action=index&search_form_only=true&to_pdf=true&search_form_view=' + theView;

				//check to see if tpl has been specified.  If so then pass location through url string
				var tpl ='';
				if(document.getElementById('search_tpl') !=null && typeof(document.getElementById('search_tpl')) != 'undefined'){
					tpl = document.getElementById('search_tpl').value;
					if(tpl != ''){url += '&search_tpl='+tpl;}
				}

				if(theView == 'saved_views') // handle the tab chooser
					url += '&displayColumns=' + SUGAR.savedViews.displayColumns + '&hideTabs=' + SUGAR.savedViews.hideTabs + '&orderBy=' + SUGAR.savedViews.selectedOrderBy + '&sortOrder=' + SUGAR.savedViews.selectedSortOrder;

				var cObj = YAHOO.util.Connect.asyncRequest('GET', url, {success: success, failure: success});
			}
			else { // that form already retrieved
				handleDisplay();
			}
		},

		// copies one input to another
		copyElement: function(inputName, copyFromElement) {
			switch(copyFromElement.type) {
				case 'select-one':
				case 'text':
					document.search_form[inputName].value = copyFromElement.value;
					break;
			}
		},
        // This function is here to clear the form, instead of "resubmitting it
		clear_form: function(form) {
            var elemList = form.elements;
            var elem;
            var elemType;

            for( var i = 0; i < elemList.length ; i++ ) {
                elem = elemList[i];
                if ( typeof(elem.type) == 'undefined' ) {
                    continue;
                }

                elemType = elem.type.toLowerCase();

                if ( elemType == 'text' || elemType == 'textarea' || elemType == 'password' ) {
                    elem.value = '';
                }
                else if ( elemType == 'select' || elemType == 'select-one' || elemType == 'select-multiple' ) {
                    // We have, what I hope, is a select box, time to unselect all options
                    var optionList = elem.options;
                    for ( var ii = 0 ; ii < optionList.length ; ii++ ) {
                        optionList[ii].selected = false;
                    }
                }
                else if ( elemType == 'radio' || elemType == 'checkbox' ) {
                    elem.checked = false;
                    elem.selected = false;
                }
                else if ( elemType == 'hidden' ) {
                    // We only want to reset the hidden values that link to the select boxes.
                    if ( ( elem.name.length > 3 && elem.name.substring(elem.name.length-3) == '_id' )
                         || ((elem.name.length > 9) && (elem.name.substring(elem.name.length - 9) == '_id_basic'))
                         || ( elem.name.length > 12 && elem.name.substring(elem.name.length-12) == '_id_advanced' ) ) {
                        elem.value = '';
                    }
                }
            }
			SUGAR.savedViews.clearColumns = true;
		}
	};
}();
// Code for the column/tab chooser used on homepage and in admin section
SUGAR.tabChooser = function () {
	var	object_refs = new Array();
	return {
			/* Describe certain transfers as invalid */
			frozenOptions: [],

			movementCallback: function(left_side, right_side) {},
			orderCallback: function(left_side, right_side) {},

			freezeOptions: function(left_name, right_name, target) {
				if(!SUGAR.tabChooser.frozenOptions) { SUGAR.tabChooser.frozenOptions = []; }
				if(!SUGAR.tabChooser.frozenOptions[left_name]) { SUGAR.tabChooser.frozenOptions[left_name] = []; }
				if(!SUGAR.tabChooser.frozenOptions[left_name][right_name]) { SUGAR.tabChooser.frozenOptions[left_name][right_name] = []; }
				if(typeof target == 'array') {
					for(var i in target) {
						SUGAR.tabChooser.frozenOptions[left_name][right_name][target[i]] = true;
					}
				} else {
					SUGAR.tabChooser.frozenOptions[left_name][right_name][target] = true;
				}
			},

			buildSelectHTML: function(info) {
				var text = "<select";

		        if(typeof (info['select']['size']) != 'undefined') {
		                text +=" size=\""+ info['select']['size'] +"\"";
		        }

		        if(typeof (info['select']['name']) != 'undefined') {
		                text +=" name=\""+ info['select']['name'] +"\"";
		        }

		        if(typeof (info['select']['style']) != 'undefined') {
		                text +=" style=\""+ info['select']['style'] +"\"";
		        }

		        if(typeof (info['select']['onchange']) != 'undefined') {
		                text +=" onChange=\""+ info['select']['onchange'] +"\"";
		        }

		        if(typeof (info['select']['multiple']) != 'undefined') {
		                text +=" multiple";
		        }
		        text +=">";

		        for(i=0; i<info['options'].length;i++) {
		                option = info['options'][i];
		                text += "<option value=\""+option['value']+"\" ";
		                if ( typeof (option['selected']) != 'undefined' && option['selected']== true) {
		                        text += "SELECTED";
		                }
		                text += ">"+option['text']+"</option>";
		        }
		        text += "</select>";
		        return text;
			},

			left_to_right: function(left_name, right_name, left_size, right_size) {
				SUGAR.savedViews.clearColumns = false;
			    var left_td = document.getElementById(left_name+'_td');
			    var right_td = document.getElementById(right_name+'_td');

			    var display_columns_ref = left_td.getElementsByTagName('select')[0];
			    var hidden_columns_ref = right_td.getElementsByTagName('select')[0];

			    var selected_left = new Array();
			    var notselected_left = new Array();
			    var notselected_right = new Array();

			    var left_array = new Array();

			    var frozen_options = SUGAR.tabChooser.frozenOptions;
			    frozen_options = frozen_options && frozen_options[left_name] && frozen_options[left_name][right_name]?frozen_options[left_name][right_name]:[];

			        // determine which options are selected in left
			    for (i=0; i < display_columns_ref.options.length; i++)
			    {
			        if ( display_columns_ref.options[i].selected == true && !frozen_options[display_columns_ref.options[i].value])
			        {
			            selected_left[selected_left.length] = {text: display_columns_ref.options[i].text, value: display_columns_ref.options[i].value};
			        }
			        else
			        {
			            notselected_left[notselected_left.length] = {text: display_columns_ref.options[i].text, value: display_columns_ref.options[i].value};
			        }

			    }

			    for (i=0; i < hidden_columns_ref.options.length; i++)
			    {
			        notselected_right[notselected_right.length] = {text:hidden_columns_ref.options[i].text, value:hidden_columns_ref.options[i].value};

			    }

			    var left_select_html_info = new Object();
			    var left_options = new Array();
			    var left_select = new Object();

			    left_select['name'] = left_name+'[]';
			    left_select['id'] = left_name;
			    left_select['size'] = left_size;
			    left_select['multiple'] = 'true';

			    var right_select_html_info = new Object();
			    var right_options = new Array();
			    var right_select = new Object();

			    right_select['name'] = right_name+'[]';
			    right_select['id'] = right_name;
			    right_select['size'] = right_size;
			    right_select['multiple'] = 'true';

			    for (i = 0; i < notselected_right.length; i++) {
			        right_options[right_options.length] = notselected_right[i];
			    }

			    for (i = 0; i < selected_left.length; i++) {
			        right_options[right_options.length] = selected_left[i];
			    }
			    for (i = 0; i < notselected_left.length; i++) {
			        left_options[left_options.length] = notselected_left[i];
			    }
			    left_select_html_info['options'] = left_options;
			    left_select_html_info['select'] = left_select;
			    right_select_html_info['options'] = right_options;
			    right_select_html_info['select'] = right_select;
			    right_select_html_info['style'] = 'background: lightgrey';

			    var left_html = this.buildSelectHTML(left_select_html_info);
			    var right_html = this.buildSelectHTML(right_select_html_info);

			    left_td.innerHTML = left_html;
			    right_td.innerHTML = right_html;

				object_refs[left_name] = left_td.getElementsByTagName('select')[0];
				object_refs[right_name] = right_td.getElementsByTagName('select')[0];

				this.movementCallback(object_refs[left_name], object_refs[right_name]);

			    return false;
			},


			right_to_left: function(left_name, right_name, left_size, right_size, max_left) {
				SUGAR.savedViews.clearColumns = false;
			    var left_td = document.getElementById(left_name+'_td');
			    var right_td = document.getElementById(right_name+'_td');

			    var display_columns_ref = left_td.getElementsByTagName('select')[0];
			    var hidden_columns_ref = right_td.getElementsByTagName('select')[0];

			    var selected_right = new Array();
			    var notselected_right = new Array();
			    var notselected_left = new Array();

			    var frozen_options = SUGAR.tabChooser.frozenOptions;
			    frozen_options = SUGAR.tabChooser.frozenOptions && SUGAR.tabChooser.frozenOptions[right_name] && SUGAR.tabChooser.frozenOptions[right_name][left_name]?SUGAR.tabChooser.frozenOptions[right_name][left_name]:[];

			    for (i=0; i < hidden_columns_ref.options.length; i++)
			    {
			        if (hidden_columns_ref.options[i].selected == true && !frozen_options[hidden_columns_ref.options[i].value])
			        {
			            selected_right[selected_right.length] = {text:hidden_columns_ref.options[i].text, value:hidden_columns_ref.options[i].value};
			        }
			        else
			        {
			            notselected_right[notselected_right.length] = {text:hidden_columns_ref.options[i].text, value:hidden_columns_ref.options[i].value};
			        }

			    }

			    if(max_left != '' && (display_columns_ref.length + selected_right.length) > max_left) {
			    	alert('Maximum of ' + max_left + ' columns can be displayed.');
					return;
			    }

			    for (i=0; i < display_columns_ref.options.length; i++)
			    {
			        notselected_left[notselected_left.length] = {text:display_columns_ref.options[i].text, value:display_columns_ref.options[i].value};

			    }

			    var left_select_html_info = new Object();
			    var left_options = new Array();
			    var left_select = new Object();

			    left_select['name'] = left_name+'[]';
			    left_select['id'] = left_name;
			    left_select['multiple'] = 'true';
			    left_select['size'] = left_size;

			    var right_select_html_info = new Object();
			    var right_options = new Array();
			    var right_select = new Object();

			    right_select['name'] = right_name+ '[]';
			    right_select['id'] = right_name;
			    right_select['multiple'] = 'true';
			    right_select['size'] = right_size;

			    for (i = 0; i < notselected_left.length; i++) {
			        left_options[left_options.length] = notselected_left[i];
			    }

			    for (i = 0; i < selected_right.length; i++) {
			        left_options[left_options.length] = selected_right[i];
			    }
			    for (i = 0; i < notselected_right.length; i++) {
			        right_options[right_options.length] = notselected_right[i];
			    }
			    left_select_html_info['options'] = left_options;
			    left_select_html_info['select'] = left_select;
			    right_select_html_info['options'] = right_options;
			    right_select_html_info['select'] = right_select;
			    right_select_html_info['style'] = 'background: lightgrey';

			    var left_html = this.buildSelectHTML(left_select_html_info);
			    var right_html = this.buildSelectHTML(right_select_html_info);

			    left_td.innerHTML = left_html;
			    right_td.innerHTML = right_html;

				object_refs[left_name] = left_td.getElementsByTagName('select')[0];
				object_refs[right_name] = right_td.getElementsByTagName('select')[0];

				this.movementCallback(object_refs[left_name], object_refs[right_name]);

			    return false;
			},

			up: function(name, left_name, right_name) {
				SUGAR.savedViews.clearColumns = false;
			    var left_td = document.getElementById(left_name+'_td');
			    var right_td = document.getElementById(right_name+'_td');
			    var td = document.getElementById(name+'_td');
			    var obj = td.getElementsByTagName('select')[0];
			    obj = (typeof obj == "string") ? document.getElementById(obj) : obj;
			    if (obj.tagName.toLowerCase() != "select" && obj.length < 2)
			        return false;
			    var sel = new Array();

			    for (i=0; i<obj.length; i++) {
			        if (obj[i].selected == true) {
			            sel[sel.length] = i;
			        }
			    }
			    for (i=0; i < sel.length; i++) {
			        if (sel[i] != 0 && !obj[sel[i]-1].selected) {
			            var tmp = new Array(obj[sel[i]-1].text, obj[sel[i]-1].value);
			            obj[sel[i]-1].text = obj[sel[i]].text;
			            obj[sel[i]-1].value = obj[sel[i]].value;
			            obj[sel[i]].text = tmp[0];
			            obj[sel[i]].value = tmp[1];
			            obj[sel[i]-1].selected = true;
			            obj[sel[i]].selected = false;
			        }
			    }

				object_refs[left_name] = left_td.getElementsByTagName('select')[0];
				object_refs[right_name] = right_td.getElementsByTagName('select')[0];

				this.orderCallback(object_refs[left_name], object_refs[right_name]);

			    return false;
			},

			down: function(name, left_name, right_name) {
				SUGAR.savedViews.clearColumns = false;
			   	var left_td = document.getElementById(left_name+'_td');
			    var right_td = document.getElementById(right_name+'_td');
			    var td = document.getElementById(name+'_td');
			    var obj = td.getElementsByTagName('select')[0];
			    if (obj.tagName.toLowerCase() != "select" && obj.length < 2)
			        return false;
			    var sel = new Array();
			    for (i=obj.length-1; i>-1; i--) {
			        if (obj[i].selected == true) {
			            sel[sel.length] = i;
			        }
			    }
			    for (i=0; i < sel.length; i++) {
			        if (sel[i] != obj.length-1 && !obj[sel[i]+1].selected) {
			            var tmp = new Array(obj[sel[i]+1].text, obj[sel[i]+1].value);
			            obj[sel[i]+1].text = obj[sel[i]].text;
			            obj[sel[i]+1].value = obj[sel[i]].value;
			            obj[sel[i]].text = tmp[0];
			            obj[sel[i]].value = tmp[1];
			            obj[sel[i]+1].selected = true;
			            obj[sel[i]].selected = false;
			        }
			    }

				object_refs[left_name] = left_td.getElementsByTagName('select')[0];
				object_refs[right_name] = right_td.getElementsByTagName('select')[0];

				this.orderCallback(object_refs[left_name], object_refs[right_name]);

			    return false;
			}
		};
}(); // end tabChooser

SUGAR.language = function() {
    return {
        languages : new Array(),

        setLanguage: function(module, data) {
           if (!SUGAR.language.languages) {

           }
            SUGAR.language.languages[module] = data;
        },

        get: function(module, str) {
            if(typeof SUGAR.language.languages[module] == 'undefined' || typeof SUGAR.language.languages[module][str] == 'undefined')
            {
                return 'undefined';
            }
            return SUGAR.language.languages[module][str];
        },
        
        translate: function(module, str)
        {
            text = this.get(module, str);
            return text != 'undefined' ? text : this.get('app_strings', str);  	
        }
    }
}();

SUGAR.contextMenu = function() {
	return {
		objects: new Object(),
		objectTypes: new Object(),
		/**
		 * Registers a new object for the context menu.
		 * objectType - name of the type
		 * id - element id
		 * metaData - metaData to pass to the action function
		 **/
		registerObject: function(objectType, id, metaData) {
			SUGAR.contextMenu.objects[id] = new Object();
            SUGAR.contextMenu.objects[id] = {'objectType' : objectType, 'metaData' : metaData};
		},
		/**
		 * Registers a new object type
		 * name - name of the type
		 * menuItems - array of menu items
		 **/
		registerObjectType: function(name, menuItems) {
			SUGAR.contextMenu.objectTypes[name] = new Object();
			SUGAR.contextMenu.objectTypes[name] = {'menuItems' : menuItems, 'objects' : new Array()};
		},
		/**
		 * Determines which menu item was clicked
		 **/
		getListItemFromEventTarget: function(p_oNode) {
            var oLI;
            if(p_oNode.tagName == "LI") {
	            oLI = p_oNode;
            }
            else {
	            do {
	                if(p_oNode.tagName == "LI") {
	                    oLI = p_oNode;
	                    break;
	                }

	            } while((p_oNode = p_oNode.parentNode));
  	        }
            return oLI;
         },
         /**
          * handles movement within context menu
          **/
         onContextMenuMove: function() {
            var oNode = this.contextEventTarget;
            var bDisabled = (oNode.tagName == "UL");
            var i = this.getItemGroups()[0].length - 1;
            do {
                this.getItem(i).cfg.setProperty("disabled", bDisabled);
            }
            while(i--);
        },
        /**
         * handles clicks on a context menu ITEM
         **/
		onContextMenuItemClick: function(p_sType, p_aArguments, p_oItem) {
            var oLI = SUGAR.contextMenu.getListItemFromEventTarget(this.parent.contextEventTarget);
            id = this.parent.contextEventTarget.parentNode.id; // id of the target
            funct = eval(SUGAR.contextMenu.objectTypes[SUGAR.contextMenu.objects[id]['objectType']]['menuItems'][this.index]['action']);
            funct(this.parent.contextEventTarget, SUGAR.contextMenu.objects[id]['metaData']);
		},
		/**
		 * Initializes all context menus registered
		 **/
		init: function() {
			for(var i in SUGAR.contextMenu.objects) { // make a variable called objects in objectTypes containg references to all triggers
                if(typeof SUGAR.contextMenu.objectTypes[SUGAR.contextMenu.objects[i]['objectType']]['objects'] == 'undefined')
                    SUGAR.contextMenu.objectTypes[SUGAR.contextMenu.objects[i]['objectType']]['objects'] = new Array();
				SUGAR.contextMenu.objectTypes[SUGAR.contextMenu.objects[i]['objectType']]['objects'].push(document.getElementById(i));
			}
            // register the menus
			for(var i in SUGAR.contextMenu.objectTypes) {
	            var oContextMenu = new YAHOO.widget.ContextMenu(i, {'trigger': SUGAR.contextMenu.objectTypes[i]['objects']});
				var aMainMenuItems = SUGAR.contextMenu.objectTypes[i]['menuItems'];
	            var nMainMenuItems = aMainMenuItems.length;
	            var oMenuItem;
	            for(var j = 0; j < nMainMenuItems; j++) {
	                oMenuItem = new YAHOO.widget.ContextMenuItem(aMainMenuItems[j].text, { helptext: aMainMenuItems[j].helptext });
	                oMenuItem.clickEvent.subscribe(SUGAR.contextMenu.onContextMenuItemClick, oMenuItem, true);
	                oContextMenu.addItem(oMenuItem);
	            }
	            //  Add a "move" event handler to the context menu
	            oContextMenu.moveEvent.subscribe(SUGAR.contextMenu.onContextMenuMove, oContextMenu, true);
	            // Add a "keydown" event handler to the context menu
	            oContextMenu.keyDownEvent.subscribe(SUGAR.contextMenu.onContextMenuItemClick, oContextMenu, true);
	            // Render the context menu
	            oContextMenu.render(document.body);
	        }
		}
	};
}();

SUGAR.contextMenu.actions = function() {
	return {
		/**
		 * redirects to a new note with the clicked on object as the target
		 **/
		createNote: function(itemClicked, metaData) {
			loc = 'index.php?module=Notes&action=EditView';
			for(i in metaData) {
				if(i == 'notes_parent_type') loc += '&parent_type=' + metaData[i];
				else if(i != 'module' && i != 'parent_type') loc += '&' + i + '=' + metaData[i];
			}
			document.location = loc;
		},
		/**
		 * redirects to a new note with the clicked on object as the target
		 **/
		scheduleMeeting: function(itemClicked, metaData) {
			loc = 'index.php?module=Meetings&action=EditView';
			for(i in metaData) {
				if(i != 'module') loc += '&' + i + '=' + metaData[i];
			}
			document.location = loc;
		},
		/**
		 * redirects to a new note with the clicked on object as the target
		 **/
		scheduleCall: function(itemClicked, metaData) {
			loc = 'index.php?module=Calls&action=EditView';
			for(i in metaData) {
				if(i != 'module') loc += '&' + i + '=' + metaData[i];
			}
			document.location = loc;
		},
		/**
		 * redirects to a new contact with the clicked on object as the target
		 **/
		createContact: function(itemClicked, metaData) {
			loc = 'index.php?module=Contacts&action=EditView';
			for(i in metaData) {
				if(i != 'module') loc += '&' + i + '=' + metaData[i];
			}
			document.location = loc;
		},
		/**
		 * redirects to a new task with the clicked on object as the target
		 **/
		createTask: function(itemClicked, metaData) {
			loc = 'index.php?module=Tasks&action=EditView';
			for(i in metaData) {
				if(i != 'module') loc += '&' + i + '=' + metaData[i];
			}
			document.location = loc;
		},
		/**
		 * redirects to a new opportunity with the clicked on object as the target
		 **/
		createOpportunity: function(itemClicked, metaData) {
			loc = 'index.php?module=Opportunities&action=EditView';
			for(i in metaData) {
				if(i != 'module') loc += '&' + i + '=' + metaData[i];
			}
			document.location = loc;
		},
		/**
		 * redirects to a new opportunity with the clicked on object as the target
		 **/
		createCase: function(itemClicked, metaData) {
			loc = 'index.php?module=Cases&action=EditView';
			for(i in metaData) {
				if(i != 'module') loc += '&' + i + '=' + metaData[i];
			}
			document.location = loc;
		},
		/**
		 * handles add to favorites menu selection
		 **/
		addToFavorites: function(itemClicked, metaData) {
			success = function(data) {
			}
			var cObj = YAHOO.util.Connect.asyncRequest('GET', 'index.php?to_pdf=true&module=Home&action=AddToFavorites&target_id=' + metaData['id'] + '&target_module=' + metaData['module'], {success: success, failure: success});

		}
	};
}();
//if(typeof YAHOO != 'undefined') YAHOO.util.Event.addListener(window, 'load', SUGAR.contextMenu.init);

// initially from popup_parent_helper.js
var popup_request_data;
var close_popup;

function get_popup_request_data()
{
	return window.document.popup_request_data;
}

function get_close_popup()
{
	return window.document.close_popup;
}

function open_popup(module_name, width, height, initial_filter, close_popup, hide_clear_button, popup_request_data, popup_mode, create, metadata)
{
	if (typeof(popupCount) == "undefined" || popupCount == 0)
	   popupCount = 1;

	// set the variables that the popup will pull from
	window.document.popup_request_data = popup_request_data;
	window.document.close_popup = close_popup;
	
	//globally changing width and height of standard pop up window from 600 x 400 to 800 x 800 
	width = (width == 600) ? 800 : width;
	height = (height == 400) ? 800 : height;
	
	// launch the popup
	URL = 'index.php?'
		+ 'module=' + module_name
		+ '&action=Popup';

	if (initial_filter != '') {
		URL += '&query=true' + initial_filter;
		// Bug 41891 - Popup Window Name
		popupName = initial_filter.replace(/[^a-z_0-9]+/ig, '_');
		windowName = module_name + '_popup_window' + popupName;
	} else {
		windowName = module_name + '_popup_window' + popupCount;
	}
	popupCount++;

	if (hide_clear_button) {
		URL += '&hide_clear_button=true';
	}

	windowFeatures = 'width=' + width
		+ ',height=' + height
		+ ',resizable=1,scrollbars=1';

	if (popup_mode == '' && popup_mode == 'undefined') {
		popup_mode='single';
	}
	URL+='&mode='+popup_mode;
	if (create == '' && create == 'undefined') {
		create = 'false';
	}
	URL+='&create='+create;

	if (metadata != '' && metadata != 'undefined') {
		URL+='&metadata='+metadata;
	}

	win = SUGAR.util.openWindow(URL, windowName, windowFeatures);

	if(window.focus)
	{
		// put the focus on the popup if the browser supports the focus() method
		win.focus();
	}

	win.popupCount = popupCount;

	return win;
}

/**
 * The reply data must be a JSON array structured with the following information:
 *  1) form name to populate
 *  2) associative array of input names to values for populating the form
 */
var from_popup_return  = false;

function set_return_basic(popup_reply_data,filter)
{
	var form_name = popup_reply_data.form_name;
	var name_to_value_array = popup_reply_data.name_to_value_array;
	for (var the_key in name_to_value_array)
	{
		if(the_key == 'toJSON')
		{
			/* just ignore */
		}
		else if(the_key.match(filter))
		{
			var displayValue=name_to_value_array[the_key].replace(/&amp;/gi,'&').replace(/&lt;/gi,'<').replace(/&gt;/gi,'>').replace(/&#039;/gi,'\'').replace(/&quot;/gi,'"');;
			// begin andopes change: support for enum fields (SELECT)
			if(window.document.forms[form_name] && window.document.forms[form_name].elements[the_key]) {
				if(window.document.forms[form_name].elements[the_key].tagName == 'SELECT') {
					var selectField = window.document.forms[form_name].elements[the_key];
					for(var i = 0; i < selectField.options.length; i++) {
						if(selectField.options[i].text == displayValue) {
							selectField.options[i].selected = true;
							break;
						}
					}
				} else {
					window.document.forms[form_name].elements[the_key].value = displayValue;
				}
			}
			// end andopes change: support for enum fields (SELECT)
		}
	}
}

function set_return(popup_reply_data)
{
	from_popup_return = true;
	var form_name = popup_reply_data.form_name;
	var name_to_value_array = popup_reply_data.name_to_value_array;
	if(typeof name_to_value_array != 'undefined' && name_to_value_array['account_id'])
	{
		var label_str = '';
		var label_data_str = '';
		var current_label_data_str = '';
		for (var the_key in name_to_value_array)
		{
			if(the_key == 'toJSON')
			{
				/* just ignore */
			}
			else
			{
				var displayValue=name_to_value_array[the_key].replace(/&amp;/gi,'&').replace(/&lt;/gi,'<').replace(/&gt;/gi,'>').replace(/&#039;/gi,'\'').replace(/&quot;/gi,'"');
				if(window.document.forms[form_name] && document.getElementById(the_key+'_label') && !the_key.match(/account/)) {
					var data_label = document.getElementById(the_key+'_label').innerHTML.replace(/\n/gi,'');
					label_str += data_label + ' \n';
					label_data_str += data_label  + ' ' + displayValue + '\n';
					if(window.document.forms[form_name].elements[the_key]) {
						current_label_data_str += data_label + ' ' + window.document.forms[form_name].elements[the_key].value +'\n';
					}
				}
			}
		}
        if(label_data_str != label_str && current_label_data_str != label_str){
        	if(confirm(SUGAR.language.get('app_strings', 'NTC_OVERWRITE_ADDRESS_PHONE_CONFIRM') + '\n\n' + label_data_str))
			{
				set_return_basic(popup_reply_data,/\S/);
			}else{
				set_return_basic(popup_reply_data,/account/);
			}
		}else if(label_data_str != label_str && current_label_data_str == label_str){
			set_return_basic(popup_reply_data,/\S/);
		}else if(label_data_str == label_str){
			set_return_basic(popup_reply_data,/account/);
		}
	}else{
		set_return_basic(popup_reply_data,/\S/);
	}
}

function set_return_and_save(popup_reply_data)
{
	var form_name = popup_reply_data.form_name;
	var name_to_value_array = popup_reply_data.name_to_value_array;

	for (var the_key in name_to_value_array)
	{
		if(the_key == 'toJSON')
		{
			/* just ignore */
		}
		else
		{
			window.document.forms[form_name].elements[the_key].value = name_to_value_array[the_key];
		}
	}

	window.document.forms[form_name].return_module.value = window.document.forms[form_name].module.value;
	window.document.forms[form_name].return_action.value = 'DetailView';
	window.document.forms[form_name].return_id.value = window.document.forms[form_name].record.value;
	window.document.forms[form_name].action.value = 'Save';
	window.document.forms[form_name].submit();
}

/**
 * This is a helper function to construct the initial filter that can be
 * passed into the open_popup() function.  It assumes that there is an
 * account_id and account_name field in the given form_name to use to
 * construct the intial filter string.
 */
function get_initial_filter_by_account(form_name)
{
	var account_id = window.document.forms[form_name].account_id.value;
	var account_name = escape(window.document.forms[form_name].account_name.value);
	var initial_filter = "&account_id=" + account_id + "&account_name=" + account_name;

	return initial_filter;
}
// end code from popup_parent_helper.js

// begin code for address copy
/**
 * This is a function used by the Address widget that will fill
 * in the given array fields using the fromKey and toKey as a
 * prefix into the form objects HTML elements.
 *
 * @param form The HTML form object to parse
 * @param fromKey The prefix of elements to copy from
 * @param toKey The prefix of elements to copy into
 * @return boolean true if successful, false otherwise
 */
function copyAddress(form, fromKey, toKey) {

    var elems = new Array("address_street", "address_city", "address_state", "address_postalcode", "address_country");
    var checkbox = document.getElementById(toKey + "_checkbox");

    if(typeof checkbox != "undefined") {
        if(!checkbox.checked) {
		    for(x in elems) {
		        t = toKey + "_" + elems[x];
			    document.getElementById(t).removeAttribute('readonly');
		    }
        } else {
		    for(x in elems) {
			    f = fromKey + "_" + elems[x];
			    t = toKey + "_" + elems[x];

			    document.getElementById(t).value = document.getElementById(f).value;
			    document.getElementById(t).setAttribute('readonly', true);
		    }
	    }
    }
  	return true;
}
// end code for address copy

/**
 * This function is used in Email Template Module.
 * It will check whether the template is used in Campaing->EmailMarketing.
 * If true, it will notify user.
 */

function check_deletable_EmailTemplate() {
	id = document.getElementsByName('record')[0].value;
	currentForm = document.getElementById('form');
	var call_back = {
		success:function(r) {
			if(r.responseText == 'true') {
				if(!confirm(SUGAR.language.get('app_strings','NTC_TEMPLATE_IS_USED'))) {
					return false;
				}
			} else {
				if(!confirm(SUGAR.language.get('app_strings','NTC_DELETE_CONFIRMATION'))) {
					return false;
				}
			}
			currentForm.return_module.value='EmailTemplates';
			currentForm.return_action.value='ListView';
			currentForm.action.value='Delete';
			currentForm.submit();
		}
		};
	url = "index.php?module=EmailTemplates&action=CheckDeletable&from=DetailView&to_pdf=1&record="+id;
	YAHOO.util.Connect.asyncRequest('POST',url, call_back,null);
}

SUGAR.image = {
     remove_upload_imagefile : function(field_name) {
            var field=document.getElementById('remove_imagefile_' + field_name);
            field.value=1;

            //enable the file upload button.
            var field=document.getElementById( field_name);
            field.style.display="";

            //hide the image and remove button.
            var field=document.getElementById('img_' + field_name);
            field.style.display="none";
            var field=document.getElementById('bt_remove_' + field_name);
            field.style.display="none";

            if(document.getElementById(field_name + '_duplicate')) {
               var field = document.getElementById(field_name + '_duplicate');
               field.value = "";
            }
    },

    confirm_imagefile : function(field_name) {
            var field=document.getElementById(field_name);
            var filename=field.value;
            var fileExtension = filename.substring(filename.lastIndexOf(".")+1);
            fileExtension = fileExtension.toLowerCase();
            if (fileExtension == "jpg" || fileExtension == "jpeg"
                || fileExtension == "gif" || fileExtension == "png" || fileExtension == "bmp"){
                    //image file
                }
            else{
                field.value=null;
                alert(SUGAR.language.get('app_strings', 'LBL_UPLOAD_IMAGE_FILE_INVALID'));
            }
    },

    lightbox : function(image)
	{
        if (typeof(SUGAR.image.lighboxWindow) == "undefined")
			SUGAR.image.lighboxWindow = new YAHOO.widget.SimpleDialog('sugarImageViewer', {
	            type:'message',
	            modal:true,
	            id:'sugarMsgWindow',
	            close:true,
	            title:"Alert",
	            msg: "<img src='" + image + "'> </img>",
	            buttons: [ ]
	        });
		SUGAR.image.lighboxWindow.setBody("<img src='" + image + "'> </img>");
		SUGAR.image.lighboxWindow.render(document.body);
        SUGAR.image.lighboxWindow.show();
		SUGAR.image.lighboxWindow.center()
    }
}

SUGAR.util.isTouchScreen = function()
{
    // first check if we have forced use of the touch enhanced interface
    if ( Get_Cookie("touchscreen") == '1' ) {
        return true;
    }

    // next check if we should use the touch interface with our device
    if ( (navigator.userAgent.match(/iPad/i) != null) ) {
        return true;
    }

    return false;
}

SUGAR.util.isLoginPage = function(content)
{
	//skip if this is packageManager screen
	if(SUGAR.util.isPackageManager()) {return false;}
	var loginPageStart = "<!DOCTYPE";
	if (content.substr(0, loginPageStart.length) == loginPageStart && content.indexOf("<html>") != -1  && content.indexOf("login_module") != -1) {
		window.location.href = window.location.protocol + window.location.pathname;
		return true;
	}
}

SUGAR.util.isPackageManager=function(){
	if(typeof(document.the_form) !='undefined' && typeof(document.the_form.language_pack_escaped) !='undefined'){
		return true;
	}else{return false;}
}

SUGAR.util.ajaxCallInProgress = function(){
	return SUGAR_callsInProgress != 0;
}

SUGAR.util.callOnChangeListers = function(field){
	var listeners = YAHOO.util.Event.getListeners(field, 'change');
	if (listeners != null) {
		for (var i = 0; i < listeners.length; i++) {
			var l = listeners[i];
			l.fn.call(l.scope ? l.scope : this, l.obj);
		}
	}
}

SUGAR.util.closeActivityPanel = {
    show:function(module,id,new_status,viewType,parentContainerId){
        if (SUGAR.util.closeActivityPanel.panel)
			SUGAR.util.closeActivityPanel.panel.destroy();
	    var singleModule = SUGAR.language.get("app_list_strings", "moduleListSingular")[module];
	    singleModule = typeof(singleModule != 'undefined') ? singleModule.toLowerCase() : '';
	    var closeText =  SUGAR.language.get("app_strings", "LBL_CLOSE_ACTIVITY_CONFIRM").replace("#module#",singleModule);
        SUGAR.util.closeActivityPanel.panel =
	    new YAHOO.widget.SimpleDialog("closeActivityDialog",
	             { width: "300px",
	               fixedcenter: true,
	               visible: false,
	               draggable: false,
	               close: true,
	               text: closeText,
	               constraintoviewport: true,
	               buttons: [ { text:SUGAR.language.get("app_strings", "LBL_EMAIL_OK"), handler:function(){
	                   if (SUGAR.util.closeActivityPanel.panel)
                            SUGAR.util.closeActivityPanel.panel.hide();

                        ajaxStatus.showStatus(SUGAR.language.get('app_strings', 'LBL_SAVING'));
                        var args = "action=save&id=" + id + "&record=" + id + "&status=" + new_status + "&module=" + module;
                        // 20110307 Frank Steegmans: Fix for bug 42361, Any field with a default configured in any activity will be set to this default when closed using the close dialog
                        // TODO: Take id out and regression test. Left id in for now to not create any other unexpected problems
                        //var args = "action=save&id=" + id + "&status=" + new_status + "&module=" + module;
                        var callback = {
                            success:function(o)
                            {	//refresh window to show updated changes
								window.location.reload(true);
								/*
                                if(viewType == 'dashlet')
                                {
                                    SUGAR.mySugar.retrieveDashlet(o.argument['parentContainerId']);
                                    ajaxStatus.hideStatus();
                                }
                                else if(viewType == 'subpanel'){
                                    showSubPanel(o.argument['parentContainerId'],null,true);
									if(o.argument['parentContainerId'] == 'activities'){
										showSubPanel('history',null,true);
									}
									ajaxStatus.hideStatus();

                                }else if(viewType == 'listview'){
                                    document.location = 'index.php?module=' + module +'&action=index';
									}
								*/
                            },
                            argument:{'parentContainerId':parentContainerId}
                        };

                        YAHOO.util.Connect.asyncRequest('POST', 'index.php', callback, args);

	               }, isDefault:true },
	                          { text:SUGAR.language.get("app_strings", "LBL_EMAIL_CANCEL"),  handler:function(){SUGAR.util.closeActivityPanel.panel.hide(); }} ]
	             } );

	    SUGAR.util.closeActivityPanel.panel.setHeader(SUGAR.language.get("app_strings", "LBL_CLOSE_ACTIVITY_HEADER"));
        SUGAR.util.closeActivityPanel.panel.render(document.body);
        SUGAR.util.closeActivityPanel.panel.show();
    }
}

SUGAR.util.setEmailPasswordDisplay = function(id, exists) {
	link = document.getElementById(id+'_link');
	pwd = document.getElementById(id);
	if(!pwd || !link) return;
	if(exists) {
    	pwd.style.display = 'none';
    	link.style.display = '';
	} else {
    	pwd.style.display = '';
    	link.style.display = 'none';
	}
}

SUGAR.util.setEmailPasswordEdit = function(id) {
	link = document.getElementById(id+'_link');
	pwd = document.getElementById(id);
	if(!pwd || !link) return;
	pwd.style.display = '';
	link.style.display = 'none';
}

