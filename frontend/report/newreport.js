document.getElementById('reportForm').addEventListener('submit', function (e) {
    e.preventDefault();
    const title = document.getElementById('reportTitle').value;
    const description = document.getElementById('reportDescription').value;
    const date = new Date().toLocaleDateString();

    const report = {
      title,
      description,
      date,
      status: 'Pending'
    };

    let reports = JSON.parse(localStorage.getItem('reports') || '[]');
    reports.push(report);
    localStorage.setItem('reports', JSON.stringify(reports));

    window.location.href = 'dashboard.html';
  });
