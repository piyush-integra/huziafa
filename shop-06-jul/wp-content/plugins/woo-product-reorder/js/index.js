jQuery(document).ready(function() {
  jQuery("#save_order").click(function() {
    jsonObj = [];
    var counter = 0;
    jQuery("#inner .gravity").each(function() {
      counter++;
      var index1 = jQuery(this).index();
      jQuery(this).attr("data-order", index1);
      var key1 = jQuery(this).data("pid");
      var value1 = index1;

      item = {};
      item["ID"] = key1;
      item["menu_order"] = value1;
      jsonObj.push(item);
    });

    jQuery.ajax({
      url: ajaxurl,
      type: "POST",
      data: { action: "wc_order", obj: jsonObj },
      success: function(data) {
        alert("Sort order saved!");
      }
    });
  });

  var $grid = jQuery("#inner").isotope({
    itemSelector: ".gravity"
  });

  // filter items on button click
  jQuery(".filter-button-group").on("click", "button", function() {
    var filterValue = jQuery(this).attr("data-filter");
    $grid.isotope({ filter: filterValue });
  });

  var filters = {};

  jQuery(".filters").on("click", ".button", function(event) {
    var $button = $(event.currentTarget);
    // get group key
    var $buttonGroup = $button.parents(".button-group");
    var filterGroup = $buttonGroup.attr("data-filter-group");
    // set filter for group
    filters[filterGroup] = $button.attr("data-filter");
    // combine filters
    var filterValue = concatValues(filters);
    // set filter for Isotope
    $grid.isotope({ filter: filterValue });
  });

  var list = jQuery("#inner");
  list.sortable({
    update: function(event, ui) {},

    cursor: "move",
    start: function(event, ui) {
      ui.item.addClass("grabbing moving").removeClass("gravity");
      ui.placeholder
        .addClass("starting")
        .removeClass("moving")
        .css({
          top: ui.originalPosition.top,
          left: ui.originalPosition.left
        });
      list.isotope("reloadItems");
    },
    change: function(event, ui) {
      ui.placeholder.removeClass("starting");
      list.isotope("reloadItems").isotope({
        sortBy: "original-order",
        transformsEnabled: false
      });
    },
    beforeStop: function(event, ui) {
      ui.placeholder.after(ui.item);
    },
    stop: function(event, ui) {
      ui.item.removeClass("grabbing").addClass("gravity");
      list.isotope("reloadItems").isotope({
        sortBy: "original-order",
        transformsEnabled: false
      });
    }
  });

  jQuery(document).ready(function() {
    // store filter for each group
    var filters = {};

    jQuery(".filters").on("click", ".button", function(event) {
      var $button = $(event.currentTarget);
      // get group key
      var $buttonGroup = $button.parents(".button-group");
      var filterGroup = $buttonGroup.attr("data-filter-group");
      // set filter for group
      filters[filterGroup] = $button.attr("data-filter");
      // combine filters
      var filterValue = concatValues(filters);
      // set filter for Isotope
      $grid.isotope({ filter: filterValue });
    });

    // change is-checked class on buttons
    jQuery(".button-group").each(function(i, buttonGroup) {
      var $buttonGroup = $(buttonGroup);
      $buttonGroup.on("click", "button", function(event) {
        $buttonGroup.find(".is-checked").removeClass("is-checked");
        var $button = $(event.currentTarget);
        $button.addClass("is-checked");
      });
    });
  });
});
