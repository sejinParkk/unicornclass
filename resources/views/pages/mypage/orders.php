<?php
/**
 * 결제내역 목록
 * 변수: $orders, $total, $page, $totalPages, $limit
 */

$statusLabel = [
    'paid'       => ['text' => '결제완료',    'cls' => 'paid'],
    'refund_req' => ['text' => '환불신청중',  'cls' => 'refund_req'],
    'refunded'   => ['text' => '환불완료',    'cls' => 'refunded'],
    'free'       => ['text' => '수강신청완료', 'cls' => 'paid'],
];
$now = new \DateTimeImmutable();

// null 또는 잘못된 날짜 문자열에 안전한 DateTimeImmutable 생성 헬퍼
function safeDt(?string $val, string $fallback = 'now'): \DateTimeImmutable {
    if (empty($val) || $val === '0000-00-00 00:00:00') {
        return new \DateTimeImmutable($fallback);
    }
    try {
        return new \DateTimeImmutable($val);
    } catch (\Throwable $e) {
        return new \DateTimeImmutable($fallback);
    }
}
?>

<div class="mp-content-title">결제내역</div>

<?php if (empty($orders)): ?>
<div class="mp-empty">
  <div class="mp-empty-icon">💳</div>
  결제 내역이 없습니다.
</div>
<?php else: ?>

<div class="order-list">
  <?php foreach ($orders as $o):
    $sl        = $statusLabel[$o['status']] ?? ['text' => $o['status'], 'cls' => ''];
    $isFree    = $o['status'] === 'free';
    $isPaid    = $o['status'] === 'paid';
    $isRefunded = $o['status'] === 'refunded';

    // 환불 신청 버튼 노출: paid + (7일 이내 OR 진도율 33% 미만)
    $showRefundBtn = false;
    if ($isPaid && $o['paid_at']) {
        $paidAt    = safeDt($o['paid_at']);
        $withinDay = $paidAt->modify('+7 days') >= $now;
        $rate      = (int)$o['total_episodes'] > 0
            ? (int)$o['done_count'] / (int)$o['total_episodes'] * 100 : 0;
        $showRefundBtn = $withinDay || $rate < 33;
    }
  ?>
  <div class="order-item" style="<?= $isRefunded ? 'opacity:.7' : '' ?>">
    <div class="order-item-top">
      <span class="order-date">
        <?= safeDt($o['paid_at'] ?? null, $o['created_at'] ?? 'now')->format('Y.m.d H:i') ?>
      </span>
      <span class="order-status <?= $sl['cls'] ?>"><?= $sl['text'] ?></span>
    </div>

    <div class="order-item-body">
      <div class="order-thumb" style="<?= $isRefunded ? 'opacity:.5' : '' ?>">
        <?php if ($o['thumbnail']): ?>
          <img src="/uploads/class/<?= htmlspecialchars($o['thumbnail']) ?>" alt="">
        <?php else: ?>
          <div style="width:100%;height:100%;display:flex;align-items:center;justify-content:center;font-size:16px;color:rgba(255,255,255,.2);background:linear-gradient(135deg,#1a2534,#2d4060)">🎬</div>
        <?php endif; ?>
      </div>
      <div class="order-info">
        <div class="order-class-title" style="<?= $isRefunded ? 'color:#aaa' : '' ?>">
          <?= htmlspecialchars($o['class_title']) ?>
        </div>
        <div class="order-class-type">
          <span class="<?= $o['class_type'] === 'free' ? 'badge-free' : 'badge-premium' ?>">
            <?= $o['class_type'] === 'free' ? '무료' : '프리미엄' ?>
          </span>
        </div>
      </div>
      <div class="order-amount" style="<?= $isRefunded ? 'color:#bbb' : '' ?>">
        <?php if ($isFree): ?>
          <span class="free">무료</span>
        <?php else: ?>
          <?= number_format((int)$o['amount']) ?>원
        <?php endif; ?>
      </div>
    </div>

    <div class="order-item-footer">
      <a href="/mypage/orders/<?= (int)$o['order_idx'] ?>" class="btn-order-detail">상세보기</a>
      <?php if ($showRefundBtn): ?>
        <a href="/mypage/orders/<?= (int)$o['order_idx'] ?>" class="btn-refund">환불 신청</a>
      <?php endif; ?>
    </div>
  </div>
  <?php endforeach; ?>
</div>

<!-- 페이지네이션 -->
<?php if ($totalPages > 1): ?>
<div class="mp-pager">
  <?php if ($page > 1): ?>
    <a href="?page=<?= $page - 1 ?>" class="mp-pg-btn">‹</a>
  <?php else: ?>
    <span class="mp-pg-btn disabled">‹</span>
  <?php endif; ?>

  <?php for ($i = 1; $i <= $totalPages; $i++): ?>
    <a href="?page=<?= $i ?>" class="mp-pg-btn <?= $i === $page ? 'active' : '' ?>"><?= $i ?></a>
  <?php endfor; ?>

  <?php if ($page < $totalPages): ?>
    <a href="?page=<?= $page + 1 ?>" class="mp-pg-btn">›</a>
  <?php else: ?>
    <span class="mp-pg-btn disabled">›</span>
  <?php endif; ?>
</div>
<?php endif; ?>

<?php endif; ?>
