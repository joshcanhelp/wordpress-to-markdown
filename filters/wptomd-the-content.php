<?php
add_filter(
	'wptomd_the_content',
	function ( $the_content, $post ) {
		$the_content = str_replace( home_url( '/wp-content/' ), '/_images/', $the_content );
		$the_content = str_replace( home_url( '/' ), '/', $the_content );
		$the_content = strip_tags( $the_content, '<a><span><object><param><embed><del>' );

		if ( 'quote' == get_post_format( $post->ID ) ) {
			$the_content = '> ' . $the_content;
		}

		return $the_content;
	}, 10, 2
);