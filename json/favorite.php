<?php
require_once $_SERVER['DOCUMENT_ROOT'].'/include/db.php';
$id=$_GET['id'];
$carid=$_GET['carid'];
$rs=$db->query("SELECT id FROM t22favorite WHERE id=$id AND car_id=$carid;");
if($rs->fetch()) {
    $json[]= "すでにお気に入りです。";
}else{
    $rs=$db->query("SELECT max(no) AS maxno FROM t22favorite WHERE id=$id");
    $r=$rs->fetch();
    $no=$r['maxno']+1;
    $ps=$db->prepare("INSERT INTO t22favorite(id,no,car_id,reg_day) VALUES (?,?,?,?)");
    if ($ps->execute(array($id,$no,$carid,date('Y-m-d H:i:s')))) {
        $json[]="お気に入りに追加しました。";
    }else{
        $json[]="データーベースエラーによりお気に入りに追加できませんでした。";
    }
}
header('Content-type: application/json');
echo json_encode($json);
?>