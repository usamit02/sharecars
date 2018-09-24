<?php
session_start();
if(isset($_SESSION['na'])){
    require_once $_SERVER['DOCUMENT_ROOT'].'/include/db.php';
    $id=$_SESSION['id'];
    $na=$_SESSION['na'];
    $car=json_decode($_POST['carids']);
    if (is_array($car)) {
        $carids=$car;
    } else {
        $carids[0]=$car;
    }
    if (isset($_POST['reservA'])) {
        $today=new DateTime();
        $f=true;
        foreach ($_POST['reservA'] as $key => $val) {
            $aday=new DateTime($val);
            if ($aday->diff($today)->format('%a') >31) {
                $f=false;
                $json['msg']="予約日が３１日以上後です。端末の時計が正常でない可能性があります。";
            }
        }
        if ($f) {
            $prices=json_decode($_POST['prices']);
            $week = array('日', '月', '火', '水', '木', '金', '土');
            $ownerName="";
            $json['msg']="";
            foreach ($carids as $i => $carid) {
                $reserv="";
                $errorSql=0;
                $errorLine=0;
                $errorPoint=0;
                $price=0;
                $db->beginTransaction();
                foreach ($_POST['reservA'] as $key => $startday) {
                    if (isset($_POST['reservZ'])) {
                        $ps=$db->prepare("INSERT INTO t52reserv(id,car_id,offer_day,price,start_day,end_day) VALUES (?,?,?,?,?,?);");
                        $errorSql+=($ps->execute(array($id,$carid,$today->format('Y-m-d H:i:s'),$prices[$i][$key],$startday,$_POST['reservZ'][$key]))&&$ps->rowCount()==1)?0:1;
                        $Aday=new DateTime($startday);
                        $Zday=new DateTime($_POST['reservZ'][$key]);
                        $reserv.=$Aday->format('n月d日(').$week[$Aday->format('w')].$Aday->format(')G時～');
                        $reserv.=($Aday->diff($Zday)->format('%R%a')>=1)?$Zday->format('n月d日(').$week[$Zday->format('w')].")":"";
                        $reserv.=($Zday->format('G')+1)."時\n";
                        $price+=$prices[$i][$key];
                    } else {//reservAのみセットは削除
                        $rs=$db->query("SELECT start_day,end_day,offer_day,agree_day FROM t52reserv WHERE id=$id AND car_id=$carid AND start_day<='$startday' AND end_day>='$startday';");
                        if($r=$rs->fetch()){
                            $Xday=new DateTime($startday);
                            $X=new DateTime($startday);
                            $Xday->setTime(0,0);
                            $Aday=new DateTime($r['start_day']);
                            $Zday=new DateTime($r['end_day']);
                            $start=$r['start_day'];
                            if($Aday==$X){
                                if($Zday->diff($Xday)->format('%a')<1){//startdayとenddayが同日
                                    $ps=$db->prepare("DELETE FROM t52reserv WHERE id=$id AND car_id=$carid AND start_day='$start';");
                                    $errorSql+=($ps->execute())?0:1;
                                }else{
                                    $ps=$db->prepare("UPDATE t52reserv SET start_day=? WHERE id=$id AND car_id=$carid AND start_day='$start';");
                                    $errorSql+=($ps->execute(array($Xday->modify('+1 day')->format('Y-m-d H:i:s'))))?0:1;
                                }
                            }else{
                                $ps=$db->prepare("UPDATE t52reserv SET end_day=? WHERE id=$id AND car_id=$carid AND start_day='$start';");
                                $errorSql+=($ps->execute(array($Xday->modify('-1 second')->format('Y-m-d H:i:s'))))?0:1;
                                $Xday->modify('+1 second');
                                if($Zday->diff($Xday)->format('%a')>0){
                                    $offerday=new DateTime($r['offer_day']);
                                    $agreeday=new DateTime($r['agree_day']);
                                    $agree=(isset($r['agree_day']))?$agreeday->format('Y-m-d H:i:s'):null;
                                    $d=$Xday->modify('+1 day')->format('Y-m-d  H:i:s');
                                    $ps=$db->prepare("INSERT INTO t52reserv(id,car_id,offer_day,agree_day,start_day,end_day) VALUES (?,?,?,?,?,?);");
                                    $errorSql+=($ps->execute(array($id,$carid,$offerday->format('Y-m-d H:i:s'),$agree,$d,$Zday->format('Y-m-d H:i:s'))))?0:1;
                                }
                            }
                        }else{
                            $errorSql++;
                        }
                    }
                }
                if($db->exec("UPDATE t14member SET req_p=req_p-1 WHERE id=$id;")!=1){
                    if($db->exec("UPDATE t14member SET share_p=share_p-30 WHERE id=$id;")!=1){
                        $errorPoint++;
                    }
                }
                if($errorSql||$errorPoint){
                    $db->rollBack();
                }else{
                    require_once $_SERVER['DOCUMENT_ROOT'].'/LINE/LINEBot.php';
                    require_once $_SERVER['DOCUMENT_ROOT'].'/LINE/LINEBot/HTTPClient.php';
                    require_once $_SERVER['DOCUMENT_ROOT'].'/LINE/LINEBot/HTTPClient/Curl.php';
                    require_once $_SERVER['DOCUMENT_ROOT'].'/LINE/LINEBot/HTTPClient/CurlHTTPClient.php';
                    require_once $_SERVER['DOCUMENT_ROOT'].'/LINE/LINEBot/MessageBuilder.php';
                    require_once $_SERVER['DOCUMENT_ROOT'].'/LINE/LINEBot/MessageBuilder/TemplateBuilder.php';
                    require_once $_SERVER['DOCUMENT_ROOT'].'/LINE/LINEBot/TemplateActionBuilder.php';
                    require_once $_SERVER['DOCUMENT_ROOT'].'/LINE/LINEBot/MessageBuilder/TemplateMessageBuilder.php';
                    require_once $_SERVER['DOCUMENT_ROOT'].'/LINE/LINEBot/TemplateActionBuilder/PostbackTemplateActionBuilder.php';
                    require_once $_SERVER['DOCUMENT_ROOT'].'/LINE/LINEBot/TemplateActionBuilder/UriTemplateActionBuilder.php';
                    require_once $_SERVER['DOCUMENT_ROOT'].'/LINE/LINEBot/MessageBuilder/TemplateBuilder/ButtonTemplateBuilder.php';
                    require_once $_SERVER['DOCUMENT_ROOT'].'/LINE/LINEBot/Constant/Meta.php';
                    require_once $_SERVER['DOCUMENT_ROOT'].'/LINE/LINEBot/Constant/MessageType.php';
                    require_once $_SERVER['DOCUMENT_ROOT'].'/LINE/LINEBot/Constant/ActionType.php';
                    require_once $_SERVER['DOCUMENT_ROOT'].'/LINE/LINEBot/Constant/TemplateType.php';
                    require_once $_SERVER['DOCUMENT_ROOT'].'/LINE/LINEBot/Exception/CurlExecutionException.php';
                    require_once $_SERVER['DOCUMENT_ROOT'].'/LINE/LINEBot/Response.php';
                    $car=$db->query("SELECT owner_id,na FROM t31car WHERE id=".$carid)->fetch();
                    $owner=$db->query("SELECT t14member.na AS na,line_id,access_token,channel_secret FROM t14member JOIN mt16lineat ON t14member.lineat_id=mt16lineat.id WHERE t14member.id=".$car['owner_id'])->fetch();
                    $bot = new \LINE\LINEBot( new \LINE\LINEBot\HTTPClient\CurlHTTPClient($owner['access_token']), ['channelSecret' => $owner['channel_secret']]);
                    $actionArray=array(
                    new LINE\LINEBot\TemplateActionBuilder\PostbackTemplateActionBuilder("承諾","res=agree&id=$id&carid=$carid&reserv=$reserv&price=$price&offerday=".$today->format('Y-m-d H:i:s')),
                    new LINE\LINEBot\TemplateActionBuilder\UriTemplateActionBuilder ($na."さんについて","https://".$_SERVER["HTTP_HOST"]."/you.php?id=$id"),
                    new LINE\LINEBot\TemplateActionBuilder\UriTemplateActionBuilder ("クルマを確認する","https://".$_SERVER["HTTP_HOST"]."/car.php?carid=$carid"),
                    new LINE\LINEBot\TemplateActionBuilder\PostbackTemplateActionBuilder("拒否","res=deny&id=$id&carid=$carid&offerday=".$today->format('Y-m-d H:i:s')),
                    );
                    $builder = new \LINE\LINEBot\MessageBuilder\TemplateMessageBuilder($car['na']."の予約を申し込まれました",
                    new \LINE\LINEBot\MessageBuilder\TemplateBuilder\ButtonTemplateBuilder($na."さんが".$car['na']."を使用したい。", "希望日時\n".$reserv."使用料金 ".number_format($price)."円", null, $actionArray)
                    );
                    if($bot->pushMessage($owner['line_id'], $builder)->isSucceeded()){
                        $db->commit();
                        $ownerName.=$owner['na']."さん、";
                    }else{
                        $db->rollBack();
                        $errorLine++;
                    }
                }
            }
        }
        $ope=(isset($_POST['reservZ']))?'申込':'取消';
        if ($errorSql) {
            $json['msg'].="データーベースエラーにより".$errorSql."件予約".$ope."できませんでした。\n";
        }else if($errorLine){
            $json['msg'].="LINEのメッセージ送信エラーにより".$errorLine."人に対し予約申込できませんでした。\n";
        }else if($errorPoint){
            $json['msg'].="今月のリクエスト可能数を超過し、シェアポイントも不足しているため、".$errorPoint."人に対し予約申込できませんでした。\n";
        }
        $json['msg'].=(strlen($ownerName))?mb_substr($ownerName,0,mb_strlen($ownerName)-1)."へ予約".$ope."しました。":"";
    } else {
        $where="(";
        foreach ($carids as $i => $carid) {
            $where.="car_id=$carid OR ";
        }
        $where=substr($where, 0, strlen($where)-4).")";
        $rs=$db->query("SELECT id,car_id,start_day,end_day FROM t52reserv WHERE $where AND deny_day is null ORDER BY start_day;");
        $json=$rs->fetchAll(PDO::FETCH_ASSOC);
    }
}else{
    $json['msg']="ログインしてください。";
}
header('Content-type: application/json');
echo json_encode($json);