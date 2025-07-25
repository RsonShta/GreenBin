<?php
session_start(); // Ensure session is started

// Generate CSRF token if it doesn't exist
if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
$csrf_token = $_SESSION['csrf_token'];

require_once __DIR__ . '/../backend/includes/auth.php';
requireRole(['superAdmin']); // Only superAdmin allowed

require_once __DIR__ . '/../backend/includes/db.php';

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
  <input type="hidden" name="_csrf_token" value="<?= htmlspecialchars($csrf_token) ?>">
  <div class="flex justify-between items-center mb-4">
    <h1 class="text-2xl font-bold">Manage Users</h1>
    <div>
      <input type="text" id="userSearch" placeholder="Search users..." class="p-2 border rounded focus:outline-none focus:ring-2 focus:ring-blue-500">
      <button id="add-user-btn" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700 ml-2">Add Admin</button>
    </div>
  </div>

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

  <!-- Add User Modal -->
  <div id="add-user-modal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden">
    <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
      <div class="mt-3 text-center">
        <h3 class="text-lg leading-6 font-medium text-gray-900">Add New Admin</h3>
        <div class="mt-2 px-7 py-3">
          <form id="add-user-form">
            <input type="text" name="first_name" placeholder="First Name" class="w-full p-2 border rounded mb-2" required>
            <input type="email" name="email" placeholder="Email" class="w-full p-2 border rounded mb-2" required>
            <input type="text" name="phone_number" placeholder="Phone Number" class="w-full p-2 border rounded mb-2" required>
            <input type="password" name="password" placeholder="Password" class="w-full p-2 border rounded mb-2" required>
            <input type="text" name="ward" placeholder="Ward" class="w-full p-2 border rounded mb-2" required>
            <input type="text" name="nagarpalika" placeholder="Nagarpalika" class="w-full p-2 border rounded mb-2" required>
            <input type="text" name="address" placeholder="Address" class="w-full p-2 border rounded mb-2" required>
            <input type="hidden" name="_csrf_token" value="<?= htmlspecialchars($csrf_token) ?>">
          </form>
        </div>
        <div class="items-center px-4 py-3">
          <button id="cancel-add-user" class="px-4 py-2 bg-gray-500 text-white text-base font-medium rounded-md w-auto shadow-sm hover:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-gray-300">
            Cancel
          </button>
          <button id="submit-add-user" class="px-4 py-2 bg-blue-600 text-white text-base font-medium rounded-md w-auto shadow-sm hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-300">
            Add
          </button>
        </div>
      </div>
    </div>
  </div>

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

        const csrfToken = document.querySelector('input[name="_csrf_token"]').value;

        try {
          const res = await fetch('/GreenBin/backend/superAdmin/updateRole.php', {
            method: 'POST',
            headers: {'Content-Type': 'application/json'},
            body: JSON.stringify({ user_id: userId, new_role: newRole, _csrf_token: csrfToken })
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

        const csrfToken = document.querySelector('input[name="_csrf_token"]').value;

        try {
          const res = await fetch('/GreenBin/backend/superAdmin/deleteUser.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ user_id: userId, _csrf_token: csrfToken })
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

    // Search functionality
    const searchInput = document.getElementById('userSearch');
    const tableRows = document.querySelectorAll('tbody tr');

    searchInput.addEventListener('keyup', (e) => {
      const searchTerm = e.target.value.toLowerCase();

      tableRows.forEach(row => {
        const rowText = row.textContent.toLowerCase();
        if (rowText.includes(searchTerm)) {
          row.style.display = '';
        } else {
          row.style.display = 'none';
        }
      });
    });

    // Modal handling
    const addUserModal = document.getElementById('add-user-modal');
    const addUserBtn = document.getElementById('add-user-btn');
    const cancelAddUserBtn = document.getElementById('cancel-add-user');
    const submitAddUserBtn = document.getElementById('submit-add-user');
    const addUserForm = document.getElementById('add-user-form');

    addUserBtn.addEventListener('click', () => {
      addUserModal.style.display = 'block';
    });

    cancelAddUserBtn.addEventListener('click', () => {
      addUserModal.style.display = 'none';
      addUserForm.reset();
    });

    submitAddUserBtn.addEventListener('click', async () => {
      const formData = new FormData(addUserForm);
      const data = Object.fromEntries(formData.entries());

      try {
        const res = await fetch('/GreenBin/backend/superAdmin/addUser.php', {
          method: 'POST',
          headers: { 'Content-Type': 'application/json' },
          body: JSON.stringify(data)
        });

        const result = await res.json();
        if (res.ok) {
          showToast('Admin added successfully');
          addUserModal.style.display = 'none';
          addUserForm.reset();
          // Optionally, refresh the user list or add the new user to the table
          location.reload();
        } else {
          showToast(result.message || 'Failed to add admin', true);
        }
      } catch (error) {
        showToast('Network error', true);
      }
    });
  </script>
</body>
</html>
