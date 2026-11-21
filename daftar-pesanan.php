<?php
// ============================================================
//  BLISSFULBERRY – Daftar Pesanan Publik (daftar-pesanan.php)
// ============================================================
require_once 'config/db.php';
$page_title = 'Daftar Pesanan';

// Cek kode pesanan
$kode_cari = bersihkan($_GET['kode'] ?? '');
$pesanan_found = null;
$msg = '';

if ($kode_cari) {
    $st = $conn->prepare("SELECT * FROM pesanan WHERE kode_pesanan = ?");
    $st->bind_param('s', $kode_cari);
    $st->execute();
    $pesanan_found = $st->get_result()->fetch_assoc();
    if (!$pesanan_found) $msg = 'Kode pesanan tidak ditemukan.';
}

// Daftar pesanan terbaru (10)
$recent = $conn->query("SELECT * FROM pesanan ORDER BY created_at DESC LIMIT 15");

require_once 'includes/header.php';
?>

<!-- Page Header -->
<section class="bb-page-header">
    <div class="container">
        <h1><i class="bi bi-list-check me-2"></i>Daftar Pesanan</h1>
        <p>Lacak status pesanan Anda di sini</p>
    </div>
</section>

<section class="py-5">
    <div class="container">
        <div class="row g-5">
            <!-- Lacak Pesanan -->
            <div class="col-lg-5">
                <div class="bb-form-card">
                    <h5 class="fw-700 mb-4" style="color:var(--dark-berry);">
                        <i class="bi bi-search me-2 text-pink"></i>Lacak Pesanan
                    </h5>
                    <form method="GET">
                        <label class="bb-form-label">Nomor Pesanan</label>
                        <input type="text" name="kode" class="form-control bb-form-control mb-3"
                               placeholder="Contoh: ORD-20261119-ABCDEF"
                               value="<?= htmlspecialchars($kode_cari) ?>">
                        <button type="submit" class="btn bb-btn-primary w-100">
                            <i class="bi bi-search me-2"></i>Lacak Sekarang
                        </button>
                    </form>

                    <?php if ($msg): ?>
                    <div class="bb-alert bb-alert-danger mt-3">
                        <i class="bi bi-exclamation-triangle"></i> <?= htmlspecialchars($msg) ?>
                    </div>
                    <?php endif; ?>

                    <?php if ($pesanan_found): ?>
                    <div class="bb-order-summary mt-4">
                        <h5><i class="bi bi-receipt me-2 text-pink"></i>Detail Pesanan</h5>
                        <div class="bb-order-row">
                            <span class="bb-order-label">Kode</span>
                            <span class="bb-order-value text-pink fw-bold"><?= htmlspecialchars($pesanan_found['kode_pesanan']) ?></span>
                        </div>
                        <div class="bb-order-row">
                            <span class="bb-order-label">Nama</span>
                            <span class="bb-order-value"><?= htmlspecialchars($pesanan_found['nama_pemesan']) ?></span>
                        </div>
                        <div class="bb-order-row">
                            <span class="bb-order-label">Produk</span>
                            <span class="bb-order-value"><?= htmlspecialchars($pesanan_found['nama_produk']) ?></span>
                        </div>
                        <div class="bb-order-row">
                            <span class="bb-order-label">Jumlah</span>
                            <span class="bb-order-value"><?= $pesanan_found['jumlah'] ?> porsi</span>
                        </div>
                        <div class="bb-order-row bb-order-total">
                            <span class="bb-order-label fw-bold">Total</span>
                            <span class="bb-order-value"><?= rupiah($pesanan_found['total_harga']) ?></span>
                        </div>
                        <div class="mt-3 d-flex align-items-center gap-2">
                            <span class="text-muted small">Status:</span>
                            <?php
                            $st_class = [
                                'Menunggu'   => 'menunggu',
                                'Diproses'   => 'diproses',
                                'Selesai'    => 'selesai',
                                'Dibatalkan' => 'dibatalkan',
                            ];
                            $sc = $st_class[$pesanan_found['status']] ?? 'menunggu';
                            ?>
                            <span class="bb-badge bb-badge-<?= $sc ?>"><?= $pesanan_found['status'] ?></span>
                        </div>
                        <?php
                        $wa_id = $pesanan_found['id_produk'];
                        $wa_no = '6281234567890';
                        if ($wa_id) {
                            $rp = $conn->query("SELECT wa_penjual FROM produk WHERE id=$wa_id")->fetch_assoc();
                            if ($rp) $wa_no = $rp['wa_penjual'];
                        }
                        $wa_msg_track = urlencode("Halo Blissfulberry! 👋\nSaya ingin menanyakan status pesanan:\n🧾 Kode: {$pesanan_found['kode_pesanan']}\n👤 Nama: {$pesanan_found['nama_pemesan']}\n\nMohon infonya. Terima kasih!");
                        ?>
                        <a href="https://wa.me/<?= $wa_no ?>?text=<?= $wa_msg_track ?>" target="_blank"
                           class="btn bb-btn-wa w-100 mt-3">
                            <i class="bi bi-whatsapp me-2"></i>Tanya Status via WhatsApp
                        </a>
                    </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Daftar Pesanan Terbaru -->
            <div class="col-lg-7">
                <h4 class="bb-section-title mb-4">Pesanan Terbaru</h4>
                <?php if ($recent->num_rows === 0): ?>
                <div class="bb-empty-state">
                    <i class="bi bi-bag-x"></i>
                    <p>Belum ada pesanan. <a href="katalog.php">Pesan sekarang!</a></p>
                </div>
                <?php else: ?>
                <div class="d-flex flex-column gap-3">
                    <?php while ($o = $recent->fetch_assoc()):
                        $sc = $st_class[$o['status']] ?? 'menunggu';
                    ?>
                    <div class="bb-form-card p-4" style="border-left:4px solid var(--pink);">
                        <div class="d-flex align-items-start justify-content-between flex-wrap gap-2">
                            <div>
                                <div class="d-flex align-items-center gap-2 mb-1">
                                    <code class="text-pink fw-bold" style="font-size:0.82rem;"><?= htmlspecialchars($o['kode_pesanan']) ?></code>
                                    <span class="bb-badge bb-badge-<?= $sc ?>"><?= $o['status'] ?></span>
                                </div>
                                <div class="fw-semibold" style="color:var(--dark-berry);"><?= htmlspecialchars($o['nama_pemesan']) ?></div>
                                <div class="text-muted small"><?= htmlspecialchars($o['nama_produk']) ?> × <?= $o['jumlah'] ?></div>
                            </div>
                            <div class="text-end">
                                <div class="text-pink fw-700"><?= rupiah($o['total_harga']) ?></div>
                                <div class="text-muted" style="font-size:0.76rem;"><?= date('d M Y', strtotime($o['created_at'])) ?></div>
                            </div>
                        </div>
                    </div>
                    <?php endwhile; ?>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</section>

<a href="https://wa.me/6281234567890" target="_blank" class="bb-wa-float">
    <i class="bi bi-whatsapp"></i>
</a>

<?php require_once 'includes/footer.php'; ?>
