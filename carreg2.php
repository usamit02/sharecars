<?php
session_start();
require_once($_SERVER['DOCUMENT_ROOT']."/include/db.php");
include_once($_SERVER['DOCUMENT_ROOT'].'/include/head.php');
$error="";
$message="";
if (isset($_GET['carid'])) {
    $carid = htmlspecialchars($_GET['carid'], ENT_QUOTES);
    if($id!=$db->query("SELECT owner_id FROM t31car WHERE id=$carid;")->fetchColumn()){
        echo"オーナーとしてログインしてください。";
        die;
    }
} else {
    echo '不正なアクセスです';
    die;
}
?>
  <!DOCTYPE html>
  <html>

  <HEAD>
    <META HTTP-EQUIV='Content-Type' CONTENT='text/html;charset=UTF-8'>
    <meta name="viewport" content="width=640">
    <title>写真登録</title>
    <link rel='stylesheet' href='css/carreg2.css'>
  </HEAD>

  <body>

    <canvas style="display:none"></canvas>
    <div id='main'>
      <?php
$sjpgs=glob("img/$carid/s-*.*");
echo"<div id='s-area'>";
$ii=0;
foreach ($sjpgs as $i => $src) {
    echo "<div><img id='s-img$i' class='s-img' src='$src' onclick='clickSimg($i)'><input type='file' accept='image/*' size='70' capture onchange='inputFile(this,$i)'></div>";
    $ii++;
    }
    //echo"<div><img id='s-img$ii' width='150' onclick='clickSimg($ii)'><input type='file' accept='image/*' size='70' capture onchange='inputFile(this,$ii)'></div>";
    echo"<div><button onclick=next(this,".$ii.")>写真を追加</button></div></div>";
    echo"<div id='end'><button onclick='location.href=".'"'."carreg3.php?id=$carid".'"'."'>アップロードを終了して次へ</button></div>";
    $jpgs=str_replace('s-', '', $sjpgs);
    
    ?>
        <script type="text/javascript" src="js/jquery-3.1.1.min.js"></script>
        <script type="text/javascript">
          var captureForm = document.querySelector('#inputFile');
          var canvas = document.querySelector('canvas');
          var ctx = canvas.getContext('2d');
          var displayimgid = "img0";

          function inputFile(obj, id) {
            var file = obj.files[0];
            var image = new Image();
            var reader = new FileReader();
            if (file.type.match(/image.*/)) {
              reader.onloadend = function() {
                image.onload = function() {
                  //var id = this.id.replace("s-img", "");
                  ctx.clearRect(0, 0, canvas.width, canvas.height);
                  var h = 112;
                  var w = image.width * (h / image.height);
                  canvas.width = w;
                  canvas.height = h;
                  ctx.drawImage(image, 0, 0, w, h);
                  $(obj).prev('img').attr('src', canvas.toDataURL("image/jpeg", 0.5)); //圧縮
                  if ($(obj).next('button').length == 0) {
                    $(obj).parent('div').append("<button onclick='upload(" + id + ")' >アップロード</button>")
                  }
                  ctx.clearRect(0, 0, canvas.width, canvas.height);
                  var h = 480;
                  var w = image.width * (h / image.height);
                  canvas.width = w;
                  canvas.height = h;
                  ctx.drawImage(image, 0, 0, w, h);
                  $('#img' + id).attr('src', canvas.toDataURL("image/jpeg", 1.0));
                }
                image.src = reader.result;
              }
              reader.readAsDataURL(file);
            }
          }

          function clickSimg(id) {
            $("#" + displayimgid).css('display', "none"); //現在表示されている大写真を非表示
            $("#img" + id).show();
            displayimgid = "img" + id;
          }

          function upload(id) {
            $("#message").html("アップロード中です・・・");
            document.body.style.cursor = 'wait';
            var base64ToBlob = function(base64) { // 引数のBase64の文字列をBlob形式にしている
              var base64Data = base64.split(',')[1], // Data URLからBase64のデータ部分のみを取得
                data = window.atob(base64Data), // base64形式の文字列をデコード
                buff = new ArrayBuffer(data.length),
                arr = new Uint8Array(buff),
                blob, i, dataLen;
              for (i = 0, dataLen = data.length; i < dataLen; i++) { // blobの生成
                arr[i] = data.charCodeAt(i);
              }
              blob = new Blob([arr], {
                type: 'image/jpeg'
              });
              return blob;
            }
            var formData = new FormData();
            var blob;
            formData.append("carid", "<?php echo $carid;?>");
            formData.append("id", id);
            blob = base64ToBlob($('#s-img' + id).attr('src'));
            formData.append('carimg[]', blob);
            blob = base64ToBlob($('#img' + id).attr('src'));
            formData.append('carimg[]', blob);
            $.ajax({
              type: 'POST',
              url: '/json/upload.php',
              data: formData,
              contentType: false,
              processData: false,
              success: function(json, dataType) {
                txt = (json.msg = 'ok') ? "<div>" + (id + 1) + "枚目のアップロードに成功しました。</div>" : "<div>" + id + "枚目" + json.msg + "</div>";
                $("#message").html(txt);
                document.body.style.cursor = 'auto';
              },
              error: function(XMLHttpRequest, textStatus, errorThrown) {
                $("#message").html("<div>" + (id + 1) + "枚目のアップロードに失敗しました。</div>");
                document.body.style.cursor = 'auto';
              }
            });
          }

          function next(obj, id) {
            $('#area').append("<img id='img" + id + "' style='display:none'>");
            $(obj).before("<div><img id='s-img" + id + "' class='s-img' onclick='clickSimg(" + id + ")'><input type='file' accept='image/*' size='70' capture onchange='inputFile(this," + id + ")'></div>");
            $(obj).attr('onclick', "next(this," + (id + 1) + ")");
          }
        </script>
        <?php
    echo"<div id='area'>";
    $ii=0;
    foreach ($jpgs as $i => $src) {
    if($i){
    echo "<img id='img$i' src='$src' style='display:none'>";
    }else{
    echo "<img id='img$i' src='$src'>";
    }
    $ii++;
    }
    if($ii){echo"<img id='img$ii' style='display:none'>";}else{echo"<img id='img$ii'>";}
    echo"</div></div><div id='message'></div>";
    include_once($_SERVER['DOCUMENT_ROOT'].'/include/foot.php'); ?>
  </body>

  </html>