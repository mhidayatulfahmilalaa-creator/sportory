<?php
require_once 'config.php';

// Cek apakah user sudah login
if (!isset($_SESSION['user_id'])) {
    redirect('login.php');
}

$user_id = $_SESSION['user_id'];
$role = $_SESSION['role'];

// Cek apakah ada parameter id
if (!isset($_GET['id'])) {
    redirect('daftar_rekomendasi.php');
}

$id = $_GET['id'];

// Ambil data rekomendasi
$query = "SELECT r.*, u.nama_lengkap as pengusul, 
          a.nama_lengkap as admin_name
          FROM rekomendasi_alat r 
          JOIN users u ON r.user_id = u.id 
          LEFT JOIN users a ON r.disetujui_oleh = a.id
          WHERE r.id = ?";

// Jika bukan admin, pastikan hanya bisa lihat rekomendasi sendiri
if ($role != 'admin') {
    $query .= " AND r.user_id = ?";
}

$stmt = $conn->prepare($query);
if ($role != 'admin') {
    $stmt->bind_param("ii", $id, $user_id);
} else {
    $stmt->bind_param("i", $id);
}
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows == 0) {
    $_SESSION['error'] = "Rekomendasi tidak ditemukan!";
    redirect('daftar_rekomendasi.php');
}

$rekomendasi = $result->fetch_assoc();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detail Rekomendasi - Sportory</title>
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
            --accent-red: #ef4444;
            --accent-orange: #f59e0b;
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

        .detail-wrapper {
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

        .detail-header {
            background: linear-gradient(135deg, var(--primary-blue) 0%, var(--primary-blue-dark) 100%);
            color: var(--white);
            padding: 2rem;
            position: relative;
            overflow: hidden;
        }

        .detail-header::before {
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

        .detail-header-content {
            position: relative;
            z-index: 1;
        }

        .detail-header h1 {
            font-size: 1.75rem;
            font-weight: 700;
            margin-bottom: 0.5rem;
        }

        .status-badge {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.5rem 1rem;
            border-radius: 50px;
            font-size: 0.875rem;
            font-weight: 600;
            margin-top: 0.75rem;
        }

        .status-badge.menunggu {
            background: rgba(245, 158, 11, 0.2);
            color: var(--white);
            border: 2px solid rgba(255, 255, 255, 0.3);
        }

        .status-badge.disetujui {
            background: rgba(16, 185, 129, 0.2);
            color: var(--white);
            border: 2px solid rgba(255, 255, 255, 0.3);
        }

        .status-badge.ditolak {
            background: rgba(239, 68, 68, 0.2);
            color: var(--white);
            border: 2px solid rgba(255, 255, 255, 0.3);
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

        .detail-content {
            padding: 2rem;
        }

        .info-section {
            background: var(--gray-50);
            padding: 1.5rem;
            border-radius: 0.75rem;
            margin-bottom: 1.5rem;
            border-left: 4px solid var(--primary-blue);
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

        .detail-value.large {
            font-size: 1.25rem;
            font-weight: 600;
        }

        .card {
            background: var(--white);
            border: 1px solid var(--gray-200);
            border-radius: 0.75rem;
            padding: 1.5rem;
            margin-bottom: 1.5rem;
        }

        .card-header {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            margin-bottom: 1rem;
            padding-bottom: 1rem;
            border-bottom: 2px solid var(--gray-200);
        }

        .card-icon {
            font-size: 1.5rem;
        }

        .card-title {
            font-size: 1rem;
            font-weight: 600;
            color: var(--gray-800);
        }

        .admin-section {
            background: linear-gradient(135deg, rgba(37, 99, 235, 0.05) 0%, rgba(37, 99, 235, 0.1) 100%);
            border-left: 4px solid var(--primary-blue);
            padding: 1.5rem;
            border-radius: 0.75rem;
            margin-top: 1.5rem;
        }

        .priority-badge {
            display: inline-flex;
            align-items: center;
            gap: 0.25rem;
            padding: 0.5rem 1rem;
            border-radius: 50px;
            font-size: 0.875rem;
            font-weight: 600;
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
            padding: 0.875rem 1.5rem;
            font-size: 0.875rem;
            font-weight: 500;
            border-radius: 0.5rem;
            border: none;
            cursor: pointer;
            transition: all 0.15s;
            text-decoration: none;
        }

        .btn-success {
            background: var(--accent-green);
            color: var(--white);
        }

        .btn-success:hover {
            background: #059669;
            transform: translateY(-2px);
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
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

        .price-display {
            display: flex;
            align-items: baseline;
            gap: 0.25rem;
        }

        .currency {
            font-size: 0.875rem;
            color: var(--gray-600);
        }

        .amount {
            font-size: 1.25rem;
            font-weight: 700;
            color: var(--accent-green);
        }

        .description-box {
            background: var(--white);
            padding: 1rem;
            border-radius: 0.5rem;
            border: 1px solid var(--gray-200);
            line-height: 1.6;
        }

        .meta-info {
            display: flex;
            flex-wrap: wrap;
            gap: 1rem;
            padding-top: 1rem;
            border-top: 1px solid var(--gray-200);
            margin-top: 1rem;
        }

        .meta-item {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            color: var(--gray-600);
            font-size: 0.875rem;
        }

        .meta-icon {
            font-size: 1rem;
        }

        /* Responsive */
        @media (max-width: 768px) {
            body {
                padding: 1rem;
            }

            .detail-header {
                padding: 1.5rem;
            }

            .detail-header h1 {
                font-size: 1.5rem;
            }

            .detail-content {
                padding: 1.5rem;
            }

            .detail-grid {
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
        <div class="detail-wrapper">
            <div class="detail-header">
                <div class="detail-header-content">
                    <h1>📄 <?php echo htmlspecialchars($rekomendasi['nama_alat']); ?></h1>
                    <span class="status-badge <?php echo strtolower($rekomendasi['status']); ?>">
                        <?php 
                        $status_icons = [
                            'Menunggu' => '⏳',
                            'Disetujui' => '✓',
                            'Ditolak' => '✗'
                        ];
                        echo $status_icons[$rekomendasi['status']] . ' ' . htmlspecialchars($rekomendasi['status']); 
                        ?>
                    </span>
                </div>
            </div>

            <div class="detail-content">
                <a href="daftar_rekomendasi.php" class="back-link">
                    ← Kembali ke Daftar Rekomendasi
                </a>

                <!-- Informasi Rekomendasi -->
                <div class="card">
                    <div class="card-header">
                        <span class="card-icon">📋</span>
                        <h3 class="card-title">Informasi Rekomendasi</h3>
                    </div>
                    
                    <div class="detail-grid">
                        <div class="detail-item">
                            <span class="detail-label">👤 Diajukan Oleh</span>
                            <span class="detail-value large"><?php echo htmlspecialchars($rekomendasi['pengusul']); ?></span>
                        </div>

                        <div class="detail-item">
                            <span class="detail-label">📅 Tanggal Pengajuan</span>
                            <span class="detail-value"><?php echo date('d F Y, H:i', strtotime($rekomendasi['created_at'])); ?> WIB</span>
                        </div>

                        <div class="detail-item">
                            <span class="detail-label">📦 Kategori</span>
                            <span class="detail-value"><?php echo htmlspecialchars($rekomendasi['kategori']); ?></span>
                        </div>

                        <div class="detail-item">
                            <span class="detail-label">🔢 Jumlah Diminta</span>
                            <span class="detail-value highlight"><?php echo htmlspecialchars($rekomendasi['jumlah_diminta']); ?></span>
                            <span style="color: var(--gray-500); font-size: 0.875rem;">unit</span>
                        </div>

                        <div class="detail-item">
                            <span class="detail-label">💰 Perkiraan Harga</span>
                            <?php if ($rekomendasi['perkiraan_harga']): ?>
                            <div class="price-display">
                                <span class="currency">Rp</span>
                                <span class="amount"><?php echo number_format($rekomendasi['perkiraan_harga'], 0, ',', '.'); ?></span>
                            </div>
                            <span style="color: var(--gray-500); font-size: 0.75rem;">per unit</span>
                            <?php else: ?>
                            <span class="detail-value" style="color: var(--gray-400);">Tidak ada estimasi</span>
                            <?php endif; ?>
                        </div>

                        <div class="detail-item">
                            <span class="detail-label">⚡ Prioritas</span>
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
                            <span class="detail-label">📝 Alasan & Deskripsi</span>
                            <div class="description-box">
                                <?php echo nl2br(htmlspecialchars($rekomendasi['alasan'])); ?>
                            </div>
                        </div>
                    </div>
                </div>

                <?php if ($rekomendasi['status'] != 'Menunggu'): ?>
                <!-- Keputusan Admin -->
                <div class="admin-section">
                    <h3 class="section-title">
                        <?php echo $rekomendasi['status'] == 'Disetujui' ? '✅' : '❌'; ?>
                        Keputusan Admin
                    </h3>
                    
                    <div class="detail-grid">
                        <div class="detail-item">
                            <span class="detail-label">Diputuskan Oleh</span>
                            <span class="detail-value"><?php echo htmlspecialchars($rekomendasi['admin_name']); ?></span>
                        </div>

                        <div class="detail-item">
                            <span class="detail-label">Tanggal Keputusan</span>
                            <span class="detail-value"><?php echo date('d F Y, H:i', strtotime($rekomendasi['tanggal_disetujui'])); ?> WIB</span>
                        </div>

                        <?php if ($rekomendasi['catatan_admin']): ?>
                        <div class="detail-item full">
                            <span class="detail-label">💬 Catatan Admin</span>
                            <div class="description-box">
                                <?php echo nl2br(htmlspecialchars($rekomendasi['catatan_admin'])); ?>
                            </div>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
                <?php endif; ?>

                <!-- Action Buttons -->
                <div class="btn-group">
                    <?php if ($role == 'admin' && $rekomendasi['status'] == 'Menunggu'): ?>
                    <a href="review_rekomendasi.php?id=<?php echo $rekomendasi['id']; ?>" class="btn btn-success">
                        ✓ Review & Putuskan
                    </a>
                    <?php endif; ?>
                    <a href="daftar_rekomendasi.php" class="btn btn-secondary">
                        ← Kembali
                    </a>
                </div>
            </div>
        </div>
    </div>
</body>
</html>