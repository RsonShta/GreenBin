document.addEventListener('DOMContentLoaded', () => {
  const form = document.querySelector('form');
  
  form.addEventListener('submit', async (e) => {
    e.preventDefault(); // prevent normal form submit
    
    const formData = new FormData(form);

    try {
      const response = await fetch(form.action, {
        method: 'POST',
        body: formData,
      });

      const data = await response.json(); // parse JSON instead of text

      if (response.ok) {
        alert(data.message || 'Login successful!');

        // Redirect based on role from response JSON
        if (data.role === 'admin') {
          window.location.href = '/GreenBin/frontend/admin/dashboard.html';
        } else if (data.role === 'user') {
          window.location.href = '/GreenBin/frontend/user/dashboard.html';
        } else {
          window.location.href = '/GreenBin/frontend/home/home.html';
        }

      } else {
        alert('Login failed: ' + (data.message || 'Unknown error'));
      }
    } catch (error) {
      alert('Network error: ' + error.message);
    }
  });
});
