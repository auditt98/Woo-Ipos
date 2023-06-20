<?php

trait PluginSettingTraits
{
  // ------ DEFINE SETTINGS AND FIELDS ------ //
  public function registerWebhook()
  {
    $this->createWebhook('/callback', 'woo_ipos_callback');
    $this->createWebhook('/reports', 'woo_ipos_report_callback');
    $this->createWebhook('/test', 'test');
    $this->createWebhook('/apply_voucher', 'apply_voucher');
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
    if (isset($data['create_customer']) && ($data['create_customer'] == 1)) {
      return $this->customer_import($data);
    }
  }


  public function createWebhook($route, $callback_name)
  {
    register_rest_route('/woo-ipos/v1', $route, array(
      'methods' => 'POST',
      'callback' => array($this, $callback_name),
    ));
  }

  //ADD A MENU TO THE ADMIN DASHBOARD
  public function addPluginAdminMenu()
  {
    //add_menu_page( $page_title, $menu_title, $capability, $menu_slug, $function, $icon_url, $position );
    add_menu_page($this->plugin_name, 'Woo IPOS', 'administrator', $this->plugin_name, array($this, 'displayPluginAdminDashboard'), 'dashicons-chart-area', 26);

    //add_submenu_page( '$parent_slug, $page_title, $menu_title, $capability, $menu_slug, $function );
    add_submenu_page($this->plugin_name, 'Woo IPOS Settings', 'Settings', 'administrator', $this->plugin_name . '-settings', array($this, 'displayPluginAdminSettings'));
  }

  // DISPLAY DASHBOARD PAGE
  public function displayPluginAdminDashboard()
  {
    require_once 'partials/' . $this->plugin_name . '-admin-display.php';
  }

  // DISPLAY SETTINGS PAGE
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

  public function woo_ipos_display_general_account()
  {
    echo '<p>These settings apply to all Woo IPOS functionality.</p>';
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

  public function woo_ipos_render_settings_field($args)
  {
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
}
