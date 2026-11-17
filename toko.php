<?php
include 'koneksi.php';
$id_toko = isset($_GET['id_toko']) ? (int)$_GET['id_toko'] : 0;
$toko = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM toko WHERE id_toko = $id_toko"));
if (!$toko) { header("Location: index.php"); exit; }

$warna = htmlspecialchars($toko['tema_warna']);

if (!isset($_SESSION['cart'][$id_toko])) {
    $_SESSION['cart'][$id_toko] = [];
}

if (isset($_POST['tambah'])) {
    $no_prod = (int)$_POST['no'];
    $varian  = $_POST['varian_rasa'];
    $qty     = (int)$_POST['qty'];
    $p = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM bilamart WHERE no = $no_prod"));

    if ($p && $qty > 0) {
        $key = $no_prod . "_" . $varian;
        if (isset($_SESSION['cart'][$id_toko][$key])) {
            $_SESSION['cart'][$id_toko][$key]['qty'] += $qty;
        } else {
            $_SESSION['cart'][$id_toko][$key] = [
                'nama'   => $p['nama_produk'],
                'varian' => $varian,
                'harga'  => $p['harga'],
                'qty'    => $qty
            ];
        }
    }
    header("Location: toko.php?id_toko=$id_toko");
    exit;
}

if (isset($_GET['hapus'])) {
    unset($_SESSION['cart'][$id_toko][$_GET['hapus']]);
    header("Location: toko.php?id_toko=$id_toko");
    exit;
}

if (isset($_POST['checkout'])) {
    $nama   = mysqli_real_escape_string($conn, $_POST['nama_pembeli']);
    $no_hp  = mysqli_real_escape_string($conn, $_POST['no_hp']);
    $alamat = mysqli_real_escape_string($conn, $_POST['alamat']);
    $list   = [];
    $total  = 0;

    foreach ($_SESSION['cart'][$id_toko] as $c) {
        $sub = $c['harga'] * $c['qty'];
        $total += $sub;
        $list[] = "{$c['nama']} ({$c['varian']}) x{$c['qty']} = Rp " . number_format($sub, 0, ',', '.');
    }

    if ($total > 0) {
        $daftar_produk = mysqli_real_escape_string($conn, implode("\n", $list));
        mysqli_query($conn, "INSERT INTO pesanan (id_toko, nama_pembeli, no_hp, alamat, daftar_produk, total_harga) VALUES ($id_toko, '$nama', '$no_hp', '$alamat', '$daftar_produk', $total)");
        $id_baru = mysqli_insert_id($conn);
        $_SESSION['cart'][$id_toko] = [];
        echo "<script>alert('Pesanan berhasil dibuat! Nomor Pesanan Anda: #$id_baru'); window.location='cek_pesanan.php?id_pesanan=$id_baru';</script>";
        exit;
    }
}

$kat_aktif = isset($_GET['kategori']) ? mysqli_real_escape_string($conn, $_GET['kategori']) : '';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title><?= htmlspecialchars($toko['nama_toko']) ?> - BilaMall</title>
    <style>
        body { font-family: 'Segoe UI', sans-serif; margin: 0; background: #f8f9fa; }
        .banner { background: <?= $warna ?>; color: white; padding: 20px 40px; display: flex; justify-content: space-between; align-items: center; }
        .banner a { color: white; text-decoration: none; font-weight: bold; }
        .wrap { max-width: 1150px; margin: 25px auto; padding: 0 20px; display: grid; grid-template-columns: 2fr 1fr; gap: 25px; }
        .kat-list { display: flex; gap: 10px; flex-wrap: wrap; margin-bottom: 20px; }
        .kat-btn { padding: 8px 16px; border-radius: 20px; border: 2px solid <?= $warna ?>; text-decoration: none; color: #333; font-weight: bold; background: white; font-size: 14px; }
        .kat-btn.active { background: <?= $warna ?>; color: white; }
        .grid-produk { display: grid; grid-template-columns: repeat(auto-fill, minmax(220px, 1fr)); gap: 18px; }
        .item { background: white; border-radius: 12px; padding: 15px; box-shadow: 0 3px 10px rgba(0,0,0,0.06); }
        .item img { width: 100%; height: 140px; object-fit: cover; border-radius: 8px; }
        .cart { background: white; border-radius: 12px; padding: 20px; box-shadow: 0 3px 12px rgba(0,0,0,0.08); height: fit-content; border-top: 5px solid <?= $warna ?>; }
        input, select, textarea { width: 100%; padding: 8px; margin: 5px 0 12px; border: 1px solid #ddd; border-radius: 6px; box-sizing: border-box; }
        button { background: <?= $warna ?>; color: white; border: none; padding: 10px; width: 100%; border-radius: 8px; font-weight: bold; cursor: pointer; }
    </style>
</head>
<body>

<div class="banner">
    <div>
        <h2 style="margin:0;"><?= htmlspecialchars($toko['nama_toko']) ?></h2>
        <small><?= htmlspecialchars($toko['deskripsi']) ?></small>
    </div>
    <a href="index.php">← Kembali ke BilaMall</a>
</div>

<div class="wrap">
    <div>
        <div class="kat-list">
            <a href="toko.php?id_toko=<?= $id_toko ?>" class="kat-btn <?= $kat_aktif == '' ? 'active' : '' ?>">Semua</a>
            <?php
            $qk = mysqli_query($conn, "SELECT DISTINCT kategori FROM bilamart WHERE id_toko = $id_toko");
            while ($k = mysqli_fetch_assoc($qk)) {
                $act = ($kat_aktif == $k['kategori']) ? 'active' : '';
                echo "<a href='toko.php?id_toko=$id_toko&kategori=".urlencode($k['kategori'])."' class='kat-btn $act'>".htmlspecialchars($k['kategori'])."</a>";
            }
            ?>
        </div>

        <div class="grid-produk">
            <?php
            $sql = "SELECT * FROM bilamart WHERE id_toko = $id_toko";
            if ($kat_aktif != '') $sql .= " AND kategori = '$kat_aktif'";
            $qp = mysqli_query($conn, $sql);
            while ($p = mysqli_fetch_assoc($qp)) {
                $img = (!empty($p['gambar']) && file_exists("uploads/" . $p['gambar'])) 
                       ? "uploads/" . $p['gambar'] 
                       : "https://via.placeholder.com/250x150?text=" . urlencode($p['nama_produk']);
                $varian = explode(',', $p['varian_rasa']);
            ?>
            <div class="item">
                <img src="<?= $img ?>" alt="">
                <h4 style="margin:10px 0 4px;"><?= htmlspecialchars($p['nama_produk']) ?></h4>
                <div style="color: <?= $warna ?>; font-weight: bold;">Rp <?= number_format($p['harga'], 0, ',', '.') ?></div>
                <small style="color:#888;">Stok: <?= $p['stok'] ?></small>
                <form method="POST" style="margin-top:8px;">
                    <input type="hidden" name="no" value="<?= $p['no'] ?>">
                    <select name="varian_rasa">
                        <?php foreach ($varian as $v) echo "<option value='".trim($v)."'>".trim($v)."</option>"; ?>
                    </select>
                    <input type="number" name="qty" value="1" min="1" max="<?= $p['stok'] ?>">
                    <button type="submit" name="tambah">+ Keranjang</button>
                </form>
            </div>
            <?php } ?>
        </div>
    </div>

    <div class="cart">
        <h3 style="margin-top:0;">🛒 Keranjang</h3>
        <?php
        $total = 0;
        if (!empty($_SESSION['cart'][$id_toko])) {
            foreach ($_SESSION['cart'][$id_toko] as $key => $item) {
                $sub = $item['harga'] * $item['qty'];
                $total += $sub;
                echo "<div style='border-bottom:1px solid #eee; padding:8px 0; font-size:14px;'>
                        <b>{$item['nama']}</b> ({$item['varian']})<br>
                        {$item['qty']} x Rp ".number_format($item['harga'],0,',','.')."
                        <a href='toko.php?id_toko=$id_toko&hapus=".urlencode($key)."' style='color:red; float:right; text-decoration:none;'>Hapus</a>
                      </div>";
            }
            echo "<h4>Total: Rp " . number_format($total, 0, ',', '.') . "</h4>";
        ?>
        <form method="POST">
            <input type="text" name="nama_pembeli" placeholder="Nama Pembeli" required>
            <input type="text" name="no_hp" placeholder="No. HP / WhatsApp" required>
            <textarea name="alamat" placeholder="Alamat Pengiriman" required></textarea>
            <button type="submit" name="checkout">Pesan Sekarang</button>
        </form>
        <?php } else { echo "<p style='color:#888;'>Keranjang kosong.</p>"; } ?>
    </div>
</div>

</body>
</html>