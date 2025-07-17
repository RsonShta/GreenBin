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

  // Toast container for notifications
  let toastTimeout = null;
  function showToast(message) {
    let toast = document.getElementById("toastNotification");
    if (!toast) {
      toast = document.createElement("div");
      toast.id = "toastNotification";
      toast.style.position = "fixed";
      toast.style.bottom = "20px";
      toast.style.right = "20px";
      toast.style.background = "#2e7d32";
      toast.style.color = "white";
      toast.style.padding = "10px 20px";
      toast.style.borderRadius = "6px";
      toast.style.boxShadow = "0 2px 6px rgba(0,0,0,0.3)";
      toast.style.fontWeight = "600";
      toast.style.zIndex = "9999";
      document.body.appendChild(toast);
    }
    toast.textContent = message;
    toast.style.opacity = "1";

    if (toastTimeout) clearTimeout(toastTimeout);
    toastTimeout = setTimeout(() => {
      toast.style.opacity = "0";
    }, 3000);
  }

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
    const imageUrl = report.image_path
      ? `/GreenBin/uploads/${report.image_path}`
      : "/GreenBin/frontend/img/default-report.png";

    let existingCard = reportsList.querySelector(
      `[data-report-id="${report.report_id}"]`
    );

    if (replaceExisting && existingCard) {
      existingCard.querySelector("img").src = imageUrl;
      existingCard.querySelector("img").loading = "lazy"; // lazy load images
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

    // Create new card element
    const reportCard = document.createElement("div");
    reportCard.className =
      "report-card flex gap-4 border p-4 rounded bg-white shadow-sm";
    reportCard.dataset.reportId = report.report_id;

    reportCard.innerHTML = `
      <div class="report-image">
        <img src="${imageUrl}" alt="Report image" loading="lazy" class="w-30 h-20 object-cover rounded" />
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

    reportCard
      .querySelector(".edit-btn")
      .addEventListener("click", () => window.openEditModal(report));

    reportCard
      .querySelector(".delete-btn")
      .addEventListener("click", () => deleteReport(report.report_id));

    reportsList.prepend(reportCard);

    noReports.style.display = "none";
    reportsList.style.display = "grid";
  }

  /**
   * Load all user reports from server and update UI intelligently.
   */
  async function loadUserReports() {
    try {
      const res = await fetch("/GreenBin/backend/getReports.php");
      if (!res.ok) throw new Error(`HTTP error! status: ${res.status}`);
      const data = await res.json();

      if (data.success && data.reports.length > 0) {
        noReports.style.display = "none";
        reportsList.style.display = "grid";

        const fetchedReportsMap = new Map();
        data.reports.forEach((report) => {
          fetchedReportsMap.set(report.report_id, report);
        });

        // Remove deleted reports
        for (const existingId of currentReportsMap.keys()) {
          if (!fetchedReportsMap.has(existingId)) {
            const cardToRemove = reportsList.querySelector(
              `[data-report-id="${existingId}"]`
            );
            if (cardToRemove) cardToRemove.remove();
            currentReportsMap.delete(existingId);
          }
        }

        // Add new or update changed reports
        for (const report of data.reports) {
          const newHash = hashReport(report);
          if (!currentReportsMap.has(report.report_id)) {
            addReportToUI(report);
            currentReportsMap.set(report.report_id, newHash);
          } else if (currentReportsMap.get(report.report_id) !== newHash) {
            addReportToUI(report, true);
            currentReportsMap.set(report.report_id, newHash);
          }
        }
      } else {
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
   * Delete a report by its ID.
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
        const card = reportsList.querySelector(
          `[data-report-id="${reportId}"]`
        );
        if (card) card.remove();
        currentReportsMap.delete(reportId);

        if (reportsList.children.length === 0) {
          noReports.style.display = "block";
          reportsList.style.display = "none";
        }

        showToast("Report deleted successfully.");
      }
    } catch (err) {
      console.error("Delete failed:", err);
      showToast("Failed to delete report.");
    }
  }

  /**
   * Try to get user's geolocation and append as hidden input.
   */
  function tryGeolocation() {
    if (!navigator.geolocation) {
      console.warn("Geolocation not supported");
      return;
    }

    navigator.geolocation.getCurrentPosition(
      (pos) => {
        const coords = `${pos.coords.latitude},${pos.coords.longitude}`;
        // Remove any existing location inputs first
        const existingInput = form.querySelector('input[name="location"]');
        if (existingInput) existingInput.remove();

        const hiddenInput = document.createElement("input");
        hiddenInput.type = "hidden";
        hiddenInput.name = "location";
        hiddenInput.value = coords;
        form.appendChild(hiddenInput);
      },
      () => {
        console.error("Geolocation unavailable");
        showToast("Unable to get location. You can enter location manually.");
      },
      { timeout: 10000 }
    );
  }

  /**
   * Handle submission of new report form via AJAX.
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

          addReportToUI(data.report);
          currentReportsMap.set(data.report.report_id, hashReport(data.report));
          showToast("Report submitted successfully.");
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

  // File input UI updates
  if (uploadArea && fileInput) {
    uploadArea.addEventListener("click", () => fileInput.click());
    fileInput.addEventListener("change", () => {
      uploadArea.querySelector("p").textContent =
        fileInput.files[0]?.name || "Click to upload or drag and drop";
    });
  }

  // Edit modal open/close handlers
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
          addReportToUI(result.report, true);
          currentReportsMap.set(
            result.report.report_id,
            hashReport(result.report)
          );
          showToast("Report updated successfully.");
        } else {
          errorBox.textContent = result.message || "Failed to update.";
          errorBox.classList.remove("hidden");
        }
      } catch (err) {
        errorBox.textContent = "Error: " + err.message;
        errorBox.classList.remove("hidden");
      }
    });

  /**
   * Load dashboard stats and update UI
   */
  async function loadDashboardStats() {
    try {
      const res = await fetch("/GreenBin/backend/getStats.php");
      if (!res.ok) throw new Error("Network response was not ok");
      const data = await res.json();
      if (data.success) {
        document.getElementById("totalReports").textContent =
          data.stats.totalReports;
        document.getElementById("resolutionRate").textContent =
          data.stats.resolutionRate + "%";
        document.getElementById("resolvedCount").textContent =
          data.stats.resolvedCount + " resolved";
        document.getElementById("co2Reduction").textContent =
          data.stats.co2Reduction.toFixed(1) + " kg";
        document.getElementById("communityPoints").textContent =
          data.stats.communityPoints;
      }
    } catch (err) {
      console.error("Stats update failed:", err);
    }
  }

  // Initial load and geolocation attempt
  tryGeolocation();
  loadUserReports();
  loadDashboardStats();

  // Poll every 5 seconds instead of 1 second to reduce server load
  setInterval(() => {
    loadUserReports();
    loadDashboardStats();
  }, 5000);
});
