<?php
session_start();
require_once($_SERVER['DOCUMENT_ROOT']."/include/db.php");
if (isset($_GET['city_cd'])) {//クエリパラメータにcity_cdあるときはcity.php、ないときはfavoriteから
    foreach($_GET['city_cd'] as $i=>$v){
        $getwhere[]=$v;
    }
    $table="q31carindex";
}else{
    $getwhere=array('id'=>$_SESSION['id']);
    $table="q22favorite";
}
$jsonwhere=json_encode($getwhere);
?>
  <!--nobanner-->
  <HTML>

  <HEAD>
    <META HTTP-EQUIV='Content-Type' CONTENT='text/html;charset=UTF-8'>
    <meta name="viewport" content="width=640">
    <title>クルマ一覧</title>
    <link rel="stylesheet" href="css/carindex.css">
    <link rel="stylesheet" href="css/calendar.css">
    <link rel="stylesheet" href="css/pop.css">
  </HEAD>

  <BODY>
    <?php
include_once($_SERVER['DOCUMENT_ROOT'].'/include/head.php');
include_once($_SERVER['DOCUMENT_ROOT'].'/include/term.php');
echo"<div id='index'></div>";
include_once($_SERVER['DOCUMENT_ROOT'].'/include/foot.php');
?>
      <script type="text/javascript" src="js/jquery-3.1.1.min.js"></script>
      <script type="text/javascript" src="js/favorite.js"></script>
      <script type="text/javascript" src="js/pop.js"></script>
      <script type="text/javascript" src="js/calendar.js"></script>
      <script type="text/javascript" src="js/schedule.js"></script>
      <script type="text/javascript" src="js/reserv.js"></script>
      <script type="text/javascript" src="js/search.js"></script>
      <script type="text/javascript">
        var id = <?php if(isset($id)){echo $id;}?>;
        var table = '<?php if(isset($table)){echo $table;}else{echo 0;}?>';
        var order = (table == 'q22favorite') ? "no" : "good_p";
        var page = 1;
        var getwhere = <?php echo $jsonwhere;?>;

        function changeback(Myid) { //チェックボックスの背景色変更
          if (Myid.checked == true) {
            Myid.parentNode.style.color = 'black';
          } else {
            Myid.parentNode.style.color = 'lightgray'; //背景色
          }
        }
        $(document).ready(function() {
          setPointMarker();
        });

        function setPointMarker() {
          $.each(getwhere, function(key, value) {
            if (!where.length) {
              where[0] = [];
              where[1] = [];
              where[2] = [];
            }
            if (key == 'id') {
              where[0].push(key);
            } else {
              where[0].push('city_cd');
            }
            where[1].push(value);
            where[2].push('checkbox');
          });
          read();
        }

        function read(del, updown) {
          var mobile = (window.innerWidth < 1000) ? 1 : 0;
          $.ajax({
            url: 'json/carindex.php',
            type: 'post',
            dataType: 'text',
            data: {
              'where': JSON.stringify(where),
              'order': order,
              'page': page,
              'table': table,
              'reservA': reservationA,
              'reservZ': reservationZ,
              'delete': del,
              'updown': updown,
              'mobile': mobile
            },
            error: function() {
              alert('一覧の作成に失敗しました。')
            },
            success: function(html) {
              $("#index").empty();
              $("#index").append(html);
            }
          });
        }

        function pager(p) {
          page = p;
          read();
        }

        function setOrder(o, desc) {
          var a = (desc) ? " DESC" : "";
          order = o + a;
          read();
        }
      </script>
  </BODY>

  </HTML>