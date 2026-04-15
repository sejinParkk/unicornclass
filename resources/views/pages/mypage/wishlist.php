<?php
/**
 * 찜목록
 * 변수: $wishlist (array)
 */

$now = new DateTimeImmutable();
$csrfToken = \App\Core\Csrf::token();
?>

<div class="mp-content-title">
  찜목록
  <span>총 <?= count($wishlist) ?>개</span>
</div>

<?php if (empty($wishlist)): ?>
<div class="mp-empty">
  <div class="mp-empty-icon">❤️</div>
  찜한 강의가 없습니다.<br>
  <a href="/classes" style="color:#c0392b;font-size:13px;margin-top:8px;display:inline-block">강의 둘러보기</a>
</div>
<?php else: ?>

<div class="wish-grid">
  <?php foreach ($wishlist as $w):
    $isFree     = $w['class_type'] === 'free';
    $isEnrolled = (bool) $w['is_enrolled'];
    $saleEnded  = !empty($w['sale_end_at']) && (new DateTimeImmutable($w['sale_end_at'])) < $now;
    $thumbOpacity = $saleEnded ? '0.4' : ($isEnrolled ? '0.7' : '1');
  ?>
  <div class="wish-card" id="wish-<?= (int)$w['wish_idx'] ?>">

    <!-- 썸네일 -->
    <div class="wish-thumb" style="opacity:<?= $thumbOpacity ?>">
      <?php if ($w['thumbnail']): ?>
        <img src="/uploads/class/<?= htmlspecialchars($w['thumbnail']) ?>" alt="">
      <?php else: ?>
        🎬
      <?php endif; ?>
      <button class="wish-remove"
              onclick="removeWish(<?= (int)$w['wish_idx'] ?>, <?= (int)$w['class_idx'] ?>)"
              title="찜 해제">✕</button>
    </div>

    <!-- 정보 -->
    <div class="wish-info">
      <a href="/classes/<?= (int)$w['class_idx'] ?>" class="wish-title"
         style="text-decoration:none;color:inherit;display:block">
        <?= htmlspecialchars($w['title']) ?>
      </a>

      <?php if ($saleEnded): ?>
        <div class="wish-price" style="color:#aaa">판매 종료</div>
        <div class="wish-btns">
          <button style="height:32px;width:100%;background:#f0f0f0;color:#aaa;border-radius:5px;font-size:11px;font-weight:600;border:none;cursor:default">
            구매 불가
          </button>
        </div>

      <?php elseif ($isEnrolled): ?>
        <?php if ($isFree): ?>
          <div class="wish-price free-tag">무료</div>
          <div class="wish-btns">
            <div class="wish-enrolled-badge" style="width:100%">✅ 수강신청 완료</div>
          </div>
        <?php else: ?>
          <div class="wish-price">
            <?= number_format((int)$w['price']) ?>원
            <?php if ($w['price_origin'] > $w['price']): ?>
              <span class="origin"><?= number_format((int)$w['price_origin']) ?>원</span>
            <?php endif; ?>
          </div>
          <div class="wish-btns">
            <div class="wish-purchased-badge" style="width:100%">✅ 수강 중</div>
          </div>
        <?php endif; ?>

      <?php else: ?>
        <?php if ($isFree): ?>
          <div class="wish-price free-tag">무료</div>
          <div class="wish-btns">
            <a href="/classes/<?= (int)$w['class_idx'] ?>"
               class="btn-buy" style="background:#27ae60;width:100%">수강 신청</a>
          </div>
        <?php else: ?>
          <div class="wish-price">
            <?= number_format((int)$w['price']) ?>원
            <?php if ($w['price_origin'] > $w['price']): ?>
              <span class="origin"><?= number_format((int)$w['price_origin']) ?>원</span>
            <?php endif; ?>
          </div>
          <div class="wish-btns">
            <a href="/classes/<?= (int)$w['class_idx'] ?>"
               class="btn-buy" style="width:100%">바로 결제</a>
          </div>
        <?php endif; ?>
      <?php endif; ?>

    </div>
  </div>
  <?php endforeach; ?>
</div>

<?php endif; ?>

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
