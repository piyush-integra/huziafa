(function ($) {
	"use strict";

	var $dashboxes = $(".rey-dashboard-main");

	if (typeof $.fn.masonry != "undefined") {
		$dashboxes.masonry({
			itemSelector: ".rey-dashBox"
		});
	}

	var ls = {
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
	 * Notices
	 */

	$('.js-storeNotice').each(function (i, el) {
		var $el = $(el),
			from = 'rey-notice-' + $el.attr('data-from');

		if (ls.get(from)) {
			$el.hide();
		}

		$('.notice-dismiss', $el).on('click', function (e) {
			$el.hide();
			ls.set(from, true, 12 * 60 * 60 * 1000); // 12hr
		});
	})



	/**
	 * TGMPA stuff
	 */

	// append refresh link
	$('<li class="rey-refresh-plugins"><a href="' + reyAdminParams.refresh_plugins_url + '">' + reyAdminParams.refresh_plugins_text + '</a></li>').appendTo($('.tgmpa.wrap .subsubsub'));

	$("form#tgmpa-plugins .wp-list-table tbody tr").each(function (i, row) {
		var $row = $(row);
		var $col_type = $row.find('td.type.column-type');
		var $needReg = $row.find('.rey-tgmpaNeedReg');

		if ($col_type.length && $col_type.text() === "Required") {
			$row.addClass("--required");
		}

		if ($needReg.length) {
			$row.find('input[type="checkbox"]').prop('disabled', true);
		}

	});

	var rearrangeDasboxes = function () {
		if (typeof $.fn.masonry != "undefined") {
			// trigger layout after item size changes
			$dashboxes.masonry("layout");
		}
	};

	// Settings page
	var $settingsPage = $('body[class*="_page_rey-settings"]'),
		$postBoxContainer = $("#postbox-container-2", $settingsPage),
		$settingsSaveBtn = $("#publishing-action", $settingsPage)
			.detach()
			.prependTo($postBoxContainer);

	// Dashboard De-register
	$(".js-dashDeregister").on("click", function (e) {
		e.preventDefault();

		var $this = $(this);

		$this.addClass("rey-adminBtn--disabled");

		$.ajax({
			method: "get",
			url: reyAdminParams.ajax_url,
			cache: false,
			data: {
				action: "rey_dashboard_deregister",
				security: reyAdminParams.ajax_dashboard_nonce
			},
			success: function (response) {
				if (response) {
					if (response.success && response.success == true) {
						$this.text(reyAdminParams.dashboard_strings.deregister_success);
						// redirect after 2 seconds
						setTimeout(function () {
							location.reload();
						}, 2000);
					} else {
						$this.removeClass("rey-adminBtn--disabled");
					}
				}
			},
			error: function (jqXHR, textStatus, errorThrown) {
				$this.removeClass("rey-adminBtn--disabled");
				console.error(jqXHR);
				console.error(textStatus);
				console.error(errorThrown);
			}
		});
	});

	// Dashboard Register
	$(".js-dashBox-registerForm").on("submit", function (e) {
		e.preventDefault();

		var $this = $(this),
			$registerBtn = $this.find(".rey-adminBtn");

		$(".rWizard-response").remove();

		$registerBtn.addClass("rey-adminBtn--disabled --loading");

		$.ajax({
			method: "post",
			url: reyAdminParams.ajax_url,
			cache: false,
			data: {
				action: "rey_dashboard_register",
				security: reyAdminParams.ajax_dashboard_nonce,
				rey_purchase_code: $('input[name="rey_purchase_code"]', $this).val(),
				rey_email_address: $('input[name="rey_email_address"]', $this).val(),
				rey_subscribe_newsletter: $('input[name="rey_subscribe_newsletter"]', $this).val()
			},
			success: function (response) {
				$registerBtn.removeClass("--loading");

				if (response) {
					if (response.success && response.success == true) {
						$registerBtn.text(reyAdminParams.dashboard_strings.reloading_text);
						setTimeout(function () {
							location.reload();
						}, 2000);
					} else {
						$registerBtn
							.removeClass("rey-adminBtn--disabled")
							.text(reyAdminParams.dashboard_strings.default_btn_text)
							.parent()
							.before("<p class='reyAdmin-response reyAdmin-notice --error'>" + response.data + "</p>");
					}
				}
			},
			error: function (jqXHR, textStatus, errorThrown) {
				$registerBtn
					.removeClass("rey-adminBtn--disabled")
					.removeClass("--loading")
					.text(reyAdminParams.dashboard_strings.default_btn_text)
					.parent()
					.before(
						"<p class='reyAdmin-response reyAdmin-notice --error'>" +
						reyAdminParams.dashboard_strings.something_went_wrong +
						"</p>"
					);
				console.error(jqXHR);
				console.error(textStatus);
				console.error(errorThrown);
			}
		});
	});

	var getResponseMatchJson = function (str) {
		var rxp = /{("success"[^}]+)}/g,
			curMatch = rxp.exec(str);

		if (curMatch && curMatch.length) {

			return JSON.parse(curMatch[0]);
		}

		return false;
	}

	/**
	 * Dashboard install plugins.
	 */

	$(".js-dashBox-installRequired").on("click", function (e) {
		e.preventDefault();

		var $this = $(this);

		// disable button
		$this.addClass("rey-adminBtn--disabled").text(reyAdminParams.dashboard_strings.installing_btn_text);

		$(".reyAdmin-reqPlugin.--inactive:not(.--uninstallable)", $this.closest(".rey-dashBox-content")).addClass('--is-installing');

		$(".reyAdmin-response").remove();

		var url = new URL(window.location.href),
			page = url.searchParams.get("page") || "";

		var doDashboardPluginInstallAjax = function () {
			$.ajax({
				method: "get",
				url: reyAdminParams.ajax_url,
				dataType: 'text',
				cache: false,
				data: {
					action: "rey_dashboard_install_plugins",
					security: reyAdminParams.ajax_dashboard_nonce,
					page: page
				},
				success: function (response) {

					rearrangeDasboxes();

					var responseJson = getResponseMatchJson(response);

					if (responseJson) {
						if (responseJson.success && responseJson.success === true && responseJson.data !== false) {

							var $wrapper = $this.closest(".rey-dashBox-content");

							$(".reyAdmin-reqPlugin[data-slug='" + responseJson.data + "']", $wrapper).removeClass('--is-installing --inactive').addClass("--is-active");

							doDashboardPluginInstallAjax();
						}
						else {
							$this.text(reyAdminParams.dashboard_strings.reloading_text);
							setTimeout(function () {
								location.reload();
							}, 2000);

						}
					}
					else {
						$this
							.text(reyAdminParams.dashboard_strings.default_install_btn_text)
							.parent()
							.before("<p class='reyAdmin-response reyAdmin-notice --error'>" + responseJson.data + "</p>");
					}
				},
				error: function (jqXHR, textStatus, errorThrown) {
					rearrangeDasboxes();

					$this
						.text(reyAdminParams.dashboard_strings.default_install_btn_text)
						.parent()
						.before(
							"<p class='reyAdmin-response reyAdmin-notice --error'>" +
							reyAdminParams.dashboard_strings.something_went_wrong +
							"</p>"
						);
					console.error(jqXHR);
					console.error(textStatus);
					console.error(errorThrown);
				}
			});
		}

		doDashboardPluginInstallAjax();
	});


	// Dashboard Newsletter Form
	$(".js-subscribeNewsletterForm").on("submit", function (e) {
		e.preventDefault();

		var $this = $(this),
			$submitBtn = $this.find(".rey-adminBtn");

		$(".rWizard-response").remove();

		$submitBtn.addClass("rey-adminBtn--disabled --loading");

		$.ajax({
			method: "post",
			url: reyAdminParams.ajax_url,
			cache: false,
			data: {
				action: "rey_dashboard_newsletter_subscribe",
				security: reyAdminParams.ajax_dashboard_nonce,
				rey_email_address: $('input[name="rey_email_address"]', $this).val()
			},
			success: function (response) {
				$submitBtn.removeClass("--loading");

				if (response) {
					if (response.success && response.success == true) {
						$submitBtn.text(reyAdminParams.dashboard_strings.reloading_text);
						setTimeout(function () {
							location.reload();
						}, 2000);
					} else {
						$submitBtn
							.removeClass("rey-adminBtn--disabled")
							.text(reyAdminParams.dashboard_strings.subscribe_default_btn_text)
							.parent()
							.before("<p class='reyAdmin-response reyAdmin-notice --error'>" + response.data + "</p>");
					}
				}
			},
			error: function (jqXHR, textStatus, errorThrown) {
				$submitBtn
					.removeClass("rey-adminBtn--disabled")
					.removeClass("--loading")
					.text(reyAdminParams.dashboard_strings.subscribe_default_btn_text)
					.parent()
					.before(
						"<p class='reyAdmin-response reyAdmin-notice --error'>" +
						reyAdminParams.dashboard_strings.something_went_wrong +
						"</p>"
					);
				console.error(jqXHR);
				console.error(textStatus);
				console.error(errorThrown);
			}
		});
	});


	$('.js-installChild').on('click', function (e) {
		e.preventDefault();

		var $this = $(this);

		$this.addClass("rey-adminBtn--disabled --loading");

		$.ajax({
			method: "post",
			url: reyAdminParams.ajax_url,
			cache: false,
			data: {
				action: "rey_dashboard_install_child",
				security: reyAdminParams.ajax_dashboard_nonce,
			},
			success: function (response) {
				$this.removeClass("--loading");

				if (response && response.success) {
					$this.text(reyAdminParams.dashboard_strings.copying_settings);

					$.ajax({
						method: "post",
						url: reyAdminParams.ajax_url,
						cache: false,
						data: {
							action: "rey_dashboard_migrate_opts_child",
							security: reyAdminParams.ajax_dashboard_nonce,
						},
						success: function (response) {
							$this.removeClass("--loading");

							if (response && response.success) {
								$this.text(reyAdminParams.dashboard_strings.reloading_text);
								setTimeout(function () {
									location.reload();
								}, 2000);
							}

						},
					});
				}

			},
			error: function (jqXHR, textStatus, errorThrown) {
				$this
					.removeClass("rey-adminBtn--disabled")
					.removeClass("--loading")
					.text(reyAdminParams.dashboard_strings.installing_btn_text)
					.parent()
					.before(
						"<p class='reyAdmin-response reyAdmin-notice --error'>" +
						reyAdminParams.dashboard_strings.something_went_wrong +
						"</p>"
					);
				console.error(jqXHR);
				console.error(textStatus);
				console.error(errorThrown);
			}
		});
	});

	$('.js-childCopySettings').on('click', function (e) {
		e.preventDefault();

		var $this = $(this);

		$this.addClass("rey-adminBtn--disabled --loading");

	});

	/**
	 * Install Wizard
	 */

	var $installWizardPage = $('body[class*="_page_rey-setup-wizard"]'),
		$installWrapper = $(".rey-wizard-wrapperInner", $installWizardPage),
		$installSteps = $(".rWizard-step", $installWizardPage),
		$registerForm = $(".js-rWizard-registrationForm", $installWizardPage),
		$registerStepButton = $(".rey-adminBtn.rey-adminBtn-primary", $registerForm),
		$disableWizardBtn = $(".js-skipWizard", $installWizardPage);

	// method to activate next panel
	var stepNewPanel = function (current, next) {
		$installSteps.removeClass("--active");
		$installSteps.eq(current).fadeOut(300);
		$installSteps.eq(next).addClass("--active");
		$installWrapper.height($installSteps.eq(next).outerHeight());
	};

	// check if registered form is not needed
	var startStep = $(".rWizard-step--1.--registered", $installWizardPage).length ? 1 : 0;

	// check if plugin installation is not needed
	if (startStep === 1) {
		startStep = $(".rWizard-step--2.--plugins-installed", $installWizardPage).length ? 2 : 1;
	}

	// activate step
	$installSteps.eq(startStep).addClass("--active");
	// adjust wrapper height
	$installWrapper.height($installSteps.eq(startStep).outerHeight()).css("opacity", 1);

	// Submit wizard registration form
	$registerForm.on("submit", function (e) {
		e.preventDefault();

		$(".rWizard-response").remove();

		$registerStepButton.addClass("--loading").text(reyAdminParams.wizard_strings.registering_btn_text);

		$.ajax({
			method: "post",
			url: reyAdminParams.ajax_url,
			cache: false,
			data: {
				action: "rey_register_purchase_code",
				security: reyAdminParams.ajax_wizard_nonce,
				rey_purchase_code: $('input[name="rey_purchase_code"]', $registerForm).val(),
				rey_email_address: $('input[name="rey_email_address"]', $registerForm).val(),
				rey_subscribe_newsletter: $('input[name="rey_subscribe_newsletter"]', $registerForm).val()
			},
			success: function (response) {
				$registerStepButton.removeClass("--loading");

				if (response) {
					if (response.success && response.success == true) {

						var $pluginsHtmlWrapper = $('.js-reyAdmin-reqPlugins');

						$pluginsHtmlWrapper.addClass('--loading');

						// refresh plugins markup
						$.ajax({
							method: "get",
							url: reyAdminParams.ajax_url,
							cache: false,
							data: {
								action: "rey_wizard_get_required_plugins_markup",
								security: reyAdminParams.ajax_wizard_nonce
							},
							success: function (response) {
								if (response.success && response.success == true) {
									$pluginsHtmlWrapper.empty().html(response.data);
								}
								$pluginsHtmlWrapper.removeClass('--loading');

								// go to next panel
								stepNewPanel(0, 1);
							},
							error: function (jqXHR, textStatus, errorThrown) {
								console.error(jqXHR);
								console.error(textStatus);
								console.error(errorThrown);
							}
						});
					} else {
						$registerStepButton
							.text(reyAdminParams.wizard_strings.default_btn_text)
							.parent()
							.before("<p class='reyAdmin-response reyAdmin-notice --error'>" + response.data + "</p>");
					}
				}
			},
			error: function (jqXHR, textStatus, errorThrown) {
				$registerStepButton
					.removeClass("--loading")
					.text(reyAdminParams.wizard_strings.default_btn_text)
					.parent()
					.before(
						"<p class='reyAdmin-response reyAdmin-notice --error'>" +
						reyAdminParams.wizard_strings.something_went_wrong +
						"</p>"
					);
				console.error(jqXHR);
				console.error(textStatus);
				console.error(errorThrown);
			}
		});
	});

	// Skip registration button
	$(".js-skipRegistration", $installWizardPage).on("click", function (e) {
		e.preventDefault();
		stepNewPanel(0, 1);
		$installWrapper.addClass("--skipped-registration");
	});

	// Back to registration button
	$(".js-backRegistration", $installWizardPage).on("click", function (e) {
		e.preventDefault();

		$installSteps.removeClass("--active");
		$installSteps
			.eq(0)
			.addClass("--active")
			.fadeIn();
		$installWrapper.height($installSteps.eq(0).outerHeight()).removeClass("--skipped-registration");
	});

	// Install plugins
	$(".js-rWizard-btnStep-2", $installWizardPage).on("click", function (e) {
		e.preventDefault();

		var $this = $(this),
			$wrapper = $this.closest(".rWizard-step"),
			installChild = $("#wizardInstallChild").prop("checked");

		// disable button
		$this.addClass("rey-adminBtn--disabled").text(reyAdminParams.wizard_strings.installing_btn_text);

		// disable checkbox
		if (installChild) {
			$("#wizardInstallChild").prop("disabled");
			$(".rWizard-installChild", $wrapper).addClass('--is-installing');
		}

		$(".reyAdmin-reqPlugin.--inactive:not(.--uninstallable)", $wrapper).addClass('--is-installing');

		$(".reyAdmin-response").remove();

		var url = new URL(window.location.href),
			page = url.searchParams.get("page") || "";

		var doWizardPluginInstallAjax = function () {
			$.ajax({
				method: "get",
				url: reyAdminParams.ajax_url,
				cache: false,
				dataType: 'text',
				data: {
					action: "rey_wizard_install_plugins",
					security: reyAdminParams.ajax_wizard_nonce,
					page: page,
					child_theme: installChild
				},
				success: function (response) {

					var responseJson = getResponseMatchJson(response);

					if (responseJson) {
						if (
							responseJson.success && responseJson.success === true &&
							responseJson.data !== false && responseJson.data !== 'child_theme'
						) {
							$(".reyAdmin-reqPlugin[data-slug='" + responseJson.data + "']", $wrapper)
								.removeClass('--is-installing --inactive')
								.addClass("--is-active");

							doWizardPluginInstallAjax();
						}
						else {

							// go to next
							stepNewPanel(1, 2);

							// disable wizard & skip wizard button
							$.ajax({
								method: "get",
								url: reyAdminParams.ajax_url,
								cache: false,
								data: {
									action: "rey_wizard_skip",
									security: reyAdminParams.ajax_wizard_nonce
								},
								success: function (response) {
									if (response && response.success && response.success == true) {
										$disableWizardBtn.css('opacity', 0);
									}
								},
								error: function (jqXHR, textStatus, errorThrown) {
									console.error(jqXHR);
									console.error(textStatus);
									console.error(errorThrown);
								}
							});

							// Enable child theme after setup
							$.ajax({
								method: "get",
								url: reyAdminParams.ajax_url,
								cache: false,
								data: {
									action: "rey_wizard_enable_child_theme",
									security: reyAdminParams.ajax_wizard_nonce
								},
								error: function (jqXHR, textStatus, errorThrown) {
									console.error(jqXHR);
									console.error(textStatus);
									console.error(errorThrown);
								}
							});
						}
					}
					else {

						$this
							.text(reyAdminParams.wizard_strings.default_btn_text)
							.parent()
							.before("<p class='reyAdmin-response reyAdmin-notice --error'>" + responseJson.data + "</p>");
					}
				},
				error: function (jqXHR, textStatus, errorThrown) {

					$this
						.text(reyAdminParams.wizard_strings.default_install_btn_text)
						.parent()
						.before(
							"<p class='reyAdmin-response reyAdmin-notice --error'>" +
							reyAdminParams.wizard_strings.something_went_wrong +
							"</p>"
						);
					console.error(jqXHR);
					console.error(textStatus);
					console.error(errorThrown);
				}
			});
		}

		doWizardPluginInstallAjax();
	});


	// Skip Wizard
	$disableWizardBtn.on("click", function (e) {
		e.preventDefault();

		var $this = $(this);

		$this.addClass("rey-adminBtn--disabled --loading");

		$.ajax({
			method: "get",
			url: reyAdminParams.ajax_url,
			cache: false,
			data: {
				action: "rey_wizard_skip",
				security: reyAdminParams.ajax_wizard_nonce
			},
			success: function (response) {
				if (response && response.success && response.success == true) {
					$this.text(reyAdminParams.wizard_strings.skipping_success);
					// redirect after 2 seconds
					setTimeout(function () {
						window.location.href = reyAdminParams.dashboard_url;
					}, 2000);
				}
			},
			error: function (jqXHR, textStatus, errorThrown) {
				console.error(jqXHR);
				console.error(textStatus);
				console.error(errorThrown);
			}
		});
	});

	// Dashbox search
	$("#dashbox-search-kb").on("keyup", function (e) {
		if (e.keyCode == 13) {
			var searchString = e.target.value.trim().replace(/ /g, "+");
			var win = window.open("https://support.reytheme.com/?source=kb&s=" + searchString, "_blank");
			win.focus();
		}
	});


})(jQuery);
