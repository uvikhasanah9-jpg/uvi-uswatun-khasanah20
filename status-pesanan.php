<?php
/**
 * BLISSFULBERRY - Status & Bukti Pesanan (Ambil & Tampilkan Data dari MySQL)
 * Dilengkapi fitur ubah catatan / alamat pesanan secara persisten ke MySQL
 */

require_once __DIR__ . '/api/db.php';
$db = getDbConnection();

$code = $_GET['code'] ?? '';
$order = null;
$items = [];
$updateMessage = '';
$updateStatus = '';

// Proses jika ada form perubahan data (UPDATE ke MySQL)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'update_order') {
    $orderId = intval($_POST['order_id'] ?? 0);
    $newNotes = trim($_POST['notes'] ?? '');
    $newAddress = trim($_POST['customer_address'] ?? '');
    $newServiceType = trim($_POST['service_type'] ?? '');

    if ($orderId > 0) {
        try {
            $updateStmt = $db->prepare("UPDATE `orders` 
                SET `notes` = ?, `customer_address` = ?, `service_type` = ?, `updated_at` = CURRENT_TIMESTAMP 
                WHERE `id` = ?");
            $updateStmt->execute([$newNotes, $newAddress, $newServiceType, $orderId]);
            $updateMessage = "Data pesanan berhasil diubah dan tersimpan kembali di database MySQL!";
            $updateStatus = "success";
        } catch (Exception $e) {
            $updateMessage = "Gagal memperbarui data: " . $e->getMessage();
            $updateStatus = "error";
        }
    }
}

// Ambil data terbaru dari MySQL (READ / Tampilkan kembali)
if (!empty($code)) {
    $stmt = $db->prepare("SELECT * FROM `orders` WHERE `order_code` = ? LIMIT 1");
    $stmt->execute([$code]);
    $order = $stmt->fetch();

    if ($order) {
        $itemStmt = $db->prepare("SELECT * FROM `order_items` WHERE `order_id` = ?");
        $itemStmt->execute([$order['id']]);
        $items = $itemStmt->fetchAll();
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= $order ? 'Nota Pesanan #' . htmlspecialchars($order['order_code']) : 'Lacak Pesanan' ?> - Blissfulberry Cirebon</title>
  <link rel="icon" type="image/svg+xml" href="assets/images/favicon.svg">
  <link rel="manifest" href="manifest.json">
  <meta name="theme-color" content="#e11d48">

  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Fredoka:wght@500;600;700&family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
  <script src="https://cdn.tailwindcss.com"></script>
  <script>
    tailwind.config = {
      theme: {
        extend: {
          colors: {
            rose: {
              50: '#fff1f2', 100: '#ffe4e6', 200: '#fecdd3', 300: '#fda4af',
              400: '#fb7185', 500: '#f43f5e', 600: '#e11d48', 700: '#be123c',
              800: '#9f1239', 900: '#881337',
            }
          },
          fontFamily: {
            sans: ['"Plus Jakarta Sans"', 'system-ui', 'sans-serif'],
            accent: ['Fredoka', 'Quicksand', 'sans-serif'],
          }
        }
      }
    }
  </script>
  <link rel="stylesheet" href="assets/css/style.css">
</head>
<body class="bg-[#FFFBF7] text-slate-700 flex flex-col min-h-screen">

  <header id="mainNavbar" class="sticky top-0 z-40 navbar-glass">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
      <div class="flex items-center justify-between h-20">
        <a href="index.html" class="flex items-center gap-2">
          <img src="assets/images/logo.svg" alt="Logo Resmi Blissfulberry" class="h-11 w-auto" width="220" height="55">
        </a>
        <nav class="hidden md:flex items-center space-x-8">
          <a href="index.html" class="nav-link text-sm font-bold">Beranda</a>
          <a href="produk.html" class="nav-link text-sm font-bold">Menu &amp; Produk</a>
          <a href="pesan.php" class="nav-link text-sm font-bold">Form Pemesanan</a>
          <a href="admin-pesanan.php" class="nav-link text-sm font-bold text-rose-600">Kelola Pesanan (MySQL)</a>
          <a href="kontak.html" class="nav-link text-sm font-bold">Kontak &amp; Lokasi</a>
        </nav>
        <div class="flex items-center gap-3">
          <a href="admin-pesanan.php" class="text-xs font-bold py-2 px-3.5 rounded-full bg-rose-100 text-rose-700 hover:bg-rose-200 transition">
            📊 Database Pesanan
          </a>
        </div>
      </div>
    </div>
  </header>

  <main class="flex-grow py-10">
    <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">
      
      <?php if (!empty($updateMessage)): ?>
        <div class="mb-6 p-4 rounded-2xl text-sm font-medium border <?= $updateStatus === 'success' ? 'bg-emerald-50 text-emerald-800 border-emerald-200' : 'bg-rose-50 text-rose-800 border-rose-200' ?>">
          <div class="flex items-center gap-2">
            <span><?= $updateStatus === 'success' ? '✅' : '❌' ?></span>
            <span><?= htmlspecialchars($updateMessage) ?></span>
          </div>
        </div>
      <?php endif; ?>

      <?php if ($order): ?>
        <!-- KARTU NOTA PESANAN -->
        <div class="bg-white rounded-3xl p-6 sm:p-10 border border-rose-200 shadow-xl space-y-6">
          
          <!-- Header Nota -->
          <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-6 border-b border-rose-100">
            <div>
              <span class="text-xs font-extrabold uppercase tracking-widest text-rose-600">Bukti Pemesanan Resmi</span>
              <h1 class="text-2xl sm:text-3xl font-black text-gray-900 mt-1">
                #<?= htmlspecialchars($order['order_code']) ?>
              </h1>
              <p class="text-xs text-gray-400 mt-1">
                Waktu Transaksi: <?= date('d M Y, H:i', strtotime($order['created_at'])) ?> WIB
              </p>
              <?php if ($order['updated_at'] !== $order['created_at']): ?>
                <p class="text-[11px] text-emerald-600 font-semibold">
                  Terakhir Diperbarui: <?= date('d M Y, H:i', strtotime($order['updated_at'])) ?> WIB
                </p>
              <?php endif; ?>
            </div>

            <div class="text-left sm:text-right">
              <span class="text-xs text-gray-400 block font-medium">Status Pesanan di MySQL:</span>
              <?php
                $statusClass = 'bg-amber-100 text-amber-800 border-amber-200';
                if ($order['status'] === 'Sedang Diproses') $statusClass = 'bg-blue-100 text-blue-800 border-blue-200';
                if ($order['status'] === 'Siap Diambil / Diantar') $statusClass = 'bg-purple-100 text-purple-800 border-purple-200';
                if ($order['status'] === 'Selesai') $statusClass = 'bg-emerald-100 text-emerald-800 border-emerald-200';
                if ($order['status'] === 'Dibatalkan') $statusClass = 'bg-red-100 text-red-800 border-red-200';
              ?>
              <span class="inline-block mt-1 px-3 py-1 rounded-full text-xs font-bold border <?= $statusClass ?>">
                ● <?= htmlspecialchars($order['status']) ?>
              </span>
            </div>
          </div>

          <!-- Info Pemesan & Layanan -->
          <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 p-4 rounded-2xl bg-rose-50/50 border border-rose-100 text-xs">
            <div>
              <p class="text-gray-400 font-medium">Nama Pemesan:</p>
              <p class="text-sm font-bold text-gray-800"><?= htmlspecialchars($order['customer_name']) ?></p>
            </div>
            <div>
              <p class="text-gray-400 font-medium">WhatsApp / Telepon:</p>
              <p class="text-sm font-bold text-gray-800"><?= htmlspecialchars($order['customer_phone']) ?></p>
            </div>
            <div>
              <p class="text-gray-400 font-medium">Pilihan Layanan:</p>
              <p class="text-sm font-bold text-rose-600"><?= htmlspecialchars($order['service_type']) ?></p>
            </div>
            <div>
              <p class="text-gray-400 font-medium">Metode Pembayaran:</p>
              <p class="text-sm font-bold text-gray-800"><?= htmlspecialchars($order['payment_method']) ?></p>
            </div>
            <?php if (!empty($order['customer_address'])): ?>
              <div class="sm:col-span-2 pt-2 border-t border-rose-100">
                <p class="text-gray-400 font-medium">Alamat Pengantaran:</p>
                <p class="text-xs font-medium text-gray-800"><?= nl2br(htmlspecialchars($order['customer_address'])) ?></p>
              </div>
            <?php endif; ?>
            <?php if (!empty($order['notes'])): ?>
              <div class="sm:col-span-2 pt-2 border-t border-rose-100">
                <p class="text-gray-400 font-medium">Catatan Khusus:</p>
                <p class="text-xs italic text-gray-700">&ldquo;<?= htmlspecialchars($order['notes']) ?>&rdquo;</p>
              </div>
            <?php endif; ?>
          </div>

          <!-- Rincian Item Produk dari Database MySQL -->
          <div>
            <h3 class="text-sm font-bold text-gray-900 mb-3">Rincian Menu yang Dipesan:</h3>
            <div class="border border-rose-100 rounded-2xl overflow-hidden divide-y divide-rose-100">
              <?php foreach ($items as $item): ?>
                <div class="p-4 flex items-center justify-between text-xs bg-white hover:bg-rose-50/30 transition">
                  <div>
                    <h4 class="font-bold text-gray-900 text-sm"><?= htmlspecialchars($item['product_name']) ?></h4>
                    <p class="text-gray-500 mt-0.5">Varian: <span class="text-rose-600 font-semibold"><?= htmlspecialchars($item['variant'] ?: 'Original') ?></span></p>
                    <p class="text-gray-400 mt-0.5">Rp <?= number_format($item['price'], 0, ',', '.') ?> &times; <?= $item['quantity'] ?> porsi</p>
                  </div>
                  <div class="text-right">
                    <span class="font-black text-sm text-gray-900">Rp <?= number_format($item['subtotal'], 0, ',', '.') ?></span>
                  </div>
                </div>
              <?php endforeach; ?>
            </div>
          </div>

          <!-- Total Bayar -->
          <div class="p-4 rounded-2xl bg-gradient-to-r from-rose-50 to-pink-50 border border-rose-200 flex items-center justify-between">
            <span class="font-bold text-sm text-gray-700">Total Pembayaran:</span>
            <span class="font-black text-2xl text-rose-600">Rp <?= number_format($order['total_price'], 0, ',', '.') ?></span>
          </div>

          <!-- FORM UBAH DATA (PERSISTENSI MYSQL NYATA) -->
          <div class="pt-6 border-t border-rose-100">
            <div class="p-5 rounded-2xl bg-amber-50/40 border border-amber-200">
              <h3 class="text-sm font-bold text-amber-900 flex items-center gap-2 mb-2">
                <span>✏️</span>
                <span>Ingin Mengubah Catatan atau Alamat Pesanan?</span>
              </h3>
              <p class="text-xs text-amber-800 mb-4 leading-relaxed">
                Data yang Anda ubah di formulir ini akan <strong>langsung disimpan ke database MySQL</strong> dan halaman ini akan otomatis menampilkan kembali data yang telah diperbarui.
              </p>

              <form method="POST" action="status-pesanan.php?code=<?= urlencode($order['order_code']) ?>" class="space-y-3">
                <input type="hidden" name="action" value="update_order">
                <input type="hidden" name="order_id" value="<?= $order['id'] ?>">

                <div>
                  <label for="editService" class="block text-xs font-bold text-gray-700 mb-1">Ubah Layanan:</label>
                  <select id="editService" name="service_type" class="w-full text-xs p-2.5 rounded-xl border border-rose-200 bg-white">
                    <option value="Takeaway" <?= $order['service_type'] === 'Takeaway' ? 'selected' : '' ?>>Takeaway</option>
                    <option value="Dine-in" <?= $order['service_type'] === 'Dine-in' ? 'selected' : '' ?>>Dine-in</option>
                    <option value="Delivery" <?= $order['service_type'] === 'Delivery' ? 'selected' : '' ?>>Delivery</option>
                  </select>
                </div>

                <div>
                  <label for="editAddress" class="block text-xs font-bold text-gray-700 mb-1">Alamat Pengantaran:</label>
                  <input type="text" id="editAddress" name="customer_address" value="<?= htmlspecialchars($order['customer_address']) ?>" placeholder="Ubah alamat pengantaran..." class="w-full text-xs p-2.5 rounded-xl border border-rose-200 bg-white">
                </div>

                <div>
                  <label for="editNotes" class="block text-xs font-bold text-gray-700 mb-1">Catatan Pesanan:</label>
                  <input type="text" id="editNotes" name="notes" value="<?= htmlspecialchars($order['notes']) ?>" placeholder="Ubah catatan pesanan..." class="w-full text-xs p-2.5 rounded-xl border border-rose-200 bg-white">
                </div>

                <div class="pt-2">
                  <button type="submit" class="btn-primary text-xs !py-2.5 !px-5 flex items-center gap-1.5">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4" /></svg>
                    <span>Simpan Perubahan ke Database MySQL</span>
                  </button>
                </div>
              </form>
            </div>
          </div>

          <!-- Tombol Aksi Tambahan -->
          <div class="flex flex-col sm:flex-row items-center justify-between gap-3 pt-4 border-t border-rose-100">
            <a href="produk.html" class="w-full sm:w-auto py-3 px-5 rounded-full border border-gray-300 text-gray-700 font-bold text-xs text-center hover:bg-gray-100 transition">
              &larr; Pesan Menu Lain
            </a>

            <?php
              $waMessage = "Halo Admin Blissfulberry! ✨\nSaya telah membuat pesanan di website dengan detail:\n\n• Kode Pesanan: #" . $order['order_code'] . "\n• Nama: " . $order['customer_name'] . "\n• Total: Rp " . number_format($order['total_price'], 0, ',', '.') . "\n• Layanan: " . $order['service_type'] . "\n\nMohon dicek ya kak. Terima kasih!";
              $waUrl = "https://wa.me/6282116506727?text=" . urlencode($waMessage);
            ?>
            <a 
              href="<?= $waUrl ?>" 
              target="_blank" 
              rel="noopener noreferrer" 
              class="w-full sm:w-auto btn-primary btn-whatsapp text-xs !py-3 !px-6 flex items-center justify-center gap-2"
            >
              <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24"><path d="M12.031 6.172c-3.181 0-5.767 2.586-5.768 5.766-.001 1.298.38 2.27 1.019 3.287l-.582 2.128 2.182-.573c.978.58 1.911.928 3.145.929 3.178 0 5.767-2.587 5.768-5.766.001-3.187-2.575-5.77-5.764-5.771zm3.392 8.244c-.144.405-.837.774-1.17.824-.312.045-.694.066-1.134-.075-.285-.091-.652-.224-1.144-.437-2.083-.902-3.447-3.033-3.551-3.174-.105-.14-8.5-1.13-8.5-2.158 0-1.028.536-1.534.726-1.744.19-.21.415-.262.553-.262.138 0 .277.001.398.007.128.007.3.048.468.455.174.423.593 1.45.646 1.556.052.106.087.23.017.37-.07.14-.105.228-.21.351-.105.123-.221.275-.316.369-.105.105-.215.22-.093.43.123.21.547.902 1.173 1.46 1.05.935 1.936 1.226 2.213 1.364.277.139.44.116.604-.07.164-.187.702-.816.889-1.096.187-.28.374-.233.626-.14.252.093 1.597.753 1.872.89.275.137.458.205.525.32.067.115.067.669-.077 1.074z"/></svg>
              <span>Konfirmasi via WhatsApp Admin</span>
            </a>
          </div>

        </div>

      <?php else: ?>
        <!-- FORM PENCARIAN KODE PESANAN JIKA BELUM ADA PARAMETER -->
        <div class="bg-white rounded-3xl p-8 sm:p-12 border border-rose-200 shadow-xl text-center space-y-6">
          <div class="w-16 h-16 rounded-full bg-rose-100 text-rose-600 flex items-center justify-center text-2xl mx-auto">
            🔍
          </div>
          <div>
            <h1 class="text-2xl font-extrabold text-gray-900">Lacak &amp; Cek Nota Pesanan</h1>
            <p class="text-xs text-gray-500 mt-2">
              Masukkan kode pesanan Anda (contoh: <strong>BB-20261118-001</strong>) untuk melihat nota digital dan status pembuatan dessert Anda langsung dari database.
            </p>
          </div>

          <form method="GET" action="status-pesanan.php" class="max-w-md mx-auto flex gap-2">
            <input 
              type="text" 
              name="code" 
              required 
              placeholder="Masukkan Kode (cth: BB-20261118-001)" 
              class="flex-1 text-sm p-3 rounded-xl border border-rose-200 focus:outline-none focus:ring-2 focus:ring-rose-500 font-bold uppercase"
            >
            <button type="submit" class="btn-primary text-xs !py-3 !px-5">
              Cek Status
            </button>
          </form>

          <div class="pt-4 border-t border-rose-100">
            <a href="pesan.php" class="text-xs text-rose-600 font-bold hover:underline">
              Belum pesan? Buka Form Pemesanan Online &rarr;
            </a>
          </div>
        </div>
      <?php endif; ?>

    </div>
  </main>

  <footer class="bg-gray-900 text-gray-300 py-8 border-t border-gray-800 text-center text-xs">
    <p>&copy; 2026 <strong>Blissfulberry Artisanal Dessert Cirebon</strong>. Data tersimpan di MySQL Database.</p>
  </footer>
</body>
</html>
