<?php
/*
Plugin Name: Article Uploader
Plugin URI: https://www.semiologic.com/software/article-uploader/
Description: RETIRED - Lets you upload files in place of using the WP editor when writing your entries.
Version: 2.5.2
Author: Denis de Bernardy & Mike Koepke
Author URI: https://www.semiologic.com
Text Domain: article-uploader
Domain Path: /lang
License: Dual licensed under the MIT and GPLv2 licenses
*/

/*
Terms of use
------------

This software is copyright Denis de Bernardy & Mike Koepke, and is distributed under the terms of the MIT and GPLv2 licenses.
**/

/*
 * This plugin has been retired.  No further development will occur on it.
 * */

// Disable the plugin

$active_plugins = get_option('active_plugins');

if ( !is_array($active_plugins) )
{
	$active_plugins = array();
}

foreach ( (array) $active_plugins as $key => $plugin )
{
	if ( $plugin == 'article-uploader/article-uploader.php' )
	{
		unset($active_plugins[$key]);
		break;
	}
}

sort($active_plugins);

update_option('active_plugins', $active_plugins);

/**
 * article_uploader
 *
 * @package Article Uploader
 **/

class article_uploader {
	/**
	 * Plugin instance.
	 *
	 * @see get_instance()
	 * @type object
	 */
	protected static $instance = NULL;

	/**
	 * URL to this plugin's directory.
	 *
	 * @type string
	 */
	public $plugin_url = '';

	/**
	 * Path to this plugin's directory.
	 *
	 * @type string
	 */
	public $plugin_path = '';

	/**
	 * Access this plugin’s working instance
	 *
	 * @wp-hook plugins_loaded
	 * @return  object of this class
	 */
	public static function get_instance()
	{
		NULL === self::$instance and self::$instance = new self;

		return self::$instance;
	}


	/**
	 * Loads translation file.
	 *
	 * Accessible to other classes to load different language files (admin and
	 * front-end for example).
	 *
	 * @wp-hook init
	 * @param   string $domain
	 * @return  void
	 */
	public function load_language( $domain )
	{
		load_plugin_textdomain(
			$domain,
			FALSE,
			dirname(plugin_basename(__FILE__)) . '/lang'
		);
	}

	/**
	 * Constructor.
	 *
	 *
	 */
	public function __construct() {
		$this->plugin_url    = plugins_url( '/', __FILE__ );
		$this->plugin_path   = plugin_dir_path( __FILE__ );
		$this->load_language( 'article-uploader' );

		add_action( 'plugins_loaded', array ( $this, 'init' ) );
    } # article_uploader()

	/**
	 * init()
	 *
	 * @return void
	 **/

	function init() {
		// more stuff: register actions and filters
		if ( !is_admin() ) {
			add_action('the_post', array($this, 'the_post'));
			add_action('loop_end', array($this, 'loop_end'));
		} else {
			add_action('admin_menu', array($this, 'meta_boxes'), 30);

			foreach ( array('post.php', 'post-new.php', 'page.php', 'page-new.php') as $hook ) {
				add_action("load-$hook", array($this, 'article_uploader_admin'));
				add_action("load-$hook", 'load_multipart_entry');
			}
		}
	}

	/**
	* article_uploader_admin()
	*
	* @return void
	**/
	function article_uploader_admin() {
		include_once $this->plugin_path . '/article-uploader-admin.php';
	}

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

$article_uploader = article_uploader::get_instance();

if ( !function_exists('load_multipart_entry') ) :
function load_multipart_entry() {
	include_once dirname(__FILE__) . '/multipart-entry/multipart-entry.php';
}
endif;