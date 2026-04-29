<?php
// $list, $total, $page, $pages 가 컨트롤러에서 전달됨
?>
<div class="sub_index">
  <div class="inner">
    <!-- 서브 배너 -->
    <div class="sub-banner sub-banner-ins">
      <div class="sub-banner-title">강사소개</div>
      <div class="sub-banner-label">수익화 전문가 유니콘 클래스 강사진을 소개합니다</div>      
    </div>

    <div class="ins-content">

      <!-- 목록 헤더 -->
      <div class="ins-list-header">
        <div class="ins-list-title">
          강사진 <span><?= number_format($total) ?></span>
        </div>
      </div>  
      <!-- 강사 카드 그리드 -->
      <?php if (empty($list)): ?>
      <p class="lecture-empty">등록된 강사가 없습니다.</p>
      <?php else: ?>
      <div class="search-ins-grid cl-grid ins-grid">
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
            <div class="i-info ver2">
              <div class="i-name"><?= htmlspecialchars($ins['name']) ?></div>
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
            </div>
          </div>
        </a>
        <?php endforeach; ?>
      </div>

      <!-- 페이지네이션 -->
      <?php if ($pages > 1): ?>
      <div class="pagination">
        <a href="?page=<?= max(1, $page - 1) ?>" class="page-btn page-prev <?= $page <= 1 ? 'disabled' : '' ?>" class=""></a>
        <?php for ($i = 1; $i <= $pages; $i++): ?>
        <a href="?page=<?= $i ?>" class="page-btn <?= $i === $page ? 'active' : '' ?>"><?= $i ?></a>
        <?php endfor; ?>
        <a href="?page=<?= min($pages, $page + 1) ?>" class="page-btn page-next <?= $page >= $pages ? 'disabled' : '' ?>"></a>
      </div>
      <?php endif; ?>
      <?php endif; ?>

      <!-- CTA 섹션 -->
      <div class="ins-cta">          
        <div class="ins-cta-title">유니콘 클래스 강사가 되어보세요!</div>
        <div class="ins-cta-desc">
          당신의 노하우와 경험을 수강생들과 나눠보세요.<br>
          유니콘클래스와 함께 더 많은 사람들에게 가치를 전달할 수 있습니다.
        </div>
        <a href="/instructors/apply" class="ins-cta-btn">
          <span>강사 지원하기</span>
          <img src="/assets/img/inst_submit_arr.svg" alt="">
        </a>
      </div>      

    </div>
  </div>
</div>