<?php
$group = $group ?? 'default';
$label = $label ?? 'data';
if (!isset($pager) || !$pager) { return; }
$total = $pager->getTotal($group);
$page = $pager->getCurrentPage($group);
$perPage = $pager->getPerPage($group);
$from = $total > 0 ? (($page - 1) * $perPage) + 1 : 0;
$to = min($page * $perPage, $total);
?>
<?php if ($total > 0): ?>
<div class="kamela-pager">
    <div class="kamela-pager__meta">Menampilkan <strong><?= $from ?>–<?= $to ?></strong> dari <strong><?= $total ?></strong> <?= esc($label) ?></div>
    <?php if ($pager->getPageCount($group) > 1): ?><?= $pager->links($group, 'kamela_full') ?><?php endif; ?>
</div>
<?php endif; ?>
