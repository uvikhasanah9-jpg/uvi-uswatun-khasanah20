<?php
// ============================================================
//  BLISSFULBERRY – Katalog Produk (katalog.php)
//  Fitur: search, filter kategori, sort harga, pagination
// ============================================================
require_once 'config/db.php';
$page_title = 'Katalog Produk';

// Filter & search
$kategori_filter = isset($_GET['kategori']) ? bersihkan($_GET['kategori']) : '';
$search          = isset($_GET['q'])        ? bersihkan($_GET['q'])        : '';

// Build query
$where = [];
$params = [];
$types  = '';
if ($kategori_filter) { $where[] = 'kategori = ?'; $params[] = $kategori_filter; $types .= 's'; }
if ($search)          { $where[] = '(nama_produk LIKE ? OR kategori LIKE ? OR deskripsi LIKE ?)';
                         $params[] = "%$search%"; $params[] = "%$search%"; $params[] = "%$search%"; $types .= 'sss'; }

$sql_where = $where ? 'WHERE ' . implode(' AND ', $where) : '';

// Pagination
$per_page = 12;
$page     = max(1, intval($_GET['page'] ?? 1));
$offset   = ($page - 1) * $per_page;

// Count total
$sql_count = "SELECT COUNT(*) FROM produk $sql_where";
$stmt = $conn->prepare($sql_count);
if ($params) $stmt->bind_param($types, ...$params);
$stmt->execute();
$total_rows = $stmt->get_result()->fetch_row()[0];
$total_pages= ceil($total_rows / $per_page);

// Fetch produk
$sql = "SELECT * FROM produk $sql_where ORDER BY created_at DESC LIMIT ? OFFSET ?";
$stmt = $conn->prepare($sql);
$all_params = array_merge($params, [$per_page, $offset]);
$all_types  = $types . 'ii';
$stmt->bind_param($all_types, ...$all_params);
$stmt->execute();
$produk_res = $stmt->get_result();

// Fetch kategori untuk filter
$kategori_res = $conn->query("SELECT DISTINCT kategori, COUNT(*) as jml FROM produk GROUP BY kategori ORDER BY kategori");

require_once 'includes/header.php';
?>

<!-- ===== PAGE HEADER ===== -->
<section class="bb-page-header">
    <div class="container">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-2" style="font-size:0.82rem; opacity:0.8;">
                <li class="breadcrumb-item"><a href="index.php" class="text-white">Beranda</a></li>
                <li class="breadcrumb-item active text-white">Katalog Produk</li>
            </ol>
        </nav>
        <h1><i class="bi bi-grid-3x3-gap me-2"></i>Katalog Produk</h1>
        <p>Temukan menu lezat Blissfulberry favorit kamu!</p>
    </div>
</section>

<!-- ===== FILTER & SEARCH BAR ===== -->
<section class="py-4" style="background:#fff;border-bottom:2px solid var(--pink-pale);">
    <div class="container">
        <form method="GET" class="d-flex flex-column flex-md-row gap-3 align-items-md-center">
            <!-- Search -->
            <div class="bb-search-wrap flex-grow-1">
                <i class="bi bi-search"></i>
                <input type="text" id="searchProduk" name="q" class="form-control bb-search-input"
                       placeholder="Cari nama produk atau kategori..."
                       value="<?= htmlspecialchars($search) ?>">
            </div>
            <!-- Sort -->
            <select name="sort" id="sortProduk" class="form-select bb-form-control" style="max-width:200px;">
                <option value="">Urutan Default</option>
                <option value="harga-asc"  <?= (($_GET['sort'] ?? '') === 'harga-asc')  ? 'selected' : '' ?>>Harga: Terendah</option>
                <option value="harga-desc" <?= (($_GET['sort'] ?? '') === 'harga-desc') ? 'selected' : '' ?>>Harga: Tertinggi</option>
                <option value="nama">Nama A–Z</option>
            </select>
            <button type="submit" class="btn bb-btn-primary" style="white-space:nowrap;">
                <i class="bi bi-search me-1"></i> Cari
            </button>
            <?php if ($search || $kategori_filter): ?>
            <a href="katalog.php" class="btn bb-btn-outline" style="white-space:nowrap;">
                <i class="bi bi-x me-1"></i> Reset
            </a>
            <?php endif; ?>
        </form>
    </div>
</section>

<section class="py-5">
    <div class="container">
        <div class="row g-4">
            <!-- ===== SIDEBAR KATEGORI ===== -->
            <div class="col-lg-3">
                <div class="bb-form-card p-0 overflow-hidden">
                    <div class="p-3" style="background:linear-gradient(135deg,var(--dark-berry),#8B1A5A);">
                        <h6 class="text-white fw-bold mb-0"><i class="bi bi-funnel me-2"></i>Filter Kategori</h6>
                    </div>
                    <div class="p-3">
                        <a href="katalog.php<?= $search ? '?q='.urlencode($search) : '' ?>"
                           class="d-flex justify-content-between align-items-center py-2 px-3 rounded-3 mb-1 text-decoration-none
                                  <?= !$kategori_filter ? 'text-white fw-semibold' : 'text-muted' ?>"
                           style="<?= !$kategori_filter ? 'background:var(--pink);' : '' ?>">
                            <span><i class="bi bi-grid me-2"></i>Semua Produk</span>
                            <small class="badge bg-secondary"><?= $total_rows ?></small>
                        </a>
                        <?php while ($k = $kategori_res->fetch_assoc()):
                            $active = $k['kategori'] === $kategori_filter;
                        ?>
                        <a href="katalog.php?kategori=<?= urlencode($k['kategori']) ?><?= $search ? '&q='.urlencode($search) : '' ?>"
                           class="d-flex justify-content-between align-items-center py-2 px-3 rounded-3 mb-1 text-decoration-none
                                  <?= $active ? 'text-white fw-semibold' : 'text-muted' ?>"
                           style="<?= $active ? 'background:var(--pink);' : 'transition:background .2s;' ?>"
                           onmouseover="<?= !$active ? 'this.style.background=\'var(--pink-soft)\'':''; ?>"
                           onmouseout="<?= !$active ? 'this.style.background=\'\'':''; ?>">
                            <span><?= htmlspecialchars($k['kategori']) ?></span>
                            <small class="badge <?= $active ? 'bg-white text-pink' : 'bg-secondary' ?>"><?= $k['jml'] ?></small>
                        </a>
                        <?php endwhile; ?>
                    </div>
                </div>

                <!-- Quick WA Box -->
                <div class="bb-form-card mt-4 text-center" style="background:linear-gradient(135deg,#E8F5E9,#C8E6C9);">
                    <div class="fs-2 mb-2">💬</div>
                    <h6 class="fw-bold" style="color:#2E7D32;">Butuh Bantuan?</h6>
                    <p class="small text-muted mb-3">Chat langsung dengan kami melalui WhatsApp!</p>
                    <a href="https://wa.me/6281234567890?text=Halo+Blissfulberry!+Saya+butuh+bantuan+memilih+menu." 
                       target="_blank" class="btn bb-btn-wa btn-sm w-100">
                        <i class="bi bi-whatsapp me-2"></i>Chat Sekarang
                    </a>
                </div>
            </div>

            <!-- ===== PRODUK GRID ===== -->
            <div class="col-lg-9">
                <!-- Info bar -->
                <div class="d-flex align-items-center justify-content-between mb-4 flex-wrap gap-2">
                    <p class="mb-0 text-muted small">
                        Menampilkan <strong><?= $produk_res->num_rows ?></strong> dari <strong><?= $total_rows ?></strong> produk
                        <?= $kategori_filter ? " — kategori <strong>".htmlspecialchars($kategori_filter)."</strong>" : "" ?>
                        <?= $search ? " — pencarian \"<strong>".htmlspecialchars($search)."</strong>\"" : "" ?>
                    </p>
                </div>

                <?php if ($produk_res->num_rows === 0): ?>
                <div id="emptyState" class="bb-empty-state">
                    <i class="bi bi-search"></i>
                    <h5>Produk tidak ditemukan</h5>
                    <p>Coba ubah kata kunci atau pilih kategori lain.</p>
                    <a href="katalog.php" class="btn bb-btn-primary mt-2">Lihat Semua Produk</a>
                </div>
                <?php else: ?>

                <div class="row g-4" id="productGrid">
                    <?php while ($p = $produk_res->fetch_assoc()):
                        $foto = !empty($p['foto']) ? $p['foto'] : 'assets/images/menu-full-blissfulberry.jpg';
                    ?>
                    <div class="col-sm-6 col-xl-4">
                        <div class="bb-card bb-product-card"
                             data-nama="<?= htmlspecialchars(strtolower($p['nama_produk'])) ?>"
                             data-kategori="<?= htmlspecialchars(strtolower($p['kategori'])) ?>"
                             data-harga="<?= $p['harga'] ?>">
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
                    <?php endwhile; ?>
                </div>
                <div id="emptyState" style="display:none;" class="bb-empty-state mt-4">
                    <i class="bi bi-search"></i>
                    <p>Tidak ada produk yang cocok dengan pencarian.</p>
                </div>

                <!-- PAGINATION -->
                <?php if ($total_pages > 1): ?>
                <nav class="mt-5">
                    <ul class="pagination bb-pagination justify-content-center flex-wrap">
                        <?php if ($page > 1): ?>
                        <li class="page-item">
                            <a class="page-link" href="?page=<?= $page-1 ?>&kategori=<?= urlencode($kategori_filter) ?>&q=<?= urlencode($search) ?>">
                                <i class="bi bi-chevron-left"></i>
                            </a>
                        </li>
                        <?php endif; ?>
                        <?php for ($i = max(1,$page-2); $i <= min($total_pages,$page+2); $i++): ?>
                        <li class="page-item <?= $i === $page ? 'active' : '' ?>">
                            <a class="page-link" href="?page=<?= $i ?>&kategori=<?= urlencode($kategori_filter) ?>&q=<?= urlencode($search) ?>"><?= $i ?></a>
                        </li>
                        <?php endfor; ?>
                        <?php if ($page < $total_pages): ?>
                        <li class="page-item">
                            <a class="page-link" href="?page=<?= $page+1 ?>&kategori=<?= urlencode($kategori_filter) ?>&q=<?= urlencode($search) ?>">
                                <i class="bi bi-chevron-right"></i>
                            </a>
                        </li>
                        <?php endif; ?>
                    </ul>
                </nav>
                <?php endif; ?>

                <?php endif; ?>
            </div>
        </div>
    </div>
</section>

<!-- WA Float -->
<a href="https://wa.me/6281234567890" target="_blank" class="bb-wa-float">
    <i class="bi bi-whatsapp"></i>
    <span class="bb-wa-float-tooltip">Chat WhatsApp</span>
</a>

<?php require_once 'includes/footer.php'; ?>
