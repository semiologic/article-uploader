<?php
/*
Plugin Name: Article Uploader
Plugin URI: http://www.semiologic.com/software/article-uploader/
Description: Lets you upload files in place of using the WP editor when writing your entries.
Version: 2.3
Author: Denis de Bernardy & Mike Koepke
Author URI: http://www.getsemiologic.com
Text Domain: article-uploader
Domain Path: /lang
License: Dual licensed under the MIT and GPLv2 licenses
*/

/*
Terms of use
------------

This software is copyright Denis de Bernardy & Mike Koepke, and is distributed under the terms of the MIT and GPLv2 licenses.
**/


load_plugin_textdomain('article-uploader', false, dirname(plugin_basename(__FILE__)) . '/lang');


/**
 * article_uploader
 *
 * @package Article Uploader
 **/

class article_uploader {
    /**
     * article_uploader()
     */
	public function __construct() {
        if ( !is_admin() ) {
        	add_action('the_post', array($this, 'the_post'));
        	add_action('loop_end', array($this, 'loop_end'));
        } else {
        	add_action('admin_menu', array($this, 'meta_boxes'), 30);
        }
    } # article_uploader()

    /**
	 * meta_boxes()
	 *
	 * @return void
	 **/
	
	function meta_boxes() {
		if ( current_user_can('unfiltered_html') ) {
			add_meta_box('article_uploader', __('Article Uploader', 'article-uploader'), array('article_uploader_admin', 'entry_editor'), 'post');
			add_meta_box('article_uploader', __('Article Uploader', 'article-uploader'), array('article_uploader_admin', 'entry_editor'), 'page');
		}
	} # meta_boxes()
	
	
	/**
	 * loop_end()
	 *
	 * @return void
	 **/

	function loop_end() {
		article_uploader::restore_filters();
	} # loop_end()


    /**
     * the_post()
     *
     * @param $post
     * @return void
     */
	
	function the_post(&$post) {
		if ( get_post_meta($post->ID, '_kill_formatting', true) === '1' )
			article_uploader::strip_filters();
		else
			article_uploader::restore_filters();
	} # the_post()
	
	
	/**
	 * strip_filters()
	 *
	 * @return void
	 **/

	function strip_filters() {
		global $article_uploader_filter_backup;
		
		if ( !isset($article_uploader_filter_backup) )
			$article_uploader_filter_backup = array('the_content' => array(), 'the_excerpt' => array());
		
		foreach ( array(
			'wptexturize',
			'wpautop',
			'Markdown',
			) as $callback ) {
			$priority = has_filter('the_content', $callback);
			
			if ( $priority !== false ) {
				$article_uploader_filter_backup['the_content'][$priority][] = $callback;
				remove_filter('the_content', $callback, $priority);
			}
			
			$priority = has_filter('the_excerpt', $callback);
			
			if ( $priority !== false ) {
				$article_uploader_filter_backup['the_excerpt'][$priority][] = $callback;
				remove_filter('the_excerpt', $callback, $priority);
			}
		}
	} # strip_filters()
	
	
	/**
	 * restore_filters()
	 *
	 * @return void
	 **/

	static function restore_filters() {
		global $article_uploader_filter_backup;
		
		foreach ( (array) $article_uploader_filter_backup as $filter => $filters )
			foreach ( (array) $filters as $priority => $callbacks )
				foreach ( (array) $callbacks as $callback )
					add_filter($filter, $callback, $priority);
	} # restore_filters()
} # article_uploader

if ( !function_exists('load_multipart_entry') ) :
function load_multipart_entry() {
	include_once dirname(__FILE__) . '/multipart-entry/multipart-entry.php';
}
endif;

function article_uploader_admin() {
	include_once dirname(__FILE__) . '/article-uploader-admin.php';
}

foreach ( array('post.php', 'post-new.php', 'page.php', 'page-new.php') as $hook ) {
	add_action("load-$hook", 'article_uploader_admin');
	add_action("load-$hook", 'load_multipart_entry');
}

$article_uploader = new article_uploader();