<html>
<head>
	<!-- AIzaSyB45rLge0qJX25y20ejv_B9iJG-mHLwt5E -->
    <meta name="viewport" content="initial-scale=1.0">
    <meta charset="utf-8">

</head>
<body>
	<div class="heading"><h1>TwittMap</h1></div>

<?php

	require_once ('init.php');
	/*use Elasticsearch\ClientBuilder;
	
	$indexed = $es->index([
						'index' => 'tweets',
						'type' => 'tweet',
						'body' => [
							'keyword' => 'ABC',
							'location' => 'ABC',
							'created_at' =>'ABC',
							'tweet' => 'ABC'
						]
					]);
	$handler = array(
				'host' => 'search-twittrends-ugmdwcpwne5ftgmdvsqtwxdoni.us-west-2.es.amazonaws.com',
                'region' => 'us-west-2',
                'version' => 'latest',
                'credentials' => array(
                    'key'    => 'AKIAJI4AYP3LYDSTYBIA',
                    'secret' => '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',
                )
            );
	
	$hosts = ['search-twittrends-ugmdwcpwne5ftgmdvsqtwxdoni.us-west-2.es.amazonaws.com'];
	$client =  ClientBuilder::create()
				->setConnectionFactory($handler)
				->setHosts($hosts)      // Set the hosts
				->build();
	#$params = 
	

	#print_r($response);
	$params = [
	    'index' => 'my_index',
	    'body' => ['testField' => 'abc']
	];

	$response = $client->indices()->create($params);
	print_r($response);*/
	/*$params = [
	    'index' => 'my_index',
	    'type' => 'my_type',
	    'id' => 'my_id'
	];

	$response = $client->get($params);
	print_r($response);
	$params = [
	    'index' => 'my_index',
	    'type' => 'my_type',
	    'body' => [
	        'query' => [
	            'match' => [
	                'testField' => 'abc'
	            ]
	        ]
	    ]
	];

	$response = $client->search($params);
	print_r($response);*/

	/**********SNS************/
	require_once ('init.php');
	use Aws\Sns\SnsClient;
	use Aws\Sns\MessageValidator;
	

	$client = SnsClient::factory(array(
					'region' => 'us-west-2',
                    'version' => 'latest',
                    'credentials' => [
                        'key' => 'AKIAIZ2OQ2QMUFT2525A',
                        'secret' => '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'
                    ]
	));
	if(isset($_POST['x-amz-sns-message-type']) && $_POST['x-amz-sns-message-type'] == 'SubscriptionConfirmation')
	{
		print "SubscriptionConfirmation";
		print_r($_POST);
		$postBody = file_get_contents('php://input');
		$json = json_decode($postBody, true);
		$result = $client->confirmSubscription(array(
		    // TopicArn is required
		    'TopicArn' => 'arn:aws:sns:us-west-2:174840842210:TwittTrends',
		    // Token is required
		    'Token' => $json['Token']
		));
		
	}
	
	if(isset($_POST['x-amz-sns-message-type']) && $_POST['x-amz-sns-message-type'] == 'Notification')
	{
		print "GOT NOTIFICATION";
		print_r($_POST);
	}
	

	/**********Sentiment************/
	/*require_once 'alchemyapi_php/alchemyapi.php';
	$alchemyapi = new AlchemyAPI("1f485fe6acc0cab1d0a4ca319a95f1f8c7d335b9");

	$myText = "Thank you so much for naming us the Best Brewery in South Florida, according toâ€¦ https://t.co/usZRnRAWTs";
	$response = $alchemyapi->sentiment("text", addslashes($myText), null);
	echo "Sentiment: ", $response["docSentiment"]["type"], PHP_EOL;*/
?>

</body>
</html>