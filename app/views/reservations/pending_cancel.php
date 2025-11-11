<?php $title = 'Pending Cancellations'; ?>
<?php include_once __DIR__ . '/../layouts/dashboard_header.php'; ?>

<div class="top-bar">
    <h1>🗑️ Permintaan Pembatalan</h1>
    <p>Review permintaan pembatalan reservasi dari Staff</p>
</div>

<?php if (empty($reservations)): ?>
    <div class="content-card">
        <p style="text-align: center; color: #999; padding: 40px;">
            ✅ Tidak ada permintaan pembatalan
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
                    <th>Alasan Pembatalan</th>
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
                    <td>
                        <div style="padding: 10px; background: #fff3cd; border-left: 3px solid #ffc107; border-radius: 4px;">
                            <?= htmlspecialchars($r['cancellation_reason'] ?? 'Tidak ada keterangan') ?>
                        </div>
                    </td>
                    <td>
                        <div style="display: flex; gap: 5px; flex-direction: column;">
                            <!-- Approve Cancel -->
                            <form method="POST" action="<?= $base_url ?>?c=reservation&a=approveCancel&id=<?= $r['id'] ?>" 
                                  style="display: inline;">
                                <input type="hidden" name="csrf_token" value="<?= $csrf ?>">
                                <input type="hidden" name="notes" value="Pembatalan disetujui">
                                <button type="submit" 
                                        style="width: 100%; padding: 8px 12px; background: #28a745; color: white; border: none; border-radius: 4px; cursor: pointer; font-size: 0.85rem;"
                                        onclick="return confirm('Setujui pembatalan reservasi ini?')">
                                    ✅ Setujui Pembatalan
                                </button>
                            </form>

                            <!-- View Details -->
                            <a href="<?= $base_url ?>?c=reservation&a=edit&id=<?= $r['id'] ?>"
                               style="width: 100%; padding: 8px 12px; background: #007bff; color: white; text-align: center; text-decoration: none; border-radius: 4px; font-size: 0.85rem; display: block;">
                                👁️ Lihat Detail
                            </a>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
<?php endif; ?>

<?php include_once __DIR__ . '/../layouts/dashboard_footer.php'; ?>