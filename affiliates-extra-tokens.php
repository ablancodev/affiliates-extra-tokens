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
 * Version: 1.0
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
		
		if (isset($data['referral_id'])) {
			$referral_id = $data['referral_id'];
			$referral = null;
			$referrals_table = _affiliates_get_tablename( 'referrals' );
			if ( $referrals = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM $referrals_table WHERE referral_id = %d", intval( $referral_id) ) ) ) {
				if ( count( $referrals ) > 0 ) {
					$referral = $referrals[0];
					
					$userdata = get_userdata($referral->user_id);
					$order_id = $referral->post_id;
					$order = new WC_Order( $order_id );

					// $userdata & $order contain information
					if ($userdata) {
						$data['user_username'] = $userdata->user_nicename;
						$data['user_email'] = $userdata->user_email;
						$data['user_displayname'] = $userdata->display_name;
						// You can add more user data here
					}
					if ($order) {
						$data['order_id'] = $order_id;
						$data['order_total'] = $order->get_total();
						// You can add more order data here
					}
					
				}
			}
		}
		return $data;
	}
}
Affiliates_Extra_Tokens_Plugin::init();

