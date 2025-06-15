<?php
session_start();
require("zoneconf.php");
require("abifunktsioonid.php");

if (!isset($_SESSION['admin'])) {
    $_SESSION['admin'] = false;
}
if (!isset($_SESSION['kasutaja'])) {
    header("Location: flogin.php");
    exit();
}

function isAdmin() {
    return isset($_SESSION['admin']) && $_SESSION['admin'];
}

// --- Обработка запросов ---
if (isset($_REQUEST["toodelisamine"]) && isAdmin() && !empty(trim($_REQUEST["nimetus"]))) {
    lisaToode($_REQUEST["nimetus"], $_REQUEST["hind"], $_REQUEST["kirjeldus"]);
    header("Location: tooteHaldus.php");
    exit();
}

if (isset($_REQUEST["kustutusid"]) && isAdmin()) {
    kustutaToode($_REQUEST["kustutusid"]);
}

if (isset($_REQUEST["muutmine"])) {
    muudaToode($_REQUEST["muudetudid"], $_REQUEST["nimetus"], $_REQUEST["hind"], $_REQUEST["kirjeldus"]);
}

$tooted = kysiToodeteAndmed();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Toodete haldus</title>
    <meta charset="utf-8" />
    <link rel="stylesheet" href="style.css">
</head>
<body>

<p>Tere, <?= htmlspecialchars($_SESSION['kasutaja']) ?>!</p>

<form action="logout.php" method="post">
    <input type="submit" name="logout" value="Logi välja">
</form>

<h1>Tooted</h1>

<?php if (isAdmin()): ?>
    <h2>Toote lisamine</h2>
    <form action="tooteHaldus.php" method="get">
        <dl>
            <dt>Nimetus:</dt>
            <dd><input type="text" name="nimetus" /></dd>
            <dt>Hind:</dt>
            <dd><input type="text" name="hind" /></dd>
            <dt>Kirjeldus:</dt>
            <dd><textarea name="kirjeldus"></textarea></dd>
        </dl>
        <input type="submit" name="toodelisamine" value="Lisa toode" />
    </form>
<?php endif; ?>

<h2>Toodete loetelu</h2>
<form action="tooteHaldus.php" method="get">
    <table>
        <tr>
            <th>Haldus</th>
            <th>Nimetus</th>
            <th>Hind</th>
            <th>Kirjeldus</th>
        </tr>

        <?php foreach($tooted as $toode): ?>
            <tr>
                <?php if (isset($_REQUEST["muutmisid"]) && intval($_REQUEST["muutmisid"]) == $toode->Toode_id): ?>
                    <td>
                        <input type="submit" name="muutmine" value="Muuda" />
                        <input type="submit" name="katkestus" value="Katkesta" />
                        <input type="hidden" name="muudetudid" value="<?= $toode->Toode_id ?>" />
                    </td>
                    <td><input type="text" name="nimetus" value="<?= htmlspecialchars($toode->Nimetus) ?>" /></td>
                    <td><input type="text" name="hind" value="<?= htmlspecialchars($toode->Hind) ?>" /></td>
                    <td><textarea name="kirjeldus"><?= htmlspecialchars($toode->Kirjeldus) ?></textarea></td>
                <?php else: ?>
                    <td>
                        <?php if (isAdmin()): ?>
                            <a href="tooteHaldus.php?kustutusid=<?= $toode->Toode_id ?>" onclick="return confirm('Kustutada?')">Kustuta</a>
                        <?php endif; ?>
                        <a href="tooteHaldus.php?muutmisid=<?= $toode->Toode_id ?>">Muuda</a>
                    </td>
                    <td><?= htmlspecialchars($toode->Nimetus) ?></td>
                    <td><?= htmlspecialchars($toode->Hind) ?></td>
                    <td><?= htmlspecialchars($toode->Kirjeldus) ?></td>
                <?php endif; ?>
            </tr>
        <?php endforeach; ?>
    </table>
</form>

</body>
</html>
