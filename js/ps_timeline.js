jQuery(document).ready(function() {

	function menu_main(elm) {
		var id = jQuery(elm).parent().parent().attr('id');
		var cls = jQuery(elm).parent().attr('class');

		if (id == 'ps_slow') {
			search_slow();	
		}

		if (cls == 'ps_hide') {
			jQuery('.' + id).hide();
		} else if (cls == 'ps_show') {
			jQuery('.' + id).show();
		} else {
			if (id == 'ps_all') {
				jQuery("#ps_panel tr").show();
			} else {
				jQuery("#ps_panel tr").hide();
				jQuery('#ps_tr_head').show();
				jQuery('.' + id).show();
			}
		}
	}

	function search_slow() {
		var msec = parseFloat ( jQuery('#ps_slow_search').val() );
		if ( ! jQuery.isNumeric(msec)) {
			msec = 10;
		}
		jQuery('#ps_panel tr').removeClass('ps_slow');
		jQuery('#ps_panel td.ps_time').each(function(){
			if ( jQuery(this).html() >= msec ) {
				jQuery(this).parent().addClass('ps_slow');
			}
		});
	}

	jQuery("#wp-admin-bar-prime-timeline > a").click(function(e) {
		e.preventDefault();
		jQuery("#ps_wrap").toggle();
	});

	var target = [ 'ps_all', 'ps_file', 'ps_sql', 'ps_hook', 'ps_hook_do', 'ps_hook_pass', 'ps_slow' ];

	for (var i=0;	i<target.length; i++) {
		jQuery('#' + target[i] + ' > li > a').click(function(e) {
			e.preventDefault();
			menu_main(this);
		});
	}

});

