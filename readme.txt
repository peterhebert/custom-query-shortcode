=== Query Shortcode ===
Contributors: shazdeh
Plugin Name: Query Shortcode
Tags: query, shortcode, post
Requires at least: 3.0
Tested up to: 3.5
Stable tag: 0.1

A simple shortcode that enables you to query anything you want and display it however you like.

== Description ==

This plugin gives you <code>[query]</code> shortcode which enables you to output posts filtered by specific attributes. See the Usage section for more info.

== Installation ==

1. Upload the whole plugin directory to the `/wp-content/plugins/` directory
2. Activate the plugin through the 'Plugins' menu in WordPress
3. Now use <code>[query]</code> shortcode anywhere you want.
4. Enjoy!

== Usage ==

You can use all parameters supported by <a href="http://codex.wordpress.org/Class_Reference/WP_Query">WP_Query class</a> to filter the posts. You must also define the template for the output, like so:
<code>[query posts_per_page="5" cat="3"] <h3>{TITLE}</h3> [/query]</code>
The above shortcode will display the title of latest 5 posts from the category of 3. Available keywords are: TITLE, CONTENT, AUTHOR, AUTHOR_URL, DATE, THUMBNAIL, CONTENT, COMMENT_COUNT and more to be added later.

* cols option

With the "cols" parameter you can display the output in a grid. So this:
<code>[query posts_per_page="3" cols="3"] {THUMBNAIL} <h3>{TITLE}</h3> {CONTENT} [/query]</code>
will display the latest 3 posts in the defined template, in 3 columns. If in the above snippet we set the posts_per_page option to 6, it will display the latest 6 posts in two rows that each has 3 columns.

* Other options

Aside from wp_query parameters, the shortcode also supports additional parameters:
 - thumbnail_size: to specify the size of the thumbnails
 - content_limit : to limit the number of words of the content