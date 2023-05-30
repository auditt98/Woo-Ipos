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

  function get_custom_css()
  {
    $css = '
  .card {
    background: #fff;
    border-radius: 4px;
    box-shadow: 0px 14px 80px rgba(34, 35, 58, 0.5);
    max-width: 400px;
    display: flex;
    flex-direction: row;
    border-radius: 25px;
    position: relative;
  }
  .card h2 {
    margin: 0;
    padding: 0 1rem;
  }
  .card .title {
    padding: 1rem;
    text-align: right;
    color: green;
    font-weight: bold;
    font-size: 12px;
  }
  .card .desc {
    padding: 0.5rem 1rem;
    font-size: 12px;
  }
  .card .actions {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    align-items: center;
    padding: 0.5rem 1rem;
  }
  .card svg {
    width: 85px;
    height: 85px;
    margin: 0 auto;
  }
  
  .img-avatar {
    width: 80px;
    height: 80px;
    position: absolute;
    border-radius: 50%;
    border: 6px solid white;
    background-image: linear-gradient(-60deg, #16a085 0%, #f4d03f 100%);
    top: 15px;
    left: 85px;
  }
  
  .card-text {
    display: grid;
    grid-template-columns: 1fr 2fr;
  }
  
  .title-total {
    padding: 2.5em 1.5em 1.5em 1.5em;
  }
  
  path {
    fill: white;
  }
  
  .img-portada {
    width: 100%;
  }
  
  .portada {
    width: 100%;
    height: 100%;
    border-top-left-radius: 20px;
    border-bottom-left-radius: 20px;
    background-image: url("https://m.media-amazon.com/images/S/aplus-media/vc/cab6b08a-dd8f-4534-b845-e33489e91240._CR75,0,300,300_PT0_SX300__.jpg");
    background-position: bottom center;
    background-size: cover;
  }
  
  button {
    border: none;
    background: none;
    font-size: 24px;
    color: #8bc34a;
    cursor: pointer;
    transition: 0.5s;
  }
  button:hover {
    color: #4caf50;
    transform: rotate(22deg);
  }
  woo-ipos-info-username-container{
    display: flex;
  }
  
  ';

    return $css;
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
    // $customer->name
    $html = "";
    if (is_user_logged_in()) {
      $customer_name = !empty($customer->name) ? $customer->name : "Chưa có thông tin";
      $customer_membership_type = !empty($customer->membership_type_name) ? $customer->membership_type_name : "Chưa có thông tin";
      $customer_point = $customer->point;
      $customer_birthday = !empty($customer->birthday) ? $this->convert_date_format($customer->birthday) : "Chưa có thông tin";
      $html =
        "<table class=\"user-table\">"
        .
        "<tr>
          <td>Họ tên</td>
          <td>$customer_name</td>
        </tr>"
        .
        "<tr>
          <td>Số điện thoại</td>

          <td>$current_user_login</td>
        </tr>"
        .
        "<tr>
          <td>Loại hội viên</td>
          <td>$customer_membership_type</td>
        </tr>"
        .
        "<tr>
          <td>Điểm thành viên</td>
          <td>$customer_point</td>
        </tr>"
        .
        "<tr>
          <td>Ngày sinh</td>
          <td>$customer_birthday</td>
        </tr>"
        . "</table>";
    }

  ?>
    <style>
      .user-table {
        border-collapse: collapse;
        width: 100%;
        border: 1px solid #f5f5f5;
      }

      .user-table td {
        padding: 10px !important;
        border: 1px solid #e2e2e2;
      }

      .user-table tr {
        background-color: white;
      }

      .user-table tr:hover {
        background-color: #e2e2e2;
      }
    </style>
<?php
    return $html;
  }

  public function format_voucher_text($voucher)
  {
    $voucher_code = $voucher->code;
    $voucher_discount_description = $voucher->discount_description;
    $voucher_date_end = $voucher->date_end;
    $voucher_discount_value = $voucher->discount_value;
    $voucher_discount_type = $voucher->discount_type;
    $voucher_discount_type_text = $voucher_discount_type == 'percent' ? '%' : 'đ';
    $voucher_discount_value_text = $voucher_discount_type == 'percent' ? $voucher_discount_value : number_format($voucher_discount_value, 0, ',', '.') . 'đ';
    $voucher_date_end_text = date('d/m/Y', strtotime($voucher_date_end));
    $voucher_code_text = $voucher_code;
    $voucher_discount_description_text = $voucher_discount_description;
    $html = "
    <div id=\"woo-ipos-voucher-container\">
      <div class=\"woo-ipos-voucher-item\">
        <div class=\"woo-ipos-voucher-item-code-container\">
          <div class=\"woo-ipos-voucher-item-code-label\">Mã giảm giá</div>
          <div class=\"woo-ipos-voucher-item-code-value\">{$voucher_code_text}</div>
          <div class=\"woo-ipos-voucher-item-code-copy\">Sao chép</div>
        </div>
        <div class=\"woo-ipos-voucher-date-end\">HSD: {$voucher_date_end_text}</div>
        <div class=\"woo-ipos-voucher-discount-info-container\">
          <div class=\"woo-ipos-voucher-discount-description-container\">
            <div class=\"woo-ipos-voucher-discount-description-label\">{$voucher_discount_description_text}</div>
            <div class=\"woo-ipos-voucher-discount-description-value\">{$voucher_discount_value_text}{$voucher_discount_type_text}</div>
          </div>
        </div>
      </div>
    </div>
    ";
    return $html;
  }

  //SHORTCODE FOR DISPLAYING VOUCHERS
  public function display_vouchers_info()
  {
    $api_key = get_option('woo_ipos_api_key_setting');
    $pos_parent = get_option('woo_ipos_pos_parent_setting');
    $current_user = wp_get_current_user();
    $current_user_login = $current_user->user_login;

    $get_member_vouchers_url = 'member_vouchers';
    $get_member_vouchers_method = 'GET';

    $query_params = array(
      'access_token' => $api_key,
      'pos_parent' => $pos_parent,
      'user_id' => $current_user_login
    );

    $response = $this->call_api($get_member_vouchers_url, $get_member_vouchers_method, array('Content-Type: application/json'), "", $query_params);
    $data = $response->data;
    return json_encode($response->data);
    $currentDate = new DateTime();
    $filteredData = array_filter($data, function ($item) use ($currentDate) {
      $endDate = new DateTime($item->date_end);
      return $endDate >= $currentDate; // Keep items with a date_end value greater than or equal to the current date
    });

    // Sort the filtered data based on the closest expiry date
    usort($filteredData, function ($a, $b) {
      $endDateA = new DateTime($a->date_end);
      $endDateB = new DateTime($b->date_end);
      return $endDateA <=> $endDateB; // Compare the expiry dates
    });
    error_log('--------RESPONSE---------' . print_r($response->data, true));
    $html = "<div id=\"woo-ipos-voucher-container\">
      <div class=\"woo-ipos-voucher-item\">
        <div class=\"woo-ipos-voucher-item-code-container\">
          <div class=\"woo-ipos-voucher-item-code-label\"></div>
          <div class=\"woo-ipos-voucher-item-code-value\"></div>
          <div class=\"woo-ipos-voucher-item-code-copy\"></div>
        </div>
        <div class=\"woo-ipos-voucher-date-end\"></div>
        <div class=\"woo-ipos-voucher-discount-info-container\">
          <div class=\"woo-ipos-voucher-discount-description-container\">
            <div class=\"woo-ipos-voucher-discount-description-label\"></div>
            <div class=\"woo-ipos-voucher-discount-description-value\"></div>
          </div>
        </div>
      </div>
    </div>";
    return json_encode($filteredData);
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
