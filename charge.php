<?php
session_start();

date_default_timezone_set( 'Asia/Manila' );
require 'vendor/autoload.php';

// Dot-Env
$dotenv = Dotenv\Dotenv::create(__DIR__);
$dotenv->load();

require 'magpie-api-client.php';

// DB login
// require_once 'rb-mysql.php';
// $isConnected = R::testConnection();
// if (!$isConnected) { 
//   $conn = 'mysql:host='.$_SERVER['DB_HOST'].':'.$_SERVER['DB_PORT'].';dbname='.$_SERVER['DB_NAME'];
//   R::setup( $conn, $_SERVER['DB_USER'], $_SERVER['DB_PASS'] );
// }

// This progams eventual output
$reply = array();

// $ts = microtime(true);
// $ts = str_replace('.', '', $ts);
// $ts = substr($ts,0,13);
// $digits = 3;
// $addthis = str_pad(rand(0, pow(10, $digits)-1), $digits, '0', STR_PAD_LEFT);
// $ts = $digits;
// $redirect_url = 'https://'.$_SERVER['HTTP_HOST'].$_SERVER['REDIRECT'];
// $callback_url = 'https://'.$_SERVER['HTTP_HOST'].$_SERVER['CALLBACK'];
$statement_descriptor = $_SERVER['STATEMENT_DESCRIPTOR'];
$gateway = $_SERVER['MAGPIE_GATEWAY'];
// $fee = (int)$_SERVER['FEE'];

/// Start
if (empty($_POST)) {
  http_response_code(400);
  $reply['title'] = 'Server Error';
  $reply['message'] = 'You are not allowed to access this.';
} else {

  // Token
  $token = '';
  if (isset($_POST['token'])) {
    $token = $_POST['token']  ;
  } else {
    http_response_code(400);
    $reply['title'] = 'Error';
    $reply['message'] = 'Token not found.';
  }

  // Amount
  $amount = 0;
  if (isset($_POST['amount'])) {
    $amount = $_POST['amount'];
    $amount = (integer)$amount;
    if ($amount < 100000  ) {
      http_response_code(400);
      $reply['title'] = 'Amount Error';
      $reply['message'] = 'Amount must not be less than P1,000.';
    }
  } else {
    http_response_code(400);
    $reply['title'] = 'Payment Error';
    $reply['message'] = 'Amount must not be less than P50.';
  }

  // Product description
  $description = '';
  if (isset($_POST['description'])) {
    $description = $_POST['description'];
  } else {
    http_response_code(400);
    $reply['title'] = 'Purchase Error';
    $reply['message'] = 'Item/s not found. This order was not processed.';
  }

  // Currency
  $currency = '';
  if (isset($_POST['currency'])) {
    $currency = $_POST['currency'];
  } else {
    http_response_code(400);
    $reply['title'] = 'Currency Error';
    $reply['message'] = 'Must set currency.';
  }

  // Args
  // $args = array();
  // if (isset($_POST['args'])) {
  //   $args = $_POST['args'];
  // }

  // $email = '';
  // if (isset($_POST['token']['email'])) {
  //   $email = $_POST['token']['email'];
  // } else {
  //   http_response_code(400);
  //   $reply['title'] = 'Error';
  //   $reply['message'] = 'Token not found.';
  // }

  // // Other post data
  // $form = array();
  // if (isset($_POST['formdata'])) {
  //   $form = $_POST['formdata'];
  // }

  // PARAMETER CHECKS DONE
  // LET'S START PAYMENT
  if (empty($reply)) {
    $payload = [
      "amount" => $amount,
      "currency" => $currency,
      "source" => $token,
      "description" => $description,
      "statement_descriptor" => $statement_descriptor,
      "capture" => true
    ];

    // For 3D Secure calls
    if (preg_match('/3ds/',$gateway)) {
      $payload['gateway'] = $gateway;
      $payload['redirect_url'] = $redirect_url;
      $payload['callback_url'] = $callback_url;
    }

    // Bank
    if (preg_match('/ba_/', $token)) {
      $payload['redirect_url'] = $redirect_url;
      $payload['checkout_url'] = $callback_url;
    }

    // Create the charge
    $magpieReply = create_charge($payload);
    $reply['response'] = $magpieReply;

    // Do something with response
    if ($magpieReply['code'] == 200 || $magpieReply['code'] == 201) {
      // SUCCESS
      $chg = $magpieReply['body'];
      // $data = R::dispense($_SERVER['DB_TABLE']);
      // $data->firstname = $form['firstname'];
      // $data->middleinitial = $form['middleinitial'];
      // $data->lastname = $form['lastname'];
      // $data->phone = $form['phone'];
      // $data->amount = (int)$chg['amount'] - $fee;
      // $data->charge = $chg['id'];
      // $data->captured = $chg['captured'];
      // $data->created = date('Y-m-d H:i:s', time());
      // $data->ts = $ts;
      // $data->payment_details = json_encode($chg);
      // $data->updated = null;

      // try {
      //   $res = R::store( $data );
      // } catch (Exception $e) {
      //   http_response_code(500);
      //   $reply['db_error'] = $e->getMessage();
      // }

      $reply['title'] = 'Success';
      $reply['message'] = "Payment good. Thank you."; //<br><br><strong>Payment details:</strong><br>" . $charge['card'] . "<br>on " . date('m/d/Y', $chg['created']);
    } else {
      http_response_code($magpieReply['code']);
      $reply['title'] = 'Error';
      $reply['message'] = 'Processing Error'; // <b><b>We are sorry!<br>Something went wrong.';
    }
  }
}

echo json_encode($reply, JSON_PRETTY_PRINT);
?>