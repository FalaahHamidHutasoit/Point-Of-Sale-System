-- POS SIM upgrade: purchasing, stock ledger, payment method, historical cost
-- Jalankan sekali setelah database pos_ci4 versi sebelumnya sudah terpasang.

START TRANSACTION;

ALTER TABLE detail_penjualan
    ADD COLUMN harga_modal DECIMAL(15,2) NOT NULL DEFAULT 0.00 AFTER harga;

ALTER TABLE penjualan
    ADD COLUMN metode_pembayaran VARCHAR(30) NOT NULL DEFAULT 'Tunai' AFTER total;

CREATE TABLE pembelian (
    id_pembelian INT NOT NULL AUTO_INCREMENT,
    no_pembelian VARCHAR(30) NOT NULL,
    tanggal DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    id_supplier INT NOT NULL,
    id_user INT NOT NULL,
    total DECIMAL(15,2) NOT NULL DEFAULT 0.00,
    catatan VARCHAR(255) DEFAULT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id_pembelian),
    UNIQUE KEY uk_no_pembelian (no_pembelian),
    KEY idx_pembelian_supplier (id_supplier),
    KEY idx_pembelian_user (id_user),
    CONSTRAINT fk_pembelian_supplier FOREIGN KEY (id_supplier) REFERENCES supplier(id_supplier) ON UPDATE CASCADE,
    CONSTRAINT fk_pembelian_user FOREIGN KEY (id_user) REFERENCES users(id_user) ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE detail_pembelian (
    id_detail_pembelian INT NOT NULL AUTO_INCREMENT,
    id_pembelian INT NOT NULL,
    id_barang INT NOT NULL,
    qty INT NOT NULL,
    harga_beli DECIMAL(15,2) NOT NULL,
    subtotal DECIMAL(15,2) NOT NULL,
    PRIMARY KEY (id_detail_pembelian),
    KEY idx_detail_pembelian_header (id_pembelian),
    KEY idx_detail_pembelian_barang (id_barang),
    CONSTRAINT fk_detail_pembelian_header FOREIGN KEY (id_pembelian) REFERENCES pembelian(id_pembelian) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT fk_detail_pembelian_barang FOREIGN KEY (id_barang) REFERENCES barang(id_barang) ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE mutasi_stok (
    id_mutasi INT NOT NULL AUTO_INCREMENT,
    id_barang INT NOT NULL,
    id_user INT NOT NULL,
    tipe ENUM('MASUK','KELUAR','PENYESUAIAN') NOT NULL,
    qty INT NOT NULL,
    stok_sebelum INT NOT NULL,
    stok_sesudah INT NOT NULL,
    referensi_tipe VARCHAR(30) DEFAULT NULL,
    referensi_id INT DEFAULT NULL,
    keterangan VARCHAR(255) DEFAULT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id_mutasi),
    KEY idx_mutasi_barang (id_barang),
    KEY idx_mutasi_user (id_user),
    KEY idx_mutasi_created (created_at),
    CONSTRAINT fk_mutasi_barang FOREIGN KEY (id_barang) REFERENCES barang(id_barang) ON UPDATE CASCADE,
    CONSTRAINT fk_mutasi_user FOREIGN KEY (id_user) REFERENCES users(id_user) ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

COMMIT;
