!function(){var t={6928:function(){STUDIP.domReady((function(){STUDIP.Statusgroups.ajax_endpoint=$('meta[name="statusgroups-ajax-movable-endpoint"]').attr("content"),STUDIP.Statusgroups.apply(),$("a.get-group-members").on("click",(function(){var t,e=$("article#group-members-"+$(this).data("group-id"));0===$.trim(e.html()).length&&(t=$(this).data("get-members-url"),e.html($("<img>").attr({width:32,height:32,src:STUDIP.ASSETS_URL+"images/ajax-indicator-black.svg"})),$.get(t).done((function(t){e.html(t),$(document).trigger("refresh-handlers")})))}));var t="> header";window.matchMedia("(hover: none)").matches&&($(".course-statusgroups[data-sortable]").addClass("by-touch").find("> .draggable").each((function(){$("header",this).prepend('<span class="sg-sortable-handle">')})),t=".sg-sortable-handle");var e=null;$(".course-statusgroups[data-sortable]").sortable({axis:"y",containment:"parent",forcePlaceholderSize:!0,handle:t,items:"> .draggable",placeholder:"sortable-placeholder",start:function(t,s){e=s.item.index()},stop:function(t,s){if(e!==s.item.index()){var a=$(this).data("sortable");$.post(a,{id:s.item.attr("id"),index:s.item.index()-1})}}})})),STUDIP.ready((function(){$(".nestable").each((function(){$(this).nestable({rootClass:"nestable",maxDepth:$(this).data("max-depth")||5})}))})),$(document).on("submit","#order_form",(function(){var t=$(".nestable").nestable("serialize"),e=JSON.stringify(t);$("#ordering").val(e)}))},3412:function(){!function(t,e,s,a){var i="ontouchstart"in s,o=function(){var t=s.createElement("div"),a=s.documentElement;if(!("pointerEvents"in t.style))return!1;t.style.pointerEvents="auto",t.style.pointerEvents="x",a.appendChild(t);var i=e.getComputedStyle&&"auto"===e.getComputedStyle(t,"").pointerEvents;return a.removeChild(t),!!i}(),n={listNodeName:"ol",itemNodeName:"li",rootClass:"dd",listClass:"dd-list",itemClass:"dd-item",dragClass:"dd-dragel",handleClass:"dd-handle",collapsedClass:"dd-collapsed",placeClass:"dd-placeholder",noDragClass:"dd-nodrag",emptyClass:"dd-empty",expandBtnHTML:'<button data-action="expand" type="button">Expand</button>',collapseBtnHTML:'<button data-action="collapse" type="button">Collapse</button>',group:0,maxDepth:5,threshold:20};function l(e,a){this.w=t(s),this.el=t(e),this.options=t.extend({},n,a),this.init()}l.prototype={init:function(){var s=this;s.reset(),s.el.data("nestable-group",this.options.group),s.placeEl=t('<div class="'+s.options.placeClass+'"/>'),t.each(this.el.find(s.options.itemNodeName),(function(e,a){s.setParent(t(a))})),s.el.on("click","button",(function(e){if(!s.dragEl){var a=t(e.currentTarget),i=a.data("action"),o=a.parent(s.options.itemNodeName);"collapse"===i&&s.collapseItem(o),"expand"===i&&s.expandItem(o)}}));var a=function(e){var a=t(e.target);if(!a.hasClass(s.options.handleClass)){if(a.closest("."+s.options.noDragClass).length)return;a=a.closest("."+s.options.handleClass)}a.length&&!s.dragEl&&(s.isTouch=/^touch/.test(e.type),s.isTouch&&1!==e.touches.length||(e.preventDefault(),s.dragStart(e.touches?e.touches[0]:e)))},o=function(t){s.dragEl&&(t.preventDefault(),s.dragMove(t.touches?t.touches[0]:t))},n=function(t){s.dragEl&&(t.preventDefault(),s.dragStop(t.touches?t.touches[0]:t))};i&&(s.el[0].addEventListener("touchstart",a,!1),e.addEventListener("touchmove",o,!1),e.addEventListener("touchend",n,!1),e.addEventListener("touchcancel",n,!1)),s.el.on("mousedown",a),s.w.on("mousemove",o),s.w.on("mouseup",n)},serialize:function(){var e=this;return step=function(s,a){var i=[];return s.children(e.options.itemNodeName).each((function(){var s=t(this),o=t.extend({},s.data()),n=s.children(e.options.listNodeName);n.length&&(o.children=step(n,a+1)),i.push(o)})),i},step(e.el.find(e.options.listNodeName).first(),0)},serialise:function(){return this.serialize()},reset:function(){this.mouse={offsetX:0,offsetY:0,startX:0,startY:0,lastX:0,lastY:0,nowX:0,nowY:0,distX:0,distY:0,dirAx:0,dirX:0,dirY:0,lastDirX:0,lastDirY:0,distAxX:0,distAxY:0},this.isTouch=!1,this.moving=!1,this.dragEl=null,this.dragRootEl=null,this.dragDepth=0,this.hasNewRoot=!1,this.pointEl=null},expandItem:function(t){t.removeClass(this.options.collapsedClass),t.children('[data-action="expand"]').hide(),t.children('[data-action="collapse"]').show(),t.children(this.options.listNodeName).show()},collapseItem:function(t){t.children(this.options.listNodeName).length&&(t.addClass(this.options.collapsedClass),t.children('[data-action="collapse"]').hide(),t.children('[data-action="expand"]').show(),t.children(this.options.listNodeName).hide())},expandAll:function(){var e=this;e.el.find(e.options.itemNodeName).each((function(){e.expandItem(t(this))}))},collapseAll:function(){var e=this;e.el.find(e.options.itemNodeName).each((function(){e.collapseItem(t(this))}))},setParent:function(e){e.children(this.options.listNodeName).length&&(e.prepend(t(this.options.expandBtnHTML)),e.prepend(t(this.options.collapseBtnHTML))),e.children('[data-action="expand"]').hide()},unsetParent:function(t){t.removeClass(this.options.collapsedClass),t.children("[data-action]").remove(),t.children(this.options.listNodeName).remove()},dragStart:function(e){var i=this.mouse,o=t(e.target),n=o.closest(this.options.itemNodeName);this.placeEl.css("height",n.height()),i.offsetX=e.offsetX!==a?e.offsetX:e.pageX-o.offset().left,i.offsetY=e.offsetY!==a?e.offsetY:e.pageY-o.offset().top,i.startX=i.lastX=e.pageX,i.startY=i.lastY=e.pageY,this.dragRootEl=this.el,this.dragEl=t(s.createElement(this.options.listNodeName)).addClass(this.options.listClass+" "+this.options.dragClass),this.dragEl.css("width",n.width()),n.after(this.placeEl),n[0].parentNode.removeChild(n[0]),n.appendTo(this.dragEl),t(s.body).append(this.dragEl),this.dragEl.css({left:e.pageX-i.offsetX,top:e.pageY-i.offsetY});var l,d,r=this.dragEl.find(this.options.itemNodeName);for(l=0;l<r.length;l++)(d=t(r[l]).parents(this.options.listNodeName).length)>this.dragDepth&&(this.dragDepth=d)},dragStop:function(t){var e=this.dragEl.children(this.options.itemNodeName).first();e[0].parentNode.removeChild(e[0]),this.placeEl.replaceWith(e),this.dragEl.remove(),this.el.trigger("change"),this.hasNewRoot&&this.dragRootEl.trigger("change"),this.reset()},dragMove:function(a){var i,n,l,d=this.options,r=this.mouse;this.dragEl.css({left:a.pageX-r.offsetX,top:a.pageY-r.offsetY}),r.lastX=r.nowX,r.lastY=r.nowY,r.nowX=a.pageX,r.nowY=a.pageY,r.distX=r.nowX-r.lastX,r.distY=r.nowY-r.lastY,r.lastDirX=r.dirX,r.lastDirY=r.dirY,r.dirX=0===r.distX?0:r.distX>0?1:-1,r.dirY=0===r.distY?0:r.distY>0?1:-1;var h=Math.abs(r.distX)>Math.abs(r.distY)?1:0;if(!r.moving)return r.dirAx=h,void(r.moving=!0);r.dirAx!==h?(r.distAxX=0,r.distAxY=0):(r.distAxX+=Math.abs(r.distX),0!==r.dirX&&r.dirX!==r.lastDirX&&(r.distAxX=0),r.distAxY+=Math.abs(r.distY),0!==r.dirY&&r.dirY!==r.lastDirY&&(r.distAxY=0)),r.dirAx=h,r.dirAx&&r.distAxX>=d.threshold&&(r.distAxX=0,l=this.placeEl.prev(d.itemNodeName),r.distX>0&&l.length&&!l.hasClass(d.collapsedClass)&&(i=l.find(d.listNodeName).last(),this.placeEl.parents(d.listNodeName).length+this.dragDepth<=d.maxDepth&&(i.length?(i=l.children(d.listNodeName).last()).append(this.placeEl):((i=t("<"+d.listNodeName+"/>").addClass(d.listClass)).append(this.placeEl),l.append(i),this.setParent(l)))),r.distX<0&&(this.placeEl.next(d.itemNodeName).length||(n=this.placeEl.parent(),this.placeEl.closest(d.itemNodeName).after(this.placeEl),n.children().length||this.unsetParent(n.parent()))));var p=!1;if(o||(this.dragEl[0].style.visibility="hidden"),this.pointEl=t(s.elementFromPoint(a.pageX-s.body.scrollLeft,a.pageY-(e.pageYOffset||s.documentElement.scrollTop))),o||(this.dragEl[0].style.visibility="visible"),this.pointEl.hasClass(d.handleClass)&&(this.pointEl=this.pointEl.parent(d.itemNodeName)),this.pointEl.hasClass(d.emptyClass))p=!0;else if(!this.pointEl.length||!this.pointEl.hasClass(d.itemClass))return;var c=this.pointEl.closest("."+d.rootClass),u=this.dragRootEl.data("nestable-id")!==c.data("nestable-id");if(!r.dirAx||u||p){if(u&&d.group!==c.data("nestable-group"))return;if(this.dragDepth-1+this.pointEl.parents(d.listNodeName).length>d.maxDepth)return;var f=a.pageY<this.pointEl.offset().top+this.pointEl.height()/2;n=this.placeEl.parent(),p?((i=t(s.createElement(d.listNodeName)).addClass(d.listClass)).append(this.placeEl),this.pointEl.replaceWith(i)):f?this.pointEl.before(this.placeEl):this.pointEl.after(this.placeEl),n.children().length||this.unsetParent(n.parent()),this.dragRootEl.find(d.itemNodeName).length||this.dragRootEl.append('<div class="'+d.emptyClass+'"/>'),u&&(this.dragRootEl=c,this.hasNewRoot=this.el[0]!==this.dragRootEl[0])}}},t.fn.nestable=function(e){var s=this;return this.each((function(){var a=t(this).data("nestable");a?"string"==typeof e&&"function"==typeof a[e]&&(s=a[e]()):(t(this).data("nestable",new l(this,e)),t(this).data("nestable-id",(new Date).getTime()))})),s||this}}(window.jQuery||window.Zepto,window,document)}},e={};function s(a){if(e[a])return e[a].exports;var i=e[a]={exports:{}};return t[a](i,i.exports,s),i.exports}s.n=function(t){var e=t&&t.__esModule?function(){return t.default}:function(){return t};return s.d(e,{a:e}),e},s.d=function(t,e){for(var a in e)s.o(e,a)&&!s.o(t,a)&&Object.defineProperty(t,a,{enumerable:!0,get:e[a]})},s.o=function(t,e){return Object.prototype.hasOwnProperty.call(t,e)},function(){"use strict";s(3412),s(6928)}()}();
//# sourceMappingURL=studip-statusgroups.js.map