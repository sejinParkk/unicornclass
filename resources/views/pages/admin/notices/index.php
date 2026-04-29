<?php
/**
 * 관리자 공지사항 목록
 * @var array  $notices
 * @var int    $total
 * @var int    $page
 * @var int    $totalPages
 * @var array  $filters
 * @var string $csrfToken
 */
?>

<?php if (isset($_GET['deleted'])): ?>
<div class="toast-msg toast-success">✓ 공지사항이 삭제되었습니다.</div>
<?php endif; ?>

<form method="GET" action="/admin/notices" class="filter-bar">
	<input type="text" name="q" value="<?= htmlspecialchars($filters['q']) ?>" placeholder="제목 검색">
	<select name="is_active">
		<option value="">전체</option>
		<option value="1" <?= $filters['is_active'] === '1' ? 'selected' : '' ?>>활성</option>
		<option value="0" <?= $filters['is_active'] === '0' ? 'selected' : '' ?>>비활성</option>
	</select>
	<label class="filter-chk"><input type="checkbox" name="is_pinned" value="1" <?= !empty($filters['is_pinned']) ? 'checked' : '' ?>> 고정</label>
	<label class="filter-chk"><input type="checkbox" name="is_maintenance" value="1" <?= !empty($filters['is_maintenance']) ? 'checked' : '' ?>> 점검</label>
	<button type="submit" class="btn-search">검색</button>
	<a href="/admin/notices" class="btn-reset">초기화</a>
</form>

<div class="top-bar">
	<div class="total-label">전체 <strong><?= number_format($total) ?></strong>건</div>
	<a href="/admin/notices/create" class="btn-create">+ 공지 등록</a>
</div>

<div class="tbl-wrap">
	<table class="data-table">
		<colgroup>
			<col width="5%">
			<col width="">
			<col width="7%">
			<col width="7%">
			<col width="7%">            
			<col width="10%">
			<col width="10%">
		</colgroup>
		<thead>
			<tr>
				<th>NO</th>
				<th class="text_left">제목</th>
				<th>유형</th>
				<th>상태</th>
				<th>조회수</th>
				<th>작성일</th>
				<th>관리</th>
			</tr>
		</thead>
		<tbody>
		<?php if (empty($notices)): ?>
			<tr class="empty-row"><td colspan="7">공지사항이 없습니다.</td></tr>
		<?php else: ?>
			<?php foreach ($notices as $_i => $n): ?>
			<tr>
				<td><?= $total - ($page-1)*$limit - $_i ?></td>
				<td class="text_left" style="max-width:360px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;font-weight:<?= $n['is_pinned'] ? '600' : '400' ?>;">
					<?= htmlspecialchars($n['title']) ?>
				</td>
				<td>
					<?php if ($n['is_maintenance']): ?>
						<span class="badge badge-maintenance">점검</span>
					<?php elseif ($n['is_pinned']): ?>
						<span class="badge badge-pinned">고정</span>
					<?php endif; ?>
				</td>
				<td>
					<span class="badge badge-<?= $n['is_active'] ? 'active' : 'inactive' ?>">
						<?= $n['is_active'] ? '활성' : '비활성' ?>
					</span>
				</td>
				<td><?= number_format($n['views'] ?? 0) ?></td>
				<td><?= date('Y-m-d', strtotime($n['created_at'])) ?></td>
				<td>
					<div class="act-btn-wrap">
						<a href="/admin/notices/<?= $n['notice_idx'] ?>/edit" class="act-btn act-edit">수정</a>
						<form method="POST" action="/admin/notices/<?= $n['notice_idx'] ?>/delete"
							onsubmit="return confirm('공지사항을 삭제하시겠습니까?')">
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
	<?php for ($i = 1; $i <= $totalPages; $i++): ?>
		<?php $q = http_build_query(array_merge($filters, ['page' => $i])); ?>
		<?php if ($i === $page): ?>
				<span class="active"><?= $i ?></span>
		<?php else: ?>
				<a href="/admin/notices?<?= $q ?>"><?= $i ?></a>
		<?php endif; ?>
  <?php endfor; ?>
</div>
<?php endif; ?>
<script>sessionStorage.setItem('back_notices', location.href);</script>
