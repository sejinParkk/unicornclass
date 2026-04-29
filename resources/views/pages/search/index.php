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


<div class="inner">

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
    <div class="home-section-header">
      <div class="home_section_flex">
        <p class="home-section-title">
          <img src="/assets/img/hero_star.svg" alt="">
          <strong>클래스</strong>
          <!-- <?= number_format(count($classes)) ?> -->
        </p>
        <p class="home-section-en">Class</p>    
      </div>
    </div>
    <div class="cl-grid search-cl-grid">
      <?php foreach ($classes as $class): ?>
      <a href="/classes/<?= (int)$class['class_idx'] ?>" class="cl-card">
        <div class="card-thumb">
          <?php if (!empty($class['thumbnail'])): ?>
          <img src="/uploads/class/<?= htmlspecialchars($class['thumbnail']) ?>" alt="<?= htmlspecialchars($class['title']) ?>">
          <?php else: ?>
          <div class="card-thumb-ph"><img src="/assets/img/logo.svg"></div>
          <?php endif; ?>
          <p class="ctag ct-premium">
            <img src="/assets/img/hero_star_fff.svg" alt="">
            <span>
              <?php if ($class['type'] === 'free'): ?>
              무료 강의
              <?php else: ?>
              프리미엄 강의
              <?php endif; ?>
            </span>
          </p>
        </div>
        <div class="card-info">
          <div class="card-tags">
            <?php if ($class['badge_hot']): ?><span class="ctags ct-hot">HOT</span><?php endif; ?>
            <?php if ($class['badge_new']): ?><span class="ctags ct-new">NEW</span><?php endif; ?>          
          </div>
          <div class="card-title"><?= searchHighlight($class['title'], $q) ?></div>
          <div class="card-meta">
            <?= searchHighlight($class['instructor_name'], $q) ?>
            <?= !empty($class['category_name']) ? ' · ' . htmlspecialchars($class['category_name']) : '' ?>
            
            <!-- <?php if ($class['type'] === 'premium' && $class['price'] > 0): ?>
            &nbsp;|&nbsp; <?= number_format((int)$class['price']) ?>원
            <?php elseif ($class['type'] === 'free'): ?>
            &nbsp;|&nbsp; 무료
            <?php endif; ?> -->
          </div>
        </div>
      </a>
      <?php endforeach; ?>
    </div>
  </div>
  <?php endif; ?>

  <?php if (!empty($classes) && !empty($instructors)): ?>
  <!-- <div class="search-divider"></div> -->
  <?php endif; ?>

  <?php if (!empty($instructors)): ?>
  <!-- 강사 섹션 -->
  <div class="search-section search-section-pb">
    <div class="home-section-header">
      <div class="home_section_flex">
        <p class="home-section-title">
          <img src="/assets/img/hero_star.svg" alt="">
          <strong>강사</strong>
          <!-- <?= number_format(count($instructors)) ?> -->
        </p>
        <p class="home-section-en">Teachers</p>    
      </div>
    </div>
    <div class="ins-grid search-ins-grid">
      <?php foreach ($instructors as $ins): ?>
      <a href="/instructors/<?= (int)$ins['instructor_idx'] ?>" class="i-card-wrap">
        <div class="i-photo-wrap">
          <?php if (!empty($ins['photo'])): ?>
          <img src="/uploads/instructor/<?= htmlspecialchars($ins['photo']) ?>" alt="<?= htmlspecialchars($ins['name']) ?>" class="i-photo-img">
          <?php else: ?>
          <div class="i-photo-ph"><img src="/assets/img/logo.svg" alt=""></div>
          <?php endif; ?>
        </div>
        <div class="i-info ver2">
          <div class="i-name"><?= searchHighlight($ins['name'], $q) ?></div>
          <?php if (!empty($ins['intro'])): ?>
          <ul class="i-photo-desc">
            <li><?= htmlspecialchars($ins['intro']) ?></li>
          </ul>
          <?php endif; ?>
          <div class="i-social-fixed">
            <?php if (!empty($ins['sns_youtube'])): ?>          
            <div class="i-social-icon" title="유튜브"><img src="/assets/img/inst_youtube.svg" alt=""></div>
            <?php endif; ?>
            <?php if (!empty($ins['sns_instagram'])): ?>
            <div class="i-social-icon" title="인스타그램"><img src="/assets/img/inst_insta.svg" alt=""></div>
            <?php endif; ?>
            <?php if (!empty($ins['sns_facebook'])): ?>
            <div class="i-social-icon" title="페이스북"><img src="/assets/img/inst_facebook.svg" alt=""></div>
            <?php endif; ?>
          </div>
          <!-- <?php if ((int)$ins['class_count'] > 0): ?>
          <div class="i-class-count">클래스 <?= (int)$ins['class_count'] ?>개</div>
          <?php endif; ?> -->
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
  <div class="search-result-header">
    <div class="search-result-count">
      <strong><?= htmlspecialchars($q) ?></strong>에 대한
      <strong><?= number_format($total) ?></strong>개의 검색 결과
    </div>
  </div>

  <!-- Empty State -->  
  <div class="empty-state">검색 결과가 없습니다.</div>

  <?php endif; ?>

</div>