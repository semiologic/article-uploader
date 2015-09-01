=== Article Uploader ===
Contributors: Denis-de-Bernardy, Mike_Koepke
Donate link: https://www.semiologic.com
Tags: semiologic, articles, upload, html import
Requires at least: 3.1
Tested up to: 4.0
Stable tag: trunk

> *This plugin has been retired.  No further development will occur on it.*

Lets you bypass WordPress' editor when it stops working as expected.


== Description ==

The Article Uploader plugin for WordPress lets you bypass WordPress' editor when it stops working as expected -- which is frequent when you're pasting complicated copy, since WordPress destroys forms and scripts as it reformats html.

Specifically, it allows you to:

- turn off WP's rich text editor and content reformatting features on individual entries.
- upload HTML and text files in place of using the WP editor.

When uploading HTML files, it replaces your entry's content with everything found in between your <body> and </body> tags. It literally does so -- complete with any html error you may have left behind.

When uploading plain text files such as those purchased on private label content sites, paragraphs will be added automatically and the rich text editor will remain turned on.

= Help Me! =

The [Semiologic forum](http://forum.semiologic.com) is the best place to report issues.


== Installation ==

1. Upload the plugin folder to the `/wp-content/plugins/` directory
2. Activate the plugin through the 'Plugins' menu in WordPress


== Change Log ==

= 2.5.2 =

- Add code to turn deactivate the plugin

= 2.5.1 =

- Development has ceased on this plugin.  Updated source and readme accordingly

= 2.5 =

- Fix static function php warning message
- WP 4.0 compat

= 2.4 =

- Code refactoring
- WP 3.9 compat

= 2.3 =

- Article uploading is now working again.  Something broke along the way with WP 3.7 or 3.8.
- Updated the instructions in the meta box including removing the language type setting link which has been removed from WP
- WP 3.8 compat

= 2.2 =

- WP 3.6 compat
- PHP 5.4
- Use size_format in place of wp_convert_bytes_to_hr

= 2.1 =

- WP 3.5 compat
- Visual Editor is not forced off when uploading html file.  Let the users choose now via checkbox

= 2.0.2 =

- WP 3.0.1 compat

= 2.0.1 =

- Avoid using broken WP functions

= 2.0 =

- Complete rewrite
- Localization
- Code enhancements and optimizations
