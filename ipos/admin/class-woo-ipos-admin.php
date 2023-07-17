<?php

/**
 * The admin-specific functionality of the plugin.
 *
 * @link       https://github.com/auditt98/Woo-Ipos
 * @since      1.0.0
 *
 * @package    Woo_Ipos
 * @subpackage Woo_Ipos/admin
 */

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    Woo_Ipos
 * @subpackage Woo_Ipos/admin
 * @author     auditt98 <vietanh8i1998@gmail.com>
 */
require_once 'membership-traits.php';
require_once 'common-traits.php';
require_once 'plugin-setting-traits.php';
require_once 'report-traits.php';
require_once 'order-traits.php';

class Woo_Ipos_Admin
{
	use CommonTraits;
	use PluginSettingTraits;
	use MembershipTraits;
	use ReportTraits;
	use OrderTraits;
	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $plugin_name    The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $version    The current version of this plugin.
	 */
	private $version;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param      string    $plugin_name       The name of this plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct($plugin_name, $version)
	{

		$this->plugin_name = $plugin_name;
		$this->version = $version;

		// PLUGIN SETTINGS
		add_action('admin_menu', array($this, 'addPluginAdminMenu'), 9);
		add_action('admin_init', array($this, 'registerAndBuildFields'));
		add_action('rest_api_init', array($this, 'registerWebhook'));

		// PART 1: MEMBERSHIP
		add_action('woocommerce_register_form_start', array($this, 'customize_woo_registration_form'));
		add_action('woocommerce_register_post', array($this, 'disable_email_validation'), 10, 3);
		add_action('woocommerce_created_customer', array($this, 'sync_created_customer_to_ipos'), 10, 3);
		add_action('woocommerce_login_form_start', array($this, 'customize_woo_login_form'));
		add_action('wp_head', array($this, 'woo_ipos_ajaxurl'));
		add_action('woocommerce_review_order_before_payment', array($this, 'add_vouchers_to_checkout_form'));

		add_shortcode('woo_ipos_customer_info', array($this, 'display_customer_info'));
		add_shortcode('profile_update_form', array($this, 'profile_update_form_shortcode'));
		// add_shortcode('woo_ipos_customer_vouchers', array($this, 'display_vouchers_info'));
		add_shortcode('test', array($this, 'test'));
		add_shortcode('test_order', array($this, 'test_order'));

		// Add CSS class for logged in and logged out users
		add_filter('body_class', array($this, 'er_logged_in_filter'));
		// add_action( 'woocommerce_applied_coupon', array($this, 'check_voucher_valid'));
		add_action('wp_ajax_apply_voucher_action', array($this, 'apply_voucher'));
		add_action('woocommerce_cart_calculate_fees',  array($this, 'add_custom_fees'));
		add_action('woocommerce_checkout_process', array($this, 'save_voucher_to_order'));
		add_action('woocommerce_new_order', array($this, 'handle_order'));
	}

	function woo_ipos_ajaxurl()
	{
		echo '<script type="text/javascript">
           var ajaxurl = "' . admin_url('admin-ajax.php') . '";
         </script>';
	}

	function er_logged_in_filter($classes)
	{
		if (is_user_logged_in()) {
			$classes[] = 'logged-in-condition';
		} else {
			$classes[] = 'logged-out-condition';
		}
		// return the $classes array
		return $classes;
	}
}
