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
      $data = $response->data;
      //check discount amount, if there's discount amount, add a fee of -discount_amount
      if (isset($data->Discount_Amount) && $data->Discount_Amount != 0) {
        WC()->session->set('applied_voucher', $data->Coupon_Code);
        error_log('------------------' . $data->Coupon_Code . '------------------');
        $discount_amount = $data->Discount_Amount;
        $splittedName = explode("_", $data->voucher_campaign_name);
        $label = $splittedName[0];
        $cart->add_fee($label, -$discount_amount);
      } else {
      }
    }
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

      //Featured POS
      $featured_pos = $this->get_featured_pos();
      $current_user = wp_get_current_user();
      $current_cart = $this->parse_current_cart();
      // echo json_encode($current_cart);
      //CURRENT USER LOGIN
      $current_user_login = $current_user->user_login;

      $order_data_item = array();
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
      }

      $request = array(
        'pos_id' => $featured_pos,
        'pos_parent' => $pos_parent,
        'voucher_code' => $voucherId,
        'membership_id' => $current_user_login,
        'order_data_item' => $order_data_item
      );

      $response = $this->call_api($check_voucher_url, $check_voucher_method, array('Content-Type: application/json'), json_encode($request), $query_params);
      session_start();
      $_SESSION['voucher_response'] = serialize($response);
      $_SESSION['voucher_code'] = $voucherId;
      // $cart->calculate_totals();
      WC()->cart->calculate_totals();
      wp_send_json_success($response);
    } catch (Exception $e) {
      wp_send_json_error($e->getMessage(), 500);
    }
  }

  public function add_vouchers_to_checkout_form()
  {
    $vouchers = $this->get_vouchers();
?>
    <div id="custom_section">
      <h3>Voucher Khuyến mãi</h3>
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

      function verifyVoucher(voucherId) {
        jQuery.ajax({
          url: ajaxurl, // The AJAX URL provided by WordPress
          type: 'POST',
          data: {
            action: 'apply_voucher_action', // The name of the AJAX action
            voucher_id: voucherId
          },
          success: function(response) {
            if (response?.data?.error?.message) {
              alert(response.data.error.message);
            }
            if (response?.data?.data?.Coupon_Code) {
              jQuery('#applied_voucher_input').val(response.data.data.Coupon_Code);
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

  public function handle_new_order($order_id)
  {
    ini_set('log_errors_max_length', '10000');
    // Get the order object
    $order = wc_get_order($order_id);
    $order_data = $order->get_data();

    //ITEMS
    $order_items_data = array_map(function ($item) {
      return $item->get_data();
    }, $order->get_items());

    //FEES
    $order_fee_data = array_map(function ($item) {
      return $item->get_data();
    }, $order->get_fees());
  }

  public function handle_logged_in_order($id)
  {
    //CONST
    $csb_thaiha_pos_id = get_option('woo_ipos_pos_id_csb_thaiha_setting');
    $csb_trunghoa_pos_id = get_option('woo_ipos_pos_id_csb_trunghoa_setting');
    //CONST
    $csb_thaiha_pos_id = get_option('woo_ipos_pos_id_csb_thaiha_setting');
    $csb_trunghoa_pos_id = get_option('woo_ipos_pos_id_csb_trunghoa_setting');

    //SETUP API CALLS
    $order_request = array();
    $api_key = get_option('woo_ipos_api_key_setting');
    $pos_parent = get_option('woo_ipos_pos_parent_setting');

    $order_request['pos_parent'] = $pos_parent;
    $order_request['created_by'] = 'API';

    $featured_pos = $this->get_featured_pos();

    //GET CURRENT USER
    $current_user = wp_get_current_user();
    $current_user_login = $current_user->user_login;

    $order_request['user_id'] = $current_user_login;
    $ipos_customer = $this->get_ipos_user();
    $order_request['username'] = $ipos_customer->name;
    $order_request['return_data'] = 'full';
    $order_request['is_estimate'] = 0;

    $order = wc_get_order($id);
    $order_request['foodbook_code'] = $id;

    $order_data = $order->get_data(); // The Order data
    $order_items_data = array_map(function ($item) {
      return $item->get_data();
    }, $order->get_items());
    $order_fee_data = array_map(function ($item) {
      return $item->get_data();
    }, $order->get_fees());
    $order_data['order_items'] = $order_items_data;
    $order_data['order_fees'] = $order_fee_data;

    $order_shipping_lines = array_map(function ($item) {
      return $item->get_data();
    }, $order->get_shipping_methods());
    //parse shipping lines
    $order_data['order_shipping_lines'] = $order_shipping_lines;

    //parse shipping method
    $order_data['shipping_method'] = $this->parse_order_shipping_method($order_data);

    //handle order shipping logic
    //if deli
    if ($order_data['shipping_method']['is_delivery']) {
      $order_request['order_type'] = 'DELIAT';
      $order_request['pos_id'] = $featured_pos['delivery'];
      $order_request['ship_price_real'] = $order_data['total'];
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
    //HANDLE NOTE
    $order_request['note'] = $order_data['customer_note'];

    //HANDLE FEES (DISCOUNT)
    $voucher_code = WC()->session->get('voucher_code');
    if ($voucher_code) {
      $order_request['coupon_log_id'] = $voucher_code;
    }
    //loop through order_fees
    $fee_total = 0;
    foreach ($order_data['order_fees'] as $fee) {
      //convert $fee['total'] to int and make it absolute
      $fee_line = absint($fee['total']);
      $fee_total += $fee_line;
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
      $payment_info['Payment_Info'] = 'ONEPAY'; //ma giao dich
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
    $order_request['Booking_Info'] = $booking_info;

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
    $order_item = array();

    //loop through order_data['order_items']
    foreach ($order_data['order_items'] as $key => $order_data_item) {
      $order_item = array();

      $product_id = $order_item['product_id'];
      $variation_id = $order_item['variation_id'];
      //if variation_id is not null and not 0
      if ($variation_id && $variation_id != 0) {
        $product = wc_get_product($variation_id);
      } else {
        $product = wc_get_product($product_id);
      }
      $sku = $product->get_sku();
      //split sku by -
      $sku_arr = explode('-', $sku);
      $item_type_id = $sku_arr[0];
      $item_id = $sku_arr[1];
      $order_item['Item_Type_Id'] = $item_type_id;
      $order_item['Item_Id'] = $item_id;
      //replace </span> with empty string
      $order_item['Item_Name'] = str_replace('</span>', '', $order_data_item['name']);
      $order_item['Quantity'] = $order_data_item['quantity'];
      //loop through meta_data
      foreach ($order_data_item['meta_data'] as $key => $meta) {
        # code...
      }

      // Parent_Id
    }



    $test_data = array(
      'order_id' => $id,
      'order_data' => $order_data,
      'order_request' => $order_request
    );
    return json_encode($test_data);
  }

  public function handle_logged_out_order($id)
  {
  }

  public function test_order($attr)
  {
    //TEST CODE TO GET ORDER ID
    $attr = shortcode_atts(array(
      'id' => 0
    ), $attr);
    $id = $attr['id'];

    // get order from id
    if (!$id || $id == 0) {
      return 'No order id';
    }
    //check if user is logged in 
    if (!is_user_logged_in()) {
      return $this->handle_logged_out_order($id);
    } else {
      return $this->handle_logged_in_order($id);
    }
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
