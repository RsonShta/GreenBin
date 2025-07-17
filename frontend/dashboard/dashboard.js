document.addEventListener("DOMContentLoaded", () => {
  // === NEW: Show/Hide Report Submission Form on button clicks ===
  const showFormBtn = document.getElementById("showReportFormBtn");
  const reportFormSection = document.getElementById("reportFormSection");
  const cancelReportFormBtn = document.getElementById("cancelReportFormBtn");

  if (showFormBtn && reportFormSection) {
    showFormBtn.addEventListener("click", () => {
      reportFormSection.classList.remove("hidden");
      reportFormSection.scrollIntoView({ behavior: "smooth" });
    });
  }

  if (cancelReportFormBtn && reportFormSection) {
    cancelReportFormBtn.addEventListener("click", () => {
      reportFormSection.classList.add("hidden");
    });
  }

  // === DOM ELEMENTS ===
  const form = document.querySelector(".form-container"); // The new report submission form
  const uploadArea = document.getElementById("uploadArea"); // The upload box area
  const fileInput = document.getElementById("fileInput"); // The file input element for photos
  const reportsList = document.getElementById("reportsList"); // Container where reports are displayed
  const noReports = document.getElementById("noReports"); // Element showing "No reports found" message

  // === State: Map to track currently displayed reports and their content hashes
  // Keys: report_id, Values: simple string hash representing report content to detect changes
  let currentReportsMap = new Map();

  // === Utility function to safely escape HTML special characters to prevent XSS
  function escapeHtml(text) {
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

  // === Converts newlines to <br> tags for HTML display
  function nl2br(str) {
    return str.replace(/\n/g, "<br>");
  }

  // === Formats a date string into a readable format (e.g. "Jul 17, 2025")
  function formatDate(dateStr) {
    const d = new Date(dateStr);
    return isNaN(d)
      ? ""
      : d.toLocaleDateString("en-US", {
          month: "short",
          day: "numeric",
          year: "numeric",
        });
  }

  // === Generate a simple hash string representing the important parts of a report
  // Used to detect changes when polling new data from server
  function hashReport(report) {
    return [
      report.title,
      report.description,
      report.location,
      report.status,
      report.date,
      report.image_path ?? "",
    ].join("|");
  }

  /**
   * Adds a new report card or updates an existing one in the UI.
   * @param {Object} report - The report data object
   * @param {boolean} replaceExisting - If true, update the existing card instead of creating a new one
   */
  function addReportToUI(report, replaceExisting = false) {
    // Determine image URL (use default if none)
    const imageUrl = report.image_path
      ? `/GreenBin/uploads/${report.image_path}`
      : "/GreenBin/frontend/img/default-report.png";

    // Try to find existing card for this report ID in the DOM
    let existingCard = reportsList.querySelector(
      `[data-report-id="${report.report_id}"]`
    );

    if (replaceExisting && existingCard) {
      // If we want to update an existing card, modify its content
      existingCard.querySelector("img").src = imageUrl;
      existingCard.querySelector("h3").textContent = escapeHtml(report.title);
      existingCard.querySelector("p").innerHTML = nl2br(
        escapeHtml(report.description)
      );
      existingCard.querySelector(".text-gray-500").innerHTML = `
        <span>Status: <strong>${escapeHtml(report.status)}</strong></span>
        <span>Date: ${formatDate(report.date)}</span>
        <span>Location: ${escapeHtml(report.location)}</span>
      `;
      return;
    }

    // Otherwise, create a new card element
    const reportCard = document.createElement("div");
    reportCard.className =
      "report-card flex gap-4 border p-4 rounded bg-white shadow-sm";
    reportCard.dataset.reportId = report.report_id;

    // Set inner HTML for the card
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

    // Bind click event for edit button to open modal with current report data
    reportCard
      .querySelector(".edit-btn")
      .addEventListener("click", () => window.openEditModal(report));

    // Bind click event for delete button to delete report
    reportCard
      .querySelector(".delete-btn")
      .addEventListener("click", () => deleteReport(report.report_id));

    // Prepend new card to the top of the reports list
    reportsList.prepend(reportCard);

    // Ensure UI reflects that reports exist
    noReports.style.display = "none";
    reportsList.style.display = "grid";
  }

  /**
   * Load all user reports from server and update UI intelligently:
   * - Remove cards that no longer exist
   * - Add new cards
   * - Update cards with changed content
   */
  async function loadUserReports() {
    try {
      const res = await fetch("/GreenBin/backend/getReports.php");

      if (!res.ok) throw new Error(`HTTP error! status: ${res.status}`);
      const data = await res.json();

      if (data.success && data.reports.length > 0) {
        noReports.style.display = "none";
        reportsList.style.display = "grid";

        // Build a map of reports fetched from server (report_id => report object)
        const fetchedReportsMap = new Map();
        data.reports.forEach((report) => {
          fetchedReportsMap.set(report.report_id, report);
        });

        // Remove cards that are no longer on server (deleted)
        for (const [existingId] of currentReportsMap.entries()) {
          if (!fetchedReportsMap.has(existingId)) {
            const cardToRemove = reportsList.querySelector(
              `[data-report-id="${existingId}"]`
            );
            if (cardToRemove) cardToRemove.remove();
            currentReportsMap.delete(existingId);
          }
        }

        // Add new cards or update changed cards
        for (const report of data.reports) {
          const reportId = report.report_id;
          const newHash = hashReport(report);

          if (!currentReportsMap.has(reportId)) {
            // New report - add card
            addReportToUI(report);
            currentReportsMap.set(reportId, newHash);
          } else if (currentReportsMap.get(reportId) !== newHash) {
            // Existing report but content changed - update card
            addReportToUI(report, true);
            currentReportsMap.set(reportId, newHash);
          }
          // If hash matches, do nothing (no changes)
        }
      } else {
        // No reports found - clear UI and state
        noReports.style.display = "block";
        reportsList.style.display = "none";
        reportsList.innerHTML = "";
        currentReportsMap.clear();
      }
    } catch (err) {
      console.error("Failed to load reports:", err);
      noReports.style.display = "block";
      reportsList.style.display = "none";
    }
  }

  /**
   * Delete a report by its ID, update UI and state map accordingly
   * @param {number} reportId
   */
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
        // Remove card from UI and from currentReportsMap state
        const card = reportsList.querySelector(
          `[data-report-id="${reportId}"]`
        );
        if (card) card.remove();
        currentReportsMap.delete(reportId);

        // If no reports left, show noReports message
        if (reportsList.children.length === 0) {
          noReports.style.display = "block";
          reportsList.style.display = "none";
        }
      }
    } catch (err) {
      console.error("Delete failed:", err);
    }
  }

  /**
   * Optionally try to get user's geolocation and append it as hidden input to form
   */
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
      () => console.error("Geolocation unavailable"),
      { timeout: 10000 }
    );
  }

  /**
   * Handle submission of new report form via AJAX.
   * On success, immediately adds the new report card to UI and updates state map.
   */
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

        if (!res.ok) throw new Error(`HTTP error! status: ${res.status}`);
        const data = await res.json();

        if (data.success) {
          form.reset();
          uploadArea.querySelector("p").textContent =
            "Click to upload or drag and drop";
          errorBox.classList.add("hidden");

          // Add new report to UI and update state
          addReportToUI(data.report);
          currentReportsMap.set(data.report.report_id, hashReport(data.report));
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

  // Handle file input UI updates
  if (uploadArea && fileInput) {
    uploadArea.addEventListener("click", () => fileInput.click());
    fileInput.addEventListener("change", () => {
      uploadArea.querySelector("p").textContent =
        fileInput.files[0]?.name || "Click to upload or drag and drop";
    });
  }

  // === Edit modal open/close handlers ===
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

  /**
   * Handle submission of the edit form via AJAX.
   * On success, updates the corresponding report card instantly.
   */
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

        if (!res.ok) throw new Error(`HTTP error! status: ${res.status}`);
        const result = await res.json();

        if (result.success) {
          closeEditModal();
          addReportToUI(result.report, true); // update card instantly
          currentReportsMap.set(
            result.report.report_id,
            hashReport(result.report)
          ); // update state map
        } else {
          errorBox.textContent = result.message || "Failed to update.";
          errorBox.classList.remove("hidden");
        }
      } catch (err) {
        errorBox.textContent = "Error: " + err.message;
        errorBox.classList.remove("hidden");
      }
    });

  // Initial load of reports and try geolocation
  tryGeolocation();
  loadUserReports();

  // Set interval to poll every 1 second and update UI accordingly
  setInterval(loadUserReports, 1000);
});
