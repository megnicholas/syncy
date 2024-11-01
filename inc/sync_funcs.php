<?php

//get the path and filename with regex
if (! function_exists(' sync_get_export_file_name')) {
    function sync_get_export_file_name($url, $refresh = false)
    {

        if (str_ends_with($url, '/')) {
            $url .= 'index.html';
        }

        $folder = ABSPATH . "_site";
        $path = '';
        $filename = '';
        if (preg_match('/^(?:https?:\/\/)?(?:www\.)?[^\/]+\/?(.*\/)?([^\/]+)$/', $url, $matches)) {
            $path = rtrim($matches[1], '/');
            $filename = $matches[2];
        }

        if ($refresh) {
            if ($path === '') {
                //delete the file
                unlink($folder . '/' . $path  . '/' . $filename);
            } else {
                //delete the folder and all it's files and subfolders
                sync_recursive_dir_delete($folder . '/' . $path);
            }
        }

        //create the empty folder if it doesn't exists
        if (!is_dir($folder . '/' . $path)) {
            mkdir($folder . '/' . $path, 0777, true);
        }

        return $folder . '/' . $path  . '/' . $filename;
    }
}

if (!function_exists('sync_recursive_dir_delete')) {
    function sync_recursive_dir_delete($dir)
    {
        // Check if the directory exists
        if (!is_dir($dir)) {
            return false; // Return false if the directory doesn't exist
        }

        // Scan the directory for files and folders
        $items = array_diff(scandir($dir), ['.', '..']);

        foreach ($items as $item) {
            $itemPath = "$dir/$item"; // Get the full path of the item

            // If it's a directory, call the function recursively
            if (is_dir($itemPath)) {
                sync_recursive_dir_delete($itemPath);
            } else {
                // If it's a file, delete it
                unlink($itemPath);
            }
        }

        // Remove the directory itself
        return rmdir($dir);
    }
}

if (! function_exists('sync_get_page_links')) {
    function sync_get_page_links($query, $slug)
    {
        $page_links = array();

        $total_posts = $query->found_posts;
        $posts_per_page = get_option('posts_per_page');
        $total_pages = ceil($total_posts / $posts_per_page);

        // Generate links for each page
        $slug = rtrim($slug, '/') . '/'; //add a slash to the end
        $page_links[] =  $slug; //first page
        for ($i = 2; $i <= $total_pages; $i++) {
            $page_links[] =  $slug . 'page/' . $i . '/';
        }
        return $page_links;
    }
}

if (! function_exists(' sync_process_links')) {
    //takes an array of links and downloads the content
    //Usage:
    //sync_process_links($links);
    function sync_process_links($links, $verbose = true, $refresh = false)
    {
        $total_links = count($links);
        $path = ABSPATH;
        $dir = WP_PLUGIN_DIR . '/syncy';

        foreach ($links as $key => $link) {

            $export_file_name = sync_get_export_file_name($link, $refresh);

            //check if the file exists, if it does not then do nothing
            if (!file_exists($export_file_name)) {

                try {
                    $content = shell_exec("wp-cli eval-file {$path}index.php --url={$link} --skip-wordpress --path={$path}");

                    file_put_contents($export_file_name, $content);
                    if ($verbose) {
                        printf("%d/%d downloaded %s %s\n", $key + 1, $total_links, $link, $export_file_name);
                    }
                } catch (Exception $e) {
                    if ($verbose) {
                        printf("%d/%d there was an error downloading %s %s\n", $key + 1, $total_links, $link, $export_file_name);
                        printf("Error message: %s\n", $e->getMessage());
                    }
                    break;
                }
            } else {
                if ($verbose) {
                    //printf("%d/%d skipped download %s %s\n", $key + 1, $total_links, $link, $export_file_name);
                }
            }
        }
    }
}

function sync_get_feeds()
{

    $feed_urls = [];
    
    $feeds = apply_filters('feed_link', [], null);

    // Initialize an array to hold feed URLs

    // Loop through each registered feed and get its URL
    foreach ($feeds as $feed_key => $feed_name) {
        $feed_urls[$feed_key] = get_feed_link($feed_key);
    }

    return $feed_urls;
}
