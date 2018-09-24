<?php
if (isset($_POST['carid'])&&isset($_POST['id'])) {
    $carid = htmlspecialchars($_POST['carid'], ENT_QUOTES);
    $id = htmlspecialchars($_POST['id'], ENT_QUOTES);
    foreach($_FILES["carimg"]["tmp_name"] as $key=>$file){
        if (is_uploaded_file($_FILES["carimg"]["tmp_name"][$key])) {
            $filename=($key)?"$id":"s-$id";
            if (move_uploaded_file($file, $_SERVER['DOCUMENT_ROOT']."/img/$carid/$filename.jpg")) {
                $json['msg']="ok";
            } else {
                $json['msg'] = "ファイルの書き込みに失敗しました。";
            }
            
        } else {
            $json['msg']= "ファイルのアップロードに失敗しました。";
        }
    }
} else {
    $json['msg']= "クルマが選択されていません";
}
header('Content-type: application/json');
echo json_encode($json);
?>