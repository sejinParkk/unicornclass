<?php
// 컨트롤러에서 전달: $q, $classes, $instructors, $total, $suggestions

/**
 * 검색어를 <mark> 태그로 감싸 하이라이트한다.
 * htmlspecialchars 처리 후 적용하므로 XSS 안전.
 */
function searchHighlight(string $text, string $q): string
{
    $safe   = htmlspecialchars($text, ENT_QUOTES, 'UTF-8');
    $safeQ  = htmlspecialchars($q,    ENT_QUOTES, 'UTF-8');
    if ($safeQ === '') return $safe;
    return preg_replace(
        '/(' . preg_quote($safeQ, '/') . ')/iu',
        '<mark>$1</mark>',
        $safe
    );
}
?>

<?php if ($total > 0): ?>
<!-- ====================================================
  SECTION A · 검색 결과 있음
===================================================== -->

<!-- 결과 헤더 -->
<div class="search-result-header">
  <div class="search-result-count">
    <strong><?= htmlspecialchars($q) ?></strong>에 대한
    <strong><?= number_format($total) ?></strong>개의 검색 결과
  </div>
</div>

<?php if (!empty($classes)): ?>
<!-- 클래스 섹션 -->
<div class="search-section">
  <div class="search-section-title">
    클래스 <span class="search-section-count"><?= number_format(count($classes)) ?>개</span>
  </div>
  <div class="cl-grid search-cl-grid">
    <?php foreach ($classes as $class): ?>
    <a href="/classes/<?= (int)$class['class_idx'] ?>" class="cl-card">
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
        <div class="card-title"><?= searchHighlight($class['title'], $q) ?></div>
        <div class="card-meta">
          <?= searchHighlight($class['instructor_name'], $q) ?>
          <?php if (!empty($class['category_name'])): ?>
          · <?= htmlspecialchars($class['category_name']) ?>
          <?php endif; ?>
          <?php if ($class['type'] === 'premium' && $class['price'] > 0): ?>
          &nbsp;|&nbsp; <?= number_format((int)$class['price']) ?>원
          <?php elseif ($class['type'] === 'free'): ?>
          &nbsp;|&nbsp; 무료
          <?php endif; ?>
        </div>
      </div>
    </a>
    <?php endforeach; ?>
  </div>
</div>
<?php endif; ?>

<?php if (!empty($classes) && !empty($instructors)): ?>
<div class="search-divider"></div>
<?php endif; ?>

<?php if (!empty($instructors)): ?>
<!-- 강사 섹션 -->
<div class="search-section search-section-pb">
  <div class="search-section-title">
    강사 <span class="search-section-count"><?= number_format(count($instructors)) ?>명</span>
  </div>
  <div class="ins-grid search-ins-grid">
    <?php foreach ($instructors as $ins): ?>
    <a href="/instructors/<?= (int)$ins['instructor_idx'] ?>" class="i-card-wrap">
      <div class="i-card">
        <div class="i-photo-wrap">
          <?php if (!empty($ins['photo'])): ?>
          <img src="/uploads/instructor/<?= htmlspecialchars($ins['photo']) ?>"
               alt="<?= htmlspecialchars($ins['name']) ?>" class="i-photo-img">
          <?php else: ?>
          <div class="i-photo-ph">
            <div class="person-icon">👤</div>
            <small><?= htmlspecialchars($ins['name']) ?></small>
          </div>
          <?php endif; ?>
        </div>
        <div class="i-info">
          <div class="i-name"><?= searchHighlight($ins['name'], $q) ?></div>
          <?php if (!empty($ins['field'])): ?>
          <ul class="i-desc-list">
            <li><?= htmlspecialchars($ins['field']) ?></li>
          </ul>
          <?php endif; ?>
          <div class="i-social-fixed">
            <?php if (!empty($ins['sns_youtube'])): ?>
            <div class="i-social-icon" title="유튜브">▶</div>
            <?php endif; ?>
            <?php if (!empty($ins['sns_instagram'])): ?>
            <div class="i-social-icon" title="인스타그램">📷</div>
            <?php endif; ?>
            <?php if (!empty($ins['sns_facebook'])): ?>
            <div class="i-social-icon" title="페이스북">f</div>
            <?php endif; ?>
          </div>
          <?php if ((int)$ins['class_count'] > 0): ?>
          <div class="i-class-count">클래스 <?= (int)$ins['class_count'] ?>개</div>
          <?php endif; ?>
        </div>
      </div>
    </a>
    <?php endforeach; ?>
  </div>
</div>
<?php endif; ?>

<?php else: ?>
<!-- ====================================================
  SECTION B · 검색 결과 없음
===================================================== -->

<!-- 결과 없음 헤더 -->
<div class="search-result-header search-result-header--empty">
  <div class="search-keyword-wrap">
    <span class="search-keyword-label">검색 결과 —</span>
    <span class="search-keyword">"<em><?= htmlspecialchars($q) ?></em>"</span>
  </div>
</div>

<!-- Empty State -->
<div class="empty-state">
  <div class="empty-icon">🔍</div>
  <div class="empty-title">"<?= htmlspecialchars($q) ?>"에 대한 검색 결과가 없습니다</div>
  <div class="empty-desc">
    검색어의 철자가 정확한지 확인해 보세요.<br>
    강사명 또는 클래스명으로 검색하실 수 있습니다.
  </div>
  <?php if (!empty($suggestions)): ?>
  <div class="empty-suggestions">
    <?php foreach ($suggestions as $s): ?>
    <a href="/search?q=<?= urlencode($s['keyword']) ?>" class="suggestion-tag">
      <?= htmlspecialchars($s['keyword']) ?>
    </a>
    <?php endforeach; ?>
  </div>
  <?php endif; ?>
</div>

<?php endif; ?>
