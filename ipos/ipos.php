<?php
/**
 * Plugin Name: IPOS
 * Description: A plugin that add Woocommerce integration to IPOS.
 * Version: 1.0
 * Author: auditt98
 * Author URI: 
 */

function ipos_settings_page() {
  if (!current_user_can('manage_options')) {
    wp_die('You do not have sufficient permissions to access this page.');
  }
  ?>
  <div class="wrap">
    <h1>IPOS Settings Page</h1>
  </div>

  <?php
}

// Display the IPOS page
function ipos_page() {
  // Check if the user is authorized to access the page
  if (!current_user_can('manage_options')) {
    wp_die('You do not have sufficient permissions to access this page.');
  }

  // Output the page content
  ?>
  <div class="wrap">
    <h1>IPOS API</h1>
  </div>
  <?php
}

function ipos_menu() {
  add_menu_page(
    'IPOS',
    'IPOS',
    'manage_options',
    'ipos-api',
    'ipos_page'
  );

  add_submenu_page('ipos-api', 'IPOS Settings', 'IPOS Settings', 'manage_options', 'ipos-settings', 'ipos_settings_page');
}

// Add the IPOS menu item
add_action('admin_menu', 'ipos_menu');