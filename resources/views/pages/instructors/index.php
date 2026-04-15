<?php
// $list, $total, $page, $pages 가 컨트롤러에서 전달됨
?>
<!-- 서브 배너 -->
<div class="ins-banner">
  <div class="ins-banner-bg"></div>
  <div class="ins-banner-label">유니콘클래스의</div>
  <div class="ins-banner-title">강사소개</div>
</div>

<div class="ins-content">

  <!-- 목록 헤더 -->
  <div class="ins-list-header">
    <div class="ins-list-title">강사진</div>
    <div class="ins-list-count">총 <?= number_format($total) ?>명</div>
  </div>

  <!-- 강사 카드 그리드 -->
  <?php if (empty($list)): ?>
  <p style="text-align:center;padding:60px 0;color:#aaa;font-size:14px">등록된 강사가 없습니다.</p>
  <?php else: ?>
  <div class="ins-grid">
    <?php foreach ($list as $ins): ?>
    <a href="/instructors/<?= $ins['instructor_idx'] ?>" class="i-card-wrap">
      <div class="i-card">
        <!-- 사진 -->
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
        <!-- 정보 -->
        <div class="i-info">
          <div class="i-name"><?= htmlspecialchars($ins['name']) ?></div>
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
        </div>
      </div>
    </a>
    <?php endforeach; ?>
  </div>
  <?php endif; ?>

  <!-- CTA 섹션 -->
  <div class="ins-cta">
    <div class="ins-cta-bg"></div>
    <div class="ins-cta-inner">
      <div class="ins-cta-en">Join Us</div>
      <div class="ins-cta-title">유니콘클래스 강사가 되어보세요</div>
      <div class="ins-cta-desc">
        당신의 노하우와 경험을 수강생들과 나눠보세요.<br>
        유니콘클래스와 함께 더 많은 사람들에게 가치를 전달할 수 있습니다.
      </div>
      <a href="/instructors/apply" class="ins-cta-btn">🎓 강사 지원하기 →</a>
    </div>
  </div>

  <!-- 페이지네이션 -->
  <?php if ($pages > 1): ?>
  <div class="pagination">
    <a href="?page=<?= max(1, $page - 1) ?>" class="page-arrow <?= $page <= 1 ? 'disabled' : '' ?>">‹</a>
    <?php for ($i = 1; $i <= $pages; $i++): ?>
    <a href="?page=<?= $i ?>" class="page-btn <?= $i === $page ? 'active' : '' ?>"><?= $i ?></a>
    <?php endfor; ?>
    <a href="?page=<?= min($pages, $page + 1) ?>" class="page-arrow <?= $page >= $pages ? 'disabled' : '' ?>">›</a>
  </div>
  <?php endif; ?>

</div>
