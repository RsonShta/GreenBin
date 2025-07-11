document.addEventListener("DOMContentLoaded", () => {
  const form = document.querySelector("form");

  // Input elements
  const emailInput = document.getElementById("email");
  const phoneInput = document.getElementById("phone_number");
  const passwordInput = document.getElementById("password");
  const confirmInput = document.getElementById("confirm_password");

  // Error spans
  const emailError = document.getElementById("email_error");
  const phoneError = document.getElementById("phone_error");
  const passwordError = document.getElementById("password_error");
  const confirmError = document.getElementById("confirm_error");

  // Validation functions
  function validateEmail() {
    const email = emailInput.value.trim();
    if (!email) {
      emailError.textContent = "Email is required.";
      return false;
    }
    if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) {
      emailError.textContent = "Invalid email format.";
      return false;
    }
    emailError.textContent = "";
    return true;
  }

  function validatePhone() {
    const phone = phoneInput.value.trim();
    if (!phone) {
      phoneError.textContent = "Phone number is required.";
      return false;
    }
    if (!/^\d{10}$/.test(phone)) {
      phoneError.textContent = "Phone number must be exactly 10 digits.";
      return false;
    }
    phoneError.textContent = "";
    return true;
  }

  function validatePassword() {
    const pass = passwordInput.value;
    if (!pass) {
      passwordError.textContent = "Password is required.";
      return false;
    }
    if (pass.length < 8) {
      passwordError.textContent = "Password must be at least 8 characters.";
      return false;
    }
    passwordError.textContent = "";
    return true;
  }

  function validateConfirm() {
    const pass = passwordInput.value;
    const confirm = confirmInput.value;
    if (!confirm) {
      confirmError.textContent = "Please confirm your password.";
      return false;
    }
    if (pass !== confirm) {
      confirmError.textContent = "Passwords do not match.";
      return false;
    }
    confirmError.textContent = "";
    return true;
  }

  // Attach real-time event listeners
  emailInput.addEventListener("input", validateEmail);
  phoneInput.addEventListener("input", validatePhone);
  passwordInput.addEventListener("input", () => {
    validatePassword();
    validateConfirm(); // Confirm depends on password too
  });
  confirmInput.addEventListener("input", validateConfirm);

  // On form submit validate all again before send
  form.addEventListener("submit", async (e) => {
    e.preventDefault();

    const isEmailValid = validateEmail();
    const isPhoneValid = validatePhone();
    const isPassValid = validatePassword();
    const isConfirmValid = validateConfirm();

    if (!(isEmailValid && isPhoneValid && isPassValid && isConfirmValid)) {
      return; // Don't submit if validation fails
    }

    try {
      const formData = new FormData(form);
      const response = await fetch(form.action, {
        method: "POST",
        body: formData,
      });
      const result = await response.json();

      if (response.ok) {
        const userId = result.user_id;
        window.location.href = `/GreenBin/frontend/dashboard/dashboard.php`;
      } else {
        alert("Error: " + result.message);
      }
    } catch (err) {
      alert("Network error: " + err.message);
    }
  });
});
