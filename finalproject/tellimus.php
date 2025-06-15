<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require("zoneconf.php");
require_once("abifunktsioonid.php");

// Проверка авторизации
if (!isset($_SESSION['kasutaja'])) {
    header("Location: login.php");
    exit();
}
global $yhendus;

// Function to get product price
function getProductPrice($toode_id) {
    global $yhendus;
    $kask = $yhendus->prepare("SELECT toote_hind FROM tooted WHERE id = ?");
    $kask->bind_param("i", $toode_id);
    $kask->execute();
    $kask->bind_result($hind);
    $kask->fetch();
    $kask->close();
    return $hind;
}

// Проверка на админа
$is_admin = isset($_SESSION['admin']) && $_SESSION['admin'] === true;

// Обработка POST-запросов
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        if (isset($_POST['lisa_tellimus'])) {
            $klient_id = $_POST['tellimus_klient'];
            $status = $_POST['status'];
            $kuupaev = $_POST['tellimus_kuupaev'];
            $toode_id = $_POST['tellimus_toode'];
            $kogus = $_POST['tellimus_tukk'];

            $uhiku_hind = getProductPrice($toode_id);
            if ($uhiku_hind === null) {
                $_SESSION['error'] = 'Failed to get product price!';
                header("Location: tellimus.php");
                exit();
            }

            $tellimus_id = lisaTellimus($klient_id, $toode_id, $kogus, $status, $uhiku_hind, $kuupaev);
            if ($tellimus_id) {
                $_SESSION['message'] = 'Order added successfully!';
            } else {
                $_SESSION['error'] = 'Failed to add order!';
            }
        } elseif (isset($_POST['update_inline'])) { // Обработка inline-редактирования
            $tellimus_id = $_POST['order_id'];
            $new_status = $_POST['new_status'];
            $new_date = $_POST['new_date'];

            // Обновляем только статус и дату заказа
            $kask = $yhendus->prepare("UPDATE tellimused SET staatus=?, kuupaev=? WHERE id=?");
            $kask->bind_param("ssi", $new_status, $new_date, $tellimus_id);
            if ($kask->execute()) {
                $_SESSION['message'] = 'Order updated successfully!';
            } else {
                $_SESSION['error'] = 'Failed to update order.';
            }
            $kask->close();
        }

        header("Location: tellimus.php");
        exit();

    } catch (Exception $e) {
        $_SESSION['error'] = 'Viga: ' . $e->getMessage();
        header("Location: tellimus.php");
        exit();
    }
}

// Обработка GET-запросов для удаления (с проверкой на админа)
if (isset($_GET['kustuta_tellimuse_id'])) {

    if ($is_admin) {

        if (kustutaTellimus($_GET['kustuta_tellimuse_id'])) {

            $_SESSION['message'] = 'Order deleted successfully!';

        } else {

            $_SESSION['error'] = 'Failed to delete order!';

        }

    } else {

        $_SESSION['error'] = 'Access denied: You do not have permission to delete orders!';

    }

    header("Location: tellimus.php");

    exit();

}

// Получение данных
$otsisona = $_GET['otsi'] ?? '';
$sort = $_GET['sort'] ?? 'kuupaev';

$tellimused_detailid = [];
$kask_tellimused = $yhendus->prepare("
    SELECT
        t.id AS Tellimus_ID,
        k.kliendi_nimi AS Klient_Nimi,
        t.staatus AS Status,
        td.toote_nimi AS Toode_Nimi,
        t.kogus AS Kogus,
        t.tellimuse_hind AS Ühiku_hind,
        (t.kogus * t.tellimuse_hind) AS Kokku,
        t.kuupaev AS Kuupaev
    FROM
        tellimused t
    JOIN
        kliendid k ON t.kliendi_id = k.id
    JOIN
        tooted td ON t.toote_id = td.id
    WHERE
        t.kuupaev LIKE ? OR t.staatus LIKE ? OR k.kliendi_nimi LIKE ? OR td.toote_nimi LIKE ?
    ORDER BY " . $yhendus->real_escape_string($sort)
);
$otsisona_param = "%".$otsisona."%";
$kask_tellimused->bind_param("ssss", $otsisona_param, $otsisona_param, $otsisona_param, $otsisona_param);
$kask_tellimused->execute();
$result_tellimused = $kask_tellimused->get_result();
while ($row = $result_tellimused->fetch_assoc()) {
    $tellimused_detailid[] = $row;
}
$kask_tellimused->close();

$kliendid_for_select = [];
$kliendid_query = $yhendus->query("SELECT id, kliendi_nimi FROM kliendid ORDER BY kliendi_nimi");
while ($klient = $kliendid_query->fetch_assoc()) {
    $kliendid_for_select[] = $klient;
}

$tooted_for_select = [];
$tooted_query = $yhendus->query("SELECT id, toote_nimi FROM tooted ORDER BY toote_nimi");
while ($toode = $tooted_query->fetch_assoc()) {
    $tooted_for_select[] = $toode;
}

// Для inline-редактирования нам нужны данные заказа, доступные в JS
// map для быстрого доступа
$main_orders_for_js = [];
foreach ($tellimused_detailid as $item) {
    if (!isset($main_orders_for_js[$item['Tellimus_ID']])) {
        $order_obj = new stdClass();
        $order_obj->id = $item['Tellimus_ID'];
        $order_obj->status = $item['Status'];
        $order_obj->kuupaev = $item['Kuupaev'];
        $main_orders_for_js[$item['Tellimus_ID']] = $order_obj;
    }
}

?>
<!DOCTYPE html>
<html lang="en">
<header>
    <h2>FlowerShop from Deniel Kruusman</h2>
</header>
<header>
        <h2><a href="https://github.com/DenielK/FlowerShop" target="_blank" class="github-link">Creator GitHub</a></h2>
</header>
<head>
    <meta charset="UTF-8">
    <title>FlowerShop</title>
    <link rel="stylesheet" href="style.css">
    <script>
        // Передаем данные заказов для редактирования в JavaScript
        window.tellimusEditData = <?= json_encode($main_orders_for_js) ?>;

        function enableInlineEdit(orderId) {
            const row = document.getElementById('order-row-' + orderId);
            if (!row) return;

            // Скрываем текстовые поля, показываем поля ввода
            row.querySelector('.status-display').style.display = 'none';
            row.querySelector('.date-display').style.display = 'none';

            row.querySelector('.status-input').style.display = 'inline-block';
            row.querySelector('.date-input').style.display = 'inline-block';

            // Показываем кнопки "Save" и "Cancel", скрываем "Edit"
            row.querySelector('.edit-button').style.display = 'none';
            row.querySelector('.save-button').style.display = 'inline-block';
            row.querySelector('.cancel-button').style.display = 'inline-block';

            // Заполняем поля ввода текущими значениями
            const currentOrder = window.tellimusEditData[orderId];
            if (currentOrder) {
                row.querySelector('.status-input').value = currentOrder.status;
                // Форматируем дату для datetime-local
                row.querySelector('.date-input').value = currentOrder.kuupaev ? currentOrder.kuupaev.replace(' ', 'T') : '';
            }
        }

        function cancelInlineEdit(orderId) {
            const row = document.getElementById('order-row-' + orderId);
            if (!row) return;

            // Показываем текстовые поля, скрываем поля ввода
            row.querySelector('.status-display').style.display = 'inline-block';
            row.querySelector('.date-display').style.display = 'inline-block';

            row.querySelector('.status-input').style.display = 'none';
            row.querySelector('.date-input').style.display = 'none';

            // Скрываем кнопки "Save" и "Cancel", показываем "Edit"
            row.querySelector('.edit-button').style.display = 'inline-block';
            row.querySelector('.save-button').style.display = 'none';
            row.querySelector('.cancel-button').style.display = 'none';
        }

        function saveInlineEdit(orderId) {
            const row = document.getElementById('order-row-' + orderId);
            if (!row) return;

            const newStatus = row.querySelector('.status-input').value;
            const newDate = row.querySelector('.date-input').value;

            // Создаем скрытую форму и отправляем данные
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = 'tellimus.php'; // Отправляем на ту же страницу

            const orderIdInput = document.createElement('input');
            orderIdInput.type = 'hidden';
            orderIdInput.name = 'order_id';
            orderIdInput.value = orderId;
            form.appendChild(orderIdInput);

            const statusInput = document.createElement('input');
            statusInput.type = 'hidden';
            statusInput.name = 'new_status';
            statusInput.value = newStatus;
            form.appendChild(statusInput);

            const dateInput = document.createElement('input');
            dateInput.type = 'hidden';
            dateInput.name = 'new_date';
            dateInput.value = newDate;
            form.appendChild(dateInput);

            const updateFlag = document.createElement('input');
            updateFlag.type = 'hidden';
            updateFlag.name = 'update_inline'; // Флаг для серверной части
            updateFlag.value = '1';
            form.appendChild(updateFlag);

            document.body.appendChild(form); // Добавляем форму в DOM
            form.submit(); // Отправляем форму
        }

        // Функции для отображения/скрытия формы добавления
        function showAddOrderForm() {
            const formContainer = document.getElementById('add-order-form-container');
            const addButton = document.getElementById('add-order-button');

            formContainer.style.display = 'block';
            // Не скрываем кнопку, а центрируем ее
            addButton.style.display = 'block'; // Ensure it's block to allow margin: auto
            addButton.style.margin = '0 auto';
            addButton.style.visibility = 'hidden'; // Скрываем кнопку, чтобы она не мешала

            // Прокрутка к форме
            formContainer.scrollIntoView({ behavior: 'smooth', block: 'start' });
        }

        function hideAddOrderForm() {
            document.getElementById('add-order-form-container').style.display = 'none';
            const addButton = document.getElementById('add-order-button');
            addButton.style.visibility = 'visible'; // Показываем кнопку снова
            addButton.style.display = 'block'; // Убедимся, что это блочный элемент
            addButton.style.margin = '20px auto'; // Центрируем ее
        }
        // НОВАЯ ФУНКЦИЯ ДЛЯ УДАЛЕНИЯ КНОПКОЙ
        function confirmAndDeleteOrder(orderId) {
            if (confirm('Are you sure you want to delete this order?')) {
                // Перенаправляем пользователя на URL для удаления
                window.location.href = '?kustuta_tellimuse_id=' + orderId;
            }
        }
    </script>

</head>
<body>

<nav>
    <div class="nav-container">
        <h1 class="nav-brand" style="margin:0; padding:0; display:inline-block;">FlowerShop</h1> <ul>
            <li><a href="tellimus.php" class="active">Orders</a></li>
            <li><a href="toode.php">Products</a></li>
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
                <th>Action</th>
            </tr>
            <?php foreach($tellimused_detailid as $tellimus_item): ?>
                <tr id="order-row-<?= htmlspecialchars($tellimus_item['Tellimus_ID']) ?>">
                    <td><?= htmlspecialchars($tellimus_item['Tellimus_ID']) ?></td>
                    <td><?= htmlspecialchars($tellimus_item['Klient_Nimi']) ?></td>
                    <td>
                        <span class="status-display"><?= htmlspecialchars($tellimus_item['Status']) ?></span>
                        <select class="status-input" style="display:none;">
                            <option value="Uus">Uus</option>
                            <option value="Töötlemisel">Töötlemisel</option>
                            <option value="Kinnitatud">Kinnitatud</option>
                            <option value="Toimetatud">Toimetatud</option>
                            <option value="Tühistatud">Tühistatud</option>
                        </select>
                    </td>
                    <td><?= htmlspecialchars($tellimus_item['Toode_Nimi']) ?></td>
                    <td><?= htmlspecialchars($tellimus_item['Kogus']) ?></td>
                    <td><?= htmlspecialchars(number_format($tellimus_item['Ühiku_hind'], 2)) ?></td>
                    <td><?= htmlspecialchars(number_format($tellimus_item['Kokku'], 2)) ?></td>
                    <td>
                        <span class="date-display"><?= htmlspecialchars($tellimus_item['Kuupaev']) ?></span>
                        <input type="datetime-local" class="date-input" style="display:none;">
                    </td>
                    <td>
                        <button type="button" class="edit-button green-button btn-base" onclick="enableInlineEdit(<?= $tellimus_item['Tellimus_ID'] ?>)">Edit Status/Date</button>
                        <button type="button" class="save-button green-button btn-base" style="display:none;" onclick="saveInlineEdit(<?= $tellimus_item['Tellimus_ID'] ?>)">Save</button>
                        <button type="button" class="cancel-button blue-button btn-base" style="display:none;" onclick="cancelInlineEdit(<?= $tellimus_item['Tellimus_ID'] ?>)">Cancel</button>

                        <?php if ($is_admin): ?>
                            <button type="button"
                                    class="red-button btn-base"
                                    onclick="confirmAndDeleteOrder(<?= htmlspecialchars($tellimus_item['Tellimus_ID']) ?>)">
                                Delete Order
                            </button>
                        <?php endif; ?>
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

<div style="text-align: center; margin-top: 20px;">
    <button type="button" id="add-order-button" class="green-button btn-base" onclick="showAddOrderForm()">Add New Order</button>
</div>


<div id="add-order-form-container" class="form-box" style="display:none;">
    <h2>Add New Order</h2>
    <form action="tellimus.php" method="post">
        <label for="kliendi_id">Client:</label>
        <select name="tellimus_klient" id="kliendi_id" required>
            <?php foreach($kliendid_for_select as $klient): ?>
                <option value="<?= htmlspecialchars($klient['id']) ?>"><?= htmlspecialchars($klient['kliendi_nimi']) ?></option>
            <?php endforeach; ?>
        </select>

        <label for="toote_id">Product:</label>
        <select name="tellimus_toode" id="toote_id" required>
            <?php foreach($tooted_for_select as $toode): ?>
                <option value="<?= htmlspecialchars($toode['id']) ?>"><?= htmlspecialchars($toode['toote_nimi']) ?></option>
            <?php endforeach; ?>
        </select>

        <label for="tellimus_tukk">Quantity:</label>
        <input type="number" min="1" max="1000" name="tellimus_tukk" id="tellimus_tukk" required>

        <label for="status">Status:</label>
        <select name="status" id="status">
            <option value="Uus">Uus</option>
            <option value="Töötlemisel">Töötlemisel</option>
            <option value="Kinnitatud">Kinnitatud</option>
            <option value="Toimetatud">Toimetatud</option>
            <option value="Tühistatud">Tühistatud</option>
        </select>

        <label for="tellimus_kuupaev">Date:</label>
        <input type="datetime-local" name="tellimus_kuupaev" id="tellimus_kuupaev" value="<?= date('Y-m-d\TH:i') ?>" required>

        <div style="margin-top: 15px;">
            <input type="submit" name="lisa_tellimus" value="Add Order" class="btn-base blue-button">
            <button type="button" onclick="hideAddOrderForm()" class="btn-base blue-button">Cancel</button>
        </div>
    </form>
</div>

</body>
</html>
<footer>
    <?php
    echo "Deniel Kruusman";
    echo " &copy; ";
    echo date("Y");
    echo "<br>";
    echo "<strong>Contact info: </strong>";
    echo " denielkruusman@gmail.com";
    ?>
</footer>