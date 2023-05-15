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
class Woo_Ipos_Admin
{

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
		add_action('admin_menu', array($this, 'addPluginAdminMenu'), 9);
		add_action('admin_init', array($this, 'registerAndBuildFields'));
		add_action('rest_api_init', array($this, 'registerWebhook'));
		add_action('woocommerce_register_form_start', array($this, 'customize_woo_registration_form'));
		add_action('woocommerce_register_post', array($this, 'disable_email_validation'), 10, 3);
		add_action('woocommerce_created_customer', array($this, 'sync_created_customer_to_ipos'), 10, 3);
	}

	// CUSTOMIZING WOOCOMMERCE REGISTRATION FORM

	function disable_email_validation($username, $email, $errors)
	{
		// Remove the email validation error
		if (isset($errors->errors['email'])) {
			unset($errors->errors['email']);
		}
	}

	public function customize_woo_registration_form()
	{
?>
		<style>
			.woocommerce-form-register {
				display: flex;
				flex-direction: column;
			}

			.woocommerce-form-row--first {
				order: -1;
			}
		</style>
		<style>
			.relative {
				position: relative;
			}

			.pointer-events-none {
				pointer-events: none;
			}

			.absolute {
				position: absolute;
			}

			.inset-y-0 {
				top: 0;
				bottom: 0;
			}

			.left-0 {
				left: 0;
			}

			.flex {
				display: flex;
			}

			.items-center {
				align-items: center;
			}

			.pl-3 {
				padding-left: 0.75rem;
			}

			.text-gray-500 {
				color: #6b7280;
			}

			.sm\:text-sm {
				font-size: 0.875rem;
			}

			.phone-prefix {
				display: inline-block;
			}
		</style>
		<p class="woocommerce-form-row woocommerce-form-row--wide form-row form-row-wide">
			<label for="billing_phone"><?php _e('Phone Number', 'woocommerce'); ?> <span class="required">*</span></label>
		<div class="relative" style="margin-bottom: 17px">
			<div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">
				<span class="text-gray-500 sm:text-sm">+84</span>
			</div>
			<input style="padding-left: 3rem; margin-bottom: 0px" type="text" pattern="(3|5|7|8|9])+([0-9]{8})\b" title="Vui lòng nhập đúng số điện thoại" class="woocommerce-Input woocommerce-Input--text input-text" required name="billing_phone" id="billing_phone" value="<?php if (!empty($_POST['billing_phone'])) esc_attr_e($_POST['billing_phone']); ?>" />
		</div>
		</p>

		<script>
			jQuery(document).ready(function($) {
				$('#reg_email').attr('type', 'hidden');
				$('label[for="reg_email"]').hide();
				$('#billing_phone').on('input', function() {
					var phone = $(this).val();
					var sanitizedPhone = phone.replace(/[^0-9]/g, "");
					if (sanitizedPhone[0] === '0') {
						sanitizedPhone = sanitizedPhone.substring(1);
					}

					if (sanitizedPhone.length > 9) {
						sanitizedPhone = sanitizedPhone.substring(0, 9);
					}
					$(this).val(sanitizedPhone);
					var email = '84' + sanitizedPhone + '@gmail.com';
					$('#reg_email').val(email);
				});
			});
		</script>
<?php

	}


	// SYNCING CREATED CUSTOMER TO IPOS
	public function sync_created_customer_to_ipos($customer_id, $new_customer_data, $password_generated) {
		$user = new WP_User($user_id);
	}



	// SETUP CALLBACK FOR IPOS
	public function woo_ipos_callback($request)
	{
		$data = $request->get_body();
		$json = json_decode($data, true);
		return array('data' => $this->ipos_event_handler($json));
	}

	// HANDLING IPOS EVENT 
	public function ipos_event_handler($data)
	{
		if (isset($data['event_id']) && ($data['event_id'] == 14 || $data['event_id'] == '14')) {
			return $this->ipos_new_member_handler($data);
		}
	}

	// HANDLING NEW MEMBER EVENT => Sync IPOS Customer to Wordpress
	public function ipos_new_member_handler($data)
	{
		// create wordpress user here
		$email = $data["membership"]["phone_number"] . '@gmail.com';
		$password = $data["membership"]["phone_number"];
		$username = $data["membership"]["phone_number"];
		$user_id = wp_create_user($username, $password, $email);
		// Check if user creation was successful
		if (!is_wp_error($user_id)) {
			// User created successfully, update the role of the user to customer
			$user = new WP_User($user_id);
			$user->set_role('customer');
			update_user_meta($user_id, 'billing_phone', $data["membership"]["phone_number"]);
			update_user_meta($user_id, 'shipping_phone', $data["membership"]["phone_number"]);
			// return the data
			$data['user_id'] = $user_id;
			return $data;
		} else {
			// Error creating user, handle the error here
			return "Error creating user";
		}
	}

	public function registerWebhook()
	{
		$this->createWebhook('/callback', 'woo_ipos_callback');
	}


	public function createWebhook($route, $callback_name)
	{
		register_rest_route('/woo-ipos/v1', $route, array(
			'methods' => 'POST',
			'callback' => array($this, $callback_name),
		));
	}

	/**
	 * Register the JavaScript for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts()
	{

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Woo_Ipos_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Woo_Ipos_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		wp_enqueue_script($this->plugin_name, plugin_dir_url(__FILE__) . 'js/woo-ipos-admin.js', array('jquery'), $this->version, false);
	}


	/**
	 * Register the stylesheets for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles()
	{

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Woo_Ipos_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Woo_Ipos_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		wp_enqueue_style($this->plugin_name, plugin_dir_url(__FILE__) . 'css/woo-ipos-admin.css', array(), $this->version, 'all');
	}

	public function addPluginAdminMenu()
	{
		//add_menu_page( $page_title, $menu_title, $capability, $menu_slug, $function, $icon_url, $position );
		add_menu_page($this->plugin_name, 'Woo IPOS', 'administrator', $this->plugin_name, array($this, 'displayPluginAdminDashboard'), 'dashicons-chart-area', 26);

		//add_submenu_page( '$parent_slug, $page_title, $menu_title, $capability, $menu_slug, $function );
		add_submenu_page($this->plugin_name, 'Woo IPOS Settings', 'Settings', 'administrator', $this->plugin_name . '-settings', array($this, 'displayPluginAdminSettings'));
	}

	public function displayPluginAdminDashboard()
	{
		require_once 'partials/' . $this->plugin_name . '-admin-display.php';
	}

	public function displayPluginAdminSettings()
	{
		// set this var to be used in the settings-display view
		$active_tab = isset($_GET['tab']) ? $_GET['tab'] : 'general';
		if (isset($_GET['error_message'])) {
			add_action('admin_notices', array($this, 'wooIposSettingsMessages'));
			do_action('admin_notices', $_GET['error_message']);
		}
		require_once 'partials/' . $this->plugin_name . '-admin-settings-display.php';
	}


	public function wooIposSettingsMessages($error_message)
	{
		switch ($error_message) {
			case '1':
				$message = __('There was an error adding this setting. Please try again.  If this persists, shoot us an email.', 'my-text-domain');
				$err_code = esc_attr('plugin_name_example_setting');
				$setting_field = 'plugin_name_example_setting';
				break;
		}
		$type = 'error';
		add_settings_error(
			$setting_field,
			$err_code,
			$message,
			$type
		);
	}


	// COMMON FUNCTION TO CREATE INPUT FIELD
	public function registerAndBuildFields()
	{
		/**
		 * First, we add_settings_section. This is necessary since all future settings must belong to one.
		 * Second, add_settings_field
		 * Third, register_setting
		 */
		add_settings_section(
			// ID used to identify this section and with which to register options
			'woo_ipos_general_section',
			// Title to be displayed on the administration page
			'',
			// Callback used to render the description of the section
			array($this, 'woo_ipos_display_general_account'),
			// Page on which to add this section of options
			'woo_ipos_general_settings'
		);
		unset($args);
		unset($callback_args);
		unset($pos_parent_args);
		unset($modify_registration_args);
		$args = array(
			'type'      => 'input',
			'subtype'   => 'text',
			'id'    => 'woo_ipos_api_key_setting',
			'name'      => 'woo_ipos_api_key_setting',
			'required' => 'true',
			'get_options_list' => '',
			'value_type' => 'normal',
			'wp_data' => 'option'
		);

		$pos_parent_args = array(
			'type'      => 'input',
			'subtype'   => 'text',
			'id'    => 'woo_ipos_pos_parent_setting',
			'name'      => 'woo_ipos_pos_parent_setting',
			'required' => 'true',
			'get_options_list' => '',
			'value_type' => 'normal',
			'wp_data' => 'option'
		);

		$modify_registration_args = array(
			'type' => 'input',
			'subtype' => 'checkbox',
			'id' => 'woo_ipos_modify_registration_setting',
			'name' => 'woo_ipos_modify_registration_setting',
			'required' => 'true',
			'get_options_list' => '',
			'value_type' => 'normal',
			'wp_data' => 'option'
		);

		add_settings_field(
			'woo_ipos_modify_registration_setting',
			'Modify Woocommerce Registration form to require phone number instead of email',
			array($this, 'woo_ipos_render_settings_field'),
			'woo_ipos_general_settings',
			'woo_ipos_general_section',
			$modify_registration_args
		);

		add_settings_field(
			'woo_ipos_pos_parent_setting',
			'POS Parent',
			array($this, 'woo_ipos_render_settings_field'),
			'woo_ipos_general_settings',
			'woo_ipos_general_section',
			$pos_parent_args
		);

		add_settings_field(
			'woo_ipos_api_key_setting',
			'API Key',
			array($this, 'woo_ipos_render_settings_field'),
			'woo_ipos_general_settings',
			'woo_ipos_general_section',
			$args
		);


		register_setting(
			'woo_ipos_general_settings',
			'woo_ipos_api_key_setting',
		);

		register_setting(
			'woo_ipos_general_settings',
			'woo_ipos_pos_parent_setting',
		);

		register_setting(
			'woo_ipos_general_settings',
			'woo_ipos_modify_registration_setting',
		);
	}

	public function woo_ipos_display_general_account()
	{
		echo '<p>These settings apply to all Woo IPOS functionality.</p>';
	}

	public function woo_ipos_render_settings_field($args)
	{
		/* EXAMPLE INPUT
							'type'      => 'input',
							'subtype'   => '',
							'id'    => $this->plugin_name.'_example_setting',
							'name'      => $this->plugin_name.'_example_setting',
							'required' => 'required="required"',
							'get_option_list' => "",
								'value_type' = serialized OR normal,
		'wp_data'=>(option or post_meta),
		'post_id' =>
		*/
		if ($args['wp_data'] == 'option') {
			$wp_data_value = get_option($args['name']);
		} elseif ($args['wp_data'] == 'post_meta') {
			$wp_data_value = get_post_meta($args['post_id'], $args['name'], true);
		}

		switch ($args['type']) {

			case 'input':
				$value = ($args['value_type'] == 'serialized') ? serialize($wp_data_value) : $wp_data_value;
				if ($args['subtype'] != 'checkbox') {
					$prependStart = (isset($args['prepend_value'])) ? '<div class="input-prepend"> <span class="add-on">' . $args['prepend_value'] . '</span>' : '';
					$prependEnd = (isset($args['prepend_value'])) ? '</div>' : '';
					$step = (isset($args['step'])) ? 'step="' . $args['step'] . '"' : '';
					$min = (isset($args['min'])) ? 'min="' . $args['min'] . '"' : '';
					$max = (isset($args['max'])) ? 'max="' . $args['max'] . '"' : '';
					if (isset($args['disabled'])) {
						// hide the actual input bc if it was just a disabled input the informaiton saved in the database would be wrong - bc it would pass empty values and wipe the actual information
						echo $prependStart . '<input type="' . $args['subtype'] . '" id="' . $args['id'] . '_disabled" ' . $step . ' ' . $max . ' ' . $min . ' name="' . $args['name'] . '_disabled" size="40" disabled value="' . esc_attr($value) . '" /><input type="hidden" id="' . $args['id'] . '" ' . $step . ' ' . $max . ' ' . $min . ' name="' . $args['name'] . '" size="40" value="' . esc_attr($value) . '" />' . $prependEnd;
					} else {
						echo $prependStart . '<input type="' . $args['subtype'] . '" id="' . $args['id'] . '" "' . $args['required'] . '" ' . $step . ' ' . $max . ' ' . $min . ' name="' . $args['name'] . '" size="40" value="' . esc_attr($value) . '" />' . $prependEnd;
					}
					/*<input required="required" '.$disabled.' type="number" step="any" id="'.$this->plugin_name.'_cost2" name="'.$this->plugin_name.'_cost2" value="' . esc_attr( $cost ) . '" size="25" /><input type="hidden" id="'.$this->plugin_name.'_cost" step="any" name="'.$this->plugin_name.'_cost" value="' . esc_attr( $cost ) . '" />*/
				} else {
					$checked = ($value) ? 'checked' : '';
					echo '<input type="' . $args['subtype'] . '" id="' . $args['id'] . '" "' . $args['required'] . '" name="' . $args['name'] . '" size="40" value="1" ' . $checked . ' />';
				}
				break;
			default:
				# code...
				break;
		}
	}
}
