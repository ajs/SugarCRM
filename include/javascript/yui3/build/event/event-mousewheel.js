/*
 Copyright (c) 2009, Yahoo! Inc. All rights reserved.
 Code licensed under the BSD License:
 http://developer.yahoo.net/yui/license.txt
 version: 3.0.0
 build: 1549
 */
YUI.add('event-mousewheel',function(Y){var DOM_MOUSE_SCROLL='DOMMouseScroll',fixArgs=function(args){var a=Y.Array(args,0,true),target;if(Y.UA.gecko){a[0]=DOM_MOUSE_SCROLL;target=Y.config.win;}else{target=Y.config.doc;}
if(a.length<3){a[2]=target;}else{a.splice(2,0,target);}
return a;};Y.Env.evt.plugins.mousewheel={on:function(){return Y.Event._attach(fixArgs(arguments));},detach:function(){return Y.Event.detach.apply(Y.Event,fixArgs(arguments));}};},'3.0.0',{requires:['node-base']});