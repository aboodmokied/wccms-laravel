/**
 * ============================================================================
 * WCCMS — Daily Review Controller (Page 3)
 *
 * On load: reads ?regId= from URL → looks up WCCMS_DB → populates CURRENT_PATIENT.
 * Falls back to REG-001 when opened directly (no regId in URL).
 *
 * Handles multi-diagnosis Daily Review modal, per-diagnosis outcomes,
 * overall outcome processing, collapsible card timeline, scheduled reviews,
 * and hospital auto-discharge simulation.
 *
 * Depends on: shared-data.js  (window.WCCMS_DB)
 * ============================================================================
 */

'use strict';

// ============================================================================
// CURRENT USER
// ============================================================================
var CURRENT_USER = {
  id: 'USR-01',
  name: 'أ. فاطمة الزهراني',
  role: 'ممرضة'
};

// ============================================================================
// PATIENT STATE — loaded from WCCMS_DB based on URL ?regId=
// ============================================================================
var CURRENT_PATIENT    = null;
var PREVIOUS_HISTORY   = [];
var DAILY_REVIEWS      = [];
var SCHEDULED_REVIEWS  = [];

var isHospitalDischarged = false;
var selectedOverallOutcomeKey = null;

// ============================================================================
// LOOKUP TABLES
// ============================================================================
var DRESSING_OPTIONS = [
  { nameAr: 'رغوية (Foam)' },
  { nameAr: 'هيدروغروانية (Hydrocolloid)' },
  { nameAr: 'هيدروجيل (Hydrogel)' },
  { nameAr: 'ألجينات (Alginate)' },
  { nameAr: 'فضة مضادة للميكروبات (Silver)' },
  { nameAr: 'سيليكون (Silicone)' },
  { nameAr: 'ضغط سلبي NPWT' }
];

var MEDICATION_OPTIONS = [
  { nameAr: 'مرهم مضاد حيوي' },
  { nameAr: 'غسول ملحي' },
  { nameAr: 'بيتادين' },
  { nameAr: 'عسل طبي (Manuka)' },
  { nameAr: 'كولاجين' }
];

// ============================================================================
// INITIALIZATION
// ============================================================================
document.addEventListener('DOMContentLoaded', function () {
  applyTheme(localStorage.getItem('m8-theme') || 'light');
  loadPatientFromUrl();
  bindEvents();
  renderPatientInfo();
  renderPreviousHistory();
  renderWoundTabs();
  renderDailyReviewTimeline();
  updateBackNavLink();
});

/**
 * Read regId from URL → load patient data from WCCMS_DB.
 * Falls back to REG-001 for direct page access.
 */
function loadPatientFromUrl() {
  var regId = WCCMS.api.getUrlParam('regId');
  var enriched = WCCMS.api.getEnrichedRegistration(regId);

  if (enriched) {
    CURRENT_PATIENT = {
      registrationId:    enriched.registration_id,
      patientId:         enriched.patient_id,
      hospitalPatientId: enriched.hospital_patient_id || '',
      nationalId:        enriched.national_id || '',
      fullName:          enriched.full_name || '',
      gender:            enriched.gender_ar || WCCMS.api.genderAr(enriched.gender) || enriched.gender || '',
      age:               enriched.age || 0,
      supervisingDoctor: enriched.supervising_doctor || '',
      hospitalDepartment:enriched.hospital_department || '',
      departmentName:    'عيادة العناية بالجروح',
      registrationDate:  WCCMS.api.formatDate(enriched.registration_date),
      registeringNurse:  enriched.registering_nurse || 'أ. فاطمة الزهراني',
      status:            enriched.status,
      dischargeDate:     WCCMS.api.formatDate(enriched.discharge_date),
      procedure:         enriched.procedures && enriched.procedures.length > 0 ? enriched.procedures[0].name : 'تقييم الجرح (Wound Assessment)',
      pmh:               (enriched.pmh || []).map(function(m) { return typeof m === 'object' ? (m.name || m.full_name || '') : m; }),
      diagnoses:         (enriched.diagnoses || []).map(function (d) {
        return {
          id: d.id,
          diagnosis_id: d.diagnosis_id,
          name: d.name || d.diagnosis_name || '',
          woundType: d.wound_type || d.wound_type_name || '',
          wound_type_id: d.wound_type_id,
          dressing: d.dressing || d.initial_dressing_name || 'لا يوجد',
          dressing_id: d.initial_dressing_id,
          medication: d.medication || d.initial_medication_name || 'لا يوجد',
          medication_id: d.initial_medication_id,
          description: d.description || ''
        };
      })
    };

    PREVIOUS_HISTORY  = (enriched.previous_registrations || []).map(function(pr) {
      return {
        registrationId: pr.registration_id,
        registrationDate: WCCMS.api.formatDate(pr.registration_date),
        dischargeDate: WCCMS.api.formatDate(pr.discharge_date),
        diagnoses: pr.diagnoses_names || (pr.diagnoses || []).map(function(d) { return d.diagnosis_name || d.name; }).join('، '),
        outcome: pr.outcome || pr.discharge_type || 'DISCHARGED',
        dressings: '—',
        medications: '—',
        woundSummary: 'حالة مسجلة سابقة'
      };
    });

    DAILY_REVIEWS     = (enriched.daily_reviews || []).map(function(dr) {
      return {
        id: dr.id,
        date: WCCMS.api.formatDateTime(dr.date),
        nurse: dr.nurse || 'أ. فاطمة الزهراني',
        woundName: dr.diagnosis_name || dr.woundName || '',
        woundType: dr.wound_type || dr.woundType || '',
        description: dr.wound_description || dr.description || '',
        changes: dr.wound_changes || dr.changes || '',
        dressing: dr.dressing || 'مستمرة',
        medication: dr.medication || 'مستمر',
        diagOutcome: dr.status === 'IMPROVED_DISCHARGE' ? 'تحسن' : (dr.status === 'WORSENED' ? 'تفاقم' : 'متابعة'),
        overallOutcomeKey: dr.status
      };
    });

    SCHEDULED_REVIEWS = (enriched.scheduled_reviews || []).slice();
  } else {
    // Default fallback if no data
    CURRENT_PATIENT = {
      registrationId: 'REG-001', patientId: 'PAT-001',
      hospitalPatientId: 'HP-10025', nationalId: '1087654321',
      fullName: 'أحمد محمد علي', gender: 'ذكر', age: 52,
      supervisingDoctor: 'د. محمد خالد', hospitalDepartment: 'العناية المركزة (ICU)',
      departmentName: 'عيادة العناية بالجروح', registrationDate: '2026/08/31',
      registeringNurse: 'أ. فاطمة الزهراني', status: 'ACTIVE', dischargeDate: null,
      procedure: 'تنضير الجرح (Debridement)',
      pmh: ['السكري (Diabetes Mellitus)', 'ضغط الدم (Hypertension)'],
      diagnoses: [
        { id: 'rdx001', name: 'قدم سكري (Diabetic Foot Ulcer)', woundType: 'نخري (Necrotic)', dressing: 'رغوية (Foam)', medication: 'مرهم مضاد حيوي', description: 'جرح عميق في الكعب الأيسر مع إفرازات قيحية.' }
      ]
    };
    PREVIOUS_HISTORY  = [];
    DAILY_REVIEWS     = [];
    SCHEDULED_REVIEWS = [];
  }
}

// ============================================================================
// THEME & EVENTS
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
    applyTheme(document.documentElement.getAttribute('data-bs-theme') === 'dark' ? 'light' : 'dark');
  });
  var mob = document.getElementById('m8-mobile-toggle');
  if (mob) mob.addEventListener('click', function () {
    var sb = document.getElementById('m8-sidebar');
    if (sb) sb.classList.toggle('show');
  });
}

/**
 * Update the "back" link in the header and breadcrumb to point to Page 4.
 */
function updateBackNavLink() {
  var backBtn = document.getElementById('btn-back-to-patients');
  if (backBtn) backBtn.href = 'registered-patients.html';

  // Breadcrumb "المرضى المسجلين" link
  var bcLink = document.getElementById('bc-registered-patients-link');
  if (bcLink) bcLink.href = 'registered-patients.html';
}

// ============================================================================
// RENDER PATIENT INFO
// ============================================================================
function renderPatientInfo() {
  var p = CURRENT_PATIENT;
  if (!p) return;

  setEl('bc-patient-name',    p.fullName);
  setEl('hdr-patient-name',   p.fullName);
  setEl('hdr-reg-id',         p.registrationId);
  setEl('card-national-id',   p.nationalId);
  setEl('card-hospital-id',   p.hospitalPatientId);
  setEl('card-age-gender',    p.age + ' سنة · ' + p.gender);
  setEl('card-reg-date',      p.registrationDate);
  setEl('card-doctor',        p.supervisingDoctor);
  setEl('card-dept',          p.hospitalDepartment);
  setEl('card-nurse',         p.registeringNurse);
  setEl('current-proc-name',  p.procedure);
  setEl('current-pmh-list',   p.pmh.join('، '));

  // Modal patient bar
  setEl('modal-patient-name',    p.fullName);
  setEl('modal-hospital-id',     p.hospitalPatientId);
  setEl('modal-reg-id',          p.registrationId);
  setEl('modal-diag-count-badge','عدد التشخيصات الخاضعة للمراجعة: ' + p.diagnoses.length);

  // Dynamic link to Page 6 (Patient History)
  var historyLink = document.getElementById('btn-patient-history-link');
  var hdrHistoryLink = document.getElementById('hdr-patient-history-link');
  var fullHistoryBtn = document.getElementById('btn-view-full-history');
  var historyUrl = 'patient-history.html?patientId=' + encodeURIComponent(p.patientId || p.registrationId);
  if (historyLink) historyLink.href = historyUrl;
  if (hdrHistoryLink) hdrHistoryLink.href = historyUrl;
  if (fullHistoryBtn) fullHistoryBtn.href = historyUrl;

  renderRegistrationStatusBadge(p.status);
}

function renderRegistrationStatusBadge(status) {
  var badge = document.getElementById('reg-status-badge');
  if (!badge) return;
  var map = {
    'ACTIVE':               ['badge-light-success', 'bi-circle-fill text-success', 'نشطة (ACTIVE)'],
    'DISCHARGED':           ['badge-light-info',    'bi-check-circle-fill text-info', 'تم الترخيص (DISCHARGED)'],
    'DISCHARGED_SCHEDULED': ['badge-light-primary', 'bi-calendar-check-fill text-primary', 'ترخيص ومراجعة مجدولة'],
    'CLOSED_DEATH':         ['badge-light-danger',  'bi-x-circle-fill text-danger', 'وفاة (CLOSED_DEATH)'],
    'HOSPITAL_AUTO':        ['badge-light-warning', 'bi-exclamation-octagon-fill text-warning', 'خروج تلقائي من المستشفى']
  };
  var s = map[status] || map['ACTIVE'];
  badge.className = 'badge ' + s[0] + ' fs-7 px-3 py-2';
  badge.innerHTML = '<i class="bi ' + s[1] + ' me-1 fs-8"></i> ' + s[2];
  if (status !== 'ACTIVE') disableReviewButton();
  else enableReviewButton();
}

function renderPreviousHistory() {
  var container = document.getElementById('prev-history-content');
  var badge     = document.getElementById('prev-history-badge');
  if (!container) return;

  if (!PREVIOUS_HISTORY || PREVIOUS_HISTORY.length === 0) {
    container.innerHTML = '<div class="text-center py-3 text-muted"><i class="bi bi-info-circle fs-3 d-block mb-1 opacity-50"></i>لا يوجد تسجيل سابق للمريض</div>';
    if (badge) badge.textContent = 'لا يوجد تسجيل سابق';
    return;
  }

  if (badge) badge.textContent = 'يوجد ' + PREVIOUS_HISTORY.length + ' تسجيل سابق';

  container.innerHTML = PREVIOUS_HISTORY.map(function (h) {
    return '<div class="p-3 border rounded mb-2 bg-light">' +
      '<div class="row g-2">' +
        '<div class="col-6 col-md-3"><small class="text-muted d-block">رقم التسجيل السابق</small><span class="fw-bold ltr-cell">' + (h.registrationId || '—') + '</span></div>' +
        '<div class="col-6 col-md-3"><small class="text-muted d-block">فترة العلاج</small><span class="fw-bold">' + (h.registrationDate || '—') + ' → ' + (h.dischargeDate || '—') + '</span></div>' +
        '<div class="col-6 col-md-3"><small class="text-muted d-block">التشخيص</small><span class="fw-bold">' + (h.diagnoses || '—') + '</span></div>' +
        '<div class="col-6 col-md-3"><small class="text-muted d-block">النتيجة</small><span class="badge badge-light-success">' + (h.outcome || '—') + '</span></div>' +
        '<div class="col-12 col-md-6"><small class="text-muted d-block">الضمادة / الدواء</small><span>' + (h.dressings || '—') + ' | ' + (h.medications || '—') + '</span></div>' +
        '<div class="col-12 col-md-6"><small class="text-muted d-block">الملخص التاريخي</small><span class="text-muted">' + (h.woundSummary || '—') + '</span></div>' +
      '</div>' +
    '</div>';
  }).join('');
}

// ============================================================================
// WOUND TABS
// ============================================================================
function renderWoundTabs() {
  var tabsNav     = document.getElementById('woundTabs');
  var tabsContent = document.getElementById('woundTabsContent');
  if (!tabsNav || !tabsContent || !CURRENT_PATIENT) return;

  var diags = CURRENT_PATIENT.diagnoses;

  tabsNav.innerHTML = diags.map(function (d, i) {
    return '<li class="nav-item" role="presentation">' +
      '<button class="nav-link diag-tab ' + (i === 0 ? 'active' : '') + '" id="tab-btn-' + d.id + '"' +
        ' data-bs-toggle="tab" data-bs-target="#tab-pane-' + d.id + '" type="button" role="tab">' +
        '<i class="bi bi-heart-pulse me-2"></i>تشخيص #' + (i + 1) + ': ' + d.name +
      '</button></li>';
  }).join('');

  tabsContent.innerHTML = diags.map(function (d, i) {
    return '<div class="tab-pane fade ' + (i === 0 ? 'show active' : '') + '" id="tab-pane-' + d.id + '" role="tabpanel">' +
      '<div class="row g-3 p-3 border rounded bg-white">' +
        '<div class="col-12 col-md-6"><div class="m8-readonly-field"><span class="field-label">التشخيص المعين</span><span class="field-value fw-bold text-primary">' + d.name + '</span></div></div>' +
        '<div class="col-12 col-md-6"><div class="m8-readonly-field"><span class="field-label">نوع الجرح الحالي</span><span class="field-value fw-bold">' + d.woundType + '</span></div></div>' +
        '<div class="col-12 col-md-6"><div class="m8-readonly-field"><span class="field-label">الضمادة الحالية</span><span class="field-value text-success fw-bold">' + d.dressing + '</span></div></div>' +
        '<div class="col-12 col-md-6"><div class="m8-readonly-field"><span class="field-label">الدواء الحالي</span><span class="field-value text-info fw-bold">' + d.medication + '</span></div></div>' +
        '<div class="col-12"><div class="m8-readonly-field"><span class="field-label">الوصف المسجل للجرح</span><span class="field-value text-muted" style="font-weight:400;">' + d.description + '</span></div></div>' +
      '</div>' +
    '</div>';
  }).join('');
}

// ============================================================================
// DAILY REVIEW MODAL — MULTI-DIAGNOSIS ACCORDION
// ============================================================================
function openDailyReviewModal() {
  if (isHospitalDischarged || CURRENT_PATIENT.status !== 'ACTIVE') {
    showToast('تسجيل الحالة مغلق ولا يمكن إضافة مراجعة يومية جديدة.', 'danger');
    return;
  }
  renderModalDiagnosesAccordion();
  resetOverallOutcomeSelection();
  showModal('dailyReviewModal');
}

function renderModalDiagnosesAccordion() {
  var container = document.getElementById('modal-diagnoses-accordion');
  if (!container || !CURRENT_PATIENT) return;

  var diags = CURRENT_PATIENT.diagnoses;

  container.innerHTML = diags.map(function (d, idx) {
    var collapseId = 'modal-diag-collapse-' + idx;
    var headerId   = 'modal-diag-heading-' + idx;

    return '<div class="accordion-item border rounded mb-3 overflow-hidden" id="modal-diag-item-' + idx + '">' +
      '<h2 class="accordion-header" id="' + headerId + '">' +
        '<button class="accordion-button ' + (idx === 0 ? '' : 'collapsed') + ' fw-bold fs-6 text-primary bg-light"' +
          ' type="button" data-bs-toggle="collapse" data-bs-target="#' + collapseId + '"' +
          ' aria-expanded="' + (idx === 0 ? 'true' : 'false') + '">' +
          '<i class="bi bi-heart-pulse-fill me-2"></i>' +
          'تشخيص #' + (idx + 1) + ': ' + d.name + ' <span class="badge badge-light-secondary ms-2">' + d.woundType + '</span>' +
        '</button>' +
      '</h2>' +
      '<div id="' + collapseId + '" class="accordion-collapse collapse ' + (idx === 0 ? 'show' : '') + '"' +
          ' aria-labelledby="' + headerId + '" data-bs-parent="#modal-diagnoses-accordion">' +
        '<div class="accordion-body p-4 bg-white">' +
          '<!-- READ-ONLY CONTEXT -->' +
          '<div class="p-3 mb-4 rounded bg-light border">' +
            '<div class="row g-2 text-dark fs-7">' +
              '<div class="col-12 col-md-3"><span class="text-muted d-block">التشخيص:</span><strong class="text-primary">' + d.name + '</strong></div>' +
              '<div class="col-6 col-md-3"><span class="text-muted d-block">نوع الجرح:</span><strong>' + d.woundType + '</strong></div>' +
              '<div class="col-6 col-md-3"><span class="text-muted d-block">الضمادة الحالية:</span><strong class="text-success">' + d.dressing + '</strong></div>' +
              '<div class="col-12 col-md-3"><span class="text-muted d-block">الدواء الحالي:</span><strong class="text-info">' + d.medication + '</strong></div>' +
              '<div class="col-12 mt-2"><span class="text-muted d-block">الوصف السابق للجرح:</span><span class="fst-italic text-muted">' + d.description + '</span></div>' +
            '</div>' +
          '</div>' +
          '<!-- REVIEW INPUTS -->' +
          '<div class="row g-3 mb-3">' +
            '<div class="col-12 col-md-6"><label for="modal-diag-desc-' + idx + '" class="form-label fw-bold">وصف الجرح الحالي <span class="text-danger">*</span></label>' +
              '<textarea id="modal-diag-desc-' + idx + '" class="form-control form-control-solid" rows="3" placeholder="أدخل الوصف التفصيلي لحالة الجرح اليوم..."></textarea></div>' +
            '<div class="col-12 col-md-6"><label for="modal-diag-chg-' + idx + '" class="form-label fw-bold">التغيرات والملاحظات اليومية <span class="text-danger">*</span></label>' +
              '<textarea id="modal-diag-chg-' + idx + '" class="form-control form-control-solid" rows="3" placeholder="أدخل التغيرات الملاحظة مقارنة بالتقييم السابق..."></textarea></div>' +
          '</div>' +
          '<!-- DRESSING & MEDICATION TOGGLES -->' +
          '<div class="row g-3 mb-4">' +
            '<div class="col-12 col-md-6"><div class="p-3 border rounded bg-light">' +
              '<div class="form-check form-switch mb-2">' +
                '<input class="form-check-input" type="checkbox" id="modal-toggle-dressing-' + idx + '" onchange="toggleModalDressingSelect(' + idx + ')"/>' +
                '<label class="form-check-label fw-bold" for="modal-toggle-dressing-' + idx + '">تغيرت الضمادة لهذا الجرح؟</label>' +
              '</div>' +
              '<div id="modal-dressing-wrapper-' + idx + '" class="d-none mt-2">' +
                '<label class="form-label fw-semibold">اختر الضمادة الجديدة</label>' +
                '<select id="modal-dressing-' + idx + '" class="form-select form-select-solid">' +
                  DRESSING_OPTIONS.map(function (o) { return '<option value="' + o.nameAr + '">' + o.nameAr + '</option>'; }).join('') +
                '</select>' +
              '</div>' +
            '</div></div>' +
            '<div class="col-12 col-md-6"><div class="p-3 border rounded bg-light">' +
              '<div class="form-check form-switch mb-2">' +
                '<input class="form-check-input" type="checkbox" id="modal-toggle-medication-' + idx + '" onchange="toggleModalMedicationSelect(' + idx + ')"/>' +
                '<label class="form-check-label fw-bold" for="modal-toggle-medication-' + idx + '">تغير الدواء لهذا الجرح؟</label>' +
              '</div>' +
              '<div id="modal-medication-wrapper-' + idx + '" class="d-none mt-2">' +
                '<label class="form-label fw-semibold">اختر الدواء الجديد</label>' +
                '<select id="modal-medication-' + idx + '" class="form-select form-select-solid">' +
                  MEDICATION_OPTIONS.map(function (o) { return '<option value="' + o.nameAr + '">' + o.nameAr + '</option>'; }).join('') +
                '</select>' +
              '</div>' +
            '</div></div>' +
          '</div>' +
          '<!-- PER-DIAGNOSIS OUTCOME -->' +
          '<div><label for="modal-diag-outcome-' + idx + '" class="form-label fw-bold">تقييم حالة هذا الجرح <span class="text-danger">*</span></label>' +
            '<select id="modal-diag-outcome-' + idx + '" class="form-select form-select-solid fw-semibold">' +
              '<option value="">اختر التقييم المناسب لهذا الجرح...</option>' +
              '<option value="IMPROVED">تحسن (Improved)</option>' +
              '<option value="WORSENED">تفاقم (Worsened)</option>' +
              '<option value="DEATH">وفاة (Death)</option>' +
            '</select>' +
          '</div>' +
        '</div></div>' +
    '</div>';
  }).join('');
}

function toggleModalDressingSelect(idx) {
  var chk     = document.getElementById('modal-toggle-dressing-' + idx);
  var wrapper = document.getElementById('modal-dressing-wrapper-' + idx);
  if (chk && wrapper) { chk.checked ? wrapper.classList.remove('d-none') : wrapper.classList.add('d-none'); }
}

function toggleModalMedicationSelect(idx) {
  var chk     = document.getElementById('modal-toggle-medication-' + idx);
  var wrapper = document.getElementById('modal-medication-wrapper-' + idx);
  if (chk && wrapper) { chk.checked ? wrapper.classList.remove('d-none') : wrapper.classList.add('d-none'); }
}

function selectOverallOutcome(key) {
  selectedOverallOutcomeKey = key;
  document.querySelectorAll('#overall-outcome-options .m8-outcome-card').forEach(function (c) {
    c.classList.remove('selected', 'border-primary');
  });
  var idMap = { 'IMPROVED_DISCHARGE':'card-outcome-1', 'IMPROVED_SCHEDULED':'card-outcome-2', 'WORSENED_STAY':'card-outcome-3', 'DEATH':'card-outcome-4' };
  var card = document.getElementById(idMap[key]);
  if (card) card.classList.add('selected', 'border-primary');
}

function resetOverallOutcomeSelection() {
  selectedOverallOutcomeKey = null;
  document.querySelectorAll('#overall-outcome-options .m8-outcome-card').forEach(function (c) {
    c.classList.remove('selected', 'border-primary');
  });
  document.querySelectorAll('input[name="overallOutcome"]').forEach(function (r) { r.checked = false; });
}

// ============================================================================
// SAVE DAILY REVIEW
// ============================================================================
function saveDailyReview() {
  if (isHospitalDischarged || CURRENT_PATIENT.status !== 'ACTIVE') {
    showToast('لا يمكن إضافة مراجعة يومية جديدة لهذا التسجيل المغلق.', 'danger');
    hideModal('dailyReviewModal');
    return;
  }

  var diags = CURRENT_PATIENT.diagnoses;

  for (var idx = 0; idx < diags.length; idx++) {
    var descEl = document.getElementById('modal-diag-desc-' + idx);
    var chgEl  = document.getElementById('modal-diag-chg-' + idx);
    var outEl  = document.getElementById('modal-diag-outcome-' + idx);

    if (!descEl || !descEl.value.trim()) {
      showToast('يرجى كتابة وصف الجرح للتشخيص #' + (idx + 1) + ' (' + diags[idx].name + ').', 'warning');
      if (descEl) descEl.focus();
      return;
    }
    if (!chgEl || !chgEl.value.trim()) {
      showToast('يرجى إدخال التغيرات اليومية للتشخيص #' + (idx + 1) + ' (' + diags[idx].name + ').', 'warning');
      if (chgEl) chgEl.focus();
      return;
    }
    if (!outEl || !outEl.value) {
      showToast('يرجى تحديد نتيجة التقييم للتشخيص #' + (idx + 1) + ' (' + diags[idx].name + ').', 'warning');
      if (outEl) outEl.focus();
      return;
    }
  }

  if (!selectedOverallOutcomeKey) {
    showToast('يرجى تحديد النتيجة العامة للتسجيل (Overall Registration Outcome).', 'danger');
    return;
  }

  var currentUser = WCCMS.api.getCurrentUser();
  var nowStr = WCCMS.api.formatDateTime(new Date().toISOString());

  for (var idx = 0; idx < diags.length; idx++) {
    var d   = diags[idx];
    var desc = document.getElementById('modal-diag-desc-' + idx).value.trim();
    var chg  = document.getElementById('modal-diag-chg-' + idx).value.trim();

    var dChanged = document.getElementById('modal-toggle-dressing-' + idx).checked;
    var newDrVal = dChanged ? document.getElementById('modal-dressing-' + idx).value : null;
    var mChanged = document.getElementById('modal-toggle-medication-' + idx).checked;
    var newMedVal = mChanged ? document.getElementById('modal-medication-' + idx).value : null;

    // Save daily review via WCCMS.api
    var created = WCCMS.api.createDailyReview({
      registration_diagnosis_id: d.id,
      nurse_id: currentUser ? currentUser.id : 'u001',
      wound_description: desc,
      wound_changes: chg,
      dressing_id: newDrVal,
      medication_id: newMedVal,
      follow_up_required: (selectedOverallOutcomeKey === 'IMPROVED_SCHEDULED'),
      status: selectedOverallOutcomeKey
    });

    DAILY_REVIEWS.unshift({
      id:               created.id,
      date:             nowStr,
      nurse:            currentUser ? currentUser.full_name : 'أ. فاطمة الزهراني',
      woundName:        d.name,
      woundType:        d.woundType,
      description:      desc,
      changes:          chg,
      dressing:         dChanged ? (newDrVal || d.dressing) + ' (جديدة)' : d.dressing + ' (مستمرة)',
      medication:       mChanged ? (newMedVal || d.medication) + ' (جديد)' : d.medication + ' (مستمر)',
      diagOutcome:      selectedOverallOutcomeKey,
      overallOutcomeKey: selectedOverallOutcomeKey
    });
  }

  renderDailyReviewTimeline();
  renderWoundTabs();
  hideModal('dailyReviewModal');
  processOverallOutcomeAction(selectedOverallOutcomeKey);
}

function processOverallOutcomeAction(outcomeKey) {
  var nowStr = WCCMS.api.today();
  var currentUser = WCCMS.api.getCurrentUser();

  if (outcomeKey === 'IMPROVED_DISCHARGE') {
    WCCMS.api.dischargeRegistration(CURRENT_PATIENT.registrationId, 'IMPROVED', currentUser ? currentUser.id : 'u001');
    CURRENT_PATIENT.status = 'DISCHARGED'; CURRENT_PATIENT.dischargeDate = nowStr;
    renderRegistrationStatusBadge('DISCHARGED');
    disableReviewButton();
    showToast('تم الترخيص بنجاح وإغلاق تسجيل الجروح للحالة.', 'success');
  } else if (outcomeKey === 'IMPROVED_SCHEDULED') {
    WCCMS.api.dischargeRegistration(CURRENT_PATIENT.registrationId, 'IMPROVED', currentUser ? currentUser.id : 'u001');
    CURRENT_PATIENT.status = 'DISCHARGED'; CURRENT_PATIENT.dischargeDate = nowStr;
    renderRegistrationStatusBadge('DISCHARGED');
    disableReviewButton();
    showToast('تم الترخيص ومراجعة الحالة بنجاح.', 'info');
  } else if (outcomeKey === 'WORSENED_STAY') {
    CURRENT_PATIENT.status = 'ACTIVE';
    renderRegistrationStatusBadge('ACTIVE');
    showToast('تم حفظ المراجعات اليومية لجميع التشخيصات بنجاح. الحالة مستمرة في العناية النشطة.', 'success');
  } else if (outcomeKey === 'DEATH') {
    WCCMS.api.dischargeRegistration(CURRENT_PATIENT.registrationId, 'DEATH', currentUser ? currentUser.id : 'u001');
    CURRENT_PATIENT.status = 'DISCHARGED'; CURRENT_PATIENT.dischargeDate = nowStr;
    renderRegistrationStatusBadge('CLOSED_DEATH');
    disableReviewButton();
    showToast('تم توثيق حالة الوفاة وإغلاق تسجيل العناية بالجروح.', 'danger');
  }
}

function disableReviewButton() {
  var btn = document.getElementById('btn-open-daily-review-modal');
  if (btn) {
    btn.disabled = true;
    btn.className = 'btn btn-secondary btn-lg px-4 py-3 d-inline-flex align-items-center gap-2 fw-bold';
    btn.innerHTML = '<i class="bi bi-lock-fill fs-5"></i><span>التسجيل مغلق</span>';
  }
}

function enableReviewButton() {
  var btn = document.getElementById('btn-open-daily-review-modal');
  if (btn) {
    btn.disabled = false;
    btn.className = 'btn btn-primary btn-lg px-4 py-3 d-inline-flex align-items-center gap-2 fw-bold';
    btn.innerHTML = '<i class="bi bi-plus-circle-fill fs-5"></i><span>+ إضافة مراجعة يومية (Add Daily Review)</span>';
  }
}

// ============================================================================
// DAILY REVIEW TIMELINE (COLLAPSIBLE CARDS)
// ============================================================================
function renderDailyReviewTimeline() {
  var container = document.getElementById('daily-review-timeline-cards');
  var countEl   = document.getElementById('review-history-count');
  if (!container) return;

  var recentReviews = (DAILY_REVIEWS || []).slice(0, 3);

  if (countEl) {
    if (DAILY_REVIEWS.length > 3) {
      countEl.textContent = 'أحدث 3 مراجعات (من إجمالي ' + DAILY_REVIEWS.length + ')';
    } else {
      countEl.textContent = DAILY_REVIEWS.length + ' مراجعة';
    }
  }

  if (!DAILY_REVIEWS || DAILY_REVIEWS.length === 0) {
    container.innerHTML = '<div class="text-center py-4 text-muted bg-light rounded"><i class="bi bi-journal-x fs-2 d-block mb-1 opacity-50"></i>لا توجد مراجعات يومية سابقة بعد لهذه الحالة.</div>';
    return;
  }

  var historyUrl = 'patient-history.html?patientId=' + encodeURIComponent(CURRENT_PATIENT ? (CURRENT_PATIENT.patientId || CURRENT_PATIENT.registrationId) : 'PAT-001');

  var html = recentReviews.map(function (r, idx) {
    var cardId = 'review-card-collapse-' + idx;
    var badgeClass = 'badge-light-primary';
    if (r.overallOutcomeKey === 'IMPROVED_DISCHARGE') badgeClass = 'badge-light-success';
    else if (r.overallOutcomeKey === 'IMPROVED_SCHEDULED') badgeClass = 'badge-light-info';
    else if (r.overallOutcomeKey === 'WORSENED_STAY') badgeClass = 'badge-light-warning';
    else if (r.overallOutcomeKey === 'DEATH') badgeClass = 'badge-light-danger';

    return '<div class="card border rounded m8-review-card shadow-sm">' +
      '<div class="card-header p-3 cursor-pointer d-flex align-items-center justify-content-between flex-wrap gap-2"' +
        ' data-bs-toggle="collapse" data-bs-target="#' + cardId + '" aria-expanded="' + (idx === 0 ? 'true' : 'false') + '">' +
        '<div class="d-flex align-items-center gap-3 flex-wrap">' +
          '<span class="badge badge-light-primary fs-7 px-3 py-1 fw-bold text-ltr"><i class="bi bi-clock me-1"></i>' + (r.date || '—') + '</span>' +
          '<span class="badge badge-primary px-3 py-1 fs-7"><i class="bi bi-heart-pulse me-1"></i>' + (r.woundName || '—') + '</span>' +
          '<span class="badge ' + badgeClass + ' px-3 py-1 fs-7">' + (r.overallOutcome || '—') + '</span>' +
          '<span class="text-muted fs-7"><i class="bi bi-person-fill me-1"></i>' + (r.nurse || '—') + '</span>' +
        '</div>' +
        '<div class="d-flex align-items-center gap-2">' +
          '<span class="text-muted fs-8">تفاصيل المراجعة</span>' +
          '<i class="bi bi-chevron-down text-primary fs-6 transition-icon"></i>' +
        '</div>' +
      '</div>' +
      '<div id="' + cardId + '" class="collapse ' + (idx === 0 ? 'show' : '') + '">' +
        '<div class="card-body p-4 bg-light-subtle border-top">' +
          '<div class="row g-3">' +
            '<div class="col-12 col-md-6"><div class="m8-readonly-field"><span class="field-label">وصف الجرح</span><span class="field-value fw-semibold">' + (r.description || '—') + '</span></div></div>' +
            '<div class="col-12 col-md-6"><div class="m8-readonly-field"><span class="field-label">التغيرات اليومية</span><span class="field-value fw-semibold">' + (r.changes || '—') + '</span></div></div>' +
            '<div class="col-6 col-md-4"><div class="m8-readonly-field"><span class="field-label">الضمادة</span><span class="field-value text-success fw-bold">' + (r.dressing || '—') + '</span></div></div>' +
            '<div class="col-6 col-md-4"><div class="m8-readonly-field"><span class="field-label">الدواء</span><span class="field-value text-info fw-bold">' + (r.medication || '—') + '</span></div></div>' +
            '<div class="col-12 col-md-4"><div class="m8-readonly-field"><span class="field-label">تقييم التشخيص</span><span class="field-value text-primary fw-bold">' + (r.diagOutcome || 'مكتمل') + '</span></div></div>' +
          '</div>' +
        '</div>' +
      '</div>' +
    '</div>';
  }).join('');

  // Callout banner to view full history on Page 6
  html += '<div class="p-3 bg-light rounded text-center border mt-2 d-flex flex-wrap align-items-center justify-content-between gap-2">' +
    '<span class="text-muted fs-7"><i class="bi bi-info-circle me-1 text-primary"></i>يتم عرض أحدث المراجعات فقط للسياق السريري. التوثيق السريري والتاريخ الكامل متاح في سجل المرضى.</span>' +
    '<a href="' + historyUrl + '" class="btn btn-sm btn-primary fw-bold d-inline-flex align-items-center gap-1"><i class="bi bi-clock-history"></i><span>عرض السجل الكامل</span></a>' +
  '</div>';

  container.innerHTML = html;
}



// ============================================================================
// HOSPITAL AUTO-DISCHARGE SIMULATION
// ============================================================================
function toggleHospitalAutoDischarge() {
  isHospitalDischarged = !isHospitalDischarged;
  var banner = document.getElementById('hospital-auto-discharge-banner');
  if (isHospitalDischarged) {
    CURRENT_PATIENT.status = 'HOSPITAL_AUTO';
    CURRENT_PATIENT.dischargeDate = formatDateFull(new Date());
    if (banner) banner.classList.remove('d-none');
    renderRegistrationStatusBadge('HOSPITAL_AUTO');
    disableReviewButton();
    showToast('Patient discharged from Hospital System. Wound Care Registration auto-closed.', 'danger');
  } else {
    CURRENT_PATIENT.status = 'ACTIVE';
    CURRENT_PATIENT.dischargeDate = null;
    if (banner) banner.classList.add('d-none');
    renderRegistrationStatusBadge('ACTIVE');
    enableReviewButton();
    showToast('تم إلغاء محاكاة الخروج التلقائي. إعادة الحالة لنشطة.', 'info');
  }
}

// ============================================================================
// UTILITIES
// ============================================================================
function setEl(id, val) { var el = document.getElementById(id); if (el) el.textContent = val; }

function formatDateFull(d) {
  return d.getFullYear() + '/' + String(d.getMonth()+1).padStart(2,'0') + '/' + String(d.getDate()).padStart(2,'0') + ' ' +
    String(d.getHours()).padStart(2,'0') + ':' + String(d.getMinutes()).padStart(2,'0');
}

function showModal(id) {
  var el = document.getElementById(id);
  if (!el) return;
  var m = bootstrap.Modal.getInstance(el) || new bootstrap.Modal(el);
  m.show();
}

function hideModal(id) {
  var el = document.getElementById(id);
  if (!el) return;
  var m = bootstrap.Modal.getInstance(el);
  if (m) m.hide();
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
