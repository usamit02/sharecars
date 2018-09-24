var oldvals = [];
$(document).ready(function () {
  $("#reg input:not(#latlng):not(#id):not([type='checkbox'])").each(function () {
    if ($(this).css('display') != 'no') { oldvals[$(this).attr('id')] = this.value; }
  });
  $("#reg textarea").each(function () {
    oldvals[$(this).attr('id')] = this.value;
  });
  $("#reg input[type='checkbox']").each(function () {
    oldvals[$(this).attr('id')] = ($(this).prop('checked')) ? "〇" : "×";
  });
  $("#reg option:selected").each(function () {
    oldvals[$(this).parent('select').attr('id')] = this.innerHTML;
  });
  if ($("#insurance_owner").length) { insurance_owner($("#insurance_owner")); }
  if ($("#carcompensation").length) { carcompensation($("#carcompensation")); }
});

function beforesend() {
  var values = new Object();
  var units = new Object();
  var err = false;
  $("#reg input:not(#latlng):not(#id):not([type='checkbox'])").each(function () {
    $(this).css('border', '');
    if ($(this).css('display') == 'no') {
      $(this).val(null);
    } else {
      values[$(this).attr('id')] = this.value;
      units[$(this).attr('id')] = $(this).nextAll("div").text()
      if ($(this).val().length == 0) {
        if ($(this).prop("required")) {
          senderr(this, "は必須入力です。");
          err = true;
        }
      } else {
        switch ($(this).attr("type")) {
          case "text":
            if ($(this).val().length > $(this).attr("maxlength")) {
              senderr(this, "は" + $(this).attr("maxlength") + "文字以下です。あと" + ($(this).val().length - $(this).attr("maxlength")) + "文字減らしてください。");
              err = true;
            };
            if (typeof $(this).attr('pattern') != 'undefined') {
              var matches = "/" + $(this).attr('pattern') + "/";
              if (matches.exec($(this).val())) {
                var msg = ($(this).attr('id') == 'line_url') ? "http://line.me/ti/p/以下の英数字のみ入力してください。" : "記号等入力できない文字が含まれています。";
                senderr(this, msg);
                err = true;
              }
            }
            break;
          case "number":
            if (!isNaN($(this).val())) {
              if (Number($(this).val()) < $(this).attr("min")) {
                senderr(this, "は" + $(this).attr("min") + "以上にしてください。");
                err = true;
              }
              if (Number($(this).val()) > $(this).attr("max")) {
                senderr(this, "は" + $(this).attr("max") + "以下にしてください。");
                err = true;
              }

            } else {
              senderr(this, "には数字を入力してください。");
              err = true;
            }
            break;
          case "date":
            if (!isdate($(this).val())) {
              senderr(this, "は年/月/日（年、月、日は数字のみ）で入力してください。");
              err = true;
            } else {
              age = getage(new Date($(this).val()));
              if (age < $(this).attr("minage") || age > $(this).attr("maxage")) {
                senderr(this, "は対象外の日付です。");
                err = true;
              }
            }
            break;
          case "email":
            if (!$(this).val().match(/[!#-9A-~]+@+[a-z0-9]+.+[^.]$/i)) {
              senderr(this, "を正しく入力してください。");
              err = true;
            }
            break;
        }
      }
    }
  })
  $("#reg textarea").each(function () {
    values[$(this).attr('id')] = this.value;
    $(this).css('border', '');
    if ($(this).val().length > 500) {
      senderr(this, "は500文字以下です。あと" + ($(this).val().length - 500) + "文字減らしてください。");
      err = true;
    }
  })
  $("#reg input[type='checkbox']").each(function () {
    if ($(this).prop('checked')) {
      values[$(this).attr('id')] = "〇";
    } else {
      values[$(this).attr('id')] = "×";
      $(this).prop('checked', false);
    }
  })
  $("#reg option:selected").each(function () {
    values[$(this).parent('select').attr('id')] = this.innerHTML;
  })
  if (err) {
    return false;
  } else {
    var auth = "";
    var confirm_message = "";
    $.each(values, function (key, newvalue) {
      if (newvalue != oldvals[key]) {
        var unit = (units[key] === undefined) ? "" : units[key];
        confirm_message += $("#" + key).next("label").text() + '：' + newvalue + unit + '\n';
        auth += ($("#" + key).data('auth')) ? $("#" + key).next("label").text() + '、' : "";
      }
    });
    if (confirm_message.length) {
      return (auth.length) ? auth.substr(0, auth.length - 1) + "を変更するとマスターへ証拠書類の再提出が必要になります。認証が済むまで数日システムの利用が制限されますが、本当に変更しますか？\n(新規登録はOKをクリック)\n\n" + confirm_message : "以下の内容で登録します。\n\n" + confirm_message;
    } else {
      return false;
    }
  }
}
function senderr(e, message) {
  $(e).focus();
  $(e).css('border', '2px solid red');
  alert($(e).nextAll("label").text() + message);
}

function isdate(s) {
  var matches = /^(\d+)\/(\d+)\/(\d+)$/.exec(s.replace(/-/g, "/"));
  if (!matches) {
    return false;
  }
  var y = parseInt(matches[1]);
  var m = parseInt(matches[2]);
  var d = parseInt(matches[3]);
  if (m < 1 || m > 12 || d < 1 || d > 31) {
    return false;
  }
  var dt = new Date(y, m - 1, d, 0, 0, 0, 0);
  if (dt.getFullYear() != y || dt.getMonth() != m - 1 || dt.getDate() != d) {
    return false;
  }
  return true;
}

function getage(birthday) {
  var today = new Date();
  var age = today.getFullYear() - birthday.getFullYear();
  var day = new Date(today.getFullYear(), birthday.getMonth(), birthday.getDate());
  if (today < day) {
    age--;
  }
  return age;
}