<?php /** @var array $r */ ?>
<div class="mb-2"><label class="form-label small mb-1">Qualification</label><input type="text" name="qualification" class="form-control form-control-sm" value="<?= esc($r['qualification'] ?? '') ?>" required></div>
<div class="mb-2"><label class="form-label small mb-1">Institution</label><input type="text" name="institution" class="form-control form-control-sm" value="<?= esc($r['institution'] ?? '') ?>" required></div>
<div class="mb-2"><label class="form-label small mb-1">Board / University</label><input type="text" name="board_university" class="form-control form-control-sm" value="<?= esc($r['board_university'] ?? '') ?>"></div>
<div class="row g-2">
  <div class="col-6 mb-2"><label class="form-label small mb-1">Percentage / CGPA</label><input type="text" name="percentage_cgpa" class="form-control form-control-sm" value="<?= esc($r['percentage_cgpa'] ?? '') ?>"></div>
  <div class="col-6 mb-2"><label class="form-label small mb-1">Year of passing</label><input type="number" name="year_of_passing" min="1950" max="2100" class="form-control form-control-sm" value="<?= esc($r['year_of_passing'] ?? '') ?>"></div>
</div>
