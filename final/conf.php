<?php
$kasutaja = "denielkruusman";
$parool = "margosa12345";
$andmebaas = "flowershop";
$serverinimi = "localhost";

$yhendus = new mysqli($serverinimi, $kasutaja, $parool, $andmebaas);
$yhendus->set_charset("utf8");