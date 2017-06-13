$ = jQuery;

function CssJsMinify(mode, box_html ) {
	var self = this;
	this.mode 		= mode;						// mode = css|js
	this.wrap		= "#tab-" + mode;			// tab wrapper ID
	this.aloader	= $( self.wrap ).find( "#file_header_" + this.mode + " .cjm_ajax_loader" );	// ajax animation loader
	this.box_html	= box_html;					// html of box (template) generated thru php
	this.data_send	= [];						// ajax data
	this.boxes_up 	= function() {
		return $( this.wrap ).find( ".cjm_sortable:not(.main)" ).length || 0;
	};
	this.init = function() {
		// object
		self.init_sortable();
		self.add_box();
		self.controls();

		// jQuery
		self.init_tooltips();

		$( self.wrap ).on( 'click', '.cjm_item .sRight.sOpen', function() {
			var parent = $(this).parent();
			parent.find( ".sSettings" ).slideToggle('fast');
			parent.toggleClass( "settings-open" );
		});

		$( self.wrap ).on( 'change', '.cjm_main_toggle', function() {
			var $this 	= $(this);

			if( $this.parent().hasClass( 'loading' ) )
				return false;

			// set vars for ajax request
			var nonce 	= $this.data('nonce');
			var task		= "main_toggle";
			var data		= {};
			data.state	= $this.is(':checked') ? 'on' : 'off';
			data.mode	= mode;

			// ajax loader icon on
			self.aloader.fadeIn( 'fast' );
			$this.parent().addClass( 'loading' );
			$this.prop( "disabled", true );

			console.log( data );

			// ajax request
			self.send_data( nonce, task, data, function( response ) {
				// process response
				if( !response || response == 'error' ) {
					if( $this.is(':checked') )
						$this.prop( "checked", false );
					else if( !$this.is(':checked') )
						$this.prop( "checked", true );
					alert( 'Error.' );
				}
				else if( response == 'saved' ) {

				}
				// ajax loader icon off
				self.aloader.fadeOut( 'fast' );
				$this.parent().removeClass( 'loading' );
				$this.prop( "disabled", false );
				console.log( response );
			});
		});

	};
	this.init_tooltips = function() {
		$( this.wrap ).find( '[data-toggle="tooltip"]' ).tooltip({
			position: {
				my: "center bottom-10",
				at: "center top",
				using: function( position, feedback ) {
					$( this ).css( position );
					$( "<div>" )
						.addClass( "arrow" )
						.addClass( feedback.vertical )
						.addClass( feedback.horizontal )
						.appendTo( this );
				}
			}
		});
	};
	this.init_sortable = function(  ) {
		$( self.wrap ).find( "ul.cjm_sortable" ).sortable({
			connectWith: "ul.cjm_sortable",
			items: "li.cjm_item",
			receive: function( event, ui ) {
				// hide welcome message
				ui.item.siblings( '.cjm_box_message_wrap' ).hide();
			},
			change: function( event, ui ) {
				// show message if box is empty

				if( ui.item.siblings( 'li' ).length == 0 ) {
					ui.item.siblings( '.cjm_box_message_wrap' ).show();
				}

			}
		});
	};
	this.add_box = function(  ) {
		$( self.wrap ).on( 'click', ".cjm-sortable-add-block", function() {
			// add box html
			$( self.box_html ).insertBefore( $(this).parent( ".cjm-sortable-add-block-wrapper" ) );
			// reinit sortable and tooltips
			self.init_sortable();
			self.init_tooltips();

		});
	};
	this.controls = function() {
		$( this.wrap ).on( 'click', ".cjm_sortable_box .cjm_sortable_header button", function() {
			var $for = $(this).data('for') || '';
			var box 	= $(this).closest(".cjm_sortable_box");

			// proccess button functionality by its type
			if( $for == 'move-left' ) {
				// move block left
				box.insertBefore( box.prev( ".cjm_sortable_box:not(.main)" ) );
			}
			else if( $for == 'move-right' ) {
				// move block right
				box.insertAfter( box.next( ".cjm_sortable_box:not(.main)" ) );
			}
			else if( $for == 'settings' ) {
				// show block settings
				box.find( ".cjm_block_settings" ).fadeIn();
			}
			else if( $for == 'delete' ) {
				// remove block
				if( confirm( cjm.msg_confirm_block_delete ) ) {
					// pick all boxes and move them to init block for reusability
					if( box.find( ".cjm_sortable li" ).length > 0 ) {
						$( self.wrap ).find( ".cjm_sortable.main" ).append( box.find( ".cjm_sortable" ).html() || "" );
						$( self.wrap ).find( ".cjm_sortable.main .cjm_box_message_wrap" ).hide();
						// refresh items
						$( self.wrap ).find( "ul." + mode + "_sortable" ).sortable( "refresh" );
					}
					// remove the block
					box.remove();
				}
			}
			else if( $for == 'close-settings' ) {
				// hide block settings
				box.find( ".cjm_block_settings" ).fadeOut();
			}
		});

		$( this.wrap ).on( 'click', '.cjm_file_header button', function() {
			var $for 	= $(this).data('for') || '';

            // proccess button functionality by its type
			if( $for == 'generate' ) {
				// získej data z boxů

				self.get_data();
                // set vars for ajax request
				var data 	= {};
				var nonce	= $(this).data('nonce') || '';
				var task		= 'generate_minified_files';
				data.files 	= self.data_send;
				data.mode 	= mode;

				// dialog
				if( confirm( cjm.msg_confirm_save ) ) {
					// ajax loader icon on
					self.aloader.fadeIn( 'fast' );

					// send ajax request
					self.send_data( nonce, task, data, function( response ) {
						if( !response || response == 'error' )
							alert( cjm.msg_error );
						else if( response == 'empty-data' )
							alert( cjm.msg_error_empty );
						else if( response == 'data-erased' )
							alert( cjm.msg_success_erased );
						else if( response == 'saved' )
							alert( cjm.msg_success_saved );

						// ajax loader icon off
						self.aloader.fadeOut( 'fast' );
					});
				}

			}
			else if( $for == 'flush' ) {
				// remove all blocks
				if( confirm( cjm.msg_confirm_all_blocks_delete ) ) {
					$( self.wrap ).find( ".cjm_sortable_box:not(.main)" ).each( function( i, v ) {
                        // pick all boxes and move them to init block for reusability
						if( $(v).find( ".cjm_sortable li" ).length > 0 ) {
							$( self.wrap ).find( ".cjm_sortable.main" ).append( $(v).find( ".cjm_sortable" ).html() || "" );
							// refresh items
							$( self.wrap ).find( "ul." + mode + "_sortable" ).sortable( "refresh" );
						}
						$(v).remove();
					});
				}
			}
			else if( $for == 'guide' ) {
				$("#cjm_help").dialog({
					width: 500,
					dialogClass: 'cjm_dialog'
				});
			}
		});
	};
	this.get_data = function() {
		// delete previous data
		self.data_send = [];

		// process all blocks
		$( this.wrap ).find( '.cjm_sortable.created' ).each( function( i, e ) {
			// init vars
			var data 	= $( e ).sortable( "toArray" );	// vem data (ids) všech elementů v boxu
			var chunk 	= {};

			if( data.length > 0 ) {
				// set data
				chunk.type	= mode;	// set type - css|js
				chunk.files	= [];

				var settings_block = $(this).siblings( ".cjm_block_settings" );

				if( mode == 'css' ) {
					chunk.media 		= settings_block.find( "select.cjm_css_media" ).val();
					chunk.async 		= ( settings_block.find( "input.cjm_css_async" ).is(':checked') || false ) ? 'async' : false;
					chunk.priority 	= settings_block.find( "input.cjm_css_priority" ).val() || false;
				}
				else if( mode == 'js' ) {
					chunk.in_footer 	= settings_block.find( "input.cjm_js_in_footer" ).is(':checked') || false;
					chunk.async 		= settings_block.find( "select.cjm_js_async" ).val() || false;
					chunk.priority 	= settings_block.find( "input.cjm_js_priority" ).val() || false;
				}

				// process all ids (id = $handle) and save them
				$( data ).each( function( i, v ) {
					chunk.files.push( v );
				});

				// if data was found and successfully added (and type css|js was set too) - add data to main object for ajax request
				if( chunk.files.length > 0 && chunk.type )
					self.data_send.push( chunk );
			}

		});
	};
	this.send_data = function( nonce, task, data_send, callback ) {
		if( !nonce || !task )
			return false;

		// set vars
		var data 		= {};
		data.data 		= {};

		// set data
		data.action 	= 'cjm_ajax_admin';
		data.nonce 		= nonce;
		data.task 		= task;
		data.data 		= data_send;

		console.log( data.data );

		// send ajax request
		$.post( cjm.ajax_url, data, function( response ) {
			console.log( response );
			// callback for response handling
			callback( response );
		});
	}
}

var CjmHelp = (function() {
	function CjmHelp() {
		this.el = $("#cjm_help");
		this.nav = this.el.find(".cjm-help-nav");
		this.navFooter = this.el.find(".cjm-help-nav-footer");
		this.activeTab = this.el.find(".cjm-help-tab.active");
		this.tabNum = this.el.find(".cjm-help-tab").length;
		this.init();
	}
    CjmHelp.prototype.init = function() {
		this.menu();
	};
    CjmHelp.prototype.menu = function() {
    	var self = this;
        self.nav.find("li").on('click', function( e ) {
			e.preventDefault();
			if( $(this).hasClass('active') )
				return false;

			var tab = $(this).data("tab");
			self.el.find( "#" + tab ).siblings(".cjm-help-tab").hide();
			self.el.find( "#" + tab ).show().addClass("active");
			$(this).siblings().removeClass('active');
			$(this).addClass('active');
		});
	};
    return CjmHelp;
})();

$(document).ready( function() {
    var CjmGuide = new CjmHelp();
//CjmGuide.init();
});

