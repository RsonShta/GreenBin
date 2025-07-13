document.addEventListener("DOMContentLoaded", () => {
  const form = document.querySelector("form");
  const emailInput = document.getElementById("email");
  const passwordInput = document.getElementById("password");
  const emailError = document.getElementById("email_error");
  const passwordError = document.getElementById("password_error");
  const messageDiv = document.getElementById("message");

  const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;

  form.addEventListener("submit", async (e) => {
    e.preventDefault();

    // Clear previous errors
    messageDiv.textContent = "";
    emailError.textContent = "";
    passwordError.textContent = "";

    let valid = true;

    if (!emailRegex.test(emailInput.value.trim())) {
      emailError.textContent = "Invalid email format.";
      valid = false;
    }

    if (passwordInput.value.length < 8) {
      passwordError.textContent = "Password must be at least 8 characters.";
      valid = false;
    }

    if (!valid) return;

    const formData = new FormData(form);

    try {
      const response = await fetch(form.action, {
        method: "POST",
        body: formData,
      });

      const data = await response.json();

      if (response.ok) {
        messageDiv.style.color = "green";
        messageDiv.textContent = data.message || "Login successful!";

        // Redirect based on role from DB
        setTimeout(() => {
          if (data.role === "admin") {
            window.location.href =
              "/GreenBin/pages/adminDashboard.php";
          } else if (data.role === "user") {
            window.location.href = "/GreenBin/pages/dashboard.php";
          } else {
            window.location.href = "/GreenBin/pages/home.php";
          }
        }, 1000);
      } else {
        messageDiv.style.color = "red";
        messageDiv.textContent = data.message || "Login failed.";
      }
    } catch (err) {
      messageDiv.style.color = "red";
      messageDiv.textContent = "Network error: " + err.message;
    }
  });
});
