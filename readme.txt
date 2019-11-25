=== Inspector ===
Contributors: elightup, rilwis
Donate link: https://paypal.me/anhtnt
Tags: meta, option, custom field, admin screen, user information, inspector, debug, debugging tool, debugging
Requires at least: 3.0
Tested up to: 5.3
Stable tag: 1.2.10

Inpect hidden information of your WordPress websites for debugging

== Description ==

The **Inspector** plugin is a tool for developers, which allows us to see hidden information for debugging. The information includes option value, post meta value, [current admin screen information](http://www.deluxeblogtips.com/2012/01/get-admin-screen-information.html), current user information, admin menu items and registered scripts and styles.

**Features**

- View/Delete option via Ajax
- View/Delete post meta via Ajax
- Autocomplte option & post meta name
- View current admin screen information
- View curren user information
- View admin menu items
- View registered scripts and styles

== Installation ==

1. Unzip the download package
1. Upload `inspector` folder to the `/wp-content/plugins/` directory
1. Activate the plugin through the 'Plugins' menu in WordPress

== Frequently Asked Questions ==

== Screenshots ==

== Changelog ==

= 1.2.10 =
* Update to compatible with the latest version of WordPress.

= 1.2.8 =

* Fix showing data via ajax
* Use jQueryUI CDN instead

= 1.2.7 =

* Fix include warning

= 1.2.6 =

* Add screen information in help tab

= 1.2.5 =

* Add debug function

= 1.2.3 =

* Fix duplicate entries
* Load class only in the back-end

= 1.2.2 =

* Fix adding menu
* Change textdomain to simple string
* Fix CSS

= 1.2.1 =

* Inspect post meta for all post types

= 1.2 =

* Add inspector for post meta
* Update jQuery UI to 1.8.7 with Smoothness theme

= 1.1 =

* Add autocomplete feature for option name input box
* Use nonces via localizing

= 1.0.2 =

* Show nonces using wp_nonce_field()
* Add document for JS

= 1.0.1 =

* Use sanitize_text_field() to sanitize option name
* Improve code of checking admin referrer
