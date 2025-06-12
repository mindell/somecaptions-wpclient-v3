=== SoMe Captions Client ===
Contributors: mindell
Tags: articles, publishing, somecaptions
Requires at least: 5.2
Tested up to: 6.8
Stable tag: 3.0.1
License: GPL-2.0+
License URI: http://www.gnu.org/licenses/gpl-2.0.txt
Requires PHP: 7.4

Integrate your WordPress site with SoMe Captions platform for automated content creation and publishing.

== Description ==

SoMe Captions Client allows you to seamlessly integrate your WordPress site with the SomeCaptions platform. This plugin enables automated content creation, publishing, and management directly from your SomeCaptions dashboard.

Key features include:

* Domain verification for secure integration
* API key management
* Automated article publishing
* Featured image support
* Content formatting options
* Support for both posts and pages
* Enhanced error handling

For more information, visit [SomeCaptions](https://somecaptions.dk/).
stable.

    Note that the `readme.txt` of the stable tag is the one that is considered the defining one for the plugin, so
if the `/trunk/readme.txt` file says that the stable tag is `4.3`, then it is `/tags/4.3/readme.txt` that'll be used
for displaying information about the plugin.  In this situation, the only thing considered from the trunk `readme.txt`
is the stable tag pointer.  Thus, if you develop in trunk, you can update the trunk `readme.txt` to reflect changes in
your in-development version, without having that information incorrectly disclosed about the current stable version
that lacks those changes -- as long as the trunk's `readme.txt` points to the correct stable tag.

    If no stable tag is provided, it is assumed that trunk is stable, but you should specify "trunk" if that's where
you put the stable version, in order to eliminate any doubt.

== Installation ==

This section describes how to install the plugin and get it working.

e.g.

= Using The WordPress Dashboard =

1. Navigate to the 'Add New' in the plugins dashboard
2. Search for 'somecaptions-wpclient'
3. Click 'Install Now'
4. Activate the plugin on the Plugin dashboard

= Uploading in WordPress Dashboard =

1. Navigate to the 'Add New' in the plugins dashboard
2. Navigate to the 'Upload' area
3. Select `somecaptionswpclient.zip` from your computer
4. Click 'Install Now'
5. Activate the plugin in the Plugin dashboard

= Using FTP =

1. Download `somecaptionswpclient.zip`
2. Extract the `somecaptionswpclient` directory to your computer
3. Upload the `somecaptionswpclient` directory to the `/wp-content/plugins/` directory
4. Activate the plugin in the Plugin dashboard

== Frequently Asked Questions ==

= A question that someone might have =

An answer to that question.

= What about foo bar? =

Answer to foo bar dilemma.

== Screenshots ==

1. This screen shot description corresponds to screenshot-1.(png|jpg|jpeg|gif). Note that the screenshot is taken from
the /assets directory or the directory that contains the stable readme.txt (tags or trunk). Screenshots in the /assets
directory take precedence. For example, `/assets/screenshot-1.png` would win over `/tags/4.3/screenshot-1.png`
(or jpg, jpeg, gif).
2. This is the second screen shot

== Changelog ==

= 0.0.1 =
* Basic changes. Non functional.

= 1.0 =
* The author of the posts created by the plugin will be "SEO af SoMe Captions".
* The publish date will be set from SoMe Captions API.
* Add image attachment to posts.

= 1.0.1 =
* Added alternative text

= 1.0.2 =
* Enabled update checker

= 1.5.2 =
* Security update

= 1.5.3 =
* The post type could be either `post` or `page`.

= 1.5.4 =
* Send publish event schedule to API.

= 1.6.0 =
* Disabled WP_Cron

= 2.0.4 =
* GSC auto-indexing.

= 2.0.5 =
* Convert base64 encoded image data to WordPress attachment image.

= 2.1.5 =
* Enable toggle of author

= 2.2.0 =
* Small bug fix

= 2.2.1 =
* Allow custom URL

= 3.0.0 =
* Added support for featured images
* Improved content formatting
* Enhanced error handling

= 3.0.1 =
* Added support for article types (post/page)
* Improved featured image handling with auto-resizing
* Enhanced integration with SoMe Captions dashboard

== Upgrade Notice ==

= 3.0.1 =
This version adds support for article types, improves image handling, and enhances integration with the SomeCaptions dashboard.
