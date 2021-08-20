<?php
/**
 * Plugin Name: Schedules
 * Description: Quickly and easily view all your scheduled posts.
 * Version:     1.1.0
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

add_action( 'wp_dashboard_setup', __NAMESPACE__ . '\\add_dashboard_widget' );

/**
 * Add new dashboard widget with list of scheduled posts.
 */
function add_dashboard_widget() {
	$name = sprintf(
		'<span><span class="dashicons %s" style="padding-right: 10px"></span>%s</span>',
		apply_filters( 'schedules_widget_icon', 'dashicons-clock' ),
		apply_filters( 'schedules_widget_title', esc_attr__( 'Scheduled', 'schedules' ) )
	);

	wp_add_dashboard_widget( 'schedules', $name, __NAMESPACE__ . '\\dashboard_widget' );
}

/**
 * Add dashboard widget for scheduled posts.
 */
function dashboard_widget() {
	$post_types = apply_filters( 'schedules_post_types_to_show', get_post_types() );
	$query_args = apply_filters( 'schedules_widget_query_args', [
		'post_type'      => $post_types,
		'post_status'    => 'future',
		'orderby'        => 'date',
		'order'          => 'ASC',
		'posts_per_page' => 25,
		'no_found_rows'  => true,
	] );

	$posts     = new WP_Query( $query_args );
	$scheduled = get_scheduled_posts( $posts );

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
 * Get the scheduled posts to display in the dashboard widget.
 *
 * @param WP_Query $posts WP_Query object.
 *
 * @return array Array of scheduled posts.
 */
function get_scheduled_posts( $posts ) {
	$scheduled = [];

	if ( $posts->have_posts() ) {
		while ( $posts->have_posts() ) {
			$posts->the_post();

			$add_to_scheduled = apply_filters( 'schedules_show_in_widget', [
				'ID'      => get_the_ID(),
				'title'   => get_the_title(),
				'date'    => gmdate( 'F j, g:ia', get_the_time( 'U' ) ),
				'preview' => get_preview_post_link(),
			] );

			if ( isset( $add_to_scheduled ) ) {
				$scheduled[] = $add_to_scheduled;
			}
		}
	}

	return $scheduled;
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
