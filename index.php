<?php
// ============================================================
//  BLISSFULBERRY – Beranda (index.php)
//  Dashboard: statistik, produk unggulan, testimoni
// ============================================================
require_once 'config/db.php';
$page_title = 'Beranda';

// Ambil statistik
$total_produk   = $conn->query("SELECT COUNT(*) FROM produk")->fetch_row()[0];
$total_pesanan  = $conn->query("SELECT COUNT(*) FROM pesanan")->fetch_row()[0];
$pesanan_proses = $conn->query("SELECT COUNT(*) FROM pesanan WHERE status='Diproses'")->fetch_row()[0];
$pesanan_selesai= $conn->query("SELECT COUNT(*) FROM pesanan WHERE status='Selesai'")->fetch_row()[0];

// Ambil produk unggulan (6 terbaru)
$produk_unggulan = $conn->query("SELECT * FROM produk ORDER BY created_at DESC LIMIT 6");

// Ambil kategori
$kategori_list = $conn->query("SELECT DISTINCT kategori FROM produk ORDER BY kategori");

require_once 'includes/header.php';
?>

<!-- ===== HERO SECTION ===== -->
<section class="bb-hero">
    <div class="container">
        <div class="row align-items-center g-5">
            <div class="col-lg-6 bb-animate">
                <span class="badge bg-pink-soft text-pink fw-semibold px-3 py-2 rounded-pill mb-3 d-inline-block"
                      style="background:#FFE8F3!important;">
                    🍓 UMKM Kuliner Lokal Indonesia
                </span>
                <h1 class="bb-hero-title mb-3">
                    Nikmati Kelezatan<br><span>Blissfulberry!</span>
                </h1>
                <p class="bb-hero-subtitle mb-4">
                    Dessert & snack premium lokal — Banana Pudding, Gabin Ice Cream, Acai Bowl,
                    Mac N Cheese, Chocoberry, dan banyak lagi. Pesan sekarang, dikirim dengan kasih sayang! 🍦
                </p>
                <div class="d-flex flex-wrap gap-3">
                    <a href="katalog.php" class="btn bb-btn-primary bb-pulse">
                        <i class="bi bi-grid-3x3-gap me-2"></i>Lihat Katalog
                    </a>
                    <a href="https://wa.me/6281234567890?text=Halo+Blissfulberry!+Saya+ingin+memesan." target="_blank"
                       class="btn bb-btn-wa">
                        <i class="bi bi-whatsapp me-2"></i>Chat WhatsApp
                    </a>
                </div>
            </div>
            <div class="col-lg-6 text-center bb-animate bb-delay-2">
                <img src="assets/images/hero-banner.jpg"
                     onerror="this.src='assets/images/menu-full-blissfulberry.jpg'"
                     alt="Blissfulberry Menu" class="bb-hero-img img-fluid">
            </div>
        </div>
    </div>
</section>

<!-- ===== DASHBOARD STATISTIK ===== -->
<section class="py-5" style="background:#fff;">
    <div class="container">
        <div class="text-center mb-4">
            <h2 class="bb-section-title">Dashboard Blissfulberry</h2>
            <p class="bb-section-subtitle mt-3">Ringkasan data produk dan pesanan</p>
        </div>
        <div class="row g-4" data-animate>
            <div class="col-6 col-lg-3">
                <div class="bb-stat-card text-center bb-animate">
                    <div class="bb-stat-icon pink mx-auto">
                        <i class="bi bi-box-seam"></i>
                    </div>
                    <div class="bb-stat-num"><?= $total_produk ?></div>
                    <div class="bb-stat-label">Total Produk</div>
                </div>
            </div>
            <div class="col-6 col-lg-3">
                <div class="bb-stat-card text-center bb-animate bb-delay-1">
                    <div class="bb-stat-icon orange mx-auto">
                        <i class="bi bi-bag-check"></i>
                    </div>
                    <div class="bb-stat-num"><?= $total_pesanan ?></div>
                    <div class="bb-stat-label">Total Pesanan</div>
                </div>
            </div>
            <div class="col-6 col-lg-3">
                <div class="bb-stat-card text-center bb-animate bb-delay-2">
                    <div class="bb-stat-icon purple mx-auto">
                        <i class="bi bi-clock-history"></i>
                    </div>
                    <div class="bb-stat-num"><?= $pesanan_proses ?></div>
                    <div class="bb-stat-label">Sedang Diproses</div>
                </div>
            </div>
            <div class="col-6 col-lg-3">
                <div class="bb-stat-card text-center bb-animate bb-delay-3">
                    <div class="bb-stat-icon green mx-auto">
                        <i class="bi bi-check-circle"></i>
                    </div>
                    <div class="bb-stat-num"><?= $pesanan_selesai ?></div>
                    <div class="bb-stat-label">Pesanan Selesai</div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- ===== KATEGORI ===== -->
<section class="py-5" style="background:var(--pink-soft);">
    <div class="container">
        <div class="text-center mb-4">
            <h2 class="bb-section-title">Kategori Menu</h2>
            <p class="bb-section-subtitle mt-3">Pilih kategori favoritmu</p>
        </div>
        <div class="d-flex flex-wrap gap-3 justify-content-center">
            <a href="katalog.php" class="bb-filter-chip active">
                <i class="bi bi-grid-3x3-gap"></i> Semua Menu
            </a>
            <?php
            $kategori_list->data_seek(0);
            $emoji_map = [
                'Banana Pudding' => '🍌',
                'Gabin Ice Cream'=> '🍦',
                'Gabin Ice Bar'  => '🧊',
                'Acai Bowl'      => '🫐',
                'Chocoberry'     => '🍫',
                'Mac N Cheese'   => '🧀',
                'Ice Cream Cone' => '🍦',
            ];
            while ($k = $kategori_list->fetch_assoc()):
                $em = $emoji_map[$k['kategori']] ?? '🍽️';
            ?>
            <a href="katalog.php?kategori=<?= urlencode($k['kategori']) ?>" class="bb-filter-chip">
                <?= $em ?> <?= htmlspecialchars($k['kategori']) ?>
            </a>
            <?php endwhile; ?>
        </div>
    </div>
</section>

<!-- ===== PRODUK UNGGULAN ===== -->
<section class="py-5">
    <div class="container">
        <div class="d-flex align-items-center justify-content-between mb-4 flex-wrap gap-3">
            <div>
                <h2 class="bb-section-title">Produk Terbaru</h2>
                <p class="bb-section-subtitle mt-3">Menu segar dari dapur Blissfulberry</p>
            </div>
            <a href="katalog.php" class="btn bb-btn-outline">
                Lihat Semua <i class="bi bi-arrow-right ms-1"></i>
            </a>
        </div>

        <div class="row g-4">
            <?php
            $produk_unggulan->data_seek(0);
            $delay = 0;
            while ($p = $produk_unggulan->fetch_assoc()):
                $foto = !empty($p['foto']) ? $p['foto'] : 'assets/images/menu-full-blissfulberry.jpg';
            ?>
            <div class="col-sm-6 col-lg-4 bb-animate" style="animation-delay:<?= $delay*0.1 ?>s">
                <div class="bb-card">
                    <div class="bb-card-img-wrap">
                        <img src="<?= htmlspecialchars($foto) ?>"
                             onerror="this.src='assets/images/menu-full-blissfulberry.jpg'"
                             alt="<?= htmlspecialchars($p['nama_produk']) ?>">
                        <div class="bb-card-badge"><?= htmlspecialchars($p['kategori']) ?></div>
                    </div>
                    <div class="bb-card-body">
                        <div class="bb-card-title"><?= htmlspecialchars($p['nama_produk']) ?></div>
                        <div class="bb-card-desc"><?= htmlspecialchars($p['deskripsi']) ?></div>
                        <div class="bb-card-price"><?= rupiah($p['harga']) ?></div>
                        <div class="bb-card-actions">
                            <a href="detail.php?id=<?= $p['id'] ?>" class="btn bb-btn-outline btn-sm flex-fill">
                                <i class="bi bi-eye me-1"></i>Detail
                            </a>
                            <a href="pesan-produk.php?id=<?= $p['id'] ?>" class="btn bb-btn-primary btn-sm flex-fill">
                                <i class="bi bi-bag-plus me-1"></i>Pesan
                            </a>
                        </div>
                    </div>
                </div>
            </div>
            <?php $delay++; endwhile; ?>
        </div>
    </div>
</section>

<!-- ===== KEUNGGULAN ===== -->
<section class="py-5" style="background:var(--pink-soft);">
    <div class="container">
        <div class="text-center mb-5">
            <h2 class="bb-section-title">Mengapa Blissfulberry?</h2>
        </div>
        <div class="row g-4 text-center">
            <div class="col-sm-6 col-lg-3">
                <div class="bb-stat-card py-4 h-100">
                    <div class="mb-3 fs-2">🍓</div>
                    <h5 class="fw-700" style="color:var(--dark-berry)">Bahan Segar</h5>
                    <p class="text-muted small mb-0">Menggunakan buah-buahan segar dan bahan berkualitas premium setiap harinya.</p>
                </div>
            </div>
            <div class="col-sm-6 col-lg-3">
                <div class="bb-stat-card py-4 h-100">
                    <div class="mb-3 fs-2">💝</div>
                    <h5 class="fw-700" style="color:var(--dark-berry)">Dibuat dengan Cinta</h5>
                    <p class="text-muted small mb-0">Setiap produk dibuat dengan penuh kasih sayang oleh tim Blissfulberry.</p>
                </div>
            </div>
            <div class="col-sm-6 col-lg-3">
                <div class="bb-stat-card py-4 h-100">
                    <div class="mb-3 fs-2">🚀</div>
                    <h5 class="fw-700" style="color:var(--dark-berry)">Proses Cepat</h5>
                    <p class="text-muted small mb-0">Pesanan diproses dengan cepat dan pelanggan diinformasikan via WhatsApp.</p>
                </div>
            </div>
            <div class="col-sm-6 col-lg-3">
                <div class="bb-stat-card py-4 h-100">
                    <div class="mb-3 fs-2">💬</div>
                    <h5 class="fw-700" style="color:var(--dark-berry)">Layanan Ramah</h5>
                    <p class="text-muted small mb-0">Tim kami siap membantu pertanyaan dan pesanan melalui WhatsApp.</p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- ===== WA FLOAT BUTTON ===== -->
<a href="https://wa.me/6281234567890?text=Halo+Blissfulberry!+Saya+ingin+bertanya+tentang+produk+kalian." 
   target="_blank" class="bb-wa-float" title="Chat WhatsApp">
    <i class="bi bi-whatsapp"></i>
    <span class="bb-wa-float-tooltip">Chat via WhatsApp</span>
</a>

<?php require_once 'includes/footer.php'; ?>
