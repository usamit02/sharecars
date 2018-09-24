<?php
session_start();
require_once($_SERVER['DOCUMENT_ROOT']."/include/db.php");
$owner=$_POST['owner'];
$good=$_POST['good'];
$yourid=$_POST['yourid'];
if($owner==1){
    $select=($good)?"owner_good_p AS p":"owner_bad_p AS p";
    $having="AND t17eval.owner=1";
}else if($owner==0){
    $select=($good)?"user_good_p AS p":"user_bad_p AS p";
    $having="AND t17eval.owner=0";
}else{
    $select=($good)?"good_p AS p":"bad_p AS p";
    $having="";
}
$point=$db->query("SELECT $select FROM q17eval2 WHERE id=$yourid;")->fetch();
$txt=($good)?"良い":"悪い";
$html="<h3>$txt:".$point['p']."p</h3><div>";
$where=($good)?"t17eval.id>=0":"t17eval.id<0";
$group=($owner==-1)?"":",t17eval.owner";
$rs=$db->query("SELECT count(t17eval.id) AS cnt,na FROM t17eval JOIN mt17eval ON t17eval.id=mt17eval.id GROUP BY t17eval.id,t17eval.get_id$group HAVING $where AND t17eval.get_id=$yourid $having ORDER BY t17eval.id;");
while($r=$rs->fetch()){
    $html.="<div>".$r['na'].":".$r['cnt']."件</div>";
}
$where.=($owner==-1)?"":" AND owner=$owner";
$p=$_POST['page'];
$L=($p-1)*10;
$rs=$db->query("SELECT mt17eval.na AS reason,t17eval.reg_day AS reg_day,t17eval.txt AS txt,owner,set_id,t14member.na AS na,img_url FROM t17eval JOIN mt17eval ON t17eval.id=mt17eval.id JOIN t14member ON t17eval.set_id=t14member.id WHERE get_id=$yourid AND $where ORDER BY t17eval.reg_day LIMIT $L,10");
$html.= '<div><table><tr><th>日時</th><th>内容</th><th>評価者</th></tr>';
while ($r=$rs->fetch()) {
    $html.= "<tr><td>".$r['reg_day']."<br>".$r['na']."さん<br>".$r['reason']."</td><td>".$r['txt']."</td>";
    $html.="<td><a href='you.php?id=".$r['set_id']."'><img class='indeximg' src='".$r['img_url']."'></a></td></tr>";
}
$html.= '</table></div>';
//ページング
$maxp=$db->query("SELECT count(id) AS maxid FROM t17eval WHERE $where;")->fetchcolumn();
$strp='<div class="pager">';
for ($i=1; $i<=ceil($maxp/10); $i++) {
    $strp.=($i==$p)?' '.$i.' ':"<button onclick='pager($i,$good)'>$i</button>";
}
$html.= $strp."</div>";
header('Content-type: text/plain; charset=UTF-8');
echo $html;