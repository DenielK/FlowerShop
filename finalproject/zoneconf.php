<?php
$kasutaja = "d133849_deniel";
$parool = "margosa123498765";
$andmebaas = "d133849_flowershop";
$serverinimi = "d133849.mysql.zonevs.eu";

$yhendus = new mysqli($serverinimi, $kasutaja, $parool, $andmebaas);
$yhendus->set_charset("utf8");