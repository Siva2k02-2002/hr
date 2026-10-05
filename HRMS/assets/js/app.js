// ---------- Toasts ----------
// Reusable, accessible toast system. window.toast(type, message) is the
// public API — type is 'success' | 'danger' | 'warning' | 'info'.
(function () {
  var ICONS = {
    success: 'check-circle',
    danger:  'x-circle',
    warning: 'triangle-alert',
    info:    'info',
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
      '<i data-lucide="' + ICONS[type] + '" class="icon" aria-hidden="true"></i>' +
      '<span class="toast-msg"></span>' +
      '<button type="button" class="toast-close" aria-label="Dismiss">&times;</button>';
    el.querySelector('.toast-msg').textContent = message;

    stack.appendChild(el);
    window.initIcons();
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

// ---------- Lucide icons ----------
// window.initIcons() re-runs the data-lucide -> <svg> replacement. Call it
// after injecting any HTML that contains new [data-lucide] elements (mirrors
// the enhanceSelects() re-init pattern already used for AJAX-loaded content).
// window.renderIcons is kept as an alias for existing call sites.
//
// A MutationObserver also watches the whole document so icons injected by
// AJAX responses, modals, drawers, or any future dynamic content render
// automatically without every call site needing to remember to call this —
// this is what keeps icons identical in Chrome and Firefox: Firefox is more
// likely to have a resource blocked by tracking protection or an extension,
// so any icon insertion path that silently depended on a manual call would
// only surface as "works in Chrome, blank in Firefox".
(function () {
  window.initIcons = function () {
    if (window.lucide && typeof lucide.createIcons === 'function') {
      lucide.createIcons();
    }
  };
  window.renderIcons = window.initIcons;
  document.addEventListener('DOMContentLoaded', window.initIcons);

  // lucide.createIcons() copies the data-lucide attribute onto the <svg> it
  // creates, so a naive "[data-lucide] was added" check would re-trigger on
  // its own output and loop forever. Only unconverted placeholders (never
  // <svg>) count as "needs init".
  function isUnrenderedIcon(el) {
    return el.tagName.toLowerCase() !== 'svg' && el.hasAttribute('data-lucide');
  }

  var scheduled = false;
  var observer = new MutationObserver(function (mutations) {
    if (scheduled) return;
    var hasNewIcon = mutations.some(function (m) {
      return Array.prototype.some.call(m.addedNodes, function (node) {
        if (node.nodeType !== 1) return false;
        if (isUnrenderedIcon(node)) return true;
        var descendants = node.querySelectorAll ? node.querySelectorAll('[data-lucide]') : [];
        return Array.prototype.some.call(descendants, isUnrenderedIcon);
      });
    });
    if (!hasNewIcon) return;
    scheduled = true;
    requestAnimationFrame(function () {
      scheduled = false;
      window.initIcons();
    });
  });

  document.addEventListener('DOMContentLoaded', function () {
    observer.observe(document.body, { childList: true, subtree: true });
  });
})();

// ---------- Page loading bar ----------
(function () {
  var bar = document.getElementById('pageLoadingBar');
  if (!bar) return;
  window.addEventListener('beforeunload', function () {
    bar.style.width = '40%';
  });
  document.addEventListener('submit', function () {
    bar.style.width = '60%';
  });

  // Exposed so spa-shell.js / live-filters.js can drive the same bar from
  // fetch lifecycle events instead of full navigation events.
  window.pageLoadingBar = {
    start: function () { bar.style.opacity = '1'; bar.style.width = '65%'; },
    done: function () {
      bar.style.width = '100%';
      setTimeout(function () { bar.style.opacity = '0'; bar.style.width = '0'; }, 200);
    },
  };
})();

// ---------- Theme toggle (dark mode) ----------
// The actual data-theme attribute is set as early as possible by an inline
// script in <head> (see layouts/main.php / auth.php) to avoid a flash of the
// wrong theme; this just wires the visible toggle + keeps localStorage and
// the icon in sync, mirroring the sidebar-collapse persistence pattern above.
(function () {
  function applyIcon(btn, theme) {
    if (!btn) return;
    btn.innerHTML = '';
    var svg = document.createElement('i');
    svg.setAttribute('data-lucide', theme === 'dark' ? 'sun' : 'moon-star');
    svg.className = 'icon';
    btn.appendChild(svg);
    window.initIcons();
  }

  document.addEventListener('DOMContentLoaded', function () {
    var btn = document.getElementById('themeToggleBtn');
    if (!btn) return;
    var current = document.documentElement.getAttribute('data-theme') === 'dark' ? 'dark' : 'light';
    applyIcon(btn, current);

    btn.addEventListener('click', function () {
      var next = document.documentElement.getAttribute('data-theme') === 'dark' ? 'light' : 'dark';
      if (next === 'dark') {
        document.documentElement.setAttribute('data-theme', 'dark');
        document.documentElement.setAttribute('data-bs-theme', 'dark');
      } else {
        document.documentElement.removeAttribute('data-theme');
        document.documentElement.removeAttribute('data-bs-theme');
      }
      try { localStorage.setItem('hrms.theme', next); } catch (e) { /* storage unavailable */ }
      applyIcon(btn, next);
    });
  });
})();

// ---------- Drawer (right slide panel) ----------
// window.openDrawer(el) / closeDrawer(el) toggle a .drawer + its
// .drawer-backdrop sibling. Drawers close on backdrop click or Escape.
(function () {
  window.openDrawer = function (drawer) {
    if (!drawer) return;
    var backdrop = drawer.previousElementSibling && drawer.previousElementSibling.classList.contains('drawer-backdrop')
      ? drawer.previousElementSibling
      : document.querySelector('[data-drawer-backdrop-for="' + drawer.id + '"]');
    drawer.classList.add('open');
    if (backdrop) backdrop.classList.add('open');
    document.body.style.overflow = 'hidden';
    window.renderIcons();
  };

  window.closeDrawer = function (drawer) {
    if (!drawer) return;
    var backdrop = drawer.previousElementSibling && drawer.previousElementSibling.classList.contains('drawer-backdrop')
      ? drawer.previousElementSibling
      : document.querySelector('[data-drawer-backdrop-for="' + drawer.id + '"]');
    drawer.classList.remove('open');
    if (backdrop) backdrop.classList.remove('open');
    document.body.style.overflow = '';
  };

  document.addEventListener('click', function (e) {
    var openBtn = e.target.closest('[data-drawer-target]');
    if (openBtn) {
      var target = document.getElementById(openBtn.dataset.drawerTarget);
      if (target) window.openDrawer(target);
      return;
    }
    var closeBtn = e.target.closest('[data-drawer-close]');
    if (closeBtn) {
      window.closeDrawer(closeBtn.closest('.drawer'));
      return;
    }
    if (e.target.classList.contains('drawer-backdrop')) {
      var openDrawerEl = document.querySelector('.drawer.open');
      if (openDrawerEl) window.closeDrawer(openDrawerEl);
    }
  });

  document.addEventListener('keydown', function (e) {
    if (e.key !== 'Escape') return;
    var openDrawerEl = document.querySelector('.drawer.open');
    if (openDrawerEl) window.closeDrawer(openDrawerEl);
  });
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

  // ---------- Sidebar collapse (desktop) — persisted per-browser ----------
  var collapseBtn = document.querySelector('.sidebar-collapse-btn');
  if (sidebar && localStorage.getItem('hrms.sidebarCollapsed') === '1') {
    sidebar.classList.add('is-collapsed');
  }
  if (collapseBtn) {
    collapseBtn.addEventListener('click', function () {
      var collapsed = sidebar.classList.toggle('is-collapsed');
      try { localStorage.setItem('hrms.sidebarCollapsed', collapsed ? '1' : '0'); } catch (e) { /* storage unavailable */ }
      // Toggling collapse doesn't fire mouseout on whatever link is under the
      // cursor, so an open hover tooltip (see below) would otherwise stick
      // around stale after expanding.
      document.querySelectorAll('.sidebar-hover-tip').forEach(function (t) { t.remove(); });
    });
  }

  // ---------- Collapsed-sidebar hover label ----------
  // sidebar.css already had a content:attr(data-label) tooltip on
  // .sidebar-link:hover::after, but .sidebar/.sidebar-nav both need
  // overflow:hidden for their own scroll/width-transition behavior, which
  // clips that ::after before it ever becomes visible — so it never showed.
  // This portals the same label (still read from the link's own data-label,
  // never a second hardcoded list) to <body>, positioned from the hovered
  // link's live bounding box, which escapes that clipping entirely.
  if (sidebar) {
    var navTip = null;

    function hideNavTip() {
      if (navTip) { navTip.remove(); navTip = null; }
    }

    function showNavTip(link) {
      if (!sidebar.classList.contains('is-collapsed')) return;
      var labelEl = link.querySelector('.label');
      // Below the mobile breakpoint .is-collapsed no longer hides the label
      // (sidebar.css), so the name is already visible — no tooltip needed.
      if (labelEl && getComputedStyle(labelEl).display !== 'none') return;
      var label = link.dataset.label;
      if (!label) return;

      hideNavTip();
      navTip = document.createElement('div');
      navTip.className = 'sidebar-hover-tip';
      navTip.textContent = label;
      navTip.setAttribute('role', 'tooltip');
      document.body.appendChild(navTip);

      var rect = link.getBoundingClientRect();
      navTip.style.top = (rect.top + rect.height / 2) + 'px';
      navTip.style.left = (rect.right + 10) + 'px';
    }

    // Delegated on .sidebar (never replaced — only .sidebar-nav's innerHTML
    // is swapped by spa-shell.js) so this keeps working after SPA navigation
    // without needing to re-bind per-link listeners on every swap.
    sidebar.addEventListener('mouseover', function (e) {
      if (window.matchMedia && !window.matchMedia('(hover: hover)').matches) return;
      var link = e.target.closest('.sidebar-link');
      if (link) showNavTip(link);
    });
    sidebar.addEventListener('mouseout', function (e) {
      var link = e.target.closest('.sidebar-link');
      if (link && (!e.relatedTarget || !link.contains(e.relatedTarget))) hideNavTip();
    });
    sidebar.addEventListener('mousedown', hideNavTip);
  }

  // ---------- Sidebar scroll position — persisted across full-page navigation ----------
  // The sidebar reloads with every page (no SPA), so scrollTop is saved just
  // before the browser navigates away and restored on the next load.
  var sidebarNav = sidebar && sidebar.querySelector('.sidebar-nav');
  if (sidebarNav) {
    var SIDEBAR_SCROLL_KEY = 'hrms.sidebarScrollTop';

    var saveSidebarScroll = function () {
      try { sessionStorage.setItem(SIDEBAR_SCROLL_KEY, sidebarNav.scrollTop); } catch (e) { /* storage unavailable */ }
    };

    // Must run again after the Favorites/Recent sections are rendered (see the
    // favorites IIFE below): they are filled in by JS and add height above the
    // menu, so a position restored before that point lands on the wrong item.
    var restoreSidebarScroll = function () {
      try {
        var savedSidebarScroll = sessionStorage.getItem(SIDEBAR_SCROLL_KEY);
        if (savedSidebarScroll !== null) {
          sidebarNav.scrollTop = parseInt(savedSidebarScroll, 10) || 0;
        }
      } catch (e) { /* storage unavailable */ }

      // If the active link still isn't visible (e.g. first visit, or navigation
      // via a link outside the sidebar), bring it into view — instantly, so the
      // menu doesn't visibly slide on every page load.
      var activeSidebarLink = sidebarNav.querySelector('.sidebar-link.active');
      if (activeSidebarLink) {
        // getBoundingClientRect, not offsetTop: .sidebar-nav isn't a positioned
        // element, so offsetTop would be measured from a different ancestor.
        var navRect  = sidebarNav.getBoundingClientRect();
        var linkRect = activeSidebarLink.getBoundingClientRect();
        if (linkRect.top < navRect.top || linkRect.bottom > navRect.bottom) {
          var offsetInNav = linkRect.top - navRect.top + sidebarNav.scrollTop;
          sidebarNav.scrollTop = Math.max(0, offsetInNav - (sidebarNav.clientHeight - linkRect.height) / 2);
        }
      }
    };
    window.restoreSidebarScroll = restoreSidebarScroll;
    restoreSidebarScroll();

    sidebarNav.addEventListener('click', function (e) {
      if (e.target.closest('.sidebar-link')) saveSidebarScroll();
    });
    window.addEventListener('beforeunload', saveSidebarScroll);
  }

  // ---------- Sidebar menu search (client-side filter) ----------
  var sidebarSearch = document.querySelector('.sidebar-search input');
  if (sidebarSearch) {
    sidebarSearch.addEventListener('input', function () {
      var q = sidebarSearch.value.trim().toLowerCase();
      document.querySelectorAll('.sidebar-nav .sidebar-link').forEach(function (link) {
        var label = (link.dataset.label || link.textContent || '').toLowerCase();
        link.style.display = (q === '' || label.indexOf(q) !== -1) ? '' : 'none';
      });
      document.querySelectorAll('.sidebar-nav .sidebar-group-label').forEach(function (heading) {
        var group = heading.nextElementSibling;
        var anyVisible = false;
        while (group && group.classList && group.classList.contains('sidebar-link')) {
          if (group.style.display !== 'none') anyVisible = true;
          group = group.nextElementSibling;
        }
        heading.style.display = (q === '' || anyVisible) ? '' : 'none';
      });
    });
  }

  // ---------- Sidebar favorites + recent pages (client-only, no schema change) ----------
  (function () {
    var FAV_KEY = 'hrms.sidebarFavorites';
    var RECENT_KEY = 'hrms.sidebarRecent';
    var RECENT_MAX = 5;
    var favSection = document.getElementById('sidebarFavorites');
    var recentSection = document.getElementById('sidebarRecent');
    if (!sidebar || !sidebarNav) return;

    function readFavorites() {
      try { return JSON.parse(localStorage.getItem(FAV_KEY) || '[]'); } catch (e) { return []; }
    }
    function writeFavorites(routes) {
      try { localStorage.setItem(FAV_KEY, JSON.stringify(routes)); } catch (e) { /* storage unavailable */ }
    }
    function readRecent() {
      try { return JSON.parse(localStorage.getItem(RECENT_KEY) || '[]'); } catch (e) { return []; }
    }
    function writeRecent(routes) {
      try { localStorage.setItem(RECENT_KEY, JSON.stringify(routes)); } catch (e) { /* storage unavailable */ }
    }

    function buildQuickLink(sourceLink) {
      var clone = sourceLink.cloneNode(true);
      var toggle = clone.querySelector('[data-favorite-toggle]');
      if (toggle) toggle.remove();
      // Cloning copies the .active class verbatim — if the current page's
      // real nav item is active, its Favorites/Recent shortcut would be too,
      // showing two highlighted pills for the same page at once. Only the
      // real nav item should ever carry the active indicator.
      clone.classList.remove('active');
      return clone;
    }

    function render() {
      var favorites = readFavorites();
      var recent = readRecent().filter(function (r) { return favorites.indexOf(r) === -1; }).slice(0, RECENT_MAX);

      if (favSection) {
        favSection.querySelectorAll('.sidebar-link').forEach(function (el) { el.remove(); });
        favorites.forEach(function (route) {
          var source = sidebarNav.querySelector('.sidebar-link[data-route="' + CSS.escape(route) + '"]');
          if (source && !favSection.contains(source)) favSection.appendChild(buildQuickLink(source));
        });
        favSection.hidden = favorites.length === 0;
      }

      if (recentSection) {
        recentSection.querySelectorAll('.sidebar-link').forEach(function (el) { el.remove(); });
        recent.forEach(function (route) {
          var source = sidebarNav.querySelector('.sidebar-link[data-route="' + CSS.escape(route) + '"]');
          if (source) recentSection.appendChild(buildQuickLink(source));
        });
        recentSection.hidden = recent.length === 0;
      }

      document.querySelectorAll('.sidebar-nav .sidebar-link[data-route] [data-favorite-toggle]').forEach(function (btn) {
        var route = btn.closest('.sidebar-link').dataset.route;
        btn.classList.toggle('is-favorite', favorites.indexOf(route) !== -1);
      });
      window.initIcons();
    }

    document.addEventListener('click', function (e) {
      var toggle = e.target.closest('[data-favorite-toggle]');
      if (!toggle) return;
      e.preventDefault();
      e.stopPropagation();
      var link = toggle.closest('.sidebar-link');
      var route = link && link.dataset.route;
      if (!route) return;
      var favorites = readFavorites();
      var idx = favorites.indexOf(route);
      if (idx === -1) favorites.push(route); else favorites.splice(idx, 1);
      writeFavorites(favorites);
      render();
    });

    // Track the page just navigated to as "recent" — called on load and,
    // once spa-shell.js exists, after every client-side navigation too.
    window.trackRecentPage = function (route) {
      if (!route) return;
      var recent = readRecent().filter(function (r) { return r !== route; });
      recent.unshift(route);
      writeRecent(recent.slice(0, RECENT_MAX));
      render();
    };

    // spa-shell.js only replaces .sidebar-nav's *innerHTML* (the container
    // itself, and sidebarNav above, stay the same node) — but that still
    // destroys and recreates #sidebarFavorites/#sidebarRecent, so favSection/
    // recentSection above go stale and render() silently repaints detached
    // nodes nobody sees. Exposed so spa-shell.js can re-query them and
    // re-render into the live nodes after every content swap.
    window.HRMSSidebarQuick = {
      refresh: function () {
        favSection = sidebarNav.querySelector('#sidebarFavorites');
        recentSection = sidebarNav.querySelector('#sidebarRecent');
        var activeLink = sidebarNav.querySelector('.sidebar-link.active[data-route]');
        if (activeLink) window.trackRecentPage(activeLink.dataset.route); else render();
      },
    };

    var activeLink = sidebarNav.querySelector('.sidebar-link.active[data-route]');
    if (activeLink) window.trackRecentPage(activeLink.dataset.route);
    render();
    if (window.restoreSidebarScroll) window.restoreSidebarScroll();
  })();

  // ---------- Confirmation modal (replaces window.confirm) ----------
  // Delegated on `document`, not bound per-form: profile tabs (see
  // employees/profile.php) inject whole forms via fetch()+innerHTML after
  // this listener is attached, so per-element binding would silently miss
  // every delete button in a dynamically-loaded tab.
  var confirmModalEl = document.getElementById('confirmModal');
  var confirmModal = (confirmModalEl && window.bootstrap) ? new bootstrap.Modal(confirmModalEl) : null;
  var pendingForm = null;
  var pendingCallback = null;

  // Generic entry point for anything that needs a "are you sure?" step but
  // isn't a form submission (e.g. the drawer's unsaved-changes guard,
  // bulk-action toolbar). Falls straight through to onAccept if the modal
  // markup isn't available for some reason, same fallback the form path uses.
  window.confirmAction = function (opts, onAccept) {
    opts = opts || {};
    if (!confirmModal) { onAccept(); return; }
    pendingForm = null;
    pendingCallback = onAccept;
    document.getElementById('confirmModalTitle').textContent = opts.title || 'Are you sure?';
    document.getElementById('confirmModalBody').textContent = opts.message || '';
    var btn = document.getElementById('confirmModalAccept');
    btn.className = 'btn btn-sm ' + (opts.variant || 'btn-danger');
    btn.textContent = opts.label || 'Confirm';
    confirmModal.show();
  };

  document.addEventListener('submit', function (e) {
    var form = e.target.closest('form[data-confirm]');
    if (!form) return;
    if (form.dataset.confirmed === '1') return; // user already accepted, let it submit
    if (!confirmModal) return; // graceful fallback: submit normally if modal unavailable

    e.preventDefault();
    pendingForm = form;
    pendingCallback = null;
    document.getElementById('confirmModalTitle').textContent = form.dataset.confirmTitle || 'Are you sure?';
    document.getElementById('confirmModalBody').textContent = form.getAttribute('data-confirm');
    var acceptBtn = document.getElementById('confirmModalAccept');
    acceptBtn.className = 'btn btn-sm ' + (form.dataset.confirmVariant || 'btn-danger');
    acceptBtn.textContent = form.dataset.confirmLabel || 'Confirm';
    confirmModal.show();
  });

  var acceptBtn = document.getElementById('confirmModalAccept');
  if (acceptBtn) {
    acceptBtn.addEventListener('click', function () {
      confirmModal.hide();
      if (pendingForm) {
        pendingForm.dataset.confirmed = '1';
        pendingForm.requestSubmit ? pendingForm.requestSubmit() : pendingForm.submit();
        pendingForm = null;
      } else if (pendingCallback) {
        var cb = pendingCallback;
        pendingCallback = null;
        cb();
      }
    });
  }

  // Disable submit buttons on submit to prevent double-submission. Also
  // delegated, for the same dynamically-loaded-form reason as above.
  document.addEventListener('submit', function (e) {
    var form = e.target;
    if (!(form instanceof HTMLFormElement)) return;
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

  // ---------- "Same as Permanent" address checkbox (employees/_tab_personal.php) ----------
  // Delegated for the same reason as the confirm-modal listeners above: this
  // checkbox is loaded into the DOM later via the profile page's tab fetch().
  document.addEventListener('change', function (e) {
    if (e.target.id !== 'sameAsPermanent' || ! e.target.checked) { return; }
    document.querySelectorAll('.current-field').forEach(function (el) {
      var source = document.querySelector('[name="permanent[' + el.dataset.field + ']"]');
      if (source) { el.value = source.value; }
    });
  });

  // ---------- Searchable selects — every <select> in the app, except ones a
  // page's own <script> already wired up for AJAX (data-ajax-select) — e.g.
  // the Reporting Manager / Manager pickers, which search a live endpoint
  // instead of listing every option inline. ----------
  window.HRMSSelect2Init(document);

  // Any Bootstrap modal (confirm modal excluded — it has no selects) may
  // contain selects that were hidden (display:none) at DOMContentLoaded
  // time — Select2 measures the trigger element's width on init, so an
  // element hidden by a collapsed/inactive tab or an unopened modal can
  // still be safely initialized (Select2 handles zero-width gracefully),
  // but re-running the init on open is a cheap, correct safety net for any
  // modal whose body gets swapped in dynamically without going through
  // spa-shell's reinitInjected.
  document.addEventListener('shown.bs.modal', function (e) {
    window.HRMSSelect2Init(e.target);
  });
});

// ---------- Select2: single idempotent entry point for the whole app ----------
// This used to be split across app.js, spa-shell.js, drawer-forms.js and
// ~14 page templates that each called `.select2()` by hand for a remote
// (AJAX-backed) field such as the employee picker. Two things made that
// dangerous:
//   1. Select2's jQuery plugin has no "already initialized?" guard — every
//      call to .select2() builds a brand new container and rewires events,
//      even on a <select> that already has one. Any code path that ran
//      twice over the same element (a page's own inline init + this file's
//      DOMContentLoaded pass, or a live-filter/AJAX refresh re-running init
//      over markup that was never actually replaced) left two live
//      instances fighting over one field — which is what actually produced
//      the "search does nothing / dropdown closes while typing / loses
//      focus" symptoms. Not a Select2 or browser bug — just double init.
//   2. Every page duplicating the same ajax/dataType/processResults
//      boilerplate meant fixing or auditing that behavior meant touching
//      14+ files instead of one.
// Now every [data-ajax-select] that wants a remote data source declares it
// declaratively — data-ajax-url (required to opt into AJAX mode; a
// [data-ajax-select] with no URL is assumed to still be wired up by a
// page's own bespoke script and is left alone), data-ajax-exclude / other
// data-ajax-* params forwarded verbatim as query params, and data-placeholder
// — and this one function handles both that and every plain select in the
// app. Checking `.data('select2')` first makes it safe to call from
// anywhere (DOMContentLoaded below, spa-shell.js after an AJAX nav swap,
// live-filters.js after a filter refresh, drawer-forms.js, employees/
// profile.php's tab loader, a modal's `shown.bs.modal`) with zero risk of
// double-initializing something that's already live.
window.HRMSSelect2Init = function (root) {
  if (!(window.jQuery && jQuery.fn.select2)) return;
  var $root = jQuery(root || document);

  function dropdownParentFor($el) {
    var modal = $el.closest('.modal');
    if (modal.length) return modal;
    var drawer = $el.closest('.drawer');
    if (drawer.length) return drawer;
    return undefined;
  }

  // Remote (AJAX) selects: opt in with data-ajax-select + data-ajax-url.
  $root.find('select[data-ajax-select]').each(function () {
    var $el = jQuery(this);
    if ($el.data('select2')) return;
    var url = $el.attr('data-ajax-url');
    if (!url) return; // no URL declared — a page script still owns this one

    // Any data-ajax-xxx attribute besides data-ajax-url/-select is forwarded
    // as a static query param alongside the live search term, e.g.
    // data-ajax-exclude="42" -> { q: term, exclude: '42' }.
    var extraParams = {};
    Array.prototype.forEach.call(this.attributes, function (attr) {
      var m = /^data-ajax-(.+)$/.exec(attr.name);
      if (!m || m[1] === 'url' || m[1] === 'select') return;
      var key = m[1].replace(/-([a-z])/g, function (_, c) { return c.toUpperCase(); });
      extraParams[key] = attr.value;
    });

    $el.select2({
      theme: 'bootstrap-5',
      width: 'resolve',
      minimumInputLength: 0,
      placeholder: $el.attr('data-placeholder') || 'Search…',
      allowClear: $el.attr('data-allow-clear') === 'true',
      dropdownParent: dropdownParentFor($el),
      ajax: {
        url: url,
        dataType: 'json',
        delay: 250,
        data: function (params) {
          var query = { q: params.term };
          for (var k in extraParams) query[k] = extraParams[k];
          return query;
        },
        processResults: function (data) { return { results: data.results }; },
      },
    });
  });

  // Plain (non-AJAX) selects: every other <select> in the app. A page can
  // still opt into a placeholder/clear button declaratively instead of a
  // bespoke script, e.g. <select data-placeholder="No shift assigned"
  // data-allow-clear="true"> (needs an empty <option value=""> to anchor
  // the placeholder, same requirement Select2 has always had).
  // data-no-select2 opts a select out entirely — for one a page shows/hides
  // itself via plain style.display toggling (e.g. a conditional field that
  // starts hidden): Select2 measures width at init time, so initializing it
  // while the element is display:none produces a 0-width/mis-sized widget,
  // and its own container then ignores the page's manual display toggling.
  $root.find('select:not([data-ajax-select]):not([data-no-select2])').each(function () {
    var $el = jQuery(this);
    if ($el.data('select2')) return;
    var minSearch = $el.attr('data-min-search');
    $el.select2({
      theme: 'bootstrap-5',
      width: 'resolve',
      minimumResultsForSearch: minSearch ? Number(minSearch) : 0,
      placeholder: $el.attr('data-placeholder') || undefined,
      allowClear: $el.attr('data-allow-clear') === 'true',
      dropdownParent: dropdownParentFor($el),
    });
  });
};
