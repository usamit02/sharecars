<?php
if(isset($_SESSION['id'])){
    $id=$_SESSION['id'];
    $na=(isset($_SESSION['na']))?$_SESSION['na']:null;
}else{
    if(isset($_COOKIE['tid'])){
        $id=$_COOKIE['tid'];
    }else{
        $id=mt_rand(10000000,100000000);
        setcookie('tid',$id,time()+60*60*24*30);
    }
    //$id=94010776;//temp
    $_SESSION['id']=$id;
}




//開発のみ
if(!isset($_SESSION['where'])){
    $rs=$db->query("SELECT birth_day,license_day,license_color,license_at,good_p,bad_p,sex,insurance_1day,insurance_driver,insurance_owner,owner_claim,insurance_user,carcompensation,carprice FROM t14member LEFT JOIN q17eval ON t14member.id=q17eval.id WHERE t14member.id=$id");
    if ($r=$rs->fetch()) {
        $_SESSION['where']=$r;
    }
}
$a=0;
?>