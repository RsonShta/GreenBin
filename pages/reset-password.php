<?php
session_start();
$lang = $_SESSION['lang'] ?? 'en';
?>
<!DOCTYPE html>
<html lang="<?= $lang ?>">
<head>
    <meta charset="UTF-8">
    <title><?= $lang === 'np' ? 'पासवर्ड रिसेट गर्नुहोस्' : 'Reset Password' ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 flex items-center justify-center h-screen">
    <div class="w-full max-w-md p-8 space-y-6 bg-white rounded-lg shadow-md">
        <h2 class="text-2xl font-bold text-center"><?= $lang === 'np' ? 'नयाँ पासवर्ड सेट गर्नुहोस्' : 'Set New Password' ?></h2>
        <form id="reset-password-form">
            <div>
                <label for="email" class="text-sm font-medium text-gray-700"><?= $lang === 'np' ? 'इमेल' : 'Email' ?></label>
                <input type="email" name="email" id="email" required class="w-full px-3 py-2 mt-1 border border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
            </div>
            <div class="mt-4">
                <label for="password" class="text-sm font-medium text-gray-700"><?= $lang === 'np' ? 'नयाँ पासवर्ड' : 'New Password' ?></label>
                <input type="password" name="password" id="password" required class="w-full px-3 py-2 mt-1 border border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
            </div>
            
            <div class="mt-6">
                <button type="submit" class="w-full px-4 py-2 font-medium text-white bg-green-600 rounded-md hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                    <?= $lang === 'np' ? 'पासवर्ड रिसेट गर्नुहोस्' : 'Reset Password' ?>
                </button>
            </div>
        </form>
        <div id="message" class="text-center"></div>
    </div>

    <script>
        document.getElementById('reset-password-form').addEventListener('submit', async function(e) {
            e.preventDefault();
            const form = e.target;
            const email = form.email.value;
            const password = form.password.value;
            const messageDiv = document.getElementById('message');
            messageDiv.textContent = '';

            try {
                const response = await fetch('/GreenBin/backend/updatePassword.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ password: password, email: email })
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
        });
    </script>
</body>
</html>
