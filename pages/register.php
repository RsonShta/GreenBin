<?php
session_start();

// Set or get language from session
if (isset($_GET['lang']) && in_array($_GET['lang'], ['en', 'np'])) {
    $_SESSION['lang'] = $_GET['lang'];
}
$lang = $_SESSION['lang'] ?? 'en';
?>

<!DOCTYPE html>
<html lang="<?= $lang ?>">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title><?= $lang === 'np' ? 'दर्ता - हरित नेपाल' : 'Register - हरित नेपाल' ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet" />
    <link rel="stylesheet" href="/GreenBin/frontend/register/register.css">
    <style>
        html,
        body {
            scrollbar-width: none;
            /* Firefox */
        }

        html::-webkit-scrollbar,
        body::-webkit-scrollbar {
            width: 0;
            height: 0;
        }

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
            color: red;
            font-size: 0.7rem;
            margin-top: 0.1rem;
            min-height: 1em;
        }
    </style>
</head>

<body class="relative  min-h-screen bg-gray-200 bg-cover bg-center bg-no-repeat">

    <div class="flex justify-center items-center w-full min-h-screen px-4 bg-overlay">
        <form action="/GreenBin/backend/register.php" method="POST"
            class="relative z-10 bg-white rounded-lg shadow-lg w-full max-w-screen-md p-6 text-sm">
            <!-- Logo & Title -->
            <div class="flex items-center justify-between mb-4">
                <div class="flex items-center gap-2">
                    <img src="/GreenBin/frontend/img/mountain.png" alt="Mountain Logo" class="w-8 h-8" />
                    <h1 class="text-2xl font-bold text-green-700">हरित नेपाल</h1>
                </div>
                <a href="?lang=<?= $lang === 'en' ? 'np' : 'en' ?>"
                    class="text-xs px-2 py-1 border rounded hover:bg-gray-100">
                    🌐 <?= $lang === 'en' ? 'नेपाली' : 'English' ?>
                </a>
            </div>

            <h2 class="text-xl font-bold text-center mb-1">
                <?= $lang === 'np' ? 'हरित नेपालमा सामेल हुनुहोस्' : 'Join GreenBin Nepal' ?>
            </h2>
            <p class="text-center text-gray-600 mb-4 text-xs">
                <?= $lang === 'np' ? 'तपाईंको समुदायमा फरक ल्याउनको लागि आफ्नो खाता बनाउनुहोस् 🌱' : 'Create your account to start making a difference in your community 🌱' ?>
            </p>

            <!-- Grid Layout -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                <div>
                    <label for="first_name"
                        class="block font-medium mb-1 text-xs"><?= $lang === 'np' ? 'नाम' : 'First Name' ?></label>
                    <input name="first_name" id="first_name" type="text"
                        placeholder="<?= $lang === 'np' ? 'नाम' : 'First Name' ?>" required
                        class="w-full p-2 border rounded-md shadow-sm focus:ring-green-600 focus:outline-none text-sm" />
                </div>

                <div>
                    <label for="last_name"
                        class="block font-medium mb-1 text-xs"><?= $lang === 'np' ? 'थर' : 'Last Name' ?></label>
                    <input name="last_name" id="last_name" type="text"
                        placeholder="<?= $lang === 'np' ? 'थर' : 'Last Name' ?>" required
                        class="w-full p-2 border rounded-md shadow-sm focus:ring-green-600 focus:outline-none text-sm" />
                </div>

                <div>
                    <label for="email" class="block font-medium mb-1 text-xs">Email</label>
                    <input name="email" id="email" type="email" placeholder="email@example.com" required
                        class="w-full p-2 border rounded-md shadow-sm focus:ring-green-600 focus:outline-none text-sm" />
                    <span id="email_error" class="error-message"></span>
                </div>

                <div>
                    <label for="phone_number"
                        class="block font-medium mb-1 text-xs"><?= $lang === 'np' ? 'फोन नम्बर' : 'Phone Number' ?></label>
                    <input name="phone_number" id="phone_number" type="text" placeholder="98XXXXXXXX" required
                        class="w-full p-2 border rounded-md shadow-sm focus:ring-green-600 focus:outline-none text-sm" />
                    <span id="phone_error" class="error-message"></span>
                </div>

                <div>
                    <label for="password"
                        class="block font-medium mb-1 text-xs"><?= $lang === 'np' ? 'पासवर्ड' : 'Password' ?></label>
                    <input name="password" id="password" type="password" placeholder="********" required
                        class="w-full p-2 border rounded-md shadow-sm focus:ring-green-600 focus:outline-none text-sm" />
                    <span id="password_error" class="error-message"></span>
                </div>

                <div>
                    <label for="confirm_password"
                        class="block font-medium mb-1 text-xs"><?= $lang === 'np' ? 'पासवर्ड पुष्टि गर्नुहोस्' : 'Confirm Password' ?></label>
                    <input name="confirm_password" id="confirm_password" type="password" placeholder="********" required
                        class="w-full p-2 border rounded-md shadow-sm focus:ring-green-600 focus:outline-none text-sm" />
                    <span id="confirm_error" class="error-message"></span>
                </div>
            </div>

            <!-- Submit -->
            <div class="mt-4">
                <button type="submit"
                    class="w-full bg-green-600 hover:bg-blue-700 text-white font-semibold py-2 rounded-md transition duration-300 text-sm">
                    <?= $lang === 'np' ? 'खाता बनाउनुहोस्' : 'Create Account' ?>
                </button>
            </div>

            <!-- Links -->
            <p class="text-center mt-4 text-xs text-gray-700">
                <?= $lang === 'np' ? 'पहिले नै खाता छ?' : 'Already have an account?' ?>
                <a href="/GreenBin/login" class="text-green-600 font-semibold hover:underline">
                    <?= $lang === 'np' ? 'लग-इन गर्नुहोस्' : 'Sign in Here' ?>
                </a>
            </p>

            <p class="text-center mt-1 text-xs text-gray-700">
                <a href="/GreenBin/home" class="text-green-600 font-semibold hover:underline">
                    &#8592; <?= $lang === 'np' ? 'मुख्य पृष्ठमा फर्कनुहोस्' : 'Back to Home' ?>
                </a>
            </p>

            <p class="text-center text-gray-600 italic mt-4 max-w-md mx-auto text-xs">
                “<?= $lang === 'np' ? 'एक पटकमा एउटा रिपोर्ट — हामी नेपालको हरियाली बढाउँछौं।' : 'Together, we can make Nepal greener — one step at a time.' ?>”
                🌿
            </p>

            <p class="text-center text-xs mt-2">
                <?= $lang === 'np' ? 'सहायता चाहिन्छ?' : 'Need help?' ?>
                <a href="mailto:support@greennepal.com" class="text-green-600 font-semibold hover:underline">
                    support@greennepal.com
                </a>
            </p>
        </form>
    </div>

    <script src="/GreenBin/frontend/register/register.js"></script>
</body>

</html>