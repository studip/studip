(self.webpackChunk_studip_core=self.webpackChunk_studip_core||[]).push([[792],{7792:function(t,e,a){"use strict";a.r(e),a.d(e,{default:function(){return o}});var n=function(){var t=this,e=t.$createElement,a=t._self._c||e;return a("div",{staticClass:"blubber_thread",attrs:{id:"blubberthread_"+t.thread_data.thread_posting.thread_id},on:{dragover:function(e){return e.preventDefault(),t.dragover(e)},dragleave:function(e){return e.preventDefault(),t.dragleave(e)},drop:function(e){return e.preventDefault(),t.upload(e)}}},[a("div",{directives:[{name:"scroll",rawName:"v-scroll"}],staticClass:"scrollable_area"},[a("div",{staticClass:"all_content"},[t.thread_data.thread_posting.content.trim()?a("div",{staticClass:"thread_posting"},[a("div",{staticClass:"contextinfo"},[a("studip-date-time",{attrs:{timestamp:t.thread_data.thread_posting.mkdate,relative:!0}}),t._v(" "),a("a",{attrs:{href:t.getUserProfileURL(t.thread_data.thread_posting.user_username)}},[t._v(t._s(t.thread_data.thread_posting.user_name))]),t._v(" "),a("a",{staticClass:"avatar",style:{backgroundImage:"url("+t.thread_data.thread_posting.avatar+")"},attrs:{href:t.getUserProfileURL(t.thread_data.thread_posting.user_username)}})],1),t._v(" "),a("div",{staticClass:"content",domProps:{innerHTML:t._s(t.thread_data.thread_posting.html)}}),t._v(" "),a("div",{staticClass:"link_to_comments"})]):t._e(),t._v(" "),t.thread_data.thread_posting.content.trim()||t.thread_data.comments.length?t._e():a("div",{staticClass:"empty_blubber_background"},[a("div",{domProps:{innerHTML:t._s("Starte die Konversation jetzt!".toLocaleString())}})]),t._v(" "),a("ol",{staticClass:"comments",attrs:{"aria-live":"polite"}},[t.thread_data.more_up?a("li",{staticClass:"more"},[a("studip-asset-img",{attrs:{file:"ajax-indicator-black.svg",width:"20"}})],1):t._e(),t._v(" "),t._l(t.sortedComments,(function(e){return a("li",{key:e.comment_id,class:e.class,attrs:{"data-comment_id":e.comment_id}},[a("a",{staticClass:"avatar",style:{backgroundImage:"url("+e.avatar+")"},attrs:{href:t.getUserProfileURL(e.user_username),title:e.user_name}}),t._v(" "),a("div",{staticClass:"content"},[a("a",{staticClass:"name",attrs:{href:t.getUserProfileURL(e.user_username)}},[t._v(t._s(e.user_name))]),t._v(" "),a("div",{staticClass:"html",domProps:{innerHTML:t._s(e.html)}}),t._v(" "),a("textarea",{staticClass:"edit",domProps:{innerHTML:t._s(e.content)},on:{keyup:[function(e){return!e.type.indexOf("key")&&t._k(e.keyCode,"enter",13,e.key,"Enter")||e.ctrlKey||e.shiftKey||e.altKey||e.metaKey?null:t.saveComment(e)},function(e){return!e.type.indexOf("key")&&t._k(e.keyCode,"escape",void 0,e.key,void 0)||e.ctrlKey||e.shiftKey||e.altKey||e.metaKey?null:t.editComment(e)}]}})]),t._v(" "),a("div",{staticClass:"time"},[a("studip-date-time",{attrs:{timestamp:e.mkdate,relative:!0}}),t._v(" "),e.writable?a("a",{staticClass:"edit_comment",attrs:{href:"",title:"Bearbeiten.".toLocaleString()},on:{click:function(e){return e.preventDefault(),t.editComment(e)}}},[a("studip-icon",{attrs:{shape:"edit",size:"14",role:"inactive"}})],1):t._e(),t._v(" "),a("a",{staticClass:"answer_comment",attrs:{href:"",title:"Hierauf antworten.".toLocaleString()},on:{click:function(e){return e.preventDefault(),t.answerComment(e)}}},[a("studip-icon",{attrs:{shape:"export",size:"14",role:"inactive"}})],1)],1)])})),t._v(" "),t.thread_data.more_down?a("li",{staticClass:"more"},[a("studip-asset-img",{attrs:{file:"ajax-indicator-black.svg",width:"20"}})],1):t._e()],2)])]),t._v(" "),t.thread_data.thread_posting.commentable?a("div",{staticClass:"writer"},[a("studip-icon",{attrs:{shape:"blubber",size:"30",role:"info"}}),t._v(" "),a("textarea",{attrs:{placeholder:t.thread_data.thread_posting.content.trim()?"Kommentar schreiben. Enter zum Abschicken.".toLocaleString():"Nachricht schreiben. Enter zum Abschicken".toLocaleString()},on:{keyup:[function(e){return!e.type.indexOf("key")&&t._k(e.keyCode,"enter",13,e.key,"Enter")||e.ctrlKey||e.shiftKey||e.altKey||e.metaKey?null:t.submit(e)},function(e){return!e.type.indexOf("key")&&t._k(e.keyCode,"up",38,e.key,["Up","ArrowUp"])||e.ctrlKey||e.shiftKey||e.altKey||e.metaKey?null:t.editPreviousComment(e)},t.saveCommentToSession],change:t.saveCommentToSession}}),t._v(" "),a("a",{staticClass:"send",attrs:{title:"Abschicken".toLocaleString()},on:{click:t.submit}},[a("studip-icon",{attrs:{shape:"arr_2up",size:"30"}})],1),t._v(" "),a("label",{staticClass:"upload",attrs:{title:"Datei hochladen".toLocaleString()}},[a("input",{staticStyle:{display:"none"},attrs:{type:"file",multiple:""},on:{change:t.upload}}),t._v(" "),a("studip-icon",{attrs:{shape:"upload",size:"30"}})],1)],1):t._e()])};n._withStripped=!0;var i={name:"blubber-thread",data:function(){return{already_loading_up:0,already_loading_down:0}},props:["thread_data"],methods:{submit:function(t){var e=this;if(t&&"string"==typeof t||(t=$(this.$el).find(".writer textarea").val(),$(this.$el).find(".writer textarea").val(""),this.thread_data.thread_posting.thread_id&&sessionStorage.removeItem("BlubberMemory-Writer-"+this.thread_data.thread_posting.thread_id)),!t.trim())return!1;var a=t.replace(/\n/g,"<br>"),n={comment_id:Math.random().toString(36),avatar:"",html:a,content:t,mkdate:Math.floor(Date.now()/1e3),name:"Nobody",class:"mine new",writable:1};this.addComment(n);var i=this;STUDIP.api.POST("blubber/threads/".concat(this.thread_data.thread_posting.thread_id,"/comments"),{data:{content:t}}).done((function(t){n.comment_id=t.comment_id,n.avatar=t.avatar,n.user_name=t.user_name,n.mkdate=t.mkdate,n.html=t.html,n.class=t.class,i.$nextTick((function(){STUDIP.Markup.element($(i.$el).find('.comments > li[data-comment_id="'.concat(t.comment_id,'"]')))}))})),this.$nextTick((function(){e.scrollDown()}))},saveCommentToSession:function(t){var e=t.target.value;this.thread_data.thread_posting.thread_id&&sessionStorage.setItem("BlubberMemory-Writer-".concat(this.thread_data.thread_posting.thread_id),e),$(this.$el).find(".writer").toggleClass("filled",""!==e.trim())},scrollDown:function(){this.$nextTick((function(){var t=this.$el,e=function(){$(t).find(".scrollable_area").scrollTo($(t).find(".scrollable_area .all_content").height())};$(t).find(".scrollable_area img").on("load",e),e()}))},addComments:function(t,e){var a=this;t.forEach((function(t){e&&(t.class+=" new"),a.addComment(t)}))},addComment:function(t){var e=this;for(var a in this.$nextTick((function(){STUDIP.Markup.element($(e.$el).find('.comments > li[data-comment_id="'.concat(t.comment_id,'"]')))})),this.thread_data.comments)if(this.thread_data.comments[a].comment_id===t.comment_id)return this.thread_data.comments[a].content=t.content,void(this.thread_data.comments[a].html=t.html);this.thread_data.comments.push(t)},removeComment:function(t){var e=this;this.thread_data.comments.forEach((function(a,n){a.comment_id===t&&e.$delete(e.thread_data.comments,n)}))},upload:function(t){var e=void 0!==t.dataTransfer?t.dataTransfer.files:t.target.files,a=this,n=new FormData;for(var i in e)e[i].size>0&&n.append("file_".concat(i),e[i],e[i].name.normalize());var r=new XMLHttpRequest;r.open("POST","".concat(STUDIP.ABSOLUTE_URI_STUDIP,"dispatch.php/blubber/upload_files")),r.upload.addEventListener("progress",(function(t){var e=0,n=t.loaded||t.position,i=t.total;t.lengthComputable&&(e=Math.ceil(n/i*100)),$(a.$el).find(".writer").css("background-size","".concat(e,"% 100%"))})),r.addEventListener("load",(function(t){var e=JSON.parse(this.response);a.submit(e.inserts.join(" "))})),r.addEventListener("loadend",(function(t){$(a.$el).find(".writer").css("background-size","0% 100%")})),r.send(n),this.dragleave()},dragover:function(){$(this.$el).addClass("dragover")},dragleave:function(){$(this.$el).removeClass("dragover")},getUserProfileURL:function(t){return STUDIP.URLHelper.getURL("dispatch.php/profile",{username:t})},editComment:function(t){var e;if("string"==typeof t){var a=t;e=$(this.$el).find('.comments > li[data-comment_id="'.concat(a,'"]'))}else{e=$(t.target).closest("li[data-comment_id]");$(t.target).closest("li[data-comment_id]").data("comment_id")}e.find(".content").toggleClass("editing");var n=e.find(".content textarea").last()[0];n.focus(),n.setSelectionRange(n.value.length,n.value.length),e.find(".content textarea:not(.auto-resizable)").addClass("auto-resizable").autoResize({animateDuration:0})},answerComment:function(t){var e;if("string"==typeof t){var a=t;e=$(this.$el).find('.comments > li[data-comment_id="'.concat(a,'"]'))}else{e=$(t.target).closest("li[data-comment_id]");$(t.target).closest("li[data-comment_id]").data("comment_id")}var n=$(e).data("comment_id"),i=null;if(this.thread_data.comments.forEach((function(t,e){t.comment_id===n&&(i=t)})),i){var r="[quote="+i.user_name+"]"+i.content.replace(/\[quote[^\]]*\].*\[\/quote\]/g,"").trim()+"[/quote]\n";$(this.$el).find(".writer textarea").val(r);var o=$(this.$el).find(".writer textarea").last()[0];o.focus(),o.setSelectionRange(o.value.length,o.value.length)}},saveComment:function(t){var e=this,a=$(t.target).closest("li[data-comment_id]"),n=a.data("comment_id"),i=a.find("textarea").val();e.thread_data.comments.forEach((function(t){t.comment_id===n&&(t.html=i)})),a.find(".content").removeClass("editing"),STUDIP.api.PUT("blubber/threads/".concat(this.thread_data.thread_posting.thread_id,"/comments/").concat(n),{data:{content:i}}).done((function(t){t.content.trim()?e.thread_data.comments.forEach((function(a){a.comment_id===n&&(a.html=t.html,a.content=t.content,e.$nextTick((function(){STUDIP.Markup.element($(e.$el).find('.comments > li[data-comment_id="'.concat(n,'"]')))})))})):e.removeComment(n),$(e.$el).find(".writer textarea").focus()}))},removeDeletedComments:function(t){for(var e in t)this.removeComment(t[e])},editPreviousComment:function(){if(!$(this.$el).find(".writer textarea").val().trim()){var t=$(this.$el).find(".comments li.mine").last();t.length>0&&this.editComment(t.data("comment_id"))}}},directives:{scroll:{inserted:function(t){var e=$(t).closest(".blubber_thread")[0].__vue__;$(t).on("scroll",(function(a){var n=$(t).scrollTop(),i=$(t).find(".all_content").height();if($(t).toggleClass("scrolled",n>0),e.$root.display_context_posting=n>=$(t).find(".all_content .thread_posting").height()?1:0,e.thread_data.more_up&&n<1e3&&!e.already_loading_up){e.already_loading_up=1;var r=e.thread_data.comments.reduce((function(t,e){return null===t?e.mkdate:Math.min(t,e.mkdate)}),null);STUDIP.api.GET("blubber/threads/".concat(e.thread_data.thread_posting.thread_id,"/comments"),{data:{modifier:"olderthan",timestamp:r,limit:50}}).done((function(a){n=$(t).scrollTop(),e.addComments(a.comments,!1),e.thread_data.more_up=a.more_up,e.$nextTick((function(){var e=$(t).find(".all_content").height()-i+n;$(t).scrollTo(e)}))})).done((function(){e.already_loading_up=0}))}if(e.thread_data.more_down&&n>$(e).find(".scrollable_area .all_content").height()-1e3&&!e.already_loading_down){e.already_loading_down=1;var o=e.thread_data.comments.reduce((function(t,e){return Math.max(t,e.mkdate)}),null);STUDIP.api.GET("blubber/threads/".concat(e.thread_data.thread_posting.thread_id,"/comments"),{data:{modifier:"newerthan",timestamp:o,limit:50}}).done((function(t){e.addComments(t.comments,!1),e.thread_data.more_down=t.more_down})).always((function(){e.already_loading_down=0}))}}))}}},mounted:function(){this.$nextTick((function(){if(this.thread_data.comments.length>0&&this.scrollDown(),$(this.$el).find(".writer textarea").autoResize({animateDuration:0,extraSpace:1}),$(this.$el).find(".comments .content .html").each((function(){STUDIP.Markup.element(this)})),this.thread_data.thread_posting.thread_id){var t=sessionStorage.getItem("BlubberMemory-Writer-".concat(this.thread_data.thread_posting.thread_id));t&&$(this.$el).find(".writer").addClass("filled").find("textarea").val(t)}}))},computed:{sortedComments:function(){return this.thread_data.comments.sort((function(t,e){return t.mkdate-e.mkdate}))}},updated:function(){this.$nextTick((function(){if(this.thread_data.thread_posting.thread_id){var t=sessionStorage.getItem("BlubberMemory-Writer-"+this.thread_data.thread_posting.thread_id);$(this.$el).find(".writer textarea").val(t)}}))},watch:{thread_data:function(t,e){t.thread_posting.thread_id!==e.thread_posting.thread_id&&(this.$nextTick((function(){$(this.$el).find(".comments .content .html").each((function(){STUDIP.Markup.element(this)}))})),this.scrollDown())}}},r=(0,a(1900).Z)(i,n,[],!1,null,null,null);r.options.__file="resources/vue/components/BlubberThread.vue";var o=r.exports}}]);
//# sourceMappingURL=792.chunk.js.map