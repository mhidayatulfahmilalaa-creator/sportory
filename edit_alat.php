<?php
require_once 'config.php';

// Cek apakah user sudah login
if (!isset($_SESSION['user_id'])) {
    redirect('login.php');
}

// Cek apakah ada parameter id
if (!isset($_GET['id'])) {
    redirect('dashboard.php');
}

$id = $_GET['id'];

// Ambil data alat berdasarkan id
$query = "SELECT * FROM alat_olahraga WHERE id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows == 0) {
    $_SESSION['error'] = "Alat tidak ditemukan!";
    redirect('dashboard.php');
}

$alat = $result->fetch_assoc();

// Proses form ketika di-submit
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nama_alat = $_POST['nama_alat'];
    $kategori = $_POST['kategori'];
    $jumlah = $_POST['jumlah'];
    $jumlah_rusak = $_POST['jumlah_rusak'];
    $lokasi_penyimpanan = $_POST['lokasi_penyimpanan'];
    $tanggal_masuk = $_POST['tanggal_masuk'];
    $keterangan = $_POST['keterangan'];
    
    $query = "UPDATE alat_olahraga SET 
              nama_alat = ?, 
              kategori = ?, 
              jumlah = ?, 
              jumlah_rusak = ?,
              lokasi_penyimpanan = ?, 
              tanggal_masuk = ?, 
              keterangan = ? 
              WHERE id = ?";
    
    $stmt = $conn->prepare($query);
    $stmt->bind_param("ssiisssi", $nama_alat, $kategori, $jumlah, $jumlah_rusak, $lokasi_penyimpanan, $tanggal_masuk, $keterangan, $id);
    
    if ($stmt->execute()) {
        $_SESSION['success'] = "Alat olahraga berhasil diupdate!";
        redirect('dashboard.php');
    } else {
        $error = "Gagal mengupdate alat olahraga!";
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Alat - Sportory</title>
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
            --accent-orange: #f59e0b;
            --accent-orange-dark: #d97706;
            --white: #ffffff;
            --gray-50: #f9fafb;
            --gray-100: #f3f4f6;
            --gray-200: #e5e7eb;
            --gray-500: #6b7280;
            --gray-600: #4b5563;
            --gray-700: #374151;
            --gray-800: #1f2937;
            --accent-red: #ef4444;
            --accent-green: #10b981;
        }

        body {
            font-family: 'Poppins', sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 1.5rem;
        }

        .container {
            max-width: 900px;
            margin: 0 auto;
        }

        .form-wrapper {
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

        .form-header {
            background: linear-gradient(135deg, var(--accent-orange) 0%, var(--accent-orange-dark) 100%);
            color: var(--white);
            padding: 2rem;
            position: relative;
            overflow: hidden;
        }

        .form-header::before {
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

        .form-header-content {
            position: relative;
            z-index: 1;
        }

        .form-header h1 {
            font-size: 1.75rem;
            font-weight: 700;
            margin-bottom: 0.5rem;
        }

        .form-header p {
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

        .form-content {
            padding: 2rem;
        }

        .alert {
            padding: 1rem 1.25rem;
            border-radius: 0.5rem;
            margin-bottom: 1.5rem;
            display: flex;
            align-items: start;
            gap: 0.75rem;
        }

        .alert-error {
            background: rgba(239, 68, 68, 0.1);
            color: #991b1b;
            border: 1px solid rgba(239, 68, 68, 0.3);
        }

        .form-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 1.5rem;
            margin-bottom: 1.5rem;
        }

        .form-group {
            display: flex;
            flex-direction: column;
        }

        .form-group.full-width {
            grid-column: 1 / -1;
        }

        .form-label {
            margin-bottom: 0.5rem;
            color: var(--gray-700);
            font-weight: 500;
            font-size: 0.875rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .required {
            color: var(--accent-red);
            margin-left: 0.25rem;
        }

        .form-control {
            width: 100%;
            padding: 0.75rem 1rem;
            border: 2px solid var(--gray-200);
            border-radius: 0.5rem;
            font-size: 0.875rem;
            font-family: inherit;
            transition: all 0.15s;
            background: var(--white);
        }

        .form-control:focus {
            outline: none;
            border-color: var(--accent-orange);
            box-shadow: 0 0 0 3px rgba(245, 158, 11, 0.1);
        }

        .form-control::placeholder {
            color: var(--gray-500);
        }

        textarea.form-control {
            resize: vertical;
            min-height: 120px;
        }

        select.form-control {
            cursor: pointer;
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 24 24' stroke='%236b7280'%3E%3Cpath stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M19 9l-7 7-7-7'%3E%3C/path%3E%3C/svg%3E");
            background-repeat: no-repeat;
            background-position: right 0.75rem center;
            background-size: 1.25rem;
            padding-right: 2.5rem;
            appearance: none;
        }

        .form-help {
            margin-top: 0.375rem;
            font-size: 0.75rem;
            color: var(--gray-500);
        }

        .info-box {
            background: rgba(245, 158, 11, 0.05);
            border-left: 4px solid var(--accent-orange);
            padding: 1rem 1.25rem;
            border-radius: 0.5rem;
            margin-bottom: 1.5rem;
        }

        .info-box p {
            margin: 0;
            color: var(--gray-700);
            font-size: 0.875rem;
        }

        .kondisi-section {
            grid-column: 1 / -1;
            background: var(--gray-50);
            padding: 1.5rem;
            border-radius: 0.75rem;
            border: 2px dashed var(--gray-200);
        }

        .kondisi-section h3 {
            color: var(--gray-700);
            font-size: 0.95rem;
            margin-bottom: 1rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .kondisi-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 1rem;
        }

        .kondisi-input-wrapper {
            display: flex;
            flex-direction: column;
        }

        .kondisi-input-wrapper label {
            margin-bottom: 0.5rem;
            color: var(--gray-700);
            font-weight: 500;
            font-size: 0.875rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .kondisi-badge {
            padding: 4px 10px;
            border-radius: 12px;
            font-size: 11px;
            font-weight: 600;
        }

        .kondisi-badge.baik {
            background: #d1fae5;
            color: #065f46;
        }

        .kondisi-badge.rusak {
            background: #fee2e2;
            color: #991b1b;
        }

        .total-display {
            grid-column: 1 / -1;
            text-align: center;
            padding: 1rem;
            background: white;
            border-radius: 0.5rem;
            border: 2px solid var(--primary-blue);
        }

        .total-display label {
            display: block;
            font-size: 0.75rem;
            color: var(--gray-600);
            margin-bottom: 0.25rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .total-display .total-number {
            font-size: 2rem;
            font-weight: 700;
            color: var(--primary-blue);
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

        .btn-warning {
            background: var(--accent-orange);
            color: var(--white);
        }

        .btn-warning:hover {
            background: var(--accent-orange-dark);
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

        /* Responsive */
        @media (max-width: 768px) {
            body {
                padding: 1rem;
            }

            .form-header {
                padding: 1.5rem;
            }

            .form-header h1 {
                font-size: 1.5rem;
            }

            .form-content {
                padding: 1.5rem;
            }

            .form-grid {
                grid-template-columns: 1fr;
            }

            .kondisi-grid {
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
        <div class="form-wrapper">
            <div class="form-header">
                <div class="form-header-content">
                    <h1>✏️ Edit Alat Olahraga</h1>
                    <p>Update informasi alat olahraga</p>
                </div>
            </div>

            <div class="form-content">
                <a href="dashboard.php" class="back-link">
                    ← Kembali ke Dashboard
                </a>

                <div class="info-box">
                    <p><strong>📝 Edit Mode:</strong> Anda sedang mengedit data alat "<?php echo htmlspecialchars($alat['nama_alat']); ?>"</p>
                </div>

                <?php if (isset($error)): ?>
                <div class="alert alert-error">
                    <span>✗</span>
                    <div><?php echo $error; ?></div>
                </div>
                <?php endif; ?>

                <form method="POST" action="" id="editForm">
                    <div class="form-grid">
                        <div class="form-group full-width">
                            <label class="form-label">
                                Nama Alat <span class="required">*</span>
                            </label>
                            <input 
                                type="text" 
                                name="nama_alat" 
                                class="form-control" 
                                value="<?php echo htmlspecialchars($alat['nama_alat']); ?>"
                                required
                            >
                        </div>

                        <div class="form-group">
                            <label class="form-label">
                                Kategori <span class="required">*</span>
                            </label>
                            <select name="kategori" class="form-control" required>
                                <option value="">Pilih Kategori</option>
                                <option value="Bola" <?php echo $alat['kategori'] == 'Bola' ? 'selected' : ''; ?>>🏀 Bola</option>
                                <option value="Raket" <?php echo $alat['kategori'] == 'Raket' ? 'selected' : ''; ?>>🏸 Raket</option>
                                <option value="Matras" <?php echo $alat['kategori'] == 'Matras' ? 'selected' : ''; ?>>🧘 Matras</option>
                                <option value="Jaring" <?php echo $alat['kategori'] == 'Jaring' ? 'selected' : ''; ?>>🥅 Jaring</option>
                                <option value="Lainnya" <?php echo $alat['kategori'] == 'Lainnya' ? 'selected' : ''; ?>>📦 Lainnya</option>
                            </select>
                        </div>

                        <div class="form-group">
                            <label class="form-label">
                                Lokasi Penyimpanan <span class="required">*</span>
                            </label>
                            <input 
                                type="text" 
                                name="lokasi_penyimpanan" 
                                class="form-control" 
                                value="<?php echo htmlspecialchars($alat['lokasi_penyimpanan']); ?>"
                                required
                            >
                        </div>

                        <div class="form-group full-width">
                            <label class="form-label">
                                Tanggal Masuk <span class="required">*</span>
                            </label>
                            <input 
                                type="date" 
                                name="tanggal_masuk" 
                                class="form-control"
                                value="<?php echo htmlspecialchars($alat['tanggal_masuk']); ?>"
                                required
                            >
                        </div>

                        <!-- Kondisi Section -->
                        <div class="kondisi-section">
                            <h3>📊 Detail Kondisi Alat</h3>
                            <div class="kondisi-grid">
                                <div class="kondisi-input-wrapper">
                                    <label>
                                        <span class="kondisi-badge baik">✓ Kondisi Baik</span>
                                        <span class="required">*</span>
                                    </label>
                                    <input 
                                        type="number" 
                                        name="jumlah" 
                                        id="jumlah_baik"
                                        class="form-control" 
                                        min="0" 
                                        value="<?php echo htmlspecialchars($alat['jumlah']); ?>"
                                        onchange="updateTotal()"
                                        required
                                    >
                                    <span class="form-help">Jumlah alat yang masih dalam kondisi baik</span>
                                </div>

                                <div class="kondisi-input-wrapper">
                                    <label>
                                        <span class="kondisi-badge rusak">✗ Kondisi Rusak</span>
                                        <span class="required">*</span>
                                    </label>
                                    <input 
                                        type="number" 
                                        name="jumlah_rusak" 
                                        id="jumlah_rusak"
                                        class="form-control" 
                                        min="0" 
                                        value="<?php echo htmlspecialchars($alat['jumlah_rusak']); ?>"
                                        onchange="updateTotal()"
                                        required
                                    >
                                    <span class="form-help">Jumlah alat yang rusak atau tidak dapat digunakan</span>
                                </div>

                                <div class="total-display">
                                    <label>Total Keseluruhan</label>
                                    <div class="total-number" id="total_display">
                                        <?php echo $alat['jumlah'] + $alat['jumlah_rusak']; ?>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="form-group full-width">
                            <label class="form-label">
                                Keterangan
                            </label>
                            <textarea 
                                name="keterangan" 
                                class="form-control"
                                placeholder="Tambahkan catatan atau keterangan tambahan..."
                            ><?php echo htmlspecialchars($alat['keterangan']); ?></textarea>
                            <span class="form-help">Informasi tambahan tentang alat (opsional)</span>
                        </div>
                    </div>

                    <div class="btn-group">
                        <button type="submit" class="btn btn-warning">
                            💾 Update Alat
                        </button>
                        <button type="button" class="btn btn-secondary" onclick="window.location.href='dashboard.php'">
                            ✗ Batal
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        function updateTotal() {
            const jumlahBaik = parseInt(document.getElementById('jumlah_baik').value) || 0;
            const jumlahRusak = parseInt(document.getElementById('jumlah_rusak').value) || 0;
            const total = jumlahBaik + jumlahRusak;
            
            document.getElementById('total_display').textContent = total;
        }

        // Initialize total on page load
        document.addEventListener('DOMContentLoaded', function() {
            updateTotal();
        });
    </script>
</body>
</html>