<script type="text/javascript" src="js/jquery-3.1.1.min.js"></script>
<script type="text/javascript" src="js/favorite.js"></script>
<script type="text/javascript">
  var map;
  var geocoder;
  var marker_ary = new Array();
  var currentInfoWindow;
  var myimg;
  var carid;
  var carids = new Array();

  function initMap() {
    var lat = <?php if(isset($lat)){echo $lat;}else{echo 35.6845;} ?>;
    var lng = <?php if(isset($lng)){echo $lng;}else{echo 139.7521;} ?>;
    var zm = <?php if(isset($zm)){echo $zm;}else{echo 11;} ?>;
    var maxzm = <?php if(isset($maxzm)){echo $maxzm;}else{echo 15;} ?>;
    var marker = <?php if(isset($marker)){echo"'$marker'";}else{echo 0;} ?>;
    carid = <?php if(isset($carid)){echo $carid;}else{echo 0;} ?>;
    var latlng = new google.maps.LatLng(lat, lng);
    var myOptions = {
      zoom: zm,
      center: latlng,
      mapTypeId: google.maps.MapTypeId.ROADMAP,
      minZoom: 5,
      maxZoom: maxzm
    };
    map = new google.maps.Map(document.getElementById("map_canvas"), myOptions);
    if (marker != "none") {
      google.maps.event.addListener(map, 'idle', function() {
        setPointMarker("", lat, lng, marker);
      });
    }
    myimg = new google.maps.MarkerImage('/img/marker.png');
  }

  function MarkerClear() {
    if (marker_ary.length > 0) { //マーカー削除
      for (i = 0; i < marker_ary.length; i++) {
        marker_ary[i].setMap();
      }
      for (i = 0; i <= marker_ary.length; i++) { //配列削除
        marker_ary.shift();
      }
    }
  }

  function MarkerSet(lat, lng, id, na, price, i, stop) {
    var marker_num = marker_ary.length;
    var marker_position = new google.maps.LatLng(lat, lng);
    var markerOpts = {
      map: map,
      position: marker_position,
      label: String(i),
    };
    var alert = (stop) ? "<div class='alert'>coming soon</div>" : "";
    marker_ary[marker_num] = new google.maps.Marker(markerOpts);
    if (id == carid || id == 0) {
      marker_ary[marker_num].setIcon(myimg);
      marker_ary[marker_num].setLabel(null);
    }
    if (na.length > 0) { //textが渡されていたらふきだしをセット
      var infoWndOpts = {
        maxWidth: 200,
        content: "<div class='marker'><a href='./car.php?carid=" + id + "'><img src='./img/" + id + "/s-0.jpg'></a>" + alert + na + " ￥" + price + "</div>"
      };
      var infoWnd = new google.maps.InfoWindow(infoWndOpts);
      google.maps.event.addListener(marker_ary[marker_num], "click", function() {
        if (currentInfoWindow) { //先に開いた情報ウィンドウがあれば、closeする
          currentInfoWindow.close();
        }
        infoWnd.open(map, marker_ary[marker_num]); //情報ウィンドウを開く
        currentInfoWindow = infoWnd; //開いた情報ウィンドウを記録しておく
      });
    }
  }

  function setPointMarker(json, lat, lng, marker) { //XMLで取得した地点を地図上でマーカーに表示
    $('ol').empty(); //リストの内容を削除
    MarkerClear();
    if (lat && lng && carid) {
      MarkerSet(lat, lng, carid, "");
    }
    if (marker != "secret") {
      var bounds = map.getBounds(); //地図の範囲内を取得
      $.ajax({ //json取得
        url: 'json/map.php',
        type: 'POST',
        dataType: 'json',
        data: {
          'nlat': bounds.getNorthEast().lat(),
          'slat': bounds.getSouthWest().lat(),
          'nlng': bounds.getNorthEast().lng(),
          'slng': bounds.getSouthWest().lng(),
          'carid': carid,
          'reservA[]': reservationA,
          'reservZ[]': reservationZ,
          'where': JSON.stringify(json)
        },
        error: function() {
          $('#message').html("表示範囲にクルマの登録はありません。");
          setTimeout(function() {
            $('#message').html('');
          }, 3000);
        },
        success: function(json) {
          var numAdd = 1;
          $(json).each(function(i, v) {
            var p = 0;
            var regday = new Date(v['reg_day']);
            var okday = new Date(v['ok_day']);
            var reday = new Date(v['re_day']);
            var today = new Date();
            var stop = (regday <= okday && v['reg_day'] !== null && v['ok_day'] !== null && reday > today) ? false : true;
            $(reservationA).each(function(ii, vv) {
              p += culcPrice(vv, reservationZ[ii], v['price'], v['holiday_price'], v['ext_price'], v['short_price'], v['short_hour'], v['long_price'], v['long_date']);
            });
            p = (p) ? p : v['price'];
            if (v['id'] == carid) {
              numAdd -= 1;
            } else {
              MarkerSet(v['lat'], v['lng'], v['id'], v['na'], p, i + numAdd, stop); //マーカーをセット
              var marker_num = marker_ary.length - 1; //リスト表示、リストに対応するマーカー配列キーをセット
              var loc = $('<a href="javascript:void(0)"/>').text(v['na'] + " ￥" + p);
              loc.bind('click', function() { //セットしたタグにイベント「マーカーがクリックされた」をセット
                google.maps.event.trigger(marker_ary[marker_num], 'click');
              });
              var a = $('<li>').append(loc);
              a.append($('<button onclick="favorite(' + id + ',' + v['id'] + ')">★</button>'));
              if (!stop) {
                a.append($('<label><input type="checkbox" class="carids" onclick="changeback(this)" style="display:none;" value="' + v['id'] + '">♪</label>'));
              }
              $('ol').append(a);
            }
          });
          $("#carlist div").remove();
          if ($("ol li").length) {
            $('#carlist').append("<div><button onclick='reservCheck()'>♪を予約する</button></div>")
          } else {
            $('#carlist').append("<div>該当するクルマは表示範囲内にありません。</div>");
          }
        }
      });
    }
  }
  $(function() {
    $("#setaddr").on('change', function() { //住所から地図を移動
      document.getElementById("getaddr").innerText = "移動中です・・・";
      var geocoder = new google.maps.Geocoder();
      geocoder.geocode({
        'address': this.value
      }, function(results, status) {
        if (status == google.maps.GeocoderStatus.OK) {
          map.setCenter(results[0].geometry.location);
          document.getElementById("getaddr").innerText = results[0].formatted_address;
        } else {
          alert("住所が見つかりませんでした。" + status);
        }
      });
    });
  });

  function codeLatlng(callback) { //地図中央の座標と住所を取得
    var mapcenter = map.getCenter(); //現在地取得
    var lat = mapcenter.lat();
    var lng = mapcenter.lng();
    var latlng = new google.maps.LatLng(lat, lng);
    var geocoder = new google.maps.Geocoder();
    var address;
    document.body.style.cursor = 'wait';
    document.getElementById("getaddr").innerText = "取得中です・・・";
    geocoder.geocode({
      'latLng': latlng
    }, function(results, status) {
      document.body.style.cursor = 'auto';
      if (status == google.maps.GeocoderStatus.OK) {
        address = results[0].formatted_address;
        if (callback) {
          callback(lat, lng, address);
        }
        document.getElementById("getaddr").innerText = address;
      } else {
        document.getElementById("getaddr").innerText = "";
        callback(lat, lng, "error");
      }
    });
  }

  function setlocation() {
    getlocation(function(lat, lng) {
      var latlng = new google.maps.LatLng(lat, lng);
      map.setCenter(latlng);
    });
  }

  function getlocation(callback) {
    document.getElementById("getaddr").innerHTML = '現在地取得中・・・';
    if (navigator.geolocation) {
      navigator.geolocation.getCurrentPosition(function(result) {
        if (callback) {
          document.getElementById("getaddr").innerHTML = '';
          callback(result.coords.latitude, result.coords.longitude);
        }
      }, function(err) {　　　
        document.getElementById("getaddr").innerText = "本ブラウザでは現在位置取得できません。";
      });
    } else {
      document.getElementById("getaddr").innerHTML = "位置情報サービスを有効にしてください。";
    }
  }

  function changeback(Myid) { //チェックボックスの背景色変更
    // Myid = document.getElementById(chkID);
    if (Myid.checked == true) {
      Myid.parentNode.style.color = 'black';
    } else {
      Myid.parentNode.style.color = 'lightgray'; //背景色
    }
  }
</script>
<div id="maparea">
  <div id="map">
    <div>
      <input type="text" size="30" id="setaddr" />
      <label>〒か住所を入力、または
      </label>
      <button type="button" onclick="setlocation()">現在地</button>
    </div>
    <div style="display:flex;margin 5px">
      <button type="button" onclick="codeLatlng()">地図中央の住所を取得</button>
      <div id="getaddr"></div>
    </div>
    <div id="map_canvas"></div>
  </div>
  <div id='carlist'>
    <ol>
      <li></li>
    </ol>
  </div>
</div>
<script async defer src="https://maps.googleapis.com/maps/api/js?key=AIzaSyDTHLyzh5B37YJPU8esWD0fV0ntvE9QOwI&callback=initMap">
</script>