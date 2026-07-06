<?php

require_once __DIR__ . '/includes/admin.php';

require_admin();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $messageId = (int) ($_POST['message_id'] ?? 0);
    $isRead = (int) ($_POST['is_read'] ?? 0);

    if ($messageId > 0 && in_array($isRead, [0, 1], true)) {
        try {
            $stmt = $conn->prepare("UPDATE contact_messages SET is_read = ? WHERE id = ?");
            $stmt->bind_param("ii", $isRead, $messageId);
            $stmt->execute();
            $stmt->close();
            admin_flash('success', 'Message status updated.');
        } catch (Throwable $exception) {
            error_log($exception->getMessage());
            admin_flash('error', 'Message status could not be updated.');
        }
    } else {
        admin_flash('error', 'Choose a valid message status.');
    }

    header("Location: messages.php");
    exit;
}

$messages = $conn->query(
    "SELECT m.*, u.fullname AS account_name
     FROM contact_messages m
     LEFT JOIN users u ON m.user_id = u.id
     ORDER BY m.is_read ASC, m.created_at DESC"
);

admin_render_shell_start('Messages', 'messages');
?>
        <div class="admin-hero">
          <div>
            <p class="eyebrow">Support</p>
            <h1>Contact messages</h1>
            <p>Review support messages submitted through the public contact form.</p>
          </div>
        </div>

        <section class="admin-card">
          <div class="admin-table-wrap">
            <table class="admin-table">
              <thead>
                <tr>
                  <th>Sender</th>
                  <th>Message</th>
                  <th>Received</th>
                  <th>Status</th>
                </tr>
              </thead>
              <tbody>
                <?php if ($messages->num_rows): ?>
                  <?php while ($message = $messages->fetch_assoc()): ?>
                    <tr>
                      <td>
                        <strong><?= e($message['fullname']) ?></strong>
                        <span><?= e($message['email']) ?></span>
                        <?php if ($message['account_name']): ?>
                          <span>Account: <?= e($message['account_name']) ?></span>
                        <?php endif; ?>
                      </td>
                      <td><?= e($message['message']) ?></td>
                      <td><?= e(admin_format_datetime($message['created_at'])) ?></td>
                      <td>
                        <form class="inline-form" method="post">
                          <input type="hidden" name="message_id" value="<?= e($message['id']) ?>" />
                          <select name="is_read" aria-label="Message status">
                            <option value="0" <?= (int) $message['is_read'] === 0 ? 'selected' : '' ?>>Unread</option>
                            <option value="1" <?= (int) $message['is_read'] === 1 ? 'selected' : '' ?>>Read</option>
                          </select>
                          <button type="submit" class="btn-clear">Save</button>
                        </form>
                      </td>
                    </tr>
                  <?php endwhile; ?>
                <?php else: ?>
                  <tr><td colspan="4">No contact messages yet.</td></tr>
                <?php endif; ?>
              </tbody>
            </table>
          </div>
        </section>
<?php admin_render_shell_end(); ?>
