<?php
// ============================================================
//  BLISSFULBERRY – Detail Produk (detail.php)
// ============================================================
require_once 'config/db.php';

$id = intval($_GET['id'] ?? 0);
if (!$id) { header('Location: katalog.php'); exit; }

$stmt = $conn->prepare("SELECT * FROM produk WHERE id = ?");
$stmt->bind_param('i', $id);
$stmt->execute();
$p = $stmt->get_result()->fetch_assoc();
if (!$p) { header('Location: katalog.php'); exit; }

$page_title = $p['nama_produk'];
$foto = !empty($p['foto']) ? $p['foto'] : 'assets/images/menu-full-blissfulberry.jpg';

// Produk terkait (kategori sama)
$stmt2 = $conn->prepare("SELECT * FROM produk WHERE kategori = ? AND id != ? LIMIT 4");
$stmt2->bind_param('si', $p['kategori'], $p['id']);
$stmt2->execute();
$related = $stmt2->get_result();

// WA message
$wa_msg = urlencode("Halo Blissfulberry! 👋\nSaya ingin memesan:\n🛍️ Produk: {$p['nama_produk']}\n💰 Harga: " . rupiah($p['harga']) . "\n\nMohon konfirmasi ketersediaan stok ya. Terima kasih! 😊");
$wa_url = "https://wa.me/{$p['wa_penjual']}?text={$wa_msg}";

require_once 'includes/header.php';
?>

<!-- Page Header -->
<section class="bb-page-header">
    <div class="container">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-2" style="font-size:0.82rem; opacity:0.8;">
                <li class="breadcrumb-item"><a href="index.php" class="text-white">Beranda</a></li>
                <li class="breadcrumb-item"><a href="katalog.php" class="text-white">Katalog</a></li>
                <li class="breadcrumb-item active text-white"><?= htmlspecialchars($p['nama_produk']) ?></li>
            </ol>
        </nav>
        <h1><?= htmlspecialchars($p['nama_produk']) ?></h1>
        <p><?= htmlspecialchars($p['kategori']) ?></p>
    </div>
</section>

<section class="py-5">
    <div class="container">
        <div class="row g-5 align-items-start">
            <!-- Foto Produk -->
            <div class="col-lg-5 text-center">
                <img src="<?= htmlspecialchars($foto) ?>"
                     onerror="this.src='assets/images/menu-full-blissfulberry.jpg'"
                     alt="<?= htmlspecialchars($p['nama_produk']) ?>"
                     class="bb-detail-img img-fluid mb-3">
                <div class="d-flex flex-wrap gap-2 justify-content-center">
                    <span class="bb-filter-chip active" style="pointer-events:none;">
                        <i class="bi bi-tag me-1"></i><?= htmlspecialchars($p['kategori']) ?>
                    </span>
                    <span class="bb-filter-chip" style="pointer-events:none; color:var(--text-muted);">
                        <i class="bi bi-upc me-1"></i><?= htmlspecialchars($p['kode_produk']) ?>
                    </span>
                </div>
            </div>

            <!-- Detail Info -->
            <div class="col-lg-7">
                <h2 class="fw-800 mb-1" style="color:var(--dark-berry);font-size:1.8rem;">
                    <?= htmlspecialchars($p['nama_produk']) ?>
                </h2>
                <div class="bb-price-tag mb-3"><?= rupiah($p['harga']) ?></div>

                <p class="text-muted lh-lg mb-4"><?= nl2br(htmlspecialchars($p['deskripsi'])) ?></p>

                <!-- Info Table -->
                <div class="bb-table-wrap mb-4">
                    <table class="table bb-table mb-0">
                        <tbody>
                            <tr>
                                <td class="fw-semibold" style="color:var(--dark-berry);width:40%">
                                    <i class="bi bi-upc me-2 text-pink"></i>Kode Produk
                                </td>
                                <td><?= htmlspecialchars($p['kode_produk']) ?></td>
                            </tr>
                            <tr>
                                <td class="fw-semibold" style="color:var(--dark-berry)">
                                    <i class="bi bi-tag me-2 text-pink"></i>Kategori
                                </td>
                                <td><?= htmlspecialchars($p['kategori']) ?></td>
                            </tr>
                            <tr>
                                <td class="fw-semibold" style="color:var(--dark-berry)">
                                    <i class="bi bi-currency-dollar me-2 text-pink"></i>Harga
                                </td>
                                <td><strong class="text-pink"><?= rupiah($p['harga']) ?></strong></td>
                            </tr>
                            <tr>
                                <td class="fw-semibold" style="color:var(--dark-berry)">
                                    <i class="bi bi-whatsapp me-2 text-success"></i>WA Penjual
                                </td>
                                <td>+<?= htmlspecialchars($p['wa_penjual']) ?></td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Tombol Aksi -->
                <div class="d-flex flex-wrap gap-3 mb-4">
                    <a href="pesan-produk.php?id=<?= $p['id'] ?>" class="btn bb-btn-primary bb-pulse">
                        <i class="bi bi-bag-plus me-2"></i>Pesan Sekarang
                    </a>
                    <a href="<?= $wa_url ?>" target="_blank" class="btn bb-btn-wa">
                        <i class="bi bi-whatsapp me-2"></i>Pesan via WhatsApp
                    </a>
                    <a href="katalog.php" class="btn bb-btn-outline">
                        <i class="bi bi-arrow-left me-1"></i>Kembali
                    </a>
                </div>

                <!-- WA Info Box -->
                <div class="bb-order-summary">
                    <h5><i class="bi bi-whatsapp me-2 text-success"></i>Pesan via WhatsApp</h5>
                    <p class="small text-muted mb-3">
                        Klik tombol di atas untuk membuka WhatsApp dengan pesan otomatis yang sudah terisi informasi produk ini!
                    </p>
                    <div class="p-3 rounded-3" style="background:rgba(255,255,255,0.7);font-size:0.82rem;color:var(--text-muted);font-style:italic;">
                        "Halo Blissfulberry! 👋<br>
                        Saya ingin memesan:<br>
                        🛍️ Produk: <strong><?= htmlspecialchars($p['nama_produk']) ?></strong><br>
                        💰 Harga: <strong><?= rupiah($p['harga']) ?></strong><br>
                        Mohon konfirmasi ketersediaan stok ya. Terima kasih! 😊"
                    </div>
                </div>
            </div>
        </div>

        <!-- ===== PRODUK TERKAIT ===== -->
        <?php if ($related->num_rows > 0): ?>
        <div class="mt-6 pt-5 border-top" style="border-color:var(--pink-pale)!important;">
            <h3 class="bb-section-title mb-4">Produk Terkait</h3>
            <div class="row g-4">
                <?php while ($r = $related->fetch_assoc()):
                    $r_foto = !empty($r['foto']) ? $r['foto'] : 'assets/images/menu-full-blissfulberry.jpg';
                ?>
                <div class="col-sm-6 col-lg-3">
                    <div class="bb-card">
                        <div class="bb-card-img-wrap">
                            <img src="<?= htmlspecialchars($r_foto) ?>"
                                 onerror="this.src='assets/images/menu-full-blissfulberry.jpg'"
                                 alt="<?= htmlspecialchars($r['nama_produk']) ?>">
                            <div class="bb-card-badge"><?= htmlspecialchars($r['kategori']) ?></div>
                        </div>
                        <div class="bb-card-body">
                            <div class="bb-card-title"><?= htmlspecialchars($r['nama_produk']) ?></div>
                            <div class="bb-card-price"><?= rupiah($r['harga']) ?></div>
                            <div class="bb-card-actions">
                                <a href="detail.php?id=<?= $r['id'] ?>" class="btn bb-btn-outline btn-sm flex-fill">Detail</a>
                                <a href="pesan-produk.php?id=<?= $r['id'] ?>" class="btn bb-btn-primary btn-sm flex-fill">Pesan</a>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endwhile; ?>
            </div>
        </div>
        <?php endif; ?>
    </div>
</section>

<!-- WA Float -->
<a href="<?= $wa_url ?>" target="_blank" class="bb-wa-float bb-pulse">
    <i class="bi bi-whatsapp"></i>
    <span class="bb-wa-float-tooltip">Pesan via WA</span>
</a>

<?php require_once 'includes/footer.php'; ?>
