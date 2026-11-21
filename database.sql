-- ============================================================
--  BLISSFULBERRY - Sistem Informasi Katalog & Pemesanan UMKM
--  Database SQL Script
-- ============================================================

CREATE DATABASE IF NOT EXISTS blissfulberry CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE blissfulberry;

-- -------------------------------------------------------
-- Tabel produk
-- -------------------------------------------------------
CREATE TABLE IF NOT EXISTS produk (
    id           INT AUTO_INCREMENT PRIMARY KEY,
    kode_produk  VARCHAR(20)   NOT NULL UNIQUE,
    nama_produk  VARCHAR(150)  NOT NULL,
    kategori     VARCHAR(80)   NOT NULL,
    harga        DECIMAL(12,0) NOT NULL,
    deskripsi    TEXT,
    foto         VARCHAR(255)  DEFAULT NULL,
    wa_penjual   VARCHAR(20)   NOT NULL DEFAULT '6281234567890',
    created_at   TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at   TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- -------------------------------------------------------
-- Tabel pesanan
-- -------------------------------------------------------
CREATE TABLE IF NOT EXISTS pesanan (
    id             INT AUTO_INCREMENT PRIMARY KEY,
    kode_pesanan   VARCHAR(30) NOT NULL UNIQUE,
    nama_pemesan   VARCHAR(100) NOT NULL,
    wa_pemesan     VARCHAR(20)  NOT NULL,
    nama_produk    VARCHAR(150) NOT NULL,
    id_produk      INT          DEFAULT NULL,
    jumlah         INT          NOT NULL DEFAULT 1,
    harga_satuan   DECIMAL(12,0) NOT NULL DEFAULT 0,
    total_harga    DECIMAL(12,0) NOT NULL DEFAULT 0,
    catatan        TEXT,
    status         ENUM('Menunggu','Diproses','Selesai','Dibatalkan') DEFAULT 'Menunggu',
    created_at     TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at     TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (id_produk) REFERENCES produk(id) ON DELETE SET NULL
) ENGINE=InnoDB;

-- -------------------------------------------------------
-- Data produk awal (sesuai menu Blissfulberry)
-- -------------------------------------------------------
INSERT INTO produk (kode_produk, nama_produk, kategori, harga, deskripsi, foto, wa_penjual) VALUES

-- Banana Pudding
('BP001', 'Ubee Banana Bliss', 'Banana Pudding', 17000,
 'Puding pisang lembut dengan topping ube (talas ungu) yang creamy, perpaduan rasa manis pisang dan ube yang unik dan memanjakan lidah.',
 'assets/images/menu-banana-pudding.jpg', '6281234567890'),

('BP002', 'Cookies Banana Pudding', 'Banana Pudding', 17000,
 'Puding pisang dengan topping remahan cookies yang renyah, kombinasi sempurna antara kelembutan puding dan tekstur cookies yang gurih.',
 'assets/images/menu-banana-pudding.jpg', '6281234567890'),

('BP003', 'Matcha Strawberry Pudding', 'Banana Pudding', 17000,
 'Puding pisang dengan cita rasa matcha yang sedikit pahit berpadu dengan kesegaran strawberry, tampilan cantik dan rasa yang menggoda.',
 'assets/images/menu-banana-pudding.jpg', '6281234567890'),

-- Gabin Ice Cream
('GI001', 'Gabin Matcha', 'Gabin Ice Cream', 12000,
 'Es krim gabin rasa matcha premium, perpaduan wafer renyah dengan isian es krim matcha yang lembut dan segar.',
 'assets/images/menu-gabin-banana-pudding.jpg', '6281234567890'),

('GI002', 'Gabin Matcha Berry', 'Gabin Ice Cream', 12000,
 'Gabin es krim rasa matcha berry, kombinasi matcha dengan buah berry yang segar dan manis dalam balutan wafer renyah.',
 'assets/images/menu-gabin-banana-pudding.jpg', '6281234567890'),

('GI003', 'Gabin Violet Ubee', 'Gabin Ice Cream', 12000,
 'Gabin es krim rasa ube (talas ungu) dengan warna ungu cantik dan rasa yang khas, manis dan creamy.',
 'assets/images/menu-gabin-banana-pudding.jpg', '6281234567890'),

('GI004', 'Gabin Cookies and Cream', 'Gabin Ice Cream', 12000,
 'Gabin es krim dengan isian cookies and cream klasik, paduan sempurna antara wafer, krim, dan remahan oreo.',
 'assets/images/menu-gabin-banana-pudding.jpg', '6281234567890'),

('GI005', 'Gabin Strawberry Kiss', 'Gabin Ice Cream', 12000,
 'Gabin es krim rasa strawberry segar, manis dan asam yang menyegarkan dalam balutan wafer crispy.',
 'assets/images/menu-gabin-banana-pudding.jpg', '6281234567890'),

('GI006', 'Gabin Chocomilk', 'Gabin Ice Cream', 12000,
 'Gabin es krim rasa coklat susu yang creamy dan lembut, cocok untuk pecinta coklat.',
 'assets/images/menu-gabin-banana-pudding.jpg', '6281234567890'),

-- Gabin Ice Bar Scoopable
('GS001', 'Gabin Ice Bar Mini 120ml', 'Gabin Ice Bar', 11000,
 'Es krim gabin scoopable ukuran mini 120ml, bisa dinikmati langsung dengan sendok. Tersedia berbagai rasa pilihan.',
 'assets/images/menu-gabin-banana-pudding.jpg', '6281234567890'),

('GS002', 'Gabin Ice Bar Big 150ml', 'Gabin Ice Bar', 14000,
 'Es krim gabin scoopable ukuran besar 150ml, porsi lebih banyak untuk kepuasan penuh. Tersedia berbagai rasa.',
 'assets/images/menu-gabin-banana-pudding.jpg', '6281234567890'),

-- Acai Bowl
('AB001', 'Mart Classic Berry Acai Bowl', 'Acai Bowl', 45000,
 'Acai bowl klasik dengan base acai yang kaya antioksidan, dilengkapi topping buah berry segar, granola, dan pisang. Sehat dan lezat!',
 'assets/images/produk-acai-bowl.jpg', '6281234567890'),

('AB002', 'Jams Nutella Crunch Acai Bowl', 'Acai Bowl', 50000,
 'Acai bowl premium dengan topping selai strawberry, Nutella, granola crunch, dan berbagai buah segar. Kombinasi rasa yang luar biasa!',
 'assets/images/produk-acai-bowl.jpg', '6281234567890'),

('AB003', 'Juju Peanut Butter Acai Bowl', 'Acai Bowl', 50000,
 'Acai bowl dengan topping selai kacang, granola, pisang, dan berry segar. Tinggi protein dan sangat mengenyangkan.',
 'assets/images/produk-acai-bowl.jpg', '6281234567890'),

('AB004', 'Kean Special Berry Acai Bowl', 'Acai Bowl', 55000,
 'Acai bowl spesial dengan pilihan topping berry premium terlengkap — blueberry, raspberry, strawberry, blackberry plus granola.',
 'assets/images/produk-acai-bowl.jpg', '6281234567890'),

('AB005', 'Premium Cortis Special Acai Max', 'Acai Bowl', 65000,
 'Acai bowl premium terbaik dari Blissfulberry! Topping berlimpah dengan buah premium, superfood, granola special, dan saus spesial.',
 'assets/images/produk-acai-bowl.jpg', '6281234567890'),

-- Chocoberry
('CB001', 'Strawberry Choco Melt', 'Chocoberry', 20000,
 'Strawberry segar dibalut coklat leleh yang manis, kombinasi rasa asam manis strawberry dengan coklat yang sempurna.',
 'assets/images/menu-chocoberry-cone.jpg', '6281234567890'),

('CB002', 'Choco Banana Sweet Delight', 'Chocoberry', 20000,
 'Pisang manis dibalut coklat lezat, camilan sehat dan mengenyangkan yang disukai semua kalangan.',
 'assets/images/menu-chocoberry-cone.jpg', '6281234567890'),

('CB003', 'Choco Berry N Banana', 'Chocoberry', 20000,
 'Perpaduan berry segar dan pisang dibalut coklat premium, dua buah favorit dalam satu gigitan yang memanjakan.',
 'assets/images/menu-chocoberry-cone.jpg', '6281234567890'),

('CB004', 'Choco Berry N Grape', 'Chocoberry', 25000,
 'Berry dan anggur segar dibalut coklat melimpah, rasa buah yang kaya dan coklat yang creamy dalam satu paket.',
 'assets/images/menu-chocoberry-cone.jpg', '6281234567890'),

('CB005', 'Choco Grill N Banana', 'Chocoberry', 25000,
 'Pisang panggang dengan coklat, tekstur luar yang sedikit karamel dan dalam yang lembut dengan coklat yang meleleh.',
 'assets/images/menu-chocoberry-cone.jpg', '6281234567890'),

('CB006', 'Choco Berry Coal Chewy', 'Chocoberry', 40000,
 'Chocoberry premium dengan coal charcoal yang unik — tampilan hitam menawan dengan isian coklat berry yang chewy dan lezat.',
 'assets/images/menu-chocoberry-cone.jpg', '6281234567890'),

-- Mac N Cheese
('MN001', 'Small Mac N Cheese', 'Mac N Cheese', 10000,
 'Mac n cheese porsi kecil yang creamy dan gurih, cocok sebagai camilan sore. Pasta makaroni dengan saus keju yang melimpah.',
 'assets/images/produk-mac-cheese.jpg', '6281234567890'),

('MN002', 'Mac N Cheese French Fries', 'Mac N Cheese', 15000,
 'Mac n cheese lengkap dengan french fries crispy, paduan sempurna antara pasta keju creamy dan kentang goreng renyah.',
 'assets/images/produk-mac-cheese.jpg', '6281234567890'),

('MN003', 'Mac N Cheese Chicken Juliet', 'Mac N Cheese', 17000,
 'Mac n cheese dengan tambahan ayam juliet yang juicy, protein tinggi dengan cita rasa gurih yang menggugah selera.',
 'assets/images/produk-mac-cheese.jpg', '6281234567890'),

('MN004', 'Mac N Cheese French Fries Chicken', 'Mac N Cheese', 23000,
 'Paket lengkap mac n cheese, french fries crispy, dan ayam juliet — combo terlengkap untuk lapar besar!',
 'assets/images/produk-mac-cheese.jpg', '6281234567890'),

-- Chocoberry Ice Cream Cone
('IC001', 'Chocoberry Ice Cream Cone Original', 'Ice Cream Cone', 12000,
 'Cone waffle renyah dengan soft serve coklat dan topping chocoberry segar. Es krim cone original yang selalu memanjakan.',
 'assets/images/menu-chocoberry-cone.jpg', '6281234567890'),

('IC002', 'Chocoberry Ice Cream Cone Premium', 'Ice Cream Cone', 15000,
 'Cone premium dengan soft serve coklat berlimpah, topping strawberry segar, coklat leleh, dan whipped cream.',
 'assets/images/menu-chocoberry-cone.jpg', '6281234567890');
