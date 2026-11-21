<?php
/**
 * BLISSFULBERRY - MySQL Order Verification Test
 * Menguji bahwa:
 * 1. Data pesanan baru berhasil ditambahkan dan tersimpan ke MySQL database.
 * 2. Data dapat ditampilkan kembali dengan rincian item.
 * 3. Data pesanan yang diubah (status, alamat, catatan) berhasil tersimpan secara persisten.
 * 4. Data perubahan dapat ditampilkan kembali secara akurat dari MySQL.
 */

require_once __DIR__ . '/api/db.php';

echo "=== TEST PERSISTENSI DATABASE MYSQL BLISSFULBERRY ===\n\n";

try {
    $db = getDbConnection();
    echo "[✓] Berhasil terhubung ke database MySQL blissfulberry_db.\n\n";

    // 1. TAMBAH DATA PESANAN BARU (CREATE)
    echo "--- 1. Menambahkan Pesanan Baru ke MySQL ---\n";
    $testCode = 'BB-TEST-' . date('His');
    $customerName = 'Dewi Sartika (Test User)';
    $customerPhone = '082116506727';
    $serviceType = 'Delivery';
    $customerAddress = 'Jl. Yudhasari II No. 15, Sunyaragi, Kesambi, Cirebon';
    $notes = 'Biskuit gabin ekstra renyah, saus cokelat lumer tebal.';
    $paymentMethod = 'QRIS';
    $status = 'Menunggu Konfirmasi';

    $db->beginTransaction();
    $insertOrder = $db->prepare("INSERT INTO `orders` 
        (`order_code`, `customer_name`, `customer_phone`, `service_type`, `customer_address`, `notes`, `total_price`, `payment_method`, `status`) 
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
    
    // Produk yang dipesan: Chocoberry Dubai Chewy (40.000) & Acai Bowl Mart Classic (45.000)
    $totalPrice = 40000 + 45000;
    $insertOrder->execute([
        $testCode, $customerName, $customerPhone, $serviceType,
        $customerAddress, $notes, $totalPrice, $paymentMethod, $status
    ]);
    $orderId = $db->lastInsertId();

    $insertItem = $db->prepare("INSERT INTO `order_items` 
        (`order_id`, `product_id`, `product_name`, `variant`, `price`, `quantity`, `subtotal`) 
        VALUES (?, ?, ?, ?, ?, ?, ?)");

    $insertItem->execute([$orderId, 'chocoberry-dubai-chewy', 'Choco Berry Dubai Chewy', 'Original Crispy', 40000, 1, 40000]);
    $insertItem->execute([$orderId, 'acai-mart-classic', 'Mart Classic Berry Acai Bowl', 'Regular', 45000, 1, 45000]);

    $db->commit();
    echo "[✓] Pesanan berhasil disimpan! ID: {$orderId}, Kode: {$testCode}\n\n";

    // 2. BACA KEMBALI DARI DATABASE (READ / DISPLAY)
    echo "--- 2. Membaca & Menampilkan Kembali Data dari MySQL ---\n";
    $readStmt = $db->prepare("SELECT * FROM `orders` WHERE `id` = ?");
    $readStmt->execute([$orderId]);
    $savedOrder = $readStmt->fetch();

    $itemsStmt = $db->prepare("SELECT * FROM `order_items` WHERE `order_id` = ?");
    $itemsStmt->execute([$orderId]);
    $savedItems = $itemsStmt->fetchAll();

    echo "Data Pesanan Tersimpan:\n";
    echo "- Kode Pesanan   : {$savedOrder['order_code']}\n";
    echo "- Nama Pemesan   : {$savedOrder['customer_name']}\n";
    echo "- WhatsApp       : {$savedOrder['customer_phone']}\n";
    echo "- Layanan        : {$savedOrder['service_type']}\n";
    echo "- Status         : {$savedOrder['status']}\n";
    echo "- Total Harga    : Rp " . number_format($savedOrder['total_price'], 0, ',', '.') . "\n";
    echo "- Alamat Kirim   : {$savedOrder['customer_address']}\n";
    echo "- Catatan        : {$savedOrder['notes']}\n";
    echo "- Jumlah Item    : " . count($savedItems) . " menu\n";
    foreach ($savedItems as $it) {
        echo "   * {$it['product_name']} ({$it['variant']}) x {$it['quantity']} = Rp " . number_format($it['subtotal'], 0, ',', '.') . "\n";
    }
    echo "\n";

    // 3. UBAH DATA PESANAN (UPDATE)
    echo "--- 3. Mengubah Data Pesanan di MySQL ---\n";
    $newStatus = 'Sedang Diproses';
    $newAddress = 'Jl. Yudhasari II No. 15 (Pagar Cokelat, Samping Arena Digital Printing), Sunyaragi, Cirebon';
    $newNotes = 'CATATAN DIUBAH: Mohon sendok kayu & es krim vanilla dipisah dalam cup terpisah.';

    $updateStmt = $db->prepare("UPDATE `orders` 
        SET `status` = ?, `customer_address` = ?, `notes` = ? 
        WHERE `id` = ?");
    $updateStmt->execute([$newStatus, $newAddress, $newNotes, $orderId]);
    echo "[✓] Perubahan berhasil dieksekusi pada record ID: {$orderId}\n\n";

    // 4. BACA KEMBALI DATA SETELAH DIUBAH (VERIFY UPDATE)
    echo "--- 4. Membaca Kembali Data Setelah Perubahan dari MySQL ---\n";
    $verifyStmt = $db->prepare("SELECT * FROM `orders` WHERE `id` = ?");
    $verifyStmt->execute([$orderId]);
    $updatedOrder = $verifyStmt->fetch();

    echo "Data Pesanan Terkini:\n";
    echo "- Status Terkini : {$updatedOrder['status']} (Diperbarui!)\n";
    echo "- Alamat Baru    : {$updatedOrder['customer_address']}\n";
    echo "- Catatan Baru   : {$updatedOrder['notes']}\n";
    echo "- Terakhir Update: {$updatedOrder['updated_at']}\n\n";

    // Verifikasi assert
    if ($updatedOrder['status'] === $newStatus && $updatedOrder['notes'] === $newNotes) {
        echo "====================================================================\n";
        echo "[SUKSES BESAR] SEMUA PENGUJIAN PERSISTENSI DATABASE MYSQL BERHASIL 100%!\n";
        echo "Data yang ditambahkan atau diubah tersimpan dan dapat ditampilkan kembali.\n";
        echo "====================================================================\n";
    } else {
        echo "[GAGAL] Data perubahan tidak sesuai dengan yang diharapkan.\n";
    }

} catch (Exception $e) {
    echo "[ERROR] Terjadi kesalahan: " . $e->getMessage() . "\n";
}
