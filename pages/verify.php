<?php
session_start();
$lang = $_SESSION['lang'] ?? 'en';
$token = htmlspecialchars($_GET['token'] ?? '');
?>
<!DOCTYPE html>
<html lang="<?= $lang ?>">
<head>
    <meta charset="UTF-8">
    <title><?= $lang === 'np' ? 'इमेल प्रमाणीकरण' : 'Email Verification' ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 flex items-center justify-center h-screen">
    <div class="w-full max-w-md p-8 space-y-6 bg-white rounded-lg shadow-md text-center">
        <h2 class="text-2xl font-bold"><?= $lang === 'np' ? 'इमेल प्रमाणीकरण गर्दै...' : 'Verifying Email...' ?></h2>
        <div id="message"></div>
    </div>

    <script>
        (async function() {
            const token = '<?= $token ?>';
            const messageDiv = document.getElementById('message');

            if (!token) {
                messageDiv.style.color = 'red';
                messageDiv.textContent = '<?= $lang === 'np' ? 'कुनै टोकन प्रदान गरिएको छैन।' : 'No token provided.' ?>';
                return;
            }

            try {
                const response = await fetch('/GreenBin/backend/verifyEmail.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ token: token })
                });

                const data = await response.json();

                if (response.ok) {
                    messageDiv.style.color = 'green';
                    messageDiv.textContent = data.message;
                    setTimeout(() => {
                        window.location.href = '/GreenBin/login';
                    }, 3000);
                } else {
                    messageDiv.style.color = 'red';
                    messageDiv.textContent = data.message;
                }
            } catch (error) {
                messageDiv.style.color = 'red';
                messageDiv.textContent = '<?= $lang === 'np' ? 'एउटा त्रुटि भयो।' : 'An error occurred.' ?>';
            }
        })();
    </script>
</body>
</html>
