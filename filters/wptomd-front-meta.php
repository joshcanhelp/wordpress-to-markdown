<?php
add_filter(
	'wptomd_front_meta',
	function ( $meta, $post ) {

		$pid = $post->ID;

		// Format the featured URL to be relative.
		$meta[ 'featured_img' ] = str_replace(
			home_url( '/wp-content/uploads/' ),
			'/_images/',
			( $meta[ 'featured_img' ] ?? '' )
		);

		// Set the layout to the post format, if there is one, falling back to post type.
		$post_format = get_post_format( $post->ID );
		if ( ! empty( $post_format ) ) {
			$meta[ 'layout' ] = $post_format;
		}

		// Pull the title from the All-In-One SEO plugin meta.
		$wpseo_title = get_post_meta( $pid, '_aioseop_title', TRUE );
		if ( ! empty( $wpseo_title ) ) {
			$meta[ 'title' ] = '"' . htmlspecialchars( $wpseo_title ) . '"';
		}

		// Pull the excerpt from the All-In-One SEO plugin meta.
		$wpseo_desc = get_post_meta( $pid, '_aioseop_description', TRUE );
		if ( ! empty( $wpseo_desc ) ) {
			$meta[ 'excerpt' ] = '"' . htmlspecialchars( $wpseo_desc ) . '"';
		}

		// Custom theme meta field.
		$link_to = get_post_meta( $pid, '_wpwb_link_url', TRUE );
		if ( ! empty( $link_to ) ) {
			$meta[ 'link_to' ] = '"' . esc_url_raw( $link_to ) . '"';
		}

		// Custom theme meta field.
		$citation = get_post_meta( $pid, '_wpwb_quote_attr', TRUE );
		if ( ! empty( $link_to ) ) {
			$meta[ 'citation' ] = '"' . htmlspecialchars( $citation ) . '"';
		}

		// Home page.
		if ( 'home-page' === $post->post_name ) {
			$meta[ 'permalink' ] = '/index.html';
		}

		return $meta;
	}, 10, 2
);