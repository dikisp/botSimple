<?php
require __DIR__ . '/vendor/autoload.php';

use \LINE\LINEBot;
use \LINE\LINEBot\HTTPClient\CurlHTTPClient;
use \LINE\LINEBot\MessageBuilder\MultiMessageBuilder;
use \LINE\LINEBot\MessageBuilder\TextMessageBuilder;
use \LINE\LINEBot\MessageBuilder\StickerMessageBuilder;
use \LINE\LINEBot\MessageBuilder\ImageMessageBuilder;
use \LINE\LINEBot\MessageBuilder\AudioMessageBuilder;
use \LINE\LINEBot\MessageBuilder\VideoMessageBuilder;
use \LINE\LINEBot\SignatureValidator as SignatureValidator;

// set false for production
$pass_signature = true;

// set LINE channel_access_token and channel_secret
$channel_access_token = "Agxk/9Cd9MDld69EMZC2BNNQuwyZzwWMJcyxG+UDOf5WR4OBQP0r1fnjEOgFfTwB/k2SxceK9cQSObFN3RbwDQ+HZMAA1NMsf9z47nY9tnlppuhQFOOv1gmbGHMaYOao8kGc/iATX0EUq+vEBKoRrQdB04t89/1O/w1cDnyilFU=";
$channel_secret = "3d42911542b9e7394f6b57b7378d2fb2";

// inisiasi objek bot
$httpClient = new CurlHTTPClient($channel_access_token);
$bot = new LINEBot($httpClient, ['channelSecret' => $channel_secret]);

$configs =  [
    'settings' => ['displayErrorDetails' => true],
];
$app = new Slim\App($configs);

// buat route untuk url homepage
$app->get('/', function($req, $res)
{
  echo "Hello World";
});

// buat route untuk webhook
$app->post('https://oursched.herokuapp.com/index.php/webhook', function ($request, $response) use ($bot, $pass_signature)
{
    // get request body and line signature header
    $body        = file_get_contents('php://input');
    $signature = isset($_SERVER['HTTP_X_LINE_SIGNATURE']) ? $_SERVER['HTTP_X_LINE_SIGNATURE'] : '';

    // log body and signature
    file_put_contents('php://stderr', 'Body: '.$body);

    if($pass_signature === false)
    {
        // is LINE_SIGNATURE exists in request header?
        if(empty($signature)){
            return $response->withStatus(400, 'Signature not set');
        }

        // is this request comes from LINE?
        if(! SignatureValidator::validateSignature($body, $channel_secret, $signature)){
            return $response->withStatus(400, 'Invalid signature');
            }
     }

// pengganti notifikasi replay message
$data = json_decode($body, true);
if(is_array($data['events'])){
    foreach ($data['events'] as $event)
    {
        if ($event['type'] == 'message')
        {
               if(
                 $event['source']['type'] == 'group' or
                 $event['source']['type'] == 'room'
               ){
                //message from group / room 
                if($event['source']['userId']){
 
                    $userId     = $event['source']['userId'];
                    $getprofile = $bot->getProfile($userId);
                    $profile    = $getprofile->getJSONDecodedBody();
                    $greetings  = new TextMessageBuilder("Halo, ".$profile['displayName']);
                 
                    $result = $bot->replyMessage($event['replyToken'], $greetings);
                    return $res->withJson($result->getJSONDecodedBody(), $result->getHTTPStatus());             
               } else {
                //message from single user
                $result = $bot->replyText($event['replyToken'], $event['message']['text']);
                return $res->withJson($result->getJSONDecodedBody(), $result->getHTTPStatus());
               }
            }
        }
        if(
            $event['message']['type'] == 'image' or
            $event['message']['type'] == 'video' or
            $event['message']['type'] == 'audio' or
            $event['message']['type'] == 'file'
        ){
            $basePath  = $request->getUri()->getBaseUrl();
            $contentURL  = $basePath."/content/".$event['message']['id'];
            $contentType = ucfirst($event['message']['type']);
            $result = $bot->replyText($event['replyToken'],
                $contentType. " yang Anda kirim bisa diakses dari link:\n " . $contentURL);
         
            return $res->withJson($result->getJSONDecodedBody(), $result->getHTTPStatus());
        }
    }
}


            // kode aplikasi nanti disini
        //     $data = json_decode($body, true);
        //     if(is_array($data['events'])){
        //         foreach ($data['events'] as $event)
        //         {
        //             if ($event['type'] == 'message')
        //             {
        //                 if($event['message']['type'] == 'text')
        //                 {
        //                     // send same message as reply to user
        //                     $result = $bot->replyText($event['replyToken'], $event['message']['text']);
        //                     $bot->replyText($replyToken, 'ini pesan balasan');

        //                     $textMessageBuilder = new TextMessageBuilder('ini pesan balasan');
        //                     $bot->replyMessage($replyToken, $textMessageBuilder);
            
        //                     //replay message
        //                     $imageMessageBuilder = new ImageMessageBuilder('url gambar asli', 'url gambar preview');
        //                     $bot->replyMessage($replyToken, $imageMessageBuilder);
        //                     $audioMessageBuilder = new AudioMessageBuilder('url audio asli', 'durasi audio');
        //                     $bot->replyMessage($replyToken, $audioMessageBuilder);
        //                     $videoMessageBuilder = new VideoMessageBuilder('url video asli', 'url gambar preview video');
        //                     $bot->replyMessage($replyToken, $videoMessageBuilder);


        //                     $textMessageBuilder1 = new TextMessageBuilder('ini pesan balasan pertama');
        //                     $textMessageBuilder2 = new TextMessageBuilder('ini pesan balasan kedua');
        //                     $stickerMessageBuilder = new StickerMessageBuilder(1, 106);
                            
        //                     $multiMessageBuilder = new MultiMessageBuilder();
        //                     $multiMessageBuilder->add($textMessageBuilder1);
        //                     $multiMessageBuilder->add($textMessageBuilder2);
        //                     $multiMessageBuilder->add($stickerMessageBuilder);
                            
        //                     $bot->replyMessage($replyToken, $multiMessageBuilder);
        //                     $bot->getProfile(userId);
        //                     $bot->multicast(userList, MessageBuilder);
        //                     $bot->getMessageContent(messageId);
                            
        //                     $userList = [
        //                         'U80209323bd1edec5c687c4cc9e7921e1'];
                             
        //                     // send multicast message to user
        //                     $textMessageBuilder = new TextMessageBuilder('Halo, ini pesan multicast');
        //                     $result = $bot->multicast($userList, $textMessageBuilder);
        //                     // or we can use replyMessage() instead to send reply message
        //                     // $textMessageBuilder = new TextMessageBuilder($event['message']['text']);
        //                     // $result = $bot->replyMessage($event['replyToken'], $textMessageBuilder);
            
        //                     return $response->withJson($result->getJSONDecodedBody(), $result->getHTTPStatus());
        //                 }
        //                     if(
        //                         $event['message']['type'] == 'image' or
        //                         $event['message']['type'] == 'video' or
        //                         $event['message']['type'] == 'audio' or
        //                         $event['message']['type'] == 'file'
        //                     ){
        //                         $basePath  = $request->getUri()->getBaseUrl();
        //                         $contentURL  = $basePath."/content/".$event['message']['id'];
        //                         $contentType = ucfirst($event['message']['type']);
        //                         $result = $bot->replyText($event['replyToken'],
        //                             $contentType. " yang Anda kirim bisa diakses dari link:\n " . $contentURL);
                            
        //                         return $res->withJson($result->getJSONDecodedBody(), $result->getHTTPStatus());
        //                     }
        //             }
        //     } 
        // }

    });


    $app->get('/pushmessage', function($req, $res) use ($bot)
    {
        // send push message to user
        $userId = 'U80209323bd1edec5c687c4cc9e7921e1';
        $textMessageBuilder = new TextMessageBuilder('Hallo Bisa antar saya ke suatu tempat ?');
        $result = $bot->pushMessage($userId, $textMessageBuilder);
       
        return $res->withJson($result->getJSONDecodedBody(), $result->getHTTPStatus());
    });

    $app->get('/multicast', function($req, $res) use ($bot)
        {
            // list of users
            $userList = [
                'U80209323bd1edec5c687c4cc9e7921e1'];
        
            // send multicast message to user
            $textMessageBuilder = new TextMessageBuilder('Multicast');
            $result = $bot->multicast($userList, $textMessageBuilder);
        
            return $res->withJson($result->getJSONDecodedBody(), $result->getHTTPStatus());
        });
    $app->get('/profile', function($req, $res) use ($bot)
    {
        // get user profile
        $userId = 'U80209323bd1edec5c687c4cc9e7921e1';
        $result = $bot->getProfile($userId);
    
        return $res->withJson($result->getJSONDecodedBody(), $result->getHTTPStatus());
    });

// mengirim mengakses content
$app->get('/content/{messageId}', function($req, $res) use ($bot)
{
    // get message content
    $route      = $req->getAttribute('route');
    $messageId = $route->getArgument('messageId');
    $result = $bot->getMessageContent($messageId);
 
    // set response
    $res->write($result->getRawBody());
 
    return $res->withHeader('Content-Type', $result->getHeader('Content-Type'));
});
    
$app->run();

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>OurSch</title>
    <style>
        *{
            font-family : roboto;
        }
        h1{
            background :black;
            color : white;
        }
    </style>
</head>
<body>
    <h1>hello world</h1>
</body>
</html>