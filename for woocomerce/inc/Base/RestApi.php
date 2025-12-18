<?php
/*
  @package Tranzak_Payment_Gateway
*/

namespace Tranzak_PG\Base;

class RestApi extends BaseController{
  public function register(){

    add_action('rest_api_init', function () {
      //Path to REST route and the callback function
      register_rest_route('tranzak-payment-gateway/v1', '/request/verify', array(
        'methods' => 'POST',
        'application-type' => 'text/json',
        'callback' => array($this, 'pollMapi'),
        'permission_callback' => function(){
          return true;
        },
      ));
    });

    add_action('rest_api_init', function () {
      //Path to REST route and the callback function
      register_rest_route('tranzak-payment-gateway/v1', '/request/cancel', array(
        'methods' => 'POST',
        'application-type' => 'text/json',
        'callback' => array($this, 'cancelRequest'),
        'permission_callback' => function(){
          return true;
        },
      ));
    });

    add_action('rest_api_init', function () {
      //Path to REST route and the callback function
      register_rest_route('tranzak-payment-gateway/v1', '/donation/create', array(
        'methods' => 'POST',
        'application-type' => 'text/json',
        'callback' => array($this,'tzCreateDonation'),
        'permission_callback' => function(){
          return true;
        },
      ));
    });

    add_action('rest_api_init', function () {
      //Path to REST route and the callback function
      register_rest_route('tranzak-payment-gateway/v1', '/webhook', array(
        'methods' => 'POST',
        'application-type' => 'text/json',
        'callback' => array($this, 'tzWebhook'),
        'permission_callback' => function(){
          return true;
        },
      ));
    });

    add_action('rest_api_init', function () {
      //Path to REST route and the callback function
      register_rest_route('tranzak-payment-gateway/v1', '/donation/get-progress', array(
        'methods' => 'POST',
        'application-type' => 'text/json',
        'callback' => array($this,'getDonationProgress'),
        'permission_callback' => function(){
          return true;
        },
      ));
    });

    add_filter( 'http_request_timeout', array($this, 'extentRequestTimeout') );
  }

  public function extentRequestTimeout( $time )
  {
      return 60;
  }

  public function cancelRequest($req)
  {
    $nonce = isset($_GET['_wpnonce'])? $_GET['_wpnonce']: null;
    if(!$nonce || !wp_verify_nonce($nonce,'wp_rest')){
      return $transaction->failedResponse('Invalid parameters');
    }
    
    if(!isset($_POST['id'])){
      return $transaction->failedResponse('Invalid parameters');
    }

    $transaction = new Transactions();

    $id = sanitize_text_field($_POST['id']);
    $data = $transaction->cancelMapiRequest($id);
    if($data && $data['status']){
      return $transaction->successResponse($data);
    }

    return $transaction->failedResponse('Failed to cancel transaction');
  }

  public function pollMapi($req)
  {
    $nonce = isset($_GET['_wpnonce'])? $_GET['_wpnonce']: null;
    if(!$nonce || !wp_verify_nonce($nonce,'wp_rest')){
      return $transaction->failedResponse('Invalid parameters');
    }
    
    if(!isset($_POST['id'])){
      return $transaction->failedResponse('Invalid parameters');
    }

    $transaction = new Transactions();

    $id = sanitize_text_field($_POST['id']);
    $data = $transaction->getMapiRequest($id);
    if($data && $data['status']){
      return $transaction->successResponse($data);
    }

    return $transaction->failedResponse('Failed to get transaction detail');
  }

  public function tzCreateDonation($req)
  {
    $nonce = isset($_GET['_wpnonce'])? $_GET['_wpnonce']: null;
    if(!$nonce || !wp_verify_nonce($nonce,'wp_rest')){
      return $transaction->failedResponse('Invalid parameters');
    }

    $donation = new Donation();
    $transactions = new Transactions();
    if(!isset($_POST['id']) || !isset($_POST['amount']) || !isset($_POST['currency']) || !isset($_POST['title'])){
      return $transactions->failedResponse('Invalid parameters parsed');
    }

    $id = sanitize_text_field($_POST['id']);
    $clientDonation = $donation->getDonation($id);

    if(!$clientDonation){
      return $transactions->failedResponse('The item you are trying to pay to has expired or does not exist');
    }

    $amount = sanitize_text_field($_POST['amount']);
    $id = sanitize_text_field($_POST['id']);
    $currency = sanitize_text_field($_POST['currency']);
    $title = sanitize_text_field($_POST['title']);
    $description = "Payment:  $title";

    if($clientDonation->amount > 0){
      $amount = $clientDonation->amount;
    }

    $currency = $clientDonation->currency;

    $data = false;
    $insertId = $transactions->createTransaction($amount, $currency, $description, 2, $id);
    if ($insertId) {
      $request = $transactions->createMapiTransaction($amount, $currency, $description, $insertId, '', array(), false);

      if($request['success'] == true) {
        $data = $request['data'];
        if($data && $data['status']){
          return $transactions->successResponse($data);
        }
      }

    }

    return $transactions->failedResponse('Failed to get transaction detail');
  }

  public function tzWebhook($req)
  {
    $data = json_decode($req->get_body());
    if($data->eventType == 'REQUEST.COMPLETED'){
      if($this->pluginOptions['auth_key'] == $data->authKey && $data->resource->status == 'SUCCESSFUL'){
        $transaction = new Transactions();

        $transactionId = explode(' ',$data->resource->mchTransactionRef)[0];
        $updatedTransaction = $transaction->updateTransaction($transactionId, 2, $data->resource->transactionId);

        if($updatedTransaction){
          $wpTransaction = $updatedTransaction;

          if($wpTransaction->origin == 1){
            $order = $transaction->updateOrder($wpTransaction->reference);
          }
        }
      }

    }else if($data->eventType == 'REQUEST.CANCELLED'){
      if($this->pluginOptions['auth_key'] == $data->authKey){
        $transaction = new Transactions();
        $transactionId = explode(' ',$data->resource->mchTransactionRef)[0];
        $wpTransaction = $transaction->getTransaction($transactionId);
        if($wpTransaction && $wpTransaction->status == 1){
          $updatedTransaction = $transaction->updateTransaction($transactionId, -1, $data->resource->transactionId ?? '');
        }
      }
    };
  }

  public function getDonationProgress($req)
  {
    $nonce = isset($_POST['_wpnonce'])? $_POST['_wpnonce']: null;
    if(!wp_verify_nonce($_POST['_wpnonce'], 'tzpg_auth_nonce')){
      die();
    }
    $transaction = new Transactions();

    if(!isset($_POST['id'])){
      return $transaction->failedResponse('Donation id is required');
    }
    $id = sanitize_text_field($_POST['id']);

    $model = new Donation();
    $donation = $model->getDonation($id);

    if(!$donation){
      return $transaction->failedResponse('Donation not found');
    }

    $data['shouldRender'] = false;
    $target = $donation->target;
    if($target <= 0){
      return $transaction->successResponse($data);
    }

    $data['shouldRender'] = true;

    $total = 0;
    $percentage = 0;


    $transactions = $model->getTransactions($id);
    if($transactions){
      foreach($transactions as $item){
        $total+= $item->amount;
      }
    }

    if($total > 0){
      $percentage = round(($total * 100) / $target, 2);
    }

    $data['title'] = $donation->title;
    $data['target'] = $target;
    $data['total'] = $total;
    $data['percentage'] = $percentage;
    $data['bg'] = $donation->background_color;
    $data['currency'] = $donation->currency;

    return $transaction->successResponse($data);
  }

}
