<?php /** @var array $r */ ?>
<div class="mb-2"><label class="form-label small mb-1">Company name</label><input type="text" name="company_name" class="form-control form-control-sm" value="<?= esc($r['company_name'] ?? '') ?>" required></div>
<div class="mb-2"><label class="form-label small mb-1">Designation</label><input type="text" name="designation" class="form-control form-control-sm" value="<?= esc($r['designation'] ?? '') ?>"></div>
<div class="row g-2">
  <div class="col-6 mb-2"><label class="form-label small mb-1">From date</label><input type="date" name="from_date" class="form-control form-control-sm" value="<?= esc($r['from_date'] ?? '') ?>" required></div>
  <div class="col-6 mb-2"><label class="form-label small mb-1">To date (blank = present)</label><input type="date" name="to_date" class="form-control form-control-sm" value="<?= esc($r['to_date'] ?? '') ?>"></div>
</div>
<div class="mb-2"><label class="form-label small mb-1">Years of experience</label><input type="number" step="0.1" min="0" name="years_experience" class="form-control form-control-sm" value="<?= esc($r['years_experience'] ?? '') ?>"></div>
<div class="mb-2"><label class="form-label small mb-1">Reason for leaving</label><textarea name="reason_for_leaving" class="form-control form-control-sm" rows="2"><?= esc($r['reason_for_leaving'] ?? '') ?></textarea></div>
