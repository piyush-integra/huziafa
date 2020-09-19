(function( $ ) {
	'use strict';

	/**
	 * All of the code for your admin-facing JavaScript source
	 * should reside in this file.
	 *
	 * Note: It has been assumed you will write jQuery code here, so the
	 * $ function reference has been prepared for usage within the scope
	 * of this function.
	 *
	 * This enables you to define handlers, for when the DOM is ready:
	 *
	 * $(function() {
	 *
	 * });
	 *
	 * When the window is loaded:
	 *
	 * $( window ).load(function() {
	 *
	 * });
	 *
	 * ...and/or other possibilities.
	 *
	 * Ideally, it is not considered best practise to attach more than a
	 * single DOM-ready or window-load handler for a particular page.
	 * Although scripts in the WordPress core, Plugins and Themes may be
	 * practising this, we should strive to set a better example in our own work.
	 */

	

	$(function() {

		$( "#stock-count-report-cat-filter" ).change(function() {console.log('this');
			var url = new URL(document.location.href);

			url.searchParams.set('cat-filter', $( "#stock-count-report-cat-filter" ).val());
			document.location.href = url.href;
		});

		
		
		// This must be a hyperlink
		$("#stock-count-report-cat-download").on('click', function(event) {
			var timeString = new Date().toLocaleString('en-GB').replace(/[/,: ]/g,'-');
			console.log(timeString);
			var cat = new URL(document.location.href).searchParams.get('cat-filter');
			console.log(cat);
			var fileName = "stock-report-"+timeString+'.csv';
			if(cat) fileName = "stock-report-"+cat+"-"+timeString+'.csv';
			// CSV
			var args = [$('table.wp-list-table'), fileName];
		
			exportTableToCSV.apply(this, args);
		
			// If CSV, don't do event.preventDefault() or return false
			// We actually need this to be a typical hyperlink
		  });
	
	});

	// Export to csv from on-screen table
	// reference - https://stackoverflow.com/a/16203218
	function exportTableToCSV($table, filename) {

		var $heads = $table.find('tr:has(th):first()'),
			$rows = $table.find('tr:has(td)'),	
		// Temporary delimiter characters unlikely to be typed by keyboard
		// This is to avoid accidentally splitting the actual contents
		tmpColDelim = String.fromCharCode(11), // vertical tab character
		tmpRowDelim = String.fromCharCode(0), // null character	
		// actual delimiter characters for CSV format
		colDelim = '","',
		rowDelim = '"\r\n"';
	
		// Grab text from table into CSV formatted string
		var csv = '"'
			+ _parseRow($heads,'th')
			+ rowDelim
			+ _parseRow($rows, 'td')
			+ '"';
		
		// Deliberate 'false', see comment below
		if (false && window.navigator.msSaveBlob) {
			
			var blob = new Blob([decodeURIComponent(csv)], {
				type: 'text/csv;charset=utf8'
			});
	
			// Crashes in IE 10, IE 11 and Microsoft Edge
			// See MS Edge Issue #10396033
			// Hence, the deliberate 'false'
			// This is here just for completeness
			// Remove the 'false' at your own risk
			window.navigator.msSaveBlob(blob, filename);
	
		} else if (window.Blob && window.URL) {
		  // HTML5 Blob        
		  var blob = new Blob([csv], {
			type: 'text/csv;charset=utf-8'
		  });
		  var csvUrl = URL.createObjectURL(blob);
	
		  $(this)
			.attr({
			  'download': filename,
			  'href': csvUrl
			});
		} else {
		  // Data URI
		  var csvData = 'data:application/csv;charset=utf-8,' + encodeURIComponent(csv);
	
		  $(this)
			.attr({
			  'download': filename,
			  'href': csvData,
			  'target': '_blank'
			});
		}

		function _parseRow($rows,selector){
			return $rows.map(function(i, row) {
				var $row = $(row),
					$cols = $row.find(selector);
			
				return $cols.map(function(j, col) {
					var $col = $(col),
						// extract immediate child text
						// reference: https://stackoverflow.com/a/14755309
						nodes = $col.contents().filter(function(){ 
							return this.nodeType == 3; 
						}),
						text = (nodes.length>0) ? nodes[0].nodeValue : $col.text();
					
					return text.replace(/"/g, '""'); // escape double quotes
			
				}).get().join(tmpColDelim);
		
			}).get().join(tmpRowDelim)
				.split(tmpRowDelim).join(rowDelim)
				.split(tmpColDelim).join(colDelim);
		}
	}



})( jQuery );
