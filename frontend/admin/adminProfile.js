document.addEventListener('DOMContentLoaded', async () => {
    const profilePhotoElement = document.querySelector('.profile-photo');
    const userNameElement = document.querySelector('.user-name');
    const userRoleElement = document.querySelector('.user-role');
    const userEmailElement = document.querySelector('.user-email');
    const userPhoneElement = document.querySelector('.user-phone');
    const userJoinedElement = document.querySelector('.user-joined');
    const userWardElement = document.querySelector('.user-ward');
    const userNagarpalikaElement = document.querySelector('.user-nagarpalika');
    const userAddressElement = document.querySelector('.user-address');
    const userOfficePhoneElement = document.querySelector('.user-office-phone');
    const userDepartmentElement = document.querySelector('.user-department');
    const userEmployeeIdElement = document.querySelector('.user-employee-id');
    const adminDetailsSection = document.getElementById('admin-details-section');

    try {
        const response = await fetch('/GreenBin/backend/admin/getAdminProfile.php');
        const data = await response.json();

        if (data.success) {
            const user = data.user;
            const profilePhotoPath = user.profile_photo ? `/GreenBin/uploads/profiles/${user.profile_photo}` : '/GreenBin/uploads/profiles/default.jpg';

            if (profilePhotoElement) profilePhotoElement.src = profilePhotoPath;
            if (userNameElement) userNameElement.textContent = `${user.first_name} ${user.last_name}`;
            if (userRoleElement) userRoleElement.textContent = user.role.charAt(0).toUpperCase() + user.role.slice(1);
            if (userEmailElement) userEmailElement.textContent = user.email_id;
            if (userPhoneElement) userPhoneElement.textContent = user.phone_number;
            if (userJoinedElement) userJoinedElement.textContent = new Date(user.created_at).toLocaleDateString('en-US', { year: 'numeric', month: 'long', day: 'numeric' });

            if (user.role === 'admin' && adminDetailsSection) {
                adminDetailsSection.style.display = 'block';
                if (userWardElement) userWardElement.textContent = user.ward || 'N/A';
                if (userNagarpalikaElement) userNagarpalikaElement.textContent = user.nagarpalika || 'N/A';
                if (userAddressElement) userAddressElement.textContent = user.address || 'N/A';
                if (userOfficePhoneElement) userOfficePhoneElement.textContent = user.office_phone || 'N/A';
                if (userDepartmentElement) userDepartmentElement.textContent = user.department || 'N/A';
                if (userEmployeeIdElement) userEmployeeIdElement.textContent = user.employee_id || 'N/A';
            } else if (adminDetailsSection) {
                adminDetailsSection.style.display = 'none';
            }

        } else {
            console.error('Failed to fetch admin profile:', data.message);
            // Optionally redirect to login or show an error message
            window.location.href = '/GreenBin/adminLogin';
        }
    } catch (error) {
        console.error('Error fetching admin profile:', error);
        // Optionally redirect to login or show an error message
        window.location.href = '/GreenBin/adminLogin';
    }
});
