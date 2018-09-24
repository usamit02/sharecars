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
function nochange($db, $id, $carid, $sign)
{
    $rs=$db->query("SELECT no FROM t31car WHERE id=$carid;");
    $r=$rs->fetch();
    $no=$r['no'];
    $a=$no+$sign;
    $ps=$db->prepare("UPDATE t31car SET no=$no WHERE owner_id=$id AND no=".$a);
    $ps->execute();
    if ($ps->rowCount()) {
        $db->query("UPDATE t31car SET no=no$sign WHERE owner_id=$id AND id=$carid;");
    }
}

?>
  <HTML>

  <HEAD>
    <META HTTP-EQUIV='Content-Type' CONTENT='text/html;charset=UTF-8'>
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
        <button onclick="Close()">閉じる</button>
      </div>
    </div>
    <?php
$sort=array('start_day','price','carna','na','offer_day','agree_day','deny_day');
$sortna=array('使用期間','使用料','クルマ','オーナー','申込日','同意','拒否');
$aline=array('left','right','center','center','left','left','left');
$select=implode(",", $sort);
$rs=$db->query("SELECT car_id,MIN(start_day) AS min_start_day,SUM(price) AS fee,offer_day FROM q52reserv WHERE id=$id GROUP BY offer_day,car_id ORDER BY min_start_day;");
$r=$rs->fetchAll(PDO::FETCH_ASSOC);
if (count($r)) {
    echo '<h2>予約申込状況</h2><div><table id="reserv">';
    echo '<tr><th>クルマ詳細</th>';
    foreach ($sortna as $value) {
        echo "<th><div>$value</div></th>";
    }
    echo "<th>操作</th></tr>";
    foreach($r as $key=>$val){
        $carid=$r['car_id'];
        $offerday=$r['offer_day'];
        $rs = $db->query("SELECT car_id,end_day,$select FROM q52reserv WHERE id=$id AND car_id=$carid AND offer_day='$offerday' ORDER BY start_day;");
        $rr=$rs->fetchAll(PDO::FETCH_ASSOC);
        foreach($rr as $i=>$v){
            
        }
        
        
        
    }
    if (count($r)) {
        echo '<h2>予約申込状況</h2><div><table id="reserv">';
        echo '<tr><th>クルマ詳細</th>';
        foreach ($sortna as $value) {
            echo "<th><div>$value</div></th>";
        }
        echo "<th>操作</th></tr>";
        $i=0;
        $prieod="";
        $price=0;
        $oldofferday=$r[0]['offer_day'];
        $oldcarid=$r[0]['car_id'];
        foreach($r as $i=>$v){
            $a=$v['offer_day'];
            $b=($v['offer_day']!=$oldofferday);
            if($v['offer_day']!=$oldofferday||$v['car_id']!=$oldcarid){
                $carid=$r[$i-1]['car_id'];
                echo "<tr><td><a href='car.php?carid=$carid'><img class='indeximg' src='img/$carid/s-0.jpg'></a></td>";
                foreach ($sort as $key => $na) {
                    $val=($na=='start_day')?$prieod:$r[$i-1][$na];
                    $val=($na=='price')?$price:$val;
                    $val=($aline[$key]=='right')?"￥".number_format($val):$val;
                    echo "<td align='$aline[$key]'>$val</td>";
                }
                $calendar="<button class='plan' id='p$carid'>予定</button>";
                echo "<td>$calendar</td></tr >";
                $price=0;
                $prieod="";
            }
            $prieod.='<div>'.$v['start_day'].'～'.$v['end_day'].'</div>';
            $price+=$v['price'];
            $oldofferday=$v['offer_day'];
            $oldcarid=$v['car_id'];
        }
        echo '</table></div>';
    } else {
        echo "予約していません。";
    }
    $sortna[5]='ユーザー';
    $rs = $db->query("SELECT car_id,$select FROM q52reserv_owner WHERE id=$id ORDER BY start_day;");
    $reservs=$rs->fetchAll(PDO::FETCH_ASSOC);
    if (count($reservs)) {
        echo '<h2>あなたのクルマの使用希望</h2><div><table id="reserv">';
        echo '<tr><th>クルマ詳細</th>';
        foreach ($sortna as $value) {
            echo "<th><div>$value</div></th>";
        }
        echo "<th>操作</th></tr>";
        foreach ($reservs as $r) {
            $carid=$r['car_id'];
            echo "<tr><td><a href='car.php?carid=$carid'><img class='indeximg' src='img/$carid/s-0.jpg'></a></td>";
            foreach ($sort as $key => $value) {
                $val=($aline[$key]=='right')?"￥".number_format($r[$value]):$r[$value];
                $val=($key>5&&!isset($r['agree_day'])&&!isset($r['deny_day']))?"<button onclick='resReserv($carid,".($key-6).")'>".$sortna[$key]."</button>":$val;
                echo "<td align='$aline[$key]'>$val</td>";
            }
            $calendar="<button class='plan' id='p$carid'>予定</button>";
            echo "<td>$calendar</td></tr>";
        }
        echo '</table></div>';
    } else {
        echo "予約していません。";
    }
    $sort=array(1=>'na','maker','year','price','price_holiday','price_ext','price_short');
    $sortna=array(1=>'車名','メーカー','年','平日','休日','延長','短時');
    $aline=array(1=>'left','left','center','right','right','right','right');
    $select=implode(",", $sort);
    $rs = $db->query("SELECT id,$select FROM t31car WHERE owner_id=$id ORDER BY no,id;");
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
            echo "<tr><td><a href='car.php?carid=$carid'><img class='indeximg' src='img/$carid/s-0.jpg'></a></td>";
            foreach ($sort as $key => $value) {
                $val=($aline[$key]=='right')?"￥".number_format($r[$value]):$r[$value];
                echo "<td align='$aline[$key]'>$val</td>";
            }
            $calendar="<button class='plan' id='p$carid'>予定</button>";
            $stop="<button onclick='(location.href=".'"'."my.php?stop=$carid".'"'.")'>変更</button>";
            $up="<a href='my.php?carup=$carid'>▲</a>";
            $down="<a href='my.php?cardown=$carid'>▼</a>";
            echo "<td>$calendar $stop $up $down</td></tr>";
        }
        echo '</table></div>';
    } else {
        echo "クルマはまだ登録されていません。";
    }
    echo "<button onclick='(location.href=".'"'."carreg.php".'"'.")'>クルマ新規登録</button>";
    $rs=$db->query("SELECT id,na,typ,name,unit FROM mt14memberreg WHERE gp='insurance' ORDER BY id;");
    $rr=$rs->fetchAll(PDO::FETCH_ASSOC);
    $select="";
    foreach ($rr as $r) {
        $select.=$r['na'].",";
    }
    $select=substr($select, 0, strlen($select)-1);
    $rs=$db->query("SELECT $select FROM t14member WHERE id=$id;");
    $rrr=$rs->fetchAll(PDO::FETCH_ASSOC);
    if (count($rrr)) {
        echo "</div><div><h2>利用できる自動車保険</h2>";
        $val=$rrr[0];
        foreach ($rr as $item) {
            switch ($item['typ']) {
                case "checkbox":
                    $txt=($val[$item['na']])?"<div>".$item['name']."</div>":false;
                    break;
                case "number":
                    $txt=(isset($val[$item['na']]))?$item['name'].":".$val[$item['na']].$item['unit']:false;
                    break;
        }
        if ($txt) {
            echo $txt;
        }
    }
    echo "<button onclick='(location.href=".'"'."carreg.php?carid=$carid".'"'.")'>変更登録</button>";
} else {
    echo "まだメンバー登録されていません。<a href='memberreg.php' class='btn'>メンバー登録</a>";
}
?>

      <script type="text/javascript" src="js/jquery-3.1.1.min.js"></script>
      <script type="text/javascript" src="js/pop.js"></script>
      <script type="text/javascript" src="js/calendar.js"></script>
      <script type="text/javascript" src="js/schedule.js"></script>
      <script type="text/javascript">
        var id = <?php if(isset($id)){echo $id;}?>;
        var endDay = new Date(toYear, toMonth + 2, 0);
        var period = Math.ceil((endDay - toDay) / 86400000);
        $(".plan").on("click", function() {
          carid = $(this).attr('id').replace("p", "");
          setSchedule(carid, function() {
            setBooking(carid, function() {
              scheCalendar();
            });
          });
          var month = (window.innerWidth > 1000) ? 3 : 1;
          makeCalendar(month, 1);
          addPlan(0);
          $.pop();
          scheInit();
        });

        function Close() {
          popClose();
          saveClose();
        }
      </script>

  </BODY>

  </HTML>