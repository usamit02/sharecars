<?php
session_start();
require_once($_SERVER['DOCUMENT_ROOT']."/include/db.php");
include_once($_SERVER['DOCUMENT_ROOT'].'/include/head.php');
$error="";
$message="";
if (isset($_POST['reg'])) {
    if ($error==""&&isset($_SESSION['carkey'])&&isset($_POST['key'])&&$_SESSION['carkey']==$_POST['key']) {
        $ip=$_SERVER['REMOTE_ADDR'];
        $carid=htmlspecialchars($_POST['id'], ENT_QUOTES);
        if($carid){
            if($id!=$db->query("SELECT owner_id FROM t31car WHERE id=$carid;")->fetchColumn()){
                $error="オーナーとしてログインしてください。";
            }else{
                $sql="";
                foreach ($_POST['reg'] as $key => $val) {
                    if (strlen($val)) {
                        $data[$key]=htmlspecialchars($val, ENT_QUOTES);
                        $sql.=($key=='latlng')?"latlng=GeomFromText(:latlng),":$key."=:".$key.",";
                    }
                }
                $sql.="ip=:ip,host=:host";
                $data+=array("ip"=>$ip,"host"=>gethostbyaddr($ip));
                $ps=$db->prepare("UPDATE t31car SET $sql WHERE id=$carid");
                if ($ps->execute($data)) {
                    unset($_SESSION['carkey']);
                    header( "Location: carreg2.php?carid=$carid" ) ;
                } else {
                    $error="データーベースエラーにより変更登録に失敗しました。マスターまでお問い合わせください。";
                }
            }
        }else{
            $rs=$db->query('SELECT max(id) AS maxid FROM t31car;');
            $r=$rs->fetch();
            $carid=$r['maxid']+1;
            $rs=$db->query("SELECT max(no) AS maxno FROM t31car WHERE owner_id=$id;");
            $r=$rs->fetch();
            $data['no']=$r['maxno']+1;
            $data['id']=$carid;
            $sql="id,no,";
            $para=":id,:no,";
            foreach ($_POST['reg'] as $key => $val) {
                if (strlen($val)) {
                    $sql.=$key.",";
                    $data[$key]=htmlspecialchars($val, ENT_QUOTES);
                    $para.=($key=='latlng')?"GeomFromText(:latlng),":":".$key.",";
                }
            }
            $sql.="owner_id,reg_day,ip,host";
            $para.=":owner_id,:reg_day,:ip,:host";
            $data+=array("owner_id"=>$id,"reg_day"=>date('Y-m-d H:i:s'),"ip"=>$ip,"host"=>gethostbyaddr($ip));
            $ps=$db->prepare("INSERT INTO t31car($sql) VALUES ($para)");
            if ($ps->execute($data)) {
                unset($_SESSION['carkey']);
                if (!(is_dir($_SERVER['DOCUMENT_ROOT']."/img/$carid"))) {
                    mkdir($_SERVER['DOCUMENT_ROOT']."/img/$carid");
                    copy($_SERVER['DOCUMENT_ROOT']."/img/noimg.jpg", $_SERVER['DOCUMENT_ROOT']."/img/$carid/0.jpg");
                    copy($_SERVER['DOCUMENT_ROOT']."/img/s-noimg.jpg", $_SERVER['DOCUMENT_ROOT']."/img/$carid/s-0.jpg");
                }
                if (isset($name)) {
                    mb_send_mail("usamit02@gmail.com", $name.'さんから<a href="https://ss1.xrea.com/clife.s17.xrea.com/clife/diary.php?p='.$p.'#comment">'.$pna.'</a>にコメント', $title.'<br>'.$txt);
                }
                header( "Location: carreg2.php?carid=$carid" ) ;
            } else {
                $error="データーベースエラーにより登録に失敗しました。マスターまでお問い合わせください。";
            }
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
    <link rel="stylesheet" href="css/carreg.css">
  </HEAD>

  <BODY>
    <?php
$grouporder=array("basic","price","equip1","equip2","equip3","introduction","secret","hidden");
$grouprow=array("basic"=>1,"price"=>1,"equip1"=>3,"equip2"=>3,"equip3"=>3,"introduction"=>4,"secret"=>5,"hidden"=>5);
$grouph1=array("basic"=>"","price"=>"","equip1"=>"","equip2"=>"","equip3"=>"","introduction"=>"","secret"=>"","hidden"=>"");
$table="t31car";
echo"<div id='main'><div>";
include ($_SERVER['DOCUMENT_ROOT']."/include/reg.php");
echo "</div><div>引き渡し場所";
$maxzm=30;
$marker="none";
include $_SERVER['DOCUMENT_ROOT'].'/include/map.php';
echo"</div></div>";
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
                $.ajax({
                  url: 'json/citycode.php?code=' + addr.substr(4, 8),
                  type: 'GET',
                  async: false,
                  cache: false,
                  dataType: 'json',
                  timeout: 1000,
                  error: function() {
                    $('#message').html("郵便番号が取得できません。地図を少しずらして再度登録ボタンを押してください。");
                    setTimeout(function() {
                      $('#message').html('');
                    }, 3000);
                  },
                  success: function(json) {
                    $(json).each(function() {
                      if (json.length == 0) {
                        $('#message').html("市町村コートが取得できません。地図を少しずらして再度登録ボタンを押してください。");
                        setTimeout(function() {
                          $('#message').html('');
                        }, 3000);
                      } else {
                        $('#city_cd').val($(this).attr('city_cd'));
                        $('#latlng').val('POINT(' + lng + ' ' + lat + ')');
                        $('#message').html('');
                        var confirm_message = beforesend();
                        if (confirm_message) {
                          confirm_message += "引渡場所:" + addr;
                          if (confirm(confirm_message)) {
                            $('#reg').submit();
                          }
                        }
                      }
                    });
                  }
                });
              } else {
                alert(addr + 'n\道路、河川上など住所が取得できない場所です。地図を少しずらして再度登録ボタンを押してください。')
              }
            })
          })
        });

        function noChange(car_id) {
          location.href = "carreg2.php?carid=" + car_id;
        }
      </script>
  </BODY>

  </HTML>