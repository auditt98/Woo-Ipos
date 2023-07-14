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

  function profile_update_form_shortcode()
  {
    ob_start();

    $current_user = wp_get_current_user();
  ?>
    <form id="profile-edit-form" method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>">
      <input type="hidden" name="action" value="update_user_profile">
      <input type="hidden" name="user_id" value="<?php echo esc_attr($current_user->ID); ?>">

      <label for="user_email">Email:</label>
      <input type="email" name="user_email" id="user_email" value="<?php echo esc_attr($current_user->user_email); ?>">

      <label for="user_password">Mật khẩu:</label>
      <input type="password" name="user_password" id="user_password">

      <input type="submit" value="Update Profile">
      <?php wp_nonce_field('update_user_profile', 'update_user_profile_nonce'); ?>
    </form>
  <?php

    return ob_get_clean();
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
          var email = '84' + sanitizedPhone;
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

  public function get_ipos_user()
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
    $customer = $response->data;
    return $customer;
  }

  public function get_membership_types()
  {
    $api_key = get_option('woo_ipos_api_key_setting');
    $pos_parent = get_option('woo_ipos_pos_parent_setting');

    $get_membership_type_url = 'get_membership_types';
    $get_membership_type_method = 'GET';
    $query_params = array(
      'access_token' => $api_key,
      'pos_parent' => $pos_parent
    );

    $response = $this->call_api($get_membership_type_url, $get_membership_type_method, array('Content-Type: application/json'), "", $query_params);
    $memberships = $response->data;
    return $memberships;
  }


  // SHORTCODE FOR DISPLAYING CUSTOMER INFO
  public function display_customer_info() // thong tin tai khoan
  {
    if (!is_user_logged_in()) {
      return;
    }
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
    // $html = "";
    $customer_name = "";
    $customer_membership_type = "";
    $customer_membership_type_id = "";
    $customer_point = "";
    $customer_birthday = "";
    $membership_types = array();
    $membership_index = -1;
    $next_membership = 0;
    $points_required = -1;
    $points_remaining = -1;
    $vouchers = array();
    $valid_vouchers = array();
    $expired_vouchers = array();

    if (is_user_logged_in()) {
      $customer_name = !empty($customer->name) ? $customer->name : "Chưa có thông tin";
      $customer_membership_type = !empty($customer->membership_type_name) ? $customer->membership_type_name : "Chưa có thông tin";
      $customer_point = floor($customer->point);
      $customer_birthday = !empty($customer->birthday) ? $this->convert_date_format($customer->birthday) : "Chưa có thông tin";

      $memberships = $this->get_membership_types();
      $filtered_types = array_filter($memberships, function ($type) {
        return $type->active == 1;
      });
      $membership_types = $filtered_types;
      usort($membership_types, function ($a, $b) {
        return $a->upgrade_amount - $b->upgrade_amount;
      });

      $customer_membership_type_id = $customer->membership_type_id;
      //find index of membership_type_id in membership_types array (type_id)
      $index = array_search($customer_membership_type_id, array_column($membership_types, 'type_id'));
      $membership_index = $index !== false ? $index : -1;
      //get the next membership type, if not found, return the last one
      $next_membership = $membership_index !== -1 && $membership_index < count($membership_types) ? $membership_types[$membership_index + 1] : 0;
      if ($next_membership != 0) {
        $points_required = $next_membership->upgrade_amount * $next_membership->point_rate;
        $points_required = ceil($points_required);
        $points_remaining = $points_required - $customer_point;
      }
      $vouchers = $this->get_vouchers();

      $currentDate = new DateTime(); // Get the current date

      $valid_vouchers = array_filter($vouchers, function ($voucher) use ($currentDate) {
        $endDate = new DateTime($voucher->date_end);
        return $endDate >= $currentDate;
      });

      $expired_vouchers = array_filter($vouchers, function ($voucher) use ($currentDate) {
        $endDate = new DateTime($voucher->date_end);
        return $endDate < $currentDate;
      });
    }

  ?>
    <div class="membership_tab">
      <div class="membership_top" id="membership_top_id">
        <div>
          <div class="membership_top--left">
            <div>hạng</div>
            <div><?php echo $customer_membership_type ?></div>
          </div>
          <div class="membership_top--right"><?php echo ceil($customer_point) ?> pon</div>
        </div>

        <div class="progress-bar">
          <div class="progress-bar_remaining">
            <p>Còn <span id="progress_remaining"><?php echo $points_remaining ?></span> pon để thăng hạng</p>
          </div>
          <div class="progress-bar_current">
            <div class="range-slider">
              <div id="tooltip"></div>
              <input id="range" type="range" step="1" value="<?php echo floor(($customer_point / $points_required) * 100) ?>" min="0" max="100" disabled>
            </div>
          </div>
        </div>
      </div>

      <div class="membership_content">
        <div>Ưu đãi thành viên</div>
        <div class="voucher_grid">
          <?php foreach ($valid_vouchers as $voucher) : ?>
            <div class="voucher valid">
              <div class="voucher_icon valid_c">
                <img src="https://cestsibon.sgp1.digitaloceanspaces.com/wp-content/uploads/2023/07/13022210/tag-2.png" />
              </div>
              <div class="voucher_content">
                <div><?php echo $voucher->voucher_label; ?></div>
                <div><?php echo $voucher->voucher_desc; ?></div>
                <div>HSD: <?php echo $voucher->voucher_display_expiry; ?></div>
              </div>
            </div>
          <?php endforeach; ?>

          <?php foreach ($expired_vouchers as $voucher) : ?>
            <div class="voucher outdate">
              <div class="voucher_icon outdate_c">
                <img src="https://cestsibon.sgp1.digitaloceanspaces.com/wp-content/uploads/2023/07/13022210/tag-2.png" />
              </div>
              <div class="voucher_content">
                <div><?php echo $voucher->voucher_label; ?></div>
                <div><?php echo $voucher->voucher_desc; ?></div>
                <div>HSD: <?php echo $voucher->voucher_display_expiry; ?></div>
              </div>
            </div>
          <?php endforeach; ?>


        </div>
      </div>
      <div class="tabs membership_content">
        <div id="tab-pon" class="tab-pon">
          <div class="tab-title">Ưu đãi Hạng PON</div>
          <div class="voucher_grid">
            <div class="voucher tobe_valid">
              <div class="voucher_icon valid_c">
                <img src="https://cestsibon.sgp1.digitaloceanspaces.com/wp-content/uploads/2023/07/13022210/tag-2.png" />
              </div>
              <div class="voucher_content">
                <div>Tặng 01 đồ uống bất kỳ</div>
                <div>khi tích đủ 20 PON</div>
              </div>
            </div>
            <div class="voucher tobe_valid">
              <div class="voucher_icon valid_c">
                <img src="https://cestsibon.sgp1.digitaloceanspaces.com/wp-content/uploads/2023/07/13022210/tag-2.png" />
              </div>
              <div class="voucher_content">
                <div>Tặng 01 slice mille crepes bất kỳ</div>
                <div>khi tích đủ 40 PON</div>
              </div>
            </div>

            <div class="voucher tobe_valid">
              <div class="voucher_icon valid_c">
                <img src="https://cestsibon.sgp1.digitaloceanspaces.com/wp-content/uploads/2023/07/13022210/tag-2.png" />
              </div>
              <div class="voucher_content">
                <div>Tặng 01 đồ uống bất kỳ</div>
                <div>khi tích đủ 60 PON</div>
              </div>
            </div>

            <div class="voucher tobe_valid">
              <div class="voucher_icon valid_c">
                <img src="https://cestsibon.sgp1.digitaloceanspaces.com/wp-content/uploads/2023/07/13022210/tag-2.png" />
              </div>
              <div class="voucher_content">
                <div>Tặng 01 entremet bất kỳ</div>
                <div>khi tích đủ 80 PON</div>
              </div>
            </div>

            <div class="voucher tobe_valid">
              <div class="voucher_icon valid_c">
                <img src="https://cestsibon.sgp1.digitaloceanspaces.com/wp-content/uploads/2023/07/13022210/tag-2.png" />
              </div>
              <div class="voucher_content">
                <div>Quà sinh nhật: Giảm 10% bánh whole-cake</div>
                <div></div>
              </div>
            </div>
          </div>

          <div class="tab-title">Điều kiện hạng thành viên PON</div>
          <div class="voucher_grid">
            <div class="voucher tobe_valid">
              <div class="voucher_icon valid_c">
                <img src="https://cestsibon.sgp1.digitaloceanspaces.com/wp-content/uploads/2023/07/13022210/tag-2.png" />
              </div>
              <div class="voucher_content">
                <div>Áp dụng ngay sau khi đăng ký và bắt đầu tích điểm</div>
                <div></div>
              </div>
            </div>
            <div class="voucher tobe_valid">
              <div class="voucher_icon valid_c">
                <img src="https://cestsibon.sgp1.digitaloceanspaces.com/wp-content/uploads/2023/07/13022210/tag-2.png" />
              </div>
              <div class="voucher_content">
                <div>Tỷ lệ tích điểm: 50.000đ tiêu dùng tích được 1 PON</div>
                <div>(1 PON = 1.000đ)</div>
              </div>
            </div>
          </div>
        </div>
        <div id="tab-pon-fr" class="tab-pon-and-friend hidden">
          <div class="tab-title">Ưu đãi Hạng PON & Friends</div>
          <div class="voucher_grid">
            <div class="voucher tobe_valid">
              <div class="voucher_icon valid_c">
                <img src="https://cestsibon.sgp1.digitaloceanspaces.com/wp-content/uploads/2023/07/13022210/tag-2.png" />
              </div>
              <div class="voucher_content">
                <div>Tặng 01 Pon Bag</div>
                <div>ngay khi thăng hạng</div>
              </div>
            </div>
            <div class="voucher tobe_valid">
              <div class="voucher_icon valid_c">
                <img src="https://cestsibon.sgp1.digitaloceanspaces.com/wp-content/uploads/2023/07/13022210/tag-2.png" />
              </div>
              <div class="voucher_content">
                <div>Tặng 01 đồ uống bất kỳ</div>
                <div>khi tích đủ 40 PON</div>
              </div>
            </div>
            <div class="voucher tobe_valid">
              <div class="voucher_icon valid_c">
                <img src="https://cestsibon.sgp1.digitaloceanspaces.com/wp-content/uploads/2023/07/13022210/tag-2.png" />
              </div>
              <div class="voucher_content">
                <div>Giảm 10% cho mọi sản phẩm Grab & Go và Merchandise</div>
                <div>khi tích đủ 60 PON</div>
              </div>
            </div>
            <div class="voucher tobe_valid">
              <div class="voucher_icon valid_c">
                <img src="https://cestsibon.sgp1.digitaloceanspaces.com/wp-content/uploads/2023/07/13022210/tag-2.png" />
              </div>
              <div class="voucher_content">
                <div>Tặng 01 entremet bất kỳ</div>
                <div>khi tích đủ 80 PON</div>
              </div>
            </div>
            <div class="voucher tobe_valid">
              <div class="voucher_icon valid_c">
                <img src="https://cestsibon.sgp1.digitaloceanspaces.com/wp-content/uploads/2023/07/13022210/tag-2.png" />
              </div>
              <div class="voucher_content">
                <div>Miễn phí vận chuyển cho đơn hàng mua bánh whole-cake</div>
                <div></div>
              </div>
            </div>
          </div>
          <div class="tab-title">Điều kiện hạng thành viên PON & Friends</div>
          <div class="voucher_grid">
            <div class="voucher tobe_valid">
              <div class="voucher_icon valid_c">
                <img src="https://cestsibon.sgp1.digitaloceanspaces.com/wp-content/uploads/2023/07/13022210/tag-2.png" />
              </div>
              <div class="voucher_content">
                <div>Áp dụng ngay sau khi đăng ký và bắt đầu tích điểm</div>
                <div></div>
              </div>
            </div>
            <div class="voucher tobe_valid">
              <div class="voucher_icon valid_c">
                <img src="https://cestsibon.sgp1.digitaloceanspaces.com/wp-content/uploads/2023/07/13022210/tag-2.png" />
              </div>
              <div class="voucher_content">
                <div>Tỷ lệ tích điểm: 50.000đ tiêu dùng tích được 1 PON</div>
                <div>(1 PON = 1.000đ)</div>
              </div>
            </div>
          </div>
        </div>
        <div class="tab-display">
          <div class="tab-dot active" id="tab-dot-pon" onclick="switchTab('tab-pon')"></div>
          <div class="tab-dot" id="tab-dot-pon-fr" onclick="switchTab('tab-pon-fr')"></div>
        </div>
      </div>
    </div>

    <style>
      .hidden {
        display: none;
      }

      .tab-display {
        display: flex;
        justify-content: center;
        margin-top: 20px;
      }

      .tab-title {
        font-weight: 700;
        font-size: 24px;
        margin-bottom: 16px;
        color: #434343;
        margin-top: 20px;
      }

      .tab-dot {
        width: 10px;
        height: 10px;
        border-radius: 50%;
        background-color: white;
        margin: 0 10px;
        cursor: pointer;
      }

      .tab-dot.active {
        background-color: #53648a;
      }

      /* media for smartphones */
      @media (max-width: 424px) {
        .voucher_grid {
          display: grid;
          grid-template-columns: unset !important;
          grid-gap: 16px;
        }
      }

      #progress_remaining {
        font-size: 15px;
      }

      .membership_top {
        background-color: #53648a;
        color: white;
        padding: 16px;
        font-weight: 700;
        font-size: 17px;
        height: 140px;
        transition: height .5s;
        margin-bottom: 20px;
      }

      .membership_top>div:first-child {
        display: flex;
        justify-content: space-between;
        text-transform: uppercase;
      }

      .membership_top--left>div:last-child {
        font-size: 40px;
        margin-top: 6px;
      }

      .membership_detail {
        display: flex;
        align-items: center;
        flex-direction: column;
      }

      .membership_detail>div:first-child {
        cursor: pointer;
        border: 2px solid white;
        padding: 8px 16px;
        border-radius: 8px;
        margin-bottom: 12px;
      }

      .membership_detail--table {
        transition: opacity 1.5s ease-in-out;
        opacity: 0;
        height: 0;
        overflow: hidden;
        width: 100%;
      }

      .active_table {
        opacity: 1;
        height: 100%;
      }

      .extend_height {
        height: 400px;
      }

      table {
        width: 100%;
      }

      td,
      th {
        border: 1px solid #dddddd;
        text-align: left;
        padding: 8px;
      }

      tr:nth-child(even) {
        background-color: #dddddd;
        color: black;
      }

      tr:nth-child(odd) {
        background-color: #a9a9a9;
      }

      .membership_content {
        padding: 24px;
        background-color: #f3f3f3;
        font-family: "DVNPoppins" !important;
      }

      .membership_content>div:first-child {
        font-weight: 700;
        font-size: 24px;
        margin-bottom: 16px;
        color: #434343;
      }

      .voucher_grid {
        display: grid;
        grid-template-columns: 50% 50%;
        grid-gap: 16px;
      }

      .voucher {
        display: flex;
        padding: 16px 0 16px 12px;
        border-radius: 12px;
        align-items: center;
        cursor: default;
      }

      .voucher_icon>img {
        width: 26px;
        margin-right: 14px;
        border-radius: 50%;
      }

      .voucher_content>div:first-child {
        font-size: 18px;
        font-weight: 700;
      }

      .voucher_content>div:last-child {
        font-size: 14px;
      }

      .valid {
        background-color: #53648a;
        color: white;
        transition: all 0.5s ease-in-out;
      }

      .valid:hover {
        box-shadow: rgba(17, 12, 46, 0.15) 0px 48px 100px 0px;
        cursor: pointer;
        transform: scale(1.1);
      }

      .tobe_valid {
        background-color: #f2f2ec;
        border-color: #53648a;
        color: #53648a;
        border: 2px solid #53648a;
      }

      .outdate {
        color: #53648a;
        background-color: #f2f2ec;
        border: 2px solid #53648a;
        opacity: 0.3;
      }

      .outdate_c>img {
        border: 2px solid #53648a;
      }

      .progress-bar_remaining {
        display: flex;
        justify-content: flex-end;
        font-size: 13px;
      }

      .progress-bar_current {
        position: relative;
      }

      /* range slider */
      .range-slider {
        width: 100%;
        /* margin: 0 auto; */
        position: relative;
        /* margin-top: 2.5rem; */
        /* margin-bottom: 2rem; */
      }

      #range {
        -webkit-appearance: none;
        width: 100%;
        border-radius: 1rem;
      }

      #range:focus {
        outline: none;
      }

      #range::before {
        left: 0;
        content: attr(data-min);
      }

      #range::after {
        right: 0;
        content: attr(data-max);
      }

      #range::-webkit-slider-runnable-track {
        width: 100%;
        height: 1rem;
        cursor: pointer;
        animate: 0.2s;
        background: linear-gradient(90deg,
            #d3c3b1 var(--range-progress),
            #dee4ec var(--range-progress));
        border-radius: 1rem;
      }

      #range::-webkit-slider-thumb {
        -webkit-appearance: none;
        border: 0.25rem solid #fff;
        box-shadow: 0 1px 3px rgba(0, 0, 255, 0.3);
        border-radius: 50%;
        background: #d3c3b1;
        cursor: pointer;
        height: 38px;
        width: 38px;
        transform: translateY(calc(-50% + 8px));
      }

      #tooltip {
        position: absolute;
        top: -0.125rem;
        z-index: 1;
      }

      #tooltip span {
        position: absolute;
        pointer-events: none;
        text-align: center;
        display: block;
        line-height: 1;
        padding: 0.25rem 0.25rem;
        margin-left: 0.225rem;
        color: #fff;
        font-size: 1rem;
        left: 50%;
        transform: translate(-50%, 0);
      }

      #tooltip span:before {
        position: absolute;
        content: '';
        left: 50%;
        bottom: -8px;
        transform: translateX(-50%);
        width: 0;
        height: 0;
        border: 4px solid transparent;
        border-top-color: #d3c3b1;
      }
    </style>

    <script>
      const range = document.getElementById('range'),
        tooltip = document.getElementById('tooltip'),
        setValue = () => {
          const newValue = Number(
              ((range.value - range.min) * 100) / (range.max - range.min)
            ),
            newPosition = 16 - newValue * 0.38;
          tooltip.innerHTML = `<span>${range.value}</span>`;
          tooltip.style.left = `calc(${newValue}% + (${newPosition}px))`;
          document.documentElement.style.setProperty(
            '--range-progress',
            `calc(${newValue}% + (${newPosition}px))`
          );
        };
      document.addEventListener('DOMContentLoaded', setValue);
      range.addEventListener('input', setValue);


      function toggleTable() {
        const table = document.getElementById('membership_table');
        const topId = document.getElementById('membership_top_id');

        if (table.classList.contains('active_table')) {
          table.classList.remove('active_table');
          topId.classList.remove('extend_height');
        } else {
          table.classList.add('active_table');
          topId.classList.add('extend_height');
        }
      }

      function switchTab(id) {
        const tabPon = document.getElementById('tab-pon');
        const tabPonFr = document.getElementById('tab-pon-fr');
        if (id == 'tab-pon') {
          tabPon.classList.remove('hidden');
          tabPonFr.classList.add('hidden');
          document.getElementById('tab-dot-pon').classList.add('active');
          document.getElementById('tab-dot-pon-fr').classList.remove('active');
        } else {
          tabPon.classList.add('hidden');
          tabPonFr.classList.remove('hidden');
          document.getElementById('tab-dot-pon').classList.remove('active');
          document.getElementById('tab-dot-pon-fr').classList.add('active');
        }

      }

      function switchTcTab(id) {
        const tabTcPon = document.getElementById('tab-tc-pon');
        const tabTcPonFr = document.getElementById('tab-tc-pon-fr');
        if (id == 'tab-tc-pon') {
          tabTcPon.classList.remove('hidden');
          tabTcPonFr.classList.add('hidden');
          document.getElementById('tab-tc-dot-pon').classList.add('active');
          document.getElementById('tab-tc-dot-pon-fr').classList.remove('active');
        } else {
          tabTcPon.classList.add('hidden');
          tabTcPonFr.classList.remove('hidden');
          document.getElementById('tab-tc-dot-pon').classList.remove('active');
          document.getElementById('tab-tc-dot-pon-fr').classList.add('active');
        }
      }
    </script>

  <?php
    // return $html;
  }

  public function format_voucher_text($voucher)
  {
    $voucher_code = $voucher->voucher_code;
    $voucher_campaign = $voucher->voucher_campaign_name;
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

    $voucher_discount_max_text = '';
    if ($discount_max_type == 'đ') {
      $voucher_discount_max_text = number_format($discount_max_amount, 0, ',', '.') . $discount_max_type;
    } else {
      $voucher_discount_max_text = number_format($discount_max_amount  * 100, 0, ',', '.') . $discount_max_type;
    }

    $order_over = $voucher->amount_order_over;
  ?>
    <div class="woo-ipos-voucher-item flex flex-row justify-around" style="">
      <div class="woo-ipos-voucher-item-code-container flex flex-column">
        <div class="woo-ipos-voucher-item-code-label"><?php echo $voucher_campaign ?></div>
        <div class="woo-ipos-voucher-item-code-label">Mã giảm giá: <?php echo $voucher_code ?></div>
        <div class="woo-ipos-voucher-item-code-label">HSD: <?php echo $voucher_date_end_text ?></div>
        <!-- <div class="woo-ipos-voucher-discount-description-value">Giảm giá: <?php echo $voucher_discount_value_text ?></div>
        <div class="woo-ipos-voucher-discount-description-value">Giảm tối đa: <?php echo $voucher_discount_max_text ?></div>
        <div class="woo-ipos-voucher-discount-description-value">Áp dụng cho đơn hàng trên: <?php echo number_format($order_over, 0, ',', '.') ?>đ</div> -->
      </div>
      <div class="woo-ipos-voucher-item-code-copy flex flex-column justify-center" onclick="copyToClipboard('<?php echo $voucher_code ?>')">
        <div class="flex flex-row items-center justify-center gap-10px"><span>Sao chép</span> <img width="18" height="18" style="" src="https://img.icons8.com/ios/50/copy--v1.png" alt="copy--v1" /></div>
      </div>

    </div>
    <style>
      .gap-10px {
        gap: 10px;
      }

      .woo-ipos-voucher-item {
        box-shadow: rgba(149, 157, 165, 0.2) 0px 8px 24px;
        padding-top: 10px;
        padding-bottom: 10px;
        margin-bottom: 50px;
        transition: all 0.3s ease-in-out;
      }

      .woo-ipos-voucher-item:hover {
        box-shadow: rgba(99, 99, 99, 0.2) 0px 2px 8px 0px;
      }

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

  public function get_vouchers()
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
    if (is_null($data)) {
      $data = array();
    }
    foreach ($data as $voucher) {
      $campaignName = $voucher->voucher_campaign_name;
      $voucher->voucher_label = $campaignName;
      $voucher->voucher_display_expiry = date('d/m/Y', strtotime($voucher->date_end));
      $campaign_id = $voucher->voucher_campaign_id;
      $get_campaign_url = 'campaigns';
      $get_campaign_method = 'GET';
      $query_params = array(
        'access_token' => $api_key,
        'pos_parent' => $pos_parent,
        'campaign_id' => $campaign_id
      );
      $campaign_response = $this->call_api($get_campaign_url, $get_campaign_method, array('Content-Type: application/json'), "", $query_params);
      $campaign_data = $campaign_response->data;
      if ($campaign_data->count == 1) {
        $voucher->voucher_desc = $campaign_data->campaigns[0]->voucher_description;
      }
    }
    usort($data, function ($a, $b) {
      $endDateA = new DateTime($a->date_end);
      $endDateB = new DateTime($b->date_end);
      $dateComparison = $endDateA <=> $endDateB;

      return $dateComparison;
    });

    //remove vouchers that are expired
    // $data = array_filter($data, function ($voucher) {
    //   $endDate = new DateTime($voucher->date_end);
    //   return $endDate >= new DateTime();
    // });
    return $data;
  }

  //SHORTCODE FOR DISPLAYING VOUCHERS
  public function display_vouchers_info() //voucher
  {
    return "";
    $api_key = get_option('woo_ipos_api_key_setting');
    $pos_parent = get_option('woo_ipos_pos_parent_setting');
    $current_user = wp_get_current_user();
    $current_user_login = $current_user->user_login;
    if (!is_user_logged_in()) {
      return "";
    }
    $get_member_vouchers_url = 'member_vouchers';
    $get_member_vouchers_method = 'GET';

    $query_params = array(
      'access_token' => $api_key,
      'pos_parent' => $pos_parent,
      'user_id' => $current_user_login
    );

    $response = $this->call_api($get_member_vouchers_url, $get_member_vouchers_method, array('Content-Type: application/json'), "", $query_params);
    $data = $response->data;
    usort($data, function ($a, $b) {
      $endDateA = new DateTime($a->date_end);
      $endDateB = new DateTime($b->date_end);
      return $endDateA <=> $endDateB; // Compare the expiry dates
    });
    $html = "<div id=\"woo-ipos-voucher-container\">";
    foreach ($data as $voucher) {
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
