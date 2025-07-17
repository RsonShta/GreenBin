<?php
session_start();
$lang = $_SESSION['lang'] ?? 'en';
?>
<!DOCTYPE html>
<html lang="<?= $lang ?>">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="/GreenBin/frontend/home/home.css" />
    <title>Home</title>
</head>

<body>
    <!-- Language switcher -->
    <nav class="nav-bar p-2 flex justify-between">
        <div class="flex gap-4 m-2">
            <div>
                <img src="/GreenBin/frontend/img/main-logo.png" alt="" class="w-14 h-17 mb-[3px]" />
            </div>
            <div>
                <h1 class="text-3xl font-bold text-center">हरित नेपाल</h1>
                <h2 class="text-1xl text-green-600">GreenBin Nepal</h2>
            </div>
        </div>

        <div class="nav-link flex item-center justify-center gap-6 p-2">
            <a href="/GreenBin/pages/lang.php?lang=<?= $lang === 'en' ? 'np' : 'en' ?>"
                class="text-xs px-2 py-1 border rounded hover:bg-gray-100">
                🌐 <?= $lang === 'en' ? 'नेपाली' : 'English' ?>
            </a>

            <a href="/GreenBin/login"
                class="pt-1 p-2 bg-gray-300 text-black text-center font-bold rounded-lg shadow-sm">Login</a>
            <a href="/GreenBin/register"
                class="pt-1 p-2 bg-green-600 hover:bg-blue-700 text-white text-center font-bold rounded-lg shadow-sm transition duration-300">Register</a>
        </div>
    </nav>

    <!-- Everything below remains unchanged -->

    <!-- 2nd section -->
    <section>
        <div class="main items-center p-2 justify-center flex flex-col">
            <?php if ($lang === 'np'): ?>
                <h1 class="text-5xl font-bold text-center m-4 mt-20">
                    नेपालको वातावरणीय समस्या रिपोर्ट गर्नुहोस्
                </h1>
                <h2 class="text-2xl text-center m-4">
                    नेपालको वातावरणीय समस्या रिपोर्ट गर्नुहोस्
                </h2>
                <h3 class="text-2xl text-center m-4">
                    आफ्नो समुदायलाई सफा बनाउन सहयोग गर्नुहोस्।<br />
                    सँगै, नेपालको स्वस्थ भविष्य बनाऔं।
                </h3>
                <div class="flex mb-20">
                    <a href="/GreenBin/register"
                        class="m-4 w-70 bg-green-600 hover:bg-gray-700 text-white text-center font-bold p-4 rounded-lg shadow-sm transition duration-300">
                        रिपोर्ट सुरु गर्नुहोस्
                    </a>
                    <a href="/GreenBin/login"
                        class="m-4 w-70 bg-white hover:bg-gray-400 text-black text-center font-bold p-4 rounded-lg shadow-sm transition duration-300">
                        मेरो खाता छ
                    </a>
                </div>
            <?php else: ?>
                <h1 class="text-5xl font-bold text-center m-4 mt-20">
                    Report Environmental Issues in <br /> Nepal
                </h1>
                <h2 class="text-2xl text-center m-4">
                    Report environmental issues in Nepal
                </h2>
                <h3 class="text-2xl text-center m-4">
                    Help make your community cleaner by reporting environmental issues in your area.<br />
                    Together, let's build a healthier future for Nepal.
                </h3>
                <div class="flex mb-20">
                    <a href="/GreenBin/register"
                        class="m-4 w-70 bg-green-600 hover:bg-gray-700 text-white text-center font-bold p-4 rounded-lg shadow-sm transition duration-300">
                        Start Reporting
                    </a>
                    <a href="/GreenBin/login"
                        class="m-4 w-70 bg-white hover:bg-gray-400 text-black text-center font-bold p-4 rounded-lg shadow-sm transition duration-300">
                        I have an account
                    </a>
                </div>
            <?php endif; ?>
        </div>
    </section>

    <!-- 3rd page -->

    <div class="items-center p-2 justify-center flex flex-col">
        <?php if ($lang === 'np'): ?>
            <h4 class="text-5xl font-bold text-center m-4 mt-20">
                हरित नेपाल कसरी काम गर्छ?
            </h4>
            <h5 class="text-2xl text-center m-4">How Green Nepal Works</h5>
        <?php else: ?>
            <h4 class="text-5xl font-bold text-center m-4 mt-20">
                How Green Nepal Works
            </h4>
            <h5 class="text-2xl text-center m-4">How Green Nepal Works</h5>
        <?php endif; ?>
    </div>

    <!-- 3rd page 3 container -->

    <div class="flex p-2 justify-between py-4">
        <?php if ($lang === 'np'): ?>
            <div
                class="m-4 w-110 bg-white hover:bg-gray-50 text-black text-center p-4 rounded-lg shadow-sm transition duration-300 items-center p-2 justify-center flex flex-col">
                <h6 class="text-2xl font-bold text-center">
                    समस्या रिपोर्ट गर्नुहोस्
                </h6>

                <h7 class="text-1xl text-center m-4">Report issues</h7>

                <h8 class="text-1xl text-center m-4">
                    आफ्नो क्षेत्रमा देखिएका वातावरणीय समस्याहरूको विस्तृत विवरण र फोटोसहित
                    रिपोर्ट पेश गर्नुहोस्।<br />
                    Submit detailed reports with photos and descriptions of environmental
                    concerns in your area.
                </h8>
            </div>

            <div
                class="m-4 w-110 bg-white hover:bg-gray-50 text-black text-center p-4 rounded-lg shadow-sm transition duration-300 items-center p-2 justify-center flex flex-col">
                <h9 class="text-2xl font-bold text-center"> स्थान पहिचान </h9>

                <h10 class="text-1xl text-center m-4">Location Tracking</h10>

                <h11 class="text-1xl text-center m-4">
                    समस्याको सही स्थान पहिचान गरी सम्बन्धित निकायलाई छिटो र प्रभावकारी
                    कारबाहीमा मद्दत गर्नुहोस्।<br />
                    Automatically capture location data to help authorities respond
                    quickly and effectively.
                </h11>
            </div>

            <div
                class="m-4 w-110 bg-white hover:bg-gray-50 text-black text-center p-4 rounded-lg shadow-sm transition duration-300 items-center p-2 justify-center flex flex-col">
                <h12 class="text-2xl font-bold text-center "> सामुदायिक प्रभाव </h12>
                <h13 class="text-1xl text-center m-4">Community Impact</h13>

                <h14 class="text-1xl text-center m-4">
                    आफ्ना रिपोर्टहरूको स्थिति हेर्नुहोस् र तपाईंको योगदानले गरेको
                    सकारात्मक प्रभाव देख्नुहोस्। Track the status of your reports and see
                    the positive impact your contributions make.
                </h14>
            </div>
        <?php else: ?>
            <div
                class="m-4 w-110 bg-white hover:bg-gray-50 text-black text-center p-4 rounded-lg shadow-sm transition duration-300 items-center p-2 justify-center flex flex-col">
                <h6 class="text-2xl font-bold text-center">
                    Report issues
                </h6>

                <h7 class="text-1xl text-center m-4">Report issues</h7>

                <h8 class="text-1xl text-center m-4">
                    Submit detailed reports with photos and descriptions of environmental
                    concerns in your area.
                </h8>
            </div>

            <div
                class="m-4 w-110 bg-white hover:bg-gray-50 text-black text-center p-4 rounded-lg shadow-sm transition duration-300 items-center p-2 justify-center flex flex-col">
                <h9 class="text-2xl font-bold text-center"> Location Tracking </h9>

                <h10 class="text-1xl text-center m-4">Location Tracking</h10>

                <h11 class="text-1xl text-center m-4">
                    Automatically capture location data to help authorities respond
                    quickly and effectively.
                </h11>
            </div>

            <div
                class="m-4 w-110 bg-white hover:bg-gray-50 text-black text-center p-4 rounded-lg shadow-sm transition duration-300 items-center p-2 justify-center flex flex-col">
                <h12 class="text-2xl font-bold text-center "> Community Impact </h12>
                <h13 class="text-1xl text-center m-4">Community Impact</h13>

                <h14 class="text-1xl text-center m-4">
                    Track the status of your reports and see
                    the positive impact your contributions make.
                </h14>
            </div>
        <?php endif; ?>
    </div>

    <!-- 4th page -->

    <section>
        <div class="page4 items-center p-2 justify-center flex flex-col">
            <?php if ($lang === 'np'): ?>
                <h1 class="text-5xl font-bold text-center m-4 mt-20">
                    नेपालको लागि बनाइएको
                </h1>
                <h2 class="text-2xl text-center m-4">
                    नेपालको वातावरणीय समस्या रिपोर्ट गर्नुहोस्
                </h2>
                <h3 class="text-1xl text-center m-4">
                    हिमालय देखि तराई सम्म, नेपालका सबै भौगोलिक क्षेत्रका वातावरणीय चुनौतीहरूलाई सम्बोधन गर्न डिजाइन <br>
                    गरिएको। From the Himalayas to the Terai, designed to address environmental challenges across<br>
                    all geographical regions of Nepal.
                </h3>

                <!-- 4th page 3 container -->

                <div class="flex gap-2 justify-between m-4 p-2 ">

                    <div>
                        <h12 class="m-4 text-green-600 flex text-4xl font-bold text-center px-15">७७</h12>
                        <h13 class="m-4 text-center">जिल्लाहरू / Districts</h13>
                    </div>

                    <div>
                        <h12 class="m-4 text-green-600 flex text-4xl font-bold text-center px-15"> ७</h12>
                        <h13 class="m-4 text-1xl text-center">प्रदेशहरू / Provinces</h13>
                    </div>

                    <div>
                        <h2 class="m-4 flex text-4xl text-green-600 font-bold text-center px-20">२९+</h2>
                        <h3 class="m-4 text-center">मिलियन नागरिकहरू / Million Citizens</h3>
                    </div>

                </div>

            <?php else: ?>
                <h1 class="text-5xl font-bold text-center m-4 mt-20">
                    Made for Nepal
                </h1>
                <h2 class="text-2xl text-center m-4">
                    Report environmental issues in Nepal
                </h2>
                <h3 class="text-1xl text-center m-4">
                    From the Himalayas to the Terai, designed to address environmental challenges across all geographical
                    regions of Nepal.
                </h3>

                <!-- 4th page 3 container -->

                <div class="flex gap-2 justify-between m-4 p-2 ">

                    <div>
                        <h12 class="m-4 text-green-600 flex text-4xl font-bold text-center px-15">77</h12>
                        <h13 class="m-4 text-center">Districts</h13>
                    </div>

                    <div>
                        <h12 class="m-4 text-green-600 flex text-4xl font-bold text-center px-15">7</h12>
                        <h13 class="m-4 text-1xl text-center">Provinces</h13>
                    </div>

                    <div>
                        <h2 class="m-4 flex text-4xl text-green-600 font-bold text-center px-20">29+</h2>
                        <h3 class="m-4 text-center">Million Citizens</h3>
                    </div>

                </div>
            <?php endif; ?>
        </div>
    </section>

    <!-- button -->

    <section>
        <div class="page5 text-white items-center p-2 justify-center flex flex-col">
            <?php if ($lang === 'np'): ?>
                <h1 class="text-3xl font-bold text-center m-4">
                    हरित नेपाल|GreenBin Nepal
                </h1>
                <h2 class="text-1xl text-center">
                    हाम्रो समुदायलाई स्वच्छ बनाउन, एक पटकमा एक रिपोर्ट।
                </h2>
                <h3 class="text-1xl text-center mb-4">
                    Making our communities cleaner, one report at a time.
                </h3>
            <?php else: ?>
                <h1 class="text-3xl font-bold text-center m-4">
                    GreenBin Nepal
                </h1>
                <h2 class="text-1xl text-center">
                    Making our communities cleaner, one report at a time.
                </h2>
                <h3 class="text-1xl text-center mb-4">
                    Making our communities cleaner, one report at a time.
                </h3>
            <?php endif; ?>
        </div>
    </section>

    <a href="index.html"></a>
</body>

</html>