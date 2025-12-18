<?php
use Tranzak_PG\Base\BaseController;
use Tranzak_PG\Base\Transactions;



/**
 * @link              https://tranzak.net
 * @since             1.0.0
 * @package           Tranzak_Payment_Gateway
 *
 * @wordpress-plugin
 * Plugin Name:       Tranzak Payment Gateway
 * Plugin URI:        https://github.com/tranzak/wordpress-payment-plugin
 * Description:       TRANZAK is the fast, easy, and safe way to collect and send payments in Cameroon and Africa at large.
 * Version:           1.0.1
 * Author:            Tranzak Core Team
 * Tags:              MTN Mobile Money, Orange money, Cameroon, WooCommerce, Payment, Gateway
 * Author URI:        https://tranzak.net
 * Stable tag:        1.0.1
 * License:           GPL-2.0+
 * Requires at least: 5.7.9
 * Requires PHP:       7.3
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       tranzak-payment-gateway
 * Domain Path:       /languages
 */


// If this file is called directly, abort.
if (!defined('WPINC')) {
  die('What are you doing here? Silly human');
}


use Tranzak_PG\Base\Activation;
use Tranzak_PG\Base\Deactivation;
use Tranzak_PG\Base\Encryption;

if (file_exists(dirname(__FILE__) . '/vendor/autoload.php')) {
  require_once(dirname(__FILE__) . '/vendor/autoload.php');
}

define('Tranzak_PG_ENC_SALT', '24778c060c3b9ee6fdaf33fae48801c76df6debb');


if(defined("CRYPT_BLOWFISH") && CRYPT_BLOWFISH) {
  $token = "The quick brown fox jumps over the lazy dog.";

  $encryption_key = 'CKXH2U9RPY3EFD70TLS1ZG4N8WQBOVI6AMJ5';
  $cryptor = new Encryption();
  $crypted_token = $cryptor->encrypt($token);

  $decrypted_token = $cryptor->decrypt($crypted_token);

}

function activate_tz_payment_gateway()
{
  Activation::activate();
  Activation::createDefaultPage();
}

function deactivate_tz_payment_gateway()
{
  Deactivation::deactivate();
}

register_activation_hook(__FILE__, 'activate_tz_payment_gateway');
register_deactivation_hook(__FILE__, 'deactivate_tz_payment_gateway');

if (class_exists('Tranzak_PG\\Init')) {
  Tranzak_PG\Init::register_services();
}




function register_woocommerse()
{
  if (class_exists('WC_Payment_Gateway')) {
    /**
     * Check if woocommerce exist
     */
    $activePlugins = apply_filters('active_plugins', get_option('active_plugins'));
    if (!in_array('woocommerce/woocommerce.php', $activePlugins)) {
      return;
    }

    if (!class_exists('TzPgCreditCards')) {
      class TzPgCreditCards extends WC_Payment_Gateway
      {
        public $baseController = [];
        public $transactions;
        public function __construct()
        {
  
          $this->transactions = new Transactions();
  
          $this->slug = 'tz-visa-mastercard-woo-gateway';
  
          $this->baseController = new BaseController();
          $this->id = 'tranzak_visa_mastercard_woo';
          $this->icon = apply_filters($this->baseController->pluginPageUrl . '_woo_icon', $this->baseController->pluginUrl . 'assets/img/visa_mastercard.jpg');
          $this->method_title = 'Tranzak Payment Gateway';
          $this->method_description = __('You can receive payments through Credit Cards (VISA & Mastercard)', 'tranzak-payment-gateway');
  
  
          $this->init_form_fields();
          $this->init_settings();
  
          $this->has_fields = false;
          $this->title = $this->get_option('title');
          $this->description = $this->get_option('description');
  
          $status = $this->get_option('order_stat');
          $this->order_stat = str_starts_with($status, 'wc-') ? $status : 'wc-' . $status;
  
          $this->register_hooks();
        }
  
        public function register_hooks()
        {
          add_action('woocommerce_update_options_payment_gateways_' . $this->id, array($this, 'process_admin_options'));
        }
  
        public function init_form_fields()
        {
  
          $defaultOption = get_option('tranzak_payment_gateway');
  
          $this->form_fields = array(
            'enabled' => array(
              'title' => __('Enable/Disable', 'tranzak-payment-gateway'),
              'type' => 'checkbox',
              'label' => __('Enable Tranzak Payment Gateway', 'tranzak-payment-gateway'),
              'default' => 'no'
            ),
            'order_stat' => array(
              'title' => __('Order Status', 'tranzak-payment-gateway'),
              'type' => 'select',
              'description' => __('Default order status when placed.', 'tranzak-payment-gateway'),
              'default' => empty($pending) ? current(wc_get_order_statuses()) : current($pending),
              'desc_tip' => true,
              'options' => wc_get_order_statuses(),
            ),
            'title' => array(
              'title' => __('Title', 'tranzak-payment-gateway'),
              'type' => 'text',
              'description' => __('This controls the title which the user sees during checkout.', 'tranzak-payment-gateway'),
              'default' => __('Credit / Debit cards (VISA & Mastercard)', 'tranzak-payment-gateway'),
              'desc_tip' => true
            ),
            'description' => array(
              'title' => __('Description', 'tranzak-payment-gateway'),
              'type' => 'textarea',
              'description' => __($this->baseController->settingsPageTemplate, 'tranzak-payment-gateway'),
              'default' => __('Pay with Credit and Debit cards (VISA & Mastercard)', 'tranzak-payment-gateway'),
              'desc_tip' => false
            )
          );
        }
  
        /**
         * query mapi and get transaction status
         */
  
        public function process_payment($orderId)
        {
  
  
          $order = new WC_Order($orderId);
          $order = wc_get_order($order);
  
          $currency = $order->get_currency();
          $amount = $order->get_total();
          $orderId = $order->get_id();
          $websiteTitle = $this->baseController->websiteTitle;
          $websiteUrl = get_bloginfo('url');
          $description = "New purchase for website ($websiteTitle - $websiteUrl). Payment for order #$orderId";
  
          // Mark as set order status (we're awaiting the payment).
          $order->update_status($this->order_stat, sprintf(__('Awaiting %s payment.', 'tranzak-payment-gateway'), $this->method_title));
  
  
          /**
           *  call mapi here
           */
          $request = false;
  
          $transaction = false;
          $insertId = $this->transactions->createTransaction($amount, $currency, $description, 1, $orderId, $this->get_return_url($order));
          if ($insertId) {
            $request = $this->transactions->createMapiTransaction($amount, $currency, $description, $insertId);
            if($request && $request['success'] == true){
              $transaction = $request['data'];
              if ($transaction) {
                // Reduce stock levels.
                wc_reduce_stock_levels($orderId);
      
                // Remove cart.
                WC()->cart->empty_cart();
                // Return thankyou redirect.
                return array(
                  'result' => 'success',
                  'redirect' => $transaction['links']['paymentAuthUrl'],
                );
              }
            }

            throw new Exception($request['errorMsg']);
          }

          throw new Exception("Failed to place order");
  
        }
      }
    }

    if (!class_exists('TzPgMTNMoMoRedirect')) {
      class TzPgMTNMoMoRedirect extends WC_Payment_Gateway
      {
        public $baseController = [];
        public $transactions;
        public function __construct()
        {
  
          $this->transactions = new Transactions();
  
          $this->slug = 'tz-mtn-momo-redirect-woo-gateway';
  
          $this->baseController = new BaseController();
          $this->id = 'tranzak_mtn_momo_redirect_woo';
          $this->icon = apply_filters($this->baseController->pluginPageUrl . '_woo_icon', $this->baseController->pluginUrl . 'assets/img/momo.jpg');
          $this->method_title = 'Tranzak Payment Gateway';
          $this->method_description = __('You can receive payments through MTN Mobile Money', 'tranzak-payment-gateway');
  
  
          $this->init_form_fields();
          $this->init_settings();
  
          $this->has_fields = false;
          $this->title = $this->get_option('title');
          $this->description = $this->get_option('description');
  
          $status = $this->get_option('order_stat');
          $this->order_stat = str_starts_with($status, 'wc-') ? $status : 'wc-' . $status;
  
          $this->register_hooks();
        }
  
        public function register_hooks()
        {
          add_action('woocommerce_update_options_payment_gateways_' . $this->id, array($this, 'process_admin_options'));
        }
  
        public function init_form_fields()
        {
  
          $defaultOption = get_option('tranzak_payment_gateway');
  
          $this->form_fields = array(
            'enabled' => array(
              'title' => __('Enable/Disable', 'tranzak-payment-gateway'),
              'type' => 'checkbox',
              'label' => __('Enable Tranzak Payment Gateway', 'tranzak-payment-gateway'),
              'default' => 'no'
            ),
            'order_stat' => array(
              'title' => __('Order Status', 'tranzak-payment-gateway'),
              'type' => 'select',
              'description' => __('Default order status when placed.', 'tranzak-payment-gateway'),
              'default' => empty($pending) ? current(wc_get_order_statuses()) : current($pending),
              'desc_tip' => true,
              'options' => wc_get_order_statuses(),
            ),
            'title' => array(
              'title' => __('Title', 'tranzak-payment-gateway'),
              'type' => 'text',
              'description' => __('This controls the title which the user sees during checkout.', 'tranzak-payment-gateway'),
              'default' => __('MTN Mobile Money', 'tranzak-payment-gateway'),
              'desc_tip' => true
            ),
            'description' => array(
              'title' => __('Description', 'tranzak-payment-gateway'),
              'type' => 'textarea',
              'description' => __($this->baseController->settingsPageTemplate, 'tranzak-payment-gateway'),
              'default' => __('Pay with MTN Mobile Money', 'tranzak-payment-gateway'),
              'desc_tip' => false
            )
          );
        }
  
        /**
         * query mapi and get transaction status
         */
  
        public function process_payment($orderId)
        {
  
  
          $order = new WC_Order($orderId);
          $order = wc_get_order($order);
  
          $currency = $order->get_currency();
          $amount = $order->get_total();
          $orderId = $order->get_id();
          $websiteTitle = $this->baseController->websiteTitle;
          $websiteUrl = get_bloginfo('url');
          $description = "New purchase for website ($websiteTitle - $websiteUrl). Payment for order #$orderId";
  
          // Mark as set order status (we're awaiting the payment).
          $order->update_status($this->order_stat, sprintf(__('Awaiting %s payment.', 'tranzak-payment-gateway'), $this->method_title));
  
  
          /**
           *  call mapi here
           */
  
  
          $transaction = false;
          $insertId = $this->transactions->createTransaction($amount, $currency, $description, 1, $orderId, $this->get_return_url($order));
          if ($insertId) {
            $request = $this->transactions->createMapiTransaction($amount, $currency, $description, $insertId);
            if($request && $request['success'] == true){
              $transaction = $request['data'];
              if ($transaction) {
                // Reduce stock levels.
                wc_reduce_stock_levels($orderId);
      
                // Remove cart.
                WC()->cart->empty_cart();
                // Return thankyou redirect.
                return array(
                  'result' => 'success',
                  'redirect' => $transaction['links']['paymentAuthUrl'],
                );
              }
            }

            throw new Exception($request['errorMsg']);

          }

          throw new Exception("Failed to place order");
  
        }
      }
    }

    if (!class_exists('TzPgDefault')) {
      class TzPgDefault extends WC_Payment_Gateway
      {
        public $baseController = [];
        public $transactions;
        public function __construct()
        {
  
          $this->transactions = new Transactions();
  
          $this->slug = 'tz-tranzak-woo-gateway';
  
          $this->baseController = new BaseController();
          $this->id = 'tranzak_redirect_woo';
          $this->icon = apply_filters($this->baseController->pluginPageUrl . '_woo_icon', $this->baseController->pluginUrl . 'assets/img/tz-payments.png');
          $this->method_title = 'Tranzak Payment Gateway';
          $this->method_description = __('You can receive payments through Mobile wallets in west and central Africa, Visa & Mastercard, bank transfer and others', 'tranzak-payment-gateway');
  
  
          $this->init_form_fields();
          $this->init_settings();
  
          $this->has_fields = false;
          $this->title = $this->get_option('title');
          $this->description = $this->get_option('description');
  
          $status = $this->get_option('order_stat');
          $this->order_stat = str_starts_with($status, 'wc-') ? $status : 'wc-' . $status;
  
          $this->register_hooks();
        }
  
        public function register_hooks()
        {
          add_action('woocommerce_update_options_payment_gateways_' . $this->id, array($this, 'process_admin_options'));
        }
  
        public function init_form_fields()
        {
  
          $defaultOption = get_option('tranzak_payment_gateway');
  
          $this->form_fields = array(
            'enabled' => array(
              'title' => __('Enable/Disable', 'tranzak-payment-gateway'),
              'type' => 'checkbox',
              'label' => __('Enable Tranzak Payment Gateway', 'tranzak-payment-gateway'),
              'default' => 'no'
            ),
            'order_stat' => array(
              'title' => __('Order Status', 'tranzak-payment-gateway'),
              'type' => 'select',
              'description' => __('Default order status when placed.', 'tranzak-payment-gateway'),
              'default' => empty($pending) ? current(wc_get_order_statuses()) : current($pending),
              'desc_tip' => true,
              'options' => wc_get_order_statuses(),
            ),
            'title' => array(
              'title' => __('Title', 'tranzak-payment-gateway'),
              'type' => 'text',
              'description' => __('This controls the title which the user sees during checkout.', 'tranzak-payment-gateway'),
              'default' => __('Tranzak Payment Gateway', 'tranzak-payment-gateway'),
              'desc_tip' => true
            ),
            'description' => array(
              'title' => __('Description', 'tranzak-payment-gateway'),
              'type' => 'textarea',
              'description' => __($this->baseController->settingsPageTemplate, 'tranzak-payment-gateway'),
              'default' => __('Pay with Tranzak', 'tranzak-payment-gateway'),
              'desc_tip' => false
            )
          );
        }
  
        /**
         * query mapi and get transaction status
         */
  
        public function process_payment($orderId)
        {
  
  
          $order = new WC_Order($orderId);
          $order = wc_get_order($order);
  
          $currency = $order->get_currency();
          $amount = $order->get_total();
          $orderId = $order->get_id();
          $websiteTitle = $this->baseController->websiteTitle;
          $websiteUrl = get_bloginfo('url');
          $description = "New purchase for website ($websiteTitle - $websiteUrl). Payment for order #$orderId";
  
          // Mark as set order status (we're awaiting the payment).
          $order->update_status($this->order_stat, sprintf(__('Awaiting %s payment.', 'tranzak-payment-gateway'), $this->method_title));
  
  
          /**
           *  call mapi here
           */
  
  
          $transaction = false;
          $insertId = $this->transactions->createTransaction($amount, $currency, $description, 1, $orderId, $this->get_return_url($order));
          if ($insertId) {
            $request = $this->transactions->createMapiTransaction($amount, $currency, $description, $insertId);
            if($request && $request['success'] == true){
              $transaction = $request['data'];
              if ($transaction) {
                // Reduce stock levels.
                wc_reduce_stock_levels($orderId);
      
                // Remove cart.
                WC()->cart->empty_cart();
                // Return thankyou redirect.
                return array(
                  'result' => 'success',
                  'redirect' => $transaction['links']['paymentAuthUrl'],
                );
              }
            }

            throw new Exception($request['errorMsg']);

          }

          throw new Exception("Failed to place order");
  
        }
      }
    }
    if (!class_exists('TzPgOrangeMoneyRedirect')) {
      class TzPgOrangeMoneyRedirect extends WC_Payment_Gateway
      {
        public $baseController = [];
        public $transactions;
        public function __construct()
        {
  
          $this->transactions = new Transactions();
  
          $this->slug = 'tz-orange-money-redirect-woo-gateway';
  
          $this->baseController = new BaseController();
          $this->id = 'tranzak_orange-money_redirect_woo';
          $this->icon = apply_filters($this->baseController->pluginPageUrl . '_woo_icon', $this->baseController->pluginUrl . 'assets/img/om.png');
          $this->method_title = 'Tranzak Payment Gateway';
          $this->method_description = __('You can receive payments through Orange Money Cameroon', 'tranzak-payment-gateway');
  
  
          $this->init_form_fields();
          $this->init_settings();
  
          $this->has_fields = false;
          $this->title = $this->get_option('title');
          $this->description = $this->get_option('description');
  
          $status = $this->get_option('order_stat');
          $this->order_stat = str_starts_with($status, 'wc-') ? $status : 'wc-' . $status;
  
          $this->register_hooks();
        }
  
        public function register_hooks()
        {
          add_action('woocommerce_update_options_payment_gateways_' . $this->id, array($this, 'process_admin_options'));
        }
  
        public function init_form_fields()
        {
  
          $defaultOption = get_option('tranzak_payment_gateway');
  
          $this->form_fields = array(
            'enabled' => array(
              'title' => __('Enable/Disable', 'tranzak-payment-gateway'),
              'type' => 'checkbox',
              'label' => __('Enable Tranzak Payment Gateway', 'tranzak-payment-gateway'),
              'default' => 'no'
            ),
            'order_stat' => array(
              'title' => __('Order Status', 'tranzak-payment-gateway'),
              'type' => 'select',
              'description' => __('Default order status when placed.', 'tranzak-payment-gateway'),
              'default' => empty($pending) ? current(wc_get_order_statuses()) : current($pending),
              'desc_tip' => true,
              'options' => wc_get_order_statuses(),
            ),
            'title' => array(
              'title' => __('Title', 'tranzak-payment-gateway'),
              'type' => 'text',
              'description' => __('This controls the title which the user sees during checkout.', 'tranzak-payment-gateway'),
              'default' => __('MTN Mobile Money', 'tranzak-payment-gateway'),
              'desc_tip' => true
            ),
            'description' => array(
              'title' => __('Description', 'tranzak-payment-gateway'),
              'type' => 'textarea',
              'description' => __($this->baseController->settingsPageTemplate, 'tranzak-payment-gateway'),
              'default' => __('Pay with MTN Mobile Money', 'tranzak-payment-gateway'),
              'desc_tip' => false
            )
          );
        }
  
        /**
         * query mapi and get transaction status
         */
  
        public function process_payment($orderId)
        {
  
  
          $order = new WC_Order($orderId);
          $order = wc_get_order($order);
  
          $currency = $order->get_currency();
          $amount = $order->get_total();
          $orderId = $order->get_id();
          $websiteTitle = $this->baseController->websiteTitle;
          $websiteUrl = get_bloginfo('url');
          $description = "New purchase for website ($websiteTitle - $websiteUrl). Payment for order #$orderId";
  
          // Mark as set order status (we're awaiting the payment).
          $order->update_status($this->order_stat, sprintf(__('Awaiting %s payment.', 'tranzak-payment-gateway'), $this->method_title));
  
  
          /**
           *  call mapi here
           */
  
  
          $transaction = false;
          $insertId = $this->transactions->createTransaction($amount, $currency, $description, 1, $orderId, $this->get_return_url($order));
          if ($insertId) {
            $request = $this->transactions->createMapiTransaction($amount, $currency, $description, $insertId);
            if($request && $request['success'] == true){
              $transaction = $request['data'];
              if ($transaction) {
                // Reduce stock levels.
                wc_reduce_stock_levels($orderId);
      
                // Remove cart.
                WC()->cart->empty_cart();
                // Return thankyou redirect.
                return array(
                  'result' => 'success',
                  'redirect' => $transaction['links']['paymentAuthUrl'],
                );
              }
            }

            throw new Exception($request['errorMsg']);

          }

          throw new Exception("Failed to place order");
  
        }
      }
    }

    if (!class_exists('TzPgPayPal')) {
      class TzPgPayPal extends WC_Payment_Gateway
      {
        public $baseController = [];
        public $transactions;
        public function __construct()
        {
  
          $this->transactions = new Transactions();
  
          $this->slug = 'tz-paypal-woo-gateway';
  
          $this->baseController = new BaseController();
          $this->id = 'tranzak_paypal_woo';
          $this->icon = apply_filters($this->baseController->pluginPageUrl . '_woo_icon', $this->baseController->pluginUrl . 'assets/img/paypal.png');
          $this->method_title = 'Tranzak Payment Gateway';
          $this->method_description = __('You can receive payments through PayPal', 'tranzak-payment-gateway');
  
  
          $this->init_form_fields();
          $this->init_settings();
  
          $this->has_fields = false;
          $this->title = $this->get_option('title');
          $this->description = $this->get_option('description');
  
          $status = $this->get_option('order_stat');
          $this->order_stat = str_starts_with($status, 'wc-') ? $status : 'wc-' . $status;
  
          $this->register_hooks();
        }
  
        public function register_hooks()
        {
          add_action('woocommerce_update_options_payment_gateways_' . $this->id, array($this, 'process_admin_options'));
        }
  
        public function init_form_fields()
        {
  
          $defaultOption = get_option('tranzak_payment_gateway');
  
          $this->form_fields = array(
            'enabled' => array(
              'title' => __('Enable/Disable', 'tranzak-payment-gateway'),
              'type' => 'checkbox',
              'label' => __('Enable Tranzak PayPal Payment Gateway', 'tranzak-payment-gateway'),
              'default' => 'no'
            ),
            'order_stat' => array(
              'title' => __('Order Status', 'tranzak-payment-gateway'),
              'type' => 'select',
              'description' => __('Default order status when placed.', 'tranzak-payment-gateway'),
              'default' => empty($pending) ? current(wc_get_order_statuses()) : current($pending),
              'desc_tip' => true,
              'options' => wc_get_order_statuses(),
            ),
            'title' => array(
              'title' => __('Title', 'tranzak-payment-gateway'),
              'type' => 'text',
              'description' => __('This controls the title which the user sees during checkout.', 'tranzak-payment-gateway'),
              'default' => __('PayPal', 'tranzak-payment-gateway'),
              'desc_tip' => true
            ),
            'description' => array(
              'title' => __('Description', 'tranzak-payment-gateway'),
              'type' => 'textarea',
              'description' => __($this->baseController->settingsPageTemplate, 'tranzak-payment-gateway'),
              'default' => __('Pay with PayPal', 'tranzak-payment-gateway'),
              'desc_tip' => false
            )
          );
        }
  
        /**
         * query mapi and get transaction status
         */
  
        public function process_payment($orderId)
        {
  
  
          $order = new WC_Order($orderId);
          $order = wc_get_order($order);
  
          $currency = $order->get_currency();
          $amount = $order->get_total();
          $orderId = $order->get_id();
          $websiteTitle = $this->baseController->websiteTitle;
          $websiteUrl = get_bloginfo('url');
          $description = "New purchase for website ($websiteTitle - $websiteUrl). Payment for order #$orderId";
  
          // Mark as set order status (we're awaiting the payment).
          $order->update_status($this->order_stat, sprintf(__('Awaiting %s payment.', 'tranzak-payment-gateway'), $this->method_title));
  
  
          /**
           *  call mapi here
           */
  
          $transaction = false;
          $insertId = $this->transactions->createTransaction($amount, $currency, $description, 1, $orderId, $this->get_return_url($order));
          if ($insertId) {
            $request = $this->transactions->createMapiTransaction($amount, $currency, $description, $insertId);

            if($request && $request['success'] == true){
              $transaction = $request['data'];
              
              if ($transaction) {
                // Reduce stock levels.
                wc_reduce_stock_levels($orderId);
      
                // Remove cart.
                WC()->cart->empty_cart();
                // Return thankyou redirect.
                return array(
                  'result' => 'success',
                  'redirect' => $transaction['links']['paymentAuthUrl']
                );
              }

            }

            throw new Exception($request['errorMsg']);

          }
  
          throw new Exception("Failed to place order");
          
        }
      }
    }

    if (!class_exists('TzPgBankTransfer')) {
      class TzPgBankTransfer extends WC_Payment_Gateway
      {
        public $baseController = [];
        public $transactions;
        public function __construct()
        {
  
          $this->transactions = new Transactions();
  
          $this->slug = 'tz-bank-woo-gateway';
  
          $this->baseController = new BaseController();
          $this->id = 'tranzak_bank-_woo';
          $this->icon = apply_filters($this->baseController->pluginPageUrl . '_woo_icon', $this->baseController->pluginUrl . 'assets/img/bank.png');
          $this->method_title = 'Tranzak Payment Gateway';
          $this->method_description = __('You can receive payments through bank transfers', 'tranzak-payment-gateway');
  
  
          $this->init_form_fields();
          $this->init_settings();
  
          $this->has_fields = false;
          $this->title = $this->get_option('title');
          $this->description = $this->get_option('description');
  
          $status = $this->get_option('order_stat');
          $this->order_stat = str_starts_with($status, 'wc-') ? $status : 'wc-' . $status;
  
          $this->register_hooks();
        }
  
        public function register_hooks()
        {
          add_action('woocommerce_update_options_payment_gateways_' . $this->id, array($this, 'process_admin_options'));
        }
  
        public function init_form_fields()
        {
  
          $defaultOption = get_option('tranzak_payment_gateway');
  
          $this->form_fields = array(
            'enabled' => array(
              'title' => __('Enable/Disable', 'tranzak-payment-gateway'),
              'type' => 'checkbox',
              'label' => __('Enable Tranzak Bank Transfer Payment Gateway', 'tranzak-payment-gateway'),
              'default' => 'no'
            ),
            'order_stat' => array(
              'title' => __('Order Status', 'tranzak-payment-gateway'),
              'type' => 'select',
              'description' => __('Default order status when placed.', 'tranzak-payment-gateway'),
              'default' => empty($pending) ? current(wc_get_order_statuses()) : current($pending),
              'desc_tip' => true,
              'options' => wc_get_order_statuses(),
            ),
            'title' => array(
              'title' => __('Title', 'tranzak-payment-gateway'),
              'type' => 'text',
              'description' => __('This controls the title which the user sees during checkout.', 'tranzak-payment-gateway'),
              'default' => __('Pay through Bank Transfer', 'tranzak-payment-gateway'),
              'desc_tip' => true
            ),
            'description' => array(
              'title' => __('Description', 'tranzak-payment-gateway'),
              'type' => 'textarea',
              'description' => __($this->baseController->settingsPageTemplate, 'tranzak-payment-gateway'),
              'default' => __('Pay with bank transfers', 'tranzak-payment-gateway'),
              'desc_tip' => false
            )
          );
        }
  
        /**
         * query mapi and get transaction status
         */
  
        public function process_payment($orderId)
        {
  
  
          $order = new WC_Order($orderId);
          $order = wc_get_order($order);
  
          $currency = $order->get_currency();
          $amount = $order->get_total();
          $orderId = $order->get_id();
          $websiteTitle = $this->baseController->websiteTitle;
          $websiteUrl = get_bloginfo('url');
          $description = "New purchase for website ($websiteTitle - $websiteUrl). Payment for order #$orderId";
  
          // Mark as set order status (we're awaiting the payment).
          $order->update_status($this->order_stat, sprintf(__('Awaiting %s payment.', 'tranzak-payment-gateway'), $this->method_title));
  
  
          /**
           *  call mapi here
           */
  
  
          $transaction = false;
          $insertId = $this->transactions->createTransaction($amount, $currency, $description, 1, $orderId, $this->get_return_url($order));
          if ($insertId) {
            $request = $this->transactions->createMapiTransaction($amount, $currency, $description, $insertId);
            if($request && $request['success'] == true) {
              $transaction = $request['data'];
              if ($transaction) {
                // Reduce stock levels.
                wc_reduce_stock_levels($orderId);
      
                // Remove cart.
                WC()->cart->empty_cart();
                // Return thankyou redirect.
                return array(
                  'result' => 'success',
                  'redirect' => $transaction['links']['paymentAuthUrl']
                );
              }
      
              $order->update_status($this->order_stat, sprintf(__('pending', 'tranzak-payment-gateway'), $this->method_title));
              return array(
                'result' => 'success',
                'redirect' => $this->get_return_url($order),
              );
            }

            throw new Exception($request['errorMsg']);

          }

          throw new Exception("Failed to place order");
  
        }
      }
    }

    if (!class_exists('TzPgMtnMobileMoney')) {
      class TzPgMtnMobileMoney extends WC_Payment_Gateway
      {
        public $baseController;
        public $transactions;
        public function __construct()
        {
  
          $this->transactions = new Transactions();
  
          $this->slug = 'tz-mtn-momo-woo-gateway';
  
          $this->baseController = new BaseController();
          $this->has_fields = true;
          $this->id = 'tranzak_mtn_momo_woo';
          $this->icon = apply_filters($this->baseController->pluginPageUrl . '_woo_icon', $this->baseController->pluginUrl . 'assets/img/momo.jpg');
          $this->method_title = 'Tranzak Payment Gateway';
          $this->method_description = __("You can receive payments through MTN Mobile Money without redirecting to Tranzak's payment gateway. ", 'tranzak-payment-gateway'). '<strong style="color: red">'. __(" Do not enable if you haven't gone through KYC on Tranzak. ", 'tranzak-payment-gateway'). '</strong>'.__(' Click', 'tranzak-payment-gateway'). '<a href="https://community.tranzak.net/t/how-can-i-do-kyc-real-name-verification-on-tranzak/14" target="_blank" style="font-weight: bolder">'.__(' here ', 'tranzak-payment-gateway').'</a>'.__('to know how.', 'tranzak-payment-gateway');
  
  
          $this->init_form_fields();
          $this->init_settings();
  
          $this->has_fields = true;
  
          $this->title = $this->get_option('title');
          $this->description = $this->get_option('description');
  
          $status = $this->get_option('order_stat');
          $this->order_stat = str_starts_with($status, 'wc-') ? $status : 'wc-' . $status;
  
          $this->register_hooks();
  
  
        }
  
        public function register_hooks()
        {
          add_action('woocommerce_update_options_payment_gateways_' . $this->id, array($this, 'process_admin_options'));
  
          add_action('wp_enqueue_scripts', array($this, 'payment_scripts'));
        }
  
        public function payment_scripts()
        {
  
          // we need JavaScript to process a token only on cart/checkout pages, right?
          if (!is_cart() && !is_checkout() && !isset($_GET['pay_for_order'])) {
            return;
          }
  
          // if our payment gateway is disabled, we do not have to enqueue JS too
          if ('no' === $this->enabled) {
            return;
          }
    
          wp_enqueue_script('tz_payment_gateway_script', $this->baseController->pluginUrl . 'assets/script/same-page-payment.js');
          /**
           * Adding global object for javascript
           */
          $nonce = wp_create_nonce( 'wp_rest' );
          $paymentVerificationUrl = wp_nonce_url( get_site_url().'/wp-json/tranzak-payment-gateway/v1/request/verify', 'wp_rest' );
          $cancelPaymentUrl = wp_nonce_url( get_site_url().'/wp-json/tranzak-payment-gateway/v1/request/cancel', 'wp_rest' );
          wp_localize_script( 'tz_payment_gateway_script', 'mtnMoMo',
            array(
              'ajaxUrl' => admin_url( 'admin-ajax.php'),
              'siteUrl' => get_site_url(),
              'pluginUrl' => $this->baseController->pluginUrl,
              'code' => '*126#',
              'paymentVerificationUrl' => $paymentVerificationUrl,
              'cancelPaymentUrl' => $cancelPaymentUrl,
              'nonce' => $nonce,
              'id' => $this->id,
              'userId' => get_current_user_id(),
              'errorKey' => 'tz-mtn-momo-errors',
              'key' => 'tz-mtn-momo',
              'logo' => 'momo.jpg',
              'defaultLogo' => $this->baseController->websiteLogo && count($this->baseController->websiteLogo) >0? $this->baseController->websiteLogo[0]: null
            )
          );
          wp_enqueue_style('tz_payment_gateway_checkout_style', $this->baseController->pluginUrl . 'assets/style/checkout-style.css');
  
        }
  
        public function payment_fields()
        {
          echo '
            <div class="tz-same-page-box">
              <div id="tz-mtn-momo-errors" class="tz-checkout-error" style="display: none"></div>
              <div class="form-row form-row-wide">
                <label>Enter MTN Cameroon mobile number to authorize transaction <span class="required">*</span></label>
                <input id="tz-mtn-momo-input" class="tz-pg-op-input input-text" name="tz_phone_number" placeholder="Phone number" maxLength="9" type="tel">
              </div>
              <div class="">
                <div class="tz-checkout-input" onclick="tzTriggerPayment(1)">
                  Pay now
                </div>
              </div>
            </div>';
  
        }
        public function validate_fields()
        {
  
        }
        public function init_form_fields()
        {
  
          $defaultOption = get_option('tranzak_payment_gateway');
  
          $this->form_fields = array(
            'enabled' => array(
              'title' => __('Enable/Disable', 'tranzak-payment-gateway'),
              'type' => 'checkbox',
              'label' => __('Enable Tranzak - MTN Mobile Money Payment', 'tranzak-payment-gateway'),
              'default' => 'no'
            ),
            'order_stat' => array(
              'title' => __('Order Status', 'tranzak-payment-gateway'),
              'type' => 'select',
              'description' => __('Default order status when placed.', 'tranzak-payment-gateway'),
              'default' => empty($pending) ? current(wc_get_order_statuses()) : current($pending),
              'desc_tip' => true,
              'options' => wc_get_order_statuses(),
            ),
            'title' => array(
              'title' => __('Title', 'tranzak-payment-gateway'),
              'type' => 'text',
              'description' => __('This controls the title which the user sees during checkout.', 'tranzak-payment-gateway'),
              'default' => __('MTM Mobile Money', 'tranzak-payment-gateway'),
              'desc_tip' => true
            ),
            'description' => array(
              'title' => __('Description', 'tranzak-payment-gateway'),
              'type' => 'textarea',
              'description' => __($this->baseController->settingsPageTemplate, 'tranzak-payment-gateway'),
              'default' => __('Pay with MTN Mobile Money', 'tranzak-payment-gateway'),
              'desc_tip' => false
            )
          );
        }
  
        /**
         * query mapi and get transaction status
         */
  
        public function process_payment($orderId)
        {

          $nonce = isset($_POST['tz_nonce'])? $_POST['tz_nonce']: null;
          if(!$nonce || !wp_verify_nonce($nonce,'wp_rest')){
            throw new Exception('Invalid parameters');
            return;
          }
  
          if(!isset($_POST['tz_phone_number'])){
            return array(
              'result' => 'success',
              'message' => json_encode($this->baseController->failedResponse('Enter phone number to authorize transaction'))
            );
          }
  
          $phone = '237'.sanitize_text_field($_POST['tz_phone_number']);
  
          $order = new WC_Order($orderId);
          $order = wc_get_order($order);
  
          $currency = $order->get_currency();
          $amount = $order->get_total();
          $orderId = $order->get_id();
          $websiteTitle = $this->baseController->websiteTitle;
          $websiteUrl = get_bloginfo('url');
          $description = "New purchase for website ($websiteTitle - $websiteUrl). Payment for order #$orderId";
  
          // Mark as set order status (we're awaiting the payment).
          $order->update_status($this->order_stat, sprintf(__('Awaiting %s payment.', 'tranzak-payment-gateway'), $this->method_title));
  
  
          /**
           *  call mapi here
           */
  
  
          $transaction = false;
          $request = false;
          $insertId = $this->transactions->createTransaction($amount, $currency, $description, 1, $orderId, $this->get_return_url($order));
          if ($insertId) {
            $request = $this->transactions->createMapiTransaction($amount, $currency, $description, $insertId, $this->baseController->mapiMobileWalletUrl, array('mobileWalletNumber'=> $phone));
            if($request && $request['success'] == true) {
              $transaction = $request['data'];
            }
          }

  
          if ($transaction) {
            // Reduce stock levels.
            wc_reduce_stock_levels($orderId);
  
            // Remove cart.
            // WC()->cart->empty_cart();
            // Return thankyou redirect.
            return array(
              'result' => 'success',
              'message' => json_encode($this->baseController->successResponse($transaction))
            );
            // return $this->baseController->successResponse($transaction);
          }
          $order->update_status($this->order_stat, sprintf(__('pending', 'tranzak-payment-gateway'), $this->method_title));
          
          
          return array(
            'result' => 'success',
            'message' => json_encode($this->baseController->failedResponse( $request? $request['errorMsg']: 'Failed to create order. Please try again'))
          );
          
  
        }
      
      }
    }

    if (!class_exists('TzPgOrangeMoney')) {
      class TzPgOrangeMoney extends WC_Payment_Gateway
      {
        public $baseController = [];
        public $transactions;
        public function __construct()
        {
  
          $this->transactions = new Transactions();
  
          $this->slug = 'tz-orange-money-woo-gateway';
  
          $this->baseController = new BaseController();
          $this->has_fields = true;
          $this->id = 'tranzak_orange_money_woo';
          $this->icon = apply_filters($this->baseController->pluginPageUrl . '_woo_icon', $this->baseController->pluginUrl . 'assets/img/om.png');
          $this->method_title = 'Tranzak Payment Gateway';
          $this->method_description = __("You can receive payments through Orange Money without redirecting to Tranzak's payment gateway. ", 'tranzak-payment-gateway'). '<strong style="color: red">'. __(" Do not enable if you haven't gone through KYC on Tranzak. ", 'tranzak-payment-gateway'). '</strong>'.__(' Click', 'tranzak-payment-gateway'). '<a href="https://community.tranzak.net/t/how-can-i-do-kyc-real-name-verification-on-tranzak/14" target="_blank" style="font-weight: bolder">'.__(' here ', 'tranzak-payment-gateway').'</a>'.__('to know how.', 'tranzak-payment-gateway');
          $this->init_form_fields();
          $this->init_settings();
  
          $this->has_fields = true;
  
          $this->title = $this->get_option('title');
          $this->description = $this->get_option('description');
  
          $status = $this->get_option('order_stat');
          $this->order_stat = str_starts_with($status, 'wc-') ? $status : 'wc-' . $status;
  
          $this->register_hooks();
  
  
        }
  
        public function register_hooks()
        {
          add_action('woocommerce_update_options_payment_gateways_' . $this->id, array($this, 'process_admin_options'));
  
          add_action('wp_enqueue_scripts', array($this, 'payment_scripts'));
        }
  
        public function payment_scripts()
        {
  
          // we need JavaScript to process a token only on cart/checkout pages, right?
          if (!is_cart() && !is_checkout() && !isset($_GET['pay_for_order'])) {
            return;
          }
  
          // if our payment gateway is disabled, we do not have to enqueue JS too
          if ('no' === $this->enabled) {
            return;
          }
  
          wp_enqueue_script('tz_payment_gateway_script', $this->baseController->pluginUrl . 'assets/script/same-page-payment.js');
          /**
           * Adding global object for javascript
           */
          $nonce = wp_create_nonce( 'wp_rest' );
          $paymentVerificationUrl = wp_nonce_url( get_site_url().'/wp-json/tranzak-payment-gateway/v1/request/verify', 'wp_rest' );
          $cancelPaymentUrl = wp_nonce_url( get_site_url().'/wp-json/tranzak-payment-gateway/v1/request/cancel', 'wp_rest' );
          wp_localize_script( 'tz_payment_gateway_script', 'orangeMoney',
            array(
              'ajaxUrl' => admin_url( 'admin-ajax.php'),
              'siteUrl' => get_site_url(),
              'pluginUrl' => $this->baseController->pluginUrl,
              'code' => '#150*50#',
              'userId' => get_current_user_id(),
              'id' => $this->id,
              'paymentVerificationUrl' => $paymentVerificationUrl,
              'cancelPaymentUrl' => $cancelPaymentUrl,
              'nonce' => $nonce,
              'errorKey' => 'tz-orange-money-errors',
              'logo' => 'om.png',
              'key' => 'tz-orange-money',
              'defaultLogo' => $this->baseController->websiteLogo && count($this->baseController->websiteLogo) >0? $this->baseController->websiteLogo[0]: null
            )
          );
  
          wp_enqueue_style('tz_payment_gateway_checkout_style', $this->baseController->pluginUrl . 'assets/style/checkout-style.css');
  
        }
  
        public function payment_fields()
        {
          echo '
            <div class="tz-same-page-box">
              <div id="tz-orange-money-errors" class="tz-checkout-error" style="display: none"></div>
              <div class="form-row form-row-wide">
                <label>Enter Orange Cameroon mobile number to authorize transaction <span class="required">*</span></label>
                <input id="tz-orange-money-input" class="tz-pg-op-input input-text" name="tz_phone_number" placeholder="Phone number" maxLength="9" type="tel">
              </div>
              <div class="">
                <div class="tz-checkout-input" onclick="tzTriggerPayment(2)">
                  Pay now
                </div>
              </div>
            </div>';
  
        }
        public function validate_fields()
        {
  
        }
        public function init_form_fields()
        {
  
          $defaultOption = get_option('tranzak_payment_gateway');
  
          $this->form_fields = array(
            'enabled' => array(
              'title' => __('Enable/Disable', 'tranzak-payment-gateway'),
              'type' => 'checkbox',
              'label' => __('Enable Tranzak - ORANGE MONEY Payment', 'tranzak-payment-gateway'),
              'default' => 'no'
            ),
            'order_stat' => array(
              'title' => __('Order Status', 'tranzak-payment-gateway'),
              'type' => 'select',
              'description' => __('Default order status when placed.', 'tranzak-payment-gateway'),
              'default' => empty($pending) ? current(wc_get_order_statuses()) : current($pending),
              'desc_tip' => true,
              'options' => wc_get_order_statuses(),
            ),
            'title' => array(
              'title' => __('Title', 'tranzak-payment-gateway'),
              'type' => 'text',
              'description' => __('This controls the title which the user sees during checkout.', 'tranzak-payment-gateway'),
              'default' => __('ORANGE MONEY', 'tranzak-payment-gateway'),
              'desc_tip' => true
            ),
            'description' => array(
              'title' => __('Description', 'tranzak-payment-gateway'),
              'type' => 'textarea',
              'description' => __($this->baseController->settingsPageTemplate, 'tranzak-payment-gateway'),
              'default' => __('Pay with Orange Money', 'tranzak-payment-gateway'),
              'desc_tip' => false
            )
          );
        }
  
        /**
         * query mapi and get transaction status
         */
  
        public function process_payment($orderId)
        {
          $nonce = isset($_POST['tz_nonce'])? $_POST['tz_nonce']: null;
          if(!$nonce || !wp_verify_nonce($nonce,'wp_rest')){
            throw new Exception('Invalid parameters');
            return;
          }
  
          if(!isset($_POST['tz_phone_number'])){
            return array(
              'result' => 'success',
              'message' => json_encode($this->baseController->failedResponse('Enter phone number to authorize transaction'))
            );
          }
  
          $phone = '237'.sanitize_text_field($_POST['tz_phone_number']);
  
          $order = new WC_Order($orderId);
          $order = wc_get_order($order);
  
          $currency = $order->get_currency();
          $amount = $order->get_total();
          $orderId = $order->get_id();
          $websiteTitle = $this->baseController->websiteTitle;
          $websiteUrl = get_bloginfo('url');
          $description = "New purchase for website ($websiteTitle - $websiteUrl). Payment for order #$orderId";
  
          // Mark as set order status (we're awaiting the payment).
          $order->update_status($this->order_stat, sprintf(__('Awaiting %s payment.', 'tranzak-payment-gateway'), $this->method_title));
  
  
          /**
           *  call mapi here
           */
  
  
          $transaction = false;
          $request = false;
          $insertId = $this->transactions->createTransaction($amount, $currency, $description, 1, $orderId, $this->get_return_url($order));
          if ($insertId) {
            $request = $this->transactions->createMapiTransaction($amount, $currency, $description, $insertId, $this->baseController->mapiMobileWalletUrl, array('mobileWalletNumber'=> $phone));
            if($request && $request['success'] == true) {
              $transaction = $request['data'];
            }
  
            if ($transaction) {
              // Reduce stock levels.
              wc_reduce_stock_levels($orderId);
  
              // Remove cart.
              // WC()->cart->empty_cart();
              // Return thankyou redirect.
              return array(
                'result' => 'success',
                'message' => json_encode($this->baseController->successResponse($transaction))
              );
              // return $this->baseController->successResponse($transaction);
            }
          }
  
  
          $order->update_status($this->order_stat, sprintf(__('pending', 'tranzak-payment-gateway'), $this->method_title));
  
          return array(
            'result' => 'success',
            'message' => json_encode($this->baseController->failedResponse(  $request? $request['errorMsg']: 'Failed to create order. Please try again'))
          );
  
        }
      }
    }
  }
}

/**
 * Summary of adding all payment methods to woocommerce
 * @param mixed $gateways holds all available payment gateways for woocommerce
 * @return mixed
 */
function addTranzakToWooCommerce($gateways)
{

  $gateways = array_merge($gateways, array('TzPgOrangeMoney', 'TzPgMtnMobileMoney', 'TzPgDefault'));
  // $gateways = array_merge($gateways, array('TzPgOrangeMoney', 'TzPgMtnMobileMoney', 'TzPgCreditCards', 'TzPgMTNMoMoRedirect', 'TzPgOrangeMoneyRedirect', 'TzPgBankTransfer', 'TzPgDefault'));
  return $gateways;
}


add_action('plugin_loaded', 'register_woocommerse', 11);

add_filter('woocommerce_payment_gateways', 'addTranzakToWooCommerce');


// check for empty-cart get param to clear the cart
add_action( 'init', 'tzPgWooClearCart' );
function tzPgWooClearCart() {
  global $woocommerce;

	if ( isset( $_GET['tz-empty-cart'] ) ) {
		$woocommerce->cart->empty_cart();
	}
}


function tzPgHookOptionAfterSaved( $oldValue, $newValue ) {
	if ( ( isset($newValue['api_key']) && $newValue['api_key'] ) ) {
    $transaction = new Transactions();
    $data = $transaction->createToken();
    if($data && isset($data['token'])){
      $token = $data['token'];
      $expiresIn = $data['expiresIn'];

      $transaction->setToken($token, $expiresIn);
    }
	}

}
function tzPgRegisterPlugin( $oldValue, $newValue ) {

	if ($newValue['app_id'] ) {
    $transaction = new Transactions();
    $transaction->registerPluginData($newValue['app_id']);
	}

}


add_action( 'update_option_tranzak_payment_gateway', 'tzPgHookOptionAfterSaved', 10, 2 );
add_action( 'update_option_tranzak_payment_gateway', 'tzPgRegisterPlugin', 10, 2 );


function tzPgGetNewTzToken(){
  $transaction = new Transactions();
  $token = $transaction->getToken();
}

add_action('reset_tranzak_token', 'tzPgGetNewTzToken');