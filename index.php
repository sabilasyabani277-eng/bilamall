<?php include 'koneksi.php'; ?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>BilaMall</title>
    <style>
        body { font-family: 'Segoe UI', sans-serif; margin: 0; background: #f4f6f9; color: #2d3436; }
        header { background: linear-gradient(135deg, #a043aa, #c24bb6); color: white; padding: 20px 40px; display: flex; justify-content: space-between; align-items: center; }
        header h1 { margin: 0; font-size: 26px; }
        .nav a { color: white; text-decoration: none; background: rgba(255,255,255,0.2); padding: 8px 16px; border-radius: 20px; margin-left: 10px; font-weight: bold; }
        .container { max-width: 1100px; margin: 30px auto; padding: 0 20px; }
        .grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(300px, 1fr)); gap: 25px; }
        .card { background: white; border-radius: 15px; overflow: hidden; box-shadow: 0 4px 15px rgba(0,0,0,0.08); transition: 0.3s; }
        .card:hover { transform: translateY(-5px); }
        .card img { width: 100%; height: 170px; object-fit: cover; }
        .card-body { padding: 20px; }
        .btn { display: inline-block; padding: 10px 20px; color: white; text-decoration: none; border-radius: 8px; font-weight: bold; margin-top: 10px; }
    </style>
</head>
<body>

<header>
    <h1>🛍️ BilaMall</h1>
    <div class="nav">
        <a href="cek_pesanan.php">📦 Pesanan & Chat</a>
        <a href="owner.php">🏪 Kelola Toko (Owner)</a>
    </div>
</header>

<div class="container">
    <h2>Daftar Toko di BilaMall</h2>
    <div class="grid">
        <?php
        $q = mysqli_query($conn, "SELECT * FROM toko");
        while ($t = mysqli_fetch_assoc($q)) {
            $warna = htmlspecialchars($t['tema_warna']);
            $foto = (!empty($t['foto_toko']) && file_exists("uploads/" . $t['foto_toko'])) 
                    ? "uploads/" . $t['foto_toko'] 
                    : "https://via.placeholder.com/400x200?text=" . urlencode($t['nama_toko']);
        ?>
        <div class="card" style="border-top: 6px solid <?= $warna ?>;">
            <img src="<?= $foto ?>" alt="<?= htmlspecialchars($t['nama_toko']) ?>">
            <div class="card-body">
                <h3 style="margin:0 0 8px; color: <?= $warna ?>;"><?= htmlspecialchars($t['nama_toko']) ?></h3>
                <p style="font-size: 14px; color: #636e72;"><?= htmlspecialchars($t['deskripsi']) ?></p>
                <a href="toko.php?id_toko=<?= $t['id_toko'] ?>" class="btn" style="background: <?= $warna ?>;">Kunjungi Toko →</a>
            </div>
        </div>
        <?php } ?>
    </div>
</div>

</body>
</html>