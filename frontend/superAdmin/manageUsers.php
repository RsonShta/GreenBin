<?php
require_once __DIR__ . '/../../backend/includes/auth.php';
requireRole(['superAdmin']); // Only superAdmin allowed

require_once __DIR__ . '/../../backend/includes/db.php';

// Fetch all users except superAdmin
$users = $pdo->query("SELECT id, first_name, email_id, phone_number, role FROM users WHERE role != 'superAdmin'")->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <title>Manage Users</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body class="p-6 font-sans bg-gray-50">
  <h1 class="text-2xl font-bold mb-4">Manage Users</h1>

  <table class="min-w-full bg-white shadow rounded">
    <thead class="bg-gray-100 text-left">
      <tr>
        <th class="p-3">Name</th>
        <th class="p-3">Email</th>
        <th class="p-3">Phone</th>
        <th class="p-3">Role</th>
        <th class="p-3">Actions</th>
      </tr>
    </thead>
    <tbody>
      <?php foreach ($users as $user): ?>
      <tr data-user-id="<?= $user['id'] ?>" class="border-t hover:bg-gray-50">
        <td class="p-3"><?= htmlspecialchars($user['first_name']) ?></td>
        <td class="p-3"><?= htmlspecialchars($user['email_id']) ?></td>
        <td class="p-3"><?= htmlspecialchars($user['phone_number']) ?></td>
        <td class="p-3">
          <select class="role-select border rounded p-1" data-current-role="<?= $user['role'] ?>">
            <option value="user" <?= $user['role'] === 'user' ? 'selected' : '' ?>>User</option>
            <option value="admin" <?= $user['role'] === 'admin' ? 'selected' : '' ?>>Admin</option>
          </select>
        </td>
        <td class="p-3">
          <button class="delete-btn bg-red-600 text-white px-3 py-1 rounded hover:bg-red-700">Delete</button>
        </td>
      </tr>
      <?php endforeach; ?>
    </tbody>
  </table>

  <script>
    function showToast(msg, error=false) {
      const toast = document.createElement('div');
      toast.textContent = msg;
      toast.style.position = 'fixed';
      toast.style.top = '10px';
      toast.style.right = '10px';
      toast.style.backgroundColor = error ? '#dc2626' : '#16a34a';
      toast.style.color = 'white';
      toast.style.padding = '10px';
      toast.style.borderRadius = '5px';
      toast.style.zIndex = '9999';
      document.body.appendChild(toast);
      setTimeout(() => toast.remove(), 3000);
    }

    // Role update handler
    document.querySelectorAll('.role-select').forEach(select => {
      select.addEventListener('change', async (e) => {
        const newRole = e.target.value;
        const tr = e.target.closest('tr');
        const userId = tr.dataset.userId;
        const oldRole = e.target.getAttribute('data-current-role');
        if (newRole === oldRole) return;

        const confirmed = confirm(`Change role from ${oldRole} to ${newRole}?`);
        if (!confirmed) {
          e.target.value = oldRole;
          return;
        }

        try {
          const res = await fetch('/GreenBin/backend/superAdmin/updateRole.php', {
            method: 'POST',
            headers: {'Content-Type': 'application/json'},
            body: JSON.stringify({ user_id: userId, new_role: newRole })
          });

          const data = await res.json();
          if (res.ok) {
            showToast('Role updated');
            e.target.setAttribute('data-current-role', newRole);
          } else {
            showToast(data.message || 'Failed to update role', true);
            e.target.value = oldRole;
          }
        } catch {
          showToast('Network error', true);
          e.target.value = oldRole;
        }
      });
    });

    // Delete handler
    document.querySelectorAll('.delete-btn').forEach(button => {
      button.addEventListener('click', async (e) => {
        const tr = e.target.closest('tr');
        const userId = tr.dataset.userId;
        if (!confirm('Delete this user? This action cannot be undone.')) return;

        try {
          const res = await fetch('/GreenBin/backend/superAdmin/deleteUser.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ user_id: userId })
          });

          const data = await res.json();
          if (res.ok) {
            showToast('User deleted');
            tr.remove();
          } else {
            showToast(data.message || 'Failed to delete user', true);
          }
        } catch {
          showToast('Network error', true);
        }
      });
    });
  </script>
</body>
</html>
