<?php include_once __DIR__ . '/../layouts/header.php'; ?>

<h2>Daftar Lapangan</h2>

<form method="POST" action="<?= $base_url ?>?c=court&a=store">
    <input type="hidden" name="csrf_token" value="<?= $csrf ?>">
    
    <label>Nama Lapangan</label>
    <input type="text" name="name" required placeholder="Contoh: Lapangan A">
    
    <label>Tipe</label>
    <input type="text" name="type" required placeholder="Contoh: Sintetis">
    
    <button class="btn" type="submit">Tambah</button>
</form>

<table>
    <thead>
        <tr>
            <th>ID</th>
            <th>Nama Lapangan</th>
            <th>Tipe</th>
            <th>Aksi</th>
        </tr>
    </thead>
    <tbody>
        <?php if (empty($courts)): ?>
        <tr>
            <td colspan="4" style="text-align:center;">Tidak ada data lapangan.</td>
        </tr>
        <?php else: ?>
            <?php foreach ($courts as $c): ?>
            <tr>
                <td><?= $c['id'] ?></td>
                <td><?= htmlspecialchars($c['name']) ?></td>
                <td><?= htmlspecialchars($c['type']) ?></td>
                <td>
                    <a class="btn btn-danger" 
                       href="<?= $base_url ?>?c=court&a=delete&id=<?= $c['id'] ?>"
                       onclick="return confirm('Yakin ingin menghapus lapangan ini?')">Hapus</a>
                </td>
            </tr>
            <?php endforeach; ?>
        <?php endif; ?>
    </tbody>
</table>

<?php include_once __DIR__ . '/../layouts/footer.php'; ?>