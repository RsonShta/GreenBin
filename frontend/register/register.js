document.addEventListener("DOMContentLoaded", () => {
  const form = document.querySelector("form");

  form.addEventListener("submit", async (e) => {
    e.preventDefault();

    const pass = document.getElementById("password").value;
    const confirm = document.getElementById("confirm_password").value;
    if (pass !== confirm) {
      alert("Passwords do not match.");
      return;
    }

    const formData = new FormData(form);

    try {
      const response = await fetch(form.action, {
        method: "POST",
        body: formData,
      });

      const text = await response.text();

      if (response.ok) {
        alert("Registration successful!");
        window.location.href = "/GreenBin/frontend/login.html";
      } else {
        alert("Registration failed: " + text);
      }
    } catch (error) {
      alert("Network error: " + error.message);
    }
  });
});
