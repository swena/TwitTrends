<?php
require 'vendor/autoload.php';
use Aws\Sns\SnsClient; 
$headers = apache_request_headers();

    foreach ($headers as $header => $value) {
        echo "$header: $value <br />\n";
    }
$client = SnsClient::factory(array(
                    'region' => 'us-west-2',
                    'version' => 'latest',
                    'credentials' => [
                        'key' => 'XXXXXX',
                        'secret' => 'XXXXXX'
                    ]
    )); 
while(true)
{
	if(isset($_SERVER['x-amz-sns-message-type']) && $_SERVER['x-amz-sns-message-type'] == 'SubscriptionConfirmation')
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
	else if (isset($_SERVER['x-amz-sns-message-type']) && $_SERVER['x-amz-sns-message-type'] == 'Notification')
	{
		print "ABCHJSKJA";
	}
}
?>
