<?php
/** @var array $employee
 *  @var array $documents */
$canEdit = can('employee.documents');
$labels  = [
    'aadhaar' => 'Aadhaar', 'pan' => 'PAN', 'passport' => 'Passport', 'driving_license' => 'Driving License',
    'resume' => 'Resume', 'appointment_letter' => 'Appointment Letter', 'offer_letter' => 'Offer Letter',
    'education_certificate' => 'Education Certificate', 'experience_certificate' => 'Experience Certificate', 'other' => 'Other',
];
?>
<div class="card">
  <div class="d-flex justify-content-between align-items-center mb-3">
    <h2 class="h6 mb-0">Documents</h2>
    <?php if ($canEdit): ?>
      <button type="button" class="btn btn-primary btn-sm" data-drawer-target="uploadDocDrawer"><?= icon('upload') ?> Upload document</button>
    <?php endif; ?>
  </div>

  <?php if (empty($documents)): ?>
    <p class="text-muted small mb-0">No documents uploaded.</p>
  <?php else: ?>
    <div class="table-scroll">
    <table class="table table-compact mb-0">
      <thead><tr><th>Type</th><th>Number</th><th>File</th><th>Expiry</th><th>Status</th><th>Uploaded</th><th></th></tr></thead>
      <tbody>
        <?php foreach ($documents as $d): ?>
          <tr class="sub-entity-row">
            <td><?= esc($labels[$d['document_type']] ?? $d['document_type']) ?></td>
            <td><?= esc($d['document_number'] ?? '—') ?></td>
            <td><?= esc($d['original_filename']) ?></td>
            <td><?= esc($d['expiry_date'] ?? '—') ?></td>
            <td><span class="badge <?= $d['status'] === 'verified' ? 'badge-success' : ($d['status'] === 'rejected' ? 'badge-danger' : 'badge-muted') ?>"><?= esc(ucfirst($d['status'])) ?></span></td>
            <td class="text-muted"><?= esc($d['created_at']) ?></td>
            <td class="text-end">
              <div class="row-actions">
                <a href="<?= site_url('employees/' . $employee['id'] . '/documents/' . $d['id'] . '/download') ?>" target="_blank" class="btn-icon btn" title="Preview"><?= icon('eye') ?></a>
                <a href="<?= site_url('employees/' . $employee['id'] . '/documents/' . $d['id'] . '/download') ?>?download=1" class="btn-icon btn" title="Download"><?= icon('download') ?></a>
                <?php if ($canEdit): ?>
                  <form action="<?= site_url('employees/' . $employee['id'] . '/documents/' . $d['id'] . '/delete') ?>" method="post" class="d-inline"
                        data-confirm="This document will be removed." data-confirm-title="Delete document?" data-confirm-label="Delete">
                    <?= csrf_field() ?>
                    <button type="submit" class="btn-icon btn text-danger" title="Delete"><?= icon('trash-2') ?></button>
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

<?php if ($canEdit): ?>
  <div class="drawer-backdrop" data-drawer-backdrop-for="uploadDocDrawer"></div>
  <div class="drawer" id="uploadDocDrawer">
    <form action="<?= site_url('employees/' . $employee['id'] . '/documents') ?>" method="post" enctype="multipart/form-data">
      <?= csrf_field() ?>
      <div class="drawer-header">
        <h2>Upload document</h2>
        <button type="button" class="btn-icon btn btn-ghost" data-drawer-close aria-label="Close"><?= icon('x') ?></button>
      </div>
      <div class="drawer-body">
        <div class="mb-2">
          <label class="form-label small mb-1">Document type</label>
          <select name="document_type" class="form-select form-select-sm" required>
            <?php foreach ($labels as $val => $label): ?>
              <option value="<?= $val ?>"><?= $label ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="mb-2"><label class="form-label small mb-1">Document number (optional)</label><input type="text" name="document_number" class="form-control form-control-sm"></div>
        <div class="mb-2"><label class="form-label small mb-1">Expiry date (optional)</label><input type="date" name="expiry_date" class="form-control form-control-sm"></div>
        <div class="mb-2"><label class="form-label small mb-1">File</label><input type="file" name="file" class="form-control form-control-sm" accept=".pdf,.jpg,.jpeg,.png,.webp,.doc,.docx" required></div>
        <div class="mb-2"><label class="form-label small mb-1">Remarks (optional)</label><input type="text" name="remarks" class="form-control form-control-sm"></div>
      </div>
      <div class="drawer-footer">
        <button type="button" class="btn btn-outline-secondary btn-sm" data-drawer-close>Cancel</button>
        <button type="submit" class="btn btn-primary btn-sm">Upload</button>
      </div>
    </form>
  </div>
<?php endif; ?>
