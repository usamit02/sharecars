<?php
require_once $_SERVER['DOCUMENT_ROOT'].'/include/db.php';
$gp=htmlspecialchars($_GET['group'], ENT_QUOTES);
$html="";
$rs=$db->query("SELECT na,typ,name,unit,para,tbl,def FROM mt31carreg where gp LIKE '$gp%';");
while($r=$rs->fetch()){
    $na=$r['na'];
    $tbl=$r['tbl'];
    $name=$r['name'];
    $unit=$r['unit'];
    $html.="<div style='margin-top:10px;'><label for='$na' style='margin-right:25px'>$name</label>";
    switch($r['typ']){
        case "checkbox":
            $html.="<input type='checkbox' class='$na' value='1'>";
            break;
        case "select":
            $rrs=$db->query("SELECT id,na FROM $tbl;");
            while($rr=$rrs->fetch()){
                $selna=$rr['na'];
                $i=$rr['id'];
                $html.="<label class='check'><input type='checkbox' class='$na' value='$i'>$selna</label>";
        }
        break;
    case "number":
        $unit=str_replace("以上","",$unit);
        $unit=str_replace("以下","",$unit);
        $html.= "<input type='number' class='$na"."1'><label>$unit"."以上"."</label><input type='number' class='$na"."2'><label>$unit"."以下</label>";
        break;
    default:
        $html.="<input type='text' class='$na'>";
}
$html.="</div></div>";
}
header('Content-type: text/plain; charset=UTF-8');
echo $html;