!function(){var e={4743:function(){var e;e=function(){if(!("fetch"in window)||!("Promise"in window)){var e=document.createElement("input");return e.setAttribute("type","hidden"),e.setAttribute("name","basic"),e.setAttribute("value",1),void document.querySelector("form").append(e)}var t=[];document.querySelectorAll("dl.requests > dt[data-request-url]").forEach((function(e){t.push({element:e,url:e.dataset.requestUrl,event_source:void 0!==e.dataset.eventSource})})),function e(){if(0!==t.length){var n,r=t.shift();if(r.element.classList.add("requesting"),r.event_source&&"EventSource"in window){var i=document.createElement("div");i.setAttribute("data-percent",0),(n=new Promise((function(e,t){r.element.classList.add("event-sourced");var n=r.element.nextElementSibling.nextElementSibling.nextElementSibling,o=0;n.insertAdjacentElement("afterend",i),i.setAttribute("style","left: ".concat(n.offsetLeft,"px; top: ").concat(n.offsetTop,"px"));var a=new EventSource(r.url+"?evts=1",{withCredentials:!0});a.addEventListener("total",(function(e){o=parseInt(e.data,10),n.setAttribute("max",o)})),a.addEventListener("file",(function(e){i.setAttribute("data-file",e.data)})),a.addEventListener("current",(function(e){var t=parseInt(e.data,10);n.setAttribute("value",t),i.setAttribute("data-percent",(100*t/o).toFixed(2))})),a.addEventListener("error",(function(e){a.close(),t(e.data||"Fehler beim Installieren")})),a.addEventListener("close",(function(t){a.close(),e()}))}))).finally((function(){i.parentNode&&i.parentNode.removeChild(i),r.element.classList.remove("event-sourced")}))}else n=fetch(r.url,{cache:"no-cache",credentials:"same-origin"}).then((function(e){if(!e.ok)return e.json().then((function(e){return Promise.reject(e)}))}));n.then((function(t){r.element.classList.add("succeeded"),e()})).catch((function(e){r.element.classList.add("failed"),null!==e&&e===Object(e)?r.element.nextElementSibling.nextElementSibling.querySelectorAll(".response").forEach((function(t){var n=t.dataset.key;t.value=e[n]})):r.element.nextElementSibling.nextElementSibling.querySelector(".response").innerText=e})).finally((function(){r.element.classList.remove("requesting")}))}}()},"complete"===document.readyState||"interactive"===document.readyState?setTimeout(e,1):document.addEventListener("DOMContentLoaded",e)}},t={};function n(r){if(t[r])return t[r].exports;var i=t[r]={exports:{}};return e[r](i,i.exports,n),i.exports}n.n=function(e){var t=e&&e.__esModule?function(){return e.default}:function(){return e};return n.d(t,{a:t}),t},n.d=function(e,t){for(var r in t)n.o(t,r)&&!n.o(e,r)&&Object.defineProperty(e,r,{enumerable:!0,get:t[r]})},n.o=function(e,t){return Object.prototype.hasOwnProperty.call(e,t)},function(){"use strict";n(4743)}()}();
//# sourceMappingURL=studip-installer.js.map