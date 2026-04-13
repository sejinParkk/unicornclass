<?php
/**
 * 관리자 강사 지원 목록
 * @var array  $applies
 * @var int    $total
 * @var int    $page
 * @var int    $totalPages
 * @var array  $filters
 */
?>

<form method="GET" action="/admin/instructor-apply" class="filter-bar">
  <input type="text" name="q" value="<?= htmlspecialchars($filters['q']) ?>" placeholder="이름/이메일 검색">
  <select name="status">
	<option value="">전체 상태</option>
	<option value="pending"  <?= $filters['status']==='pending'  ?'selected':'' ?>>검토중</option>
	<option value="approved" <?= $filters['status']==='approved' ?'selected':'' ?>>승인</option>
	<option value="rejected" <?= $filters['status']==='rejected' ?'selected':'' ?>>거절</option>
  </select>
  <button type="submit" class="btn-search">검색</button>
  <a href="/admin/instructor-apply" class="btn-reset">초기화</a>
</form>

<div class="top-bar">
<span class="total-label">총 <strong><?= number_format($total) ?></strong>건</span>
</div>

<div class="tbl-wrap">
  <table class="data-table">
	<colgroup>
			<col width="5%">
			<col width="7%">
			<col width="20%">
			<col width="10%">
			<col width="">
			<col width="7%">
			<col width="7%">
			<col width="10%">
			<col width="10%">
		</colgroup>
	<thead>
	  <tr>
		<th>NO</th>
		<th>이름</th>
		<th>이메일</th>
		<th>연락처</th>
		<th>강의 분야</th>
		<th>선호 형태</th>
		<th>상태</th>
		<th>지원일</th>
		<th>상세</th>
	  </tr>
	</thead>
	<tbody>
	  <?php if (empty($applies)): ?>
		<tr class="empty-row"><td colspan="9">지원 내역이 없습니다.</td></tr>
	  <?php else: ?>
		<?php foreach ($applies as $_i => $a): ?>
		<?php
		$formatLabels = [
			'free_webinar' => '무료 웨비나',
			'paid_vod'     => '유료 VOD',
			'mixed'        => '혼합',
		];
		?>
		<tr>
		  <td><?= $total - ($page-1)*$limit - $_i ?></td>
		  <td><?= htmlspecialchars($a['name']) ?></td>
		  <td><?= htmlspecialchars($a['email']) ?></td>
		  <td><?= htmlspecialchars($a['phone']) ?></td>
		  <td><?= htmlspecialchars($a['teach_field'] ?? '') ?></td>
		  <td><?= htmlspecialchars($formatLabels[$a['teach_format'] ?? ''] ?? ($a['teach_format'] ?? '-')) ?></td>
		  <td>
			<?php if ($a['status'] === 'pending'): ?>
			  <span class="badge badge-pending">검토중</span>
			<?php elseif ($a['status'] === 'approved'): ?>
			  <span class="badge badge-approved">승인</span>
			<?php else: ?>
			  <span class="badge badge-rejected">거절</span>
			<?php endif; ?>
		  </td>
		  <td><?= date('Y-m-d', strtotime($a['created_at'])) ?></td>
		  <td><a href="/admin/instructor-apply/<?= $a['apply_idx'] ?>" class="act-btn act-view">보기</a></td>
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
	<a href="/admin/instructor-apply?<?= $qs ?>">‹</a>
<?php else: ?>
	<span class="disabled">‹</span>
<?php endif; ?>

<?php for ($p = max(1, $page-2); $p <= min($totalPages, $page+2); $p++): ?>
	<?php $qs = http_build_query(array_merge($filters, ['page' => $p])); ?>
	<?php if ($p === $page): ?>
		<span class="active"><?= $p ?></span>
	<?php else: ?>
		<a href="/admin/instructor-apply?<?= $qs ?>"><?= $p ?></a>
	<?php endif; ?>
<?php endfor; ?>

<?php $qs = http_build_query(array_merge($filters, ['page' => $page + 1])); ?>
<?php if ($page < $totalPages): ?>
	<a href="/admin/instructor-apply?<?= $qs ?>">›</a>
<?php else: ?>
	<span class="disabled">›</span>
<?php endif; ?>
</div>
<?php endif; ?>
<script>sessionStorage.setItem('back_instructor_apply', location.href);</script>
