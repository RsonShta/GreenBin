document.addEventListener("DOMContentLoaded", () => {
  const form = document.querySelector("form");
  const emailInput = document.getElementById("email");
  const passwordInput = document.getElementById("password");
  const emailError = document.getElementById("email_error");
  const passwordError = document.getElementById("password_error");

  const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;

  // Real-time validation for email
  emailInput.addEventListener("input", () => {
    if (!emailRegex.test(emailInput.value.trim())) {
      emailError.textContent = "Invalid email format.";
    } else {
      emailError.textContent = "";
    }
  });

  // Real-time validation for password length (min 8 chars)
  passwordInput.addEventListener("input", () => {
    if (passwordInput.value.length < 8) {
      passwordError.textContent = "Password must be at least 8 characters.";
    } else {
      passwordError.textContent = "";
    }
  });

  form.addEventListener("submit", async (e) => {
    e.preventDefault();

    let valid = true;

    if (!emailRegex.test(emailInput.value.trim())) {
      emailError.textContent = "Invalid email format.";
      valid = false;
    } else {
      emailError.textContent = "";
    }

    if (passwordInput.value.length < 8) {
      passwordError.textContent = "Password must be at least 8 characters.";
      valid = false;
    } else {
      passwordError.textContent = "";
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
        alert(data.message || "Login successful!");

        if (data.role === "admin") {
          window.location.href =
            "/GreenBin/frontend/adminDashboard/admindashboard.html";
        } else if (data.role === "user") {
          window.location.href = "/GreenBin/frontend/user/dashboard.html";
        } else {
          window.location.href = "/GreenBin/frontend/home/home.html";
        }
      } else {
        alert("Login failed: " + (data.message || "Unknown error"));
      }
    } catch (error) {
      alert("Network error: " + error.message);
    }
  });
});
