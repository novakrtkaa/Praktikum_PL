<?php include_once __DIR__ . '/../layouts/header.php'; ?>

<h2>Daftar Reservasi</h2>

<form method="GET" action="">
    <input type="hidden" name="c" value="reservation">
    <input type="hidden" name="a" value="index">
    <input type="text" name="search" placeholder="Cari nama / lapangan..." value="<?= htmlspecialchars($search) ?>">
    <button class="btn" type="submit">Cari</button>
    <a href="<?= $base_url ?>?c=reservation&a=create" class="btn">+ Tambah Reservasi</a>
</form>

<table>
    <thead>
        <tr>
            <th><a href="?c=reservation&a=index&sort=id">ID</a></th>
            <th>Nama Pelanggan</th>
            <th>Lapangan</th>
            <th>Mulai</th>
            <th>Selesai</th>
            <th>Status</th>
            <th>Aksi</th>
        </tr>
    </thead>
    <tbody>
        <?php if (empty($reservations)): ?>
        <tr>
            <td colspan="7" style="text-align:center;">Tidak ada data reservasi.</td>
        </tr>
        <?php else: ?>
            <?php foreach ($reservations as $r): ?>
            <tr>
                <td><?= $r['id'] ?></td>
                <td><?= htmlspecialchars($r['customer_name']) ?></td>
                <td><?= htmlspecialchars($r['court_name']) ?></td>
                <td><?= DateHelper::format($r['start_time']) ?></td>
                <td><?= DateHelper::format($r['end_time']) ?></td>
                <td><?= htmlspecialchars($r['status']) ?></td>
                <td>
                    <a class="btn" href="<?= $base_url ?>?c=reservation&a=edit&id=<?= $r['id'] ?>">Edit</a>
                    <a class="btn btn-danger" href="<?= $base_url ?>?c=reservation&a=delete&id=<?= $r['id'] ?>" onclick="return confirm('Yakin ingin menghapus?')">Hapus</a>
                </td>
            </tr>
            <?php endforeach; ?>
        <?php endif; ?>
    </tbody>
</table>

<div class="pagination">
    <?php for ($i = 1; $i <= $pages; $i++): ?>
        <a href="?c=reservation&a=index&page=<?= $i ?>&search=<?= urlencode($search) ?>" 
           class="<?= $page == $i ? 'active' : '' ?>"><?= $i ?></a>
    <?php endfor; ?>
</div>

<?php include_once __DIR__ . '/../layouts/footer.php'; ?>