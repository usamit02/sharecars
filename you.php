<?php
session_start();
require_once($_SERVER['DOCUMENT_ROOT']."/include/db.php");
include_once($_SERVER['DOCUMENT_ROOT'].'/include/head.php');
$yourid=(isset($_GET['id']))?htmlspecialchars($_GET['id'], ENT_QUOTES):0;
$you=$db->query("SELECT t14member.id AS id, t14member.na AS na,img_url,introduction,agree,deny,res_min,mt15sex.na AS sex FROM t14member LEFT JOIN mt15sex ON t14member.sex=mt15sex.id WHERE t14member.id=$yourid;")->fetch();
echo"<HTML><HEAD><META HTTP-EQUIV='Content-Type' CONTENT='text/html;charset=UTF-8'><meta name='viewport' content='width=640'>";
echo"<title>".$you['na']."さんについて</title>";
echo"<link rel='stylesheet' href='/css/you.css'></HEAD><BODY><div id='main'>";
echo"<div id='basic'><div><a href='you.php?id=".$you['id']."'><img style='width:150px;' src='".$you['img_url']."'></a></div>";
$resmin=($you['agree']+$you['deny'])?floor($you['res_min']/($you['agree']+$you['deny'])):"-";
$txt=($you['agree']&&$you['deny'])?"予約承認:".$you['agree']."件　予約拒否:".$you['deny']."件　平均返答時間:".$resmin."分":"まだ予約された実績がありません。";
echo"<div><div>".$you['na']."　性別:".$you['sex']."</div><div>$txt</div></div></div><div id='introduction'>".$you['introduction']."</div></div>";
echo"<div id='eval'><div id='eval_title'><div><h2>評判　</h2></div><div><input type='radio' name='owner' value='-1' checked>全て<input type='radio' name='owner' value='1'>オーナーとして<input type='radio' name='owner' value='0'>ユーザーとして</div></div>";
echo"<div><div id='good'></div><div id='bad'></div></div></div>";
$sort=array('na','maker','year','price','holiday_price','ext_price','short_price','long_price');
$sortna=array('車名','メーカー','年','平日','休日+','延長-','短時-','長期-');
$aline=array('left','left','center','right','right','right','right','right');
$select=implode(",", $sort);
$cars=$db->query("SELECT id,reg_day,ok_day,re_day,$select FROM t31car WHERE owner_id=$yourid ORDER BY no,id;")->fetchAll(PDO::FETCH_ASSOC);
if (count($cars)) {
    echo'<h2>クルマ一覧</h2><div><table id="car">';
    echo'<tr><th>詳細</th>';
    foreach ($sortna as $value) {
        echo"<th><div>$value</div></th>";
    }
    echo"</tr>";
    foreach ($cars as $r) {
        $carid=$r['id'];
        $alert=(isset($r['reg_day'])&&isset($r['ok_day'])&&isset($r['re_day'])&&date($r['reg_day'])<=date($r['ok_day'])&&date($r['re_day'])>=date('Y-m-d H:i:s'))?"":"<div class='alert'>coming soon</div>";
        echo "<tr><td class='carimg'><a href='car.php?carid=$carid'><img class='indeximg' src='img/$carid/s-0.jpg'></a>$alert</td>";
        foreach ($sort as $key => $value) {
            $val=($aline[$key]=='right')?"￥".number_format($r[$value]):$r[$value];
            echo "<td align='$aline[$key]'>$val</td>";
        }
    }
    echo'</table></div>';
} else {
    echo"<div>クルマはまだ登録されていません。</div>";
}
include_once($_SERVER['DOCUMENT_ROOT'].'/include/foot.php');

?>

  <script type="text/javascript" src="js/jquery-3.1.1.min.js"></script>
  <script type="text/javascript">
    var yourid = <?php echo $yourid;?>;
    var owner = -1;
    var goodPage = 1;
    var badPage = 1;
    $(document).ready(function() {
      read(1, 1, -1);
      read(1, 0, -1);
    });
    $('input[name="owner"]:radio').change(function() {
      owner = $(this).val();
      read(goodPage, 1, owner);
      read(badPage, 0, owner);
    });

    function read(page, good, owner) {
      $.ajax({
        url: 'json/you.php',
        type: 'post',
        dataType: 'text',
        data: {
          'yourid': yourid,
          'page': page,
          'good': good,
          'owner': owner,
        },
        error: function() {
          alert('評価一覧の作成に失敗しました。')
        },
        success: function(html) {
          tag = (good) ? "#good" : "#bad";
          $(tag).empty();
          $(tag).append(html);
        }
      });
    }

    function pager(page, good) {
      if (good) {
        goodPage = page;
      } else {
        badPage = page;
      }
      read(page, good, owner);
    }
  </script>

  </BODY>

  </HTML>