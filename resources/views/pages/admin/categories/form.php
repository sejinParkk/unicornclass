<?php
/**
 * 관리자 카테고리 등록/수정 폼
 * @var array|null $category   null=신규, array=수정
 * @var string     $type       'class' | 'instructor'
 * @var string     $csrfToken
 */
$isEdit    = $category !== null;
$typeLabel = $type === 'class' ? '강의' : '강사';
$backUrl   = '/admin/categories?type=' . $type;
$action    = $isEdit ? '/admin/categories/' . (int)$category['category_idx'] : '/admin/categories';
?>

<?php if (isset($_GET['saved'])): ?>
<div class="toast-msg toast-success">✓ 저장되었습니다.</div>
<?php elseif (isset($_GET['error'])): ?>
<div class="toast-msg toast-error">카테고리명을 입력해주세요.</div>
<?php endif; ?>

<form method="POST" action="<?= $action ?>">
	<input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken) ?>">
	<input type="hidden" name="type" value="<?= htmlspecialchars($type) ?>">

	<div class="form-card">
		<h3><?= $typeLabel ?> 카테고리 <?= $isEdit ? '수정' : '추가' ?></h3>

		<div class="form-group">
			<label class="form-label required">카테고리명</label>
			<input type="text" name="name" class="form-control"
				value="<?= $isEdit ? htmlspecialchars($category['name']) : '' ?>"
				maxlength="50" required autofocus>
		</div>

		<div class="form-group">
			<label class="form-label">정렬 순서</label>
			<input type="number" name="sort_order" class="form-control" style="width:120px;"
				value="<?= $isEdit ? (int)$category['sort_order'] : 0 ?>" min="0">
			<p class="form-hint">숫자가 낮을수록 앞에 표시됩니다.</p>
		</div>

		<?php if ($isEdit): ?>
		<div class="form-group">
			<label class="form-label">상태</label>
			<div class="radio-row">
				<label class="radio-item">
					<input type="radio" name="is_active" value="1"
						<?= $category['is_active'] ? 'checked' : '' ?>>
					활성 (노출)
				</label>
				<label class="radio-item">
					<input type="radio" name="is_active" value="0"
						<?= !$category['is_active'] ? 'checked' : '' ?>>
					비활성 (숨김)
				</label>
			</div>
		</div>
		<?php endif; ?>
	</div>

	<div class="form-actions">
		<a href="<?= $backUrl ?>" class="btn-back">목록</a>
		<button type="submit" class="btn-save"><?= $isEdit ? '저장' : '추가' ?></button>
		<?php if ($isEdit): ?>
		<button type="button" class="btn-delete"
			onclick="if(confirm('카테고리를 삭제하시겠습니까?\n사용 중인 카테고리는 삭제되지 않습니다.')) document.getElementById('deleteForm').submit()">
			삭제
		</button>
		<?php endif; ?>
	</div>
</form>

<?php if ($isEdit): ?>
<form id="deleteForm" method="POST"
	action="/admin/categories/<?= (int)$category['category_idx'] ?>/delete" style="display:none">
	<input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken) ?>">
	<input type="hidden" name="type" value="<?= htmlspecialchars($type) ?>">
</form>
<?php endif; ?>
