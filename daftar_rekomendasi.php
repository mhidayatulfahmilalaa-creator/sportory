<?php
require_once 'config.php';

if (!isset($_SESSION['user_id'])) {
    redirect('login.php');
}

// Set page title
$page_title = "Rekomendasi Alat";

$user_id = $_SESSION['user_id'];
$role = $_SESSION['role'];

// Ambil data rekomendasi
if ($role == 'admin') {
    $query = "SELECT r.*, u.nama_lengkap as pengusul 
              FROM rekomendasi_alat r 
              JOIN users u ON r.user_id = u.id 
              ORDER BY 
                CASE r.status 
                    WHEN 'Menunggu' THEN 1 
                    WHEN 'Disetujui' THEN 2 
                    WHEN 'Ditolak' THEN 3 
                END,
                CASE r.prioritas 
                    WHEN 'Mendesak' THEN 1 
                    WHEN 'Tinggi' THEN 2 
                    WHEN 'Sedang' THEN 3 
                    WHEN 'Rendah' THEN 4 
                END,
                r.created_at DESC";
    $stmt = $conn->prepare($query);
} else {
    $query = "SELECT r.*, u.nama_lengkap as pengusul 
              FROM rekomendasi_alat r 
              JOIN users u ON r.user_id = u.id 
              WHERE r.user_id = ? 
              ORDER BY r.created_at DESC";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $user_id);
}
$stmt->execute();
$result = $stmt->get_result();

// Statistik untuk admin
if ($role == 'admin') {
    $query_stats = "SELECT 
        COUNT(*) as total,
        SUM(CASE WHEN status = 'Menunggu' THEN 1 ELSE 0 END) as menunggu,
        SUM(CASE WHEN status = 'Disetujui' THEN 1 ELSE 0 END) as disetujui,
        SUM(CASE WHEN status = 'Ditolak' THEN 1 ELSE 0 END) as ditolak
    FROM rekomendasi_alat";
    $result_stats = $conn->query($query_stats);
    $stats = $result_stats->fetch_assoc();
}

// Include header
include 'header.php';
?>

<style>
    .stats-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
        gap: 24px;
        margin-bottom: 32px;
    }

    .stat-card {
        background: white;
        padding: 28px;
        border-radius: 16px;
        box-shadow: 0 4px 12px rgba(0,0,0,0.1);
        transition: all 0.3s ease;
        border-left: 4px solid #2563eb;
        position: relative;
        overflow: hidden;
    }

    .stat-card::before {
        content: '';
        position: absolute;
        top: 0;
        right: 0;
        width: 100px;
        height: 100px;
        background: #2563eb;
        opacity: 0.05;
        border-radius: 50%;
        transform: translate(30%, -30%);
    }

    .stat-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 8px 20px rgba(0,0,0,0.15);
    }

    .stat-card h3 {
        color: #6b7280;
        font-size: 13px;
        margin-bottom: 12px;
        text-transform: uppercase;
        font-weight: 600;
    }

    .stat-card .number {
        font-size: 40px;
        font-weight: 700;
        color: #2563eb;
    }

    .stat-card.warning {
        border-left-color: #f59e0b;
    }

    .stat-card.warning .number {
        color: #f59e0b;
    }

    .stat-card.success {
        border-left-color: #10b981;
    }

    .stat-card.success .number {
        color: #10b981;
    }

    .stat-card.danger {
        border-left-color: #ef4444;
    }

    .stat-card.danger .number {
        color: #ef4444;
    }

    .action-section {
        background: white;
        padding: 20px 32px;
        border-radius: 16px;
        box-shadow: 0 4px 12px rgba(0,0,0,0.1);
        margin-bottom: 32px;
        display: flex;
        justify-content: space-between;
        align-items: center;
        flex-wrap: wrap;
        gap: 16px;
    }

    .action-section h2 {
        color: #1f2937;
        font-size: 20px;
        font-weight: 700;
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .table-container {
        background: white;
        padding: 32px;
        border-radius: 16px;
        box-shadow: 0 4px 12px rgba(0,0,0,0.1);
    }

    .table-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 24px;
        flex-wrap: wrap;
        gap: 16px;
    }

    .table-header h2 {
        color: #1f2937;
        font-size: 24px;
        font-weight: 700;
    }

    .table-wrapper {
        overflow-x: auto;
        border-radius: 12px;
    }

    table {
        width: 100%;
        border-collapse: collapse;
    }

    thead {
        background: linear-gradient(135deg, #2563eb, #1e40af);
    }

    th {
        padding: 16px 20px;
        text-align: left;
        color: white;
        font-weight: 600;
        text-transform: uppercase;
        font-size: 12px;
        letter-spacing: 0.5px;
        white-space: nowrap;
    }

    tbody tr {
        border-bottom: 1px solid #e5e7eb;
        transition: all 0.3s ease;
    }

    tbody tr:nth-child(even) {
        background: #f9fafb;
    }

    tbody tr:hover {
        background: #f3f4f6;
        transform: scale(1.01);
    }

    td {
        padding: 16px 20px;
        color: #1f2937;
        font-size: 14px;
    }

    .badge {
        padding: 6px 14px;
        border-radius: 20px;
        font-size: 12px;
        font-weight: 600;
        display: inline-block;
        text-transform: uppercase;
        letter-spacing: 0.3px;
    }

    .badge.menunggu {
        background: #fef3c7;
        color: #92400e;
    }

    .badge.disetujui {
        background: #d1fae5;
        color: #065f46;
    }

    .badge.ditolak {
        background: #fee2e2;
        color: #991b1b;
    }

    .badge.prioritas-rendah {
        background: #dbeafe;
        color: #1e40af;
    }

    .badge.prioritas-sedang {
        background: #fef3c7;
        color: #92400e;
    }

    .badge.prioritas-tinggi {
        background: #fed7aa;
        color: #9a3412;
    }

    .badge.prioritas-mendesak {
        background: #fee2e2;
        color: #991b1b;
    }

    .action-buttons {
        display: flex;
        gap: 8px;
        flex-wrap: wrap;
    }

    .action-btn {
        padding: 8px 14px;
        border: none;
        border-radius: 8px;
        cursor: pointer;
        font-size: 12px;
        font-weight: 600;
        transition: all 0.3s ease;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        gap: 4px;
    }

    .action-btn.view {
        background: #3b82f6;
        color: white;
    }

    .action-btn.view:hover {
        background: #2563eb;
    }

    .action-btn.approve {
        background: #10b981;
        color: white;
    }

    .action-btn.approve:hover {
        background: #059669;
    }

    @media (max-width: 768px) {
        .action-section {
            flex-direction: column;
            padding: 20px;
        }

        .action-section h2 {
            font-size: 18px;
        }

        .stats-grid {
            grid-template-columns: 1fr;
        }

        .table-container {
            padding: 20px;
        }

        table {
            min-width: 800px;
        }
    }
</style>

<?php if ($role != 'admin'): ?>
<div class="action-section">
    <h2>💡 Rekomendasi Alat Olahraga</h2>
    <a href="rekomendasi_alat.php" class="btn btn-success">
        ➕ Ajukan Rekomendasi
    </a>
</div>
<?php endif; ?>

<?php if ($role == 'admin'): ?>
<div class="stats-grid">
    <div class="stat-card">
        <h3>📊 Total Rekomendasi</h3>
        <div class="number"><?php echo number_format($stats['total']); ?></div>
    </div>
    <div class="stat-card warning">
        <h3>⏳ Menunggu Review</h3>
        <div class="number"><?php echo number_format($stats['menunggu']); ?></div>
    </div>
    <div class="stat-card success">
        <h3>✅ Disetujui</h3>
        <div class="number"><?php echo number_format($stats['disetujui']); ?></div>
    </div>
    <div class="stat-card danger">
        <h3>❌ Ditolak</h3>
        <div class="number"><?php echo number_format($stats['ditolak']); ?></div>
    </div>
</div>
<?php endif; ?>

<div class="table-container">
    <div class="table-header">
        <h2>📋 Daftar Rekomendasi</h2>
    </div>

    <div class="table-wrapper">
        <table>
            <thead>
                <tr>
                    <th>No</th>
                    <?php if ($role == 'admin'): ?>
                    <th>Pengusul</th>
                    <?php endif; ?>
                    <th>Nama Alat</th>
                    <th>Kategori</th>
                    <th>Jumlah</th>
                    <th>Prioritas</th>
                    <th>Status</th>
                    <th>Tanggal</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php 
                if ($result->num_rows > 0):
                    $no = 1;
                    while ($row = $result->fetch_assoc()): 
                        $status_class = strtolower($row['status']);
                        $prioritas_class = 'prioritas-' . strtolower(str_replace(' ', '-', $row['prioritas']));
                ?>
                <tr>
                    <td><?php echo $no++; ?></td>
                    <?php if ($role == 'admin'): ?>
                    <td><?php echo htmlspecialchars($row['pengusul']); ?></td>
                    <?php endif; ?>
                    <td><strong><?php echo htmlspecialchars($row['nama_alat']); ?></strong></td>
                    <td><?php echo htmlspecialchars($row['kategori']); ?></td>
                    <td><strong><?php echo number_format($row['jumlah_diminta']); ?></strong></td>
                    <td><span class="badge <?php echo $prioritas_class; ?>"><?php echo htmlspecialchars($row['prioritas']); ?></span></td>
                    <td><span class="badge <?php echo $status_class; ?>"><?php echo htmlspecialchars($row['status']); ?></span></td>
                    <td><?php echo date('d/m/Y', strtotime($row['created_at'])); ?></td>
                    <td>
                        <div class="action-buttons">
                            <a href="detail_rekomendasi.php?id=<?php echo $row['id']; ?>" class="action-btn view">
                                👁️ Detail
                            </a>
                            <?php if ($role == 'admin' && $row['status'] == 'Menunggu'): ?>
                            <a href="review_rekomendasi.php?id=<?php echo $row['id']; ?>" class="action-btn approve">
                                ✅ Review
                            </a>
                            <?php endif; ?>
                        </div>
                    </td>
                </tr>
                <?php 
                    endwhile;
                else:
                ?>
                <tr>
                    <td colspan="<?php echo $role == 'admin' ? '9' : '8'; ?>" style="text-align: center; padding: 40px; color: #6b7280;">
                        <div style="font-size: 48px; margin-bottom: 16px;">📭</div>
                        <strong>Belum ada rekomendasi</strong>
                        <p style="margin-top: 8px;">
                            <?php if ($role == 'admin'): ?>
                            Belum ada rekomendasi yang diajukan
                            <?php else: ?>
                            Klik tombol "Ajukan Rekomendasi" untuk menambahkan
                            <?php endif; ?>
                        </p>
                    </td>
                </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

</div> <!-- End container -->

</body>
</html>