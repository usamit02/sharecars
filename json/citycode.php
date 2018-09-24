<?php
require_once $_SERVER['DOCUMENT_ROOT'].'/include/db.php';
$postcode=intval(substr($_GET['code'],0,3).substr($_GET['code'],4,4));
$sql="SELECT city_cd FROM t43post WHERE cd=$postcode";
header('Content-type: application/json');
echo json_encode($db->query($sql)->fetch());