<?php

trait OrderTraits
{
  public function get_pos()
  {
    $api_key = get_option('woo_ipos_api_key_setting');
    $pos_parent = get_option('woo_ipos_pos_parent_setting');
    $current_user = wp_get_current_user();
    $current_user_login = $current_user->user_login;

    $get_pos_info_url = 'pos';
    $get_pos_info_method = 'GET';
    $query_params = array(
      'access_token' => $api_key,
      'pos_parent' => $pos_parent
    );

    $response = $this->call_api($get_pos_info_url, $get_pos_info_method, array('Content-Type: application/json'), "", $query_params);
    $all_poses = $response->data;
    return $all_poses;
  }

  public function check_voucher_valid()
  {
    $product = wc_get_product(13831);
    $datas = [];

    // Get all metadata
    $metadata = $product->get_meta_data();

    // Loop through each metadata object
    foreach ($metadata as $meta) {
      // Get the meta key and value
      $meta_key = $meta->key;
      $meta_value = $meta->value;

      array_push($datas, $meta);
    }
    return $datas;
    $api_key = get_option('woo_ipos_api_key_setting');
    $pos_parent = get_option('woo_ipos_pos_parent_setting');
    $current_user = wp_get_current_user();
    $current_user_login = $current_user->user_login;

    $check_voucher_url = 'check_voucher';
    $check_voucher_method = 'POST';
    $query_params = array(
      'access_token' => $api_key,
    );
    // foreach ($meta_data as $meta) {
    //   // Get the meta key and value
    //   $meta_key = $meta->key;
    //   $meta_value = $meta->value;
    //   $meta = [
    //     'meta_key' => $meta_key,
    //     'meta_value' => $meta_value,
    //   ];
    //   array_push($metas, $meta);
    //   // Output or process the meta data as needed
    //   echo "Meta Key: " . $meta_key . "<br>";
    //   echo "Meta Value: " . $meta_value . "<br>";
    // }
    foreach (WC()->cart->get_cart() as $cart_item) {

      // get the data of the cart item
      $product_id         = $cart_item['product_id'];
      $variation_id       = $cart_item['variation_id'];

      // gets the cart item quantity
      $quantity           = $cart_item['quantity'];
      // gets the cart item subtotal
      $line_subtotal      = $cart_item['line_subtotal'];
      $line_subtotal_tax  = $cart_item['line_subtotal_tax'];
      // gets the cart item total
      $line_total         = $cart_item['line_total'];
      $line_tax           = $cart_item['line_tax'];
      // unit price of the product
      $item_price         = $line_subtotal / $quantity;
      $item_tax           = $line_subtotal_tax / $quantity;

      // gets the product object
      $product            = $cart_item['data'];
      // get the data of the product
      $sku                = $product->get_sku();
      $name               = $product->get_name();
      $regular_price      = $product->get_regular_price();
      $sale_price         = $product->get_sale_price();
      $price              = $product->get_price();
      $stock_qty          = $product->get_stock_quantity();
      // attributes
      $attributes         = $product->get_attributes();
      $attribute          = $product->get_attribute('pa_attribute-name'); // // specific attribute eg. "pa_color"
      // custom meta
      $custom_meta        = $product->get_meta('_custom_meta_key', true);
      // product categories
      $categories         = wc_get_product_category_list($product->get_id()); // returns a string with all product categories separated by a comma
      $data = [
        'product_id' => $product_id,
        'variation_id' => $variation_id,
        'quantity' => $quantity,
        'line_subtotal' => $line_subtotal,
        'line_subtotal_tax' => $line_subtotal_tax,
        'line_total' => $line_total,
        'line_tax' => $line_tax,
        'item_price' => $item_price,
        'item_tax' => $item_tax,
        'sku' => $sku,
        'name' => $name,
        'regular_price' => $regular_price,
        'sale_price' => $sale_price,
        'price' => $price,
        'stock_qty' => $stock_qty,
        'attributes' => $attributes,
        'attribute' => $attribute,
        'custom_meta' => $custom_meta,
        'categories' => $categories
      ];
      array_push($datas, $data);
    }
    return json_encode($datas);
    // $json_body = json_encode(array('phone' => $user_login));
    // $response = $this->call_api($check_voucher_url, $check_voucher_method, array('Content-Type: application/json'), "", $query_params);

  }
}
