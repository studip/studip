(self.webpackChunk_studip_core=self.webpackChunk_studip_core||[]).push([[609,900,758],{6609:function(e,t,n){"use strict";n.r(t),n.d(t,{default:function(){return o}});var a=function(){var e=this,t=e.$createElement,n=e._self._c||t;return n("div",{staticClass:"writer"},[n("studip-icon",{attrs:{shape:"blubber",size:"30",role:"info"}}),e._v(" "),n("textarea",{attrs:{placeholder:"Schreib was, frag was. Enter zum Abschicken.".toLocaleString()},on:{keyup:[function(t){return!t.type.indexOf("key")&&e._k(t.keyCode,"enter",13,t.key,"Enter")||t.ctrlKey||t.shiftKey||t.altKey||t.metaKey?null:e.submit(t)},e.saveCommentToSession],change:e.saveCommentToSession}}),e._v(" "),n("label",{staticClass:"upload",attrs:{title:"Datei hochladen".toLocaleString()}},[n("input",{staticStyle:{display:"none"},attrs:{type:"file",multiple:""},on:{change:e.upload}}),e._v(" "),n("studip-icon",{attrs:{shape:"upload",size:"30"}})],1)],1)};a._withStripped=!0;var r={name:"blubber-public-composer",methods:{submit:function(e){var t=this;if(e&&"string"==typeof e||(e=$(this.$el).find("textarea").val(),$(this.$el).find("textarea").val(""),sessionStorage.removeItem("BlubberMemory-Writer-Public")),!e.trim())return!1;STUDIP.api.POST("blubber/threads",{data:{content:e}}).done((function(e){t.$parent.addPosting(e.thread_posting)}))},saveCommentToSession:function(e){var t=e.target.value;sessionStorage.setItem("BlubberMemory-Writer-Public",t)},upload:function(e){var t=void 0!==e.dataTransfer?e.dataTransfer.files:e.target.files,n=this,a=new FormData;for(var r in t)t[r].size>0&&a.append("file_".concat(r),t[r],t[r].name.normalize());var i=new XMLHttpRequest;i.open("POST","".concat(STUDIP.ABSOLUTE_URI_STUDIP,"dispatch.php/blubber/upload_files")),i.upload.addEventListener("progress",(function(e){var t=0,a=e.loaded||e.position,r=e.total;e.lengthComputable&&(t=Math.ceil(a/r*100)),$(n.$el).css("background-size","".concat(t,"% 100%"))})),i.addEventListener("load",(function(e){var t=JSON.parse(this.response);$(n.$el).find("textarea").val($(n.$el).find("textarea").val()+" "+t.inserts.join(" "))})),i.addEventListener("loadend",(function(e){$(n.$el).css("background-size","0% 100%")})),i.send(a)}},mounted:function(){this.$nextTick((function(){$(this.$el).find("textarea").autoResize({animateDuration:0,extraSpace:1});var e=sessionStorage.getItem("BlubberMemory-Writer-Public");e&&$(this.$el).find("textarea").val(e)}))}},i=(0,n(1900).Z)(r,a,[],!1,null,null,null);i.options.__file="resources/vue/components/BlubberPublicComposer.vue";var o=i.exports},1900:function(e,t,n){"use strict";function a(e,t,n,a,r,i,o,s){var l,u="function"==typeof e?e.options:e;if(t&&(u.render=t,u.staticRenderFns=n,u._compiled=!0),a&&(u.functional=!0),i&&(u._scopeId="data-v-"+i),o?(l=function(e){(e=e||this.$vnode&&this.$vnode.ssrContext||this.parent&&this.parent.$vnode&&this.parent.$vnode.ssrContext)||"undefined"==typeof __VUE_SSR_CONTEXT__||(e=__VUE_SSR_CONTEXT__),r&&r.call(this,e),e&&e._registeredComponents&&e._registeredComponents.add(o)},u._ssrRegister=l):r&&(l=s?function(){r.call(this,(u.functional?this.parent:this).$root.$options.shadowRoot)}:r),l)if(u.functional){u._injectStyles=l;var c=u.render;u.render=function(e,t){return l.call(t),c(e,t)}}else{var d=u.beforeCreate;u.beforeCreate=d?[].concat(d,l):[l]}return{exports:e,options:u}}n.d(t,{Z:function(){return a}})}}]);
//# sourceMappingURL=609.chunk.js.map