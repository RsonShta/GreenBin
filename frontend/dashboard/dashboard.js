// script.js

document.addEventListener('DOMContentLoaded', function () {
  const uploadArea = document.getElementById('uploadArea');
  const fileInput = document.getElementById('fileInput');
  const reportForm = document.querySelector('.form-container');
  const reportsList = document.querySelector('.reports-list');
  const noReports = document.querySelector('.no-reports');
  const newReportBtn = document.querySelector('.btn-primary:last-child');

  const sampleReports = [
    {
      id: 1,
      title: "Illegal dumping near riverbank",
      description:
        "Found large amounts of construction waste dumped illegally near the Bagmati riverbank. Need urgent cleanup.",
      date: "2023-06-15",
      status: "pending",
      location: "Kathmandu, Ward 5",
    },
    {
      id: 2,
      title: "Overflowing bins at city center",
      description:
        "Public bins at Ratna Park are overflowing for two days. Attracting pests and creating health hazard.",
      date: "2023-06-10",
      status: "resolved",
      location: "Kathmandu, Ward 10",
    },
    {
      id: 3,
      title: "Plastic waste accumulation",
      description:
        "Large amount of plastic waste accumulated in open area near Swayambhu temple. Needs recycling or proper disposal.",
      date: "2023-06-05",
      status: "in-progress",
      location: "Kathmandu, Ward 3",
    },
  ];

  uploadArea.addEventListener('click', () => fileInput.click());

  fileInput.addEventListener('change', (e) => {
    if (e.target.files.length > 0) {
      const fileName = e.target.files[0].name;
      uploadArea.innerHTML = `
        <i class="fas fa-check-circle" style="color: var(--success);"></i>
        <p>File uploaded successfully</p>
        <p class="file-info">${fileName}</p>
      `;
      uploadArea.style.borderColor = 'var(--success)';
      uploadArea.style.backgroundColor = 'rgba(56, 142, 60, 0.05)';
    }
  });

  uploadArea.addEventListener('dragover', (e) => {
    e.preventDefault();
    uploadArea.style.borderColor = 'var(--primary)';
    uploadArea.style.backgroundColor = 'rgba(46, 125, 50, 0.1)';
  });

  uploadArea.addEventListener('dragleave', () => {
    uploadArea.style.borderColor = 'var(--border)';
    uploadArea.style.backgroundColor = '#fafafa';
  });

  uploadArea.addEventListener('drop', (e) => {
    e.preventDefault();
    if (e.dataTransfer.files.length > 0) {
      fileInput.files = e.dataTransfer.files;
      const fileName = e.dataTransfer.files[0].name;
      uploadArea.innerHTML = `
        <i class="fas fa-check-circle" style="color: var(--success);"></i>
        <p>File uploaded successfully</p>
        <p class="file-info">${fileName}</p>
      `;
      uploadArea.style.borderColor = 'var(--success)';
      uploadArea.style.backgroundColor = 'rgba(56, 142, 60, 0.05)';
    }
  });

  reportForm.addEventListener('submit', (e) => {
    e.preventDefault();
    const title = document.getElementById('reportTitle').value;
    const description = document.getElementById('description').value;

    if (title && description) {
      const newReport = {
        id: Date.now(),
        title,
        description,
        date: new Date().toISOString().split('T')[0],
        status: 'pending',
        location: 'Kathmandu',
      };
      addReportToUI(newReport);
      reportForm.reset();
      uploadArea.innerHTML = `
        <i class="fas fa-cloud-upload-alt"></i>
        <p>Click to upload or drag and drop</p>
        <p class="file-info">PNG, JPG, GIF up to 5MB</p>
      `;
      uploadArea.style.borderColor = 'var(--border)';
      uploadArea.style.backgroundColor = '#fafafa';
      alert('Report submitted successfully!');
    } else {
      alert('Please fill in all required fields');
    }
  });

  newReportBtn.addEventListener('click', () => {
    document.getElementById('reportTitle').focus();
  });

  function addReportToUI(report) {
    noReports.style.display = 'none';
    reportsList.style.display = 'grid';

    const statusMap = {
      pending: { text: 'Pending', class: 'status-pending' },
      resolved: { text: 'Resolved', class: 'status-resolved' },
      'in-progress': { text: 'In Progress', class: 'status-in-progress' },
    };

    const reportCard = document.createElement('div');
    reportCard.className = 'report-card';
    reportCard.innerHTML = `
      <div class="report-image">
        <img src="https://images.unsplash.com/photo-1581578731548-c64695cc6952?ixlib=rb-4.0.3&auto=format&fit=crop&w=500&q=80" alt="Report image">
      </div>
      <div class="report-content">
        <h3 class="report-title">${report.title}</h3>
        <p class="report-description">${report.description}</p>
        <div class="report-meta">
          <span class="report-status ${statusMap[report.status].class}">${statusMap[report.status].text}</span>
          <span class="divider"></span>
          <span><i class="far fa-calendar"></i> ${report.date}</span>
          <span class="divider"></span>
          <span><i class="fas fa-map-marker-alt"></i> ${report.location}</span>
        </div>
      </div>
    `;

    reportsList.prepend(reportCard);
  }

  function loadSampleReports() {
    sampleReports.forEach((report) => addReportToUI(report));
  }

  loadSampleReports();
});
