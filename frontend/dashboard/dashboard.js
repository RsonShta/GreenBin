document.addEventListener("DOMContentLoaded", () => {
  class Dashboard {
    constructor() {
      this.reportsTab = document.getElementById("reportsTab");
      this.reportsSection = document.getElementById("reportsSection");
      this.showFormBtn = document.getElementById("showReportFormBtn");
      this.reportFormSection = document.getElementById("reportFormSection");
      this.cancelReportFormBtn = document.getElementById("cancelReportFormBtn");
      this.form = document.querySelector("#reportForm, .form-container");
      this.uploadArea = document.getElementById("uploadArea");
      this.fileInput = document.getElementById("fileInput");
      this.reportsList = document.getElementById("reportsList");
      this.noReports = document.getElementById("noReports");
      this.editForm = document.getElementById("editReportForm");
      this.getLocationBtn = document.getElementById("getLocationBtn");
      this.closeModalBtn = document.getElementById("closeEditModalBtn");
      this.firstReportBtn = document.getElementById("firstReportBtn");


      this.toastTimeout = null;
      this.currentReportsMap = new Map();

      this.init();
    }

    init() {
      this.setupEventListeners();
      this.tryGeolocation();
      this.loadUserReports();
      this.loadDashboardStats();
      this.startPolling();
      this.initializeDefaultTab();
    }

    setupEventListeners() {
      if (this.reportsTab && this.reportsSection) {
        this.reportsTab.addEventListener("click", (e) => {
          e.preventDefault();
          this.switchTab(this.reportsTab, this.reportsSection);
        });
      }

      if (this.showFormBtn && this.reportFormSection) {
        this.showFormBtn.addEventListener("click", () => {
          this.reportFormSection.classList.remove("hidden");
          this.reportFormSection.scrollIntoView({ behavior: "smooth" });
        });
      }

      if (this.cancelReportFormBtn && this.reportFormSection) {
        this.cancelReportFormBtn.addEventListener("click", () => {
          this.reportFormSection.classList.add("hidden");
        });
      }

      if (this.form) {
        this.form.addEventListener("submit", (e) => this.handleFormSubmit(e));
      }

      if (this.uploadArea && this.fileInput) {
        this.uploadArea.addEventListener("click", () => this.fileInput.click());
        this.fileInput.addEventListener("change", () => this.updateFileInputUI());
      }

      if (this.editForm) {
        this.editForm.addEventListener("submit", (e) => this.handleEditFormSubmit(e));
      }
       if (this.closeModalBtn) {
        this.closeModalBtn.addEventListener("click", () => this.closeEditModal());
      }

      if (this.getLocationBtn) {
        this.getLocationBtn.addEventListener("click", () => this.tryGeolocation());
      }

      if (this.firstReportBtn) {
        this.firstReportBtn.addEventListener("click", () => {
          if (this.reportFormSection) {
            this.reportFormSection.classList.remove("hidden");
            this.reportFormSection.scrollIntoView({ behavior: "smooth" });
          }
        });
      }

       this.reportsList.addEventListener("click", (e) => {
        if (e.target.classList.contains("edit-btn")) {
          e.preventDefault();
          const reportId = e.target.closest(".report-card").dataset.reportId;
          this.openEditModalForReport(reportId);
        } else if (e.target.classList.contains("delete-btn")) {
          e.preventDefault();
          const reportId = e.target.closest(".report-card").dataset.reportId;
          this.deleteReport(reportId);
        }
      });
    }

    switchTab(activeTab, activeSection) {
      document.querySelectorAll(".tab-link").forEach((tab) => {
        tab.classList.remove("border-b-2", "border-green-700", "pb-1");
        tab.classList.add("hover:underline");
      });

      activeTab.classList.add("border-b-2", "border-green-700", "pb-1");
      activeTab.classList.remove("hover:underline");

      if (this.reportsSection) this.reportsSection.classList.add("hidden");

      if (activeSection) activeSection.classList.remove("hidden");
    }

    showToast(message, type = 'success') {
      let toast = document.getElementById("toastNotification");
      if (!toast) {
        toast = document.createElement("div");
        toast.id = "toastNotification";
        toast.style.position = "fixed";
        toast.style.bottom = "20px";
        toast.style.right = "20px";
        toast.style.padding = "12px 20px";
        toast.style.borderRadius = "6px";
        toast.style.boxShadow = "0 4px 8px rgba(0,0,0,0.3)";
        toast.style.fontWeight = "600";
        toast.style.zIndex = "9999";
        toast.style.transition = "opacity 0.3s ease";
        document.body.appendChild(toast);
      }

      if (type === 'error') {
        toast.style.background = "#dc2626";
        toast.style.color = "white";
      } else {
        toast.style.background = "#16a34a";
        toast.style.color = "white";
      }

      toast.textContent = message;
      toast.style.opacity = "1";

      if (this.toastTimeout) clearTimeout(this.toastTimeout);
      this.toastTimeout = setTimeout(() => {
        toast.style.opacity = "0";
      }, 3000);
    }

    escapeHtml(text) {
      if (!text) return "";
      const div = document.createElement('div');
      div.textContent = text;
      return div.innerHTML;
    }

    nl2br(str) {
      if (!str) return "";
      return str.replace(/\n/g, "<br>");
    }

    formatDate(dateStr) {
      if (!dateStr) return "";
      const d = new Date(dateStr);
      return isNaN(d) ? "" : d.toLocaleDateString("en-US", {
        month: "short",
        day: "numeric",
        year: "numeric",
      });
    }

    hashReport(report) {
      return [
        report.title || "",
        report.description || "",
        report.location || "",
        report.status || "",
        report.date || "",
        report.image_path || "",
      ].join("|");
    }

    addReportToUI(report, replaceExisting = false) {
      if (!this.reportsList) return;

      const imageUrl = report.image_path
        ? `/GreenBin/uploads/${report.image_path}`
        : "/GreenBin/frontend/img/default-report.png";

      let existingCard = this.reportsList.querySelector(`[data-report-id="${report.report_id}"]`);

      if (replaceExisting && existingCard) {
        const img = existingCard.querySelector("img");
        const title = existingCard.querySelector("h3");
        const description = existingCard.querySelector("p");
        const metaInfo = existingCard.querySelector(".report-meta, .text-sm.text-gray-500");

        if (img) {
          img.src = imageUrl;
          img.loading = "lazy";
        }
        if (title) title.textContent = report.title || "";
        if (description) description.innerHTML = this.nl2br(this.escapeHtml(report.description || ""));
        if (metaInfo) {
          metaInfo.innerHTML = `
            <span>Status: <strong class="status-${report.status?.toLowerCase().replace(' ', '-')}">${this.escapeHtml(report.status || "")}</strong></span>
            <div class="divider"></div>
            <span>Date: ${this.formatDate(report.date)}</span>
            <div class="divider"></div>
            <span>Location: ${this.escapeHtml(report.location || "")}</span>
          `;
        }
        return;
      }

      const reportCard = document.createElement("div");
      reportCard.className = "report-card bg-white border rounded-lg p-4 shadow-sm hover:shadow-md transition-shadow";
      reportCard.dataset.reportId = report.report_id;

      reportCard.innerHTML = `
        <div class="report-image mb-3">
          <img src="${imageUrl}" alt="Report image" loading="lazy" class="w-full h-40 object-cover rounded-md" />
        </div>
        <div class="report-content">
          <h3 class="report-title font-semibold text-lg mb-2 text-gray-800">${this.escapeHtml(report.title || "")}</h3>
          <p class="report-description text-gray-600 mb-3 text-sm leading-relaxed">${this.nl2br(this.escapeHtml(report.description || ""))}
          </p>
          <div class="report-meta text-xs text-gray-500 flex items-center gap-2 mb-3 flex-wrap">
            <span>Status: <strong class="status-${report.status?.toLowerCase().replace(' ', '-')}">${this.escapeHtml(report.status || "")}</strong></span>
            <div class="divider"></div>
            <span>Date: ${this.formatDate(report.date)}</span>
            <div class="divider"></div>
            <span>Location: ${this.escapeHtml(report.location || "")}</span>
          </div>
          <div class="flex gap-3">
            <button class="edit-btn bg-blue-500 hover:bg-blue-600 text-white px-3 py-1 rounded text-sm transition-colors">Edit</button>
            <button class="delete-btn bg-red-500 hover:bg-red-600 text-white px-3 py-1 rounded text-sm transition-colors">Delete</button>
          </div>
        </div>
      `;

      this.reportsList.prepend(reportCard);

      if (this.noReports) this.noReports.style.display = "none";
      this.reportsList.style.display = "block";
    }

    async loadUserReports() {
      if (!this.reportsList) return;

      try {
        const res = await fetch("/GreenBin/backend/getReports.php");
        if (!res.ok) throw new Error(`HTTP error! status: ${res.status}`);
        
        const data = await res.json();

        if (data.success && data.reports && data.reports.length > 0) {
          if (this.noReports) this.noReports.style.display = "none";
          this.reportsList.style.display = "block";

          const fetchedReportsMap = new Map();
          data.reports.forEach((report) => {
            fetchedReportsMap.set(report.report_id, report);
          });

          for (const existingId of this.currentReportsMap.keys()) {
            if (!fetchedReportsMap.has(existingId)) {
              const cardToRemove = this.reportsList.querySelector(`[data-report-id="${existingId}"]`);
              if (cardToRemove) cardToRemove.remove();
              this.currentReportsMap.delete(existingId);
            }
          }

          for (const report of data.reports) {
            const newHash = this.hashReport(report);
            if (!this.currentReportsMap.has(report.report_id)) {
              this.addReportToUI(report);
              this.currentReportsMap.set(report.report_id, newHash);
            } else if (this.currentReportsMap.get(report.report_id) !== newHash) {
              this.addReportToUI(report, true);
              this.currentReportsMap.set(report.report_id, newHash);
            }
          }
        } else {
          if (this.noReports) this.noReports.style.display = "block";
          this.reportsList.style.display = "none";
          this.reportsList.innerHTML = "";
          this.currentReportsMap.clear();
        }
      } catch (err) {
        console.error("Failed to load reports:", err);
        this.showToast("Failed to load reports: " + err.message, 'error');
        if (this.noReports) this.noReports.style.display = "block";
        this.reportsList.style.display = "none";
      }
    }

    async deleteReport(reportId) {
      if (!confirm("Are you sure you want to delete this report?")) return;

      try {
        const response = await fetch("/GreenBin/backend/deleteReport.php", {
          method: "POST",
          headers: { "Content-Type": "application/x-www-form-urlencoded" },
          body: `reportId=${encodeURIComponent(reportId)}`,
        });

        const result = await response.json();
        if (result.success) {
          const card = this.reportsList.querySelector(`[data-report-id="${reportId}"]`);
          if (card) card.remove();
          this.currentReportsMap.delete(reportId);

          if (this.reportsList.children.length === 0) {
            if (this.noReports) this.noReports.style.display = "block";
            this.reportsList.style.display = "none";
          }

          this.showToast("Report deleted successfully.");
        } else {
          this.showToast("Failed to delete report: " + (result.message || "Unknown error"), 'error');
        }
      } catch (err) {
        console.error("Delete failed:", err);
        this.showToast("Failed to delete report: " + err.message, 'error');
      }
    }

    tryGeolocation() {
      const locationInput = document.getElementById("location");
      const locationPermissionMessage = document.getElementById('location-permission-message');

      if (!locationInput || !locationPermissionMessage) return;
      
      if (!navigator.geolocation) {
        locationPermissionMessage.textContent = 'Your browser does not support geolocation.';
        console.warn("Geolocation not supported");
        this.showToast("Geolocation is not supported by your browser.", 'error');
        return;
      }

      locationPermissionMessage.textContent = 'Please allow location access to automatically detect your location. You can change it later.';
      this.showToast("Getting your location...");

      navigator.geolocation.getCurrentPosition(
        (pos) => {
          const coords = `${pos.coords.latitude},${pos.coords.longitude}`;
          locationInput.value = coords;
          sessionStorage.setItem('user_location', coords);
          locationPermissionMessage.textContent = 'Location successfully detected.';
          this.showToast("Location captured successfully.");
        },
        (error) => {
          locationPermissionMessage.textContent = 'Location access denied. Please enter location manually.';
          console.error("Geolocation error:", error);
          this.showToast("Unable to get location. Please enter it manually.", 'error');
        },
        { timeout: 10000, enableHighAccuracy: true }
      );
    }

    async handleFormSubmit(e) {
      e.preventDefault();
      const formData = new FormData(this.form);
      const errorBox = this.form.querySelector(".form-error, #formError");

      try {
        const res = await fetch(this.form.action || "/GreenBin/backend/reportSubmit.php", {
          method: "POST",
          body: formData,
        });

        if (!res.ok) throw new Error(`HTTP error! status: ${res.status}`);
        const data = await res.json();

        if (data.success) {
          this.form.reset();
          if (this.uploadArea?.querySelector("p")) {
            this.uploadArea.querySelector("p").textContent = "Click to upload or drag and drop";
          }
          if (errorBox) errorBox.classList.add("hidden");

          if (data.report) {
            this.addReportToUI(data.report);
            this.currentReportsMap.set(data.report.report_id, this.hashReport(data.report));
          }
          
          this.showToast("Report submitted successfully.");
          
          if (this.reportFormSection) {
            this.reportFormSection.classList.add("hidden");
          }
        } else {
          const message = data.message || "Submission failed.";
          if (errorBox) {
            errorBox.textContent = message;
            errorBox.classList.remove("hidden");
          }
          this.showToast(message, 'error');
        }
      } catch (err) {
        const message = "Error: " + err.message;
        if (errorBox) {
          errorBox.textContent = message;
          errorBox.classList.remove("hidden");
        }
        this.showToast(message, 'error');
      }
    }

    updateFileInputUI() {
      const fileName = this.fileInput.files[0]?.name || "Click to upload or drag and drop";
      const textElement = this.uploadArea.querySelector("p");
      if (textElement) textElement.textContent = fileName;
    }

    openEditModal(report) {
      const modal = document.getElementById("editModal");
      if (!modal) return;

      modal.classList.remove("hidden");
      
      const reportIdField = document.getElementById("editReportId");
      const titleField = document.getElementById("editTitle");
      const descriptionField = document.getElementById("editDescription");
      const locationField = document.getElementById("editLocation");
      const photoField = document.getElementById("editPhoto");
      const errorField = document.getElementById("editError");

      if (reportIdField) reportIdField.value = report.report_id || "";
      if (titleField) titleField.value = report.title || "";
      if (descriptionField) descriptionField.value = report.description || "";
      if (locationField) locationField.value = report.location || "";
      if (photoField) photoField.value = "";
      if (errorField) errorField.classList.add("hidden");
    }
     openEditModalForReport(reportId) {
      const reportData = this.getReportDataFromMap(reportId);
      if (reportData) {
        this.openEditModal(reportData);
      } else {
        // Fallback to fetch from server if not in map
        this.fetchReportAndOpenModal(reportId);
      }
    }
     getReportDataFromMap(reportId) {
      // This requires the full report object to be stored in the map.
      // Let's adjust the map to store the whole report object.
      for (const report of this.currentReportsMap.values()) {
        if (report.report_id == reportId) {
          return report;
        }
      }
      return null;
    }
    async fetchReportAndOpenModal(reportId) {
      try {
        const res = await fetch(`/GreenBin/backend/getReport.php?id=${reportId}`);
        if (!res.ok) throw new Error("Failed to fetch report details");
        const data = await res.json();
        if (data.success && data.report) {
          this.openEditModal(data.report);
        } else {
          this.showToast(data.message || "Could not find report details.", 'error');
        }
      } catch (err) {
        this.showToast(err.message, 'error');
      }
    }


    closeEditModal() {
      const modal = document.getElementById("editModal");
      if (modal) modal.classList.add("hidden");
    }

    async handleEditFormSubmit(e) {
      e.preventDefault();

      const formData = new FormData(this.editForm);
      const errorBox = document.getElementById("editError");

      if (!formData.get("title")?.trim() || !formData.get("description")?.trim()) {
        if (errorBox) {
          errorBox.textContent = "Title and description are required.";
          errorBox.classList.remove("hidden");
        }
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
          this.closeEditModal();
          if (result.report) {
            this.addReportToUI(result.report, true);
            this.currentReportsMap.set(result.report.report_id, this.hashReport(result.report));
          }
          this.showToast("Report updated successfully.");
        } else {
          const message = result.message || "Failed to update.";
          if (errorBox) {
            errorBox.textContent = message;
            errorBox.classList.remove("hidden");
          }
          this.showToast(message, 'error');
        }
      } catch (err) {
        const message = "Error: " + err.message;
        if (errorBox) {
          errorBox.textContent = message;
          errorBox.classList.remove("hidden");
        }
        this.showToast(message, 'error');
      }
    }

    async loadDashboardStats() {
      try {
        const res = await fetch("/GreenBin/backend/getDashboardStats.php");
        if (!res.ok) throw new Error("Network response was not ok");
        const data = await res.json();
        
        if (data.success && data.stats) {
          const stats = data.stats;
          document.getElementById("totalReports").textContent = stats.totalReports || "0";
          document.getElementById("resolutionRate").textContent = (stats.resolutionRate || 0) + "%";
          document.getElementById("resolvedCount").textContent = (stats.resolvedCount || 0) + " resolved";
          document.getElementById("co2Reduction").textContent = parseFloat(stats.co2Reduction || 0).toFixed(1) + " kg";
          document.getElementById("communityPoints").textContent = stats.communityPoints || "0";
        }
      } catch (err) {
        console.error("Stats update failed:", err);
      }
    }

    startPolling() {
      setInterval(() => {
        this.loadUserReports();
        this.loadDashboardStats();
      }, 5000);
    }

    initializeDefaultTab() {
      if (this.reportsTab && this.reportsSection) {
        this.switchTab(this.reportsTab, this.reportsSection);
      }
    }
  }

  new Dashboard();
});
