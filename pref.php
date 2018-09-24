<?php
session_start();
require_once($_SERVER['DOCUMENT_ROOT']."/include/db.php");
include_once($_SERVER['DOCUMENT_ROOT'].'/include/head.php');
?>
  <HTML>

  <HEAD>
    <meta name="viewport" content="width=640">
    <META HTTP-EQUIV='Content-Type' CONTENT='text/html;charset=UTF-8'>
    <title>シェアカーズ</title>
    <link rel="stylesheet" href="css/pref.css">
  </HEAD>

  <BODY>
    <div class="pref">
      <?php
$where="";
require_once($_SERVER['DOCUMENT_ROOT']."/include/where.php");
$where=(strlen($where))?"WHERE ".substr($where,5):"";
$sql="SELECT COUNT(id) AS car,city_cd DIV 1000 AS cd FROM t31car $where GROUP BY city_cd DIV 1000 ORDER BY cd;";
$cars = $db->query($sql)->fetchAll(PDO::FETCH_ASSOC);
$rs = $db->query("SELECT t41pref.cd AS cd,t41pref.na AS na FROM t41pref ORDER BY cd");
while ($r=$rs->fetch()) {
    $f=true;
    foreach($cars as $i=>$v){
        if ($v['cd']==$r['cd']) {
            echo '<a class="prefbotton" href="./city.php?pref='.$r['cd'].'">'.$r['na'].'<br>('.$v['car'].')</a>';
            $f=false;
            break;
    }
}
if($f){echo '<div class="prefpanel">'.$r['na'].'</div>';}
}
echo "</div>";
include_once($_SERVER['DOCUMENT_ROOT'].'/include/foot.php');
?>
  </BODY>

  </HTML>