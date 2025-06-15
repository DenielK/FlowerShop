<nav>
    <ul>
        <li><a href="final.php">Avaleht</a></li>
        <li><a href="toode.php">Tooded</a></li>
        <li><a href="tellimus.php">Tellimused</a></li>
        <?php if (isset($_SESSION['kasutaja'])): ?>
            <li><a href="adminPanel.php">Broneeringute haldus</a></li>
            <li>
                <form action="logout.php" method="post" style="display:inline;">
                    <button type="submit" name="logout">Logi välja (<?=htmlspecialchars($_SESSION['kasutaja'])?>)</button>
                </form>
            </li>
        <?php else: ?>
            <li><a href="login.php">Logi sisse</a></li>
        <?php endif; ?>
    </ul>
</nav>