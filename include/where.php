<?php
if(isset($_SESSION['where'])){
    $age=floor((date('Ymd')-date('Ymd', strtotime($_SESSION['where']['birth_day'])))/10000);
    $licenseage=floor((date('Ymd')-date('Ymd', strtotime($_SESSION['where']['license_day'])))/10000);
    $str=" AND min_age<=$age AND (max_age=0 OR max_age>=$age) AND (good_p=0 OR good_p>=".$_SESSION['where']['good_p'].") AND bad_p<=".$_SESSION['where']['bad_p'];
    $str.=($_SESSION['where']['bad_p'])?" AND bad_rate<=".floor($_SESSION['where']['good_p']/$_SESSION['where']['bad_p']):"";
    $str.=" AND (sex=false OR sex=".$_SESSION['where']['sex'].") AND license_age<=$licenseage AND ";
    $str.=($_SESSION['where']['license_at'])?"mission_id<4 AND ":"";
    $str.=($_SESSION['where']['license_color']==1)?"license_green=0 AND ":"";
    $str.=($_SESSION['where']['license_color']==2)?"license_blue=0 AND ":"";
    $str.=($_SESSION['where']['carcompensation'])?"carcompensation=1 AND (":"(carcompensation=1 OR (carcompensation=0 AND carprice<=".$_SESSION['where']['carprice'].")) AND (";
    $str.=($_SESSION['where']['insurance_1day'])?"insurance_1day=1 OR ":"";
    $str.=($_SESSION['where']['insurance_driver'])?"insurance_driver=1 OR ":"";
    $str.=($_SESSION['where']['insurance_owner'])?"(insurance_owner=1 AND owner_claim<=".$_SESSION['where']['owner_claim'].") OR ":"";
    $str.=($_SESSION['where']['insurance_user'])?"insurance_user=1 OR ":"";
    $where.=substr($str, 0, strlen($str)-4).")";
}
if(isset($_POST['where'])&&strlen($_POST['where'])>2){
    $json=json_decode($_POST['where']);
    $pre="";
    $i=-1;
    $where.=" ";
    foreach($json[0] as $i=>$na){//$json[0]=className,$json[1]=value,json[2]=type
        $na=htmlspecialchars($na, ENT_QUOTES);
        $v= htmlspecialchars($json[1][$i], ENT_QUOTES);
        switch($json[2][$i]){
            case "number":
                $sign=(substr($na,-1,1)==1) ? ">=" : "<=";
                $str=substr($na,0,strlen($na)-1).$sign.$v;
                break;
            case "checkbox":
                $str="$na=$v";
                break;
            default:
                $str="$na LIKE '%$v%'";
        }
        if($pre==$na){
            $where.=($json[2][$i]=="checkbox")? " OR $str":" AND $str";
        }else{
            $where.=($i)?") AND ($str":"AND ($str";
        }
        $pre=$na;
    }
    $where.=($i!=-1)?")":"";
}
if(isset($_POST['reservA'])){
    $A=$_POST['reservA'];
    $Z=$_POST['reservZ'];
    $where .= " AND NOT EXISTS(SELECT car_id FROM q51schedule WHERE (";
    foreach($A as $key=>$Aval){
        $Zval=$Z[$key];
        $where .= "(start_day BETWEEN '$Aval' AND '$Zval' OR end_day BETWEEN '$Aval' AND '$Zval') OR ";
    }    
    $where = substr($where,0,strlen($where)-4);
    $str=(isset($_POST['table']))?$_POST['table'].".car_id":"t31car.id";
    $where .= ") AND $str=car_id)";
}
