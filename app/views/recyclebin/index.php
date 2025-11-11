<?php include_once __DIR__ . '/../layouts/header.php'; ?>

<h2>Recycle Bin</h2>

<form method="POST" id="restoreForm" action="<?= $base_url ?>?c=recyclebin&a=restoreBulk">
    <input type="hidden" name="csrf_token" value="<?= $csrf ?>">

    <table>
        <thead>
            <tr>
                <th><input type="checkbox" id="checkAll"></th>
                <th>ID</th>
                <th>Nama</th>
                <th>Lapangan</th>
                <th>Dihapus Pada</th>
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($trashed)): ?>
            <tr>
                <td colspan="6" style="text-align:center;">Tidak ada data di Recycle Bin.</td>
            </tr>
            <?php else: ?>
                <?php foreach ($trashed as $r): ?>
                <tr>
                    <td><input type="checkbox" name="selected[]" value="<?= $r['id'] ?>" class="item-checkbox"></td>
                    <td><?= $r['id'] ?></td>
                    <td><?= htmlspecialchars($r['customer_name']) ?></td>
                    <td><?= htmlspecialchars($r['court_name']) ?></td>
                    <td><?= DateHelper::diffHuman($r['deleted_at']) ?></td>
                    <td>
                        <a class="btn" href="<?= $base_url ?>?c=recyclebin&a=restore&id=<?= $r['id'] ?>">Restore</a>
                        <a class="btn btn-danger" 
                           href="<?= $base_url ?>?c=recyclebin&a=destroy&id=<?= $r['id'] ?>"
                           onclick="return confirm('Hapus permanen? Data tidak bisa dikembalikan!')">Hapus Permanen</a>
                    </td>
                </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>

    <div style="margin-top:15px;">
        <button class="btn" type="submit">Restore Selected</button>
    </div>
</form>

<form method="POST" id="deleteForm" action="<?= $base_url ?>?c=recyclebin&a=deleteBulk" style="display:inline;">
    <input type="hidden" name="csrf_token" value="<?= $csrf ?>">
    <input type="hidden" name="selected" id="deleteSelected">
    <button class="btn btn-danger" type="button" onclick="submitDeleteForm()">Delete Selected</button>
</form>

<a href="<?= $base_url ?>?c=recyclebin&a=autoDelete" 
   class="btn btn-secondary" 
   onclick="return confirm('Auto delete data >30 hari?')"
   style="margin-left:10px;">Auto Delete (>30 hari)</a>

<script>
document.getElementById('checkAll').addEventListener('change', function(){
    let boxes = document.querySelectorAll('.item-checkbox');
    boxes.forEach(b => b.checked = this.checked);
});

function submitDeleteForm() {
    let selected = [];
    document.querySelectorAll('.item-checkbox:checked').forEach(cb => {
        selected.push(cb.value);
    });
    
    if (selected.length === 0) {
        alert('Pilih minimal 1 data!');
        return;
    }
    
    if (!confirm('Hapus permanen ' + selected.length + ' data? Tidak bisa dikembalikan!')) {
        return;
    }

    let form = document.getElementById('deleteForm');
    form.innerHTML = '<input type="hidden" name="csrf_token" value="<?= $csrf ?>">';
    
    selected.forEach(id => {
        let input = document.createElement('input');
        input.type = 'hidden';
        input.name = 'selected[]';
        input.value = id;
        form.appendChild(input);
    });
    
    form.submit();
}
</script>

<?php include_once __DIR__ . '/../layouts/footer.php'; ?>