<?php

require_once __DIR__ . '/includes/admin.php';

$admin = require_admin();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $targetUserId = (int) ($_POST['user_id'] ?? 0);
    $makeAdmin = (int) ($_POST['is_admin'] ?? 0);

    if ($targetUserId === (int) $admin['id']) {
        admin_flash('error', 'You cannot change your own admin role.');
    } elseif ($targetUserId > 0 && in_array($makeAdmin, [0, 1], true)) {
        try {
            $stmt = $conn->prepare("UPDATE users SET is_admin = ? WHERE id = ?");
            $stmt->bind_param("ii", $makeAdmin, $targetUserId);
            $stmt->execute();
            $stmt->close();
            admin_flash('success', 'User role updated.');
        } catch (Throwable $exception) {
            error_log($exception->getMessage());
            admin_flash('error', 'User role could not be updated.');
        }
    } else {
        admin_flash('error', 'Choose a valid user role.');
    }

    header("Location: users.php");
    exit;
}

$users = $conn->query(
    "SELECT u.id, u.fullname, u.email, u.phone, u.is_admin, u.created_at, COUNT(a.id) AS appointment_count
     FROM users u
     LEFT JOIN appointments a ON a.user_id = u.id
     GROUP BY u.id, u.fullname, u.email, u.phone, u.is_admin, u.created_at
     ORDER BY u.created_at DESC, u.id DESC"
);

admin_render_shell_start('Users', 'users');
?>
        <div class="admin-hero">
          <div>
            <p class="eyebrow">Accounts</p>
            <h1>Users</h1>
            <p>Review patient and staff accounts, appointment counts, and admin access.</p>
          </div>
        </div>

        <section class="admin-card">
          <div class="admin-table-wrap">
            <table class="admin-table">
              <thead>
                <tr>
                  <th>Name</th>
                  <th>Contact</th>
                  <th>Bookings</th>
                  <th>Role</th>
                  <th>Joined</th>
                </tr>
              </thead>
              <tbody>
                <?php if ($users->num_rows): ?>
                  <?php while ($user = $users->fetch_assoc()): ?>
                    <tr>
                      <td>
                        <strong><?= e($user['fullname']) ?></strong>
                        <span>ID: <?= e($user['id']) ?></span>
                      </td>
                      <td>
                        <span><?= e($user['email']) ?></span>
                        <span><?= e($user['phone']) ?></span>
                      </td>
                      <td><?= e($user['appointment_count']) ?></td>
                      <td>
                        <?php if ((int) $user['id'] === (int) $admin['id']): ?>
                          <span class="status-pill status-confirmed">Current Admin</span>
                        <?php else: ?>
                          <form class="inline-form" method="post">
                            <input type="hidden" name="user_id" value="<?= e($user['id']) ?>" />
                            <select name="is_admin" aria-label="User role">
                              <option value="0" <?= (int) $user['is_admin'] === 0 ? 'selected' : '' ?>>Patient</option>
                              <option value="1" <?= (int) $user['is_admin'] === 1 ? 'selected' : '' ?>>Admin</option>
                            </select>
                            <button type="submit" class="btn-clear">Save</button>
                          </form>
                        <?php endif; ?>
                      </td>
                      <td><?= e(admin_format_datetime($user['created_at'])) ?></td>
                    </tr>
                  <?php endwhile; ?>
                <?php else: ?>
                  <tr><td colspan="5">No users found.</td></tr>
                <?php endif; ?>
              </tbody>
            </table>
          </div>
        </section>
<?php admin_render_shell_end(); ?>
