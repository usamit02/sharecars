<?php
require_once $_SERVER['DOCUMENT_ROOT'].'/include/db.php';
$no=(isset($_GET['no']))?$_GET['no']:0;
$na=(isset($_GET['na']))?$_GET['na']:"プラン$no";
$cd=$_GET['cd'];
$id=$_GET['id'];
$start=(isset($_GET['start']))?$_GET['start']:99;
$end=$_GET['end'];
$sign=($cd>-1)?">":"<=";
if ($no>0&&$start>-1) {
    $r=$db->query("SELECT MAX(no) AS maxno FROM t53plan WHERE id=$id AND cd$sign-1;")->fetch();
    $startday=new DateTime('1970-1-1');
    $startday->modify("+$start hour");
    $endday=new DateTime('1970-1-1');
    $endday->modify("+$end hour");
    $maxno=(isset($r['maxno']))?$r['maxno']:0;
    $r=$db->query("SELECT MAX(cd) AS maxcd FROM t53plan WHERE id=$id AND cd$sign-1 AND no=$no;")->fetch();
    $maxcd=isset($r['maxcd'])?$r['maxcd']:0-($cd<0)*2;
    if ($maxno+1<$no || $maxcd<$cd) {
        //if ($cd>0) {
        if ($no>20) {
            $newno=($cd==0||$cd==-2)?$maxno +1:$maxno ;
        } else {
            $newno=$no;
        }
        //} else {
        //  $newno=$maxno+1;
        // }
        $na=(isset($_GET['na']))?$_GET['na']:"プラン$newno";
        if ($newno<10) {
            $ps=$db->prepare("INSERT INTO t53plan(id,no,cd,na,start_day,end_day) VALUES (?,?,?,?,?,?);");
            if ($ps->execute(array($id,$newno,$cd,$na,$startday->format('Y-m-d H:i:s'),$endday->format('Y-m-d H:i:s')))) {
                $json['msg']="ok";
            } else {
                $json['msg']="データーベースエラーにより".$na."を追加できませんでした。";
            }
        } else {
            $json['msg']="追加プランは８個までです。".$na."を追加できませんでした。";
        }
    } else {
        $ps=$db->prepare("UPDATE t53plan SET na=?,start_day=?,end_day=? WHERE id=$id AND cd=$cd AND no=$no;");
        if ($ps->execute(array($na,$startday->format('Y-m-d H:i:s'),$endday->format('Y-m-d H:i:s')))) {
            $json['msg']="ok";
        } else {
            $json['msg']="データーベースエラーにより".$na."を変更できませんでした。";
        }
    }
} elseif ($no<0) {
    $db->query("DELETE FROM t53plan WHERE id=$id AND cd$sign-1 AND na='$na';");
    $json['msg']="ok";
} elseif ($start<0) {
    $db->query("DELETE FROM t53plan WHERE id=$id AND no=$no AND cd=$cd;");
    $json['msg']="ok";
} else {
    $json['msg']="ok";
}
$rs=$db->query("SELECT na,no,cd,start_day,end_day FROM t53plan WHERE id=$id AND cd$sign-1;");
$json+=$rs->fetchAll(PDO::FETCH_ASSOC);
header('Content-type: application/json');
echo json_encode($json);