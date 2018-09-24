<?php
session_start();
require_once($_SERVER['DOCUMENT_ROOT']."/include/db.php");
include_once($_SERVER['DOCUMENT_ROOT'].'/include/head.php');
if (isset($_GET['carup'])) {
    $carid=htmlspecialchars($_GET['carup'], ENT_QUOTES);
    nochange($db, $id, $carid, "-1");
}
if (isset($_GET['cardown'])) {
    $carid=htmlspecialchars($_GET['cardown'], ENT_QUOTES);
    nochange($db, $id, $carid, "+1");
}
$already=(isset($_GET['already']))?true:false;
function nochange($db, $id, $carid, $sign)
{
    $r=$db->query("SELECT no FROM t31car WHERE id=$carid;")->fetch();
    $no=$r['no'];
    $a=$no+$sign;
    $ps=$db->prepare("UPDATE t31car SET no=$no WHERE owner_id=$id AND no=".$a);
    $ps->execute();
    if ($ps->rowCount()) {
        $db->query("UPDATE t31car SET no=no$sign WHERE owner_id=$id AND id=$carid;");
    }
}
if(isset($_GET['cardelete'])){//1=agerr,0=deny
    $carid=htmlspecialchars($_GET['cardelete'], ENT_QUOTES);
    $ownerid=$db->query("SELECT owner_id FROM t31car WHERE id=$carid")->fetchcolumn();
    if($ownerid==$id){
        if($db->query("SELECT id FROM t52reserv WHERE car_id=$carid AND agree_day is not null AND end_day > Now();")->fetch()){
            echo"まだ使用終了日時に達していない予約があるため削除できません";
            die;
        }
        
        $db->beginTransaction();
        try{
            $db->exec("DELETE FROM t31car WHERE id=$carid;");
            $db->exec("DELETE FROM t51schedule WHERE car_id=$carid;");
            $db->exec("DELETE FROM t52reserv WHERE car_id=$carid;");
            $db->exec("DELETE FROM t22favorite WHERE car_id=$carid;");
            foreach(grob("/img/$carid/"."*.*") as $file){
                unlink($file);
            }
            $db->commit();
        }
        catch(Exception $e){
            $db->rollBack();
            echo"データベースエラーにより削除できませんでした。";
            die;
        }
    }else{
        echo"オーナー以外削除できません。";
        die;
    }
}

?>
  <HTML>

  <HEAD>
    <META HTTP-EQUIV='Content-Type' CONTENT='text/html;charset=UTF-8'>
    <meta name="viewport" content="width=640">
    <title>マイページ</title>
    <link rel="stylesheet" href="css/my.css">
    <link rel="stylesheet" href="css/pop.css">
    <link rel="stylesheet" href="css/calendar.css">
  </HEAD>

  <BODY>

    <div id="pop">
      <div id="pop_title">スケジュール</div>
      <div id="calendar">&nbsp;</div>
      <div id="plans">&nbsp;</div>
      <div id="timetable">&nbsp;</div>
      <div id='pop_close'>
        <button onclick="saveClose()">設定</button>
        <button onclick="popClose()">閉じる</button>
      </div>
    </div>
    <?php
function reservSelect($db,$id,$select,$from,$already){
    $sign=($already)? "<":">";
    $day=new DateTime();
    $today=$day->format('Y-m-d H:i:s');
    $rs=$db->query("SELECT id,eval_id,car_id,img_url,end_day,eval_id,$select FROM $from WHERE id=$id AND end_day $sign '$today' ORDER BY offer_day,car_id,start_day DESC;");
    $r=$rs->fetchAll(PDO::FETCH_ASSOC);
    $cr=count($r);
    if ($cr) {
        $period="";
        $price=0;
        $rr=[];
        for($i=0;$i<$cr;$i++){
            $period='<div>'.$r[$i]['start_day'].'～'.$r[$i]['end_day'].'</div>'.$period;
            $price+=$r[$i]['price'];
            if($i==$cr-1||$r[$i+1]['offer_day']!=$r[$i]['offer_day']||$r[$i+1]['car_id']!=$r[$i]['car_id']){
                array_push($rr,$r[$i]);
                $rr[count($rr)-1]['period']=$period;
                $rr[count($rr)-1]['price']=$price;
                $period="";
                $price=0;
            }
        }
        foreach($rr as $i=>$v){
            $startday[$i]=$v['start_day'];
        }
        array_multisort($startday,SORT_ASC,$rr);
        return $rr;
    }else{
        return false;
    }
}
$sort=array('carna','start_day','price','offer_day','agree_day','deny_day','na');
$sortna=array('クルマ','使用期間','使用料','申込日','同意','拒否','オーナー');
$aline=array('center','left','right','left','left','left','center');
if($already){//誰かが終了後ボタンをクリックしたら、使用期間終了１か月経過した予約の削除と自動評価を行う、
    $alreadyButton="<button onclick='(location.href=".'"'."my.php".'"'.")'>終了前</button>";
    $error=0;
    $day=new DateTime();
    $today=$day->format('Y-m-d H:i:s');
    $nextMonthday=$day->modify('+1 months')->format('Y-m-d H:i:s');
    $rs=$db->query("SELECT DISTINCT t52reserv.id AS user_id,owner_id FROM t52reserv JOIN t31car ON t52reserv.car_id=t31car.id WHERE t52reserv.end_day>'$nextMonthday';");
    $db->beginTransaction();
    while($r=$rs->fetch()){
        $q=$db->prepare("INSERT INTO t17eval (set_id,get_id,id,reg_day,owner) VALUES (?,?,?,?,?)");
        $error+=($q->execute(array($r['user_id'],$r['owner_id'],0,$today,1))&&$q->rowCount()==1||$q->errorCode()==23000)?0:1;
        $error+=($q->execute(array($r['owner_id'],$r['user_id'],0,$today,0))&&$q->rowCount()==1||$q->errorCode()==23000)?0:1;
    }
    if(!$error&&$db->exec("DELETE FROM t52reserv WHERE end_day>'$nextMonthday';")){
        $db->commit();
    }else{
        $db->rollBack();
    }
}else{
    $alreadyButton="<button onclick='(location.href=".'"'."my.php?already=1".'"'.")'>終了後</button>";
}
$r=reservSelect($db,$id,implode(",", $sort),"q52reserv",$already);
if($r){
    echo '<div style="display:flex;"><h2>予約申込状況</h2>'.$alreadyButton.'</div><div><table id="reserv">';
    echo '<tr><th>クルマ詳細</th>';
    foreach ($sortna as $value) {
        echo "<th><div>$value</div></th>";
    }
    echo "<th>詳細</th><th>操作</th></tr>";
    foreach($r as $i=>$v){
        $carid=$v['car_id'];
        echo "<tr><td><a href='car.php?carid=$carid'><img class='indeximg' src='img/$carid/s-0.jpg'></a></td>";
        foreach ($sort as $key => $na) {
            $val=($na=='start_day')?$v['period']:$v[$na];
            $val=($aline[$key]=='right')?"￥".number_format($val):$val;
            echo "<td align='$aline[$key]'>$val</td>";
        }
        echo "<td><a href='you.php?id=".$v['eval_id']."'><img class='indeximg' src='".$v['img_url']."'></a></td>";
        $eval=(isset($v['agree_day']))?"<button onclick='(location.href=".'"'."eval.php?owner=1&id=".$v['eval_id'].'"'.")'>評価</button>":"";
        echo "<td>$eval</td></tr >";
    }
    echo '</table></div>';
} else {
    echo "<div>予約リクエストしていません。</div>";
}

$sortna[6]='ユーザー';
$r=reservSelect($db,$id,implode(",", $sort),'q52reserv_owner',$already);
if ($r) {
    echo '<div style="display:flex;"><h2>あなたのクルマの使用希望</h2>'.$alreadyButton.'</div><div><table id="reserv">';
    echo '<tr><th>クルマ詳細</th>';
    foreach ($sortna as $value) {
        echo "<th><div>$value</div></th>";
    }
    echo "<th>詳細</th><th>操作</th></tr>";
    foreach ($r as $i=>$v) {
        $carid=$v['car_id'];
        echo "<tr><td><a href='car.php?carid=$carid'><img class='indeximg' src='img/$carid/s-0.jpg'></a></td>";
        foreach ($sort as $key => $na) {
            $val=($na=='start_day')?$v['period']:$v[$na];
            $val=($aline[$key]=='right')?"￥".number_format($val):$val;
            echo "<td align='$aline[$key]'>$val</td>";
        }
        echo "<td><a href='you.php?id=".$v['eval_id']."'><img class='indeximg' src='".$v['img_url']."'></a></td>";
        $calendar="<button onclick='plan($carid)'>予定</button>";
        $eval=(isset($v['agree_day']))?"<button onclick='(location.href=".'"'."eval.php?owner=0&id=".$v['eval_id'].'"'.")'>評価</button>":"";
        echo "<td>$calendar $eval</td></tr>";
    }
    echo '</table></div>';
} else {
    echo "<div>使用希望がありません。</div>";
}
$sort=array(1=>'na','maker','year','price','holiday_price','ext_price','short_price','long_price');
$sortna=array(1=>'車名','メーカー','年','平日','休日+','延長-','短時-','長期-');
$aline=array(1=>'left','left','center','right','right','right','right','right');
$select=implode(",", $sort);
$rs = $db->query("SELECT id,reg_day,ok_day,re_day,$select FROM t31car WHERE owner_id=$id ORDER BY no,id;");
$cars=$rs->fetchAll(PDO::FETCH_ASSOC);
if (count($cars)) {
    echo '<h2>クルマ一覧</h2><div><table id="car">';
    echo '<tr><th>詳細</th>';
    foreach ($sortna as $value) {
        echo "<th><div>$value</div></th>";
    }
    echo "<th>操作</th></tr>";
    foreach ($cars as $r) {
        $carid=$r['id'];
        $carna=$r['na'];
        $alert=(isset($r['reg_day'])&&isset($r['ok_day'])&&isset($r['re_day'])&&date($r['reg_day'])<=date($r['ok_day'])&&date($r['re_day'])>=date('Y-m-d H:i:s'))?"":"<div class='alert'>確認中</div>";
        echo "<tr><td class='carimg'><a href='car.php?carid=$carid'><img class='indeximg' src='img/$carid/s-0.jpg'></a>$alert</td>";
        foreach ($sort as $key => $value) {
            $val=($aline[$key]=='right')?"￥".number_format($r[$value]):$r[$value];
            echo "<td align='$aline[$key]'>$val</td>";
        }
        $calendar="<button onclick='plan($carid)'>予定</button>";
        $change="<button onclick='(location.href=".'"'."carreg.php?id=$carid".'"'.")'>変更</button>";
        $delete="<button onclick='carDelete($carid,$carna)'>削除</button>";
        $up="<a href='my.php?carup=$carid'>▲</a>";
        $down="<a href='my.php?cardown=$carid'>▼</a>";
        echo "<td>$calendar $change $delete $up $down</td></tr>";
    }
    echo '</table></div>';
} else {
    echo "<div>クルマはまだ登録されていません。";
}
echo"<button onclick='(location.href=".'"'."carreg.php".'"'.")'>クルマ新規登録</button></div>";
echo"<div id='me'>";
$reg=0;
$groups=array('point'=>'現在のポイント','insurance'=>'利用できる自動車保険');
foreach($groups as $gp=>$group){
    $fields=$db->query("SELECT id,na,typ,name,unit FROM mt14memberreg WHERE gp='$gp' ORDER BY id;")->fetchAll(PDO::FETCH_ASSOC);
    $select="";
    foreach ($fields as $field) {
        $select.=$field['na'].",";
    }
    $select=substr($select, 0, strlen($select)-1);
    $me=$db->query("SELECT $select,req_p FROM t14member LEFT JOIN q17eval ON t14member.id=q17eval.id WHERE t14member.id=$id;")->fetchAll(PDO::FETCH_ASSOC);
    if (count($me)) {
        echo "<div><h2>$group</h2>";
        $my=$me[0];
        foreach ($fields as $i) {
            if ($i['typ']== "checkbox") {
                $txt=($my[$i['na']])?"<div>".$i['name']."</div>":false;
            }else if($i['typ']=="number"||$i['typ']=="hidden"){
                $txt=(isset($my[$i['na']]))?"<div>".$i['name'].":".$my[$i['na']].$i['unit']."</div>":false;
            }
            if ($txt) {
                echo $txt;
            }
        }
        if($gp=='point'){
            echo"<div>今月の予約リクエスト残数:".$my['req_p']."回</div>";
        }
        echo"</div>";
    } else {
        $reg++;
    }
}
echo"</div>";
if($reg){
    echo "<div>まだ登録されていません。<button onclick='(location.href=".'"memberreg.php"'.")'>メンバー登録</button></div>";
}else{
    echo "<button onclick='(location.href=".'"'."memberreg.php?id=$id".'"'.")'>変更登録</button>";
}
include_once($_SERVER['DOCUMENT_ROOT'].'/include/foot.php');
?>

      <script type="text/javascript" src="js/jquery-3.1.1.min.js"></script>
      <script type="text/javascript" src="js/pop.js"></script>
      <script type="text/javascript" src="js/calendar.js"></script>
      <script type="text/javascript" src="js/schedule.js"></script>
      <script type="text/javascript">
        var id = <?php if(isset($id)){echo $id;}?>;
        var endDay = new Date(toYear, toMonth + 2, 0);
        var period = Math.ceil((endDay - toDay) / 86400000);
        var carid;

        function plan(car) {
          carid = car;
          var carids = new Array;
          carids[0] = carid;
          setSchedule(carids, function() {
            setBooking(carids, function() {
              scheCalendar();
            });
          });
          var month = (window.innerWidth > 1000) ? 3 : 1;
          makeCalendar(month, 1);
          addPlan(0);
          $.pop();
          scheInit();
        }

        function resReserv(carid, offerday, agree) {
          location.href = 'my.php?carid=' + carid + '&agree=' + agree + '&offerday=' + offerday;
        }

        function carDelete(carid, carna) {
          if (confirm(carna + "を削除します。本当によろしいですか。")) {
            location.href = 'my.php?cardelete=' + carid;
          }
        }
      </script>
  </BODY>

  </HTML>