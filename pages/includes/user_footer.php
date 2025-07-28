<script src="https://kit.fontawesome.com/3f471bb5a5.js" crossorigin="anonymous"></script>
  <script src="/GreenBin/frontend/dashboard/dashboard.js" defer></script>
  <script src="https://maps.googleapis.com/maps/api/js?key=YOUR_GOOGLE_MAPS_API_KEY&callback=initMap" async
    defer></script>

  <!-- Edit Report Modal -->
  <div id="editModal" class="fixed inset-0 bg-black bg-opacity-40 flex items-center justify-center z-50 hidden">
    <div class="bg-white p-6 rounded-md w-full max-w-lg shadow-lg relative">
      <button id="closeEditModalBtn" class="absolute top-2 right-2 text-gray-600 text-xl">&times;</button>
      <h3 class="text-xl font-bold mb-4"><?= $lang === 'np' ? 'रिपोर्ट सम्पादन गर्नुहोस्' : 'Edit Report' ?></h3>
      <form id="editReportForm" class="space-y-4" enctype="multipart/form-data">
        <input type="hidden" name="reportId" id="editReportId" />
        <input type="hidden" name="existingImage" id="existingImage" />

        <div>
          <label for="editTitle" class="block text-sm font-medium mb-1">
            <?= $lang === 'np' ? 'शीर्षक' : 'Title' ?>
          </label>
          <input type="text" id="editTitle" name="title" required
            class="w-full border border-gray-300 p-2 rounded-md" />
        </div>

        <div>
          <label for="editDescription" class="block text-sm font-medium mb-1">
            <?= $lang === 'np' ? 'विवरण' : 'Description' ?>
          </label>
          <textarea id="editDescription" name="description" required rows="4"
            class="w-full border border-gray-300 p-2 rounded-md"></textarea>
        </div>

        <div>
          <label for="editLocation" class="block text-sm font-medium mb-1">
            <?= $lang === 'np' ? 'स्थान' : 'Location' ?>
          </label>
          <input type="text" id="editLocation" name="location" class="w-full border border-gray-300 p-2 rounded-md" />
        </div>

        <div>
          <label class="block text-sm font-medium mb-1">
            <?= $lang === 'np' ? 'नयाँ फोटो (वैकल्पिक)' : 'New Photo (optional)' ?>
          </label>
          <input type="file" name="photo" id="editPhoto" accept="image/*"
            class="w-full border border-dashed p-2 rounded-md" />
        </div>

        <div class="text-right">
          <button type="submit" class="bg-green-700 hover:bg-green-800 text-white px-4 py-2 rounded-md">
            <?= $lang === 'np' ? 'परिवर्तनहरू सुरक्षित गर्नुहोस्' : 'Save Changes' ?>
          </button>
        </div>
        <p id="editError" class="text-sm text-red-600 mt-2 hidden"></p>
      </form>
    </div>
  </div>

</body>

</html>
