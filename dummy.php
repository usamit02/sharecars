<?php
session_start();
require_once($_SERVER['DOCUMENT_ROOT']."/include/db.php");
if (isset($_POST['dummy'])) {
    if ($_POST['dummy']=='latlng') {
        $regday=date('Y-m-d H:i:s');
        $endday=new DateTime();
        $plusday=mt_rand(0, 360);
        $end_day=$endday->modify("+$plusday days")->format('Y-m-d H:i:s');
        $db->query("DELETE FROM t31car WHERE id > 50;");
        mt_srand(random_int(-2**31, 2**31-1));
        for ($i=50; $i<3000; $i++) {
            $lat=125.0842679 + mt_rand(1, 32000)/1234;
            $lng=28.1738949 + mt_rand(1, 32000)/1856;
            $db->query("INSERT INTO t31car(id,na,latlng,reg_day,end_day) VALUES ($i,'カローラ',GeomFromText('POINT($lat $lng)'),'$regday','$end_day')");
        }
    } else if ($_POST['dummy']=='sche') {
        $db->query("DELETE FROM t51schedule WHERE car_id > 50;");
        for ($i=50; $i<3000; $i++) {
            for ($j=1; $j<10; $j++) {
                $startday=new DateTime();
                $endday=new DateTime();
                $plusday=mt_rand(0, 60);
                $plushour=mt_rand(0,24);
                $start_day=$startday->modify("+$plusday days + $plushour hours")->format('Y-m-d H:i:s');
                $plushour+=mt_rand(0,80);
                $end_day=$endday->modify("+$plusday days + $plushour hours")->format('Y-m-d H:i:s');
                $db->query("INSERT INTO t51schedule(car_id,start_day,end_day) VALUES ($i,'$start_day','$end_day')");
            }
        }
    } else if ($_POST['dummy']=='reserv') {
        $db->query("DELETE FROM t52reserv WHERE car_id > 50;");
        for ($i=50; $i<3000; $i++) {
            for ($j=1; $j<3; $j++) {
                $startday=new DateTime();
                $endday=new DateTime();
                $plusday=mt_rand(0, 30);
                $plushour=mt_rand(0,24);
                $start_day=$startday->modify("+$plusday days + $plushour hours")->format('Y-m-d H:i:s');
                $plushour+=mt_rand(0,80);
                $end_day=$endday->modify("+$plusday days + $plushour hours")->format('Y-m-d H:i:s');
                $db->query("INSERT INTO t52reserv(id,car_id,offer_day,agree_day,start_day,end_day) VALUES ($i,$i,'$start_day','$start_day','$start_day','$end_day')");
            }
        }
    }
}

?>
  <HTML>

  <HEAD>
    <META HTTP-EQUIV='Content-Type' CONTENT='text/html;charset=UTF-8'>
    <title>シェアカーズ</title>

  </HEAD>

  <BODY>
    <form ACTION="<?php echo $_SERVER['SCRIPT_NAME'];?>" METHOD="post">
      <input name='dummy' type='text'></input>
      <button type='submit'>ダミーデータ作成</button>
    </form>
  </body>

  </html>