'use strict';(function(h){"object"==typeof exports&&"object"==typeof module?h(require("../../lib/codemirror")):"function"==typeof define&&define.amd?define(["../../lib/codemirror"],h):h(CodeMirror)})(function(h){h.defineMode("tiki",function(h){function e(a,b,c){return function(d,e){for(;!d.eol();){if(d.match(b)){e.tokenize=g;break}d.next()}c&&(e.tokenize=c);return a}}function r(a){return function(b,c){for(;!b.eol();)b.next();c.tokenize=g;return a}}function g(a,b){function c(c){b.tokenize=c;return c(a,
b)}var d=a.sol(),f=a.next();switch(f){case "{":return a.eat("/"),a.eatSpace(),a.eatWhile(/[^\s\u00a0="'\/?(}]/),b.tokenize=n,"tag";case "_":if(a.eat("_"))return c(e("strong","__",g));break;case "'":if(a.eat("'"))return c(e("em","''",g));break;case "(":if(a.eat("("))return c(e("variable-2","))",g));break;case "[":return c(e("variable-3","]",g));case "|":if(a.eat("|"))return c(e("comment","||"));break;case "-":if(a.eat("="))return c(e("header string","=-",g));if(a.eat("-"))return c(e("error tw-deleted",
"--",g));break;case "=":if(a.match("=="))return c(e("tw-underline","===",g));break;case ":":if(a.eat(":"))return c(e("comment","::"));break;case "^":return c(e("tw-box","^"));case "~":if(a.match("np~"))return c(e("meta","~/np~"))}if(d)switch(f){case "!":return a.match("!!!!!")||a.match("!!!!")||a.match("!!!")||a.match("!!"),c(r("header string"));case "*":case "#":case "+":return c(r("tw-listitem bracket"))}return null}function n(a,b){var c=a.next(),d=a.peek();if("}"==c)return b.tokenize=g,"tag";if("("==
c||")"==c)return"bracket";if("="==c)return m="equals",">"==d&&(a.next(),d=a.peek()),/['"]/.test(d)||(b.tokenize=v()),"operator";if(/['"]/.test(c))return b.tokenize=w(c),b.tokenize(a,b);a.eatWhile(/[^\s\u00a0="'\/?]/);return"keyword"}function w(a){return function(b,c){for(;!b.eol();)if(b.next()==a){c.tokenize=n;break}return"string"}}function v(){return function(a,b){for(;!a.eol();){var c=a.next(),d=a.peek();if(" "==c||","==c||/[ )}]/.test(d)){b.tokenize=n;break}}return"string"}}function l(){for(var a=
arguments.length-1;0<=a;a--)d.cc.push(arguments[a])}function f(){l.apply(null,arguments);return!0}function t(a,b){d.context={prev:d.context,pluginName:a,indent:d.indented,startOfLine:b,noIndent:d.context&&d.context.noIndent}}function x(a){if("openPlugin"==a)return d.pluginName=p,f(q,y(d.startOfLine));if("closePlugin"==a)return d.context?(a=d.context.pluginName!=p,d.context&&(d.context=d.context.prev)):a=!0,a&&(k="error"),f(z(a));"string"==a&&(d.context&&"!cdata"==d.context.name||t("!cdata"),d.tokenize==
g&&d.context&&(d.context=d.context.prev));return f()}function y(a){return function(b){if("selfclosePlugin"==b||"endPlugin"==b)return f();"endPlugin"==b&&t(d.pluginName,a);return f()}}function z(a){return function(b){a&&(k="error");return"endPlugin"==b?f():l()}}function q(a){return"keyword"==a?(k="attribute",f(q)):"equals"==a?f(A,q):l()}function A(a){return"keyword"==a?(k="string",f()):"string"==a?f(u):l()}function u(a){return"string"==a?f(u):l()}var B=h.indentUnit,p,m,d,k;return{startState:function(){return{tokenize:g,
cc:[],indented:0,startOfLine:!0,pluginName:null,context:null}},token:function(a,b){a.sol()&&(b.startOfLine=!0,b.indented=a.indentation());if(a.eatSpace())return null;k=m=p=null;if(((a=b.tokenize(a,b))||m)&&"comment"!=a)for(d=b;!(b.cc.pop()||x)(m||a););b.startOfLine=!1;return k||a},indent:function(a,b){if((a=a.context)&&a.noIndent)return 0;a&&/^{\//.test(b)&&(a=a.prev);for(;a&&!a.startOfLine;)a=a.prev;return a?a.indent+B:0},electricChars:"/"}});h.defineMIME("text/tiki","tiki")});