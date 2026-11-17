<?php
include 'koneksi.php';
$id_pesanan = isset($_GET['id_pesanan']) ? (int)$_GET['id_pesanan'] : 0;

if (isset($_POST['kirim']) && $id_pesanan > 0) {
    $pesan = mysqli_real_escape_string($conn, $_POST['pesan']);
    if (!empty($pesan)) {
        mysqli_query($conn, "INSERT INTO chat_pesanan (id_pesanan, pengirim, pesan) VALUES ($id_pesanan, 'customer', '$pesan')");
    }
    header("Location: cek_pesanan.php?id_pesanan=$id_pesanan");
    exit;
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Lacak Pesanan - BilaMall</title>
    <style>
        body { font-family: 'Segoe UI', sans-serif; background: #f4f6f9; padding: 30px; }
        .box { max-width: 650px; margin: auto; background: white; padding: 25px; border-radius: 12px; box-shadow: 0 4px 12px rgba(0,0,0,0.08); }
        .chat { background: #f1f2f6; padding: 15px; border-radius: 10px; height: 240px; overflow-y: auto; margin: 15px 0; }
        .msg { padding: 8px 12px; border-radius: 8px; margin-bottom: 8px; max-width: 75%; font-size: 14px; }
        .customer { background: #83308e; color: white; margin-left: auto; }
        .owner { background: white; border: 1px solid #ddd; }
        input { padding: 10px; width: 70%; border: 1px solid #ccc; border-radius: 6px; }
        button { padding: 10px 18px; background: #cd49c2; color: white; border: none; border-radius: 6px; cursor: pointer; font-weight: bold; }
    </style>
</head>
<body>
<div class="box">
    <a href="index.php" style="text-decoration:none; color:#e84393; font-weight:bold;">← Beranda BilaMall</a>
    <h2>📦 Cek Status Pesanan & Chat</h2>
    <form method="GET">
        <input type="number" name="id_pesanan" placeholder="Masukkan ID Pesanan..." value="<?= $id_pesanan ?: '' ?>" required>
        <button type="submit">Cek</button>
    </form>

    <?php
    if ($id_pesanan > 0) {
        $p = mysqli_fetch_assoc(mysqli_query($conn, "SELECT pesanan.*, toko.nama_toko FROM pesanan JOIN toko ON pesanan.id_toko = toko.id_toko WHERE id_pesanan = $id_pesanan"));
        if ($p) {
    ?>
        <hr>
        <p><b>Toko:</b> <?= htmlspecialchars($p['nama_toko']) ?> | <b>Status:</b> <span style="color:#e84393; font-weight:bold;"><?= htmlspecialchars($p['status']) ?></span></p>
        <p><b>Pembeli:</b> <?= htmlspecialchars($p['nama_pembeli']) ?> (<?= htmlspecialchars($p['no_hp']) ?>)</p>
        <pre style="background:#f8f9fa; padding:10px; border-radius:6px;"><?= htmlspecialchars($p['daftar_produk']) ?></pre>
        <p><b>Total Harga:</b> Rp <?= number_format($p['total_harga'], 0, ',', '.') ?></p>

        <h4>💬 Chat dengan Owner</h4>
        <div class="chat">
            <?php
            $chats = mysqli_query($conn, "SELECT * FROM chat_pesanan WHERE id_pesanan = $id_pesanan ORDER BY waktu_pesan ASC");
            while ($c = mysqli_fetch_assoc($chats)) {
                $cls = $c['pengirim'];
                $lbl = ($cls == 'customer') ? 'Anda' : 'Owner';
                echo "<div class='msg $cls'><small><b>$lbl</b> ({$c['waktu_pesan']})</small><br>".htmlspecialchars($c['pesan'])."</div>";
            }
            ?>
        </div>
        <form method="POST">
            <input type="text" name="pesan" placeholder="Ketik pesan..." required>
            <button type="submit" name="kirim">Kirim</button>
        </form>
    <?php } else { echo "<p style='color:red;'>Pesanan tidak ditemukan.</p>"; } } ?>
</div>
</body>
</html>