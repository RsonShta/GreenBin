document.addEventListener('DOMContentLoaded', async () => {
    const profilePhotoElement = document.querySelector('.profile-photo');
    const userNameElement = document.querySelector('.user-name');
    const userRoleElement = document.querySelector('.user-role');
    const userEmailElement = document.querySelector('.user-email');
    const userPhoneElement = document.querySelector('.user-phone');
    const userJoinedElement = document.querySelector('.user-joined');

    try {
        const response = await fetch('/GreenBin/backend/getProfile.php');
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

        } else {
            console.error('Failed to fetch profile:', data.message);
            // Optionally redirect to login or show an error message
            window.location.href = '/GreenBin/login';
        }
    } catch (error) {
        console.error('Error fetching profile:', error);
        // Optionally redirect to login or show an error message
        window.location.href = '/GreenBin/login';
    }
});
