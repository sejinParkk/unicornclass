<?php
/**
 * 관리자 강사 목록
 * @var array  $instructors
 * @var int    $total
 * @var int    $page
 * @var int    $totalPages
 * @var array  $filters
 */
?>

<?php if (isset($_GET['created'])): ?>
<div class="toast-msg toast-success">✓ 강사가 등록되었습니다.</div>
<?php elseif (isset($_GET['updated'])): ?>
<div class="toast-msg toast-success">✓ 강사 정보가 수정되었습니다.</div>
<?php elseif (isset($_GET['deleted'])): ?>
<div class="toast-msg toast-success">✓ 강사가 삭제되었습니다.</div>
<?php elseif (isset($_GET['error'])): ?>
<div class="toast-msg toast-error"><?= htmlspecialchars($_GET['error']) ?></div>
<?php endif; ?>

<form method="GET" action="/admin/instructors" class="filter-bar">
<input type="text" name="q" value="<?= htmlspecialchars($filters['q']) ?>" placeholder="강사명/분야 검색">
<select name="is_active">
	<option value="">전체 상태</option>
	<option value="1" <?= $filters['is_active']==='1' ?'selected':'' ?>>활성</option>
	<option value="0" <?= $filters['is_active']==='0' ?'selected':'' ?>>비활성</option>
</select>
<button type="submit" class="btn-search">검색</button>
<a href="/admin/instructors" class="btn-reset">초기화</a>
</form>

<div class="top-bar">
<span class="total-label">총 <strong><?= number_format($total) ?></strong>명</span>
<a href="/admin/instructors/create" class="btn-create">
	<svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24">
			<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
	</svg>
	강사 등록
</a>
</div>

<div class="tbl-wrap">
	<table class="data-table">
		<colgroup>
			<col width="5%">
			<col width="7%">
			<col width="7%">
			<col width="">
			<col width="">
			<col width="7%">
			<col width="7%">
			<col width="10%">
			<col width="10%">
		</colgroup>
		<thead>
			<tr>
				<th>NO</th>
				<th>사진</th>
				<th>이름</th>
				<th>분야</th>
				<th>카테고리</th>
				<th>담당강의</th>
				<th>순서</th>
				<th>상태</th>
				<th>관리</th>
			</tr>
		</thead>
		<tbody>
		<?php if (empty($instructors)): ?>
			<tr class="empty-row"><td colspan="9">등록된 강사가 없습니다.</td></tr>
		<?php else: ?>
		<?php foreach ($instructors as $_i => $i): ?>
			<tr>
				<td><?= $total - ($page-1)*$limit - $_i ?></td>
				<td>
					<?php if ($i['photo']): ?>
							<img src="/uploads/instructor/<?= htmlspecialchars($i['photo']) ?>" alt="" style="border-radius:50%;">
					<?php else: ?>
							<div class="photo-empty">
									<svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
											<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
									</svg>
							</div>
					<?php endif; ?>
				</td>
				<td><?= htmlspecialchars($i['name']) ?></td>
				<td><?= htmlspecialchars($i['field'] ?? '') ?></td>
				<td><?= htmlspecialchars($i['category_name'] ?? '미분류') ?></td>
				<td><?= (int)($i['class_count'] ?? 0) ?></td>
				<td><?= (int)$i['sort_order'] ?></td>
				<td>
					<?php if ($i['is_active']): ?>
							<span class="badge badge-active">활성</span>
					<?php else: ?>
							<span class="badge badge-inactive">비활성</span>
					<?php endif; ?>
				</td>
				<td>
					<div class="act-btn-wrap">
							<a href="/admin/instructors/<?= $i['instructor_idx'] ?>/edit" class="act-btn act-edit">수정</a>
							<form method="POST" action="/admin/instructors/<?= $i['instructor_idx'] ?>/delete"
											onsubmit="return confirm('강사를 삭제하시겠습니까?\n담당 강의가 있으면 삭제할 수 없습니다.')">
									<input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken) ?>">
									<button type="submit" class="act-btn act-del">삭제</button>
							</form>
					</div>
				</td>
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
	<a href="/admin/instructors?<?= $qs ?>">‹</a>
<?php else: ?>
	<span class="disabled">‹</span>
<?php endif; ?>

<?php for ($p = max(1, $page-2); $p <= min($totalPages, $page+2); $p++): ?>
	<?php $qs = http_build_query(array_merge($filters, ['page' => $p])); ?>
	<?php if ($p === $page): ?>
			<span class="active"><?= $p ?></span>
	<?php else: ?>
			<a href="/admin/instructors?<?= $qs ?>"><?= $p ?></a>
	<?php endif; ?>
<?php endfor; ?>

<?php $qs = http_build_query(array_merge($filters, ['page' => $page + 1])); ?>
<?php if ($page < $totalPages): ?>
	<a href="/admin/instructors?<?= $qs ?>">›</a>
<?php else: ?>
	<span class="disabled">›</span>
<?php endif; ?>
</div>
<?php endif; ?>
<script>sessionStorage.setItem('back_instructors', location.href);</script>
