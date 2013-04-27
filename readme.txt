=== Query Shortcode ===
Contributors: shazdeh
Plugin Name: Query Shortcode
Tags: query, shortcode, post
Requires at least: 3.3
Tested up to: 3.6
Stable tag: 0.2.1

An insanely powerful shortcode that enables you to query anything you want and display it however you like.

== Description ==

This plugin gives you <code>[query]</code> shortcode which enables you to output posts filtered by specific attributes. You can format the output to your liking and even display the results in a grid of customizable columns and rows.
Also supports "lenses" which can turn your query results into Tabs, Accordion, or Carousel widgets. This feature relies on Bootstrap library to be already loaded on the page, the plugin does *not* include it (for that you can use the <a href="http://wordpress.org/extend/plugins/bootstrap/">Bootstrap plugin</a>). You can create new lenses or override the built-in ones in your theme to customize the output.

= Usage =
You can use all parameters supported by <a href="http://codex.wordpress.org/Class_Reference/WP_Query">WP_Query class</a> to filter the posts; you can query for specific post types, categories, tags, authors, etc. You also have to define how you want to format the output:
<code>[query posts_per_page="5" cat="3"] <h3><a href="{URL}">{TITLE} ({COMMENT_COUNT})</a></h3> [/query]</code>
The above shortcode will display the title of the latest 5 posts from the category with the ID of 3. Available keywords are: TITLE, CONTENT, AUTHOR, AUTHOR_URL, DATE, THUMBNAIL, CONTENT, COMMENT_COUNT and more to be added later.

= Grid display =
With the "cols" parameter you can display the output in a grid. So this:
<code>[query posts_per_page="3" cols="3"] {THUMBNAIL} <h3>{TITLE}</h3> {CONTENT} [/query]</code>
will display the latest 3 posts in the defined template, in 3 columns. If in the above snippet we set the posts_per_page option to 6, it will display the latest 6 posts in two rows that each has 3 columns.

= Lenses =
With the "lens" parameter you can display the query results in a Tab, Accordion, or Carousel widget. Example:
<code>[query posts_per_page="0" post_type="faq" lens="accordion"]</code>
This will create an accordion widget of all our posts from the "faq" post type. This creates a carousel of latest five featured posts:
<code>[query posts_per_page="5" featured="true" lens="carousel"]</code>

= Other supported parameters =
Aside from wp_query parameters, the shortcode also supports additional parameters:

* *featured* : to query for sticky posts which by default are excluded from the query.
* *thumbnail_size* : to specify the size of the {THUMBNAIL} images. You can use <a href="http://codex.wordpress.org/Function_Reference/add_image_size#Reserved_Image_Size_Names">built-in image sizes</a> or custom ones you've defined.
* *content_limit* : to limit the number of words of the {CONTENT} var; by default it's "0" which means it outputs the whole content.
* *posts_separator* : text to display between individual posts.


== Installation ==

1. Upload the whole plugin directory to the `/wp-content/plugins/` directory
2. Activate the plugin through the 'Plugins' menu in WordPress
3. Now use <code>[query]</code> shortcode anywhere you want.
4. Enjoy!

 
 == Installation ==

= 0.2.1 =
* Added posts_separator parameter.

= 0.2 =
* Added Lens functionality. Now you can build tabs, accordions, and carousels (and build custom ones) out of queried posts. Relies on Twitter Bootstrap framework.