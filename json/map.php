<?php
session_start();
require_once $_SERVER['DOCUMENT_ROOT'].'/include/db.php';
$id=$_SESSION['id'];
$nlat=$_POST['nlat'];
$slat=$_POST['slat'];
$nlng=$_POST['nlng'];
$slng=$_POST['slng'];
$clat=($nlat+$slat)/2;
$clng=($nlng+$slng)/2;
$len="ST_LENGTH(ST_GEOMETRYFROMTEXT(CONCAT('LineString($clng $clat,', ST_X(`latlng`),' ',ST_Y(`latlng`),')'))) AS len";
$where="WHERE MBRContains(ST_GeomFromText('LineString($nlng $nlat, $slng $slat)'), latlng)";
require_once $_SERVER['DOCUMENT_ROOT'].'/include/where.php';
$sql="SELECT $len,ST_Y(`latlng`) AS lat,ST_X(`latlng`) AS lng,id,na,price,holiday_price,ext_price,short_price,short_hour,long_price,long_date,reg_day,ok_day,re_day FROM t31car $where ORDER BY len LIMIT 0,30;";
header('Content-type: application/json');
echo json_encode($db->query($sql)->fetchAll(PDO::FETCH_ASSOC));