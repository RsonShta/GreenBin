document.addEventListener('DOMContentLoaded', async () => {
    const form = document.getElementById('editProfileForm');
    const firstNameInput = document.getElementById('first_name');
    const lastNameInput = document.getElementById('last_name');
    const emailInput = document.getElementById('email');
    const phoneInput = document.getElementById('phone_number');
    const countryInput = document.getElementById('country'); // New country input
    const profilePhotoInput = document.getElementById('profile_photo');
    const profilePhotoPreview = document.getElementById('preview');
    const currentPasswordInput = document.getElementById('current_password');
    const newPasswordInput = document.getElementById('new_password');
    const confirmNewPasswordInput = document.getElementById('confirm_new_password');
    const messageDiv = document.getElementById('message');
    const emailErrorDiv = document.createElement('div');
    emailErrorDiv.className = 'text-red-600 text-xs mt-1';
    emailInput.parentNode.appendChild(emailErrorDiv);

    const phoneErrorDiv = document.createElement('div');
    phoneErrorDiv.className = 'text-red-600 text-xs mt-1';
    phoneInput.parentNode.appendChild(phoneErrorDiv);

    // Admin specific fields
    const adminFieldsDiv = document.getElementById('admin-fields');
    const wardInput = document.getElementById('ward');
    const nagarpalikaInput = document.getElementById('nagarpalika');
    const addressInput = document.getElementById('address');

    const displayMessage = (message, type) => {
        messageDiv.textContent = message;
        messageDiv.className = `text-sm mb-4 ${type === 'success' ? 'text-green-600' : 'text-red-600'}`;
        messageDiv.style.display = 'block';
    };

    const isValidEmail = (email) => {
        // Basic email regex for format validation
        return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email);
    };

    const isValidPhoneNumber = (phone) => {
        // Regex for 10-digit phone number (common in Nepal)
        return /^\d{10}$/.test(phone);
    };

    // Fetch user data and populate form
    try {
        const response = await fetch('/GreenBin/backend/getProfile.php');
        const data = await response.json();

        if (data.success) {
            const user = data.user;
            firstNameInput.value = user.first_name;
            lastNameInput.value = user.last_name;
            emailInput.value = user.email_id; // Email is disabled, so no direct validation needed here
            phoneInput.value = user.phone_number;
            countryInput.value = user.country || ''; // Populate country
            profilePhotoPreview.src = user.profile_photo ? `/GreenBin/uploads/profiles/${user.profile_photo}` : '/GreenBin/uploads/profiles/default.jpg';

            if (user.role === 'admin' && adminFieldsDiv) {
                adminFieldsDiv.style.display = 'block';
                wardInput.value = user.ward || '';
                nagarpalikaInput.value = user.nagarpalika || '';
                addressInput.value = user.address || '';
            } else if (adminFieldsDiv) {
                adminFieldsDiv.style.display = 'none';
            }

        } else {
            displayMessage(data.message || 'Failed to load profile data.', 'error');
            // Redirect to login if not authenticated
            if (data.code === 401) {
                window.location.href = '/GreenBin/login';
            }
        }
    } catch (error) {
        console.error('Error fetching profile:', error);
        displayMessage('An error occurred while fetching profile data.', 'error');
    }

    // Handle profile photo preview
    if (profilePhotoInput) {
        profilePhotoInput.addEventListener('change', function() {
            const file = this.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    profilePhotoPreview.src = e.target.result;
                };
                reader.readAsDataURL(file);
            } else {
                profilePhotoPreview.src = '/GreenBin/uploads/profiles/default.jpg';
            }
        });
    }

    // Real-time phone number validation
    phoneInput.addEventListener('input', () => {
        if (phoneInput.value.trim() === '') {
            phoneErrorDiv.textContent = '';
            phoneInput.classList.remove('border-red-500');
        } else if (!isValidPhoneNumber(phoneInput.value)) {
            phoneErrorDiv.textContent = 'Please enter a valid 10-digit phone number.';
            phoneInput.classList.add('border-red-500');
        } else {
            phoneErrorDiv.textContent = '';
            phoneInput.classList.remove('border-red-500');
        }
    });

    // Handle form submission
    if (form) {
        form.addEventListener('submit', async (event) => {
            event.preventDefault();

            messageDiv.style.display = 'none'; // Hide previous messages
            emailErrorDiv.textContent = ''; // Clear previous errors
            phoneErrorDiv.textContent = '';

            // Perform validation before submission
            let isValid = true;

            // Email validation (though disabled, good to have a check if it were enabled)
            // if (!isValidEmail(emailInput.value)) {
            //     emailErrorDiv.textContent = 'Please enter a valid email address.';
            //     emailInput.classList.add('border-red-500');
            //     isValid = false;
            // } else {
            //     emailInput.classList.remove('border-red-500');
            // }

            // Phone number validation
            if (!isValidPhoneNumber(phoneInput.value)) {
                phoneErrorDiv.textContent = 'Please enter a valid 10-digit phone number.';
                phoneInput.classList.add('border-red-500');
                isValid = false;
            } else {
                phoneInput.classList.remove('border-red-500');
            }

            if (!isValid) {
                displayMessage('Please correct the errors in the form.', 'error');
                return; // Stop submission if validation fails
            }

            const formData = new FormData();
            formData.append('first_name', firstNameInput.value);
            formData.append('last_name', lastNameInput.value);
            formData.append('email', emailInput.value); // Email is sent but not updated by backend
            formData.append('phone_number', phoneInput.value);
            formData.append('country', countryInput.value); // Append country

            if (profilePhotoInput.files.length > 0) {
                formData.append('profile_photo', profilePhotoInput.files[0]);
            }

            // Only append password fields if new password is provided
            if (newPasswordInput.value) {
                formData.append('current_password', currentPasswordInput.value);
                formData.append('new_password', newPasswordInput.value);
                formData.append('confirm_new_password', confirmNewPasswordInput.value);
            }

            // Append admin fields if visible
            if (adminFieldsDiv && adminFieldsDiv.style.display === 'block') {
                formData.append('ward', wardInput.value);
                formData.append('nagarpalika', nagarpalikaInput.value);
                formData.append('address', addressInput.value);
            }

            try {
                const response = await fetch('/GreenBin/backend/updateProfile.php', {
                    method: 'POST',
                    body: formData,
                });
                const data = await response.json();

                if (data.success) {
                    displayMessage(data.message, 'success');
                    // Clear password fields after successful update
                    currentPasswordInput.value = '';
                    newPasswordInput.value = '';
                    confirmNewPasswordInput.value = '';
                    // Optionally, refresh the page or update session display
                    // window.location.reload();
                } else {
                    displayMessage(data.message, 'error');
                }
            } catch (error) {
                console.error('Error updating profile:', error);
                displayMessage('An error occurred while updating profile.', 'error');
            }
        });
    }
});
