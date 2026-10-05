<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>

<?php $isEdit = $component !== null; ?>

<div class="card card-narrow-lg">
  <?php if (! empty($errors)): ?>
    <div class="alert alert-danger"><?= esc(implode(' ', $errors)) ?></div>
  <?php endif; ?>
  <form method="post" action="<?= $isEdit ? site_url('payroll/components/' . $component['id']) : site_url('payroll/components') ?>">
    <?= csrf_field() ?>
    <div class="row g-3">
      <div class="col-md-6">
        <label class="form-label">Component name</label>
        <input type="text" name="name" class="form-control" value="<?= esc(old('name', $component['name'] ?? '')) ?>" required>
      </div>
      <div class="col-md-3">
        <label class="form-label">Code</label>
        <input type="text" name="code" class="form-control text-uppercase" value="<?= esc(old('code', $component['code'] ?? '')) ?>" required maxlength="30">
      </div>
      <div class="col-md-3">
        <label class="form-label">Type</label>
        <select name="type" class="form-select">
          <option value="earning" <?= old('type', $component['type'] ?? 'earning') === 'earning' ? 'selected' : '' ?>>Earning</option>
          <option value="deduction" <?= old('type', $component['type'] ?? '') === 'deduction' ? 'selected' : '' ?>>Deduction</option>
        </select>
      </div>

      <div class="col-md-4">
        <label class="form-label">Calculation type</label>
        <select name="calculation_type" class="form-select">
          <option value="fixed" <?= old('calculation_type', $component['calculation_type'] ?? 'fixed') === 'fixed' ? 'selected' : '' ?>>Fixed amount</option>
          <option value="percentage" <?= old('calculation_type', $component['calculation_type'] ?? '') === 'percentage' ? 'selected' : '' ?>>Percentage</option>
          <option value="formula" <?= old('calculation_type', $component['calculation_type'] ?? '') === 'formula' ? 'selected' : '' ?>>Formula</option>
        </select>
      </div>
      <div class="col-md-4">
        <label class="form-label">Percentage of <span class="text-muted small">(component code, optional)</span></label>
        <input type="text" name="percentage_of" class="form-control text-uppercase" value="<?= esc(old('percentage_of', $component['percentage_of'] ?? '')) ?>" placeholder="e.g. BASIC">
      </div>
      <div class="col-md-4">
        <label class="form-label">Display order</label>
        <input type="number" name="display_order" class="form-control" value="<?= esc(old('display_order', $component['display_order'] ?? 0)) ?>">
      </div>

      <div class="col-md-12">
        <label class="form-label">Formula <span class="text-muted small">(only used when calculation type is Formula, e.g. BASIC*0.4+DA)</span></label>
        <input type="text" name="formula" class="form-control text-uppercase" value="<?= esc(old('formula', $component['formula'] ?? '')) ?>" placeholder="BASIC*0.4">
      </div>

      <div class="col-md-3">
        <label class="form-label">Status</label>
        <select name="status" class="form-select">
          <option value="active" <?= old('status', $component['status'] ?? 'active') === 'active' ? 'selected' : '' ?>>Active</option>
          <option value="inactive" <?= old('status', $component['status'] ?? '') === 'inactive' ? 'selected' : '' ?>>Inactive</option>
        </select>
      </div>

      <div class="col-md-9 d-flex align-items-end">
        <div class="form-check form-check-inline">
          <input class="form-check-input" type="checkbox" name="is_taxable" value="1" id="isTaxable" <?= old('is_taxable', $component['is_taxable'] ?? true) ? 'checked' : '' ?>>
          <label class="form-check-label" for="isTaxable">Taxable</label>
        </div>
        <div class="form-check form-check-inline">
          <input class="form-check-input" type="checkbox" name="pf_applicable" value="1" id="pfApplicable" <?= old('pf_applicable', $component['pf_applicable'] ?? false) ? 'checked' : '' ?>>
          <label class="form-check-label" for="pfApplicable">PF applicable</label>
        </div>
        <div class="form-check form-check-inline">
          <input class="form-check-input" type="checkbox" name="esi_applicable" value="1" id="esiApplicable" <?= old('esi_applicable', $component['esi_applicable'] ?? false) ? 'checked' : '' ?>>
          <label class="form-check-label" for="esiApplicable">ESI applicable</label>
        </div>
      </div>
    </div>

    <div class="d-flex gap-2 mt-4">
      <button type="submit" class="btn btn-primary"><?= $isEdit ? 'Save changes' : 'Create component' ?></button>
      <a href="<?= site_url('payroll/components') ?>" class="btn btn-outline-secondary">Cancel</a>
    </div>
  </form>
</div>

<?= $this->endSection() ?>
