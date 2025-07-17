document.addEventListener("DOMContentLoaded", () => {
  // ===================== SELECT IMPORTANT DOM ELEMENTS =====================
  const form = document.querySelector(".form-container"); // Report submission form
  const uploadArea = document.getElementById("uploadArea"); // Upload box area
  const fileInput = document.getElementById("fileInput"); // File input for photo
  const reportsList = document.getElementById("reportsList"); // Container to display reports
  const noReports = document.getElementById("noReports"); // "No reports found" message

  // ===================== LOAD USER REPORTS FROM DATABASE =====================
  async function loadUserReports() {
    try {
      const res = await fetch("/GreenBin/backend/getReports.php");

      // Check for HTTP errors
      if (!res.ok) {
        throw new Error(`HTTP error! status: ${res.status}`);
      }

      const data = await res.json();
      reportsList.innerHTML = ""; // Clear existing reports before re-rendering

      if (data.success && data.reports.length > 0) {
        // Reports exist
        noReports.style.display = "none";
        reportsList.style.display = "grid";
        data.reports.forEach((report) => addReportToUI(report));
      } else {
        // No reports found
        noReports.style.display = "block";
        reportsList.style.display = "none";
      }
    } catch (err) {
      // Display "no reports" message if AJAX fails
      noReports.style.display = "block";
      reportsList.style.display = "none";
      console.error("Failed to load reports:", err);
    }
  }

  // ===================== RENDER OR UPDATE A REPORT CARD IN THE UI =====================
  function addReportToUI(report, replaceExisting = false) {
    const imageUrl = report.image_path
      ? `/GreenBin/uploads/${report.image_path}`
      : "/GreenBin/frontend/img/default-report.png";

    let existingCard = reportsList.querySelector(
      `[data-report-id="${report.report_id}"]`
    );

    // === If report already exists, update its content instead of creating a new one ===
    if (replaceExisting && existingCard) {
      existingCard.innerHTML = `
        <div class="report-image">
          <img src="${imageUrl}" alt="Report image" class="w-30 h-20 object-cover rounded" />
        </div>
        <div class="report-content flex-1">
          <h3 class="font-semibold text-lg mb-1">${escapeHtml(
            report.title
          )}</h3>
          <p class="text-gray-700 mb-2">${nl2br(
            escapeHtml(report.description)
          )}</p>
          <div class="text-sm text-gray-500 flex gap-4">
            <span>Status: <strong>${escapeHtml(report.status)}</strong></span>
            <span>Date: ${formatDate(report.date)}</span>
            <span>Location: ${escapeHtml(report.location)}</span>
          </div>
          <div class="mt-2 flex gap-3">
            <button class="edit-btn text-blue-600 hover:underline text-sm">Edit</button>
            <button class="delete-btn text-red-600 hover:underline text-sm">Delete</button>
          </div>
        </div>
      `;

      // Re-bind event listeners after updating the DOM
      existingCard
        .querySelector(".edit-btn")
        .addEventListener("click", () => window.openEditModal(report));
      existingCard
        .querySelector(".delete-btn")
        .addEventListener("click", () => deleteReport(report.report_id));
      return;
    }

    // === Create a new report card for new submissions ===
    const reportCard = document.createElement("div");
    reportCard.className =
      "report-card flex gap-4 border p-4 rounded bg-white shadow-sm";
    reportCard.dataset.reportId = report.report_id;

    reportCard.innerHTML = `
      <div class="report-image">
        <img src="${imageUrl}" alt="Report image" class="w-30 h-20 object-cover rounded" />
      </div>
      <div class="report-content flex-1">
        <h3 class="font-semibold text-lg mb-1">${escapeHtml(report.title)}</h3>
        <p class="text-gray-700 mb-2">${nl2br(
          escapeHtml(report.description)
        )}</p>
        <div class="text-sm text-gray-500 flex gap-4">
          <span>Status: <strong>${escapeHtml(report.status)}</strong></span>
          <span>Date: ${formatDate(report.date)}</span>
          <span>Location: ${escapeHtml(report.location)}</span>
        </div>
        <div class="mt-2 flex gap-3">
          <button class="edit-btn text-blue-600 hover:underline text-sm">Edit</button>
          <button class="delete-btn text-red-600 hover:underline text-sm">Delete</button>
        </div>
      </div>
    `;

    // Bind Edit & Delete events
    reportCard
      .querySelector(".edit-btn")
      .addEventListener("click", () => window.openEditModal(report));
    reportCard
      .querySelector(".delete-btn")
      .addEventListener("click", () => deleteReport(report.report_id));

    // Add card to the top of the list
    reportsList.prepend(reportCard);
    noReports.style.display = "none";
    reportsList.style.display = "grid";
  }

  // ===================== HELPER FUNCTIONS =====================
  function escapeHtml(text) {
    // Prevent XSS attacks by escaping HTML
    return (
      text?.replace(
        /[&<>"']/g,
        (char) =>
          ({
            "&": "&amp;",
            "<": "&lt;",
            ">": "&gt;",
            '"': "&quot;",
            "'": "&#39;",
          }[char])
      ) || ""
    );
  }

  function nl2br(str) {
    // Replace newlines with <br> tags for description formatting
    return str.replace(/\n/g, "<br>");
  }

  function formatDate(dateStr) {
    // Format date to: "Jan 1, 2025"
    const d = new Date(dateStr);
    return isNaN(d)
      ? ""
      : d.toLocaleDateString("en-US", {
          month: "short",
          day: "numeric",
          year: "numeric",
        });
  }

  // ===================== DELETE REPORT =====================
  async function deleteReport(reportId) {
    if (!confirm("Are you sure you want to delete this report?")) return;

    try {
      const response = await fetch("/GreenBin/backend/deleteReport.php", {
        method: "POST",
        headers: { "Content-Type": "application/x-www-form-urlencoded" },
        body: `reportId=${encodeURIComponent(reportId)}`,
      });

      const result = await response.json();
      if (result.success) {
        const card = reportsList.querySelector(
          `[data-report-id="${reportId}"]`
        );
        if (card) card.remove();

        if (reportsList.children.length === 0) {
          noReports.style.display = "block";
          reportsList.style.display = "none";
        }
      }
    } catch (err) {
      console.error("Delete failed:", err);
    }
  }

  // ===================== AUTO-ADD GEOLOCATION TO FORM =====================
  function tryGeolocation() {
    if (!navigator.geolocation) return;

    navigator.geolocation.getCurrentPosition(
      (pos) => {
        const coords = `${pos.coords.latitude},${pos.coords.longitude}`;
        const hiddenInput = document.createElement("input");
        hiddenInput.type = "hidden";
        hiddenInput.name = "location";
        hiddenInput.value = coords;
        form.appendChild(hiddenInput);
      },
      () => {
        console.error("Geolocation unavailable");
      },
      { timeout: 10000 }
    );
  }

  // ===================== SUBMIT REPORT FORM (AJAX) =====================
  if (form) {
    form.addEventListener("submit", async (e) => {
      e.preventDefault();
      const formData = new FormData(form);
      const errorBox = form.querySelector(".form-error");

      try {
        const res = await fetch(form.action, {
          method: "POST",
          body: formData,
        });

        if (!res.ok) {
          throw new Error(`HTTP error! status: ${res.status}`);
        }

        const data = await res.json();

        if (data.success) {
          // Clear form & reset upload text
          form.reset();
          uploadArea.querySelector("p").textContent =
            "Click to upload or drag and drop";
          errorBox.classList.add("hidden");

          // ✅ Show the new report instantly (No reload)
          addReportToUI(data.reportsList);
        } else {
          errorBox.textContent = data.message || "Submission failed.";
          errorBox.classList.remove("hidden");
        }
      } catch (err) {
        errorBox.textContent = "Error: " + err.message;
        errorBox.classList.remove("hidden");
      }
    });
  }

  // ===================== HANDLE FILE INPUT DISPLAY =====================
  if (uploadArea && fileInput) {
    uploadArea.addEventListener("click", () => fileInput.click());
    fileInput.addEventListener("change", () => {
      uploadArea.querySelector("p").textContent =
        fileInput.files[0]?.name || "Click to upload or drag and drop";
    });
  }

  // ===================== INIT ON PAGE LOAD =====================
  tryGeolocation();
  loadUserReports();
});

// ===================== EDIT MODAL HANDLERS =====================
window.openEditModal = function (report) {
  document.getElementById("editModal").classList.remove("hidden");
  document.getElementById("editReportId").value = report.report_id;
  document.getElementById("editTitle").value = report.title;
  document.getElementById("editDescription").value = report.description;
  document.getElementById("editLocation").value = report.location;
  document.getElementById("editPhoto").value = "";
  document.getElementById("editError").classList.add("hidden");
};

window.closeEditModal = function () {
  document.getElementById("editModal").classList.add("hidden");
};

// ===================== EDIT REPORT FORM (AJAX UPDATE) =====================
document
  .getElementById("editReportForm")
  .addEventListener("submit", async function (e) {
    e.preventDefault();

    const form = e.target;
    const formData = new FormData(form);
    const errorBox = document.getElementById("editError");

    if (!formData.get("title") || !formData.get("description")) {
      errorBox.textContent = "Title and description are required.";
      errorBox.classList.remove("hidden");
      return;
    }

    try {
      const res = await fetch("/GreenBin/backend/editReport.php", {
        method: "POST",
        body: formData,
      });

      if (!res.ok) {
        throw new Error(`HTTP error! status: ${res.status}`);
      }

      const result = await res.json();
      if (result.success) {
        closeEditModal();

        // ✅ Update existing report instantly (No reload)
        addReportToUI(result.reportsList, true);
      } else {
        errorBox.textContent = result.message || "Failed to update.";
        errorBox.classList.remove("hidden");
      }
    } catch (err) {
      errorBox.textContent = "Error: " + err.message;
      errorBox.classList.remove("hidden");
    }
  });
