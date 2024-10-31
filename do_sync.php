<?php
//https://koala.sh/chat?chatId=5998d010-204c-44e1-9f1c-ffa36a4c7b18

// Load WordPress
if (! defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}

$links = array();
$always_sync_links = array();

//----------------------------------------------------------------------------------------------
// Posts and pages
//----------------------------------------------------------------------------------------------

do_action('sync_before_sync_posts');

$posts = new WP_Query(array(
    'post_type' => 'any',
    'posts_per_page' => -1,
));

if ($posts->have_posts()) {
    while ($posts->have_posts()) {
        $posts->the_post();
        $links[] = get_permalink();
    }
}
wp_reset_postdata();

do_action('sync_after_sync_posts');
//----------------------------------------------------------------------------------------------
// Post indexes and their pages
//----------------------------------------------------------------------------------------------
$post_types = get_post_types();
foreach ($post_types as $post_type) {
    $posts = new WP_Query(array(
        'post_type' => $post_type,
        'posts_per_page' => -1,
    ));
    $links = array_merge($links, sync_get_page_links($posts, get_post_type_archive_link($post_type)));
}

//----------------------------------------------------------------------------------------------
// Terms
//----------------------------------------------------------------------------------------------
$taxonomies = get_taxonomies(array(
    'public' => true,
));
foreach ($taxonomies as $taxonomy) {
    $terms = get_terms(array(
        'taxonomy'   => $taxonomy,
        'hide_empty' => true,
    ));

    foreach ($terms as $term) {
        $query = new WP_Query(array(
            'post_type' => 'post',
            'posts_per_page' => -1,
            'tax_query' => array(
                array(
                    'taxonomy' => $taxonomy,
                    'field' => 'slug',
                    'terms' => $term->slug,
                ),
            ),
        ));

        $links = array_merge($links, sync_get_page_links($query, get_term_link($term->term_id)));
    }
}

//robots
$links[] = home_url('robots.txt');

//404
$always_sync_links[] = home_url('404.html');

//allow addition of additional
$always_sync_links = apply_filters('sync_additional_files', $always_sync_links);

// Download each link in the array
$start_time = new DateTime();

sync_process_links($links, true, false);
sync_process_links($always_sync_links, true, true);

do_action('sync_after_process', $folder = ABSPATH . "_site");

$end_time = new DateTime();
$interval = $start_time->diff($end_time);
$minutes = $interval->i;
$seconds = $interval->s;

// Display the time passed
echo "Completed in: " . $minutes . " minutes and " . $seconds . " seconds.\n";
