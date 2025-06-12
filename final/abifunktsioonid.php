<?php
require("conf.php");

function kustutaKategooria($id) {
    global $yhendus;
    $kask = $yhendus->prepare("DELETE FROM toodekategooria WHERE Kategooria_ID=?");
    $kask->bind_param("i", $id);
    return $kask->execute();
}

function kustutaKlient($id) {
    global $yhendus;
    try {
        // Сначала удаляем связанные записи в kohaletoimetamine
        $kask = $yhendus->prepare("DELETE FROM kohaletoimetamine WHERE Tellimuseinfo_ID IN (SELECT Tellimuseinfo_ID FROM tellimuseinfo WHERE Tellimus_ID IN (SELECT Tellimus_ID FROM tellimus WHERE Klient_ID=?))");
        $kask->bind_param("i", $id);
        $kask->execute();

        // Затем удаляем связанные записи в makse
        $kask = $yhendus->prepare("DELETE FROM makse WHERE Tellimus_ID IN (SELECT Tellimus_ID FROM tellimus WHERE Klient_ID=?)");
        $kask->bind_param("i", $id);
        $kask->execute();

        // Затем удаляем связанные записи в tellimuseinfo
        $kask = $yhendus->prepare("DELETE FROM tellimuseinfo WHERE Tellimus_ID IN (SELECT Tellimus_ID FROM tellimus WHERE Klient_ID=?)");
        $kask->bind_param("i", $id);
        $kask->execute();

        // Затем удаляем связанные заказы
        $kask = $yhendus->prepare("DELETE FROM tellimus WHERE Klient_ID=?");
        $kask->bind_param("i", $id);
        $kask->execute();

        // Затем удаляем клиента
        $kask = $yhendus->prepare("DELETE FROM klient WHERE Klient_ID=?");
        $kask->bind_param("i", $id);
        return $kask->execute();
    } catch (Exception $e) {
        error_log("Client delete error: " . $e->getMessage());
        return false;
    }
}

function kustutaTellimus($id) {
    global $yhendus;
    try {
        // Удаляем связанные записи из kohaletoimetamine
        $kask = $yhendus->prepare("DELETE FROM kohaletoimetamine WHERE Tellimuseinfo_ID IN (SELECT Tellimuseinfo_ID FROM tellimuseinfo WHERE Tellimus_ID = ?)");
        $kask->bind_param("i", $id);
        $kask->execute();

        // Удаляем связанные записи из makse
        $kask = $yhendus->prepare("DELETE FROM makse WHERE Tellimus_ID=?");
        $kask->bind_param("i", $id);
        $kask->execute();

        // Удаляем связанные записи из tellimuseinfo
        $kask = $yhendus->prepare("DELETE FROM tellimuseinfo WHERE Tellimus_ID=?");
        $kask->bind_param("i", $id);
        $kask->execute();

        // Затем удаляем сам заказ
        $kask = $yhendus->prepare("DELETE FROM tellimus WHERE Tellimus_ID=?");
        $kask->bind_param("i", $id);
        return $kask->execute();
    } catch (Exception $e) {
        error_log("Order delete error: " . $e->getMessage());
        return false;
    }
}

function kustutaToode($id) {
    global $yhendus;
    try {
        // Удаляем связанные записи из toodeladu
        $kask = $yhendus->prepare("DELETE FROM toodeladu WHERE Toode_ID=?");
        $kask->bind_param("i", $id);
        $kask->execute();

        // Удаляем связанные записи из toode_kategooria
        $kask = $yhendus->prepare("DELETE FROM toode_kategooria WHERE Toode_ID=?");
        $kask->bind_param("i", $id);
        $kask->execute();

        // Удаляем связанные записи из tellimuseinfo
        $kask = $yhendus->prepare("DELETE FROM tellimuseinfo WHERE Toode_ID=?");
        $kask->bind_param("i", $id);
        $kask->execute();

        // Затем удаляем сам товар
        $kask = $yhendus->prepare("DELETE FROM toode WHERE Toode_ID=?");
        $kask->bind_param("i", $id);
        return $kask->execute();
    } catch (Exception $e) {
        error_log("Product delete error: " . $e->getMessage());
        return false;
    }
}

function kysiTooted($sorttulp = "Nimetus", $otsisona = '') {
    global $yhendus;
    $lubatudtulbad = ["Nimetus", "Hind"];
    if (!in_array($sorttulp, $lubatudtulbad)) {
        $sorttulp = "Nimetus";
    }

    $otsisona = "%".addslashes($otsisona)."%";
    $kask = $yhendus->prepare("
        SELECT Toode_ID, Nimetus, Hind, Kirjeldus
        FROM toode
        WHERE Nimetus LIKE ? OR Kirjeldus LIKE ?
        ORDER BY $sorttulp
    ");
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
        INSERT INTO toode (Nimetus, Hind, Kirjeldus)
        VALUES (?, ?, ?)
    ");
    $kask->bind_param("sds", $nimetus, $hind, $kirjeldus);
    return $kask->execute();
}

function muudaToode($id, $nimetus, $hind, $kirjeldus) {
    global $yhendus;
    $kask = $yhendus->prepare("UPDATE toode SET Nimetus=?, Hind=?, Kirjeldus=? WHERE Toode_ID=?");
    $kask->bind_param("sdsi", $nimetus, $hind, $kirjeldus, $id);
    return $kask->execute();
}

function muudaKategooria($id, $nimi, $kirjeldus) {
    global $yhendus;
    $kask = $yhendus->prepare("UPDATE toodekategooria SET Nimi=?, Kirjeldus=? WHERE Kategooria_ID=?");
    $kask->bind_param("ssi", $nimi, $kirjeldus, $id);
    return $kask->execute();
}

function muudaKlient($id, $nimi, $email, $telefon, $extrainfo) {
    global $yhendus;
    $kask = $yhendus->prepare("UPDATE klient SET Nimi=?, Email=?, telefon=?, extrainfo=? WHERE Klient_ID=?");
    $kask->bind_param("ssssi", $nimi, $email, $telefon, $extrainfo, $id);
    return $kask->execute();
}

function kysiKategooriad() {
    global $yhendus;
    $kask = $yhendus->prepare("SELECT Kategooria_ID, Nimi, Kirjeldus FROM toodekategooria ORDER BY Nimi");
    $kask->execute();
    $kask->bind_result($id, $nimi, $kirjeldus);

    $hoidla = [];
    while ($kask->fetch()) {
        $kategooria = new stdClass();
        $kategooria->id = $id;
        $kategooria->nimi = htmlspecialchars($nimi);
        $kategooria->kirjeldus = htmlspecialchars($kirjeldus);
        $hoidla[] = $kategooria;
    }
    return $hoidla;
}

function lisaKategooria($nimi, $kirjeldus) {
    global $yhendus;
    $kask = $yhendus->prepare("INSERT INTO toodekategooria (Nimi, Kirjeldus) VALUES (?, ?)");
    $kask->bind_param("ss", $nimi, $kirjeldus);
    return $kask->execute();
}

function kysiKliendid() {
    global $yhendus;
    $kask = $yhendus->prepare("SELECT Klient_ID, Nimi, Email, telefon, extrainfo FROM klient ORDER BY Nimi");
    $kask->execute();
    $kask->bind_result($id, $nimi, $email, $telefon, $extrainfo);

    $hoidla = [];
    while ($kask->fetch()) {
        $klient = new stdClass();
        $klient->id = $id;
        $klient->nimi = htmlspecialchars($nimi);
        $klient->email = htmlspecialchars($email);
        $klient->telefon = htmlspecialchars($telefon);
        $klient->extrainfo = htmlspecialchars($extrainfo);
        $hoidla[] = $klient;
    }
    return $hoidla;
}

function lisaKlient($nimi, $email = null, $telefon = null, $extrainfo = null) {
    global $yhendus;
    $kask = $yhendus->prepare("INSERT INTO klient (Nimi, Email, telefon, extrainfo) VALUES (?, ?, ?, ?)");
    $kask->bind_param("ssss", $nimi, $email, $telefon, $extrainfo);
    return $kask->execute();
}

function lisaTellimus($klient_id, $status, $kuupaev, $tootaja_id = null) {
    global $yhendus;
    $stmt = $yhendus->prepare("INSERT INTO tellimus (Klient_ID, Status, Kuupaev, Tootaja_ID) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("issi", $klient_id, $status, $kuupaev, $tootaja_id);
    $stmt->execute();
    $tellimus_id = $stmt->insert_id;
    $stmt->close();
    return $tellimus_id;
}

function kysiTellimused($sorttulp = "Kuupaev", $otsisona = '') {
    global $yhendus;
    $lubatudtulbad = ["Kuupaev", "Status", "Nimi"];
    if (!in_array($sorttulp, $lubatudtulbad)) {
        $sorttulp = "Kuupaev";
    }

    $otsisona = "%".addslashes($otsisona)."%";
    $kask = $yhendus->prepare("
        SELECT t.Tellimus_ID, t.Status, t.Kuupaev, k.Klient_ID, k.Nimi
        FROM tellimus t
        LEFT JOIN klient k ON t.Klient_ID = k.Klient_ID
        WHERE t.Kuupaev LIKE ? OR
              t.Status LIKE ? OR
              k.Nimi LIKE ?
        ORDER BY $sorttulp
    ");
    $kask->bind_param("sss", $otsisona, $otsisona, $otsisona);
    $kask->execute();
    $kask->bind_result($id, $status, $kuupaev, $klient_id, $klient_nimi);

    $hoidla = [];
    while ($kask->fetch()) {
        $tellimus = new stdClass();
        $tellimus->id = $id;
        $tellimus->status = htmlspecialchars($status);
        $tellimus->kuupaev = htmlspecialchars($kuupaev);
        $tellimus->klient_id = $klient_id;
        $tellimus->klient_nimi = htmlspecialchars($klient_nimi);
        $hoidla[] = $tellimus;
    }
    return $hoidla;
}
?>