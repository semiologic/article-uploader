<?php


/**
 * article_uploader_admin
 *
 * @package Article Uploader
 **/

class article_uploader_admin {
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
	 * Constructor.
	 *
	 *
	 */

	public function __construct() {
		$this->plugin_url    = plugins_url( '/', __FILE__ );
		$this->plugin_path   = plugin_dir_path( __FILE__ );

		$this->init();

    } # article_uploader_admin()

	/**
	 * init()
	 *
	 * @return void
	 **/

	function init() {
		// more stuff: register actions and filters
		add_filter('get_user_option_rich_editing', array($this, 'disable_tinymce'));
        add_action('save_post', array($this, 'save_entry'), 10);
	}

    /**
	 * disable_tinymce()
	 *
	 * @param string $bool pseudo boolean
	 * @return string $bool
	 **/
	
	function disable_tinymce($bool) {
		global $post_ID;
		
		if ( !isset($post_ID) || $post_ID <= 0 )
			return $bool;
		
		if ( get_post_meta($post_ID, '_kill_formatting', true) === '1' )
			return 'false';
		else
			return $bool;
	} # disable_tinymce()
	
	
	/**
	 * entry_editor()
	 *
	 * @param object $post
	 * @return void
	 **/
	
	static function entry_editor($post) {
		$post_ID = $post->ID;
		
		echo '<p>'
			. __('The article uploader lets you bypass WordPress\' editor when it stops working as expected -- which is frequent when you\'re pasting complicated copy, since WordPress destroys forms and scripts as it reformats html.', 'article-uploader')
			. '</p>' . "\n";
		
		echo '<p>'
			. '<label>'
			. '<input type="checkbox" id="kill_formatting" name="kill_formatting" tabindex="5"'
			. ( $post_ID > 0 && get_post_meta($post_ID, '_kill_formatting', true)
				? ' checked="checked"'
				: ''
				)
			. ' />'
			. '&nbsp;'
			. __('Turn off WordPress\'s rich text editor and content reformatting features on this entry.', 'article-uploader')
			. '</label>'
			. '</p>' . "\n";
		
		echo '<p>'
			. '<input type="file" name="upload_article" tabindex="5" />'
			. ' '
			. '<input type="submit" name="save" class="button" tabindex="5"'
			. ' value="' . __('Upload', 'article-uploader') . '"'
			. ' />'
			. '</p>' . "\n";
		
		echo '<p>'
			. __('A few points to keep in mind when uploading an article:')
			. '</p>' . "\n";
		
		echo '<ul class="ul-square">' . "\n";
		
		echo '<li>'
			. sprintf(__('Maximum file size is %s based on your server\'s configuration.', 'article-uploader'), size_format(apply_filters('import_upload_size_limit', wp_max_upload_size())))
			. '</li>' . "\n";
		
		echo '<li>'
			. __('Everything within the &lt;body&gt; and &lt;/body&gt; tags of your uploaded file will <strong>replace</strong> the entry\'s contents.', 'article-uploader')
			. '</li>' . "\n";
		
		echo '<li>'
			. __('It will <strong><em>really</em></strong> replace the entry\'s contents. No kidding!', 'article-uploader')
			. '</li>' . "\n";
		
		echo '<li>'
			. __('Your html is inserted <em>as is</em>, complete with any html error you may have left behind. If your site looks like a train wreck after you use the article uploader, it is because you\'ve uploaded invalidly nested html -- and you\'re on your own to fix it.', 'article-uploader')
			. '</li>' . "\n";
		
		echo '<li>'
			. __('Expanding on this, and as a tip... Prefer Dreamweaver to FrontPage. If you\'re using the latter, consider checking your original document\'s syntax with an <a href="http://validator.w3.org">html validator</a>. If all else fails, have someone from a site like eLance fix the html code.', 'article-uploader')
			. '</li>' . "\n";
		
		echo '<li>'
			. __('The article uploader also works with plain text files. If you upload such a file, paragraphs will be added automatically and the rich text editor will remain turned on.', 'article-uploader')
			. '</li>' . "\n";
		
/*		echo '<li>'
			. __('Uploading an html file using the above form will force off WordPress\' rich text editor and content reformatting features on this entry. You can restore them later on.', 'article-uploader')
			. '</li>' . "\n";
*/
		echo '</ul>' . "\n";
	} # entry_editor()


    /**
     * save_entry()
     *
     * @param $post_id
     * @internal param int $post_ID
     * @return void
     */
	
	function save_entry($post_id) {
		if ( wp_is_post_revision($post_id) || !current_user_can('unfiltered_html') )
			return;

		$post_id = (int) $post_id;
		$post = get_post($post_id);

		if ( $post->post_status == 'trash' ) {
			return;
		}
		
		if ( !empty($_POST['kill_formatting']) )
			update_post_meta($post_id, '_kill_formatting', '1');
		else
			delete_post_meta($post_id, '_kill_formatting');
		
		if ( empty($_FILES['upload_article']['name']) )
			return;
		
		preg_match("/\.([^.]+)$/", $_FILES['upload_article']['name'], $ext);
		$ext = end($ext);
		
		switch ( strtolower($ext) ) {
		case 'htm':
		case 'html':
			$content = file_get_contents($_FILES['upload_article']['tmp_name']);
			
			if ( preg_match("/
				<\s*body(?:\s.*?)?\s*>
				(.*?)
				<\s*\/\s*body\s*>
				/isx", $content, $body)
				) {
				$content = end($body);
			}
			
			$content = trim($content);

			global $wpdb;
			if ( $content ) {
				$wpdb->query("
					UPDATE	$wpdb->posts
					SET		post_content = '" . $wpdb->_real_escape($content) . "'
					WHERE	ID = " . intval($post_id)
					);
				
//				update_post_meta($post_id, '_kill_formatting', '1');
			}
			
			break;

		case 'txt':
		case 'text':
			$content = file_get_contents($_FILES['upload_article']['tmp_name']);
			$content = trim($content);
			$content = htmlspecialchars($content, ENT_COMPAT, get_option('blog_charset'));

			global $wpdb;
			if ( $content ) {
				$wpdb->query("
					UPDATE	$wpdb->posts
					SET		post_content = '" . $wpdb->_real_escape($content) . "'
					WHERE	ID = " . intval($post_id)
					);
			}
			
			break;
		}
	} # save_entry()
} # article_uploader_admin

$article_uploader_admin = article_uploader_admin::get_instance();
