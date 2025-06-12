<?php
include('conf.php');
session_start();
global $yhendus;
$error = "";
$success = "";

if (!empty($_POST['login']) && !empty($_POST['pass'])) {
    $login = htmlspecialchars(trim($_POST['login']));
    $pass = htmlspecialchars(trim($_POST['pass']));
    $sool = "cool";
    $krypt = crypt($pass, $sool);

    // Если админ — можно выбрать onadmin
    $onadmin = (isset($_SESSION['admin']) && $_SESSION['admin'] && isset($_POST['onadmin'])) ? 1 : 0;

    // Проверим, есть ли уже такой пользователь
    $paring = $yhendus->prepare("SELECT id FROM kasutajad WHERE kasutaja=?");
    $paring->bind_param("s", $login);
    $paring->execute();
    $paring->store_result();

    if ($paring->num_rows > 0) {
        $error = "Kasutaja on juba olemas!";
    } else {
        $paring->close();
        $paring = $yhendus->prepare("INSERT INTO kasutajad (kasutaja, parool, onadmin) VALUES (?, ?, ?)");
        $paring->bind_param("ssi", $login, $krypt, $onadmin);
        if ($paring->execute()) {
            $success = "Kasutaja loodud edukalt!";
        } else {
            $error = " Viga registreerimisel.";
        }
    }

    $paring->close();
    $yhendus->close();
}
?>

<!DOCTYPE html>
<html lang="et">
<head>
    <meta charset="UTF-8">
    <title>Registreerimine</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<h1>Registreerimine</h1>

<?php if ($error): ?>
    <p style="color: red; font-weight: bold;"><?= $error ?></p>
<?php endif; ?>

<?php if ($success): ?>
    <p style="color: green; font-weight: bold;"><?= $success ?></p>
<?php endif; ?>

<form action="" method="post">
    <table>
        <tr>
            <td>Kasutaja Nimi</td>
            <td><input type="text" name="login" required></td>
        </tr>
        <tr>
            <td>Password</td>
            <td><input type="password" name="pass" required></td>
        </tr>
        <!--
        <?php if (isset($_SESSION['admin']) && $_SESSION['admin']): ?>
            <tr>
                <td>Admin õigused?</td>
                <td><input type="checkbox" name="onadmin" value="1"> Jah</td>
            </tr>
        <?php endif; ?>
        -->
        <tr>
            <td></td>
            <td><input type="submit" value="Registreeri" class="btn-link"></td>
        </tr>
    </table>
</form>

<p><a href="flogin.php">< Tagasi sisselogimise juurde</a></p>
</body>
</html>