<?php
session_start();
require_once($_SERVER['DOCUMENT_ROOT']."/include/db.php");
include_once($_SERVER['DOCUMENT_ROOT'].'/include/head.php');
?>
  <HTML>

  <HEAD>
    <META HTTP-EQUIV='Content-Type' CONTENT='text/html;charset=UTF-8'>
    <meta name="viewport" content="width=640">
    <title>シェアカーズ</title>
    <link rel="stylesheet" href="css/index.css">
    <link rel="stylesheet" href="css/calendar.css">
    <link rel="stylesheet" href="css/pop.css">
  </HEAD>

  <BODY>
    <script type="text/javascript">
      var id = <?php if(isset($id)){echo $id;}?>
    </script>
    <?php
include_once($_SERVER['DOCUMENT_ROOT'].'/include/term.php');
$lat=(isset($_SESSION['lat']))?$_SESSION['lat']:35.6845;
$lng=(isset($_SESSION['lng']))?$_SESSION['lng']:139.7521;
$zm=(isset($_SESSION['zm']))?$_SESSION['zm']:11;
include($_SERVER['DOCUMENT_ROOT'].'/include/map.php');
include($_SERVER['DOCUMENT_ROOT'].'/include/foot.php');
?>
      <script type="text/javascript">
        var endDay, period;

        $(document).ready(function() {

          if (window.innerWidth > 1000) {

          } else {

          }
        });
      </script>
      <script type="text/javascript" src="js2/poper.js"></script>
      <script type="text/javascript" src="js/calendar.js"></script>
      <script type="text/javascript" src="js/schedule.js"></script>
      <script type="text/javascript" src="js/reserv.js"></script>
      <script type="text/javascript" src="js/search.js"></script>


  </BODY>

  </HTML>