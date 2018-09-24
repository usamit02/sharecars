var resvA = new Array();//rv=resve
var resvZ = new Array();
var rvA = [-1, -1], rvZ = [-1, -1];
var reservationA,
  reservationZ;
var cars = new Array();
function reservInit() {
  reservA = [];
  reservZ = [];
  var html = "<div id='reservcars'>";
  //$.each(car, function (key, val) {
  for (var i = 0; i < cars.length; i++) {
    html += "<label>" + cars[i].na + "</label><div></div>";
  }
  html += "</div>";
  $('#calendar').append(html);
  rvTimetable();
  $('.hour').on('mousedown touchstart', function (e) {
    var a = parseInt($(this).attr('id').replace('hour', ''));
    if ((rvA[0] <= a && a <= rvZ[0]) || (rvA[1] <= a && a <= rvZ[1])) {
      var handle = (rvA[0] <= a && a <= rvZ[0]) ? 0 : 1;
    } else {
      if (rvA[0] > -1 && rvZ[0] > -1 && rvA[1] > -1 && rvZ[1] > -1) {
        alert("不可期間は２カ所までしか設定できません。");
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
      var b = parseInt($(this).attr('id').replace('hour', ''));
      if (a != b && a > -1) {
        var ab = b - a;
        if (rvZ[0] == a) {
          rvZ[0] = b;
        } else if (rvA[0] == a) {
          rvA[0] = b;
        } else if (rvZ[1] == a) {
          rvZ[1] = b;
        } else if (rvA[1] == a) {
          rvA[1] = b;
        } else if ((rvA[handle] + ab) > -1 && (rvZ[handle] + ab) < 24) {
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
    });
  });
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
function reservEvent() {
  var hover;
  $('.calendar td:not(.non)').unbind("mouseenter").unbind("mouseleave");
  $('.calendar td:not(.non)').off('mousedown touchstart mousemove touchmove mouseup');
  $('.calendar td:not(.non)').hover(function (e) {
    var a = parseInt($(this).attr('id').replace('d', ''));
    if (a != hover) {
      if (rvA[0] == 0 && rvZ[1] > 23) {//予約が翌日にまたがる場合
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
    if (rvA[0] == 0 && rvZ[1] > 23) {
      setReserv2(a, a);
    } else {
      setReserv(a);
    }
    var bb;
    $('.calendar td:not(.non)').on('mousemove touchmove', function (e) {
      e.preventDefault();
      var b = parseInt($(this).attr('id').replace('d', ''));
      if (a < b && bb != b) {
        if (b == bb + 1 && rvA[0] == 0 && rvZ[1] > 23) {
          if (setReserv2(a, b) === false) {
            $('.calendar td:not(.non)').off('mousemove touchmove');
          }
        } else {
          setReserv(b);
        }
      }
      bb = b;
    }).one('mouseup', function (e) {
      if (rvA[0] == 0 && rvZ[1] == 23) {
        var b = parseInt($(this).attr('id').replace('d', ''));
        if (a < b) {
          if (setReserv2(a, b) == false) {
            for (i = a; i <= b; i++) {
              for (j = 0; j < 2; j++) {
                var f = resvA[i][j];
                resvA[i][j] = -1;
                resvZ[i][j] = -1;
              }
              paintReserv(i);
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
  if (rvA[0] == 0 && rvZ[1] == 23) {
    var html = "<div><div style='width:50px;'>翌日</div>";
    for (var i = 24; i < 48; i++) {
      html += "<div class='hour' id='hour" + i + "'></div>";
    }
    html += "<div style='widht:50px></div>";
    for (i = 24; i < 48; i++) {
      tthtml += "<div class='hourlabel' id='hourlabel" + i + "'>" + (i) + ":00</div>";
    }
    tthtml += "</div>";
    $('#timetable').append(html);
    var a = rvA[0];
    var z = rvZ[0];
    rvA[0] = rvA[1];
    rvZ[0] = rvZ[1];
    rvA[1] = a + 24;
    rvZ[1] = z + 24;
    ii = 48;
  } else if ($('#timetable > div').length > 2 && rvA[1] < 24) {
    $('#timetable > div').eq(3).remove();
    $('#timetable > div').eq(4).remove();
    ii = 24;
  }
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
      $("#hourlabel" + i).css('color', 'black');
      $("#hour" + i % 24).html('');
    } else {
      $('#hourlabel' + i).css('color', 'white');
      $("#hour" + i % 24).html('');
    }
    if (a > -1 && ttX[a + Math.ceil(i / 24)][i % 24]) {
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
  return handle;
}
function setReservX(a) {
  var f = true, A, Z;
  for (var i = 0; i < scheA.length; i++) {
    if (Math.ceil((scheA[i] - toDay + 1) / 86400000) - 1 == a) {
      for (i = 0; i < 2; i++) {
        if (rvA[i] > -1) {
          A = new Date(scheA[i]).getHours();
          Z = new Date(scheA[i]).getHours();
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
      if (!ttX[a][i]) {
        if (rvA[0] <= i && i <= rvZ[0] || rvA[1] <= i && i <= rvZ[1]) {
          $('#d' + a + ' div').eq(i).css('background-color', 'lime');
        } else {
          $('#d' + a + ' div').eq(i).css('background-color', 'white');
        }
      }
    }
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
        price += culcPrice(reservationA[i], reservationZ[i], cars[j].price, cars[j].holi, cars[j].ext, cars[j].short, cars[j].short_h);
      }
      $('#reservcars div').eq(j).text(price);
    }
  }
  rvTimetable(0, a);
}
function paintReserv(a) {
  for (j = 0; j < 24; j++) {
    if (scheA[a][0] <= j && j <= scheZ[a][0] || scheA[a][1] <= j && j <= scheZ[a][1]) {
      $('#d' + a + ' div').eq(j).css('background-color', 'gray');
    } else if (resvA[a][0] <= j && j <= resvZ[a][0] || resvA[a][1] <= j && j <= resvZ[a][1]) {
      $('#d' + a + ' div').eq(j).css('background-color', 'lime');
    } else {
      $('#d' + a + ' div').eq(j).css('background-color', 'white');
    }
  }
}
function setReserv2(a, b) {//泊りがけ予約　rvA=0 AND rvZ=23
  for (i = 0; i < 2; i++) {
    var k = (i) ? b : b + 1;
    for (j = 0; j < 2; j++) {
      if (rvA[i] > -1 && (scheA[k][j] <= rvA[i] && scheZ[k][j] >= rvA[i] || rvA[i] <= scheA[k][j] && rvZ[i] >= scheA[k][j])) {
        return false;
      }
    }
  }
  if (a == b - 1) {
    resvA[b][0] = 0;
    resvZ[b][0] = 23;
    $('#d' + b + ' div').css('background-color', 'lime');
    resvA[b + 1][0] = rvA[0];
    resvZ[b + 1][0] = rvZ[0];
    paintReserv(b + 1);
  } else if (a < b) {
    c = b - a;
    for (i = 1; i <= c; i++) {
      var dd = scheA[a + i][0];
      if (scheA[a + i][0] > -1 || scheA[a + i][1] > -1) {
        return false;
      }
    }
    for (i = 1; i <= c; i++) {
      resvA[a + i][0] = 0;
      resvZ[a + i][0] = 23;
      $('#d' + (a + i) + ' div').css('background-color', 'lime');
    }
    resvA[a + c + 1][0] = rvA[0];
    resvZ[a + c + 1][0] = rvZ[0];
    paintReserv(a + c + 1);
  } else {
    for (i = 0; i < 2; i++) {
      resvA[b + i][0] = rvA[1 - i];
      resvZ[b + i][0] = rvZ[1 - i];
      if (rvA[i] > -1) {
        paintReserv(b + i);
      }
    }
  }
  rvTimetable();
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
    var prices = new Array();
    for (var j = 0; j < cars.length; j++) {
      var subs = [];
      for (var i = 0; i < reservationA.length; i++) {
        var p = culcPrice(reservationA[i], reservationZ[i], cars[j].price, cars[j].holi, cars[j].ext, cars[j].short, cars[j].short_h);
        subs.push(p);
      }
      prices.push(subs);
    }
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
  }



}
function culcPrice(startday, endday, price, holi, ext, short, short_h) {
  var Aday = new Date(startday);
  var Zday = new Date(endday);
  var p = 0;
  var price = Number(price);
  var holi = Number(holi);
  var ext = Number(ext);
  var short = Number(short);
  var short_h = Number(short_h);
  var diffday = Math.ceil((Zday - Aday) / 86400000);
  if (diffday > 1) {
    for (var i = 0; i < diffday; i++) {
      if (Aday.getDay() == 6 || Aday.getDay() == 0) {
        p += holi;
      }
      p += (i) ? price - ext : price;
      Aday.setDate(Aday.getDate() + 1);
    }
    if (Aday < Zday) {
      p += (Zday.getDay() == 0 || Zday.getDay() == 6) ? price + holi - ext : price - ext;
    }
  } else {
    var diffhour = Math.ceil((Zday - Aday) / 3600000);
    p += (diffhour > short_h) ? price : price - short;
    p += (Aday.getDay() == 0 || Aday.getDay() == 6 || Zday.getDay() == 0 || Zday.getDay() == 6) ? holi : 0;
  }
  return p;
}