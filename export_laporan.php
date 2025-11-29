<?php
require_once 'config.php';

// Cek apakah user sudah login
if (!isset($_SESSION['user_id'])) {
    redirect('login.php');
}

$format = isset($_GET['format']) ? $_GET['format'] : 'excel';

// Ambil data alat olahraga dengan kalkulasi kondisi
$query = "SELECT 
    a.*,
    (a.jumlah - a.jumlah_rusak) as jumlah_baik,
    CASE 
        WHEN a.jumlah_rusak >= a.jumlah THEN 'Rusak'
        WHEN a.jumlah_rusak > 0 THEN 'Sebagian Rusak'
        ELSE 'Baik'
    END as kondisi,
    (SELECT SUM(jumlah) FROM alat_olahraga) as grand_total,
    (SELECT SUM(jumlah - jumlah_rusak) FROM alat_olahraga) as total_baik,
    (SELECT SUM(jumlah_rusak) FROM alat_olahraga) as total_rusak
FROM alat_olahraga a 
ORDER BY a.kategori, a.nama_alat";
$result = $conn->query($query);

if ($format == 'excel') {
    exportExcel($result, $_SESSION['nama_lengkap'], $_SESSION['role']);
} else if ($format == 'pdf') {
    exportPDF($result, $_SESSION['nama_lengkap'], $_SESSION['role']);
} else if ($format == 'csv') {
    exportCSV($result, $_SESSION['nama_lengkap'], $_SESSION['role']);
}

function exportExcel($result, $nama_user, $role) {
    header('Content-Type: application/vnd.ms-excel');
    header('Content-Disposition: attachment;filename="Laporan_Inventory_Sportory_' . date('Y-m-d_His') . '.xls"');
    header('Cache-Control: max-age=0');
    
    echo '<html xmlns:x="urn:schemas-microsoft-com:office:excel">';
    echo '<head>';
    echo '<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />';
    echo '<xml>';
    echo '<x:ExcelWorkbook>';
    echo '<x:ExcelWorksheets>';
    echo '<x:ExcelWorksheet>';
    echo '<x:Name>Laporan Inventory</x:Name>';
    echo '<x:WorksheetOptions>';
    echo '<x:Print>';
    echo '<x:ValidPrinterInfo/>';
    echo '</x:Print>';
    echo '</x:WorksheetOptions>';
    echo '</x:ExcelWorksheet>';
    echo '</x:ExcelWorksheets>';
    echo '</x:ExcelWorkbook>';
    echo '</xml>';
    echo '</head>';
    echo '<body>';
    
    echo '<table border="1" cellpadding="5" cellspacing="0" style="border-collapse: collapse; width: 100%;">';
    
    // Header Laporan
    echo '<tr>';
    echo '<td colspan="10" style="text-align: center; font-size: 18px; font-weight: bold; background-color: #2563eb; color: white;">';
    echo 'LAPORAN INVENTORY ALAT OLAHRAGA';
    echo '</td>';
    echo '</tr>';
    
    echo '<tr>';
    echo '<td colspan="10" style="text-align: center; font-size: 14px; background-color: #f3f4f6;">';
    echo 'SPORTORY - Sistem Manajemen Alat Olahraga';
    echo '</td>';
    echo '</tr>';
    
    echo '<tr>';
    echo '<td colspan="3" style="background-color: #f9fafb;"><strong>Tanggal Export:</strong></td>';
    echo '<td colspan="7" style="background-color: #f9fafb;">' . date('d F Y, H:i:s') . '</td>';
    echo '</tr>';
    
    echo '<tr>';
    echo '<td colspan="3" style="background-color: #f9fafb;"><strong>Di-export oleh:</strong></td>';
    echo '<td colspan="7" style="background-color: #f9fafb;">' . htmlspecialchars($nama_user) . ' (' . ucfirst($role) . ')</td>';
    echo '</tr>';
    
    // Statistik
    $stats = getStatistics($result);
    
    echo '<tr><td colspan="10" style="height: 10px;"></td></tr>';
    
    echo '<tr style="background-color: #dbeafe;">';
    echo '<td colspan="2"><strong>Total Jenis Alat:</strong></td>';
    echo '<td>' . $stats['total_jenis'] . '</td>';
    echo '<td colspan="2"><strong>Total Item:</strong></td>';
    echo '<td>' . $stats['total_item'] . '</td>';
    echo '<td colspan="2"><strong>Kondisi Baik:</strong></td>';
    echo '<td>' . $stats['kondisi_baik'] . '</td>';
    echo '<td style="background-color: #fee2e2;"><strong>Rusak: ' . $stats['kondisi_rusak'] . '</strong></td>';
    echo '</tr>';
    
    echo '<tr><td colspan="10" style="height: 10px;"></td></tr>';
    
    // Header Tabel
    echo '<tr style="background-color: #2563eb; color: white; font-weight: bold; text-align: center;">';
    echo '<th>No</th>';
    echo '<th>Nama Alat</th>';
    echo '<th>Kategori</th>';
    echo '<th>Jumlah Total</th>';
    echo '<th>Baik</th>';
    echo '<th>Rusak</th>';
    echo '<th>Kondisi</th>';
    echo '<th>Lokasi Penyimpanan</th>';
    echo '<th>Tanggal Masuk</th>';
    echo '<th>Keterangan</th>';
    echo '</tr>';
    
    // Data
    mysqli_data_seek($result, 0); // Reset pointer
    $no = 1;
    $current_category = '';
    
    while ($row = $result->fetch_assoc()) {
        // Tambahkan baris kategori jika berbeda
        if ($current_category != $row['kategori']) {
            $current_category = $row['kategori'];
            echo '<tr style="background-color: #e0e7ff;">';
            echo '<td colspan="10" style="font-weight: bold; padding: 8px;">KATEGORI: ' . strtoupper($row['kategori']) . '</td>';
            echo '</tr>';
        }
        
        $bg_color = $no % 2 == 0 ? '#f9fafb' : '#ffffff';
        if ($row['kondisi'] == 'Rusak') {
            $bg_color = '#fee2e2';
        } else if ($row['kondisi'] == 'Sebagian Rusak') {
            $bg_color = '#fef3c7';
        }
        
        echo '<tr style="background-color: ' . $bg_color . ';">';
        echo '<td style="text-align: center;">' . $no++ . '</td>';
        echo '<td>' . htmlspecialchars($row['nama_alat']) . '</td>';
        echo '<td>' . htmlspecialchars($row['kategori']) . '</td>';
        echo '<td style="text-align: center; font-weight: bold;">' . $row['jumlah'] . '</td>';
        echo '<td style="text-align: center; color: #10b981; font-weight: bold;">' . $row['jumlah_baik'] . '</td>';
        echo '<td style="text-align: center; color: #ef4444; font-weight: bold;">' . $row['jumlah_rusak'] . '</td>';
        echo '<td style="text-align: center; font-weight: bold;">' . $row['kondisi'] . '</td>';
        echo '<td>' . htmlspecialchars($row['lokasi_penyimpanan']) . '</td>';
        echo '<td style="text-align: center;">' . date('d/m/Y', strtotime($row['tanggal_masuk'])) . '</td>';
        echo '<td>' . htmlspecialchars($row['keterangan'] ?? '-') . '</td>';
        echo '</tr>';
    }
    
    // Footer
    echo '<tr><td colspan="10" style="height: 10px;"></td></tr>';
    echo '<tr style="background-color: #f3f4f6;">';
    echo '<td colspan="10" style="text-align: center; font-size: 11px; padding: 10px;">';
    echo 'Dokumen ini digenerate otomatis oleh sistem SPORTORY pada ' . date('d F Y, H:i:s');
    echo '</td>';
    echo '</tr>';
    
    echo '</table>';
    echo '</body>';
    echo '</html>';
    exit;
}

function exportPDF($result, $nama_user, $role) {
    header('Content-Type: text/html; charset=utf-8');
    ?>
    <!DOCTYPE html>
    <html>
    <head>
        <meta charset="UTF-8">
        <title>Laporan Inventory Sportory</title>
        <style>
            @media print {
                .no-print { display: none; }
            }
            body {
                font-family: Arial, sans-serif;
                margin: 20px;
                font-size: 12px;
            }
            .header {
                text-align: center;
                margin-bottom: 20px;
                border-bottom: 3px solid #2563eb;
                padding-bottom: 10px;
            }
            .header h1 {
                color: #2563eb;
                margin: 0;
                font-size: 24px;
            }
            .header h2 {
                color: #666;
                margin: 5px 0;
                font-size: 14px;
                font-weight: normal;
            }
            .info-table {
                width: 100%;
                margin-bottom: 20px;
                border-collapse: collapse;
            }
            .info-table td {
                padding: 5px;
                font-size: 11px;
            }
            .info-table .label {
                font-weight: bold;
                width: 150px;
            }
            .stats-box {
                background: #f3f4f6;
                padding: 15px;
                margin: 20px 0;
                border-radius: 8px;
                display: flex;
                justify-content: space-around;
            }
            .stat-item {
                text-align: center;
            }
            .stat-item .number {
                font-size: 24px;
                font-weight: bold;
                color: #2563eb;
            }
            .stat-item .label {
                font-size: 11px;
                color: #666;
            }
            table.data-table {
                width: 100%;
                border-collapse: collapse;
                margin-top: 20px;
            }
            table.data-table th {
                background: #2563eb;
                color: white;
                padding: 10px 5px;
                text-align: left;
                font-size: 10px;
                border: 1px solid #1e40af;
            }
            table.data-table td {
                padding: 8px 5px;
                border: 1px solid #ddd;
                font-size: 9px;
            }
            table.data-table tr:nth-child(even) {
                background: #f9fafb;
            }
            .category-row {
                background: #e0e7ff !important;
                font-weight: bold;
            }
            .kondisi-rusak {
                background: #fee2e2 !important;
            }
            .kondisi-sebagian {
                background: #fef3c7 !important;
            }
            .footer {
                margin-top: 30px;
                text-align: center;
                font-size: 10px;
                color: #666;
                border-top: 1px solid #ddd;
                padding-top: 10px;
            }
            .btn-print {
                background: #2563eb;
                color: white;
                padding: 10px 20px;
                border: none;
                border-radius: 5px;
                cursor: pointer;
                margin: 10px;
            }
        </style>
    </head>
    <body>
        <div class="no-print" style="text-align: center; margin-bottom: 20px;">
            <button class="btn-print" onclick="window.print()">🖨️ Print / Save as PDF</button>
            <button class="btn-print" onclick="window.close()" style="background: #6b7280;">❌ Tutup</button>
        </div>
        
        <div class="header">
            <h1>LAPORAN INVENTORY ALAT OLAHRAGA</h1>
            <h2>SPORTORY - Sistem Manajemen Alat Olahraga</h2>
        </div>
        
        <table class="info-table">
            <tr>
                <td class="label">Tanggal Export:</td>
                <td><?php echo date('d F Y, H:i:s'); ?></td>
                <td class="label">Di-export oleh:</td>
                <td><?php echo htmlspecialchars($nama_user) . ' (' . ucfirst($role) . ')'; ?></td>
            </tr>
        </table>
        
        <?php
        $stats = getStatistics($result);
        ?>
        
        <div class="stats-box">
            <div class="stat-item">
                <div class="number"><?php echo $stats['total_jenis']; ?></div>
                <div class="label">Total Jenis Alat</div>
            </div>
            <div class="stat-item">
                <div class="number"><?php echo $stats['total_item']; ?></div>
                <div class="label">Total Item</div>
            </div>
            <div class="stat-item">
                <div class="number" style="color: #10b981;"><?php echo $stats['kondisi_baik']; ?></div>
                <div class="label">Kondisi Baik</div>
            </div>
            <div class="stat-item">
                <div class="number" style="color: #ef4444;"><?php echo $stats['kondisi_rusak']; ?></div>
                <div class="label">Kondisi Rusak</div>
            </div>
        </div>
        
        <table class="data-table">
            <thead>
                <tr>
                    <th style="width: 25px;">No</th>
                    <th>Nama Alat</th>
                    <th style="width: 70px;">Kategori</th>
                    <th style="width: 45px;">Total</th>
                    <th style="width: 40px;">Baik</th>
                    <th style="width: 40px;">Rusak</th>
                    <th style="width: 70px;">Kondisi</th>
                    <th>Lokasi</th>
                    <th style="width: 70px;">Tgl Masuk</th>
                    <th>Keterangan</th>
                </tr>
            </thead>
            <tbody>
                <?php
                mysqli_data_seek($result, 0);
                $no = 1;
                $current_category = '';
                
                while ($row = $result->fetch_assoc()) {
                    if ($current_category != $row['kategori']) {
                        $current_category = $row['kategori'];
                        echo '<tr class="category-row">';
                        echo '<td colspan="10">KATEGORI: ' . strtoupper($row['kategori']) . '</td>';
                        echo '</tr>';
                    }
                    
                    $row_class = '';
                    if ($row['kondisi'] == 'Rusak') {
                        $row_class = 'kondisi-rusak';
                    } else if ($row['kondisi'] == 'Sebagian Rusak') {
                        $row_class = 'kondisi-sebagian';
                    }
                    
                    echo '<tr class="' . $row_class . '">';
                    echo '<td style="text-align: center;">' . $no++ . '</td>';
                    echo '<td>' . htmlspecialchars($row['nama_alat']) . '</td>';
                    echo '<td>' . htmlspecialchars($row['kategori']) . '</td>';
                    echo '<td style="text-align: center;"><strong>' . $row['jumlah'] . '</strong></td>';
                    echo '<td style="text-align: center; color: #10b981;"><strong>' . $row['jumlah_baik'] . '</strong></td>';
                    echo '<td style="text-align: center; color: #ef4444;"><strong>' . $row['jumlah_rusak'] . '</strong></td>';
                    echo '<td style="text-align: center;"><strong>' . $row['kondisi'] . '</strong></td>';
                    echo '<td>' . htmlspecialchars($row['lokasi_penyimpanan']) . '</td>';
                    echo '<td style="text-align: center;">' . date('d/m/Y', strtotime($row['tanggal_masuk'])) . '</td>';
                    echo '<td>' . htmlspecialchars($row['keterangan'] ?? '-') . '</td>';
                    echo '</tr>';
                }
                ?>
            </tbody>
        </table>
        
        <div class="footer">
            Dokumen ini digenerate otomatis oleh sistem SPORTORY pada <?php echo date('d F Y, H:i:s'); ?><br>
            Dicetak oleh: <?php echo htmlspecialchars($nama_user); ?>
        </div>
    </body>
    </html>
    <?php
    exit;
}

function exportCSV($result, $nama_user, $role) {
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment;filename="Laporan_Inventory_Sportory_' . date('Y-m-d_His') . '.csv"');
    header('Cache-Control: max-age=0');
    
    $output = fopen('php://output', 'w');
    
    // UTF-8 BOM untuk Excel
    fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));
    
    // Header Info
    fputcsv($output, ['LAPORAN INVENTORY ALAT OLAHRAGA - SPORTORY']);
    fputcsv($output, ['Tanggal Export', date('d F Y, H:i:s')]);
    fputcsv($output, ['Di-export oleh', $nama_user . ' (' . ucfirst($role) . ')']);
    fputcsv($output, []);
    
    // Header Tabel
    fputcsv($output, ['No', 'Nama Alat', 'Kategori', 'Jumlah Total', 'Jumlah Baik', 'Jumlah Rusak', 'Kondisi', 'Lokasi Penyimpanan', 'Tanggal Masuk', 'Keterangan']);
    
    // Data
    mysqli_data_seek($result, 0);
    $no = 1;
    
    while ($row = $result->fetch_assoc()) {
        fputcsv($output, [
            $no++,
            $row['nama_alat'],
            $row['kategori'],
            $row['jumlah'],
            $row['jumlah_baik'],
            $row['jumlah_rusak'],
            $row['kondisi'],
            $row['lokasi_penyimpanan'],
            date('d/m/Y', strtotime($row['tanggal_masuk'])),
            $row['keterangan'] ?? '-'
        ]);
    }
    
    fclose($output);
    exit;
}

function getStatistics($result) {
    mysqli_data_seek($result, 0);
    
    $stats = [
        'total_jenis' => 0,
        'total_item' => 0,
        'kondisi_baik' => 0,
        'kondisi_rusak' => 0
    ];
    
    while ($row = $result->fetch_assoc()) {
        $stats['total_jenis']++;
        $stats['total_item'] += $row['jumlah'];
        $stats['kondisi_baik'] += $row['jumlah_baik'];
        $stats['kondisi_rusak'] += $row['jumlah_rusak'];
    }
    
    return $stats;
}
?>