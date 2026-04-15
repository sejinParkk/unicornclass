<?php
// $instructor 가 컨트롤러에서 전달됨
// $instructor['intros'][], $instructor['careers'][], $instructor['classes'][]
?>
<!-- 뒤로가기 -->
<a href="/instructors" class="ins-back">← 강사 목록으로</a>

<!-- 프로필 섹션 -->
<div class="ins-profile">

  <!-- 좌: 사진 -->
  <div class="ins-photo-wrap">
    <div class="ins-photo-deco"></div>
    <?php if (!empty($instructor['photo'])): ?>
    <img src="/uploads/instructor/<?= htmlspecialchars($instructor['photo']) ?>"
         alt="<?= htmlspecialchars($instructor['name']) ?>" class="ins-photo-img">
    <?php else: ?>
    <div class="ins-photo-ph">
      <div class="person-icon">👤</div>
      <small>강사 사진</small>
    </div>
    <?php endif; ?>
  </div>

  <!-- 우: 정보 -->
  <div class="ins-info-wrap">

    <!-- 이름 + 분야 -->
    <div class="ins-name"><?= htmlspecialchars($instructor['name']) ?></div>
    <div class="ins-category">
      <?= htmlspecialchars($instructor['field'] ?: ($instructor['category_name'] ?? '')) ?>
    </div>

    <!-- 소셜 아이콘 -->
    <?php $hasSocial = !empty($instructor['sns_youtube']) || !empty($instructor['sns_instagram']) || !empty($instructor['sns_facebook']); ?>
    <?php if ($hasSocial): ?>
    <div class="ins-social">
      <?php if (!empty($instructor['sns_youtube'])): ?>
      <a href="<?= htmlspecialchars($instructor['sns_youtube']) ?>" target="_blank" rel="noopener" class="social-icon-btn" title="유튜브">▶</a>
      <?php endif; ?>
      <?php if (!empty($instructor['sns_instagram'])): ?>
      <a href="<?= htmlspecialchars($instructor['sns_instagram']) ?>" target="_blank" rel="noopener" class="social-icon-btn" title="인스타그램">📷</a>
      <?php endif; ?>
      <?php if (!empty($instructor['sns_facebook'])): ?>
      <a href="<?= htmlspecialchars($instructor['sns_facebook']) ?>" target="_blank" rel="noopener" class="social-icon-btn" title="페이스북">f</a>
      <?php endif; ?>
    </div>
    <?php endif; ?>

    <!-- 강사소개 -->
    <div class="ins-row">
      <div class="ins-row-label">강사소개</div>
      <div class="ins-row-content">
        <?php if (!empty($instructor['intros'])): ?>
        <ul class="bullet-list">
          <?php foreach ($instructor['intros'] as $intro): ?>
          <li><?= htmlspecialchars($intro['content']) ?></li>
          <?php endforeach; ?>
        </ul>
        <?php else: ?>
        <div class="ins-empty-text">등록된 소개가 없습니다.</div>
        <?php endif; ?>
      </div>
    </div>

    <!-- 강사 경력 -->
    <div class="ins-row">
      <div class="ins-row-label">강사 경력</div>
      <div class="ins-row-content">
        <?php if (!empty($instructor['careers'])): ?>
        <ul class="bullet-list">
          <?php foreach ($instructor['careers'] as $career): ?>
          <li><?= htmlspecialchars($career['content']) ?></li>
          <?php endforeach; ?>
        </ul>
        <?php else: ?>
        <div class="ins-empty-text">등록된 경력이 없습니다.</div>
        <?php endif; ?>
      </div>
    </div>

  </div><!-- /ins-info-wrap -->
</div><!-- /ins-profile -->

<!-- 강의 목록 섹션 -->
<div class="ins-lecture-section">
  <div class="lecture-title">강의 목록</div>

  <?php if (empty($instructor['classes'])): ?>
  <div class="lecture-empty">등록된 강의가 없습니다.</div>
  <?php else: ?>
  <div class="lecture-grid">
    <?php foreach ($instructor['classes'] as $class): ?>
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
          <?php if ($class['type'] === 'free'): ?>
          <span class="ctag ct-free">무료</span>
          <?php else: ?>
          <span class="ctag ct-premium">프리미엄</span>
          <?php endif; ?>
        </div>
        <div class="card-title"><?= htmlspecialchars($class['title']) ?></div>
        <div class="card-meta"><?= htmlspecialchars($instructor['name']) ?></div>
      </div>
    </a>
    <?php endforeach; ?>
  </div>
  <?php endif; ?>
</div>
