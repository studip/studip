(self.webpackChunk_studip_core=self.webpackChunk_studip_core||[]).push([[630],{2320:function(e,t,n){"use strict";n.r(t);var a=n(7081),i=n(6091),r=n.n(i),s=n(7278),o=n.n(s),l=n(1514),d=n(4176),c=n(9340),u=n(8401),v=n(5955),p=n(1448),g=n(219),h=n.n(g),f=n(1120),m=n.n(f);function w(e,t){for(var n=0;n<t.length;n++){var a=t[n];a.enumerable=a.enumerable||!1,a.configurable=!0,"value"in a&&(a.writable=!0),Object.defineProperty(e,a.key,a)}}function _(e){var t=arguments.length>1&&void 0!==arguments[1]?arguments[1]:2,n=arguments.length>2&&void 0!==arguments[2]?arguments[2]:"0",a=new Array(t+1).join(n);return"".concat(a).concat(e).substr(-t)}Date.prototype.getWeekNumber=function(){var e=new Date(Date.UTC(this.getFullYear(),this.getMonth(),this.getDate())),t=e.getUTCDay()||7;e.setUTCDate(e.getUTCDate()+4-t);var n=new Date(Date.UTC(e.getUTCFullYear(),0,1));return Math.ceil(((e-n)/864e5+1)/7)};var S=function(){function e(){!function(e,t){if(!(e instanceof t))throw new TypeError("Cannot call a class as a function")}(this,e)}var t,n,i;return t=e,i=[{key:"init",value:function(e){var t=arguments.length>1&&void 0!==arguments[1]?arguments[1]:null;if(e=$(e)[0]){document.getElementById("external-events")&&new l._l(document.getElementById("external-events"),{itemSelector:".fc-event",eventData:function(e){return{title:e.dataset.eventTitle,duration:e.dataset.eventDuration,course_id:e.dataset.eventCourse,tooltip:e.dataset.eventTooltip,studip_api_urls:{drop:e.dataset.eventDropUrl},studip_view_urls:{edit:e.dataset.eventDetailsUrl}}}});var n=new a.f(e,t);return e.calendar=n,n.render(),n}}},{key:"convertSemesterEvents",value:function(e){var t=arguments.length>1&&void 0!==arguments[1]?arguments[1]:Date();if(!e)return{};var n=String(e.start).split("T"),a=String(e.end).split("T"),i=new Date(t);i.setHours(12),i.setMinutes(0),i.setSeconds(0);var r=new Date(t);r.setHours(12),r.setMinutes(0),r.setSeconds(0);var s=i.getDay()||7,o=r.getDay()||7;return s-=e.studip_weekday_begin,o-=e.studip_weekday_end,i=new Date(i.getTime()-24*s*60*60*1e3),r=new Date(r.getTime()-24*o*60*60*1e3),e.start="".concat(i.getFullYear(),"-").concat(_(i.getMonth()+1),"-").concat(_(i.getDate()),"T").concat(n[1]),e.end="".concat(r.getFullYear(),"-").concat(_(r.getMonth()+1),"-").concat(_(r.getDate()),"T").concat(a[1]),e}},{key:"createSemesterCalendarFromNode",value:function(e){var t=arguments.length>1&&void 0!==arguments[1]?arguments[1]:{};if(e){var n=$.extend({},$(e).data("config")||{},t);return Array.isArray(n.eventSources)&&(n.eventSources=n.eventSources.map((function(e){if(e.hasOwnProperty("url"))return e}))),this.createFromNode(e,n)}}},{key:"defaultResizeEventHandler",value:function(e){e.event.durationEditable&&e.view.viewSpec.options.editable?e.event.extendedProps.studip_api_urls.resize&&$.post({url:e.event.extendedProps.studip_api_urls.resize,async:!1,data:{begin:this.toRFC3339String(e.event.start),end:this.toRFC3339String(e.event.end)}}).fail(e.revert):e.revert()}},{key:"downloadPDF",value:function(){var e=arguments.length>0&&void 0!==arguments[0]?arguments[0]:"landscape",t=arguments.length>1&&void 0!==arguments[1]&&arguments[1];$('*[data-fullcalendar="1"]').each((function(){if(null!=this.calendar){$(this).addClass("print-view").toggleClass("without-weekend",!t);var n=$(this).data("title"),a=$("<h1>").text(n).prependTo(this);window.scrollTo(0,0),m()(this).then((function(t){var a=t.toDataURL("image/jpeg"),i=new(h())({orientation:"landscape"===e?"landscape":"portrait"});"landscape"===e?i.addImage(a,"JPEG",20,20,250,250,"i1","NONE",0):i.addImage(a,"JPEG",25,20,160,190,"i1","NONE",0),i.save(n+".pdf")})),a.remove(),$(this).removeClass("print-view without-weekend")}}))}},{key:"toRFC3339String",value:function(e){var t,n=e.getTimezoneOffset(),a=parseInt(Math.abs(n/60),10),i=Math.abs(n%60);a=_(a),i=_(i),t=n<0?"+".concat(a,":").concat(i):n>0?"-".concat(a,":").concat(i):"+00:00";var r=_(e.getDate()),s=_(e.getMonth()+1),o=e.getFullYear(),l=_(e.getHours()),d=_(e.getMinutes()),c=_(e.getSeconds());return"".concat(o,"-").concat(s,"-").concat(r,"T").concat(l,":").concat(d,":").concat(c)+t}},{key:"defaultDropEventHandler",value:function(e){if(e.event.startEditable&&e.view.viewSpec.options.editable){var t=e.newResource?e.newResource.id:e.event.extendedProps.studip_range_id;if(e.event.extendedProps.studip_api_urls.move)if(e.event.allDay)$.post({async:!1,url:e.event.extendedProps.studip_api_urls.move,data:{resource_id:t,begin:this.toRFC3339String(e.event.start.setHours(0,0,0)),end:this.toRFC3339String(e.event.start.setHours(23,59,59))}}).fail(e.revert);else if(null===e.event.end){var n=new Date;n.setTime(e.event.start.getTime()),n.setHours(e.event.start.getHours()+2),$.post({async:!1,url:e.event.extendedProps.studip_api_urls.move,data:{resource_id:t,begin:this.toRFC3339String(e.event.start),end:this.toRFC3339String(n)}}).fail(e.revert)}else $.post({async:!1,url:e.event.extendedProps.studip_api_urls.move,data:{resource_id:t,begin:this.toRFC3339String(e.event.start),end:this.toRFC3339String(e.event.end)}}).fail(e.revert)}else e.revert()}},{key:"institutePlanDropEventHandler",value:function(e){if(e.newResource)$.post({async:!1,url:e.event.extendedProps.studip_api_urls.move,data:{cycle_id:e.event.id,resource_id:e.newResource.id,begin:this.toRFC3339String(e.event.start),end:this.toRFC3339String(e.event.end)}}).fail(e.revert);else{if(!e.event.startEditable||!e.view.viewSpec.options.editable)return void e.revert();$.post({async:!1,url:e.event.extendedProps.studip_api_urls.move,data:{cycle_id:e.event.id,begin:this.toRFC3339String(e.event.start),end:this.toRFC3339String(e.event.end)}}).fail(e.revert)}}},{key:"institutePlanExternalDropEventHandler",value:function(e){var t=e.event.getResources().map((function(e){return e.id}));$.post({async:!1,url:e.event.extendedProps.studip_api_urls.drop,data:{course_id:e.event.extendedProps.course_id,begin:this.toRFC3339String(e.event.start),end:this.toRFC3339String(e.event.end),resource_id:t[0]}}).done((function(t){t&&(e.view.context.calendar.addEvent(JSON.parse(t)),e.event.remove())}))}},{key:"createFromNode",value:function(e){var t=arguments.length>1&&void 0!==arguments[1]?arguments[1]:{};if(e){var n=$(e).data("config");return n=$.extend({plugins:[l.ZP,d.ZP,c.ZP,u.ZP,v.ZP,p.Z],schedulerLicenseKey:"GPL-My-Project-Is-Open-Source",defaultView:"timeGridWeek",header:{left:"dayGridMonth,timeGridWeek,timeGridDay"},minTime:"08:00:00",maxTime:"20:00:00",height:"auto",contentHeight:"auto",firstDay:1,weekNumberCalculation:"ISO",locales:[o(),r()],locale:"de-DE"===String.locale?"de":"en-gb",timeFormat:"H:mm",nowIndicator:!0,timeZone:"local",studip_functions:[],resourceAreaWidth:"20%",select:function(e){e.view.viewSpec.options.editable&&e.view.viewSpec.options.studip_urls&&e.view.viewSpec.options.studip_urls.add&&(e.resource?STUDIP.Dialog.fromURL(e.view.viewSpec.options.studip_urls.add,{data:{begin:e.start.getTime()/1e3,end:e.end.getTime()/1e3,ressource_id:e.resource.id}}):STUDIP.Dialog.fromURL(e.view.viewSpec.options.studip_urls.add,{data:{begin:e.start.getTime()/1e3,end:e.end.getTime()/1e3}}))},eventClick:function(e){var t=e.event,a=t.extendedProps;return $(e.jsEvent.target).hasClass("event-colorpicker")?(STUDIP.Dialog.fromURL(STUDIP.URLHelper.getURL("dispatch.php/admin/courseplanning/pick_color/"+a.metadate_id+"/"+n.actionCalled),{size:"400x400"}),!1):$(e.event._calendar.el).hasClass("request-plan")?(a.request_id&&a.studip_view_urls.edit?STUDIP.Dialog.fromURL(STUDIP.URLHelper.getURL(a.studip_view_urls.edit)):"ResourceBooking"==a.studip_parent_object_class&&-1!=$.inArray("for-course",t._def.ui.classNames)&&STUDIP.Dialog.fromURL(STUDIP.URLHelper.getURL("dispatch.php/resources/room_request/rerequest_booking/"+a.studip_parent_object_id)),!1):void 0!==a.studip_view_urls?(!t.startEditable&&a.studip_view_urls.show?STUDIP.Dialog.fromURL(STUDIP.URLHelper.getURL(a.studip_view_urls.show)):t.startEditable&&a.studip_view_urls.edit&&STUDIP.Dialog.fromURL(STUDIP.URLHelper.getURL(a.studip_view_urls.edit)),!1):void 0},eventResize:function(e){e.view.viewSpec.options.studip_functions.resize_event?e.view.viewSpec.options.studip_functions.resize_event(e):STUDIP.Fullcalendar.defaultResizeEventHandler(e),e.event.source.refetch()},eventDrop:function(e){if($(e.view.context.calendar.el).hasClass("institute-plan")){var t=e.event.start,n=e.view.activeStart;(t.getHours()-n.getHours())%2==1&&e.event.moveDates("-01:00"),STUDIP.Fullcalendar.institutePlanDropEventHandler(e)}else e.view.viewSpec.options.studip_functions.drop_event?e.view.viewSpec.options.studip_functions.drop_event(e):STUDIP.Fullcalendar.defaultDropEventHandler(e),e.event.source.refetch()},eventRender:function(e){var t=e.event,n=e.el,a="#000000"==t.textColor?"black":"white";$(e.view.context.calendar.el).hasClass("institute-plan")?($(n).attr("title",t.extendedProps.tooltip),$(n).find(".fc-title").html($("<div>").css({width:"calc(100% - 21px)",height:"100%",wordBreak:"break-word"}).text(n.text)),$(n).find(".fc-title").append($('<button class="event-colorpicker">').addClass(a))):$(n).attr("title",t.title),t.extendedProps.icon&&$(n).find(".fc-title").prepend($("<img>").attr("src","".concat(STUDIP.ASSETS_URL,"images/icons/").concat(a,"/").concat(t.extendedProps.icon,".svg")).css({verticalAlign:"text-bottom",marginRight:"3px",width:14,height:14}))},eventSourceSuccess:function(t,a){return $(e).hasClass("semester-plan")&&$(t).each((function(e,t){STUDIP.Fullcalendar.convertSemesterEvents(t,n.defaultDate)})),t},loading:function(e){e?$("#loading-spinner").length||jQuery("#layout_content").append($('<div id="loading-spinner" style="position: absolute; top: calc(50% - 55px); left: calc(50% + 135px); z-index: 9001;">').html($("<img>").attr("src",STUDIP.ASSETS_URL+"images/ajax-indicator-black.svg").css({width:64,height:64}))):($("#loading-spinner").remove(),this.updateSize())},datesRender:function(e){var t=e.view.props.dateProfile.activeRange,n=t.start,a=t.end;if($(e.el).hasClass("institute-plan")&&$(".fc-slats tr:odd .fc-widget-content:not(.fc-axis)").remove(),$(".booking-plan-header").length){a.setDate(a.getDate());var i=$(".booking-plan-header").data("semester-begin"),r=$(".booking-plan-header").data("semester-end");if(n.getTime()/1e3<i||n.getTime()/1e3>r)i=null,r=null;else{var s=Math.floor((a.getTime()/1e3-i)/604800)+1;$("#booking-plan-header-semweek").text(s)}$("#booking-plan-header-calweek").text(n.getWeekNumber()),$("#booking-plan-header-calbegin").text(n.toLocaleDateString("de-DE",{weekday:"short"})+" "+n.toLocaleDateString("de-DE")),$("#booking-plan-header-calend").text(a.toLocaleDateString("de-DE",{weekday:"short"})+" "+a.toLocaleDateString("de-DE")),i&&r||STUDIP.Resources.updateBookingPlanSemesterByView(t)}},columnHeaderHtml:function(e){return $("*[data-fullcalendar='1']").hasClass("institute-plan")?'<a href="'+STUDIP.URLHelper.getURL("dispatch.php/admin/courseplanning/weekday/"+e.getDay())+'">'+e.toLocaleDateString("de-DE",n.columnHeaderFormat)+"</a>":e.toLocaleDateString("de-DE",n.columnHeaderFormat)},resourceRender:function(e){if($(e.view.context.calendar.el).hasClass("room-group-booking-plan")){var t=$(e.view.context.calendar.el).hasClass("semester-plan")?"semester":"booking",n=STUDIP.URLHelper.getURL("dispatch.php/resources/room_planning/".concat(t,"_plan/").concat(e.resource.id));$(e.el).find(".fc-cell-text").html($("<a>").attr("href",n).text(e.el.innerText).append($("<img>").attr("src",STUDIP.ASSETS_URL+"images/icons/blue/link-intern.svg").addClass("text-bottom").css({width:16,height:16,margin:"0px 5px"})))}else if($("*[data-fullcalendar='1']").hasClass("institute-plan")&&e.resource.id>0){var a='<img class="text-bottom icon-role-clickable icon-shape-edit" width="16" height="16" src="'+STUDIP.URLHelper.getURL("assets/images/icons/blue/edit.svg")+'" alt="edit">';$(e.el).append('<a href="'+STUDIP.URLHelper.getURL("dispatch.php/admin/courseplanning/rename_column/"+e.resource.id+"/"+e.view.activeStart.getDay())+'" data-dialog="size=auto"> '+a+"</a>")}},drop:function(e){$(e.draggedEl).remove()},eventReceive:function(e){$(e.view.context.calendar.el).hasClass("institute-plan")&&STUDIP.Fullcalendar.institutePlanExternalDropEventHandler(e)}},n),n=$.extend({},n,t),this.init(e,n)}}}],(n=null)&&w(t.prototype,n),i&&w(t,i),e}();STUDIP.Fullcalendar=S}}]);
//# sourceMappingURL=630.chunk.js.map