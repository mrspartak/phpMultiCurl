<?
$time = $_GET['time'] ? $_GET['time'] : $_POST['time'];

usleep($time);
echo $time;

echo $_COOKIE['foo'];