!function(e){var t={};function n(i){if(t[i])return t[i].exports;var r=t[i]={i:i,l:!1,exports:{}};return e[i].call(r.exports,r,r.exports,n),r.l=!0,r.exports}n.m=e,n.c=t,n.d=function(e,t,i){n.o(e,t)||Object.defineProperty(e,t,{enumerable:!0,get:i})},n.r=function(e){"undefined"!=typeof Symbol&&Symbol.toStringTag&&Object.defineProperty(e,Symbol.toStringTag,{value:"Module"}),Object.defineProperty(e,"__esModule",{value:!0})},n.t=function(e,t){if(1&t&&(e=n(e)),8&t)return e;if(4&t&&"object"==typeof e&&e&&e.__esModule)return e;var i=Object.create(null);if(n.r(i),Object.defineProperty(i,"default",{enumerable:!0,value:e}),2&t&&"string"!=typeof e)for(var r in e)n.d(i,r,function(t){return e[t]}.bind(null,r));return i},n.n=function(e){var t=e&&e.__esModule?function(){return e.default}:function(){return e};return n.d(t,"a",t),t},n.o=function(e,t){return Object.prototype.hasOwnProperty.call(e,t)},n.p="/",n(n.s="EUag")}({EUag:function(e,t,n){"use strict";function i(e,t){for(var n=0;n<t.length;n++){var i=t[n];i.enumerable=i.enumerable||!1,i.configurable=!0,"value"in i&&(i.writable=!0),Object.defineProperty(e,i.key,i)}}n.r(t);var r=function(){function e(){!function(e,t){if(!(e instanceof t))throw new TypeError("Cannot call a class as a function")}(this,e),this.$repeaterElement=jQuery(".emoji-repeater"),this.$addItemButton=jQuery(".emoji-repeater-item-add"),this.removeSelector=".emoji-repeater-item-remove",this.itemSelector=".emoji-repeater-row",this.bindRemoveItem=this.bindRemoveItem.bind(this),this.bindCloneItem=this.bindCloneItem.bind(this),this.bindChangeIndexes=this.bindChangeIndexes.bind(this)}var t,n,r;return t=e,r=[{key:"changeIndexes",value:function(){return e.changeItemIndexes(jQuery(this))}},{key:"changeItemIndexes",value:function(t){var n=+t.index();return t.find("input").each((function(){jQuery(this).prop("name",e.changeIndex(jQuery(this).prop("name"),n))})),t}},{key:"changeIndex",value:function(e,t){return e.toString().replace(new RegExp(/\[\d+]/gm),"["+t+"]")}}],(n=[{key:"init",value:function(){this.initSortable(),this.$addItemButton.on("click",this.bindCloneItem),this.$repeaterElement.on("click",this.removeSelector,this.bindRemoveItem)}},{key:"initSortable",value:function(){this.$repeaterElement.sortable({axis:"y",stop:this.bindChangeIndexes})}},{key:"bindChangeIndexes",value:function(){this.$repeaterElement.find(this.itemSelector).each(e.changeIndexes)}},{key:"reinitSortable",value:function(){this.$repeaterElement.sortable("destroy"),this.initSortable()}},{key:"bindRemoveItem",value:function(e){e.preventDefault(),this.removeItem(jQuery(e.target))}},{key:"removeItem",value:function(e){e.closest(".emoji-repeater-row").slideUp(400,(function(){e.remove()}))}},{key:"bindCloneItem",value:function(e){e.preventDefault(),this.cloneItem()}},{key:"cloneItem",value:function(){var t=this.$repeaterElement.find(".emoji-repeater-row:last"),n=this.clearItem(t.clone());n.find("input").each((function(){jQuery(this).prop("name",e.changeIndex(jQuery(this).prop("name"),+t.index()+1))})),jQuery(".emoji-repeater").append(n),n.slideDown(),this.reinitSortable()}},{key:"clearItem",value:function(e){return e.removeProp("style"),e.find("input").val(""),e.find(".emoji-repeater-item-preview").removeProp("style"),e.css("display","none"),e}}])&&i(t.prototype,n),r&&i(t,r),e}();function o(e,t){for(var n=0;n<t.length;n++){var i=t[n];i.enumerable=i.enumerable||!1,i.configurable=!0,"value"in i&&(i.writable=!0),Object.defineProperty(e,i.key,i)}}var a,u=wp.media({title:"Insert image",library:{type:"image"},button:{text:"Use this image"},multiple:!1}),l=function(){function e(){!function(e,t){if(!(e instanceof t))throw new TypeError("Cannot call a class as a function")}(this,e),this.$wrapper=jQuery(".emoji-repeater")}var t,n,i;return t=e,(n=[{key:"init",value:function(){this.$wrapper.on("click",".emoji-repeater-item-preview",this.openMediaLibrary),u.on("select",this.chooseImage)}},{key:"openMediaLibrary",value:function(){a=jQuery(this).closest(".emoji-repeater-item"),u.open()}},{key:"chooseImage",value:function(){var e=u.state().get("selection").first().toJSON();a.find("input").val(e.id),a.find(".emoji-repeater-item-preview").css("background-image","url("+e.url+")")}}])&&o(t.prototype,n),i&&o(t,i),e}();(new r).init(),(new l).init()}});