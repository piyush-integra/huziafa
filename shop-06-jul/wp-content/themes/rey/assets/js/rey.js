(function ($) {
	"use strict";

	jQuery.migrateMute = true;

	var ReyHelpers = function () {
		var self = this;

		// params
		this.params = $.extend(
			{
				icons_path: ""
			},
			typeof reyParams === "object" && !!reyParams ? reyParams : {}
		);

		// https://underscorejs.org/#isArray
		this.isArray =
			Array.isArray ||
			function (obj) {
				return toString.call(obj) === "[object Array]";
			};
		// https://underscorejs.org/#debounce
		this.debounce = function (func, wait, immediate) {
			var timeout, args, context, timestamp, result;
			var later = function () {
				var last = Date.now - timestamp;
				if (last < wait && last >= 0) {
					timeout = setTimeout(later, wait - last);
				} else {
					timeout = null;
					if (!immediate) {
						result = func.apply(context, args);
						if (!timeout) context = args = null;
					}
				}
			};
			return function () {
				context = this;
				args = arguments;
				timestamp = Date.now;
				var callNow = immediate && !timeout;
				if (!timeout) timeout = setTimeout(later, wait);
				if (callNow) {
					result = func.apply(context, args);
					context = args = null;
				}

				return result;
			};
		};

		// Checks if a URL string is a YouTube URL and returns ID
		this.matchYoutubeUrl = function (url) {
			var p = /^(?:https?:\/\/)?(?:m\.|www\.)?(?:youtu\.be\/|youtube\.com\/(?:embed\/|v\/|watch\?v=|watch\?.+&v=))((\w|-){11})(?:\S+)?$/;
			if (url.match(p)) {
				return url.match(p)[1];
			}
			return false;
		};

		// Verfies if a string is URL
		this.isValidURL = function (string) {
			try {
				new URL(string);
				return true;
			} catch (_) {
				return false;
			}
		};

		this.setProperty = function (property, value, scope) {

			if (!scope) {
				scope = document.documentElement;
			}

			scope.style.setProperty(property, value);
		};

		this.getProperty = function (property, scope, ext) {

			if (!scope) {
				scope = document.documentElement;
			}

			if (ext === true) {
				return getComputedStyle(scope).getPropertyValue(property);
			}

			return scope.style.getPropertyValue(property);
		};

		this.doScroll = {
			isDisabled: false,
			disable: function () {
				window.oldScrollPos = $(window).scrollTop();

				$(window).on('scroll.scrolldisabler', function (event) {
					$(window).scrollTop(window.oldScrollPos);
					event.preventDefault();
					this.isDisabled = true;
				});
			},
			enable: function () {
				this.isDisabled = false;
				$(window).off('scroll.scrolldisabler');
			}
		};

		/**
		 * LocalStorage with expiration
		 * @since 1.0.2
		 */
		this.ls = {
			set: function (variable, value, ttl_ms) {
				if (variable && value && ttl_ms) {
					var data = { value: value, expires_at: new Date().getTime() + ttl_ms / 1 };
					localStorage.setItem(variable.toString(), JSON.stringify(data));
				}
			},
			get: function (variable) {
				if (!variable) {
					return null;
				}
				var data = JSON.parse(localStorage.getItem(variable.toString()));
				if (data !== null) {
					if (data.expires_at !== null && data.expires_at < new Date().getTime()) {
						localStorage.removeItem(variable.toString());
					} else {
						return data.value;
					}
				}
				return null;
			}
		};

		/**
		 * Expiration times
		 * @since 1.0.2
		 */
		this.expiration = {
			min: 60 * 1000,
			hr: 60 * 60 * 1000,
			day: 24 * 60 * 60 * 1000,
			week: 7 * 24 * 60 * 60 * 1000,
			month: 30 * 7 * 24 * 60 * 60 * 1000,
		};

		this.overlay = function (type, state, darken) {
			var _class = type + "-overlay--is-opened";
			var $body = $('body');
			if (state == "open") {
				$body.addClass(_class);
			} else if (state == "close") {
				$body.removeClass(_class).removeClass('--overlay-darken');
			}

			if (darken) {
				$body.addClass('--overlay-darken');
			}
		};

		this.getRandomInt = function (int) {
			return Math.floor(Math.random() * Math.floor(int));
		};

		this.sprintf = function (format) {
			var args = Array.prototype.slice.call(arguments, 1);
			var i = 0;
			return format.replace(/%s/g, function () {
				return args[i++];
			});
		};

		this.filters = {};

		this.addFilter = function ($tag, $function_to_add, $priority, $accepted_args) {

			var $idx;

			$priority = parseInt($priority, 10);
			if (isNaN($priority)) {
				$priority = 10;
			}
			$accepted_args = parseInt($accepted_args, 10);
			if (isNaN($accepted_args)) {
				$accepted_args = 1;
			}
			$idx = $function_to_add + "_" + $priority;
			if (!this.filters[$tag]) {
				this.filters[$tag] = {};
			}
			if (!this.filters[$tag][$priority]) {
				this.filters[$tag][$priority] = {};
			}
			this.filters[$tag][$priority][$idx] = {
				"func": $function_to_add,
				"accepted_args": $accepted_args
			};

			return true;

		};

		this.removeFilter = function ($tag, $function_to_remove, $priority) {

			var $idx;
			$priority = parseInt($priority, 10);
			if (isNaN($priority)) {
				$priority = 10;
			}
			$idx = $function_to_remove + "_" + $priority;

			if (this.filters[$tag] && this.filters[$tag][$priority] && this.filters[$tag][$priority][$idx]) {
				delete (this.filters[$tag][$priority][$idx]);
				return true;
			}

			return false;
		};

		this.applyFilter = function ($tag, $value) {

			var $args = $.makeArray(arguments);
			var priorities;

			$args.splice(0, 1);

			if (!this.filters[$tag]) {
				return $value;
			}

			priorities = this.filters[$tag];
			$.each(priorities, function (i, el) {

				$.each(el, function (i2, el2) {
					var func = el2.func;

					if (func instanceof Function) {
						$value = func.apply(null, $args);
					} else if (window[func] && window[func] instanceof Function) {
						$value = window[func].apply(null, $args);
					}

				});

			});

			return $value;

		};

		this.youTubeApiLoaded = false;

		// edit mode
		this.elementor_edit_mode = $("body.rey-elementor-edit-mode").length > 0;
		this.customizer_preview = $("body.customizer-preview-mode").length > 0;
		this.is_edit_mode = this.elementor_edit_mode || this.customizer_preview;
		this.$sitePreloader = $("#rey-site-preloader");
		this.$container_size = parseInt($('html').attr('data-container') || 1440);

		// Global Section Edit
		this.is_global_section_mode = $("body.single-rey-global-sections").length > 0;

		this.refresh = function () {
			// Solution from https://stackoverflow.com/a/4819886
			this.is_touch_device = function () {
				var prefixes = " -webkit- -moz- -o- -ms- ".split(" ");
				var mq = function (query) {
					return window.matchMedia(query).matches;
				};
				if ("ontouchstart" in window || (window.DocumentTouch && document instanceof DocumentTouch)) {
					return true;
				}
				// include the 'heartz' as a way to have a non matching MQ to help terminate the join
				// https://git.io/vznFH
				var query = ["(", prefixes.join("touch-enabled),("), "heartz", ")"].join("");
				return mq(query);
			};
			this.is_mobile = window.matchMedia("(max-width: 767px)").matches;
			this.is_tablet = window.matchMedia("(min-width: 768px) and (max-width: 1024px)").matches;
			this.is_desktop = window.matchMedia("(min-width: 1025px)").matches;
			this.is_desktop_touch = window.matchMedia("(min-width: 1025px)").matches && this.is_touch_device();
			this.adminBar = $("body.admin-bar").length > 0 ? (!this.is_desktop ? 46 : 32) : 0;
		};

		this.refresh();

		$(window).on(
			"resize",
			self.debounce(function () {
				self.refresh();
			}, 300)
		);
	};

	$.reyHelpers = new ReyHelpers();

	/**
	 * Rey Theme
	 */
	var ReyTheme = function () {
		this.init = function () {

			$(document).trigger('reytheme/before_init');

			this.events();

			this.cssJs();
			this.checkXl();
			this.searchForm();
			this.general_html();
			this.animateItems();
			this.initMainMenus();
			this.initSlick();

			$(document).trigger('reytheme/init');
		};

		this.elements = {
			$body: $(document.body),
			$header: $(".rey-siteHeader"),
			$site_overlay: $(".rey-overlay--site"),
			$cssJsStylesheet: $("style#rey-js-css"),
		};

		/**
		 * Creates a style tag and adds custom CSS styles
		 */
		this.cssJs = function () {
			var cssStyles = "",
				rootStyles = "";

			this.getScrollbarSize();

			rootStyles += "--site-width:" + $.reyHelpers.applyFilter('rey/siteWidth', (document.documentElement.clientWidth || document.body.clientWidth) + "px") + ";";
			rootStyles += "--scrollbar-width:" + this.scrollbarSize + "px;";

			// update root header height
			if (this.elements.$header.length && !this.elements.$header.hasClass("rey-siteHeader--default")) {
				rootStyles += "--header-default--height:" + $.reyHelpers.applyFilter('rey/headerHeight', this.elements.$header.outerHeight() + "px") + ";";
			}

			if (rootStyles !== '') {
				cssStyles += ":root{" + rootStyles + ";}";
			}

			if (!this.elements.$cssJsStylesheet.length) {
				this.elements.$cssJsStylesheet = $('<style id="rey-js-css" />').appendTo($("head"));
			}

			$(document).trigger('rey/cssjs', [this.elements.$cssJsStylesheet, cssStyles, this]);

			this.elements.$cssJsStylesheet.text(cssStyles);
		};

		this.events = function () {
			var self = this;

			$(document).on("keyup", function (e) {
				if (e.keyCode == 27) {
					$(document).trigger("rey/close_panels");
				}
			});

			// Close header panels on overlay click
			$(".rey-overlay").on("click", function () {
				$(document).trigger("rey/close_panels");
			});

			// refresh events
			$(window).on(
				"resize",
				$.reyHelpers.debounce(function () {
					self.cssJs();
					self.checkXl();
				}, 50)
			);

			this.elements.$header.on('lazyloaded', function (e) {
				self.cssJs();
			});

			$(".rey-header-dropPanel-btn").on("click", function (e) {
				e.preventDefault();

				// Prevent click on Global Section Mode
				if ($.reyHelpers.is_global_section_mode) {
					return;
				}

				var $this = $(this),
					$panel = $this.parent();

				if (!$panel.hasClass("--active")) {
					// open
					$(document).trigger("rey/close_panels", ["drop-panel"]);
					$.reyHelpers.overlay('header', 'open');
					$panel.addClass("--active");
				}
			});

			$(document).on("rey/close_panels", function (e, except) {
				if (except !== "drop-panel") {
					$(".rey-header-dropPanel").removeClass("--active");
				}
			});

			// Link that copies data-url attribute
			$(document).on("click", ".js-copy-url", function (e) {
				e.preventDefault();

				var $temp = $("<input>");
				$("body").append($temp);
				$temp.val($(this).attr("data-url")).select();
				document.execCommand("copy");
				$temp.remove();
				$(this).addClass("--copied");
			});

			$(document).on("rey/site_loaded", function () {
				self.elements.$body.addClass("site-preloader--loaded");
			});

			$(document).on("rey/close_panels", function (e, except) {
				$.reyHelpers.overlay('site', 'close');
				$.reyHelpers.overlay('header', 'close');
				if ($.reyHelpers.scrollDisabled) {
					$.reyHelpers.doScroll.enable();
				}
			});

			$(document).on("rey/post/loaded", function (event, newItems) {
				self.general_html(newItems);
			});

			/**
			 * Animate in posts or products
			 */
			$(document).on("rey/refresh_html", function (e, scope) {
				// arrange blog columns
				self.blogColumns(scope);
			});

		};

		this.getScrollbarSize = function () {
			var scrollDiv = document.createElement("div");
			scrollDiv.setAttribute("style", "width: 100px; height: 100px; overflow: scroll; position: absolute; left: -200vw;");
			document.body.appendChild(scrollDiv);
			this.scrollbarSize = scrollDiv.offsetWidth - scrollDiv.clientWidth;
			document.body.removeChild(scrollDiv);
		};

		this.checkXl = function () {
			var ww = $(window).width(),
				containerWidth = $.reyHelpers.$container_size;

			$('html').attr('data-xl', function () {
				var v;

				if (ww < containerWidth && ww < 1025) {
					v = 0;
				}
				else if (ww > containerWidth) {
					v = 2;
				}
				else if (ww < containerWidth && ww > 1025) {
					v = 1;
				}

				return v;
			});
		}

		this.searchForm = function () {

			var self = this,
				$searchBtn = $('.js-rey-headerSearch-form'),
				$hdIcon = $searchBtn.parent();

			var close = function () {
				$.reyHelpers.overlay('header', 'close');
				$hdIcon.removeClass('--active');
			};

			$searchBtn.on('click', function () {

				if (!$hdIcon.hasClass('--active')) {
					$(document).trigger("rey/close_panels", ['header-search-form']);
					$.reyHelpers.overlay('header', 'open');
					$hdIcon.addClass('--active');
				}
				else {
					close();
				}
			});

			$(document).on("rey/close_panels", function (e, except) {
				if (except !== "header-search-form") {
					close();
				}
			});

		};

		this.initMainMenus = function () {
			var self = this;
			$('.rey-mainNavigation.rey-mainNavigation--desktop').each(function (i, el) {
				var mainMenu = Object.create(self.main_menu);
				mainMenu.init($(el), self);
			})
		};

		this.main_menu = {
			windowEdge: $(window).width(),
			init: function ($el, reyTheme) {

				this.$mainMenu = $el;

				if (!this.$mainMenu.length) {
					return;
				}

				this.$mobileBtn = this.$mainMenu.prevAll('.rey-mainNavigation-mobileBtn');
				this.$mobileNav = this.$mainMenu.nextAll('.rey-mainNavigation.rey-mainNavigation--mobile');
				this.id = this.$mainMenu.attr('data-id');
				this.$t = reyTheme;

				if ($.reyHelpers.params.theme_js_params.menu_prevent_delays) {
					this.$mainMenu.addClass('--prevent-delays');
				}

				this.createSubmenuIndicators();
				this.createBadges();
				this.events();
			},

			createBadges: function () {
				var badgeClasses = [
					'.menu-item.--badge-green',
					'.menu-item.--badge-red',
					'.menu-item.--badge-orange',
					'.menu-item.--badge-blue',
					'.menu-item.--badge-accent'
				];

				$(badgeClasses.join(','), this.$mainMenu).each(function (i, el) {
					var $a = $(el).children('a');
					$('<i class="--menu-badge"></i>').text($a.attr('title')).prependTo($a.children('span'));
				});
			},

			createSubmenuIndicators: function () {
				var self = this;
				$.each($('.menu-item-has-children', this.$mainMenu), function (i, el) {

					var _appendTo = el,
						lvl1 = $(el).hasClass('depth--0');
					if (!lvl1) {
						_appendTo = $(' > a', el);
					}

					$('<i class="--submenu-indicator --submenu-indicator-' + self.$mainMenu.attr('data-sm-indicator') + '"></i>').appendTo(_appendTo);

					if (lvl1 && self.$mainMenu.children('ul').hasClass('--submenu-top')) {
						$('<i class="__submenu-top-indicator"></i>').appendTo(_appendTo);
					}


				});

				$.each($('.menu-item-has-children > a > span', this.$mobileNav), function (i, el) {
					$('<i class="--submenu-indicator --submenu-indicator-' + self.$mainMenu.attr('data-sm-indicator') + '"></i>').prependTo(el);
				});
			},

			events: function () {
				var self = this;

				// Prevent click on Global Section Mode
				if ($.reyHelpers.is_global_section_mode) {
					return;
				}

				$(document).on("rey/close_panels", function (e, except) {
					if (except !== "mobile-menu" + self.id) {
						self.closeMobileMenu();
					}
				});

				// Main Menu Hover with overlay
				var timer;
				$(".rey-mainMenu.rey-mainMenu--desktop > .menu-item-has-children", this.$mainMenu)
					.on("mouseenter", function (e) {
						$(document).trigger("rey/close_panels");

						if ($.reyHelpers.params.theme_js_params.menu_hover_overlay) {
							$.reyHelpers.overlay('header', 'open');
						}

						if ($.reyHelpers.params.theme_js_params.menu_prevent_delays) {
							$(e.currentTarget).addClass("--hover");
						}
						else {
							timer = setTimeout(function () {
								$(e.currentTarget).addClass("--hover");
							}, parseFloat($.reyHelpers.params.theme_js_params.menu_items_hover_timer));
						}
					})
					.on("mouseleave", function (e) {
						clearTimeout(timer);
						$(e.currentTarget).removeClass("--hover");
						$.reyHelpers.overlay('header', 'close');
					});

				$(".rey-mainMenu.rey-mainMenu--desktop > .menu-item .menu-item-has-children", this.$mainMenu)
					.on("mouseenter", function (e) {
						$(e.currentTarget).addClass("--hover");
					})
					.on("mouseleave", function (e) {
						$(e.currentTarget).removeClass("--hover");
					});

				var menuActiveTimer;
				$(".rey-mainMenu.rey-mainMenu--desktop", this.$mainMenu)
					.on("mouseenter", function (e) {
						menuActiveTimer = setTimeout(function () {
							self.$mainMenu.addClass("--active");
						}, parseFloat($.reyHelpers.params.theme_js_params.menu_hover_timer));
					})
					.on("mouseleave", function (e) {
						clearTimeout(menuActiveTimer);
						self.$mainMenu.removeClass("--active");
					});


				// mobile menu - open panel
				this.$mobileBtn.on("click", function (e) {
					e.preventDefault();
					$(document).trigger("rey/close_panels", ['mobile-menu' + self.id]); // close other panels
					$.reyHelpers.overlay('header', 'open'); // open overlay
					$.reyHelpers.doScroll.disable();
					self.$mobileNav.addClass('--is-active');
					self.$t.elements.$body.addClass("--mobileNav--active");
				});

				// mobile menu - close panel
				$(".rey-mobileMenu-close", this.$mobileNav).on("click", function (e) {
					e.preventDefault();
					self.closeMobileMenu();
				});

				// mobile menu - submenus show/hide
				$(".rey-mainMenu-mobile .menu-item-has-children > a", this.$mobileNav).on("click", function (e) {
					var $this = $(this),
						hasSubs = $this.siblings().is('.sub-menu');
					if (hasSubs) {
						e.preventDefault();
						$this.next().slideToggle();
					}
				});

				// make delay for menu items
				$(".rey-mainMenu--desktop .sub-menu", this.$mainMenu).each(function (i, el) {
					$("> li > a > span", el).each(function (i, el) {
						$(el).css({ "transition-delay": ((0.03 * i)) + "s" });
					});
				});

				// make delay for mobile menu items
				$("ul.rey-mainMenu-mobile", this.$mobileNav).each(function (i, el) {
					$("> li > a > span", el).each(function (i, el) {
						$(el).css({ "transition-delay": ((0.03 * i) + 0.3) + "s" });
					});
				});

				// determine submenus direction
				$(".rey-mainMenu.rey-mainMenu--desktop .menu-item-has-children", this.$mainMenu).on('mouseenter', function (event) {

					var $submenu = $(event.currentTarget).children('.sub-menu');

					if ($submenu.length === 0) {
						return;
					}

					// remove reverse classes
					$submenu.removeClass('--reached-end');
					// get end edge
					var submenuEndEdge = $submenu.offset().left + $submenu.width();
					// compare window edge with submenu's edge
					if (submenuEndEdge > self.windowEdge) {
						$submenu.addClass('--reached-end');
					}
				});

				$(".menu-item > a[href*='#']:not([href='#'])", this.$mobileNav).on("click", function (e) {
					e.preventDefault();
					self.closeMobileMenu();
				});

				$(window).on(
					"resize",
					$.reyHelpers.debounce(function () {
						self.windowEdge = $(window).width();
					}, 500)
				);

				$(document).on("keyup", function (e) {
					if (e.keyCode == 27) {
						$(".rey-mainMenu.rey-mainMenu--desktop > .menu-item-has-children", self.$mainMenu).removeClass('--hover');
					}
				});
			},

			closeMobileMenu: function () {
				$.reyHelpers.overlay('header', 'close');
				this.$mobileNav.removeClass('--is-active');
				$.reyHelpers.doScroll.enable();
				this.$t.elements.$body.removeClass("--mobileNav--active");
			},
		};

		this.initSlick = function () {
			var self = this;
			$(".rey-slick[data-slick]").slick({
				dotsClass: "rey-slick__dots",
				rows: 0
			});
		};

		this.general_html = function (scope) {
			var $scope = $(scope || document);

			$(".rey-postContent p > iframe", $scope).wrap(
				"<div class='embed-responsive embed-responsive-16by9'></div>"
			);

			var $dropPanel = $(".rey-header-dropPanel", $scope);
			if ($dropPanel.length && $dropPanel.offset().left < $(window).width() / 2) {
				$dropPanel.addClass("--left");
			}

			$(".u-toggle-text").each(function (i, el) {
				var $el = $(el);
				el.style.setProperty("--toggle-height", $el.css("line-height"));
				$el.children("button").on("click", function (e) {
					e.preventDefault();
					$el.toggleClass("--collapsed");
				});
			});

			$(".u-toggle-text-next-btn").each(function (i, el) {
				var $el = $(el);
				$el.next('.btn').on("click", function (e) {
					e.preventDefault();
					$el.toggleClass("--expanded");
				});
			});

			$(document).on("click", ".js-toggle-target", function () {
				var $this = $(this),
					$target = $($this.attr('data-target')),
					$targetClass = $($this.attr('data-target-class'));
				if ($target.length) {
					$this.toggleClass("--toggled");
					$target.slideToggle("fast");
				}
				if ($targetClass.length) {
					$this.toggleClass("--toggled");
					$targetClass.toggleClass("--toggled");
				}
			});

			$('.js-check-empty').each(function (i, el) {
				if ($(el).children().length === 0) {
					$(el).addClass('--empty');
				}
			});

			// Wrap elements
			$.each({
				'.tabs.wc-tabs': 'rey-wcTabs-wrapper'
			}, function (elementClass, wraperClass) {
				$(elementClass).wrap('<div class="' + wraperClass + '" />')
			});

			$(document).trigger("rey/refresh_html", [scope, this]);
		};

		this.$blogColumnsMasonry = false;

		this.blogColumns = function (scope) {
			var self = this;

			if (typeof $.fn.masonry != "undefined" && typeof $.fn.imagesLoaded != "undefined") {
				var $container = $(".rey-siteMain[class*='blog--columns-']:not(.blog--columns-1) .rey-postList");
				if ($container.length && !$.reyHelpers.is_mobile) {
					$container.imagesLoaded(function () {
						if (!self.$blogColumnsMasonry) {
							self.$blogColumnsMasonry = $container.masonry({
								itemSelector: ".rey-postItem",
								percentPosition: true,
								isInitLayout: false
							});
							self.$blogColumnsMasonry.on("layoutComplete", function () {
								self.animateItems(scope);
							});
							self.$blogColumnsMasonry.masonry();
						} else {
							self.$blogColumnsMasonry.masonry("appended", scope);
						}
					});
				}
			}
		};

		this.animateItems = function ($scope) {

			var targetClass = ".is-animated-entry",
				getTargets = function () {
					var toAnimate = [];
					if (typeof $scope != "undefined") {
						$scope.each(function (i, el) {
							if (!$(el).hasClass(targetClass)) {
								toAnimate.push(el);
							}
						});
					} else {
						toAnimate = document.querySelectorAll(targetClass);
					}
					return toAnimate;
				};

			if (typeof ScrollOut != "undefined") {
				// on first site load, the css seems to be downloaded slower
				// and the scrollout library is not calculating element dimensions properly.
				// Adding a 10ms delay solves the issue.
				setTimeout(function () {
					// Do scroll
					ScrollOut({
						targets: getTargets(),
						once: true,
						onShown: function (el, ctx, doc) {
							$(el)
								.addClass("--animated-in")
								.css("transition-delay", 0.05 * ctx.index + "s");
						}
					});
				}, 10);
			} else {
				if (typeof $scope != "undefined") {
					$scope.addClass("--animated-in");
				}
			}
		};

		this.init();
	};

	$(document).ready(function () {
		$.reyTheme = new ReyTheme();
	});

	$(window).load(function () {
		if ($.reyHelpers.$sitePreloader.length > 0 && !$.reyHelpers.is_edit_mode) {
			$.reyHelpers.$sitePreloader.fadeOut("fast", function () {
				$(document).trigger("rey/site_loaded");
			});
		}
	});
})(jQuery);
