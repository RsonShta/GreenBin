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
    <?php include $_SERVER['DOCUMENT_ROOT'] . '/GreenBin/frontend/login/login.html'; ?>
  </div>
</body>
</html>
