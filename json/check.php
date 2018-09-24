<?php
session_start();
require_once $_SERVER['DOCUMENT_ROOT'].'/include/db.php';
$id=$_SESSION['id'];
$carid=$_GET['carid'];
$now=date('Y-m-d H:i:s');
$rs=$db->query("SELECT birth_day,license_day,re_day,ok_day,reg_day FROM t14member WHERE id=$id;");
if ($r=$rs->fetch()) {
    if (date($r['re_day'])>=$now) {
        if (date($r['reg_day'])<=date($r['ok_day'])&&isset($r['reg_day'])&&isset($r['ok_day'])) {
            $judg=array("="=>"が必要",">"=>"が不足している","<"=>"が超過している");
            $age=floor((date('Ymd')-date('Ymd', strtotime($r['birth_day'])))/10000);
            $licenseage=floor((date('Ymd')-date('Ymd', strtotime($r['license_day'])))/10000);
            $selectmem=$selectcar="";
            $rs=$db->query("SELECT na,name,compare FROM mt31carreg WHERE compare is not null AND (gp='insurance' OR gp='user') ORDER BY id;");
            $compare=$rs->fetchAll(PDO::FETCH_ASSOC);
            foreach ($compare as $val) {
                if (strpos($val['na'], "_age")===false) {
                    $selectmem.=$val['na'].",";
                }
                $selectcar.=$val['na'].",";
            }
            $selectmem=substr($selectmem, 0, strlen($selectmem)-1);
            $selectcar=substr($selectcar, 0, strlen($selectcar)-1);
            $rs=$db->query("SELECT $selectmem FROM t14member LEFT JOIN q17eval ON t14member.id=q17eval.id WHERE t14member.id=$id;");
            $mems=$rs->fetchAll(PDO::FETCH_ASSOC);
            $mem=$mems[0];
            $rs=$db->query("SELECT na,maker,price,holiday_price,ext_price,short_price,short_hour,long_price,long_date,$selectcar FROM t31car WHERE id=$carid;");
            $cars=$rs->fetchAll(PDO::FETCH_ASSOC);
            $car=$cars[0];
            $insurance=0;
            foreach ($compare as $r) {
                if(strlen($car[$r['na']])>0&&$car[$r['na']]){//$carオーナーの要求が、"",null,0は比較しない
                    $b=(strpos($r['na'], '_age')!==false)?$age: $mem[$r['na']];//???_ageは年齢と比較する
                    $b=($r['na']=='license_age')?$licenseage: $b;
                    if (compare($car[$r['na']], $b, $r['compare'])) {
                        if(strpos($r['na'], 'insurance_')===false&&strpos($r['na'], 'owner_')===false){
                            $json['error'][$r['na']]=$r['name'].$judg[$r['compare']];//不適合エラーをjsonに入れる
                        }
                        if($r['na']=='carprice'&&$mem['carcompensation']!=1){
                            $json['error']['carprice']="車両の弁償想定額が足りない";
                        }
                    }else{
                        if(strpos($r['na'], 'insurance_')===0){//保険は４つのうち１つ以上適合すれば$jsonにエラーを入れない
                            $insurance++;
                            if($r['na']=='insurance_owner'){
                                if(compare($car['owner_age'],$age,">")||compare($car['owner_claim'],$mem['owner_claim'],">")){
                                    $owner="オーナーの保険を使用するための条件が不足している";
                                }
                            }
                        }
                    }
                }
            }
            if($insurance>0){
                if(isset($owner)&&$insurance==1){//保険適合がオーナーの保険のみかつ年齢資金条件不足
                    $json['error']['insurance']=$owner;
                }
            }else{
                $json['error']['insurance']="任意保険が違う";
            }
        }else{
            $json['error']['ok']="本人確認が済んでいない";
        }
    }else{
        $json['error']['end']="免許証の更新確認が済んでいない";
    }
}else{
    $json['error']['id']="ログイン履歴がない";
}
//if(!isset($json)){$json=array("success");}else{$json[0]="error";}
if(isset($car)){$json['car']=array('na'=>$car['na'],'maker'=>$car['maker'],'price'=>$car['price'],'holiday_price'=>$car['holiday_price'],'ext_price'=>$car['ext_price'],'short_price'=>$car['short_price'],'short_hour'=>$car['short_hour'],'long_price'=>$car['long_price'],'long_date'=>$car['long_date']);}
header('Content-type: application/json');
echo json_encode($json);
function compare($a, $b, $ope)
{
    switch ($ope) {
        case "=":
            $result=($a==$b)?false:true;
            break;
        case ">":
            $result=($a>$b)?true:false;
            break;
        case "<":
            $result=($a<$b)?true:false;
            break;
        case ">=":
            $result=($a>=$b)?true:false;
            break;
        case "<=":
            $result=($a<=$b)?true:false;
            break;
        case "!=":
            $result=($a!=$b)?false:true;
            break;
}
return $result;
};