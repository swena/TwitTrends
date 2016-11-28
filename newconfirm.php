<?php
require 'vendor/autoload.php';
use Aws\Sns\SnsClient; 
$headers = apache_request_headers();
$params = array();
$params['hosts'] = array (
	'http://localhost'        // SSL to localhost
);
$es = new Elasticsearch\Client($params);

    foreach ($headers as $header => $value) {
        echo "$header: $value <br />\n";
    }
$client = SnsClient::factory(array(
                    'region' => 'us-west-2',
                    'version' => 'latest',
                    'credentials' => [
                        'key' => 'XXXXX',
                        'secret' => 'XXXXX'
                    ]
    )); 

if(isset($headers['x-amz-sns-message-type']) && $headers['x-amz-sns-message-type'] == 'SubscriptionConfirmation')
	{
		$postBody = file_get_contents('php://input');

		// JSON decode the body to an array of message data
		$message = json_decode($postBody, true);
		print_r($message);
		 $result = $client->confirmSubscription([
	                'Token' => $message['Token'], // REQUIRED
	                'TopicArn' => 'arn:aws:sns:us-west-2:174840842210:TwittTrends', // REQUIRED
	        ]);
	}
	else if (isset($headers['x-amz-sns-message-type']) && $headers['x-amz-sns-message-type'] == 'Notification')
	{
		$postBody = file_get_contents('php://input');

		// JSON decode the body to an array of message data
		$message = json_decode($postBody, true);

		$msg = json_decode($message['Message'],true);
		$indexed = $es->index([
                                'index' => 'twittrends',
                                'type' => 'tweet',
                                'body' => $msg
                            ]);
		//print_r($message);
	}

?>
