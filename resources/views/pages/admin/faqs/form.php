<?php
/**
 * 관리자 FAQ 등록/수정 폼
 * @var array|null $faq        null = 등록, array = 수정
 * @var array      $categories [key => label]
 * @var string     $csrfToken
 */
$isEdit    = $faq !== null;
$action    = $isEdit ? '/admin/faqs/' . $faq['faq_idx'] : '/admin/faqs';
?>

<?php if (isset($_GET['saved'])): ?>
<div class="toast-msg toast-success">✓ 저장되었습니다.</div>
<?php endif; ?>
<?php if (isset($_GET['error'])): ?>
<div class="toast-msg toast-error"><?= htmlspecialchars($_GET['error']) ?></div>
<?php endif; ?>

<div class="form-card">
	<form method="POST" action="<?= $action ?>">
		<input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken) ?>">

		<div class="form-row">
			<div class="form-group">
				<label>카테고리</label>
				<select name="category" class="form-control">
					<?php foreach ($categories as $k => $v): ?>
					<option value="<?= $k ?>" <?= ($faq['category'] ?? '') === $k ? 'selected' : '' ?>><?= $v ?></option>
					<?php endforeach; ?>
				</select>
			</div>
			<div class="form-group">
				<label>순서</label>
				<input type="number" name="sort_order" class="form-control"
					   value="<?= (int) ($faq['sort_order'] ?? 0) ?>" min="0">
			</div>
		</div>

		<div class="form-group">
			<label>활성 상태</label>
			<select name="is_active" class="form-control" style="max-width:200px;">
				<option value="1" <?= ($faq['is_active'] ?? 1) == 1 ? 'selected' : '' ?>>활성</option>
				<option value="0" <?= ($faq['is_active'] ?? 1) == 0 ? 'selected' : '' ?>>비활성</option>
			</select>
		</div>

		<div class="form-group">
			<label>질문 <span class="req">*</span></label>
			<input type="text" name="question" class="form-control"
				   value="<?= htmlspecialchars($faq['question'] ?? '') ?>"
				   placeholder="자주 묻는 질문을 입력하세요." required>
		</div>

		<div class="form-group">
			<label>답변 <span class="req">*</span></label>
			<textarea name="answer" class="form-control"
					  placeholder="답변을 입력하세요." required><?= htmlspecialchars($faq['answer'] ?? '') ?></textarea>
		</div>

		<div class="form-actions">
					<a href="/admin/faqs" class="btn-back">목록</a>
					<button type="submit" class="btn-save"><?= $isEdit ? '수정 저장' : '등록' ?></button>
					<!-- <a href="/admin/faqs" class="btn-cancel">취소</a> -->
					<?php if ($isEdit): ?>
					<button type="button" class="btn-delete"
									onclick="if(confirm('삭제하시겠습니까?')) deleteFaq(<?= $faq['faq_idx'] ?>)">삭제</button>
					<?php endif; ?>
		</div>
	</form>

	<?php if ($isEdit): ?>
	<form id="deleteForm" method="POST" action="/admin/faqs/<?= $faq['faq_idx'] ?>/delete" style="display:none;">
		<input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken) ?>">
	</form>
	<script>
	function deleteFaq(idx) {
		document.getElementById('deleteForm').submit();
	}
	</script>
	<?php endif; ?>
</div>
<script>
(function(){
	var u = sessionStorage.getItem('back_faqs');
	if (u) { var el = document.querySelector('.btn-back'); if (el) el.href = u; }
})();
</script>
