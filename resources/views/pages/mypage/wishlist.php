<?php
/**
 * 찜목록
 * 변수: $wishlist (array)
 */

$now = new DateTimeImmutable();
$csrfToken = \App\Core\Csrf::token();
?>

<div class="sub_index">
  <div class="inner">
    <?php require VIEW_PATH . '/components/mp-user-area.php'; ?>
    <div class="sub_page_flex">
      <?php require VIEW_PATH . '/components/mp-subnav.php'; ?>
      <div class="sub_page_contents">
        <div class="page-section-title">찜 목록 <!--<span>총 <?= count($wishlist) ?>개</span>--></div>
        <?php if (empty($wishlist)): ?>
          <div class="notice-empty">찜한 강의가 없습니다.</div>

        <?php else: ?>
          <div class="wish-grid">
            <?php foreach ($wishlist as $w):
              $isFree       = $w['class_type'] === 'free';
              $isEnrolled   = (bool) $w['is_enrolled'];
              $saleEnded    = !empty($w['sale_end_at']) && (new DateTimeImmutable($w['sale_end_at'])) < $now;
              $thumbOpacity = $saleEnded ? '0.45' : '1';
            ?>
            <div class="wish-card" id="wish-<?= (int)$w['wish_idx'] ?>">

              <!-- 썸네일 -->
              <div class="wish-thumb" style="opacity:<?= $thumbOpacity ?>">
                <a href="/classes/<?= (int)$w['class_idx'] ?>">
                  <?php if ($w['thumbnail']): ?>
                    <img src="/uploads/class/<?= htmlspecialchars($w['thumbnail']) ?>" alt="" class="real_img">
                  <?php else: ?>
                    <img src="/assets/img/logo.svg" class="none_img">
                  <?php endif; ?>      
                  
                  <p class="ctag ct-premium">
                    <img src="/assets/img/hero_star_fff.svg" alt="">
                    <span>
                      <?php if ($w['class_type'] === 'free'): ?>
                      무료 강의
                      <?php else: ?>
                      프리미엄 강의
                      <?php endif; ?>
                    </span>
                  </p>
                </a>
              </div>

              <!-- 정보 -->
              <div class="wish-info">
                <button class="wish-remove"
                        onclick="removeWish(<?= (int)$w['wish_idx'] ?>, <?= (int)$w['class_idx'] ?>)"
                        title="찜 해제"><img src="/assets/img/icon_like_on.svg" alt=""></button>
                <a href="/classes/<?= (int)$w['class_idx'] ?>" class="wish-title"><?= htmlspecialchars($w['title']) ?></a>

                <?php if ($saleEnded): ?>
                  <div class="wish-price wish-nosell">판매 종료</div>
                  <div class="wish-btns">
                    <button type="button" class="wish-btn ver3">구매 불가</button>
                  </div>

                <?php elseif ($isEnrolled): ?>
                  <?php if ($isFree): ?>
                    <div class="wish-price free-tag">무료</div>
                    <div class="wish-btns">
                      <div class="wish-btn ver2">수강신청 완료</div>
                    </div>
                  <?php else: ?>
                    <div class="wish-price">                      
                      <?php if ($w['price_origin'] > $w['price']): ?>
                        <span class="origin"><?= number_format((int)$w['price_origin']) ?>원</span>
                      <?php endif; ?>
                      <?= number_format((int)$w['price']) ?>원
                    </div>
                    <div class="wish-btns">
                      <div class="wish-btn ver2">수강 중</div>
                    </div>
                  <?php endif; ?>

                <?php else: ?>
                  <?php if ($isFree): ?>
                    <div class="wish-price free-tag">무료</div>
                    <div class="wish-btns">
                      <a href="/classes/<?= (int)$w['class_idx'] ?>" class="wish-btn">수강 신청</a>
                    </div>
                  <?php else: ?>
                    <div class="wish-price">                      
                      <?php if ($w['price_origin'] > $w['price']): ?>
                        <span class="origin"><?= number_format((int)$w['price_origin']) ?>원</span>
                      <?php endif; ?>
                      <?= number_format((int)$w['price']) ?>원
                    </div>
                    <div class="wish-btns">
                      <a href="/classes/<?= (int)$w['class_idx'] ?>" class="wish-btn">바로 결제</a>
                    </div>
                  <?php endif; ?>
                <?php endif; ?>

              </div>
            </div>
            <?php endforeach; ?>
          </div>

          <?php if ($totalPages > 1): ?>
          <div class="pagination">
            <a href="?page=<?= max(1, $page - 1) ?>" class="page-btn page-prev <?= $page <= 1 ? 'disabled' : '' ?>"></a>
            <?php for ($i = 1; $i <= $totalPages; $i++): ?>
            <a href="?page=<?= $i ?>" class="page-btn <?= $i === $page ? 'active' : '' ?>"><?= $i ?></a>
            <?php endfor; ?>
            <a href="?page=<?= min($totalPages, $page + 1) ?>" class="page-btn page-next <?= $page >= $totalPages ? 'disabled' : '' ?>"></a>
          </div>
          <?php endif; ?>

        <?php endif; ?>
      </div>
    </div>
  </div>
</div>

<script>
function removeWish(wishIdx, classIdx) {
  if (!confirm('찜 목록에서 삭제하시겠습니까?')) return;

  fetch('/api/wish/' + classIdx, {
    method: 'POST',
    headers: {'Content-Type': 'application/x-www-form-urlencoded'},
    body: 'csrf_token=<?= htmlspecialchars($csrfToken) ?>'
  })
  .then(r => r.json())
  .then(data => {
    if (data.wished === false || data.success) {
      const card = document.getElementById('wish-' + wishIdx);
      if (card) {
        card.style.transition = 'opacity .3s';
        card.style.opacity    = '0';
        setTimeout(() => { card.remove(); updateCount(); }, 300);
      }
    }
  })
  .catch(() => alert('오류가 발생했습니다. 다시 시도해주세요.'));
}

function updateCount() {
  const remaining = document.querySelectorAll('.wish-card').length;
  const title     = document.querySelector('.mp-content-title');
  if (title) title.innerHTML = '찜목록 <span>총 ' + remaining + '개</span>';
  if (remaining === 0) {
    const grid = document.querySelector('.wish-grid');
    if (grid) {
      grid.outerHTML =
        '<div class="mp-empty"><div class="mp-empty-icon">❤️</div>' +
        '찜한 강의가 없습니다.<br>' +
        '<a href="/classes" style="color:#c0392b;font-size:13px;margin-top:8px;display:inline-block">강의 둘러보기</a></div>';
    }
  }
}
</script>
