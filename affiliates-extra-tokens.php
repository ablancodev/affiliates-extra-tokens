<?php
/**
 * affiliates-extra-tokens.php
 *
 * Copyright (c) 2011,2012 Antonio Blanco http://www.blancoleon.com
 *
 * This code is released under the GNU General Public License.
 * See COPYRIGHT.txt and LICENSE.txt.
 *
 * This code is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * This header and all notices must be kept intact.
 *
 * @author Antonio Blanco	
 * @package affiliates-extra-tokens
 * @since affiliates-extra-tokens 1.0
 *
 * Plugin Name: Affiliates Extra Tokens
 * Plugin URI: http://www.eggemplo.com
 * Description: Add extra tokens to affiliates notifications.
 * Version: 1.0.1
 * Author: eggemplo
 * Author URI: http://www.eggemplo.com
 * License: GPLv3
 */

define( 'AFFILIATES_EXTRA_TOKENS_DOMAIN', 'affiliatesextratokens' );

define( 'AFFILIATES_EXTRA_TOKENS_FILE', __FILE__ );


class Affiliates_Extra_Tokens_Plugin {
	
	private static $notices = array();
	
	public static function init() {

		add_action( 'init', array( __CLASS__, 'wp_init' ) );
		add_action( 'admin_notices', array( __CLASS__, 'admin_notices' ) );
	}
	
	public static function wp_init() {
		if ( !defined ( 'AFFILIATES_PLUGIN_DOMAIN' ) )  {
			self::$notices[] = "<div class='error'>" . __( '<strong>Affiliates Extra Tokens</strong> plugin requires <a href="http://www.itthinx.com/plugins/affiliates-pro" target="_blank">Affiliates Pro</a> or <a href="http://www.itthinx.com/plugins/affiliates-enterprise" target="_blank">Affiliates Enterprise</a>.', AFFILIATES_PERMANENT_PLUGIN_DOMAIN ) . "</div>";
		} else {
		
			add_filter("affiliates_notifications_tokens", array(__CLASS__, "affiliates_notifications_tokens"));
		}
		
	}
		
	
	public static function admin_notices() { 
		if ( !empty( self::$notices ) ) {
			foreach ( self::$notices as $notice ) {
				echo $notice;
			}
		}
	}
	
	
	public static function affiliates_notifications_tokens ($data) {
		global $wpdb;
		global $create_affiliate_userdata;

		if (isset($data['referral_id'])) {
			$referral_id = $data['referral_id'];
			$referral = null;
			$referrals_table = _affiliates_get_tablename( 'referrals' );
			if ( $referrals = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM $referrals_table WHERE referral_id = %d", intval( $referral_id) ) ) ) {
				if ( count( $referrals ) > 0 ) {
					$referral = $referrals[0];
					
					$userdata = get_userdata($referral->user_id);
					
					// $userdata & $order contain information
					if ($userdata) {
						$data['user_username'] = $userdata->user_nicename;
						$data['user_email'] = $userdata->user_email;
						$data['user_displayname'] = $userdata->display_name;
						// You can add more user data here
					}
					
					// affiliate user data
					// 'aff_field_name' is a custom field in the affiliates registration form.
					$data['aff_field_name'] = $create_affiliate_userdata['aff_field_name'];
					
					// woocommerce
					$active_plugins = get_option( 'active_plugins', array() );
					$woocommerce_is_active = in_array( 'woocommerce/woocommerce.php', $active_plugins );
					if ( $woocommerce_is_active ) {
						$order_id = $referral->post_id;
						$order = new WC_Order( $order_id );
	
						if ($order) {
							$order_items = '';
							$data['order_id'] = $order_id;
							$data['order_total'] = $order->get_total();
							$data['customer_first_name'] = $order->get_billing_first_name();
							$data['customer_last_name'] = $order->get_billing_last_name();
							if ( sizeof( $order->get_items() ) > 0 ) {
								foreach ( $order->get_items() as $item ) {
									if ( $product = self::get_the_product_from_item( $item ) ) {
										$order_items .= '<a href=" ' . get_permalink( $product->get_id() ) . ' " >';
										$order_items .= $product->get_name();
										$order_items .= '</a>';
										$order_items .= ' ';
									}
								}
								$data['order_items'] = $order_items;
							}
							// You can add more order data here
						}
					}
				}
			}
		}
		return $data;
	}

	/**
	 * Retrieve the product from an order item
	 *
	 * @param WC_Order_Item_Product $item
	 * @return WC_Product|null
	 */
	public static function get_the_product_from_item( $item ) {
	    if( method_exists( 'WC_Order_Item_Product', 'get_product_id' ) ) {
	        $product_id = $item->get_variation_id() ? $item->get_variation_id() : $item->get_product_id();
	    } else {
	        $product_id = $item->variation_id ? $item->variation_id : $item->product_id;
	    }
	    return new WC_Product( $product_id ) ? new WC_Product( $product_id ) : null;
	}
}
Affiliates_Extra_Tokens_Plugin::init();

