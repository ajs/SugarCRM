/*
 Copyright (c) 2009, Yahoo! Inc. All rights reserved.
 Code licensed under the BSD License:
 http://developer.yahoo.net/yui/license.txt
 version: 3.0.0
 build: 1549
 */
YUI.add('selector-native',function(Y){(function(Y){Y.namespace('Selector');var COMPARE_DOCUMENT_POSITION='compareDocumentPosition',OWNER_DOCUMENT='ownerDocument',TMP_PREFIX='yui-tmp-',g_counter=0;var Selector={_foundCache:[],useNative:true,_compare:('sourceIndex'in document.documentElement)?function(nodeA,nodeB){var a=nodeA.sourceIndex,b=nodeB.sourceIndex;if(a===b){return 0;}else if(a>b){return 1;}
return-1;}:(document.documentElement[COMPARE_DOCUMENT_POSITION]?function(nodeA,nodeB){if(nodeA[COMPARE_DOCUMENT_POSITION](nodeB)&4){return-1;}else{return 1;}}:function(nodeA,nodeB){var rangeA,rangeB,compare;if(nodeA&&nodeB){rangeA=nodeA[OWNER_DOCUMENT].createRange();rangeA.setStart(nodeA,0);rangeB=nodeB[OWNER_DOCUMENT].createRange();rangeB.setStart(nodeB,0);compare=rangeA.compareBoundaryPoints(1,rangeB);}
return compare;}),_sort:function(nodes){if(nodes){nodes=Y.Array(nodes,0,true);if(nodes.sort){nodes.sort(Selector._compare);}}
return nodes;},_deDupe:function(nodes){var ret=[],i,node;for(i=0;(node=nodes[i++]);){if(!node._found){ret[ret.length]=node;node._found=true;}}
for(i=0;(node=ret[i++]);){node._found=null;node.removeAttribute('_found');}
return ret;},query:function(selector,root,firstOnly,skipNative){root=root||Y.config.doc;var ret=[],useNative=(Y.Selector.useNative&&document.querySelector&&!skipNative),queries=[[selector,root]],query,result,i,fn=(useNative)?Y.Selector._nativeQuery:Y.Selector._bruteQuery;if(selector&&fn){if(!skipNative&&(!useNative||root.tagName)){queries=Selector._splitQueries(selector,root);}
for(i=0;(query=queries[i++]);){result=fn(query[0],query[1],firstOnly);if(!firstOnly){result=Y.Array(result,0,true);}
if(result){ret=ret.concat(result);}}
if(queries.length>1){ret=Selector._sort(Selector._deDupe(ret));}}
return(firstOnly)?(ret[0]||null):ret;},_splitQueries:function(selector,node){var groups=selector.split(','),queries=[],prefix='',i,len;if(node){if(node.tagName){node.id=node.id||Y.guid();prefix='#'+node.id+' ';}
for(i=0,len=groups.length;i<len;++i){selector=prefix+groups[i];queries.push([selector,node]);}}
return queries;},_nativeQuery:function(selector,root,one){try{return root['querySelector'+(one?'':'All')](selector);}catch(e){return Y.Selector.query(selector,root,one,true);}},filter:function(nodes,selector){var ret=[],i,node;if(nodes&&selector){for(i=0;(node=nodes[i++]);){if(Y.Selector.test(node,selector)){ret[ret.length]=node;}}}else{}
return ret;},test:function(node,selector,root){var ret=false,groups=selector.split(','),item,i,group;if(node&&node.tagName){root=root||node.ownerDocument;if(!node.id){node.id=TMP_PREFIX+g_counter++;}
for(i=0;(group=groups[i++]);){group+='#'+node.id;item=Y.Selector.query(group,root,true);ret=(item===node);if(ret){break;}}}
return ret;}};Y.mix(Y.Selector,Selector,true);})(Y);},'3.0.0',{requires:['dom-base']});YUI.add('selector-css2',function(Y){var PARENT_NODE='parentNode',TAG_NAME='tagName',ATTRIBUTES='attributes',COMBINATOR='combinator',PSEUDOS='pseudos',Selector=Y.Selector,SelectorCSS2={SORT_RESULTS:true,_children:function(node,tag){var ret=node.children,i,children=[],childNodes,child;if(node.children&&tag&&node.children.tags){children=node.children.tags(tag);}else if((!ret&&node[TAG_NAME])||(ret&&tag)){childNodes=ret||node.childNodes;ret=[];for(i=0;(child=childNodes[i++]);){if(child.tagName){if(!tag||tag===child.tagName){ret.push(child);}}}}
return ret||[];},_regexCache:{},_re:{attr:/(\[.*\])/g,pseudos:/:([\-\w]+(?:\(?:['"]?(.+)['"]?\)))*/i},shorthand:{'\\#(-?[_a-z]+[-\\w]*)':'[id=$1]','\\.(-?[_a-z]+[-\\w]*)':'[className~=$1]'},operators:{'':function(node,attr){return Y.DOM.getAttribute(node,attr)!=='';},'~=':'(?:^|\\s+){val}(?:\\s+|$)','|=':'^{val}-?'},pseudos:{'first-child':function(node){return Y.Selector._children(node[PARENT_NODE])[0]===node;}},_bruteQuery:function(selector,root,firstOnly){var ret=[],nodes=[],tokens=Selector._tokenize(selector),token=tokens[tokens.length-1],rootDoc=Y.DOM._getDoc(root),id,className,tagName;if(tokens[0]&&rootDoc===root&&(id=tokens[0].id)&&rootDoc.getElementById(id)){root=rootDoc.getElementById(id);}
if(token){id=token.id;className=token.className;tagName=token.tagName||'*';if(id){if(rootDoc.getElementById(id)){nodes=[rootDoc.getElementById(id)];}}else if(className){nodes=root.getElementsByClassName(className);}else if(tagName){nodes=root.getElementsByTagName(tagName||'*');}
if(nodes.length){ret=Selector._filterNodes(nodes,tokens,firstOnly);}}
return ret;},_filterNodes:function(nodes,tokens,firstOnly){var i=0,j,len=tokens.length,n=len-1,result=[],node=nodes[0],tmpNode=node,getters=Y.Selector.getters,operator,combinator,token,path,pass,value,tests,test;for(i=0;(tmpNode=node=nodes[i++]);){n=len-1;path=null;testLoop:while(tmpNode&&tmpNode.tagName){token=tokens[n];tests=token.tests;j=tests.length;if(j&&!pass){while((test=tests[--j])){operator=test[1];if(getters[test[0]]){value=getters[test[0]](tmpNode,test[0]);}else{value=tmpNode[test[0]];if(value===undefined&&tmpNode.getAttribute){value=tmpNode.getAttribute(test[0]);}}
if((operator==='='&&value!==test[2])||(operator.test&&!operator.test(value))||(operator.call&&!operator(tmpNode,test[0]))){if((tmpNode=tmpNode[path])){while(tmpNode&&(!tmpNode.tagName||(token.tagName&&token.tagName!==tmpNode.tagName))){tmpNode=tmpNode[path];}}
continue testLoop;}}}
n--;if(!pass&&(combinator=token.combinator)){path=combinator.axis;tmpNode=tmpNode[path];while(tmpNode&&!tmpNode.tagName){tmpNode=tmpNode[path];}
if(combinator.direct){path=null;}}else{result.push(node);if(firstOnly){return result;}
break;}}}
node=tmpNode=null;return result;},_getRegExp:function(str,flags){var regexCache=Selector._regexCache;flags=flags||'';if(!regexCache[str+flags]){regexCache[str+flags]=new RegExp(str,flags);}
return regexCache[str+flags];},combinators:{' ':{axis:'parentNode'},'>':{axis:'parentNode',direct:true},'+':{axis:'previousSibling',direct:true}},_parsers:[{name:ATTRIBUTES,re:/^\[([a-z]+\w*)+([~\|\^\$\*!=]=?)?['"]?([^\]]*?)['"]?\]/i,fn:function(match,token){var operator=match[2]||'',operators=Y.Selector.operators,test;if((match[1]==='id'&&operator==='=')||(match[1]==='className'&&document.getElementsByClassName&&(operator==='~='||operator==='='))){token.prefilter=match[1];token[match[1]]=match[3];}
if(operator in operators){test=operators[operator];if(typeof test==='string'){test=Y.Selector._getRegExp(test.replace('{val}',match[3]));}
match[2]=test;}
if(!token.last||token.prefilter!==match[1]){return match.slice(1);}}},{name:TAG_NAME,re:/^((?:-?[_a-z]+[\w-]*)|\*)/i,fn:function(match,token){var tag=match[1].toUpperCase();token.tagName=tag;if(tag!=='*'&&(!token.last||token.prefilter)){return[TAG_NAME,'=',tag];}
if(!token.prefilter){token.prefilter='tagName';}}},{name:COMBINATOR,re:/^\s*([>+~]|\s)\s*/,fn:function(match,token){}},{name:PSEUDOS,re:/^:([\-\w]+)(?:\(['"]?(.+)['"]?\))*/i,fn:function(match,token){var test=Selector[PSEUDOS][match[1]];if(test){return[match[2],test];}else{return false;}}}],_getToken:function(token){return{tagName:null,id:null,className:null,attributes:{},combinator:null,tests:[]};},_tokenize:function(selector){selector=selector||'';selector=Selector._replaceShorthand(Y.Lang.trim(selector));var token=Selector._getToken(),query=selector,tokens=[],found=false,match,test,i,parser;outer:do{found=false;for(i=0;(parser=Selector._parsers[i++]);){if((match=parser.re.exec(selector))){if(parser!==COMBINATOR){token.selector=selector;}
selector=selector.replace(match[0],'');if(!selector.length){token.last=true;}
if(Selector._attrFilters[match[1]]){match[1]=Selector._attrFilters[match[1]];}
test=parser.fn(match,token);if(test===false){found=false;break outer;}else if(test){token.tests.push(test);}
if(!selector.length||parser.name===COMBINATOR){tokens.push(token);token=Selector._getToken(token);if(parser.name===COMBINATOR){token.combinator=Y.Selector.combinators[match[1]];}}
found=true;}}}while(found&&selector.length);if(!found||selector.length){tokens=[];}
return tokens;},_replaceShorthand:function(selector){var shorthand=Selector.shorthand,attrs=selector.match(Selector._re.attr),pseudos=selector.match(Selector._re.pseudos),re,i,len;if(pseudos){selector=selector.replace(Selector._re.pseudos,'!!REPLACED_PSEUDO!!');}
if(attrs){selector=selector.replace(Selector._re.attr,'!!REPLACED_ATTRIBUTE!!');}
for(re in shorthand){if(shorthand.hasOwnProperty(re)){selector=selector.replace(Selector._getRegExp(re,'gi'),shorthand[re]);}}
if(attrs){for(i=0,len=attrs.length;i<len;++i){selector=selector.replace('!!REPLACED_ATTRIBUTE!!',attrs[i]);}}
if(pseudos){for(i=0,len=pseudos.length;i<len;++i){selector=selector.replace('!!REPLACED_PSEUDO!!',pseudos[i]);}}
return selector;},_attrFilters:{'class':'className','for':'htmlFor'},getters:{href:function(node,attr){return Y.DOM.getAttribute(node,attr);}}};Y.mix(Y.Selector,SelectorCSS2,true);Y.Selector.getters.src=Y.Selector.getters.rel=Y.Selector.getters.href;if(Y.Selector.useNative&&document.querySelector){Y.Selector.shorthand['\\.(-?[_a-z]+[-\\w]*)']='[class~=$1]';}},'3.0.0',{requires:['selector-native']});YUI.add('selector',function(Y){},'3.0.0',{use:['selector-native','selector-css2']});