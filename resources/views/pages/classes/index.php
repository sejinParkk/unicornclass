<?php
// $type, $categoryIdx, $categories, $list, $total, $page, $pages 가 컨트롤러에서 전달됨
$typeLabel = match($type) {
    'free'    => '무료강의',
    'premium' => '프리미엄강의',
    default   => '전체 클래스',
};
?>
<!-- 서브 배너 -->
<div class="cl-banner">
  <div class="cl-banner-bg"></div>
  <div class="cl-banner-label">지금 진행중인</div>
  <div class="cl-banner-title">클래스</div>
</div>

<!-- 타입 탭 -->
<div class="cl-tabs">
  <?php
  $tabBase = $categoryIdx > 0 ? '?cat=' . $categoryIdx : '?';
  ?>
  <a href="/classes" class="cl-tab <?= $type === '' ? 'active' : '' ?>">전체</a>
  <a href="/classes?type=free<?= $categoryIdx > 0 ? '&cat='.$categoryIdx : '' ?>"
     class="cl-tab <?= $type === 'free' ? 'active' : '' ?>">무료강의</a>
  <a href="/classes?type=premium<?= $categoryIdx > 0 ? '&cat='.$categoryIdx : '' ?>"
     class="cl-tab <?= $type === 'premium' ? 'active' : '' ?>">프리미엄강의</a>
</div>

<!-- 카테고리 필터 -->
<?php if (!empty($categories)): ?>
<div class="cat-bar">
  <?php
  $catBase = $type !== '' ? '?type=' . $type : '?';
  ?>
  <a href="/classes<?= $type !== '' ? '?type='.$type : '' ?>"
     class="cat-btn <?= $categoryIdx === 0 ? 'active' : '' ?>">전체</a>
  <?php foreach ($categories as $cat): ?>
  <a href="/classes?<?= $type !== '' ? 'type='.$type.'&' : '' ?>cat=<?= $cat['category_idx'] ?>"
     class="cat-btn <?= $categoryIdx === (int)$cat['category_idx'] ? 'active' : '' ?>">
    <?= htmlspecialchars($cat['name']) ?>
  </a>
  <?php endforeach; ?>
</div>
<?php endif; ?>

<!-- 카드 그리드 -->
<div class="cl-grid-wrap">
  <div class="cl-count">총 <strong><?= number_format($total) ?></strong>개 강의</div>

  <?php if (empty($list)): ?>
  <div class="cl-empty">등록된 강의가 없습니다.</div>
  <?php else: ?>
  <div class="cl-grid">
    <?php foreach ($list as $class): ?>
    <a href="/classes/<?= $class['class_idx'] ?>" class="cl-card">
      <div class="card-thumb">
        <?php if (!empty($class['thumbnail'])): ?>
        <img src="/uploads/class/<?= htmlspecialchars($class['thumbnail']) ?>"
             alt="<?= htmlspecialchars($class['title']) ?>">
        <?php else: ?>
        <div class="card-thumb-ph"></div>
        <?php endif; ?>
      </div>
      <div class="card-info">
        <div class="card-tags">
          <?php if ($class['badge_hot']): ?><span class="ctag ct-hot">HOT</span><?php endif; ?>
          <?php if ($class['badge_new']): ?><span class="ctag ct-new">NEW</span><?php endif; ?>
          <?php if ($class['type'] === 'free'): ?>
          <span class="ctag ct-free">무료</span>
          <?php else: ?>
          <span class="ctag ct-premium">프리미엄</span>
          <?php endif; ?>
        </div>
        <div class="card-title"><?= htmlspecialchars($class['title']) ?></div>
        <div class="card-meta">
          <?= htmlspecialchars($class['instructor_name']) ?>
          <?php if (!empty($class['category_name'])): ?>
          · <?= htmlspecialchars($class['category_name']) ?>
          <?php endif; ?>
        </div>
      </div>
    </a>
    <?php endforeach; ?>
  </div>
  <?php endif; ?>

  <!-- 페이지네이션 -->
  <?php if ($pages > 1): ?>
  <?php
  $qBase = [];
  if ($type !== '') $qBase[] = 'type=' . $type;
  if ($categoryIdx > 0) $qBase[] = 'cat=' . $categoryIdx;
  $qBaseStr = implode('&', $qBase);
  ?>
  <div class="pagination">
    <a href="?<?= $qBaseStr ? $qBaseStr.'&' : '' ?>page=<?= max(1, $page - 1) ?>"
       class="page-arrow <?= $page <= 1 ? 'disabled' : '' ?>">‹</a>
    <?php
    $start = max(1, $page - 2);
    $end   = min($pages, $page + 2);
    if ($start > 1): ?><a href="?<?= $qBaseStr ? $qBaseStr.'&' : '' ?>page=1" class="page-btn">1</a><?php if ($start > 2): ?><span style="padding:0 4px;color:#ccc">…</span><?php endif; endif;
    for ($i = $start; $i <= $end; $i++):
    ?><a href="?<?= $qBaseStr ? $qBaseStr.'&' : '' ?>page=<?= $i ?>" class="page-btn <?= $i === $page ? 'active' : '' ?>"><?= $i ?></a><?php
    endfor;
    if ($end < $pages): if ($end < $pages - 1): ?><span style="padding:0 4px;color:#ccc">…</span><?php endif; ?><a href="?<?= $qBaseStr ? $qBaseStr.'&' : '' ?>page=<?= $pages ?>" class="page-btn"><?= $pages ?></a><?php endif; ?>
    <a href="?<?= $qBaseStr ? $qBaseStr.'&' : '' ?>page=<?= min($pages, $page + 1) ?>"
       class="page-arrow <?= $page >= $pages ? 'disabled' : '' ?>">›</a>
  </div>
  <?php endif; ?>
</div>
