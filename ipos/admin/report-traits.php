<?php
$prefix = 'test';
$wp_root_path = ABSPATH;
require_once $wp_root_path . '/vendor/autoload.php';
putenv("GOOGLE_APPLICATION_CREDENTIALS=" . __DIR__ . '/' . $prefix . '_credentials.json');

use Google\Analytics\Data\V1beta\BetaAnalyticsDataClient;
use Google\Analytics\Data\V1beta\DateRange;
use Google\Analytics\Data\V1beta\Dimension;
use Google\Analytics\Data\V1beta\Metric;

trait ReportTraits
{
  public function get_google_analytics_users()
  {
    $credentialsPath = __DIR__ . '/credentials.json';
    $client = new BetaAnalyticsDataClient([
      'keyFilename' => $credentialsPath
    ]);

    $propertyId = get_option('woo_ipos_ga_property_id_setting');

    $dateRange = new DateRange();
    $dateRange->setStartDate('2023-01-01');
    $dateRange->setEndDate('2023-06-30');

    $metric = new Metric();
    $metric->setName('activeUsers');

    $response = $client->runReport([
      'property' => 'properties/' . $propertyId,
      'dateRanges' => [$dateRange],
      'metrics' => [$metric],
    ]);

    // Process the response or perform other actions based on your requirements
    $rows = $response->getRows();
    $userCount = 0;
    foreach ($rows as $row) {
      $users = $row->getMetricValues()[0]->getValue();
      $userCount += $users;
    }
    return $userCount;
  }

  public function get_woocommerce_report()
  {
    try {
      $product_args = array(
        'post_type' => 'product',
        'post_status' => 'publish',
        'posts_per_page' => -1,
      );
      //get products query
      $products = get_posts($product_args);
      $product_count = count($products);
      $today = date('Y-m-d');
      $first_day_of_year = date('Y-01-01');
      $products_new = array_filter($products, function ($product) use ($today, $first_day_of_year) {
        $product_date = $product->post_date;
        return ($product_date >= $first_day_of_year && $product_date <= $today);
      });
      $new_product_count = count($products_new);

      //get orders query
      // $order_args = array(
      //   'posts_per_page' => -1,
      //   'type' => 'shop_order'
      // );
      // $orders = wc_get_orders($order_args);
      $query = new WC_Order_Query(array(
        'limit' => -1,
        'orderby' => 'date',
        'order' => 'DESC',
        'return' => 'ids',
      ));
      $orders = $query->get_orders();

      //loop through orders to get data
      $order_count = count($orders);
      $order_data = array();
      $completed_order_count = 0;
      $cancelled_order_count = 0;
      $order_total_sum = 0;

      $today = new DateTime(); // Get current date
      $year_start = new DateTime(date('Y-01-01'));
      foreach ($orders as $orderId) {
        $order = wc_get_order($orderId);
        $order_id  = $order->get_id();
        $order_status  = $order->get_status();
        $payment_method = $order->get_payment_method();
        $date_created  = $order->get_date_created();
        $date_modified  = $order->get_date_modified();
        $order_total = $order->get_total();
        if ($date_created >= $year_start) {
          $order_data[] = array(
            'order_id' => $order_id,
            'order_status' => $order_status,
            'payment_method' => $payment_method,
            'date_created' => $date_created->format('Y-m-d H:i:s'),
            'date_modified' => $date_modified->format('Y-m-d H:i:s'),
            'order_total' => $order_total,
          );
          if ($order_status == 'completed' || $order_status == 'processing' && $payment_method == 'cod') {
            $completed_order_count += 1;
            $order_total_sum += $order_total;
          }
          if ($order_status == 'cancelled' || $order_status == 'failed') {
            $cancelled_order_count += 1;
          }
        }
      }
      $newObject = [
        "soLuongTruyCap" => $this->get_google_analytics_users(),
        "soNguoiBan" => 1,
        "soNguoiBanMoi" => 1,
        "tongSoSanPham" => $product_count,
        "soSanPhamMoi" => $new_product_count,
        "soLuongGiaoDich" => $order_count,
        "tongSoDonHangThanhCong" => $completed_order_count,
        "tongSoDongHangKhongThanhCong" => $cancelled_order_count,
        "tongGiaTriGiaoDich" => $order_total_sum,
      ];

      return $newObject;
    } catch (Exception $e) {
      return $e->getMessage();
    }
  }


  public function woo_ipos_report_callback($request)
  {
    return array('data' => $this->get_woocommerce_report());
  }

  public function woo_ipos_test_callback($request)
  {
    return array('data' => $this->check_voucher_valid);
  }
}
