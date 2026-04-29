<?php
/**
 * 관리자 카테고리 관리
 * @var array  $classCategories
 * @var array  $instructorCategories
 * @var string $type  'class' | 'instructor'
 * @var string $csrfToken
 */
?>

<?php if (isset($_GET['saved'])): ?>
<div class="toast-msg toast-success">✓ 저장되었습니다.</div>
<?php endif; ?>

<!-- 탭 -->
<div class="tab-bar" style="margin-bottom:24px">
	<a href="/admin/categories?type=class" class="tab-btn <?= $type === 'class' ? 'active' : '' ?>">강의 카테고리</a>
	<a href="/admin/categories?type=instructor" class="tab-btn <?= $type === 'instructor' ? 'active' : '' ?>">강사 카테고리</a>
</div>

<?php
$currentList = $type === 'class' ? $classCategories : $instructorCategories;
$typeLabel   = $type === 'class' ? '강의' : '강사';
?>

<div class="top-bar">
	<div class="total-label">전체 <strong><?= count($currentList) ?></strong>개</div>
	<a class="btn-create" href="/admin/categories/create?type=<?= $type ?>">+ 카테고리 추가</a>
</div>

<div class="tbl-wrap">
	<table class="data-table">
		<colgroup>
			<col width="8%">
			<col width="">
			<col width="10%">
			<col width="10%">
			<col width="14%">
		</colgroup>
		<thead>
			<tr>
				<th>NO</th>
				<th>카테고리명</th>
				<th>순서</th>
				<th>상태</th>
				<th>관리</th>
			</tr>
		</thead>
		<tbody id="category-tbody">
			<?php if (empty($currentList)): ?>
				<tr class="empty-row"><td colspan="5">등록된 카테고리가 없습니다.</td></tr>
			<?php else: ?>
				<?php foreach ($currentList as $i => $cat): ?>
				<tr data-idx="<?= (int)$cat['category_idx'] ?>">
					<td><?= $i + 1 ?></td>
					<td class="td-name"><?= htmlspecialchars($cat['name']) ?></td>
					<td><?= (int)$cat['sort_order'] ?></td>
					<td>
						<span class="badge <?= $cat['is_active'] ? 'badge-active' : 'badge-inactive' ?>">
							<?= $cat['is_active'] ? '활성' : '비활성' ?>
						</span>
					</td>
					<td>
					<a class="act-btn act-edit"
						href="/admin/categories/<?= (int)$cat['category_idx'] ?>/edit?type=<?= $type ?>">
						수정
					</a>
						<button class="act-btn act-del"
										onclick="deleteCategory(<?= (int)$cat['category_idx'] ?>, this)">
								삭제
						</button>
					</td>
				</tr>
				<?php endforeach; ?>
			<?php endif; ?>
		</tbody>
	</table>
</div>

<script>
const CSRF = '<?= htmlspecialchars($csrfToken) ?>';
const TYPE = '<?= $type ?>';

async function deleteCategory(idx, btn) {
	if (!confirm('삭제하시겠습니까? 사용 중인 카테고리는 삭제되지 않습니다.')) return;

	const fd = new FormData();
	fd.append('csrf_token', CSRF);
	fd.append('type', TYPE);

	const res  = await fetch(`/admin/categories/${idx}/delete`, { method: 'POST', body: fd });
	const data = await res.json();
	if (!data.ok) { alert(data.error || '삭제할 수 없습니다.'); return; }

	btn.closest('tr').remove();

	const tbody = document.getElementById('category-tbody');
	if (!tbody.querySelector('tr')) {
		tbody.innerHTML = '<tr class="empty-row"><td colspan="5">등록된 카테고리가 없습니다.</td></tr>';
	}
}

document.getElementById('addModal').addEventListener('click', e => {
	if (e.target.id === 'addModal') closeModal('addModal');
});
</script>
