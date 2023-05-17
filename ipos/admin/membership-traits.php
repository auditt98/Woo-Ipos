<?php

trait MembershipTraits
{
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
      <label for="billing_phone_register"><?php _e('Phone Number', 'woocommerce'); ?> <span class="required">*</span></label>
    <div class="relative" style="margin-bottom: 17px">
      <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">
        <span class="text-gray-500 sm:text-sm">+84</span>
      </div>
      <input style="padding-left: 3rem; margin-bottom: 0px" type="text" pattern="(3|5|7|8|9])+([0-9]{8})\b" title="Vui lòng nhập đúng số điện thoại" class="woocommerce-Input woocommerce-Input--text input-text" required name="billing_phone_register" id="billing_phone_register" value="<?php if (!empty($_POST['billing_phone_register'])) esc_attr_e($_POST['billing_phone_register']); ?>" />
    </div>
    </p>

    <script>
      jQuery(document).ready(function($) {
        $('#reg_email').attr('type', 'hidden');
        $('label[for="reg_email"]').hide();
        $('#billing_phone_register').on('input', function() {
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

  public function customize_woo_login_form()
  {
  ?>
    <style>
      .woocommerce-form-login {
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
      <label for="billing_phone_login"><?php _e('Phone Number', 'woocommerce'); ?> <span class="required">*</span></label>
    <div class="relative" style="margin-bottom: 17px">
      <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">
        <span class="text-gray-500 sm:text-sm">+84</span>
      </div>
      <input style="padding-left: 3rem; margin-bottom: 0px" type="text" pattern="(3|5|7|8|9])+([0-9]{8})\b" title="Vui lòng nhập đúng số điện thoại" class="woocommerce-Input woocommerce-Input--text input-text" required name="billing_phone_login" id="billing_phone_login" value="<?php if (!empty($_POST['billing_phone_login'])) esc_attr_e($_POST['billing_phone_login']); ?>" />
    </div>
    </p>

    <script>
      jQuery(document).ready(function($) {
        $('#username').attr('type', 'hidden');
        $('label[for="username"]').hide();
        $('#billing_phone_login').on('input', function() {
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
          $('#username').val(email);
        });
      });
    </script>
<?php

  }

  function disable_email_validation($username, $email, $errors)
  {
    // Remove the email validation error
    if (isset($errors->errors['email'])) {
      unset($errors->errors['email']);
    }
  }

  // SHORTCODE FOR DISPLAYING CUSTOMER INFO
  public function display_customer_info()
  {
    $api_key = get_option('woo_ipos_api_key_setting');
    $pos_parent = get_option('woo_ipos_pos_parent_setting');
    $current_user = wp_get_current_user();
    $current_user_login = $current_user->user_login;

    $get_member_info_url = 'membership_detail';
    $get_member_info_method = 'GET';
    $query_params = array(
      'access_token' => $api_key,
      'pos_parent' => $pos_parent,
      'user_id' => $current_user_login
    );

    $response = $this->call_api($get_member_info_url, $get_member_info_method, array('Content-Type: application/json'), "", $query_params);

    error_log('--------RESPONSE---------' . print_r($response->data, true));
    //return $response->data as json 
    $customer = $response->data;
    $html = "
    <div id=\"woo-ipos-info-container\" class=\"woo-ipos-info-container\">
      <div id=\"woo-ipos-info-username-container\">
        <div id=\"woo-ipos-info-username-label\">Tài khoản</div>
        <div id=\"woo-ipos-info-username-value\">{$current_user_login}</div>
      </div>
      <div id=\"woo-ipos-info-customer-name-container\">
        <div id=\"woo-ipos-info-customer-name-label\">Tên khách hàng: </div>
        <div id=\"woo-ipos-info-customer-name-value\">{$customer->name}</div>
      </div>
      <div id=\"woo-ipos-info-birthday-container\">
        <div id=\"woo-ipos-info-birthday-label\">Ngày sinh: </div>
        <div id=\"woo-ipos-info-birthday-value\">{$customer->birthday}</div>
      </div>
      <div id=\"woo-ipos-info-membership-type-container\">
        <div id=\"woo-ipos-info-membership-type-label\">Loại thành viên: </div>
        <div id=\"woo-ipos-info-membership-type-value\">{$customer->membership_type_name}</div>
      </div>
    
      <div id=\"woo-ipos-info-point-container\">
        <div id=\"woo-ipos-info-point-label\">Điểm tích lũy: </div>
        <div id=\"woo-ipos-info-point-value\">{$customer->point}</div>
      </div>
    </div>
    ";
    return $html;
  }

  // SYNCING CREATED CUSTOMER TO IPOS
  public function sync_created_customer_to_ipos($customer_id, $new_customer_data, $password_generated)
  {
    $user = new WP_User($customer_id);
    $user_login = $user->get('user_login');
    //CALL API
    $api_key = get_option('woo_ipos_api_key_setting');
    $pos_parent = get_option('woo_ipos_pos_parent_setting');
    $add_membership_url = 'add_membership';
    $add_membership_method = 'POST';
    $json_body = json_encode(array('phone' => $user_login));
    $query_params = array(
      'access_token' => $api_key,
      'pos_parent' => $pos_parent
    );
    $response = $this->call_api($add_membership_url, $add_membership_method, array('Content-Type: application/json'), $json_body, $query_params);
    error_log('--------SYNC RESPONSE---------' . print_r($response, true));
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
}
