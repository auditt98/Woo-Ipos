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

      if (isset($cart_item['variation_id'])) {
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

  public function apply_voucher($request)
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

      $data = $_POST;
      //VOUCHER ID
      $voucherId = $data['voucherId'];

      //POS Parent
      $pos_parent = get_option('woo_ipos_pos_parent_setting');

      //Featured POS
      $featured_pos = $this->get_featured_pos();

      $current_cart = $this->parse_current_cart();

      $current_user = wp_get_current_user();

      //CURRENT USER LOGIN
      $current_user_login = $current_user->user_login;

      $order_data_item = array();

      foreach ($current_cart as $cart_item) {
        $item = array();
        //
        $sku = '';
        $variation_id = $cart_item['variation_id'];

        //SET SKU
        if (isset($cart_item['variation_sku'])) {
          $sku = $cart_item['variation_sku'];
        } else {
          $sku = $cart_item['product_sku'];
        }

        //parse sku
        $sku_parts = $this->parse_product_sku($sku);
        $item_type_id = $sku_parts['type_id']; //id of the type of product
        $item_id = $sku_parts['store_id']; //id of product 
        $item_product_type = $sku_parts['product_type']; //Depends on SKU, can be COMBO, NORMAL

        //get product name
        $product_name = $cart_item['product_name'];
        //if variation id is set, use variation price
        $item_price = $cart_item['product_price'];

        if (isset($variation_id)) {
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
      return $request;
      $response = $this->call_api($check_voucher_url, $check_voucher_method, array('Content-Type: application/json'), json_encode($request), $query_params);
      return $response;
    } catch (Exception $e) {
      return $e->getMessage();
    }
  }

  public function add_vouchers_to_checkout_form()
  {
    $vouchers = $this->get_vouchers();
?>
    <div id="custom_section">
      <h3>Voucher Khuyến mãi</h3>
      <button class="show-vouchers-btn">Show Vouchers</button>
    </div>

    <div id="voucher-popup" class="popup">
      <div class="popup-content">
        <span class="close">&times;</span>
        <div class="voucher-container">
          <?php foreach ($vouchers as $voucher) : ?>
            <div class="voucher" data-voucher-code="<?php echo $voucher->voucher_code; ?>">
              <div class='voucher-label'><?php echo $voucher->voucher_label; ?></div>
              <div class='voucher-desc'><?php echo $voucher->voucher_desc; ?></div>
              <div class='voucher-exp'><?php echo $voucher->voucher_display_expiry; ?></div>
            </div>
          <?php endforeach; ?>
        </div>
      </div>
    </div>

    <script>
      jQuery(function($) {
        $('.show-vouchers-btn').click(function() {
          $('#voucher-popup').addClass('show');
          return false;
        });

        $('.close').click(function() {
          $('#voucher-popup').removeClass('show');
          return false;
        });
      });
    </script>

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
        var xhr = new XMLHttpRequest();
        xhr.open('POST', '/wp-json/woo-ipos/v1/apply_voucher'); // Replace 'verify_voucher.php' with the actual file or endpoint for voucher verification
        xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
        xhr.onload = function() {
          if (xhr.status === 200) {
            var response = JSON.parse(xhr.responseText);
            console.log(response); // Log or process the response data from the backend
          } else {
            console.error('Voucher verification failed. Status code: ' + xhr.status);
          }
        };
        xhr.send('voucherId=' + voucherId);
      }
    </script>

    <style>
      .voucher {
        flex-basis: 47%;
        max-width: 47%;
        padding: 30px 20px;
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

      .popup {
        display: none;
        position: fixed;
        z-index: 999;
        left: 0;
        top: 0;
        width: 100%;
        height: 100%;
        overflow: auto;
        background-color: rgba(0, 0, 0, 0.4);
      }

      .popup.show {
        display: block !important;
      }

      .popup-content {
        background-color: #fefefe;
        margin: 10% auto;
        padding: 20px 10px 20px 20px;
        border: 1px solid #888;
        width: 80%;
        max-width: 600px;
        position: relative;
        overflow: auto;
        height: 100%;
        max-height: 500px;
      }

      .close {
        color: #aaa;
        float: right;
        font-size: 28px;
        font-weight: bold;
        position: sticky;
        cursor: pointer;
        top: 0;
        right: 0;
      }

      .close:hover,
      .close:focus {
        color: #000;
        text-decoration: none;
        cursor: pointer;
      }

      .voucher-container {
        display: flex;
        flex-wrap: wrap;
        max-height: 450px;
      }
    </style>
<?php
  }


  public function test_order()
  {
    $all_poses = $this->parse_current_cart();
    return json_encode($all_poses);
    // return json_encode($this->get_vouchers());
  }

  public function test()
  {

    $all_poses = $this->get_current_cart();
    return $all_poses;
  }
}
