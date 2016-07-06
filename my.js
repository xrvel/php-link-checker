var _index = 0;

var backlink_count = 0;
var unprocessed = 0;

var request_running = 0;
var max_requests = 5;

var dead_backlinks = 0;
var ok_backlinks = 0;
var no_backlinks = 0;

var my_textarea = ["id_found", "id_not_found", "id_dead"];
var all_done = 0;

var my_status = 'stop';

function check_backlinks_bulk() {
	_interval = 5;

	if ('start' == my_status) {
		if (request_running < max_requests) {
			i = _index;
			_backlink = backlinks[i];
			my_log(i);
			my_log(_backlink);
			my_log('url='+encodeURIComponent( _backlink)+'&index='+i);
			unprocessed--;
			jQuery('#unprocessed').html(unprocessed);

			jQuery('#processed').html(backlink_count - unprocessed);

			request_running++;
			jQuery('#status_'+i).html( '<img alt="Loading" src="./loading.gif" />');

			jQuery.ajax({
				url: 'backlink-checker-ajax.php',
				type: 'POST',
				data: 'url='+encodeURIComponent( _backlink )+'&index='+i,
				dataType: 'json',
				cache: false
			}).done(function( data ) {
				/* console.log(i + ' = done'); */

				_output_selector = '';
				_textarea_selector = '';

				if ('dead' == data.status) {
					dead_backlinks++;
					jQuery('#id_tabs-2-3_label').html('Dead ('+dead_backlinks+')');;
					jQuery('#dead').html(dead_backlinks);
					jQuery('#status_'+data.index).html( data.message ).addClass('no');
					_textarea_selector = '#id_dead';
					_output_selector = '#id_output_dead';
				} else if ('ok' == data.status) {
					ok_backlinks++;
					jQuery('#id_tabs-2-1_label').html('Found ('+ok_backlinks+')');;
					jQuery('#ok').html(ok_backlinks);
					jQuery('#status_'+data.index).html( data.message ).addClass('ok');
					_textarea_selector = '#id_found';
					_output_selector = '#id_output_found';
				} else {
					no_backlinks++;
					jQuery('#id_tabs-2-2_label').html('Not Found ('+no_backlinks+')');;
					jQuery('#no').html(no_backlinks);
					jQuery('#status_'+data.index).html( data.message ).addClass('no');
					_textarea_selector = '#id_not_found';
					_output_selector = '#id_output_not_found';
				}

				jQuery( '.tabs' ).tabs('refresh');

				my_sort( _textarea_selector );
				jQuery( _textarea_selector ).val(jQuery.trim(jQuery( _textarea_selector ).val() + "\n" + data.url));

				if ('' == jQuery( _textarea_selector ).val()) {
					_str = '';
				} else {
					_str = '<ol>';
					jQuery( _textarea_selector ).val().split("\n").forEach(function(item, index) {
						_link = jQuery.trim(item);
						_str += '<li><a href="'+ _link +'" target="_blank">'+ _link +'</a></li>';
					});
					_str += '</ol>';
				}

				jQuery( _output_selector ).html( _str );

				request_running--;
			});

			if (i < max_backlinks - 1) {
				_index++;
			} else {
				all_done = 1;
			}
		} else {
			_interval = 100;
		}
	}

	if (0 == all_done) {
		jQuery('#threads').html(request_running);
		setTimeout('check_backlinks_bulk()', _interval);
	} else {
		jQuery('#threads').html(0);
	}
}

function my_go( _status ) {
	if ('start' == _status) {
		jQuery('#id_start').hide();
		jQuery('#id_pause').show();
		my_log('Status set is start.');
		if ('stop' == my_status) {
			my_log('Starting ...');
			check_backlinks_bulk();
			my_scroll();
		} else {
			my_log('Already started');
		}
	} else {
		jQuery('#id_pause').hide();
		jQuery('#id_start').show();
		my_status = 'stop';
	}
	my_status = _status;
	my_log('Set status = '+_status);
}

function my_log(_log) {
	console.log(_log);
}

/* Auto scroll the textarea */
function my_scroll() {

	if (jQuery('#id_auto_scroll').is(':checked')) {
		my_textarea.forEach(function(item, index) {
			if (jQuery("#"+item).val() != '') {
				document.getElementById(item).scrollTop = document.getElementById(item).scrollHeight;
				/* jQuery("#"+item).scrollTop(jQuery("#"+item)[0].scrollHeight); */
			}
		});
	}

	if (0 == all_done) {
		setTimeout('my_scroll()', 100);
	}
}

/* Sort textarea content */
function my_sort(el) {
	my_list = jQuery(el).val().split("\n");
	my_list.sort();
	jQuery(el).val(jQuery.trim(my_list.join("\n")));
}

jQuery(document).ready(function() {
	jQuery( '.tabs' ).tabs();
	backlink_count = jQuery('#backlink_count').val();
	unprocessed = backlink_count;
	if (jQuery('#autostart').val() == '1') {
		jQuery('#id_start').hide();
		check_backlinks_bulk();
		my_scroll();
	} else {
		jQuery('#id_pause').hide();
	}
});