<?php
session_start();
$holiday = [1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12];
$holiday[1] = [1, 2, 3, 4, 5, 8];
$holiday[2] = [12];
$holiday[3] = [21];
$holiday[4] = [30];
$holiday[5] = [1, 2, 3, 4, 5, 6];
$holiday[6] = [];
$holiday[7] = [16];
$holiday[8] = [13, 14, 15];
$holiday[9] = [17, 24];
$holiday[10] = [8];
$holiday[11] = [23];
$holiday[12] = [24, 31];
function culcPrice($startday,$endday,$price,$holiday_price,$ext_price,$short_price,$short_hour,$long_price,$long_date,$holiday){
    $Aday=new Datetime($startday);
    $Zday=new Datetime($endday);
    $p=0;
    $diffday=$Aday->diff($Zday)->format('%R%a');
    if($diffday>0){
        for($i=0;$i<$diffday;$i++){
            $p+=$price;
            $p+=(isHoliday($Aday,$holiday))?$holiday_price:0;
            $p-=($i>=$long_date)?$long_price:0;
            $p-=($i)?$ext_price:0;
            $Aday->modify('+1 day');
        }
        if($Aday<$Zday){//帰着時間が貸出時間より遅い、最終日に余りがある場合
            $p+=(isHoliday($Zday,$holiday))?$price+$holiday_price-$ext_price:$price-$ext_price;
            $p-=($i>=$long_date)?$long_price:0;
        }
    }else{
        $diffhour=$Aday->diff($Zday)->format('%R%H');
        $p+=($diffhour > $short_hour)?$price:$price-$short_price;
        $p+=(isHoliday($Aday,$holiday)||isHoliday($Zday,$holiday))?$holiday_price:0;
    }
    return $p;
}
function isHoliday($day,$holiday){
    $w=$day->format('w');
    if($w=='6'||$w=='0'){
        return true;
    }else{
        $m=intval($day->format('m'));
        $d=intval($day->format('d'));
        foreach($holiday[$m] as $i=>$v){
            if($d==$v){return true;}
        }
    }
    return false;
}
require_once($_SERVER['DOCUMENT_ROOT']."/include/db.php");
$id=$_SESSION['id'];
if (isset($_POST['delete'])&&$_POST['delete']) {
    $carid=htmlspecialchars($_POST['delete'], ENT_QUOTES);
    $r=$db->query("SELECT no FROM t22favorite WHERE id=$id AND car_id=$carid;")->fetch();
    $ps=$db->prepare("DELETE FROM t22favorite WHERE id=? AND car_id=?");
    $ps->execute(array($id,$carid));
    if($ps->rowCount()){
        $db->query("UPDATE t22favorite SET no=no-1 WHERE id=$id AND no>".$r['no']);
    }
}
if (isset($_POST['updown'])) {
    $sign=($_POST['updown']<0)?"+1":"-1";
    $updown=abs(htmlspecialchars($_POST['updown']));
    $r=$db->query("SELECT no FROM t22favorite WHERE id=$id AND car_id=$updown;")->fetch();
    $no=$r['no'];
    $ps=$db->prepare("UPDATE t22favorite SET no=? WHERE id=? AND no=?");
    $ps->execute(array($no,$id,$no+$sign));
    if ($ps->rowCount()) {
        $db->query("UPDATE t22favorite SET no=no$sign WHERE id=$id AND car_id=$updown;");
    }
}
$table=$_POST['table'];
$order=$_POST['order'];
$p=$_POST['page'];
$where="";
require_once($_SERVER['DOCUMENT_ROOT']."/include/where.php");
$where=substr($where,5);
$maxp=$db->query("SELECT count(car_id) AS maxid FROM $table WHERE $where;")->fetchcolumn();
$L=($p-1)*10;
$sort=array('','price','year','km','good_p','bad_p');
$sortna=array('クルマ','金','年','km','良','悪');
if(!$_POST['mobile']){
    array_splice($sort,4,0,'');
    array_splice($sortna,4,0,'オーナー');
    array_push($sort,'agree','deny','res_min');
    array_push($sortna,'承','否','返');
}
$html= '<div><table><tr>';
foreach ($sort as $key => $value) {
    $up=(strlen($value))?"<button onclick='setOrder(".'"'.$value.'"'.")'>▲</button>":"";
    $down=(strlen($value))?"<button onclick='setOrder(".'"'.$value.'"'.",1)'>▼</button>":"";
    $html.= "<th><div class='index'>$up$sortna[$key]$down</div></th>";
}
$html.= "<th><a href='carindex.php'>☆</a><button onclick='reservCheck()'>♪</button></th></tr>";
$rs = $db->query("SELECT * FROM $table WHERE $where ORDER BY $order LIMIT $L,10");
while ($r=$rs->fetch()) {
    $carid=$r['car_id'];
    $stop=(date($r['reg_day'])<=date($r['ok_day'])&&isset($r['reg_day'])&&isset($r['ok_day'])&&date($r['re_day'])>=date('Y-m-d H:i:s'))?false:true;
    $fee=0;
    if(isset($_POST['reservA'])){
        foreach($_POST['reservA'] as $i=>$v){
            $fee+=culcPrice($v,$_POST['reservZ'][$i],$r['price'],$r['holiday_price'],$r['ext_price'],$r['short_price'],$r['short_hour'],$r['long_price'],$r['long_date'],$holiday);
        }
    }
    $fee=($fee)?$fee:$r['price'];
    $alert=($stop)?"<div class='alert'>coming soon</div>":"";
    $html.="<tr><td rowspan='2' class='carimg'><a href='car.php?carid=$carid'><img class='indeximg' src='img/$carid/s-0.jpg'></a>$alert</td>";
    $html.="<td rowspan='2' align='right'>".number_format($fee)."</td>";
    $colspan=($_POST['mobile'])?2:5;
    $html.="<td align='left'>".$r['maker']."</td><td align='left'>".$r['na']."</td>";
    $html.=($_POST['mobile'])?"":"<td rowspan='2'><a href='you.php?id=".$r['owner_id']."'><img class='indeximg' src='".$r['img_url']."'></a></td>";
    $html.="<td colspan='$colspan' align='left'><a href='you.php?id=".$r['owner_id']."'>".$r['owner']."</a>さん</td>";
    $request=($stop)?"":'<label style="color:lightgray;"><input type="checkbox" class="carids" onclick="changeback(this)" style="display:none;" value="'.$carid.'">♪</label>';
    if($table=="q22favorite"){
        $delete="<button onclick='read($carid)'>削除</button>";
        $up="<button onclick='read(0,$carid)'>▲</button>";
        $down="<button onclick='read(0,-$carid)'>▼</button>";
        $html.= "<td rowspan='2'>$request $delete $up $down</td>";
    }else{
        $html.= "<td rowspan='2'><button type='button' onClick='favorite($id,$carid);'>★</button>$request</td>";
    }
    $html.= '</tr>';
    $html.="<tr><td align='center'>".$r['year']."</td><td align='right'>".number_format($r['km'])."km</td>";
    $html.="<td align='right'>".$r['good_p']."</td><td align='right'>".$r['bad_p']."</td>";
    $html.=($_POST['mobile'])?"</tr>":"<td align='right'>".$r['agree']."</td><td align='right'>".$r['deny']."</td></td><td align='right'>".$r['res_min']."</td></tr>";
}
$html.= '</table></div>';
//ページング
$strp='<div class="pager">';
for ($i=1; $i<=ceil($maxp/10); $i++) {
    if ($i==$p) {
        $strp=$strp.' '.$i.' ';
    } else {
        $strp=$strp."<button onclick='pager($i)'>$i</button>";
    }
}
$html.= $strp."</div>";
header('Content-type: text/plain; charset=UTF-8');
echo $html;