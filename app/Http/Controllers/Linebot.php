<?php

namespace App\Http\Controllers;

require(__DIR__.'/../../../lib/vendor/autoload.php');
use \LINE\LINEBot\SignatureValidator as SignatureValidator;
use \LINE\LINEBot\HTTPClient\CurlHTTPClient;
use  \LINE\LINEBot;

// load config
$dotenv = new Dotenv\Dotenv(__DIR__);
$dotenv->load();

class Linebot extends Controller
{
	public function replyMessage() {

		//get request body
		$body = file_get_contents('php://input');
		$signature = $_SERVER['HTTP_X_LINE_SIGNATURE'];

		if (empty($signature)){
			return $response->withStatus(400, 'Signature not set');
		}

		// is this request comes from LINE?
		if($_ENV['PASS_SIGNATURE'] == false && ! SignatureValidator::validateSignature($body, $_ENV['CHANNEL_SECRET'], $signature)){
			return $response->withStatus(400, 'Invalid signature');
		}

		$httpClient = new CurlHTTPClient($_ENV['CHANNEL_ACCESS_TOKEN']);
		$bot = new LINEBot($httpClient, ['channelSecret' => $_ENV['CHANNEL_SECRET']])

		$data = json_decode($body, true);
		foreach ($data['events'] as $event) {
			if ($event['type'] == 'message') {
				if ($event['message']['type'] == 'text') {
					$result = $bot->replyText($event['replyToken'], $event['message']['text']);
					echo $result -> getHttpStatus().' '. $result->getRawBody();
				}
			}
		}
	}
}