document.addEventListener("DOMContentLoaded", () => {
    const reportsContainer = document.getElementById("reportsContainer");
    const noReports = document.getElementById("noReports");
    const filterTabs = document.querySelectorAll(".filter-tab");

    const modal = document.getElementById("reportModal");
    const modalImage = document.getElementById("modalImage");
    const modalTitle = document.getElementById("modalTitle");
    const modalDescription = document.getElementById("modalDescription");
    const modalUser = document.getElementById("modalUser");
    const modalCo2 = document.getElementById("modalCo2");
    const modalLocation = document.getElementById("modalLocation");
    const statusSelect = document.getElementById("statusSelect");

    let currentReportId = null;
    let currentStatusFilter = "all";

    // Fetch reports on page load
    fetchReports();

    // Filter tabs click event
    filterTabs.forEach(tab => {
        tab.addEventListener("click", () => {
            filterTabs.forEach(t => t.classList.remove("ring-2", "ring-green-700"));
            tab.classList.add("ring-2", "ring-green-700");
            currentStatusFilter = tab.dataset.status;
            fetchReports();
        });
    });

    /**
     * Fetch reports from backend and render them
     */
    function fetchReports() {
        reportsContainer.innerHTML = `<p class="text-center text-gray-500">Loading reports...</p>`;
        fetch(`/GreenBin/backend/admin/getReports.php?status=${currentStatusFilter}`)
            .then(res => res.json())
            .then(data => {
                reportsContainer.innerHTML = "";
                if (!data.success || data.reports.length === 0) {
                    noReports.classList.remove("hidden");
                    return;
                }
                noReports.classList.add("hidden");

                data.reports.forEach(report => {
                    const card = document.createElement("div");
                    card.className = "bg-white rounded shadow p-4 hover:shadow-lg cursor-pointer transition";
                    card.innerHTML = `
                        <img src="${report.image_path || "/GreenBin/frontend/img/no-image.png"}"
                             class="w-full h-40 object-cover rounded mb-3" alt="Report">
                        <h3 class="text-lg font-bold text-green-700">${report.title}</h3>
                        <p class="text-sm text-gray-600 truncate">${report.description}</p>
                        <p class="text-xs text-gray-500 mt-1">
                            Status: <span class="capitalize">${report.status}</span>
                        </p>
                    `;
                    card.addEventListener("click", () => openModal(report.report_id));
                    reportsContainer.appendChild(card);
                });
            })
            .catch(err => {
                console.error("Error fetching reports:", err);
                reportsContainer.innerHTML = `<p class="text-center text-red-600">Failed to load reports.</p>`;
            });
    }

    /**
     * Open modal and show report details
     */
    function openModal(reportId) {
        fetch(`/GreenBin/backend/admin/getReportDetails.php?report_id=${reportId}`)
            .then(res => res.json())
            .then(data => {
                if (!data.success) {
                    alert("Failed to load report details");
                    return;
                }

                const r = data.report;
                currentReportId = r.report_id;
                modalImage.src = r.image_path || "/GreenBin/frontend/img/no-image.png";
                modalTitle.textContent = r.title;
                modalDescription.textContent = r.description;
                modalUser.textContent = r.user_id;
                statusSelect.value = r.status;

                modalLocation.textContent = r.location;

                modal.classList.remove("hidden");
                modal.classList.add("flex");
            })
            .catch(err => console.error("Error:", err));
    }

    /**
     * Update the report status
     */
    window.updateStatus = function () {
        if (!currentReportId) return;

        const formData = new FormData();
        formData.append("report_id", currentReportId);
        formData.append("status", statusSelect.value);

        fetch("/GreenBin/backend/admin/updateReportStatus.php", {
            method: "POST",
            body: formData,
        })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    alert("Status updated successfully");
                    closeModal();
                    fetchReports();
                } else {
                    alert("Failed to update status");
                }
            })
            .catch(err => console.error("Error updating status:", err));
    };

    /**
     * Close the modal
     */
    window.closeModal = function () {
        modal.classList.add("hidden");
        modal.classList.remove("flex");
    };
});
