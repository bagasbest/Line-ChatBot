<?php
require __DIR__ . '/../vendor/autoload.php';

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Factory\AppFactory;

use \LINE\LINEBot;
use \LINE\LINEBot\HTTPClient\CurlHTTPClient;
use \LINE\LINEBot\MessageBuilder\MultiMessageBulider;
use \LINE\LINEBot\MessageBuilder\TextMessageBuilder;
use \LINE\LINEBot\MessageBuilder\StickerMessageBuilder;
use \LINE\LINEBot\SignatureValidator as SignatureValidator;

$pass_signature = true;

//set LINE channel_access_token and channel_secret
$channel_access_token = "6mSRMgppVDcMnaIx9rPMtUodKuUt1MfBcMpqz1g2iqKnKB6ZJXvcpAWmDje5QuM8n4FiS+tUYeQYx11h3imLw9w/h1ASmvpd0hniDN2kA9rg4BDW+sqYo2L7BAWAoxLZAsKUir3DCroudLJThmtkKgdB04t89/1O/w1cDnyilFU=";
$channel_secret = "ece13b8b53a9b51fb493b93bf5f7d1cf";

// inisiasi objek bot
$httpClient = new CurlHTTPClient($channel_access_token);
$bot = new LINEBot($httpClient, ['channelSecret' => $channel_secret]);

$app = AppFactory::create();
$app->setBasePath("/public");

$app->get('/', function (Request $request, Response $response, $args){
    $response->getBody()->write("Hello User");
    return $response;
});

//buat route untuk webhook
$app->post('/webhook', function (Request $request, Response $response) use ($channel_secret, $bot, $httpClient, $pass_signature) {
    //get request body and line signature header
    $body = $request->getBody();
    $signature = $request->getHeaderLine('HTTP_X_LINE_SIGNATURE');

    //log body and signature
    file_put_contents('php://stderr', 'Body' . $body);

    if($pass_signature == false) {
        //is LINE_SIGNATURE exist in request header?
        if(empty($signature)){
            return $response->withStatus(400, 'Signature not set');
        }

        //is this request come from LINE?
        if(!SignatureValidator::validateSignature($body, $channel_secret, $signature)){
            return $response->withStatus(400, 'Invalid signature');
        }
    }

});
$app->run();