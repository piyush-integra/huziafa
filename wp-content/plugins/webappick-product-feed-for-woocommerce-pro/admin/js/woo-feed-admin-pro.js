"use strict"; // noinspection JSUnresolvedVariable

/**!
 * WooFeed Pro Scripts
 * @version 1.0.0
 * @package WooFeed
 * @copyright 2020 WebAppick
 *
 */

(function($, window, document, wf, wpAjax, opts) {
	"use strict";
	/* global ajaxurl, wpAjax, postboxes, pagenow, alert, deleteUserSetting, typenow, adminpage, thousandsSeparator, decimalPoint, isRtl */

	$(window).load(function() {
		var __idx__ = 0,
			srt = $('#wf_str_replace tbody');
		$(document).on('change', "#custom2_attribute", function(e) {
			// Custom 2 template attribute name
			$("#custom2_attribute_value").val( `{${$(this).val().trim()}}` ); // phpcs:ignore WordPressVIPMinimum.JS.HTMLExecutingFunctions.html
		}).on('change', '#wpf_ptitle', function() {
			// Variation Product Title
			let selectize = $(this)[0].selectize,
				titleEl = $("textarea[name='ptitle_show']"),
				title = titleEl.val().trim().split('|');
			title.push( $(this).val() );
			// Show Title
			titleEl.val( title.filter( x => '' !== x ).join('|') );
			selectize.clear(true); // reset the select value silently.
			selectize.refreshOptions(false); // reset selectize scroll position silently.
		}).on('click', '#wf_new_str_replace', function() {
			if (0 === __idx__) {
				__idx__ = srt.find('tr').length;
			} else {
				__idx__++;
			}

			srt.append($('#wf_str_replace_template').text().trim().replace(/__idx__/g, __idx__));
		}).on('click', '.wf-add-row', function(e) {
			e.preventDefault();

			var self = $(this),
				__idx__ = parseInt(self.data('idx') || 0) + 1,
				template = (/^</.test(self.data('template')) ? self.data('template') : $(self.data('template')).text()).trim(),
				target = $(self.data('target'));

			target.append(template.trim().replace(/__idx__/g, __idx__));
			wf.helper.selectize();
			self.data('idx', __idx__);
		}).on('submit', '#attribute-mapping-form', function(e) {
			if (!$(this).find('[name^="value["]').length) {
				alert(opts.form.one_item_required);
				return false;
			}
		}).on('click', '#wf_newFilter', function() {
			// Add New Condition for Filter
			$('#table-advanced-filter tbody tr:eq(0)').show().clone().find('input').val('').end().find('select').val('').end().insertAfter('#table-advanced-filter tbody tr:last');
			$('.fsrow:gt(2)').prop('disabled', false);
			$('.daRow:eq(0)').hide();
		}).on('click', '#wf_newCon', function() {
			// Dynamic Attribute Add New Condition
			$('#table-1 tbody tr:first').show().clone().find('input').val('').end().insertAfter('#table-1 tbody tr:last');
			$('.fsrow:gt(7)').prop('disabled', false);
			$('.daRow:eq(0)').hide();
			$('#table-1 tbody tr:last td select.woo_feed_dynamic_attr_condition_select').change(function() {
				woo_feed_dynamic_attr_condition_between($(this));
			});
		}).on('change keyup', '.treegrid-parent', function() {
			// Category Mapping (Auto Field Populate)
			// noinspection ES6ConvertVarToLetConst
			var val = $(this).val(),
				parent = $(this).attr('data-cat_id'); // noinspection ES6ConvertVarToLetConst

			$(".treegrid-parent-" + parent).val(val);
		}); // placeholder for Between Dynamic Attribute condition

		function woo_feed_dynamic_attr_condition_between(elem) {
			// noinspection ES6ConvertVarToLetConst
			var input = elem.closest('tr').find('.woo_feed_dynamic_attr_condition_value');
			if ('between' === elem.val()) input.attr('placeholder', 'Ex: 10,20');
			else input.attr('placeholder', '');
		}

		$('.woo_feed_dynamic_attr_condition_select').change(function() {
			woo_feed_dynamic_attr_condition_between($(this));
		});

		if ($('#attribute-mapping-form').length > 0) {
			wf.helper.sortable();
		}
	});
	$(document).on('feedEditor.init', function() {
		wf.helper.fancySelect($('#wf_product_post_status, .filter_mode'), {
			maxItemShow: 2
		});
	});
})(jQuery, window, document, wf, wp.ajax, wpf_ajax_obj);
