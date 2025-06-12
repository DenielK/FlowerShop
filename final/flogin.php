<?php include('conf.php'); ?>
<?php
session_start();

if (!empty($_POST['login']) && !empty($_POST['pass'])) {
$login = htmlspecialchars(trim($_POST['login']));
$pass = htmlspecialchars(trim($_POST['pass']));
global $yhendus;

    $sool = 'cool';
    $krypt = crypt($pass, $sool);
    $paring = $yhendus->prepare("SELECT kasutaja, parool, onadmin FROM kasutajad WHERE kasutaja=? AND parool=?");
    $paring->bind_param('ss', $login, $krypt);
    $paring->bind_result($login, $password, $onadmin);
    $paring->execute();
    //$valjund = mysqli_query($yhendus, $paring);
    //kui on, siis loome sessiooni ja suuname
    /*if (mysqli_num_rows($valjund)==1) {
        $_SESSION['tuvastamine'] = 'misiganes';
        header('Location: final.php');
    } else {
        echo "kasutaja või parool on vale";
    }
}*/
    if($paring->fetch() && $password=$krypt) {
        $_SESSION['kasutaja'] = $login;
        if($onadmin==1) {
            $_SESSION['admin'] = true;
        }
        header('Location: final.php');
        $yhendus->close();
    } else {
        echo "kasutaja või parool on vale";
        $yhendus->close();
    }
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
                <td>
                    <input type="submit" value="Logi sisse"</td>
            </tr>
        </table>
        <h2>Registration</h2>
        <p><a href="registration.php">Registration page ></a></p>
    </form>
