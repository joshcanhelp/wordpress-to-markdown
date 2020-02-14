<?php
add_filter(
	'wptomd_filename',
	function ( $filename, $wp_post, $count ) {

		$post_type = $wp_post->post_type;

		if ( 'post' === $post_type ) {
			$filename = $count . '-' . $filename;
		}

		return $post_type . '/' . $filename;

	}, 10, 3
);