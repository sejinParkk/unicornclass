<?php
/**
 * 관리자 회원 상세
 * @var array  $member
 * @var array  $enrolls
 * @var array  $orders
 * @var string $csrfToken
 */

// 상태 계산
$isLoginLocked = !$member['is_active'] && ($member['login_fail_count'] ?? 0) >= 5 && !$member['leave_at'];
if ($member['is_active']) {
	$memberStatus     = 'active';
	$memberStatusText = '정상';
	$memberStatusCls  = 'badge-active';
} elseif ($member['leave_at']) {
	$memberStatus     = 'withdrawn';
	$memberStatusText = '탈퇴';
	$memberStatusCls  = 'badge-withdrawn';
} elseif ($isLoginLocked) {
	$memberStatus     = 'dormant';
	$memberStatusText = '로그인잠금';
	$memberStatusCls  = 'badge-locked';
} else {
	$memberStatus     = 'dormant';
	$memberStatusText = '정지';
	$memberStatusCls  = 'badge-dormant';
}
?>

<?php if (isset($_GET['status_updated'])): ?>
<div class="toast-msg toast-success">✓ 회원 상태가 변경되었습니다.</div>
<?php endif; ?>
<?php if (isset($_GET['profile_updated'])): ?>
<div class="toast-msg toast-success">✓ 회원 정보가 수정되었습니다.</div>
<?php endif; ?>

<div class="form-layout ver2">
	<div>
		<!-- 기본 정보 수정 폼 -->
		<div class="form-card">
			<h3>
				기본 정보
				<span class="badge left <?= $memberStatusCls ?>"><?= $memberStatusText ?></span>
			</h3>
			<form id="memberProfileForm" method="POST" action="/admin/members/<?= $member['member_idx'] ?>/profile">
				<input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken) ?>">

				<div class="info-grid">
					<div class="info-item">
						<div class="label">회원번호</div>
						<div class="value"><?= $member['member_idx'] ?></div>
					</div>
					<div class="info-item">
						<div class="label">아이디</div>
						<div class="value"><?= htmlspecialchars($member['mb_id']) ?></div>
					</div>
					<div class="info-item">
						<div class="label">가입일</div>
						<div class="value"><?= date('Y-m-d H:i', strtotime($member['created_at'])) ?></div>
					</div>
					<?php if ($member['leave_at']): ?>
					<div class="info-item">
						<div class="label">탈퇴일</div>
						<div class="value" style="color:#e53e3e"><?= date('Y-m-d H:i', strtotime($member['leave_at'])) ?></div>
					</div>
					<?php endif; ?>
				</div>

				<div class="form-group" style="margin-top:16px">
					<label class="form-label">이름 <span class="req">*</span></label>
					<input type="text" name="mb_name" class="form-control <?= isset($errors['mb_name']) ? 'error' : '' ?>"
							value="<?= htmlspecialchars($_POST['mb_name'] ?? $member['mb_name']) ?>"
							maxlength="20">
					<div class="error-msg" data-ajax-err="mb_name" style="display:none"></div>
				</div>

				<div class="form-group">
					<label class="form-label">이메일</label>
					<input type="email" name="mb_email" class="form-control <?= isset($errors['mb_email']) ? 'error' : '' ?>"
							value="<?= htmlspecialchars($_POST['mb_email'] ?? $member['mb_email'] ?? '') ?>">
					<div class="error-msg" data-ajax-err="mb_email" style="display:none"></div>
				</div>

				<div class="form-group">
					<label class="form-label">연락처</label>
					<input type="text" name="mb_phone" id="mb_phone" class="form-control <?= isset($errors['mb_phone']) ? 'error' : '' ?>"
							value="<?= htmlspecialchars($_POST['mb_phone'] ?? $member['mb_phone'] ?? '') ?>"
							placeholder="010-0000-0000" maxlength="13">
					<div class="error-msg" data-ajax-err="mb_phone" style="display:none"></div>
					<div class="hint">숫자 입력 시 하이픈 자동 추가</div>
					<script>
					(function () {
						var el = document.getElementById('mb_phone');
						function fmt(v) {
							v = v.replace(/[^0-9]/g, '');
							if (v.length < 4)  return v;
							if (v.length < 8)  return v.slice(0,3) + '-' + v.slice(3);
							return v.slice(0,3) + '-' + v.slice(3,7) + '-' + v.slice(7,11);
						}
						el.value = fmt(el.value);
						el.addEventListener('input', function () {
							var pos = this.selectionStart;
							var old = this.value;
							this.value = fmt(this.value);
							// 커서 위치 보정
							var diff = this.value.length - old.length;
							this.setSelectionRange(pos + diff, pos + diff);
						});
					})();
					</script>
				</div>

				<div class="form-group">
					<label class="form-label">새 비밀번호</label>
					<input type="password" name="new_password" class="form-control <?= isset($errors['new_password']) ? 'error' : '' ?>"
							placeholder="변경 시에만 입력 (8자 이상)">
					<div class="error-msg" data-ajax-err="new_password" style="display:none"></div>
				</div>

				<div class="form-group">
					<label class="form-label">새 비밀번호 확인</label>
					<input type="password" name="confirm_password" class="form-control <?= isset($errors['confirm_password']) ? 'error' : '' ?>"
							placeholder="새 비밀번호 재입력">
					<div class="error-msg" data-ajax-err="confirm_password" style="display:none"></div>
				</div>

				<div class="form-group">
					<label class="form-label">수신 동의</label>
					<div style="display:flex;gap:20px;margin-top:6px">
						<label style="display:flex;align-items:center;gap:6px;font-weight:400;cursor:pointer">
							<input type="checkbox" name="mb_mailling" value="1"
									<?= (isset($errors) && !empty($errors) ? isset($_POST['mb_mailling']) : (int)$member['mb_mailling']) ? 'checked' : '' ?>>
							이메일 수신
						</label>
						<label style="display:flex;align-items:center;gap:6px;font-weight:400;cursor:pointer">
							<input type="checkbox" name="mb_sms" value="1"
									<?= (isset($errors) && !empty($errors) ? isset($_POST['mb_sms']) : (int)$member['mb_sms']) ? 'checked' : '' ?>>
							SMS 수신
						</label>
					</div>
				</div>

				<div style="margin-top:16px">
					<button type="submit" class="btn-save">정보 저장</button>
				</div>
			</form>
		</div>

		<!-- 소셜 연동 현황 -->
		<!-- <div class="form-card">
			<h3>소셜 연동 현황</h3>
			<div class="info-grid">
				<div class="info-item">
					<div class="label">가입 유형</div>
					<div class="value">
						<?php if ($member['signup_type'] === 'kakao'): ?>
							<span class="social-tag social-kakao">카카오 로그인</span>
						<?php elseif ($member['signup_type'] === 'naver'): ?>
							<span class="social-tag social-naver">네이버 로그인</span>
						<?php else: ?>
							<span class="social-tag social-email">이메일 가입</span>
						<?php endif; ?>
					</div>
				</div>
				<div class="info-item">
					<div class="label">카카오 연동</div>
					<div class="value">
						<?php if ($member['signup_type'] === 'kakao' && $member['social_id']): ?>
							<span style="color:#276749;font-weight:600;">연동됨</span>
							<span style="font-size:12px;color:#8898aa;margin-left:6px;">(ID: <?= htmlspecialchars($member['social_id']) ?>)</span>
						<?php else: ?>
							<span style="color:#a0aec0;">미연동</span>
						<?php endif; ?>
					</div>
				</div>
				<div class="info-item">
					<div class="label">네이버 연동</div>
					<div class="value">
						<?php if ($member['signup_type'] === 'naver' && $member['social_id']): ?>
							<span style="color:#276749;font-weight:600;">연동됨</span>
							<span style="font-size:12px;color:#8898aa;margin-left:6px;">(ID: <?= htmlspecialchars($member['social_id']) ?>)</span>
						<?php else: ?>
							<span style="color:#a0aec0;">미연동</span>
						<?php endif; ?>
					</div>
				</div>
			</div>
		</div> -->

		<!-- 상태 변경 -->
		<div class="form-card">
			<h3>상태 변경</h3>
			<div class="status-bar">
				<?php if ($memberStatus !== 'active'): ?>
				<form method="POST" action="/admin/members/<?= $member['member_idx'] ?>/status"
						onsubmit="return confirm('정상 상태로 변경하시겠습니까?')">
					<input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken) ?>">
					<input type="hidden" name="status" value="active">
					<button type="submit" class="btn-status btn-active">정상으로 변경</button>
				</form>
				<?php endif; ?>

				<?php if ($memberStatus !== 'dormant'): ?>
				<form method="POST" action="/admin/members/<?= $member['member_idx'] ?>/status"
						onsubmit="return confirm('정지 상태로 변경하시겠습니까?')">
					<input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken) ?>">
					<input type="hidden" name="status" value="dormant">
					<button type="submit" class="btn-status btn-dormant">정지</button>
				</form>
				<?php endif; ?>

				<?php if ($memberStatus !== 'withdrawn'): ?>
				<form method="POST" action="/admin/members/<?= $member['member_idx'] ?>/status"
						onsubmit="return confirm('탈퇴 처리하시겠습니까? 이 작업은 되돌리기 어렵습니다.')">
					<input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken) ?>">
					<input type="hidden" name="status" value="withdrawn">
					<button type="submit" class="btn-status btn-withdraw">탈퇴 처리</button>
				</form>
				<?php endif; ?>
			</div>
			<?php if ($isLoginLocked): ?>
			<div style="margin-top:12px;padding:10px 14px;background:#fff5f5;border:1px solid #fed7d7;border-radius:6px;font-size:12px;color:#c53030;">
				⚠ 로그인 실패 5회 초과로 자동 잠금된 계정입니다. "정상으로 변경" 클릭 시 잠금 해제 및 실패 횟수가 초기화됩니다.
			</div>
			<?php endif; ?>
			<div style="margin-top:12px;font-size:12px;color:#a0aec0;">
				정지: 로그인 불가 (데이터 보존) &nbsp;|&nbsp; 탈퇴: leave_at 기록, 30일 후 개인정보 삭제 배치
			</div>
		</div>
	</div>

	<div>
		<!-- 수강 이력 -->
		<div class="form-card">
			<h3>수강 이력 <span style="font-size:13px;font-weight:400;color:#8898aa;">(<?= count($enrolls) ?>건)</span></h3>
			<?php if (empty($enrolls)): ?>
				<div class="empty-row"><td>수강 이력이 없습니다.</td></div>
			<?php else: ?>
			<table class="mini-table">
				<thead>
					<tr>
						<th>강의명</th>
						<th>유형</th>
						<th>수강 시작일</th>
						<th>만료일</th>
					</tr>
				</thead>
				<tbody>
				<?php foreach ($enrolls as $e): ?>
					<tr>
						<td><?= htmlspecialchars($e['class_title']) ?></td>
						<td>
							<?php if ($e['type'] === 'free'): ?>
								<span class="type-free">무료</span>
							<?php else: ?>
								<span class="type-premium">프리미엄</span>
							<?php endif; ?>
						</td>
						<td style="font-size:12px;color:#8898aa"><?= date('Y-m-d', strtotime($e['enrolled_at'])) ?></td>
						<td style="font-size:12px;color:#8898aa">
							<?= $e['expire_at'] ? date('Y-m-d', strtotime($e['expire_at'])) : '무제한' ?>
						</td>
					</tr>
				<?php endforeach; ?>
				</tbody>
			</table>
			<?php endif; ?>
		</div>

		<!-- 결제 이력 -->
		<div class="form-card">
			<h3>결제 이력 <span style="font-size:13px;font-weight:400;color:#8898aa;">(<?= count($orders) ?>건)</span></h3>
			<?php if (empty($orders)): ?>
				<div class="empty-row"><td>결제 이력이 없습니다.</td></div>
			<?php else: ?>
			<table class="mini-table">
				<thead>
					<tr>
						<th>강의명</th>
						<th>결제금액</th>
						<th>상태</th>
						<th>결제일</th>
					</tr>
				</thead>
				<tbody>
				<?php
				$statusLabels = [
					'paid'        => ['정상', 'order-paid'],
					'refund_req'  => ['환불신청', ''],
					'refunded'    => ['환불완료', 'order-refunded'],
					'free'        => ['무료', ''],
				];
				?>
				<?php foreach ($orders as $o): ?>
					<?php [$label, $cls] = $statusLabels[$o['status']] ?? [$o['status'], '']; ?>
					<tr>
						<td><?= htmlspecialchars($o['class_title']) ?></td>
						<td class="<?= $cls ?>"><?= number_format($o['amount']) ?>원</td>
						<td style="font-size:12px"><?= $label ?></td>
						<td style="font-size:12px;color:#8898aa">
							<?= $o['paid_at'] ? date('Y-m-d', strtotime($o['paid_at'])) : '-' ?>
						</td>
					</tr>
				<?php endforeach; ?>
				</tbody>
			</table>
			<?php endif; ?>
		</div>    
	</div>
</div>

<div class="form-actions">
	<a href="/admin/members" class="btn-back">목록</a>
</div>
<script>
(function(){
	var u = sessionStorage.getItem('back_members');
	if (u) { var el = document.querySelector('.btn-back'); if (el) el.href = u; }
})();

document.getElementById('memberProfileForm')?.addEventListener('submit', function(e) {
	e.preventDefault();
	ajaxSubmit(this, {
		onSuccess: function() {
			var toast = document.createElement('div');
			toast.className = 'toast-msg toast-success';
			toast.textContent = '✓ 회원 정보가 수정되었습니다.';
			document.querySelector('.form-layout').insertAdjacentElement('beforebegin', toast);
			setTimeout(function() { toast.remove(); }, 3000);
		}
	});
});
</script>