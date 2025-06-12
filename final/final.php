<?php
session_start();
require("conf.php");
require("abifunktsioonid.php");

// Ensure 'admin' session variable is initialized
if (!isset($_SESSION['admin'])) {
    $_SESSION['admin'] = false;
}
// Redirect if not logged in (assuming 'flogin.php' is the login page)
if (!isset($_SESSION['kasutaja'])) {
    header("Location: flogin.php");
    exit();
}

// Function to check if the user is an admin
function isAdmin() {
    return isset($_SESSION['admin']) && $_SESSION['admin'];
}

// --- Обработка запросов (Request Handling) ---

// Handle adding a product
if (isset($_REQUEST["toodelisamine"]) && isAdmin() && !empty(trim($_REQUEST["nimetus"]))) {
    // Ensure 'hind' is a valid number, default to 0.00 if not
    $hind = floatval($_REQUEST["hind"]);
    lisaToode($_REQUEST["nimetus"], $hind, $_REQUEST["kirjeldus"]);
    header("Location: tooteHaldus.php");
    exit();
}

// Handle deleting a product
if (isset($_REQUEST["kustutusid"]) && isAdmin()) {
    kustutaToode($_REQUEST["kustutusid"]);
    header("Location: tooteHaldus.php"); // Redirect after deletion to refresh the page
    exit();
}

// Handle modifying a product
if (isset($_REQUEST["muutmine"])) {
    // Ensure 'hind' is a valid number, default to 0.00 if not
    $hind = floatval($_REQUEST["hind"]);
    muudaToode($_REQUEST["muudetudid"], $_REQUEST["nimetus"], $hind, $_REQUEST["kirjeldus"]);
    header("Location: tooteHaldus.php"); // Redirect after modification to refresh the page
    exit();
}

// Get product data for display
$tooted = kysiTooted(); // Changed from kysiToodeteAndmed()
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
            <dd><input type="text" name="nimetus" required /></dd>
            <dt>Hind:</dt>
            <dd><input type="number" step="0.01" name="hind" required /></dd>
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
                <?php if (isset($_REQUEST["muutmisid"]) && intval($_REQUEST["muutmisid"]) == $toode->id): // Changed toode->id ?>
                    <td>
                        <input type="submit" name="muutmine" value="Muuda" />
                        <input type="submit" name="katkestus" value="Katkesta" />
                        <input type="hidden" name="muudetudid" value="<?= $toode->id ?>" /> </td>
                    <td><input type="text" name="nimetus" value="<?= htmlspecialchars($toode->nimetus) ?>" /></td>
                    <td><input type="number" step="0.01" name="hind" value="<?= htmlspecialchars($toode->hind) ?>" /></td>
                    <td><textarea name="kirjeldus"><?= htmlspecialchars($toode->kirjeldus) ?></textarea></td>
                <?php else: ?>
                    <td>
                        <?php if (isAdmin()): ?>
                            <a href="tooteHaldus.php?kustutusid=<?= $toode->id ?>" onclick="return confirm('Kustutada?')">Kustuta</a> <?php endif; ?>
                        <a href="tooteHaldus.php?muutmisid=<?= $toode->id ?>">Muuda</a> </td>
                    <td><?= htmlspecialchars($toode->nimetus) ?></td>
                    <td><?= htmlspecialchars($toode->hind) ?></td>
                    <td><?= htmlspecialchars($toode->kirjeldus) ?></td>
                <?php endif; ?>
            </tr>
        <?php endforeach; ?>
    </table>
</form>

</body>
</html>