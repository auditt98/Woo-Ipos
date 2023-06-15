<?php

trait CommonTraits
{
  //---------- COMMON FUNCTIONS ----------//

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

    $ga_project_id_args = array(
      'type' => 'input',
      'subtype' => 'text',
      'id' => 'woo_ipos_ga_project_id_setting',
      'name' => 'woo_ipos_ga_project_id_setting',
      'required' => 'true',
      'get_options_list' => '',
      'value_type' => 'normal',
      'wp_data' => 'option'
    );

    $ga_client_x509_args = array(
      'type' => 'input',
      'subtype' => 'text',
      'id' => 'woo_ipos_ga_client_x509_setting',
      'name' => 'woo_ipos_ga_client_x509_setting',
      'required' => 'true',
      'get_options_list' => '',
      'value_type' => 'normal',
      'wp_data' => 'option'
    );

    $ga_private_key_id_args = array(
      'type' => 'input',
      'subtype' => 'text',
      'id' => 'woo_ipos_ga_private_key_id_setting',
      'name' => 'woo_ipos_ga_private_key_id_setting',
      'required' => 'true',
      'get_options_list' => '',
      'value_type' => 'normal',
      'wp_data' => 'option'
    );

    $ga_private_key_args = array(
      'type' => 'input',
      'subtype' => 'text',
      'id' => 'woo_ipos_ga_private_key_setting',
      'name' => 'woo_ipos_ga_private_key_setting',
      'required' => 'true',
      'get_options_list' => '',
      'value_type' => 'normal',
      'wp_data' => 'option'
    );

    $ga_client_email_args = array(
      'type' => 'input',
      'subtype' => 'text',
      'id' => 'woo_ipos_ga_client_email_setting',
      'name' => 'woo_ipos_ga_client_email_setting',
      'required' => 'true',
      'get_options_list' => '',
      'value_type' => 'normal',
      'wp_data' => 'option'
    );
    
    $ga_client_id_args = array(
      'type' => 'input',
      'subtype' => 'text',
      'id' => 'woo_ipos_ga_client_id_setting',
      'name' => 'woo_ipos_ga_client_id_setting',
      'required' => 'true',
      'get_options_list' => '',
      'value_type' => 'normal',
      'wp_data' => 'option'
    );

    $ga_property_id_args = array(
      'type' => 'input',
      'subtype' => 'text',
      'id' => 'woo_ipos_ga_property_id_setting',
      'name' => 'woo_ipos_ga_property_id_setting',
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

    add_settings_field(
      'woo_ipos_ga_project_id_setting',
      'GA Project ID',
      array($this, 'woo_ipos_render_settings_field'),
      'woo_ipos_general_settings',
      'woo_ipos_general_section',
      $ga_project_id_args
    );
    add_settings_field(
      'woo_ipos_ga_client_x509_setting',
      'GA Client_x509 Cert URL',
      array($this, 'woo_ipos_render_settings_field'),
      'woo_ipos_general_settings',
      'woo_ipos_general_section',
      $ga_client_x509_args
    );

    add_settings_field(
      'woo_ipos_ga_private_key_id_setting',
      'GA Private Key ID',
      array($this, 'woo_ipos_render_settings_field'),
      'woo_ipos_general_settings',
      'woo_ipos_general_section',
      $ga_private_key_id_args
    );

    add_settings_field(
      'woo_ipos_ga_private_key_setting',
      'GA Private Key',
      array($this, 'woo_ipos_render_settings_field'),
      'woo_ipos_general_settings',
      'woo_ipos_general_section',
      $ga_private_key_args
    );

    add_settings_field(
      'woo_ipos_ga_client_email_setting',
      'GA Client Email',
      array($this, 'woo_ipos_render_settings_field'),
      'woo_ipos_general_settings',
      'woo_ipos_general_section',
      $ga_client_email_args
    );
    
    add_settings_field(
      'woo_ipos_ga_client_id_setting',
      'GA Client Id',
      array($this, 'woo_ipos_render_settings_field'),
      'woo_ipos_general_settings',
      'woo_ipos_general_section',
      $ga_client_id_args
    );

    add_settings_field(
      'woo_ipos_ga_property_id_setting',
      'GA Property ID',
      array($this, 'woo_ipos_render_settings_field'),
      'woo_ipos_general_settings',
      'woo_ipos_general_section',
      $ga_property_id_args
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
    
    register_setting(
      'woo_ipos_general_settings',
      'woo_ipos_ga_project_id_setting',
    );   

    register_setting(
      'woo_ipos_general_settings',
      'woo_ipos_ga_client_x509_setting',
    );

    register_setting(
      'woo_ipos_general_settings',
      'woo_ipos_ga_private_key_id_setting',
    );
    
    register_setting(
      'woo_ipos_general_settings',
      'woo_ipos_ga_private_key_setting',
    );
    
    register_setting(
      'woo_ipos_general_settings',
      'woo_ipos_ga_client_email_setting',
    );
    
    register_setting(
      'woo_ipos_general_settings',
      'woo_ipos_ga_client_id_setting',
    );

    register_setting(
      'woo_ipos_general_settings',
      'woo_ipos_ga_property_id_setting',
    );
    
  }

  // COMMON FUNCTION TO CALL API
  public function call_api($url, $method = 'GET', $headers = array(), $body = '', $query_params = array())
  {
    $base_url = 'https://api.foodbook.vn/ipos/ws/xpartner/';

    // Combine the URL and query parameters
    $url_with_params = add_query_arg($query_params, $base_url . $url);

    $args = array(
      'method'  => $method,
      'headers' => $headers,
      'body'    => $body,
    );

    $response = wp_remote_request($url_with_params, $args);

    if (is_wp_error($response)) {
      // Handle error
      return false;
    }

    $response_code = wp_remote_retrieve_response_code($response);
    $response_body = wp_remote_retrieve_body($response);

    // Handle the API response as needed
    // For example, you can decode JSON response using:
    $decoded_response = json_decode($response_body);

    return $decoded_response;
  }

  function convert_date_format($dateString)
  {
    $dateTime = DateTime::createFromFormat('Y-m-d H:i:s', $dateString);
    return $dateTime->format('d/m/Y');
  }
}
