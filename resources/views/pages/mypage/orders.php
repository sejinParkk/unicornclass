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

<div class="sub_index">
  <div class="inner">
    <?php require VIEW_PATH . '/components/mp-user-area.php'; ?>
    <div class="sub_page_flex">
      <?php require VIEW_PATH . '/components/mp-subnav.php'; ?>
      <div class="sub_page_contents">
        <div class="page-section-title">결제 내역</div>

        <?php if (empty($orders)): ?>
          <div class="notice-empty">결제 내역이 없습니다.</div>
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
          <div class="order-item" style="<?= $isRefunded ? 'opacity:.5' : '' ?>">
            <div class="order-item-top">
              <span class="order-date">
                <?= safeDt($o['paid_at'] ?? null, $o['created_at'] ?? 'now')->format('Y.m.d H:i') ?>
              </span>
              <span class="order-status <?= $sl['cls'] ?>"><?= $sl['text'] ?></span>
            </div>

            <div class="order-item-body">
              <div class="order-thumb" style="<?= $isRefunded ? 'opacity:.5' : '' ?>">
                <?php if ($o['thumbnail']): ?>
                  <img src="/uploads/class/<?= htmlspecialchars($o['thumbnail']) ?>" alt="" class="real_img">
                <?php else: ?>
                  <img src="/assets/img/logo.svg" alt="" class="none_img">
                <?php endif; ?>

                <p class="class_badge"><img src="/assets/img/<?=$o['class_type']?>_badge.png" alt=""></p>
              </div>
              <div class="order-info">
                <div class="order-class-type <?= $o['class_type'] === 'free' ? 'order-class-free' : 'order-class-premium' ?>">                  
                  <?= $o['class_type'] === 'free' ? '무료' : '프리미엄' ?>                  
                </div>
                <div class="order-class-title"><?= htmlspecialchars($o['class_title']) ?></div>                
                <p class="order-class-inst">
                  <?= htmlspecialchars($o['instructor_name'] ?? '') ?>
                  <?php if (!empty($o['category_name'])): ?>
                  · <?= htmlspecialchars($o['category_name']) ?>
                  <?php endif; ?>
                </p>
              </div>              
            </div>

            <div class="order-item-footer">
              <div class="order-amount <?= $isRefunded ? 'refunded' : '' ?>">
                <?php if ($isFree): ?>무료<?php else: ?><?= number_format((int)$o['amount']) ?>원<?php endif; ?>
              </div>
              <div class="btn-order-btns">
                <a href="/mypage/orders/<?= (int)$o['order_idx'] ?>" class="btn-order-btn">상세보기</a>
                <?php if ($showRefundBtn): ?>
                  <a href="/mypage/orders/<?= (int)$o['order_idx'] ?>" class="btn-order-btn btn-order-refund">환불 신청</a>
                <?php endif; ?>
              </div>
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