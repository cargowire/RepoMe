<?php
/*
Plugin Name: Repo Me
Plugin URI: http://cargowire.net/projects/repo-me
Description: A plugin that provides an endpoint for posts querying.  Aimed at usage by caching clients that require checking for updates
Version: 1.0
Author: Craig Rowe
Author URI: http://cargowire.net
*/

include_once("repome.php");

add_action('delete_post', array('repome', 'register_deleted_post'), 10);
add_filter( 'generate_rewrite_rules', array('repome', 'register_rewrite_rules'));
add_action( 'parse_request', array('repome','handle_request'));