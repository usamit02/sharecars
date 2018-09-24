<?php
echo'<div id="pop">';
echo'<div id="pop_title"></div>';
echo'<div id="where"></div>';
echo'<div id="calendar"></div>';
echo'<div id="plans"></div>';
echo'<div id="timetable"></div>';
echo"<div id='pop_close'>";
echo'<button id="Close" onclick="Close()">設定</button>';
echo'<button onclick="Cancel()">閉じる</button>';
echo'</div></div>';
echo"<div id='terms'>";
echo"<div><button id='daytime' onclick='search(".'"daytime"'.")'>条件-日時</button>";
echo"<button id='basic' onclick='search(".'"basic"'.")'>条件-基本</button>";
echo"<button id='insurance' onclick='search(".'"insurance"'.")'>条件-保険</button>";
echo"<button id='equip' onclick='search(".'"equip"'.")'>条件-装備</button></div>";
echo"<div><input type='text' id='search_na' value='条件1'>";
echo"<button id='save' onclick='termSave(0)'>現在の条件を保存</button>";
echo"<select id='termLoad'>";
$rs=$db->query("SELECT no,na FROM t21search WHERE id=$id ORDER BY no;");
echo"<option value='0'>全消去</option>";
while($option=$rs->fetch()){
    echo"<option value='".$option['no']."'>".$option['na']."</option>";
}
echo"</select>";
echo"<button id='delete' onclick='termDelete()'>削除</button></div>";
echo"</div>";