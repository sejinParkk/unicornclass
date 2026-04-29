<?php
/**
 * 관리자 결제 상세
 * @var array  $order
 * @var string $csrfToken
 */
$statusLabel = fn(string $s) => match($s) {
	'paid'       => ['text' => '결제완료', 'cls' => 'badge-paid'],
	'refund_req' => ['text' => '환불신청', 'cls' => 'badge-refund-req'],
	'refunded'   => ['text' => '환불완료', 'cls' => 'badge-refunded'],
	'free'       => ['text' => '무료수강', 'cls' => 'badge-free'],
	default      => ['text' => $s,         'cls' => 'badge-default'],
};
$sl = $statusLabel($order['status']);
?>

<?php if (isset($_GET['refund_done'])): ?>
<div class="toast-msg toast-success">✓ 환불 승인이 완료되었습니다.</div>
<?php elseif (isset($_GET['refund_rejected'])): ?>
<div class="toast-msg toast-success">✓ 환불 신청이 거절되었습니다.</div>
<?php elseif (isset($_GET['error'])): ?>
<div class="toast-msg toast-error"><?= htmlspecialchars($_GET['error']) ?></div>
<?php endif; ?>

<div class="form-card">
	<h3 class="form-card-title">주문 정보 <span class="badge <?= $sl['cls'] ?>"><?= $sl['text'] ?></span></h3>
	<div class="info-grid">
		<div class="info-item">
			<div class="label">주문번호</div>
			<div class="value">#<?= $order['order_idx'] ?></div>
		</div>
		<div class="info-item">
			<div class="label">Toss 주문 ID</div>
			<div class="value" style="font-size:12px;word-break:break-all;"><?= htmlspecialchars($order['toss_order_id'] ?? '-') ?></div>
		</div>
		<div class="info-item">
			<div class="label">결제금액</div>
			<div class="value" style="font-weight:700;font-size:16px;"><?= number_format($order['amount']) ?>원</div>
		</div>
		<div class="info-item">
			<div class="label">정가</div>
			<div class="value"><?= number_format($order['amount_origin']) ?>원</div>
		</div>
		<div class="info-item">
			<div class="label">결제일시</div>
			<div class="value"><?= $order['paid_at'] ? date('Y-m-d H:i:s', strtotime($order['paid_at'])) : '-' ?></div>
		</div>
		<div class="info-item">
			<div class="label">환불일시</div>
			<div class="value"><?= $order['refunded_at'] ? date('Y-m-d H:i:s', strtotime($order['refunded_at'])) : '-' ?></div>
		</div>
		<?php if ($order['refund_reason']): ?>
		<div class="info-item" style="grid-column:1/-1;">
			<div class="label">환불 사유</div>
			<div class="value"><?= htmlspecialchars($order['refund_reason']) ?></div>
		</div>
		<?php endif; ?>
		<?php if ($order['toss_payment_key']): ?>
		<div class="info-item" style="grid-column:1/-1;">
			<div class="label">Toss 결제키</div>
			<div class="value" style="font-size:11.5px;word-break:break-all;color:#718096;"><?= htmlspecialchars($order['toss_payment_key']) ?></div>
		</div>
		<?php endif; ?>
	</div>
</div>

<div class="form-card">
	<h3>회원 정보</h3>
	<div class="info-grid">
		<div class="info-item">
			<div class="label">이름</div>
			<div class="value"><a href="/admin/members/<?= $order['member_idx'] ?>" style="color:#c0392b;font-weight:600;"><?= htmlspecialchars($order['mb_name']) ?></a></div>
		</div>
		<div class="info-item">
			<div class="label">아이디</div>
			<div class="value"><?= htmlspecialchars($order['mb_id']) ?></div>
		</div>
		<div class="info-item">
			<div class="label">이메일</div>
			<div class="value"><?= htmlspecialchars($order['mb_email'] ?? '-') ?></div>
		</div>
		<div class="info-item">
			<div class="label">휴대폰</div>
			<div class="value"><?= htmlspecialchars($order['mb_phone'] ?? '-') ?></div>
		</div>
	</div>
</div>

<div class="form-card">
	<h3>강의 정보</h3>
	<div class="info-grid">
		<div class="info-item" style="grid-column:1/-1;">
			<div class="label">강의명</div>
			<div class="value" style="font-weight:600;"><?= htmlspecialchars($order['class_title']) ?></div>
		</div>
		<div class="info-item">
			<div class="label">유형</div>
			<div class="value"><?= $order['class_type'] === 'free' ? '무료' : '프리미엄' ?></div>
		</div>
	</div>
</div>

<?php if ($order['status'] === 'refund_req'): ?>
<div class="form-card">
	<h3>환불 처리</h3>
	<p style="font-size:13px;color:#718096;margin-bottom:16px;">
		환불 신청된 주문입니다. 승인 시 Toss Payments 환불 API가 호출되고 수강이 비활성화됩니다.
	</p>
	<div class="info-grid" style="margin-bottom:16px;">
		<div class="info-item">
			<div class="label">환불 유형</div>
			<div class="value" style="font-weight:600;"><?= htmlspecialchars($refundCalc['type']) ?></div>
		</div>
		<div class="info-item">
			<div class="label">예상 환불금액</div>
			<div class="value" style="font-weight:700;font-size:16px;color:#c0392b;">
				<?= $refundCalc['refund_amount'] > 0 ? number_format($refundCalc['refund_amount']) . '원' : '-' ?>
			</div>
		</div>
	</div>
	<div class="action-bar">
		<form method="POST" action="/admin/orders/<?= $order['order_idx'] ?>/refund/approve"
						onsubmit="return confirm('환불을 승인하시겠습니까?')">
			<input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken) ?>">
			<button type="submit" class="btn-approve">환불 승인</button>
		</form>
		<form method="POST" action="/admin/orders/<?= $order['order_idx'] ?>/refund/reject"
						onsubmit="return confirm('환불 신청을 거절하시겠습니까?')">
			<input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken) ?>">
			<button type="submit" class="btn-reject">환불 거절</button>
		</form>
	</div>
</div>
<?php endif; ?>

<div class="form-actions">
  <a href="/admin/orders" class="btn-back">목록</a>
</div>
<script>
(function(){
	var u = sessionStorage.getItem('back_orders');
	if (u) { var el = document.querySelector('.btn-back'); if (el) el.href = u; }
})();
</script>