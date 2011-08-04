/*
 Copyright (c) 2009, Yahoo! Inc. All rights reserved.
 Code licensed under the BSD License:
 http://developer.yahoo.net/yui/license.txt
 version: 3.0.0
 build: 1549
 */
YUI.add('anim-base',function(Y){var RUNNING='running',START_TIME='startTime',ELAPSED_TIME='elapsedTime',START='start',TWEEN='tween',END='end',NODE='node',PAUSED='paused',REVERSE='reverse',ITERATION_COUNT='iterationCount',NUM=Number;var _running={},_instances={},_timer;Y.Anim=function(){Y.Anim.superclass.constructor.apply(this,arguments);_instances[Y.stamp(this)]=this;};Y.Anim.NAME='anim';Y.Anim.RE_DEFAULT_UNIT=/^width|height|top|right|bottom|left|margin.*|padding.*|border.*$/i;Y.Anim.DEFAULT_UNIT='px';Y.Anim.DEFAULT_EASING=function(t,b,c,d){return c*t/d+b;};Y.Anim.behaviors={left:{get:function(anim,attr){return anim._getOffset(attr);}}};Y.Anim.behaviors.top=Y.Anim.behaviors.left;Y.Anim.DEFAULT_SETTER=function(anim,att,from,to,elapsed,duration,fn,unit){unit=unit||'';anim._node.setStyle(att,fn(elapsed,NUM(from),NUM(to)-NUM(from),duration)+unit);};Y.Anim.DEFAULT_GETTER=function(anim,prop){return anim._node.getComputedStyle(prop);};Y.Anim.ATTRS={node:{setter:function(node){node=Y.get(node);this._node=node;if(!node){}
return node;}},duration:{value:1},easing:{value:Y.Anim.DEFAULT_EASING,setter:function(val){if(typeof val==='string'&&Y.Easing){return Y.Easing[val];}}},from:{},to:{},startTime:{value:0,readOnly:true},elapsedTime:{value:0,readOnly:true},running:{getter:function(){return!!_running[Y.stamp(this)];},value:false,readOnly:true},iterations:{value:1},iterationCount:{value:0,readOnly:true},direction:{value:'normal'},paused:{readOnly:true,value:false},reverse:{value:false}};Y.Anim.run=function(){for(var i in _instances){if(_instances[i].run){_instances[i].run();}}};Y.Anim.pause=function(){for(var i in _running){if(_running[i].pause){_running[i].pause();}}
Y.Anim._stopTimer();};Y.Anim.stop=function(){for(var i in _running){if(_running[i].stop){_running[i].stop();}}
Y.Anim._stopTimer();};Y.Anim._startTimer=function(){if(!_timer){_timer=setInterval(Y.Anim._runFrame,1);}};Y.Anim._stopTimer=function(){clearInterval(_timer);_timer=0;};Y.Anim._runFrame=function(){var done=true;for(var anim in _running){if(_running[anim]._runFrame){done=false;_running[anim]._runFrame();}}
if(done){Y.Anim._stopTimer();}};Y.Anim.RE_UNITS=/^(-?\d*\.?\d*){1}(em|ex|px|in|cm|mm|pt|pc|%)*$/;var proto={run:function(){if(!this.get(RUNNING)){this._start();}else if(this.get(PAUSED)){this._resume();}
return this;},pause:function(){if(this.get(RUNNING)){this._pause();}
return this;},stop:function(finish){if(this.get(RUNNING)||this.get(PAUSED)){this._end(finish);}
return this;},_added:false,_start:function(){this._set(START_TIME,new Date()-this.get(ELAPSED_TIME));this._actualFrames=0;if(!this.get(PAUSED)){this._initAnimAttr();}
_running[Y.stamp(this)]=this;Y.Anim._startTimer();this.fire(START);},_pause:function(){this._set(START_TIME,null);this._set(PAUSED,true);delete _running[Y.stamp(this)];this.fire('pause');},_resume:function(){this._set(PAUSED,false);_running[Y.stamp(this)]=this;this.fire('resume');},_end:function(finish){this._set(START_TIME,null);this._set(ELAPSED_TIME,0);this._set(PAUSED,false);delete _running[Y.stamp(this)];this.fire(END,{elapsed:this.get(ELAPSED_TIME)});},_runFrame:function(){var attr=this._runtimeAttr,customAttr=Y.Anim.behaviors,easing=attr.easing,d=attr.duration,t=new Date()-this.get(START_TIME),reversed=this.get(REVERSE),done=(t>=d),lastFrame=d,attribute,setter;if(reversed){t=d-t;done=(t<=0);lastFrame=0;}
for(var i in attr){if(attr[i].to){attribute=attr[i];setter=(i in customAttr&&'set'in customAttr[i])?customAttr[i].set:Y.Anim.DEFAULT_SETTER;if(!done){setter(this,i,attribute.from,attribute.to,t,d,easing,attribute.unit);}else{setter(this,i,attribute.from,attribute.to,lastFrame,d,easing,attribute.unit);}}}
this._actualFrames+=1;this._set(ELAPSED_TIME,t);this.fire(TWEEN);if(done){this._lastFrame();}},_lastFrame:function(){var iter=this.get('iterations'),iterCount=this.get(ITERATION_COUNT);iterCount+=1;if(iter==='infinite'||iterCount<iter){if(this.get('direction')==='alternate'){this.set(REVERSE,!this.get(REVERSE));}
this.fire('iteration');}else{iterCount=0;this._end();}
this._set(START_TIME,new Date());this._set(ITERATION_COUNT,iterCount);},_initAnimAttr:function(){var from=this.get('from')||{},to=this.get('to')||{},dur=this.get('duration')*1000,node=this.get(NODE),easing=this.get('easing')||{},attr={},customAttr=Y.Anim.behaviors,unit,begin,end;Y.each(to,function(val,name){if(typeof val==='function'){val=val.call(this,node);}
begin=from[name];if(begin===undefined){begin=(name in customAttr&&'get'in customAttr[name])?customAttr[name].get(this,name):Y.Anim.DEFAULT_GETTER(this,name);}else if(typeof begin==='function'){begin=begin.call(this,node);}
var mFrom=Y.Anim.RE_UNITS.exec(begin);var mTo=Y.Anim.RE_UNITS.exec(val);begin=mFrom?mFrom[1]:begin;end=mTo?mTo[1]:val;unit=mTo?mTo[2]:mFrom?mFrom[2]:'';if(!unit&&Y.Anim.RE_DEFAULT_UNIT.test(name)){unit=Y.Anim.DEFAULT_UNIT;}
if(!begin||!end){Y.error('invalid "from" or "to" for "'+name+'"','Anim');return;}
attr[name]={from:begin,to:end,unit:unit};attr.duration=dur;attr.easing=easing;},this);this._runtimeAttr=attr;},_getOffset:function(attr){var node=this._node,val=node.getComputedStyle(attr),get=(attr==='left')?'getX':'getY',set=(attr==='left')?'setX':'setY';if(val==='auto'){var position=node.getStyle('position');if(position==='absolute'||position==='fixed'){val=node[get]();node[set](val);}else{val=0;}}
return val;}};Y.extend(Y.Anim,Y.Base,proto);},'3.0.0',{requires:['base-base','node-style']});YUI.add('anim-color',function(Y){var NUM=Number;Y.Anim.behaviors.color={set:function(anim,att,from,to,elapsed,duration,fn){from=Y.Color.re_RGB.exec(Y.Color.toRGB(from));to=Y.Color.re_RGB.exec(Y.Color.toRGB(to));if(!from||from.length<3||!to||to.length<3){Y.error('invalid from or to passed to color behavior');}
anim._node.setStyle(att,'rgb('+[Math.floor(fn(elapsed,NUM(from[1]),NUM(to[1])-NUM(from[1]),duration)),Math.floor(fn(elapsed,NUM(from[2]),NUM(to[2])-NUM(from[2]),duration)),Math.floor(fn(elapsed,NUM(from[3]),NUM(to[3])-NUM(from[3]),duration))].join(', ')+')');},get:function(anim,att){var val=anim._node.getComputedStyle(att);val=(val==='transparent')?'rgb(255, 255, 255)':val;return val;}};Y.each(['backgroundColor','borderColor','borderTopColor','borderRightColor','borderBottomColor','borderLeftColor'],function(v,i){Y.Anim.behaviors[v]=Y.Anim.behaviors.color;});},'3.0.0',{requires:['anim-base']});YUI.add('anim-curve',function(Y){Y.Anim.behaviors.curve={set:function(anim,att,from,to,elapsed,duration,fn){from=from.slice.call(from);to=to.slice.call(to);var t=fn(elapsed,0,100,duration)/100;to.unshift(from);anim._node.setXY(Y.Anim.getBezier(to,t));},get:function(anim,att){return anim._node.getXY();}};Y.Anim.getBezier=function(points,t){var n=points.length;var tmp=[];for(var i=0;i<n;++i){tmp[i]=[points[i][0],points[i][1]];}
for(var j=1;j<n;++j){for(i=0;i<n-j;++i){tmp[i][0]=(1-t)*tmp[i][0]+t*tmp[parseInt(i+1,10)][0];tmp[i][1]=(1-t)*tmp[i][1]+t*tmp[parseInt(i+1,10)][1];}}
return[tmp[0][0],tmp[0][1]];};},'3.0.0',{requires:['anim-xy']});YUI.add('anim-easing',function(Y){Y.Easing={easeNone:function(t,b,c,d){return c*t/d+b;},easeIn:function(t,b,c,d){return c*(t/=d)*t+b;},easeOut:function(t,b,c,d){return-c*(t/=d)*(t-2)+b;},easeBoth:function(t,b,c,d){if((t/=d/2)<1){return c/2*t*t+b;}
return-c/2*((--t)*(t-2)-1)+b;},easeInStrong:function(t,b,c,d){return c*(t/=d)*t*t*t+b;},easeOutStrong:function(t,b,c,d){return-c*((t=t/d-1)*t*t*t-1)+b;},easeBothStrong:function(t,b,c,d){if((t/=d/2)<1){return c/2*t*t*t*t+b;}
return-c/2*((t-=2)*t*t*t-2)+b;},elasticIn:function(t,b,c,d,a,p){var s;if(t===0){return b;}
if((t/=d)===1){return b+c;}
if(!p){p=d*0.3;}
if(!a||a<Math.abs(c)){a=c;s=p/4;}
else{s=p/(2*Math.PI)*Math.asin(c/a);}
return-(a*Math.pow(2,10*(t-=1))*Math.sin((t*d-s)*(2*Math.PI)/p))+b;},elasticOut:function(t,b,c,d,a,p){var s;if(t===0){return b;}
if((t/=d)===1){return b+c;}
if(!p){p=d*0.3;}
if(!a||a<Math.abs(c)){a=c;s=p/4;}
else{s=p/(2*Math.PI)*Math.asin(c/a);}
return a*Math.pow(2,-10*t)*Math.sin((t*d-s)*(2*Math.PI)/p)+c+b;},elasticBoth:function(t,b,c,d,a,p){var s;if(t===0){return b;}
if((t/=d/2)===2){return b+c;}
if(!p){p=d*(0.3*1.5);}
if(!a||a<Math.abs(c)){a=c;s=p/4;}
else{s=p/(2*Math.PI)*Math.asin(c/a);}
if(t<1){return-0.5*(a*Math.pow(2,10*(t-=1))*Math.sin((t*d-s)*(2*Math.PI)/p))+b;}
return a*Math.pow(2,-10*(t-=1))*Math.sin((t*d-s)*(2*Math.PI)/p)*0.5+c+b;},backIn:function(t,b,c,d,s){if(s===undefined){s=1.70158;}
if(t===d){t-=0.001;}
return c*(t/=d)*t*((s+1)*t-s)+b;},backOut:function(t,b,c,d,s){if(typeof s==='undefined'){s=1.70158;}
return c*((t=t/d-1)*t*((s+1)*t+s)+1)+b;},backBoth:function(t,b,c,d,s){if(typeof s==='undefined'){s=1.70158;}
if((t/=d/2)<1){return c/2*(t*t*(((s*=(1.525))+1)*t-s))+b;}
return c/2*((t-=2)*t*(((s*=(1.525))+1)*t+s)+2)+b;},bounceIn:function(t,b,c,d){return c-Y.Easing.bounceOut(d-t,0,c,d)+b;},bounceOut:function(t,b,c,d){if((t/=d)<(1/2.75)){return c*(7.5625*t*t)+b;}else if(t<(2/2.75)){return c*(7.5625*(t-=(1.5/2.75))*t+0.75)+b;}else if(t<(2.5/2.75)){return c*(7.5625*(t-=(2.25/2.75))*t+0.9375)+b;}
return c*(7.5625*(t-=(2.625/2.75))*t+0.984375)+b;},bounceBoth:function(t,b,c,d){if(t<d/2){return Y.Easing.bounceIn(t*2,0,c,d)*0.5+b;}
return Y.Easing.bounceOut(t*2-d,0,c,d)*0.5+c*0.5+b;}};},'3.0.0',{requires:['anim-base']});YUI.add('anim-node-plugin',function(Y){var NodeFX=function(config){config=(config)?Y.merge(config):{};config.node=config.host;NodeFX.superclass.constructor.apply(this,arguments);};NodeFX.NAME="nodefx";NodeFX.NS="fx";Y.extend(NodeFX,Y.Anim);Y.namespace('Plugin');Y.Plugin.NodeFX=NodeFX;},'3.0.0',{requires:['node-pluginhost','anim-base']});YUI.add('anim-scroll',function(Y){var NUM=Number;Y.Anim.behaviors.scroll={set:function(anim,att,from,to,elapsed,duration,fn){var
node=anim._node,val=([fn(elapsed,NUM(from[0]),NUM(to[0])-NUM(from[0]),duration),fn(elapsed,NUM(from[1]),NUM(to[1])-NUM(from[1]),duration)]);if(val[0]){node.set('scrollLeft',val[0]);}
if(val[1]){node.set('scrollTop',val[1]);}},get:function(anim){var node=anim._node;return[node.get('scrollLeft'),node.get('scrollTop')];}};},'3.0.0',{requires:['anim-base']});YUI.add('anim-xy',function(Y){var NUM=Number;Y.Anim.behaviors.xy={set:function(anim,att,from,to,elapsed,duration,fn){anim._node.setXY([fn(elapsed,NUM(from[0]),NUM(to[0])-NUM(from[0]),duration),fn(elapsed,NUM(from[1]),NUM(to[1])-NUM(from[1]),duration)]);},get:function(anim){return anim._node.getXY();}};},'3.0.0',{requires:['anim-base','node-screen']});YUI.add('anim',function(Y){},'3.0.0',{use:['anim-base','anim-color','anim-curve','anim-easing','anim-node-plugin','anim-scroll','anim-xy'],skinnable:false});