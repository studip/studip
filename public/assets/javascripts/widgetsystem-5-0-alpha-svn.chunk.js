(window.webpackJsonp=window.webpackJsonp||[]).push([[15],{194:function(e,t,d){"use strict";d.r(t);d(229),d(230),d(201),d(231);var a=d(41);function i(e){return $.Deferred(function(s){if(0<$("#layout-sidebar .sidebar-secondary-widget").length)return s.resolve();$.get(e).then(function(e){var t=$(e),d=$(".addable-widgets",t).data().containerId,a=STUDIP.WidgetSystem.get(d),i=$(".addable-widgets div[data-widget-id]",t),n=Math.floor($(a.grid).width()/a.width);$(t).appendTo("#layout-sidebar > .sidebar"),$(i).each(function(){var e=$(this).data().widgetId,t=$("h2",this).html(),d=$(this).children(":not(h2)").clone(),a=$('<div class="grid-stack-item widget-to-add" data-gs-width="1" data-gs-height="1">'),i=$('<div class="grid-stack-item-content has-layout">').appendTo(a),s=$('<header class="widget-header">').appendTo(i);a.attr("data-widget-id",e),$('<h2 class="widget-title">').html(t).appendTo(s),$('<article class="widget-content">').append(d).appendTo(i),function t(d,a,i){var e=a.clone(),s=!1;d.append(e.width(i)),e.draggable({appendTo:"body",helper:function(){return $(this).clone().css({zIndex:1e3})},revert:function(e){return!1!==e?(t(d,a,i),$("#layout-sidebar").removeClass("second-display"),!1):s=!0},stop:function(){s&&(e.draggable("destroy").remove(),t(d,a,i))}})}($(this).parent(),a,n)}),$("#layout-sidebar .addable-widgets li").on("mousemove",function(e){var t=$(this).offset(),d={left:e.pageX-t.left-16,top:e.pageY-t.top-16};$(".widget-to-add",this).css(d)}),s.resolve()},s.reject)}).promise()}$(document).on("widget-add",function(e,t){var d=t.getResponseHeader("X-Widget-Remove"),a=t.getResponseHeader("X-Widget-Id");d&&$('.addable-widgets li:has([data-widget-id="'+a+'"])').each(function(){var e=this;$(".ui-draggable",this).draggable("destroy"),$(this).slideUp(function(){return $(e).remove()})})}).on("widget-remove",function(e,t){t.getResponseHeader("X-Refresh")&&$("#layout-sidebar .sidebar-secondary-widget").remove()}).on("click",function(e){0===$(e.target).closest(".sidebar-secondary-widget").length&&$("#layout-sidebar").removeClass("second-display")}),$("#layout-sidebar").on("click",".widget-add-toggle",function(){return i(this.href).done(function(){$("#layout-sidebar").toggleClass("second-display")}),!1}),STUDIP.WidgetSystem=a.a},230:function(e,t,d){}}]);
//# sourceMappingURL=widgetsystem-5-0-alpha-svn.chunk.js.map