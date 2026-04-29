<?php
/**
 * 관리자 강의 목록
 * @var array  $classes
 * @var int    $total
 * @var int    $page
 * @var int    $totalPages
 * @var array  $filters
 * @var array  $categories
 */
?>

<?php if (isset($_GET['created'])): ?>
<div class="toast-msg toast-success">✓ 강의가 등록되었습니다. 챕터를 추가해주세요.</div>
<?php elseif (isset($_GET['updated'])): ?>
<div class="toast-msg toast-success">✓ 강의가 수정되었습니다.</div>
<?php elseif (isset($_GET['deleted'])): ?>
<div class="toast-msg toast-success">✓ 강의가 삭제되었습니다.</div>
<?php elseif (isset($_GET['error'])): ?>
<div class="toast-msg toast-error"><?= htmlspecialchars($_GET['error']) ?></div>
<?php endif; ?>

<!-- 필터 -->
<form method="GET" action="/admin/classes" class="filter-bar">
	<input type="text" name="q" value="<?= htmlspecialchars($filters['q']) ?>" placeholder="강의명 검색">
	<select name="type">
		<option value="">전체 유형</option>
		<option value="free"    <?= $filters['type']==='free'    ?'selected':'' ?>>무료</option>
		<option value="premium" <?= $filters['type']==='premium' ?'selected':'' ?>>프리미엄</option>
	</select>
	<select name="category_idx">
		<option value="">전체 카테고리</option>
		<?php foreach ($categories as $cat): ?>
		<option value="<?= $cat['category_idx'] ?>"
			<?= (string)$filters['category_idx'] === (string)$cat['category_idx'] ? 'selected' : '' ?>>
			<?= htmlspecialchars($cat['name']) ?>
		</option>
		<?php endforeach; ?>
	</select>
	<select name="is_active">
		<option value="">전체 상태</option>
		<option value="1" <?= $filters['is_active']==='1' ?'selected':'' ?>>활성</option>
		<option value="0" <?= $filters['is_active']==='0' ?'selected':'' ?>>비활성</option>
	</select>
	<button type="submit" class="btn-search">검색</button>
	<a href="/admin/classes" class="btn-reset">초기화</a>
</form>

<div class="top-bar">
	<span class="total-label">총 <strong><?= number_format($total) ?></strong>개</span>
	<a href="/admin/classes/create" class="btn-create">
		<svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24">
			<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
		</svg>
		강의 등록
	</a>
</div>

<div class="tbl-wrap">
	<table class="data-table">
	<colgroup>
		<col width="5%">
		<col width="6%">
		<col width="">
		<col width="7%">
		<col width="7%">
		<col width="7%">
		<col width="7%">
		<col width="7%">
		<col width="7%">
		<col width="7%">
		<col width="10%">
		<col width="10%">
	</colgroup>
	<thead>
		<tr>
			<th>NO</th>
			<th>썸네일</th>
			<th class="text_left">제목</th>
			<th>강사</th>
			<th>카테고리</th>
			<th>유형</th>
			<th>수강자</th>
			<th>후기</th>
			<th>상태</th>
			<th>판매종료</th>
			<th>등록일</th>
			<th>관리</th>
		</tr>
	</thead>
	<tbody>
	<?php if (empty($classes)): ?>
		<tr class="empty-row"><td colspan="12">등록된 강의가 없습니다.</td></tr>
	<?php else: ?>
	<?php foreach ($classes as $_i => $c): ?>
		<?php
		$isSaleEnded = $c['sale_end_at'] && strtotime($c['sale_end_at']) < time();
		?>
		<tr>
			<td><?= $total - ($page-1)*$limit - $_i ?></td>
			<td>
				<?php if ($c['thumbnail']): ?>
					<img src="/uploads/class/<?= htmlspecialchars($c['thumbnail']) ?>" alt="">
				<?php else: ?>
					<div class="thumb-empty">No img</div>
				<?php endif; ?>
			</td>
			<td class="text_left">
				<div class="class-title"><?= htmlspecialchars($c['title']) ?></div>
				<!-- <?php if ($c['summary']): ?>
				<div class="class-summary"><?= htmlspecialchars($c['summary']) ?></div>
				<?php endif; ?> -->
			</td>
			<td><?= htmlspecialchars($c['instructor_name']) ?></td>
			<td><?= htmlspecialchars($c['category_name'] ?? '미분류') ?></td>
			<td>
				<span class="badge badge-<?= $c['type'] ?>">
					<?= $c['type'] === 'free' ? '무료' : '프리미엄' ?>
				</span>
			</td>
			<td><?= number_format((int)$c['enroll_count']) ?></td>
			<td><?= number_format((int)$c['review_count']) ?></td>
			<td>
				<?php if (!$c['is_active']): ?>
					<span class="badge badge-inactive">비활성</span>
				<?php elseif ($isSaleEnded): ?>
					<span class="badge badge-ended">판매종료</span>
				<?php else: ?>
					<span class="badge badge-active">판매중</span>
				<?php endif; ?>
			</td>
			<td>
				<?= $c['sale_end_at'] ? date('Y-m-d', strtotime($c['sale_end_at'])) : '무기한' ?>
			</td>
			<td>
				<?= date('Y-m-d', strtotime($c['created_at'])) ?>
			</td>
			<td>
				<div class="act-btn-wrap">
					<a href="/admin/classes/<?= $c['class_idx'] ?>/edit" class="act-btn act-edit">수정</a>
					<form method="POST" action="/admin/classes/<?= $c['class_idx'] ?>/delete"
						  onsubmit="return confirm('삭제하시겠습니까?\n수강자가 있으면 비활성화됩니다.')">
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

<!-- 페이지네이션 -->
<?php if ($totalPages > 1): ?>
<div class="pagination">
	<?php
	$qs = http_build_query(array_merge($filters, ['page' => $page - 1]));
	?>
	<?php if ($page > 1): ?>
		<a href="/admin/classes?<?= $qs ?>">‹</a>
	<?php else: ?>
		<span class="disabled">‹</span>
	<?php endif; ?>

	<?php for ($i = max(1, $page-2); $i <= min($totalPages, $page+2); $i++): ?>
		<?php $qs = http_build_query(array_merge($filters, ['page' => $i])); ?>
		<?php if ($i === $page): ?>
			<span class="active"><?= $i ?></span>
		<?php else: ?>
			<a href="/admin/classes?<?= $qs ?>"><?= $i ?></a>
		<?php endif; ?>
	<?php endfor; ?>

	<?php $qs = http_build_query(array_merge($filters, ['page' => $page + 1])); ?>
	<?php if ($page < $totalPages): ?>
		<a href="/admin/classes?<?= $qs ?>">›</a>
	<?php else: ?>
		<span class="disabled">›</span>
	<?php endif; ?>
</div>
<?php endif; ?>
<script>sessionStorage.setItem('back_classes', location.href);</script>
