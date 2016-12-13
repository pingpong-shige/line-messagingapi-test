<?php
$accessToken = getenv('LINE_CHANNEL_ACCESS_TOKEN');

//ユーザーからのメッセージ取得
$json_string = file_get_contents('php://input');
$jsonObj = json_decode($json_string);

$type = $jsonObj->{"events"}[0]->{"message"}->{"type"};
//メッセージ取得
$text = $jsonObj->{"events"}[0]->{"message"}->{"text"};
//ReplyToken取得
$replyToken = $jsonObj->{"events"}[0]->{"replyToken"};


/*$type = $jsonObj->{"events"}[0]->{"message"}->{"type"};
//スタンプ取得
$text = $jsonObj->{"events"}[0]->{"message"}->{"sticker"};
//ReplyToken取得
$replyToken = $jsonObj->{"STKTXT"=>"[おねがい]","AT_RECV_MODE"=>"2",
			"STKVER"=>"100","STDID"=>"4","STKPKGID"=>"1",
			"SKIP_BADGE_COUNT"=>"true"};*/
//メッセージ以外のときは何も返さず終了
if($type != "text"){
	exit;
}

//返信データ作成
if ($text == 'はい') {
  $response_format_text = [
    "type" => "template",
    "altText" => "こちらの〇〇はいかがですか？",
    "template" => [
      "type" => "buttons",
      "thumbnailImageUrl" => "https://" . $_SERVER['SERVER_NAME'] . "/img1.jpg",
      "title" => "○○レストラン",
      "text" => "お探しのレストランはこれですね",
      "actions" => [
          [
            "type" => "postback",
            "label" => "予約する",
            "data" => "action=buy&itemid=123"
          ],
          [
            "type" => "postback",
            "label" => "電話する",
            "data" => "action=pcall&itemid=123"
          ],
          [
            "type" => "uri",
            "label" => "詳しく見る",
            "uri" => "https://" . $_SERVER['SERVER_NAME'] . "/"
          ],
          [
            "type" => "message",
            "label" => "違うやつ",
            "text" => "他を探す"
          ]
      ]
    ]
  ];
} else if ($text == 'いいえ') {
  exit;
} else if ($text == '他を探す') {
  $response_format_text = [
    "type" => "template",
    "altText" => "候補を３つご案内しています。",
    "template" => [
      "type" => "carousel",
      "columns" => [
          [
            "thumbnailImageUrl" => "https://" . $_SERVER['SERVER_NAME'] . "/img2-1.jpg",
            "title" => "●●レストラン",
            "text" => "こちらにしますか？",
            "actions" => [
              [
                  "type" => "postback",
                  "label" => "予約する",
                  "data" => "action=rsv&itemid=111"
              ],
              [
                  "type" => "postback",
                  "label" => "電話する",
                  "data" => "action=pcall&itemid=111"
              ],
              [
                  "type" => "uri",
                  "label" => "詳しく見る（ブラウザ起動）",
                  "uri" => "https://" . $_SERVER['SERVER_NAME'] . "/"
              ]
            ]
          ],
          [
            "thumbnailImageUrl" => "https://" . $_SERVER['SERVER_NAME'] . "/img2-2.jpg",
            "title" => "▲▲レストラン",
            "text" => "それともこちら？（２つ目）",
            "actions" => [
              [
                  "type" => "postback",
                  "label" => "予約する",
                  "data" => "action=rsv&itemid=222"
              ],
              [
                  "type" => "postback",
                  "label" => "電話する",
                  "data" => "action=pcall&itemid=222"
              ],
              [
                  "type" => "uri",
                  "label" => "詳しく見る（ブラウザ起動）",
                  "uri" => "https://" . $_SERVER['SERVER_NAME'] . "/"
              ]
            ]
          ],
          [
            "thumbnailImageUrl" => "https://" . $_SERVER['SERVER_NAME'] . "/img2-3.jpg",
            "title" => "■■レストラン",
            "text" => "はたまたこちら？（３つ目）",
            "actions" => [
              [
                  "type" => "postback",
                  "label" => "予約する",
                  "data" => "action=rsv&itemid=333"
              ],
              [
                  "type" => "postback",
                  "label" => "電話する",
                  "data" => "action=pcall&itemid=333"
              ],
              [
                  "type" => "uri",
                  "label" => "詳しく見る（ブラウザ起動）",
                  "uri" => "https://" . $_SERVER['SERVER_NAME'] . "/"
              ]
            ]
          ]
      ]
    ]
  ];
} else if ($text == 'こんにちは') {
  $response_format_text = [
    "type" => "template",
    "altText" => "こんにちは、何かご用ですか？（はい／いいえ）",
    "template" => [
        "type" => "confirm",
        "text" => "こんにちは、何かご用ですか？",
        "actions" => [
            [
              "type" => "message",
              "label" => "はい",
              "text" => "はい"
            ],
            [
              "type" => "message",
              "label" => "いいえ",
              "text" => "いいえ"
            ]
        ]
    ]
  ];
} else {
  //ドコモの雑談データ取得
  $response = chat($text);
 
  $response_format_text = [
      "type" => "text",
      "text" =>  $response
  ];
}

$post_data = [
	"replyToken" => $replyToken,
	"messages" => [$response_format_text]
];

$ch = curl_init("https://api.line.me/v2/bot/message/reply");
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($post_data));
curl_setopt($ch, CURLOPT_HTTPHEADER, array(
    'Content-Type: application/json; charser=UTF-8',
    'Authorization: Bearer ' . $accessToken
    ));
$result = curl_exec($ch);
curl_close($ch);


//ドコモの雑談APIから雑談データを取得
function chat($text) {
    // docomo chatAPI
    $api_key = getenv('DOCOMO_API_KEY');
    $api_url = sprintf('https://api.apigw.smt.docomo.ne.jp/dialogue/v1/dialogue?APIKEY=%s', $api_key);
    $req_body = array('utt' => $text);
    
    $headers = array(
        'Content-Type: application/json; charset=UTF-8',
    );
    $options = array(
        'http'=>array(
            'method'  => 'POST',
            'header'  => implode("\r\n", $headers),
            'content' => json_encode($req_body),
            )
        );
    $stream = stream_context_create($options);
    $res = json_decode(file_get_contents($api_url, false, $stream));
 
    return $res->utt;
}


/*require_once('LineBot.php');
// LINE:チャンネルID
$CHANNEL_ID = '1491932896';
// LINE:チャンネルシークレット
$CHANNEL_SECRET = '3a1166f4866376b1a42276a124e4d2b3';
// LINE:MID
$CHANNEL_MID = '@ust3694v';
// Bingアカウントキー
$ACCOUNT_KEY = 'i9Deyl1gN1mUCKwOMmHrEswDV61gsddzuq+E/4SPPHc';
$bot = new LineBot($CHANNEL_ID, $CHANNEL_SECRET, $CHANNEL_MID);
$bot->sendText('「%s」デスネ...');
$bot->sendImage($ACCOUNT_KEY);*/

/**
* BingSeachAPIで画像を取得するサンプルコード
*
* @param string $keyword 検索ワード
* @return object 
*/

/*function search_bing($keyword) {
  //取得したアカウントキー
  $accountKey = 'i9Deyl1gN1mUCKwOMmHrEswDV61gsddzuq+E/4SPPHc';

  //エンドポイントとパラメーターなどをセット
  //画像検索以外の場合は$serviceOpを変更
  $query = urlencode("'{$keyword}'");
  $rootUri = 'https://api.datamarket.azure.com/Bing/Search';
  $serviceOp = "Image";
  $endpoint = "$rootUri/$serviceOp?\$format=json&Query=$query&ImageFilters='Aspect:Wide'";

  //ストリームコンテキストを作成
  $auth = base64_encode("$accountKey:$accountKey");
  $data = array(
    'http' => array(
    'request_fulluri' => true,
    'ignore_errors' => true,
    'header' => "Authorization: Basic $auth")
  );
  $context = stream_context_create($data);

  //とりあえず、file_get_contents()でjsonを取得
  $response = file_get_contents($endpoint, 0, $context);
  $response = json_decode($response);

  return $response;
}*/
