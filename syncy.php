<?php
/*
	Plugin Name: Syncy
	Plugin URI: https://megnicholas.com
	Description: Sync Website To Static HML
	Version: 1.0
	Author: Meghan Nicholas
	Author URI: https://megnicholas.com
	License: GPLv2 or later
	*/

/* 
add this to package.json scripts like this:
    
"sync:html": "wp eval-file wordpress/wp-content/plugins/syncy/do_sync.php --path=wordpress",

filter to add additional urls:

add_filter('sync_additional_files', function ($links) {
	$links[] = home_url('sitemap_index.xml');
	$links[] = home_url('post-sitemap.xml');
	$links[] = home_url('project-sitemap.xml');
	$links[] = home_url('page-sitemap.xml');
	$links[] = home_url('main-sitemap.xsl');
	$links[] = home_url('feed/');
	return $links;
});
Anything else you want to do, do it here:
add_action('sync_after_process', function($site_folder){},10,1);

add as a submodule:
git submodule add https://github.com/megnicholas/syncy.git wordpress/wp-content/plugins/syncy
 */



require 'inc/website_funcs.php';
require 'inc/sync_funcs.php';
