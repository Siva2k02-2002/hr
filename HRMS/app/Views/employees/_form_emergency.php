<?php /** @var array $c */ ?>
<div class="mb-2"><label class="form-label small mb-1">Name</label><input type="text" name="name" class="form-control form-control-sm" value="<?= esc($c['name'] ?? '') ?>" required></div>
<div class="mb-2"><label class="form-label small mb-1">Relationship</label><input type="text" name="relationship" class="form-control form-control-sm" value="<?= esc($c['relationship'] ?? '') ?>" required></div>
<div class="row g-2">
  <div class="col-6 mb-2"><label class="form-label small mb-1">Phone</label><input type="text" name="phone" class="form-control form-control-sm" maxlength="10" value="<?= esc($c['phone'] ?? '') ?>" required></div>
  <div class="col-6 mb-2"><label class="form-label small mb-1">Alternate phone</label><input type="text" name="alternate_phone" class="form-control form-control-sm" maxlength="10" value="<?= esc($c['alternate_phone'] ?? '') ?>"></div>
</div>
<div class="mb-2"><label class="form-label small mb-1">Address</label><input type="text" name="address" class="form-control form-control-sm" value="<?= esc($c['address'] ?? '') ?>"></div>
<div class="mb-2"><label class="form-label small mb-1">Priority</label><input type="number" name="priority" min="1" class="form-control form-control-sm" value="<?= esc($c['priority'] ?? 1) ?>"></div>
