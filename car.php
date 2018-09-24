<?php
session_start();
require_once($_SERVER['DOCUMENT_ROOT']."/include/db.php");
include_once($_SERVER['DOCUMENT_ROOT'].'/include/head.php');
if (isset($_GET['carid'])) {
    $carid = htmlspecialchars($_GET['carid'], ENT_QUOTES);
    //$json_carid='["'.$carid.'"]';
} else {
    echo '不正なアクセスです';
    die;
}
?>
  <HTML>

  <HEAD>
    <META HTTP-EQUIV='Content-Type' CONTENT='text/html;charset=UTF-8'>
    <meta name="viewport" content="width=640">
    <title>クルマ</title>
    <link rel="stylesheet" href="css/car.css">
    <link rel="stylesheet" href="css/pop.css">
    <link rel="stylesheet" href="css/calendar.css">
    <link rel="stylesheet" href="css/reserv.css">
  </HEAD>

  <BODY>

    <div id="pop">
      <div id="pop_title">予約</div>
      <div id="calendar"></div>
      <div id="plans"></div>
      <div id="timetable">&nbsp;</div>
      <div id='pop_close'>
        <button id="Close" onclick="Close()">設定</button>
        <button onclick="Cancel()">閉じる</button>
      </div>
    </div>
    <?php

$rs=$db->query("SELECT * FROM q31car WHERE id=$carid");
while ($r=$rs->fetch()) {
    $secret=($db->query("SELECT id FROM t52reserv WHERE id=$id AND car_id=$carid AND agree_day is not null AND deny_day is null AND end_day>NOW();")->fetch())?true:false;
    $stop=(isset($r['reg_day'])&&isset($r['ok_day'])&&isset($r['re_day'])&&date($r['reg_day'])<=date($r['ok_day'])&&date($r['re_day'])>=date('Y-m-d H:i:s'))?false:true;
    //写真
    $sjpgs=glob("img/$carid/s-*.*");
    if (isset($sjpgs)&&$sjpgs) {
        $jpgs=str_replace('s-', '', $sjpgs);
        echo "<div id='carimg'><div class='thumbimg'><img src='$jpgs[0]'></div>";
        if($stop){echo"<div id='alert'>coming soon</div>";};
        echo"<ul class='thumblist'>";
        foreach ($sjpgs as $key => $value) {
            echo "<li><a href='$jpgs[$key]'><img src='$value'></a></li>";
        }
        echo '</ul></div>';
    }
    // メインパネル　　車名、金額
    echo "<div id='main'>";
    echo "<div id='panel'><div>".$r['maker']."</div><div>".$r['na']."</div>";
    echo "<div>￥".number_format($r['price'])."</div></div>";
    echo "<table><tr id='moneypanel'><td>+￥".number_format($r['holiday_price'])."</td><td>-￥".number_format($r['short_price'])."</td><td>-￥".number_format($r['ext_price'])."</td></tr>";
    echo "<tr id='moneysubpanel'><td>土日祝</td><td>".$r['short_hour']."時間以内</td><td>2日目以降</td></table></div>";
    echo "<div id='sub'><div><div id='carinfo'>";
    //ベーシックパネル　　クルマの基本情報　年式、排気量、燃料、走行距離
    echo "<table>";
    $where=($secret)?" OR gp='secret'":"";
    $rrs=$db->query("SELECT id,na,name,unit FROM mt31carreg WHERE gp='basic'$where ORDER BY id;");
    while ($rr=$rrs->fetch()) {
        echo "<tr><th>".$rr['name']."</th>";
        echo "<td>".$r[$rr['na']].$rr['unit']."</td></tr>";
    }
    //エクイップパネル　　便利な装備
    echo "</table><div class='equip'>";
    for ($i=1; $i<4; $i++) {
        echo "<div>";
        $rrs=$db->query("SELECT id,na,name,typ FROM mt31carreg WHERE gp='equip".$i."' ORDER BY id;");
        while ($rr=$rrs->fetch()) {
            $class=($r[$rr['na']]&&$r[$rr['na']]!="×"&&isset($r[$rr['na']]))?'equipon':'equipoff';
            switch ($rr['typ']) {
                case "select":
                    $txt=(mb_strlen($r[$rr['na']])>1)?$r[$rr['na']]:$rr['name'];
                    break;
                case "number":
                    $txt=($r[$rr['na']])?$rr['name']."×".$r[$rr['na']]:$rr['name'];
                    break;
                default:
                    $txt=$rr['name'];
            }
            echo"<div class='$class'>$txt</div>";
        }
        echo"</div>";
    }
    echo"</div></div><div id='intro'>".$r['introduction']."</div></div>";
    //使用者制限
    $rrs=$db->query("SELECT id,na,typ,name,unit FROM mt31carreg WHERE gp='insurance' ORDER BY id;");
    echo"<div><div><h2>利用できる自動車保険</h2>";
    while ($rr=$rrs->fetch()) {
        switch ($rr['typ']) {
            case "checkbox":
                $txt=($r[$rr['na']])?"<div>".$rr['name']."</div>":false;
                break;
            case "number":
                $txt=(isset($r[$rr['na']])&&$r[$rr['na']])?$rr['name'].":".$r[$rr['na']].$rr['unit']:false;
                break;
    }
    if ($txt) {
        echo$txt;
    }
}
echo"</div><div><h2>対象ユーザー</h2>";
$txt=(isset($r['min_age'])&&$r['min_age'])?$r['min_age']."歳から":"";
$txt.=(isset($r['max_age'])&&$r['max_age'])?$r['max_age']."歳まで":"";
$txt=($txt=="")?"全年齢":$txt;
echo$txt;
$txt=(isset($r['good_p'])&&$r['good_p'])?"良い評価:".$r['good_p']."p以上":"";
$txt.=(isset($r['bad_p'])&&$r['bad_p'])?"悪い評価:".$r['bad_p']."p以下":"";
$txt.=(isset($r['bad_rate'])&&$r['bad_rate'])?"悪い評価: １/".$r['bad_rate']."以下":"";
$txt=($txt=="")?"評価制限なし":$txt;
echo"<div>$txt</div></div>";
$owner=$db->query("SELECT t14member.id AS id, t14member.na AS na,img_url,agree,deny,res_min,mt15sex.na AS sex,p,q17eval.good_p AS good_p,q17eval.bad_p AS bad_p FROM t14member JOIN t31car ON t14member.id=t31car.owner_id LEFT JOIN mt15sex ON t14member.sex=mt15sex.id LEFT JOIN q17eval ON t14member.id=q17eval.id WHERE t31car.id=$carid;")->fetch();
echo"<div><h2>オーナー</h2>";
echo"<div>".$owner['na']."　性別:".$owner['sex']."</div>";
echo"<div id='owner'><a href='you.php?id=".$owner['id']."'><img style='width:150px;' src='".$owner['img_url']."'></a>";
$resmin=($owner['agree']+$owner['deny'])?floor($owner['res_min']/($owner['agree']+$owner['deny'])):"-";
$txt=($owner['agree']&&$owner['deny'])?"予約承認:".$owner['agree']."件　予約拒否:".$owner['deny']."件　平均返答時間:".$resmin."分":"まだ予約された実績がありません。";
echo"<div><div>$txt</div>";
echo"<div>良い評価:".$owner['good_p']."p　悪い評価:".$owner['bad_p']."p</div></div></div></div>";
echo"<div id='operate'><div><button type='button' onClick='favorite($id,$carid);'>"."★お気に入り</button>";
if(!$stop){
    echo"<button type='button' onclick='reservCheck(".$carid.")'>♪予約する</button>";
}
echo"</div></div></div></div></div>";
$lat=$r['lat'];
$lng=$r['lng'];
$maxzm=($secret)?30:15;
$marker=($secret)?"secret":0;
}
?>
      <script type="text/javascript">
        var id = <?php if(isset($id)){echo $id;}?>
      </script>
      <?php
include $_SERVER['DOCUMENT_ROOT'].'/include/map.php';
include_once($_SERVER['DOCUMENT_ROOT'].'/include/foot.php');
?>
        <script type="text/javascript" src="js/favorite.js"></script>
        <script type="text/javascript" src="js/pop.js"></script>
        <script type="text/javascript" src="js/calendar.js"></script>
        <script type="text/javascript" src="js/schedule.js"></script>
        <script type="text/javascript" src="js/reserv.js"></script>
        <script type="text/javascript">
          var endDay, period;
          $(".thumblist li a").click(function() {
            var url = $(this).attr("href");
            var img = new Image();
            var $img = $(this).parents("#carimg").find('.thumbimg img');
            $img.attr({
              'src': "img/load.gif"
            });
            $(img).on("load", function() {
              $img.attr({
                'src': url
              });
            });
            img.src = url;
            return false;
          });

          function Close() {
            if (reservClose()) {
              popClose();
            }
          }

          function Cancel() {
            popClose();
            $("#reservcars").empty();
          }
        </script>

  </BODY>

  </HTML>