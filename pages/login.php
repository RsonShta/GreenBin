<?php
session_start();
if (isset($_SESSION['user_id'])) {
  $role = $_SESSION['user_role'];
  header("Location: /GreenBin/pages/" . ($role === 'admin' ? "adminDashboard" : "dashboard"));
  exit;
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Login - हरित नेपाल</title>
  <link rel="stylesheet" href="/GreenBin/frontend/login/login.css" />
  <script src="https://cdn.tailwindcss.com"></script>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet" />
  <script src="/GreenBin/frontend/login/login.js" defer></script>
  <style>
    body {
      font-family: 'Inter', sans-serif;
    }

    .bg-overlay::before {
      content: "";
      position: absolute;
      inset: 0;
      background: rgba(255, 255, 255, 0.8);
      z-index: 0;
    }

    .error-message {
      color: #dc2626;
      font-size: 0.7rem;
      margin-top: 0.1rem;
      min-height: 1em;
    }
  </style>
</head>

<body class="relative min-h-screen bg-gray-200 bg-cover bg-center bg-no-repeat">
  <div class="flex justify-center items-center min-h-screen px-4 bg-overlay">
    <form action="/GreenBin/backend/login.php" method="POST"
      class="bg-white rounded-lg shadow-lg w-96 p-6 relative z-10" novalidate>
      <!-- Logo & Title -->
      <div class="flex justify-center items-center mb-4">
        <img src="/GreenBin/frontend/img/mountain.png" alt="Logo" class="w-12 h-10 mt-1 mr-2" />
        <h1 class="text-3xl font-bold text-center">हरित नेपाल</h1>
      </div>

      <h2 class="text-2xl font-bold text-center mb-2">Welcome</h2>
      <h3 class="text-xl text-center mb-4">Login</h3>

      <!-- Message -->
      <div id="message" class="text-sm text-center mb-3 font-medium" style="min-height: 1.5rem;"></div>

      <!-- Email -->
      <div class="mb-4">
        <label for="email" class="block mb-1 font-medium">Email</label>
        <input name="email" id="email" type="email" required
          class="w-full p-2 rounded-sm border shadow-sm focus:outline-none" autocomplete="email" />
        <span id="email_error" class="error-message" aria-live="polite"></span>
      </div>

      <!-- Password -->
      <div class="mb-4">
        <label for="password" class="block mb-1 font-medium">Password</label>
        <input name="password" id="password" type="password" required
          class="w-full p-2 rounded-sm border shadow-sm focus:outline-none" autocomplete="current-password" />
        <span id="password_error" class="error-message" aria-live="polite"></span>
      </div>

      <!-- Login Button -->
      <div class="mb-4">
        <button type="submit"
          class="w-full bg-green-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded-lg shadow-sm transition duration-300">
          Login
        </button>
      </div>

      <!-- Register Link -->
      <p class="text-center text-sm mb-2">
        Don't have an account?
        <a href="/GreenBin/register" class="text-green-600 font-semibold hover:underline">Register</a>
      </p>

      <!-- Home Link -->
      <p class="text-center text-sm mb-4">
        <a href="/GreenBin/home" class="text-green-600 font-semibold hover:underline">&#8592; Back to Home</a>
      </p>
    </form>
  </div>
</body>

</html>