var currentPlan = 1;
var ttA = [-1, -1, -1, -1],
  ttZ = [-1, -1, -1, -1]; //tt=timetable
var scheA = new Array();
var scheZ = new Array();
var caleA = new Array();
var caleZ = new Array();
var bookA = new Array();
var bookZ = new Array();
var bookid = new Array();
var planA = new Array(10);
var planZ = new Array(10);
for (var i = 0; i < 10; i++) {
  planA[i] = new Array(2);
  planZ[i] = new Array(2);
}
var toDay = new Date();
toDay.setHours(0, 0, 0, 0);
var toYear = toDay.getFullYear(),
  toMonth = toDay.getMonth() + 1,
  toDate = toDay.getDate();

function setSchedule(car_ids, callback) {
  caleA = [];
  caleZ = [];
  scheA = [];
  scheZ = [];
  if (car_ids) {
    $.ajax({
      url: 'json/schedule.php',
      type: 'POST',
      dataType: 'json',
      data: {
        'carids': JSON.stringify(car_ids)
      },
      timeout: 1000,
      error: function () {
        alert('スケジュール読み込みのajax通信に失敗しました。');
      },
      success: function (json) {
        var A, Z, Ad, Zd, ZZ, k;
        $.each(json, function (i, v) {
          Ad = new Date(v.start_day.replace(/-/g, "/"));
          Zd = new Date(v.end_day.replace(/-/g, '/'));
          scheA.push(new Date(Ad));
          Ad.setHours(0, 0, 0, 0);
          while (Ad < Zd) {
            Ad.setDate(Ad.getDate() + 1);
            if (Ad > Zd) {
              scheZ.push(new Date(Zd));
            } else {
              scheZ.push(new Date(Ad - 1000));
              scheA.push(new Date(Ad));
            }
          }
        });
        caleA = $.extend(true, [], scheA);
        caleZ = $.extend(true, [], scheZ);
        callback();
      }
    });
  } else {
    callback();
  }
}
function setBooking(car_ids, callback) {
  bookA = [];
  bookZ = [];
  bookid = [];
  if (car_ids) {
    $.ajax({
      url: 'json/reserv.php',
      type: 'POST',
      dataType: 'json',
      data: {
        'carids': JSON.stringify(car_ids)
      },
      timeout: 1000,
      error: function () {
        alert('予約状況読み込みのajax通信に失敗しました。');
      },
      success: function (json) {
        var A, Z, Ad, Zd, ZZ, k;
        $.each(json, function (i, v) {
          Ad = new Date(v.start_day.replace(/-/g, "/"));
          Zd = new Date(v.end_day.replace(/-/g, '/'));
          bookA.push(new Date(Ad));
          bookid.push(v.id);
          Ad.setHours(0, 0, 0, 0);
          while (Ad < Zd) {
            Ad.setDate(Ad.getDate() + 1);
            if (Ad > Zd) {
              bookZ.push(new Date(Zd));
            } else {
              bookZ.push(new Date(Ad - 1000));
              bookA.push(new Date(Ad));
              bookid.push(v.id);
            }
          }
        });
        callback();
      }
    });
  } else {
    callback();
  }
}
function setPlan(no, cd, A, Z, na) {
  for (i = 0; i < 10; i++) {
    planA[i] = [-1, -1];
    planZ[i] = [-1, -1];
  }
  planA[1][0] = 0;
  planZ[1][0] = 23;
  var planNa = ["取消", "一日"];
  var a = (cd > -1) ? carid : id;
  $.ajax({
    url: 'json/plan.php?id=' + a + '&no=' + no + '&cd=' + cd + '&start=' + A + '&end=' + Z + '&na=' + na,
    type: 'GET',
    async: false,
    cache: false,
    dataType: 'json',
    timeout: 1000,
    error: function () {
      alert('プラン作成のajax通信に失敗しました。');
    },
    success: function (json) {
      var baseDay = new Date('1970-1-1');
      $.each(json, function (i, v) {
        if (i != 'msg') {
          var j = parseInt(v.no) + 1;
          var k = parseInt(v.cd) + (v.cd < 0) * 2;
          planNa[j] = v.na;
          var dateA = new Date(v.start_day.replace(/-/g, "/"));
          var add = (Math.floor((dateA - baseDay) / 86400000)) ? 24 : 0;
          planA[j][k] = dateA.getHours() + add;
          var dateZ = new Date(v.end_day.replace(/-/g, '/'));
          planZ[j][k] = dateZ.getHours() + add;
        }
      });
      var planhtml = "<div class='plan'><div class='planlabel'></div><div class='planhours'>";
      for (i = 0; i < 24; i++) {
        planhtml += "<div style='width:3px;height:2em' class='planhour" + i + "'></div>";
      }
      planhtml += "</div><div class='plantime'></div></div>";
      var planhtml2 = "<div class='plan'><div class='planlabel'></div><div class='planhours'>";
      for (i = 0; i < 48; i++) {
        planhtml2 += "<div style='width:3px;height:1em' class='planhour" + i + "'></div>";
      }
      planhtml2 += "</div><div class='plantime'></div></div>";
      $('#plans').empty();
      planNa.forEach(function (na, j, plan) {
        var html = (planA[j][1] == 24) ? planhtml2 : planhtml;
        var ii = (planA[j][1] == 24) ? 48 : 24;
        $('#plans').prepend(html);
        $('.planlabel:first').text(na);
        $('.planhours:first').attr('id', 'plan' + j);
        var $plantime = (planA[j][0] > -1) ? planA[j][0] + '～' + (planZ[j][0] + 1) : "";
        var Z = (planZ[j][1] > 24) ? "翌" + (planZ[j][1] - 23) : planZ[j][1] + 1;
        $plantime += (planA[j][1] > -1) ? '<br>' + planA[j][1] + '～' + Z : "";
        $('.plantime:first').html($plantime);
        var color = (cd < 0) ? 'green' : 'gray';
        for (i = 0; i < ii; i++) {
          if ((planA[j][0] <= i && i <= planZ[j][0]) || (planA[j][1] <= i && i <= planZ[j][1])) {
            $('.plan:first div .planhour' + i).css('background-color', color);
          } else {
            $('.plan:first div .planhour' + i).css('background-color', 'white');
          }
        }
      });
      if (json['msg'] != 'ok') {
        alert(json['msg']);
      }
    }
  });
  $('.planhours').off('click');
  $('.planhours').on('click', function (e) {
    $('.planhours').css('border', '');
    setCurrentPlan(this, cd);
    if (cd < 0) {
      h = rvTimetable();
    } else {
      h = setTimetable();
    }
  });
}
function setCurrentPlan(that, cd) {
  $(that).css({
    "border": "2px solid",
    "border-color": "red"
  });
  $("#planna").val($(that).prev('.planlabel').text());
  currentPlan = $(that).attr('id').replace('plan', '');
  for (i = 0; i < 2; i++) {
    if (cd < 0) {
      rvA[i] = planA[currentPlan][i];
      rvZ[i] = planZ[currentPlan][i];
    } else {
      ttA[i] = planA[currentPlan][i];
      ttZ[i] = planZ[currentPlan][i];
    }
  }
}
function addPlan(cd) {
  setPlan(0, cd);
  addTimetable(cd);
  setCurrentPlan($('.planhours:first'), cd);
  if (cd < 0) {
    rvTimetable();
  } else {
    setTimetable();
  }
}

function addTimetable(cd) {
  var tthtml = "<div><input type='text' id='planna'>";
  for (i = 0; i < 24; i++) {
    tthtml += "<div class='hour' id='hour" + i + "'></div>";
  }
  tthtml += "<button id='saveplan'>保存</button></div><div><label id='delplan'>削除</label>";
  for (i = 0; i < 24; i++) {
    tthtml += "<div class='hourlabel' id='hourlabel" + i + "'>" + (i) + ":00</div>";
  }
  tthtml += "</div>";
  $('#timetable').append(tthtml);
  if (cd < 0) {
    rvTimetable();
  } else {
    setTimetable();
  }
}
function setTimetable(handle) {
  for (i = 0; i < 2; i++) {
    if (ttA[i] > ttZ[i]) {
      ttA[i] = -1;
      ttZ[i] = -1;
    } else {
      if (ttA[0] > -1 && ttZ[0] > -1 && ttA[1] > -1 && ttZ[1] > -1) {
        j = (i) ? 0 : 1;
        if (ttA[i] == ttZ[j] + 1) {
          ttA[0] = ttA[j];
          ttZ[0] = ttZ[i];
          ttA[1] = -1;
          ttZ[1] = -1;
          handle = 0;
        }
      }
    }
  }
  if (ttA[0] > ttA[1] && ttA[1] > -1 || ttA[0] < 0 && ttA[1] > -1) {
    var A = ttA[0];
    ttA[0] = ttA[1];
    ttA[1] = A;
    A = ttZ[0];
    ttZ[0] = ttZ[1];
    ttZ[1] = A;
  }
  for (i = 0; i < 24; i++) {
    if (ttA[0] == i || ttZ[0] == i || ttA[1] == i || ttZ[1] == i) {
      $("#hourlabel" + i).css("color", "black");
    } else {
      $('#hourlabel' + i).css("color", "transparent");
    }
    if (i % 6 == 0) {
      $('#hourlabel' + i).css("color", "gray");
    }
    if ((ttA[0] <= i && i <= ttZ[0]) || (ttA[1] <= i && i <= ttZ[1])) {
      $("#hour" + i).css('background-color', 'gray');
    } else {
      $("#hour" + i).css('background-color', 'white');
    }
  }
  //console.log(ttA[0] + '～' + ttZ[0] + ' : ' + ttA[1] + '～' + ttZ[1])
  return handle;
}
function scheInit() {
  $('.hour').off('mousedown touchstart mousemove touchmove dragstart taphold');
  $('.hour').on('mousedown touchstart', function (e) {
    var a = parseInt($(this).attr('id').replace('hour', ''));
    if (e.handleObj.type == 'touchstart') {
      var sx = event.changedTouches[0].pageX; //フリック開始時のX軸の座標    
    }
    if ((ttA[0] <= a && a <= ttZ[0]) || (ttA[1] <= a && a <= ttZ[1])) {
      var handle = (ttA[0] <= a && a <= ttZ[0]) ? 0 : 1;
      if ((ttA[0] == a || ttZ[0] == a || ttA[1] == a || ttZ[1] == a)) {
        $(this).css('color', 'red');
      } else {
        for (i = ttA[handle]; i <= ttZ[handle]; i++) {
          $("#hour" + i).css('color', 'red');
        }
      }
    } else {
      if (ttA[0] > -1 && ttZ[0] > -1 && ttA[1] > -1 && ttZ[1] > -1) {
        alert("不可期間は２カ所までしか設定できません。");
        a = -1;
        $('.hour').off('mousemove touchmove');
      } else {
        if (ttA[1] > -1 || ttZ[1] > -1) {
          ttA[0] = a;
          ttZ[0] = a;
        } else {
          ttA[1] = a;
          ttZ[1] = a;
        }
        handle = setTimetable(handle);
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
      }
      function hourmove() {
        var ab = b - a;
        if (ttZ[0] == a) {
          ttZ[0] = b;
          $('#hour' + b).css('color', 'red');
        } else if (ttA[0] == a) {
          ttA[0] = b;
          $('#hour' + b).css('color', 'red');
        } else if (ttZ[1] == a) {
          ttZ[1] = b;
          $('#hour' + b).css('color', 'red');
        } else if (ttA[1] == a) {
          ttA[1] = b;
          $('#hour' + b).css('color', 'red');
        } else if ((ttA[handle] + ab) > -1 && (ttZ[handle] + ab) < 24) {
          ttA[handle] += ab;
          ttZ[handle] += ab;
        }
        handle = setTimetable(handle);
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
  $('#saveplan').on('click', function () {
    var a = (currentPlan > 1) ? currentPlan - 1 : 99;
    for (var i = 0; i < 2; i++) {
      setPlan(a, i, ttA[i], ttZ[i], $("#planna").val());
    }
  });
  $('#delplan').on('click', function () {
    setPlan(-1, 0, 0, 0, $("#planna").val());
    setCurrentPlan($('.planhours:first'));
  });
  scheEvent();
}
function scheEvent() {
  $('.calendar thead').off('mousedown touchstart');
  $('.calendar thead').on('mousedown touchstart', function (e) {
    var m = $(this).parent('table').attr('id').replace('cal', '');
    if (confirm(m + "月を全てタイムテーブルの時間にセットします。")) {
      $(this).next('tbody').find('td:not(.non)').each(function () {
        setHour(parseInt(this.id.replace('d', '')));
      });
    }
  });
  $('.calendar tbody th').off('mousedown touchstart');
  $('.calendar tbody th').on('mousedown touchstart', function (e) {
    var m = $(this).closest('table').attr('id').replace('cal', '');
    var w = this.cellIndex;
    if (confirm(m + "月の" + $(this).text() + "曜日を全てタイムテーブルの時間にセットします。")) {
      $(this).parent().nextAll().each(function () {
        var td = this.children[w];
        if (td.hasAttribute('id')) {
          setHour(parseInt(td.id.replace('d', '')));
        }
      });
    }
  });

  $('.calendar td:not(.non)').off('mousedown touchstart mousemove touchmove mouseup');
  $('.calendar td:not(.non)').on('mousedown touchstart', function (e) {
    var a = parseInt($(this).attr('id').replace('d', ''));
    setHour(a);
    $('.calendar td:not(.non)').on('mousemove touchmove', function (e) {
      var b = parseInt($(this).attr('id').replace('d', ''));
      if (a != b) {
        setHour(b);
      }
      a = b;
    }).one('mouseup', function (e) {
      $('.calendar td:not(.non)').off('mousemove touchmove mouseup');
    });
  });
}
function setHour(a) {
  $('.calendar td:not(.non)').off('mousemove touchmove mouseup');
  for (var i = 0; i < bookA.length; i++) {
    if (Math.ceil((bookA[i] - toDay + 1) / 86400000) - 1 == a) {
      if (confirm(bookid[i] + 'さんの予約' + bookA[i].toLocaleString() + '～' + bookZ[i].toLocaleString() + 'を削除しますか。')) {
        $.ajax({
          url: 'json/reserv.php',
          type: 'POST',
          async: false,
          dataType: 'json',
          data: {
            'carids': carid,
            'reservA[]': bookA[i].getFullYear() + '-' + (bookA[i].getMonth() + 1) + '-' + bookA[i].getDate() + ' ' + bookA[i].getHours() + ':00:00'
          },
          error: function () {
            alert('予約削除のajax通信に失敗しました。');
            return false;
          },
          success: function (json) {
            if (json.msg != '予約申込しました。') {
              alert(json.msg);
              return false;
            }
            for (var j = bookA[i].getHours(); j <= bookZ[i].getHours(); j++) {
              ttX[a][j] = 0;
              $('#d' + a + ' div').eq(j).css('background-color', 'white');
            }
            bookA.splice(i, 1);
            bookZ.splice(i, 1);
            i--;
          }
        });
      } else {
        return false;
      }
    }
  }
  for (var i = 0; i < scheA.length; i++) {
    if (Math.ceil((scheA[i] - toDay + 1) / 86400000) - 1 == a) {
      scheA.splice(i, 1);
      scheZ.splice(i, 1);
      i--;
    }
  }
  for (i = 0; i < 2; i++) {
    if (ttA[i] > -1) {
      var d = new Date();
      d.setDate(d.getDate() + a);
      d.setHours(ttA[i], 0, 0, 0);
      scheA.push(new Date(d));
      d.setHours(ttZ[i], 59, 59, 0);
      scheZ.push(new Date(d));
    }
  }
  for (i = 0; i < 24; i++) {
    if (ttA[0] <= i && i <= ttZ[0] || ttA[1] <= i && i <= ttZ[1]) {
      $('#d' + a + ' div').eq(i).css('background-color', 'gray');
    } else {
      $('#d' + a + ' div').eq(i).css('background-color', 'white');
    }
  }
}
function saveClose() {
  if (caleA.toString() == scheA.toString() && caleZ.toString() == scheZ.toString()) {
    return false;
  }
  var scheduleA = new Array(),
    scheduleZ = new Array();
  scheA.sort(function (a, b) { return (a < b ? -1 : 1) })
  scheZ.sort(function (a, b) { return (a < b ? -1 : 1) })
  if (scheA.length) {
    scheduleA.push(scheA[0].getFullYear() + '-' + (scheA[0].getMonth() + 1) + '-' + scheA[0].getDate() + ' ' + scheA[0].getHours() + ':00:00');
    for (var i = 1; i < scheA.length; i++) {
      if (scheA[i] - scheZ[i - 1] > 1000) {
        scheduleZ.push(scheZ[i - 1].getFullYear() + '-' + (scheZ[i - 1].getMonth() + 1) + '-' + scheZ[i - 1].getDate() + ' ' + scheZ[i - 1].getHours() + ':59:59');
        scheduleA.push(scheA[i].getFullYear() + '-' + (scheA[i].getMonth() + 1) + '-' + scheA[i].getDate() + ' ' + scheA[i].getHours() + ':00:00');
      }
    }
    var i = scheA.length - 1;
    scheduleZ.push(scheZ[i].getFullYear() + '-' + (scheZ[i].getMonth() + 1) + '-' + scheZ[i].getDate() + ' ' + scheZ[i].getHours() + ':59:59');
  } else {
    scheduleA[0] = '1970-1-1';
  }
  $.ajax({
    url: 'json/schedule.php',
    type: 'POST',
    dataType: 'json',
    data: {
      'carids': carid,
      'scheA[]': scheduleA,
      'scheZ[]': scheduleZ
    },
    timeout: 1000,
    error: function () {
      alert('スケジュール保存のためのajax通信に失敗しました。');
    },
    success: function (json) {
      if (json['msg'] != "ok") { alert(json['msg']); }
    }
  });
}

function dateFormat(date) {
  var m = date.getMonth() + 1;
  var d = date.getDate();
  var w = date.getDay();
  var h = date.getHours();
  var min = date.getMinutes();
  if (min < 10) { min = "0" + min; }
  var wNames = ['日', '月', '火', '水', '木', '金', '土'];
  return m + '月' + d + '日 (' + wNames[w] + ')' + h + ':' + min;
}
