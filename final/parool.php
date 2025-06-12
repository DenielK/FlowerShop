<?php
$parool = 'kasutaja';
$sool = 'cool';
$krypt = crypt($parool, $sool);
echo $krypt;