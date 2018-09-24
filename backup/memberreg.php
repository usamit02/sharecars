<?php
session_start();
require_once($_SERVER['DOCUMENT_ROOT']."/include/db.php");
include_once($_SERVER['DOCUMENT_ROOT'].'/include/head.php');
$error="";
$message="";
if (isset($_POST['reg'])) {
    if ($error==""&&isset($_SESSION['key'])&&isset($_POST['key'])&&$_SESSION['key']==$_POST['key']) {
        $ip=$_SERVER['REMOTE_ADDR'];
        $rs=$db->query("SELECT reg_day FROM t14member WHERE id=$id");
        if ($r=$rs->fetch()) {
            $data['id']=$id;
            $sql="id=:id,";
            foreach ($_POST['reg'] as $key => $val) {
                if (strlen($val)) {
                    $data[$key]=htmlspecialchars($val, ENT_QUOTES);
                    $sql.=($key=='latlng')?"latlng=GeomFromText(:latlng),":$key."=:".$key.",";
                }
            }
            $data+=array("reg_day"=>date('Y-m-d H:i:s'),"ip"=>$ip,"host"=>gethostbyaddr($ip));
            $sql.="reg_day=:reg_day,ip=:ip,host=:host";
            $strsql="UPDATE t14member SET $sql;";
            $ps=$db->prepare($strsql);
            if ($ps->execute($data)) {
                unset($_SESSION['key']);
                if (isset($na)) {
                    mb_send_mail("usamit02@gmail.com", $na.'さんが新規登録しました。', $data['id'].'<br>'.$data['birth_day']);
                }
                header( "Location: owner.php?id=$id" ) ;
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
    <title>メンバー登録</title>
    <link rel="stylesheet" href="css/memberreg.css">
  </HEAD>

  <BODY>
    <FORM id="main" ,ACTION="memberreg.php" METHOD="post" ENCTYPE="multipart/form-data">
      <div>
        <?php
$groups=$db->query('SELECT gp AS na FROM mt14memberreg GROUP by gp ORDER BY na');
while ($group=$groups->fetch()) {
    echo "<fieldset><div id='".$group['na']."'>";
    $sql="SELECT na,typ,name,para,tbl,def FROM mt14memberreg where gp='".$group['na']."'";
    $tags=$db->query($sql);
    while ($tag=$tags->fetch()) {
        $data[$tag['na']]=$tag['def'];//開発のみ
        $prevalue=isset($data[$tag['na']])?$data[$tag['na']]:"";
        echo "<div>";
        if (isset($tag['tbl'])) {
            echo "<select id='".$tag['na']."' value='$prevalue' name='reg[".$tag['na']."]'>";
            $options=$db->query("SELECT id,na FROM ".$tag['tbl']);
            while ($option=$options->fetch()) {
                echo "<option value='".$option['id']."'>".$option['na']."</option>";
            }
            echo"</select>";
        } else {
            if ($tag['typ']=='textarea') {
                echo "<".$tag['typ']." id='".$tag['na']."' name='reg[".$tag['na']."]' ".$tag['para'].">$prevalue</".$tag['typ'].">";
            } else {
                echo "<input type='".$tag['typ']."' id='".$tag['na']."' name='reg[".$tag['na']."]' value='$prevalue' ".$tag['para'].">";
            }
        }
        echo "<label for='".$tag['na']."'>".$tag['name']."</label></div>";
    }
    echo "</div></fieldset>";
}
?>
      </div>
      <input type="hidden" name="key" value="<?php echo $key; ?>" />
      <div id="post">
        <button type="button" id="posting">登録</button>
      </div>
    </FORM>
    <div id="error"><font color="#ff0000"><?php echo $error; ?></font></div>
    <div id="message"><font color="#0000ff"><?php echo $message; ?></font></div>
    </div>

    <?php
$lat=35.6845;
$lng=139.7521;
$zm=12;
include $_SERVER['DOCUMENT_ROOT'].'/include/map.php';
include_once($_SERVER['DOCUMENT_ROOT'].'/include/foot.php');
//   <script type="text/javascript" src="./js/memberreg.js"></script>
?>

      <script type="text/javascript">
        function beforesend(formname) {
          var values = new Object();
          var err = false;
          $("#" + formname + " input:not([type='hidden']):not([type='checkbox'])").each(function() {
            values[$(this).next("label").text()] = this.value;
            $(this).css('border', '');
            if ($(this).val().length == 0) {　
              if ($(this).prop("required")) {
                senderr(this, "は必須入力です。");
                err = true;
              }
            } else {
              switch ($(this).attr("type")) {
                case "text":
                  if ($(this).val().length > $(this).attr("maxlength")) {
                    senderr(this, "は" + $(this).attr("maxlength") + "文字以下です。あと" + ($(this).val().length - $(this).attr("maxlength")) + "文字減らしてください。");
                    err = true;
                  };
                  break;
                case "number":
                  if ($(this).val().match(/[^0-9]/g)) {
                    //if($(this).attr("min")!=='undefined'){
                    if ($(this).val() < $(this).attr("min")) {
                      senderr(this, "は" + $(this).attr("min") + "以上にしてください。");
                      err = true;
                    }
                    if ($(this).val() > $(this).attr("max")) {
                      senderr(this, "は" + $(this).attr("max") + "以下にしてください。");
                      err = true;
                    }
                  } else {
                    senderr(this, "には数字を入力してください。");
                    err = true;
                  }
                  break;
                case "date":
                  if (!isdate($(this).val())) {
                    senderr(this, "は年/月/日（年、月、日は数字のみ）で入力してください。");
                    err = true;
                  } else {
                    age = getage(new Date($(this).val()));
                    if (age < $(this).attr("minage") || age > $(this).attr("maxage")) {
                      senderr(this, "は対象外の日付です。");
                      err = true;
                    }
                  }
                  break;
                case "email":
                  if (!$(this).val().match(/[!#-9A-~]+@+[a-z0-9]+.+[^.]$/i)) {
                    senderr(this, "を正しく入力してください。");
                    err = true;
                  }
                  break;　
              }
            }
          });
          $("#" + formname + " textarea").each(function() {
            values[$(this).next("label").text()] = this.value;
            $(this).css('border', '');
            if ($(this).val().length > 500) {
              senderr(this, "は500文字以下です。あと" + ($(this).val().length - 500) + "文字減らしてください。");
              err = true;
            }
          });
          $("#" + formname + " input:checked").each(function() {
            values[$(this).next("label").text()] = "〇";
          });
          $("#" + formname + " option:selected").each(function() {
            var input_name = $(this).parent().next("label").text();
            values[input_name] = this.innerHTML;
          });
          var confirm_massage = "以下の内容で登録します。\n\n";
          $.each(values, function(key, value) {
            confirm_massage += key + '：' + value + '\n';
          });
          if (err) {
            return false;
          } else {
            return confirm_massage;
          }
        }

        function senderr(e, message) {
          $(e).focus();
          $(e).css('border', '2px solid red');
          alert($(e).next("label").text() + message);
        }

        function isdate(s) {
          var matches = /^(\d+)\/(\d+)\/(\d+)$/.exec(s);
          if (!matches) {
            return false;
          }
          var y = parseInt(matches[1]);
          var m = parseInt(matches[2]);
          var d = parseInt(matches[3]);
          if (m < 1 || m > 12 || d < 1 || d > 31) {
            return false;
          }
          var dt = new Date(y, m - 1, d, 0, 0, 0, 0);
          if (dt.getFullYear() != y || dt.getMonth() != m - 1 || dt.getDate() != d) {
            return false;
          }
          return true;
        }

        function getage(birthday) {
          var today = new Date();
          var age = today.getFullYear() - birthday.getFullYear();
          var day = new Date(today.getFullYear(), birthday.getMonth(), birthday.getDate());
          if (today < day) {
            age--;
          }
          return age;
        }

        $(function() {
          $('#posting').click(function() {
            $('#message').html('処理中です・・・');
            var addr = "error";
            codeLatlng(function(lat, lng, addr) {
              if (addr.substr(0, 4) == '日本、〒') {
                $('#post_cd').val(addr.substr(4, 3) + addr.substr(8, 4));
                $('#latlng').val('POINT(' + lng + ' ' + lat + ')');
                $('#message').html('');
                // $.getScript("js/beforesend.js", function () {
                var confirm_message = beforesend("main");
                if (confirm_message) {
                  confirm_message += "住所:" + addr.substr(3);
                  if (confirm(confirm_message)) {
                    $('#addr').val(addr.substr(13) + $('#addr').val())
                    $('#main').submit();
                  }
                }
                // });
              } else {
                alert(addr + 'n\道路、河川上など住所が取得できない場所です。地図を少しずらして再度登録ボタンを押してください。')
              }
            })
          })
        });
      </script>
  </BODY>

  </HTML>