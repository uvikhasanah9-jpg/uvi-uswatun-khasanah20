<?php
// ============================================================
//  BLISSFULBERRY – Form Pemesanan (pesan-produk.php)
// ============================================================
require_once 'config/db.php';
$page_title = 'Form Pemesanan';

$id_produk = intval($_GET['id'] ?? 0);
$produk = null;
if ($id_produk) {
    $st = $conn->prepare("SELECT * FROM produk WHERE id = ?");
    $st->bind_param('i', $id_produk);
    $st->execute();
    $produk = $st->get_result()->fetch_assoc();
}

$success = $error = '';
$kode_pesanan_baru = '';

// ---- PROSES SUBMIT ----
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama_pemesan = bersihkan($_POST['nama_pemesan'] ?? '');
    $wa_pemesan   = preg_replace('/\D/', '', $_POST['wa_pemesan'] ?? '');
    $nama_produk  = bersihkan($_POST['nama_produk'] ?? '');
    $jumlah       = max(1, intval($_POST['jumlah'] ?? 1));
    $catatan      = bersihkan($_POST['catatan'] ?? '');
    $id_produk_p  = intval($_POST['id_produk'] ?? 0);
    $harga_satuan = intval($_POST['harga_satuan'] ?? 0);
    $total        = $jumlah * $harga_satuan;
    $kode         = generateKodePesanan();

    if (strlen($nama_pemesan) < 3) {
        $error = 'Nama pemesan minimal 3 karakter.';
    } elseif (strlen($wa_pemesan) < 10) {
        $error = 'Nomor WhatsApp tidak valid.';
    } elseif (!$nama_produk) {
        $error = 'Nama produk harus diisi.';
    } else {
        $ins = $conn->prepare("INSERT INTO pesanan (kode_pesanan, nama_pemesan, wa_pemesan, nama_produk, id_produk, jumlah, harga_satuan, total_harga, catatan, status) VALUES (?,?,?,?,?,?,?,?,?,'Menunggu')");
        $ins->bind_param('ssssiiiis', $kode, $nama_pemesan, $wa_pemesan, $nama_produk, $id_produk_p, $jumlah, $harga_satuan, $total, $catatan);

        if ($ins->execute()) {
            $success = $kode;
            $kode_pesanan_baru = $kode;

            // Build WA URL
            $wa_penjual = $produk['wa_penjual'] ?? '6281234567890';
            $wa_msg = "Halo Blissfulberry! 👋\n".
                      "Saya ingin konfirmasi pesanan:\n\n".
                      "🧾 No. Pesanan : $kode\n".
                      "👤 Nama       : $nama_pemesan\n".
                      "📱 WhatsApp   : $wa_pemesan\n".
                      "🛍️ Produk     : $nama_produk\n".
                      "🔢 Jumlah     : $jumlah porsi\n".
                      "💰 Total      : " . rupiah($total) . "\n".
                      ($catatan ? "📝 Catatan    : $catatan\n" : "").
                      "\nMohon segera diproses. Terima kasih! 🙏";
            $wa_url = "https://wa.me/{$wa_penjual}?text=" . urlencode($wa_msg);
        } else {
            $error = 'Gagal menyimpan pesanan: ' . $conn->error;
        }
    }
}

require_once 'includes/header.php';
?>

<!-- Page Header -->
<section class="bb-page-header">
    <div class="container">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-2" style="font-size:0.82rem; opacity:0.8;">
                <li class="breadcrumb-item"><a href="index.php" class="text-white">Beranda</a></li>
                <li class="breadcrumb-item"><a href="katalog.php" class="text-white">Katalog</a></li>
                <?php if ($produk): ?>
                <li class="breadcrumb-item"><a href="detail.php?id=<?= $produk['id'] ?>" class="text-white"><?= htmlspecialchars($produk['nama_produk']) ?></a></li>
                <?php endif; ?>
                <li class="breadcrumb-item active text-white">Form Pemesanan</li>
            </ol>
        </nav>
        <h1><i class="bi bi-bag-plus me-2"></i>Form Pemesanan</h1>
        <p>Isi data di bawah untuk memesan produk Blissfulberry</p>
    </div>
</section>

<section class="py-5">
    <div class="container">
        <?php if ($success): ?>
        <!-- ===== SUCCESS STATE ===== -->
        <div class="bb-animate text-center mb-5">
            <div style="font-size:4rem;">✅</div>
            <h2 class="fw-800 mt-3" style="color:var(--dark-berry);">Pesanan Berhasil!</h2>
            <p class="text-muted">Nomor pesanan Anda: <strong class="text-pink"><?= htmlspecialchars($kode_pesanan_baru) ?></strong></p>
        </div>

        <?php
        // Ambil data pesanan yang baru dibuat
        $st3 = $conn->prepare("SELECT * FROM pesanan WHERE kode_pesanan = ?");
        $st3->bind_param('s', $kode_pesanan_baru);
        $st3->execute();
        $ord = $st3->get_result()->fetch_assoc();
        $wa_penjual2 = $produk['wa_penjual'] ?? '6281234567890';
        $wa_msg2 = "Halo Blissfulberry! 👋\nSaya ingin konfirmasi pesanan:\n\n".
                   "🧾 No. Pesanan : {$ord['kode_pesanan']}\n".
                   "👤 Nama       : {$ord['nama_pemesan']}\n".
                   "📱 WhatsApp   : {$ord['wa_pemesan']}\n".
                   "🛍️ Produk     : {$ord['nama_produk']}\n".
                   "🔢 Jumlah     : {$ord['jumlah']} porsi\n".
                   "💰 Total      : " . rupiah($ord['total_harga']) . "\n".
                   ($ord['catatan'] ? "📝 Catatan    : {$ord['catatan']}\n" : "").
                   "\nMohon segera diproses. Terima kasih! 🙏";
        $wa_url2 = "https://wa.me/{$wa_penjual2}?text=" . urlencode($wa_msg2);
        ?>

        <div class="row justify-content-center">
            <div class="col-lg-6">
                <div class="bb-order-summary">
                    <h5><i class="bi bi-receipt me-2 text-pink"></i>Ringkasan Pesanan</h5>
                    <div class="bb-order-row">
                        <span class="bb-order-label">No. Pesanan</span>
                        <span class="bb-order-value text-pink fw-bold"><?= htmlspecialchars($ord['kode_pesanan']) ?></span>
                    </div>
                    <div class="bb-order-row">
                        <span class="bb-order-label">Nama Pemesan</span>
                        <span class="bb-order-value"><?= htmlspecialchars($ord['nama_pemesan']) ?></span>
                    </div>
                    <div class="bb-order-row">
                        <span class="bb-order-label">WhatsApp</span>
                        <span class="bb-order-value"><?= htmlspecialchars($ord['wa_pemesan']) ?></span>
                    </div>
                    <div class="bb-order-row">
                        <span class="bb-order-label">Produk</span>
                        <span class="bb-order-value"><?= htmlspecialchars($ord['nama_produk']) ?></span>
                    </div>
                    <div class="bb-order-row">
                        <span class="bb-order-label">Jumlah</span>
                        <span class="bb-order-value"><?= $ord['jumlah'] ?> porsi</span>
                    </div>
                    <div class="bb-order-row">
                        <span class="bb-order-label">Harga Satuan</span>
                        <span class="bb-order-value"><?= rupiah($ord['harga_satuan']) ?></span>
                    </div>
                    <?php if ($ord['catatan']): ?>
                    <div class="bb-order-row">
                        <span class="bb-order-label">Catatan</span>
                        <span class="bb-order-value"><?= htmlspecialchars($ord['catatan']) ?></span>
                    </div>
                    <?php endif; ?>
                    <div class="bb-order-row bb-order-total mt-2 pt-2" style="border-top:2px dashed var(--pink-light);">
                        <span class="bb-order-label fw-bold fs-6">Total Pembayaran</span>
                        <span class="bb-order-value"><?= rupiah($ord['total_harga']) ?></span>
                    </div>
                    <div class="mt-3">
                        <span class="bb-badge bb-badge-menunggu">Status: Menunggu</span>
                    </div>
                </div>

                <!-- CTA Buttons -->
                <div class="d-flex flex-column gap-3 mt-4">
                    <a href="<?= $wa_url2 ?>" target="_blank" class="btn bb-btn-wa btn-lg w-100 bb-pulse">
                        <i class="bi bi-whatsapp me-2"></i>Konfirmasi Pesanan via WhatsApp
                    </a>
                    <div class="d-flex gap-3">
                        <a href="katalog.php" class="btn bb-btn-outline flex-fill">
                            <i class="bi bi-grid me-1"></i>Lanjut Belanja
                        </a>
                        <a href="daftar-pesanan.php" class="btn bb-btn-primary flex-fill">
                            <i class="bi bi-list-check me-1"></i>Lihat Pesanan
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <?php else: ?>
        <!-- ===== FORM PEMESANAN ===== -->
        <div class="row justify-content-center g-5">
            <div class="col-lg-7">

                <?php if ($error): ?>
                <div class="bb-alert bb-alert-danger mb-4 bb-auto-dismiss">
                    <i class="bi bi-exclamation-triangle"></i> <?= htmlspecialchars($error) ?>
                </div>
                <?php endif; ?>

                <div class="bb-form-card">
                    <h4 class="fw-700 mb-4" style="color:var(--dark-berry);">
                        <i class="bi bi-bag-plus me-2 text-pink"></i>Detail Pemesanan
                    </h4>

                    <form id="formPemesanan" method="POST" novalidate>
                        <input type="hidden" name="id_produk"    value="<?= $produk['id'] ?? '' ?>">
                        <input type="hidden" name="harga_satuan" id="harga_satuan" value="<?= $produk['harga'] ?? 0 ?>">

                        <div class="row g-3">
                            <!-- Nama Pemesan -->
                            <div class="col-12">
                                <label for="nama_pemesan" class="bb-form-label">
                                    <i class="bi bi-person me-1 text-pink"></i>Nama Pemesan <span class="text-danger">*</span>
                                </label>
                                <input type="text" id="nama_pemesan" name="nama_pemesan"
                                       class="form-control bb-form-control"
                                       placeholder="Masukkan nama lengkap Anda"
                                       value="<?= htmlspecialchars($_POST['nama_pemesan'] ?? '') ?>" required>
                                <div class="invalid-feedback">Nama minimal 3 karakter.</div>
                            </div>

                            <!-- WA Pemesan -->
                            <div class="col-12">
                                <label for="wa_pemesan" class="bb-form-label">
                                    <i class="bi bi-whatsapp me-1 text-success"></i>Nomor WhatsApp <span class="text-danger">*</span>
                                </label>
                                <div class="input-group">
                                    <span class="input-group-text" style="border:2px solid var(--pink-pale);border-right:none;border-radius:var(--radius) 0 0 var(--radius);background:#FFFAFD;">
                                        +62
                                    </span>
                                    <input type="tel" id="wa_pemesan" name="wa_pemesan"
                                           class="form-control bb-form-control"
                                           style="border-radius:0 var(--radius) var(--radius) 0!important;"
                                           placeholder="8xxxxxxxxxx"
                                           value="<?= htmlspecialchars($_POST['wa_pemesan'] ?? '') ?>" required>
                                </div>
                                <div class="form-text text-muted" style="font-size:0.78rem;">
                                    <i class="bi bi-info-circle me-1"></i>Format: 08xxxx atau 628xxxx (tanpa tanda +)
                                </div>
                                <div class="invalid-feedback">Nomor WhatsApp tidak valid.</div>
                            </div>

                            <!-- Nama Produk -->
                            <div class="col-12">
                                <label for="nama_produk" class="bb-form-label">
                                    <i class="bi bi-box-seam me-1 text-pink"></i>Nama Produk <span class="text-danger">*</span>
                                </label>
                                <input type="text" name="nama_produk"
                                       class="form-control bb-form-control"
                                       value="<?= htmlspecialchars($produk['nama_produk'] ?? ($_POST['nama_produk'] ?? '')) ?>"
                                       <?= $produk ? 'readonly' : '' ?>
                                       placeholder="Nama produk yang dipesan" required>
                            </div>

                            <!-- Jumlah -->
                            <div class="col-sm-6">
                                <label for="jumlah" class="bb-form-label">
                                    <i class="bi bi-123 me-1 text-pink"></i>Jumlah Pesanan <span class="text-danger">*</span>
                                </label>
                                <input type="number" id="jumlah" name="jumlah" min="1" max="100"
                                       class="form-control bb-form-control"
                                       value="<?= intval($_POST['jumlah'] ?? 1) ?>" required>
                                <div class="invalid-feedback">Jumlah minimal 1.</div>
                            </div>

                            <!-- Harga & Total Preview -->
                            <div class="col-sm-6">
                                <label class="bb-form-label">
                                    <i class="bi bi-cash me-1 text-pink"></i>Total Harga
                                </label>
                                <div class="form-control bb-form-control d-flex align-items-center justify-content-center fw-700 text-pink"
                                     id="preview_total" style="font-size:1.1rem;cursor:default;">
                                    <?= rupiah(($produk['harga'] ?? 0) * intval($_POST['jumlah'] ?? 1)) ?>
                                </div>
                            </div>

                            <!-- Catatan -->
                            <div class="col-12">
                                <label for="catatan" class="bb-form-label">
                                    <i class="bi bi-chat-text me-1 text-pink"></i>Catatan <span class="text-muted">(opsional)</span>
                                </label>
                                <textarea id="catatan" name="catatan" rows="3"
                                          class="form-control bb-form-control"
                                          placeholder="Contoh: tanpa gula, tambah topping, dll."><?= htmlspecialchars($_POST['catatan'] ?? '') ?></textarea>
                            </div>

                            <div class="col-12 mt-2">
                                <div id="alertContainer"></div>
                                <button type="submit" class="btn bb-btn-primary btn-lg w-100 py-3">
                                    <i class="bi bi-bag-check me-2"></i>Kirim Pesanan Sekarang
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Sidebar: Info Produk -->
            <?php if ($produk): ?>
            <div class="col-lg-5">
                <div class="bb-order-summary mb-4">
                    <h5><i class="bi bi-box-seam me-2 text-pink"></i>Info Produk</h5>
                    <img src="<?= htmlspecialchars($produk['foto'] ?: 'assets/images/menu-full-blissfulberry.jpg') ?>"
                         onerror="this.src='assets/images/menu-full-blissfulberry.jpg'"
                         alt="<?= htmlspecialchars($produk['nama_produk']) ?>"
                         class="img-fluid rounded-3 mb-3 w-100" style="max-height:180px;object-fit:cover;">
                    <div class="bb-order-row">
                        <span class="bb-order-label">Produk</span>
                        <span class="bb-order-value"><?= htmlspecialchars($produk['nama_produk']) ?></span>
                    </div>
                    <div class="bb-order-row">
                        <span class="bb-order-label">Kategori</span>
                        <span class="bb-order-value"><?= htmlspecialchars($produk['kategori']) ?></span>
                    </div>
                    <div class="bb-order-row bb-order-total">
                        <span class="bb-order-label">Harga / porsi</span>
                        <span class="bb-order-value"><?= rupiah($produk['harga']) ?></span>
                    </div>
                </div>

                <div class="bb-form-card" style="background:linear-gradient(135deg,#E8F5E9,#C8E6C9);">
                    <h6 class="fw-bold mb-3" style="color:#2E7D32;">
                        <i class="bi bi-whatsapp me-2"></i>Langsung Chat WA
                    </h6>
                    <p class="small text-muted mb-3">Mau tanya-tanya dulu sebelum pesan? Chat kami langsung!</p>
                    <?php
                    $wa_quick = "https://wa.me/{$produk['wa_penjual']}?text=" . urlencode(
                        "Halo Blissfulberry! Saya tertarik dengan produk {$produk['nama_produk']} (harga " . rupiah($produk['harga']) . "). Apakah stok tersedia? 😊"
                    );
                    ?>
                    <a href="<?= $wa_quick ?>" target="_blank" class="btn bb-btn-wa w-100">
                        <i class="bi bi-whatsapp me-2"></i>Tanya via WhatsApp
                    </a>
                </div>
            </div>
            <?php endif; ?>
        </div>
        <?php endif; ?>
    </div>
</section>

<a href="https://wa.me/6281234567890" target="_blank" class="bb-wa-float">
    <i class="bi bi-whatsapp"></i>
</a>

<?php require_once 'includes/footer.php'; ?>
