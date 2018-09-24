(function ($) {
  var wx, wy;    // ウインドウの左上座標
  $.pop = function () {
    if ($("#pop_overlay")[0]) return false;		//新しくモーダルウィンドウを起動しない (防止策1)
    $("body").append('<div id="pop_overlay"></div>');
    $("#pop_overlay").fadeIn();
    $("#pop").fadeIn();
    //	$("#pop_close").off('click');
    //	$("#pop_close").on('click', function () {
    centeringModalSyncer();
    //});
  }
  $("#pop_title").off('mousedown');
  $('#pop_title').mousedown(function (e) {
    var mx = e.pageX;
    var my = e.pageY;
    $(document).on('mousemove.pop', function (e) {
      wx += e.pageX - mx;
      wy += e.pageY - my;
      $('#pop').css({ top: wy, left: wx });
      mx = e.pageX;
      my = e.pageY;
      return false;
    }).one('mouseup', function (e) {
      $(document).off('mousemove.pop');
    });
    return false;
  });

  $(window).resize(centeringModalSyncer);
  function centeringModalSyncer() {
    if (window.innerWidth > 1000) {
      wx = ($(window).outerWidth() - $("#pop").outerWidth()) / 2;
      wy = ($(window).outerHeight() - $("#pop").outerHeight()) / 2;
      //		console.log($(window).outerHeight() + ':' + $("#pop").outerHeight());
      $("#pop").css({ top: wy, left: wx });
    }
  }
})(jQuery);
function popClose() {
  $("#pop,#pop_overlay").fadeOut(function () {
    $('#pop_overlay').remove();
    $('#pop div:not(#pop_close,#pop_title)').empty();
  });
}