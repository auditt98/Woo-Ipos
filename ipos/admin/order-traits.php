<?php

trait OrderTraits
{
  //Lấy POS mặc định
  public function get_featured_pos()
  {
    $all_pos = $this->get_foodbook();
    $featured_pos = $all_pos->data->pos_parent->Pos_Feature;
    return $featured_pos;
  }

  //Lấy danh sách tất cả món ăn, nhà hàng
  public function get_foodbook()
  {
    $api_key = get_option('woo_ipos_api_key_setting');
    $pos_parent = get_option('woo_ipos_pos_parent_setting');
    $get_pos_info_url = 'v2/items';
    $get_pos_info_method = 'GET';
    $query_params = array(
      'access_token' => $api_key,
      'pos_parent' => $pos_parent
    );

    $response = $this->call_api($get_pos_info_url, $get_pos_info_method, array('Content-Type: application/json'), "", $query_params);
    return $response;
  }

  //Lấy danh sách món ăn
  public function get_menu()
  {
    $foodbook = $this->get_foodbook();
    return $foodbook->data->items;
  }

  //Lấy cart hiện tại
  public function get_current_cart()
  {
    return WC()->cart->get_cart();
  }

  public function parse_current_cart()
  {
    $current_cart = $this->get_current_cart();
    //for each item in cart
    foreach ($current_cart as $key => $cart_item) {
      //get product id
      $product_id = $cart_item['product_id'];
      $product = wc_get_product($product_id);
      $product_name = $product->get_name();
      $product_sku = $this->get_product_sku_from_id($product_id);
      $variation_id = $cart_item['variation_id'];
      //get product price and variation price
      $product_price = $product->get_price();
      $variation_price = $product->get_price();

      if (isset($cart_item['variation_id']) && $cart_item['variation_id'] != 0) {
        $variation = wc_get_product($variation_id);
        $variation_name = $variation->get_name();
        $variation_sku = $this->get_product_sku_from_id($variation_id);
      } else {
        $variation_name = '';
        $variation_sku = '';
      }
      $current_cart[$key]['product_name'] = $product_name;
      $current_cart[$key]['variation_name'] = $variation_name;
      $current_cart[$key]['variation_sku'] = $variation_sku;
      $current_cart[$key]['product_sku'] = $product_sku;
      $current_cart[$key]['product_price'] = $product_price;
      $current_cart[$key]['variation_price'] = $variation_price;

      //loop through $cart_item['product_extras']
      $product_extras = $cart_item['product_extras'];
      if (isset($product_extras['groups'])) {
        $groups = $product_extras['groups'];
        $extras = array();
        foreach ($groups as $group_item) {
          //loop through $group_item
          foreach ($group_item as $group_item_key) {
            # code...
            if (isset($group_item_key['label']) && isset($group_item_key['value'])) {
              $label = $group_item_key['label'];
              $value = $group_item_key['value'];
              //push to extras
              array_push($extras, array(
                'label' => $label,
                'value' => $value
              ));
            }
          }
        }
        if (!empty($extras)) {
          //update extras in current cart
          $current_cart[$key]['extras'] = $extras;
        } else {
          $current_cart[$key]['extras'] = array();
        }
      }
      $original_price = $product_extras['original_price'];
      $current_cart[$key]['original_price'] = $original_price;
    }
    return $current_cart;
  }

  //Lấy SKU sản phẩm từ ID
  public function get_product_sku_from_id($id)
  {
    $product = wc_get_product($id);
    return $product->get_sku();
  }

  public function parse_product_sku($sku)
  {
    $sku_parts = explode('|', $sku);
    if (count($sku_parts) == 3) {
      $type_id = $sku_parts[0];
      $store_id = $sku_parts[1];
      $product_type = $sku_parts[2];
      return array(
        'product_type' => $product_type,
        'type_id' => $type_id,
        'store_id' => $store_id
      );
    } else if (count($sku_parts) == 2) {
      $type_id = $sku_parts[0];
      $store_id = $sku_parts[1];
      return array(
        'product_type' => '[NORMAL]',
        'type_id' => $type_id,
        'store_id' => $store_id
      );
    } else {
      return '';
    }
  }

  function add_custom_fees($cart)
  {
    // Calculate the amount to reduce
    session_start();
    $response = isset($_SESSION['voucher_response']) ? unserialize($_SESSION['voucher_response']) : null;
    if ($response) {
      error_log('Json($response->data) - ' . json_encode($response->data));
      $data = $response->data;
      //check discount amount, if there's discount amount, add a fee of -discount_amount
      if (isset($data->Discount_Amount) && $data->Discount_Amount != 0) {
        WC()->session->set('applied_voucher', $data->Coupon_Code);
        error_log('------------------' . $data->Coupon_Code . '------------------');
        $discount_amount = $data->Discount_Amount;
        $splittedName = explode("_", $data->voucher_campaign_name);
        $label = $splittedName[0] . ' | Mã: ' . $data->Coupon_Code;
        $cart->add_fee($label, -$discount_amount);
      } else {
      }
    }
  }

  public function on_cart_update() {
    error_log("~~~~~Cart quantity updated~~~~~");
  }

  public function clear_voucher()
  {
    session_start();
    unset($_SESSION['voucher_response']);
    unset($_SESSION['voucher_code']);
    WC()->cart->calculate_totals();
  }

  public function apply_voucher()
  {
    // {
    //   "pos_id": 3160,
    //   "pos_parent": "SAOBANG",
    //   "voucher_code": "M5JZM5QG",
    //   "membership_id": "84967142868",
    //   "order_data_item": [
    //     {
    //       "Item_Type_Id": "CF",
    //       "Item_Id": "BR09",
    //       "Item_Name": "DUCK DUCK",
    //       "Price": 60000,
    //       "Amount": 120000,
    //       "Quantity": 2,
    //       "Note": "DUCK DUCK"
    //     }
    //   ]
    // }
    try {
      //API PARAMS
      $api_key = get_option('woo_ipos_api_key_setting');

      $check_voucher_url = 'check_voucher';
      $check_voucher_method = 'POST';

      $query_params = array(
        'access_token' => $api_key
      );

      $voucherId = $_POST['voucher_id'];
      // return;
      //POS Parent
      $pos_parent = get_option('woo_ipos_pos_parent_setting');
      session_start();
      if ($voucherId == '') {
        $_SESSION['voucher_response'] = "";
        $_SESSION['voucher_code'] = "";
        WC()->cart->calculate_totals();
        wp_send_json_success("");
        return;
      }

      //Featured POS
      $featured_pos = $this->get_featured_pos();
      $current_user = wp_get_current_user();
      $current_cart = $this->parse_current_cart();
      //CURRENT USER LOGIN
      $current_user_login = $current_user->user_login;

      $order_data_item = array();
      $children_data_item = array();
      foreach ($current_cart as $cart_item) {
        $item = array();
        //
        $sku = '';
        $variation_id = $cart_item['variation_id'];

        //SET SKU
        //isset and not empty
        if (isset($cart_item['variation_sku']) && !empty($cart_item['variation_sku'])) {
          $sku = $cart_item['variation_sku'];
        } else {
          $sku = $cart_item['product_sku'];
        }

        //parse sku
        $sku_parts = $this->parse_product_sku($sku);
        $item_type_id = '';
        $item_id = '';
        $item_product_type = '';
        if ($sku_parts != '') {
          $item_type_id = $sku_parts['type_id']; //id of the type of product
          $item_id = $sku_parts['store_id']; //id of product 
          $item_product_type = $sku_parts['product_type']; //Depends on SKU, can be COMBO, NORMAL
        }


        //get product name
        $product_name = $cart_item['product_name'];
        //if variation id is set, use variation price
        $item_price = $cart_item['product_price'];

        if (isset($variation_id) && $variation_id != 0) {
          $item_price = $cart_item['variation_price'];
        } else {
          $item_price = $cart_item['product_price'];
        }
        $amount = $cart_item['line_total'];
        //set item
        $item['Item_Type_Id'] = $item_type_id;
        $item['Item_Id'] = $item_id;
        $item['Item_Name'] = $product_name;
        $price = $item_price;
        //if original price is set, use original price
        if (isset($cart_item['original_price'])) {
          $price = $cart_item['original_price'];
        }
        $item['Price'] = $price;
        $quantity = $cart_item['quantity'];
        $item['Amount'] = $amount;
        $item['Quantity'] = $quantity;
        $item['Note'] = $product_name;
        //push item to order_data_item

        foreach ($cart_item['extras'] as $child_item) {
          if ($child_item->label == 'Hộp trà') {
            $item['Package_Id'] = $item_id;
            $tea_result = $this->handle_tea($child_item->value, $cart_item['product_sku']);
            array_push($children_data_item, $tea_result);
          }
          if ($child_item->label == 'Bánh wholecake') {
            $item['Package_Id'] = $item_id;
            $wholecake_result = $this->handle_wholecake($child_item->value, $cart_item['product_sku']);
            array_push($order_item_children, $wholecake_result);
          }
          if ($child_item->label == 'Vị bánh Brioche 1' || $child_item->label == 'Vị bánh Brioche 2') {
            $item['Package_Id'] = $item_id;
            $brioche_result = $this->handle_brioche($child_item->value, $cart_item['product_sku']);
            array_push($order_item_children, $brioche_result);
          }
          if ($child_item->label == 'Vị Cold Brew Coffee') {
            $item['Package_Id'] = $item_id;
            $cold_brew_result = $this->handle_cold_brew($child_item->value, $cart_item['product_sku']);
            array_push($order_item_children, $cold_brew_result);
          }
          if ($child_item->label == 'Chọn Đá/Nóng') {
            $item['Package_Id'] = $item_id;
            $cold_hot_salty_result = $this->handle_cold_hot_salty($child_item->value, $cart_item['product_sku']);
            array_push($order_item_children, $cold_hot_salty_result);
          }
          if ($child_item->label == 'Chai thứ nhất' || $child_item->label == 'Chai thứ hai' || $child_item->label == 'Chai thứ ba' || $child_item->label == 'Chai thứ tư' || $child_item->label == 'Chai thứ năm' || $child_item->label == 'Chai thứ sáu') {
            $item['Package_Id'] = $item_id;
            $kombucha_result = $this->handle_kombucha($child_item->value, $cart_item['product_sku']);
            array_push($order_item_children, $kombucha_result);
          }
        }
        foreach ($order_item_children as $order_item_child) {
          array_push($order_data_item, $order_item_child);
        }
        $custom_children = array();
        $custom_children = $this->handle_custom_combo($cart_item['product_sku']);
        foreach ($custom_children as $custom_child) {
          $item['Package_Id'] = $item_id;
          array_push($order_data_item, $custom_child);
        }
        array_push($order_data_item, $item);
  
        $children_data_item = array();
      }

      $request = array(
        'pos_id' => $featured_pos,
        'pos_parent' => $pos_parent,
        'voucher_code' => $voucherId,
        'membership_id' => $current_user_login,
        'order_data_item' => $order_data_item
      );
      error_log('Request: ' . json_encode($request));

      $response = $this->call_api($check_voucher_url, $check_voucher_method, array('Content-Type: application/json'), json_encode($request), $query_params);
      session_start();
      $_SESSION['voucher_response'] = serialize($response);
      $_SESSION['voucher_code'] = $voucherId;
      // $cart->calculate_totals();
      WC()->cart->calculate_totals();
      $combinedResponse = array(
        'url' => $check_voucher_url,
        'query' => $query_params,
        'request' => $request,
        'response' => $response
      );
      wp_send_json_success($combinedResponse);
    } catch (Exception $e) {
      wp_send_json_error($e->getMessage(), 500);
    }
  }

  public function exchange_point()
  {
    if (!is_user_logged_in()) {
      return;
    }
    try {
      $point_amount = $_POST['point_amount'];
      $api_key = get_option('woo_ipos_api_key_setting');
      $pos_parent = get_option('woo_ipos_pos_parent_setting');
      $exchange_point_url = 'exchange_point';
      $exchange_point_method = 'GET';
      $current_user = wp_get_current_user();
      $current_user_login = $current_user->user_login;

      $headers = array(
        'access_token' => $api_key
      );

      $query_params = array(
        'access_token' => $api_key,
        'pos_parent' => $pos_parent,
        'point' => $point_amount,
        'user_id' => $current_user_login
      );
      $response = $this->call_api($exchange_point_url, $exchange_point_method, $headers, "", $query_params);
      $data = $response->data;
      wp_send_json_success($data->voucher_code);
    } catch (Exception $e) {
      wp_send_json_error($e->getMessage(), 500);
    }
  }

  public function add_vouchers_to_checkout_form()
  {
    if (!is_user_logged_in()) {
?>
      <h3 class="order_review_heading" style="font-family: 'Noto Serif Display', serif !important;">Khuyến mãi & Đổi điểm</h3>
      <div style="font-family: Cormorant,serif; font-weight: 600;">Vui lòng đăng nhập hoặc đăng ký để nhận khuyến mãi hoặc đổi điểm PON</div>
      <div class="button" onclick="window.location.href='/my-account'">ĐĂNG NHẬP hoặc ĐĂNG KÝ</div>
    <?php
      return;
    }

    $vouchers = $this->get_vouchers();
    $vouchers = array_filter($vouchers, function ($voucher) {
      $endDate = new DateTime($voucher->date_end);
      return $endDate >= new DateTime() && $voucher->status == 4;
    });
    $customer_points = $this->get_customer_points();

    ?>
    <div id="custom_section">
      <h5 class="order_review_heading" style="font-family: 'Noto Serif Display', serif !important;">Voucher Khuyến mãi</h5>
      <div class="voucher-container">
        <?php foreach ($vouchers as $voucher) : ?>
          <div class="voucher" data-voucher-code="<?php echo $voucher->voucher_code; ?>">
            <div class='voucher-label'><?php echo $voucher->voucher_label; ?></div>
            <div class='voucher-desc'><?php echo $voucher->voucher_desc; ?></div>
            <div class='voucher-exp'><?php echo $voucher->voucher_display_expiry; ?></div>
          </div>
        <?php endforeach; ?>
        <input type="hidden" name="applied_voucher" id="applied_voucher_input" value="">
      </div>
      <h5 class="order_review_heading" style="font-family: 'Noto Serif Display', serif !important;">Bạn có mã giảm giá</h5>
      <div class="voucher-container">
        <div>Nếu bạn có mã giảm giá, hãy nhập mã ở đây nhé</div>
        <input type="text" name="custom_voucher" id="custom_voucher_input" placeholder="Nhập mã giảm giá của bạn">
        <div class="button" onclick="handleCustomVoucher()">Áp dụng voucher</div>
      </div>

      <h5 class="order_review_heading" style="font-family: 'Noto Serif Display', serif !important;">Đổi điểm PON</h5>
      <div class="voucher-container">
        <div style="width: 100%">Hiện tại bạn có <?php echo $customer_points; ?> PON</div>
        <div>Quy đổi điểm Pon để được giảm giá!</div>
        <input type="number" min="0" max="<?php echo $customer_points; ?>" step="1" name="point_exchange" id="point_exchange_input" placeholder="Nhập số điểm bạn muốn quy đổi">
        <div class="button" onclick="handlePointExchange()">Đổi điểm PON</div>
      </div>
    </div>

    <script>
      document.addEventListener('DOMContentLoaded', function() {
        var vouchers = document.querySelectorAll('.voucher');
        vouchers.forEach(function(voucher) {
          voucher.addEventListener('click', function() {
            var voucherId = voucher.getAttribute('data-voucher-code');
            verifyVoucher(voucherId);
          });
        });
      });

      function handleCustomVoucher() {
        const customVoucherInput =document.getElementById('custom_voucher_input')
        if (customVoucherInput) {
          let value = customVoucherInput.value;
          verifyVoucher(value);
        }
      }

      function handlePointExchange() {
        const pointExchangeInput = document.getElementById('point_exchange_input');
        if (pointExchangeInput) {
          let value = Number(pointExchangeInput.value);
          verifyPointExchange(value);
        }
      }

      function verifyPointExchange(pointAmount) {
        jQuery.ajax({
          url: ajaxurl,
          type: 'POST',
          data: {
            action: 'exchange_point_action',
            point_amount: pointAmount
          },
          success: function(response) {
            if (response && response.data && response.data.length > 0) {
              verifyVoucher(response.data);
              window.location.reload();
            }
          },
          error: function(error) {
            console.error('Đã có lỗi xảy ra');
          }
        })
      }

      function verifyVoucher(voucherId) {
        jQuery.ajax({
          url: ajaxurl, // The AJAX URL provided by WordPress
          type: 'POST',
          data: {
            action: 'apply_voucher_action', // The name of the AJAX action
            voucher_id: voucherId
          },
          success: function(response) {
            console.log("response", response)
            if (response?.data?.response?.error?.message) {
              alert(response?.data?.response?.error?.message);
              jQuery(document.body).trigger("update_checkout");
              return
            }
            if (response?.data?.response?.data?.Code != 4) {
              alert('Voucher không hợp lệ');
              jQuery(document.body).trigger("update_checkout");
              return;
            }
            if (response?.data?.response?.data?.Coupon_Code) {
              jQuery('#applied_voucher_input').val(response?.data?.response?.data?.Coupon_Code);
            }
            //refresh page
            jQuery(document.body).trigger("update_checkout");
          },
          error: function(error) {
            console.error('Đã có lỗi xảy ra');
          }
        });
      }
    </script>

    <style>
      .voucher {
        width: 30%;
        padding: 20px 10px;
        border: 1px solid #53648aff;
      }

      .voucher-desc {
        font-size: 0.8rem;
        margin-bottom: 10px;
        color: #53648aff;
      }

      .voucher-label {
        font-size: 1.1rem;
        font-weight: bold;
        margin-bottom: 10px;
        color: #53648aff;
      }

      .voucher:hover .voucher-desc {
        color: white;
      }

      .voucher:hover .voucher-label {
        color: white;
      }

      .voucher:hover {
        background-color: #53648aff;
        color: white;
      }

      .voucher-container {
        display: flex;
        flex-wrap: wrap;
        gap: 10px;
      }
    </style>
<?php
  }

  public function save_voucher_to_order()
  {
    if (isset($_POST['applied_voucher']) && !empty($_POST['applied_voucher'])) {
      // Perform any validation you need
      $voucher_value = sanitize_text_field($_POST['applied_voucher']);

      // Save the value as order meta data or perform other necessary actions
      WC()->session->set('applied_voucher', $voucher_value);
    }
  }

  public function handle_candle($candle, $price)
  {
    // $pos_nen_que = 'PK0016';
    // $pos_nen_1 = 'PK0017.1';
    // $pos_nen_2 = 'PK0017.2';
    // $pos_nen_0 = 'PK0017.0';
    // $pos_nen_3 = 'PK0017.3';
    // $pos_nen_4 = 'PK0017.4';
    // $pos_nen_5 = 'PK0017.5';
    // $pos_nen_6 = 'PK0017.6';
    // $pos_nen_7 = 'PK0017.7';
    // $pos_nen_8 = 'PK0017.8';
    // $pos_nen_9 = 'PK0017.9';

    //test code
    $pos_nen_1 = 'I100';
    $pos_nen_2 = 'PK0017.2';
    $pos_nen_0 = 'PK0017.0';
    $pos_nen_3 = 'I1100';
    $pos_nen_4 = 'PK0017.4';
    $pos_nen_5 = 'PK0017.5';
    $pos_nen_6 = 'PK0017.6';
    $pos_nen_7 = 'PK0017.7';
    $pos_nen_8 = 'PK0017.8';
    $pos_nen_9 = 'PK0017.9';
    $pos_nen_que = 'PK0016';

    $candle_result = array();
    $candle_item_type_id = 'DA';
    $candle_result['Item_Type_Id'] = $candle_item_type_id;
    $candle_result['Quantity'] = 1;
    $candle_result['Price'] = $price;
    $candle_result['Amount'] = $price;
    $candle_result['Parent_Id'] = '';
    if ($candle == 'Set phụ kiện nến que') {
      $candle_result['Item_Id'] = $pos_nen_que;
      $candle_result['Item_Name'] = 'Set phụ kiện nến que';
      $candle_result['Note'] = 'Set phụ kiện nến que';
    }
    if ($candle == 'Nến số 0') {
      $candle_result['Item_Id'] = $pos_nen_0;
      $candle_result['Item_Name'] = 'Nến số 0';
      $candle_result['Note'] = 'Nến số 0';
    }
    if ($candle == 'Nến số 1') {
      $candle_result['Item_Id'] = $pos_nen_1;
      $candle_result['Item_Name'] = 'Nến số 1';
      $candle_result['Note'] = 'Nến số 1';
    }
    if ($candle == 'Nến số 2') {
      $candle_result['Item_Id'] = $pos_nen_2;
      $candle_result['Item_Name'] = 'Nến số 2';
      $candle_result['Note'] = 'Nến số 2';
    }

    if ($candle == 'Nến số 3') {
      $candle_result['Item_Id'] = $pos_nen_3;
      $candle_result['Item_Name'] = 'Nến số 3';
      $candle_result['Note'] = 'Nến số 3';
    }

    if ($candle == 'Nến số 4') {
      $candle_result['Item_Id'] = $pos_nen_4;
      $candle_result['Item_Name'] = 'Nến số 4';
      $candle_result['Note'] = 'Nến số 4';
    }

    if ($candle == 'Nến số 5') {
      $candle_result['Item_Id'] = $pos_nen_5;
      $candle_result['Item_Name'] = 'Nến số 5';
      $candle_result['Note'] = 'Nến số 5';
    }

    if ($candle == 'Nến số 6') {
      $candle_result['Item_Id'] = $pos_nen_6;
      $candle_result['Item_Name'] = 'Nến số 6';
      $candle_result['Note'] = 'Nến số 6';
    }

    if ($candle == 'Nến số 7') {
      $candle_result['Item_Id'] = $pos_nen_7;
      $candle_result['Item_Name'] = 'Nến số 7';
      $candle_result['Note'] = 'Nến số 7';
    }

    if ($candle == 'Nến số 8') {
      $candle_result['Item_Id'] = $pos_nen_8;
      $candle_result['Item_Name'] = 'Nến số 8';
      $candle_result['Note'] = 'Nến số 8';
    }

    if ($candle == 'Nến số 9') {
      $candle_result['Item_Id'] = $pos_nen_9;
      $candle_result['Item_Name'] = 'Nến số 9';
      $candle_result['Note'] = 'Nến số 9';
    }
    return $candle_result;
  }

  public function handle_tea($tea, $parent_id)
  {
    // South Of France
    //The Other Earl Grey
    //A Walk On Grass
    $pos_south_of_france = 'SOFCB';
    $pos_the_other_earl_grey = 'TOEGCB';
    $pos_a_walk_on_grass = 'AWOGCB';

    $tea_result = array();
    $tea_result['Item_Type_Id'] = 'CB'; //---------------- REPLACE THIS
    $tea_result['Quantity'] = 1;
    $tea_result['Price'] = 0;
    $tea_result['Amount'] = 0;
    $tea_result['Parent_Id'] = $parent_id;
    $tea_result['Package_id'] = $parent_id;

    if ($tea == 'South Of France') {
      $tea_result['Item_Id'] = $pos_south_of_france;
      $tea_result['Item_Name'] = 'South Of France';
      $tea_result['Note'] = 'South Of France';
    }
    if ($tea == 'The Other Earl Grey') {
      $tea_result['Item_Id'] = $pos_the_other_earl_grey;
      $tea_result['Item_Name'] = 'The Other Earl Grey';
      $tea_result['Note'] = 'The Other Earl Grey';
    }
    if ($tea == 'A Walk On Grass') {
      $tea_result['Item_Id'] = $pos_a_walk_on_grass;
      $tea_result['Item_Name'] = 'A Walk On Grass';
      $tea_result['Note'] = 'A Walk On Grass';
    }

    return $tea_result;
  }

  public function handle_wholecake($cake, $parent_id)
  {
    // Milky Mille Crepes
    // Earl Grey Mille Crepes
    // Pistachio Mille Crepes
    // Passion Fruit Mille Crepes
    // Tiramisu Mille Crepes
    // Milky Milo
    // Strawberry Short Cake
    // Mango Fromage
    // Smooth Chocolate
    // Daisy Darling
    // Vanilla Choux

    //---------------REPLACE THIS
    $pos_milky_mille_crepes = 'MMC16';
    $pos_earl_grey_mille_crepes = 'EGMCWC16';
    $pos_pistachio_mille_crepes = 'PMCWC16';
    $pos_passion_fruit_mille_crepes = 'PASSMCWC16';
    $pos_tiramisu_mille_crepes = 'TMCWC16';
    $pos_milky_milo = 'MMCWC16';
    $pos_strawberry_short_cake = 'SSCWC16';
    $pos_mango_fromage = 'MFWC16';
    $pos_smooth_choc = 'SCWC14';
    $pos_daisy_darling = 'DDW14';
    $pos_vanilla_choux = 'VCWC14';

    $cake_result = array();
    $cake_result['Item_Type_Id'] = 'WCN'; //---------------- REPLACE THIS
    $cake_result['Quantity'] = 1;
    $cake_result['Price'] = 0;
    $cake_result['Amount'] = 0;
    $cake_result['Parent_Id'] = $parent_id;
    $cake_result['Package_Id'] = $parent_id;
    if ($cake == 'Milky Mille Crepes') {
      $cake_result['Item_Id'] = $pos_milky_mille_crepes;
      $cake_result['Item_Name'] = 'Milky Mille Crepes';
      $cake_result['Note'] = 'Milky Mille Crepes';
    }
    if ($cake == 'Earl Grey Mille Crepes') {
      $cake_result['Item_Id'] = $pos_earl_grey_mille_crepes;
      $cake_result['Item_Name'] = 'Earl Grey Mille Crepes';
      $cake_result['Note'] = 'Earl Grey Mille Crepes';
    }
    if ($cake == 'Pistachio Mille Crepes') {
      $cake_result['Item_Id'] = $pos_pistachio_mille_crepes;
      $cake_result['Item_Name'] = 'Pistachio Mille Crepes';
      $cake_result['Note'] = 'Pistachio Mille Crepes';
    }
    if ($cake == 'Passion Fruit Mille Crepes') {
      $cake_result['Item_Id'] = $pos_passion_fruit_mille_crepes;
      $cake_result['Item_Name'] = 'Passion Fruit Mille Crepes';
      $cake_result['Note'] = 'Passion Fruit Mille Crepes';
    }
    if ($cake == 'Tiramisu Mille Crepes') {
      $cake_result['Item_Id'] = $pos_tiramisu_mille_crepes;
      $cake_result['Item_Name'] = 'Tiramisu Mille Crepes';
      $cake_result['Note'] = 'Tiramisu Mille Crepes';
    }
    if ($cake == 'Milky Milo') {
      $cake_result['Item_Id'] = $pos_milky_milo;
      $cake_result['Item_Name'] = 'Milky Milo';
      $cake_result['Note'] = 'Milky Milo';
    }
    if ($cake == 'Strawberry Short Cake') {
      $cake_result['Item_Id'] = $pos_strawberry_short_cake;
      $cake_result['Item_Name'] = 'Strawberry Short Cake';
      $cake_result['Note'] = 'Strawberry Short Cake';
    }
    if ($cake == 'Mango Fromage') {
      $cake_result['Item_Id'] = $pos_mango_fromage;
      $cake_result['Item_Name'] = 'Mango Fromage';
      $cake_result['Note'] = 'Mango Fromage';
    }
    if ($cake == 'Smooth Chocolate') {
      $cake_result['Item_Id'] = $pos_smooth_choc;
      $cake_result['Item_Name'] = 'Smooth Chocolate';
      $cake_result['Note'] = 'Smooth Chocolate';
    }
    if ($cake == 'Daisy Darling') {
      $cake_result['Item_Id'] = $pos_daisy_darling;
      $cake_result['Item_Name'] = 'Daisy Darling';
      $cake_result['Note'] = 'Daisy Darling';
    }
    if ($cake == 'Vanilla Choux') {
      $cake_result['Item_Id'] = $pos_vanilla_choux;
      $cake_result['Item_Name'] = 'Vanilla Choux';
      $cake_result['Note'] = 'Vanilla Choux';
    }
    return $cake_result;
  }

  public function handle_brioche($brioche, $parent_id)
  {
    $pos_classic_butter_bri = 'CBC';
    $pos_strawberries_ricotta_bri = 'SARB';
    $pos_truffle_bri = 'TMACB';
    $brioche_result = array();
    $bri_item_type_id = 'GAGAO'; //---------------- REPLACE THIS
    $brioche_result['Item_Type_Id'] = $bri_item_type_id;
    $brioche_result['Quantity'] = 1;
    $brioche_result['Price'] = 0;
    $brioche_result['Amount'] = 0;
    $brioche_result['Parent_Id'] = $parent_id;
    $brioche_result['Package_Id'] = $parent_id;

    if ($brioche == 'Classic Butter Brioche') {
      $brioche_result['Item_Id'] = $pos_classic_butter_bri;
      $brioche_result['Item_Name'] = 'Classic Butter Brioche';
      $brioche_result['Note'] = 'Classic Butter Brioche';
    }
    if ($brioche == 'Strawberries & Ricotta Brioche') {
      $brioche_result['Item_Id'] = $pos_strawberries_ricotta_bri;
      $brioche_result['Item_Name'] = 'Strawberries & Ricotta Brioche';
      $brioche_result['Note'] = 'Strawberries & Ricotta Brioche';
    }
    if ($brioche == 'Truffle Mushroom & Camembert Brioche') {
      $brioche_result['Item_Id'] = $pos_truffle_bri;
      $brioche_result['Item_Name'] = 'Truffle Mushroom & Camembert Brioche';
      $brioche_result['Note'] = 'Truffle Mushroom & Camembert Brioche';
    }
    return $brioche_result;
  }

  public function handle_cold_hot_salty($cold_hot, $parent_id)
  {
    $pos_salty_hand_hot = 'SHLHOT';
    $pos_salty_hand_cold = 'SHLC';

    $salty_result = array();
    $salty_item_type_id = 'CAL'; //---------------- REPLACE THIS

    $salty_result['Item_Type_Id'] = $salty_item_type_id;
    $salty_result['Quantity'] = 1;
    $salty_result['Price'] = 0;
    $salty_result['Amount'] = 0;
    $salty_result['Parent_Id'] = $parent_id;
    $salty_result['Package_Id'] = $parent_id;

    if ($cold_hot == 'Salty Hand Coffee Đá') {
      $salty_result['Item_Id'] = $pos_salty_hand_cold;
      $salty_result['Item_Name'] = 'Salty Hand Coffee Đá';
      $salty_result['Note'] = 'Salty Hand Coffee Đá';
    }
    if ($cold_hot == 'Salty Hand Coffee Nóng') {
      $salty_result['Item_Id'] = $pos_salty_hand_hot;
      $salty_result['Item_Name'] = 'Salty Hand Coffee Nóng';
      $salty_result['Note'] = 'Salty Hand Coffee Nóng';
    }
    return $salty_result;
  }

  public function handle_kombucha($kombucha, $parent_id)
  {
    $kombucha_result = array();

    $pos_padme = 'PK750ML';
    $pos_rose_champ = 'RCK750ML';
    $pos_summo = 'SMO750ML';
    $komb_type_id = 'GAGB'; //---------------- REPLACE THIS
    $kombucha_result['Item_Type_Id'] = $komb_type_id;
    $kombucha_result['Quantity'] = 1;
    $kombucha_result['Price'] = 0;
    $kombucha_result['Amount'] = 0;
    $kombucha_result['Parent_Id'] = $parent_id;
    $kombucha_result['Package_Id'] = $parent_id;

    if ($kombucha == 'Padme Kombucha 750ml') {
      $kombucha_result['Item_Id'] = $pos_padme;
      $kombucha_result['Item_Name'] = 'Padme Kombucha 750ml';
      $kombucha_result['Note'] = 'Padme Kombucha 750ml';
    }
    if ($kombucha == 'Rose Champagne Kombucha 750ml') {
      $kombucha_result['Item_Id'] = $pos_rose_champ;
      $kombucha_result['Item_Name'] = 'Rose Champagne Kombucha 750ml';
      $kombucha_result['Note'] = 'Rose Champagne Kombucha 750ml';
    }
    if ($kombucha == 'Sum-mơ Kombucha 750ml') {
      $kombucha_result['Item_Id'] = $pos_summo;
      $kombucha_result['Item_Name'] = 'Sum-mơ Kombucha 750ml';
      $kombucha_result['Note'] = 'Sum-mơ Kombucha 750ml';
    }

    return $kombucha_result;
  }

  public function handle_cold_brew($cold_brew, $parent_id)
  {
    $cold_brew_type_id = 'GAGB';
    $cold_brew_result = array();
    $pos_cold_brew_french_vanilla = 'FVCBC';
    $pos_cold_brew_golden_berry = 'GBCBC';

    $cold_brew_result['Item_Type_Id'] = $cold_brew_type_id;
    $cold_brew_result['Quantity'] = 1;
    $cold_brew_result['Price'] = 0;
    $cold_brew_result['Amount'] = 0;
    $cold_brew_result['Parent_Id'] = $parent_id;
    $cold_brew_result['Package_Id'] = $parent_id;

    if ($cold_brew == 'French Vanilla Cold Brew Coffee 290ml') {
      $cold_brew_result['Item_Id'] = $pos_cold_brew_french_vanilla;
      $cold_brew_result['Item_Name'] = 'French Vanilla Cold Brew Coffee';
      $cold_brew_result['Note'] = 'French Vanilla Cold Brew Coffee';
    }

    if ($cold_brew == 'Golden Berry Cold Brew Coffee 290ml') {
      $cold_brew_result['Item_Id'] = $pos_cold_brew_golden_berry;
      $cold_brew_result['Item_Name'] = 'Golden Berry Cold Brew Coffee';
      $cold_brew_result['Note'] = 'Golden Berry Cold Brew Coffee';
    }
    return $cold_brew_result;
  }

  public function parse_phone_number($phoneNumber)
  {
    // Remove any non-digit characters from the phone number
    $phoneNumber = preg_replace('/\D/', '', $phoneNumber);

    // Check if the phone number starts with 0 or +
    if (strpos($phoneNumber, '0') === 0) {
      // Remove the leading 0 and prepend the country code
      $phoneNumber = '84' . substr($phoneNumber, 1);
    } elseif (strpos($phoneNumber, '+') === 0) {
      // Remove the leading + and prepend the country code
      $phoneNumber = '84' . substr($phoneNumber, 1);
    }

    return $phoneNumber;
  }

  public function generateRandomString($length)
  {
    $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $random_string = '';
    for ($i = 0; $i < $length; $i++) {
      $random_string .= $characters[rand(0, strlen($characters) - 1)];
    }
    return $random_string;
  }

  public function handle_custom_combo($sku)
  {
    $result = array();

    if ($sku == 'COMBO|PCB') {
      $parent_id = 'PCB';
      $custom_item1 = array();
      $custom_item1['Item_Type_Id'] = 'GAGB'; //---------------- REPLACE THIS
      $custom_item1['Quantity'] = 1;
      $custom_item1['Price'] = 0;
      $custom_item1['Amount'] = 0;
      $custom_item1['Parent_Id'] = $parent_id;
      $custom_item1['Package_Id'] = $parent_id;
      $custom_item1['Item_Id'] = "RCK750ML";
      $custom_item1['Item_Name'] = '01 Rose Champagne Kombucha 750ml';
      $custom_item1['Note'] = '01 Rose Champagne Kombucha 750ml';

      $custom_item2 = array();
      $custom_item2['Item_Type_Id'] = 'GAGAO'; //---------------- REPLACE THIS
      $custom_item2['Quantity'] = 1;
      $custom_item2['Price'] = 0;
      $custom_item2['Amount'] = 0;
      $custom_item2['Parent_Id'] = $parent_id;
      $custom_item2['Package_Id'] = $parent_id;
      $custom_item2['Item_Id'] = "FB";
      $custom_item2['Item_Name'] = '01 Bó hoa tươi trong ngày/Flower Bouquet';
      $custom_item2['Note'] = '01 Bó hoa tươi trong ngày/Flower Bouquet';
      array_push($result, $custom_item1);
      array_push($result, $custom_item2);
    }

    if ($sku == 'COMBO|PHBB') {
      $parent_id = 'PHBB';
      $custom_item1 = array();
      $custom_item1['Item_Type_Id'] = 'GAGB'; //---------------- REPLACE THIS
      $custom_item1['Quantity'] = 1;
      $custom_item1['Price'] = 0;
      $custom_item1['Amount'] = 0;
      $custom_item1['Parent_Id'] = $parent_id;
      $custom_item1['Package_Id'] = $parent_id;
      $custom_item1['Item_Id'] = "HRCBT";
      $custom_item1['Item_Name'] = 'Mood Up! Cold Brew Tea 300ml';
      $custom_item1['Note'] = 'Mood Up! Cold Brew Tea 300ml';

      $custom_item2 = array();
      $custom_item2['Item_Type_Id'] = 'GAGAO'; //---------------- REPLACE THIS
      $custom_item2['Quantity'] = 1;
      $custom_item2['Price'] = 0;
      $custom_item2['Amount'] = 0;
      $custom_item2['Parent_Id'] = $parent_id;
      $custom_item2['Package_Id'] = $parent_id;
      $custom_item2['Item_Id'] = "HFBP";
      $custom_item2['Item_Name'] = 'Holiday Flavored Butter Pack';
      $custom_item2['Note'] = 'Holiday Flavored Butter Pack';

      $custom_item3 = array();
      $custom_item3['Item_Type_Id'] = 'CB'; //---------------- REPLACE THIS
      $custom_item3['Quantity'] = 1;
      $custom_item3['Price'] = 0;
      $custom_item3['Amount'] = 0;
      $custom_item3['Parent_Id'] = $parent_id;
      $custom_item3['Package_Id'] = $parent_id;
      $custom_item3['Item_Id'] = "HCM";
      $custom_item3['Item_Name'] = 'Houjicha Cocoa Mix';
      $custom_item3['Note'] = 'Houjicha Cocoa Mix';

      array_push($result, $custom_item1);
      array_push($result, $custom_item2);
      array_push($result, $custom_item3);
    }

    if ($sku == 'COMBO|CBVB') {
      $parent_id = 'CBVB';
      $custom_item1 = array();
      $custom_item1['Item_Type_Id'] = 'PAS'; //---------------- REPLACE THIS
      $custom_item1['Quantity'] = 1;
      $custom_item1['Price'] = 0;
      $custom_item1['Amount'] = 0;
      $custom_item1['Parent_Id'] = $parent_id;
      $custom_item1['Package_Id'] = $parent_id;
      $custom_item1['Item_Id'] = "WWC";
      $custom_item1['Item_Name'] = 'Whole Wheat Croissant';
      $custom_item1['Note'] = 'Whole Wheat Croissant';

      $custom_item2 = array();
      $custom_item2['Item_Type_Id'] = 'GAGB'; //---------------- REPLACE THIS
      $custom_item2['Quantity'] = 1;
      $custom_item2['Price'] = 0;
      $custom_item2['Amount'] = 0;
      $custom_item2['Parent_Id'] = $parent_id;
      $custom_item2['Package_Id'] = $parent_id;
      $custom_item2['Item_Id'] = "HRCBT";
      $custom_item2['Item_Name'] = 'Mood Up! Cold Brew Tea 300ml';
      $custom_item2['Note'] = 'Mood Up! Cold Brew Tea 300ml';
      array_push($result, $custom_item1);
      array_push($result, $custom_item2);
    }

    if ($sku == 'COMBO|CBBB') {
      $parent_id = 'CBBB';
      $custom_item1 = array();
      $custom_item1['Item_Type_Id'] = 'PAS'; //---------------- REPLACE THIS
      $custom_item1['Quantity'] = 1;
      $custom_item1['Price'] = 0;
      $custom_item1['Amount'] = 0;
      $custom_item1['Parent_Id'] = $parent_id;
      $custom_item1['Package_Id'] = $parent_id;
      $custom_item1['Item_Id'] = "HACCP";
      $custom_item1['Item_Name'] = 'Ham & Cheese Croisant (Plain)';
      $custom_item1['Note'] = 'Ham & Cheese Croisant (Plain)';
      array_push($result, $custom_item1);
    }
    return $result;
  }

  public function handle_order($id)
  {
    error_log("--HANDLE ORDER CALLED---" . $id);
    //CONST
    $csb_thaiha_pos_id = get_option('woo_ipos_pos_id_csb_thaiha_setting');
    $csb_trunghoa_pos_id = get_option('woo_ipos_pos_id_csb_trunghoa_setting');

    //SETUP API CALLS
    $order_request = array();
    $api_key = get_option('woo_ipos_api_key_setting');
    $pos_parent = get_option('woo_ipos_pos_parent_setting');

    $order_request['pos_parent'] = $pos_parent;
    $order_request['created_by'] = 'API';

    //GET CURRENT USER
    $current_user = wp_get_current_user();
    $current_user_login = $current_user->user_login;

    $order_request['return_data'] = 'full';
    $order_request['is_estimate'] = 0;

    $order = wc_get_order($id);
    $random_string = $this->generateRandomString(4);
    $order_request['foodbook_code'] = "o" . $random_string . $id;

    $order_data = $order->get_data(); // The Order data
    $order_items_data = array_map(function ($item) {
      return $item->get_data();
    }, $order->get_items());
    $order_fee_data = array_map(function ($item) {
      return $item->get_data();
    }, $order->get_fees());
    $order_data['order_items'] = $order_items_data;
    $order_data['order_fees'] = $order_fee_data;

    $order_request['user_id'] = $current_user_login ? $current_user_login : $this->parse_phone_number($order_data['billing']['phone']);
    $ipos_customer = $this->get_ipos_user();
    $order_request['username'] = $ipos_customer->name ? $ipos_customer->name : $order_data['billing']['first_name'] . ' ' . $order_data['billing']['last_name'];
    $order_shipping_lines = array_map(function ($item) {
      return $item->get_data();
    }, $order->get_shipping_methods());
    //parse shipping lines
    $order_data['order_shipping_lines'] = $order_shipping_lines;
    $order_request['adapt_to_online'] = 1;

    //parse shipping method
    $order_data['shipping_method'] = $this->parse_order_shipping_method($order_data);

    //handle order shipping logic
    //if deli
    if ($order_data['shipping_method']['is_delivery']) {
      $order_request['order_type'] = 'DELI';
      $order_request['pos_id'] = $csb_thaiha_pos_id;
      $order_request['ship_price_real'] = $order_data['shipping_total'];
      $order_request['to_address'] = $order_data['billing']['address_1'];
    } else {
      $order_request['order_type'] = 'PICK';
      $order_request['to_address'] = $order_data['shipping_method']['pickup_location'];
      if (strpos($order_data['shipping_method']['pickup_location'], '276') !== false) {
        $order_request['pos_id'] = $csb_thaiha_pos_id;
      } else {
        $order_request['pos_id'] = $csb_trunghoa_pos_id;
      }
    }

    //HANDLE FEES (DISCOUNT)
    $voucher_code = '';

    //loop through order_fees
    $fee_total = 0;
    foreach ($order_data['order_fees'] as $fee) {
      $fee_name = $fee['name'];
      $pattern = '/\| Mã: (\w+)/';
      if (preg_match($pattern, $fee_name, $matches)) {
        $voucher_code = $matches[1]; // Extract the code from the matched pattern
      }
      //convert $fee['total'] to int and make it absolute
      $fee_line = absint($fee['total']);
      $fee_total += $fee_line;
    }
    if ($voucher_code) {
      $order_request['coupon_log_id'] = $voucher_code;
    }
    //convert shipping total to int
    $shipping_total = absint($order_data['shipping_total']);
    $total = absint($order_data['total']);
    $real_total = $total - $shipping_total + $fee_total;
    //SET ORDER REQUEST TOTAL
    $order_request['amount'] = $real_total;
    $order_request['total_amount'] = $total;

    //HANDLE PAYMENT INFO
    $payment_info = array();
    if ($order_data['payment_method'] == 'cod') {
      $payment_info['Payment_Method'] = 'PAYMENT_ON_DELIVERY';
      $payment_info['Amount'] = 0;
    } else {
      //onepay
      $payment_info['Amount'] = $total;
      $payment_info['Payment_Method'] = 'PAYMENT_ON_DELIVERY';
      $payment_info['Payment_Info'] = $id; //ma giao dich
    }

    $order_request['PaymentInfo'] = $payment_info;

    //HANDLE BOOKING INFO
    $booking_info = array();

    //find meta_data with key = '_billing_wooccm13'
    foreach ($order_data['meta_data'] as $key => $meta) {
      if ($meta->key == '_billing_wooccm13') {
        $originalValue = $meta->value;
        $dateTime = DateTime::createFromFormat('Y/m/d H:i', $originalValue);
        $formattedValue = $dateTime->format('Y-m-d H:i:s');
        //set $booking_info
        $date = date('Y-m-d 00:00:00', strtotime($formattedValue));
        $hour = date('H', strtotime($formattedValue));
        $minute = date('i', strtotime($formattedValue));
        $booking_info['Book_Date'] = $date;
        $booking_info['Hour'] = $hour;
        $booking_info['Minute'] = $minute;
        $booking_info['Number_People'] = -1;
      }
    }

    if (empty($booking_info['Book_Date']) || empty($booking_info['Hour']) || empty($booking_info['Minute'])) {
      // Set the booking information as the current date, hour, and minute.
      $currentDate = date('Y-m-d 00:00:00');
      $currentHour = date('H');
      $currentMinute = date('i');

      $booking_info['Book_Date'] = $currentDate;
      $booking_info['Hour'] = $currentHour;
      $booking_info['Minute'] = $currentMinute;
    }
    // $order_request['booking_info'] = $booking_info;
    //HANDLE NOTE
    $order_request['is_pending'] = 0;
    $order_request['note'] = $order_data['customer_note'] ? $order_data['customer_note'] : '';
    $order_request['note'] = $order_request['note'] . ' [WEBSITE] --- Giao luc: ' . $booking_info['Book_Date'] . ' ' . $booking_info['Hour'] . ':' . $booking_info['Minute'];
    //handle order_items
    //   {
    //     "Item_Type_Id": "DU",               
    //     "Item_Id": "SU04",              //id món ăn trên hệ thống POS
    //     "Item_Name": "Caramel Macchiato Đá (S)",    //tên món ăn
    //     "Price": 55000,                 //giá 1 món
    //     "Quantity": 1,                  //số lượng
    //     "Note": "[CMI02] Caramel Machiato (ice)",   //ghi chú trên món
    //     "Discount":0.1,             //giảm giá % (nếu có) mặc định = 0
    //     "Foc": 0,                   //nếu có giảm giá thì foc=1. Mặc định = 0
    //     "Package_Id": "",               //option
    //     "Parent_Id": "",
    // },
    $order_items = array();
    $order_item_children = array();
    foreach ($order_data['order_items'] as $key => $order_data_item) {
      $order_item = array();
      $product_id = $order_data_item['product_id'];
      $variation_id = $order_data_item['variation_id'];
      $product = array();
      if ($variation_id && $variation_id != 0) {
        $product = wc_get_product($variation_id);
      } else {
        $product = wc_get_product($product_id);
      }
      $sku = $product->get_sku();
      $sku_arr = explode('|', $sku);
      $item_type_id = $sku_arr[0];
      $item_id = $sku_arr[1];
      $order_item['Item_Type_Id'] = $item_type_id;
      $order_item['Item_Id'] = $item_id;
      $order_item['Item_Name'] = str_replace('</span>', '', $order_data_item['name']);
      $order_item['Quantity'] = $order_data_item['quantity'];
      $order_item['Price'] = $product->get_price();
      foreach ($order_data_item['meta_data'] as $key => $meta) {
        if ($meta->key == 'product_extras') {
          $groups = $meta->value['groups'];

          if (isset($meta->value['original_price']) && $meta->value['original_price'] != null) {
            $order_item['Price'] = absint($meta->value['original_price']);
          }
          foreach ($groups as $key => $group_line) {
            foreach ($group_line as $gr_line => $group) {
              if ($group['label'] == 'Hộp trà') {
                $order_item['package_id'] = $item_id;
                $tea_result = $this->handle_tea($group['value_without_price'], $item_id);
                array_push($order_item_children, $tea_result);
              }
              if ($group['label'] == 'Bánh wholecake') {
                $order_item['package_id'] = $item_id;
                $wholecake_result = $this->handle_wholecake($group['value_without_price'], $item_id);
                array_push($order_item_children, $wholecake_result);
              }
              if ($group['label'] == 'Vị bánh Brioche 1' || $group['label'] == 'Vị bánh Brioche 2') {
                $order_item['package_id'] = $item_id;
                $brioche_result = $this->handle_brioche($group['value'], $item_id);
                array_push($order_item_children, $brioche_result);
              }
              if ($group['label'] == 'Vị Cold Brew Coffee') {
                $order_item['package_id'] = $item_id;
                $cold_brew_result = $this->handle_cold_brew($group['value'], $item_id);
                array_push($order_item_children, $cold_brew_result);
              }
              if ($group['label'] == 'Chọn Đá/Nóng') {
                $order_item['package_id'] = $item_id;
                $cold_hot_salty_result = $this->handle_cold_hot_salty($group['value'], $item_id);
                array_push($order_item_children, $cold_hot_salty_result);
              }
              if ($group['label'] == 'Chai thứ nhất' || $group['label'] == 'Chai thứ hai' || $group['label'] == 'Chai thứ ba' || $group['label'] == 'Chai thứ tư' || $group['label'] == 'Chai thứ năm' || $group['label'] == 'Chai thứ sáu') {
                $order_item['package_id'] = $item_id;
                $kombucha_result = $this->handle_kombucha($group['value'], $item_id);
                array_push($order_item_children, $kombucha_result);
              }
            }
          }
        }
      }
      foreach ($order_item_children as $order_item_child) {
        array_push($order_items, $order_item_child);
      }
      $custom_children = array();
      $custom_children = $this->handle_custom_combo($sku);
      foreach ($custom_children as $custom_child) {
        $order_item['package_id'] = $item_id;
        array_push($order_items, $custom_child);
      }
      array_push($order_items, $order_item);
      $order_item_children = array();
    }

    $order_request['order_data_item'] = $order_items;
    $voucher_code = isset($_SESSION['voucher_code']) ? $_SESSION['voucher_code'] : 'null';

    //calls api 
    $pos_order_online_url = 'order_online';
    $pos_order_online_method = 'POST';
    $api_key = get_option('woo_ipos_api_key_setting');
    $query_params = array(
      'access_token' => $api_key
    );
    $test_data = array(
      'order_id' => $id,
      'order_data' => $order_data,
      'order_request' => $order_request,
      'order_items' => $order_items,
      'voucher_code' => $voucher_code
    );
    $json_body = json_encode($order_request);
    $response = $this->call_api($pos_order_online_url, $pos_order_online_method, array('Content-Type: application/json'), $json_body, $query_params);
    $test_data['order_response'] = $response;

    error_log('---ORDER REQUEST---' . json_encode($order_request));
    error_log('---ORDER RESPONSE---' . json_encode($response));
    add_post_meta($id, 'request', $order_request, true);
    add_post_meta($id, 'response', $response, true);
    $this->clear_voucher();
    return json_encode($test_data);
  }

  public function test_order()
  {
    $current_cart = $this->parse_current_cart();
    $order_data_item = array();
    $children_data_item = array();
      foreach ($current_cart as $cart_item) {
        $item = array();
        //
        $sku = '';
        $variation_id = $cart_item['variation_id'];

        //SET SKU
        //isset and not empty
        if (isset($cart_item['variation_sku']) && !empty($cart_item['variation_sku'])) {
          $sku = $cart_item['variation_sku'];
        } else {
          $sku = $cart_item['product_sku'];
        }

        //parse sku
        $sku_parts = $this->parse_product_sku($sku);
        $item_type_id = '';
        $item_id = '';
        $item_product_type = '';
        if ($sku_parts != '') {
          $item_type_id = $sku_parts['type_id']; //id of the type of product
          $item_id = $sku_parts['store_id']; //id of product 
          $item_product_type = $sku_parts['product_type']; //Depends on SKU, can be COMBO, NORMAL
        }


        //get product name
        $product_name = $cart_item['product_name'];
        //if variation id is set, use variation price
        $item_price = $cart_item['product_price'];

        if (isset($variation_id) && $variation_id != 0) {
          $item_price = $cart_item['variation_price'];
        } else {
          $item_price = $cart_item['product_price'];
        }
        $amount = $cart_item['line_total'];
        //set item
        $item['Item_Type_Id'] = $item_type_id;
        $item['Item_Id'] = $item_id;
        $item['Item_Name'] = $product_name;
        $price = $item_price;
        //if original price is set, use original price
        if (isset($cart_item['original_price'])) {
          $price = $cart_item['original_price'];
        }
        $item['Price'] = $price;
        $quantity = $cart_item['quantity'];
        $item['Amount'] = $amount;
        $item['Quantity'] = $quantity;
        $item['Note'] = $product_name;
        //push item to order_data_item
        array_push($order_data_item, $item);
        foreach ($cart_item['extras'] as $child_item) {
          if ($child_item->label == 'Hộp trà') {
            $tea_result = $this->handle_tea($child_item->value, $cart_item['product_sku']);
            array_push($children_data_item, $tea_result);
          }
          if ($child_item->label == 'Bánh wholecake') {
            $wholecake_result = $this->handle_wholecake($child_item->value, $cart_item['product_sku']);
            array_push($order_item_children, $wholecake_result);
          }
          if ($child_item->label == 'Vị bánh Brioche 1' || $child_item->label == 'Vị bánh Brioche 2') {
            $brioche_result = $this->handle_brioche($child_item->value, $cart_item['product_sku']);
            array_push($order_item_children, $brioche_result);
          }
          if ($child_item->label == 'Vị Cold Brew Coffee') {
            $cold_brew_result = $this->handle_cold_brew($child_item->value, $cart_item['product_sku']);
            array_push($order_item_children, $cold_brew_result);
          }
          if ($child_item->label == 'Chọn Đá/Nóng') {
            $cold_hot_salty_result = $this->handle_cold_hot_salty($child_item->value, $cart_item['product_sku']);
            array_push($order_item_children, $cold_hot_salty_result);
          }
          if ($child_item->label == 'Chai thứ nhất' || $child_item->label == 'Chai thứ hai' || $child_item->label == 'Chai thứ ba' || $child_item->label == 'Chai thứ tư' || $child_item->label == 'Chai thứ năm' || $child_item->label == 'Chai thứ sáu') {
            $kombucha_result = $this->handle_kombucha($child_item->value, $cart_item['product_sku']);
            array_push($order_item_children, $kombucha_result);
          }
        }
        foreach ($order_item_children as $order_item_child) {
          array_push($order_data_item, $order_item_child);
        }
        $custom_children = array();
        $custom_children = $this->handle_custom_combo($cart_item['product_sku']);
        foreach ($custom_children as $custom_child) {
          array_push($order_data_item, $custom_child);
        }
  
        $children_data_item = array();
      }
    
    return json_encode($order_data_item);
  }

  public function parse_order_shipping_method($order_data)
  {
    $shipping_method = array(
      'is_delivery' => false,
      'pickup_location' => '',
      'total' => 0
    );
    foreach ($order_data['order_shipping_lines'] as $shipping_line) {
      $shipping_method_title = $shipping_line['method_title'];
      if (strpos($shipping_method_title, 'Delivery') !== false) {
        $shipping_method['is_delivery'] = true;
        $shipping_method['pickup_location'] = '';
        //parse to number
        $shipping_method['total'] = (int) $shipping_line['total'];
      }
      if (strpos($shipping_method_title, 'Pickup') !== false) {
        $shipping_method['is_delivery'] = false;
        $shipping_method['pickup_location'] = explode('-', $shipping_method_title)[1];
      }
    }
    return $shipping_method;
  }

  public function test()
  {

    $all_poses = $this->get_current_cart();
    return $all_poses;
  }
}
