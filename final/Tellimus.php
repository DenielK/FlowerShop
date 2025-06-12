<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
error_reporting(E_ALL);
ini_set('display_errors', 1);
require_once("abifunktsioonid.php");

if (!isset($_SESSION['kasutaja'])) {
    header("Location: login.php");
    exit();
}
global $yhendus;

function lisaTellimuseinfo($tellimus_id, $toode_id, $kogus, $uhiku_hind) {
    global $yhendus;
    $stmt = $yhendus->prepare("INSERT INTO tellimuseinfo (Tellimus_ID, Toode_ID, Kogus, Ühiku_hind) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("iiid", $tellimus_id, $toode_id, $kogus, $uhiku_hind);
    return $stmt->execute();
}

// Function to get product price - assumes it's in funktsioonid.php now
function getProductPrice($toode_id) {
    global $yhendus;
    $kask = $yhendus->prepare("SELECT Hind FROM toode WHERE Toode_ID = ?");
    $kask->bind_param("i", $toode_id);
    $kask->execute();
    $kask->bind_result($hind);
    $kask->fetch();
    $kask->close();
    return $hind;
}

// Обработка POST-запросов (без проверки onadmin())
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        if (isset($_POST['lisa_tellimus'])) { // Убрана проверка onadmin()
            $klient_id = $_POST['tellimus_klient'];
            $status = $_POST['status'];
            $kuupaev = $_POST['tellimus_kuupaev'];
            $toode_id = $_POST['tellimus_toode'];
            $kogus = $_POST['tellimus_tukk'];

            $tellimus_id = lisaTellimus($klient_id, $status, $kuupaev);
            if ($tellimus_id) {
                $uhiku_hind = getProductPrice($toode_id);
                if (lisaTellimuseinfo($tellimus_id, $toode_id, $kogus, $uhiku_hind)) {
                    $_SESSION['message'] = 'Order and order item added successfully!';
                } else {
                    $_SESSION['error'] = 'Failed to add order item!';
                }
            } else {
                $_SESSION['error'] = 'Failed to add order!';
            }
        } elseif (isset($_POST['muuda_Tellimused'])) { // Убрана проверка onadmin()
            $tellimus_id = $_POST['tellimus_id'];
            $status = $_POST['status'];
            $kuupaev = $_POST['tellimus_kuupaev'];

            $kask = $yhendus->prepare("UPDATE tellimus SET Status=?, Kuupaev=? WHERE Tellimus_ID=?");
            $kask->bind_param("ssi", $status, $kuupaev, $tellimus_id);
            if ($kask->execute()) {
                $_SESSION['message'] = 'Order (status and date) modified successfully!';
            } else {
                $_SESSION['error'] = 'Failed to modify order (status and date)!';
            }
            $kask->close();
        }

        header("Location: Tellimus.php");
        exit();

    } catch (Exception $e) {
        $_SESSION['error'] = 'Viga: ' . $e->getMessage();
        header("Location: Tellimus.php");
        exit();
    }
}

// Обработка GET-запросов для удаления (без проверки onadmin())
if (isset($_GET['kustuta_tellimuse_id'])) { // Убрана проверка onadmin()
    if (kustutaTellimus($_GET['kustuta_tellimuse_id'])) {
        $_SESSION['message'] = 'Order deleted successfully!';
    } else {
        $_SESSION['error'] = 'Failed to delete order!';
    }
    header("Location: Tellimus.php");
    exit();
}

// Получение данных
$otsisona = $_GET['otsi'] ?? '';
$sort = $_GET['sort'] ?? 'Kuupaev';

$tellimused_detailid = [];
$kask_tellimused = $yhendus->prepare("SELECT Tellimus_ID, Toode_Nimi, Kogus, Ühiku_hind, Kokku FROM tellimusetooted");
$kask_tellimused->execute();
$result_tellimused = $kask_tellimused->get_result();
while ($row = $result_tellimused->fetch_assoc()) {
    $tellimused_detailid[] = $row;
}
$kask_tellimused->close();

$kliendid_for_select = [];
$kliendid_query = $yhendus->query("SELECT Klient_ID, Nimi FROM klient ORDER BY Nimi");
while ($klient = $kliendid_query->fetch_assoc()) {
    $kliendid_for_select[] = $klient;
}

$tooted_for_select = [];
$tooted_query = $yhendus->query("SELECT Toode_ID, Nimetus FROM toode ORDER BY Nimetus");
while ($toode = $tooted_query->fetch_assoc()) {
    $tooted_for_select[] = $toode;
}

$main_orders = kysiTellimused($sort, $otsisona);
$order_map = [];
foreach ($main_orders as $order) {
    $order_map[$order->id] = $order;
}

?>
<!DOCTYPE html>
<html lang="en">
<header>
    <h2>FlowerShop from Deniel Kruusman</h2>
</header>
<head>
    <meta charset="UTF-8">
    <title>FlowerShop - Orders</title>
    <link rel="stylesheet" href="style.css">
    <script>
        window.tellimusEditData = <?= json_encode($order_map) ?>;

        function showEditForm(type, id) {
            if (type === 'tellimus') {
                const form = document.getElementById('edit-form-tellimus');
                form.style.display = 'block';

                const order = window.tellimusEditData[id];
                if (order) {
                    document.getElementById('edit_tellimuse_id').value = order.id;
                    document.getElementById('edit_status').value = order.status;
                    document.getElementById('edit_tellimus_kuupaev').value = order.kuupaev ? order.kuupaev.replace(' ', 'T') : '';
                }
            }
        }

        function hideEditForm(formId) {
            document.getElementById(formId).style.display = 'none';
        }
    </script>
    <script src="script.js"></script>
</head>
<body>

<nav>
    <div class="nav-container">
        <a href="Tellimus.php" class="nav-brand">FlowerShop</a>
        <ul>
            <li><a href="Tellimus.php" class="active">Orders</a></li>
            <li><a href="Toode.php">Products</a></li>

        </ul>
        <div class="nav-user">
            <span>Welcome, <?= htmlspecialchars($_SESSION['kasutaja']) ?>!</span>
            <a href="logout.php">Logout</a>
        </div>
    </div>
</nav>

<?php if (isset($_SESSION['message'])): ?>
    <div class="success-message"><?= $_SESSION['message'] ?></div>
    <?php unset($_SESSION['message']); ?>
<?php endif; ?>

<?php if (isset($_SESSION['error'])): ?>
    <div class="error-message"><?= $_SESSION['error'] ?></div>
    <?php unset($_SESSION['error']); ?>
<?php endif; ?>

<h2>Orders</h2>
<div class="container2">
    <div class="table-container4">
        <table>
            <tr>
                <th>Order ID</th>
                <th>Client's Name</th>
                <th>Status</th>
                <th>Product</th>
                <th>Quantity</th>
                <th>Unit Price</th>
                <th>Total Price</th>
                <th>Order Date</th>
                <th>Action</th> </tr>
            <?php foreach($tellimused_detailid as $tellimus_item):
                $main_order_info = $order_map[$tellimus_item['Tellimus_ID']] ?? null;
                ?>
                <tr>
                    <td><?= htmlspecialchars($tellimus_item['Tellimus_ID']) ?></td>
                    <td><?= htmlspecialchars($main_order_info->klient_nimi ?? 'N/A') ?></td>
                    <td><?= htmlspecialchars($main_order_info->status ?? 'N/A') ?></td>
                    <td><?= htmlspecialchars($tellimus_item['Toode_Nimi']) ?></td>
                    <td><?= htmlspecialchars($tellimus_item['Kogus']) ?></td>
                    <td><?= htmlspecialchars(number_format($tellimus_item['Ühiku_hind'], 2)) ?></td>
                    <td><?= htmlspecialchars(number_format($tellimus_item['Kokku'], 2)) ?></td>
                    <td><?= htmlspecialchars($main_order_info->kuupaev ?? 'N/A') ?></td>
                    <td>
                        <a href="?kustuta_tellimuse_id=<?= $tellimus_item['Tellimus_ID'] ?>" onclick="return confirm('Are you sure you want to delete this order? This will delete all items within this order and related payments/deliveries.')" style="font-style: italic;">Delete Order</a>
                        <button type="button" onclick="showEditForm('tellimus', <?= $tellimus_item['Tellimus_ID'] ?>)">Edit Status/Date</button>
                    </td>
                </tr>
            <?php endforeach; ?>
            <?php if (empty($tellimused_detailid)): ?>
                <tr>
                    <td colspan="9">No orders available.</td>
                </tr>
            <?php endif; ?>
        </table>
    </div>
</div>


<form action="Tellimus.php" method="post">
    <div class="form-box">
        <h2>Add New Order</h2>
        <label for="kliendi_id">Client:</label>
        <select name="tellimus_klient" id="kliendi_id" required>
            <?php foreach($kliendid_for_select as $klient): ?>
                <option value="<?= htmlspecialchars($klient['Klient_ID']) ?>"><?= htmlspecialchars($klient['Nimi']) ?></option>
            <?php endforeach; ?>
        </select>

        <label for="toote_id">Product:</label>
        <select name="tellimus_toode" id="toote_id" required>
            <?php foreach($tooted_for_select as $toode): ?>
                <option value="<?= htmlspecialchars($toode['Toode_ID']) ?>"><?= htmlspecialchars($toode['Nimetus']) ?></option>
            <?php endforeach; ?>
        </select>

        <label for="tellimus_tukk">Quantity:</label>
        <input type="number" min="1" name="tellimus_tukk" id="tellimus_tukk" required>

        <label for="status">Status:</label>
        <select name="status" id="status">
            <option value="Töötlemisel">Töötlemisel</option>
            <option value="Kinnitatud">Kinnitatud</option>
            <option value="Toimetatud">Toimetatud</option>
            <option value="Tühistatud">Tühistatud</option>
            <option value="80% tehtud">80% tehtud</option>
            <option value="50%">50%</option>
        </select>

        <label for="tellimus_kuupaev">Date:</label>
        <input type="datetime-local" name="tellimus_kuupaev" id="tellimus_kuupaev" required>

        <input type="submit" name="lisa_tellimus" value="Add New Order">
    </div>
</form>

<form action="Tellimus.php" method="post">
    <div id="edit-form-tellimus" class="form-box" style="display:none;">
        <h2>Change Order Status/Date</h2>
        <label for="edit_tellimuse_id">Order ID:</label>
        <input type="text" name="tellimus_id" id="edit_tellimuse_id" readonly>

        <label for="edit_status">Status:</label>
        <select name="status" id="edit_status">
            <option value="Töötlemisel">Töötlemisel</option>
            <option value="Kinnitatud">Kinnitatud</option>
            <option value="Toimetatud">Toimetatud</option>
            <option value="Tühistatud">Tühistatud</option>
            <option value="80% tehtud">80% tehtud</option>
            <option value="50%">50%</option>
        </select>

        <label for="edit_tellimus_kuupaev">Date:</label>
        <input type="datetime-local" name="tellimus_kuupaev" id="edit_tellimus_kuupaev">
        <br>
        <br>

        <input type="submit" name="muuda_Tellimused" value="Save Changes">
        <button type="button" id="cancelbutton" onclick="hideEditForm('edit-form-tellimus')">Cancel</button>
    </div>
</form>
</body>
</html>
<footer>
    <?php
    echo "Deniel Kruusman";
    echo "&copy; ";
    echo date("Y"); // y-year
    echo "<br>";
    echo "<strong>Contact info: </strong>";
    echo " denielkruusman@gmail.com";
    ?>
</footer>