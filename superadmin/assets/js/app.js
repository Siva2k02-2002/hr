// ---------- Toasts ----------
// Reusable, accessible toast system. window.toast(type, message) is the
// public API — type is 'success' | 'danger' | 'warning' | 'info'.
(function () {
  var ICONS = {
    success: 'bi-check-circle-fill',
    danger:  'bi-x-circle-fill',
    warning: 'bi-exclamation-triangle-fill',
    info:    'bi-info-circle-fill',
  };

  window.toast = function (type, message, timeout) {
    var stack = document.getElementById('toastStack');
    if (!stack) return;

    type = ICONS[type] ? type : 'info';
    timeout = timeout || 5000;

    var el = document.createElement('div');
    el.className = 'toast-item toast-' + type;
    el.setAttribute('role', 'status');
    el.innerHTML =
      '<i class="bi ' + ICONS[type] + '"></i>' +
      '<span class="toast-msg"></span>' +
      '<button type="button" class="toast-close" aria-label="Dismiss">&times;</button>';
    el.querySelector('.toast-msg').textContent = message;

    stack.appendChild(el);
    requestAnimationFrame(function () { el.classList.add('show'); });

    function dismiss() {
      el.classList.remove('show');
      setTimeout(function () { el.remove(); }, 200);
    }
    el.querySelector('.toast-close').addEventListener('click', dismiss);
    if (timeout > 0) setTimeout(dismiss, timeout);
  };

  document.addEventListener('DOMContentLoaded', function () {
    var data = document.getElementById('flash-data');
    if (!data) return;
    try {
      JSON.parse(data.textContent).forEach(function (f) {
        window.toast(f.type, f.message);
      });
    } catch (e) { /* malformed flash payload — nothing to show */ }
  });
})();

// ---------- Loading states ----------
// window.startLoading(el) / stopLoading(el) toggle a scoped overlay+spinner.
// window.skeletonRows(tbody, rows, cols) fills a table body with shimmering
// placeholder rows while real data is being fetched.
(function () {
  window.startLoading = function (host) {
    if (!host || host.querySelector(':scope > .loading-overlay')) return;
    host.classList.add('loading-host');
    var overlay = document.createElement('div');
    overlay.className = 'loading-overlay';
    overlay.innerHTML = '<span class="spinner-border spinner-border-sm text-primary"></span>';
    host.appendChild(overlay);
  };

  window.stopLoading = function (host) {
    if (!host) return;
    var overlay = host.querySelector(':scope > .loading-overlay');
    if (overlay) overlay.remove();
  };

  window.skeletonRows = function (tbody, rows, cols) {
    if (!tbody) return;
    var html = '';
    for (var r = 0; r < (rows || 3); r++) {
      html += '<tr class="skeleton-row">';
      for (var c = 0; c < (cols || 4); c++) {
        html += '<td><div class="skeleton-bar" style="width:' + (50 + Math.random() * 40) + '%"></div></td>';
      }
      html += '</tr>';
    }
    tbody.innerHTML = html;
  };
})();

document.addEventListener('DOMContentLoaded', function () {
  var sidebar  = document.querySelector('.sidebar');
  var backdrop = document.querySelector('.sidebar-backdrop');
  var toggle   = document.querySelector('.topbar-toggle');

  function closeSidebar() {
    sidebar && sidebar.classList.remove('open');
    backdrop && backdrop.classList.remove('open');
  }

  if (toggle) {
    toggle.addEventListener('click', function () {
      sidebar.classList.toggle('open');
      backdrop.classList.toggle('open');
    });
  }
  if (backdrop) {
    backdrop.addEventListener('click', closeSidebar);
  }

  // ---------- Confirmation modal (replaces window.confirm) ----------
  var confirmModalEl = document.getElementById('confirmModal');
  var confirmModal = (confirmModalEl && window.bootstrap) ? new bootstrap.Modal(confirmModalEl) : null;
  var pendingForm = null;

  document.querySelectorAll('form[data-confirm]').forEach(function (form) {
    form.addEventListener('submit', function (e) {
      if (form.dataset.confirmed === '1') return; // user already accepted, let it submit
      if (!confirmModal) return; // graceful fallback: submit normally if modal unavailable

      e.preventDefault();
      pendingForm = form;
      document.getElementById('confirmModalTitle').textContent = form.dataset.confirmTitle || 'Are you sure?';
      document.getElementById('confirmModalBody').textContent = form.getAttribute('data-confirm');
      var acceptBtn = document.getElementById('confirmModalAccept');
      acceptBtn.className = 'btn btn-sm ' + (form.dataset.confirmVariant || 'btn-danger');
      acceptBtn.textContent = form.dataset.confirmLabel || 'Confirm';
      confirmModal.show();
    });
  });

  var acceptBtn = document.getElementById('confirmModalAccept');
  if (acceptBtn) {
    acceptBtn.addEventListener('click', function () {
      if (!pendingForm) return;
      pendingForm.dataset.confirmed = '1';
      confirmModal.hide();
      pendingForm.requestSubmit ? pendingForm.requestSubmit() : pendingForm.submit();
      pendingForm = null;
    });
  }

  // Disable submit buttons on submit to prevent double-submission.
  document.querySelectorAll('form').forEach(function (form) {
    form.addEventListener('submit', function () {
      if (form.hasAttribute('data-confirm') && form.dataset.confirmed !== '1') return;
      var btn = form.querySelector('button[type="submit"]');
      if (btn && !btn.disabled) {
        btn.disabled = true;
        btn.dataset.originalText = btn.innerHTML;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>' + (btn.dataset.loadingText || 'Saving…');
        // Safety net: re-enable if the page doesn't navigate away (e.g. validation error re-render).
        setTimeout(function () {
          btn.disabled = false;
          if (btn.dataset.originalText) btn.innerHTML = btn.dataset.originalText;
        }, 8000);
      }
    });
  });

  // ---------- Searchable selects — every <select> in the app, no exceptions ----------
  if (window.jQuery && jQuery.fn.select2) {
    jQuery('select').select2({
      theme: 'bootstrap-5',
      width: 'resolve',
      minimumResultsForSearch: 0,
    });
  }
});
