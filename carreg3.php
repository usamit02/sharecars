<?php
session_start();
if (isset($_GET['id'])) {
    $carid = htmlspecialchars($_GET['id'], ENT_QUOTES);
} else if (isset($_POST['id'])) {
    $carid = htmlspecialchars($_POST['id'], ENT_QUOTES);
} else {
    echo '不正なアクセスです';
    die;
}
require_once($_SERVER['DOCUMENT_ROOT']."/include/db.php");
include_once($_SERVER['DOCUMENT_ROOT'].'/include/head.php');
if($id!=$db->query("SELECT owner_id FROM t31car WHERE id=$carid;")->fetchColumn()){
    echo"オーナーとしてログインしてください。";
    die;
}
$error="";
$message="";
$sql="";
if (isset($_POST['reg'])) {
    if ($error==""&&isset($_SESSION['carkey'])&&isset($_POST['key'])&&$_SESSION['carkey']==$_POST['key']) {
        foreach ($_POST['reg'] as $key => $val) {
            if (strlen($val)) {
                $data[$key]=htmlspecialchars($val, ENT_QUOTES);
                $sql.=$key."=:".$key.",";
            }
        }
        $sql=substr($sql,0,strlen($sql)-1);
        $strsql="UPDATE t31car SET $sql WHERE id=$carid;";
        $ps=$db->prepare($strsql);
        if ($ps->execute($data)) {
            unset($_SESSION['carkey']);
            header( "Location: car.php?carid=$carid" ) ;
        } else {
            $error="データーベースエラーにより登録に失敗しました。お問い合わせください。";
        }
    } else {
        $message='';
    }
}
$key = md5(microtime() . mt_rand());
$_SESSION['carkey'] = $key;            //連投防止
?>
  <HTML>

  <HEAD>
    <META HTTP-EQUIV='Content-Type' CONTENT='text/html;charset=UTF-8'>
    <meta name="viewport" content="width=640">
    <title>クルマ登録</title>
    <link rel="stylesheet" href="css/carreg3.css">
  </HEAD>

  <BODY>
    <?php
$grouporder=array("user","cancel","insurance");
$grouprow=array("user"=>1,"cancel"=>1,"insurance"=>2);
$grouph1=array("cancel"=>"キャンセルポリシー","insurance"=>"適用可能な自動車保険","user"=>"対象となるユーザー");
$table="t31car";
include ($_SERVER['DOCUMENT_ROOT']."/include/reg.php");
include_once($_SERVER['DOCUMENT_ROOT'].'/include/foot.php');
?>
      <script type="text/javascript" src="js/jquery-3.1.1.min.js"></script>
      <script type="text/javascript" src="js/beforesend.js"></script>
      <script type="text/javascript">
        $(function() {
          $('#posting').click(function() {

            var confirm_message = beforesend();
            if (confirm_message) {
              if (confirm(confirm_message)) {
                noChange();
              }
            }
          });
        });

        function noChange() {
          $("input[type='number']").each(function() {
            if ($(this).css('display') == 'none' || $(this).val().length == 0) {
              $(this).val('0');
            }
          });
          $('#reg').submit(); //location.href = "car.php?carid=" + carid;
        }
        $('#insurance_owner').change(function() {
          insurance_owner(this);
        });

        function insurance_owner(that) {
          if ($(that).is(':checked')) {
            $('.owner,.owner + label,.owner + label + div').css('display', 'block');
            $("#delay_rate,#delay_rate + label,#delay_rate + label + div").css('display', 'block');
            $(".owner,#delay_rate").prop('required', true);
          } else {
            $('.owner,.owner + label,.owner + label + div').css('display', 'none');
            $(".owner").prop('required', false);
            if ($("#carcompensation").is(':checked')) {
              $("#delay_rate,#delay_rate + label,#delay_rate + label + div").css('display', 'none');
              $("#delay_rate").prop('required', false);
            }
          }
        }
        $('#carcompensation').change(function() {
          carcompensation(this);
        });

        function carcompensation(that) {
          if ($(that).is(':checked')) {
            $(".car,.car + label,.car + label + div").css('display', 'none');
            $(".car").prop('required', false);
            if (!$("#insurance_owner").is(':checked')) {
              $("#delay_rate,#delay_rate + label,#delay_rate + label + div").css('display', 'none');
              $("#delay_rate").prop('required', false);
            }
          } else {
            $(".car,.car + label,.car + label + div").css('display', 'block');
            $("#delay_rate,#delay_rate + label,#delay_rate + label + div").css('display', 'block');
            $(".car,#delay_rate").prop('required', true);
          }
        }
      </script>
  </BODY>

  </HTML>