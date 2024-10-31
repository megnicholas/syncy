<?php

add_action('admin_bar_menu', function ($wp_admin_bar) {
	if (is_admin()) {
		$current_url = get_the_permalink();
	} else {
		$current_url = "http://" . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
	}

	if ($current_url) {
		$args = array(
			'id'    => 'sync-this-page',
			'title' => 'Sync',
			'href'  => add_query_arg('sync', '1', $current_url),
			'meta'  => array(
				'class' => 'custom-button-class'
			)
		);
		$wp_admin_bar->add_node($args);
	}
}, 1000);

add_action('template_redirect', function () {

	//if we find sync=1
	if (strpos($_SERVER['REQUEST_URI'], 'sync=1') !== false) {
		$current_url = "http://" . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
		$current_url = remove_query_arg('sync', $current_url);

		//get the export file name and delete the file
		$export_file_name = sync_get_export_file_name($current_url, true);
		wp_redirect($current_url);
		exit();
	}
});

// Hook into the save_post action
add_action('save_post', function ($post_id) {
	// Check if this is an autosave, if so, do nothing
	if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
		return;
	}
	$url = get_the_permalink($post_id);
	//if this isn't a pretty url then bail
	if (strpos($url, '?p=') !== false) {
		return;
	}
	$export_file_name = sync_get_export_file_name($url, true);
});

add_action('transition_post_status', function ($new_status, $old_status, $post) {

	if ('publish' === $new_status && 'publish' !== $old_status) {
		if (in_array($post->post_type, ['post', 'page']) & ! get_post_meta($post->ID, 'reshare', true)) {

			//sync the category archives for each category that the post is in
			$categories = get_the_category($post->ID);
			foreach ($categories as $category) {
				sync_get_export_file_name(get_category_link($category->term_id), true);
			}
			//sync the archive
			sync_get_export_file_name(get_post_type_archive_link($post->post_type), true);

			//sync the home page
			sync_get_export_file_name(home_url('/'), true);
		}
	}
}, 10, 3);
