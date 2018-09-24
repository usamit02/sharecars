<?php
//require_once __DIR__ . '/vendor/autoload.php';
require_once $_SERVER['DOCUMENT_ROOT'].'/LINE/LINEBot.php';
require_once $_SERVER['DOCUMENT_ROOT'].'/LINE/LINEBot/HTTPClient.php';
require_once $_SERVER['DOCUMENT_ROOT'].'/LINE/LINEBot/HTTPClient/Curl.php';
require_once $_SERVER['DOCUMENT_ROOT'].'/LINE/LINEBot/HTTPClient/CurlHTTPClient.php';
require_once $_SERVER['DOCUMENT_ROOT'].'/LINE/LINEBot/Constant/HTTPHeader.php';
require_once $_SERVER['DOCUMENT_ROOT'].'/LINE/LINEBot/Exception/InvalidSignatureException.php';
require_once $_SERVER['DOCUMENT_ROOT'].'/LINE/LINEBot/Exception/InvalidEventRequestException.php';
require_once $_SERVER['DOCUMENT_ROOT'].'/LINE/LINEBot/Event/BaseEvent.php';
require_once $_SERVER['DOCUMENT_ROOT'].'/LINE/LINEBot/Event/PostbackEvent.php';
require_once $_SERVER['DOCUMENT_ROOT'].'/LINE/LINEBot/Event/MessageEvent.php';
require_once $_SERVER['DOCUMENT_ROOT'].'/LINE/LINEBot/Event/MessageEvent/TextMessage.php';
require_once $_SERVER['DOCUMENT_ROOT'].'/LINE/LINEBot/Event/MessageEvent/StickerMessage.php';
require_once $_SERVER['DOCUMENT_ROOT'].'/LINE/LINEBot/Event/Parser/EventRequestParser.php';
require_once $_SERVER['DOCUMENT_ROOT'].'/LINE/LINEBot/SignatureValidator.php';
require_once $_SERVER['DOCUMENT_ROOT'].'/LINE/LINEBot/MessageBuilder.php';
require_once $_SERVER['DOCUMENT_ROOT'].'/LINE/LINEBot/MessageBuilder/TextMessageBuilder.php';
require_once $_SERVER['DOCUMENT_ROOT'].'/LINE/LINEBot/MessageBuilder/TemplateBuilder.php';
require_once $_SERVER['DOCUMENT_ROOT'].'/LINE/LINEBot/TemplateActionBuilder.php';
require_once $_SERVER['DOCUMENT_ROOT'].'/LINE/LINEBot/MessageBuilder/TemplateMessageBuilder.php';
require_once $_SERVER['DOCUMENT_ROOT'].'/LINE/LINEBot/TemplateActionBuilder/UriTemplateActionBuilder.php';
require_once $_SERVER['DOCUMENT_ROOT'].'/LINE/LINEBot/MessageBuilder/TemplateBuilder/ButtonTemplateBuilder.php';
require_once $_SERVER['DOCUMENT_ROOT'].'/LINE/LINEBot/Constant/Meta.php';
require_once $_SERVER['DOCUMENT_ROOT'].'/LINE/LINEBot/Constant/MessageType.php';
require_once $_SERVER['DOCUMENT_ROOT'].'/LINE/LINEBot/Constant/ActionType.php';
require_once $_SERVER['DOCUMENT_ROOT'].'/LINE/LINEBot/Constant/TemplateType.php';
require_once $_SERVER['DOCUMENT_ROOT'].'/LINE/LINEBot/Exception/CurlExecutionException.php';
require_once $_SERVER['DOCUMENT_ROOT'].'/LINE/LINEBot/Response.php';
require_once $_SERVER['DOCUMENT_ROOT'].'/include/db.php';
$r=$db->query("SELECT access_token,channel_secret FROM mt16lineat WHERE id=".$_GET['lineatid'])->fetch();
$ownerbot = new \LINE\LINEBot(new \LINE\LINEBot\HTTPClient\CurlHTTPClient($r['access_token']), ['channelSecret'=>$r['channel_secret']]);
$signature = $_SERVER['HTTP_'.\LINE\LINEBot\Constant\HTTPHeader::LINE_SIGNATURE];
try {
    $events = $ownerbot->parseEventRequest(file_get_contents('php://input'), $signature);
} catch(\LINE\LINEBot\Exception\InvalidSignatureException $e) {
    error_log('parseEventRequest failed. InvalidSignatureException => '.var_export($e, true));
} catch(\LINE\LINEBot\Exception\UnknownEventTypeException $e) {
    error_log('parseEventRequest failed. UnknownEventTypeException => '.var_export($e, true));
} catch(\LINE\LINEBot\Exception\UnknownMessageTypeException $e) {
    error_log('parseEventRequest failed. UnknownMessageTypeException => '.var_export($e, true));
} catch(\LINE\LINEBot\Exception\InvalidEventRequestException $e) {
    error_log('parseEventRequest failed. InvalidEventRequestException => '.var_export($e, true));
}
$todate=new DateTime();
foreach ($events as $event){
    if($event instanceof \LINE\LINEBot\Event\PostbackEvent){
        $lineid = $event->getUserId();
        $queryString = $event->getPostbackData();
        if ($queryString){
            parse_str($queryString, $data);
            $res=(isset($data["res"]))?$data["res"]:0;
            $carid=(isset($data["carid"]))?$data["carid"]:0;
            $userid=(isset($data["id"]))?$data["id"]:0;
            $offerday=(isset($data["offerday"]))?$data["offerday"]:"2013-1-1 12:00:00";
            $offerdate=new DateTime($offerday);
        }
        $error=0;
        $owner=$db->query("SELECT id,na,line_url FROM t14member WHERE line_id='$lineid';")->fetch();
        $car=$db->query("SELECT owner_id,na FROM t31car WHERE id=$carid;")->fetch();
        $user=$db->query("SELECT t14member.id AS id,t14member.na AS na,access_token,channel_secret,line_id,line_url FROM t14member JOIN mt16lineat ON t14member.lineat_id=mt16lineat.id WHERE t14member.id=$userid")->fetch();
        $userbot = new \LINE\LINEBot(new \LINE\LINEBot\HTTPClient\CurlHTTPClient($user['access_token']), ['channelSecret'=>$user['channel_secret']]);
        if($res=="agree"){
            $db->beginTransaction();
            $ps=$db->prepare("UPDATE t52reserv SET agree_day=? WHERE id=".$user['id']." AND car_id=$carid AND offer_day='$offerday' AND agree_day is null AND deny_day is null;");
            $error+=($ps->execute(array($todate->format('Y-m-d H:i:s'))))?0:1;
            $already=($ps->rowCount())?0:1;
            $ps=$db->prepare("UPDATE t52reserv SET deny_day=? WHERE id=".$user['id']." AND car_id!=$carid AND offer_day='$offerday' AND agree_day is null AND deny_day is null;");
            $error+=($ps->execute(array($todate->format('Y-m-d H:i:s'))))?0:1;
            $ps=$db->prepare("UPDATE t14member SET res_min=res_min+?,agree=agree+1 WHERE id=".$owner['id']);
            $interval=$offerdate->diff($todate);
            $min=$interval->format('%R%I')+$interval->format('%R%H')*60+$interval->format('%R%D')*24*60;
            $error+=($ps->execute(array($min))&&$ps->rowCount()==1)?0:1;
            if($error){
                $ownerbot->replyText($event->getReplyToken(),$user['na']."さんの予約はデータベースエラーにより承認されていません。");
                $db->rollBack();
            }else if($already){
                $ownerbot->replyText($event->getReplyToken(),$user['na']."さんの予約はすでに処理されています。");
                $db->rollBack();
            }else{
                $actions=array(
                new LINE\LINEBot\TemplateActionBuilder\UriTemplateActionBuilder ($owner['na']."さんを友達に追加","http://line.me/ti/p/".$owner["line_url"]),
                new LINE\LINEBot\TemplateActionBuilder\UriTemplateActionBuilder ($owner['na']."さんについて","https://".$_SERVER["HTTP_HOST"]."/you.php?id=".$owner['id']),
                new LINE\LINEBot\TemplateActionBuilder\UriTemplateActionBuilder ("クルマを確認する","https://".$_SERVER["HTTP_HOST"]."/car.php?carid=$carid"),
                );
                $builder=new \LINE\LINEBot\MessageBuilder\TemplateMessageBuilder($car['na']."の予約が承認されました。",
                new \LINE\LINEBot\MessageBuilder\TemplateBuilder\ButtonTemplateBuilder($owner['na']."さんにこちらから連絡しましょう。","使用日時\n".$data['reserv']."使用料金 ".number_format($data['price'])."円",null,$actions));
                if($userbot->pushMessage($user['line_id'], $builder)->isSucceeded()){
                    $db->commit();
                    $actions=array(
                    new LINE\LINEBot\TemplateActionBuilder\UriTemplateActionBuilder ($user['na']."さんを友達に追加","http://line.me/ti/p/".$user["line_url"]),
                    );
                    $builder=new \LINE\LINEBot\MessageBuilder\TemplateMessageBuilder($user['na']."さんに予約承認メッセージを送信しました。",
                    new \LINE\LINEBot\MessageBuilder\TemplateBuilder\ButtonTemplateBuilder($user['na']."さんを友達に追加して連絡を待ちましょう。","いつまでも連絡がないときはこちらから連絡してみてください。",null,$actions));
                    $ownerbot->replyMessage($event->getReplyToken(),$builder);
                }else{
                    $ownerbot->replyText($event->getReplyToken(),$user['na']."さんへのメッセージ送信に失敗しました。予約は承認されていません。");
                    $db->rollBack();
                }
            }
        }else if($res=="deny"){
            $db->beginTransaction();
            $ps=$db->prepare("UPDATE t52reserv SET deny_day=? WHERE id=".$user['id']." AND car_id=$carid AND offer_day='$offerday' AND agree_day is null AND deny_day is null;");
            $error+=($ps->execute(array($todate->format('Y-m-d H:i:s'))));
            $already=($ps->rowCount())?0:1;
            //$ps=$db->prepare("UPDATE t14member SET res_min=res_min+?,deny=deny+1 WHERE id=".$owner['id']);
            //$interval=$offerdate->diff($todate);
            //$min=$interval->format('%R%I')+$interval->format('%R%H')*60+$interval->format('%R%D')*24*60;
            $error+=($ps->execute(array($min))&&$ps->rowCount()==1)?0:1;
            if($error){
                $ownerbot->replyText($event->getReplyToken(),$user['na']."さんの予約をデーターベースエラーによりキャンセルできませんでした。id=".$id.":carid=".$carid.":offerday=".$offerday);
                $db->rollBack();
            }else if($already){
                $ownerbot->replyText($event->getReplyToken(),$user['na']."さんの予約はすでに処理されています。");
                $db->rollBack();
            }else{
                $builder=new \LINE\LINEBot\MessageBuilder\TextMessageBuilder($car['na']."への予約リクエストは".$owner['na']."さんにキャンセルされました。");
                if($userbot->pushMessage($user['line_id'], $builder)->isSucceeded()){
                    $ownerbot->replyText($event->getReplyToken(),$user['na']."さんに予約キャンセルのメッセージを送信しました。");
                }else{
                    $ownerbot->replyText($event->getReplyToken(),"予約はキャンセルしましたが、".$user['na']."さんへのメッセージ送信に失敗しました。");
                }
                $db->commit();
            }
        }else{
            $ownerbot->replyText($event->getReplyToken(),"無効なコマンドです。");
        }
    }
}