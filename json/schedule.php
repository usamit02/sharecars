<?php
require_once $_SERVER['DOCUMENT_ROOT'].'/include/db.php';
$car=json_decode($_POST['carids']);
if (is_array($car)) {
    $carids=$car;
} else {
    $carids[0]=$car;
}
if (isset($_POST['scheA'])) {
    $error=0;
    foreach ($carids as $i => $carid) {
        $db->query("DELETE FROM t51schedule WHERE car_id=$carid;");
        foreach ($_POST['scheA'] as $key => $startday) {
            if(isset($_POST['scheZ'])){//scheAのみセットは削除するだけ
                $ps=$db->prepare("INSERT INTO t51schedule(car_id,start_day,end_day) VALUES (?,?,?);");
                if (!$ps->execute(array($carid,$startday,$_POST['scheZ'][$key]))) {
                    $error++;
                }
            }
        }
    }
    $json['msg']=($error)?$error+"件のスケジュールを保存できませんでした。":"ok";
} else {
    // if (is_array($carids)) {
    $where="";
    foreach ($carids as $i => $carid) {
        $where.="car_id=$carid OR ";
    }
    $where=substr($where, 0, strlen($where)-4);
    //} else {
    //   $where="car_id=$carids";
    //  }
    $rs=$db->query("SELECT start_day,end_day FROM t51schedule WHERE $where ORDER BY start_day;");
    $json=$rs->fetchAll(PDO::FETCH_ASSOC);
}
header('Content-type: application/json');
echo json_encode($json);