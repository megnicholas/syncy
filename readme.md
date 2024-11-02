Requires wp-cli to be available and on the path.

Add this package as a submodule:

`git submodule add -f https://github.com/megnicholas/syncy.git wordpress/wp-content/plugins/syncy`

Run it  as a script by adding to package.json scripts like this:
    
`"sync:html": "wp sync dosync --path=wordpress"`

Add additional urls using filter e.g.:

`add_filter('sync_additional_files', function ($links) {
	$links[] = home_url('sitemap_index.xml');
	$links[] = home_url('post-sitemap.xml');
	$links[] = home_url('project-sitemap.xml');
	$links[] = home_url('page-sitemap.xml');
	$links[] = home_url('main-sitemap.xsl');
	$links[] = home_url('feed/');
	return $links;
});`

Do something after the sync completes if you like:

`add_action('sync_after_process', function($site_folder){},10,1);`

