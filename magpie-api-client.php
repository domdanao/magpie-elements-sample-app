<?php
require 'vendor/autoload.php';

// Dot-Env
$dotenv = Dotenv\Dotenv::create(__DIR__);
$dotenv->load();
$magpie_secret_key = $_SERVER['MAGPIE_SECRET_KEY'];

use GuzzleHttp\Client;

$client = new GuzzleHttp\Client(['base_uri' => 'https://api.magpie.im/']);
$auth = [$magpie_secret_key, ''];

function create_charge($payload) {
    /*
    $payload = [
        "amount" => 50000,
        "currency" => "php",
        "source" => "tok_1235",
        "description" => "Product summary",
        "statement_descriptor" => "MerchantID",
        "capture" => true,
        "gateway" => "magpie_3ds",
        "redirect_url" => $redirect_url,
        "callback_url" => $callback_url
    ];
    */

    global $client, $auth;
    $reply = [];
	try {
		$response = $client->request('POST', 'v1.1/charges', [
			'auth' => $auth,
			'json' => $payload
        ]);

        $reply['code'] = $response->getStatusCode();
		$reply['body'] = json_decode($response->getBody(), true);

	} catch (GuzzleHttp\Exception\BadResponseException $e) {

        $res = $e->getResponse();
        $reply['code'] = 400;
        $reply['body'] = json_decode($res->getBody()->getContents(), true);

	}

	return $reply;
}

function get_charge($charge_id) {
    global $client, $auth;
	$reply = [];
	try {
		$response = $client->request('GET', 'v1.1/charges/'.$charge_id, ['auth' => $auth]);
		$reply['code'] = $response->getStatusCode();
		$reply['body'] = json_decode($response->getBody(), true);

	} catch (GuzzleHttp\Exception\BadResponseException $e) {

        $res = $e->getResponse();
		$reply['code'] = 400;
        $reply['body'] = json_decode($res->getBody()->getContents(), true);

	}

	return $reply;
}

function capture_charge($charge_id, $payload) {
    /*
    $payload = [
        "amount" => 50000,
        "reason" => "Customer request"
    ]
    */
    global $client, $auth;
	$reply = [];
	try {
		$response = $client->request('POST', 'v1.1/charges/'.$charge_id.'/capture', [
            'auth' => $auth,
            'json'=> $payload
        ]);
		$reply['code'] = $response->getStatusCode();
		$reply['body'] = json_decode($response->getBody(), true);

	} catch (GuzzleHttp\Exception\BadResponseException $e) {

        $res = $e->getResponse();
		$reply['code'] = 400;
        $reply['body'] = json_decode($res->getBody()->getContents(), true);

	}

	return $reply;
}

function void_charge($charge_id) {
    global $client, $auth;
	$reply = [];
	try {
		$response = $client->request('POST', 'v1.1/charges/'.$charge_id.'/void', [ 'auth' => $auth ]);
		$reply['code'] = $response->getStatusCode();
		$reply['body'] = json_decode($response->getBody(), true);

	} catch (GuzzleHttp\Exception\BadResponseException $e) {

		$res = $e->getResponse();
        $reply['code'] = 400;
        $reply['body'] = json_decode($res->getBody()->getContents(), true);

	}

	return $reply;
}

function refund_charge($charge_id, $payload) {
    /*
    $payload = [
        "amount" => 50000,
        "reason" => "Customer request"
    ]
    */
    global $client, $auth;
	$reply = '';
	try {
		$response = $client->request('POST', 'v1.1/charges/'.$charge_id.'/refund', [
            'auth' => $auth,
            'json'=> $payload
        ]);
		$reply['code'] = $response->getStatusCode();
		$reply['body'] = json_decode($response->getBody(), true);

	} catch (GuzzleHttp\Exception\BadResponseException $e) {

		$res = $e->getResponse();
		$reply['code'] = 400;
        $reply['body'] = json_decode($res->getBody()->getContents(), true);

	}

	return $reply;
}
?>
