<?php
/**
 * Premium pagination — replaces CI4's default pager markup app-wide.
 * @var CodeIgniter\Pager\PagerRenderer $pager
 * @var string $pagerGroup
 */
$pager->setSurroundCount(1);

$group        = $pagerGroup ?? 'default';
$pageSelector = $group === 'default' ? 'page' : 'page_' . $group;
$total        = $pager->getTotal();
$start        = $pager->getPerPageStart();
$end          = $pager->getPerPageEnd();
$current      = $pager->getCurrentPageNumber();
$last         = $pager->getPageCount();
$firstShown   = $pager->getFirstPageNumber();
$lastShown    = $pager->getLastPageNumber();
?>
<?php if ($last > 1): ?>
<nav class="pagination-bar" aria-label="Pagination">
  <div class="pagination-summary">
    <?php if ($total !== null): ?>
      Showing <strong><?= (int) $start ?>&ndash;<?= (int) $end ?></strong> of <strong><?= (int) $total ?></strong>
    <?php endif; ?>
  </div>

  <div class="pagination-controls">
    <?php if ($pager->hasPreviousPage()): ?>
      <a class="page-btn" href="<?= esc($pager->getPreviousPage()) ?>" aria-label="Previous page"><?= icon('chevron-left') ?></a>
    <?php else: ?>
      <span class="page-btn is-disabled" aria-hidden="true"><?= icon('chevron-left') ?></span>
    <?php endif; ?>

    <?php if ($firstShown > 1): ?>
      <a class="page-btn" href="<?= esc($pager->getFirst()) ?>">1</a>
      <?php if ($firstShown > 2): ?><span class="page-ellipsis">&hellip;</span><?php endif; ?>
    <?php endif; ?>

    <?php foreach ($pager->links() as $link): ?>
      <a class="page-btn <?= $link['active'] ? 'is-active' : '' ?>" href="<?= esc($link['uri']) ?>" <?= $link['active'] ? 'aria-current="page"' : '' ?>><?= esc((string) $link['title']) ?></a>
    <?php endforeach; ?>

    <?php if ($lastShown < $last): ?>
      <?php if ($lastShown < $last - 1): ?><span class="page-ellipsis">&hellip;</span><?php endif; ?>
      <a class="page-btn" href="<?= esc($pager->getLast()) ?>"><?= (int) $last ?></a>
    <?php endif; ?>

    <?php if ($pager->hasNextPage()): ?>
      <a class="page-btn" href="<?= esc($pager->getNextPage()) ?>" aria-label="Next page"><?= icon('chevron-right') ?></a>
    <?php else: ?>
      <span class="page-btn is-disabled" aria-hidden="true"><?= icon('chevron-right') ?></span>
    <?php endif; ?>
  </div>

  <form class="pagination-jump" method="get">
    <?php foreach ($_GET as $key => $value): ?>
      <?php if ($key === $pageSelector || is_array($value)) continue; ?>
      <input type="hidden" name="<?= esc($key, 'attr') ?>" value="<?= esc($value, 'attr') ?>">
    <?php endforeach; ?>
    <label for="pager-jump-<?= esc($group, 'attr') ?>">Go to</label>
    <input type="number" min="1" max="<?= (int) $last ?>" id="pager-jump-<?= esc($group, 'attr') ?>" name="<?= esc($pageSelector, 'attr') ?>" value="<?= (int) $current ?>">
  </form>
</nav>
<?php endif; ?>
