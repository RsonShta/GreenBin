<?php
session_start();
$lang = $_SESSION['lang'] ?? 'en';
?>
<!DOCTYPE html>
<html lang="<?= $lang ?>">
<head>
    <meta charset="UTF-8">
    <title><?= $lang === 'np' ? 'पासवर्ड बिर्सनुभयो' : 'Forgot Password' ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 flex items-center justify-center h-screen">
    <div class="w-full max-w-md p-8 space-y-6 bg-white rounded-lg shadow-md">
        <h2 class="text-2xl font-bold text-center"><?= $lang === 'np' ? 'पासवर्ड रिसेट अनुरोध' : 'Request Password Reset' ?></h2>
        <form id="forgot-password-form">
            <div>
                <label for="email" class="text-sm font-medium text-gray-700"><?= $lang === 'np' ? 'इमेल' : 'Email' ?></label>
                <input type="email" name="email" id="email" required class="w-full px-3 py-2 mt-1 border border-gray-300 rounded-md shadow-sm focus:ring-green-500 focus:border-green-500">
            </div>
            <div class="mt-6">
                <button type="submit" class="w-full px-4 py-2 font-medium text-white bg-green-600 rounded-md hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500">
                    <?= $lang === 'np' ? 'रिसेट लिङ्क पठाउनुहोस्' : 'Send Reset Link' ?>
                </button>
            </div>
        </form>
        <div id="message" class="text-center"></div>
    </div>

    <script>
        document.getElementById('forgot-password-form').addEventListener('submit', async function(e) {
            e.preventDefault();
            const form = e.target;
            const email = form.email.value;
            const messageDiv = document.getElementById('message');
            messageDiv.textContent = '';

            try {
                const response = await fetch('/GreenBin/backend/requestPasswordReset.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ email: email })
                });

                const data = await response.json();

                if (response.ok) {
                    messageDiv.style.color = 'green';
                    if (data.reset_link) {
                        messageDiv.innerHTML = `${data.message} <br><a href="${data.reset_link}" class="text-green-600 hover:underline">Click here to reset</a>`;
                    } else {
                        messageDiv.textContent = data.message;
                    }
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
