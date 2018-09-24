var terms = {};
var where = [];
function search(condition) {
  $("#Close").attr('onclick', "Close('" + condition + "')");
  if (condition == 'daytime') {
    endDay = new Date(toYear, toMonth, toDate);
    var lastDay = new Date(toYear, toMonth + 1, 0);
    if (endDay > lastDay) {
      endDay = lastDay;
    }
    period = Math.ceil((endDay - toDay) / 86400000);
    var month = (window.innerWidth > 1000) ? 2 : 1;
    cars = [];
    makeCalendar(month);
    addPlan(-1);
    reservInit();
    $.pop();
    $("#pop_title").text('日時を設定してクルマを絞り込む');
  } else {
    $.ajax({
      url: 'json/search.php?group=' + condition,
      type: 'GET',
      dataType: 'text',
      error: function () {
        alert('条件画面の作成に失敗しました。')
      },
      success: function (html) {
        $("#where").append(html);
        $("#pop_title").text('条件を設定してクルマを絞り込む');
        $("#clear").remove();
        $("#pop_close").append("<button id='clear' onclick=termClear('" + condition + "')>条件無</button>");
        if (terms.hasOwnProperty(condition)) {
          for (var i in terms[condition][0]) {
            var na = terms[condition][0][i];
            var val = terms[condition][1][i];
            var typ = terms[condition][2][i];
            if (typ == 'checkbox') {
              $("." + na + "[value=" + val + "]").prop('checked', true);
            } else {
              $("." + na).val(val);
            }
          }
        }
        $.pop();
      }
    });
  }
}

function Close(condition) {
  var html = "<div>";
  if (!condition) {
    if (reservClose()) {
      popClose();
    }
    return false;
  } else if (condition == 'daytime') {
    reservClose(true);
    $.each(reservationA, function (key, val) {
      var A = new Date(val);
      var Z = new Date(reservationZ[key]);
      html += dateFormat(A) + "～"
      if (A.getMonth() == Z.getMonth() && A.getDate() == Z.getDate()) {
        html += Z.getHours() + ':' + Z.getMinutes();
      } else {
        html += dateFormat(Z);
      }
      html += ' ';
    })
  } else {
    var w = new Array();
    var na = new Array();
    var val = new Array();
    var typ = new Array();
    var pre = "";
    var c = 0;
    $('#where input').each(function (i, e) {
      if (e.type != 'checkbox' && e.value.length || e.type == 'checkbox' && e.checked) {
        na.push(e.className);
        val.push(e.value);
        typ.push(e.type);
        switch (e.type) {
          case "number":
            var f = (pre.substr(0, pre.length - 1) != e.className.substr(0, e.className.length - 1));
            html += (f && c) ? "　" : "";
            html += (f) ? $(e).parent("div").children("label:first-child").text() + ":" : "";
            html += e.value + $(e).next("label").text();
            break;
          case "checkbox":
            html += (pre != e.className && c) ? "　" : "";
            var s = $(e).parent('label').text();
            if (s.length) {
              html += (pre != e.className) ? $(e).parent('label').parent('div').children("label:first-child").text() + ":" + s : "、" + s;
            } else {
              html += $(e).prev('label').text() + ":〇";
            }
            break;
          default:
            html += (c) ? "　" : "";
            html += $(e).prev('label').text() + ":" + e.value;
        }
        pre = e.className;
        c++;
      }
    })
    w.push(na);
    w.push(val);
    w.push(typ);
    terms[condition] = w;
  }
  setSearch();
  $('#' + condition).next('div').remove();
  $('#' + condition).after(html + '</div>');
}
function setSearch() {
  where = [];
  var n = new Array();
  var v = new Array();
  var t = new Array();
  $.each(terms, function (key, val) {
    for (var j = 0; j < terms[key][0].length; j++) {
      n.push(terms[key][0][j]);
      v.push(terms[key][1][j]);
      t.push(terms[key][2][j]);
    }
  });
  if (n.length) {
    where.push(n);
    where.push(v);
    where.push(t);
  }
  setPointMarker(where);
}

function Cancel() {
  popClose();
  $("#Close").attr('onclick', "Close()");
  $("#reservcars").empty();
  /*
  scheA = [];
  scheZ = [];
  resvA = [];
  resvZ = [];
  bookA = [];
  bookZ = [];
  bookid = [];
  ttX = [];
  */
}

function termClear(condition) {
  $('#where input').each(function (i, e) {
    if (e.type == "checkbox") {
      e.checked = false;
    } else {
      e.value = "";
    }
  });
}

function termSave(overWrite) {
  if (Object.keys(terms).length || typeof map !== "undefined") {
    if (typeof map === "undefined") {
      var lat;
      var lng;
      var zm;
    } else {
      var mapcenter = map.getCenter();
      var lat = mapcenter.lat();
      var lng = mapcenter.lng();
      var zm = map.getZoom();
    }
    $.ajax({
      url: 'json/term.php',
      type: 'POST',
      dataType: 'json',
      data: {
        'terms': JSON.stringify(terms),
        'searchna': $('#search_na').val(),
        'overWrite': overWrite,
        'lat': lat,
        'lng': lng,
        'zm': zm
      },
      error: function () {
        alert('条件保存のためのajax通信に失敗しました。')
      },
      success: function (json) {
        if (json.overWrite) {
          if (confirm(json.msg)) {
            termSave(1);
          }
        } else {
          alert($('#search_na').val() + "を保存しました。");
        }
      }
    });
  } else {
    alert('条件を設定してください。');
  }
}

$("#termLoad").change(function () {
  $.ajax({
    url: 'json/term.php',
    type: 'POST',
    dataType: 'json',
    data: {
      'no': $(this).val(),
    },
    error: function () {
      alert('条件読み込みのためのajax通信に失敗しました。')
    },
    success: function (json) {
      terms = {};
      var html = {};
      var c = [];
      var pre = "";
      var i;
      for (i = 0; i < json.length - 1; i++) {
        condition = json[i].condition_;
        var na = json[i].na;
        var val = json[i].val;
        var typ = json[i].typ;
        if (!terms.hasOwnProperty(condition)) {
          var nas = new Array();
          var vals = new Array();
          var typs = new Array();
          nas.push(na);
          vals.push(val);
          typs.push(typ);
          var w = new Array();
          w.push(nas);
          w.push(vals);
          w.push(typs);
          terms[condition] = w;
          html[condition] = "<div>";
          c[condition] = 0;
        } else {
          terms[condition][0].push(na);
          terms[condition][1].push(val);
          terms[condition][2].push(typ);
        }
        switch (typ) {
          case "number":
            var f = (pre.substr(0, pre.length - 1) != na.substr(0, na.length - 1));
            html[condition] += (f && c[condition]) ? "　" : "";
            html[condition] += (f) ? json[i].name + ":" : "";
            html[condition] += val + json[i].unit;
            break;
          case "checkbox":
            html[condition] += (pre != na && c[condition]) ? "　" : "";
            if (json[i]['tbl']) {
              html[condition] += (pre != na) ? json[i].name + ":" + json[i].option : "、" + json[i].option;
            } else {
              html[condition] += json[i].name + ":〇";
            }
            break;
          default:
            html[condition] += (c[condition]) ? "　" : "";
            html[condition] += json[i].name + ":" + val;
        }
        pre = na;
        c[condition]++;
      }
      if (typeof json[i].lat != "undefined" && json[i].lat !== null && typeof map !== "undefined") {
        var latlng = new google.maps.LatLng(Number(json[i].lat), Number(json[i].lng));
        map.setCenter(latlng);
        map.setZoom(Number(json[i].zm));
      }
      setSearch();
      var termNames = ['basic', 'insurance', 'equip'];
      for (i = 0; i < termNames.length; i++) {
        $('#' + termNames[i]).next('div').remove();
      }
      $.each(html, function (key) {
        $('#' + key).after(html[key] + '</div>');
      });
    }
  });
});
function termDelete() {
  na = $('#termLoad').children(':selected').text();
  if (confirm(na + "を削除しますか。")) {
    $.ajax({
      url: 'json/term.php',
      type: 'POST',
      dataType: 'json',
      data: {
        'delete': $('#termLoad').val(),
      },
      error: function () {
        alert('条件削除のためのajax通信に失敗しました。')
      },
      success: function (json) {
        if (json.msg == 'delete ok') {
          alert(na + "を削除しました。");
        } else {
          alert(json.msg);
        }
      }
    });
  }
}