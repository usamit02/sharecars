<?php
session_start();
require_once($_SERVER['DOCUMENT_ROOT']."/include/db.php");
include_once($_SERVER['DOCUMENT_ROOT'].'/include/head.php');
$error=(isset($na))?"":"ログインしてください。";
$message="";
if (isset($_POST['reg'])) {
    if (isset($_SESSION['key'])&&isset($_POST['key'])&&$_SESSION['key']==$_POST['key']) {//$error==""&&本番時
        $ip=$_SERVER['REMOTE_ADDR'];
        $para=[];
        $rs=$db->query("SELECT na,para FROM mt14memberreg;");
        foreach($rs as $r){
            $para+=array($r['na']=>$r['para']);
        }
        $rs=$db->query("SELECT * FROM t14member WHERE id=$id;");
        if ($r=$rs->fetch()) {
            $sql="";
            $auth=0;
            foreach ($_POST['reg'] as $key => $val) {
                if ($r[$key]!=$val&&strlen($val)) {
                    $data[$key]=htmlspecialchars($val, ENT_QUOTES);
                    $sql.=($key=='latlng')?"latlng=GeomFromText(:latlng),":$key."=:".$key.",";
                    $auth+=(strpos($para[$key],"auth"))?1:0;
                }
            }
            $data+=array("ip"=>$ip,"host"=>gethostbyaddr($ip));
            $sql.="ip=:ip,host=:host";
            if($auth){
                $data+=array("reg_day"=>date('Y-m-d H:i:s'));
                $sql.=",reg_day=:reg_day";
            }
            $strsql="UPDATE t14member SET $sql WHERE id=$id;";
            $ps=$db->prepare($strsql);
            if ($ps->execute($data)) {
                unset($_SESSION['key']);
                if (isset($na)) {
                    mb_send_mail("usamit02@gmail.com", $na.'さんが新規登録しました。', $data['id'].'<br>'.$data['birth_day']);
                }
                header( "Location: my.php?id=$id" ) ;
            } else {
                $error="データーベースエラーにより登録に失敗しました。お問い合わせください。";
            }
        } else {
            $error="ログインエラー";
        }
    } else {
        $message='';
    }
}
$key = md5(microtime() . mt_rand());
$_SESSION['key'] = $key;            //連投防止
?>
  <HTML>

  <HEAD>
    <META HTTP-EQUIV='Content-Type' CONTENT='text/html;charset=UTF-8'>
    <meta name="viewport" content="width=640">
    <title>メンバー登録</title>
    <link rel="stylesheet" href="css/memberreg.css">
  </HEAD>

  <BODY>
    <?php
$grouporder=array("basic","insurance","license","introduction","hidden");
$grouprow=array("basic"=>1,"insurance"=>1,"license"=>1,"introduction"=>2,"hidden"=>4);
$grouph1=array("basic"=>"","insurance"=>"加入可能な保険","license"=>"運転免許証","introduction"=>"","hidden"=>"");
$table="t14member";
include ($_SERVER['DOCUMENT_ROOT']."/include/reg.php");
echo "住　所";
$maxzm=30;
$marker="none";
include $_SERVER['DOCUMENT_ROOT'].'/include/map.php';
include_once($_SERVER['DOCUMENT_ROOT'].'/include/foot.php');
?>
      <script type="text/javascript" src="js/beforesend.js"></script>
      <script type="text/javascript">
        $(function() {
          $('#posting').click(function() {
            $('#message').html('処理中です・・・');
            var addr = "error";
            codeLatlng(function(lat, lng, addr) {
              if (addr.substr(0, 4) == '日本、〒') {
                $('#post_cd').val(addr.substr(4, 3) + addr.substr(8, 4));
                $('#addr1').val(addr.substr(13));
                $('#latlng').val('POINT(' + lng + ' ' + lat + ')');
                $('#message').html('');
                var confirm_message = beforesend();
                if (confirm_message) {
                  if (confirm(confirm_message)) {
                    $("input [type='number']").each(function() {
                      if ($(this).css('display') == 'none') {
                        $(this).val(null);
                      }
                    });
                    $('#reg').submit();
                  }
                }
              } else {
                alert(addr + 'n\道路、河川上など住所が取得できない場所です。地図を少しずらして再度登録ボタンを押してください。')
              }
            });
          });
        });

        function noChange() {
          location.href = "my.php";
        }
        $('#insurance_owner').change(function() {
          insurance_owner(this);
        });
        $('#carcompensation').change(function() {
          carcompensation(this);
        });

        function insurance_owner(that) {
          if ($(that).is(':checked')) {
            $('.owner,.owner + label,.owner + label + div').css('display', 'block');
          } else {
            $('.owner,.owner + label,.owner + label + div').css('display', 'none');
            $(".owner").prop('required', false);
          }
        }

        function carcompensation(that) {
          if ($(that).is(':checked')) {
            $('#carprice,#carprice + label,#carprice + label + div').css('display', 'none');
            $("#carprice").prop('required', false);
          } else {
            $('#carprice,#carprice + label,#carprice + label + div').css('display', 'block');
          } //beforesendからの呼び出しエラー回避のため設置
        }
      </script>
  </BODY>

  </HTML>