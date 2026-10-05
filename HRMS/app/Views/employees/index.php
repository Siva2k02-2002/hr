<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>

<div class="page-header page-header--actions-only">
  <div class="page-actions">
    <a href="<?= current_url() ?>" class="btn btn-outline-secondary btn-sm"><?= icon('refresh-cw') ?> Refresh</a>
    <?php if (can('employee.export')): ?>
      <div class="btn-group export-menu">
        <button type="button" class="btn btn-outline-secondary btn-sm dropdown-toggle" data-bs-toggle="dropdown"><?= icon('download') ?> Export</button>
        <ul class="dropdown-menu dropdown-menu-end">
          <li><a class="dropdown-item" href="<?= site_url('employees/export/excel') ?>?<?= http_build_query($filters) ?>"><?= icon('file-text') ?> Excel (.xlsx)</a></li>
          <li><a class="dropdown-item" href="<?= site_url('employees/export/csv') ?>?<?= http_build_query($filters) ?>"><?= icon('file-text') ?> CSV</a></li>
          <li><a class="dropdown-item" href="<?= site_url('employees/export/pdf') ?>?<?= http_build_query($filters) ?>"><?= icon('file-text') ?> PDF</a></li>
        </ul>
      </div>
    <?php endif; ?>
    <?php if (can('employee.import')): ?>
      <a href="<?= site_url('employees/import') ?>" class="btn btn-outline-secondary btn-sm"><?= icon('upload') ?> Import</a>
    <?php endif; ?>
    <?php if (can('employee.delete')): ?>
      <a href="<?= site_url('employees/archived') ?>" class="btn btn-outline-secondary btn-sm"><?= icon('archive') ?> Archived</a>
    <?php endif; ?>
    <?php if (can('employee.create')): ?>
      <a href="<?= site_url('employees/create') ?>" class="btn btn-primary btn-sm"><?= icon('user-plus') ?> Add employee</a>
    <?php endif; ?>
  </div>
</div>

<form method="get" class="filters-bar" data-live-key="employees">
  <div class="search-input">
    <?= icon('search') ?>
    <input type="text" name="q" placeholder="Search ID, name, email, mobile, PAN, Aadhaar…" value="<?= esc($filters['q']) ?>">
  </div>

  <select name="branch_id" class="form-select form-select-sm">
    <option value="">All branches</option>
    <?php foreach ($branches as $b): ?>
      <option value="<?= $b['id'] ?>" <?= (string) $filters['branch_id'] === (string) $b['id'] ? 'selected' : '' ?>><?= esc($b['name']) ?></option>
    <?php endforeach; ?>
  </select>

  <select name="department_id" class="form-select form-select-sm">
    <option value="">All departments</option>
    <?php foreach ($departments as $d): ?>
      <option value="<?= $d['id'] ?>" <?= (string) $filters['department_id'] === (string) $d['id'] ? 'selected' : '' ?>><?= esc($d['name']) ?></option>
    <?php endforeach; ?>
  </select>

  <select name="designation_id" class="form-select form-select-sm">
    <option value="">All designations</option>
    <?php foreach ($designations as $d): ?>
      <option value="<?= $d['id'] ?>" <?= (string) $filters['designation_id'] === (string) $d['id'] ? 'selected' : '' ?>><?= esc($d['name']) ?></option>
    <?php endforeach; ?>
  </select>

  <select name="status" class="form-select form-select-sm">
    <option value="">All statuses</option>
    <?php foreach (['active','probation','notice_period','suspended','resigned','terminated','retired','absconded','relieved'] as $s): ?>
      <option value="<?= $s ?>" <?= $filters['status'] === $s ? 'selected' : '' ?>><?= esc(employee_status_label($s)) ?></option>
    <?php endforeach; ?>
  </select>

  <select name="employment_type" class="form-select form-select-sm">
    <option value="">All employment types</option>
    <?php foreach (['full_time'=>'Full Time','part_time'=>'Part Time','contract'=>'Contract','intern'=>'Intern','consultant'=>'Consultant'] as $val => $label): ?>
      <option value="<?= $val ?>" <?= $filters['employment_type'] === $val ? 'selected' : '' ?>><?= $label ?></option>
    <?php endforeach; ?>
  </select>

  <select name="manager_id" id="manager_id" class="form-select form-select-sm" data-ajax-select
          data-ajax-url="<?= site_url('api/employees/search') ?>" data-placeholder="All managers" data-allow-clear="true" style="min-width:200px">
    <?php if ($selectedManager): ?>
      <option value="<?= $selectedManager['id'] ?>" selected><?= esc(trim($selectedManager['first_name'] . ' ' . $selectedManager['last_name'])) ?> (<?= esc($selectedManager['employee_code']) ?>)</option>
    <?php endif; ?>
  </select>

  <a href="<?= site_url('employees') ?>" class="btn btn-sm btn-outline-secondary" data-clear-filters>Clear</a>
</form>

<div data-live-region="employees">
<div class="table-wrap" data-density-toggle data-bulk-select data-density-key="employees">
  <?php if (empty($employees)): ?>
    <div class="empty-state">
      <div class="empty-icon"><?= icon('contact') ?></div>
      <h3>No employees found</h3>
      <p>Try adjusting your filters, or add the first employee.</p>
      <?php if (can('employee.create')): ?>
        <a href="<?= site_url('employees/create') ?>" class="btn btn-primary btn-sm"><?= icon('user-plus') ?> Add employee</a>
      <?php endif; ?>
    </div>
  <?php else: ?>
    <div class="table-scroll">
    <table class="table table-compact table-sticky-cols mb-0">
      <thead>
        <tr>
          <th>Employee</th>
          <th>Department</th>
          <th>Designation</th>
          <th>Branch</th>
          <th>Mobile</th>
          <th>Joining Date</th>
          <th>Status</th>
          <th></th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($employees as $e): ?>
          <tr>
            <td>
              <a href="<?= site_url('employees/' . $e['id']) ?>" class="cell-identity text-body">
                <?= view('partials/employee_avatar', ['employee' => $e, 'size' => 'sm']) ?>
                <span class="d-flex flex-column">
                  <span class="name"><?= esc(trim($e['first_name'] . ' ' . $e['last_name'])) ?></span>
                  <span class="sub"><?= esc($e['employee_code']) ?></span>
                </span>
              </a>
            </td>
            <td><?= esc($e['department_name']) ?></td>
            <td><?= esc($e['designation_name']) ?></td>
            <td><?= esc($e['branch_name']) ?></td>
            <td class="text-muted"><?= esc($e['mobile']) ?></td>
            <td class="text-muted"><?= esc($e['date_of_joining']) ?></td>
            <td><span class="badge <?= employee_status_badge_class($e['status']) ?>"><?= esc(employee_status_label($e['status'])) ?></span></td>
            <td class="text-end">
              <div class="row-actions">
                <a href="<?= site_url('employees/' . $e['id']) ?>" class="btn-icon btn" title="View"><?= icon('eye') ?></a>
                <?php if (can('employee.edit')): ?>
                  <a href="<?= site_url('employees/' . $e['id'] . '/edit') ?>" class="btn-icon btn" title="Edit"><?= icon('pencil') ?></a>
                <?php endif; ?>
                <?php if (can('employee.delete')): ?>
                  <form action="<?= site_url('employees/' . $e['id'] . '/delete') ?>" method="post" class="d-inline"
                        data-confirm="This employee will be archived, not permanently deleted." data-confirm-title="Archive employee?" data-confirm-label="Archive">
                    <?= csrf_field() ?>
                    <button type="submit" class="btn-icon btn text-danger" title="Archive"><?= icon('archive') ?></button>
                  </form>
                <?php endif; ?>
              </div>
            </td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
    </div>
  <?php endif; ?>
</div>

<?php if (isset($pager)): ?>
  <div class="mt-3"><?= $pager->links('employees') ?></div>
<?php endif; ?>
</div>

<?= $this->endSection() ?>

