<?php
defined( 'ABSPATH' ) || exit();

/**
 * This snippet is providing support for css async loading.
 * This snippet is injected at the bottom of footer.
 */
function cjm_css_async() {
   ?>
   <script>
   jQuery(document).ready( function() {
   	var css = "head noscript.cjm_css_async";
   	if( jQuery( css ).length > 0 ) {
   		jQuery( css ).each( function( i ) {
   			var html = jQuery( this ).html();
   			if( html.length > 0 ) {
   				jQuery( this ).before( html.replace('&lt;', '<').replace('&gt;', '>') );
   			}
   		});
   	}
   });
   </script>
   <?php
}
add_action( 'wp_footer', 'cjm_css_async' );
