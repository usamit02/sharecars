<?php
session_start();
require_once($_SERVER['DOCUMENT_ROOT']."/include/db.php");
include_once($_SERVER['DOCUMENT_ROOT'].'/include/head.php');
?>
  <HTML>

  <HEAD>
    <META HTTP-EQUIV='Content-Type' CONTENT='text/html;charset=UTF-8'>
    <title>シェアカーズ</title>
    <link rel="stylesheet" href="css/index.css">
    <link rel="stylesheet" href="css/calendar.css">
    <link rel="stylesheet" href="css/pop.css">
  </HEAD>

  <BODY>
    <div id="pop">
      <div id="pop_title">日時で検索</div>
      <div id="where"></div>
      <div id="calendar"></div>
      <div id="plans"></div>
      <div id="timetable"></div>
      <div id='pop_close'>
        <button onclick="Close()">設定</button>
      </div>
    </div>
    <div id="terms">
      <button id='daytime' onclick="seach('daytime')">日時</button>
      <button onclick="seach('basic')">条件－基本</button>
      <button onclick="seach('insurance')">条件－保険</button>
      <button onclick="seach('equip')">条件－装備</button>
    </div>
    <?php
include($_SERVER['DOCUMENT_ROOT'].'/include/map.php');?>
      <script type="text/javascript" src="js/pop.js"></script>
      <script type="text/javascript" src="js/calendar.js"></script>
      <script type="text/javascript" src="js/schedule.js"></script>
      <script type="text/javascript" src="js/reserv.js"></script>
      <script type="text/javascript">
        id = <?php echo $id;?>;
        var endDay, period;
        var where = [];
        $(document).ready(function() {

          if (window.innerWidth > 1000) {

          } else {

          }



        });

        function seach(condition) {
          if (condition == 'daytime') {
            endDay = new Date(toYear, toMonth, toDate);
            var lastDay = new Date(toYear, toMonth + 1, 0);
            if (endDay > lastDay) {
              endDay = lastDay;
            }
            period = Math.ceil((endDay - toDay) / 86400000);
            if (window.innerWidth > 1000) {
              makeCalendar(2);
              addPlan(-1);
            } else {
              makeCalendar(1);
              addPlan(-1);
              initCalendar(0);
            }
            reservInit();
            reservEvent();
            $.pop();
          } else {
            $.ajax({
              url: 'json/search.php?group=' + condition,
              type: 'GET',
              dataType: 'json',
              error: function() {
                alert('条件画面の作成に失敗しました。')
              },
              success: function(json) {
                var html = '';
                $(json).each(function(i, v) {
                  html += "<div>";
                  switch (v['typ']) {
                    case "select":
                      $.ajax({
                        url: 'json/search.php?group=' + v['tbl'],
                        type: 'GET',
                        dataType: 'json',
                        error: function() {
                          alert(json['name'] + 'の選択肢の作成に失敗しました。')
                        },
                        success: function(list) {
                          $(list).each(function(ii, vv) {
                            html += "<label class='check'><input type='checkbox' class='" + v['na'] + "' value='" + ii + "'>" + vv['na'] + "</label>";
                          });
                        }
                      });
                      break;
                    case "checkbox":
                      html += "<label class='check'><input type='checkbox' class='" + v['na'] + "' value='1'>" + v['na'] + "</label>";
                      break;
                    case "number":
                      html += "<input type='number' class='" + v['na'] + "'>" + v['unit'] + "以上<input type='number' class='" + v['na'] + "'>" + v['unit'] + "以下";
                      break;
                    default:
                      html += "<input type='text' class='" + v['na'] + "'>";
                  }
                  html += "<label for'" + v['na'] + "'>" + v['name'] + "</label></div>";
                });
                $("#where").append(html);
                $.pop();
              }
            });
          }
        }

        function Close(condition) {
          popClose();
          reservClose('index');
          setPointMarker();
        }
      </script>

  </BODY>

  </HTML>