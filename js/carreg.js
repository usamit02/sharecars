$(function () {
  $('#posting').click(function () {
    $('#message').html('処理中です・・・');
    var addr = "error";
    codeLatlng(function (lat, lng, addr) {
      if (addr.substr(0, 4) == '日本、〒') {
        $.ajax({
          url: 'json/citycode.php?code=' + addr.substr(4, 8),
          type: 'GET',
          async: false,
          cache: false,
          dataType: 'json',
          timeout: 1000,
          error: function () {
            $('#message').html("郵便番号が取得できません。地図を少しずらして再度登録ボタンを押してください。");
            setTimeout(function () {
              $('#message').html('');
            }, 3000);
          },
          success: function (json) {
            $(json).each(function () {
              if (json.length == 0) {
                $('#message').html("市町村コートが取得できません。地図を少しずらして再度登録ボタンを押してください。");
                setTimeout(function () {
                  $('#message').html('');
                }, 3000);
              } else {
                $('#city_cd').val($(this).attr('city_cd'));
                $('#latlng').val('POINT(' + lng + ' ' + lat + ')');
                $('#message').html('');
                $.getScript("js/beforesend.js", function () {
                  var confirm_message = beforesend();
                  if (confirm_message) {
                    confirm_message += "引渡場所:" + addr;
                    if (confirm(confirm_message)) {
                      $('#main').submit();
                    }
                  }
                })
              }
            });
          }
        });
      } else {
        alert(addr + 'n\道路、河川上など住所が取得できない場所です。地図を少しずらして再度登録ボタンを押してください。')
      }
    })
  })
});