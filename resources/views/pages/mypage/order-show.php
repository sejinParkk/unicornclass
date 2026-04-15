<?php
/**
 * 결제내역 상세
 * 변수: $order, $canRefund, $csrfToken
 */

$statusLabel = [
    'paid'       => '결제완료',
    'refund_req' => '환불신청중',
    'refunded'   => '환불완료',
    'free'       => '수강신청완료',
];

$now       = new \DateTimeImmutable();

// null / zero-date 안전 헬퍼
function safeDtOrder(?string $val): ?\DateTimeImmutable {
    if (empty($val) || $val === '0000-00-00 00:00:00') return null;
    try { return new \DateTimeImmutable($val); } catch (\Throwable $e) { return null; }
}

$paidAtObj = safeDtOrder($order['paid_at'] ?? null);

// 진도율 계산
$rate = 0;
if ((int)$order['total_episodes'] > 0) {
    $rate = (int) round((int)$order['done_count'] / (int)$order['total_episodes'] * 100);
}

// 비례환불 예상금액 계산
$refundAmount = 0;
if ($canRefund && $paidAtObj && (int)$order['amount'] > 0) {
    if ($rate < 33 && $paidAtObj->modify('+7 days') >= $now) {
        $refundAmount = (int)$order['amount']; // 전액
        $refundType   = '전액 환불';
    } else {
        // 잔여기간 비례
        $enrollRow = \App\Core\DB::selectOne(
            "SELECT enrolled_at, expire_at FROM lc_enroll
             WHERE member_idx = ? AND class_idx = ? LIMIT 1",
            [(int)$order['member_idx'], (int)$order['class_idx']]
        );
        if ($enrollRow && $enrollRow['expire_at']) {
            $startDt   = safeDtOrder($enrollRow['enrolled_at']) ?? $now;
            $expireDt  = safeDtOrder($enrollRow['expire_at'])   ?? $now;
            $totalDays = (int) $startDt->diff($expireDt)->days;
            $elapsedDays = (int) $startDt->diff($now)->days;
            $remainDays  = max(0, $totalDays - $elapsedDays);
            $perDay      = $totalDays > 0 ? (int)$order['amount'] / $totalDays : 0;
            $refundAmount = (int) round($perDay * $remainDays);
            $refundType   = '잔여기간 비례 환불';
        } else {
            $refundType = '잔여기간 비례 환불';
        }
    }
}

$isFree    = $order['status'] === 'free';
$isRefunded = $order['status'] === 'refunded';

$refundedMsg = '';
if (isset($_GET['refund'])) $refundedMsg = 'success';
if (isset($_GET['err']))    $refundedMsg = $_GET['err'];
?>

<?php if ($refundedMsg === 'success'): ?>
<div style="background:#edf7f0;border:1px solid #b2dfdb;border-radius:8px;padding:12px 16px;margin-bottom:16px;font-size:13px;color:#27ae60;font-weight:600;">
  ✅ 환불 신청이 접수되었습니다. 관리자 검토 후 3~5 영업일 내 처리됩니다.
</div>
<?php elseif ($refundedMsg === 'reason'): ?>
<div style="background:#fef0ee;border:1px solid #f5c6c6;border-radius:8px;padding:12px 16px;margin-bottom:16px;font-size:13px;color:#c0392b;">
  환불 사유를 선택해주세요.
</div>
<?php elseif ($refundedMsg === 'fail'): ?>
<div style="background:#fef0ee;border:1px solid #f5c6c6;border-radius:8px;padding:12px 16px;margin-bottom:16px;font-size:13px;color:#c0392b;">
  환불 신청에 실패했습니다. 이미 처리된 주문이거나 환불 불가 상태입니다.
</div>
<?php endif; ?>

<div class="mp-content-title">결제내역 상세</div>

<!-- 강의 정보 -->
<div class="order-detail-header">
  <div class="od-thumb">
    <?php if ($order['thumbnail']): ?>
      <img src="/uploads/class/<?= htmlspecialchars($order['thumbnail']) ?>" alt="">
    <?php else: ?>
      <div style="width:100%;height:100%;display:flex;align-items:center;justify-content:center;font-size:20px;color:rgba(255,255,255,.2);background:linear-gradient(135deg,#1a2534,#2d4060)">🎬</div>
    <?php endif; ?>
  </div>
  <div class="od-info">
    <div class="od-title"><?= htmlspecialchars($order['class_title']) ?></div>
    <span class="od-type-badge <?= $order['class_type'] === 'free' ? 'free' : 'premium' ?>">
      <?= $order['class_type'] === 'free' ? '무료강의' : '프리미엄강의' ?>
    </span>
    <div class="od-meta">
      상태: <strong><?= $statusLabel[$order['status']] ?? $order['status'] ?></strong>
    </div>
  </div>
</div>

<!-- 결제 정보 -->
<div class="detail-section">
  <div class="detail-section-title">결제 정보</div>
  <?php if ($order['toss_order_id']): ?>
  <div class="detail-row">
    <span class="detail-label">주문번호</span>
    <span class="detail-value"><?= htmlspecialchars($order['toss_order_id']) ?></span>
  </div>
  <?php endif; ?>
  <div class="detail-row">
    <span class="detail-label">결제일시</span>
    <span class="detail-value">
      <?= $paidAtObj ? $paidAtObj->format('Y.m.d H:i') : '-' ?>
    </span>
  </div>
  <div class="detail-row">
    <span class="detail-label">결제수단</span>
    <span class="detail-value">토스페이먼츠</span>
  </div>
  <?php if (!$isFree): ?>
  <div class="detail-row">
    <span class="detail-label">정가</span>
    <span class="detail-value"><?= number_format((int)$order['amount_origin']) ?>원</span>
  </div>
  <?php if ((int)$order['amount_origin'] > (int)$order['amount']): ?>
  <div class="detail-row">
    <span class="detail-label">할인금액</span>
    <span class="detail-value" style="color:#27ae60">
      -<?= number_format((int)$order['amount_origin'] - (int)$order['amount']) ?>원
    </span>
  </div>
  <?php endif; ?>
  <div class="detail-row">
    <span class="detail-label">결제금액</span>
    <span class="detail-value amount"><?= number_format((int)$order['amount']) ?>원</span>
  </div>
  <?php else: ?>
  <div class="detail-row">
    <span class="detail-label">결제금액</span>
    <span class="detail-value" style="color:#27ae60;font-weight:700">무료</span>
  </div>
  <?php endif; ?>
  <?php if ($order['status'] === 'refunded' && $order['refunded_at']): ?>
  <div class="detail-row">
    <span class="detail-label">환불일시</span>
    <span class="detail-value">
      <?= (safeDtOrder($order['refunded_at']) ?? $now)->format('Y.m.d H:i') ?>
    </span>
  </div>
  <?php endif; ?>
  <?php if ($order['refund_reason']): ?>
  <div class="detail-row">
    <span class="detail-label">환불사유</span>
    <span class="detail-value"><?= htmlspecialchars($order['refund_reason']) ?></span>
  </div>
  <?php endif; ?>
</div>

<!-- 환불 안내 + 신청 -->
<?php if ($canRefund): ?>
<div class="refund-policy-box">
  <strong>환불 가능 조건 안내</strong>
  · 결제일로부터 7일 이내 &amp; 수강률 33% 미만 → <span style="color:#27ae60;font-weight:700">전액 환불</span><br>
  · 이후 또는 수강률 33% 이상 → 잔여 수강기간 비례 환불<br>
  · 현재 수강률: <strong><?= $rate ?>%</strong>
  <?php if ($rate >= 33): ?>
    — <span style="color:#888">잔여 기간 비례 환불 적용</span>
  <?php else: ?>
    — <span style="color:#27ae60;font-weight:700">전액 환불 가능</span>
  <?php endif; ?>
</div>

<button class="btn-refund-apply" onclick="document.getElementById('refund-modal').style.display='flex'">
  환불 신청하기
</button>
<?php endif; ?>

<a href="/mypage/orders" class="btn-back-list" style="margin-top:8px">← 결제내역 목록</a>

<!-- 환불 신청 모달 -->
<?php if ($canRefund): ?>
<div id="refund-modal"
     style="display:none;position:fixed;inset:0;background:rgba(0,0,0,.45);z-index:1000;align-items:center;justify-content:center">
  <div style="background:#fff;border-radius:14px;padding:26px 24px;width:360px;max-width:94vw;position:relative">
    <button onclick="document.getElementById('refund-modal').style.display='none'"
            style="position:absolute;top:14px;right:16px;background:none;border:none;font-size:20px;color:#bbb;cursor:pointer">✕</button>

    <div style="font-size:15px;font-weight:900;color:#1a1a1a;margin-bottom:4px">환불 신청</div>
    <div style="font-size:11px;color:#888;margin-bottom:16px"><?= htmlspecialchars($order['class_title']) ?></div>

    <!-- 예상 환불금액 -->
    <div style="background:#fef9f9;border:1px solid #f5c6c6;border-radius:8px;padding:12px 14px;margin-bottom:14px">
      <div style="display:flex;justify-content:space-between;font-size:12px;margin-bottom:4px">
        <span style="color:#888">결제금액</span>
        <span style="font-weight:600"><?= number_format((int)$order['amount']) ?>원</span>
      </div>
      <div style="display:flex;justify-content:space-between;font-size:12px;margin-bottom:4px">
        <span style="color:#888">환불 유형</span>
        <span style="color:#c0392b;font-weight:700"><?= $refundType ?? '확인 중' ?></span>
      </div>
      <?php if ($refundAmount > 0): ?>
      <div style="border-top:1px dashed #f5c6c6;margin:8px 0"></div>
      <div style="display:flex;justify-content:space-between;font-size:13px">
        <span style="color:#c0392b;font-weight:700">예상 환불금액</span>
        <span style="color:#c0392b;font-weight:900;font-size:15px"><?= number_format($refundAmount) ?>원</span>
      </div>
      <?php endif; ?>
    </div>

    <form method="POST" action="/mypage/orders/<?= (int)$order['order_idx'] ?>/refund">
      <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken) ?>">

      <div style="margin-bottom:12px">
        <label style="font-size:12px;color:#555;font-weight:600;display:block;margin-bottom:6px">
          환불 사유 <span style="color:#c0392b">*</span>
        </label>
        <select name="reason" required
                style="width:100%;height:38px;border:1px solid #ddd;border-radius:6px;padding:0 10px;font-size:12px;font-family:inherit;color:#555;outline:none">
          <option value="">사유를 선택해 주세요</option>
          <option>강의 내용이 기대와 달라요</option>
          <option>강의 품질 문제 (영상/음질)</option>
          <option>실수로 결제했어요</option>
          <option>개인 사정으로 수강이 어려워요</option>
          <option>기타</option>
        </select>
      </div>

      <div style="margin-bottom:14px">
        <label style="font-size:12px;color:#555;font-weight:600;display:block;margin-bottom:6px">
          상세 내용 <span style="font-size:11px;color:#bbb;font-weight:400">(선택)</span>
        </label>
        <textarea name="detail" rows="3"
                  style="width:100%;border:1px solid #ddd;border-radius:6px;padding:8px 10px;font-size:12px;resize:none;box-sizing:border-box;font-family:inherit;color:#555;outline:none"
                  placeholder="추가 내용을 입력해 주세요"></textarea>
      </div>

      <div style="font-size:10.5px;color:#aaa;margin-bottom:14px;line-height:1.6">
        환불 신청 후 관리자 검토를 거쳐 영업일 기준 3~5일 내 처리됩니다.
      </div>

      <div style="display:flex;gap:8px">
        <button type="button"
                onclick="document.getElementById('refund-modal').style.display='none'"
                style="flex:1;height:44px;background:#f5f5f5;color:#555;border-radius:8px;font-size:13px;font-weight:600;border:none;cursor:pointer">
          취소
        </button>
        <button type="submit"
                style="flex:1;height:44px;background:#e74c3c;color:#fff;border-radius:8px;font-size:13px;font-weight:700;border:none;cursor:pointer">
          환불 신청
        </button>
      </div>
    </form>
  </div>
</div>
<?php endif; ?>
