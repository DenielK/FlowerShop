<?php
session_start();
if (!isset($_SESSION['tuvastamine'])) {
    header('Location: flogin.php');
    exit();
}
if(isset($_POST['logout'])){
    session_destroy();
    header('Location: final.php');
    exit();
}
?>