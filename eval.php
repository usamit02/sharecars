<?php
session_start();
require_once($_SERVER['DOCUMENT_ROOT']."/include/db.php");
include_once($_SERVER['DOCUMENT_ROOT'].'/include/head.php');
$error=(isset($_SESSION['na']))?"":$id."ログインしてください。";
$message="";
if(isset($_POST['getid'])) {
    if (isset($_SESSION['key'])&&isset($_POST['key'])&&$_SESSION['key']==$_POST['key']) {//$error==""&&本番時
        $setid=$_POST['setid'];
        $getid=$_POST['getid'];
        $today=new DateTime();
        if($_POST['pre']){
            $q=$db->prepare("UPDATE t17eval SET id=?,txt=?,reg_day=?,owner=? WHERE set_id=$setid AND get_id=$getid;");
            $q->execute(array($_POST['id'],$_POST['txt'],$today->format('Y-m-d H:i:s'),$POST['owner']));
        }else{
            $q=$db->prepare("INSERT INTO t17eval(set_id,get_id,id,txt,reg_day,owner) VALUES (?,?.?,?,?,?);");
            $q->execute(array($setid,$getid,$_POST['id'],$_POST['txt'],$today->format('Y-m-d H:i:s'),$_POST['owner']));
        }
        header( "Location: my.php?id=$id" ) ;
    }
}
$key = md5(microtime() . mt_rand());
$_SESSION['key'] = $key;            //連投防止
?>
  <HTML>

  <HEAD>
    <META HTTP-EQUIV='Content-Type' CONTENT='text/html;charset=UTF-8'>
    <meta name="viewport" content="width=640">
    <title>評価</title>
    <link rel="stylesheet" href="css/memberreg.css">
  </HEAD>

  <BODY>
    <?php
if (isset($_GET['id'])) {
    $getid=$_GET['id'];
    $sql=($_GET['owner'])?"SELECT t31car.id AS id FROM t52reserv JOIN t31car ON t52reserv.car_id=t31car.id WHERE agree_day is not null AND owner_id=$getid;":"SELECT car_id AS id FROM t52reserv WHERE agree_day is not null AND id=$getid;";
    if($db->query($sql)->fetch()){
        $r=$db->query("SELECT na FROM t14member WHERE id=$getid;")->fetch();
        echo$r['na']."さんを以下のとおり評価する。";
        $rs=$db->query("SELECT id,txt FROM t17eval WHERE get_id=$getid AND set_id=$id;");
        if($preval=$rs->fetch()){
            $pre=1;
        }else{
            $preval['id']=0;
            $preval['txt']="";
            $pre=0;
        }
        echo"<FORM id='reg' action='eval.php' method='post'><div class='row'><fieldset><div>";
        echo"<div><select id='id' value='".$preval['id']."' name='id'>";
        $options=$db->query("SELECT id,na FROM mt17eval ORDER BY id DESCphp ;");
        while ($option=$options->fetch()) {
            $selected=($option['id']==$preval['id'])?" selected":"";
            echo "<option value='".$option['id']."'$selected>".$option['na']."</option>";
        }
        echo"</select><label for='id'>理由</label></div>";
        echo"<div><textarea id ='txt' name='txt' rows='5' cols='60'>".$preval['txt']."</textarea><label for='txt'>特記事項</label></div>";
        echo"<input type='hidden' name='key' value='$key'>";
        echo"<input type='hidden' name='getid' value='$getid'>";
        echo"<input type='hidden' name='setid' value='$id'>";
        echo"<input type='hidden' name='owner' value='".$_GET['owner']."'>";
        echo"<input type='hidden' name='pre' value='$pre'>";
        echo"</div></div></div><button onclick='posting()'>評価する</button></FORM>";
    }else{
        echo"評価できるのは予約成立から使用期間終了後１か月の期間です。";
    }
}else{
    echo "不正なアクセスです。";
}
?>
      <div id="message">
        <div id="error"><font color="#ff0000"><?php echo $error; ?></font></div>
        <div id="message"><font color="#0000ff"><?php echo $message; ?></font></div>
      </div>
      <script type="text/javascript">
        function posting() {
          $('#reg').submit();
        }
      </script>
  </BODY>

  </HTML>