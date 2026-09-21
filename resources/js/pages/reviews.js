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
/**
 * ============================================================================
 * WCCMS — Page 5: Scheduled Reviews Logic
 * Metronic 8 RTL — Bootstrap 5 — Vanilla JavaScript
 * ============================================================================
 */

'use strict';

(function () {

  // Global state
  var reviewsList = [];
  var filteredReviews = [];
  var currentSort = { column: 'scheduledDate', dir: 'asc' };
  var currentPage = 1;
  var pageSize = 10;

  var currentTabFilter = 'ALL'; // ALL, TODAY, UPCOMING, OVERDUE, COMPLETED
  var activeReviewId = null;

  // DOM Elements
  var tbodyEl, emptyEl, noResultsEl, pageInfoEl, paginationEl;
  var statTodayEl, statUpcomingEl, statOverdueEl, statCompletedEl;

  // Initialize page on DOM content loaded
  document.addEventListener('DOMContentLoaded', function () {
    initDOMElements();
    initTheme();
    initHeaderDate();
    initSidebarMobile();
    loadReviewsData();
    initEventListeners();
    applyFilters();
  });

  /* ============================================================================
     1. DOM INITIALIZATION
     ============================================================================ */
  function initDOMElements() {
    tbodyEl       = document.getElementById('sch-tbody');
    emptyEl       = document.getElementById('sch-empty');
    noResultsEl   = document.getElementById('sch-no-results');
    pageInfoEl    = document.getElementById('pagination-info');
    paginationEl  = document.getElementById('pagination-container');

    statTodayEl     = document.getElementById('stat-today-count');
    statUpcomingEl  = document.getElementById('stat-upcoming-count');
    statOverdueEl   = document.getElementById('stat-overdue-count');
    statCompletedEl = document.getElementById('stat-completed-count');
  }

  /* ============================================================================
     2. THEME & HEADER HELPERS
     ============================================================================ */
  function initTheme() {
    var themeToggleBtn = document.getElementById('m8-theme-toggle');
    var themeToggleIcon = document.getElementById('m8-theme-toggle-icon');
    var themeToggleText = document.getElementById('m8-theme-toggle-text');

    var savedTheme = localStorage.getItem('m8_theme') || 'light';
    setTheme(savedTheme);

    if (themeToggleBtn) {
      themeToggleBtn.addEventListener('click', function () {
        var current = document.documentElement.getAttribute('data-bs-theme');
        var next = (current === 'dark') ? 'light' : 'dark';
        setTheme(next);
      });
    }

    function setTheme(theme) {
      document.documentElement.setAttribute('data-bs-theme', theme);
      localStorage.setItem('m8_theme', theme);
      if (themeToggleIcon && themeToggleText) {
        if (theme === 'dark') {
          themeToggleIcon.className = 'bi bi-sun-fill';
          themeToggleText.textContent = 'الوضع النهار';
        } else {
          themeToggleIcon.className = 'bi bi-moon-stars-fill';
          themeToggleText.textContent = 'الوضع الليلي';
        }
      }
    }
  }

  function initHeaderDate() {
    var dateEl = document.getElementById('header-date-display');
    if (dateEl) {
      var now = new Date();
      var options = { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' };
      dateEl.textContent = now.toLocaleDateString('ar-SA', options);
    }
  }

  function initSidebarMobile() {
    var mobileToggleBtn = document.getElementById('m8-mobile-toggle');
    var sidebar = document.getElementById('m8-sidebar');
    if (mobileToggleBtn && sidebar) {
      mobileToggleBtn.addEventListener('click', function () {
        sidebar.classList.toggle('show');
      });
    }
  }

  /* ============================================================================
     3. LOAD DATA & STAT CARDS
     ============================================================================ */
  function loadReviewsData() {
    if (window.WCCMS && window.WCCMS.api) {
      var list = window.WCCMS.api.getEnrichedScheduledReviews();
      var todayStr = WCCMS.api.today();

      reviewsList = list.map(function (s) {
        var computedStatus = s.status;
        if (s.status === 'SCHEDULED') {
          if (s.scheduled_date === todayStr) computedStatus = 'TODAY';
          else if (s.scheduled_date < todayStr) computedStatus = 'OVERDUE';
          else computedStatus = 'SCHEDULED';
        }

        return {
          id: s.id,
          registrationId: s.registration_id,
          patientId: s.patient_id,
          patientName: s.patient_name || '',
          nationalId: s.national_id || '',
          hospitalPatientId: s.hospital_patient_id || '',
          age: s.age || 0,
          gender: s.gender_ar || s.gender || '',
          supervisingDoctor: s.supervising_doctor || '',
          department: s.department || '',
          departmentKey: s.department_id || '',
          diagnosisId: s.registration_diagnosis_id,
          diagnosisName: s.diagnosis_name || '',
          woundType: s.wound_type || '',
          currentDressing: s.dressing || 'رغوية (Foam)',
          currentMedication: s.medication || 'مرهم مضاد حيوي',
          scheduledDate: s.scheduled_date,
          createdDate: WCCMS.api.formatDate(s.created_at),
          createdBy: s.created_by || 'أ. فاطمة الزهراني',
          reasonForFollowup: s.notes || 'متابعة مجدولة تقييمية للجرح',
          lastReviewDate: s.latest_daily_review_date || '2026-09-01',
          previousWoundDescription: s.latest_daily_review_desc || 'تقييم الجرح السابق',
          previousWoundChanges: s.latest_daily_review_changes || 'استجابة جيدة للضمادة',
          previousDressing: s.dressing,
          previousMedication: s.medication,
          previousOutcome: 'تحسن مع الحاجة لمتابعة مجدولة',
          status: computedStatus,
          completionDate: WCCMS.api.formatDate(s.completed_at),
          completedBy: s.created_by
        };
      });
    } else {
      reviewsList = [];
    }
    updateStatCards();
  }

  function saveReviewsData() {
    updateStatCards();
  }

  function updateStatCards() {
    var todayCount = 0;
    var upcomingCount = 0;
    var overdueCount = 0;
    var completedCount = 0;

    var todayStr = '2026-09-05';

    reviewsList.forEach(function (r) {
      if (r.status === 'COMPLETED') {
        completedCount++;
      } else if (r.status === 'CANCELLED') {
        // Not counted in active stats
      } else if (r.status === 'TODAY' || r.scheduledDate === todayStr) {
        todayCount++;
      } else if (r.status === 'OVERDUE' || (r.scheduledDate < todayStr)) {
        overdueCount++;
      } else if (r.status === 'SCHEDULED' || (r.scheduledDate > todayStr)) {
        upcomingCount++;
      }
    });

    if (statTodayEl)     statTodayEl.textContent     = todayCount;
    if (statUpcomingEl)  statUpcomingEl.textContent  = upcomingCount;
    if (statOverdueEl)   statOverdueEl.textContent   = overdueCount;
    if (statCompletedEl) statCompletedEl.textContent = completedCount;

    // Update counts on quick tabs
    var countTabAll       = document.getElementById('tab-count-all');
    var countTabToday     = document.getElementById('tab-count-today');
    var countTabUpcoming  = document.getElementById('tab-count-upcoming');
    var countTabOverdue   = document.getElementById('tab-count-overdue');
    var countTabCompleted = document.getElementById('tab-count-completed');

    if (countTabAll)       countTabAll.textContent       = reviewsList.length;
    if (countTabToday)     countTabToday.textContent     = todayCount;
    if (countTabUpcoming)  countTabUpcoming.textContent  = upcomingCount;
    if (countTabOverdue)   countTabOverdue.textContent   = overdueCount;
    if (countTabCompleted) countTabCompleted.textContent = completedCount;
  }

  /* ============================================================================
     4. EVENT LISTENERS & FILTERS
     ============================================================================ */
  function initEventListeners() {
    // Search input
    var searchInput = document.getElementById('sch-search');
    if (searchInput) {
      searchInput.addEventListener('input', function () {
        currentPage = 1;
        applyFilters();
      });
    }

    // Date range dropdown
    var filterDate = document.getElementById('filter-date-range');
    var customWrapperFrom = document.getElementById('custom-date-from-wrapper');
    var customWrapperTo   = document.getElementById('custom-date-to-wrapper');
    if (filterDate) {
      filterDate.addEventListener('change', function () {
        if (filterDate.value === 'custom') {
          if (customWrapperFrom) customWrapperFrom.style.display = 'block';
          if (customWrapperTo)   customWrapperTo.style.display   = 'block';
        } else {
          if (customWrapperFrom) customWrapperFrom.style.display = 'none';
          if (customWrapperTo)   customWrapperTo.style.display   = 'none';
        }
        currentPage = 1;
        applyFilters();
      });
    }

    var filterFrom = document.getElementById('filter-date-from');
    var filterTo   = document.getElementById('filter-date-to');
    if (filterFrom) filterFrom.addEventListener('change', function() { currentPage = 1; applyFilters(); });
    if (filterTo)   filterTo.addEventListener('change', function() { currentPage = 1; applyFilters(); });

    // Department dropdown
    var filterDept = document.getElementById('filter-dept');
    if (filterDept) {
      filterDept.addEventListener('change', function () {
        currentPage = 1;
        applyFilters();
      });
    }

    // Status dropdown
    var filterStatus = document.getElementById('filter-status');
    if (filterStatus) {
      filterStatus.addEventListener('change', function () {
        currentPage = 1;
        syncQuickTabFromStatus(filterStatus.value);
        applyFilters();
      });
    }

    // Page size dropdown
    var filterPageSize = document.getElementById('filter-page-size');
    if (filterPageSize) {
      filterPageSize.addEventListener('change', function () {
        pageSize = parseInt(filterPageSize.value, 10) || 10;
        currentPage = 1;
        renderTable();
      });
    }

    // Sortable table headers
    document.querySelectorAll('.sch-sortable').forEach(function (th) {
      th.addEventListener('click', function () {
        var sortCol = th.getAttribute('data-sort');
        if (currentSort.column === sortCol) {
          currentSort.dir = (currentSort.dir === 'asc') ? 'desc' : 'asc';
        } else {
          currentSort.column = sortCol;
          currentSort.dir = 'asc';
        }
        updateSortIcons();
        applyFilters();
      });
    });

    // Quick filter tabs
    document.querySelectorAll('.m8-quick-tab').forEach(function (tab) {
      tab.addEventListener('click', function () {
        document.querySelectorAll('.m8-quick-tab').forEach(function (t) { t.classList.remove('active'); });
        tab.classList.add('active');
        currentTabFilter = tab.getAttribute('data-tab');

        // Sync status dropdown if applicable
        var filterStatus = document.getElementById('filter-status');
        if (filterStatus) {
          if (currentTabFilter === 'ALL') filterStatus.value = '';
          else if (currentTabFilter === 'TODAY') filterStatus.value = 'TODAY';
          else if (currentTabFilter === 'UPCOMING') filterStatus.value = 'SCHEDULED';
          else if (currentTabFilter === 'OVERDUE') filterStatus.value = 'OVERDUE';
          else if (currentTabFilter === 'COMPLETED') filterStatus.value = 'COMPLETED';
        }

        currentPage = 1;
        applyFilters();
      });
    });

    // Stat cards click -> triggers tab filter
    var cardToday     = document.getElementById('card-stat-today');
    var cardUpcoming  = document.getElementById('card-stat-upcoming');
    var cardOverdue   = document.getElementById('card-stat-overdue');
    var cardCompleted = document.getElementById('card-stat-completed');

    if (cardToday)     cardToday.addEventListener('click', function() { triggerQuickTab('TODAY'); });
    if (cardUpcoming)  cardUpcoming.addEventListener('click', function() { triggerQuickTab('UPCOMING'); });
    if (cardOverdue)   cardOverdue.addEventListener('click', function() { triggerQuickTab('OVERDUE'); });
    if (cardCompleted) cardCompleted.addEventListener('click', function() { triggerQuickTab('COMPLETED'); });

    // Review Modal Dressing Radio Toggle
    var dressingRadios = document.querySelectorAll('input[name="dressingChangeRadio"]');
    dressingRadios.forEach(function (r) {
      r.addEventListener('change', function () {
        var wrapper = document.getElementById('new-dressing-wrapper');
        if (wrapper) {
          wrapper.style.display = (r.value === 'YES') ? 'block' : 'none';
        }
      });
    });

    // Review Modal Medication Radio Toggle
    var medRadios = document.querySelectorAll('input[name="medicationChangeRadio"]');
    medRadios.forEach(function (r) {
      r.addEventListener('change', function () {
        var wrapper = document.getElementById('new-medication-wrapper');
        if (wrapper) {
          wrapper.style.display = (r.value === 'YES') ? 'block' : 'none';
        }
      });
    });

    // Review Modal Outcome Cards Selection
    var outcomeCards = document.querySelectorAll('.m8-outcome-card');
    outcomeCards.forEach(function (card) {
      card.addEventListener('click', function () {
        outcomeCards.forEach(function (c) { c.classList.remove('selected'); });
        card.classList.add('selected');
        var radio = card.querySelector('.m8-outcome-radio');
        if (radio) radio.checked = true;
        handleOutcomeChange(radio.value);
      });
    });
  }

  function triggerQuickTab(tabKey) {
    var targetTab = document.querySelector('.m8-quick-tab[data-tab="' + tabKey + '"]');
    if (targetTab) {
      targetTab.click();
    }
  }

  function syncQuickTabFromStatus(statusVal) {
    var tabKey = 'ALL';
    if (statusVal === 'TODAY') tabKey = 'TODAY';
    else if (statusVal === 'SCHEDULED') tabKey = 'UPCOMING';
    else if (statusVal === 'OVERDUE') tabKey = 'OVERDUE';
    else if (statusVal === 'COMPLETED') tabKey = 'COMPLETED';

    document.querySelectorAll('.m8-quick-tab').forEach(function (t) {
      if (t.getAttribute('data-tab') === tabKey) {
        t.classList.add('active');
      } else {
        t.classList.remove('active');
      }
    });
    currentTabFilter = tabKey;
  }

  function updateSortIcons() {
    document.querySelectorAll('.sch-sortable').forEach(function (th) {
      th.classList.remove('sort-asc', 'sort-desc');
      var col = th.getAttribute('data-sort');
      if (col === currentSort.column) {
        th.classList.add(currentSort.dir === 'asc' ? 'sort-asc' : 'sort-desc');
      }
    });
  }

  /* ============================================================================
     5. FILTERING & SORTING LOGIC
     ============================================================================ */
  function applyFilters() {
    var searchVal  = (document.getElementById('sch-search') ? document.getElementById('sch-search').value.trim().toLowerCase() : '');
    var dateVal    = (document.getElementById('filter-date-range') ? document.getElementById('filter-date-range').value : '');
    var deptVal    = (document.getElementById('filter-dept') ? document.getElementById('filter-dept').value : '');
    var statusVal  = (document.getElementById('filter-status') ? document.getElementById('filter-status').value : '');

    var todayStr = '2026-09-05';

    filteredReviews = reviewsList.filter(function (item) {

      // 1. Text Search (Patient Name, National ID, Hospital ID)
      if (searchVal) {
        var matchName = (item.patientName || '').toLowerCase().includes(searchVal);
        var matchNid  = (item.nationalId || '').toLowerCase().includes(searchVal);
        var matchHid  = (item.hospitalPatientId || '').toLowerCase().includes(searchVal);
        if (!matchName && !matchNid && !matchHid) return false;
      }

      // 2. Quick Tab Filter
      if (currentTabFilter === 'TODAY') {
        if (item.status === 'COMPLETED' || item.status === 'CANCELLED') return false;
        if (item.status !== 'TODAY' && item.scheduledDate !== todayStr) return false;
      } else if (currentTabFilter === 'UPCOMING') {
        if (item.status === 'COMPLETED' || item.status === 'CANCELLED') return false;
        if (item.scheduledDate <= todayStr && item.status !== 'SCHEDULED') return false;
      } else if (currentTabFilter === 'OVERDUE') {
        if (item.status === 'COMPLETED' || item.status === 'CANCELLED') return false;
        if (item.status !== 'OVERDUE' && item.scheduledDate >= todayStr) return false;
      } else if (currentTabFilter === 'COMPLETED') {
        if (item.status !== 'COMPLETED') return false;
      }

      // 3. Status Dropdown Filter
      if (statusVal) {
        if (statusVal === 'TODAY') {
          if (item.status !== 'TODAY' && item.scheduledDate !== todayStr) return false;
        } else if (statusVal === 'SCHEDULED') {
          if (item.status !== 'SCHEDULED' && !(item.scheduledDate > todayStr && item.status !== 'COMPLETED' && item.status !== 'CANCELLED')) return false;
        } else if (statusVal === 'OVERDUE') {
          if (item.status !== 'OVERDUE' && !(item.scheduledDate < todayStr && item.status !== 'COMPLETED' && item.status !== 'CANCELLED')) return false;
        } else if (statusVal === 'COMPLETED') {
          if (item.status !== 'COMPLETED') return false;
        } else if (statusVal === 'CANCELLED') {
          if (item.status !== 'CANCELLED') return false;
        }
      }

      // 4. Department Filter
      if (deptVal && item.departmentKey !== deptVal) {
        return false;
      }

      // 5. Date Range Filter
      if (dateVal === 'today') {
        if (item.scheduledDate !== todayStr) return false;
      } else if (dateVal === 'tomorrow') {
        if (item.scheduledDate !== '2026-09-06') return false;
      } else if (dateVal === 'this_week') {
        if (item.scheduledDate < '2026-09-01' || item.scheduledDate > '2026-09-10') return false;
      } else if (dateVal === 'this_month') {
        if (!item.scheduledDate.startsWith('2026-09')) return false;
      } else if (dateVal === 'custom') {
        var fromVal = document.getElementById('filter-date-from') ? document.getElementById('filter-date-from').value : '';
        var toVal   = document.getElementById('filter-date-to')   ? document.getElementById('filter-date-to').value   : '';
        if (fromVal && item.scheduledDate < fromVal) return false;
        if (toVal && item.scheduledDate > toVal) return false;
      }

      return true;
    });

    // Sort filtered list
    filteredReviews.sort(function (a, b) {
      var valA = a[currentSort.column] || '';
      var valB = b[currentSort.column] || '';
      if (typeof valA === 'string') valA = valA.toLowerCase();
      if (typeof valB === 'string') valB = valB.toLowerCase();

      if (valA < valB) return currentSort.dir === 'asc' ? -1 : 1;
      if (valA > valB) return currentSort.dir === 'asc' ? 1 : -1;
      return 0;
    });

    renderTable();
  }

  window.resetFilters = function () {
    var searchInput = document.getElementById('sch-search');
    var filterDate   = document.getElementById('filter-date-range');
    var filterDept   = document.getElementById('filter-dept');
    var filterStatus = document.getElementById('filter-status');
    var customFrom   = document.getElementById('custom-date-from-wrapper');
    var customTo     = document.getElementById('custom-date-to-wrapper');

    if (searchInput) searchInput.value = '';
    if (filterDate)   filterDate.value   = '';
    if (filterDept)   filterDept.value   = '';
    if (filterStatus) filterStatus.value = '';
    if (customFrom)   customFrom.style.display = 'none';
    if (customTo)     customTo.style.display   = 'none';

    document.querySelectorAll('.m8-quick-tab').forEach(function (t) { t.classList.remove('active'); });
    var allTab = document.querySelector('.m8-quick-tab[data-tab="ALL"]');
    if (allTab) allTab.classList.add('active');
    currentTabFilter = 'ALL';

    currentPage = 1;
    applyFilters();
  };

  /* ============================================================================
     6. TABLE RENDERING & PAGINATION
     ============================================================================ */
  function renderTable() {
    var totalCount = filteredReviews.length;
    var countBadge = document.getElementById('table-count-badge');
    if (countBadge) {
      countBadge.textContent = totalCount + ' مراجعة';
    }

    if (totalCount === 0) {
      tbodyEl.innerHTML = '';
      if (reviewsList.length === 0) {
        if (emptyEl) emptyEl.classList.remove('d-none');
        if (noResultsEl) noResultsEl.classList.add('d-none');
      } else {
        if (emptyEl) emptyEl.classList.add('d-none');
        if (noResultsEl) noResultsEl.classList.remove('d-none');
      }
      renderPagination(0, 0, 0);
      return;
    }

    if (emptyEl)     emptyEl.classList.add('d-none');
    if (noResultsEl) noResultsEl.classList.add('d-none');

    // Calculate pagination slices
    var totalPages = Math.ceil(totalCount / pageSize);
    if (currentPage > totalPages) currentPage = totalPages;
    if (currentPage < 1) currentPage = 1;

    var startIdx = (currentPage - 1) * pageSize;
    var endIdx = Math.min(startIdx + pageSize, totalCount);
    var pageSlice = filteredReviews.slice(startIdx, endIdx);

    var html = '';
    var todayStr = '2026-09-05';

    pageSlice.forEach(function (item) {
      var statusBadgeHtml = '';

      // Determine computed status
      var statusKey = item.status;
      if (statusKey !== 'COMPLETED' && statusKey !== 'CANCELLED') {
        if (item.scheduledDate === todayStr) statusKey = 'TODAY';
        else if (item.scheduledDate < todayStr) statusKey = 'OVERDUE';
        else statusKey = 'SCHEDULED';
      }

      if (statusKey === 'TODAY') {
        statusBadgeHtml = '<span class="m8-status-badge badge-today"><i class="bi bi-clock-history"></i>اليوم</span>';
      } else if (statusKey === 'SCHEDULED') {
        statusBadgeHtml = '<span class="m8-status-badge badge-scheduled"><i class="bi bi-calendar-event"></i>مجدولة</span>';
      } else if (statusKey === 'OVERDUE') {
        statusBadgeHtml = '<span class="m8-status-badge badge-overdue"><i class="bi bi-exclamation-triangle-fill"></i>متأخرة</span>';
      } else if (statusKey === 'COMPLETED') {
        statusBadgeHtml = '<span class="m8-status-badge badge-completed"><i class="bi bi-check-circle-fill"></i>مكتملة</span>';
      } else if (statusKey === 'CANCELLED') {
        statusBadgeHtml = '<span class="m8-status-badge badge-cancelled"><i class="bi bi-x-circle-fill"></i>ملغاة</span>';
      }

      var actionBtnHtml = '';
      if (statusKey === 'COMPLETED' || statusKey === 'CANCELLED') {
        actionBtnHtml = '<button class="btn-view-details" onclick="openViewDetailsModal(\'' + item.id + '\')"><i class="bi bi-eye-fill"></i><span>عرض التفاصيل</span></button>';
      } else {
        actionBtnHtml = '<button class="btn-open-review" onclick="openReviewModal(\'' + item.id + '\')"><i class="bi bi-folder-symlink-fill"></i><span>فتح المراجعة</span></button>';
      }

      var formattedDate = formatDateDisplay(item.scheduledDate);
      var formattedLast = formatDateDisplay(item.lastReviewDate);
      var historyUrl = 'patient-history.html?patientId=' + encodeURIComponent(item.patientId || item.registrationId);

      html += '<tr>' +
        '  <td>' +
        '    <div class="d-flex align-items-center justify-content-between">' +
        '      <div>' +
        '        <a href="' + historyUrl + '" class="sch-patient-name text-primary text-decoration-none fw-bold me-2" title="عرض السجل التاريخي الكامل للمريض">' + escapeHtml(item.patientName) + '</a>' +
        '        <span class="sch-national-id">رقم الهوية: ' + escapeHtml(item.nationalId) + '</span>' +
        '      </div>' +
        '      <a href="' + historyUrl + '" class="btn btn-sm btn-icon btn-light-info flex-shrink-0" title="عرض السجل المرضي بالصفحة 6">' +
        '        <i class="bi bi-clock-history"></i>' +
        '      </a>' +
        '    </div>' +
        '  </td>' +
        '  <td><span class="badge badge-light-primary px-2 py-1 fs-8 fw-semibold">' + escapeHtml(item.department) + '</span></td>' +
        '  <td><span class="fw-bold text-dark fs-7">' + escapeHtml(item.diagnosisName) + '</span></td>' +
        '  <td><span class="text-muted fs-8 fw-semibold">' + escapeHtml(item.woundType) + '</span></td>' +
        '  <td><span class="sch-date-text text-primary fw-bold">' + formattedDate + '</span></td>' +
        '  <td><span class="sch-date-text text-muted">' + formattedLast + '</span></td>' +
        '  <td>' + statusBadgeHtml + '</td>' +
        '  <td class="text-center">' + actionBtnHtml + '</td>' +
        '</tr>';
    });

    tbodyEl.innerHTML = html;
    renderPagination(startIdx + 1, endIdx, totalCount);
  }

  function renderPagination(from, to, total) {
    if (pageInfoEl) {
      pageInfoEl.textContent = 'عرض ' + from + '–' + to + ' من ' + total + ' مراجعة';
    }

    if (!paginationEl) return;
    if (total === 0) {
      paginationEl.innerHTML = '';
      return;
    }

    var totalPages = Math.ceil(total / pageSize);
    var html = '';

    // Previous page
    var prevDisabled = (currentPage === 1) ? ' disabled' : '';
    html += '<li class="page-item' + prevDisabled + '"><a class="page-link" href="#" onclick="changePage(' + (currentPage - 1) + '); return false;">السابق</a></li>';

    for (var i = 1; i <= totalPages; i++) {
      var activeCls = (i === currentPage) ? ' active' : '';
      html += '<li class="page-item' + activeCls + '"><a class="page-link" href="#" onclick="changePage(' + i + '); return false;">' + i + '</a></li>';
    }

    // Next page
    var nextDisabled = (currentPage === totalPages) ? ' disabled' : '';
    html += '<li class="page-item' + nextDisabled + '"><a class="page-link" href="#" onclick="changePage(' + (currentPage + 1) + '); return false;">التالي</a></li>';

    paginationEl.innerHTML = html;
  }

  window.changePage = function (p) {
    var totalPages = Math.ceil(filteredReviews.length / pageSize);
    if (p >= 1 && p <= totalPages) {
      currentPage = p;
      renderTable();
    }
  };

  /* ============================================================================
     7. OPEN SCHEDULED REVIEW MODAL
     ============================================================================ */
  window.openReviewModal = function (id) {
    activeReviewId = id;
    var review = reviewsList.find(function (r) { return r.id === id; });
    if (!review) return;

    // 1. Patient Summary Header
    setElText('rev-patient-name', review.patientName);
    setElText('rev-patient-nid', review.nationalId);
    setElText('rev-patient-hid', review.hospitalPatientId);
    setElText('rev-patient-age-gender', review.age + ' سنة / ' + review.gender);
    setElText('rev-patient-doctor', review.supervisingDoctor);
    setElText('rev-patient-dept', review.department);

    var historyBtn = document.getElementById('modal-rev-history-btn');
    if (historyBtn) {
      historyBtn.href = 'patient-history.html?patientId=' + encodeURIComponent(review.patientId || review.registrationId);
    }

    // 2. Appointment Info
    setElText('rev-sch-date', formatDateDisplay(review.scheduledDate));
    setElText('rev-created-date', formatDateDisplay(review.createdDate));
    setElText('rev-reason', review.reasonForFollowup);

    // Overdue Alert Banner check
    var overdueBanner = document.getElementById('rev-overdue-banner');
    var todayStr = '2026-09-05';
    if (review.scheduledDate < todayStr && review.status !== 'COMPLETED') {
      if (overdueBanner) {
        overdueBanner.style.display = 'flex';
        setElText('rev-original-date-text', formatDateDisplay(review.scheduledDate));
      }
    } else {
      if (overdueBanner) overdueBanner.style.display = 'none';
    }

    // 3. Diagnosis Target Banner
    setElText('rev-diag-title', review.diagnosisName);
    setElText('rev-diag-type', 'نوع الجرح: ' + review.woundType);

    // 4. Previous Clinical History (Read-Only)
    setElText('rev-prev-diag', review.diagnosisName);
    setElText('rev-prev-wound-type', review.woundType);
    setElText('rev-prev-dressing', review.currentDressing || review.previousDressing || 'رغوية (Foam)');
    setElText('rev-prev-med', review.currentMedication || review.previousMedication || 'مرهم مضاد حيوي');

    setElText('rev-prev-date', formatDateDisplay(review.lastReviewDate));
    setElText('rev-prev-desc', review.previousWoundDescription || 'لا يوجد وصف سابق تسجيلي');
    setElText('rev-prev-changes', review.previousWoundChanges || 'لا توجد تغيرات مسبقة');
    setElText('rev-prev-outcome', review.previousOutcome || 'تحسن مع الحاجة لمتابعة مجدولة');

    // 5. Current Review Inputs Reset
    var woundDesc = document.getElementById('rev-input-desc');
    var woundChanges = document.getElementById('rev-input-changes');
    if (woundDesc) woundDesc.value = '';
    if (woundChanges) woundChanges.value = '';

    // Dressing Radio reset (default NO)
    var dNoRadio = document.getElementById('dressing-change-no');
    if (dNoRadio) dNoRadio.checked = true;
    var dWrapper = document.getElementById('new-dressing-wrapper');
    if (dWrapper) dWrapper.style.display = 'none';

    setElText('rev-current-dressing-name', review.currentDressing || 'رغوية (Foam)');

    // Medication Radio reset (default NO)
    var mNoRadio = document.getElementById('medication-change-no');
    if (mNoRadio) mNoRadio.checked = true;
    var mWrapper = document.getElementById('new-medication-wrapper');
    if (mWrapper) mWrapper.style.display = 'none';

    setElText('rev-current-med-name', review.currentMedication || 'مرهم مضاد حيوي');

    // Outcome Card Reset (default 1: IMPROVED_FINISHED)
    document.querySelectorAll('.m8-outcome-card').forEach(function (c) { c.classList.remove('selected'); });
    var firstCard = document.getElementById('card-outcome-1');
    if (firstCard) firstCard.classList.add('selected');
    var firstRadio = document.getElementById('outcome-radio-1');
    if (firstRadio) firstRadio.checked = true;

    handleOutcomeChange('IMPROVED_FINISHED');

    // Show Modal
    var modalEl = document.getElementById('scheduled-review-modal');
    if (modalEl && window.bootstrap) {
      var modal = bootstrap.Modal.getOrCreateInstance(modalEl);
      modal.show();
    }
  };

  function handleOutcomeChange(outcomeKey) {
    var nextApptBox    = document.getElementById('next-appointment-box');
    var doctorNotice   = document.getElementById('doctor-transfer-notice');
    var saveBtnText    = document.getElementById('rev-save-btn-text');
    var nextDateInput  = document.getElementById('rev-next-date');

    if (outcomeKey === 'IMPROVED_FINISHED') {
      if (nextApptBox)  nextApptBox.style.display  = 'none';
      if (doctorNotice) doctorNotice.style.display = 'none';
      if (saveBtnText)  saveBtnText.textContent    = 'حفظ المراجعة وإغلاق الحالة';
    } else if (outcomeKey === 'IMPROVED_ANOTHER_VISIT') {
      if (nextApptBox)  nextApptBox.style.display  = 'block';
      if (doctorNotice) doctorNotice.style.display = 'none';
      if (saveBtnText)  saveBtnText.textContent    = 'حفظ وجدولة الزيارة القادمة';

      // Set default next date (+7 days)
      if (nextDateInput && !nextDateInput.value) {
        var d = new Date();
        d.setDate(d.getDate() + 7);
        var y = d.getFullYear();
        var m = String(d.getMonth() + 1).padStart(2, '0');
        var day = String(d.getDate()).padStart(2, '0');
        nextDateInput.value = y + '-' + m + '-' + day;
      }
    } else if (outcomeKey === 'WORSENED_DOCTOR_TRANSFER') {
      if (nextApptBox)  nextApptBox.style.display  = 'none';
      if (doctorNotice) doctorNotice.style.display = 'block';
      if (saveBtnText)  saveBtnText.textContent    = 'حفظ وتحويل المريض للطبيب';
    }
  }

  /* ============================================================================
     8. SAVE SCHEDULED REVIEW
     ============================================================================ */
  window.saveReviewOutcome = function () {
    var review = reviewsList.find(function (r) { return r.id === activeReviewId; });
    if (!review) return;

    var woundDesc = document.getElementById('rev-input-desc') ? document.getElementById('rev-input-desc').value.trim() : '';
    var woundChanges = document.getElementById('rev-input-changes') ? document.getElementById('rev-input-changes').value.trim() : '';

    if (!woundDesc) {
      showToast('يرجى كتابة وصف الجرح الحالي قبل الحفظ', 'danger');
      if (document.getElementById('rev-input-desc')) document.getElementById('rev-input-desc').focus();
      return;
    }

    if (!woundChanges) {
      showToast('يرجى تدوين التغيرات والتطورات قبل الحفظ', 'danger');
      if (document.getElementById('rev-input-changes')) document.getElementById('rev-input-changes').focus();
      return;
    }

    // Read Dressing update
    var dressingRadio = document.querySelector('input[name="dressingChangeRadio"]:checked');
    var isDressingChanged = (dressingRadio && dressingRadio.value === 'YES');
    var newDressingVal = isDressingChanged ? (document.getElementById('rev-new-dressing') ? document.getElementById('rev-new-dressing').value : '') : '';

    // Read Medication update
    var medRadio = document.querySelector('input[name="medicationChangeRadio"]:checked');
    var isMedChanged = (medRadio && medRadio.value === 'YES');
    var newMedVal = isMedChanged ? (document.getElementById('rev-new-medication') ? document.getElementById('rev-new-medication').value : '') : '';

    // Read Selected Outcome
    var selectedOutcomeRadio = document.querySelector('input[name="reviewOutcomeRadio"]:checked');
    var outcomeKey = selectedOutcomeRadio ? selectedOutcomeRadio.value : 'IMPROVED_FINISHED';

    var nextDateVal = '';
    var nextNotesVal = '';

    if (outcomeKey === 'IMPROVED_ANOTHER_VISIT') {
      nextDateVal = document.getElementById('rev-next-date') ? document.getElementById('rev-next-date').value : '';
      nextNotesVal = document.getElementById('rev-next-notes') ? document.getElementById('rev-next-notes').value.trim() : '';

      if (!nextDateVal) {
        showToast('يرجى تحديد تاريخ الزيارة القادمة', 'danger');
        if (document.getElementById('rev-next-date')) document.getElementById('rev-next-date').focus();
        return;
      }
    }

    // Call WCCMS.api to complete
    WCCMS.api.completeScheduledReview(review.id);

    // Update current review record
    review.status = 'COMPLETED';
    review.completionDate = WCCMS.api.today();
    review.completedBy = 'أ. فاطمة الزهراني';
    review.currentWoundDescription = woundDesc;
    review.currentWoundChanges = woundChanges;
    review.dressingChanged = isDressingChanged;
    if (isDressingChanged && newDressingVal) review.currentDressing = newDressingVal;
    review.medicationChanged = isMedChanged;
    if (isMedChanged && newMedVal) review.currentMedication = newMedVal;

    review.outcomeKey = outcomeKey;

    if (outcomeKey === 'IMPROVED_FINISHED') {
      review.outcomeText = 'تحسن — لا حاجة لزيارة أخرى';
    } else if (outcomeKey === 'IMPROVED_ANOTHER_VISIT') {
      review.outcomeText = 'تحسن — تم جدولة زيارة جديدة بتاريخ ' + formatDateDisplay(nextDateVal);
      review.nextScheduledDate = nextDateVal;
      review.nextScheduledNotes = nextNotesVal;

      // Create NEW Scheduled Review in WCCMS.api
      if (review.diagnosisId) {
        WCCMS.api.createScheduledReview({
          registration_diagnosis_id: review.diagnosisId,
          scheduled_date: nextDateVal,
          notes: nextNotesVal || 'متابعة مجدولة قادمة'
        });
      }
    } else if (outcomeKey === 'WORSENED_DOCTOR_TRANSFER') {
      review.outcomeText = 'تدهور — تم تحويل المريض للطبيب للتقييم ودخول المستشفى';
    }

    // Refresh data list from API
    loadReviewsData();

    // Close Modal
    var modalEl = document.getElementById('scheduled-review-modal');
    if (modalEl && window.bootstrap) {
      var modal = bootstrap.Modal.getInstance(modalEl);
      if (modal) modal.hide();
    }

    if (outcomeKey === 'IMPROVED_ANOTHER_VISIT') {
      showToast('تم توثيق المراجعة وتحديد الموعد القادم بنجاح!', 'success');
    } else if (outcomeKey === 'WORSENED_DOCTOR_TRANSFER') {
      showToast('تم حفظ المراجعة وإرسال إشعار تحويل للطبيب لتقييم حالة الدخول', 'warning');
    } else {
      showToast('تم توثيق المراجعة وإغلاق الحالة بنجاح!', 'success');
    }

    applyFilters();
  };

  /* ============================================================================
     9. VIEW DETAILS MODAL (READ-ONLY)
     ============================================================================ */
  window.openViewDetailsModal = function (id) {
    var review = reviewsList.find(function (r) { return r.id === id; });
    if (!review) return;

    setElText('view-patient-name', review.patientName);
    setElText('view-patient-nid', review.nationalId);
    setElText('view-patient-hid', review.hospitalPatientId);
    setElText('view-diag-title', review.diagnosisName + ' (' + review.woundType + ')');
    setElText('view-sch-date', formatDateDisplay(review.scheduledDate));
    setElText('view-nurse', review.completedBy || review.createdBy || 'أ. فاطمة الزهراني');

    if (review.status === 'CANCELLED') {
      setElText('view-completion-date', 'ملغى بتاريخ ' + formatDateDisplay(review.cancellationDate));
      setElText('view-outcome-title', 'موعد ملغى');
      setElText('view-desc-text', 'السبب: ' + (review.cancellationReason || 'لم يذكر سبب الإلغاء'));
      setElText('view-changes-text', 'تم الإلغاء بواسطة: ' + (review.cancelledBy || 'المستخدِم'));
    } else {
      setElText('view-completion-date', formatDateDisplay(review.completionDate));
      setElText('view-outcome-title', review.outcomeText || 'مكتمل');
      setElText('view-desc-text', review.currentWoundDescription || review.previousWoundDescription || 'لا يوجد وصف سجل');
      setElText('view-changes-text', review.currentWoundChanges || review.previousWoundChanges || 'لا توجد تغيرات مسجلة');
    }

    var modalEl = document.getElementById('view-details-modal');
    if (modalEl && window.bootstrap) {
      var modal = bootstrap.Modal.getOrCreateInstance(modalEl);
      modal.show();
    }
  };

  /* ============================================================================
     10. CANCEL APPOINTMENT MODAL
     ============================================================================ */
  window.openCancelModal = function () {
    var reviewModalEl = document.getElementById('scheduled-review-modal');
    if (reviewModalEl && window.bootstrap) {
      var reviewModal = bootstrap.Modal.getInstance(reviewModalEl);
      if (reviewModal) reviewModal.hide();
    }

    var reasonInput = document.getElementById('cancel-reason-input');
    if (reasonInput) reasonInput.value = '';

    var cancelModalEl = document.getElementById('cancel-appointment-modal');
    if (cancelModalEl && window.bootstrap) {
      var cancelModal = bootstrap.Modal.getOrCreateInstance(cancelModalEl);
      cancelModal.show();
    }
  };

  window.confirmCancelAppointment = function () {
    var review = reviewsList.find(function (r) { return r.id === activeReviewId; });
    if (!review) return;

    var reasonVal = document.getElementById('cancel-reason-input') ? document.getElementById('cancel-reason-input').value.trim() : '';

    WCCMS.api.cancelScheduledReview(review.id, reasonVal);

    loadReviewsData();

    var cancelModalEl = document.getElementById('cancel-appointment-modal');
    if (cancelModalEl && window.bootstrap) {
      var cancelModal = bootstrap.Modal.getInstance(cancelModalEl);
      if (cancelModal) cancelModal.hide();
    }

    showToast('تم إلغاء الموعد المجدول بنجاح', 'info');
    applyFilters();
  };

  /* ============================================================================
     11. HELPER UTILITIES
     ============================================================================ */
  function formatDateDisplay(dateStr) {
    if (window.WCCMS_DB && typeof window.WCCMS_DB.formatDateDisplay === 'function') {
      return window.WCCMS_DB.formatDateDisplay(dateStr);
    }
    if (!dateStr) return 'غير متوفر';
    var parts = dateStr.split(' ');
    var dateParts = parts[0].split('-');
    if (dateParts.length === 3) {
      var formattedDate = dateParts[2].padStart(2, '0') + '/' + dateParts[1].padStart(2, '0') + '/' + dateParts[0];
      if (parts[1]) {
        return formattedDate + ' — ' + parts[1];
      }
      return formattedDate;
    }
    return dateStr;
  }

  function setElText(id, text) {
    var el = document.getElementById(id);
    if (el) el.textContent = text || 'غير متوفر';
  }

  function escapeHtml(str) {
    if (!str) return '';
    return String(str)
      .replace(/&/g, '&amp;')
      .replace(/</g, '&lt;')
      .replace(/>/g, '&gt;')
      .replace(/"/g, '&quot;')
      .replace(/'/g, '&#039;');
  }

  function showToast(message, type) {
    type = type || 'info';
    var container = document.getElementById('toast-container');
    if (!container) return;

    var toast = document.createElement('div');
    toast.className = 'm8-toast toast-' + type;

    var iconClass = 'bi bi-info-circle-fill';
    if (type === 'success') iconClass = 'bi bi-check-circle-fill';
    if (type === 'danger')  iconClass = 'bi bi-exclamation-triangle-fill';
    if (type === 'warning') iconClass = 'bi bi-exclamation-circle-fill';

    toast.innerHTML = '<i class="' + iconClass + ' m8-toast-icon"></i><span>' + escapeHtml(message) + '</span>';
    container.appendChild(toast);

    setTimeout(function () {
      toast.style.animation = 'toastOut 0.3s ease forwards';
      setTimeout(function () {
        if (toast.parentNode) toast.parentNode.removeChild(toast);
      }, 300);
    }, 4000);
  }

})(window);
