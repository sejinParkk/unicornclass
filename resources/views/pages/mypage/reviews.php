<?php
/**
 * 후기 목록
 * 변수: $reviewableClasses (array), $type (string), $page (int), $totalPages (int), $total (int)
 */
?>

<div class="sub_index">
  <div class="inner">
    <?php require VIEW_PATH . '/components/mp-user-area.php'; ?>
    <div class="sub_page_flex">
      <?php require VIEW_PATH . '/components/mp-subnav.php'; ?>
      <div class="sub_page_contents">
        <div class="page-section-title">후기 관리</div>

        <?php if (isset($_GET['saved'])): ?>
        <div style="background:#edf7f0;border:1px solid #b2dfdb;border-radius:6px;padding:10px 14px;margin-bottom:16px;font-size:13px;color:#27ae60">
          후기가 저장되었습니다. 감사합니다!
        </div>
        <?php endif; ?>

        <div class="faq-filters">
          <button class="faq-filter-btn <?= $type === '0' || $type === '' ? 'active' : '' ?>" onclick="location.href='/mypage/reviews?type=0'">
            후기 작성
          </button>
          <button class="faq-filter-btn <?= $type === '1' ? 'active' : '' ?>" onclick="location.href='/mypage/reviews?type=1'">
            나의 후기
          </button>
        </div>

        <?php if (empty($reviewableClasses)): ?>
          <div class="notice-empty">
            <?= $type === '1' ? '작성한 후기가 없습니다.' : '후기를 작성할 수 있는 강의가 없습니다.' ?>
          </div>
        <?php else: ?>
          <ul class="my_review_list">
          <?php foreach ($reviewableClasses as $c):
            $hasReview  = !empty($c['review_idx']);
            $rating     = (int) ($c['rating'] ?? 0);
            $starPct    = $rating > 0 ? round($rating / 5 * 100) : 0;
            $dateStr    = $c['paid_at'] ?? $c['enrolled_at'];
            try { $displayDate = (new \DateTimeImmutable($dateStr))->format('Y.m.d'); }
            catch (\Exception $e) { $displayDate = ''; }
          ?>
          <li class="my_review_li">

            <!-- 강의 행 -->
            <div class="my_review_top">
              <!-- 썸네일 -->
              <div class="my_review_thumb">
                <?php if ($c['thumbnail']): ?>
                  <img src="/uploads/class/<?= htmlspecialchars($c['thumbnail']) ?>" alt="" class="real_img">
                <?php else: ?>
                  <img src="/assets/img/logo.svg" alt="" class="none_img">
                <?php endif; ?>

                <p class="class_badge"><img src="/assets/img/premium_badge.png" alt=""></p>
              </div>

              <!-- 강의 정보 -->
              <div class="my_review_info">
                <div class="my_review_left">
                  <div class="my_review_title"><?= htmlspecialchars($c['class_title']) ?></div>
                  <div class="my_review_paydate">결제일 <?= $displayDate ?></div>
                </div>
                <div class="my_review_right">
                  <?php if ($hasReview): ?>
                    <?php $rimages = $reviewImageMap[(int)$c['review_idx']] ?? []; ?>
                    <button type="button" class="my_review_btn"
                      data-class-title="<?= htmlspecialchars($c['class_title']) ?>"
                      data-review-title="<?= htmlspecialchars($c['review_title'] ?? '') ?>"
                      data-rating="<?= $rating ?>"
                      data-star-pct="<?= $starPct ?>"
                      data-content="<?= htmlspecialchars($c['content'] ?? '') ?>"
                      data-thumbnail="<?= htmlspecialchars($c['thumbnail'] ?? '') ?>"
                      data-images="<?= htmlspecialchars(json_encode($rimages)) ?>"
                      onclick="openReviewModal(this)">리뷰 보기</button>
                    <a href="/mypage/reviews/write?edit=<?= (int)$c['review_idx'] ?>" class="my_review_btn">리뷰 수정</a>
                  <?php else: ?>
                    <a href="/mypage/reviews/write?class_idx=<?= (int)$c['class_idx'] ?>" class="my_review_btn ver2">후기 작성</a>
                  <?php endif; ?>
                </div>
              </div>
            </div>

            <!-- 후기 행 (작성된 경우만) -->
            <?php if ($hasReview): ?>
            <div class="my_review_box">
              <?php if (!empty($c['review_title'])): ?>
              <p class="my_review_subject"><?= htmlspecialchars($c['review_title']) ?></p>
              <?php endif; ?>
              <div class="my_review_star">
                <div class="my_review_stars">
                  <p class="my_review_star-front"><img src="/assets/img/star_avg.png" alt=""></p>
                  <p class="my_review_star-bar" style="width:<?= $starPct ?>%;"></p>
                </div>
                <p class="my_review_avg"><?= number_format($rating, 1) ?></p>
              </div>
              <div class="my_review_content"><?= htmlspecialchars($c['content'] ?? '') ?></div>
            </div>
            <?php endif; ?>

          </li>
          <?php endforeach; ?>
        </ul>

        <!-- 페이징 -->
        <?php if ($totalPages > 1): ?>
        <div class="pagination">
          <?php if ($page > 1): ?>
            <a href="/mypage/reviews?<?= http_build_query(['type' => $type, 'page' => $page - 1]) ?>" class="page-btn">&laquo;</a>
          <?php endif; ?>
          <?php for ($i = 1; $i <= $totalPages; $i++): ?>
            <a href="/mypage/reviews?<?= http_build_query(['type' => $type, 'page' => $i]) ?>"
               class="page-btn <?= $i === $page ? 'active' : '' ?>"><?= $i ?></a>
          <?php endfor; ?>
          <?php if ($page < $totalPages): ?>
            <a href="/mypage/reviews?<?= http_build_query(['type' => $type, 'page' => $page + 1]) ?>" class="page-btn">&raquo;</a>
          <?php endif; ?>
        </div>
        <?php endif; ?>

        <?php endif; ?>
      </div>
    </div>
  </div>
</div>

<!-- 후기 상세 모달 -->
<div id="review-modal" class="review-modal-overlay" style="display:none"
     onclick="if(event.target===this)closeReviewModal()">
  <div class="review-modal-box">
    <button class="review-modal-close" onclick="closeReviewModal()"></button>
    <div class="review-modal_wrap">
      <div id="rm-img-area" class="rm-img-area" style="display:none">
        <div class="swiper rm-img-swiper" id="rm-img-swiper">
          <div class="swiper-wrapper" id="rm-img-slides"></div>
          <div class="swiper-pagination"></div>
          <div class="swiper-button-prev"></div>
          <div class="swiper-button-next"></div>
        </div>
      </div>
      <div class="rm-body">
        <div class="rv-class-title" id="rm-title"></div>
        <div class="rv-head">
          <div class="rv-stars">
            <p class="rv-star-front"><img src="/assets/img/star_avg.png" alt=""></p>
            <p class="rv-star-bar" id="rm-star-bar"></p>
          </div>
          <p class="rv-avg" id="rm-avg"></p>
        </div>
        <div class="rv-content rv-content--full" id="rm-content"></div>
        <div class="rv-class-info">
          <div class="rv-class-img" id="rm-class-img"></div>
          <p class="rv-class-name" id="rm-class-name"></p>
        </div>
      </div>
    </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js"></script>
<script>
var _rmSwiper = null;

function openReviewModal(btn) {
  var reviewTitle = btn.dataset.reviewTitle || '';
  var classTitle  = btn.dataset.classTitle  || '';
  var rating      = parseInt(btn.dataset.rating, 10) || 0;
  var starPct     = parseInt(btn.dataset.starPct, 10) || 0;
  var content     = btn.dataset.content    || '';
  var thumbnail   = btn.dataset.thumbnail  || '';
  var images      = JSON.parse(btn.dataset.images || '[]');

  document.getElementById('rm-title').textContent      = reviewTitle || classTitle;
  document.getElementById('rm-avg').textContent        = rating.toFixed ? rating.toFixed(1) : rating;
  document.getElementById('rm-star-bar').style.width   = starPct + '%';
  document.getElementById('rm-content').textContent    = content;
  document.getElementById('rm-class-name').textContent = classTitle;

  var imgEl = document.getElementById('rm-class-img');
  var badge = '<p class="class_badge"><img src="/assets/img/premium_badge.png" alt=""></p>';
  imgEl.innerHTML = (thumbnail
    ? '<img src="/uploads/class/' + thumbnail + '" alt="">'
    : '<div class="rv-thumb-ph"><img src="/assets/img/logo.svg" alt=""></div>') + badge;

  // 이미지 스와이퍼
  var imgArea   = document.getElementById('rm-img-area');
  var imgSlides = document.getElementById('rm-img-slides');

  if (_rmSwiper) { _rmSwiper.destroy(true, true); _rmSwiper = null; }

  if (images.length > 0) {
    imgSlides.innerHTML = images.map(function(f) {
      return '<div class="swiper-slide"><img src="/uploads/review/' + f + '" alt="후기 이미지"></div>';
    }).join('');
    imgArea.style.display = 'block';

    _rmSwiper = new Swiper('#rm-img-swiper', {
      loop: images.length > 1,
      grabCursor: true,
      pagination: { el: '#rm-img-swiper .swiper-pagination', clickable: true },
      navigation: {
        prevEl: '#rm-img-swiper .swiper-button-prev',
        nextEl: '#rm-img-swiper .swiper-button-next',
      },
    });
  } else {
    imgArea.style.display = 'none';
    imgSlides.innerHTML   = '';
  }

  document.getElementById('review-modal').style.display = 'flex';
}

function closeReviewModal() {
  document.getElementById('review-modal').style.display = 'none';
}
</script>
