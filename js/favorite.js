function favorite(id, carid) {
  $.ajax({
    url: 'json/favorite.php?id=' + id + '&carid=' + carid,
    type: 'GET',
    async: false,
    cache: false,
    dataType: 'json',
    timeout: 1000,
    error: function () {
      alert('ajax通信に失敗しました。');
    },
    success: function (json) {
      $(json).each(function () {
        if (json.length == 0) {
          alert("json取得に失敗しました。");
        } else {
          if (!confirm(json)) {
            window.location.href = "carindex.php";
          };
        }
      })
    }
  })
}