<?php
$f = fopen(__DIR__.'/../../mysql.ini', 'r');
$dsn = "mysql:host=".trim(fgets($f)).";charset=utf8";
$user = trim(fgets($f));
$password = trim(fgets($f));
$dsn = $dsn.";dbname=".trim(fgets($f));
fclose($f);
$db = new PDO($dsn, $user, $password);