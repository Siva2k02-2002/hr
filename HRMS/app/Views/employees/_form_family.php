<?php /** @var array $m */ ?>
<div class="mb-2"><label class="form-label small mb-1">Name</label><input type="text" name="name" class="form-control form-control-sm" value="<?= esc($m['name'] ?? '') ?>" required></div>
<div class="mb-2"><label class="form-label small mb-1">Relationship</label><input type="text" name="relationship" class="form-control form-control-sm" value="<?= esc($m['relationship'] ?? '') ?>" required></div>
<div class="row g-2">
  <div class="col-6 mb-2"><label class="form-label small mb-1">Date of birth</label><input type="date" name="dob" class="form-control form-control-sm" value="<?= esc($m['dob'] ?? '') ?>"></div>
  <div class="col-6 mb-2"><label class="form-label small mb-1">Occupation</label><input type="text" name="occupation" class="form-control form-control-sm" value="<?= esc($m['occupation'] ?? '') ?>"></div>
</div>
<div class="form-check">
  <input class="form-check-input" type="checkbox" name="is_dependent" value="1" id="dep<?= $m['id'] ?? 'new' ?>" <?= ($m['is_dependent'] ?? false) ? 'checked' : '' ?>>
  <label class="form-check-label small" for="dep<?= $m['id'] ?? 'new' ?>">Dependent</label>
</div>
<div class="form-check">
  <input class="form-check-input" type="checkbox" name="is_nominee" value="1" id="nom<?= $m['id'] ?? 'new' ?>" <?= ($m['is_nominee'] ?? false) ? 'checked' : '' ?>>
  <label class="form-check-label small" for="nom<?= $m['id'] ?? 'new' ?>">Nominee</label>
</div>
