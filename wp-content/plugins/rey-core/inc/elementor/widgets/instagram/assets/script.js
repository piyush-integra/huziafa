!function(o,t,e){"use strict";var i=function(){this.init=function(){t.hooks.addAction("frontend/element_ready/reycore-instagram.shuffle",this.instagramShuffle)},this.instagramShuffle=function(e){({id:0,cols:0,$el:!1,$grid:!1,topSpacingScheme:{1:[0],2:[0,.5],3:[.5,1,0],4:[.5,1,0,.5],5:[1,.5,1,.5,0],6:[1,.5,1,.5,0,.5]},init:function(t){this.$rc=t,this.id=e.attr("data-id"),this.$el=this.getElement(),this.$items=o(".rey-elInsta-item",this.$el),this.checkItems(),this.doMasonry(),this.events()},getElement:function(){var t=o(".elementor-widget-reycore-instagram[data-id='"+this.id+"'] .rey-elInsta--skin-shuffle",o(document));return this.cols=parseFloat(t.attr("data-per-row")),t},events:function(){var t=this;o(window).on("resize",o.reyHelpers.debounce(function(){t.topPositioning(),t.$grid&&t.$grid.masonry()},300))},checkItems:function(){0==this.$items.length&&this.$el.closest(".elementor-element").addClass("--empty")},doMasonry:function(){var t=this;0!=this.$items.length&&void 0!==o.fn.masonry&&void 0!==o.fn.imagesLoaded&&this.$el.imagesLoaded(function(){t.topPositioning(),t.$grid=t.$el.masonry({isInitLayout:!1,itemSelector:".rey-elInsta-item",percentPosition:!0}),t.$grid.on("layoutComplete",function(t,e){if(!o.reyHelpers.is_edit_mode&&"undefined"!=typeof ScrollOut){var i=[];e.filter(function(t,e){i[e]=t.element}),ScrollOut({targets:i,once:!0,threshold:.25,onShown:function(t,e,i){o(t).addClass("--animated-in").css("transition-delay",.05*e.index+"s")}})}}),t.$grid.masonry()})},topPositioning:function(){var n=this;o.reyHelpers.is_desktop?this.$items.each(function(t,e){if(t<=n.cols){var i=o(e);6<n.cols&&(n.cols=6),i.css("margin-top",i.height()*n.topSpacingScheme[n.cols][t])}}):this.$items.css("margin-top",0)}}).init()},this.init()};o(window).on("elementor/frontend/init",function(){new i})}(jQuery,window.elementorFrontend,window.elementorModules);