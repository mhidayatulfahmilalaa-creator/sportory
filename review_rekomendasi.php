<?php
require_once 'config.php';

// Cek apakah user sudah login dan admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    redirect('dashboard.php');
}

$admin_id = $_SESSION['user_id'];

// Cek apakah ada parameter id
if (!isset($_GET['id'])) {
    redirect('daftar_rekomendasi.php');
}

$id = $_GET['id'];

// Ambil data rekomendasi
$query = "SELECT r.*, u.nama_lengkap as pengusul 
          FROM rekomendasi_alat r 
          JOIN users u ON r.user_id = u.id 
          WHERE r.id = ? AND r.status = 'Menunggu'";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows == 0) {
    $_SESSION['error'] = "Rekomendasi tidak ditemukan atau sudah diproses!";
    redirect('daftar_rekomendasi.php');
}

$rekomendasi = $result->fetch_assoc();

// Proses form ketika di-submit
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $status = $_POST['status'];
    $catatan_admin = $_POST['catatan_admin'];
    
    // Update status rekomendasi
    $query = "UPDATE rekomendasi_alat SET 
              status = ?, 
              catatan_admin = ?, 
              tanggal_disetujui = NOW(), 
              disetujui_oleh = ? 
              WHERE id = ?";
    
    $stmt = $conn->prepare($query);
    $stmt->bind_param("ssii", $status, $catatan_admin, $admin_id, $id);
    
    if ($stmt->execute()) {
        // Jika disetujui, tambahkan ke tabel alat_olahraga
        if ($status == 'Disetujui') {
            // Query INSERT yang diperbaiki - tanpa kolom 'kondisi'
            $query_insert = "INSERT INTO alat_olahraga 
                           (nama_alat, kategori, jumlah, jumlah_rusak, lokasi_penyimpanan, tanggal_masuk, keterangan) 
                           VALUES (?, ?, ?, 0, 'Gudang Utama', CURDATE(), ?)";
            
            $keterangan = "Pengadaan berdasarkan rekomendasi dari " . $rekomendasi['pengusul'];
            $stmt_insert = $conn->prepare($query_insert);
            $stmt_insert->bind_param("ssis", 
                $rekomendasi['nama_alat'], 
                $rekomendasi['kategori'], 
                $rekomendasi['jumlah_diminta'],
                $keterangan
            );
            
            if ($stmt_insert->execute()) {
                $_SESSION['success'] = "Rekomendasi berhasil disetujui dan alat telah ditambahkan ke inventori!";
            } else {
                $_SESSION['error'] = "Rekomendasi disetujui, tapi gagal menambahkan ke inventori: " . $stmt_insert->error;
            }
        } else {
            $_SESSION['success'] = "Rekomendasi berhasil ditolak!";
        }
        
        redirect('daftar_rekomendasi.php');
    } else {
        $error = "Gagal memproses rekomendasi: " . $stmt->error;
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Review Rekomendasi - Sportory</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        :root {
            --primary-blue: #2563eb;
            --primary-blue-dark: #1e40af;
            --accent-green: #10b981;
            --accent-green-dark: #059669;
            --accent-red: #ef4444;
            --accent-red-dark: #dc2626;
            --white: #ffffff;
            --gray-50: #f9fafb;
            --gray-100: #f3f4f6;
            --gray-200: #e5e7eb;
            --gray-500: #6b7280;
            --gray-600: #4b5563;
            --gray-700: #374151;
            --gray-800: #1f2937;
        }

        body {
            font-family: 'Poppins', sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 1.5rem;
        }

        .container {
            max-width: 1000px;
            margin: 0 auto;
        }

        .review-wrapper {
            background: var(--white);
            border-radius: 1rem;
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1);
            overflow: hidden;
            animation: slideUp 0.4s ease-out;
        }

        @keyframes slideUp {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .review-header {
            background: linear-gradient(135deg, var(--primary-blue) 0%, var(--primary-blue-dark) 100%);
            color: var(--white);
            padding: 2rem;
            position: relative;
            overflow: hidden;
        }

        .review-header::before {
            content: '';
            position: absolute;
            top: 0;
            right: 0;
            width: 200px;
            height: 200px;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 50%;
            transform: translate(30%, -30%);
        }

        .review-header-content {
            position: relative;
            z-index: 1;
        }

        .review-header h1 {
            font-size: 1.75rem;
            font-weight: 700;
            margin-bottom: 0.5rem;
        }

        .review-header p {
            font-size: 0.875rem;
            opacity: 0.9;
        }

        .back-link {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            color: var(--primary-blue);
            text-decoration: none;
            font-size: 0.875rem;
            font-weight: 500;
            margin-bottom: 1.5rem;
            transition: all 0.15s;
        }

        .back-link:hover {
            color: var(--primary-blue-dark);
            gap: 0.75rem;
        }

        .review-content {
            padding: 2rem;
        }

        .alert {
            padding: 1rem 1.25rem;
            border-radius: 0.5rem;
            margin-bottom: 1.5rem;
            display: flex;
            align-items: start;
            gap: 0.75rem;
            animation: slideDown 0.3s ease-out;
        }

        @keyframes slideDown {
            from { opacity: 0; transform: translateY(-10px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .alert-error {
            background: rgba(239, 68, 68, 0.1);
            color: #991b1b;
            border: 1px solid rgba(239, 68, 68, 0.3);
        }

        .info-box {
            background: linear-gradient(135deg, rgba(37, 99, 235, 0.05) 0%, rgba(37, 99, 235, 0.1) 100%);
            border-left: 4px solid var(--primary-blue);
            padding: 1.5rem;
            border-radius: 0.75rem;
            margin-bottom: 2rem;
        }

        .section-title {
            font-size: 1.125rem;
            font-weight: 600;
            color: var(--gray-800);
            margin-bottom: 1.5rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .detail-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 1.5rem;
            margin-bottom: 2rem;
        }

        .detail-item {
            display: flex;
            flex-direction: column;
            gap: 0.5rem;
        }

        .detail-item.full {
            grid-column: 1 / -1;
        }

        .detail-label {
            color: var(--gray-500);
            font-size: 0.75rem;
            text-transform: uppercase;
            font-weight: 600;
            letter-spacing: 0.05em;
        }

        .detail-value {
            color: var(--gray-800);
            font-size: 1rem;
            font-weight: 500;
        }

        .detail-value.highlight {
            color: var(--primary-blue);
            font-size: 1.5rem;
            font-weight: 700;
        }

        .priority-badge {
            display: inline-flex;
            align-items: center;
            gap: 0.25rem;
            padding: 0.5rem 1rem;
            border-radius: 50px;
            font-size: 0.875rem;
            font-weight: 600;
            width: fit-content;
        }

        .priority-badge.rendah {
            background: rgba(16, 185, 129, 0.1);
            color: #065f46;
            border: 1px solid rgba(16, 185, 129, 0.3);
        }

        .priority-badge.sedang {
            background: rgba(245, 158, 11, 0.1);
            color: #92400e;
            border: 1px solid rgba(245, 158, 11, 0.3);
        }

        .priority-badge.tinggi {
            background: rgba(239, 68, 68, 0.1);
            color: #991b1b;
            border: 1px solid rgba(239, 68, 68, 0.3);
        }

        .description-box {
            background: var(--white);
            padding: 1rem;
            border-radius: 0.5rem;
            border: 1px solid var(--gray-200);
            line-height: 1.6;
        }

        .decision-section {
            background: var(--gray-50);
            padding: 2rem;
            border-radius: 0.75rem;
            margin: 2rem 0;
        }

        .decision-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 1.5rem;
            margin-bottom: 2rem;
        }

        .decision-card {
            background: var(--white);
            border: 3px solid var(--gray-200);
            border-radius: 1rem;
            padding: 2rem;
            cursor: pointer;
            transition: all 0.25s;
            text-align: center;
            position: relative;
            overflow: hidden;
        }

        .decision-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: linear-gradient(135deg, transparent 0%, transparent 100%);
            opacity: 0;
            transition: all 0.25s;
        }

        .decision-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
        }

        .decision-card.approve {
            border-color: var(--accent-green);
        }

        .decision-card.approve:hover::before,
        .decision-card.approve.selected::before {
            background: linear-gradient(135deg, rgba(16, 185, 129, 0.1) 0%, rgba(16, 185, 129, 0.05) 100%);
            opacity: 1;
        }

        .decision-card.approve.selected {
            border-color: var(--accent-green);
            border-width: 4px;
            background: linear-gradient(135deg, var(--accent-green) 0%, var(--accent-green-dark) 100%);
            color: var(--white);
        }

        .decision-card.reject {
            border-color: var(--accent-red);
        }

        .decision-card.reject:hover::before,
        .decision-card.reject.selected::before {
            background: linear-gradient(135deg, rgba(239, 68, 68, 0.1) 0%, rgba(239, 68, 68, 0.05) 100%);
            opacity: 1;
        }

        .decision-card.reject.selected {
            border-color: var(--accent-red);
            border-width: 4px;
            background: linear-gradient(135deg, var(--accent-red) 0%, var(--accent-red-dark) 100%);
            color: var(--white);
        }

        .decision-icon {
            font-size: 4rem;
            margin-bottom: 1rem;
            position: relative;
            z-index: 1;
        }

        .decision-title {
            font-size: 1.25rem;
            font-weight: 700;
            margin-bottom: 0.5rem;
            position: relative;
            z-index: 1;
        }

        .decision-desc {
            font-size: 0.875rem;
            opacity: 0.8;
            position: relative;
            z-index: 1;
        }

        .form-group {
            margin-bottom: 1.5rem;
        }

        .form-label {
            display: block;
            margin-bottom: 0.5rem;
            color: var(--gray-700);
            font-weight: 500;
            font-size: 0.875rem;
        }

        .required {
            color: var(--accent-red);
            margin-left: 0.25rem;
        }

        .form-control {
            width: 100%;
            padding: 0.875rem 1rem;
            border: 2px solid var(--gray-200);
            border-radius: 0.5rem;
            font-size: 0.875rem;
            font-family: inherit;
            transition: all 0.15s;
            resize: vertical;
            min-height: 140px;
        }

        .form-control:focus {
            outline: none;
            border-color: var(--primary-blue);
            box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.1);
        }

        .form-control::placeholder {
            color: var(--gray-500);
        }

        .form-help {
            margin-top: 0.375rem;
            font-size: 0.75rem;
            color: var(--gray-500);
        }

        .btn-group {
            display: flex;
            gap: 0.75rem;
            margin-top: 2rem;
        }

        .btn {
            flex: 1;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            padding: 1rem 1.5rem;
            font-size: 0.875rem;
            font-weight: 600;
            border-radius: 0.5rem;
            border: none;
            cursor: pointer;
            transition: all 0.15s;
            text-decoration: none;
        }

        .btn-primary {
            background: var(--primary-blue);
            color: var(--white);
        }

        .btn-primary:hover:not(:disabled) {
            background: var(--primary-blue-dark);
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(37, 99, 235, 0.3);
        }

        .btn-primary:disabled {
            background: var(--gray-300);
            cursor: not-allowed;
            opacity: 0.6;
        }

        .btn-secondary {
            background: var(--gray-500);
            color: var(--white);
        }

        .btn-secondary:hover {
            background: var(--gray-600);
            transform: translateY(-2px);
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }

        .warning-box {
            background: rgba(245, 158, 11, 0.1);
            border-left: 4px solid #f59e0b;
            padding: 1rem;
            border-radius: 0.5rem;
            margin-top: 1rem;
            font-size: 0.875rem;
            color: #92400e;
        }

        .warning-box strong {
            display: block;
            margin-bottom: 0.25rem;
        }

        /* Responsive */
        @media (max-width: 768px) {
            body {
                padding: 1rem;
            }

            .review-header {
                padding: 1.5rem;
            }

            .review-header h1 {
                font-size: 1.5rem;
            }

            .review-content {
                padding: 1.5rem;
            }

            .detail-grid {
                grid-template-columns: 1fr;
            }

            .decision-grid {
                grid-template-columns: 1fr;
            }

            .btn-group {
                flex-direction: column;
            }

            .btn {
                width: 100%;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="review-wrapper">
            <div class="review-header">
                <div class="review-header-content">
                    <h1>⚖️ Review & Keputusan Rekomendasi</h1>
                    <p>Tinjau dan berikan keputusan untuk rekomendasi pengadaan alat olahraga</p>
                </div>
            </div>

            <div class="review-content">
                <a href="daftar_rekomendasi.php" class="back-link">
                    ← Kembali ke Daftar Rekomendasi
                </a>

                <?php if (isset($error)): ?>
                <div class="alert alert-error">
                    <span>✗</span>
                    <div><?php echo $error; ?></div>
                </div>
                <?php endif; ?>

                <!-- Informasi Rekomendasi -->
                <div class="info-box">
                    <h3 class="section-title">📋 Informasi Rekomendasi</h3>
                    
                    <div class="detail-grid">
                        <div class="detail-item">
                            <span class="detail-label">Nama Alat</span>
                            <span class="detail-value" style="font-size: 1.125rem; font-weight: 600;">
                                <?php echo htmlspecialchars($rekomendasi['nama_alat']); ?>
                            </span>
                        </div>

                        <div class="detail-item">
                            <span class="detail-label">Diusulkan Oleh</span>
                            <span class="detail-value"><?php echo htmlspecialchars($rekomendasi['pengusul']); ?></span>
                        </div>

                        <div class="detail-item">
                            <span class="detail-label">Kategori</span>
                            <span class="detail-value"><?php echo htmlspecialchars($rekomendasi['kategori']); ?></span>
                        </div>

                        <div class="detail-item">
                            <span class="detail-label">Jumlah Diminta</span>
                            <span class="detail-value highlight"><?php echo htmlspecialchars($rekomendasi['jumlah_diminta']); ?></span>
                            <span style="color: var(--gray-500); font-size: 0.875rem;">unit</span>
                        </div>

                        <div class="detail-item">
                            <span class="detail-label">Perkiraan Harga</span>
                            <span class="detail-value">
                                <?php 
                                if ($rekomendasi['perkiraan_harga']) {
                                    echo 'Rp ' . number_format($rekomendasi['perkiraan_harga'], 0, ',', '.');
                                } else {
                                    echo '<span style="color: var(--gray-400);">Tidak ada estimasi</span>';
                                }
                                ?>
                            </span>
                        </div>

                        <div class="detail-item">
                            <span class="detail-label">Prioritas</span>
                            <span class="priority-badge <?php echo strtolower($rekomendasi['prioritas']); ?>">
                                <?php 
                                $priority_icons = [
                                    'Tinggi' => '🔴',
                                    'Sedang' => '🟡',
                                    'Rendah' => '🟢'
                                ];
                                echo $priority_icons[$rekomendasi['prioritas']] . ' ' . htmlspecialchars($rekomendasi['prioritas']); 
                                ?>
                            </span>
                        </div>

                        <div class="detail-item full">
                            <span class="detail-label">Alasan & Deskripsi</span>
                            <div class="description-box">
                                <?php echo nl2br(htmlspecialchars($rekomendasi['alasan'])); ?>
                            </div>
                        </div>

                        <div class="detail-item full">
                            <span class="detail-label">Tanggal Pengajuan</span>
                            <span class="detail-value">
                                <?php echo date('d F Y, H:i', strtotime($rekomendasi['created_at'])); ?> WIB
                            </span>
                        </div>
                    </div>
                </div>

                <!-- Form Keputusan -->
                <form method="POST" action="" id="reviewForm">
                    <div class="decision-section">
                        <h3 class="section-title">⚖️ Berikan Keputusan</h3>
                        
                        <input type="hidden" name="status" id="statusInput" required>
                        
                        <div class="decision-grid">
                            <div class="decision-card approve" onclick="selectDecision('Disetujui', this)">
                                <div class="decision-icon">✓</div>
                                <div class="decision-title">Setujui</div>
                                <div class="decision-desc">Pengadaan akan diproses dan ditambahkan ke inventori</div>
                            </div>
                            
                            <div class="decision-card reject" onclick="selectDecision('Ditolak', this)">
                                <div class="decision-icon">✗</div>
                                <div class="decision-title">Tolak</div>
                                <div class="decision-desc">Pengadaan tidak akan dilanjutkan</div>
                            </div>
                        </div>

                        <div class="warning-box">
                            <strong>ℹ️ Informasi:</strong>
                            Jika disetujui, alat akan otomatis ditambahkan ke inventori dengan:
                            <ul style="margin-top: 0.5rem; padding-left: 1.5rem;">
                                <li>Jumlah: <?php echo $rekomendasi['jumlah_diminta']; ?> unit (semua dalam kondisi baik)</li>
                                <li>Lokasi: Gudang Utama</li>
                                <li>Status: Siap digunakan</li>
                            </ul>
                        </div>

                        <div class="form-group">
                            <label class="form-label">
                                💬 Catatan & Alasan Keputusan <span class="required">*</span>
                            </label>
                            <textarea 
                                name="catatan_admin" 
                                class="form-control" 
                                placeholder="Berikan catatan atau alasan keputusan Anda...

Contoh untuk Disetujui:
- Disetujui karena sangat dibutuhkan untuk kegiatan ekstrakurikuler basket
- Prioritas tinggi dan sesuai dengan anggaran yang tersedia
- Akan segera diproses pengadaannya

Contoh untuk Ditolak:
- Ditolak karena budget tahun ini sudah habis
- Alat serupa masih tersedia dalam kondisi baik
- Prioritas rendah, dapat ditunda ke periode berikutnya"
                                required
                            ></textarea>
                            <span class="form-help">Berikan penjelasan yang jelas agar pengusul memahami keputusan Anda</span>
                        </div>
                    </div>

                    <div class="btn-group">
                        <button type="submit" class="btn btn-primary" id="submitBtn" disabled>
                            💾 Simpan Keputusan
                        </button>
                        <button type="button" class="btn btn-secondary" onclick="window.location.href='daftar_rekomendasi.php'">
                            ✗ Batal
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        function selectDecision(status, element) {
            // Remove selected class from all cards
            document.querySelectorAll('.decision-card').forEach(card => {
                card.classList.remove('selected');
            });
            
            // Add selected class to clicked card
            element.classList.add('selected');
            
            // Set status value
            document.getElementById('statusInput').value = status;
            
            // Enable submit button
            document.getElementById('submitBtn').disabled = false;
        }

        // Validasi form sebelum submit
        document.getElementById('reviewForm').addEventListener('submit', function(e) {
            const status = document.getElementById('statusInput').value;
            const catatan = document.querySelector('textarea[name="catatan_admin"]').value.trim();
            
            if (!status) {
                e.preventDefault();
                alert('⚠️ Silakan pilih keputusan terlebih dahulu (Setujui atau Tolak)');
                return false;
            }
            
            if (!catatan) {
                e.preventDefault();
                alert('⚠️ Catatan keputusan wajib diisi');
                return false;
            }
            
            // Konfirmasi keputusan
            const confirmMsg = status === 'Disetujui' 
                ? '✓ Anda akan menyetujui rekomendasi ini dan alat akan ditambahkan ke inventori.\n\nLanjutkan?' 
                : '✗ Anda akan menolak rekomendasi ini.\n\nLanjutkan?';
            
            if (!confirm(confirmMsg)) {
                e.preventDefault();
                return false;
            }
        });
    </script>
</body>
</html>