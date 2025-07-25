// --- JavaScript from manageUsers.php ---
function showToast(msg, error = false) {
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
                headers: { 'Content-Type': 'application/json' },
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

function validateField(input, message) {
    const errorSpan = input.nextElementSibling;
    if (input.value.trim() === '') {
        errorSpan.textContent = message;
        return false;
    }
    errorSpan.textContent = '';
    return true;
}

function validateEmail(input) {
    const errorSpan = input.nextElementSibling;
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    if (!emailRegex.test(input.value)) {
        errorSpan.textContent = 'Invalid email format.';
        return false;
    }
    errorSpan.textContent = '';
    return true;
}

function validatePassword(input) {
    const errorSpan = input.nextElementSibling;
    if (input.value.length < 6) {
        errorSpan.textContent = 'Password must be at least 6 characters.';
        return false;
    }
    errorSpan.textContent = '';
    return true;
}

submitAddUserBtn.addEventListener('click', async () => {
    let isValid = true;
    const fields = [
        { name: 'first_name', message: 'First name is required.' },
        { name: 'email', message: 'Email is required.', validator: validateEmail },
        { name: 'phone_number', message: 'Phone number is required.' },
        { name: 'password', message: 'Password is required.', validator: validatePassword },
        { name: 'ward', message: 'Ward is required.' },
        { name: 'nagarpalika', message: 'Nagarpalika is required.' },
        { name: 'address', message: 'Address is required.' }
    ];

    fields.forEach(field => {
        const input = addUserForm.querySelector(`[name="${field.name}"]`);
        if (!validateField(input, field.message)) {
            isValid = false;
        }
        if (field.validator && !field.validator(input)) {
            isValid = false;
        }
    });

    if (!isValid) {
        return;
    }

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
            location.reload();
        } else {
            showToast(result.message || 'Failed to add admin', true);
        }
    } catch (error) {
        showToast('Network error', true);
    }
});
// --- End JavaScript from manageUsers.php ---
