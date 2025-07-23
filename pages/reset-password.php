<?php
session_start();
$lang = $_SESSION['lang'] ?? 'en';
$token = htmlspecialchars($_GET['token'] ?? '');
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
            <input type="hidden" name="token" value="<?= $token ?>">
            <div>
                <label for="password" class="text-sm font-medium text-gray-700"><?= $lang === 'np' ? 'नयाँ पासवर्ड' : 'New Password' ?></label>
                <input type="password" name="password" id="password" required class="w-full px-3 py-2 mt-1 border border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
            </div>
            <div class="mt-4">
                <label for="confirm_password" class="text-sm font-medium text-gray-700"><?= $lang === 'np' ? 'पासवर्ड पुष्टि गर्नुहोस्' : 'Confirm Password' ?></label>
                <input type="password" name="confirm_password" id="confirm_password" required class="w-full px-3 py-2 mt-1 border border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
            </div>
            <div class="mt-6">
                <button type="submit" class="w-full px-4 py-2 font-medium text-white bg-indigo-600 rounded-md hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
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
            const password = form.password.value;
            const confirm_password = form.confirm_password.value;
            const token = form.token.value;
            const messageDiv = document.getElementById('message');
            messageDiv.textContent = '';

            if (password !== confirm_password) {
                messageDiv.style.color = 'red';
                messageDiv.textContent = '<?= $lang === 'np' ? 'पासवर्डहरू मेल खाँदैनन्।' : 'Passwords do not match.' ?>';
                return;
            }

            try {
                const response = await fetch('/GreenBin/backend/updatePassword.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ token: token, password: password })
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
