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
function loadSugarChart(chartId,jsonFilename,css,chartConfig,pageCols){var labelType,useGradients,nativeTextSupport,animate;(function(){var ua=navigator.userAgent,typeOfCanvas=typeof HTMLCanvasElement,nativeCanvasSupport=(typeOfCanvas=='object'||typeOfCanvas=='function'),textSupport=nativeCanvasSupport&&(typeof document.createElement('canvas').getContext('2d').fillText=='function');labelType='Native';nativeTextSupport=labelType=='Native';useGradients=nativeCanvasSupport;animate=false;})();var delay=(SUGAR.isIE)?500:0;switch(chartConfig["chartType"]){case"barChart":var handleFailure=function(o){alert('fail');if(o.responseText!==undefined){alert('failed');}}
var handleSuccess=function(o){if(o.responseText!==undefined&&o.responseText!="No Data"){var json=eval('('+o.responseText+')');var properties=$jit.util.splat(json.properties)[0];var marginBottom=(chartConfig["orientation"]=='vertical'&&json.values.length>8)?20*4:20;var barChart=new $jit.BarChart({injectInto:chartId,animate:false,nodeCount:json.values.length,renderBackground:chartConfig['imageExportType']=="jpg"?true:false,backgroundColor:'rgb(255,255,255)',colorStop1:'rgba(255,255,255,.8)',colorStop2:'rgba(255,255,255,0)',shadow:{enable:true,size:2},orientation:chartConfig["orientation"],hoveredColor:false,Title:{text:properties['title'],size:16,color:'#444444',offset:20},Subtitle:{text:properties['subtitle'],size:11,color:css["color"],offset:20},Ticks:{enable:true,color:css["gridLineColor"]},barsOffset:(chartConfig["orientation"]=="vertical")?30:20,Margin:{top:20,left:30,right:20,bottom:marginBottom},ScrollNote:{text:(chartConfig["scroll"]&&SUGAR.util.isTouchScreen())?"Use two fingers to scroll":"",size:12},Events:{enable:true,onClick:function(node){if(!node||SUGAR.util.isTouchScreen())return;if(node.link=='undefined'||node.link=='')return;window.location.href=node.link;}},labelOffset:5,type:useGradients?chartConfig["barType"]+':gradient':chartConfig["barType"],showAggregates:true,showLabels:true,Label:{type:labelType,size:12,family:css["font-family"],color:css["color"],colorAlt:"#ffffff"},Tips:{enable:true,onShow:function(tip,elem){if(elem.link!='undefined'&&elem.link!=''){drillDown=(SUGAR.util.isTouchScreen())?"<br><a href='"+elem.link+"'>Click to drilldown</a>":"<br>Click to drilldown";}else{drillDown="";}
if(elem.valuelabel!='undefined'&&elem.valuelabel!=undefined&&elem.valuelabel!=''){value="elem.valuelabel";}else{value="elem.value";}
eval("tip.innerHTML = '<b>' + elem."+chartConfig["tip"]+" + '</b>: ' + "+value+" + ' - ' + elem.percentage + '%' + drillDown");}}});barChart.loadJSON(json);var list=$jit.id('legend'+chartId);var legend=barChart.getLegend(),cols=(typeof SUGAR=='undefined'||typeof SUGAR.mySugar=='undefined')?8:4,rows=Math.ceil(legend["name"].length/cols),table="<table cellpadding='0' cellspacing='0' align='left'>";var j=0;for(i=0;i<rows;i++){table+="<tr>";for(td=0;td<cols;td++){table+='<td width=\'16\' valign=\'top\'>';if(legend["name"][j]!=undefined){table+='<div class=\'query-color\' style=\'background-color:'
+legend["color"][j]+'\'>&nbsp;</div>';}
table+='</td>';table+='<td class=\'label\' valign=\'top\'>';if(legend["name"][j]!=undefined){table+=legend["name"][j];}
table+='</td>';j++;}
table+="</tr>";}
table+="</table>";list.innerHTML=table;$jit.util.saveImageTest(chartId,jsonFilename,chartConfig["imageExportType"]);var firstLoad=(SUGAR.isIE)?true:false,orgWindowWidth=document.body.offsetWidth,orgContainerDivWidth=document.getElementById(chartId).offsetWidth;var refreshGraph=function(){var newWindowWidth=document.body.offsetWidth;var diff=Math.abs(newWindowWidth-orgWindowWidth);if(diff>20&&!firstLoad){barChart.resizeGraph(json,orgWindowWidth,orgContainerDivWidth,pageCols);}
firstLoad=false;}
var doRefresh=function(){setTimeout(function(){refreshGraph()},delay);}
YAHOO.util.Event.addListener(window,'resize',function(){doRefresh()});}}
var callback={success:handleSuccess,failure:handleFailure,argument:{foo:'foo',bar:''}};var request=YAHOO.util.Connect.asyncRequest('GET',jsonFilename+"?r="+new Date().getTime(),callback);break;case"lineChart":var handleFailure=function(o){alert('fail');if(o.responseText!==undefined){alert('failed');}}
var handleSuccess=function(o){if(o.responseText!==undefined&&o.responseText!="No Data"){var json=eval('('+o.responseText+')');var properties=$jit.util.splat(json.properties)[0];var lineChart=new $jit.LineChart({injectInto:chartId,animate:false,renderBackground:chartConfig['imageExportType']=="jpg"?true:false,backgroundColor:'rgb(255,255,255)',colorStop1:'rgba(255,255,255,.8)',colorStop2:'rgba(255,255,255,0)',selectOnHover:false,Title:{text:properties['title'],size:16,color:'#444444',offset:20},Subtitle:{text:properties['subtitle'],size:11,color:css["color"],offset:20},Ticks:{enable:true,color:css["gridLineColor"]},Margin:{top:20,left:40,right:40,bottom:20},Events:{enable:true,onClick:function(node){if(!node||SUGAR.util.isTouchScreen())return;if(node.link=='undefined'||node.link=='')return;window.location.href=node.link;}},labelOffset:5,type:useGradients?chartConfig["lineType"]+':gradient':chartConfig["lineType"],showAggregates:true,showLabels:true,Label:{type:labelType,size:12,family:css["font-family"],color:css["color"],colorAlt:"#ffffff"},Tips:{enable:true,onShow:function(tip,elem){if(elem.link!='undefined'&&elem.link!=''){drillDown=(SUGAR.util.isTouchScreen())?"<br><a href='"+elem.link+"'>Click to drilldown</a>":"<br>Click to drilldown";}else{drillDown="";}
if(elem.valuelabel!='undefined'&&elem.valuelabel!=undefined&&elem.valuelabel!=''){var value="elem.valuelabel";}else{var value="elem.value";}
if(elem.collision){eval("var name = elem."+chartConfig["tip"]+";");var content='<table>';for(var i=0;i<name.length;i++){content+='<tr><td><b>'+name[i]+'</b>:</td><td> '+elem.value[i]+' - '+elem.percentage[i]+'%'+'</td></tr>';}
content+='</table>';tip.innerHTML=content;}else{eval("tip.innerHTML = '<b>' + elem."+chartConfig["tip"]+" + '</b>: ' + "+value+" + ' - ' + elem.percentage + '%' + drillDown");}}}});lineChart.loadJSON(json);var list=$jit.id('legend'+chartId);var legend=lineChart.getLegend(),cols=(typeof SUGAR=='undefined'||typeof SUGAR.mySugar=='undefined')?8:4,rows=Math.ceil(legend["name"].length/cols),table="<table cellpadding='0' cellspacing='0' align='left'>";var j=0;for(i=0;i<rows;i++){table+="<tr>";for(td=0;td<cols;td++){table+='<td width=\'16\' valign=\'top\'>';if(legend["name"][j]!=undefined){table+='<div class=\'query-color\' style=\'background-color:'
+legend["color"][j]+'\'>&nbsp;</div>';}
table+='</td>';table+='<td class=\'label\' valign=\'top\'>';if(legend["name"][j]!=undefined){table+=legend["name"][j];}
table+='</td>';j++;}
table+="</tr>";}
table+="</table>";list.innerHTML=table;$jit.util.saveImageTest(chartId,jsonFilename,chartConfig["imageExportType"]);var firstLoad=(SUGAR.isIE)?true:false,orgWindowWidth=document.body.offsetWidth,orgContainerDivWidth=document.getElementById(chartId).offsetWidth;var refreshGraph=function(){var newWindowWidth=document.body.offsetWidth;var diff=Math.abs(newWindowWidth-orgWindowWidth);if(diff>20&&!firstLoad){lineChart.resizeGraph(json,orgWindowWidth,orgContainerDivWidth,pageCols);}
firstLoad=false;}
var doRefresh=function(){setTimeout(function(){refreshGraph()},delay);}
YAHOO.util.Event.addListener(window,'resize',function(){doRefresh()});}}
var callback={success:handleSuccess,failure:handleFailure,argument:{foo:'foo',bar:''}};var request=YAHOO.util.Connect.asyncRequest('GET',jsonFilename+"?r="+new Date().getTime(),callback);break;case"pieChart":var handleFailure=function(o){alert('fail');if(o.responseText!==undefined){alert('failed');}}
var handleSuccess=function(o){if(o.responseText!==undefined){var json=eval('('+o.responseText+')');var properties=$jit.util.splat(json.properties)[0];var pieChart=new $jit.PieChart({injectInto:chartId,animate:false,renderBackground:chartConfig['imageExportType']=="jpg"?true:false,backgroundColor:'rgb(255,255,255)',colorStop1:'rgba(255,255,255,.8)',colorStop2:'rgba(255,255,255,0)',labelType:properties['labels'],hoveredColor:false,offset:50,sliceOffset:0,labelOffset:30,type:useGradients?chartConfig["pieType"]+':gradient':chartConfig["pieType"],showLabels:true,Title:{text:properties['title'],size:16,color:'#444444',offset:20},Subtitle:{text:properties['subtitle'],size:11,color:css["color"],offset:20},Margin:{top:20,left:20,right:20,bottom:20},Events:{enable:true,onClick:function(node){if(!node||SUGAR.util.isTouchScreen())return;if(node.link=='undefined'||node.link=='')return;window.location.href=node.link;}},Label:{type:labelType,size:12,family:css["font-family"],color:css["color"]},Tips:{enable:true,onShow:function(tip,elem){if(elem.link!='undefined'&&elem.link!=''){drillDown=(SUGAR.util.isTouchScreen())?"<br><a href='"+elem.link+"'>Click to drilldown</a>":"<br>Click to drilldown";}else{drillDown="";}
if(elem.valuelabel!='undefined'&&elem.valuelabel!=undefined&&elem.valuelabel!=''){value="elem.valuelabel";}else{value="elem.value";}
eval("tip.innerHTML = '<b>' + elem.label + '</b>: ' + "+value+" + ' - ' + elem.percentage + '%' + drillDown");}}});pieChart.loadJSON(json);var list=$jit.id('legend'+chartId);var legend=pieChart.getLegend(),cols=(typeof SUGAR=='undefined'||typeof SUGAR.mySugar=='undefined')?8:4,rows=Math.ceil(legend["name"].length/cols);table="<table cellpadding='0' cellspacing='0' align='left'>";var j=0;for(i=0;i<rows;i++){table+="<tr>";for(td=0;td<cols;td++){table+='<td width=\'16\' valign=\'top\'>';if(legend["name"][j]!=undefined){table+='<div class=\'query-color\' style=\'background-color:'
+legend["color"][j]+'\'>&nbsp;</div>';}
table+='</td>';table+='<td class=\'label\' valign=\'top\'>';if(legend["name"][j]!=undefined){table+=legend["name"][j];}
table+='</td>';j++;}
table+="</tr>";}
table+="</table>";list.innerHTML=table;$jit.util.saveImageTest(chartId,jsonFilename,chartConfig["imageExportType"]);var firstLoad=(SUGAR.isIE)?true:false,orgWindowWidth=document.body.offsetWidth,orgContainerDivWidth=document.getElementById(chartId).offsetWidth;var refreshGraph=function(){var newWindowWidth=document.body.offsetWidth;var diff=Math.abs(newWindowWidth-orgWindowWidth);if(diff>20&&!firstLoad){pieChart.resizeGraph(json,orgWindowWidth,orgContainerDivWidth,pageCols);}
firstLoad=false;}
var doRefresh=function(){setTimeout(function(){refreshGraph()},delay);}
YAHOO.util.Event.addListener(window,'resize',function(){doRefresh()});}}
var callback={success:handleSuccess,failure:handleFailure,argument:{foo:'foo',bar:''}};var request=YAHOO.util.Connect.asyncRequest('GET',jsonFilename+"?r="+new Date().getTime(),callback);break;case"funnelChart":var handleFailure=function(o){alert('fail');if(o.responseText!==undefined){alert('failed');}}
var handleSuccess=function(o){if(o.responseText!==undefined&&o.responseText!="No Data"){var json=eval('('+o.responseText+')');var properties=$jit.util.splat(json.properties)[0];var funnelChart=new $jit.FunnelChart({injectInto:chartId,animate:false,renderBackground:chartConfig['imageExportType']=="jpg"?true:false,backgroundColor:'rgb(255,255,255)',colorStop1:'rgba(255,255,255,.8)',colorStop2:'rgba(255,255,255,0)',orientation:"vertical",hoveredColor:false,Title:{text:properties['title'],size:16,color:'#444444',offset:20},Subtitle:{text:properties['subtitle'],size:11,color:css["color"],offset:20},segmentOffset:20,Margin:{top:20,left:20,right:20,bottom:20},Events:{enable:true,onClick:function(node){if(!node||SUGAR.util.isTouchScreen())return;if(node.link=='undefined'||node.link=='')return;window.location.href=node.link;}},labelOffset:10,type:useGradients?chartConfig["funnelType"]+':gradient':chartConfig["funnelType"],showAggregates:true,showLabels:true,Label:{type:labelType,size:12,family:css["font-family"],color:css["color"],colorAlt:"#ffffff"},Tips:{enable:true,onShow:function(tip,elem){if(elem.link!='undefined'&&elem.link!=''){drillDown=(SUGAR.util.isTouchScreen())?"<br><a href='"+elem.link+"'>Click to drilldown</a>":"<br>Click to drilldown";}else{drillDown="";}
if(elem.valuelabel!='undefined'&&elem.valuelabel!=undefined&&elem.valuelabel!=''){value="elem.valuelabel";}else{value="elem.value";}
eval("tip.innerHTML = '<b>' + elem."+chartConfig["tip"]+" + '</b>: ' + "+value+"  + ' - ' + elem.percentage + '%' +  drillDown");}}});funnelChart.loadJSON(json);var list=$jit.id('legend'+chartId);var legend=funnelChart.getLegend(),cols=(typeof SUGAR=='undefined'||typeof SUGAR.mySugar=='undefined')?8:4,rows=Math.ceil(legend["name"].length/cols);table="<table cellpadding='0' cellspacing='0' align='left'>";var j=0;for(i=0;i<rows;i++){table+="<tr>";for(td=0;td<cols;td++){table+='<td width=\'16\' valign=\'top\'>';if(legend["name"][j]!=undefined){table+='<div class=\'query-color\' style=\'background-color:'
+legend["color"][j]+'\'>&nbsp;</div>';}
table+='</td>';table+='<td class=\'label\' valign=\'top\'>';if(legend["name"][j]!=undefined){table+=legend["name"][j];}
table+='</td>';j++;}
table+="</tr>";}
table+="</table>";list.innerHTML=table;$jit.util.saveImageTest(chartId,jsonFilename,chartConfig["imageExportType"]);var firstLoad=(SUGAR.isIE)?true:false,orgWindowWidth=document.body.offsetWidth,orgContainerDivWidth=document.getElementById(chartId).offsetWidth;var refreshGraph=function(){var newWindowWidth=document.body.offsetWidth;var diff=Math.abs(newWindowWidth-orgWindowWidth);if(diff>20&&!firstLoad){funnelChart.resizeGraph(json,orgWindowWidth,orgContainerDivWidth,pageCols);}
firstLoad=false;}
var doRefresh=function(){setTimeout(function(){refreshGraph()},delay);}
YAHOO.util.Event.addListener(window,'resize',function(){doRefresh()});}}
var callback={success:handleSuccess,failure:handleFailure,argument:{foo:'foo',bar:''}};var request=YAHOO.util.Connect.asyncRequest('GET',jsonFilename+"?r="+new Date().getTime(),callback);break;case"gaugeChart":var handleFailure=function(o){alert('fail');if(o.responseText!==undefined){alert('failed');}}
var handleSuccess=function(o){if(o.responseText!==undefined){var json=eval('('+o.responseText+')');var properties=$jit.util.splat(json.properties)[0];var gaugeChart=new $jit.GaugeChart({injectInto:chartId,animate:false,renderBackground:chartConfig['imageExportType']=="jpg"?true:false,backgroundColor:'rgb(255,255,255)',colorStop1:'rgba(255,255,255,.8)',colorStop2:'rgba(255,255,255,0)',labelType:properties['labels'],hoveredColor:false,Title:{text:properties['title'],size:16,color:'#444444',offset:20},Subtitle:{text:properties['subtitle'],size:11,color:css["color"],offset:5},offset:20,gaugeStyle:{backgroundColor:'#aaaaaa',borderColor:'#999999',needleColor:'rgba(255,0,0,.8)',borderSize:4,positionFontSize:24,positionOffset:2},type:useGradients?chartConfig["gaugeType"]+':gradient':chartConfig["gaugeType"],showLabels:true,Events:{enable:true,onClick:function(node){if(!node||SUGAR.util.isTouchScreen())return;if(node.link=='undefined'||node.link=='')return;window.location.href=node.link;}},Label:{type:labelType,size:12,family:css["font-family"],color:css["color"]},Tips:{enable:true,onShow:function(tip,elem){if(elem.link!='undefined'&&elem.link!=''){drillDown=(SUGAR.util.isTouchScreen())?"<br><a href='"+elem.link+"'>Click to drilldown</a>":"<br>Click to drilldown";}else{drillDown="";}
if(elem.valuelabel!='undefined'&&elem.valuelabel!=undefined&&elem.valuelabel!=''){value="elem.valuelabel";}else{value="elem.value";}
eval("tip.innerHTML = '<b>' + elem.label + '</b>: ' + "+value+" + drillDown");}}});gaugeChart.loadJSON(json);var list=$jit.id('legend'+chartId);var legend=gaugeChart.getLegend(),cols=(typeof SUGAR=='undefined'||typeof SUGAR.mySugar=='undefined')?8:4,rows=Math.ceil(legend["name"].length/cols);table="<table cellpadding='0' cellspacing='0' align='left'>";var j=1;for(i=0;i<rows;i++){table+="<tr>";for(td=0;td<cols;td++){table+='<td width=\'16\' valign=\'top\'>';if(legend["name"][j]!=undefined){table+='<div class=\'query-color\' style=\'background-color:'
+legend["color"][j]+'\'>&nbsp;</div>';}
table+='</td>';table+='<td class=\'label\' valign=\'top\'>';if(legend["name"][j]!=undefined){table+=legend["name"][j];}
table+='</td>';j++;}
table+="</tr>";}
table+="</table>";list.innerHTML=table;$jit.util.saveImageTest(chartId,jsonFilename,chartConfig["imageExportType"]);var firstLoad=(SUGAR.isIE)?true:false,orgWindowWidth=document.body.offsetWidth,orgContainerDivWidth=document.getElementById(chartId).offsetWidth;var refreshGraph=function(){var newWindowWidth=document.body.offsetWidth;var diff=Math.abs(newWindowWidth-orgWindowWidth);if(diff>20&&!firstLoad){gaugeChart.resizeGraph(json,orgWindowWidth,orgContainerDivWidth,pageCols);}
firstLoad=false;}
var doRefresh=function(){setTimeout(function(){refreshGraph()},delay);}
YAHOO.util.Event.addListener(window,'resize',function(){doRefresh()});}}
var callback={success:handleSuccess,failure:handleFailure,argument:{foo:'foo',bar:''}};var request=YAHOO.util.Connect.asyncRequest('GET',jsonFilename+"?r="+new Date().getTime(),callback);break;}}