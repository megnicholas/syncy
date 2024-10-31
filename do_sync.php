<?php
//https://koala.sh/chat?chatId=5998d010-204c-44e1-9f1c-ffa36a4c7b18


// Load WordPress
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

$links = array();
$always_sync_links = array();



//----------------------------------------------------------------------------------------------
// Posts and pages
//----------------------------------------------------------------------------------------------
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
//----------------------------------------------------------------------------------------------
// Post indexes and their pages
//----------------------------------------------------------------------------------------------
$post_types = array('testimonial','project','post');
foreach ($post_types as $post_type) {
    $posts = new WP_Query(array(
        'post_type' => $post_type,
        'posts_per_page' => -1,
    ));
    $links = array_merge($links, sync_get_page_links($posts, get_post_type_archive_link($post_type)));
}

//----------------------------------------------------------------------------------------------
// Categories
//----------------------------------------------------------------------------------------------
$categories = get_categories();
foreach ($categories as $category) {
    $robots = get_term_meta($category->term_id, 'rank_math_robots', true);

    if (!($robots && 'noindex' === $robots[0])) {

        //get the url of each page in the category and visit the link
        $query = new WP_Query(array(
            'category__in' => array($category->term_id), // Get posts in this category
            'posts_per_page' => -1, // Get all posts to calculate total
        ));

        $links = array_merge($links, sync_get_page_links($query, get_category_link($category->term_id)));
    }
}

//robots
$links[] = home_url('robots.txt');


//404
$always_sync_links[] = home_url('404.html');

//sitemap
$always_sync_links[] = home_url('sitemap_index.xml');
$always_sync_links[] = home_url('post-sitemap.xml');
$always_sync_links[] = home_url('project-sitemap.xml');
$always_sync_links[] = home_url('page-sitemap.xml');
$links[] = home_url('main-sitemap.xsl');

//feed
$always_sync_links[] = home_url('feed/');

// Download each link in the array
$start_time = new DateTime();

sync_process_links($links, true, false);
sync_process_links($always_sync_links, true, true);

$end_time = new DateTime();
$interval = $start_time->diff($end_time);
$minutes = $interval->i;
$seconds = $interval->s;

// Display the time passed
echo "Completed in: " . $minutes . " minutes and " . $seconds . " seconds.\n";