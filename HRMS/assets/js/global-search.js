// ---------- Global search / command palette (Ctrl+K) ----------
// Three result groups:
//  - Pages: fuzzy-matched client-side against the already-rendered sidebar
//    menu (no endpoint — the menu is already permission-filtered server-side
//    by MenuBuilder, so this can't surface anything the user can't open).
//  - Employees / Users: debounced calls to the existing
//    api/employees/search and api/users/search endpoints (already used by
//    the manager/head-of-department select2 pickers elsewhere in the app).
// Record-level search for Leave/Payroll/Attendance/Reports/Settings is not
// included here — no backend search endpoint exists for those modules yet.
(function () {
  var backdrop = document.getElementById('commandPaletteBackdrop');
  var palette = document.getElementById('commandPalette');
  var input = document.getElementById('commandPaletteInput');
  var resultsEl = document.getElementById('commandPaletteResults');
  var scriptTag = document.querySelector('script[src*="global-search.js"]');
  if (!palette || !input || !resultsEl) return;

  var employeesUrl = scriptTag ? scriptTag.getAttribute('data-employees-url') : null;
  var usersUrl = scriptTag ? scriptTag.getAttribute('data-users-url') : null;
  var RECENT_KEY = 'hrms.searchRecent';
  var RECENT_MAX = 6;
  var debounceTimer = null;
  var activeIndex = -1;

  function readRecent() {
    try { return JSON.parse(localStorage.getItem(RECENT_KEY) || '[]'); } catch (e) { return []; }
  }
  function pushRecent(entry) {
    var list = readRecent().filter(function (r) { return r.url !== entry.url; });
    list.unshift(entry);
    try { localStorage.setItem(RECENT_KEY, JSON.stringify(list.slice(0, RECENT_MAX))); } catch (e) { /* storage unavailable */ }
  }
  function clearRecent() {
    try { localStorage.removeItem(RECENT_KEY); } catch (e) { /* storage unavailable */ }
  }

  function pageEntries() {
    return Array.from(document.querySelectorAll('.sidebar-nav .sidebar-link[data-route]')).map(function (a) {
      return { type: 'Page', label: a.querySelector('.label') ? a.querySelector('.label').textContent.trim() : a.textContent.trim(), url: a.href };
    });
  }

  function open() {
    palette.classList.add('open');
    backdrop.classList.add('open');
    document.body.style.overflow = 'hidden';
    input.value = '';
    renderIdle();
    setTimeout(function () { input.focus(); }, 10);
  }
  function close() {
    palette.classList.remove('open');
    backdrop.classList.remove('open');
    document.body.style.overflow = '';
  }

  function highlight(text, q) {
    if (!q) return escapeHtml(text);
    var idx = text.toLowerCase().indexOf(q.toLowerCase());
    if (idx === -1) return escapeHtml(text);
    return escapeHtml(text.slice(0, idx)) + '<mark>' + escapeHtml(text.slice(idx, idx + q.length)) + '</mark>' + escapeHtml(text.slice(idx + q.length));
  }
  function escapeHtml(s) {
    return String(s).replace(/[&<>"']/g, function (c) {
      return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c];
    });
  }

  function renderGroups(groups) {
    activeIndex = -1;
    if (!groups.length) {
      resultsEl.innerHTML = '<div class="empty-state is-compact"><p class="mb-0">No matches.</p></div>';
      return;
    }
    resultsEl.innerHTML = groups.map(function (g) {
      var items = g.items.map(function (item, i) {
        return '<a href="' + escapeHtml(item.url) + '" class="command-palette-item" data-cp-item data-cp-url="' + escapeHtml(item.url) + '" data-cp-label="' + escapeHtml(item.rawLabel) + '" data-cp-type="' + escapeHtml(item.type) + '">' +
          '<span class="command-palette-item-label">' + item.label + '</span>' +
          '<span class="command-palette-item-type">' + escapeHtml(item.type) + '</span></a>';
      }).join('');
      return '<div class="command-palette-group"><p class="command-palette-group-label">' + escapeHtml(g.label) + '</p>' + items + '</div>';
    }).join('');
    window.initIcons();
  }

  function renderIdle() {
    var recent = readRecent();
    if (!recent.length) {
      resultsEl.innerHTML = '<div class="empty-state is-compact"><p class="mb-0">Start typing to search pages, employees, or users.</p></div>';
      return;
    }
    renderGroups([{
      label: 'Recent',
      items: recent.map(function (r) { return { label: escapeHtml(r.label), rawLabel: r.label, type: r.type, url: r.url }; }),
    }]);
    var clearBtn = document.createElement('button');
    clearBtn.type = 'button';
    clearBtn.className = 'filters-clear';
    clearBtn.style.margin = '.4rem .9rem';
    clearBtn.textContent = 'Clear recent searches';
    clearBtn.addEventListener('click', function () { clearRecent(); renderIdle(); });
    resultsEl.appendChild(clearBtn);
  }

  function runSearch(q) {
    var pageMatches = pageEntries()
      .filter(function (p) { return p.label.toLowerCase().indexOf(q.toLowerCase()) !== -1; })
      .slice(0, 6)
      .map(function (p) { return { label: highlight(p.label, q), rawLabel: p.label, type: 'Page', url: p.url }; });

    var groups = [];
    if (pageMatches.length) groups.push({ label: 'Pages', items: pageMatches });
    renderGroups(groups);

    if (employeesUrl) {
      fetch(employeesUrl + '?q=' + encodeURIComponent(q), { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
        .then(function (r) { return r.ok ? r.json() : { results: [] }; })
        .then(function (data) {
          if (input.value.trim() !== q) return; // stale response
          var items = (data.results || []).slice(0, 6).map(function (r) {
            return { label: highlight(r.text, q), rawLabel: r.text, type: 'Employee', url: siteUrl('employees/' + r.id) };
          });
          upsertGroup(groups, 'Employees', items);
        }).catch(function () { /* search endpoint unavailable — page results still stand */ });
    }
    if (usersUrl) {
      fetch(usersUrl + '?q=' + encodeURIComponent(q), { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
        .then(function (r) { return r.ok ? r.json() : { results: [] }; })
        .then(function (data) {
          if (input.value.trim() !== q) return;
          var items = (data.results || []).slice(0, 6).map(function (r) {
            return { label: highlight(r.text, q), rawLabel: r.text, type: 'User', url: siteUrl('users/' + r.id) };
          });
          upsertGroup(groups, 'Users', items);
        }).catch(function () { /* search endpoint unavailable — page results still stand */ });
    }
  }

  function siteBase() {
    if (employeesUrl) return employeesUrl.replace(/api\/employees\/search\/?$/, '');
    if (usersUrl) return usersUrl.replace(/api\/users\/search\/?$/, '');
    return window.location.origin + '/';
  }
  function siteUrl(path) {
    return siteBase() + path.replace(/^\//, '');
  }

  function upsertGroup(groups, label, items) {
    if (!items.length) return;
    var existing = groups.filter(function (g) { return g.label !== label; });
    existing.push({ label: label, items: items });
    renderGroups(existing);
  }

  input.addEventListener('input', function () {
    var q = input.value.trim();
    clearTimeout(debounceTimer);
    if (q === '') { renderIdle(); return; }
    debounceTimer = setTimeout(function () { runSearch(q); }, 250);
  });

  resultsEl.addEventListener('click', function (e) {
    var item = e.target.closest('[data-cp-item]');
    if (!item) return;
    pushRecent({ label: item.getAttribute('data-cp-label'), type: item.getAttribute('data-cp-type'), url: item.getAttribute('data-cp-url') });
  });

  input.addEventListener('keydown', function (e) {
    var items = Array.from(resultsEl.querySelectorAll('[data-cp-item]'));
    if (e.key === 'ArrowDown') {
      e.preventDefault();
      activeIndex = Math.min(activeIndex + 1, items.length - 1);
      items.forEach(function (it, i) { it.classList.toggle('is-active', i === activeIndex); });
      if (items[activeIndex]) items[activeIndex].scrollIntoView({ block: 'nearest' });
    } else if (e.key === 'ArrowUp') {
      e.preventDefault();
      activeIndex = Math.max(activeIndex - 1, 0);
      items.forEach(function (it, i) { it.classList.toggle('is-active', i === activeIndex); });
      if (items[activeIndex]) items[activeIndex].scrollIntoView({ block: 'nearest' });
    } else if (e.key === 'Enter' && activeIndex >= 0 && items[activeIndex]) {
      e.preventDefault();
      items[activeIndex].click();
    }
  });

  document.addEventListener('click', function (e) {
    if (e.target.closest('[data-global-search-trigger]')) {
      e.preventDefault();
      open();
    }
  });
  if (backdrop) backdrop.addEventListener('click', close);
  document.addEventListener('keydown', function (e) {
    if ((e.ctrlKey || e.metaKey) && e.key.toLowerCase() === 'k') {
      e.preventDefault();
      palette.classList.contains('open') ? close() : open();
    } else if (e.key === 'Escape' && palette.classList.contains('open')) {
      close();
    }
  });
})();
