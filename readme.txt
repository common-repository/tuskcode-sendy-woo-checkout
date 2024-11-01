=== TuskCode's Checkout for Sendy on WooCommerce ===
Contributors: dan009
Donate link: https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=HH7J3U2U9YYQ2
Tags: sendy, woo, checkout, woo checkout
Requires at least: 5.0
Tested up to: 6.4
Stable tag: 1.3.1
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Add Customers from WooCommerce checkout page to Sendy Newsletter list

== Description ==

Upon checkout page, give the customers option to subscribe to your Sendy Newsletter
Add custom checkbox field under "Terms & Conditions" checkbox on the Checkout Page of your shop


Features:

* Activate / Deactivate checkbox 
* Url to your Sendy Server  
* Api Key of Sendy
* Sendy List Id
* Custom Checkout Label
* Custom Checked / Unchecked default value 

Requirements:

* Sendy v5.0 or later
* WooCommerce v5.0.0 or later
* Wordpress v5.6.1 or later recommended

== Installation ==

The installation process is very basic:

1. Upload the unzipped plugin folder to the `/wp-content/plugins/` directory
2. Activate the plugin through the 'Plugins' menu in WordPress
3. Use the WooCommerce->Settings->Integration check "TuskCode's Checkout for Sendy on WooCommerce" to configure this plugin, or click on "Settings" located by the plugin's deactivate link

If you face issues with installation, you can read detail instructions here: http://codex.wordpress.org/Managing_Plugins#Installing_Plugins

== Frequently Asked Questions ==
= What is sendy? =
Sendy is a self hosted email newsletter application that lets you send trackable emails via Amazon Simple Email Service (SES). 

= Where is the settings page? =
Go to  "Woo->Settings->TuskCode's Checkout for Sendy on WooCommerce" to configure it.

= When is the data sent to Sendy Server? =
When the order is created. Upon hook "woocommerce_checkout_create_order" is triggered.

== Screenshots ==

1. Sendy settings.

== Changelog ==
= 1.3.1  2023-11-23 = 
* Tested with Wordpress 6.4
* Added State of checkox in the Order Admin View

= 1.3 =
* Added Custom Location at Checkout *

= 1.2 =
* Tested with 6.1 

= 1.1 =
* Tested with 5.9

= 1.0 =
* First version 