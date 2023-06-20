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

  //Lấy SKU sản phẩm từ ID
  public function get_product_sku_from_id($id)
  {
    $product = wc_get_product($id);
    return $product->get_sku();
  }

  public function apply_voucher($request)
  {
    $voucherId = $request->get_param('voucherId');
    return $voucherId;
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
    // return json_encode(WC()->cart->get_cart());
    return json_encode($this->get_vouchers());
  }

  public function test()
  {

    $all_poses = $this->get_current_cart();
    return $all_poses;
  }
}
