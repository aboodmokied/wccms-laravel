/**
 * ============================================================================
 * WCCMS — Admin / Master Data Controller
 * ============================================================================
 */

'use strict';

(function () {

  var currentTable = 'diagnoses';
  var searchQuery = '';

  var tableTitles = {
    diagnoses: 'جدول التشخيصات الطبية (Diagnoses)',
    wound_types: 'جدول أنواع الجروح (Wound Types)',
    dressings: 'جدول الضمادات الطبية (Dressings)',
    medications: 'جدول الأدوية والمراهم (Medications)',
    procedures: 'جدول الإجراءات الطبية (Procedures)',
    pmh: 'جدول التاريخ المرضي (PMH List)',
    departments: 'جدول الأقسام الطبية (Departments)'
  };

  document.addEventListener('DOMContentLoaded', function () {
    bindEvents();
    renderCurrentLookup();
  });

  function bindEvents() {
    // Search input
    var searchInput = document.getElementById('admin-search');
    if (searchInput) {
      searchInput.addEventListener('input', function () {
        searchQuery = this.value.trim().toLowerCase();
        renderCurrentLookup();
      });
    }

    // Tab buttons
    document.querySelectorAll('.lookup-tab-btn').forEach(function (btn) {
      btn.addEventListener('click', function () {
        document.querySelectorAll('.lookup-tab-btn').forEach(function (b) { b.classList.remove('active'); });
        this.classList.add('active');
        currentTable = this.getAttribute('data-table');
        renderCurrentLookup();
      });
    });

    // Add item form submission
    var addForm = document.getElementById('add-item-form');
    if (addForm) {
      addForm.addEventListener('submit', function (e) {
        e.preventDefault();
        saveNewItem();
      });
    }
  }

  function renderCurrentLookup() {
    var titleEl = document.getElementById('admin-table-title');
    var countEl = document.getElementById('admin-item-count');
    var tbody = document.getElementById('admin-tbody');

    if (titleEl) titleEl.textContent = tableTitles[currentTable] || currentTable;

    var rawData = WCCMS._tables[currentTable] || [];
    var filtered = rawData.filter(function (item) {
      if (!searchQuery) return true;
      var name = (item.name || item.full_name || '').toLowerCase();
      var id = (item.id || '').toLowerCase();
      return name.includes(searchQuery) || id.includes(searchQuery);
    });

    if (countEl) countEl.textContent = filtered.length + ' عنصر';

    if (!tbody) return;

    if (filtered.length === 0) {
      tbody.innerHTML = '<tr><td colspan="4" class="text-center py-5 text-muted"><i class="bi bi-inbox fs-1 d-block mb-2 opacity-50"></i>لا توجد عناصر مطابقة في هذا الجدول</td></tr>';
      return;
    }

    tbody.innerHTML = filtered.map(function (item, index) {
      var name = item.name || item.full_name || '';
      var isActive = item.is_active !== false;

      return '<tr class="table-lookup-item">' +
        '<td class="fw-bold text-muted">' + (index + 1) + '</td>' +
        '<td><span class="badge badge-light-primary font-monospace ltr-cell">' + item.id + '</span></td>' +
        '<td><span class="fw-bold text-dark fs-6">' + escapeHtml(name) + '</span></td>' +
        '<td>' +
          (isActive
            ? '<span class="badge badge-active px-3 py-1"><i class="bi bi-check-circle-fill me-1"></i>نشط</span>'
            : '<span class="badge badge-inactive px-3 py-1"><i class="bi bi-x-circle-fill me-1"></i>غير نشط</span>') +
        '</td>' +
        '<td class="text-end">' +
          '<button class="btn btn-sm ' + (isActive ? 'btn-light-danger' : 'btn-light-success') + ' fw-semibold px-3" onclick="toggleItemStatus(\'' + currentTable + '\', \'' + item.id + '\', ' + !isActive + ')">' +
            '<i class="bi ' + (isActive ? 'bi-pause-circle-fill' : 'bi-play-circle-fill') + ' me-1"></i>' +
            (isActive ? 'تعطيل' : 'تفعيل') +
          '</button>' +
        '</td>' +
      '</tr>';
    }).join('');
  }

  window.toggleItemStatus = function (tableName, id, newStatus) {
    WCCMS.api.updateLookupItem(tableName, id, { is_active: newStatus });
    renderCurrentLookup();
    showToast(newStatus ? 'تم تفعيل العنصر بنجاح' : 'تم تعطيل العنصر بنجاح', newStatus ? 'success' : 'warning');
  };

  window.openAddItemModal = function () {
    var modalTitle = document.getElementById('add-modal-title');
    var input = document.getElementById('new-item-name');
    if (modalTitle) modalTitle.textContent = 'إضافة عنصر جديد إلى ' + (tableTitles[currentTable] || currentTable);
    if (input) input.value = '';

    var modalEl = document.getElementById('addItemModal');
    if (modalEl && window.bootstrap) {
      var modal = bootstrap.Modal.getOrCreateInstance(modalEl);
      modal.show();
    }
  };

  function saveNewItem() {
    var input = document.getElementById('new-item-name');
    var name = input ? input.value.trim() : '';

    if (!name) {
      showToast('الرجاء إدخال اسم العنصر', 'danger');
      return;
    }

    var prefixMap = {
      diagnoses: 'dx',
      wound_types: 'wt',
      dressings: 'drs',
      medications: 'med',
      procedures: 'proc',
      pmh: 'pmh',
      departments: 'dept'
    };

    var prefix = prefixMap[currentTable] || 'item';
    var newId = prefix + String(Math.floor(100 + Math.random() * 900));

    var newItem = { id: newId, name: name, is_active: true };
    WCCMS.api.addLookupItem(currentTable, newItem);

    var modalEl = document.getElementById('addItemModal');
    if (modalEl && window.bootstrap) {
      var modal = bootstrap.Modal.getInstance(modalEl);
      if (modal) modal.hide();
    }

    renderCurrentLookup();
    showToast('تمت إضافة العنصر بنجاح!', 'success');
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
    }, 3500);
  }

})();
