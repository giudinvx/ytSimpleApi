<?php

require_once( "ytSimpleApi.php" );

$instanceyt = new ytSimpleApi("http://www.youtube.com/watch?v=the0KZLEacs");
echo $instanceyt->embed(400,250);
?>
<html><br/><b>Array</b><pre> <?php print_r($instanceyt->info());?></pre><br/><b>Only first 10 comment</b></html>
<?
$instanceyt->comment(10); //first 10 comment

//echo ytSimpleApi::embed("http://www.youtube.com/watch?v=the0KZLEacs");
//echo ytSimpleApi::info("http://www.youtube.com/watch?v=the0KZLEacs");
//ytSimpleApi::comment(10);
