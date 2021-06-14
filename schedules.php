<?php
/**
 * Plugin Name: Schedules
 * Description: Quickly and easily view all your scheduled posts.
 * Version:     1.0.0
 * Author:      Brad Parbs
 * Author URI:  https://bradparbs.com/
 * License:     GPLv2
 * Text Domain: schedules
 * Domain Path: /lang/
 *
 * @package schedules
 */

namespace Schedules;

use WP_Query;

defined( 'ABSPATH' ) || die();

// Add new dashboard widget with list of scheduled posts.
add_action(
	'wp_dashboard_setup',
	function () {
		wp_add_dashboard_widget( 'scheduled', '<span><span class="dashicons dashicons-clock" style="padding-right: 10px"></span>' . esc_attr__( 'Scheduled' ) . '</span>', __NAMESPACE__ . '\\dashboard_widget' );
	}
);

/**
 * Add dashboard widget for scheduled posts.
 */
function dashboard_widget() {
	$posts = new \WP_Query(
		[
			'post_type'      => get_post_types(),
			'post_status'    => 'future',
			'orderby'        => 'date',
			'order'          => 'ASC',
			'posts_per_page' => 25,
			'no_found_rows'  => true,
		]
	);

	$scheduled = [];

	if ( $posts->have_posts() ) {
		while ( $posts->have_posts() ) {
			$posts->the_post();

			$scheduled[] = [
				'ID'      => get_the_ID(),
				'title'   => get_the_title(),
				'date'    => gmdate( 'F j, g:ia', get_the_time( 'U' ) ),
				'preview' => get_preview_post_link(),
			];
		}
	}

	printf(
		'<div id="scheduled-posts-widget-wrapper">
			<div id="scheduled-posts-widget" class="activity-block" style="padding-top: 0;">
				<ul>%s</ul>
			</div>
		</div>',
		display_scheduled_in_widget( $scheduled ) // phpcs:ignore
	);
}
/**
 * Display scheduled posts in widget.
 *
 * @param array $posts Post data.
 *
 * @return string Output of post data.
 */
function display_scheduled_in_widget( $posts ) {
	$output = '';

	foreach ( $posts as $post ) {
		$output .= sprintf(
			'<li><em style="%4$s">%1$s</em> <a href="%2$s">%3$s</a></li>',
			isset( $post['date'] ) ? $post['date'] : '',
			isset( $post['preview'] ) ? $post['preview'] : '',
			isset( $post['title'] ) ? $post['title'] : '',
			'display: inline-block; margin-right: 5px; min-width: 125px; color: #646970;'
		);
	}

	return $output;
}
