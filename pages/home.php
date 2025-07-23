<?php require_once 'includes/header.php'; ?>

  <!-- ✅ HERO -->
  <section class="text-center max-w-3xl mx-auto mt-16 px-4 fade-in">
    <?php if ($lang === 'np'): ?>
      <h2 class="text-4xl font-bold text-green-700 mb-4">नेपालको वातावरणीय समस्या रिपोर्ट गर्नुहोस्</h2>
      <p class="text-gray-600 mb-6 text-lg">आफ्नो समुदायलाई सफा बनाउन सहयोग गर्नुहोस्। सँगै, नेपालको स्वस्थ भविष्य बनाऔं।
      </p>
      <div class="flex justify-center gap-4">
        <a href="/GreenBin/register"
          class="bg-green-600 text-white px-6 py-3 rounded-md shadow hover:bg-green-700 transition">रिपोर्ट सुरु
          गर्नुहोस्</a>
        <a href="/GreenBin/login" class="bg-white border px-6 py-3 rounded-md shadow hover:bg-gray-100 transition">मेरो
          खाता छ</a>
      </div>
    <?php else: ?>
      <h2 class="text-4xl font-bold text-green-700 mb-4">Report Environmental Issues in Nepal</h2>
      <p class="text-gray-600 mb-6 text-lg">Help make your community cleaner. Together, let's build a healthier future for
        Nepal.</p>
      <div class="flex justify-center gap-4">
        <a href="/GreenBin/register"
          class="bg-green-600 text-white px-6 py-3 rounded-md shadow hover:bg-green-700 transition">Start Reporting</a>
        <a href="/GreenBin/login" class="bg-white border px-6 py-3 rounded-md shadow hover:bg-gray-100 transition">I have
          an account</a>
      </div>
    <?php endif; ?>
  </section>

  <!-- ✅ HOW IT WORKS -->
  <section class="max-w-5xl mx-auto mt-20 px-4 fade-in">
    <h3 class="text-3xl font-bold text-center text-green-700 mb-10">
      <?= $lang === 'np' ? 'यो कसरी काम गर्छ?' : 'How It Works' ?>
    </h3>
    <div class="grid md:grid-cols-3 gap-6">
      <div class="bg-white p-6 rounded-lg shadow hover-lift">
        <h4 class="text-lg font-bold text-green-700 mb-2">
          <?= $lang === 'np' ? '१. समस्या रिपोर्ट' : '1. Report Issue' ?>
        </h4>
        <p class="text-sm text-gray-600"><?= $lang === 'np'
          ? 'तस्वीर र विवरण सहित रिपोर्ट पेश गर्नुहोस्।'
          : 'Submit detailed reports with photos & description.' ?></p>
      </div>
      <div class="bg-white p-6 rounded-lg shadow hover-lift">
        <h4 class="text-lg font-bold text-green-700 mb-2">
          <?= $lang === 'np' ? '२. स्थान ट्र्याकिङ' : '2. Location Tracking' ?>
        </h4>
        <p class="text-sm text-gray-600"><?= $lang === 'np'
          ? 'स्वचालित रूपमा स्थान कैप्चर हुन्छ।'
          : 'Location auto-captured for accurate response.' ?></p>
      </div>
      <div class="bg-white p-6 rounded-lg shadow hover-lift">
        <h4 class="text-lg font-bold text-green-700 mb-2">
          <?= $lang === 'np' ? '३. समाधान र अपडेट' : '3. Solution Updates' ?>
        </h4>
        <p class="text-sm text-gray-600"><?= $lang === 'np'
          ? 'समाधानको स्थिति हेर्न सकिन्छ।'
          : 'Track real-time updates of your reports.' ?></p>
      </div>
    </div>
  </section>

  <!-- ✅ TESTIMONIALS -->
  <section class="bg-green-50 py-12 mt-20 fade-in">
    <div class="max-w-5xl mx-auto text-center">
      <h3 class="text-3xl font-bold text-green-700 mb-6">
        <?= $lang === 'np' ? 'हाम्रा प्रयोगकर्ताहरूको अनुभव' : 'What People Say' ?>
      </h3>
      <div class="grid md:grid-cols-3 gap-6 text-left">
        <div class="bg-white p-6 rounded-lg shadow hover-lift">
          <p class="text-sm text-gray-700">"GreenBin Nepal has helped our community solve waste management issues faster
            than ever."</p>
          <p class="mt-3 font-bold text-green-700">- Sita Rai</p>
        </div>
        <div class="bg-white p-6 rounded-lg shadow hover-lift">
          <p class="text-sm text-gray-700">"I reported a garbage issue, and it was cleaned within 3 days. Amazing
            initiative!"</p>
          <p class="mt-3 font-bold text-green-700">- Prakash Thapa</p>
        </div>
        <div class="bg-white p-6 rounded-lg shadow hover-lift">
          <p class="text-sm text-gray-700">"This platform is easy to use and gives real updates about our reports."</p>
          <p class="mt-3 font-bold text-green-700">- Anjali Gurung</p>
        </div>
      </div>
    </div>
  </section>

  <!-- ✅ FAQ -->
  <section class="max-w-4xl mx-auto mt-20 px-4 fade-in">
    <h3 class="text-3xl font-bold text-center text-green-700 mb-6">
      <?= $lang === 'np' ? 'प्रश्नोत्तर' : 'Frequently Asked Questions' ?>
    </h3>
    <div class="space-y-4">
      <div class="accordion bg-white p-4 rounded-lg shadow cursor-pointer">
        <h4 class="font-semibold"><?= $lang === 'np' ? 'कसरी रिपोर्ट गर्ने?' : 'How do I report an issue?' ?></h4>
        <div class="accordion-content text-gray-600 text-sm mt-2">
          <?= $lang === 'np'
            ? 'साइन अप गरेर, समस्या विवरण र तस्वीर अपलोड गर्नुहोस्।'
            : 'Sign up, fill details, and upload photos.' ?>
        </div>
      </div>
      <div class="accordion bg-white p-4 rounded-lg shadow cursor-pointer">
        <h4 class="font-semibold"><?= $lang === 'np' ? 'के यो निःशुल्क छ?' : 'Is it free?' ?></h4>
        <div class="accordion-content text-gray-600 text-sm mt-2">
          <?= $lang === 'np'
            ? 'हो, यो पूर्ण रूपमा निःशुल्क छ।'
            : 'Yes, it’s completely free for everyone.' ?>
        </div>
      </div>
    </div>
  </section>

  <!-- ✅ PARTNERS -->
  <section class="bg-gray-100 py-12 mt-20 fade-in">
    <div class="max-w-5xl mx-auto text-center">
      <h3 class="text-3xl font-bold text-green-700 mb-6">
        <?= $lang === 'np' ? 'हाम्रा साझेदारहरू' : 'Our Partners & Supporters' ?>
      </h3>
      <div class="grid grid-cols-2 md:grid-cols-4 gap-6 items-center">
        <img src="/GreenBin/frontend/img/partner1.png" class="mx-auto w-28 grayscale hover:grayscale-0 transition"
          alt="Partner Logo 1">
        <img src="/GreenBin/frontend/img/partner2.png" class="mx-auto w-28 grayscale hover:grayscale-0 transition"
          alt="Partner Logo 2">
        <img src="/GreenBin/frontend/img/partner3.png" class="mx-auto w-28 grayscale hover:grayscale-0 transition"
          alt="Partner Logo 3">
        <img src="/GreenBin/frontend/img/partner4.png" class="mx-auto w-28 grayscale hover:grayscale-0 transition"
          alt="Partner Logo 4">
      </div>
    </div>
  </section>

  <!-- ✅ CALL TO ACTION -->
  <section class="text-center py-12 mt-10 fade-in">
    <h3 class="text-2xl font-bold text-green-700 mb-4">
      <?= $lang === 'np' ? 'हामीसँग जोडिनुहोस्' : 'Join the Movement' ?>
    </h3>
    <p class="text-gray-600 mb-6"><?= $lang === 'np'
      ? 'आफ्नो क्षेत्रको समस्या रिपोर्ट गर्नुहोस् र समाधानको हिस्सा बन्नुहोस्।'
      : 'Report issues in your area & be part of the solution.' ?></p>
    <a href="/GreenBin/register"
      class="bg-green-600 text-white px-6 py-3 rounded-md shadow hover:bg-green-700 transition"><?= $lang === 'np' ? 'सुरु गर्नुहोस्' : 'Get Started' ?></a>
  </section>

<?php require_once 'includes/footer.php'; ?>
