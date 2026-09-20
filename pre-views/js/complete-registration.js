/**
 * ============================================================================
 * WCCMS — Dedicated Patient Registration Page Controller
 * Page: complete-registration.html
 * Accepts URL parameter: ?id=REQ-001
 * Completes registration for referred patient and creates ACTIVE registration.
 * ============================================================================
 */

'use strict';

let currentWorkspaceRequest = null;
let wsDiagnoses = [];
let wsSelectedPMH = [];

const FALLBACK_PMH_LIST = [
  { id: 'pmh001', name: 'السكري (Diabetes Mellitus)' },
  { id: 'pmh002', name: 'ضغط الدم (Hypertension)' },
  { id: 'pmh003', name: 'الفشل الكلوي (Renal Failure)' },
  { id: 'pmh004', name: 'أمراض القلب (Cardiac Disease)' },
  { id: 'pmh005', name: 'السكتة الدماغية (Stroke)' },
  { id: 'pmh006', name: 'البدانة (Obesity)' },
  { id: 'pmh007', name: 'السرطان (Oncology Patient)' },
  { id: 'pmh008', name: 'كيماوي (Chemotherapy)' },
  { id: 'pmh009', name: 'كسر الورك (Hip Fracture)' },
  { id: 'pmh010', name: 'كسر الكاحل (Ankle Fracture)' },
  { id: 'pmh011', name: 'احتشاء عضلة القلب (MI)' },
  { id: 'pmh012', name: 'سرطان القولون (Colon Cancer)' },
  { id: 'pmh013', name: 'الربو (Asthma)' }
];

document.addEventListener('DOMContentLoaded', function () {
  applyTheme(localStorage.getItem('m8-theme') || 'light');
  bindEvents();

  const urlParams = new URLSearchParams(window.location.search);
  const requestId = urlParams.get('id') || urlParams.get('reqId');

  if (requestId) {
    initRegistrationForm(requestId);
  } else {
    showWorkspaceError('لم يتم تحديد رقم طلب التسجيل. سيتم توجيهك إلى قائمة الطلبات.');
    setTimeout(function () {
      window.location.href = 'requests.html';
    }, 2000);
  }
});

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
}

function initRegistrationForm(requestId) {
  var r = WCCMS.api.getRequestById(requestId);
  if (!r) {
    showWorkspaceError('لم يتم العثور على الطلب المطلوب (' + requestId + ').');
    return;
  }

  currentWorkspaceRequest = r;

  var name = r.patient_full_name || r.fullName || '';
  var natId = r.national_id || r.nationalId || '';
  var hospId = r.hospital_patient_id || r.hospitalPatientId || '';
  var age = r.patient_age || r.age || '';
  var gender = WCCMS.api.genderAr(r.gender) || r.gender || '';
  var doc = r.supervising_doctor || r.supervisingDoctor || '';
  var dept = r.hospital_department || r.hospitalDepartment || '';
  var admDate = r.hospital_admission_date || r.admissionDate || '';

  // Workspace Titles
  setEl('ws-page-title', 'استكمال تسجيل مريض: ' + name);
  setEl('ws-req-ref-subtitle', 'رقم الطلب المرجعي: ' + r.id);

  // 1. Patient Information Card (Read-Only)
  setEl('ws-patient-name', name);
  setEl('ws-national-id',  natId);
  setEl('ws-hospital-id',  hospId);
  setEl('ws-gender',       gender);
  setEl('ws-age',          age ? age + ' سنة' : 'غير متوفر');

  // 2. Hospital Admission Information Card (Read-Only)
  setEl('ws-doctor',       doc);
  setEl('ws-dept',         dept);
  setEl('ws-adm-date',     WCCMS.api.formatDate(admDate));

  // 3. Previous Wound Care History (Expandable Section)
  loadWorkspaceHistory(hospId, natId, r.patient_id);

  // 4. Patient Assessment Confirmation Checkbox
  var chk = document.getElementById('ws-assessment-checkbox');
  if (chk) {
    chk.checked = false;
    chk.disabled = false;
  }

  // 5. Complete Registration Form Initial Data
  var currentUser = WCCMS.api.getCurrentUser();
  var todayDateStr = (window.WCCMS && window.WCCMS.api)
    ? (typeof window.WCCMS.api.today === 'function' ? window.WCCMS.api.today() : window.WCCMS.api.today)
    : (new Date().toISOString().split('T')[0]);
  setEl('ws-reg-date', WCCMS.api.formatDate(todayDateStr));
  setEl('ws-reg-nurse', currentUser ? currentUser.full_name : 'أ. فاطمة الزهراني');
  setEl('ws-reg-dept', 'عيادة العناية بالجروح');

  // Populate Procedures Select
  var procSelect = document.getElementById('ws-procedure');
  if (procSelect) {
    var procs = WCCMS.api.getActiveLookups('procedures');
    procSelect.innerHTML = '<option value="">اختر الإجراء الطبي الأول...</option>' +
      procs.map(function(p) { return '<option value="' + p.id + '">' + p.name + '</option>'; }).join('');
  }

  wsSelectedPMH = (r && r.pmh_ids && Array.isArray(r.pmh_ids)) ? r.pmh_ids.slice() : [];
  var pmhNotesEl = document.getElementById('ws-pmh-notes');
  if (pmhNotesEl) pmhNotesEl.value = '';
  renderWorkspacePMH();

  // Initial Diagnoses (Add 1 default empty card)
  wsDiagnoses = [{ id: 0, type: '', woundType: '', description: '', dressing: '', medication: '' }];
  renderWorkspaceDiagnoses();
}

function loadWorkspaceHistory(hospId, nationalId, patientId) {
  var patient = (patientId ? WCCMS.api.getPatientById(patientId) : null) ||
                (nationalId ? WCCMS.api.getPatientByNationalId(nationalId) : null) ||
                (hospId ? WCCMS.api.getPatientByHospitalId(hospId) : null);
  var history = patient ? WCCMS.api.getPatientHistory(patient.id) : null;
  var noHistory = document.getElementById('ws-no-history');
  var hasHistory = document.getElementById('ws-has-history');
  var badge = document.getElementById('ws-history-badge');

  if (!history || !history.registrations || history.registrations.length === 0) {
    if (noHistory)  noHistory.classList.remove('d-none');
    if (hasHistory) hasHistory.classList.add('d-none');
    if (badge) {
      badge.textContent = 'لا يوجد تسجيل سابق';
      badge.className = 'badge badge-light-success';
    }
    return;
  }

  if (noHistory)  noHistory.classList.add('d-none');
  if (hasHistory) hasHistory.classList.remove('d-none');
  if (badge) {
    badge.textContent = 'يوجد تسجيل سابق';
    badge.className = 'badge badge-light-warning';
  }

  var prevReg = history.registrations[0];
  setEl('ws-hist-date',       WCCMS.api.formatDate(prevReg.registration_date));
  setEl('ws-hist-diag',       (prevReg.diagnoses || []).map(function(d) { return d.diagnosis_name || d.name || ''; }).filter(Boolean).join('، ') || '—');
  setEl('ws-hist-wound-type', (prevReg.diagnoses || []).map(function(d) { return d.wound_type_name || d.wound_type || ''; }).filter(Boolean).join('، ') || '—');
  setEl('ws-hist-reviews',    ((prevReg.daily_reviews || []).length) + ' مراجعة سابقة');
  setEl('ws-hist-dressing',   (prevReg.diagnoses || []).map(function(d) { return d.initial_dressing_name || d.dressing || '—'; }).filter(Boolean).join('، ') || '—');
  setEl('ws-hist-desc',       (prevReg.diagnoses || []).map(function(d) { return d.description || ''; }).filter(Boolean).join(' | ') || '—');
}

// PMH Dropdown Select Management
function renderWorkspacePMH() {
  var select = document.getElementById('ws-pmh-select');
  if (!select) return;

  var pmhList = (window.WCCMS && window.WCCMS.api && typeof window.WCCMS.api.getActiveLookups === 'function')
    ? window.WCCMS.api.getActiveLookups('pmh')
    : [];

  if (!pmhList || pmhList.length === 0) {
    pmhList = FALLBACK_PMH_LIST;
  }

  var selectedValue = (wsSelectedPMH && wsSelectedPMH.length > 0) ? wsSelectedPMH[0] : '';

  select.innerHTML = '<option value="">— اختر التاريخ المرضي (اختياري) —</option>' +
    pmhList.map(function (p) {
      var isSelected = selectedValue === p.id;
      var nameStr = p.name || p.nameAr || p.title || p.id;
      return '<option value="' + p.id + '" ' + (isSelected ? 'selected' : '') + '>' + nameStr + '</option>';
    }).join('');
}

function onWorkspacePMHChange() {
  var select = document.getElementById('ws-pmh-select');
  if (!select) return;
  var val = select.value;
  wsSelectedPMH = val ? [val] : [];
}
window.onWorkspacePMHChange = onWorkspacePMHChange;

// Diagnoses Management
function addWorkspaceDiagnosis() {
  var idx = wsDiagnoses.length;
  wsDiagnoses.push({ id: idx, type: '', woundType: '', description: '', dressing: '', medication: '' });
  renderWorkspaceDiagnoses();
}
window.addWorkspaceDiagnosis = addWorkspaceDiagnosis;

function removeWorkspaceDiagnosis(idx) {
  wsDiagnoses.splice(idx, 1);
  wsDiagnoses.forEach(function (d, i) { d.id = i; });
  renderWorkspaceDiagnoses();
}
window.removeWorkspaceDiagnosis = removeWorkspaceDiagnosis;

function syncWorkspaceDiag(idx, field, value) {
  if (wsDiagnoses[idx]) {
    wsDiagnoses[idx][field] = value;
  }
}
window.syncWorkspaceDiag = syncWorkspaceDiag;

function renderWorkspaceDiagnoses() {
  var container = document.getElementById('ws-diagnoses-container');
  if (!container) return;

  var diagnosesList = WCCMS.api.getActiveLookups('diagnoses');
  var woundTypesList = WCCMS.api.getActiveLookups('wound_types');
  var dressingsList = WCCMS.api.getActiveLookups('dressings');
  var medicationsList = WCCMS.api.getActiveLookups('medications');

  container.innerHTML = wsDiagnoses.map(function (d, i) {
    return '<div class="diagnosis-card p-3 mb-3 border rounded" id="ws-diag-card-' + i + '">' +
      '<div class="diagnosis-card-header d-flex justify-content-between align-items-center mb-3 pb-2 border-bottom">' +
        '<span class="fw-bold text-dark fs-6 d-flex align-items-center gap-2">' +
          '<span class="badge badge-light-primary rounded-circle p-2">' + (i + 1) + '</span>' +
          'تشخيص #' + (i + 1) +
        '</span>' +
        (wsDiagnoses.length > 1 ? '<button type="button" class="btn btn-sm btn-light-danger btn-remove-diagnosis text-nowrap" onclick="removeWorkspaceDiagnosis(' + i + ')"><i class="bi bi-trash3 me-1"></i> حذف</button>' : '') +
      '</div>' +
      '<div class="row g-3">' +
        '<div class="col-12 col-md-6">' +
          '<label class="form-label fw-semibold">التشخيص <span class="text-danger">*</span></label>' +
          '<select class="form-select form-select-solid" onchange="syncWorkspaceDiag(' + i + ',\'type\',this.value)">' +
            '<option value="">اختر التشخيص...</option>' +
            diagnosesList.map(function (dt) { return '<option value="' + dt.id + '" ' + (d.type === dt.id ? 'selected' : '') + '>' + dt.name + '</option>'; }).join('') +
          '</select>' +
        '</div>' +
        '<div class="col-12 col-md-6">' +
          '<label class="form-label fw-semibold">نوع الجرح <span class="text-danger">*</span></label>' +
          '<select class="form-select form-select-solid" onchange="syncWorkspaceDiag(' + i + ',\'woundType\',this.value)">' +
            '<option value="">اختر نوع الجرح...</option>' +
            woundTypesList.map(function (wt) { return '<option value="' + wt.id + '" ' + (d.woundType === wt.id ? 'selected' : '') + '>' + wt.name + '</option>'; }).join('') +
          '</select>' +
        '</div>' +
        '<div class="col-12">' +
          '<label class="form-label fw-semibold">وصف الجرح الأولي <span class="text-danger">*</span></label>' +
          '<textarea class="form-control form-control-solid" rows="2" placeholder="أدخل وصف تفصيلي أولي لحالة وموقع الجرح..." oninput="syncWorkspaceDiag(' + i + ',\'description\',this.value)">' + (d.description || '') + '</textarea>' +
        '</div>' +
        '<div class="col-12 col-md-6">' +
          '<label class="form-label fw-semibold">الضمادة الأولية</label>' +
          '<select class="form-select form-select-solid" onchange="syncWorkspaceDiag(' + i + ',\'dressing\',this.value)">' +
            '<option value="">— اختياري —</option>' +
            dressingsList.map(function (dr) { return '<option value="' + dr.id + '" ' + (d.dressing === dr.id ? 'selected' : '') + '>' + dr.name + '</option>'; }).join('') +
          '</select>' +
        '</div>' +
        '<div class="col-12 col-md-6">' +
          '<label class="form-label fw-semibold">الدواء الأولي</label>' +
          '<select class="form-select form-select-solid" onchange="syncWorkspaceDiag(' + i + ',\'medication\',this.value)">' +
            '<option value="">— اختياري —</option>' +
            medicationsList.map(function (m) { return '<option value="' + m.id + '" ' + (d.medication === m.id ? 'selected' : '') + '>' + m.name + '</option>'; }).join('') +
          '</select>' +
        '</div>' +
      '</div>' +
    '</div>';
  }).join('');
}

// Submission
function submitWorkspaceRegistration() {
  onWorkspacePMHChange();
  var alertBox = document.getElementById('ws-validation-alert');

  // Rule 1: Nurse must confirm physical assessment
  var chk = document.getElementById('ws-assessment-checkbox');
  if (!chk || !chk.checked) {
    showWorkspaceError('إلزامي: يجب تأكيد معاينة المريض قبل إتمام عملية التسجيل.');
    return;
  }

  // Rule 2: One Procedure per Registration
  var procSelect = document.getElementById('ws-procedure');
  if (!procSelect || !procSelect.value) {
    showWorkspaceError('إلزامي: يرجى اختيار الإجراء الطبي المناسب.');
    return;
  }

  // Rule 3: One or more diagnoses required with description
  if (wsDiagnoses.length === 0) {
    showWorkspaceError('إلزامي: يرجى إضافة تشخيص واحد على الأقل.');
    return;
  }

  for (var i = 0; i < wsDiagnoses.length; i++) {
    var d = wsDiagnoses[i];
    if (!d.type || !d.woundType || !d.description.trim()) {
      showWorkspaceError('يرجى استكمال بيانات تشخيص #' + (i + 1) + ' (التشخيص، نوع الجرح، الوصف).');
      return;
    }
  }

  // Clear errors
  if (alertBox) alertBox.classList.add('d-none');

  var currentUser = WCCMS.api.getCurrentUser();

  var regData = {
    department_id: 'dept008', // Wound Care Clinic
    pmh_ids: wsSelectedPMH,
    diagnoses: wsDiagnoses.map(function(d) {
      return {
        diagnosis_id: d.type,
        wound_type_id: d.woundType,
        description: d.description,
        initial_dressing_id: d.dressing || null,
        initial_medication_id: d.medication || null,
        procedure_ids: [procSelect.value]
      };
    })
  };

  // Call WCCMS.api.approveRequest
  var result = WCCMS.api.approveRequest(currentWorkspaceRequest.id, currentUser ? currentUser.id : 'u001', regData);

  if (!result) {
    showWorkspaceError('حدث خطأ أثناء الموافقة على الطلب والتسجيل.');
    return;
  }

  var regId = result.registration.id;

  // Transition Workspace UI to Success State
  var mainContent = document.getElementById('ws-main-content');
  var successContainer = document.getElementById('ws-success-container');

  if (mainContent) mainContent.classList.add('d-none');
  if (successContainer) {
    setEl('ws-success-reg-id', regId);
    successContainer.classList.remove('d-none');
    window.scrollTo({ top: 0, behavior: 'smooth' });
  }
}
window.submitWorkspaceRegistration = submitWorkspaceRegistration;

function showWorkspaceError(msg) {
  var alertBox = document.getElementById('ws-validation-alert');
  var msgEl = document.getElementById('ws-validation-msg');
  if (msgEl) msgEl.textContent = msg;
  if (alertBox) {
    alertBox.classList.remove('d-none');
    alertBox.scrollIntoView({ behavior: 'smooth', block: 'center' });
  }
}

function setEl(id, val) {
  var el = document.getElementById(id);
  if (el) el.textContent = val;
}
