(window.webpackJsonp=window.webpackJsonp||[]).push([[1],{SsKN:function(e,t,n){"use strict";n.r(t);n("eV1F"),n("pDWP"),n("FZkX"),n("fARK"),n("c+2l"),n("pBon");var a=n("SZB9"),i=n("TPju"),r=n.n(i),o=n("kvoc"),s=n.n(o),d=n("Gbwi"),l=n("iOEq"),c=n("p8AH"),u=n("0yvR"),v=n("6yPs"),p=n("PQoC"),g=n("cH8c"),h=n.n(g),f=n("wOnQ"),m=n.n(f);function w(e,t){for(var n=0;n<t.length;n++){var a=t[n];a.enumerable=a.enumerable||!1,a.configurable=!0,"value"in a&&(a.writable=!0),Object.defineProperty(e,a.key,a)}}function S(e){var t=1<arguments.length&&void 0!==arguments[1]?arguments[1]:2,n=2<arguments.length&&void 0!==arguments[2]?arguments[2]:"0",a=new Array(t+1).join(n);return"".concat(a).concat(e).substr(-t)}Date.prototype.getWeekNumber=function(){var e=new Date(Date.UTC(this.getFullYear(),this.getMonth(),this.getDate())),t=e.getUTCDay()||7;e.setUTCDate(e.getUTCDate()+4-t);var n=new Date(Date.UTC(e.getUTCFullYear(),0,1));return Math.ceil(((e-n)/864e5+1)/7)};var D=function(){function e(){!function(e,t){if(!(e instanceof t))throw new TypeError("Cannot call a class as a function")}(this,e)}return function(e,t,n){t&&w(e.prototype,t),n&&w(e,n)}(e,null,[{key:"init",value:function(e){var t=1<arguments.length&&void 0!==arguments[1]?arguments[1]:null;if(e=$(e)[0]){document.getElementById("external-events")&&new d.a(document.getElementById("external-events"),{itemSelector:".fc-event",eventData:function(e){return{title:e.dataset.eventTitle,duration:e.dataset.eventDuration,course_id:e.dataset.eventCourse,tooltip:e.dataset.eventTooltip,studip_api_urls:{drop:e.dataset.eventDropUrl},studip_view_urls:{edit:e.dataset.eventDetailsUrl}}}});var n=new a.a(e,t);return(e.calendar=n).render(),n}}},{key:"convertSemesterEvents",value:function(e){if(!e)return{};var t=String(e.start).split("T"),n=String(e.end).split("T"),a=new Date;a.setHours(12),a.setMinutes(0),a.setSeconds(0);var i=new Date;i.setHours(12),i.setMinutes(0),i.setSeconds(0);var r=a.getDay()||7,o=i.getDay()||7;return r-=e.studip_weekday_begin,o-=e.studip_weekday_end,a=new Date(a.getTime()-24*r*60*60*1e3),i=new Date(i.getTime()-24*o*60*60*1e3),e.start=[a.getFullYear(),+S(a.getMonth()+1),+S(a.getDate())].join("-")+"T"+t[1],e.end=[i.getFullYear(),S(i.getMonth()+1),S(i.getDate())].join("-")+"T"+n[1],e}},{key:"createSemesterCalendarFromNode",value:function(e){var t=1<arguments.length&&void 0!==arguments[1]?arguments[1]:{};if(e){var n=$.extend({},$(e).data("config")||{},t);return Array.isArray(n.eventSources)&&(n.eventSources=n.eventSources.map(function(e){return e.hasOwnProperty("url")?e:$.extend({eventDataTransform:STUDIP.Fullcalendar.convertSemesterEvents},e)})),this.createFromNode(e,n)}}},{key:"defaultResizeEventHandler",value:function(e){e.event.durationEditable&&e.view.viewSpec.options.editable?e.event.extendedProps.studip_api_urls.resize&&$.post({url:e.event.extendedProps.studip_api_urls.resize,async:!1,data:{begin:this.toRFC3339String(e.event.start),end:this.toRFC3339String(e.event.end)}}).fail(e.revert):e.revert()}},{key:"downloadPDF",value:function(){var i=0<arguments.length&&void 0!==arguments[0]?arguments[0]:"landscape",t=1<arguments.length&&void 0!==arguments[1]&&arguments[1];$('*[data-fullcalendar="1"]').each(function(){if(null!=this.calendar){$(this).addClass("print-view").toggleClass("without-weekend",!t);var a=$(this).data("title"),e=$("<h1>").text(a).prependTo(this);window.scrollTo(0,0),m()(this).then(function(e){var t=e.toDataURL("image/jpeg"),n=new h.a({orientation:"landscape"===i?"landscape":"portrait"});"landscape"===i?n.addImage(t,"JPEG",20,20,250,250,"i1","NONE",0):n.addImage(t,"JPEG",25,20,160,190,"i1","NONE",0),n.save(a+".pdf")}),e.remove(),$(this).removeClass("print-view without-weekend")}})}},{key:"toRFC3339String",value:function(e){var t,n=e.getTimezoneOffset(),a=parseInt(Math.abs(n/60),10),i=Math.abs(n%60);a=S(a),i=S(i),t=n<0?"+".concat(a,":").concat(i):0<n?"-".concat(a,":").concat(i):"+00:00";var r=S(e.getDate()),o=S(e.getMonth()+1),s=e.getFullYear(),d=S(e.getHours()),l=S(e.getMinutes()),c=S(e.getSeconds());return"".concat(s,"-").concat(o,"-").concat(r,"T").concat(d,":").concat(l,":").concat(c)+t}},{key:"defaultDropEventHandler",value:function(e){if(e.event.startEditable&&e.view.viewSpec.options.editable){var t=e.newResource?e.newResource.id:e.event.extendedProps.studip_range_id;if(e.event.extendedProps.studip_api_urls.move)if(e.event.allDay)$.post({async:!1,url:e.event.extendedProps.studip_api_urls.move,data:{resource_id:t,begin:this.toRFC3339String(e.event.start.setHours(0,0,0)),end:this.toRFC3339String(e.event.start.setHours(23,59,59))}}).fail(e.revert);else if(null===e.event.end){var n=new Date;n.setTime(e.event.start.getTime()),n.setHours(e.event.start.getHours()+2),$.post({async:!1,url:e.event.extendedProps.studip_api_urls.move,data:{resource_id:t,begin:this.toRFC3339String(e.event.start),end:this.toRFC3339String(n)}}).fail(e.revert)}else $.post({async:!1,url:e.event.extendedProps.studip_api_urls.move,data:{resource_id:t,begin:this.toRFC3339String(e.event.start),end:this.toRFC3339String(e.event.end)}}).fail(e.revert)}else e.revert()}},{key:"institutePlanDropEventHandler",value:function(e){if(e.newResource)$.post({async:!1,url:e.event.extendedProps.studip_api_urls.move,data:{cycle_id:e.event.id,resource_id:e.newResource.id,begin:this.toRFC3339String(e.event.start),end:this.toRFC3339String(e.event.end)}}).fail(e.revert);else{if(!e.event.startEditable||!e.view.viewSpec.options.editable)return void e.revert();$.post({async:!1,url:e.event.extendedProps.studip_api_urls.move,data:{cycle_id:e.event.id,begin:this.toRFC3339String(e.event.start),end:this.toRFC3339String(e.event.end)}}).fail(e.revert)}}},{key:"institutePlanExternalDropEventHandler",value:function(t){var e=t.event.getResources().map(function(e){return e.id});$.post({async:!1,url:t.event.extendedProps.studip_api_urls.drop,data:{course_id:t.event.extendedProps.course_id,begin:this.toRFC3339String(t.event.start),end:this.toRFC3339String(t.event.end),resource_id:e[0]}}).done(function(e){e&&(t.view.calendar.addEvent(JSON.parse(e)),t.event.remove())})}},{key:"createFromNode",value:function(e){var t=1<arguments.length&&void 0!==arguments[1]?arguments[1]:{};if(e){var a=$(e).data("config");return a=$.extend({plugins:[d.b,l.g,c.e,u.k,v.a,p.a],schedulerLicenseKey:"GPL-My-Project-Is-Open-Source",defaultView:"timeGridWeek",header:{left:"dayGridMonth,timeGridWeek,timeGridDay"},minTime:"08:00:00",maxTime:"20:00:00",height:"auto",contentHeight:"auto",firstDay:1,weekNumberCalculation:"ISO",locales:[s.a,r.a],locale:"de-DE"===String.locale?"de":"en-gb",timeFormat:"H:mm",nowIndicator:!0,timeZone:"local",studip_functions:[],resourceAreaWidth:"20%",select:function(e){e.view.viewSpec.options.editable&&e.view.viewSpec.options.studip_urls&&e.view.viewSpec.options.studip_urls.add&&(e.resource?STUDIP.Dialog.fromURL(e.view.viewSpec.options.studip_urls.add,{data:{begin:e.start.getTime()/1e3,end:e.end.getTime()/1e3,ressource_id:e.resource.id}}):STUDIP.Dialog.fromURL(e.view.viewSpec.options.studip_urls.add,{data:{begin:e.start.getTime()/1e3,end:e.end.getTime()/1e3}}))},eventClick:function(e){var t=e.event,n=t.extendedProps;return $(e.jsEvent.target).hasClass("event-colorpicker")?(STUDIP.Dialog.fromURL(STUDIP.URLHelper.getURL("dispatch.php/admin/courseplanning/pick_color/"+n.metadate_id+"/"+a.actionCalled),{size:"400x400"}),!1):void 0!==n.studip_view_urls?(!t.startEditable&&n.studip_view_urls.show?STUDIP.Dialog.fromURL(STUDIP.URLHelper.getURL(n.studip_view_urls.show)):t.startEditable&&n.studip_view_urls.edit&&STUDIP.Dialog.fromURL(STUDIP.URLHelper.getURL(n.studip_view_urls.edit)),!1):void 0},eventResize:function(e){e.view.viewSpec.options.studip_functions.resize_event?e.view.viewSpec.options.studip_functions.resize_event(e):STUDIP.Fullcalendar.defaultResizeEventHandler(e),e.event.source.refetch()},eventDrop:function(e){if($(e.view.calendar.el).hasClass("institute-plan")){var t=e.event.start,n=e.view.activeStart;(t.getHours()-n.getHours())%2==1&&e.event.moveDates("-01:00"),STUDIP.Fullcalendar.institutePlanDropEventHandler(e)}else e.view.viewSpec.options.studip_functions.drop_event?e.view.viewSpec.options.studip_functions.drop_event(e):STUDIP.Fullcalendar.defaultDropEventHandler(e),e.event.source.refetch()},eventRender:function(e){var t=e.event,n=e.el,a="#000000"==t.textColor?"black":"white";$(e.view.calendar.el).hasClass("institute-plan")?($(n).attr("title",t.extendedProps.tooltip),$(n).find(".fc-title").html($("<div>").css({width:"calc(100% - 21px)",height:"100%",wordBreak:"break-word"}).text(n.text)),$(n).find(".fc-title").append($('<button class="event-colorpicker">').addClass(a))):$(n).attr("title",t.title),t.extendedProps.icon&&$(n).find(".fc-title").prepend($("<img>").attr("src","".concat(STUDIP.ASSETS_URL,"images/icons/").concat(a,"/").concat(t.extendedProps.icon,".svg")).css({verticalAlign:"text-bottom",marginRight:"3px",width:14,height:14}))},loading:function(e){e?$("#loading-spinner").length||$(".fullcalendar-header").after($('<div id="loading-spinner" style="margin-top: 5px; text-align:center;">').html($("<img>").attr("src",STUDIP.ASSETS_URL+"images/ajax-indicator-black.svg").css({width:64,height:64}))):($("#loading-spinner").empty(),this.updateSize())},datesRender:function(e){var t=e.view,n=t.activeStart,a=t.activeEnd;if($(t.calendar.el).hasClass("institute-plan")&&$(".fc-slats tr:odd .fc-widget-content:not(.fc-axis)").remove(),$(".booking-plan-header").length){a.setDate(a.getDate()-1);var i=$(".booking-plan-header").data("semester-begin"),r=$(".booking-plan-header").data("semester-end");if(n.getTime()/1e3<i||n.getTime()/1e3>r)r=i=null;else{var o=Math.floor((n.getTime()/1e3-i)/604800)+1;$("#booking-plan-header-semweek").text(o)}$("#booking-plan-header-calweek").text(n.getWeekNumber()),$("#booking-plan-header-calbegin").text(n.toLocaleDateString("de-DE",{weekday:"short"})+" "+n.toLocaleDateString("de-DE")),$("#booking-plan-header-calend").text(a.toLocaleDateString("de-DE",{weekday:"short"})+" "+a.toLocaleDateString("de-DE")),i&&r||STUDIP.Resources.updateBookingPlanSemesterByView(t)}},columnHeaderHtml:function(e){return $("*[data-fullcalendar='1']").hasClass("institute-plan")?'<a href="'+STUDIP.URLHelper.getURL("dispatch.php/admin/courseplanning/weekday/"+e.getDay())+'">'+e.toLocaleDateString("de-DE",a.columnHeaderFormat)+"</a>":e.toLocaleDateString("de-DE",a.columnHeaderFormat)},resourceRender:function(e){if($(e.view.calendar.el).hasClass("room-group-booking-plan")){var t=$(e.view.calendar.el).hasClass("semester-plan")?"semester":"booking",n=STUDIP.URLHelper.getURL("dispatch.php/resources/room_planning/".concat(t,"_plan/").concat(e.resource.id));$(e.el).find(".fc-cell-text").html($("<a>").attr("href",n).text(e.el.innerText).append($("<img>").attr("src",STUDIP.ASSETS_URL+"images/icons/blue/link-intern.svg").addClass("text-bottom").css({width:16,height:16,margin:"0px 5px"})))}else if($("*[data-fullcalendar='1']").hasClass("institute-plan")&&0<e.resource.id){var a='<img class="text-bottom icon-role-clickable icon-shape-edit" width="16" height="16" src="'+STUDIP.URLHelper.getURL("assets/images/icons/blue/edit.svg")+'" alt="edit">';$(e.el).append('<a href="'+STUDIP.URLHelper.getURL("dispatch.php/admin/courseplanning/rename_column/"+e.resource.id+"/"+e.view.activeStart.getDay())+'" data-dialog="size=auto"> '+a+"</a>")}},drop:function(e){$(e.draggedEl).remove()},eventReceive:function(e){$(e.view.calendar.el).hasClass("institute-plan")&&STUDIP.Fullcalendar.institutePlanExternalDropEventHandler(e)}},a),a=$.extend({},a,t),this.init(e,a)}}}]),e}();STUDIP.Fullcalendar=D},pBon:function(e,t,n){}}]);
//# sourceMappingURL=fullcalendar-19cb981342fc7d69331d.chunk.js.map