<?php
/**
 * Plugin Name: Maypay for WooCommerce
 * Description: Maypay integration for WooCommerce.
 * Version: 1.0.0
 * Requires at least: 5.2
 * Requires PHP: 7.2
 * Author: Maypay
 * Author URI: https://maypay.com
 * License: GPL v2 or later
 * License URI: http://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: maypay
 * Domain Path: /languages
 */

/**
 * Automatically deactivates the plugin
 */
function maypay_deactivate()
{
    if (is_plugin_active('maypay/maypay.php')) {
        deactivate_plugins('maypay/maypay.php');
        unset($_GET['activate']);
    }
}

/**
 * Shows the missing woocommerce error in the admin console.
 */
function maypay_missing_woocommerce_error()
{
    echo ('<div class="notice notice-error"><p>' . esc_html__('Maypay requires WooCommerce to be installed and active.', 'maypay') . '</p></div>');
}

/**
 * Shows the https required xerror in the admin console.
 */
function maypay_https_required_error()
{
    echo ('<div class="notice notice-error"><p>' . esc_html__('Maypay requires your domain to be ssl protected (https).', 'maypay') . '</p></div>');
}

if (!defined('ABSPATH')){
    exit; // Exit if accessed directly
}

// Ensure domain is under ssl
if (is_ssl()) {
    // Ensure WooCommerce is active
    if (in_array('woocommerce/woocommerce.php', apply_filters('active_plugins', get_option('active_plugins')))) {

        // Initialize the Maypay Gateway
        function maypay_init_gateway()
        {
            if (!class_exists('WC_Payment_Gateway')) {
                return;
            }

            /**
             * Create a new payment gateway for WooCommerce 
             */
            class Maypay_WC_Gateway extends WC_Payment_Gateway
            {
                /**
                 * Constructor for the gateway.
                 */
                public function __construct()
                {
                    $this->id = 'maypay';
                    $this->icon = plugins_url('maypay/assets/images/logo.png');
                    $this->has_fields = false;
                    $this->method_title = 'Maypay';
                    $this->method_description = __('What if it was free?', 'maypay');
                    $this->order_button_text = __('Win or Pay', 'maypay');

                    $this->init_form_fields();
                    $this->init_settings();

                    $this->title = __('Maypay - Win or pay', 'maypay');
                    $this->storeId = $this->get_option('storeId');
                    $this->publicKey = $this->get_option('publicKey');
                    $this->privateKey = $this->get_option('privateKey');
                    $this->showDesc = $this->get_option('showDesc');
                    if ($this->showDesc === 'yes') {
                        $this->description = __('Win or pay with Maypay, the only app to win what you\'re buying', 'maypay');
                    } else {
                        $this->description = '';
                    }

                    $this->sandboxMode = $this->get_option('sandboxMode');
                    $this->sandboxStoreId = $this->get_option('sandboxStoreId');
                    $this->sandboxPublicKey = $this->get_option('sandboxPublicKey');
                    $this->sandboxPrivateKey = $this->get_option('sandboxPrivateKey');
                    $this->sandboxRefundFailed = $this->get_option('sandboxRefundFailed');

                    include_once(plugin_dir_path(__FILE__) . 'env.php');

                    $this->baseUrl = $baseUrl;
                    $this->serviceUrl = $serviceUrl;
                    $this->pluginsUrl = plugins_url();

                    // Add support for refunds
                    $this->supports = array(
                        'products',
                        'refunds'
                    );

                    add_action('woocommerce_update_options_payment_gateways_' . $this->id, array($this, 'process_admin_options'));
                    add_action('woocommerce_checkout_order_processed', array($this, 'maypay_send_order'));
                    add_action('wp_enqueue_scripts', array($this, 'maypay_enqueue_scripts'));
                }

                /**
                 * Return the private key
                 */
                public function get_private_key()
                {
                    if ($this->sandboxMode === 'yes') {
                        return $this->sandboxPrivateKey;
                    } else {
                        return $this->privateKey;
                    }
                }

                /**
                 * Return the public key
                 */
                public function get_public_key()
                {
                    if ($this->sandboxMode === 'yes') {
                        return $this->sandboxPublicKey;
                    } else {
                        return $this->publicKey;
                    }
                }

                /**
                 * Return the storeId
                 */
                public function get_storeId()
                {
                    if ($this->sandboxMode === 'yes') {
                        return $this->sandboxStorId;
                    } else {
                        return $this->storeId;
                    }
                }

                /**
                 * Initialize the gateway settings form fields.
                 */
                public function init_form_fields()
                {
                    $this->form_fields = array(
                        'showDesc' => array(
                            'title' => __('Show description', 'maypay'),
                            'type' => 'checkbox',
                            'description' => __('Shows a brief description of maypay when selected as payment method.', 'maypay'),
                            'default' => 'yes'
                        ),
                        'storeId' => array(
                            'title' => 'Store ID',
                            'type' => 'text',
                            'description' => __('Enter your Maypay Store ID.', 'maypay'),
                            'default' => ''
                        ),
                        'publicKey' => array(
                            'title' => __('Public Key', 'maypay'),
                            'type' => 'text',
                            'description' => __('Enter your Maypay Public Key.', 'maypay'),
                            'default' => ''
                        ),
                        'privateKey' => array(
                            'title' => __('Private Key', 'maypay'),
                            'type' => 'text',
                            'description' => __('Enter your Maypay Private Key.', 'maypay'),
                            'default' => ''
                        ),
                        'sandboxMode' => array(
                            'title' => __('Sandbox mode', 'maypay'),
                            'type' => 'checkbox',
                            'description' => __('Enable the sanbox mode to try out maypay.', 'maypay'),
                            'default' => 'no'
                        ),
                        'sandboxStoreId' => array(
                            'title' => __('Sandbox Store ID', 'maypay'),
                            'type' => 'text',
                            'description' => __('Enter your Maypay sandbox Store ID.', 'maypay'),
                            'default' => ''
                        ),
                        'sandboxPublicKey' => array(
                            'title' => __('Sandbox Public Key', 'maypay'),
                            'type' => 'text',
                            'description' => __('Enter your Maypay sandbox Public Key.', 'maypay'),
                            'default' => ''
                        ),
                        'sandboxPrivateKey' => array(
                            'title' => __('Sandbox Private Key', 'maypay'),
                            'type' => 'text',
                            'description' => __('Enter your Maypay sandbox Private Key.', 'maypay'),
                            'default' => ''
                        ),
                        'sandboxRefundFailed' => array(
                            'title' => __('Refunds Fail', 'maypay'),
                            'type' => 'checkbox',
                            'description' => __('Make the sandbox refund failed.', 'maypay'),
                            'default' => 'no'
                        ),
                    );
                }

                /**
                 * Enqueue the Maypay scripts.
                 */
                public function maypay_enqueue_scripts()
                {
                    wp_enqueue_script('maypay-online-button-box', plugins_url('/maypay/assets/js/maypay-online-button-box.js'));
                    wp_enqueue_script('maypay-online-button', plugins_url('/maypay/assets/js/maypay-online-button.js'));
                    wp_enqueue_style('maypay-fonts', plugins_url('/maypay/assets/css/fonts.css'));
                    wp_localize_script('maypay-online-button', 'env', array(
                        'serviceUrl' => $this->serviceUrl,
                    )
                    );
                    wp_localize_script('maypay-online-button-box', 'env', array(
                        'serviceUrl' => $this->serviceUrl,
                        'pluginsUrl' => $this->pluginsUrl,
                    )
                    );
                }

                /**
                 * Process the payment and return the redirect URL to Maypay payment page.
                 *
                 * @param int $order_id The order ID.
                 * @return array
                 */
                public function process_payment($order_id)
                {
                    $order = wc_get_order($order_id);

                    return array(
                        'result' => 'success',
                        'redirect' => $order->get_checkout_payment_url(true)
                    );
                }

                /**
                 * Process the refund
                 */
                public function process_refund($order_id, $amount = null, $reason = '')
                {
                    $order = wc_get_order($order_id);
                    $paymentRequestId = $order->get_meta('paymentRequestId');

                    $data = array(
                        'amount' => intval($amount * 100),
                        'paymentRequestId' => $paymentRequestId
                    );

                    $url = $this->baseUrl . '/refundPaymentRequest';
                    $privateKey = $this->privateKey;
                    $publicKey = $this->publicKey;
                    $sandboxRefundFailed = $this->sandboxRefundFailed;

                    if ($this->sandboxMode === 'yes') {
                        $privateKey = $this->sandboxPrivateKey;
                        $publicKey = $this->sandboxPublicKey;
                        $transactionId = 'sandbox_successful_refund_transaction';
                        if ($sandboxRefundFailed === 'yes') {
                            $transactionId = 'sandbox_failed_refund_transaction';
                        }
                        $data['transactionId'] = $transactionId;
                    }
                    $api_response = $this->maypay_api_request('POST', $url, 'application/json', $data, $publicKey, $privateKey);

                    if ($api_response['success']) {
                        $refundedAt = $api_response['data']['refundedAt'];
                        $order->add_order_note(__('Refund Request Sent. Merchant Payment Request ID: ', 'maypay') . $paymentRequestId);
                        $order->update_meta_data('refundedAt', $refundedAt);
                        $order->save();
                        return true;
                    } else {
                        error_log(json_encode($api_response['error']));
                        $error_message = $api_response['error'];
                        $order->add_order_note(__('Failed to send refund request to Maypay. Error: ', 'maypay') . $error_message);
                        $order->save();
                        return false;
                    }
                }

                /**
                 * Send the order to Maypay for payment processing.
                 *
                 * @param int $order_id The order ID.
                 */
                public function maypay_send_order($order_id)
                {
                    $order = wc_get_order($order_id);

                    if ($order->get_payment_method() === 'maypay') {
                        $storeId = $this->storeId;
                        $privateKey = $this->privateKey;
                        $publicKey = $this->publicKey;

                        if ($this->sandboxMode === 'yes') {
                            $storeId = $this->sandboxStoreId;
                            $privateKey = $this->sandboxPrivateKey;
                            $publicKey = $this->sandboxPublicKey;
                        }

                        $url = $this->baseUrl . '/createMerchantPaymentRequest';

                        $data = array(
                            'amount' => intval($order->get_total() * 100),
                            'currency' => get_woocommerce_currency(),
                            'buyerFlowType' => 'all',
                            'buyerPhoneNumber' => strval('+39' . $order->get_billing_phone()),
                            'orderId' => strval($order_id),
                            'callbackUrl' => home_url('/?rest_route=/maypay/v1/hook'),
                            'storeId' => $storeId,
                            'timeout' => 10,
                            'metadata' => new stdClass(),
                        );

                        $api_response = $this->maypay_api_request('POST', $url, 'application/json', $data, $publicKey, $privateKey);

                        if ($api_response['success']) {
                            $merchantPaymentRequestId = $api_response['data']['merchantPaymentRequestId'];
                            $order->add_order_note(__('Payment request sent to Maypay. Merchant Payment Request ID: ', 'maypay') . $merchantPaymentRequestId);
                            $order->update_meta_data('paymentRequestId', $merchantPaymentRequestId);
                            $order->save();
                        } else {
                            error_log(json_encode($api_response['error']));
                            $error_message = $api_response['error'];
                            $order->add_order_note(__('Failed to send payment request to Maypay. Error: ', 'maypay') . $error_message);
                            $order->save();
                        }
                    }
                }

                /**
                 * Format the request to be sent to Maypay backend
                 */
                public function maypay_api_request($method, $url, $contentType, $data, $publicKey, $privateKey)
                {

                    /**
                     * Generates a V4 UUID
                     */
                    function maypay_generateUUIDv4()
                    {
                        if (function_exists('random_bytes')) {
                            $data = random_bytes(16);
                        } elseif (function_exists('openssl_random_pseudo_bytes')) {
                            $data = openssl_random_pseudo_bytes(16);
                        } else {
                            $data = uniqid('', true);
                        }

                        $data[6] = chr(ord($data[6]) & 0x0f | 0x40); // Set version to 4
                        $data[8] = chr(ord($data[8]) & 0x3f | 0x80); // Set bits 6-7 to 10

                        return vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($data), 4));
                    }

                    $uuid = maypay_generateUUIDv4();
                    $date = gmdate("D, d M Y H:i:s T");

                    $headers = array(
                        'X-MAYPAY-Req-ID: ' . $uuid,
                        'Date: ' . $date,
                    );

                    $payload = $method . ' ' . $url . "\n" . $date . "\n" . $uuid;

                    if ($contentType === 'application/json') {
                        $headers[] = 'Content-Type: ' . $contentType;
                        $payload .= "\n" . $contentType;
                        if ($data) {
                            $body = json_encode($data, JSON_UNESCAPED_SLASHES);
                            $payload .= "\n" . $body;
                        }
                    }

                    $hm = hash_hmac("sha1", $payload, $privateKey, true);
                    $headers[] = 'Authorization: MAYPAY ' . $publicKey . ':' . base64_encode($hm) . ';';

                    $options = array(
                        'http' => array(
                            'method' => $method,
                            'header' => implode("\r\n", $headers),
                            'content' => isset($body) ? $body : null,
                            'ignore_errors' => true,
                        ),
                    );

                    $context = stream_context_create($options);
                    $response = file_get_contents($url, false, $context);

                    if ($response === false) {
                        return array(
                            'success' => false,
                            'error' => 'Failed to connect to Maypay API.',
                            'data' => null,
                        );
                    }

                    $json = json_decode($response, true);

                    if ($json === null) {
                        return array(
                            'success' => false,
                            'error' => 'Invalid response from Maypay API.',
                            'data' => null,
                        );
                    }

                    return $json;

                }
            }
            //END CLASS

            add_filter('woocommerce_payment_gateways', 'maypay_add_gateway');
            /**
             * Add Maypay Gateway as available payment method.
             */
            function maypay_add_gateway($methods)
            {
                $methods[] = 'Maypay_WC_Gateway';
                return $methods;
            }


            add_action('after_woocommerce_pay', 'maypay_show_web_button');

            /**
             * Shows the Maypay js button in the order pay page.
             */
            function maypay_show_web_button()
            {
                // Get order_id from current cart.
                $order_id = WC()->session->get('order_awaiting_payment');

                // If order_id is null, throw an error.
                if (empty($order_id)) {
                    wp_die(__('Invalid or missing order ID', 'maypay'));
                }

                $order = wc_get_order($order_id);
                $paymentRequestId = $order->get_meta('paymentRequestId');
                $amount = $order->get_total();
                $redirect_link = $order->get_checkout_order_received_url();
                $cancel_link = $order->get_cancel_order_url();
                ?>
                <maypay-online-button-box paymentRequestId="<?php echo esc_attr($paymentRequestId); ?>"
                    paymentRequestAmount="<?php echo esc_attr($amount); ?>"></maypay-online-button>
                    <script>
                        window.addEventListener("maypay-event", (event) => {
                            switch (event.detail.state) {
                                case 'PAYED':
                                    window.location.href = "<?php echo esc_url($redirect_link); ?>"
                                    break
                                case 'ERROR':
                                    window.location.href = "<?php echo esc_url($cancel_link); ?>"
                                    break
                                case 'CANCELLED_BY_BUYER':
                                    window.location.href = "<?php echo esc_url($cancel_link); ?>"
                                    break
                                default:
                                    window.location.href = "<?php echo esc_url($cancel_link); ?>"
                            }
                        })
                    </script>
                <?php
            }

            add_action('rest_api_init', 'maypay_register_handler');

            /**
             * Register the Maypay callback for webhook.
             */
            function maypay_register_handler()
            {
                register_rest_route('maypay/v1', '/hook', array(
                    'methods' => 'POST',
                    'callback' => 'maypay_handle_webhook',
                    'permission_callback' => '__return_true',
                )
                );
            }

            /**
             * Handle the webhook response, validates it and updates the order.
             */
            function maypay_handle_webhook($request)
            {
                $maypay = new Maypay_WC_Gateway();
                $publicKey = $maypay->get_public_key();
                $privateKey = $maypay->get_private_key();

                $request_body = $request->get_body();
                $headers = $request->get_headers();
                $method = $request->get_method();
                $protocol = $request->get_header('x_forwarded_proto');
                $host = $request->get_header('host');
                $url = $protocol . '://' . $host . '/?rest_route=/maypay/v1/hook';

                $request_id = $request->get_header('x_maypay_req_id');
                $date = $request->get_header('date');
                $contentType = $request->get_header('content_type');
                $data = json_decode($request_body, true);

                //Check for date difference
                $currentDate = gmdate("D, d M Y H:i:s T");
                $datetime1 = new DateTime($date);
                $datetime2 = new DateTime($currentDate);

                $interval = $datetime1->diff($datetime2);

                if ($interval->format('%i') > 5) {
                    $response = array(
                        'message' => 'Invalid datetime, > 5 minutes.',
                    );
                    $response = rest_ensure_response($response);
                    $response->set_status(400);
                    return $response;
                }

                // Fromatting payload
                $payload = $method . ' ' . $url . "\n" . $date . "\n" . $request_id;

                if ($contentType === 'application/json') {
                    $payload .= "\n" . $contentType;
                    if ($data) {
                        $body = json_encode($data, JSON_UNESCAPED_SLASHES);
                        $payload .= "\n" . $body;
                    }
                }

                // Generates the digest
                $hm = hash_hmac("sha1", $payload, $privateKey, true);
                $digest = $request->get_header('x_maypay_digest');

                $calculatedDigest = 'MAYPAY ' . $publicKey . ':' . base64_encode($hm) . ';';

                // Verify the generated digest is the same as the received one.
                if ($calculatedDigest === $digest) {
                    $order_id = $data['metadata']['orderId'];
                    $state = $data['state'];
                    $refund_amount = $data['refundedAmount'];
                    $refunds = $data['refunds'];
                    $is_winning_transaction = $data['isWinningTransaction'];

                    $order = wc_get_order($order_id);

                    if ($order) {
                        if ($state === 'PAYED') {
                            if ($is_winning_transaction) {
                                // Update the order to completed with a 100% discount
                                $order->set_discount_total($order->get_total());
                                $order->set_total(0);
                                $order->update_status('completed', __('Order won with Maypay, paid by the customer with a 100% discount (for which Maypay has transferred the agreed amount to you)', 'maypay'));
                            } else {
                                // Update the order to completed
                                $order->update_status('completed', 'Pagato');
                                $order->update_status('completed', __('Order not won, the customer has paid the whole amount.', 'maypay'));
                            }

                            $order->save();
                        } elseif ($state === 'ERROR') {
                            $order->update_status('failed', 'Fallito');
                        } elseif ($state === 'CANCELLED_BY_BUYER') {
                            $order->update_status('cancelled', 'Annullato');
                        } elseif ($state === 'WAITING_REFUND') {
                            $order->add_order_note(__('Waiting for refund', 'maypay'));
                        } elseif ($state === 'REFUNDED') {

                            $order->update_status("refunded", 'Rimborsato');
                            $order->add_order_note(__('Order refunded successfully', 'maypay'));
                            $wc_refunds = $order->get_refunds();

                            foreach ($wc_refunds as $wc_refund) {
                                error_log($wc_refund);
                                $wc_refund->set_status("refunded");
                                $to_delete = true;
                                foreach($refunds as $key => $value){
                                    if($wc_refund->get_amount() == $value['amount'] / 100){
                                        $to_delete = false;
                                    }
                                }
                                if($to_delete){
                                    $wc_refund->delete();
                                }
                            }


                        } elseif ($state === 'REFUND_FAILED') {
                            $order->add_order_note(__('Refund request failed.', 'maypay'));
                            $order->update_status('failed', 'Fallito');
                        } else {
                            $order->update_status('failed', 'Fallito');
                        }
                    }

                    $response = array(
                        'message' => 'Order updated successfully',
                    );
                    $response = rest_ensure_response($response);
                    return $response;

                } else {
                    error_log('Invalid digest, received is: ' . $digest . ' Calculated is: ' . $calculatedDigest);
                    $response = array(
                        'message' => 'Invalid digest, received is: ' . $digest . ' Calculated is: ' . $calculatedDigest,
                    );
                    $response = rest_ensure_response($response);
                    $response->set_status(400);
                    return $response;
                }

                return rest_ensure_response($response);
            }

        }
        //END INIT
        add_action('plugins_loaded', 'maypay_init_gateway');

        /**
         * Load the localization files
         */
        function maypay_load_textdomain()
        {
            load_plugin_textdomain('maypay', false, dirname(plugin_basename(__FILE__)) . '/languages/');
        }
        add_action('plugins_loaded', 'maypay_load_textdomain');

    } else {

        function maypay_load_textdomain()
        {
            load_plugin_textdomain('maypay', false, dirname(plugin_basename(__FILE__)) . '/languages/');
        }
        add_action('admin_init', 'maypay_load_textdomain');

        //Deactivates the plugin if woocommerce is not installed and active.
        add_action('admin_notices', 'maypay_missing_woocommerce_error');

        $plugin = plugin_basename(__FILE__);
        add_action('admin_init', 'maypay_deactivate');

    }
} else {
    function maypay_load_textdomain()
    {
        load_plugin_textdomain('maypay', false, dirname(plugin_basename(__FILE__)) . '/languages/');
    }
    add_action('admin_init', 'maypay_load_textdomain');

    //Deactivates the plugin if woocommerce is not installed and active.
    add_action('admin_notices', 'maypay_https_required_error');

    $plugin = plugin_basename(__FILE__);
    add_action('admin_init', 'maypay_deactivate');
}