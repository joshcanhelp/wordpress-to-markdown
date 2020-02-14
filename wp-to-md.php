<?php
/**
 * A WP-CLI command to convert WordPress content to Markdown files.
 *
 * PHP version 7.0
 *
 * @package    WP-to-MD
 * @author     Josh Cunningham <josh@joshcanhelp.com>
 * @license    https://www.gnu.org/licenses/gpl-3.0.html GPLv3
 * @link       https://github.com/joshcanhelp/wordpress-to-markdown
 */

require 'vendor/autoload.php';

use League\HTMLToMarkdown\HtmlConverter;

WP_CLI::add_command(
	'wptomd',
	function ( $args, $assoc_args ) {

		$is_dry_run = ! empty( $assoc_args['dry-run'] );

		// Output path for created files.
		$output_dir = $args[0] ?? null;
		if ( empty( $output_dir ) ) {
			WP_CLI::error( 'No output directory.' );
		}

		$output_dir = apply_filters( 'wptomd_output_dir', trailingslashit( $output_dir ) );
		if ( ! is_writable( $output_dir ) ) {
			WP_CLI::error( $output_dir . ' cannot be written to.' );
		}

		// One last chance to stop the process ...
		WP_CLI::line( 'Output dir: ' . $output_dir );
		sleep( 3 );

		$converter = new HtmlConverter();

		$get_post_args = [
			'posts_per_page' => - 1,
			'post_type'      => [ 'post', 'page' ],
			'post_status'    => 'any',
			'orderby'        => 'date',
			'order'          => 'ASC',
		];
		$get_post_args = apply_filters( 'wptomd_get_post_args', $get_post_args, $output_dir );

		$count = 0;
		foreach ( get_posts( $get_post_args ) as $post ) {
			$count ++;

			WP_CLI::line( 'Processing: ' . $post->post_title );

			$pid = $post->ID;

			// Using a core filter.
			// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedHooknameFound
			$post_title = apply_filters( 'the_title', $post->post_title );

			// Using a core filter.
			// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedHooknameFound
			$post_excerpt = apply_filters( 'the_excerpt', $post->post_excerpt );

			if ( empty( $post->post_name ) ) {
				$post->post_name = sanitize_title( $post_title );
			}

			$permalink = trailingslashit( get_permalink( $pid ) ) . 'index.html';
			$permalink = str_replace( home_url( '/' ), '', $permalink );

			$meta = [
				'title'        => wptomd_prepare_front_meta( $post_title ),
				'layout'       => $post->post_type,
				'excerpt'      => wptomd_prepare_front_meta( $post_excerpt ),
				'date'         => $post->post_date_gmt,
				'modified'     => $post->post_modified_gmt,
				'permalink'    => 'publish' === $post->post_status ? $permalink : 'false',
				'tags'         => wptomd_prepare_terms(
					array_merge(
						wptomd_get_terms( $pid, 'post_tag' ),
						wptomd_get_terms( $pid, 'category' )
					)
				),
				'featured_img' => get_the_post_thumbnail_url( $pid, 'thumbnail' ),
				'wpid'         => $pid,
			];
			$meta = apply_filters( 'wptomd_front_meta', $meta, $post );

			$output = '---' . PHP_EOL;
			foreach ( $meta as $key => $val ) {
				$output .= $key . ': ' . $val . PHP_EOL;
			}
			$output .= '---' . PHP_EOL . PHP_EOL;
			$output .= '# ' . $post_title . PHP_EOL . PHP_EOL;

			// Using a core filter.
			// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedHooknameFound
			$the_content = apply_filters( 'the_content', $post->post_content );
			$the_content = $converter->convert( $the_content );

			$output .= apply_filters( 'wptomd_the_content', $the_content, $post );

			$filename = apply_filters( 'wptomd_filename', $post->post_name . '.md', $post, $count );

			WP_CLI::line( 'Filename: ' . $filename );

			if ( $is_dry_run ) {
				continue;
			}

			$fh = fopen( $output_dir . $filename, 'w+' );
			fwrite( $fh, $output );
			fclose( $fh );
		}

		WP_CLI::success( 'Processed: ' . (string) $count );
	}
);

WP_CLI::add_command(
	'wptomd-types',
	function() {
		$posts = get_posts(
			[
				'posts_per_page' => - 1,
				'post_type'      => 'any',
				'post_status'    => 'any',
			]
		);

		$types = [];
		foreach ( $posts as $post ) {
			if ( ! in_array( $post->post_type, $types, true ) ) {
				$types[] = $post->post_type;
				WP_CLI::line( $post->post_type );
			}
		}
	}
);

/**
 * Get terms as an array, whether there are terms or not.
 *
 * @param int    $pid Post ID.
 * @param string $tax Taxonomy.
 *
 * @return array|false|WP_Error|WP_Term[]
 */
function wptomd_get_terms( $pid, $tax ) {
	$terms = get_the_terms( $pid, $tax );
	return is_array( $terms ) ? $terms : [];
}

/**
 * Output tags and categories
 *
 * @param array $terms Array of WP_terms.
 *
 * @return string
 */
function wptomd_prepare_terms( $terms ) {
	$term_names = array_map( 'wptomd_map_terms_to_tags', $terms );
	$term_names = array_filter( $term_names );

	return '[' . implode( ', ', $term_names ) . ']';
}

/**
 * Map function to convert WP_Term to a quoted string.
 *
 * @param WP_Term $wp_term WP term to transform.
 *
 * @return null|string
 */
function wptomd_map_terms_to_tags( $wp_term ) {
	if ( 'uncategorized' === $wp_term->name ) {
		return null;
	}

	return '"' . ucwords( $wp_term->name ) . '"';
}

/**
 * Prepare a string for YAML front matter.
 *
 * @param string $string Text to prepare.
 *
 * @return string
 */
function wptomd_prepare_front_meta( $string ) {
	return '"' . htmlspecialchars( wp_strip_all_tags( $string ) ) . '"';
}
