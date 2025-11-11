<?php include_once __DIR__ . '/../layouts/header.php'; ?>

<h2>Tambah Reservasi Baru</h2>

<form method="POST" action="<?= $base_url ?>?c=reservation&a=store">
    <input type="hidden" name="csrf_token" value="<?= $csrf ?>">

    <label>Nama Pelanggan</label>
    <input type="text" name="customer_name" required>

    <label>Lapangan</label>
    <select name="court_id" required>
        <option value="">-- Pilih Lapangan --</option>
        <?php foreach ($courts as $c): ?>
            <option value="<?= $c['id'] ?>"><?= htmlspecialchars($c['name']) ?></option>
        <?php endforeach; ?>
    </select>

    <label>Waktu Mulai</label>
    <input type="datetime-local" name="start_time" required>

    <label>Waktu Selesai</label>
    <input type="datetime-local" name="end_time" required>

    <label>Status</label>
    <select name="status">
        <option value="aktif">Aktif</option>
        <option value="selesai">Selesai</option>
    </select>

    <button class="btn" type="submit">Simpan</button>
    <a href="<?= $base_url ?>?c=reservation&a=index" class="btn btn-secondary">Batal</a>
</form>

<?php include_once __DIR__ . '/../layouts/footer.php'; ?>