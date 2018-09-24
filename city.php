<?php
session_start();
if (isset($_GET['pref'])) {
    $pref = htmlspecialchars($_GET['pref'], ENT_QUOTES);
} else {
    $pref=13;
}
require_once($_SERVER['DOCUMENT_ROOT']."/include/db.php");
include_once($_SERVER['DOCUMENT_ROOT'].'/include/head.php');
?>
  <HTML>

  <HEAD>
    <META HTTP-EQUIV='Content-Type' CONTENT='text/html;charset=UTF-8'>
    <meta name="viewport" content="width=670">
    <title>シェアカーズ</title>
    <link rel="stylesheet" href="css/city.css">
  </HEAD>

  <BODY>
    <form id="main" action="carindex.php" method="get">
      <div class="center">
        <a href="javascript:void(0)" onclick='allcheck(1)'>全て選択</a>
        <button type="button" class="posting">選択した市町のクルマを見る</button>
        <a href="javascript:void(0)" onclick='allcheck(0)'>全て解除</a>
      </div>
      <div class="city">
        <?php
$where="";
require_once($_SERVER['DOCUMENT_ROOT']."/include/where.php");
$sql="SELECT COUNT(id) AS car,city_cd as cd FROM t31car WHERE (city_cd DIV 1000)=$pref$where GROUP BY city_cd ORDER BY city_cd;";
$cars = $db->query($sql)->fetchAll(PDO::FETCH_ASSOC);
$rs = $db->query("SELECT cd,na FROM t42city WHERE pref_cd=$pref ORDER BY cd");
while ($r=$rs->fetch()) {
    $f=true;
    foreach($cars as $i=>$v){
        if ($v['cd']==$r['cd']) {
            echo '<label class="citybutton"><input name="city_cd[]" id="check'.$r['cd'].'" type="checkbox" onclick="changeback('."'check".$r['cd']."')".'" value="'.$r['cd'].'">'.$r['na'].'<br>('.$v['car'].')</label>';
            $f=false;
            break;
    }
}
if($f){echo '<div class="citypanel">'.$r['na'].'</div>';}
}
?>
      </div>
      <div class="center">
        <a href="javascript:void(0)" onclick='allcheck(1)'>全て選択</a>
        <button type="button" class="posting">選択した市町のクルマを見る</button>
        <a href="javascript:void(0)" onclick='allcheck(0)'>全て解除</a>
      </div>
    </form>
    <?php include_once($_SERVER['DOCUMENT_ROOT'].'/include/foot.php'); ?>
      <script type="text/javascript" src="js/jquery-3.1.1.min.js"></script>
      <script type="text/javascript" language="javascript">
        <!--
        $('.posting').on('click', function() {
          if ($(':checkbox').is(':checked')) {
            $('#main').submit();
          } else {
            alert("市町を選択してください。");
          }
        });

        function allcheck(check) {
          $(':checkbox').prop('checked', check);
          $.each($(':checkbox'), function() {
            changeback(this.id);
          });
        }

        function changeback(chkID) { //チェックボックスの背景色変更
          Myid = document.getElementById(chkID);
          if (Myid.checked == true) {
            Myid.parentNode.style.backgroundColor = '#CC28A8';
          } else {
            Myid.parentNode.style.backgroundColor = '#49a9d4'; //背景色
          }
        }
        -->
      </script>

  </BODY>

  </HTML>