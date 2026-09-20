/**
 * ============================================================================
 * WCCMS - Wound Care Clinic Management System
 * Registration Request Page - JavaScript Controller
 * ============================================================================
 *
 * This module handles:
 *   1. Patient search via National ID (simulated hospital API)
 *   2. Dynamic rendering of patient & admission data (read-only)
 *   3. Registration request form validation
 *   4. Confirmation modal flow
 *   5. Request submission & success state
 *   6. Form reset for new request
 */

// ============================================================================
// Mock Hospital Database
// ============================================================================
const hospitalPatients = [
  {
    hospitalPatientId: "HP-10025",
    nationalId: "987654321",
    fullName: "أحمد محمد علي",
    gender: "ذكر",
    age: 52,
    supervisingDoctor: "د. محمد خالد",
    hospitalDepartment: "العناية المركزة ICU",
    hospitalAdmissionDate: "2026-08-31"
  },
  {
    hospitalPatientId: "HP-10026",
    nationalId: "123456789",
    fullName: "سارة أحمد محمود",
    gender: "أنثى",
    age: 44,
    supervisingDoctor: "د. أحمد محمود",
    hospitalDepartment: "الأورام Oncology",
    hospitalAdmissionDate: "2026-08-30"
  },
  {
    hospitalPatientId: "HP-10027",
    nationalId: "111222333",
    fullName: "خالد عبدالرحمن الشمري",
    gender: "ذكر",
    age: 67,
    supervisingDoctor: "د. فهد السعيد",
    hospitalDepartment: "الجراحة العامة Surgery",
    hospitalAdmissionDate: "2026-08-28"
  },
  {
    hospitalPatientId: "HP-10028",
    nationalId: "444555666",
    fullName: "نورة محمد العتيبي",
    gender: "أنثى",
    age: 38,
    supervisingDoctor: "د. هند الزهراني",
    hospitalDepartment: "القلب CCU",
    hospitalAdmissionDate: "2026-08-29"
  }
];

// Fixed department — all registration requests go to the Wound Care Clinic
const WOUND_CARE_DEPARTMENT = {
  id: "wound_care_clinic",
  name: "Wound Care Clinic",
  nameAr: "عيادة العناية بالجروح"
};

// ============================================================================
// ============================================================================
// State
// ============================================================================
let currentPatient = null;
let currentStep = 1;
let requestCounter = 1;
let selectedPMH = [];

// ============================================================================
// PMH Grid Rendering
// ============================================================================
function renderPMHGrid() {
  var grid = document.getElementById('request-pmh-grid');
  if (!grid) return;

  var pmhList = WCCMS.api ? WCCMS.api.getActiveLookups('pmh') : [];
  if (!pmhList || pmhList.length === 0) {
    grid.innerHTML = '<div class="text-muted small p-2">لا توجد خيارات متوفرة</div>';
    return;
  }

  grid.innerHTML = pmhList.map(function (p) {
    var isSelected = selectedPMH.indexOf(p.id) !== -1;
    return '<label class="pmh-card ' + (isSelected ? 'selected' : '') + '" onclick="togglePMH(\'' + p.id + '\')">' +
      '<span class="pmh-check"><i class="bi bi-check-lg"></i></span>' +
      '<span class="pmh-label">' + (p.name || p.nameAr || '') + '</span>' +
    '</label>';
  }).join('');
}

function togglePMH(id) {
  var idx = selectedPMH.indexOf(id);
  if (idx === -1) {
    selectedPMH.push(id);
  } else {
    selectedPMH.splice(idx, 1);
  }
  renderPMHGrid();
  updateSummary();
}
window.togglePMH = togglePMH;

// ============================================================================
// DOM Ready
// ============================================================================
document.addEventListener('DOMContentLoaded', function () {
  initializePage();
  bindEvents();
});

// ============================================================================
// Initialization
// ============================================================================
function initializePage() {
  // Set initial stepper state
  updateStepper(1);

  // Ensure steps 2 & 3 are hidden
  hideElement('step-2-section');
  hideElement('step-3-section');
  hideElement('search-alert');

  // Pre-render PMH Grid options so they are ready when step 3 opens
  renderPMHGrid();

  // Disable submit button initially
  updateSubmitButton();
}

// ============================================================================
// Event Bindings
// ============================================================================
function bindEvents() {
  // Search form submission
  var searchForm = document.getElementById('search-form');
  if (searchForm) {
    searchForm.addEventListener('submit', function (e) {
      e.preventDefault();
      searchPatient();
    });
  }

  // No department selection needed — fixed to Wound Care Clinic

  // Notes input change
  var notesInput = document.getElementById('request-notes');
  if (notesInput) {
    notesInput.addEventListener('input', function () {
      updateSummary();
    });
  }

  // Submit registration request button
  var submitBtn = document.getElementById('btn-submit-request');
  if (submitBtn) {
    submitBtn.addEventListener('click', function () {
      openConfirmationModal();
    });
  }

  // Confirm button inside modal
  var confirmBtn = document.getElementById('btn-confirm-submit');
  if (confirmBtn) {
    confirmBtn.addEventListener('click', function () {
      submitRegistrationRequest();
    });
  }

  // New request button (in success modal)
  var newRequestBtn = document.getElementById('btn-new-request');
  if (newRequestBtn) {
    newRequestBtn.addEventListener('click', function () {
      resetForm();
    });
  }

  // View request status button
  var viewStatusBtn = document.getElementById('btn-view-status');
  if (viewStatusBtn) {
    viewStatusBtn.addEventListener('click', function () {
      window.location.href = 'requests.html';
    });
  }

  // Theme toggle
  var themeToggle = document.getElementById('m8-theme-toggle');
  if (themeToggle) {
    themeToggle.addEventListener('click', function () {
      toggleTheme();
    });
  }

  // Mobile menu toggle
  var mobileToggle = document.getElementById('m8-mobile-toggle');
  if (mobileToggle) {
    mobileToggle.addEventListener('click', function () {
      var sidebar = document.getElementById('m8-sidebar');
      if (sidebar) {
        sidebar.classList.toggle('show');
      }
    });
  }

  // Close sidebar on outside click (mobile)
  document.addEventListener('click', function (e) {
    if (window.innerWidth < 992) {
      var sidebar = document.getElementById('m8-sidebar');
      var toggle = document.getElementById('m8-mobile-toggle');
      if (sidebar && toggle) {
        if (!sidebar.contains(e.target) && !toggle.contains(e.target)) {
          sidebar.classList.remove('show');
        }
      }
    }
  });
}

// ============================================================================
// Patient Search
// ============================================================================
function searchPatient() {
  var nationalIdInput = document.getElementById('national-id');
  var nationalId = nationalIdInput.value.trim();

  // Hide previous results
  hideElement('search-alert');
  hideElement('step-2-section');
  hideElement('step-3-section');
  currentPatient = null;
  updateSubmitButton();

  if (!nationalId) {
    showSearchAlert('الرجاء إدخال رقم الهوية', 'danger');
    return;
  }

  // Show loading state on button
  var searchBtn = document.getElementById('btn-search');
  var btnText = searchBtn.querySelector('.btn-text');
  var btnSpinner = searchBtn.querySelector('.btn-spinner');
  searchBtn.disabled = true;
  btnText.textContent = 'جاري البحث...';
  btnSpinner.classList.remove('d-none');

  // Simulate async hospital API call via WCCMS.api
  setTimeout(function () {
    // Reset button
    searchBtn.disabled = false;
    btnText.textContent = 'بحث';
    btnSpinner.classList.add('d-none');

    // Search in hospital database via WCCMS.api
    var patient = WCCMS.api.searchHospitalPatient(nationalId);

    if (patient) {
      currentPatient = patient;
      displayPatientData(patient);
      updateStepper(2);

      // Smooth scroll to step 2
      setTimeout(function () {
        var step2 = document.getElementById('step-2-section');
        if (step2) {
          step2.scrollIntoView({ behavior: 'smooth', block: 'start' });
        }
      }, 100);
    } else {
      showSearchAlert('لم يتم العثور على مريض بهذا الرقم.', 'danger');
      updateStepper(1);
    }
  }, 600);
}

// ============================================================================
// Display Patient Data (Read-Only)
// ============================================================================
function displayPatientData(patient) {
  // Patient information fields (supporting both snake_case and camelCase)
  setFieldValue('field-fullname', patient.patient_full_name || patient.fullName);
  setFieldValue('field-national-id', patient.national_id || patient.nationalId);
  setFieldValue('field-hospital-id', patient.hospital_patient_id || patient.hospitalPatientId);
  setFieldValue('field-gender', WCCMS.api.genderAr(patient.gender) || patient.gender);
  setFieldValue('field-age', (patient.patient_age || patient.age) + ' سنة');

  // Admission information fields
  setFieldValue('field-doctor', patient.supervising_doctor || patient.supervisingDoctor);
  setFieldValue('field-department', patient.hospital_department || patient.hospitalDepartment);
  setFieldValue('field-admission-date', WCCMS.api.formatDate(patient.hospital_admission_date || patient.hospitalAdmissionDate));

  // Show step 2
  showElement('step-2-section');

  // Show step 3
  showElement('step-3-section');
  updateStepper(3);

  // Render PMH grid options
  renderPMHGrid();

  // Update summary with patient data
  updateSummary();
  updateSubmitButton();
}

// ============================================================================
// Update Request Summary
// ============================================================================
function updateSummary() {
  if (!currentPatient) return;

  var summaryName = document.getElementById('summary-patient-name');
  var summaryNationalId = document.getElementById('summary-national-id');
  var summaryDept = document.getElementById('summary-department');
  var summaryStatus = document.getElementById('summary-status');
  var summaryPMH = document.getElementById('summary-pmh');

  if (summaryName) summaryName.textContent = currentPatient.patient_full_name || currentPatient.full_name || currentPatient.fullName;
  if (summaryNationalId) summaryNationalId.textContent = currentPatient.national_id || currentPatient.nationalId;
  if (summaryDept) summaryDept.textContent = WOUND_CARE_DEPARTMENT.nameAr;

  if (summaryPMH) {
    if (selectedPMH.length > 0) {
      var pmhList = WCCMS.api.getActiveLookups('pmh');
      var names = selectedPMH.map(function (id) {
        var item = pmhList.find(function (p) { return p.id === id; });
        return item ? item.name : id;
      });
      summaryPMH.textContent = names.join('، ');
    } else {
      summaryPMH.textContent = 'لا يوجد / لم يحدد';
    }
  }

  if (summaryStatus) {
    summaryStatus.textContent = 'سيتم إرسال الطلب للمراجعة';
  }
}

// ============================================================================
// Validate Request
// ============================================================================
function validateRequest() {
  if (!currentPatient) return false;
  return true;
}

// ============================================================================
// Update Submit Button State
// ============================================================================
function updateSubmitButton() {
  var submitBtn = document.getElementById('btn-submit-request');
  if (submitBtn) {
    submitBtn.disabled = !validateRequest();
  }
}

// ============================================================================
// Open Confirmation Modal
// ============================================================================
function openConfirmationModal() {
  if (!validateRequest()) return;

  // Populate modal data
  var modalName = document.getElementById('modal-patient-name');
  var modalNationalId = document.getElementById('modal-national-id');
  var modalDept = document.getElementById('modal-department');
  var modalPMH = document.getElementById('modal-pmh');

  if (modalName) modalName.textContent = currentPatient.patient_full_name || currentPatient.full_name || currentPatient.fullName;
  if (modalNationalId) modalNationalId.textContent = currentPatient.national_id || currentPatient.nationalId;
  if (modalDept) modalDept.textContent = WOUND_CARE_DEPARTMENT.nameAr;

  if (modalPMH) {
    if (selectedPMH.length > 0) {
      var pmhList = WCCMS.api.getActiveLookups('pmh');
      var names = selectedPMH.map(function (id) {
        var item = pmhList.find(function (p) { return p.id === id; });
        return item ? item.name : id;
      });
      modalPMH.textContent = names.join('، ');
    } else {
      modalPMH.textContent = 'لا يوجد / لم يحدد';
    }
  }

  // Show the modal
  var confirmModal = new bootstrap.Modal(document.getElementById('confirmationModal'));
  confirmModal.show();
}

// ============================================================================
// Submit Registration Request
// ============================================================================
function submitRegistrationRequest() {
  if (!validateRequest()) return;

  var confirmBtn = document.getElementById('btn-confirm-submit');
  var btnText = confirmBtn.querySelector('.btn-text');
  var btnSpinner = confirmBtn.querySelector('.btn-spinner');

  // Show loading
  confirmBtn.disabled = true;
  btnText.textContent = 'جاري الإرسال...';
  btnSpinner.classList.remove('d-none');

  var notesInput = document.getElementById('request-notes');

  setTimeout(function () {
    // Create the REGISTRATION_REQUEST via WCCMS.api
    var reqData = {
      hospital_patient_id: currentPatient.hospital_patient_id || currentPatient.hospitalPatientId,
      national_id: currentPatient.national_id || currentPatient.nationalId,
      patient_full_name: currentPatient.patient_full_name || currentPatient.full_name || currentPatient.fullName,
      gender: currentPatient.gender,
      patient_age: currentPatient.patient_age || currentPatient.age,
      supervising_doctor: currentPatient.supervising_doctor || currentPatient.supervisingDoctor,
      hospital_department: currentPatient.hospital_department || currentPatient.hospitalDepartment,
      hospital_admission_date: currentPatient.hospital_admission_date || currentPatient.hospitalAdmissionDate,
      notes: notesInput ? notesInput.value.trim() : '',
      pmh_ids: selectedPMH.slice()
    };

    var createdReq = WCCMS.api.submitRequest(reqData);

    // Close confirmation modal
    var confirmModal = bootstrap.Modal.getInstance(document.getElementById('confirmationModal'));
    if (confirmModal) {
      confirmModal.hide();
    }

    // Reset confirm button
    confirmBtn.disabled = false;
    btnText.textContent = 'تأكيد إرسال الطلب';
    btnSpinner.classList.add('d-none');

    // Show success
    showSuccess(createdReq.id);
  }, 600);
}

// ============================================================================
// Show Success State
// ============================================================================
function showSuccess(requestId) {
  // Populate success modal data
  var successRequestId = document.getElementById('success-request-id');
  var successStatus = document.getElementById('success-status');

  if (successRequestId) successRequestId.textContent = requestId;
  if (successStatus) successStatus.textContent = 'قيد المراجعة';

  // Show success modal
  var successModal = new bootstrap.Modal(document.getElementById('successModal'));
  successModal.show();
}

// ============================================================================
// Reset Form
// ============================================================================
function resetForm() {
  // Close success modal
  var successModal = bootstrap.Modal.getInstance(document.getElementById('successModal'));
  if (successModal) {
    successModal.hide();
  }

  // Reset state
  currentPatient = null;
  selectedPMH = [];
  renderPMHGrid();

  // Reset form fields
  var nationalIdInput = document.getElementById('national-id');
  if (nationalIdInput) nationalIdInput.value = '';

  // No department select to reset — it's always the Wound Care Clinic

  var notesInput = document.getElementById('request-notes');
  if (notesInput) notesInput.value = '';

  // Hide steps 2 and 3
  hideElement('step-2-section');
  hideElement('step-3-section');
  hideElement('search-alert');

  // Reset stepper
  updateStepper(1);

  // Reset submit button
  updateSubmitButton();

  // Scroll to top
  window.scrollTo({ top: 0, behavior: 'smooth' });
}

// ============================================================================
// Stepper Navigation
// ============================================================================
function updateStepper(step) {
  currentStep = step;
  var steps = document.querySelectorAll('.m8-stepper-item');

  steps.forEach(function (item, index) {
    var stepNum = index + 1;
    item.classList.remove('active', 'completed');

    if (stepNum < step) {
      item.classList.add('completed');
      // Replace number with check icon
      var icon = item.querySelector('.m8-stepper-icon');
      if (icon) icon.innerHTML = '<i class="bi bi-check-lg"></i>';
    } else if (stepNum === step) {
      item.classList.add('active');
      var icon = item.querySelector('.m8-stepper-icon');
      if (icon) icon.textContent = stepNum;
    } else {
      var icon = item.querySelector('.m8-stepper-icon');
      if (icon) icon.textContent = stepNum;
    }
  });
}

// ============================================================================
// Theme Toggle
// ============================================================================
function toggleTheme() {
  var html = document.documentElement;
  var currentTheme = html.getAttribute('data-bs-theme');
  var newTheme = currentTheme === 'dark' ? 'light' : 'dark';

  html.setAttribute('data-bs-theme', newTheme);
  localStorage.setItem('m8-theme', newTheme);

  var icon = document.getElementById('m8-theme-toggle-icon');
  var text = document.getElementById('m8-theme-toggle-text');

  if (newTheme === 'dark') {
    if (icon) { icon.classList.remove('bi-moon-stars-fill'); icon.classList.add('bi-sun-fill'); }
    if (text) text.textContent = 'الوضع المضيء';
  } else {
    if (icon) { icon.classList.remove('bi-sun-fill'); icon.classList.add('bi-moon-stars-fill'); }
    if (text) text.textContent = 'الوضع الليلي';
  }
}

// Apply saved theme on load
(function () {
  var saved = localStorage.getItem('m8-theme') || 'light';
  document.documentElement.setAttribute('data-bs-theme', saved);
})();

// ============================================================================
// Utility Functions
// ============================================================================
function showElement(id) {
  var el = document.getElementById(id);
  if (el) {
    el.classList.remove('d-none');
    el.style.opacity = '0';
    el.style.transform = 'translateY(16px)';
    // Trigger reflow
    el.offsetHeight;
    el.style.transition = 'opacity 0.4s ease, transform 0.4s ease';
    el.style.opacity = '1';
    el.style.transform = 'translateY(0)';
  }
}

function hideElement(id) {
  var el = document.getElementById(id);
  if (el) {
    el.classList.add('d-none');
    el.style.opacity = '';
    el.style.transform = '';
    el.style.transition = '';
  }
}

function setFieldValue(id, value) {
  var el = document.getElementById(id);
  if (el) el.textContent = value;
}

function showSearchAlert(message, type) {
  var alert = document.getElementById('search-alert');
  if (alert) {
    alert.className = 'm8-alert alert-' + type;
    var icon = alert.querySelector('.alert-icon');
    var text = alert.querySelector('.alert-text');
    if (icon) {
      icon.className = 'alert-icon bi ' + (type === 'danger' ? 'bi-exclamation-triangle-fill' : 'bi-check-circle-fill');
    }
    if (text) text.textContent = message;
    alert.classList.remove('d-none');
  }
}

function formatDate(dateStr) {
  if (!dateStr) return 'غير متوفر';
  var parts = dateStr.split('-');
  if (parts.length !== 3) return dateStr;
  // Format: DD/MM/YYYY
  return parts[2].padStart(2, '0') + '/' + parts[1].padStart(2, '0') + '/' + parts[0];
}
