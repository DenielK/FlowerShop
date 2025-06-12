<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once("abifunktsioonid.php");

// Проверка авторизации: если пользователь не залогинен, перенаправляем на страницу входа.
if (!isset($_SESSION['kasutaja'])) {
    header("Location: login.php"); // Убедитесь, что это правильный путь к вашей странице логина
    exit();
}

// Глобальный объект подключения к базе данных, используется функциями из abifunktsioonid.php
global $yhendus;

// Обработка POST-запросов
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Обработка добавления товара
        if (isset($_POST['lisa_Toode'])) {
            $nimetus = $_POST['nimetus'];
            $hind = floatval($_POST['hind']); // Преобразуем цену в число с плавающей точкой
            $kirjeldus = $_POST['kirjeldus'];

            if (lisaToode($nimetus, $hind, $kirjeldus)) {
                $_SESSION['message'] = 'Product added successfully!';
            } else {
                $_SESSION['error'] = 'Failed to add product.';
            }
        }
        // Обработка изменения товара
        elseif (isset($_POST['muuda_Toode'])) {
            $toote_id = $_POST['toote_id'];
            $nimetus = $_POST['nimetus'];
            $hind = floatval($_POST['hind']); // Преобразуем цену в число с плавающей точкой
            $kirjeldus = $_POST['kirjeldus'];

            if (muudaToode($toote_id, $nimetus, $hind, $kirjeldus)) {
                $_SESSION['message'] = 'Product modified successfully!';
            } else {
                $_SESSION['error'] = 'Failed to modify product.';
            }
        }
        // После обработки POST-запроса всегда перенаправляем, чтобы избежать повторной отправки формы
        header("Location: toode.php");
        exit();
    } catch (Exception $e) {
        // Логирование ошибки и сохранение сообщения об ошибке в сессии
        error_log("Error in toode.php POST: " . $e->getMessage());
        $_SESSION['error'] = 'Viga: ' . $e->getMessage();
        header("Location: toode.php");
        exit();
    }
}

// Обработка GET-запросов для удаления товара
if (isset($_GET['kustuta_id'])) {
    $kustuta_id = $_GET['kustuta_id'];
    try {
        if (kustutaToode($kustuta_id)) {
            $_SESSION['message'] = 'Product deleted successfully!';
        } else {
            $_SESSION['error'] = 'Failed to delete product.';
        }
    } catch (Exception $e) {
        error_log("Error in toode.php DELETE: " . $e->getMessage());
        $_SESSION['error'] = 'Viga: ' . $e->getMessage();
    }
    // После удаления всегда перенаправляем
    header("Location: toode.php");
    exit();
}

// Получение данных для отображения таблицы товаров
$otsisona = $_GET['otsi'] ?? ''; // Слово для поиска
$sort = $_GET['sort'] ?? 'Nimetus'; // Столбец для сортировки по умолчанию

$tooted = kysiTooted($sort, $otsisona); // Вызываем функцию для получения списка товаров
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Rose&Thorn</title>
    <link rel="stylesheet" href="style.css"> <script>
        // Передаем данные из PHP в JavaScript для удобства редактирования
        window.toodeData = <?= json_encode($tooted) ?>;

        // Функция для отображения/скрытия формы редактирования и заполнения ее данными
        function showEditForm(type, id) {
            if (type === 'toode') {
                const form = document.getElementById('edit-form-toode');
                form.style.display = 'block'; // Показываем форму

                // Находим нужный товар по ID из переданных данных
                const product = window.toodeData.find(p => p.id == id);
                if (product) {
                    // Заполняем поля формы данными товара
                    document.getElementById('edit_toote_id').value = product.id;
                    document.getElementById('edit_toote_nimetus').value = product.nimetus;
                    document.getElementById('edit_toote_hind').value = product.hind;
                    document.getElementById('edit_toote_kirjeldus').value = product.kirjeldus;
                }
            }
        }

        // Функция для скрытия формы редактирования
        function hideEditForm(formId) {
            document.getElementById(formId).style.display = 'none';
        }
    </script>
</head>
<body>

<nav>
    <div class="nav-container">
        <a href="toode.php" class="nav-brand">FlowerShop</a>
        <ul>
            <li><a href="Tellimus.php">Orders</a></li>
            <li><a href="Toode.php" class="active">Products</a></li>

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

<div class="container">
    <div class="table-container">
        <h2>Products</h2>
        <table>
            <thead>
            <tr>
                <th>Name</th>
                <th>Price</th>
                <th>Description</th>
                <th>Action</th> </tr>
            </thead>
            <tbody>
            <?php foreach($tooted as $toode): ?>
                <tr>
                    <td><?= htmlspecialchars($toode->nimetus) ?></td>
                    <td><?= number_format($toode->hind, 2) ?> €</td>
                    <td><?= htmlspecialchars($toode->kirjeldus) ?></td>
                    <td>
                        <a href="?kustuta_id=<?= $toode->id ?>" onclick="return confirm('Are you sure you want to delete the product? This will also remove it from any existing orders.')" style="font-style: italic;">Delete</a>
                        <button type="button" onclick="showEditForm('toode', <?= $toode->id ?>)">Edit</button>
                    </td>
                </tr>
            <?php endforeach; ?>
            <?php if (empty($tooted)): ?>
                <tr>
                    <td colspan="4">No products available.</td>
                </tr>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<form action="toode.php" method="post"> <div class="form-box" id="lisatoode">
        <h2>Add new product</h2>
        <label for="nimetus">Name:</label>
        <input type="text" name="nimetus" id="nimetus" required>

        <label for="hind">Price (€):</label>
        <input type="number" min="0.01" max="1000" step="0.01" name="hind" id="hind" required>

        <label for="kirjeldus">Description:</label>
        <input type="text" name="kirjeldus" id="kirjeldus">

        <input type="submit" name="lisa_Toode" value="Add new product">
    </div>
</form>

<form action="toode.php" method="post"> <div id="edit-form-toode" class="form-box" style="display:none;">
        <h2>Edit product</h2>
        <input type="hidden" name="toote_id" id="edit_toote_id"> <label for="edit_toote_nimetus">Name:</label>
        <input type="text" name="nimetus" id="edit_toote_nimetus" required>

        <label for="edit_toote_hind">Price (€):</label>
        <input type="number" step="0.01" name="hind" id="edit_toote_hind" required>

        <label for="edit_toote_kirjeldus">Description:</label>
        <input type="text" name="kirjeldus" id="edit_toote_kirjeldus">

        <input type="submit" name="muuda_Toode" value="Save changes">
        <button type="button" id="cancelbutton" onclick="hideEditForm('edit-form-toode')">Cancel</button>
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