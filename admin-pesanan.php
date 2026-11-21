<?php
// ============================================================
//  BLISSFULBERRY – Admin Data Pesanan (admin-pesanan.php)
// ============================================================
require_once 'config/db.php';
$page_title = 'Data Pesanan';

$msg_ok = $msg_err = '';

// ---- HAPUS PESANAN ----
if (isset($_GET['hapus'])) {
    $hid = intval($_GET['hapus']);
    $msg_ok = $conn->query("DELETE FROM pesanan WHERE id=$hid")
        ? 'Pesanan berhasil dihapus.' : 'Gagal menghapus.';
}

// ---- UPDATE STATUS ----
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_status'])) {
    $uid     = intval($_POST['id']);
    $status  = bersihkan($_POST['status']);
    $allowed = ['Menunggu','Diproses','Selesai','Dibatalkan'];
    if (in_array($status, $allowed)) {
        $st = $conn->prepare("UPDATE pesanan SET status=? WHERE id=?");
        $st->bind_param('si', $status, $uid);
        $msg_ok = $st->execute() ? 'Status pesanan diperbarui.' : 'Gagal memperbarui status.';
    }
}

// Filter
$filter_status = bersihkan($_GET['status'] ?? '');
$search_q      = bersihkan($_GET['q'] ?? '');

$where = [];
$params = []; $types = '';
if ($filter_status) { $where[]='status=?';    $params[]=$filter_status; $types.='s'; }
if ($search_q)      { $where[]='(nama_pemesan LIKE ? OR kode_pesanan LIKE ? OR nama_produk LIKE ?)';
                      $params[]="%$search_q%"; $params[]="%$search_q%"; $params[]="%$search_q%"; $types.='sss'; }

$sql_w = $where ? 'WHERE '.implode(' AND ',$where) : '';

// Statistik
$stat = $conn->query("SELECT status, COUNT(*) as n FROM pesanan GROUP BY status")->fetch_all(MYSQLI_ASSOC);
$stat_map = array_column($stat, 'n', 'status');
$total_all = array_sum(array_column($stat, 'n'));

// Fetch
$sql = "SELECT p.*, pr.nama_produk as nama_p FROM pesanan p LEFT JOIN produk pr ON p.id_produk=pr.id $sql_w ORDER BY p.created_at DESC";
$stmt = $conn->prepare($sql);
if ($params) $stmt->bind_param($types, ...$params);
$stmt->execute();
$pesanan_list = $stmt->get_result();

require_once 'includes/header.php';
?>

<!-- Page Header -->
<section class="bb-page-header">
    <div class="container d-flex align-items-center justify-content-between flex-wrap gap-3">
        <div>
            <h1><i class="bi bi-bag-check me-2"></i>Data Pesanan</h1>
            <p>Kelola dan pantau semua pesanan masuk</p>
        </div>
        <a href="admin-produk.php" class="btn btn-light fw-semibold">
            <i class="bi bi-box-seam me-1"></i>Data Produk
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

        <!-- Statistik -->
        <div class="row g-3 mb-5">
            <div class="col-6 col-md-3">
                <div class="bb-stat-card text-center">
                    <div class="bb-stat-icon orange mx-auto"><i class="bi bi-clock"></i></div>
                    <div class="bb-stat-num"><?= $stat_map['Menunggu'] ?? 0 ?></div>
                    <div class="bb-stat-label">Menunggu</div>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="bb-stat-card text-center">
                    <div class="bb-stat-icon purple mx-auto"><i class="bi bi-arrow-repeat"></i></div>
                    <div class="bb-stat-num"><?= $stat_map['Diproses'] ?? 0 ?></div>
                    <div class="bb-stat-label">Diproses</div>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="bb-stat-card text-center">
                    <div class="bb-stat-icon green mx-auto"><i class="bi bi-check-circle"></i></div>
                    <div class="bb-stat-num"><?= $stat_map['Selesai'] ?? 0 ?></div>
                    <div class="bb-stat-label">Selesai</div>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="bb-stat-card text-center">
                    <div class="bb-stat-icon pink mx-auto"><i class="bi bi-bag-check"></i></div>
                    <div class="bb-stat-num"><?= $total_all ?></div>
                    <div class="bb-stat-label">Total Pesanan</div>
                </div>
            </div>
        </div>

        <!-- Filter & Search -->
        <form method="GET" class="d-flex flex-wrap gap-3 mb-4 align-items-center">
            <div class="bb-search-wrap" style="min-width:240px;flex:1;">
                <i class="bi bi-search"></i>
                <input type="text" name="q" class="form-control bb-search-input"
                       placeholder="Cari nama, kode, atau produk..."
                       value="<?= htmlspecialchars($search_q) ?>">
            </div>
            <select name="status" class="form-select bb-form-control" style="max-width:180px;">
                <option value="">Semua Status</option>
                <?php foreach (['Menunggu','Diproses','Selesai','Dibatalkan'] as $s): ?>
                <option value="<?= $s ?>" <?= $filter_status===$s?'selected':'' ?>><?= $s ?></option>
                <?php endforeach; ?>
            </select>
            <button type="submit" class="btn bb-btn-primary">Filter</button>
            <?php if ($search_q || $filter_status): ?>
            <a href="admin-pesanan.php" class="btn bb-btn-outline">Reset</a>
            <?php endif; ?>
        </form>

        <!-- Tabel Pesanan -->
        <div class="bb-table-wrap">
            <div class="p-3 d-flex align-items-center justify-content-between flex-wrap gap-2"
                 style="background:linear-gradient(135deg,var(--dark-berry),#8B1A5A);">
                <h6 class="text-white fw-bold mb-0">
                    <i class="bi bi-table me-2"></i>Daftar Pesanan
                    <span class="badge bg-white text-pink ms-2"><?= $pesanan_list->num_rows ?></span>
                </h6>
            </div>

            <?php if ($pesanan_list->num_rows === 0): ?>
            <div class="bb-empty-state">
                <i class="bi bi-bag-x"></i>
                <h5>Belum ada pesanan</h5>
                <p>Pesanan akan muncul di sini setelah pelanggan melakukan pemesanan.</p>
                <a href="katalog.php" class="btn bb-btn-primary mt-2">Lihat Katalog</a>
            </div>
            <?php else: ?>
            <div class="table-responsive">
                <table class="table bb-table">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Kode Pesanan</th>
                            <th>Nama Pemesan</th>
                            <th>WhatsApp</th>
                            <th>Produk</th>
                            <th>Jml</th>
                            <th>Total</th>
                            <th>Status</th>
                            <th>Waktu</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $no=1; while ($o = $pesanan_list->fetch_assoc()):
                            $wa_konfirm = "https://wa.me/{$o['wa_pemesan']}?text=" . urlencode(
                                "Halo {$o['nama_pemesan']}! 👋\n".
                                "Ini adalah konfirmasi pesanan Anda dari Blissfulberry:\n\n".
                                "🧾 No. Pesanan : {$o['kode_pesanan']}\n".
                                "🛍️ Produk     : {$o['nama_produk']}\n".
                                "🔢 Jumlah     : {$o['jumlah']} porsi\n".
                                "💰 Total      : " . rupiah($o['total_harga']) . "\n".
                                "📋 Status     : {$o['status']}\n\n".
                                "Terima kasih telah memesan di Blissfulberry! 🍓"
                            );
                        ?>
                        <tr>
                            <td><?= $no++ ?></td>
                            <td>
                                <code style="font-size:0.78rem;color:var(--pink);"><?= htmlspecialchars($o['kode_pesanan']) ?></code>
                            </td>
                            <td>
                                <strong><?= htmlspecialchars($o['nama_pemesan']) ?></strong>
                                <?php if ($o['catatan']): ?>
                                <div class="text-muted" style="font-size:0.72rem;" title="<?= htmlspecialchars($o['catatan']) ?>">
                                    <i class="bi bi-chat-text me-1"></i><?= htmlspecialchars(substr($o['catatan'],0,25)) ?>…
                                </div>
                                <?php endif; ?>
                            </td>
                            <td>
                                <a href="https://wa.me/<?= $o['wa_pemesan'] ?>" target="_blank"
                                   class="text-success fw-semibold text-decoration-none" style="font-size:0.82rem;">
                                    <i class="bi bi-whatsapp me-1"></i><?= htmlspecialchars($o['wa_pemesan']) ?>
                                </a>
                            </td>
                            <td style="max-width:140px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">
                                <?= htmlspecialchars($o['nama_produk']) ?>
                            </td>
                            <td class="text-center"><?= $o['jumlah'] ?></td>
                            <td class="text-pink fw-semibold"><?= rupiah($o['total_harga']) ?></td>
                            <td>
                                <!-- Inline status update -->
                                <form method="POST" class="d-inline">
                                    <input type="hidden" name="id" value="<?= $o['id'] ?>">
                                    <input type="hidden" name="update_status" value="1">
                                    <select name="status" class="form-select form-select-sm form-status-select"
                                            onchange="this.form.submit()"
                                            style="border-radius:20px;border:1.5px solid var(--pink-pale);font-size:0.78rem;min-width:110px;">
                                        <?php foreach (['Menunggu','Diproses','Selesai','Dibatalkan'] as $s): ?>
                                        <option value="<?= $s ?>" <?= $o['status']===$s?'selected':'' ?>><?= $s ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </form>
                            </td>
                            <td style="font-size:0.76rem;color:var(--text-muted);">
                                <?= date('d/m/Y H:i', strtotime($o['created_at'])) ?>
                            </td>
                            <td>
                                <div class="d-flex flex-column gap-1">
                                    <a href="<?= $wa_konfirm ?>" target="_blank"
                                       class="btn bb-btn-wa btn-sm" title="Hubungi via WA">
                                        <i class="bi bi-whatsapp"></i>
                                    </a>
                                    <a href="admin-pesanan.php?hapus=<?= $o['id'] ?>"
                                       class="btn bb-btn-danger btn-sm"
                                       onclick="return konfirmasiHapus('pesanan <?= addslashes($o['kode_pesanan']) ?>')">
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
</section>

<a href="https://wa.me/6281234567890" target="_blank" class="bb-wa-float">
    <i class="bi bi-whatsapp"></i>
</a>

<?php require_once 'includes/footer.php'; ?>
