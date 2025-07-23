document.addEventListener("DOMContentLoaded", () => {
  const form = document.querySelector("form");
  const emailInput = document.getElementById("email");
  const passwordInput = document.getElementById("password");
  const emailError = document.getElementById("email_error");
  const passwordError = document.getElementById("password_error");
  const messageDiv = document.getElementById("message");
  const submitButton = form.querySelector("button[type='submit']");

  const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;

  form.addEventListener("submit", async (e) => {
    e.preventDefault();
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
    formData.set("email", emailInput.value.trim().toLowerCase());

    submitButton.disabled = true;
    submitButton.textContent = "Logging in...";

    try {
      const response = await fetch(form.action, {
        method: "POST",
        body: formData,
      });

      let data = {};
      try {
        if (response.headers.get("content-type")?.includes("application/json")) {
          data = await response.json();
        }
      } catch {
        messageDiv.style.color = "red";
        messageDiv.textContent = "Unexpected server response.";
        return;
      }

      if (response.ok) {
        messageDiv.style.color = "green";
        messageDiv.textContent = data.message || "Login successful!";
        setTimeout(() => {
          if (data.role === "superAdmin") {
            window.location.href = "/GreenBin/manageUsers";
          } else if (data.role === "admin") {
            window.location.href = "/GreenBin/adminDashboard";
          } else if (data.role === "user") {
            window.location.href = "/GreenBin/dashboard";
          } else {
            window.location.href = "/GreenBin/home";
          }
        }, 1000);
      } else {
        messageDiv.style.color = "red";
        messageDiv.textContent = data.message || "Login failed.";
      }
    } catch (err) {
      messageDiv.style.color = "red";
      messageDiv.textContent = "Network error: " + err.message;
    } finally {
      submitButton.disabled = false;
      submitButton.textContent = "Login";
    }
  });
});
