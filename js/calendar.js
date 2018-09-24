var addMonth = 0;
var ttX = new Array(100);
var holiday = [1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12];
holiday[1] = [1, 2, 3, 4, 5, 8];
holiday[2] = [12];
holiday[3] = [21];
holiday[4] = [30];
holiday[5] = [1, 2, 3, 4, 5, 6];
holiday[6] = [];
holiday[7] = [16];
holiday[8] = [13, 14, 15];
holiday[9] = [17, 24];
holiday[10] = [8];
holiday[11] = [23];
holiday[12] = [24, 31];
function makeCalendar(month, typ) {
  var aDay = new Date(toYear, toMonth - 2, 1);
  var d, td;
  for (var i = 0; i < 100; i++) {
    ttX[i] = new Array(24);
    for (var j = 0; j < 24; j++) {
      ttX[i][j] = 0;
    }
  }
  for (diffMonth = 0; diffMonth < month; diffMonth++) {
    aDay.setMonth(aDay.getMonth() + 1);
    aYear = aDay.getFullYear();
    aMonth = aDay.getMonth() + 1;
    var aWeek = aDay.getDay();
    aWeek = (aWeek == 0) ? 6 : aWeek - 1;
    var zDay = new Date(aYear, aMonth, 0);
    ad = Math.floor((aDay - toDay) / 86400000);
    var html = "<table class='calendar' id='cal" + aMonth + "' border='0' cellspacing='0' cellpadding='0'><thead><tr>"
    html += (month == 1) ? '<th onclick="changeMonth(-1,' + typ + ')" colspan="2">＜＜</th><th colspan="3" id="month">' + aYear + '年' + aMonth + '月' + '</th><th onclick="changeMonth(1,' + typ + ')" colspan="2">＞＞' : "<th colspan= '7'> " + aYear + '年' + aMonth + '月';
    html += "</th></tr></thead><tbody><tr><th>月</th><th>火</th><th>水</th><th>木</th><th>金</th><th class='sat'>土</th><th class='sun'>日</th></tr>";
    for (var i = 0; i < 6; i++) {
      html += "<tr>";
      for (var j = 0; j < 7; j++) {
        n = i * 7 + j;
        var td = "<td ";
        if (aWeek <= n && n < aWeek + zDay.getDate()) {
          d = ad + n - aWeek;
          if (0 <= d && d <= period) {
            var innertd = "";
            var day = n - aWeek + 1;
            td += "id='d" + d + "' ";
            if (j == 5) {
              td += "class='sat'";
            } else if (j == 6) {
              td += "class='sun' "
            } else {
              for (var k = 0; k < holiday[aMonth].length; k++) {
                if (day == holiday[aMonth][k]) {
                  td += "class='holi'";
                  break;
                }
              }
            }
            td += ">" + day;
            for (k = 0; k < 24; k++) {
              innertd += (window.innerWidth > 1000) ? "<div style='left:" + (k * 5 / 4) + "px;'></div>" : "<div style='left:" + k / 2 + "vw;'></div>";
            }
            td += innertd + "</div>";
          } else {
            td += "class='non'>" + (n - aWeek + 1);
          }
        } else {
          td += "class='non'>"
        }
        html += td + "</td>";
      }
      html += "</tr>";
    }
    html += "</tbody></table>";
    $("#calendar").append(html);
  }
  $('#d0').css('border-color', 'green');
}
function changeMonth(diffMonth, typ) {
  var aDay = new Date(toYear, toMonth - 1, 1);
  var d;
  addMonth += diffMonth;
  aDay.setMonth(aDay.getMonth() + addMonth);
  aYear = aDay.getFullYear();
  aMonth = aDay.getMonth() + 1;
  var aWeek = aDay.getDay();
  aWeek = (aWeek == 0) ? 6 : aWeek - 1;
  var zDay = new Date(aYear, aMonth, 0);
  $('#month').html(aYear + '年' + aMonth + '月');
  ad = Math.floor((aDay - toDay) / 86400000);
  var innerDiv = "";
  for (i = 0; i < 24; i++) {
    innerDiv += "<div style='left:" + i / 2 + "vw;'></div>";
  }
  for (i = 0; i < 42; i++) {
    if (aWeek <= i && i < aWeek + zDay.getDate()) {
      $('tbody td').eq(i).text(i - aWeek + 1);
      d = ad + i - aWeek;
      if (0 <= d && d <= period) {
        $('tbody td').eq(i).attr('id', 'd' + d);
        $('tbody td').eq(i).removeClass('non');
        $('tbody td').eq(i).append(innerDiv);
      } else {
        $('tbody td').eq(i).addClass('non');
        $('tbody td').eq(i).removeAttr('id');
      }
    } else {
      $('tbody td').eq(i).html('');
      $('tbody td').eq(i).addClass('non');
      $('tbody td').eq(i).removeAttr('id');
    }
  }
  $('#d0').css('border-color', 'green');
  scheCalendar(period);
  if (typ) {
    scheEvent();
  } else {
    reservEvent();
  }
}
function scheCalendar(prod) {
  for (var i = 0; i < scheA.length; i++) {
    var d = Math.ceil((scheA[i] - toDay + 1) / 86400000) - 1;
    if (0 <= d && (!prod || d <= prod)) {
      var A = scheA[i].getHours();
      var Z = scheZ[i].getHours();
      for (var j = A; j <= Z; j++) {
        $('#d' + d + ' div').eq(j).css('background-color', 'gray');
        ttX[d][j] = 1;
      }
    }
  }
  for (var i = 0; i < bookA.length; i++) {
    var d = Math.ceil((bookA[i] - toDay + 1) / 86400000) - 1;
    if (0 <= d && (!prod || d <= prod)) {
      var A = bookA[i].getHours();
      var Z = bookZ[i].getHours();
      var color = (bookid[i] == id) ? 'green' : 'purple';
      for (var j = A; j <= Z; j++) {
        $('#d' + d + ' div').eq(j).css('background-color', color);
        ttX[d][j] = 2;
      }
    }
  }

}