<?php
require_once($_SERVER['DOCUMENT_ROOT'].'/include/id.php');
if (isset($_POST['logout'])&&session_status()===PHP_SESSION_ACTIVE) {
    unset($_SESSION['id']);
    unset($_SESSION['na']);
    unset($_SESSION['where']);
    unset($_SESSION['latlng']);
    include($_SERVER['DOCUMENT_ROOT'].'/include/id.php');
    //  unset($na);
}
if (isset($_SESSION['na'])) {
    echo '<div style="background-color:blue;color:white;width:100px;">'.$_SESSION['na'].'さん</div>';
    ?>
  <FORM ACTION="<?php basename(__FILE__) ?>" METHOD="post" ENCTYPE="multipart/form-data">
    <INPUT TYPE="submit" NAME="logout" VALUE="ログアウト">
  </FORM>
  <?php
} else {
    $_SESSION['state']=md5(microtime().mt_rand());
    $_SESSION['callback']=$_SERVER['REQUEST_URI'];
    $callback = urlencode('https://ss1.xrea.com/sharecars.s1003.xrea.com/logon.php');
    //$callback = urlencode('https://localhost/logon.php');
    $url = 'https://access.line.me/dialog/oauth/weblogin?response_type=code&client_id=1534503015&redirect_uri='.$callback.'&state='.$_SESSION['state'];
    echo '<a href=' . $url . '><img src="img/linelogin.png"></a>';
}
?>