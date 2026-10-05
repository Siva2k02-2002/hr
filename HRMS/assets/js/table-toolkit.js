// ---------- Table toolkit: density toggle + bulk select ----------
// Generic, opt-in via data attributes on `.table-wrap` so any future list
// page can adopt either feature without bespoke JS:
//   data-density-toggle              adds a comfortable/compact/spacious switch
//   data-bulk-select                 adds row checkboxes + a selection toolbar
//   data-density-key="employees"     (optional) keys the density preference;
//                                     defaults to the wrap's id, else "default"
// Bulk-select only manages selection state and fires `hrms:bulk-selection`
// with the current count — wiring an actual bulk action to a real endpoint
// (which doesn't exist yet for any module) is left to the page.
(function () {
  function densityKey(wrap) {
    return 'hrms.tableDensity.' + (wrap.getAttribute('data-density-key') || wrap.id || 'default');
  }

  function applyDensity(wrap, density) {
    wrap.classList.remove('density-comfortable', 'density-compact', 'density-spacious');
    wrap.classList.add('density-' + density);
  }

  function initDensity(wrap) {
    if (wrap.dataset.densityBound === '1') return;
    wrap.dataset.densityBound = '1';

    var stored = 'comfortable';
    try { stored = localStorage.getItem(densityKey(wrap)) || 'comfortable'; } catch (e) { /* storage unavailable */ }
    applyDensity(wrap, stored);

    var group = document.createElement('div');
    group.className = 'density-toggle';
    group.setAttribute('role', 'group');
    group.setAttribute('aria-label', 'Table row density');
    var icons = { comfortable: 'rows-4', compact: 'rows-3', spacious: 'rows-2' };
    group.innerHTML = ['comfortable', 'compact', 'spacious'].map(function (d) {
      return '<button type="button" class="density-btn' + (d === stored ? ' is-active' : '') + '" data-density="' + d + '" title="' + d.charAt(0).toUpperCase() + d.slice(1) + ' rows" aria-label="' + d + ' rows">' +
        '<i data-lucide="' + icons[d] + '" class="icon icon-sm"></i></button>';
    }).join('');
    wrap.parentElement.insertBefore(group, wrap);

    group.addEventListener('click', function (e) {
      var btn = e.target.closest('.density-btn');
      if (!btn) return;
      var d = btn.dataset.density;
      applyDensity(wrap, d);
      group.querySelectorAll('.density-btn').forEach(function (b) { b.classList.toggle('is-active', b === btn); });
      try { localStorage.setItem(densityKey(wrap), d); } catch (e2) { /* storage unavailable */ }
    });
    window.initIcons();
  }

  function initBulkSelect(wrap) {
    if (wrap.dataset.bulkBound === '1') return;
    wrap.dataset.bulkBound = '1';

    var table = wrap.querySelector('table');
    if (!table) return;
    var headRow = table.querySelector('thead tr');
    var bodyRows = table.querySelectorAll('tbody tr');
    if (!headRow || !bodyRows.length) return;

    var selectAllTh = document.createElement('th');
    selectAllTh.style.width = '40px';
    selectAllTh.innerHTML = '<input type="checkbox" class="form-check-input" data-select-all aria-label="Select all rows">';
    headRow.insertBefore(selectAllTh, headRow.firstChild);

    bodyRows.forEach(function (row) {
      var td = document.createElement('td');
      td.innerHTML = '<input type="checkbox" class="form-check-input" data-row-select aria-label="Select row">';
      row.insertBefore(td, row.firstChild);
    });

    var toolbar = document.createElement('div');
    toolbar.className = 'bulk-toolbar';
    toolbar.hidden = true;
    toolbar.innerHTML = '<span class="bulk-toolbar-count"></span><button type="button" class="btn btn-ghost btn-sm" data-bulk-clear>Clear selection</button>';
    wrap.parentElement.insertBefore(toolbar, wrap);

    var selectAll = headRow.querySelector('[data-select-all]');
    var countEl = toolbar.querySelector('.bulk-toolbar-count');

    function selected() { return Array.from(table.querySelectorAll('[data-row-select]:checked')); }

    function refresh() {
      var rows = selected();
      toolbar.hidden = rows.length === 0;
      countEl.textContent = rows.length + ' selected';
      var all = table.querySelectorAll('[data-row-select]');
      selectAll.checked = rows.length > 0 && rows.length === all.length;
      selectAll.indeterminate = rows.length > 0 && rows.length < all.length;
      document.dispatchEvent(new CustomEvent('hrms:bulk-selection', { detail: { wrap: wrap, count: rows.length } }));
    }

    table.addEventListener('change', function (e) {
      if (e.target.matches('[data-row-select]')) refresh();
    });
    selectAll.addEventListener('change', function () {
      table.querySelectorAll('[data-row-select]').forEach(function (cb) { cb.checked = selectAll.checked; });
      refresh();
    });
    toolbar.querySelector('[data-bulk-clear]').addEventListener('click', function () {
      table.querySelectorAll('[data-row-select]').forEach(function (cb) { cb.checked = false; });
      refresh();
    });
  }

  function bind(root) {
    root = (root && root.querySelectorAll) ? root : document;
    root.querySelectorAll('[data-density-toggle]').forEach(initDensity);
    root.querySelectorAll('[data-bulk-select]').forEach(initBulkSelect);
  }

  window.HRMSTableToolkit = { bind: bind };
  document.addEventListener('DOMContentLoaded', function () { bind(document); });
})();
