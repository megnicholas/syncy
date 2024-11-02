<?php

function sample_command($args)
{
    do_sync();

    WP_CLI::success('All done.');
}
WP_CLI::add_command('sync dosync', 'sample_command');
