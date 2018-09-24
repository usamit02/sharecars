<?php
session_start();
require_once($_SERVER['DOCUMENT_ROOT']."/include/db.php");
$id=$_SESSION['id'];
$json=[];
if(isset($_POST['no'])){
    $no=$_POST['no'];
    $r=$db->query("SELECT condition_,t21term.na AS na,t21term.val AS val,t21term.typ AS typ,mt31carreg.name AS name,mt31carreg.unit AS unit,mt31carreg.tbl AS tbl FROM t21term JOIN mt31carreg ON t21term.na=mt31carreg.na WHERE t21term.id=$id AND t21term.search_no=$no ORDER BY t21term.no;")->fetchAll(PDO::FETCH_ASSOC);
    for($i=0;$i<count($r);$i++){
        if($r[$i]['tbl']){
            $r[$i]['option']=$db->query("SELECT na FROM ".$r[$i]['tbl']." WHERE id=".$r[$i]['val'])->fetchcolumn();
        }
        $json[$i]=$r[$i];
    }
    $json[$i]=$db->query("SELECT 'map' AS condition_,ST_X(latlng) AS lng,ST_Y(latlng) AS lat,zm FROM t21search WHERE id=$id AND no=$no;")->fetch();
}else if(isset($_POST['delete'])){
    $no=$_POST['delete'];
    $db->beginTransaction();
    $sql1="DELETE FROM t21search WHERE id=$id AND no=$no;";
    $sql2="DELETE FROM t21term WHERE id=$id AND search_no=$no;";
    if($db->exec($sql1)&&$db->exec($sql2)){
        $json['msg']="delete ok";
        $db->commit();
    }else{
        $json['msg']='データーベースエラーにより削除に失敗しました。';
        $db->rollBack();
    }
}else{
    $searchna=$_POST['searchna'];
    $overWrite=$_POST['overWrite'];
    $f=true;
    if($r=$db->query("SELECT no,reg_day FROM t21search WHERE id=$id AND na='$searchna';")->fetch()){
        if($overWrite){
            $no=$r['no'];
        }else{
            $json['msg']=$r['reg_day']."に保存した".$searchna."を上書きしますか。";
            $json['overWrite']="1";
            $f=false;
        }
    }
    if($f){
        $terms=json_decode($_POST['terms']);
        if(!isset($no)){
            $no=($no=$db->query("SELECT MAX(no) AS maxno FROM t21search WHERE id=$id;")->fetchcolumn())?$no+1:1;
        }
        $todate=new DateTime();
        $error=0;
        $i=1;
        $db->beginTransaction();
        $db->exec("DELETE FROM t21search WHERE id=$id AND no=$no;");
        $db->exec("DELETE FROM t21term WHERE id=$id AND search_no=$no;");
        if(isset($_POST['lat'])&&isset($_POST['lng'])&&isset($_POST['zm'])){
            $q=$db->prepare("INSERT INTO t21search(id,no,na,reg_day,latlng,zm)VALUES(?,?,?,?,GeomFromText(?),?);");
            $error+=($q->execute(array($id,$no,$searchna,$todate->format('Y-m-d H:i:s'),"POINT(".$_POST['lng']." ".$_POST['lat'].")",$_POST['zm']))&&$q->rowCount()==1)?0:1;
        }else{
            $q=$db->prepare("INSERT INTO t21search(id,no,na,reg_day)VALUES(?,?,?,?);");
            $error+=($q->execute(array($id,$no,$searchna,$todate->format('Y-m-d H:i:s')))&&$q->rowCount()==1)?0:1;
        }
        foreach($terms as $condition=>$term){
            for($k=0;$k<count($term[0]);$k++){
                $q=$db->prepare("INSERT INTO t21term(id,search_no,no,condition_,na,val,typ)VALUES(?,?,?,?,?,?,?);");
                $error+=($q->execute(array($id,$no,$i,$condition,$term[0][$k],$term[1][$k],$term[2][$k]))&&$q->rowCount()==1)?0:1;
                $i++;
            }
        }
        if($error){
            $json['msg']="データベースエラーにより".$searchna."を保存できませんでした。";
            $db->rollBack();
        }else{
            $json['msg']="ok";
            $db->commit();
        }
    }
}
header('Content-type: application/json');
echo json_encode($json);