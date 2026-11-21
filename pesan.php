<?php
/**
 * BLISSFULBERRY - Form Pemesanan Sederhana
 * Terhubung langsung ke Database MySQL & WhatsApp Admin
 */

require_once __DIR__ . '/api/db.php';
$db = getDbConnection();

// Ambil produk terpilih jika ada parameter ?product=
$selectedProductId = $_GET['product'] ?? '';

// Ambil seluruh daftar produk aktif dari database MySQL
$productStmt = $db->query("SELECT * FROM `products` ORDER BY `category` ASC, `price` ASC");
$products = $productStmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Form Pemesanan Menu - Blissfulberry Artisanal Dessert Cirebon</title>
  <meta name="description" content="Formulir pemesanan online resmi Blissfulberry Cirebon. Pesan Chocoberry, Acai Bowl, Gabin Ice Cream, Banana Pudding, & Mac N Cheese praktis dan tersimpan otomatis ke database.">
  <link rel="canonical" href="https://blissfulberry.my.id/pesan.php">

  <!-- Favicon & PWA -->
  <link rel="icon" type="image/svg+xml" href="assets/images/favicon.svg">
  <link rel="manifest" href="manifest.json">
  <meta name="theme-color" content="#e11d48">

  <!-- Google Fonts & Tailwind -->
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

  <!-- Top Announcement -->
  <aside class="bg-gradient-to-r from-rose-600 to-pink-500 text-white text-xs py-2 px-4 text-center font-medium shadow-sm">
    <span>🍓 Pesanan langsung tersimpan di sistem kasir Blissfulberry &amp; terhubung ke WhatsApp Admin!</span>
  </aside>

  <!-- Navbar -->
  <header id="mainNavbar" class="sticky top-0 z-40 navbar-glass">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
      <div class="flex items-center justify-between h-20">
        <a href="index.html" class="flex items-center gap-2" aria-label="Beranda Blissfulberry">
          <img src="assets/images/logo.svg" alt="Logo Resmi Blissfulberry" class="h-11 w-auto" width="220" height="55">
        </a>
        <nav class="hidden md:flex items-center space-x-8">
          <a href="index.html" class="nav-link text-sm font-bold">Beranda</a>
          <a href="produk.html" class="nav-link text-sm font-bold">Menu &amp; Produk</a>
          <a href="pesan.php" class="nav-link active text-sm font-bold">Form Pemesanan</a>
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

  <!-- Main Content -->
  <main class="flex-grow py-10">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
      
      <!-- Breadcrumb -->
      <nav class="flex items-center gap-2 text-xs text-gray-500 mb-6">
        <a href="index.html" class="hover:text-rose-600">Beranda</a>
        <span>&rsaquo;</span>
        <a href="produk.html" class="hover:text-rose-600">Menu &amp; Katalog</a>
        <span>&rsaquo;</span>
        <span class="text-rose-600 font-bold">Form Pemesanan Online</span>
      </nav>

      <div class="bg-white rounded-3xl p-6 sm:p-10 border border-rose-200 shadow-xl">
        
        <!-- Header Form -->
        <div class="text-center max-w-xl mx-auto mb-8">
          <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-rose-50 text-rose-600 text-xs font-bold border border-rose-200 mb-3">
            🛒 Tersambung Langsung ke MySQL Database
          </span>
          <h1 class="text-2xl sm:text-3xl font-extrabold text-gray-900">
            Form Pemesanan Blissfulberry
          </h1>
          <p class="text-gray-500 text-sm mt-2">
            Pilih menu favoritmu, isi detail pemesan, dan sistem kami akan langsung menyimpan data pesanan Anda serta membuat nota digital otomatis.
          </p>
        </div>

        <!-- Alert Notifikasi -->
        <div id="orderAlert" class="hidden mb-6 p-4 rounded-2xl text-sm"></div>

        <!-- Form Pemesanan -->
        <form id="orderForm" class="space-y-6">
          
          <!-- BAGIAN 1: PILIHAN PRODUK -->
          <div class="p-5 rounded-2xl bg-rose-50/40 border border-rose-100 space-y-4">
            <h2 class="text-base font-bold text-gray-900 flex items-center gap-2">
              <span class="w-6 h-6 rounded-full bg-rose-600 text-white flex items-center justify-center text-xs">1</span>
              Pilih Menu &amp; Varian
            </h2>

            <div class="grid grid-cols-1 sm:grid-cols-12 gap-4">
              <!-- Menu Dropdown -->
              <div class="sm:col-span-6">
                <label for="productSelect" class="block text-xs font-bold text-gray-700 mb-1">
                  Menu Utama <span class="text-rose-600">*</span>
                </label>
                <select id="productSelect" required class="w-full text-sm p-3 rounded-xl border border-rose-200 bg-white font-medium text-gray-800 focus:ring-2 focus:ring-rose-500">
                  <option value="">-- Pilih Menu Dessert / Savory --</option>
                  <?php foreach ($products as $p): ?>
                    <option 
                      value="<?= htmlspecialchars($p['id']) ?>" 
                      data-name="<?= htmlspecialchars($p['name']) ?>" 
                      data-price="<?= $p['price'] ?>" 
                      data-variants='<?= htmlspecialchars($p['variants_json'] ?? '[]') ?>'
                      <?= ($selectedProductId === $p['id']) ? 'selected' : '' ?>
                    >
                      <?= htmlspecialchars($p['name']) ?> - Rp <?= number_format($p['price'], 0, ',', '.') ?> (<?= htmlspecialchars($p['category_name']) ?>)
                    </option>
                  <?php endforeach; ?>
                </select>
              </div>

              <!-- Varian Dropdown -->
              <div class="sm:col-span-4">
                <label for="variantSelect" class="block text-xs font-bold text-gray-700 mb-1">
                  Pilihan Varian / Rasa
                </label>
                <select id="variantSelect" class="w-full text-sm p-3 rounded-xl border border-rose-200 bg-white font-medium text-gray-800 focus:ring-2 focus:ring-rose-500">
                  <option value="Original">Original</option>
                </select>
              </div>

              <!-- Jumlah Stepper -->
              <div class="sm:col-span-2">
                <label for="qtyInput" class="block text-xs font-bold text-gray-700 mb-1">
                  Jumlah Porsi
                </label>
                <input type="number" id="qtyInput" min="1" max="50" value="1" required class="w-full text-sm p-3 rounded-xl border border-rose-200 bg-white font-bold text-center text-gray-800 focus:ring-2 focus:ring-rose-500">
              </div>
            </div>

            <!-- Preview Harga Menu yang Dipilih -->
            <div class="pt-3 border-t border-rose-100 flex items-center justify-between text-xs text-gray-600">
              <span>Subtotal Item Ini:</span>
              <span id="singleSubtotal" class="font-extrabold text-base text-rose-600">Rp 0</span>
            </div>
          </div>

          <!-- BAGIAN 2: DATA PEMESAN & METODE LAYANAN -->
          <div class="p-5 rounded-2xl bg-white border border-rose-100 space-y-4">
            <h2 class="text-base font-bold text-gray-900 flex items-center gap-2">
              <span class="w-6 h-6 rounded-full bg-rose-600 text-white flex items-center justify-center text-xs">2</span>
              Informasi Pemesan &amp; Pengambilan
            </h2>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
              <div>
                <label for="custName" class="block text-xs font-bold text-gray-700 mb-1">
                  Nama Pemesan <span class="text-rose-600">*</span>
                </label>
                <input type="text" id="custName" required placeholder="Contoh: Sarah Aurelia" class="w-full text-sm p-3 rounded-xl border border-rose-200 bg-white focus:ring-2 focus:ring-rose-500">
              </div>

              <div>
                <label for="custPhone" class="block text-xs font-bold text-gray-700 mb-1">
                  Nomor WhatsApp / HP Aktif <span class="text-rose-600">*</span>
                </label>
                <input type="tel" id="custPhone" required placeholder="Contoh: 0821xxxxxxxx" class="w-full text-sm p-3 rounded-xl border border-rose-200 bg-white focus:ring-2 focus:ring-rose-500">
              </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
              <div>
                <label for="serviceType" class="block text-xs font-bold text-gray-700 mb-1">
                  Pilihan Layanan <span class="text-rose-600">*</span>
                </label>
                <select id="serviceType" class="w-full text-sm p-3 rounded-xl border border-rose-200 bg-white font-medium focus:ring-2 focus:ring-rose-500">
                  <option value="Takeaway">Takeaway (Bungkus Bawa Pulang)</option>
                  <option value="Dine-in">Dine-in (Makan di Gerai Cirebon)</option>
                  <option value="Delivery">Delivery (Diantar ke Alamat)</option>
                </select>
              </div>

              <div>
                <label for="payMethod" class="block text-xs font-bold text-gray-700 mb-1">
                  Metode Pembayaran
                </label>
                <select id="payMethod" class="w-full text-sm p-3 rounded-xl border border-rose-200 bg-white font-medium focus:ring-2 focus:ring-rose-500">
                  <option value="QRIS">QRIS (Gopay / OVO / Dana / ShopeePay / BCA)</option>
                  <option value="Tunai di Gerai">Tunai di Gerai (Cash on Store)</option>
                  <option value="Transfer Bank">Transfer Bank Manual</option>
                </select>
              </div>
            </div>

            <!-- Alamat Pengantaran (Jika Delivery) -->
            <div id="addressGroup">
              <label for="custAddress" class="block text-xs font-bold text-gray-700 mb-1">
                Alamat Pengantaran Lengkap (Khusus Delivery)
              </label>
              <textarea id="custAddress" rows="2" placeholder="Nama jalan, nomor rumah, RT/RW, kelurahan, dan patokan (wilayah Kota Cirebon)..." class="w-full text-sm p-3 rounded-xl border border-rose-200 bg-white focus:ring-2 focus:ring-rose-500"></textarea>
            </div>

            <!-- Catatan Khusus -->
            <div>
              <label for="custNotes" class="block text-xs font-bold text-gray-700 mb-1">
                Catatan Khusus Pesanan (Opsional)
              </label>
              <input type="text" id="custNotes" placeholder="Contoh: Es dipisah, biskuit gabin ekstra renyah, saus cokelat lumer tebal" class="w-full text-sm p-3 rounded-xl border border-rose-200 bg-white focus:ring-2 focus:ring-rose-500">
            </div>
          </div>

          <!-- Total Ringkasan & Tombol Submit -->
          <div class="pt-4 border-t border-rose-100 flex flex-col sm:flex-row items-center justify-between gap-4">
            <div>
              <span class="text-xs text-gray-500 block">Total Pembayaran:</span>
              <span id="grandTotal" class="text-2xl sm:text-3xl font-black text-rose-600">Rp 0</span>
            </div>

            <div class="flex items-center gap-3 w-full sm:w-auto">
              <a href="produk.html" class="w-full sm:w-auto py-3.5 px-5 rounded-full border border-gray-300 text-gray-700 font-bold text-xs text-center hover:bg-gray-100 transition">
                &larr; Kembali ke Katalog
              </a>
              <button 
                type="submit" 
                id="submitOrderBtn" 
                class="w-full sm:w-auto btn-primary text-sm !py-3.5 !px-8 flex items-center justify-center gap-2"
              >
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg>
                <span>Simpan Pesanan ke Database</span>
              </button>
            </div>
          </div>

        </form>

      </div>
    </div>
  </main>

  <!-- Footer -->
  <footer class="bg-gray-900 text-gray-300 py-8 border-t border-gray-800 text-center text-xs">
    <p>&copy; 2026 <strong>Blissfulberry Artisanal Dessert Cirebon</strong>. Data pesanan terintegrasi dengan MySQL Database.</p>
  </footer>

  <!-- Script Logika Form -->
  <script>
    const productSelect = document.getElementById('productSelect');
    const variantSelect = document.getElementById('variantSelect');
    const qtyInput = document.getElementById('qtyInput');
    const singleSubtotal = document.getElementById('singleSubtotal');
    const grandTotal = document.getElementById('grandTotal');
    const orderForm = document.getElementById('orderForm');
    const orderAlert = document.getElementById('orderAlert');
    const submitBtn = document.getElementById('submitOrderBtn');

    function formatRupiah(num) {
      return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 }).format(num);
    }

    function updatePrice() {
      const selected = productSelect.options[productSelect.selectedIndex];
      if (!selected || !selected.value) {
        singleSubtotal.textContent = 'Rp 0';
        grandTotal.textContent = 'Rp 0';
        return;
      }

      const price = parseFloat(selected.getAttribute('data-price') || 0);
      const qty = parseInt(qtyInput.value || 1);
      const total = price * qty;

      singleSubtotal.textContent = formatRupiah(total);
      grandTotal.textContent = formatRupiah(total);
    }

    // Saat menu dipilih, render varian rasa
    productSelect.addEventListener('change', () => {
      const selected = productSelect.options[productSelect.selectedIndex];
      variantSelect.innerHTML = '';

      if (selected && selected.value) {
        try {
          const variants = JSON.parse(selected.getAttribute('data-variants') || '[]');
          if (variants.length > 0) {
            variants.forEach(v => {
              const opt = document.createElement('option');
              opt.value = v;
              opt.textContent = v;
              variantSelect.appendChild(opt);
            });
          } else {
            variantSelect.innerHTML = '<option value="Original">Original</option>';
          }
        } catch (e) {
          variantSelect.innerHTML = '<option value="Original">Original</option>';
        }
      } else {
        variantSelect.innerHTML = '<option value="Original">Original</option>';
      }

      updatePrice();
    });

    qtyInput.addEventListener('input', updatePrice);

    // Initial update
    if (productSelect.value) {
      productSelect.dispatchEvent(new Event('change'));
    }

    // Submit handler
    orderForm.addEventListener('submit', async (e) => {
      e.preventDefault();

      const selected = productSelect.options[productSelect.selectedIndex];
      if (!selected || !selected.value) {
        alert('Silakan pilih menu yang ingin dipesan.');
        return;
      }

      submitBtn.disabled = true;
      submitBtn.innerHTML = '<span>Menyimpan ke MySQL...</span>';

      const orderPayload = {
        customer_name: document.getElementById('custName').value.trim(),
        customer_phone: document.getElementById('custPhone').value.trim(),
        service_type: document.getElementById('serviceType').value,
        customer_address: document.getElementById('custAddress').value.trim(),
        notes: document.getElementById('custNotes').value.trim(),
        payment_method: document.getElementById('payMethod').value,
        items: [
          {
            id: selected.value,
            name: selected.getAttribute('data-name'),
            variant: variantSelect.value,
            price: parseFloat(selected.getAttribute('data-price')),
            quantity: parseInt(qtyInput.value)
          }
        ]
      };

      try {
        const response = await fetch('api/orders.php?action=create', {
          method: 'POST',
          headers: { 'Content-Type': 'application/json' },
          body: JSON.stringify(orderPayload)
        });

        const result = await response.json();

        if (result.status === 'success') {
          orderAlert.className = 'mb-6 p-4 rounded-2xl text-sm bg-emerald-50 text-emerald-800 border border-emerald-200 block';
          orderAlert.innerHTML = `
            <strong>Pesanan Berhasil Disimpan!</strong> Kode Pesanan: <strong>${result.order_code}</strong>.<br>
            Mengarahkan ke halaman nota digital...
          `;
          setTimeout(() => {
            window.location.href = `status-pesanan.php?code=${result.order_code}`;
          }, 1000);
        } else {
          throw new Error(result.message || 'Gagal menyimpan pesanan');
        }
      } catch (err) {
        orderAlert.className = 'mb-6 p-4 rounded-2xl text-sm bg-rose-50 text-rose-800 border border-rose-200 block';
        orderAlert.innerHTML = `<strong>Gagal:</strong> ${err.message}`;
        submitBtn.disabled = false;
        submitBtn.innerHTML = '<span>Coba Simpan Kembali</span>';
      }
    });
  </script>
</body>
</html>
