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

    //return $response->data as json 
    $customer = $response->data;
    // $customer->name
    $html = "";
    error_log('--------CUSTOMER---------' . print_r($customer, true));
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
    $voucher_code = $voucher->voucher_code;
    //end date
    $voucher_date_end = $voucher->date_end;
    $voucher_date_end_text = date('d/m/Y', strtotime($voucher_date_end));
    //amount and percentage
    $discount_amount = $voucher->discount_amount;
    $discount_extra = $voucher->discount_extra;
    $discount_value = $discount_amount == '0' ? $discount_extra : $discount_amount;
    $voucher_discount_type = $discount_amount == '0' ? '%' : 'đ';
    $voucher_discount_value_text = number_format($discount_value, 0, ',', '.') . $voucher_discount_type;

    $discount_max_amount = $voucher->discount_max;
    $discount_max_type = $discount_amount == '0' ? 'đ' : '%';
    $voucher_discount_max_text = $discount_max_type == 'đ' ? number_format($discount_amount * 100, 0, ',', '.') : number_format($discount_max_amount, 0, ',', '.') . $discount_max_type;

    $order_over = $voucher->amount_order_over;
  ?>
    <div class="woo-ipos-voucher-item flex flex-row justify-around">
      <div class="woo-ipos-voucher-item-code-container flex flex-column">
        <div class="woo-ipos-voucher-item-code-label">Mã giảm giá: <?php echo $voucher_code ?></div>
        <div class="woo-ipos-voucher-item-code-label">HSD: <?php echo $voucher_date_end_text ?></div>
        <div class="woo-ipos-voucher-discount-description-value">Giảm giá: <?php echo $voucher_discount_value_text ?></div>
        <div class="woo-ipos-voucher-discount-description-value">Giảm tối đa: <?php echo $voucher_discount_max_text ?></div>
        <div class="woo-ipos-voucher-discount-description-value">Áp dụng cho đơn hàng trên: <?php echo number_format($order_over, 0, ',', '.') ?>đ</div>
      </div>
      <div class="woo-ipos-voucher-item-code-copy flex flex-column" onclick="copyToClipboard('<?php echo $voucher_code ?>')"><img width="16" height="16" src="https://img.icons8.com/ios/50/copy--v1.png" alt="copy--v1" /></div>


    </div>
    <style>
      .flex {
        display: flex;
      }

      .justify-around {
        justify-content: space-around;
      }

      .flex-row {
        flex-direction: row;
      }

      .flex-column {
        flex-direction: column;
      }

      .justify-center {
        justify-content: center;
      }

      .items-center {
        align-items: center;
      }
    </style>
    <script>

    </script>
<?php
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
    $html = "<div id=\"woo-ipos-voucher-container\">";
    foreach ($filteredData as $voucher) {
      $html .= $this->format_voucher_text($voucher);
    }
    $html .= "</div>";
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

  public function customer_import($data)
  {
    // create wordpress user here
    $all_data = $data["data"];
    error_log(print_r($all_data, true));
    foreach ($all_data as $customer) {

      $email = $customer["phone_number"] . '@gmail.com';
      $password = $customer["phone_number"];
      $username = $customer["phone_number"];
      $user_id = wp_create_user($username, $password, $email);
      // Check if user creation was successful
      if (!is_wp_error($user_id)) {
        // User created successfully, update the role of the user to customer
        $user = new WP_User($user_id);
        $user->set_role('customer');
        update_user_meta($user_id, 'billing_phone', $customer["phone_number"]);
        update_user_meta($user_id, 'shipping_phone', $customer["phone_number"]);
        update_user_meta($user_id, 'display_name', $customer["name"]);
        // return the data
        $customer['user_id'] = $user_id;
      } else {
        // Error creating user, skip
        continue;
      }
    }
  }
}
