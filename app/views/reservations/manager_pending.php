<?php $title = 'Pending Approvals'; ?>
<?php include_once __DIR__ . '/../layouts/dashboard_header.php'; ?>

<div class="top-bar">
    <h1>📋 Reservasi Menunggu Approval</h1>
    <p>Review dan setujui reservasi yang dibuat oleh Staff</p>
</div>

<?php if (empty($reservations)): ?>
    <div class="content-card">
        <p style="text-align: center; color: #999; padding: 40px;">
            ✅ Tidak ada reservasi yang menunggu approval
        </p>
    </div>
<?php else: ?>
    <div class="content-card">
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Customer</th>
                    <th>Lapangan</th>
                    <th>Waktu</th>
                    <th>Dibuat Oleh</th>
                    <th>Catatan</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($reservations as $r): ?>
                <tr>
                    <td><?= $r['id'] ?></td>
                    <td><?= htmlspecialchars($r['customer_name']) ?></td>
                    <td><?= htmlspecialchars($r['court_name']) ?></td>
                    <td>
                        <?= DateHelper::format($r['start_time'], 'd M Y H:i') ?>
                        <br>
                        <small style="color: #666;">s/d <?= DateHelper::format($r['end_time'], 'H:i') ?></small>
                    </td>
                    <td><?= htmlspecialchars($r['created_by_name'] ?? 'N/A') ?></td>
                    <td>
                        <small><?= htmlspecialchars(substr($r['notes'] ?? '-', 0, 50)) ?></small>
                    </td>
                    <td>
                        <div style="display: flex; gap: 5px; flex-wrap: wrap;">
                            <!-- Approve Button -->
                            <form method="POST" action="<?= $base_url ?>?c=reservation&a=approve&id=<?= $r['id'] ?>" 
                                  style="display: inline;">
                                <input type="hidden" name="csrf_token" value="<?= $csrf ?>">
                                <input type="hidden" name="notes" value="Disetujui">
                                <button type="submit" 
                                        style="padding: 6px 12px; background: #28a745; color: white; border: none; border-radius: 4px; cursor: pointer; font-size: 0.85rem;"
                                        onclick="return confirm('Setujui reservasi ini?')">
                                    ✅ Setujui
                                </button>
                            </form>

                            <!-- Request Reschedule Button -->
                            <button onclick="showRescheduleModal(<?= $r['id'] ?>)"
                                    style="padding: 6px 12px; background: #ffc107; color: #333; border: none; border-radius: 4px; cursor: pointer; font-size: 0.85rem;">
                                📅 Reschedule
                            </button>

                            <!-- Reject Button -->
                            <button onclick="showRejectModal(<?= $r['id'] ?>)"
                                    style="padding: 6px 12px; background: #dc3545; color: white; border: none; border-radius: 4px; cursor: pointer; font-size: 0.85rem;">
                                ❌ Tolak
                            </button>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
<?php endif; ?>

<!-- Modal Reschedule -->
<div id="rescheduleModal" style="display: none; position: fixed; top: 0; left: 0; right: 0; bottom: 0; background: rgba(0,0,0,0.5); z-index: 1000; align-items: center; justify-content: center;">
    <div style="background: white; padding: 30px; border-radius: 10px; max-width: 500px; width: 90%;">
        <h3 style="margin-top: 0;">📅 Request Reschedule</h3>
        <form id="rescheduleForm" method="POST">
            <input type="hidden" name="csrf_token" value="<?= $csrf ?>">
            <div style="margin-bottom: 15px;">
                <label style="display: block; margin-bottom: 5px; font-weight: 600;">Alasan Reschedule:</label>
                <textarea name="reason" 
                          style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 5px; min-height: 100px;"
                          placeholder="Contoh: Lapangan sedang maintenance, mohon pilih waktu lain"
                          required></textarea>
            </div>
            <div style="display: flex; gap: 10px; justify-content: flex-end;">
                <button type="button" onclick="closeRescheduleModal()"
                        style="padding: 10px 20px; background: #999; color: white; border: none; border-radius: 5px; cursor: pointer;">
                    Batal
                </button>
                <button type="submit"
                        style="padding: 10px 20px; background: #ffc107; color: #333; border: none; border-radius: 5px; cursor: pointer;">
                    Kirim Request
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Modal Reject -->
<div id="rejectModal" style="display: none; position: fixed; top: 0; left: 0; right: 0; bottom: 0; background: rgba(0,0,0,0.5); z-index: 1000; align-items: center; justify-content: center;">
    <div style="background: white; padding: 30px; border-radius: 10px; max-width: 500px; width: 90%;">
        <h3 style="margin-top: 0; color: #dc3545;">❌ Tolak Reservasi</h3>
        <form id="rejectForm" method="POST">
            <input type="hidden" name="csrf_token" value="<?= $csrf ?>">
            <div style="margin-bottom: 15px;">
                <label style="display: block; margin-bottom: 5px; font-weight: 600;">Alasan Penolakan:</label>
                <textarea name="reason" 
                          style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 5px; min-height: 100px;"
                          placeholder="Contoh: Waktu sudah terisi, lapangan tidak tersedia"
                          required></textarea>
            </div>
            <div style="display: flex; gap: 10px; justify-content: flex-end;">
                <button type="button" onclick="closeRejectModal()"
                        style="padding: 10px 20px; background: #999; color: white; border: none; border-radius: 5px; cursor: pointer;">
                    Batal
                </button>
                <button type="submit"
                        style="padding: 10px 20px; background: #dc3545; color: white; border: none; border-radius: 5px; cursor: pointer;">
                    Tolak Reservasi
                </button>
            </div>
        </form>
    </div>
</div>

<script>
function showRescheduleModal(id) {
    const modal = document.getElementById('rescheduleModal');
    const form = document.getElementById('rescheduleForm');
    form.action = '<?= $base_url ?>?c=reservation&a=requestReschedule&id=' + id;
    modal.style.display = 'flex';
}

function closeRescheduleModal() {
    document.getElementById('rescheduleModal').style.display = 'none';
}

function showRejectModal(id) {
    const modal = document.getElementById('rejectModal');
    const form = document.getElementById('rejectForm');
    form.action = '<?= $base_url ?>?c=reservation&a=reject&id=' + id;
    modal.style.display = 'flex';
}

function closeRejectModal() {
    document.getElementById('rejectModal').style.display = 'none';
}

// Close modal when clicking outside
document.getElementById('rescheduleModal').addEventListener('click', function(e) {
    if (e.target === this) closeRescheduleModal();
});

document.getElementById('rejectModal').addEventListener('click', function(e) {
    if (e.target === this) closeRejectModal();
});
</script>

<?php include_once __DIR__ . '/../layouts/dashboard_footer.php'; ?>