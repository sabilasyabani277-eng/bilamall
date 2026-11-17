<?php
include 'koneksi.php';
$id_toko = isset($_GET['id_toko']) ? (int)$_GET['id_toko'] : 1;

if (isset($_POST['tambah_toko'])) {
    $nama  = mysqli_real_escape_string($conn, $_POST['nama_toko']);
    $desk  = mysqli_real_escape_string($conn, $_POST['deskripsi']);
    $warna = mysqli_real_escape_string($conn, $_POST['tema_warna']);
    $foto  = "default_toko.jpg";
    if (!empty($_FILES['foto_toko']['name'])) {
        $foto = time() . "_" . $_FILES['foto_toko']['name'];
        move_uploaded_file($_FILES['foto_toko']['tmp_name'], "uploads/" . $foto);
    }
    mysqli_query($conn, "INSERT INTO toko (nama_toko, deskripsi, foto_toko, tema_warna) VALUES ('$nama', '$desk', '$foto', '$warna')");
    $new_id = mysqli_insert_id($conn);
    header("Location: owner.php?id_toko=$new_id");
    exit;
}

if (isset($_POST['tambah_produk'])) {
    $nama   = mysqli_real_escape_string($conn, $_POST['nama_produk']);
    $kat    = mysqli_real_escape_string($conn, $_POST['kategori']);
    $varian = mysqli_real_escape_string($conn, $_POST['varian_rasa']);
    $harga  = (int)$_POST['harga'];
    $stok   = (int)$_POST['stok'];
    $gambar = "default.jpg";

    if (!empty($_FILES['gambar']['name'])) {
        $gambar = time() . "_" . $_FILES['gambar']['name'];
        move_uploaded_file($_FILES['gambar']['tmp_name'], "uploads/" . $gambar);
    }

    mysqli_query($conn, "INSERT INTO bilamart (id_toko, nama_produk, kategori, varian_rasa, harga, stok, gambar) VALUES ($id_toko, '$nama', '$kat', '$varian', $harga, $stok, '$gambar')");
    header("Location: owner.php?id_toko=$id_toko");
    exit;
}

if (isset($_GET['hapus_produk'])) {
    $no = (int)$_GET['hapus_produk'];
    mysqli_query($conn, "DELETE FROM bilamart WHERE no=$no AND id_toko=$id_toko");
    header("Location: owner.php?id_toko=$id_toko");
    exit;
}

if (isset($_POST['update_status'])) {
    $idp  = (int)$_POST['id_pesanan'];
    $stat = mysqli_real_escape_string($conn, $_POST['status']);
    mysqli_query($conn, "UPDATE pesanan SET status='$stat' WHERE id_pesanan=$idp");
    header("Location: owner.php?id_toko=$id_toko");
    exit;
}

if (isset($_POST['balas_chat'])) {
    $idp   = (int)$_POST['id_pesanan'];
    $pesan = mysqli_real_escape_string($conn, $_POST['pesan']);
    if (!empty($pesan)) {
        mysqli_query($conn, "INSERT INTO chat_pesanan (id_pesanan, pengirim, pesan) VALUES ($idp, 'owner', '$pesan')");
    }
    header("Location: owner.php?id_toko=$id_toko");
    exit;
}

$toko_aktif = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM toko WHERE id_toko=$id_toko"));
$warna = $toko_aktif ? htmlspecialchars($toko_aktif['tema_warna']) : '#e081f0';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Owner Panel - BilaMall</title>
    <style>
        body { font-family: 'Segoe UI', sans-serif; background: #f1f2f6; margin: 0; }
        .topbar { background: <?= $warna ?>; color: white; padding: 15px 30px; display: flex; justify-content: space-between; align-items: center; }
        .container { max-width: 1150px; margin: 20px auto; padding: 0 20px; display: grid; grid-template-columns: 1fr 1fr; gap: 20px; }
        .card { background: white; padding: 20px; border-radius: 12px; box-shadow: 0 3px 10px rgba(0,0,0,0.06); margin-bottom: 20px; }
        input, select, textarea { width: 100%; padding: 8px; margin: 5px 0 10px; border: 1px solid #ccc; border-radius: 6px; box-sizing: border-box; }
        button { background: <?= $warna ?>; color: white; border: none; padding: 9px 15px; border-radius: 6px; cursor: pointer; font-weight: bold; }
        table { width: 100%; border-collapse: collapse; font-size: 13px; }
        th, td { border-bottom: 1px solid #eee; padding: 8px; text-align: left; }
    </style>
</head>
<body>

<div class="topbar">
    <div style="display:flex; align-items:center; gap:15px;">
        <h2 style="margin:0;">🏪 Owner BilaMall</h2>
        <form method="GET" style="margin:0;">
            <select name="id_toko" onchange="this.form.submit()" style="margin:0; padding:6px;">
                <?php
                $all_t = mysqli_query($conn, "SELECT * FROM toko");
                while ($rt = mysqli_fetch_assoc($all_t)) {
                    $sel = ($rt['id_toko'] == $id_toko) ? 'selected' : '';
                    echo "<option value='{$rt['id_toko']}' $sel>{$rt['nama_toko']}</option>";
                }
                ?>
            </select>
        </form>
    </div>
    <a href="index.php" style="color:white; text-decoration:none; font-weight:bold;">← Lihat Halaman Customer</a>
</div>

<div class="container">
    <div>
        <div class="card">
            <h3>➕ Tambah Produk ke <?= htmlspecialchars($toko_aktif['nama_toko'] ?? '') ?></h3>
            <form method="POST" enctype="multipart/form-data">
                <input type="text" name="nama_produk" placeholder="Nama Produk" required>
                <input type="text" name="kategori" placeholder="Kategori (cth: Minuman, Snack)" required>
                <input type="text" name="varian_rasa" placeholder="Varian Rasa (pisahkan koma, cth: Cokelat, Keju)" required>
                <input type="number" name="harga" placeholder="Harga (Rp)" required>
                <input type="number" name="stok" placeholder="Stok" value="20" required>
                <label style="font-size:12px;">Gambar Produk:</label>
                <input type="file" name="gambar">
                <button type="submit" name="tambah_produk">Simpan Produk</button>
            </form>
        </div>

        <div class="card">
            <h3>📋 Daftar Produk (bilamart)</h3>
            <table>
                <tr><th>No</th><th>Produk</th><th>Kategori</th><th>Varian</th><th>Harga</th><th>Aksi</th></tr>
                <?php
                $qp = mysqli_query($conn, "SELECT * FROM bilamart WHERE id_toko=$id_toko");
                while ($pr = mysqli_fetch_assoc($qp)) {
                    echo "<tr>
                            <td>{$pr['no']}</td>
                            <td>".htmlspecialchars($pr['nama_produk'])."</td>
                            <td>".htmlspecialchars($pr['kategori'])."</td>
                            <td>".htmlspecialchars($pr['varian_rasa'])."</td>
                            <td>Rp ".number_format($pr['harga'],0,',','.')."</td>
                            <td><a href='?id_toko=$id_toko&hapus_produk={$pr['no']}' style='color:red;'>Hapus</a></td>
                          </tr>";
                }
                ?>
            </table>
        </div>

        <div class="card">
            <h3>🏬 Buka Cabang Toko Baru</h3>
            <form method="POST" enctype="multipart/form-data">
                <input type="text" name="nama_toko" placeholder="Nama Toko Baru" required>
                <textarea name="deskripsi" placeholder="Deskripsi Singkat Toko" required></textarea>
                <label style="font-size:12px;">Tema Warna Toko:</label>
                <input type="color" name="tema_warna" value="#971fce">
                <label style="font-size:12px;">Foto Banner Toko:</label>
                <input type="file" name="foto_toko">
                <button type="submit" name="tambah_toko">Buat Toko</button>
            </form>
        </div>
    </div>

    <div>
        <div class="card">
            <h3>📦 Pesanan Masuk & Chat Customer</h3>
            <?php
            $qpes = mysqli_query($conn, "SELECT * FROM pesanan WHERE id_toko=$id_toko ORDER BY id_pesanan DESC");
            while ($ps = mysqli_fetch_assoc($qpes)) {
                $idp = $ps['id_pesanan'];
            ?>
            <div style="border:1px solid #ddd; border-radius:10px; padding:15px; margin-bottom:15px;">
                <b>Order #<?= $idp ?> - <?= htmlspecialchars($ps['nama_pembeli']) ?></b> (<?= htmlspecialchars($ps['no_hp']) ?>)<br>
                <small>Alamat: <?= htmlspecialchars($ps['alamat']) ?> | <?= $ps['tanggal_pesan'] ?></small>
                <pre style="background:#f8f9fa; padding:8px; border-radius:5px;"><?= htmlspecialchars($ps['daftar_produk']) ?></pre>
                <b>Total: Rp <?= number_format($ps['total_harga'], 0, ',', '.') ?></b>

                <form method="POST" style="display:flex; gap:8px; margin-top:10px;">
                    <input type="hidden" name="id_pesanan" value="<?= $idp ?>">
                    <select name="status" style="margin:0;">
                        <?php
                        foreach (['Baru', 'Diproses', 'Selesai', 'Dibatalkan'] as $st) {
                            $sel = ($ps['status'] == $st) ? 'selected' : '';
                            echo "<option value='$st' $sel>$st</option>";
                        }
                        ?>
                    </select>
                    <button type="submit" name="update_status" style="width:auto;">Ubah Status</button>
                </form>

                <div style="background:#f1f2f6; padding:10px; border-radius:8px; margin-top:10px; max-height:140px; overflow-y:auto; font-size:13px;">
                    <?php
                    $qch = mysqli_query($conn, "SELECT * FROM chat_pesanan WHERE id_pesanan=$idp ORDER BY waktu_pesan ASC");
                    while ($ch = mysqli_fetch_assoc($qch)) {
                        $who = ($ch['pengirim'] == 'owner') ? '<b>Owner:</b>' : '<b>Customer:</b>';
                        echo "<div>$who ".htmlspecialchars($ch['pesan'])."</div>";
                    }
                    ?>
                </div>
                <form method="POST" style="display:flex; gap:5px; margin-top:6px;">
                    <input type="hidden" name="id_pesanan" value="<?= $idp ?>">
                    <input type="text" name="pesan" placeholder="Balas chat customer..." style="margin:0;" required>
                    <button type="submit" name="balas_chat" style="width:auto;">Kirim</button>
                </form>
            </div>
            <?php } ?>
        </div>
    </div>
</div>

</body>
</html>