<?php

function sync_add_command($args)
{
    do_sync();
    WP_CLI::success('All done.');
}

if (class_exists('WP_CLI')) {
    WP_CLI::add_command('sync dosync', 'sync_add_command');
}
