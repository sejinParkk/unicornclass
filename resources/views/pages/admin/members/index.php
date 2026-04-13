<?php
/**
 * 관리자 회원 목록
 * @var array  $members
 * @var int    $total
 * @var int    $page
 * @var int    $totalPages
 * @var array  $filters
 */

/**
 * 상태 문자열 반환 (is_active + leave_at 기반)
 */
function memberStatus(array $m): string
{
	if ($m['is_active']) return 'active';
	if ($m['leave_at'])  return 'withdrawn';
	return 'dormant';
}
?>
<style>

</style>

<form method="GET" action="/admin/members" class="filter-bar">
	<input type="text" name="q" value="<?= htmlspecialchars($filters['q']) ?>" placeholder="이름 / 아이디 / 이메일 검색">
	<select name="status">
		<option value="">전체 상태</option>
		<option value="active"    <?= $filters['status'] === 'active'    ? 'selected' : '' ?>>정상</option>
		<option value="dormant"   <?= $filters['status'] === 'dormant'   ? 'selected' : '' ?>>정지</option>
		<option value="withdrawn" <?= $filters['status'] === 'withdrawn' ? 'selected' : '' ?>>탈퇴</option>
	</select>
	<select name="signup_type">
		<option value="">전체 가입유형</option>
		<option value="email" <?= $filters['signup_type'] === 'email' ? 'selected' : '' ?>>이메일</option>
		<option value="kakao" <?= $filters['signup_type'] === 'kakao' ? 'selected' : '' ?>>카카오</option>
		<option value="naver" <?= $filters['signup_type'] === 'naver' ? 'selected' : '' ?>>네이버</option>
	</select>
	<button type="submit" class="btn-search">검색</button>
	<a href="/admin/members" class="btn-reset">초기화</a>
</form>

<div class="top-bar">
	<span class="total-label">총 <strong><?= number_format($total) ?></strong>명</span>
</div>

<div class="tbl-wrap">
<table class="data-table">
	<colgroup>
			<col width="5%">
			<col width="7%">
			<col width="7%">
			<col width="">
			<col width="12%">
			<col width="7%">
			<col width="7%">
			<col width="10%">
			<col width="7%">
			<col width="10%">
			<col width="10%">
		</colgroup>
	<thead>
			<tr>
				<th>NO</th>
				<th>이름</th>
				<th>아이디</th>
				<th>이메일</th>
				<th>연락처</th>
				<th>가입유형</th>
				<th>수강</th>
				<th>결제총액</th>
				<th>상태</th>
				<th>가입일</th>
				<th>관리</th>
			</tr>
	</thead>
	<tbody>
	<?php if (empty($members)): ?>
	  <tr class="empty-row"><td colspan="11">검색 결과가 없습니다.</td></tr>
	<?php else: ?>
		<?php foreach ($members as $_i => $m): ?>
		<?php $status = memberStatus($m); ?>
		<tr>
			<td><?= $total - ($page-1)*$limit - $_i ?></td>
			<td><?= htmlspecialchars($m['mb_name']) ?></td>
			<td><?= htmlspecialchars($m['mb_id']) ?></td>
			<td><?= htmlspecialchars($m['mb_email'] ?? '') ?></td>
			<td><?= htmlspecialchars($m['mb_phone'] ?? '-') ?></td>
			<td>
				<?php if ($m['signup_type'] === 'kakao'): ?>
					<span class="social-tag social-kakao">카카오</span>
				<?php elseif ($m['signup_type'] === 'naver'): ?>
					<span class="social-tag social-naver">네이버</span>
				<?php else: ?>
					<span class="social-tag social-email">이메일</span>
				<?php endif; ?>
			</td>
			<td><?= (int)($m['enroll_count'] ?? 0) ?>개</td>
			<td><?= number_format((int)($m['total_paid'] ?? 0)) ?>원</td>
			<td>
							<?php if ($status === 'active'): ?>
								<span class="badge badge-active">정상</span>
							<?php elseif ($status === 'dormant'): ?>
								<span class="badge badge-dormant">정지</span>
							<?php else: ?>
								<span class="badge badge-withdrawn">탈퇴</span>
							<?php endif; ?>
			</td>
			<td><?= date('Y-m-d', strtotime($m['created_at'])) ?></td>
			<td><a href="/admin/members/<?= $m['member_idx'] ?>" class="act-btn act-view">보기</a></td>
		</tr>
		<?php endforeach; ?>
	<?php endif; ?>
	</tbody>
</table>
</div>

<?php if ($totalPages > 1): ?>
<div class="pagination">
	<?php $qs = http_build_query(array_merge($filters, ['page' => $page - 1])); ?>
	<?php if ($page > 1): ?>
		<a href="/admin/members?<?= $qs ?>">‹</a>
	<?php else: ?>
		<span class="disabled">‹</span>
	<?php endif; ?>

	<?php for ($p = max(1, $page - 2); $p <= min($totalPages, $page + 2); $p++): ?>
		<?php $qs = http_build_query(array_merge($filters, ['page' => $p])); ?>
		<?php if ($p === $page): ?>
			<span class="active"><?= $p ?></span>
		<?php else: ?>
			<a href="/admin/members?<?= $qs ?>"><?= $p ?></a>
		<?php endif; ?>
	<?php endfor; ?>

	<?php $qs = http_build_query(array_merge($filters, ['page' => $page + 1])); ?>
	<?php if ($page < $totalPages): ?>
		<a href="/admin/members?<?= $qs ?>">›</a>
	<?php else: ?>
		<span class="disabled">›</span>
	<?php endif; ?>
</div>
<?php endif; ?>
<script>sessionStorage.setItem('back_members', location.href);</script>
