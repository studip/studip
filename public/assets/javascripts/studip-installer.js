!function(n){var r={};function i(e){if(r[e])return r[e].exports;var t=r[e]={i:e,l:!1,exports:{}};return n[e].call(t.exports,t,t.exports,i),t.l=!0,t.exports}i.m=n,i.c=r,i.d=function(e,t,n){i.o(e,t)||Object.defineProperty(e,t,{enumerable:!0,get:n})},i.r=function(e){"undefined"!=typeof Symbol&&Symbol.toStringTag&&Object.defineProperty(e,Symbol.toStringTag,{value:"Module"}),Object.defineProperty(e,"__esModule",{value:!0})},i.t=function(t,e){if(1&e&&(t=i(t)),8&e)return t;if(4&e&&"object"==typeof t&&t&&t.__esModule)return t;var n=Object.create(null);if(i.r(n),Object.defineProperty(n,"default",{enumerable:!0,value:t}),2&e&&"string"!=typeof t)for(var r in t)i.d(n,r,function(e){return t[e]}.bind(null,r));return n},i.n=function(e){var t=e&&e.__esModule?function(){return e.default}:function(){return e};return i.d(t,"a",t),t},i.o=function(e,t){return Object.prototype.hasOwnProperty.call(e,t)},i.p="",i(i.s="SFVy")}({SFVy:function(e,t,n){"use strict";n.r(t);n("iA2q"),n("xOJZ")},iA2q:function(e,t,n){},xOJZ:function(e,t){var n;n=function(){if(!("fetch"in window&&"Promise"in window)){var e=document.createElement("input");return e.setAttribute("type","hidden"),e.setAttribute("name","basic"),e.setAttribute("value",1),void document.querySelector("form").append(e)}var n=[];document.querySelectorAll("dl.requests > dt[data-request-url]").forEach(function(e){n.push({element:e,url:e.dataset.requestUrl,event_source:void 0!==e.dataset.eventSource})}),function t(){if(0!==n.length){var e,a=n.shift();if(a.element.classList.add("requesting"),a.event_source&&"EventSource"in window){var u=document.createElement("div");u.setAttribute("data-percent",0),(e=new Promise(function(t,n){a.element.classList.add("event-sourced");var r=a.element.nextElementSibling.nextElementSibling.nextElementSibling,i=0;r.insertAdjacentElement("afterend",u),u.setAttribute("style","left: ".concat(r.offsetLeft,"px; top: ").concat(r.offsetTop,"px"));var o=new EventSource(a.url+"?evts=1",{withCredentials:!0});o.addEventListener("total",function(e){i=parseInt(e.data,10),r.setAttribute("max",i)}),o.addEventListener("file",function(e){u.setAttribute("data-file",e.data)}),o.addEventListener("current",function(e){var t=parseInt(e.data,10);r.setAttribute("value",t),u.setAttribute("data-percent",(100*t/i).toFixed(2))}),o.addEventListener("error",function(e){o.close(),n(e.data||"Fehler beim Installieren")}),o.addEventListener("close",function(e){o.close(),t()})})).finally(function(){u.parentNode&&u.parentNode.removeChild(u),a.element.classList.remove("event-sourced")})}else e=fetch(a.url,{cache:"no-cache",credentials:"same-origin"}).then(function(e){if(!e.ok)return e.json().then(function(e){return Promise.reject(e)})});e.then(function(e){a.element.classList.add("succeeded"),t()}).catch(function(n){a.element.classList.add("failed"),null!==n&&n===Object(n)?a.element.nextElementSibling.nextElementSibling.querySelectorAll(".response").forEach(function(e){var t=e.dataset.key;e.value=n[t]}):a.element.nextElementSibling.nextElementSibling.querySelector(".response").innerText=n}).finally(function(){a.element.classList.remove("requesting")})}}()},"complete"===document.readyState||"interactive"===document.readyState?setTimeout(n,1):document.addEventListener("DOMContentLoaded",n)}});
//# sourceMappingURL=studip-installer.js.map