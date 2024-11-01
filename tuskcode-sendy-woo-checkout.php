<?php
/*
Plugin Name: TuskCode's Checkout for Sendy on WooCommerce
Plugin URI: http://tuskcode.com
Description: Subscribe users to Sendy list from Woocommerce Checkout page
Version: 1.3.1
Author: dan009
Author URI: https://profiles.wordpress.org/dan009/
Text Domain: tuskcode-sendy-woo-checkout
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html
*/

if (!defined('ABSPATH')) {
	exit; // Exit if accessed directly
}
if (!class_exists('PLG_Sendy_Woo_Checkout_Main')) :

	if( ! defined( 'PLG_SENDY_WOO_CHECKOUT_BASENAME' ))
		define('PLG_SENDY_WOO_CHECKOUT_BASENAME', plugin_basename( __FILE__ ) );

	if( ! defined('PLG_SENDY_WOO_CHECKOUT_URL')) 
    	define( 'PLG_SENDY_WOO_CHECKOUT_URL', plugins_url( '', __FILE__ ) );

	if( ! defined('PLG_SENDY_WOO_CHECKOUT_VER')) 
    	define( 'PLG_SENDY_WOO_CHECKOUT_VER', '1.3.1' );
	
	if( ! defined('PLG_SENDY_WOO_CHECKOUT_DIR_PATH') ) 
		define('PLG_SENDY_WOO_CHECKOUT_DIR_PATH', plugin_dir_path( __FILE__ ));

	class PLG_Sendy_Woo_Checkout_Main
	{

		public function __construct()
		{
			add_action('plugins_loaded', array($this, 'init'));
			add_action('ini', array( $this, 'sendy_i18n_init'));
		}

		public function init()
		{
		
			if (class_exists('WC_Integration')) {
			
				include_once 'includes/class-integ-sendy-checkout.php';

				// Register the integration.
				add_filter('woocommerce_integrations', array($this, 'sendy_woo_integration'), 10, 1);				

			} else {
			
			}
		}

		public function sendy_woo_integration($integrations)
		{
			$integrations[] = 'PLG_Sendy_Woo_Checkout_Integration';
			return $integrations;
		}

		public function sendy_i18n_init(){
			$pluginDir = dirname( plugin_basename(__FILE__) );
			load_plugin_textdomain( 'tuskcode-sendy-woo-checkout' , false, $pluginDir . '/languages/');
		}

	}

	$PT_wc_sendy = new PLG_Sendy_Woo_Checkout_Main( __FILE__ );

endif;