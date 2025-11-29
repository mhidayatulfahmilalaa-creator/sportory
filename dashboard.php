<?php
require_once 'config.php';

// Cek apakah user sudah login
if (!isset($_SESSION['user_id'])) {
    redirect('login.php');
}

// Set page title
$page_title = "Dashboard";

// Ambil statistik alat olahraga dengan perhitungan baru
$query_stats = "SELECT 
    COUNT(*) as total_alat,
    SUM(jumlah + jumlah_rusak) as total_item,
    SUM(jumlah) as kondisi_baik,
    SUM(jumlah_rusak) as rusak
FROM alat_olahraga";
$result_stats = $conn->query($query_stats);
$stats = $result_stats->fetch_assoc();

// Ambil data alat olahraga
$query_alat = "SELECT * FROM alat_olahraga ORDER BY updated_at DESC";
$result_alat = $conn->query($query_alat);

// Include header
include 'header.php';
?>

<style>
    /* Stats Grid */
    .stats-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
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
        letter-spacing: 0.5px;
    }

    .stat-card .number {
        font-size: 40px;
        font-weight: 700;
        color: #2563eb;
        line-height: 1;
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

    /* Table Container */
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

    .search-box {
        margin-bottom: 24px;
    }

    .search-box input {
        width: 100%;
        padding: 14px 20px;
        border: 2px solid #e5e7eb;
        border-radius: 12px;
        font-size: 14px;
        transition: all 0.3s ease;
        font-family: 'Poppins', sans-serif;
    }

    .search-box input:focus {
        outline: none;
        border-color: #2563eb;
        box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.1);
    }

    .table-wrapper {
        overflow-x: auto;
        border-radius: 12px;
    }

    table {
        width: 100%;
        border-collapse: collapse;
        background: white;
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

    /* Kondisi Detail Badges */
    .kondisi-detail {
        display: flex;
        flex-direction: column;
        gap: 6px;
    }

    .kondisi-item {
        display: flex;
        align-items: center;
        gap: 8px;
        font-size: 13px;
    }

    .kondisi-badge {
        padding: 4px 10px;
        border-radius: 12px;
        font-size: 11px;
        font-weight: 600;
        display: inline-flex;
        align-items: center;
        gap: 4px;
        min-width: 70px;
    }

    .kondisi-badge.baik {
        background: #d1fae5;
        color: #065f46;
    }

    .kondisi-badge.rusak {
        background: #fee2e2;
        color: #991b1b;
    }

    .kondisi-badge.warning {
        background: #fef3c7;
        color: #92400e;
    }

    .jumlah-total {
        font-size: 18px;
        font-weight: 700;
        color: #2563eb;
    }

    /* Status Indicator */
    .status-indicator {
        display: inline-block;
        width: 8px;
        height: 8px;
        border-radius: 50%;
        margin-right: 4px;
    }

    .status-indicator.baik {
        background: #10b981;
    }

    .status-indicator.rusak {
        background: #ef4444;
    }

    .status-indicator.warning {
        background: #f59e0b;
    }

    /* Action Buttons */
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

    .action-btn.edit {
        background: #3b82f6;
        color: white;
    }

    .action-btn.edit:hover {
        background: #2563eb;
    }

    .action-btn.delete {
        background: #ef4444;
        color: white;
    }

    .action-btn.delete:hover {
        background: #dc2626;
    }

    /* Responsive */
    @media (max-width: 768px) {
        .stats-grid {
            grid-template-columns: 1fr;
        }

        .table-container {
            padding: 20px;
        }

        .table-header {
            flex-direction: column;
            align-items: flex-start;
        }

        .table-wrapper {
            overflow-x: auto;
            -webkit-overflow-scrolling: touch;
        }

        table {
            min-width: 700px;
        }

        th, td {
            padding: 12px;
            font-size: 13px;
        }
    }

    @media (max-width: 480px) {
        .stat-card {
            padding: 20px;
        }

        .stat-card .number {
            font-size: 32px;
        }
    }
</style>

<!-- Statistics -->
<div class="stats-grid">
    <div class="stat-card">
        <h3>📊 Total Jenis Alat</h3>
        <div class="number"><?php echo number_format($stats['total_alat']); ?></div>
    </div>
    <div class="stat-card">
        <h3>📦 Total Item</h3>
        <div class="number"><?php echo number_format($stats['total_item']); ?></div>
    </div>
    <div class="stat-card success">
        <h3>✅ Kondisi Baik</h3>
        <div class="number"><?php echo number_format($stats['kondisi_baik']); ?></div>
    </div>
    <div class="stat-card danger">
        <h3>⚠️ Kondisi Rusak</h3>
        <div class="number"><?php echo number_format($stats['rusak']); ?></div>
    </div>
</div>

<!-- Table -->
<div class="table-container">
    <div class="table-header">
        <h2>📋 Data Alat Olahraga</h2>
        <div style="display: flex; gap: 8px; flex-wrap: wrap;">
            <a href="export_laporan.php?format=excel" class="btn btn-success">
                📊 Export Excel
            </a>
            <a href="export_laporan.php?format=pdf" class="btn btn-info" target="_blank">
                📄 Export PDF
            </a>
            <a href="export_laporan.php?format=csv" class="btn btn-secondary">
                📁 Export CSV
            </a>
        </div>
    </div>

    <div class="search-box">
        <input 
            type="text" 
            id="searchInput" 
            placeholder="🔍 Cari berdasarkan nama, kategori, atau lokasi..." 
            onkeyup="searchTable()"
        >
    </div>

    <div class="table-wrapper">
        <table id="alatTable">
            <thead>
                <tr>
                    <th>No</th>
                    <th>Nama Alat</th>
                    <th>Kategori</th>
                    <th style="text-align: center;">Total</th>
                    <th>Detail Kondisi</th>
                    <th>Lokasi</th>
                    <th>Tanggal Masuk</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php 
                if ($result_alat->num_rows > 0):
                    $no = 1;
                    while ($row = $result_alat->fetch_assoc()): 
                        $total = $row['jumlah'] + $row['jumlah_rusak'];
                        $jumlah_baik = $row['jumlah'];
                        $jumlah_rusak = $row['jumlah_rusak'];
                        
                        // Tentukan status keseluruhan
                        if ($jumlah_rusak == 0) {
                            $status = 'baik';
                        } elseif ($jumlah_baik == 0) {
                            $status = 'rusak';
                        } else {
                            $status = 'warning';
                        }
                ?>
                <tr>
                    <td><?php echo $no++; ?></td>
                    <td><strong><?php echo htmlspecialchars($row['nama_alat']); ?></strong></td>
                    <td><?php echo htmlspecialchars($row['kategori']); ?></td>
                    <td style="text-align: center;">
                        <span class="jumlah-total"><?php echo number_format($total); ?></span>
                    </td>
                    <td>
                        <div class="kondisi-detail">
                            <?php if ($jumlah_baik > 0): ?>
                            <div class="kondisi-item">
                                <span class="kondisi-badge baik">
                                    <span class="status-indicator baik"></span>
                                    Baik: <?php echo number_format($jumlah_baik); ?>
                                </span>
                            </div>
                            <?php endif; ?>
                            
                            <?php if ($jumlah_rusak > 0): ?>
                            <div class="kondisi-item">
                                <span class="kondisi-badge rusak">
                                    <span class="status-indicator rusak"></span>
                                    Rusak: <?php echo number_format($jumlah_rusak); ?>
                                </span>
                            </div>
                            <?php endif; ?>
                            
                            <?php if ($jumlah_baik == 0 && $jumlah_rusak == 0): ?>
                            <div class="kondisi-item">
                                <span class="kondisi-badge warning">
                                    <span class="status-indicator warning"></span>
                                    Kosong
                                </span>
                            </div>
                            <?php endif; ?>
                        </div>
                    </td>
                    <td><?php echo htmlspecialchars($row['lokasi_penyimpanan']); ?></td>
                    <td><?php echo date('d/m/Y', strtotime($row['tanggal_masuk'])); ?></td>
                    <td>
                        <div class="action-buttons">
                            <a href="edit_alat.php?id=<?php echo $row['id']; ?>" class="action-btn edit">
                                ✏️ Edit
                            </a>
                            <button 
                                class="action-btn delete" 
                                onclick="deleteAlat(<?php echo $row['id']; ?>, '<?php echo htmlspecialchars(addslashes($row['nama_alat'])); ?>')"
                            >
                                🗑️ Hapus
                            </button>
                        </div>
                    </td>
                </tr>
                <?php 
                    endwhile;
                else:
                ?>
                <tr>
                    <td colspan="8" style="text-align: center; padding: 40px; color: #6b7280;">
                        <div style="font-size: 48px; margin-bottom: 16px;">📭</div>
                        <strong>Belum ada data alat olahraga</strong>
                        <p style="margin-top: 8px;">Klik tombol "Rekomendasi" untuk mengajukan data alat baru</p>
                    </td>
                </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

</div> <!-- End container -->

<script>
    function deleteAlat(id, namaAlat) {
        if (confirm('Apakah Anda yakin ingin menghapus alat "' + namaAlat + '"?\n\nData yang dihapus tidak dapat dikembalikan!')) {
            window.location.href = 'hapus_alat.php?id=' + id;
        }
    }

    function searchTable() {
        const input = document.getElementById('searchInput');
        const filter = input.value.toUpperCase();
        const table = document.getElementById('alatTable');
        const tr = table.getElementsByTagName('tr');

        for (let i = 1; i < tr.length; i++) {
            let found = false;
            const td = tr[i].getElementsByTagName('td');
            
            if (td.length === 1 && td[0].getAttribute('colspan')) {
                continue;
            }
            
            for (let j = 0; j < td.length; j++) {
                if (td[j]) {
                    const txtValue = td[j].textContent || td[j].innerText;
                    if (txtValue.toUpperCase().indexOf(filter) > -1) {
                        found = true;
                        break;
                    }
                }
            }
            
            tr[i].style.display = found ? '' : 'none';
        }
    }
</script>

</body>
</html>