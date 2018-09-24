var resvA = new Array();//rv=resve
var resvZ = new Array();
var rvA = [-1, -1], rvZ = [-1, -1];
var reservationA,
  reservationZ;
var cars = new Array();
function reservInit() {
  for (var j = 0; j < resvA.length; j++) {
    var d = new Date(resvA[j]);
    var a = Math.floor((resvA[j] - toDay) / 86400000);
    A = new Date(resvA[j]).getHours();
    Z = new Date(resvZ[j]).getHours();
    for (var i = 0; i < 24; i++) {
      if (A <= i && i <= Z) {
        $('#d' + a + ' div').eq(i).css('background-color', 'lime');
      }
    }
  }
  var html = "<div id='reservcars'>";
  for (var i = 0; i < cars.length; i++) {
    html += "<label>" + cars[i].na + "</label><div></div>";
  }
  html += "</div>";
  $('#calendar').append(html);
  setReservation();
  rvTimetable();
  hourEvent();
  $('#saveplan').on('click', function () {
    var a = (currentPlan > 1) ? currentPlan - 1 : 99;
    for (var i = 0; i < 2; i++) {
      setPlan(a, i - 2, rvA[i], rvZ[i], $("#planna").val());
    }
  });
  $('#delplan').on('click', function () {
    setPlan(-1, -1, 0, 0, $("#planna").val());
    setCurrentPlan($('.planhours:first'), -1);
  });
  reservEvent();
}
function hourEvent() {
  $('.hour').off('mousedown touchstart mousemove touchmove dragstart taphold');
  $('.hour').on('taphold', function (e) {
    e.preventDefault();
  });
  $('.hour').on('mousedown touchstart', function (e) {
    var a = parseInt($(this).attr('id').replace('hour', ''));
    if (e.handleObj.type == 'touchstart') {
      var sx = event.changedTouches[0].pageX; //フリック開始時のX軸の座標
      $('#sx').val(Math.ceil(sx));
      $('#a').val(a);
    }
    if ((rvA[0] <= a && a <= rvZ[0]) || (rvA[1] <= a && a <= rvZ[1])) {
      var handle = (rvA[0] <= a && a <= rvZ[0]) ? 0 : 1;
      if ((rvA[0] == a || rvZ[0] == a || rvA[1] == a || rvZ[1] == a)) {
        $(this).css('color', 'red');
      } else {
        for (i = rvA[handle]; i <= rvZ[handle]; i++) {
          $("#hour" + i).css('color', 'red');
        }
      }
    } else {
      if (rvA[0] > -1 && rvZ[0] > -1 && rvA[1] > -1 && rvZ[1] > -1) {
        alert("予約期間は２カ所までしか設定できません。");
        a = -1;
        $('.hour').off('mousemove touchmove');
      } else {
        if (rvA[1] > -1 || rvZ[1] > -1) {
          rvA[0] = a;
          rvZ[0] = a;
        } else {
          rvA[1] = a;
          rvZ[1] = a;
        }
        handle = rvTimetable(handle);
      }
    }
    $('.hour').on('mousemove touchmove', function (e) {
      e.preventDefault();
      if (e.handleObj.type == 'mousemove') {
        var b = parseInt($(this).attr('id').replace('hour', ''));
        if (a != b && a > -1) {
          hourmove();
        }
      } else {
        var ex = event.changedTouches[0].pageX; //フリック終了時のX軸の座標
        if (ex - sx > 25 && a < 23 || sx - ex > 25 && a > 0) {
          b = (ex > sx) ? a + 1 : a - 1;
          hourmove();
          sx = (ex > sx) ? sx + 25 : sx - 25;
        }
        $('#sx').val(Math.ceil(sx));
        $('#ex').val(Math.ceil(ex));
        $('#dx').val(Math.ceil(sx - ex));
        $('#a').val(a);
        $('#b').val(b);
        $('#ab').val(ab);
      }
      function hourmove() {
        var ab = b - a;
        if (rvZ[0] == a) {
          rvZ[0] = b;
          $('#hour' + b).css('color', 'red');
        } else if (rvA[0] == a) {
          rvA[0] = b;
          $('#hour' + b).css('color', 'red');
        } else if (rvZ[1] == a) {
          rvZ[1] = b;
          $('#hour' + b).css('color', 'red');
        } else if (rvA[1] == a) {
          rvA[1] = b;
          $('#hour' + b).css('color', 'red');
        } else if (rvA[handle] < 24 && (rvA[handle] + ab) > -1 && (rvZ[handle] + ab) < 24 || rvA[handle] > 23 && (rvA[handle] + ab) > 23 && (rvZ[handle] + ab) < 48) {
          rvA[handle] += ab;
          rvZ[handle] += ab;
        }
        handle = rvTimetable(handle);
        a = b;
      }
    });
    $(".hour").on('dragstart', function () {
      $('.hour').off('mousemove touchmove');
    })
    $(document).off('mouseup touchend');
    $(document).on('mouseup touchend', function (e) {
      $('.hour').off('mousemove touchmove dragstart');
      $('.hour').css('color', 'black');
    });
  });
}
function reservEvent() {
  var hover;
  $('.calendar td:not(.non)').unbind("mouseenter").unbind("mouseleave");
  $('.calendar td:not(.non)').off('mousedown touchstart mousemove touchmove mouseup');
  $('.calendar td:not(.non)').hover(function (e) {
    var a = parseInt($(this).attr('id').replace('d', ''));
    if (a != hover) {
      if (rvZ[0] == 23 && rvA[1] == 24) {//予約が翌日にまたがる場合
        if (!($('#d' + (a + 1))[0])) {//翌日が存在しない場合、１日不可の翌日を作る
          for (var i = 0; i < 24; i++) {
            ttX[a + 1][i] = 1;
          }
        }
      }
      rvTimetable(0, a);
    }
    hover = a;
  });
  $('.calendar td:not(.non)').on('mousedown touchstart', function (e) {
    var a = parseInt($(this).attr('id').replace('d', ''));
    if (rvZ[0] == 23 && rvA[1] == 24) {
      setReserv2(a, a);
    } else {
      setReserv(a);
    }
    var bb;
    $('.calendar td:not(.non)').on('mousemove touchmove', function (e) {
      e.preventDefault();
      var b = parseInt($(this).attr('id').replace('d', ''));
      if (a < b && bb != b) {
        if (b == bb + 1 && rvZ[0] == 23 && rvA[1] == 24) {
          if (setReserv2(a, b) === false) {
            $('.calendar td:not(.non)').off('mousemove touchmove');
          }
        } else {
          setReserv(b);
        }
      }
      bb = b;
    }).one('mouseup', function (e) {
      if (rvZ[0] == 23 && rvA[1] == 24) {
        var b = parseInt($(this).attr('id').replace('d', ''));
        if (a < b) {
          if (setReserv2(a, b) == false) {
            for (i = a; i <= b; i++) {
              for (j = 0; j < 2; j++) {
                var f = resvA[i][j];
                //    resvA[i][j] = -1;
                //   resvZ[i][j] = -1;
              }
              // paintReserv(i);
            }
          };
        }
      }
      $('.calendar td:not(.non)').off('mousemove touchmove mouseup');
    });
  });
}

function rvTimetable(handle, a) {
  for (i = 0; i < 2; i++) {
    if (rvA[i] > rvZ[i]) {
      rvA[i] = -1;
      rvZ[i] = -1;
    } else {
      if (rvA[0] > -1 && rvZ[0] > -1 && rvA[1] > -1 && rvZ[1] > -1) {
        j = (i) ? 0 : 1;
        if (rvA[i] == rvZ[j]) {
          rvA[0] = rvA[j];
          rvZ[0] = rvZ[i];
          rvA[1] = -1;
          rvZ[1] = -1;
          handle = 0;
        }
      }
    }
  }
  if (rvA[0] > rvA[1] && rvA[1] > -1 || rvA[0] < 0 && rvA[1] > -1) {
    var A = rvA[0];
    rvA[0] = rvA[1];
    rvA[1] = A;
    A = rvZ[0];
    rvZ[0] = rvZ[1];
    rvZ[1] = A;
  }
  var ii = 24;
  if (rvA[0] == 0 && rvZ[1] == 23 || $('#timetable > div').length < 3 && rvA[1] == 24) {
    var html = "<div><div style='width:50px;'>翌日</div>";
    for (var i = 24; i < 48; i++) {
      html += "<div class='hour' id='hour" + i + "'></div>";
    }
    html += "</div><div><button style='widht:50px'>翌日</button>";
    for (i = 24; i < 48; i++) {
      html += "<div class='hourlabel' id='hourlabel" + i + "'>" + (i - 24) + ":00</div>";
    }
    html += "</div>";
    $('#timetable').append(html);
    if (rvA[1] != 24) {
      var A = rvA[0];
      var Z = rvZ[0];
      rvA[0] = rvA[1];
      rvZ[0] = rvZ[1];
      rvA[1] = A + 24;
      rvZ[1] = Z + 24;
    }
    hourEvent();
  } else if ($('#timetable > div').length > 2 && (rvA[1] != 24 || rvZ[0] != 23)) {
    $('#timetable > div').eq(3).remove();
    $('#timetable > div').eq(2).remove();
    rvA[1] = -1;
    rvZ[1] = -1;
  }
  var ii = (rvA[1] > 23) ? 48 : 24;
  for (i = 0; i < ii; i++) {
    if (rvA[0] == i || rvA[1] == i) {
      $("#hourlabel" + i).css('color', 'black');
      $("#hour" + i).html('');
      var txt = (i == 0 && rvZ[1] == 23) ? '前日' : '＜';
      $("#hour" + i).html(txt);
    } else if (rvZ[0] == i || rvZ[1] == i) {
      $("#hourlabel" + i).css('color', 'black');
      var txt = (i == 23 && rvA[0] == 0) ? '翌日' : '＞';
      $("#hour" + i).html(txt);
    } else if (i % 6 == 0) {
      $("#hourlabel" + i).css('color', 'gray');
      $("#hour" + i).html('');
    } else {
      $('#hourlabel' + i).css('color', 'transparent');
      $("#hour" + i).html('');
    }
    if (a > -1 && ttX[a + Math.floor(i / 24)][i % 24]) {
      if (rvA[0] <= i && i <= rvZ[0] || rvA[1] <= i && i <= rvZ[1]) {
        $("#hour" + i).css('background-color', 'red');
      } else {
        $("#hour" + i).css('background-color', 'gray');
      }
    } else {
      if (rvA[0] <= i && i <= rvZ[0] || rvA[1] <= i && i <= rvZ[1]) {
        $("#hour" + i).css('background-color', 'lime');
      } else {
        $("#hour" + i).css('background-color', 'white');
      }
    }
  }
  console.log(rvA[0] + ":" + rvZ[0] + ":" + rvA[1] + ":" + rvZ[1])
  return handle;
}
function setReservX(a) {
  var f = true, A, Z;
  for (var i = 0; i < scheA.length; i++) {
    if (Math.floor((scheA[i] - toDay) / 86400000) == a) {
      for (i = 0; i < 2; i++) {
        if (rvA[i] > -1) {
          A = new Date(scheA[i]).getHours();
          Z = new Date(scheZ[i]).getHours();
          if (A <= rvA[i] && Z >= rvA[i] || rvA[i] <= A && rvZ[i] >= A) {
            f = false;
            break;
          }
        }
      }
    }
    if (!f) { break; }
  }
}

function setReserv(a) {
  var f = true;
  for (var i = 0; i < 2; i++) {
    if (rvA[i] > -1) {
      for (var j = rvA[i]; j <= rvZ[i]; j++) {
        if (ttX[a][j]) {
          f = false;
          break;
        }
      }
    }
    if (!f) { break; }
  }
  if (f) {
    for (var i = 0; i < resvA.length; i++) {//更新する日のresv情報を削除
      if (Math.ceil((resvA[i] - toDay + 1) / 86400000) - 1 == a) {
        resvA.splice(i, 1);
        resvZ.splice(i, 1);
        i--;
      }
    }
    for (i = 0; i < 2; i++) {
      if (rvA[i] > -1) {
        var d = new Date();
        d.setDate(d.getDate() + a);
        d.setHours(rvA[i], 0, 0, 0);
        resvA.push(new Date(d));
        d.setHours(rvZ[i], 59, 59, 0);
        resvZ.push(new Date(d));
      }
    }
    for (i = 0; i < 24; i++) {
      if (rvA[0] <= i && i <= rvZ[0] || rvA[1] <= i && i <= rvZ[1]) {
        $('#d' + a + ' div').eq(i).css('background-color', 'lime');
      } else if (!ttX[a][i]) {
        $('#d' + a + ' div').eq(i).css('background-color', 'white');
      }
    }
    setReservation();
  }
  rvTimetable(0, a);
}
function setReservation() {
  reservationA = [];
  reservationZ = [];
  resvA.sort(function (a, b) { return (a < b ? -1 : 1) })
  resvZ.sort(function (a, b) { return (a < b ? -1 : 1) })
  if (resvA.length) { reservationA.push(resvA[0]) };
  for (var i = 1; i < resvA.length; i++) {
    if (resvA[i] - resvZ[i - 1] > 1000) {
      reservationZ.push(resvZ[i - 1]);
      reservationA.push(resvA[i]);
    }
  }
  var i = resvA.length - 1;
  if (i >= 0) { reservationZ.push(resvZ[i]); }
  for (var j = 0; j < cars.length; j++) {
    var price = 0;
    for (var i = 0; i < reservationA.length; i++) {
      price += culcPrice(reservationA[i], reservationZ[i], cars[j].price, cars[j].holiday_price, cars[j].ext_price, cars[j].short_price, cars[j].short_hour, cars[j].long_price, cars[j].long_date);
    }
    $('#reservcars div').eq(j).text(price);
  }
}
function setReserv2(a, b) {
  var f = true;
  var i, j;
  if (a == b) {//最初のクリック
    for (i = 0; i < 2; i++) {
      for (j = rvA[i]; j < rvZ[i]; j++) {
        if (ttX[a + i][j - i * 24]) {//予定が空いてるか
          f = false;
          break;
        }
      }
    }
    if (f) {
      for (var i = 0; i < resvA.length; i++) {//更新する2日のresv情報を削除
        var d = Math.floor((resvA[i] - toDay) / 86400000);
        if (d == a || d == a + 1) {
          resvA.splice(i, 1);
          resvZ.splice(i, 1);
          i--;
        }
      }
      for (i = 0; i < 2; i++) {
        var d = new Date();
        d.setDate(d.getDate() + a);
        d.setHours(rvA[i], 0, 0, 0);
        resvA.push(new Date(d));
        d.setHours(rvZ[i] - i * 24, 59, 59, 0);
        resvZ.push(new Date(d));
      }
      for (j = 0; j < 2; j++) {
        for (i = 0; i < 24; i++) {
          if (rvA[j] - j * 24 <= i && i <= rvZ[j] - j * 24) {
            $('#d' + (a + j) + ' div').eq(i).css('background-color', 'lime');
          } else if (!ttX[a + j][i]) {
            $('#d' + (a + j) + ' div').eq(i).css('background-color', 'white');
          }
        }
      }
    }

  } else if (a == b - 1) {//ドラッグ１つめ
    for (i = 0; i < 24; i++) {
      if (ttX[b][i]) {//１日空いてるか
        f = false;
        break;
      }
    }
    for (i = rvA[1]; i < rvZ[1]; i++) {
      if (ttX[b + 1][i - 24]) {//予定が空いてるか
        f = false;
        break;
      }
    }
    if (f) {
      for (i = 0; i < resvA.length; i++) {//更新する2日のresv情報を削除
        var d = Math.floor((resvA[i] - toDay) / 86400000);
        if (d == b || d == b + 1) {
          resvA.splice(i, 1);
          resvZ.splice(i, 1);
          i--;
        }
      }
      var d = new Date();
      d.setDate(d.getDate() + b);
      d.setHours(0, 0, 0, 0);
      resvA.push(new Date(d));
      d.setHours(23, 59, 59, 0);
      resvZ.push(new Date(d));
      d.setHours(rvA[1], 0, 0, 0);
      resvA.push(new Date(d));
      d.setHours(rvZ[1], 59, 59, 0);
      resvZ.push(new Date(d));
      $('#d' + b + ' div').css('background-color', 'lime');
      for (i = 0; i < 24; i++) {
        if (rvA[1] - 24 <= i && i <= rvZ[1] - 24) {
          $('#d' + (b + 1) + ' div').eq(i).css('background-color', 'lime');
        } else if (!ttX[b + 1][i]) {
          $('#d' + (b + 1) + ' div').eq(i).css('background-color', 'white');
        }
      }
    }
  } else if (a < b) {//ドラッグ２つ以上
    var c = b - a;
    for (i = 1; i <= c; i++) {
      for (j = 0; j < 24; j++) {
        if (ttX[a + i][j]) {
          f = false;
          break;
        }
      }
    }
    for (i = rvA[1]; i < rvZ[1]; i++) {
      if (ttX[b + 1][i - 24]) {//予定が空いてるか
        f = false;
        break;
      }
    }
    if (f) {
      for (i = 0; i < resvA.length; i++) {//更新するa+1からb+1のresv情報を削除
        var d = Math.floor((resvA[i] - toDay) / 86400000);
        if (a < d && d <= b + 1) {
          resvA.splice(i, 1);
          resvZ.splice(i, 1);
          i--;
        }
      }
      var d = new Date();
      d.setDate(d.getDate() + a);
      for (i = 1; i <= c; i++) {
        d.setDate(d.getDate() + 1);
        d.setHours(0, 0, 0, 0);
        resvA.push(new Date(d));
        d.setHours(23, 59, 59, 0);
        resvZ.push(new Date(d));
      }
      d.setHours(rvA[1], 0, 0, 0);
      resvA.push(new Date(d));
      d.setHours(rvZ[1], 59, 59, 0);
      resvZ.push(new Date(d));
      for (i = 1; i <= c; i++) {
        $('#d' + (a + i) + ' div').css('background-color', 'lime');
      }
      for (i = 0; i < 24; i++) {
        if (rvA[1] - 24 <= i && i <= rvZ[1] - 24) {
          $('#d' + (b + 1) + ' div').eq(i).css('background-color', 'lime');
        } else if (!ttX[b + 1][i]) {
          $('#d' + (b + 1) + ' div').eq(i).css('background-color', 'white');
        }
      }
    }
  }
  setReservation();
  rvTimetable(0, b);
}
function reservCheck(car_id) {
  var msg = "",
    msgs = new Array();
  var err;
  var returnf = false;
  carids = [];
  cars = [];
  if (car_id) {
    carids[0] = carid;
  } else {
    $(".carids").each(function (i, v) {
      if (this.checked) {
        carids.push(v.value);
      }
    })
  }
  if (!carids.length) {
    alert('予約したいクルマを♪で選択してください。');
    return false;
  }
  for (var i = 0; i < carids.length; i++) {
    err = 0;
    $.ajax({
      url: 'json/check.php?carid=' + carids[i],
      type: 'GET',
      dataType: 'json',
      async: false,
      error: function () {
        alert('carid=' + carids[i] + 'のチェックajax通信に失敗しました。');
        return false;
      },
      success: function (msgs) {
        if (msgs.length == 0) {
          msgs[0] = 'carid=' + carids[i] + "のjson取得に失敗しました。";
          err++;
        }
        if (typeof (msgs.error) != 'undefined') {
          err++; //本番のみ
        }
        if (err) {
          carids.splice(i, 1);
          i--;
          msg += (typeof (msgs.car) == 'undefined') ? "" : msgs.car.maker + ' ' + msgs.car.na + " は\n";
          $.each(msgs.error, function (key, val) {
            msg += " ・" + val + "\n";
          });
          msg += "ため予約できません。\n"
          if (confirm(msg)) {
            if (!carids.length) { returnf = true; }
          } else {
            returnf = true;
          }
        } else {
          cars.push(msgs.car);
        }
      }
    });
    if (returnf) { return false; }
  }
  reservpop();
  $('#pop_title').text('クルマを予約リクエストする');
}
function reservpop() {
  endDay = new Date(toYear, toMonth, toDate);
  var lastDay = new Date(toYear, toMonth + 1, 0);
  if (endDay > lastDay) {
    endDay = lastDay;
  }
  period = Math.ceil((endDay - toDay) / 86400000);
  var month = (window.innerWidth > 1000) ? 2 : 1;
  setSchedule(carids, function () {
    setBooking(carids, function () {
      scheCalendar();
    });
  });
  makeCalendar(month);
  addPlan(-1);
  reservInit();
  $.pop();
}
function reservClose(condition) {
  reservationA = [];
  reservationZ = [];
  if (resvA.length == 0) { return false; }
  resvA.sort(function (a, b) { return (a < b ? -1 : 1) })
  resvZ.sort(function (a, b) { return (a < b ? -1 : 1) })
  if (resvA.length) { reservationA.push(resvA[0].getFullYear() + '-' + (resvA[0].getMonth() + 1) + '-' + resvA[0].getDate() + ' ' + resvA[0].getHours() + ':00:00'); }
  for (var i = 1; i < resvA.length; i++) {
    if (resvA[i] - resvZ[i - 1] > 1000) {
      reservationZ.push(resvZ[i - 1].getFullYear() + '-' + (resvZ[i - 1].getMonth() + 1) + '-' + resvZ[i - 1].getDate() + ' ' + resvZ[i - 1].getHours() + ':59:59');
      reservationA.push(resvA[i].getFullYear() + '-' + (resvA[i].getMonth() + 1) + '-' + resvA[i].getDate() + ' ' + resvA[i].getHours() + ':00:00');
    }
  }
  //SELECT * FROM t52reserv WHERE car_id=40;
  var i = resvA.length - 1;
  if (i >= 0) { reservationZ.push(resvZ[i].getFullYear() + '-' + (resvZ[i].getMonth() + 1) + '-' + resvZ[i].getDate() + ' ' + resvZ[i].getHours() + ':59:59'); }
  if (!condition) {
    var confirmMessage = "";//確認メッセージ作成
    var weekNames = ['日', '月', '火', '水', '木', '金', '土'];
    for (var i = 0; i < reservationA.length; i++) {
      var dateA = new Date(reservationA[i]);
      confirmMessage += (dateA.getMonth() + 1) + "月" + dateA.getDate() + "日(" + weekNames[dateA.getDay()] + ")" + dateA.getHours() + "時～";
      var dateZ = new Date(reservationZ[i]);
      confirmMessage += (dateA.getMonth() == dateZ.getMonth()) ? "" : (dateZ.getMonth() + 1) + "月";
      confirmMessage += (dateA.getMonth() == dateZ.getMonth() && dateA.getDate() == dateZ.getDate()) ? "" : dateZ.getDate() + "日(" + weekNames[dateZ.getDay()] + ")";
      confirmMessage += (dateZ.getHours() + 1) + "時\n";
    }
    var prices = new Array();//価格計算
    for (var j = 0; j < cars.length; j++) {
      var subs = [];
      for (var i = 0; i < reservationA.length; i++) {
        var p = culcPrice(reservationA[i], reservationZ[i], cars[j].price, cars[j].holiday_price, cars[j].ext_price, cars[j].short_price, cars[j].short_hour, cars[j].long_price, cars[j].long_date);
        subs.push(p);
      }
      prices.push(subs);
      confirmMessage += cars[j].na + " ￥" + prices[j] + "\n";
    }
    if (confirm(confirmMessage + "以上の内容で予約リクエストしてよろしいですか。")) {
      $.ajax({
        url: 'json/reserv.php',
        type: 'POST',
        dataType: 'json',
        data: {
          'carids': JSON.stringify(carids),
          'prices': JSON.stringify(prices),
          'reservA[]': reservationA,
          'reservZ[]': reservationZ
        },
        error: function () {
          alert('予約登録のajax通信に失敗しました。');
        },
        success: function (json) {
          alert(json['msg']);
        }
      });
      scheA = [];
      scheZ = [];
      resvA = [];
      resvZ = [];
      bookA = [];
      bookZ = [];
      bookid = [];
      ttX = [];
    } else {
      return false;
    }
  }
}
function culcPrice(startday, endday, price, holiday_price, ext_price, short_price, short_hour, long_price, long_date) {
  var Aday = new Date(startday);
  var Zday = new Date(endday);
  var p = 0;
  var price = Number(price);
  var holiday_price = Number(holiday_price);
  var ext_price = Number(ext_price);
  var short_price = Number(short_price);
  var short_hour = Number(short_hour);
  var long_price = Number(long_price);
  var long_date = Number(long_date);
  var diffday = Math.ceil((Zday - Aday) / 86400000);
  function isHoliday(day) {
    var w = day.getDay();
    if (w == 0 || w == 6) {
      return true;
    } else {
      var m = day.getMonth() + 1;
      var d = day.getDate();
      for (var i = 0; i < holiday[m].length; i++) {
        if (d == holiday[m][i]) { return true; }
      }
    }
    return false;
  }
  if (diffday > 1) {
    for (var i = 0; i < diffday; i++) {
      p += price;
      p += (isHoliday(Aday)) ? holiday_price : 0;
      p -= (i) ? ext_price : 0;
      p -= (i >= long_date) ? long_price : 0;
      Aday.setDate(Aday.getDate() + 1);
    }
    if (Aday < Zday) {//帰着時間が貸出時間より遅い、最終日に余りがある場合
      p += (isHoliday(Zday)) ? price + holiday_price - ext_price : price - ext_price;
      p -= (i >= long_date) ? long_price : 0;
    }
  } else {
    var diffhour = Math.ceil((Zday - Aday) / 3600000);
    p += (diffhour > short_hour) ? price : price - short_price;
    p += (isHoliday(Aday) || isHoliday(Zday)) ? holiday_price : 0;
  }
  return p;
}
