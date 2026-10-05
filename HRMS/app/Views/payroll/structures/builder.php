<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>

<div class="page-header page-header--actions-only">
  <div class="page-actions">
    <a href="<?= site_url('payroll/structures') ?>" class="btn btn-outline-secondary btn-sm"><?= icon('arrow-left') ?> Back to structures</a>
  </div>
</div>

<?php if (session('error')): ?>
  <div class="alert alert-danger small"><?= esc(session('error')) ?></div>
<?php endif; ?>

<div class="row g-3">
  <div class="col-lg-8">
    <div class="card">
      <div class="table-wrap">
        <div class="table-scroll">
        <table class="table table-compact mb-0" id="builderTable">
          <thead>
            <tr>
              <th class="field-w-md">Component</th>
              <th class="field-w-sm">Calc. Type</th>
              <th>Amount / %</th>
              <th>Formula</th>
              <th class="text-center">Editable</th>
              <th></th>
            </tr>
          </thead>
          <tbody id="builderRows"></tbody>
        </table>
        </div>
      </div>
      <button type="button" class="btn btn-sm btn-outline-primary mt-2 btn-fit" id="addRowBtn"><?= icon('plus') ?> Add component</button>

      <div class="d-flex gap-2 mt-4">
        <button type="button" class="btn btn-primary" id="saveBuilderBtn">Save components</button>
      </div>
    </div>
  </div>

  <div class="col-lg-4">
    <div class="card">
      <h6 class="mb-3">Live preview</h6>
      <label class="form-label">Test gross salary</label>
      <input type="number" step="0.01" class="form-control form-control-sm mb-3" id="previewGross" value="30000">
      <div id="previewResult" class="small"></div>
    </div>
  </div>
</div>

<form method="post" action="<?= site_url('payroll/structures/' . $structure['id'] . '/save') ?>" id="builderForm" class="d-none">
  <?= csrf_field() ?>
  <input type="hidden" name="items" id="itemsInput">
</form>

<script>
const COMPONENTS = <?= json_encode(array_map(static fn ($c) => [
    'id' => $c['id'], 'code' => $c['code'], 'name' => $c['name'], 'type' => $c['type'], 'percentage_of' => $c['percentage_of'],
    'is_active' => $c['status'] === 'active',
], $components)) ?>;
const EXISTING = <?= json_encode(array_map(static fn ($i) => [
    'salary_component_id' => $i['salary_component_id'], 'calculation_type' => $i['calculation_type'],
    'value' => $i['value'], 'formula' => $i['formula'], 'is_editable' => (bool) $i['is_editable'],
], $items)) ?>;
const PREVIEW_URL = <?= json_encode(site_url('payroll/structures/' . $structure['id'] . '/preview')) ?>;
const CSRF_NAME = <?= json_encode(csrf_token()) ?>;
const CSRF_HASH = <?= json_encode(csrf_hash()) ?>;

const rowsBody = document.getElementById('builderRows');

// Inactive components are hidden from selection except when they're the row's current
// value — that keeps an existing structure's row intact (and clearly labeled) instead of
// the <select> silently landing on whatever option happens to render first.
function componentOptions(selectedId) {
  return COMPONENTS
    .filter(c => c.is_active || c.id == selectedId)
    .map(c => `<option value="${c.id}" ${c.id == selectedId ? 'selected' : ''}>${c.name} (${c.code})${c.is_active ? '' : ' — Inactive'}</option>`)
    .join('');
}

function addRow(data) {
  data = data || { salary_component_id: '', calculation_type: 'fixed', value: 0, formula: '', is_editable: true };
  const tr = document.createElement('tr');
  tr.innerHTML = `
    <td><select class="form-select form-select-sm component-select">${componentOptions(data.salary_component_id)}</select></td>
    <td>
      <select class="form-select form-select-sm calc-type" data-min-search="Infinity">
        <option value="fixed" ${data.calculation_type === 'fixed' ? 'selected' : ''}>Fixed</option>
        <option value="percentage" ${data.calculation_type === 'percentage' ? 'selected' : ''}>Percentage</option>
        <option value="formula" ${data.calculation_type === 'formula' ? 'selected' : ''}>Formula</option>
      </select>
    </td>
    <td><input type="number" step="0.01" class="form-control form-control-sm value-input" value="${data.value}"></td>
    <td><input type="text" class="form-control form-control-sm formula-input text-uppercase" value="${data.formula || ''}" placeholder="BASIC*0.4"></td>
    <td class="text-center"><input type="checkbox" class="form-check-input editable-check" ${data.is_editable ? 'checked' : ''}></td>
    <td class="text-end"><button type="button" class="btn-icon btn text-danger remove-row"><i data-lucide="trash-2" class="icon" aria-hidden="true"></i></button></td>
  `;
  rowsBody.appendChild(tr);
  window.renderIcons();
  if (window.HRMSSelect2Init) window.HRMSSelect2Init(tr);
}

document.getElementById('addRowBtn').addEventListener('click', () => addRow());
rowsBody.addEventListener('click', (e) => {
  if (e.target.closest('.remove-row')) {
    e.target.closest('tr').remove();
  }
});

(EXISTING.length ? EXISTING : []).forEach(addRow);
if (EXISTING.length === 0) {
  addRow();
}

function collectItems() {
  return Array.from(rowsBody.querySelectorAll('tr')).map(tr => ({
    salary_component_id: tr.querySelector('.component-select').value,
    calculation_type: tr.querySelector('.calc-type').value,
    value: tr.querySelector('.value-input').value,
    formula: tr.querySelector('.formula-input').value,
    is_editable: tr.querySelector('.editable-check').checked ? 1 : 0,
  })).filter(i => i.salary_component_id !== '');
}

document.getElementById('saveBuilderBtn').addEventListener('click', () => {
  document.getElementById('itemsInput').value = JSON.stringify(collectItems());
  document.getElementById('builderForm').submit();
});

let previewTimer = null;
function runPreview() {
  clearTimeout(previewTimer);
  previewTimer = setTimeout(async () => {
    const body = new URLSearchParams();
    body.set('items', JSON.stringify(collectItems()));
    body.set('gross_salary', document.getElementById('previewGross').value || 0);
    body.set(CSRF_NAME, CSRF_HASH);

    const res = await fetch(PREVIEW_URL, { method: 'POST', body, headers: { 'X-Requested-With': 'XMLHttpRequest' } });
    const data = await res.json();
    const el = document.getElementById('previewResult');
    if (!data.success) {
      el.innerHTML = `<div class="text-danger">${data.message}</div>`;
      return;
    }
    let rows = data.items.map(i => `<div class="d-flex justify-content-between"><span>${i.component_name}</span><span>${Number(i.amount).toFixed(2)}</span></div>`).join('');
    el.innerHTML = rows +
      `<hr><div class="d-flex justify-content-between fw-semibold"><span>Gross Earnings</span><span>${data.gross_earnings.toFixed(2)}</span></div>` +
      `<div class="d-flex justify-content-between text-danger"><span>Gross Deductions</span><span>${data.gross_deductions.toFixed(2)}</span></div>` +
      `<div class="d-flex justify-content-between fw-bold border-top pt-1 mt-1"><span>Net</span><span>${data.net.toFixed(2)}</span></div>`;
  }, 350);
}

rowsBody.addEventListener('input', runPreview);
rowsBody.addEventListener('change', runPreview);
document.getElementById('previewGross').addEventListener('input', runPreview);
runPreview();
</script>

<?= $this->endSection() ?>
