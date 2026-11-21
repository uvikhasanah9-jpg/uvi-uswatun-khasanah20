<?php
// ============================================================
//  BLISSFULBERRY – Admin CRUD Produk (admin-produk.php)
// ============================================================
require_once 'config/db.php';
$page_title = 'Data Produk';

$msg_ok  = '';
$msg_err = '';

// ---- HAPUS ----
if (isset($_GET['hapus'])) {
    $hid = intval($_GET['hapus']);
    // Hapus foto lama jika ada
    $r = $conn->query("SELECT foto FROM produk WHERE id=$hid")->fetch_assoc();
    if ($r && $r['foto'] && file_exists($r['foto'])) @unlink($r['foto']);
    if ($conn->query("DELETE FROM produk WHERE id=$hid")) {
        $msg_ok = 'Produk berhasil dihapus.';
    } else {
        $msg_err = 'Gagal menghapus produk.';
    }
}

// ---- TAMBAH / EDIT ----
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $pid          = intval($_POST['id'] ?? 0);
    $kode_produk  = bersihkan($_POST['kode_produk'] ?? '');
    $nama_produk  = bersihkan($_POST['nama_produk'] ?? '');
    $kategori     = bersihkan($_POST['kategori'] ?? '');
    $harga        = intval($_POST['harga'] ?? 0);
    $deskripsi    = bersihkan($_POST['deskripsi'] ?? '');
    $wa_penjual   = preg_replace('/\D/', '', $_POST['wa_penjual'] ?? '6281234567890');

    // Upload foto
    $foto_path = bersihkan($_POST['foto_lama'] ?? '');
    if (!empty($_FILES['foto']['name']) && $_FILES['foto']['error'] === 0) {
        $ext   = strtolower(pathinfo($_FILES['foto']['name'], PATHINFO_EXTENSION));
        $allowed = ['jpg','jpeg','png','webp','gif'];
        if (in_array($ext, $allowed) && $_FILES['foto']['size'] < 3 * 1024 * 1024) {
            $upload_dir = 'assets/images/produk/';
            if (!is_dir($upload_dir)) mkdir($upload_dir, 0755, true);
            $fname = 'produk_' . time() . '_' . rand(1000,9999) . '.' . $ext;
            if (move_uploaded_file($_FILES['foto']['tmp_name'], $upload_dir . $fname)) {
                if ($foto_path && file_exists($foto_path)) @unlink($foto_path);
                $foto_path = $upload_dir . $fname;
            }
        } else {
            $msg_err = 'Format foto tidak valid atau ukuran melebihi 3MB.';
        }
    }

    if (!$msg_err) {
        if ($pid) {
            // UPDATE
            $st = $conn->prepare("UPDATE produk SET kode_produk=?, nama_produk=?, kategori=?, harga=?, deskripsi=?, foto=?, wa_penjual=? WHERE id=?");
            $st->bind_param('sssissi', $kode_produk, $nama_produk, $kategori, $harga, $deskripsi, $foto_path, $wa_penjual, $pid);
            $msg_ok  = $st->execute() ? 'Data produk berhasil diperbarui.' : 'Gagal memperbarui: ' . $conn->error;
        } else {
            // INSERT
            $st = $conn->prepare("INSERT INTO produk (kode_produk, nama_produk, kategori, harga, deskripsi, foto, wa_penjual) VALUES (?,?,?,?,?,?,?)");
            $st->bind_param('sssisss', $kode_produk, $nama_produk, $kategori, $harga, $deskripsi, $foto_path, $wa_penjual);
            if ($st->execute()) {
                $msg_ok = 'Produk baru berhasil ditambahkan.';
            } else {
                $msg_err = 'Gagal menambahkan: ' . ($conn->errno === 1062 ? 'Kode produk sudah digunakan.' : $conn->error);
            }
        }
    }
}

// Data edit
$edit_data = null;
if (isset($_GET['edit'])) {
    $eid = intval($_GET['edit']);
    $edit_data = $conn->query("SELECT * FROM produk WHERE id=$eid")->fetch_assoc();
}

// Daftar produk
$search_p = bersihkan($_GET['q'] ?? '');
$sql_p = $search_p
    ? "SELECT * FROM produk WHERE nama_produk LIKE '%$search_p%' OR kategori LIKE '%$search_p%' OR kode_produk LIKE '%$search_p%' ORDER BY created_at DESC"
    : "SELECT * FROM produk ORDER BY created_at DESC";
$produk_list = $conn->query($sql_p);

// Kategori list untuk dropdown
$kategori_opts = ['Banana Pudding','Gabin Ice Cream','Gabin Ice Bar','Acai Bowl','Chocoberry','Mac N Cheese','Ice Cream Cone','Lainnya'];

require_once 'includes/header.php';
?>

<!-- Page Header -->
<section class="bb-page-header">
    <div class="container d-flex align-items-center justify-content-between flex-wrap gap-3">
        <div>
            <h1><i class="bi bi-box-seam me-2"></i>Data Produk</h1>
            <p>Kelola katalog produk Blissfulberry (CRUD)</p>
        </div>
        <a href="admin-pesanan.php" class="btn btn-light fw-semibold">
            <i class="bi bi-bag-check me-1"></i>Data Pesanan
        </a>
    </div>
</section>

<section class="py-5">
    <div class="container">

        <!-- Alerts -->
        <?php if ($msg_ok): ?>
        <div class="bb-alert bb-alert-success mb-4 bb-auto-dismiss">
            <i class="bi bi-check-circle"></i> <?= htmlspecialchars($msg_ok) ?>
        </div>
        <?php endif; ?>
        <?php if ($msg_err): ?>
        <div class="bb-alert bb-alert-danger mb-4 bb-auto-dismiss">
            <i class="bi bi-exclamation-triangle"></i> <?= htmlspecialchars($msg_err) ?>
        </div>
        <?php endif; ?>

        <div class="row g-4">
            <!-- ===== FORM TAMBAH / EDIT ===== -->
            <div class="col-lg-4">
                <div class="bb-form-card">
                    <h5 class="fw-700 mb-4" style="color:var(--dark-berry);">
                        <i class="bi bi-<?= $edit_data ? 'pencil' : 'plus-circle' ?> me-2 text-pink"></i>
                        <?= $edit_data ? 'Edit Produk' : 'Tambah Produk Baru' ?>
                    </h5>

                    <form id="formProduk" method="POST" enctype="multipart/form-data" novalidate>
                        <input type="hidden" name="id"       value="<?= $edit_data['id'] ?? '' ?>">
                        <input type="hidden" name="foto_lama" value="<?= htmlspecialchars($edit_data['foto'] ?? '') ?>">

                        <div class="mb-3">
                            <label for="kode_produk" class="bb-form-label">Kode Produk *</label>
                            <input type="text" id="kode_produk" name="kode_produk"
                                   class="form-control bb-form-control"
                                   placeholder="mis. BP001"
                                   value="<?= htmlspecialchars($edit_data['kode_produk'] ?? '') ?>"
                                   <?= $edit_data ? 'readonly' : '' ?> required>
                            <div class="invalid-feedback">Kode produk wajib diisi (min. 3 karakter).</div>
                        </div>

                        <div class="mb-3">
                            <label for="nama_produk" class="bb-form-label">Nama Produk *</label>
                            <input type="text" id="nama_produk" name="nama_produk"
                                   class="form-control bb-form-control"
                                   placeholder="Nama produk"
                                   value="<?= htmlspecialchars($edit_data['nama_produk'] ?? '') ?>" required>
                            <div class="invalid-feedback">Nama produk wajib diisi.</div>
                        </div>

                        <div class="mb-3">
                            <label for="kategori" class="bb-form-label">Kategori *</label>
                            <select id="kategori" name="kategori" class="form-select bb-form-control" required>
                                <option value="">-- Pilih Kategori --</option>
                                <?php foreach ($kategori_opts as $k): ?>
                                <option value="<?= $k ?>" <?= (($edit_data['kategori'] ?? '') === $k) ? 'selected' : '' ?>><?= $k ?></option>
                                <?php endforeach; ?>
                            </select>
                            <div class="invalid-feedback">Pilih kategori.</div>
                        </div>

                        <div class="mb-3">
                            <label for="harga" class="bb-form-label">Harga (Rp) *</label>
                            <input type="number" id="harga" name="harga" min="0"
                                   class="form-control bb-form-control"
                                   placeholder="mis. 17000"
                                   value="<?= $edit_data['harga'] ?? '' ?>" required>
                            <div class="invalid-feedback">Harga harus berupa angka positif.</div>
                        </div>

                        <div class="mb-3">
                            <label class="bb-form-label">Deskripsi</label>
                            <textarea name="deskripsi" rows="3"
                                      class="form-control bb-form-control"
                                      placeholder="Deskripsi singkat produk..."><?= htmlspecialchars($edit_data['deskripsi'] ?? '') ?></textarea>
                        </div>

                        <div class="mb-3">
                            <label for="wa_penjual" class="bb-form-label">WA Penjual *</label>
                            <input type="text" id="wa_penjual" name="wa_penjual"
                                   class="form-control bb-form-control"
                                   placeholder="6281234567890"
                                   value="<?= htmlspecialchars($edit_data['wa_penjual'] ?? '6281234567890') ?>" required>
                            <div class="form-text" style="font-size:0.76rem;">Format: 62xxxxxxxxxx (kode negara, tanpa +)</div>
                            <div class="invalid-feedback">Nomor WA tidak valid.</div>
                        </div>

                        <div class="mb-4">
                            <label for="foto" class="bb-form-label">Foto Produk</label>
                            <?php if (!empty($edit_data['foto'])): ?>
                            <div class="mb-2">
                                <img id="fotoPreview" src="<?= htmlspecialchars($edit_data['foto']) ?>"
                                     onerror="this.style.display='none'"
                                     class="img-fluid rounded-3" style="max-height:120px;object-fit:cover;">
                            </div>
                            <?php else: ?>
                            <img id="fotoPreview" style="display:none;max-height:120px;object-fit:cover;border-radius:12px;" class="mb-2 img-fluid">
                            <?php endif; ?>
                            <input type="file" id="foto" name="foto"
                                   class="form-control bb-form-control"
                                   accept="image/*">
                            <div class="form-text" style="font-size:0.76rem;">Format: JPG/PNG/WebP, maks. 3MB</div>
                        </div>

                        <div class="d-flex gap-3">
                            <button type="submit" class="btn bb-btn-primary flex-fill">
                                <i class="bi bi-<?= $edit_data ? 'check-lg' : 'plus-lg' ?> me-1"></i>
                                <?= $edit_data ? 'Simpan Perubahan' : 'Tambah Produk' ?>
                            </button>
                            <?php if ($edit_data): ?>
                            <a href="admin-produk.php" class="btn bb-btn-outline">Batal</a>
                            <?php endif; ?>
                        </div>
                    </form>
                </div>
            </div>

            <!-- ===== TABEL PRODUK ===== -->
            <div class="col-lg-8">
                <!-- Search bar -->
                <form method="GET" class="d-flex gap-3 mb-4">
                    <div class="bb-search-wrap flex-grow-1">
                        <i class="bi bi-search"></i>
                        <input type="text" name="q" class="form-control bb-search-input"
                               placeholder="Cari kode, nama, atau kategori..."
                               value="<?= htmlspecialchars($search_p) ?>">
                    </div>
                    <button type="submit" class="btn bb-btn-primary">Cari</button>
                    <?php if ($search_p): ?>
                    <a href="admin-produk.php" class="btn bb-btn-outline">Reset</a>
                    <?php endif; ?>
                </form>

                <div class="bb-table-wrap">
                    <div class="p-3 d-flex align-items-center justify-content-between flex-wrap gap-2"
                         style="background:linear-gradient(135deg,var(--dark-berry),#8B1A5A);">
                        <h6 class="text-white fw-bold mb-0">
                            <i class="bi bi-table me-2"></i>Daftar Produk
                            <span class="badge bg-white text-pink ms-2"><?= $produk_list->num_rows ?></span>
                        </h6>
                        <a href="katalog.php" class="btn btn-light btn-sm fw-semibold">
                            <i class="bi bi-eye me-1"></i>Lihat Katalog
                        </a>
                    </div>
                    <?php if ($produk_list->num_rows === 0): ?>
                    <div class="bb-empty-state">
                        <i class="bi bi-box-seam"></i>
                        <p>Belum ada produk. Tambahkan produk pertama Anda!</p>
                    </div>
                    <?php else: ?>
                    <div class="table-responsive">
                        <table class="table bb-table">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Kode</th>
                                    <th>Nama Produk</th>
                                    <th>Kategori</th>
                                    <th>Harga</th>
                                    <th>Foto</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php $no=1; while ($p = $produk_list->fetch_assoc()): ?>
                                <tr>
                                    <td><?= $no++ ?></td>
                                    <td><code style="color:var(--pink);font-size:0.8rem;"><?= htmlspecialchars($p['kode_produk']) ?></code></td>
                                    <td>
                                        <strong><?= htmlspecialchars($p['nama_produk']) ?></strong>
                                        <?php if ($p['deskripsi']): ?>
                                        <div class="text-muted" style="font-size:0.75rem;max-width:200px;overflow:hidden;white-space:nowrap;text-overflow:ellipsis;">
                                            <?= htmlspecialchars(substr($p['deskripsi'],0,60)) ?>…
                                        </div>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <span class="bb-badge bb-badge-menunggu" style="font-size:0.68rem;">
                                            <?= htmlspecialchars($p['kategori']) ?>
                                        </span>
                                    </td>
                                    <td class="text-pink fw-semibold"><?= rupiah($p['harga']) ?></td>
                                    <td>
                                        <?php if ($p['foto']): ?>
                                        <img src="<?= htmlspecialchars($p['foto']) ?>"
                                             onerror="this.src='assets/images/favicon.svg'"
                                             alt="" style="width:40px;height:40px;object-fit:cover;border-radius:8px;border:2px solid var(--pink-pale);">
                                        <?php else: ?>
                                        <span class="text-muted" style="font-size:0.75rem;">–</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <div class="d-flex flex-column gap-1">
                                            <a href="detail.php?id=<?= $p['id'] ?>" class="btn bb-btn-outline btn-sm">
                                                <i class="bi bi-eye"></i>
                                            </a>
                                            <a href="admin-produk.php?edit=<?= $p['id'] ?>" class="btn bb-btn-edit btn-sm">
                                                <i class="bi bi-pencil"></i>
                                            </a>
                                            <a href="admin-produk.php?hapus=<?= $p['id'] ?>"
                                               class="btn bb-btn-danger btn-sm"
                                               onclick="return konfirmasiHapus('<?= addslashes($p['nama_produk']) ?>')">
                                                <i class="bi bi-trash"></i>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</section>

<a href="https://wa.me/6281234567890" target="_blank" class="bb-wa-float">
    <i class="bi bi-whatsapp"></i>
</a>

<?php require_once 'includes/footer.php'; ?>
