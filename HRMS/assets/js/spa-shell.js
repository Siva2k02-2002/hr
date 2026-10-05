// ---------- SPA shell: AJAX navigation for the app chrome ----------
// Intercepts clicks on the sidebar/topbar navigation links and swaps only
// the .content region instead of a full page reload, by fetching the exact
// same server-rendered URL CI4 already returns for a normal GET and diffing
// it client-side with DOMParser. No new JSON endpoints, no controller
// changes — every permission check / filter / pagination keeps working
// exactly as it does today.
//
// Deliberately scoped to .sidebar / .topbar (+ anything explicitly opted in
// via [data-spa-link]) rather than every link in .content: this app has ~150
// largely un-audited views, some of which link straight to file downloads
// (exports, payslip PDFs, import templates) with no [download] attribute to
// detect — fetching those through DOMParser would silently corrupt the
// download. Primary module-to-module navigation (the thing users feel as
// "does this reload the page") lives entirely in the sidebar/topbar, so
// scoping there gets the SPA feel without auditing every view first.
(function () {
  var contentEl = document.querySelector('.content');
  if (!contentEl) return;

  function isEligible(a) {
    if (!a || !a.href) return false;
    if (a.origin !== window.location.origin) return false;
    if (a.target && a.target !== '' && a.target !== '_self') return false;
    if (a.hasAttribute('download')) return false;
    if (a.hasAttribute('data-full-reload')) return false;
    if (a.closest('[data-drawer-form]') || a.closest('[data-drawer-target]')) return false;
    var href = a.getAttribute('href') || '';
    if (href === '' || href.charAt(0) === '#') return false;
    if (/^(mailto|tel|javascript):/i.test(href)) return false;
    return true;
  }

  function inScope(a) {
    if (a.closest('[data-spa-link]')) return true;
    return !!a.closest('.sidebar, .topbar');
  }

  function fetchDoc(url) {
    if (window.pageLoadingBar) window.pageLoadingBar.start();
    return fetch(url, { headers: { 'X-Requested-With': 'XMLHttpRequest' }, credentials: 'same-origin' })
      .then(function (res) {
        if (!res.ok) throw new Error('Navigation failed: ' + res.status);
        return res.text();
      })
      .then(function (html) { return new DOMParser().parseFromString(html, 'text/html'); })
      .finally(function () { if (window.pageLoadingBar) window.pageLoadingBar.done(); });
  }

  function reinitInjected(root) {
    window.initIcons();
    window.HRMSSelect2Init(root);
    if (window.HRMSLiveFilters) window.HRMSLiveFilters.bind(root);
    if (window.HRMSTableToolkit) window.HRMSTableToolkit.bind(root);
  }

  // Every view's own <?= $this->section('scripts') ?> block (per-page init:
  // geolocation, live clocks, punch handlers, etc.) renders outside .content,
  // right before </body> (see layouts/main.php) — swapping .content alone
  // fetches that markup into `doc` but then throws it away, and even if it
  // were inside .content, assigning via innerHTML never executes <script>
  // tags anyway. This re-creates each <script> found in the fetched body (in
  // document order, skipping ones already loaded by src) so per-page init
  // runs after an SPA swap exactly as it would after a full page load.
  var EXECUTABLE_SCRIPT_TYPES = ['', 'text/javascript', 'application/javascript', 'module'];

  function runPageScripts(doc) {
    var scripts = Array.prototype.slice.call(doc.body.querySelectorAll('script'));
    var chain = Promise.resolve();
    scripts.forEach(function (old) {
      // Data islands such as partials/flash.php's #flash-data (application/json)
      // aren't code — they're already carried over as part of .content's/
      // markup swap and must not be re-created as an executable <script>.
      if (EXECUTABLE_SCRIPT_TYPES.indexOf((old.getAttribute('type') || '').toLowerCase()) === -1) return;

      var src = old.getAttribute('src');
      if (src) {
        if (document.querySelector('script[src="' + src.replace(/"/g, '\\"') + '"]')) return;
        chain = chain.then(function () {
          return new Promise(function (resolve) {
            var s = document.createElement('script');
            s.src = src;
            s.async = false;
            s.onload = resolve;
            s.onerror = resolve;
            document.body.appendChild(s);
          });
        });
      } else {
        chain = chain.then(function () {
          var s = document.createElement('script');
          s.textContent = old.textContent;
          document.body.appendChild(s);
        });
      }
    });
    return chain;
  }

  function swapFromDoc(doc, url, push) {
    var newContent = doc.querySelector('.content');
    if (!newContent) { window.location.href = url; return; }

    // Lets the page being left clean up its own timers/listeners (e.g. a
    // live clock's setInterval) before its DOM is discarded — see the
    // matching teardown listener in attendance/my.php.
    document.dispatchEvent(new CustomEvent('hrms:before-content-swap'));

    contentEl.innerHTML = newContent.innerHTML;
    contentEl.scrollTop = 0;
    window.scrollTo(0, 0);

    var newTopbarTitle = doc.querySelector('.topbar-title');
    var topbarTitle = document.querySelector('.topbar-title');
    if (topbarTitle && newTopbarTitle) topbarTitle.innerHTML = newTopbarTitle.innerHTML;

    var newSidebarNav = doc.querySelector('.sidebar-nav');
    var sidebarNav = document.querySelector('.sidebar-nav');
    if (sidebarNav && newSidebarNav) sidebarNav.innerHTML = newSidebarNav.innerHTML;

    var newTitle = doc.querySelector('title');
    if (newTitle) document.title = newTitle.textContent;

    if (push) history.pushState({ hrmsShell: true }, '', url);

    // Full page loads used to reset the mobile off-canvas sidebar; a content
    // swap doesn't, so close it here (no-op on desktop, where it's never .open).
    var mobileSidebar = document.querySelector('.sidebar');
    var mobileBackdrop = document.querySelector('.sidebar-backdrop');
    if (mobileSidebar) mobileSidebar.classList.remove('open');
    if (mobileBackdrop) mobileBackdrop.classList.remove('open');

    reinitInjected(contentEl);
    window.initIcons();

    if (window.HRMSSidebarQuick) window.HRMSSidebarQuick.refresh();

    runPageScripts(doc).then(function () {
      document.dispatchEvent(new CustomEvent('hrms:navigated', { detail: { url: url } }));
    });
  }

  window.HRMSShell = { fetchDoc: fetchDoc, swapFromDoc: swapFromDoc, reinitInjected: reinitInjected };

  document.addEventListener('click', function (e) {
    if (e.defaultPrevented || e.button !== 0 || e.metaKey || e.ctrlKey || e.shiftKey || e.altKey) return;
    var a = e.target.closest('a');
    if (!a || !inScope(a) || !isEligible(a)) return;

    e.preventDefault();
    var url = a.href;
    fetchDoc(url).then(function (doc) { swapFromDoc(doc, url, true); })
      .catch(function () { window.location.href = url; });
  });

  window.addEventListener('popstate', function () {
    // Same-page filter/pagination changes (pushed by live-filters.js) are
    // restored in place — form fields, Select2, the live region only — by
    // HRMSLiveFilters itself. Only fall back to a full `.content` swap when
    // Back/Forward is crossing to a genuinely different page.
    if (window.HRMSLiveFilters && window.HRMSLiveFilters.handlePopState(window.location.href)) return;

    fetchDoc(window.location.href).then(function (doc) { swapFromDoc(doc, window.location.href, false); })
      .catch(function () { window.location.reload(); });
  });

  // Land on the current URL as the first history entry so popstate has
  // something to return to before the very first SPA navigation.
  history.replaceState({ hrmsShell: true }, '', window.location.href);
})();
