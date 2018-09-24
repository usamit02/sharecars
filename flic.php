<html>

<head>
  <META HTTP-EQUIV='Content-Type' CONTENT='text/html;charset=UTF-8'>
  <meta name="viewport" content="width=640">
  <style>
    .hour {
      width: 8vw;
      height: 50px;
      border: 1px solid;
    }
  </style>
</head>

<body>
  状態
  <input id='state' type='text'> ID
  <input id='hourid' type='text'> X
  <input id='dx' type='text'>
  <div style='width:100vw;height:20vh;'></div>
  <div style="display:flex;justify-content:center;">
    <?php
for($i=0;$i<10;$i++){
    echo"<div id='hour$i' class='hour'></div>";
}

?>


  </div>

  <script src="js/jquery-3.1.1.min.js"></script>

  <script>
    $(function() {
      $('.hour').on('touchstart', function() {
        sx = event.changedTouches[0].pageX; //フリック開始時のX軸の座標
        $('#state').val('touchstart');
        $('#hourid').val(this.id);
        $(document).on('touchmove.noScroll', function(e) {
          e.preventDefault();
        });
      });
      $('.hour').on('touchmove', function(e) {
        e.preventDefault();
        ex = event.changedTouches[0].pageX; //フリック終了時のX軸の座標
        dx = Math.round(sx - ex); //フリック開始時の座標-終了時の座標=フリックの移動距離
        $('#state').val('touchmove');
        $('#hourid').val(this.id);
        $('#dx').val(dx);
      });
      $('.hour').on('touchend', function(e) {
        var drec = (dx > 0) ? 1 : -1;
        $('#state').val('touchend');
        $('#hourid').val(this.id);
        $('#dx').val(dx);
      });
    });
  </script>
</body>

</html>