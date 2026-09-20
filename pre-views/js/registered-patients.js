/**
 * ============================================================================
 * WCCMS — Registered Patients Page Controller (Page 4)
 * Displays ACTIVE wound-care registrations.
 * Clicking a patient name or "فتح الحالة" navigates to Page 3 (daily-review.html?regId=…)
 *
 * Depends on: shared-data.js  (window.WCCMS_DB)
 * ============================================================================
 */

'use strict';

// ============================================================================
// DATE HELPERS
// ============================================================================
var TODAY_STR = (function () {
  var d = new Date();
  return d.getFullYear() + '-' +
    String(d.getMonth() + 1).padStart(2, '0') + '-' +
    String(d.getDate()).padStart(2, '0');
})();

// ============================================================================
// DATA SOURCE — pull from shared registry via WCCMS.api
// ============================================================================
function getActiveRegistrationsList() {
  if (window.WCCMS && window.WCCMS.api) {
    return window.WCCMS.api.getEnrichedActiveRegistrations().map(function (r) {
      return {
        registrationId: r.registration_id,
        patientId: r.patient_id,
        fullName: r.full_name,
        nationalId: r.national_id,
        hospitalPatientId: r.hospital_patient_id,
        age: r.age,
        gender: r.gender_ar || r.gender,
        supervisingDoctor: r.supervising_doctor,
        hospitalDepartment: r.hospital_department,
        departmentKey: r.department_id,
        registrationDate: r.registration_date,
        diagnosesCount: r.diagnoses_count,
        latestDailyReview: r.latest_daily_review,
        nextScheduledReview: r.next_scheduled_review
      };
    });
  }
  return [];
}

var ACTIVE_REGISTRATIONS = getActiveRegistrationsList();

// ============================================================================
// PAGE STATE
// ============================================================================
var filteredData = ACTIVE_REGISTRATIONS.slice();
var currentPage  = 1;
var pageSize     = 10;
var sortField    = 'registrationDate';
var sortDir      = 'desc';

// ============================================================================
// INITIALIZATION
// ============================================================================
document.addEventListener('DOMContentLoaded', function () {
  applyTheme(localStorage.getItem('m8-theme') || 'light');
  initHeaderDate();
  bindEvents();
  ACTIVE_REGISTRATIONS = getActiveRegistrationsList();
  computeAndRenderStats();
  showSkeleton();
  // Simulate async data fetch
  setTimeout(function () {
    hideSkeleton();
    applyFilters();
  }, 300);
});

// ============================================================================
// THEME
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

function initHeaderDate() {
  var el = document.getElementById('header-date-display');
  if (el) el.textContent = TODAY_STR.replace(/-/g, '/');
}

// ============================================================================
// EVENT BINDING
// ============================================================================
function bindEvents() {
  var themeBtn = document.getElementById('m8-theme-toggle');
  if (themeBtn) themeBtn.addEventListener('click', function () {
    applyTheme(document.documentElement.getAttribute('data-bs-theme') === 'dark' ? 'light' : 'dark');
  });

  var mobileToggle = document.getElementById('m8-mobile-toggle');
  if (mobileToggle) mobileToggle.addEventListener('click', function () {
    var sb = document.getElementById('m8-sidebar');
    if (sb) sb.classList.toggle('show');
  });

  // Live search with debounce
  var searchInput = document.getElementById('rp-search');
  if (searchInput) {
    var debounceTimer;
    searchInput.addEventListener('keyup', function () {
      clearTimeout(debounceTimer);
      debounceTimer = setTimeout(function () { currentPage = 1; applyFilters(); }, 280);
    });
    searchInput.addEventListener('keydown', function (e) {
      if (e.key === 'Enter') { clearTimeout(debounceTimer); currentPage = 1; applyFilters(); }
    });
  }

  // Filter selects — auto-apply on change
  ['filter-dept', 'filter-doctor', 'filter-review', 'filter-scheduled', 'filter-reg-date'].forEach(function (id) {
    var el = document.getElementById(id);
    if (el) el.addEventListener('change', function () {
      if (id === 'filter-reg-date') handleRegDateFilterChange();
      currentPage = 1;
      applyFilters();
    });
  });

  // Page-size selector
  var pageSizeEl = document.getElementById('filter-page-size');
  if (pageSizeEl) pageSizeEl.addEventListener('change', function () {
    pageSize = parseInt(this.value, 10) || 10;
    currentPage = 1;
    renderTable();
    renderPagination();
  });

  // Custom date inputs
  ['filter-date-from', 'filter-date-to'].forEach(function (id) {
    var el = document.getElementById(id);
    if (el) el.addEventListener('change', function () { currentPage = 1; applyFilters(); });
  });

  // Column sort headers
  document.querySelectorAll('.rp-sortable').forEach(function (th) {
    th.addEventListener('click', function () {
      var field = this.getAttribute('data-sort');
      if (sortField === field) {
        sortDir = sortDir === 'asc' ? 'desc' : 'asc';
      } else {
        sortField = field;
        sortDir = 'asc';
      }
      updateSortHeadersUI();
      sortData();
      currentPage = 1;
      renderTable();
      renderPagination();
    });
  });
}

function handleRegDateFilterChange() {
  var val  = document.getElementById('filter-reg-date').value;
  var fromW = document.getElementById('custom-date-from-wrapper');
  var toW   = document.getElementById('custom-date-to-wrapper');
  if (val === 'custom') {
    if (fromW) fromW.style.display = '';
    if (toW)   toW.style.display   = '';
  } else {
    if (fromW) fromW.style.display = 'none';
    if (toW)   toW.style.display   = 'none';
  }
}

// ============================================================================
// STATISTICS
// ============================================================================
function computeAndRenderStats() {
  var data       = ACTIVE_REGISTRATIONS;
  var total      = data.length;
  var today      = data.filter(function (r) { return r.registrationDate === TODAY_STR; }).length;
  var needReview = data.filter(function (r) { return r.latestDailyReview !== TODAY_STR; }).length;
  var scheduled  = data.filter(function (r) { return !!r.nextScheduledReview; }).length;

  setEl('stat-total-active',  total);
  setEl('stat-today',         today);
  setEl('stat-need-review',   needReview);
  setEl('stat-scheduled',     scheduled);
  setEl('header-active-count', total);
}

// ============================================================================
// FILTER → SORT → PAGINATE
// ============================================================================
function applyFilters() {
  var query    = val('rp-search').toLowerCase();
  var dept     = val('filter-dept');
  var doctor   = val('filter-doctor');
  var review   = val('filter-review');
  var sched    = val('filter-scheduled');
  var regRange = val('filter-reg-date');
  var fromDate = val('filter-date-from');
  var toDate   = val('filter-date-to');

  var cutoff7  = daysAgoStr(7);
  var cutoff30 = daysAgoStr(30);

  filteredData = ACTIVE_REGISTRATIONS.filter(function (r) {
    // text search
    if (query) {
      var haystack = ((r.fullName || '') + ' ' + (r.nationalId || '') + ' ' + (r.hospitalPatientId || '')).toLowerCase();
      if (!haystack.includes(query)) return false;
    }
    if (dept   && r.departmentKey     !== dept)   return false;
    if (doctor && r.supervisingDoctor !== doctor) return false;
    if (review === 'today' && r.latestDailyReview !== TODAY_STR)  return false;
    if (review === 'none'  && r.latestDailyReview === TODAY_STR)   return false;
    if (sched  === 'has'   && !r.nextScheduledReview)              return false;
    if (sched  === 'none'  &&  r.nextScheduledReview)              return false;
    if (regRange === 'today' && r.registrationDate !== TODAY_STR)  return false;
    if (regRange === '7d'    && r.registrationDate <  cutoff7)     return false;
    if (regRange === '30d'   && r.registrationDate <  cutoff30)    return false;
    if (regRange === 'custom') {
      if (fromDate && r.registrationDate < fromDate) return false;
      if (toDate   && r.registrationDate > toDate)   return false;
    }
    return true;
  });

  sortData();
  currentPage = 1;
  renderTable();
  renderPagination();
}

function sortData() {
  filteredData.sort(function (a, b) {
    var av, bv;
    switch (sortField) {
      case 'name':
        return sortDir === 'asc'
          ? (a.fullName || '').localeCompare(b.fullName || '', 'ar')
          : (b.fullName || '').localeCompare(a.fullName || '', 'ar');
      case 'regDate':
        av = a.registrationDate || '';  bv = b.registrationDate || '';  break;
      case 'lastReview':
        av = a.latestDailyReview   || '1900-01-01';
        bv = b.latestDailyReview   || '1900-01-01'; break;
      case 'nextReview':
        av = a.nextScheduledReview || '9999-12-31';
        bv = b.nextScheduledReview || '9999-12-31'; break;
      default:
        av = a.registrationDate || ''; bv = b.registrationDate || '';
    }
    if (av < bv) return sortDir === 'asc' ? -1 : 1;
    if (av > bv) return sortDir === 'asc' ?  1 : -1;
    return 0;
  });
}

function updateSortHeadersUI() {
  document.querySelectorAll('.rp-sortable').forEach(function (th) {
    th.classList.remove('sort-asc', 'sort-desc', 'rp-sort-active');
    var icon = th.querySelector('.sort-icon');
    if (icon) icon.className = 'bi bi-arrow-down-up fs-8 ms-1 sort-icon';
  });
  var active = document.querySelector('.rp-sortable[data-sort="' + sortField + '"]');
  if (active) {
    active.classList.add(sortDir === 'asc' ? 'sort-asc' : 'sort-desc', 'rp-sort-active');
    var aIcon = active.querySelector('.sort-icon');
    if (aIcon) aIcon.className = 'bi bi-arrow-' + (sortDir === 'asc' ? 'up' : 'down') + ' fs-8 ms-1 sort-icon text-primary';
  }
}

// ============================================================================
// RENDER TABLE
// ============================================================================
function renderTable() {
  var tbody   = document.getElementById('rp-tbody');
  var empty   = document.getElementById('rp-empty');
  var noRes   = document.getElementById('rp-no-results');
  var tbl     = document.getElementById('rp-table');
  var badge   = document.getElementById('table-count-badge');

  if (!tbody) return;

  empty.classList.add('d-none');
  noRes.classList.add('d-none');
  tbl.style.display = '';

  var hasAny     = ACTIVE_REGISTRATIONS.length > 0;
  var hasResults = filteredData.length > 0;

  if (!hasAny) {
    tbl.style.display = 'none';
    empty.classList.remove('d-none');
    if (badge) badge.textContent = '0 مريض';
    setEl('pagination-info', 'لا توجد حالات نشطة');
    renderPagination();
    return;
  }

  if (!hasResults) {
    tbl.style.display = 'none';
    noRes.classList.remove('d-none');
    if (badge) badge.textContent = '0 نتيجة';
    setEl('pagination-info', 'لا توجد نتائج مطابقة');
    renderPagination();
    return;
  }

  var start = (currentPage - 1) * pageSize;
  var end   = Math.min(start + pageSize, filteredData.length);
  var page  = filteredData.slice(start, end);

  if (badge) badge.textContent = filteredData.length + ' مريض';
  setEl('pagination-info', 'عرض ' + (start + 1) + '–' + end + ' من ' + filteredData.length + ' مريض');

  tbody.innerHTML = page.map(buildTableRow).join('');

  // Initialize Bootstrap Popovers for newly rendered rows
  tbody.querySelectorAll('.m8-popover-trigger[data-bs-toggle="popover"]').forEach(function (el) {
    if (!el._bsPopoverInit) {
      el._bsPopoverInit = true;
      new bootstrap.Popover(el, { html: true, trigger: 'hover focus', placement: 'bottom' });
    }
  });
}

// ============================================================================
// BUILD TABLE ROW — patient name + avatar are now CLICKABLE navigation links
// ============================================================================
function buildTableRow(r) {
  var openUrl = 'daily-review.html?regId=' + encodeURIComponent(r.registrationId);

  // Last review indicator
  var lastReviewDisplay;
  if (!r.latestDailyReview) {
    lastReviewDisplay = '<span class="rp-indicator no-review"><i class="bi bi-dash-circle fs-8"></i> لا توجد مراجعة</span>';
  } else if (r.latestDailyReview === TODAY_STR) {
    lastReviewDisplay = '<span class="rp-indicator has-review-today"><i class="bi bi-check-circle-fill fs-8"></i> مراجعة اليوم</span>';
  } else {
    lastReviewDisplay = '<span class="rp-date-cell">' + formatDateDisplay(r.latestDailyReview) + '</span>';
  }

  // Next review indicator
  var nextReviewDisplay;
  if (!r.nextScheduledReview) {
    nextReviewDisplay = '<span class="rp-indicator no-review"><i class="bi bi-dash-circle fs-8"></i> لا يوجد موعد</span>';
  } else {
    nextReviewDisplay = '<span class="rp-indicator has-schedule"><i class="bi bi-calendar-event-fill fs-8"></i> موعد: ' + formatDateDisplay(r.nextScheduledReview) + '</span>';
  }

  // Popover quick-info content (hover only — does NOT navigate)
  var popoverContent =
    '<div class="text-end" style="font-size:0.82rem;min-width:230px;">' +
    '<div class="mb-1"><span class="text-muted">الهوية:</span> <strong>' + escapeHtml(r.nationalId) + '</strong></div>' +
    '<div class="mb-1"><span class="text-muted">رقم الملف:</span> <strong>' + escapeHtml(r.hospitalPatientId) + '</strong></div>' +
    '<div class="mb-1"><span class="text-muted">العمر / الجنس:</span> ' + r.age + ' سنة / ' + escapeHtml(r.gender) + '</div>' +
    '<div class="mb-1"><span class="text-muted">القسم:</span> ' + escapeHtml(r.hospitalDepartment) + '</div>' +
    '<div class="mb-2"><span class="text-muted">الطبيب:</span> ' + escapeHtml(r.supervisingDoctor) + '</div>' +
    '<div class="pt-2 border-top text-center"><a href="patient-history.html?patientId=' + encodeURIComponent(r.patientId || r.registrationId) + '" class="btn btn-sm btn-light-primary fw-bold text-decoration-none py-1 px-2 d-inline-block" style="font-size:0.75rem;"><i class="bi bi-file-earmark-medical-fill me-1"></i>التاريخ الصحي للمريض</a></div>' +
    '</div>';

  return '<tr class="rp-row" onclick="openPatientCase(\'' + escapeAttr(r.registrationId) + '\')">' +
    '<td>' +
      '<div class="d-flex align-items-center gap-2">' +
        /* Avatar — clicking navigates */
        '<a href="' + openUrl + '" class="rp-avatar-link flex-shrink-0" onclick="event.stopPropagation()" title="فتح حالة ' + escapeAttr(r.fullName) + '">' +
          '<div class="rp-patient-avatar">' +
            '<i class="bi bi-person-fill text-white" style="font-size:0.9rem;"></i>' +
          '</div>' +
        '</a>' +
        '<div>' +
          /* Clickable patient name */
          '<a href="' + openUrl + '" class="rp-patient-name-link d-block" onclick="event.stopPropagation()">' +
            escapeHtml(r.fullName) +
          '</a>' +
          '<span class="rp-national-id">رقم الهوية: ' + escapeHtml(r.nationalId) + '</span>' +
        '</div>' +
        /* Quick-info popover icon */
        '<button type="button" class="rp-info-btn m8-popover-trigger ms-1 flex-shrink-0"' +
          ' data-bs-toggle="popover" data-bs-placement="bottom" data-bs-trigger="hover focus"' +
          ' data-bs-html="true" data-bs-title="' + escapeAttr(r.fullName) + '"' +
          ' data-bs-content="' + escapeAttr(popoverContent) + '"' +
          ' onclick="event.stopPropagation()" title="معلومات المريض والتاريخ الصحي">' +
          '<i class="bi bi-info-circle text-muted"></i>' +
        '</button>' +
      '</div>' +
    '</td>' +
    '<td><span class="rp-hospital-id">' + escapeHtml(r.hospitalPatientId) + '</span></td>' +
    '<td><span class="fw-semibold">' + r.age + ' سنة</span><br><small class="text-muted">' + escapeHtml(r.gender) + '</small></td>' +
    '<td><small class="text-muted">' + escapeHtml(r.hospitalDepartment) + '</small></td>' +
    '<td><small>' + escapeHtml(r.supervisingDoctor) + '</small></td>' +
    '<td><span class="rp-date-cell">' + formatDateDisplay(r.registrationDate) + '</span></td>' +
    '<td>' +
      '<span class="rp-diag-badge"><i class="bi bi-heart-pulse-fill"></i> ' + r.diagnosesCount + ' تشخيص</span>' +
    '</td>' +
    '<td>' + lastReviewDisplay + '</td>' +
    '<td>' + nextReviewDisplay + '</td>' +
    '<td class="text-center" onclick="event.stopPropagation()">' +
      '<div class="d-inline-flex align-items-center gap-1">' +
        '<a href="' + openUrl + '" class="btn-open-case" title="فتح المراجعة اليومية لحالة: ' + escapeAttr(r.fullName) + '">' +
          '<i class="bi bi-folder2-open"></i> المراجعة اليومية' +
        '</a>' +
        '<a href="patient-history.html?patientId=' + encodeURIComponent(r.patientId || r.registrationId) + '" class="btn btn-sm btn-light-info fw-bold d-inline-flex align-items-center gap-1" title="عرض التاريخ الصحي والسجل السريري الكامل للمريض">' +
          '<i class="bi bi-file-earmark-medical-fill"></i><span>التاريخ الصحي</span>' +
        '</a>' +
      '</div>' +
    '</td>' +
  '</tr>';
}

/**
 * Navigate to Page 3 for the given registration (called on full-row click).
 */
function openPatientCase(regId) {
  window.location.href = 'daily-review.html?regId=' + encodeURIComponent(regId);
}

// ============================================================================
// PAGINATION
// ============================================================================
function renderPagination() {
  var container  = document.getElementById('pagination-container');
  if (!container) return;
  var totalPages = Math.ceil(filteredData.length / pageSize);
  if (totalPages <= 1) { container.innerHTML = ''; return; }

  var items = '';
  items += '<li class="page-item' + (currentPage === 1 ? ' disabled' : '') + '">' +
    '<button class="page-link" onclick="goToPage(' + (currentPage - 1) + ')">السابق</button></li>';

  var startP = Math.max(1, currentPage - 2);
  var endP   = Math.min(totalPages, startP + 4);
  if (endP - startP < 4) startP = Math.max(1, endP - 4);

  if (startP > 1) {
    items += '<li class="page-item"><button class="page-link" onclick="goToPage(1)">1</button></li>';
    if (startP > 2) items += '<li class="page-item disabled"><span class="page-link">...</span></li>';
  }
  for (var p = startP; p <= endP; p++) {
    items += '<li class="page-item' + (p === currentPage ? ' active' : '') + '">' +
      '<button class="page-link" onclick="goToPage(' + p + ')">' + p + '</button></li>';
  }
  if (endP < totalPages) {
    if (endP < totalPages - 1) items += '<li class="page-item disabled"><span class="page-link">...</span></li>';
    items += '<li class="page-item"><button class="page-link" onclick="goToPage(' + totalPages + ')">' + totalPages + '</button></li>';
  }
  items += '<li class="page-item' + (currentPage === totalPages ? ' disabled' : '') + '">' +
    '<button class="page-link" onclick="goToPage(' + (currentPage + 1) + ')">التالي</button></li>';

  container.innerHTML = items;
}

function goToPage(p) {
  var totalPages = Math.ceil(filteredData.length / pageSize);
  if (p < 1 || p > totalPages) return;
  currentPage = p;
  renderTable();
  renderPagination();
  window.scrollTo({ top: 0, behavior: 'smooth' });
}

// ============================================================================
// RESET FILTERS
// ============================================================================
function resetFilters() {
  ['rp-search','filter-dept','filter-doctor','filter-review','filter-scheduled',
   'filter-reg-date','filter-date-from','filter-date-to'].forEach(function (id) {
    var el = document.getElementById(id);
    if (el) el.value = '';
  });
  var fromW = document.getElementById('custom-date-from-wrapper');
  var toW   = document.getElementById('custom-date-to-wrapper');
  if (fromW) fromW.style.display = 'none';
  if (toW)   toW.style.display   = 'none';
  sortField = 'registrationDate';
  sortDir   = 'desc';
  updateSortHeadersUI();
  currentPage = 1;
  applyFilters();
}

// ============================================================================
// SKELETON LOADER
// ============================================================================
function showSkeleton() {
  var sk  = document.getElementById('rp-skeleton');
  var tbl = document.getElementById('rp-table');
  var sp  = document.getElementById('page-loading-spinner');
  if (sk)  sk.classList.remove('d-none');
  if (tbl) tbl.style.display = 'none';
  if (sp)  sp.classList.remove('d-none');
}

function hideSkeleton() {
  var sk  = document.getElementById('rp-skeleton');
  var tbl = document.getElementById('rp-table');
  var sp  = document.getElementById('page-loading-spinner');
  if (sk)  sk.classList.add('d-none');
  if (tbl) tbl.style.display = '';
  if (sp)  sp.classList.add('d-none');
}

// ============================================================================
// UTILITIES
// ============================================================================
function val(id) { var el = document.getElementById(id); return el ? el.value : ''; }
function setEl(id, v) { var el = document.getElementById(id); if (el) el.textContent = v; }

function formatDateDisplay(isoStr) {
  if (window.WCCMS_DB && typeof window.WCCMS_DB.formatDateDisplay === 'function') {
    return window.WCCMS_DB.formatDateDisplay(isoStr);
  }
  if (!isoStr) return 'غير متوفر';
  var p = isoStr.split(' ')[0].split('-');
  return p.length === 3 ? p[2].padStart(2, '0') + '/' + p[1].padStart(2, '0') + '/' + p[0] : isoStr;
}

function daysAgoStr(n) {
  var d = new Date(); d.setDate(d.getDate() - n);
  return d.getFullYear() + '-' + String(d.getMonth()+1).padStart(2,'0') + '-' + String(d.getDate()).padStart(2,'0');
}

function escapeHtml(s) {
  if (!s) return '';
  return String(s).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;').replace(/'/g,'&#39;');
}

function escapeAttr(s) {
  if (!s) return '';
  return String(s).replace(/&/g,'&amp;').replace(/"/g,'&quot;').replace(/'/g,'&#39;').replace(/</g,'&lt;').replace(/>/g,'&gt;');
}

function showToast(message, type) {
  type = type || 'info';
  var c = document.getElementById('toast-container');
  if (!c) return;
  var icons = { success:'bi-check-circle-fill', danger:'bi-exclamation-circle-fill', warning:'bi-exclamation-triangle-fill', info:'bi-info-circle-fill' };
  var el = document.createElement('div');
  el.className = 'm8-toast toast-' + type;
  el.innerHTML = '<i class="bi ' + (icons[type] || icons.info) + ' m8-toast-icon"></i><span>' + message + '</span>';
  c.appendChild(el);
  setTimeout(function () {
    el.style.animation = 'toastOut 0.3s ease forwards';
    setTimeout(function () { if (el.parentNode) el.parentNode.removeChild(el); }, 320);
  }, 4000);
}
