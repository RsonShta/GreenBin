// ----------------------------
// ✅ MAIN ADMIN DASHBOARD JS
// ----------------------------
document.addEventListener('DOMContentLoaded', () => {

  const sidebar = document.querySelector('.sidebar');
  const toggleBtn = document.querySelector('.sidebar-toggle');
  const menuItems = document.querySelectorAll('.sidebar-nav li');
  const contentSections = document.querySelectorAll('.content-section');
  // Wait for the DOM to load
  const searchInput = document.querySelector("#users input[type='text']");
  const userCards = document.querySelectorAll("#users .user-card");

  function updatePassword() {
  const current = document.getElementById("currentPassword").value.trim();
  const newPass = document.getElementById("newPassword").value.trim();
  const confirmPass = document.getElementById("confirmPassword").value.trim();

  if (!current || !newPass || !confirmPass) {
    alert("Please fill in all password fields.");
    return;
  }

  if (newPass !== confirmPass) {
    alert("New password and confirm password do not match.");
    return;
  }

  alert("Password updated successfully! (simulated)");
}

function saveSettings() {
  const settings = {
    systemName: document.getElementById("systemName").value,
    adminEmail: document.getElementById("adminEmail").value,
    systemDescription: document.getElementById("systemDescription").value,
    emailNotif: document.getElementById("emailNotif").checked,
    smsNotif: document.getElementById("smsNotif").checked,
    dashNotif: document.getElementById("dashNotif").checked,
    smtpServer: document.getElementById("smtpServer").value,
    smtpPort: document.getElementById("smtpPort").value,
    emailUsername: document.getElementById("emailUsername").value,
    emailPassword: document.getElementById("emailPassword").value
  };

  console.log("Saved Settings:", settings);
  alert("Settings saved successfully! (simulated)");
}

function resetDefaults() {
  if (confirm("Are you sure you want to reset to default settings?")) {
    location.reload();
  }
}


  searchInput.addEventListener("input", () => {
    const searchTerm = searchInput.value.toLowerCase().trim();

    userCards.forEach(card => {
      const userName = card.querySelector(".user-details h3").textContent.toLowerCase();

      if (userName.includes(searchTerm)) {
        card.style.display = "";
      } else {
        card.style.display = "none";
      }
    });
  });

  // Toggle sidebar on small screens
  toggleBtn.addEventListener('click', () => {
    sidebar.classList.toggle('active');
  });

  // Navigation menu click handling
  menuItems.forEach((item) => {
    item.addEventListener('click', (e) => {
      e.preventDefault();

      // Remove active class from all menu items
      menuItems.forEach((i) => i.classList.remove('active'));
      // Add active class to clicked menu item
      item.classList.add('active');

      // Show the corresponding content section
      const targetSection = item.getAttribute('data-section');
      contentSections.forEach((section) => {
        if (section.id === targetSection) {
          section.classList.add('active-section');
          // ✅ Reinitialize Create Bin form when bins section is shown
          if (targetSection === 'bins') {
            setTimeout(initCreateBin, 0);
          }
        } else {
          section.classList.remove('active-section');
        }
      });

      // Close sidebar on small screens after selection
      if (window.innerWidth <= 900) {
        sidebar.classList.remove('active');
      }
    });
  });

  // Logout button (demo)
  const logoutBtn = document.querySelector('.logout-btn');
  logoutBtn.addEventListener('click', () => {
    alert('Logout functionality not implemented.');
  });
});

// ----------------------------
// ✅ CREATE BIN FUNCTIONALITY
// ----------------------------

let DATA;

async function loadLocationData() {
  const resp = await fetch('https://raw.githubusercontent.com/sagautam5/local-states-nepal/master/dataset/local-levels.json');
  DATA = await resp.json();
  populateProvinces();
}

function populateProvinces() {
  const provSel = document.getElementById('province');
  if (!provSel) return;
  provSel.innerHTML = '<option value="">Select province</option>';
  const provinces = [...new Set(DATA.map(x => x.province))].sort();
  provinces.forEach(p => {
    const o = new Option(p, p);
    provSel.add(o);
  });
}

function onProvinceChange() {
  const prov = this.value;
  const distSel = document.getElementById('district');
  const muniSel = document.getElementById('municipality');
  const wardSel = document.getElementById('ward');
  distSel.innerHTML = '<option value="">Select district</option>';
  muniSel.innerHTML = '<option value="">Select municipality</option>';
  wardSel.innerHTML = '<option value="">Select ward</option>';
  muniSel.disabled = wardSel.disabled = true;
  if (prov) {
    const districts = [...new Set(DATA.filter(x => x.province === prov).map(x => x.district))].sort();
    districts.forEach(d => distSel.add(new Option(d, d)));
    distSel.disabled = false;
  } else distSel.disabled = true;
}

function onDistrictChange() {
  const prov = document.getElementById('province').value;
  const dist = this.value;
  const muniSel = document.getElementById('municipality');
  muniSel.innerHTML = '<option value="">Select municipality</option>';
  document.getElementById('ward').innerHTML = '<option value="">Select ward</option>';
  document.getElementById('ward').disabled = true;
  if (dist) {
    const munis = DATA.filter(x => x.province === prov && x.district === dist);
    munis.forEach(m => muniSel.add(new Option(m.locallevel_name, m.locallevel_name)));
    muniSel.disabled = false;
  } else muniSel.disabled = true;
}

function onMunicipalityChange() {
  const prov = document.getElementById('province').value;
  const dist = document.getElementById('district').value;
  const muni = this.value;
  const wardSel = document.getElementById('ward');
  wardSel.innerHTML = '<option value="">Select ward</option>';
  if (muni) {
    const wardsCount = DATA.find(x => x.province === prov && x.district === dist && x.locallevel_name === muni).wards;
    for (let i = 1; i <= wardsCount; i++) {
      wardSel.add(new Option(`Ward ${i}`, i));
    }
    wardSel.disabled = false;
  } else wardSel.disabled = true;
}

function setupPhotoUpload() {
  const upload = document.getElementById('photo-upload');
  const fileInput = document.getElementById('file-input');
  const count = document.getElementById('photo-count');

  upload.addEventListener('click', () => fileInput.click());
  fileInput.addEventListener('change', () => {
    const files = Array.from(fileInput.files).slice(0, 3);
    count.textContent = files.length;
  });
}

function initCreateBin() {
  const binForm = document.getElementById('create-bin-form');
  if (!binForm) return;

  loadLocationData();
  document.getElementById('province').addEventListener('change', onProvinceChange);
  document.getElementById('district').addEventListener('change', onDistrictChange);
  document.getElementById('municipality').addEventListener('change', onMunicipalityChange);
  setupPhotoUpload();
  binForm.addEventListener('submit', e => {
    e.preventDefault();
    alert('Bin created successfully!');
    e.target.reset();
    document.getElementById('photo-count').textContent = '0';
  });
}

// ---------------------------------------------
// ✅ DRIVER MANAGEMENT FUNCTIONALITY (ADDED)
// ---------------------------------------------
document.addEventListener("DOMContentLoaded", () => {
  const driverForm = document.getElementById("driverForm");
  const searchInput = document.getElementById("search");
  const addBtn = document.querySelector(".add-driver-btn");
  const cancelBtn = document.querySelector(".cancel-btn");
  const driverSection = document.getElementById("drivers");
  const driverCardContainer = document.getElementById("driverCardContainer");
  const fileInput = document.getElementById("driverPhoto");

  let drivers = [];

  addBtn.addEventListener("click", () => {
    driverForm.style.display = "block";
    addBtn.style.display = "none";
  });

  cancelBtn.addEventListener("click", (e) => {
    e.preventDefault();
    driverForm.reset();
    driverForm.style.display = "none";
    addBtn.style.display = "inline-block";
  });

  driverForm.addEventListener("submit", (e) => {
    e.preventDefault();

    const name = driverForm.querySelector('input[name="name"]').value.trim();
    const email = driverForm.querySelector('input[name="email"]').value.trim();
    const phone = driverForm.querySelector('input[name="phone"]').value.trim();
    const ward = driverForm.querySelector('select[name="ward"]').value;
    const status = driverForm.querySelector('select[name="status"]').value;

    const newDriver = { name, email, phone, ward, status };
    drivers.push(newDriver);
    renderDrivers(drivers);
    driverForm.reset();
    driverForm.style.display = "none";
    addBtn.style.display = "inline-block";
  });

  searchInput.addEventListener("input", () => {
    const keyword = searchInput.value.toLowerCase();
    const filtered = drivers.filter((driver) =>
      driver.name.toLowerCase().includes(keyword)
    );
    renderDrivers(filtered);
  });

  // ================================
// Complaint Management Script
// ================================

document.addEventListener('DOMContentLoaded', () => {
  const areaToStreets = {
    Kathmandu: ['Thamel', 'Baneshwor', 'Kalanki'],
    Lalitpur: ['Jawalakhel', 'Pulchowk', 'Satdobato'],
    Bhaktapur: ['Durbar Square', 'Suryabinayak', 'Kamalbinayak']
  };

  const streetSelect = document.getElementById('street');
  const areaSelect = document.getElementById('area');
  const complaintForm = document.getElementById('complaintForm');
  const complaintList = document.getElementById('complaint-list');
  const searchInput = document.getElementById('search');

  const countNew = document.getElementById('new-count');
  const countAssigned = document.getElementById('assigned-count');
  const countClosed = document.getElementById('closed-count');

  let complaints = [];

  // Update streets based on selected area
  areaSelect.addEventListener('change', () => {
    const area = areaSelect.value;
    streetSelect.innerHTML = '<option value="">Select Street</option>';

    if (areaToStreets[area]) {
      areaToStreets[area].forEach(street => {
        const option = document.createElement('option');
        option.value = street;
        option.textContent = street;
        streetSelect.appendChild(option);
      });
    }
  });

  // Submit form and create complaint card
  complaintForm.addEventListener('submit', (e) => {
    e.preventDefault();

    const name = document.getElementById('name').value.trim();
    const date = document.getElementById('date').value;
    const area = areaSelect.value;
    const street = streetSelect.value;
    const title = document.getElementById('title').value;
    const priority = document.getElementById('priority').value;

    const complaint = {
      id: `CMP-${Date.now()}`,
      name,
      date,
      area,
      street,
      title,
      priority,
      status: 'New'
    };

    complaints.push(complaint);
    renderComplaints();
    complaintForm.reset();
    streetSelect.innerHTML = '<option value="">Select Street</option>';
  });

  // Render complaints list
  function renderComplaints() {
    complaintList.innerHTML = '';
    let newCount = 0, assignedCount = 0, closedCount = 0;

    complaints.forEach(c => {
      if (c.status === 'New') newCount++;
      if (c.status === 'Assigned') assignedCount++;
      if (c.status === 'Closed') closedCount++;

      const card = document.createElement('div');
      card.className = 'complaint-card';
      card.innerHTML = `
        <strong>${c.name}</strong><br>
        ID: ${c.id} | Date: ${c.date}<br>
        Area: ${c.area}, ${c.street}<br>
        Title: ${c.title} | Priority: ${c.priority}<br>
        Status: <span>${c.status}</span><br><br>
        <button onclick="changeStatus('${c.id}', 'Assigned')">Assign Driver</button>
        <button onclick="changeStatus('${c.id}', 'Closed')">Close</button>
      `;
      complaintList.appendChild(card);
    });

    countNew.textContent = newCount;
    countAssigned.textContent = assignedCount;
    countClosed.textContent = closedCount;
  }

  // Filter by name
  searchInput.addEventListener('input', () => {
    const keyword = searchInput.value.toLowerCase();
    const filtered = complaints.filter(c => c.name.toLowerCase().includes(keyword));
    renderFilteredComplaints(filtered);
  });

  function renderFilteredComplaints(list) {
    complaintList.innerHTML = '';
    list.forEach(c => {
      const card = document.createElement('div');
      card.className = 'complaint-card';
      card.innerHTML = `
        <strong>${c.name}</strong><br>
        ID: ${c.id} | Date: ${c.date}<br>
        Area: ${c.area}, ${c.street}<br>
        Title: ${c.title} | Priority: ${c.priority}<br>
        Status: <span>${c.status}</span><br><br>
        <button onclick="changeStatus('${c.id}', 'Assigned')">Assign Driver</button>
        <button onclick="changeStatus('${c.id}', 'Closed')">Close</button>
      `;
      complaintList.appendChild(card);
    });
  }

  // Change status by ID (global so buttons work)
  window.changeStatus = function (id, newStatus) {
    complaints = complaints.map(c => {
      if (c.id === id) {
        return { ...c, status: newStatus };
      }
      return c;
    });
    renderComplaints();
  };
});


  function renderDrivers(driverList) {
    driverCardContainer.innerHTML = "";

    if (driverList.length === 0) {
      driverCardContainer.innerHTML = `<p>No drivers found.</p>`;
      return;
    }

    driverList.forEach((driver) => {
      const card = document.createElement("div");
      card.classList.add("driver-card");

      card.innerHTML = `
        <div class="driver-info">
          <h3>${driver.name}</h3>
          <p>Email: ${driver.email}</p>
          <p>Ward: ${driver.ward}</p>
        </div>
        <div class="driver-contact">
          <p><i class="fa fa-phone"></i> ${driver.phone}</p>
        </div>
        <div class="driver-status">
          <span class="status ${driver.status.toLowerCase()}">${driver.status}</span><br />
          <button class="view-btn">View</button>
        </div>
      `;

      driverCardContainer.appendChild(card);
    });
  }

  if (fileInput) {
    fileInput.addEventListener("change", () => {
      const fileName = fileInput.files[0]?.name || "No file chosen";
      alert(`Selected File: ${fileName}`);
    });
  }
});
