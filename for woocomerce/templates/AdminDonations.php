<?php

  if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
  
  use Tranzak_PG\Base\Transactions;
  use Tranzak_PG\Base\Donations;

  $donations = new Donations();

  $action = sanitize_text_field($_GET['action'] ?? null);
  $element = sanitize_text_field($_GET['element'] ?? null);



  if( !empty($action) && $action == 'delete' && !empty($element)){
    $deleted = $donations->deleteItem($element);
  }
  


  echo '<div class="wrap"><h1 class="wp-heading-inline">Donations</h1>
  <a href="'.admin_url( '/admin.php?page=tranzak_payment_gateway_donations_add').'" class="page-title-action">Add new</a><br /><br /><hr />';
  echo '<form method="post">';
  // Prepare table
  $donations->prepare_items();
  // Search form
  $donations->search_box('search', 'search_id');
  // Display table
  $donations->display();
  wp_nonce_field( 'tzpg_auth_nonce' );
  echo '</div><donations/form>';


?>