<html>

<head>
  <link rel="stylesheet" href="css/rangeslider.css">
  <style>
  .ud{
     font-size:5em;
     
  }
  
  </style>
</head>

<body>
  <div style="display:flex;">
    <div style="display:flex;">
      <input class='updown' type='text'>
      <div class='ud' >▲▼</div>
    </div>
    <div style="display:flex;">
      <input class='updown' type='text'>
      <div class='ud'>▲▼</div>
    </div>
    <div style="display:flex;">
      <input class='updown' type='text'>
      <div class='ud'>▲▼</div>
    </div>
  </div>
  <input type="range" min="10" max="1000" step="10" value="300">
  <script src="js/jquery-3.1.1.min.js"></script>

  <script>
    $(function() {
      $('.ud').on('touchstart', function() {
        sy = event.changedTouches[0].pageY; //フリック開始時のX軸の座標
         
        $(document).on('touchmove.noScroll', function(e) {
          e.preventDefault();
        });
      });
      $('.ud').on('touchmove', function(e) {
        ey = event.changedTouches[0].pageY; //フリック終了時のX軸の座標
        dy = Math.round(sy - ey); //フリック開始時の座標-終了時の座標=フリックの移動距離
      });
      $('.ud').on('touchend', function(e) {
        var that = this;
        var id = setInterval(function() {
          $(that).prev().val(Number($(that).prev().val()) + dy / 100);
        }, 40);
          $(document).off('.noScroll');
          setTimeout(function() {
          clearInterval(id);
          }, Math.abs(dy*5));
      });
     
      $('.ud').mousedown(function(e) {
        var sy = e.clientY;
        var vy = 0;
        var f = this;
        var id = setInterval(function() {
          $(f).prev().val(Number($(f).prev().val()) + vy);
        }, 50);
          setTimeout(function() {
         //   clearInterval(id);
          }, 500);

        $(document).on('mousemove.ud', function(e) {
          vy = Math.floor((e.clientY - sy));
          return false;
        }).on('mouseup', function() {
       });
       });


    });
  </script>
  <script src="js/rangeslider.js"></script>
</body>

</html>