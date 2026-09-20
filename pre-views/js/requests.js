/**
 * ============================================================================
 * WCCMS — Registration Requests Page & Workspace Controller
 * Workflow: Display pending patient referrals awaiting registration completion.
 * No request status badges. No approval/rejection step.
 * Completing registration creates ACTIVE registration and removes request from list.
 * ============================================================================
 */

'use strict';

// ============================================================================
// DATA LAYER — Referred Patients Awaiting Registration
// ============================================================================

const CURRENT_USER = {
  id: 'USR-01',
  name: 'أ. فاطمة الزهراني',
  role: 'ممرضة',
  departmentId: 'wound_care_clinic'
};

let registrationCounter = 1;

// Patients referred for wound care registration (No status property, all pending registration)
let MOCK_REQUESTS = [
  {
    id: 'REQ-001',
    hospitalPatientId: 'HP-10025',
    nationalId: '987654321',
    fullName: 'أحمد محمد علي',
    gender: 'ذكر',
    age: 52,
    supervisingDoctor: 'د. محمد خالد',
    hospitalDepartment: 'ICU',
    admissionDate: '2026-08-25',
    departmentId: 'wound_care_clinic',
    departmentName: 'عيادة العناية بالجروح',
    requestedAt: '2026-08-31 09:30',
    notes: 'جرح عميق في القدم اليسرى يحتاج تقييم عاجل.'
  },
  {
    id: 'REQ-002',
    hospitalPatientId: 'HP-10026',
    nationalId: '123456789',
    fullName: 'سارة أحمد محمود',
    gender: 'أنثى',
    age: 44,
    supervisingDoctor: 'د. أحمد محمود',
    hospitalDepartment: 'Oncology',
    admissionDate: '2026-08-27',
    departmentId: 'wound_care_clinic',
    departmentName: 'عيادة العناية بالجروح',
    requestedAt: '2026-08-31 10:15',
    notes: ''
  },
  {
    id: 'REQ-003',
    hospitalPatientId: 'HP-10027',
    nationalId: '111222333',
    fullName: 'خالد عبدالرحمن الشمري',
    gender: 'ذكر',
    age: 67,
    supervisingDoctor: 'د. فهد السعيد',
    hospitalDepartment: 'Surgery',
    admissionDate: '2026-08-20',
    departmentId: 'wound_care_clinic',
    departmentName: 'عيادة العناية بالجروح',
    requestedAt: '2026-08-30 14:00',
    notes: 'قرحة فراش درجة ثالثة في منطقة العجز.'
  },
  {
    id: 'REQ-004',
    hospitalPatientId: 'HP-10030',
    nationalId: '321654987',
    fullName: 'ليلى إبراهيم السعيد',
    gender: 'أنثى',
    age: 59,
    supervisingDoctor: 'د. رنا العمري',
    hospitalDepartment: 'ICU',
    admissionDate: '2026-08-28',
    departmentId: 'wound_care_clinic',
    departmentName: 'عيادة العناية بالجروح',
    requestedAt: '2026-08-31 07:00',
    notes: ''
  },
  {
    id: 'REQ-005',
    hospitalPatientId: 'HP-10031',
    nationalId: '654321098',
    fullName: 'عبدالعزيز ناصر القحطاني',
    gender: 'ذكر',
    age: 45,
    supervisingDoctor: 'د. تركي المالكي',
    hospitalDepartment: 'Oncology',
    admissionDate: '2026-08-22',
    departmentId: 'wound_care_clinic',
    departmentName: 'عيادة العناية بالجروح',
    requestedAt: '2026-08-30 16:30',
    notes: 'مريض يحتاج تقييم جرح ما بعد العملية.'
  },
  {
    id: 'REQ-006',
    hospitalPatientId: 'HP-10032',
    nationalId: '112233445',
    fullName: 'هيفاء عمر الدوسري',
    gender: 'أنثى',
    age: 63,
    supervisingDoctor: 'د. سمية القرني',
    hospitalDepartment: 'Surgery',
    admissionDate: '2026-08-29',
    departmentId: 'wound_care_clinic',
    departmentName: 'عيادة العناية بالجروح',
    requestedAt: '2026-08-31 13:10',
    notes: ''
  },
  {
    id: 'REQ-007',
    hospitalPatientId: 'HP-10033',
    nationalId: '556677889',
    fullName: 'سعد فهد الماجد',
    gender: 'ذكر',
    age: 58,
    supervisingDoctor: 'د. خالد العمري',
    hospitalDepartment: 'CCU',
    admissionDate: '2026-08-26',
    departmentId: 'wound_care_clinic',
    departmentName: 'عيادة العناية بالجروح',
    requestedAt: '2026-08-29 11:20',
    notes: 'جرح ما بعد قسطرة قدم.'
  },
  {
    id: 'REQ-008',
    hospitalPatientId: 'HP-10034',
    nationalId: '998877665',
    fullName: 'منيرة سليمان الخالد',
    gender: 'أنثى',
    age: 72,
    supervisingDoctor: 'د. بدرية الشمري',
    hospitalDepartment: 'Medical',
    admissionDate: '2026-08-18',
    departmentId: 'wound_care_clinic',
    departmentName: 'عيادة العناية بالجروح',
    requestedAt: '2026-08-28 09:40',
    notes: 'قرحة سكرية متقدمة.'
  }
];

// ============================================================================
// STATE
// ============================================================================
let filteredRequests = [...MOCK_REQUESTS];

// Pagination & Sorting state
let currentPage = 1;
const pageSize = 5;
let currentSortKey = 'reqDate';
let currentSortDir = 'desc';

// ============================================================================
// INITIALIZATION
// ============================================================================
document.addEventListener('DOMContentLoaded', function () {
  applyTheme(localStorage.getItem('m8-theme') || 'light');
  loadRequests();
  bindEvents();
});

// ============================================================================
// THEME & EVENT BINDINGS
// ============================================================================
function applyTheme(theme) {
  document.documentElement.setAttribute('data-bs-theme', theme);
  localStorage.setItem('m8-theme', theme);
  var icon = document.getElementById('m8-theme-toggle-icon');
  var text = document.getElementById('m8-theme-toggle-text');
  if (theme === 'dark') {
    if (icon) icon.className = 'bi bi-sun-fill';
    if (text) text.textContent = 'الوضع المضيء';
  } else {
    if (icon) icon.className = 'bi bi-moon-stars-fill';
    if (text) text.textContent = 'الوضع الليلي';
  }
}

function bindEvents() {
  var t = document.getElementById('m8-theme-toggle');
  if (t) t.addEventListener('click', function () {
    var cur = document.documentElement.getAttribute('data-bs-theme');
    applyTheme(cur === 'dark' ? 'light' : 'dark');
  });

  var mob = document.getElementById('m8-mobile-toggle');
  if (mob) mob.addEventListener('click', function () {
    var sb = document.getElementById('m8-sidebar');
    if (sb) sb.classList.toggle('show');
  });

  // Search & Filter Events
  var search = document.getElementById('req-search');
  if (search) search.addEventListener('input', function () { currentPage = 1; filterRequests(); });

  var deptF = document.getElementById('filter-dept');
  if (deptF) deptF.addEventListener('change', function () { currentPage = 1; filterRequests(); });

  var dateF = document.getElementById('filter-date');
  if (dateF) dateF.addEventListener('change', function () { currentPage = 1; filterRequests(); });

  var sortSelect = document.getElementById('sort-select');
  if (sortSelect) sortSelect.addEventListener('change', function () {
    var parts = this.value.split('-');
    currentSortKey = parts[0];
    currentSortDir = parts[1] || 'desc';
    filterRequests();
  });
}

// ============================================================================
// LOAD, FILTER, SORT & RENDER REQUESTS TABLE
// ============================================================================
function loadRequests() {
  filterRequests();
}

function filterRequests() {
  var searchVal = (document.getElementById('req-search') || {}).value || '';
  var deptVal = (document.getElementById('filter-dept') || {}).value || '';
  var dateVal = (document.getElementById('filter-date') || {}).value || '';

  searchVal = searchVal.trim().toLowerCase();

  var allReqs = WCCMS.api.getAllRequests({ status: 'PENDING' });

  filteredRequests = allReqs.filter(function (r) {
    var name = r.patient_full_name || r.fullName || '';
    var natId = r.national_id || r.nationalId || '';
    var hospId = r.hospital_patient_id || r.hospitalPatientId || '';
    var dept = r.hospital_department || r.hospitalDepartment || '';
    var reqDate = r.requested_at || r.requestedAt || '';

    var matchSearch = !searchVal ||
      name.toLowerCase().includes(searchVal) ||
      natId.includes(searchVal) ||
      hospId.toLowerCase().includes(searchVal) ||
      r.id.toLowerCase().includes(searchVal);

    var matchDept = !deptVal || dept.toLowerCase().includes(deptVal.toLowerCase());
    var matchDate = !dateVal || reqDate.startsWith(dateVal);

    return matchSearch && matchDept && matchDate;
  });

  // Sort
  filteredRequests.sort(function (a, b) {
    var valA = a[currentSortKey] || '';
    var valB = b[currentSortKey] || '';

    if (currentSortKey === 'reqDate') {
      valA = a.requested_at || a.requestedAt || '';
      valB = b.requested_at || b.requestedAt || '';
    } else if (currentSortKey === 'admDate') {
      valA = a.hospital_admission_date || a.admissionDate || '';
      valB = b.hospital_admission_date || b.admissionDate || '';
    } else if (currentSortKey === 'name') {
      valA = a.patient_full_name || a.fullName || '';
      valB = b.patient_full_name || b.fullName || '';
    } else if (currentSortKey === 'dept') {
      valA = a.hospital_department || a.hospitalDepartment || '';
      valB = b.hospital_department || b.hospitalDepartment || '';
    }

    if (valA < valB) return currentSortDir === 'asc' ? -1 : 1;
    if (valA > valB) return currentSortDir === 'asc' ? 1 : -1;
    return 0;
  });

  updateStats();
  renderTable();
}

function resetFilters() {
  var s = document.getElementById('req-search');
  var d = document.getElementById('filter-dept');
  var dt = document.getElementById('filter-date');
  var sort = document.getElementById('sort-select');

  if (s) s.value = '';
  if (d) d.value = '';
  if (dt) dt.value = '';
  if (sort) sort.value = 'reqDate-desc';

  currentSortKey = 'reqDate';
  currentSortDir = 'desc';
  currentPage = 1;

  filterRequests();
}

function updateStats() {
  var pendingCount = WCCMS.api.getAllRequests({ status: 'PENDING' }).length;
  setEl('stat-pending-count', pendingCount);
}

function renderTable() {
  var tbody = document.getElementById('requests-tbody');
  var emptyState = document.getElementById('table-empty');
  var countEl = document.getElementById('req-count');
  if (!tbody) return;

  var totalPending = WCCMS.api.getAllRequests({ status: 'PENDING' }).length;
  if (countEl) countEl.textContent = totalPending + ' طلب بانتظار التسجيل';

  if (filteredRequests.length === 0) {
    tbody.innerHTML = '';
    if (emptyState) emptyState.classList.remove('d-none');
    renderPagination(0);
    return;
  }
  if (emptyState) emptyState.classList.add('d-none');

  // Pagination slicing
  var totalItems = filteredRequests.length;
  var totalPages = Math.ceil(totalItems / pageSize);
  currentPage = Math.max(1, Math.min(currentPage, totalPages));

  var startIndex = (currentPage - 1) * pageSize;
  var paginatedList = filteredRequests.slice(startIndex, startIndex + pageSize);

  tbody.innerHTML = paginatedList.map(function (r) {
    var name = r.patient_full_name || r.fullName || '';
    var natId = r.national_id || r.nationalId || '';
    var hospId = r.hospital_patient_id || r.hospitalPatientId || '';
    var age = r.patient_age || r.age || 'غير متوفر';
    var gender = WCCMS.api.genderAr(r.gender) || r.gender || 'غير متوفر';
    var doc = r.supervising_doctor || r.supervisingDoctor || 'غير متوفر';
    var dept = r.hospital_department || r.hospitalDepartment || 'غير متوفر';
    var admDate = r.hospital_admission_date || r.admissionDate || '';
    var reqDate = r.requested_at || r.requestedAt || '';

    return '<tr>' +
      '<td><span class="patient-name fw-bold">' + name + '</span><br><small class="text-muted ltr-cell">' + r.id + '</small></td>' +
      '<td><span class="ltr-cell fw-semibold">' + natId + '</span></td>' +
      '<td><span class="ltr-cell text-muted">' + hospId + '</span></td>' +
      '<td><small>' + age + ' سنة · ' + gender + '</small></td>' +
      '<td><small>' + doc + '</small></td>' +
      '<td><span class="badge badge-light-primary px-2 py-1 fs-8">' + dept + '</span></td>' +
      '<td><span class="date-cell">' + formatDateDisplay(admDate) + '</span></td>' +
      '<td><span class="date-cell">' + formatDateDisplay(reqDate) + '</span></td>' +
      '<td class="text-end text-nowrap" style="min-width: 165px; width: 165px;">' +
        '<a href="complete-registration.html?id=' + r.id + '" class="btn btn-sm btn-primary text-nowrap d-inline-flex align-items-center gap-1.5 fw-bold px-3 text-decoration-none" style="white-space: nowrap !important; flex-wrap: nowrap !important; min-height: 32px !important; max-height: 32px !important; height: 32px !important;">' +
          '<i class="bi bi-clipboard2-plus-fill me-1"></i><span style="white-space: nowrap !important;">استكمال التسجيل</span>' +
        '</a>' +
      '</td>' +
      '</tr>';
  }).join('');

  renderPagination(totalItems);
}

function sortTable(key) {
  if (currentSortKey === key) {
    currentSortDir = currentSortDir === 'asc' ? 'desc' : 'asc';
  } else {
    currentSortKey = key;
    currentSortDir = 'asc';
  }
  var sortSelect = document.getElementById('sort-select');
  if (sortSelect) sortSelect.value = key + '-' + currentSortDir;
  filterRequests();
}

function renderPagination(totalItems) {
  var container = document.getElementById('pagination-container');
  var info = document.getElementById('pagination-info');
  if (!container) return;

  if (totalItems === 0) {
    container.innerHTML = '';
    if (info) info.textContent = 'عرض 0 من 0 طلبات';
    return;
  }

  var totalPages = Math.ceil(totalItems / pageSize);
  var startItem = (currentPage - 1) * pageSize + 1;
  var endItem = Math.min(currentPage * pageSize, totalItems);

  if (info) info.textContent = 'عرض ' + startItem + ' - ' + endItem + ' من ' + totalItems + ' طلبات بانتظار التسجيل';

  var html = '';

  // Previous button
  html += '<li class="page-item ' + (currentPage === 1 ? 'disabled' : '') + '">' +
    '<a class="page-link" href="#" onclick="changePage(' + (currentPage - 1) + '); return false;">السابق</a>' +
    '</li>';

  for (var p = 1; p <= totalPages; p++) {
    html += '<li class="page-item ' + (p === currentPage ? 'active' : '') + '">' +
      '<a class="page-link" href="#" onclick="changePage(' + p + '); return false;">' + p + '</a>' +
      '</li>';
  }

  // Next button
  html += '<li class="page-item ' + (currentPage === totalPages ? 'disabled' : '') + '">' +
    '<a class="page-link" href="#" onclick="changePage(' + (currentPage + 1) + '); return false;">التالي</a>' +
    '</li>';

  container.innerHTML = html;
}

function changePage(p) {
  currentPage = p;
  renderTable();
}

// ============================================================================
// UTILITY FUNCTIONS
// ============================================================================
function findRequest(id) {
  return WCCMS.api.getRequestById(id);
}

function setEl(id, val) {
  var el = document.getElementById(id);
  if (el) el.textContent = val;
}

function formatDateDisplay(isoStr) {
  if (!isoStr) return 'غير متوفر';
  if (window.WCCMS && window.WCCMS.api) {
    if (isoStr.includes('T') && typeof window.WCCMS.api.formatDateTime === 'function') {
      return window.WCCMS.api.formatDateTime(isoStr);
    }
    if (typeof window.WCCMS.api.formatDate === 'function') {
      return window.WCCMS.api.formatDate(isoStr);
    }
  }
  var cleanStr = String(isoStr).replace('T', ' ').replace('Z', '').split('.')[0];
  var parts = cleanStr.split(' ');
  var dateParts = parts[0].split('-');
  if (dateParts.length === 3) {
    var formattedDate = dateParts[2].padStart(2, '0') + '/' + dateParts[1].padStart(2, '0') + '/' + dateParts[0];
    if (parts[1]) {
      return formattedDate + ' — ' + parts[1].substring(0, 5);
    }
    return formattedDate;
  }
  return isoStr;
}

function formatDate(d) {
  if (!d) return 'غير متوفر';
  var y = d.getFullYear();
  var m = String(d.getMonth() + 1).padStart(2, '0');
  var day = String(d.getDate()).padStart(2, '0');
  return day + '/' + m + '/' + y;
}

function showToast(message, type) {
  type = type || 'info';
  var container = document.getElementById('toast-container');
  if (!container) return;

  var icons = { success: 'bi-check-circle-fill', danger: 'bi-exclamation-circle-fill', warning: 'bi-exclamation-triangle-fill', info: 'bi-info-circle-fill' };
  var icon = icons[type] || icons.info;

  var toastEl = document.createElement('div');
  toastEl.className = 'm8-toast toast-' + type;
  toastEl.innerHTML = '<i class="bi ' + icon + ' m8-toast-icon"></i><span>' + message + '</span>';
  container.appendChild(toastEl);

  setTimeout(function () {
    toastEl.style.animation = 'toastOut 0.3s ease forwards';
    setTimeout(function () { if (toastEl.parentNode) toastEl.parentNode.removeChild(toastEl); }, 320);
  }, 4000);
}
