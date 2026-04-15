<?php
/**
 * 후기 목록
 * 변수: $reviewableClasses (array), $myReviews (array — 미사용, 하위 호환)
 *
 * $reviewableClasses 한 행에 강의 + 후기(있으면) 가 모두 포함됨 (LEFT JOIN)
 */

function renderStars(int $rating): string {
    $out = '';
    for ($i = 1; $i <= 5; $i++) {
        $out .= $i <= $rating ? '★' : '☆';
    }
    return $out;
}

$totalWritten   = count(array_filter($reviewableClasses, fn($c) => !empty($c['review_idx'])));
$totalWritable  = count(array_filter($reviewableClasses, fn($c) =>  empty($c['review_idx'])));
?>

<div class="mp-content-title">
  내 후기
  <span>작성완료 <?= $totalWritten ?>개 · 미작성 <?= $totalWritable ?>개</span>
</div>

<?php if (isset($_GET['saved'])): ?>
<div style="background:#edf7f0;border:1px solid #b2dfdb;border-radius:6px;padding:10px 14px;margin-bottom:16px;font-size:13px;color:#27ae60">
  후기가 저장되었습니다. 감사합니다!
</div>
<?php endif; ?>

<?php if (empty($reviewableClasses)): ?>
<div class="mp-empty">
  <div class="mp-empty-icon">⭐</div>
  프리미엄 수강 강의가 없습니다.
</div>
<?php else: ?>

<ul style="list-style:none;padding:0;margin:0;display:flex;flex-direction:column;gap:12px">
  <?php foreach ($reviewableClasses as $c):
    $hasReview = !empty($c['review_idx']);
  ?>
  <li style="background:#fff;border:1px solid #eee;border-radius:12px;overflow:hidden">

    <!-- ── 강의 행 ── -->
    <div style="display:flex;align-items:center;gap:14px;padding:14px 18px;
                border-bottom:<?= $hasReview ? '1px solid #f5f5f5' : 'none' ?>">

      <!-- 썸네일 -->
      <div style="width:64px;height:46px;border-radius:6px;overflow:hidden;flex-shrink:0;
                  background:linear-gradient(135deg,#1a2534,#2d4060);
                  display:flex;align-items:center;justify-content:center;
                  font-size:14px;color:rgba(255,255,255,.3)">
        <?php if ($c['thumbnail']): ?>
          <img src="/uploads/class/<?= htmlspecialchars($c['thumbnail']) ?>"
               alt="" style="width:100%;height:100%;object-fit:cover">
        <?php else: ?>
          🎬
        <?php endif; ?>
      </div>

      <!-- 강의 정보 -->
      <div style="flex:1;min-width:0">
        <div style="font-size:13px;font-weight:700;color:#111;
                    white-space:nowrap;overflow:hidden;text-overflow:ellipsis">
          <?= htmlspecialchars($c['title']) ?>
        </div>
        <div style="font-size:11px;color:#aaa;margin-top:3px">
          수강시작 <?= (new \DateTimeImmutable($c['enrolled_at']))->format('Y.m.d') ?>
          <?php if ($hasReview): ?>
            <span style="margin-left:6px;color:#27ae60;font-weight:600">✓ 후기 작성완료</span>
          <?php endif; ?>
        </div>
      </div>

      <!-- 버튼 -->
      <?php if ($hasReview): ?>
        <a href="/mypage/reviews/write?edit=<?= (int)$c['review_idx'] ?>"
           style="height:32px;padding:0 14px;background:#f0f0f0;color:#777;
                  border-radius:6px;font-size:12px;font-weight:600;white-space:nowrap;
                  text-decoration:none;display:inline-flex;align-items:center;flex-shrink:0">
          수정하기
        </a>
      <?php else: ?>
        <a href="/mypage/reviews/write?class_idx=<?= (int)$c['class_idx'] ?>"
           style="height:32px;padding:0 14px;background:#c0392b;color:#fff;
                  border-radius:6px;font-size:12px;font-weight:700;white-space:nowrap;
                  text-decoration:none;display:inline-flex;align-items:center;flex-shrink:0">
          후기 작성
        </a>
      <?php endif; ?>
    </div>

    <!-- ── 후기 행 (작성된 경우만) ── -->
    <?php if ($hasReview): ?>
    <div style="padding:14px 18px;background:#fafafa">
      <!-- 별점 + 날짜 -->
      <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:8px">
        <div style="color:#f39c12;font-size:15px;letter-spacing:1px">
          <?= renderStars((int)$c['rating']) ?>
          <span style="font-size:11px;color:#888;margin-left:4px;letter-spacing:0"><?= (int)$c['rating'] ?>점</span>
        </div>
        <div style="font-size:11px;color:#bbb">
          <?= (new \DateTimeImmutable($c['review_at']))->format('Y.m.d') ?>
        </div>
      </div>
      <!-- 내용 -->
      <div style="font-size:13px;color:#555;line-height:1.8">
        <?= nl2br(htmlspecialchars(mb_substr($c['content'], 0, 200))) ?>
        <?= mb_strlen($c['content']) > 200 ? '...' : '' ?>
      </div>
    </div>
    <?php endif; ?>

  </li>
  <?php endforeach; ?>
</ul>

<?php endif; ?>
