// ---------- Live filters (no "Apply" button) ----------
// A `.filters-bar[data-live-key="x"]` form and a `[data-live-region="x"]`
// container are paired by that shared key. Any change inside the form
// (debounced 300ms) re-fetches the *same* URL the server has always
// rendered for that GET request — via HRMSShell.fetchDoc, the exact
// fetch()+DOMParser helper spa-shell.js exposes — and swaps only the region
// markup. The controller's applyFilters()/paginate() calls never change:
// this is purely "don't throw away and re-render the whole page for a
// result set the server already knows how to produce."
//
// History strategy (hybrid):
//   - Discrete filter changes (select/date/checkbox), pagination clicks and
//     the pagination "jump to page" form each get their own history entry
//     via pushState, so Back/Forward step through them one at a time.
//   - Free text search fields debounce on `input`: every keystroke-driven
//     refresh while the field is still "live" uses replaceState (so the URL
//     stays in sync without flooding history), and only commits a pushState
//     entry once the field settles — on blur, or immediately if the value
//     didn't actually change since the last pushed state.
//   - window.HRMSLiveFilters.handlePopState() is called by spa-shell.js's
//     popstate listener before it falls back to a full `.content` swap: if
//     the popped URL belongs to the same list page (a filter/pagination
//     change), it restores the form fields (including Select2) from the
//     freshly fetched document and refreshes only the live region — no full
//     page reload, no full `.content` replace.
(function () {
  var DEBOUNCE_MS = 300;
  var debounceTimers = new WeakMap();

  function cssEscape(v) {
    return (window.CSS && CSS.escape) ? CSS.escape(v) : String(v).replace(/([^a-zA-Z0-9_-])/g, '\\$1');
  }

  function absUrl(url) {
    try { return new URL(url, window.location.href).href; } catch (e) { return url; }
  }

  function buildUrl(form) {
    var params = new URLSearchParams(new FormData(form));
    Array.from(params.keys()).forEach(function (k) {
      if (params.get(k) === '') params.delete(k);
    });
    var base = form.getAttribute('action') || window.location.pathname;
    var qs = params.toString();
    return qs ? (base + '?' + qs) : base;
  }

  // Select2 (initialized on *every* <select> in the app — see
  // HRMSSelect2Init in app.js) never fires a real DOM change event: on
  // selection it does `this.$element.trigger('change')`, and jQuery's
  // trigger() only calls a native `elem[type]()` shortcut for events that
  // have one (click/submit/focus/blur) — there is no `elem.change()`, so a
  // synthetic jQuery 'change' never reaches a plain
  // `addEventListener('change', ...)` on that element. Since the native
  // <select> is hidden behind the Select2 widget, the user never generates
  // a real native change event either — so a `<select>` filter must be
  // bound through jQuery's own event system to ever see the change at all.
  // Plain (non-select2) fields — date/checkbox inputs — still get real
  // native events and work with addEventListener either way.
  function bindChangeEvent(field, handler) {
    if (field.tagName === 'SELECT' && window.jQuery) {
      jQuery(field).on('change', handler);
    } else {
      field.addEventListener('change', handler);
    }
  }

  function findRegionFor(form) {
    var key = form.getAttribute('data-live-key');
    if (!key) return null;
    return document.querySelector('[data-live-region="' + cssEscape(key) + '"]');
  }

  // Active-filter count badge + auto-hide "Clear" — built entirely client-side
  // next to whatever [data-clear-filters] element the page already has, so
  // every converted list page gets this with zero per-page markup changes.
  function updateFilterCount(form) {
    var clearEl = form.querySelector('[data-clear-filters]');
    if (!clearEl) return;

    var count = 0;
    form.querySelectorAll('input, select').forEach(function (field) {
      if (field.type === 'submit' || field.type === 'button' || field.type === 'hidden') return;
      if (field.value && field.value.trim() !== '') count++;
    });

    clearEl.hidden = count === 0;
    var badge = clearEl.querySelector('.filter-count-badge');
    if (count > 0) {
      if (!badge) {
        badge = document.createElement('span');
        badge.className = 'filter-count-badge';
        clearEl.appendChild(badge);
      }
      badge.textContent = String(count);
    } else if (badge) {
      badge.remove();
    }
  }

  // Copies field values (including Select2 selections) from the freshly
  // fetched document's matching filter form into the live one — used after a
  // popstate restore so the visible filter bar matches the URL being
  // navigated to, without needing to hand-parse query params per field type.
  function syncFormFromDoc(form, doc) {
    var key = form.getAttribute('data-live-key');
    var newForm = doc.querySelector('.filters-bar[data-live-key="' + cssEscape(key) + '"]');
    if (!newForm) return;

    form.querySelectorAll('input, select').forEach(function (field) {
      if (!field.name || field.type === 'submit' || field.type === 'button') return;
      var newField = newForm.querySelector('[name="' + cssEscape(field.name) + '"]');
      if (!newField) return;

      if (field.type === 'checkbox' || field.type === 'radio') {
        field.checked = !!newField.checked;
      } else {
        field.value = newField.value;
      }

      if (field.tagName === 'SELECT' && window.jQuery && jQuery.fn.select2 && jQuery(field).data('select2')) {
        jQuery(field).val(field.value).trigger('change.select2');
      }
    });

    updateFilterCount(form);
  }

  // historyMode: 'push' (real filter/pagination change) | 'replace'
  // (intermediate debounced typing) | null/omitted (popstate restore — the
  // browser already moved history, so we must not touch it again).
  function fetchAndSwap(region, url, historyMode, formToSync) {
    if (!window.HRMSShell) { window.location.href = url; return; }

    region.dataset.lastLiveUrl = absUrl(url);

    var tbody = region.querySelector('tbody');
    if (tbody) {
      var cols = tbody.closest('table') ? tbody.closest('table').querySelectorAll('thead th').length : 4;
      window.skeletonRows(tbody, Math.max(tbody.children.length, 3), cols);
    }
    window.startLoading(region);

    window.HRMSShell.fetchDoc(url).then(function (doc) {
      var key = region.getAttribute('data-live-region');
      var newRegion = doc.querySelector('[data-live-region="' + cssEscape(key) + '"]');
      window.stopLoading(region);
      if (!newRegion) { window.location.href = url; return; }
      region.innerHTML = newRegion.innerHTML;
      window.HRMSShell.reinitInjected(region);
      bindRegionInteractions(region);
      if (formToSync) syncFormFromDoc(formToSync, doc);
      if (historyMode === 'push') history.pushState({ hrmsShell: true }, '', url);
      else if (historyMode === 'replace') history.replaceState({ hrmsShell: true }, '', url);
    }).catch(function () {
      window.stopLoading(region);
      window.location.href = url;
    });
  }

  function bindFilterForm(form) {
    if (form.dataset.liveBound === '1') return;
    form.dataset.liveBound = '1';

    var region = findRegionFor(form);
    if (!region) return;
    region.dataset.lastLiveUrl = absUrl(window.location.href);

    // Still degrades to a normal GET submission if JS is unavailable; while
    // JS is live, submit (e.g. hitting Enter) commits immediately as a real
    // history entry instead of a full reload.
    form.addEventListener('submit', function (e) {
      e.preventDefault();
      clearTimeout(debounceTimers.get(form));
      fetchAndSwap(region, buildUrl(form), 'push');
    });

    // Free-text fields: debounce on every keystroke, replacing the current
    // history entry so the URL stays live without spamming Back/Forward.
    function scheduleTypingRefresh() {
      updateFilterCount(form);
      clearTimeout(debounceTimers.get(form));
      var t = setTimeout(function () {
        var url = buildUrl(form);
        if (region.dataset.lastLiveUrl === absUrl(url)) return;
        fetchAndSwap(region, url, 'replace');
      }, DEBOUNCE_MS);
      debounceTimers.set(form, t);
    }

    // Promote the in-progress typing state to a real, navigable history
    // entry once the field settles (loses focus).
    function commitTypingFilter() {
      clearTimeout(debounceTimers.get(form));
      var url = buildUrl(form);
      if (region.dataset.lastLiveUrl === absUrl(url)) {
        if (absUrl(window.location.href) !== absUrl(url)) history.pushState({ hrmsShell: true }, '', url);
        return;
      }
      fetchAndSwap(region, url, 'push');
    }

    // Discrete fields (select / date / checkbox): each change is its own
    // real filter change, so it gets its own pushState entry immediately.
    function commitDiscreteChange() {
      updateFilterCount(form);
      clearTimeout(debounceTimers.get(form));
      var url = buildUrl(form);
      if (region.dataset.lastLiveUrl === absUrl(url)) return;
      fetchAndSwap(region, url, 'push');
    }

    form.querySelectorAll('input, select').forEach(function (field) {
      if (field.type === 'submit' || field.type === 'button' || field.hasAttribute('data-ajax-select')) return;
      var isDiscrete = (field.tagName === 'SELECT' || field.type === 'date' || field.type === 'checkbox');
      if (isDiscrete) {
        bindChangeEvent(field, commitDiscreteChange);
      } else {
        field.addEventListener('input', scheduleTypingRefresh);
        field.addEventListener('blur', commitTypingFilter);
      }
    });

    // select2-driven fields (e.g. the manager picker) fire a native `change`
    // on the underlying <select>, treated the same as any discrete change —
    // but only once select2 has actually initialized it, so re-bind is
    // idempotent-safe.
    form.querySelectorAll('[data-ajax-select]').forEach(function (field) {
      bindChangeEvent(field, commitDiscreteChange);
    });

    updateFilterCount(form);

    form.querySelectorAll('[data-clear-filters]').forEach(function (clearLink) {
      clearLink.addEventListener('click', function (e) {
        e.preventDefault();
        clearTimeout(debounceTimers.get(form));
        form.querySelectorAll('input[type="text"], input[type="date"], input[type="search"]').forEach(function (f) { f.value = ''; });
        form.querySelectorAll('select').forEach(function (f) {
          f.value = '';
          if (window.jQuery && jQuery.fn.select2 && jQuery(f).data('select2')) jQuery(f).val(null).trigger('change');
        });
        updateFilterCount(form);
        fetchAndSwap(region, form.getAttribute('action') || window.location.pathname, 'push');
      });
    });
  }

  function bindRegionInteractions(region) {
    region.querySelectorAll('.pagination-controls a.page-btn').forEach(function (a) {
      if (a.dataset.liveBound === '1') return;
      a.dataset.liveBound = '1';
      a.addEventListener('click', function (e) {
        e.preventDefault();
        fetchAndSwap(region, a.href, 'push');
      });
    });
    var jumpForm = region.querySelector('.pagination-jump');
    if (jumpForm && jumpForm.dataset.liveBound !== '1') {
      jumpForm.dataset.liveBound = '1';
      jumpForm.addEventListener('submit', function (e) {
        e.preventDefault();
        fetchAndSwap(region, buildUrl(jumpForm), 'push');
      });
    }
  }

  function bind(root) {
    root = root || document;
    (root.querySelectorAll ? root : document).querySelectorAll('.filters-bar[data-live-key]').forEach(bindFilterForm);
    (root.querySelectorAll ? root : document).querySelectorAll('[data-live-region]').forEach(bindRegionInteractions);
  }

  // Called by spa-shell.js's global popstate listener before it falls back
  // to a full `.content` swap. Returns true if a live-filter form on the
  // *current* page owns this URL (same pathname), in which case it has
  // fully handled the restore — form fields, Select2, pagination, sort —
  // via an AJAX-only refresh of just the live region.
  function handlePopState(url) {
    var handled = false;
    document.querySelectorAll('.filters-bar[data-live-key]').forEach(function (form) {
      if (form.dataset.liveBound !== '1') return;
      var actionPath = new URL(form.getAttribute('action') || window.location.pathname, window.location.href).pathname;
      var targetPath = new URL(url, window.location.href).pathname;
      if (actionPath !== targetPath) return;

      var region = findRegionFor(form);
      if (!region) return;

      handled = true;
      clearTimeout(debounceTimers.get(form));
      fetchAndSwap(region, url, null, form);
    });
    return handled;
  }

  window.HRMSLiveFilters = { bind: bind, handlePopState: handlePopState };
  document.addEventListener('DOMContentLoaded', function () { bind(document); });
})();
