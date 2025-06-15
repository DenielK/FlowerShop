<?php
// Весь PHP-код, включая session_start() и header(), должен быть в самом начале файла
session_start();
require('zoneconf.php');

global $yhendus;

if (!empty($_POST['login']) && !empty($_POST['pass'])) {
    $login = htmlspecialchars(trim($_POST['login']));
    $pass = htmlspecialchars(trim($_POST['pass']));

    $sool = 'cool';
    $krypt = crypt($pass, $sool);

    $paring = $yhendus->prepare("SELECT kasutaja_nimi, parool, onadmin FROM kasutajad WHERE kasutaja_nimi=? AND parool=?");
    $paring->bind_param('ss', $login, $krypt);
    $paring->bind_result($kasutaja, $parool, $onadmin);
    $paring->execute();

    if ($paring->fetch() && $parool === $krypt) {
        $_SESSION['kasutaja'] = $kasutaja;
        $_SESSION['admin'] = ($onadmin == 'admin'); // 'admin' из enum, не 1
        header('Location: tellimus.php');
        exit(); // Важно всегда использовать exit() после header('Location: ...')
    } else {
        // Здесь мы сохраняем сообщение об ошибке в сессии, чтобы вывести его позже в HTML
        $_SESSION['error_message'] = "<p style='color:red;'>Kasutajanimi või parool on vale.</p>";
        // Если выводите сообщение сразу, убедитесь, что это не нарушает заголовки
        // Для демонстрации, пусть это будет внутри else, но лучше выводить после всего PHP
    }

    $paring->close();
    // $yhendus->close(); // Закрытие соединения здесь может быть проблемой, если оно нужно дальше в скрипте.
    // Обычно соединение закрывают в конце скрипта или используют постоянное соединение.
}
?>
<header>
    <h3>Admin account - admin/admin</h3>
    <h3>Normal user account - test/test</h3>
</header>
<?php
// Вывод сообщения об ошибке, если оно есть
if (isset($_SESSION['error_message'])) {
    echo $_SESSION['error_message'];
    unset($_SESSION['error_message']); // Удаляем сообщение после вывода, чтобы оно не показывалось повторно
}
?>
<h1>Login</h1>
<link rel="stylesheet" href="style.css">
<form action="" method="post">
    <table>
        <tr>
            <td>
                <label for="login">Login:</label>
            </td>
            <td>
                <input type="text" id="login" name="login">
            </td>
        </tr>
        <tr>
            <td>
                <label for="login">Password:</label>
            </td>
            <td>
                <input type="password" id="password" name="pass">
            </td>
        </tr>
        <tr>
            <td></td>
            <td class="align-right">
                <input type="submit" value="Logi sisse"</td>
        </tr>
    </table>
    <p><a href="registration.php" class="registration-link">Registration page ></a></p>
</form>
<?php
include("footer.php");
?>