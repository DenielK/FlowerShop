<?php
require("zoneconf.php");

function kustutaTellimus($id) {
    global $yhendus;

    if (!$yhendus || $yhendus->connect_error) {
        error_log("DB connection error in kustutaTellimus: " . ($yhendus->connect_error ?? 'Connection object is null'));
        return false;
    }

    try {
        $kask = $yhendus->prepare("DELETE FROM tellimused WHERE id=?"); // Корректно, согласно БД структуре

        if ($kask === false) {
            error_log("Order delete (Prepare failed): " . $yhendus->error);
            return false;
        }

        $kask->bind_param("i", $id);

        if ($kask->execute()) {
            if ($kask->affected_rows > 0) {
                error_log("Order ID " . $id . " deleted successfully. Affected rows: " . $kask->affected_rows);
                return true;
            } else {
                error_log("No rows affected for order ID: " . $id . ". Order might not exist or already deleted.");
                return false;
            }
        } else {
            error_log("Order delete (Execute failed for ID " . $id . "): " . $kask->error);
            return false;
        }
    } catch (Exception $e) {
        error_log("Order delete error: " . $e->getMessage());
        return false;
    } finally {
        if ($kask) {
            $kask->close();
        }
    }
}

function kustutaToode($id) {
    global $yhendus;
    try {
        // Удаляем сам товар
        $kask = $yhendus->prepare("DELETE FROM tooted WHERE id=?"); // Изменено на tooted и id
        $kask->bind_param("i", $id);
        return $kask->execute();
    } catch (Exception $e) {
        error_log("Product delete error: " . $e->getMessage());
        return false;
    }
}

function kysiTooted($sorttulp = "toote_nimi", $otsisona = '') { // Изменено на toote_nimi
    global $yhendus;
    $lubatudtulbad = ["toote_nimi", "toote_hind"]; // Изменено на toote_nimi, toote_hind
    if (!in_array($sorttulp, $lubatudtulbad)) {
        $sorttulp = "toote_nimi";
    }

    $otsisona = "%".addslashes($otsisona)."%";
    $kask = $yhendus->prepare("
        SELECT id, toote_nimi, toote_hind, kirjeldus
        FROM tooted
        WHERE toote_nimi LIKE ? OR kirjeldus LIKE ?
        ORDER BY $sorttulp
    "); // Изменено на tooted, toote_nimi, toote_hind
    $kask->bind_param("ss", $otsisona, $otsisona);
    $kask->execute();
    $kask->bind_result($id, $nimetus, $hind, $kirjeldus);

    $hoidla = [];
    while ($kask->fetch()) {
        $toode = new stdClass();
        $toode->id = $id;
        $toode->nimetus = htmlspecialchars($nimetus);
        $toode->hind = $hind;
        $toode->kirjeldus = htmlspecialchars($kirjeldus);
        $hoidla[] = $toode;
    }
    return $hoidla;
}

function lisaToode($nimetus, $hind, $kirjeldus) {
    global $yhendus;
    $kask = $yhendus->prepare("
        INSERT INTO tooted (toote_nimi, toote_hind, kirjeldus)
        VALUES (?, ?, ?)
    "); // Изменено на tooted, toote_nimi, toote_hind
    $kask->bind_param("sds", $nimetus, $hind, $kirjeldus);
    return $kask->execute();
}

function muudaToode($id, $nimetus, $hind, $kirjeldus) {
    global $yhendus;
    $kask = $yhendus->prepare("UPDATE tooted SET toote_nimi=?, toote_hind=?, kirjeldus=? WHERE id=?"); // Изменено на tooted, toote_nimi, toote_hind, id
    $kask->bind_param("sdsi", $nimetus, $hind, $kirjeldus, $id);
    return $kask->execute();
}

// Изменена функция lisaTellimus для соответствия новой структуре tellimused
function lisaTellimus($kliendi_id, $toote_id, $kogus, $staatus, $tellimuse_hind, $kuupaev) {
    global $yhendus;
    $stmt = $yhendus->prepare("INSERT INTO tellimused (kliendi_id, toote_id, kogus, staatus, tellimuse_hind, kuupaev) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("iiisds", $kliendi_id, $toote_id, $kogus, $staatus, $tellimuse_hind, $kuupaev);
    $stmt->execute();
    $tellimus_id = $stmt->insert_id;
    $stmt->close();
    return $tellimus_id;
}

function kysiTellimused($sorttulp = "kuupaev", $otsisona = '') { // Изменено на kuupaev
    global $yhendus;
    $lubatudtulbad = ["kuupaev", "staatus", "kliendi_nimi", "toote_nimi"]; // Добавлены kliendi_nimi и toote_nimi для сортировки/поиска, изменены на kuupaev, staatus
    if (!in_array($sorttulp, $lubatudtulbad)) {
        $sorttulp = "kuupaev";
    }

    $otsisona = "%".addslashes($otsisona)."%";
    $kask = $yhendus->prepare("
        SELECT t.id, t.staatus, t.kuupaev, k.id AS kliendi_id, k.kliendi_nimi, td.id AS toote_id, td.toote_nimi, t.kogus, t.tellimuse_hind
        FROM tellimused t
        LEFT JOIN kliendid k ON t.kliendi_id = k.id
        LEFT JOIN tooted td ON t.toote_id = td.id
        WHERE t.kuupaev LIKE ? OR
              t.staatus LIKE ? OR
              k.kliendi_nimi LIKE ? OR
              td.toote_nimi LIKE ?
        ORDER BY $sorttulp
    "); // Обновлен запрос с новыми именами таблиц и столбцов
    $kask->bind_param("ssss", $otsisona, $otsisona, $otsisona, $otsisona);
    $kask->execute();
    $kask->bind_result($id, $staatus, $kuupaev, $kliendi_id, $kliendi_nimi, $toote_id, $toote_nimi, $kogus, $tellimuse_hind);

    $hoidla = [];
    while ($kask->fetch()) {
        $tellimus = new stdClass();
        $tellimus->id = $id;
        $tellimus->status = htmlspecialchars($staatus);
        $tellimus->kuupaev = htmlspecialchars($kuupaev);
        $tellimus->kliendi_id = $kliendi_id;
        $tellimus->kliendi_nimi = htmlspecialchars($kliendi_nimi);
        $tellimus->toote_id = $toote_id;
        $tellimus->toote_nimi = htmlspecialchars($toote_nimi);
        $tellimus->kogus = $kogus;
        $tellimus->tellimuse_hind = $tellimuse_hind;
        $hoidla[] = $tellimus;
    }
    return $hoidla;
}
?>