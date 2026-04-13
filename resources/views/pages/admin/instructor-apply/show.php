<?php
/**
 * 관리자 강사 지원 상세
 * @var array  $apply
 * @var string $csrfToken
 */
$formatLabels = [
	'free_webinar' => '무료 웨비나',
	'paid_vod'     => '유료 VOD',
	'mixed'        => '혼합',
];

<?php if (isset($_GET['approved'])): ?>
<div class="toast-msg toast-success">✓ 지원이 승인되었습니다.</div>
<?php elseif (isset($_GET['rejected'])): ?>
<div class="toast-msg toast-reject">✗ 지원이 거절되었습니다.</div>
<?php endif; ?>

<div class="apply-wrap">
	<!-- 지원자 기본 정보 -->
	<div class="apply-card">
		<h3>
			지원자 정보
			<span style="float:right">
				<?php if ($apply['status'] === 'pending'): ?>
					<span class="badge badge-pending">검토중</span>
				<?php elseif ($apply['status'] === 'approved'): ?>
					<span class="badge badge-approved">승인됨</span>
				<?php else: ?>
					<span class="badge badge-rejected">거절됨</span>
				<?php endif; ?>
			</span>
		</h3>
		<div class="info-grid">
			<div class="info-item">
				<div class="label">이름</div>
				<div class="value"><?= htmlspecialchars($apply['name']) ?></div>
			</div>
			<div class="info-item">
				<div class="label">이메일</div>
				<div class="value"><a href="mailto:<?= htmlspecialchars($apply['email']) ?>"><?= htmlspecialchars($apply['email']) ?></a></div>
			</div>
			<div class="info-item">
				<div class="label">연락처</div>
				<div class="value"><?= htmlspecialchars($apply['phone']) ?></div>
			</div>
			<div class="info-item">
				<div class="label">강의 분야</div>
				<div class="value"><?= htmlspecialchars($apply['teach_field'] ?? '-') ?></div>
			</div>
			<div class="info-item">
				<div class="label">강의 경력</div>
				<div class="value"><?= htmlspecialchars($apply['teach_exp'] ?? '-') ?></div>
			</div>
			<div class="info-item">
				<div class="label">선호 강의 형태</div>
				<div class="value"><?= htmlspecialchars($formatLabels[$apply['teach_format'] ?? ''] ?? ($apply['teach_format'] ?? '-')) ?></div>
			</div>
			<div class="info-item">
				<div class="label">지원일</div>
				<div class="value"><?= date('Y-m-d H:i', strtotime($apply['created_at'])) ?></div>
			</div>
		</div>
	</div>

	<!-- SNS / 포트폴리오 -->
	<?php if ($apply['sns_youtube'] || $apply['sns_instagram'] || $apply['sns_blog'] || $apply['sns_other'] || $apply['portfolio_link']): ?>
	<div class="apply-card">
		<h3>SNS / 포트폴리오</h3>
		<div class="info-grid">
			<?php if ($apply['sns_youtube']): ?>
			<div class="info-item">
				<div class="label">유튜브</div>
				<div class="value"><a href="<?= htmlspecialchars($apply['sns_youtube']) ?>" target="_blank" rel="noopener"><?= htmlspecialchars($apply['sns_youtube']) ?></a></div>
			</div>
			<?php endif; ?>
			<?php if ($apply['sns_instagram']): ?>
			<div class="info-item">
				<div class="label">인스타그램</div>
				<div class="value"><a href="<?= htmlspecialchars($apply['sns_instagram']) ?>" target="_blank" rel="noopener"><?= htmlspecialchars($apply['sns_instagram']) ?></a></div>
			</div>
			<?php endif; ?>
			<?php if ($apply['sns_blog']): ?>
			<div class="info-item">
				<div class="label">블로그</div>
				<div class="value"><a href="<?= htmlspecialchars($apply['sns_blog']) ?>" target="_blank" rel="noopener"><?= htmlspecialchars($apply['sns_blog']) ?></a></div>
			</div>
			<?php endif; ?>
			<?php if ($apply['sns_other']): ?>
			<div class="info-item">
				<div class="label">기타 SNS</div>
				<div class="value"><a href="<?= htmlspecialchars($apply['sns_other']) ?>" target="_blank" rel="noopener"><?= htmlspecialchars($apply['sns_other']) ?></a></div>
			</div>
			<?php endif; ?>
			<?php if ($apply['portfolio_link']): ?>
			<div class="info-item" style="grid-column:1/-1">
				<div class="label">포트폴리오</div>
				<div class="value"><a href="<?= htmlspecialchars($apply['portfolio_link']) ?>" target="_blank" rel="noopener"><?= htmlspecialchars($apply['portfolio_link']) ?></a></div>
			</div>
			<?php endif; ?>
		</div>
	</div>
	<?php endif; ?>

	<!-- 자기소개 -->
	<?php if ($apply['bio']): ?>
	<div class="apply-card">
		<h3>자기소개</h3>
		<div class="text-block"><?= htmlspecialchars($apply['bio']) ?></div>
	</div>
	<?php endif; ?>

	<!-- 강의 계획 -->
	<?php if ($apply['curriculum']): ?>
	<div class="apply-card">
		<h3>강의 계획</h3>
		<div class="text-block"><?= htmlspecialchars($apply['curriculum']) ?></div>
	</div>
	<?php endif; ?>

	<!-- 거절 사유 (거절된 경우) -->
	<?php if ($apply['status'] === 'rejected' && $apply['reject_reason']): ?>
	<div class="apply-card">
		<h3>거절 사유</h3>
		<div class="reject-reason-box"><?= htmlspecialchars($apply['reject_reason']) ?></div>
	</div>
	<?php endif; ?>

	<!-- 액션 버튼 -->
	<?php if ($apply['status'] === 'pending'): ?>
	<div class="apply-card">
		<h3>검토 처리</h3>
		<div class="action-bar">
			<!-- 승인 -->
			<form method="POST" action="/admin/instructor-apply/<?= $apply['apply_idx'] ?>/approve"
				  onsubmit="return confirm('승인하시겠습니까?')">
				<input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken) ?>">
				<button type="submit" class="btn-approve">승인</button>
			</form>

			<!-- 거절 -->
			<form method="POST" action="/admin/instructor-apply/<?= $apply['apply_idx'] ?>/reject"
				  class="reject-section" onsubmit="return confirm('거절하시겠습니까?')">
				<input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken) ?>">
				<label>거절 사유 (선택)</label>
				<textarea name="reject_reason" placeholder="거절 사유를 입력하면 지원자에게 전달됩니다."></textarea>
				<button type="submit" class="btn-reject">거절</button>
			</form>
		</div>
	</div>
	<?php endif; ?>

	<div class="form-actions">
		<a href="/admin/instructor-apply" class="btn-back">목록</a>
	</div>
</div>
<script>
(function(){
	var u = sessionStorage.getItem('back_instructor_apply');
	if (u) { var el = document.querySelector('.btn-back'); if (el) el.href = u; }
})();
</script>
