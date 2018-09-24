<?php
session_start();
if (isset($_SESSION['callback'])) {
    $callback=$_SESSION['callback'];
    unset($_SESSION['callback']);
}
$unsafe = $_SERVER['REQUEST_METHOD']=='POST'||$_SERVER['REQUEST_METHOD']=='PUT'||$_SERVER['REQUEST_METHOD']=='DELETE';
$unset =!(isset($_SESSION['state'])&&isset($_GET['state']));
if ($unsafe||$unset||$_GET['state']!=$_SESSION['state']) {
    echo('不正なアクセスです。');
    die;
}
define('CLIENT_ID', '1534503015');
define('CLIENT_SECRET', '02213f3acf61f795eed48eb7d79962b0');
define('TOKEN_URL', 'https://api.line.me/v2/oauth/accessToken');
define('INFO_URL', 'https://api.line.me/v2/profile');

if (isset($_GET['code'])) {
    $params = array(
    'grant_type' => 'authorization_code',
    'code' => $_GET['code'],
    'client_id' => CLIENT_ID,
    'client_secret' => CLIENT_SECRET,
    'redirect_uri' => 'https://ss1.xrea.com/sharecars.s1003.xrea.com/logon.php',
    //'redirect_uri' => 'https://localhost/logon.php'
    );
    $headers = array(
    'Content-Type: application/x-www-form-urlencoded'
    );
    $options = array('http' => array(
    'method' => 'POST',
    'header' => implode("\r\n", $headers),
    'content' => http_build_query($params, '', '&')
    ));
    $res = file_get_contents(TOKEN_URL, false, stream_context_create($options));
    $token = json_decode($res, true);
    if (isset($token['error'])) {
        echo 'エラー発生';
        die;
    }
    if (isset($token['access_token'])) {
        $context = array(
        'http' => array(
        'method' => 'GET',
        'header' => 'Authorization: Bearer '.$token['access_token']
        )
        );
        $res = file_get_contents(INFO_URL, false, stream_context_create($context));
        $result = json_decode($res, true);
        if (isset($result['userId'])) {
            $lineid=$result['userId'];
            $linena=$result['displayName'];
            $imgurl=$result['pictureUrl'];
            $ip=$_SERVER['REMOTE_ADDR'];
            $host=gethostbyaddr($ip);
            $today=new DateTime();
            require_once($_SERVER['DOCUMENT_ROOT']."/include/db.php");
            $rs=$db->query("SELECT t14member.id AS id,na,ST_X(latlng) AS lng,ST_Y(latlng) AS lat,img_url,req_p,req_day,birth_day,license_day,license_color,license_at,IF(good_p IS NULL,0,good_p) AS good_p,IF(bad_p IS NULL,0,bad_p) AS bad_p,sex,insurance_1day,insurance_driver,insurance_owner,owner_claim,insurance_user,carcompensation,carprice FROM t14member LEFT JOIN q17eval ON t14member.id=q17eval.id WHERE line_id='$lineid'");
            if ($r=$rs->fetch()) {//ログイン履歴あり
                if ($db->query("SELECT auto FROM t15black WHERE id=".$r['id']." OR ip='$ip';")->fetch()) {//ブラックリストチェック
                    echo 'あなたのログインはマスターにより停止されています。お心あたりがなければお問い合わせください。';
                    die;
                }
                $id=$r['id'];
                if ($r['na']!=$linena) {//名前変更チェック
                    $q=$db->prepare("UPDATE t14member SET na=?,img_url=?,reg_day=?,ok_day=?,ip=?,host=? WHERE id=$id;");
                    $q->execute(array($linena,$imgurl,$today->format('Y-m-d H:i:s'),null,$ip,$host));
                    echo '氏名が変更されているので、新しい運転免許証の写真をマスターにLINEで送ってください。';
                    die;//sleep(5);
                }
                if($r['img_url']!=$imgurl){
                    $db->exec("UPDATE t14member SET img_url='$imgurl' WHERE id=$id;");
                }
            } else {//ログイン履歴なし
                $r1=$db->query('SELECT max(id) AS maxid FROM t14member WHERE id<10000000;')->fetch();
                $id=$r1['maxid']+1;
                $q=$db->prepare("INSERT INTO t14member(id,line_id,na,imgurl,req_p,req_day,reg_day,ip,host,insurance_1day,carcompensation,sex) VALUES (?,?,?,?,?,?,?,?,?,?)");
                $q->execute(array($id,$lineid,$linena,$imgurl,4,$today->format('Y-m-d H:i:s'),$ip,$host,1,1,0));
            }
            session_regenerate_id(true);
            $na=$linena;
            $_SESSION['id']=$id;
            $_SESSION['na']=$na;
            if($r['lat']&&$r['lng']){
                $_SESSION['lat']=$r['lat'];
                $_SESSION['lng']=$r['lng'];
            }else{
                unset($_SESSION['lat']);
                unset($_SESSION['lng']);
            }
            if (isset($_COOKIE['tid'])) {//仮IDを本登録
                $tid=$_COOKIE['tid'];
                $db->exec("UPDATE t31car SET owner_id=$id WHERE owner_id=$tid");
                $db->exec("UPDATE t21search SET id=$id WHERE id=$tid");
                $db->exec("UPDATE t21term SET id=$id WHERE id=$tid");
                $db->exec("UPDATE t22favorite SET id=$id WHERE id=$tid");
                if ($db->exec("UPDATE t10comment SET id=$id WHERE id='$tid'")) {
                    mb_send_mail("usamit02@gmail.com", $na.'さんからコメント'.$num.'件投稿されました。');
                }
            }
            $firstday=new DateTime('first day of this month');
            $reqday=new DateTime($r['req_day']);
            if($firstday>$reqday){
                $q=$db->prepare("UPDATE t14member SET req_p=4,req_day=? WHERE id=$id;");
                $q->execute(array($today->format('Y-m-d H:i:s')));
            }
            if(isset($r['birth_day'])&&$r['birth_day']){
                $_SESSION['where']=array_splice($r,12);//$rからid,na,latlng,img_url,req_p,req_dayを除いた7番目以降をwhereにカット&ペースト
            }else{
                unset($_SESSION['where']);
            }
        } else {
            echo('LINE_IDの取得に失敗しました。');
            die;
        }
    } else {
        echo('アクセストークンの取得に失敗しました');
        die;
    }
} else {
    echo('オーソライゼンションコードの取得に失敗しました');
    die;
}
if (isset($callback)) {
    header("Location: https://ss1.xrea.com/sharecars.s1003.xrea.com/$callback");
} else {
    header("Location: https://ss1.xrea.com/sharecars.s1003.xrea.com/index.php");
}