function renderReports() {
    const reports = JSON.parse(localStorage.getItem('reports') || '[]');
    const container = document.getElementById('reportsContainer');
    container.innerHTML = '';

    if (reports.length === 0) {
      container.innerHTML = '<p class="text-gray-500">No reports submitted yet.</p>';
      return;
    }

    reports.forEach((report) => {
      const div = document.createElement('div');
      div.className = 'bg-white p-4 rounded shadow';
      div.innerHTML = `
        <div class="flex justify-between items-start">
          <div>
            <h3 class="text-xl font-bold">${report.title}</h3>
            <p class="text-gray-600 text-sm">Submitted on ${report.date}</p>
            <p class="mt-2">${report.description}</p>
          </div>
          <div class="flex flex-col gap-2 items-end">
            <span class="px-3 py-1 rounded-full text-sm font-semibold bg-yellow-100 text-yellow-700">${report.status}</span>
            <button onclick="editReport(${report.id})" class="text-blue-600 text-sm">Edit</button>
            <button onclick="deleteReport(${report.id})" class="text-red-500 text-sm">Delete</button>
          </div>
        </div>
      `;
      container.appendChild(div);
    });
  }

  function deleteReport(id) {
    let reports = JSON.parse(localStorage.getItem('reports') || '[]');
    reports = reports.filter(report => report.id !== id);
    localStorage.setItem('reports', JSON.stringify(reports));
    renderReports();
  }

  function editReport(id) {
    const reports = JSON.parse(localStorage.getItem('reports') || '[]');
    const report = reports.find(r => r.id === id);
    if (!report) return;

    const newTitle = prompt('Edit Title:', report.title);
    const newDescription = prompt('Edit Description:', report.description);
    if (newTitle !== null && newDescription !== null) {
      report.title = newTitle;
      report.description = newDescription;
      localStorage.setItem('reports', JSON.stringify(reports));
      renderReports();
    }
  }

  renderReports();