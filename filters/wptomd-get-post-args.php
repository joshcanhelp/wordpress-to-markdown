<?php
add_filter(
	'wptomd_get_post_args',
	function ( array $get_post_args, $output_dir ) {

		// Only process posts.
		$get_post_args[ 'post_type' ] = [ 'post' ];

		// Exclude certain posts/pages by ID.
		$get_post_args[ 'exclude' ] = [];

		// Make directories for all the post types used.
		foreach ( $get_post_args[ 'post_type' ] as $post_type ) {
			if ( ! is_dir( $output_dir . '/' . $post_type ) ) {
				mkdir( $output_dir . '/' . $post_type );
			}
		}

		return $get_post_args;
	}, 10, 2
);