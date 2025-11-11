<?php $title = 'Edit Reservasi'; ?>
<?php include_once __DIR__ . '/../layouts/dashboard_header.php'; ?>

<div class="top-bar">
    <h1>✏️ Edit Reservasi #<?= $reservation['id'] ?></h1>
    <div style="margin-top: 10px;">
        <span class="badge" style="padding: 6px 12px; border-radius: 4px; font-size: 0.9rem;
              background: <?= match($reservation['workflow_status']) {
                  'draft' => '#6c757d',
                  'pending_manager' => '#ffc107',
                  'approved' => '#28a745',
                  'pending_reschedule' => '#ff851b',
                  'pending_cancel' => '#dc3545',
                  'rejected' => '#dc3545',
                  'canceled' => '#6c757d',
                  default => '#6c757d'
              } ?>; color: white;">
            <?= strtoupper(str_replace('_', ' ', $reservation['workflow_status'])) ?>
        </span>
    </div>
</div>

<div style="display: grid; grid-template-columns: 2fr 1fr; gap: 20px;">
    <!-- Form Edit -->
    <div class="content-card">
        <h3>📝 Detail Reservasi</h3>

        <?php if (in_array($reservation['workflow_status'], ['draft', 'pending_reschedule', 'pending_manager'])): ?>
        <form method="POST" action="<?= $base_url ?>?c=reservation&a=update&id=<?= $reservation['id'] ?>">
            <input type="hidden" name="csrf_token" value="<?= $csrf ?>">

            <div class="form-group">
                <label>Nama Pelanggan *</label>
                <input type="text" name="customer_name" 
                       value="<?= htmlspecialchars($reservation['customer_name']) ?>" 
                       required>
            </div>

            <div class="form-group">
                <label>Lapangan *</label>
                <select name="court_id" required>
                    <?php foreach ($courts as $c): ?>
                        <option value="<?= $c['id'] ?>" 
                                <?= $c['id'] == $reservation['court_id'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($c['name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-group">
                <label>Waktu Mulai *</label>
                <input type="datetime-local" name="start_time" 
                       value="<?= date('Y-m-d\TH:i', strtotime($reservation['start_time'])) ?>" 
                       required>
            </div>

            <div class="form-group">
                <label>Waktu Selesai *</label>
                <input type="datetime-local" name="end_time" 
                       value="<?= date('Y-m-d\TH:i', strtotime($reservation['end_time'])) ?>" 
                       required>
            </div>

            <div class="form-group">
                <label>Catatan</label>
                <textarea name="notes" rows="3"><?= htmlspecialchars($reservation['notes'] ?? '') ?></textarea>
            </div>

            <div style="display: flex; gap: 10px; margin-top: 20px;">
                <button type="submit" 
                        style="padding: 10px 20px; background: #667eea; color: white; border: none; border-radius: 5px; cursor: pointer;">
                    💾 Simpan Perubahan
                </button>
                <a href="<?= $base_url ?>?c=reservation&a=index" 
                   style="padding: 10px 20px; background: #999; color: white; text-decoration: none; border-radius: 5px; display: inline-block;">
                    ← Kembali
                </a>
            </div>
        </form>
        <?php else: ?>
        <!-- Read Only View -->
        <div style="background: #f8f9fa; padding: 20px; border-radius: 8px;">
            <p><strong>Nama Pelanggan:</strong> <?= htmlspecialchars($reservation['customer_name']) ?></p>
            <p><strong>Lapangan:</strong> <?= htmlspecialchars($reservation['court_name']) ?></p>
            <p><strong>Waktu Mulai:</strong> <?= DateHelper::format($reservation['start_time'], 'd M Y H:i') ?></p>
            <p><strong>Waktu Selesai:</strong> <?= DateHelper::format($reservation['end_time'], 'd M Y H:i') ?></p>
            <p><strong>Catatan:</strong> <?= htmlspecialchars($reservation['notes'] ?? '-') ?></p>
        </div>
        
        <div style="margin-top: 20px;">
            <a href="<?= $base_url ?>?c=reservation&a=index" 
               style="padding: 10px 20px; background: #999; color: white; text-decoration: none; border-radius: 5px; display: inline-block;">
                ← Kembali
            </a>
        </div>
        <?php endif; ?>

        <!-- Rejection/Cancellation Reason -->
        <?php if (!empty($reservation['rejection_reason'])): ?>
        <div style="margin-top: 20px; padding: 15px; background: #fff3cd; border-left: 4px solid #ffc107; border-radius: 4px;">
            <strong>⚠️ Alasan Reschedule/Penolakan:</strong>
            <p style="margin: 10px 0 0 0;"><?= nl2br(htmlspecialchars($reservation['rejection_reason'])) ?></p>
        </div>
        <?php endif; ?>

        <?php if (!empty($reservation['cancellation_reason'])): ?>
        <div style="margin-top: 20px; padding: 15px; background: #f8d7da; border-left: 4px solid #dc3545; border-radius: 4px;">
            <strong>🗑️ Alasan Pembatalan:</strong>
            <p style="margin: 10px 0 0 0;"><?= nl2br(htmlspecialchars($reservation['cancellation_reason'])) ?></p>
        </div>
        <?php endif; ?>
    </div>

    <!-- Sidebar: Actions & Activity -->
    <div>
        <!-- Workflow Actions -->
        <?php $auth = Auth::getInstance(); ?>
        
        <?php if ($auth->hasRole('manager') || $auth->hasRole('admin')): ?>
            <?php if ($reservation['workflow_status'] == 'pending_manager'): ?>
            <div class="content-card" style="margin-bottom: 20px;">
                <h4>⚡ Manager Actions</h4>
                
                <form method="POST" action="<?= $base_url ?>?c=reservation&a=approve&id=<?= $reservation['id'] ?>" style="margin-bottom: 10px;">
                    <input type="hidden" name="csrf_token" value="<?= $csrf ?>">
                    <button type="submit" onclick="return confirm('Setujui reservasi ini?')"
                            style="width: 100%; padding: 10px; background: #28a745; color: white; border: none; border-radius: 5px; cursor: pointer;">
                        ✅ Setujui Reservasi
                    </button>
                </form>

                <button onclick="showRescheduleModal()"
                        style="width: 100%; padding: 10px; background: #ffc107; color: #333; border: none; border-radius: 5px; cursor: pointer; margin-bottom: 10px;">
                    📅 Request Reschedule
                </button>

                <button onclick="showRejectModal()"
                        style="width: 100%; padding: 10px; background: #dc3545; color: white; border: none; border-radius: 5px; cursor: pointer;">
                    ❌ Tolak Reservasi
                </button>
            </div>
            <?php endif; ?>

            <?php if ($reservation['workflow_status'] == 'pending_cancel'): ?>
            <div class="content-card" style="margin-bottom: 20px;">
                <h4>⚡ Manager Actions</h4>
                
                <form method="POST" action="<?= $base_url ?>?c=reservation&a=approveCancel&id=<?= $reservation['id'] ?>">
                    <input type="hidden" name="csrf_token" value="<?= $csrf ?>">
                    <button type="submit" onclick="return confirm('Setujui pembatalan reservasi ini?')"
                            style="width: 100%; padding: 10px; background: #28a745; color: white; border: none; border-radius: 5px; cursor: pointer;">
                        ✅ Setujui Pembatalan
                    </button>
                </form>
            </div>
            <?php endif; ?>
        <?php endif; ?>

        <!-- Staff: Request Cancel -->
        <?php if ($reservation['workflow_status'] == 'approved' && !$auth->hasRole('admin')): ?>
        <div class="content-card" style="margin-bottom: 20px;">
            <h4>⚡ Actions</h4>
            <button onclick="showCancelModal()"
                    style="width: 100%; padding: 10px; background: #dc3545; color: white; border: none; border-radius: 5px; cursor: pointer;">
                🗑️ Request Pembatalan
            </button>
        </div>
        <?php endif; ?>

        <!-- Activity Log -->
        <div class="content-card">
            <h4>📊 Activity Log</h4>
            <?php if (empty($activities)): ?>
                <p style="color: #999; font-size: 0.9rem;">Belum ada aktivitas</p>
            <?php else: ?>
                <div style="max-height: 400px; overflow-y: auto;">
                    <?php foreach ($activities as $activity): ?>
                    <div style="padding: 10px; background: #f8f9fa; margin-bottom: 8px; border-radius: 4px; border-left: 3px solid #667eea;">
                        <div style="font-weight: 600; font-size: 0.85rem; color: #333;">
                            <?= htmlspecialchars($activity['full_name']) ?>
                            <span style="background: #e3f2fd; padding: 2px 6px; border-radius: 3px; font-size: 0.75rem; margin-left: 5px;">
                                <?= htmlspecialchars($activity['role']) ?>
                            </span>
                        </div>
                        <div style="font-size: 0.8rem; color: #666; margin: 5px 0;">
                            <?= htmlspecialchars($activity['action']) ?>
                            <?php if ($activity['old_status'] && $activity['new_status']): ?>
                                : <?= htmlspecialchars($activity['old_status']) ?> → <?= htmlspecialchars($activity['new_status']) ?>
                            <?php endif; ?>
                        </div>
                        <?php if ($activity['notes']): ?>
                        <div style="font-size: 0.8rem; color: #666; font-style: italic;">
                            "<?= htmlspecialchars($activity['notes']) ?>"
                        </div>
                        <?php endif; ?>
                        <div style="font-size: 0.75rem; color: #999; margin-top: 5px;">
                            <?= DateHelper::diffHuman($activity['created_at']) ?>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Modal Reschedule (Manager) -->
<div id="rescheduleModal" style="display: none; position: fixed; top: 0; left: 0; right: 0; bottom: 0; background: rgba(0,0,0,0.5); z-index: 1000; align-items: center; justify-content: center;">
    <div style="background: white; padding: 30px; border-radius: 10px; max-width: 500px; width: 90%;">
        <h3 style="margin-top: 0;">📅 Request Reschedule</h3>
        <form method="POST" action="<?= $base_url ?>?c=reservation&a=requestReschedule&id=<?= $reservation['id'] ?>">
            <input type="hidden" name="csrf_token" value="<?= $csrf ?>">
            <textarea name="reason" style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 5px; min-height: 100px;" 
                      placeholder="Alasan permintaan reschedule..." required></textarea>
            <div style="display: flex; gap: 10px; justify-content: flex-end; margin-top: 15px;">
                <button type="button" onclick="document.getElementById('rescheduleModal').style.display='none'"
                        style="padding: 10px 20px; background: #999; color: white; border: none; border-radius: 5px;">Batal</button>
                <button type="submit" style="padding: 10px 20px; background: #ffc107; color: #333; border: none; border-radius: 5px;">Kirim</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal Reject (Manager) -->
<div id="rejectModal" style="display: none; position: fixed; top: 0; left: 0; right: 0; bottom: 0; background: rgba(0,0,0,0.5); z-index: 1000; align-items: center; justify-content: center;">
    <div style="background: white; padding: 30px; border-radius: 10px; max-width: 500px; width: 90%;">
        <h3 style="margin-top: 0; color: #dc3545;">❌ Tolak Reservasi</h3>
        <form method="POST" action="<?= $base_url ?>?c=reservation&a=reject&id=<?= $reservation['id'] ?>">
            <input type="hidden" name="csrf_token" value="<?= $csrf ?>">
            <textarea name="reason" style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 5px; min-height: 100px;" 
                      placeholder="Alasan penolakan..." required></textarea>
            <div style="display: flex; gap: 10px; justify-content: flex-end; margin-top: 15px;">
                <button type="button" onclick="document.getElementById('rejectModal').style.display='none'"
                        style="padding: 10px 20px; background: #999; color: white; border: none; border-radius: 5px;">Batal</button>
                <button type="submit" style="padding: 10px 20px; background: #dc3545; color: white; border: none; border-radius: 5px;">Tolak</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal Cancel (Staff) -->
<div id="cancelModal" style="display: none; position: fixed; top: 0; left: 0; right: 0; bottom: 0; background: rgba(0,0,0,0.5); z-index: 1000; align-items: center; justify-content: center;">
    <div style="background: white; padding: 30px; border-radius: 10px; max-width: 500px; width: 90%;">
        <h3 style="margin-top: 0; color: #dc3545;">🗑️ Request Pembatalan</h3>
        <form method="POST" action="<?= $base_url ?>?c=reservation&a=requestCancel&id=<?= $reservation['id'] ?>">
            <input type="hidden" name="csrf_token" value="<?= $csrf ?>">
            <textarea name="cancellation_reason" style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 5px; min-height: 100px;" 
                      placeholder="Alasan pembatalan..." required></textarea>
            <div style="display: flex; gap: 10px; justify-content: flex-end; margin-top: 15px;">
                <button type="button" onclick="document.getElementById('cancelModal').style.display='none'"
                        style="padding: 10px 20px; background: #999; color: white; border: none; border-radius: 5px;">Batal</button>
                <button type="submit" style="padding: 10px 20px; background: #dc3545; color: white; border: none; border-radius: 5px;">Kirim Request</button>
            </div>
        </form>
    </div>
</div>

<script>
function showRescheduleModal() {
    document.getElementById('rescheduleModal').style.display = 'flex';
}
function showRejectModal() {
    document.getElementById('rejectModal').style.display = 'flex';
}
function showCancelModal() {
    document.getElementById('cancelModal').style.display = 'flex';
}
</script>

<style>
.form-group {
    margin-bottom: 20px;
}
.form-group label {
    display: block;
    margin-bottom: 8px;
    color: #333;
    font-weight: 600;
}
.form-group input, .form-group select, .form-group textarea {
    width: 100%;
    padding: 10px 15px;
    border: 2px solid #e0e0e0;
    border-radius: 5px;
    font-size: 1rem;
}
.form-group input:focus, .form-group select:focus, .form-group textarea:focus {
    outline: none;
    border-color: #667eea;
}
</style>

<?php include_once __DIR__ . '/../layouts/dashboard_footer.php'; ?>