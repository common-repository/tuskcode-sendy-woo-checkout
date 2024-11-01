<?php

if (!defined('ABSPATH')) {
	exit; // Exit if accessed directly
}

if ( ! class_exists('PLG_Sendy_Woo_Checkout_Integration') ) :

	class PLG_Sendy_Woo_Checkout_Integration extends WC_Integration{

		public function __construct()
		{		

			$this->id                 = 'integ-sendy-woo-checkout';
			$this->method_title       = esc_html__("TuskCode's Checkout for Sendy on WooCommerce", 'tuskcode-sendy-woo-checkout' );
			$this->method_description = esc_html__('Subscribe users to Sendy Newsletter from Woo Checkout Page', 'tuskcode-sendy-woo-checkout' );

			// Load the settings.
			$this->init_form_fields();
			$this->init_settings();

			// Define user set variables.
			$this->sendy_enabled     		= $this->get_option('sendy_enabled');
			$this->sendy_url         		= $this->get_option('sendy_url');
			$this->sendy_list        		= $this->get_option('sendy_list');
			$this->sendy_api_key     		= $this->get_option('sendy_api_key');
			$this->checkout_label    		= $this->get_option('sendy_checkbox_label');			
			$this->sendy_checkbox_default 	= $this->get_option('sendy_checkbox_default');
			$this->sendy_list_pos			= $this->get_option('sendy_list_pos');
		

			if( $this->sendy_enabled == 'yes' ){				
				add_action( 'woocommerce_checkout_create_order', array( &$this, 'custom_checkout_field_update_order_meta'), 10, 2 );
				//add_action( 'woocommerce_thankyou', array( &$this,'add_to_sendy_mailer'), 20, 1 );				
				add_action( 'woocommerce_checkout_order_created', array( &$this, 'add_to_sendy_mailer'), 20, 1 );
				add_action( $this->sendy_list_pos, array( &$this, 'my_custom_checkout_field' ) );
				add_action( 'woocommerce_admin_order_data_after_order_details', array( &$this, 'sendy_display_data_in_admin'), 21, 1 ); //admin order view
			}

			if( is_admin() ){	
				$current_page = http_build_query($_GET);
				$url_params = [];

				parse_str( $current_page, $url_params );				

				if( isset( $url_params['section'] ) && $url_params['section'] == $this->id ){
					wp_enqueue_style('sendy_custom_css', PLG_SENDY_WOO_CHECKOUT_URL . '/assets/admin/css/plg-sendy-main-style.css', [], PLG_SENDY_WOO_CHECKOUT_VER );
				}			
			}

			add_action( 'woocommerce_update_options_integration_' .  $this->id, array(&$this, 'process_admin_options'));
			add_filter( 'plugin_row_meta', array( &$this, 'settings_link_to_row_meta'), 10, 2 );
			add_filter( 'plugin_action_links_' . PLG_SENDY_WOO_CHECKOUT_BASENAME, array( &$this, 'settings_link_to_row_meta_two'), 10, 1 );	

		}

		public function sendy_display_data_in_admin( $order ){	
				 
			$order_id = $order->get_id();			
			$key_meta = 'sendy_response';

			echo '<p class="form-field form-field-wide">' . get_post_meta( $order_id, $key_meta, true ) . '</p> ';			
			
		}

		public function my_custom_checkout_field( $checkout ) {
		
			woocommerce_form_field( 'sendy_checkout_val', array(
				'type'          => 'checkbox',
				'id'			=> 'sendy_checkout_val',		
				'label_class'	=> array('woocommerce-form__label woocommerce-form__label-for-checkbox checkbox'),	
				'input_class'	=> array('woocommerce-form__input woocommerce-form__input-checkbox input-checkbox'),
				'required'		=> false,
				'label'         => $this->checkout_label

			), $this->sendy_checkbox_default == 'yes' );
		
		}

		public function settings_link_to_row_meta( $links, $file ) { 

			if ( PLG_SENDY_WOO_CHECKOUT_BASENAME == $file ) {
				
				$url = admin_url() . 'admin.php?page=wc-settings&tab=integration&section=' . $this->id;
			
				$row_meta = array(
				  'docs'    => '<a href="' . esc_url( $url ) . '"  aria-label="' . esc_attr__( 'Plugin Additional Links', 'tuskcode-sendy-woo-checkout' ) .
				  				 '" style="color:green;">' . esc_html__( 'Settings', 'tuskcode-sendy-woo-checkout' ) . '</a>'
				);
		
				return array_merge( $links, $row_meta );
			}

			return (array) $links;
		}

		public function settings_link_to_row_meta_two( $links ) { 
			$url = admin_url() . 'admin.php?page=wc-settings&tab=integration&section=' . $this->id;
				

			$link = '<a href="' . esc_url( $url ) . '"  aria-label="' . esc_attr__( 'Plugin Additional Links', 'tuskcode-sendy-woo-checkout' ) . 
							'" style="color:green;">' . esc_html__( 'Settings', 'tuskcode-sendy-woo-checkout' ) . '</a>';
			
			$links[] = $link;

			return (array) $links;
		}

		public function custom_checkout_field_update_order_meta( $order, $data ) {
		
			$value = isset($_POST['sendy_checkout_val']) ? 'yes' : 'no'; // Set the correct values
			
			$order->update_meta_data( 'sendy_checkout_val', $value );
		}

		public function init_form_fields()
		{
			$this->form_fields = array(

				'sendy_url' => array(
					'title'             => esc_html__('Sendy URL', 'tuskcode-sendy-woo-checkout' ),
					'type'              => 'text',
					'description'       => esc_html__('URL of your Sendy installation', 'tuskcode-sendy-woo-checkout' ),
					'placeholder'		=> 'http://your-sendy-server.com',
					'desc_tip'          => true,
					'default'           => ''
				),

				'sendy_api_key' => array(
					'title'             => esc_html__('Sendy API Key', 'tuskcode-sendy-woo-checkout'),
					'type'              => 'text',
					'placeholder'		=> 'A323bdaec3452abde',
					'default'           => '',
					'desc_tip'          => true,
					'description'       => esc_html__('Add your Sendy API Key', 'tuskcode-sendy-woo-checkout' ),
				),

				//==========================================================

				'sendy_checkbox_default__label_hide_one' => array(
					'title'             => '<b>--- '. esc_html__('1 FREE', 'tuskcode-sendy-woo-checkout' ) . ' ---</b>',
					'type'              => 'checkbox',
					'label'				=> 'Checked / Unchecked (2)',					
					'desc_tip'          => false,
					'default'           => 'no'
				),

				'sendy_enabled' => array(
					'title'             => esc_html__('Active List 1 ', 'tuskcode-sendy-woo-checkout' ),
					'type'              => 'checkbox',
					'description'       => esc_html__('Yes / No', 'tuskcode-sendy-woo-checkout' ),
					'desc_tip'          => true,
					'default'           => 'no'
				),

				'sendy_list' => array(
					'title'             => esc_html__('Sendy List ID 1 (As in Sendy)', 'tuskcode-sendy-woo-checkout' ),
					'type'              => 'text',
					'default'           => '',
					'desc_tip'          => true,
					'description'       => esc_html__('Add ID of your Sendy list', 'tuskcode-sendy-woo-checkout'),
				),


				'sendy_list_pos' => array(
					'title' 			=> esc_html__('Position at Checkout Page (1)', 'tuskcode-sendy-woo-checkout'),
					'type'				=> 'select',
					'options'			=> $this->checkout_hooks_opts(),
					'default'			=> 'woocommerce_review_order_before_submit',
					'desc_tip'			=> true,
					'description'		=> esc_html__( "Select where you want the checkbox to show at the checkout page,
												If your theme is overriding the WooCommerce templates then it's
						 						possible that these hooks could be missing, or placed in a slightly different position.
										", 'tuskcode-sendy-woo-checkout' )							
				),

				'sendy_checkbox_default' => array(
					'title'             => esc_html__('Default Checkbox Value (1)', 'tuskcode-sendy-woo-checkout' ),
					'type'              => 'checkbox',
					'label'				=> 'Checked / Unchecked',					
					'desc_tip'          => false,
					'default'           => 'no'
				),

				'sendy_checkbox_label' => array(
					'title'			 	=> esc_html__('Checkbox Label (1)', 'tuskcode-sendy-woo-checkout'),
					'type'				=> 'text',
					'default'			=> 'Subscribe to our Newsletter',
					'desc_tip'			=> true,
					'description'		=> esc_html__('Checkbox shown on checkout page, after Terms&Conditions', 'tuskcode-sendy-woo-checkout'),
				),

				// premium			
				'sendy_checkbox_default__label_hide' => array(
					'title'             => '*** ' . sprintf( esc_html__('Premium at %s', 'tuskcode-sendy-woo-checkout' ), '<a  href="https://tuskcode.com/sendy-woo-checkout-premium/" target="_blank"> tuskcode.com </a>' ) . ' ***',
					'type'              => 'checkbox',
					'label'				=> 'Checked / Unchecked (2)',					
					'desc_tip'          => false,
					'default'           => 'no'
				),

				'sendy_tuskcode_license' => array(
					'title'             => esc_html__('Tuskcode License for Premium', 'tuskcode-sendy-woo-checkout' ),
					'type'              => 'text',
					'default'           => '',
					'desc_tip'          => true,
					'description'       => esc_html__('License number provided when buying the premium', 'tuskcode-sendy-woo-checkout'),
				),

				'sendy_email_pos'  => array(
					'title' 			=> esc_html__('Show Selected / Unselected Options in Customer Order Email - *** PREMIUM ***', 'tuskcode-sendy-woo-checkout'),
					'type'				=> 'select',
					'options'			=> $this->email_pos_opts(),
					'default'			=> '0',
					'desc_tip'			=> true,
					'description'		=> esc_html__( 'Show checked options in the email for customer records
										', 'tuskcode-sendy-woo-checkout' )		
				),

				'sendy_thank_you_page' => array(
					'title'             =>  esc_html__('Show the selected / unselected options in the Thank You page - *** PREMIUM ***', 'tuskcode-sendy-woo-checkout'),
					'type'              => 'checkbox',
					'label'				=> esc_html__('Yes / No', 'tuskcode-sendy-woo-checkout' ),					
					'desc_tip'          => true,
					'description'		=> esc_html__('Show options in the thank you page in the same format as in the email template', 'tuskcode-sendy-woo-checkout' ),
					'default'           => 'no'
				),

				//two premium
				'sendy_checkbox_default__label_hide_two' => array(
					'title'             => '<b>*** '. esc_html__('2 PREMIUM', 'tuskcode-sendy-woo-checkout' ) . ' ***</b>',
					'type'              => 'checkbox',
					'label'				=> 'Checked / Unchecked (2)',					
					'desc_tip'          => false,
					'default'           => 'no'
				),

				'sendy_enabled_two' => array(
					'title'             => '<b>' . esc_html__('Active List 2 (Premium)', 'tuskcode-sendy-woo-checkout' ) . '</b>',
					'type'              => 'checkbox',
					'description'       => esc_html__('Yes / No', 'tuskcode-sendy-woo-checkout' ),
					'desc_tip'          => true,
					'default'           => 'no'
				),

				'sendy_list_two' => array(
					'title'             => esc_html__('Sendy List ID 2', 'tuskcode-sendy-woo-checkout' ),
					'type'              => 'text',
					'default'           => '',
					'desc_tip'          => true,
					'description'       => esc_html__('Add ID of your Sendy list', 'tuskcode-sendy-woo-checkout'),
				),

				'sendy_list_name_two' => array(
					'title'             => esc_html__('Sendy List Name - Admin (2)', 'tuskcode-sendy-woo-checkout' ),
					'type'              => 'text',
					'default'           => esc_html__('List Two', 'tuskcode-sendy-woo-checkout' ),
					'desc_tip'          => true,
					'description'       => esc_html__('Sendy List Name for Wordpress Reference ', 'tuskcode-sendy-woo-checkout'),
				),

				'sendy_list_pos_two' => array(
					'title' 			=> esc_html__('Position at Checkout Page (2)', 'tuskcode-sendy-woo-checkout'),
					'type'				=> 'select',
					'options'			=> $this->checkout_hooks_opts(),
					'default'			=> 'woocommerce_review_order_before_submit',
					'desc_tip'			=> true,
					'description'		=> esc_html__( "Select where you want the checkbox to show at the checkout page,
												If your theme is overriding the WooCommerce templates then it's
						 						possible that these hooks could be missing, or placed in a slightly different position.
										", 'tuskcode-sendy-woo-checkout' )							
				),

				'sendy_checkbox_default_two' => array(
					'title'             => esc_html__('Default Checkbox Value (2)', 'tuskcode-sendy-woo-checkout' ),
					'type'              => 'checkbox',
					'label'				=> 'Checked / Unchecked',					
					'desc_tip'          => false,
					'default'           => 'no'
				),

				'sendy_checkbox_label_two' => array(
					'title'			 	=> esc_html__('Checkout Checkbox Label (2)', 'tuskcode-sendy-woo-checkout'),
					'type'				=> 'text',
					'default'			=> 'Subscribe to our Newsletter',
					'desc_tip'			=> true,
					'description'		=> esc_html__('Checkbox shown on checkout page', 'tuskcode-sendy-woo-checkout'),
				),


				//three premium

				// premium			
				'sendy_checkbox_default__label_hide_three' => array(
					'title'             => '<b>*** ' . esc_html__('3 PREMIUM', 'tuskcode-sendy-woo-checkout' ) . ' ***</b>',
					'type'              => 'checkbox',
					'label'				=> 'Checked / Unchecked (3)',					
					'desc_tip'          => false,
					'default'           => 'no'
				),

				'sendy_enabled_three' => array(
					'title'             => '<b>'. esc_html__('Active List 3 (Premium)', 'tuskcode-sendy-woo-checkout' ) . '</b>',
					'type'              => 'checkbox',
					'description'       => esc_html__('Yes / No', 'tuskcode-sendy-woo-checkout' ),
					'desc_tip'          => true,
					'default'           => 'no'
				),

				'sendy_list_three' => array(
					'title'             => esc_html__('Sendy List ID 3', 'tuskcode-sendy-woo-checkout' ),
					'type'              => 'text',
					'default'           => '',
					'desc_tip'          => true,
					'description'       => esc_html__('Add ID of your Sendy list', 'tuskcode-sendy-woo-checkout'),
				),

				'sendy_list_name_three' => array(
					'title'             => esc_html__('Sendy List Name - Admin (3)', 'tuskcode-sendy-woo-checkout' ),
					'type'              => 'text',
					'default'           => esc_html__('List Three', 'tuskcode-sendy-woo-checkout' ),
					'desc_tip'          => true,
					'description'       => esc_html__('Sendy List Name for Wordpress Reference ', 'tuskcode-sendy-woo-checkout'),
				),

				'sendy_list_pos_three' => array(
					'title' 			=> esc_html__('Position at Checkout Page (3)', 'tuskcode-sendy-woo-checkout'),
					'type'				=> 'select',
					'options'			=> $this->checkout_hooks_opts(),
					'default'			=> 'woocommerce_review_order_before_submit',
					'desc_tip'			=> true,
					'description'		=> esc_html__( "Select where you want the checkbox to show at the checkout page,
												If your theme is overriding the WooCommerce templates then it's
						 						possible that these hooks could be missing, or placed in a slightly different position.
										", 'tuskcode-sendy-woo-checkout' )							
				),

				'sendy_checkbox_default_three' => array(
					'title'             => esc_html__('Default Checkbox Value (3)', 'tuskcode-sendy-woo-checkout' ),
					'type'              => 'checkbox',
					'label'				=> 'Checked / Unchecked',					
					'desc_tip'          => false,
					'default'           => 'no'
				),

				'sendy_checkbox_label_three' => array(
					'title'			 	=> esc_html__('Checkout Checkbox Label (3)', 'tuskcode-sendy-woo-checkout'),
					'type'				=> 'text',
					'default'			=> 'Subscribe to our Newsletter',
					'desc_tip'			=> true,
					'description'		=> esc_html__('Checkbox shown on checkout page', 'tuskcode-sendy-woo-checkout'),
				),


				//four premium

				'sendy_checkbox_default__label_hide_four' => array(
					'title'             => '<b>*** ' . esc_html__('4 PREMIUM', 'tuskcode-sendy-woo-checkout' ) . ' ***</b>',
					'type'              => 'checkbox',
					'label'				=> 'Checked / Unchecked (4)',					
					'desc_tip'          => false,
					'default'           => 'no'
				),

				'sendy_enabled_four' => array(
					'title'             => '<b>' . esc_html__('Active List 4 (Premium)', 'tuskcode-sendy-woo-checkout' ) . '</b>',
					'type'              => 'checkbox',
					'description'       => esc_html__('Yes / No', 'tuskcode-sendy-woo-checkout' ),
					'desc_tip'          => true,
					'default'           => 'no'
				),

				'sendy_list_four' => array(
					'title'             => esc_html__('Sendy List ID 4', 'tuskcode-sendy-woo-checkout' ),
					'type'              => 'text',
					'default'           => '',
					'desc_tip'          => true,
					'description'       => esc_html__('Add ID of your Sendy list', 'tuskcode-sendy-woo-checkout'),
				),

				'sendy_list_name_four' => array(
					'title'             => esc_html__('Sendy List Name - Admin (4)', 'tuskcode-sendy-woo-checkout' ),
					'type'              => 'text',
					'default'           => esc_html__('List Four', 'tuskcode-sendy-woo-checkout' ),
					'desc_tip'          => true,
					'description'       => esc_html__('Sendy List Name for Wordpress Reference ', 'tuskcode-sendy-woo-checkout'),
				),

				'sendy_list_pos_four' => array(
					'title' 			=> esc_html__('Position at Checkout Page (4)', 'tuskcode-sendy-woo-checkout'),
					'type'				=> 'select',
					'options'			=> $this->checkout_hooks_opts(),
					'default'			=> 'woocommerce_review_order_before_submit',
					'desc_tip'			=> true,
					'description'		=> esc_html__( "Select where you want the checkbox to show at the checkout page,
												If your theme is overriding the WooCommerce templates then it's
						 						possible that these hooks could be missing, or placed in a slightly different position.
										", 'tuskcode-sendy-woo-checkout' )							
				),

				'sendy_checkbox_default_four' => array(
					'title'             => esc_html__('Default Checkbox Value (4)', 'tuskcode-sendy-woo-checkout' ),
					'type'              => 'checkbox',
					'label'				=> 'Checked / Unchecked',					
					'desc_tip'          => false,
					'default'           => 'no'
				),

				'sendy_checkbox_label_four' => array(
					'title'			 	=> esc_html__('Checkout Checkbox Label (4)', 'tuskcode-sendy-woo-checkout'),
					'type'				=> 'text',
					'default'			=> 'Subscribe to our Newsletter',
					'desc_tip'			=> true,
					'description'		=> esc_html__('Checkbox shown on checkout page', 'tuskcode-sendy-woo-checkout'),
				),

				//five premium

				'sendy_checkbox_default__label_hide_five' => array(
					'title'             => '<b>*** ' . esc_html__('5 PREMIUM', 'tuskcode-sendy-woo-checkout' ) . ' ***</b>',
					'type'              => 'checkbox',
					'label'				=> 'Checked / Unchecked (5)',					
					'desc_tip'          => false,
					'default'           => 'no'
				),

				'sendy_enabled_five' => array(
					'title'             => '<b>'. esc_html__('Active List 5 (Premium)', 'tuskcode-sendy-woo-checkout' ) .'</b>', 
					'type'              => 'checkbox',
					'description'       => esc_html__('Yes / No', 'tuskcode-sendy-woo-checkout' ),
					'desc_tip'          => true,
					'default'           => 'no'
				),

				'sendy_list_five' => array(
					'title'             => esc_html__('Sendy List ID 5', 'tuskcode-sendy-woo-checkout' ),
					'type'              => 'text',
					'default'           => '',
					'desc_tip'          => true,
					'description'       => esc_html__('Add ID of your Sendy list (5)', 'tuskcode-sendy-woo-checkout'),
				),

				'sendy_list_name_five' => array(
					'title'             => esc_html__('Sendy List Name - Admin (5)', 'tuskcode-sendy-woo-checkout' ),
					'type'              => 'text',
					'default'           => esc_html__('List Four', 'tuskcode-sendy-woo-checkout' ),
					'desc_tip'          => true,
					'description'       => esc_html__('Sendy List Name for Wordpress Reference ', 'tuskcode-sendy-woo-checkout'),
				),

				'sendy_list_pos_five' => array(
					'title' 			=> esc_html__('Position at Checkout Page (5)', 'tuskcode-sendy-woo-checkout'),
					'type'				=> 'select',
					'options'			=> $this->checkout_hooks_opts(),
					'default'			=> 'woocommerce_review_order_before_submit',
					'desc_tip'			=> true,
					'description'		=> esc_html__( "Select where you want the checkbox to show at the checkout page,
												If your theme is overriding the WooCommerce templates then it's
													possible that these hooks could be missing, or placed in a slightly different position.
										", 'tuskcode-sendy-woo-checkout' )							
				),

				'sendy_checkbox_default_five' => array(
					'title'             => esc_html__('Default Checkbox Value (5)', 'tuskcode-sendy-woo-checkout' ),
					'type'              => 'checkbox',
					'label'				=> 'Checked / Unchecked',					
					'desc_tip'          => false,
					'default'           => 'no'
				),

				'sendy_checkbox_label_five' => array(
					'title'			 	=> esc_html__('Checkout Checkbox Label (5)', 'tuskcode-sendy-woo-checkout'),
					'type'				=> 'text',
					'default'			=> 'Subscribe to our Newsletter',
					'desc_tip'			=> true,
					'description'		=> esc_html__('Checkbox shown on checkout page', 'tuskcode-sendy-woo-checkout'),
				),


			);
		}

		private function email_pos_opts(){
			return array(
				'no' => esc_html__('Do not show in email', 'tuskcode-sendy-woo-checkout' ),
				'woocommerce_email_before_order_table' => esc_html__('Before Order Table', 'tuskcode-sendy-woo-checkout' ),
				'woocommerce_email_after_order_table' => esc_html__('After Order Table', 'tuskcode-sendy-woo-checkout' )  
			);
		}


		private function checkout_hooks_opts(){

			return array(
				'woocommerce_before_checkout_form' 				=> esc_html__('Before Checkout Form', 'tuskcode-sendy-woo-checkout' ),
				'woocommerce_checkout_before_customer_details' 	=> esc_html__('Before Customer Details', 'tuskcode-sendy-woo-checkout'),
				'woocommerce_before_checkout_billing_form' 		=> esc_html__('Before Billing Form', 'tuskcode-sendy-woo-checkout'),
				'woocommerce_after_checkout_billing_form' 		=> esc_html__('After Billing Form', 'tuskcode-sendy-woo-checkout' ),
				'woocommerce_before_checkout_shipping_form' 	=> esc_html__('Before Shipping Form', 'tuskcode-sendy-woo-checkout' ),
				'woocommerce_after_checkout_shipping_form'  	=> esc_html__('After Shipping Form', 'tuskcode-sendy-woo-checkout' ),
				'woocommerce_before_order_notes'				=> esc_html__('Before Order Notes', 'tuskcode-sendy-woo-checkout' ),
				'woocommerce_after_order_notes'					=> esc_html__('After Order Notes', 'tuskcode-sendy-woo-checkout' ),
				'woocommerce_checkout_after_customer_details' 	=> esc_html__('After Customer Details', 'tuskcode-sendy-woo-checkout' ),
				'woocommerce_checkout_before_order_review' 		=> esc_html__('Before Order Review', 'tuskcode-sendy-woo-checkout' ),
				'woocommerce_review_order_before_cart_contents'	=> esc_html__('Before Review Cart Contents', 'tuskcode-sendy-woo-checkout' ),
				'woocommerce_review_order_after_cart_contents'	=> esc_html__('After Review Cart Contents', 'tuskcode-sendy-woo-checkout' ),
				'woocommerce_review_order_before_shipping'		=> esc_html__('Before Review Shipping', 'tuskcode-sendy-woo-checkout' ),
				'woocommerce_review_order_after_shipping'		=> esc_html__('After Review Shipping', 'tuskcode-sendy-woo-checkout' ),
				'woocommerce_review_order_before_order_total'	=> esc_html__('Before Review Order Total', 'tuskcode-sendy-woo-checkout' ),
				'woocommerce_review_order_after_order_total'	=> esc_html__('After Review Order Total', 'tuskcode-sendy-woo-checkout' ),
				'woocommerce_review_order_before_payment'		=> esc_html__('Before Review Payment', 'tuskcode-sendy-woo-checkout'  ),
				'woocommerce_review_order_before_submit'		=> esc_html__('Before Review Submit', 'tuskcode-sendy-woo-checkout' ),
				'woocommerce_review_order_after_submit'			=> esc_html__('After Review Submit', 'tuskcode-sendy-woo-checkout' ),
				'woocommerce_review_order_after_payment'		=> esc_html__('After Review Payment', 'tuskcode-sendy-woo-checkout' ),
				'woocommerce_checkout_after_order_review'		=> esc_html__('After Order Review', 'tuskcode-sendy-woo-checkout' ),
				'woocommerce_after_checkout_form'				=> esc_html__('After Checkout Form', 'tuskcode-sendy-woo-checkout' )
			);
		}

		public function add_to_sendy_mailer( $order ){
			$key_meta = 'sendy_response';
			$is_checked = $order->get_meta('sendy_checkout_val', true, '' );
			$order_id = $order->get_id();	

			if( $is_checked == 'no'){
				$value = esc_html( $this->checkout_label  ) . ' : ' . esc_html__('No', 'tuskcode-sendy-woo-checkout');
				update_post_meta( $order_id, $key_meta, $value );
			}

			if( $is_checked != 'yes' )
				return;

			$url = rtrim($this->sendy_url, "/");			

			$sendy_data = array(
				'name' => $order->get_billing_first_name() . ' ' . $order->get_billing_last_name(),
				'email' => $order->get_billing_email(),
				'list' => trim( $this->sendy_list ),
				'api_key' => trim( $this->sendy_api_key ),
				'boolean' => 'true',
			);

			$sendy_url = $url . '/subscribe';
			
			try {
				$result = wp_remote_post($sendy_url, array('body' => $sendy_data));
				$result = $result['body'];
				$value = esc_html( $this->checkout_label  ) . ' : ' . esc_html( $result );
				update_post_meta( $order_id, $key_meta, $value );
			} catch (\Throwable $th) {
				error_log('Failed to connect with Sendy');
				error_log( print_r( $th->getMessage(), true ));
			}

		}


		public function sanitize_settings($settings)
		{
			return $settings;
		}
	}

endif;