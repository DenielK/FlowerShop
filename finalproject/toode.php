<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require("zoneconf.php");
require_once("abifunktsioonid.php");

// Check authentication: if user isn't logged in, redirect to login page.
if (!isset($_SESSION['kasutaja'])) {
    header("Location: login.php"); // Убедитесь, что это правильный путь
    exit();
}

// Global database connection object, used by functions from abifunktsioonid.php
global $yhendus;

// Determine if the current user is an administrator.
// This value is set on the login.php page.
$is_admin = isset($_SESSION['admin']) && $_SESSION['admin'] === true;


// Handle POST requests
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Handle adding a product
        if (isset($_POST['lisa_Toode'])) {
            $nimetus = $_POST['nimetus'];
            $hind = floatval($_POST['hind']);
            $kirjeldus = $_POST['kirjeldus'];

            if (lisaToode($nimetus, $hind, $kirjeldus)) {
                $_SESSION['message'] = 'Product added successfully!';
            } else {
                $_SESSION['error'] = 'Failed to add product.';
            }
        }
        // Handle modifying a product (inline editing)
        elseif (isset($_POST['update_inline_product'])) {
            $toote_id = $_POST['product_id'];
            $nimetus = $_POST['new_name'];
            $hind = floatval($_POST['new_price']);
            $kirjeldus = $_POST['new_description'];

            if (muudaToode($toote_id, $nimetus, $hind, $kirjeldus)) {
                $_SESSION['message'] = 'Product updated successfully!';
            } else {
                $_SESSION['error'] = 'Failed to update product.';
            }
        }
        // After processing a POST request, always redirect to prevent form re-submission.
        header("Location: toode.php");
        exit();
    } catch (Exception $e) {
        // Log the error and save the error message in the session.
        error_log("Error in toode.php POST: " . $e->getMessage());
        $_SESSION['error'] = 'Viga: ' . $e->getMessage();
        header("Location: toode.php");
        exit();
    }
}

// Handle GET requests for deleting a product (still admin-only)
if (isset($_GET['kustuta_id'])) {
    $kustuta_id = $_GET['kustuta_id'];
    try {
        if ($is_admin) {
            if (kustutaToode($kustuta_id)) {
                $_SESSION['message'] = 'Product deleted successfully!';
            } else {
                $_SESSION['error'] = 'Failed to delete product.';
            }
        } else {
            $_SESSION['error'] = 'Access denied: You do not have permission to delete products.';
        }
    } catch (Exception $e) {
        error_log("Error in toode.php DELETE: " . $e->getMessage());
        $_SESSION['error'] = 'Viga: ' . $e->getMessage();
    }
    // After deletion, always redirect.
    header("Location: toode.php");
    exit();
}

// Get data for displaying the product table.
$otsisona = $_GET['otsi'] ?? '';
$sort = $_GET['sort'] ?? 'toote_nimi';

$tooted = kysiTooted($sort, $otsisona);
?>
<!DOCTYPE html>
<html lang="en">
<header>
    <h2>FlowerShop from Deniel Kruusman</h2>
</header>
<header>
    <h2><a href="https://github.com/DenielK/FlowerShop" target="_blank" class="github-link">Creator Github</a></h2>
</header>
<head>
    <meta charset="UTF-8">
    <title>FlowerShop</title>
    <link rel="stylesheet" href="style.css">
    <script>
        window.toodeData = <?= json_encode($tooted) ?>;

        function enableProductInlineEdit(productId) {
            const row = document.getElementById('product-row-' + productId);
            if (!row) return;

            // Скрываем текстовые элементы
            row.querySelector('.name-display').style.display = 'none';
            row.querySelector('.price-display').style.display = 'none';
            row.querySelector('.description-display').style.display = 'none';

            // Показываем поля ввода
            row.querySelector('.name-input').style.display = 'inline-block';
            row.querySelector('.price-input').style.display = 'inline-block';
            row.querySelector('.description-input').style.display = 'inline-block';

            // Скрываем кнопку Edit, показываем Save и Cancel
            row.querySelector('.edit-product-button').style.display = 'none';
            row.querySelector('.save-product-button').style.display = 'inline-block';
            row.querySelector('.cancel-product-button').style.display = 'inline-block';

            // Заполняем поля ввода текущими значениями
            const currentProduct = window.toodeData.find(p => p.id == productId);
            if (currentProduct) {
                row.querySelector('.name-input').value = currentProduct.nimetus;
                row.querySelector('.price-input').value = currentProduct.hind;
                row.querySelector('.description-input').value = currentProduct.kirjeldus;
            }
        }

        function cancelProductInlineEdit(productId) {
            const row = document.getElementById('product-row-' + productId);
            if (!row) return;

            // Показываем текстовые элементы
            row.querySelector('.name-display').style.display = 'inline-block';
            row.querySelector('.price-display').style.display = 'inline-block';
            row.querySelector('.description-display').style.display = 'inline-block';

            // Скрываем поля ввода
            row.querySelector('.name-input').style.display = 'none';
            row.querySelector('.price-input').style.display = 'none';
            row.querySelector('.description-input').style.display = 'none';

            // Показываем кнопку Edit, скрываем Save и Cancel
            row.querySelector('.edit-product-button').style.display = 'inline-block';
            row.querySelector('.save-product-button').style.display = 'none';
            row.querySelector('.cancel-product-button').style.display = 'none';
        }

        function saveProductInlineEdit(productId) {
            const row = document.getElementById('product-row-' + productId);
            if (!row) return;

            const newName = row.querySelector('.name-input').value;
            const newPrice = row.querySelector('.price-input').value;
            const newDescription = row.querySelector('.description-input').value;

            // Создаем скрытую форму и отправляем данные
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = 'toode.php';

            const productIdInput = document.createElement('input');
            productIdInput.type = 'hidden';
            productIdInput.name = 'product_id';
            productIdInput.value = productId;
            form.appendChild(productIdInput);

            const nameInput = document.createElement('input');
            nameInput.type = 'hidden';
            nameInput.name = 'new_name';
            nameInput.value = newName;
            form.appendChild(nameInput);

            const priceInput = document.createElement('input');
            priceInput.type = 'hidden';
            priceInput.name = 'new_price';
            priceInput.value = newPrice;
            form.appendChild(priceInput);

            const descriptionInput = document.createElement('input');
            descriptionInput.type = 'hidden';
            descriptionInput.name = 'new_description';
            descriptionInput.value = newDescription;
            form.appendChild(descriptionInput);

            const updateFlag = document.createElement('input');
            updateFlag.type = 'hidden';
            updateFlag.name = 'update_inline_product'; // Флаг для серверной части
            updateFlag.value = '1';
            form.appendChild(updateFlag);

            document.body.appendChild(form);
            form.submit();
        }

        // Функции для отображения/скрытия формы добавления продукта
        function showAddProductForm() {
            const formContainer = document.getElementById('add-product-form-container');
            const addButton = document.getElementById('add-product-button');

            formContainer.style.display = 'block';
            // Правильное скрытие кнопки при открытии формы
            addButton.style.display = 'none';

            // Прокрутка к форме
            formContainer.scrollIntoView({ behavior: 'smooth', block: 'start' });
        }

        function hideAddProductForm() {
            document.getElementById('add-product-form-container').style.display = 'none';
            const addButton = document.getElementById('add-product-button');
            // При отмене снова делаем кнопку видимой, центрируя ее
            addButton.style.display = 'block'; // Делаем ее блочным элементом
            addButton.style.margin = '20px auto'; // Центрируем по горизонтали
        }
        // ... (существующие JavaScript функции: enableProductInlineEdit, cancelProductInlineEdit, saveProductInlineEdit, showAddProductForm, hideAddProductForm) ...

        // НОВАЯ ФУНКЦИЯ ДЛЯ УДАЛЕНИЯ ПРОДУКТА КНОПКОЙ
        function confirmAndDeleteProduct(productId) {
            if (confirm('Are you sure you want to delete the product?')) {
                // Перенаправляем пользователя на URL для удаления
                window.location.href = '?kustuta_id=' + productId;
            }
        }

    </script>
</head>
<body>

<nav>
    <div class="nav-container">
        <a href="toode.php" class="nav-brand">FlowerShop</a>
        <ul>
            <li><a href="tellimus.php">Orders</a></li>
            <li><a href="toode.php" class="active">Products</a></li>
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
                <th>Action</th>
            </tr>
            </thead>
            <tbody>
            <?php foreach($tooted as $toode): ?>
                <tr id="product-row-<?= htmlspecialchars($toode->id) ?>">
                    <td>
                        <span class="name-display"><?= htmlspecialchars($toode->nimetus) ?></span>
                        <input type="text" class="name-input" style="display:none;" value="<?= htmlspecialchars($toode->nimetus) ?>">
                    </td>
                    <td>
                        <span class="price-display"><?= number_format($toode->hind, 2) ?> €</span>
                        <input type="number" step="0.01" min="0.01" max="1000" class="price-input" style="display:none;" value="<?= htmlspecialchars($toode->hind) ?>">
                    </td>
                    <td>
                        <span class="description-display"><?= htmlspecialchars($toode->kirjeldus) ?></span>
                        <input type="text" class="description-input" style="display:none;" value="<?= htmlspecialchars($toode->kirjeldus) ?>">
                    </td>
                    <td>
                        <button type="button" class="edit-product-button green-button btn-base" onclick="enableProductInlineEdit(<?= $toode->id ?>)">Edit</button>
                        <button type="button" class="save-product-button green-button btn-base" style="display:none;" onclick="saveProductInlineEdit(<?= $toode->id ?>)">Save</button>
                        <button type="button" class="cancel-product-button blue-button btn-base" style="display:none;" onclick="cancelProductInlineEdit(<?= $toode->id ?>)">Cancel</button>
                        <?php if ($is_admin): ?>
                            <button type="button"
                                    class="red-button btn-base"
                                    onclick="confirmAndDeleteProduct(<?= htmlspecialchars($toode->id) ?>)">
                                Delete
                            </button>
                        <?php endif; ?>
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

<div style="text-align: center; margin-top: 20px;">
    <button type="button" id="add-product-button" class="green-button btn-base" onclick="showAddProductForm()">Add New Product</button>
</div>

<div id="add-product-form-container" class="form-box" style="display:none;">
    <h2>Add new product</h2>
    <form action="toode.php" method="post">
        <label for="nimetus">Name:</label>
        <input type="text" name="nimetus" id="nimetus" required>

        <label for="hind">Price (€):</label>
        <input type="number" min="0.01" max="1000" step="0.01" name="hind" id="hind" required>

        <label for="kirjeldus">Description:</label>
        <input type="text" name="kirjeldus" id="kirjeldus">

        <div style="margin-top: 15px;">
            <input type="submit" name="lisa_Toode" value="Add Product" class="btn-base green-button">
            <button type="button" onclick="hideAddProductForm()" class="btn-base blue-button">Cancel</button>
        </div>
    </form>
</div>

<?php // Удалена форма edit-form-toode, так как она больше не используется ?>

</body>
</html>
<footer>
    <?php
    echo "Deniel Kruusman";
    echo " &copy; ";
    echo date("Y"); // y-year
    echo "<br>";
    echo "<strong>Contact info: </strong>";
    echo " denielkruusman@gmail.com";
    ?>
</footer>