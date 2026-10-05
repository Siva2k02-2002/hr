// ---------- Drawer-based create/edit forms ----------
// Turns any link marked [data-drawer-form] into a drawer flow: fetch the
// exact same full create/edit page the link already points to, pull just
// the <form> out of it, and drop that into the shared generic drawer
// (#genericFormDrawer, see layouts/main.php) instead of navigating there.
// The form still POSTs normally on submit — full page response, normal
// server redirect — so no JSON success contract is needed; this only
// changes how the form is opened, never how it's saved.
(function () {
  var drawer = document.getElementById('genericFormDrawer');
  var body = document.getElementById('genericFormDrawerBody');
  var titleEl = document.getElementById('genericFormDrawerTitle');
  if (!drawer || !body) return;

  var dirty = false;
  function markDirty() { dirty = true; }

  function loadForm(url) {
    body.innerHTML = '<div class="tab-pane-loading"><span class="spinner-border spinner-border-sm"></span> Loading&hellip;</div>';
    titleEl.textContent = 'Form';
    dirty = false;
    window.openDrawer(drawer);

    fetch(url, { headers: { 'X-Requested-With': 'XMLHttpRequest' }, credentials: 'same-origin' })
      .then(function (res) { return res.text(); })
      .then(function (html) {
        var doc = new DOMParser().parseFromString(html, 'text/html');
        var form = doc.querySelector('.content form');
        var heading = doc.querySelector('.content .page-header h1');
        titleEl.textContent = heading ? heading.textContent : 'Form';
        if (!form) { window.location.href = url; return; }

        body.innerHTML = '';
        body.appendChild(form);
        window.initIcons();
        window.HRMSSelect2Init(body);
        body.addEventListener('input', markDirty);
        body.addEventListener('change', markDirty);
      })
      .catch(function () { window.location.href = url; });
  }

  function attemptClose() {
    if (!dirty) { window.closeDrawer(drawer); return; }
    window.confirmAction({
      title: 'Discard unsaved changes?',
      message: 'You have unsaved changes in this form. Closing now will discard them.',
      variant: 'btn-danger',
      label: 'Discard',
    }, function () {
      dirty = false;
      window.closeDrawer(drawer);
    });
  }

  document.addEventListener('click', function (e) {
    var trigger = e.target.closest('[data-drawer-form]');
    if (trigger) {
      e.preventDefault();
      loadForm(trigger.getAttribute('href'));
      return;
    }
    // Any plain link inside the injected form (its "Cancel" link, typically)
    // closes the drawer instead of navigating away from the list page.
    var innerLink = e.target.closest('#genericFormDrawerBody a[href]');
    if (innerLink) {
      e.preventDefault();
      attemptClose();
    }
  });

  // Capture phase so this runs before app.js's own (bubble-phase) drawer
  // close handling — lets us intercept and show the unsaved-changes prompt
  // instead of letting the drawer close immediately underneath it. When
  // nothing is dirty we do nothing here and let the normal close proceed.
  document.addEventListener('click', function (e) {
    if (!drawer.classList.contains('open') || !dirty) return;
    var closesIt = e.target.closest('[data-drawer-close]') || e.target.matches('[data-drawer-backdrop-for="genericFormDrawer"]');
    if (!closesIt) return;
    e.preventDefault();
    e.stopPropagation();
    attemptClose();
  }, true);

  document.addEventListener('keydown', function (e) {
    if (e.key !== 'Escape' || !drawer.classList.contains('open') || !dirty) return;
    e.preventDefault();
    e.stopPropagation();
    attemptClose();
  }, true);
})();
