<?php

require_once __DIR__ . '/includes/admin.php';

require_admin();

$logs = $conn->query(
    "SELECT l.*, u.fullname AS account_name, u.email AS account_email
     FROM assistant_logs l
     LEFT JOIN users u ON l.user_id = u.id
     ORDER BY l.created_at DESC
     LIMIT 150"
);

admin_render_shell_start('AI Logs', 'assistant');
?>
        <div class="admin-hero">
          <div>
            <p class="eyebrow">AI assistant</p>
            <h1>Assistant logs</h1>
            <p>Review recent symptom guidance exchanges saved from the assistant.</p>
          </div>
        </div>

        <section class="admin-card">
          <div class="admin-table-wrap">
            <table class="admin-table">
              <thead>
                <tr>
                  <th>User</th>
                  <th>Patient Message</th>
                  <th>Assistant Reply</th>
                  <th>Time</th>
                </tr>
              </thead>
              <tbody>
                <?php if ($logs->num_rows): ?>
                  <?php while ($log = $logs->fetch_assoc()): ?>
                    <tr>
                      <td>
                        <?php if ($log['account_name']): ?>
                          <strong><?= e($log['account_name']) ?></strong>
                          <span><?= e($log['account_email']) ?></span>
                        <?php else: ?>
                          <strong>Guest session</strong>
                          <span><?= e(substr($log['session_token'], 0, 12)) ?></span>
                        <?php endif; ?>
                      </td>
                      <td><?= e($log['user_message']) ?></td>
                      <td><?= e($log['bot_reply']) ?></td>
                      <td><?= e(admin_format_datetime($log['created_at'])) ?></td>
                    </tr>
                  <?php endwhile; ?>
                <?php else: ?>
                  <tr><td colspan="4">No assistant logs yet.</td></tr>
                <?php endif; ?>
              </tbody>
            </table>
          </div>
        </section>
<?php admin_render_shell_end(); ?>
