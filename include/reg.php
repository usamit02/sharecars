<FORM id="reg" ACTION="<?php echo $_SERVER['SCRIPT_NAME'];?>" METHOD="post" ENCTYPE="multipart/form-data">
  <?php
$prerow=0;$ct=0;
$updateid=(isset($_GET['id']))?htmlspecialchars($_GET['id'], ENT_QUOTES):0;
if($updateid){
    $r=$db->query("SELECT X(latlng) AS lng,Y(latlng) AS lat FROM $table WHERE id=$updateid")->fetch();
    $lat=$r['lat'];
    $lng=$r['lng'];
    $r=$db->query("SELECT * FROM $table WHERE id=$updateid")->fetch();
}else if($table=="t31car"){
    $rs=$db->query("SELECT X(latlng) AS lng,Y(latlng) AS lat FROM t14member WHERE id=$id");
    if($r=$rs->fetch()){
        $lat=$r['lat'];
        $lng=$r['lng'];
    }
}
foreach($grouporder as $i => $gp){
    if($grouprow[$gp]!=$prerow){
        echo "<div class='row'>";
        $prerow=$grouprow[$gp];
    }
    echo "<fieldset><div id='$gp'><h1>".$grouph1[$gp]."</h1>";
    $sql="SELECT na,typ,name,unit,para,tbl,def,gp FROM m".$table."reg where gp='$gp'";
    $tags=$db->query($sql);
    while ($tag=$tags->fetch()) {
        if($updateid){
            $prevalue=(($tag['gp']=='user'||$tag['gp']=='insurance')&&$r[$tag['na']]==0)?"":$r[$tag['na']];
        }else{
            //$data[$tag['na']]=$tag['def'];//開発のみ
            $prevalue=isset($data[$tag['na']])?$data[$tag['na']]:"";
        }
        echo "<div>";
        switch ($tag['typ']) {
            case "select":
                echo "<select id='".$tag['na']."' value='$prevalue' name='reg[".$tag['na']."]'".$tag['para'].">";
                $options=$db->query("SELECT id,na FROM ".$tag['tbl']);
                while ($option=$options->fetch()) {
                    $selected=($option['id']==$prevalue)?" selected":"";
                    echo "<option value='".$option['id']."'$selected>".$option['na']."</option>";
            }
            echo"</select>";
            break;
        case 'textarea':
            echo "<textarea id='".$tag['na']."' name='reg[".$tag['na']."]' ".$tag['para'].">$prevalue</".$tag['typ'].">";
            break;
        case "checkbox":
            $checked=($prevalue)?" checked":"";
            echo "<input type='hidden' name='reg[".$tag['na']."]' value='0'>";
            echo "<input type='checkbox' id='".$tag['na']."' name='reg[".$tag['na']."]' value='1'$checked ".$tag['para'].">";
            break;
        case "date":
            $date=new DateTime($prevalue);
            $prevalue=$date->format('Y-m-d');
            default:
                echo "<input type='".$tag['typ']."' id='".$tag['na']."' name='reg[".$tag['na']."]' value='$prevalue' ".$tag['para'].">";
        }
        echo "<label for='".$tag['na']."'>".$tag['name']."</label><div>".$tag['unit']."</div></div>";
    }
    echo "</div></fieldset>";
    if(!isset($grouporder[$i+1])||$grouprow[$grouporder[$i+1]]!=$prerow){
        echo "</div>";
    }
}
?>
    <input type="hidden" name="key" value="<?php echo $key; ?>" />
    <input type="hidden" name="id" value="<?php echo $updateid; ?>" />
    <div id="post">
      <button type="button" id="posting">登録</button>
      <?php
if($updateid){
    // echo '<button type="button" onclick="location.href='."'"."carreg2.php?carid=$updateid"."'".'">変更せず次へ</button>';
    echo "<button type='button' onclick='noChange($updateid)'>変更せず次へ</button>";
}
?>
    </div>
</FORM>
<div id="message">
  <div id="error"><font color="#ff0000"><?php echo $error; ?></font></div>
  <div id="message"><font color="#0000ff"><?php echo $message; ?></font></div>
</div>