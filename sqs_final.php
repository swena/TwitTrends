<?php

require 'vendor/autoload.php';
require_once "thread.php";
require_once('phirehose/lib/Phirehose.php');
require_once('phirehose/lib/OauthPhirehose.php');
require_once 'alchemyapi_php/alchemyapi.php';
use Aws\Sqs\SqsClient;
use Aws\Sns\SnsClient; 


class FilterTrackConsumer extends OauthPhirehose
{
  /**
   * Enqueue each status
   *
   * @param string $status
   */
  public $client;
  public $url;
  public $snsclient;
  public function enqueueStatus($status)
  {
    /*
     * In this simple example, we will just display to STDOUT rather than enqueue.
     * NOTE: You should NOT be processing tweets at this point in a real application, instead they should be being
     *       enqueued and processed asyncronously from the collection process. 
     */
    $data = json_decode($status, true);
    #print "<pre>";
    #var_dump($data);
    #print "</pre>";
    #if (is_array($data) && isset($data['user']['screen_name'])) {
    #  print $data['user']['screen_name'] . ': ' . urldecode($data['text']) . "<br>";
    #}
    if(is_array($data)  && isset($data['geo']) && $data['geo']!=NULL && isset($data['lang']) && $data['lang']=='en')
    {
        try {
                //$our_message = array('var11' => 'heloo!');
                $json = array('lat' => $data['geo']['coordinates'][0], 'long' => $data['geo']['coordinates'][1], 'text' => addslashes($data['text']));

                #print_r($sqs_client);
                $this->client->sendMessage(array(
                    'QueueUrl' => $this->url,
                    'MessageBody' => json_encode($json)

                ));
                $thread1 = new MyWork('thread1',$this->client,$this->url,$this->snsclient);

                $thread1->run();
        }

        catch (Exception $e) {
            die('Error sending message to queue ' . $e->getMessage());
        }
      
    }
  }
}

class MyWork extends Thread{

    public $name;
    public $client;
    public $url;
    public $snsclient;

    public function __construct($name,$sqsclient,$queue,$sns) {
        #echo "Constructing worker $name<br>";
        $this->name = $name;
        $this->client = $sqsclient;
        $this->url = $queue;
        $this->snsclient = $sns;
    }



    public function run() {
        
        #echo "Worker $this->name start running<br>";
            


        $result = $this->client->receiveMessage(array('QueueUrl' => $this->url));
#'MaxNumberOfMessages' => 10
        if ($result['Messages'] == null) {
            #echo 'No Messages';
            exit;
        }
           
    
        $result_message = array_pop($result['Messages']);
        $queue_handle = $result_message['ReceiptHandle'];
        $message_json = $result_message['Body'];
        $tweetjson = json_decode($message_json,true);

        $this->client->deleteMessage(array(
             'QueueUrl' => $this->url,
             'ReceiptHandle' => $queue_handle
        ));
        #print $tweetjson['text'];
        $sentiment = $this->sentimentAnalysis($tweetjson['text']);
        $params = array();
        $params['hosts'] = array (
            'http://localhost'        // SSL to localhost
        );
        #$es = new Elasticsearch\Client($params);
        
        $long = $tweetjson['long'];
        $lat = $tweetjson['lat'];
        $tweet = $tweetjson['text'];
        $keyword = 'you';
        $keys = array('president', 'love', 'trump', 'vote','food','holiday','sale','hollywood','you');
        foreach($keys as $a) {
            if (stripos($tweet,$a) !== false) 
            {
                $keyword = $a;
                break;
            }

        }
    $enc = json_encode([
                                    'keyword' => $keyword,
                                    'lat' => $lat,
                                    'long' => $long,
                                    'tweet' => $tweet,
                                    'sentiment' => $sentiment
                                ]);
        $result = $this->snsclient->publish([
                'Message' => $enc,
                'TopicArn' => 'arn:aws:sns:us-west-2:174840842210:TwittTrends'
            ]);
        /*$indexed = $es->index([
                                'index' => 'twittrend',
                                'type' => 'tweet',
                                'body' => [
                                    'keyword' => $keyword,
                                    'lat' => $lat,
                                    'long' => $long,
                                    'tweet' => $tweet,
                                    'sentiment' => $sentiment
                                ]
                            ]);*/
        print "<b> keyword : ".$keyword."</b> <b>sentiment : ".$sentiment."</b> text : ".$tweetjson['text']."<br>";
            
    }
    public function sentimentAnalysis($myText)
    {
        $alchemyapi = new AlchemyAPI("1f485fe6acc0cab1d0a4ca319a95f1f8c7d335b9");
        $response = $alchemyapi->sentiment("text", $myText, null);
        return $response["docSentiment"]["type"];
    }
}


/**
 * Example of using Phirehose to display a live filtered stream using track words 
 */
$sqs_credentials = array(
                'region' => 'us-west-2',
                'version' => 'latest',
                'credentials' => array(
                    'key' => 'XXXXX',
                    'secret' => 'XXXXX'
                )
            );


define("TWITTER_CONSUMER_KEY", "XXXXX");
define("TWITTER_CONSUMER_SECRET", "XXXXX");


// The OAuth data for the twitter account
define("OAUTH_TOKEN", "XXXXX");
define("OAUTH_SECRET", "XXXXX");

// Start streaming

 
    
  
    $sns_client = SnsClient::factory(array(
                    'region' => 'us-west-2',
                    'version' => 'latest',
                    'credentials' => [
                        'key' => 'XXXXX',
                        'secret' => 'XXXXX'
                    ]
    )); 
    $result = $sns_client->subscribe([
        'Endpoint' => 'http://custom-env.ihxhacnyr2.us-west-2.elasticbeanstalk.com/newconfirm.php',
        'Protocol' => 'HTTP', // REQUIRED
        'TopicArn' => 'arn:aws:sns:us-west-2:174840842210:TwittTrends', // REQUIRED
    ]);
while(true)
{

    // The OAuth credentials you received when registering your app at Twitter
    $sqs_client = new SqsClient($sqs_credentials);
    #$queue_options = array('QueueName' => 'Tweets1');
    #$sqs_client->createQueue($queue_options);
    #$result = $sqs_client->getQueueUrl(array('QueueName' => "Tweets1"));
    #$queue_url = $result->get('QueueUrl');
    
    $sc = new FilterTrackConsumer(OAUTH_TOKEN, OAUTH_SECRET, Phirehose::METHOD_FILTER);
    $queue_url = 'https://sqs.us-west-2.amazonaws.com/174840842210/Tweets1';
    $sc->snsclient = $sns_client;
    $sc->client = $sqs_client;
    $sc->url = $queue_url;
    $sc->setTrack(array('president', 'love', 'trump', 'vote','food','holiday','sale','hollywood','you'));
    $sc->consume();
    #while(true)
    #{
        #$thread1 = new MyWork('thread1',$sqs_client,$queue_url);

        #$thread1->run();
    #}
}

?>
