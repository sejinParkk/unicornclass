<?php
/**
 * 관리자 강사 등록/수정 폼
 * @var array|null $instructor   null=등록, array=수정
 * @var array      $categories
 * @var array      $errors
 * @var string     $csrfToken
 */
$isEdit = !is_null($instructor);
$action = $isEdit
	? '/admin/instructors/' . $instructor['instructor_idx']
	: '/admin/instructors';

$initIntros  = $instructor['intros']  ?? [];
$initCareers = $instructor['careers'] ?? [];

// 유효성 검사 실패 시 POST 값 복원
if (!empty($_POST['intros_json'])) {
	$decoded = json_decode($_POST['intros_json'], true);
	if (is_array($decoded)) $initIntros = array_map(fn($v) => ['content' => $v], $decoded);
}
if (!empty($_POST['careers_json'])) {
	$decoded = json_decode($_POST['careers_json'], true);
	if (is_array($decoded)) $initCareers = array_map(fn($v) => ['content' => $v], $decoded);
}
?>

<div class="">
<form method="POST" action="<?= $action ?>" enctype="multipart/form-data">
	<input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken) ?>">

	<div class="form-layout ver2">
		<div>
			<!-- 기본 정보 -->
			<div class="form-card">
				<h3>기본 정보</h3>

				<div class="form-row">
						<div class="form-group">
								<label>강사명 <span class="req">*</span></label>
								<input type="text" name="name" class="form-control <?= isset($errors['name']) ? 'error' : '' ?>"
												value="<?= htmlspecialchars($_POST['name'] ?? $instructor['name'] ?? '') ?>"
												placeholder="강사명을 입력하세요">
								<?php if (isset($errors['name'])): ?>
										<div class="error-msg"><?= htmlspecialchars($errors['name']) ?></div>
								<?php endif; ?>
						</div>
						<div class="form-group">
								<label>전문 분야</label>
								<input type="text" name="field" class="form-control"
												value="<?= htmlspecialchars($_POST['field'] ?? $instructor['field'] ?? '') ?>"
												placeholder="예: 마케팅, 브랜딩">
						</div>
				</div>

				<div class="form-row">
						<div class="form-group">
								<label>카테고리</label>
								<select name="category_idx" class="form-control">
										<option value="">카테고리 없음</option>
										<?php foreach ($categories as $cat): ?>
										<option value="<?= $cat['category_idx'] ?>"
												<?= ((int)($_POST['category_idx'] ?? $instructor['category_idx'] ?? 0)) === (int)$cat['category_idx'] ? 'selected' : '' ?>>
												<?= htmlspecialchars($cat['name']) ?>
										</option>
										<?php endforeach; ?>
								</select>
						</div>
						<div class="form-group">
								<label>정렬 순서</label>
								<input type="number" name="sort_order" class="form-control"
												value="<?= (int)($_POST['sort_order'] ?? $instructor['sort_order'] ?? 0) ?>" min="0">
								<div class="hint">숫자가 낮을수록 먼저 표시됩니다.</div>
						</div>
				</div>

				<div class="form-group">
					<label>노출 상태</label>
					<div class="toggle-wrap">
							<input type="checkbox" class="toggle" name="is_active" id="isActive"
											<?= (isset($_POST['is_active']) || (!isset($_POST['name']) && ($instructor['is_active'] ?? 1))) ? 'checked' : '' ?>>
							<label for="isActive" style="font-weight:400;font-size:13px;color:#4a5568;">강사 목록에 노출</label>
					</div>
				</div>
			</div>

			<!-- 강사 소개 -->
			<div class="form-card">
				<h3>강사 소개</h3>
				<input type="hidden" name="intros_json"  id="introsJson"  value="">
				<input type="hidden" name="careers_json" id="careersJson" value="">

				<div class="form-group">
					<label>소개 항목</label>
					<ul class="item-list" id="introList"></ul>
					<button type="button" class="btn-item-add" onclick="addItem('intro')">+ 소개 항목 추가</button>
				</div>

				<div class="form-group" style="margin-top:16px">
					<label>경력 사항</label>
					<ul class="item-list" id="careerList"></ul>
					<button type="button" class="btn-item-add" onclick="addItem('career')">+ 경력 항목 추가</button>
				</div>
			</div>
		</div>

		<div>
			<!-- 프로필 사진 -->
			<div class="form-card">
				<h3>프로필 사진</h3>
				<?php if ($isEdit && $instructor['photo']): ?>
					<img src="/uploads/instructor/<?= htmlspecialchars($instructor['photo']) ?>" alt="현재 사진" class="photo-preview" id="photoPreview">
				<?php else: ?>
					<div class="photo-placeholder" id="photoPlaceholder">
						<svg width="32" height="32" fill="none" stroke="currentColor" viewBox="0 0 24 24">
							<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
						</svg>
					</div>
					<img src="" alt="" class="photo-preview" id="photoPreview" style="display:none">
				<?php endif; ?>
				<input type="file" name="photo" id="photoInput" accept="image/jpeg,image/png,image/webp" style="margin-top:4px">
				<div class="hint">jpg, png, webp / 최대 5MB / 권장 1:1 비율</div>
				<?php if (isset($errors['photo'])): ?>
				<div class="error-msg"><?= htmlspecialchars($errors['photo']) ?></div>
				<?php endif; ?>
			</div>

			<!-- SNS -->
			<div class="form-card">
				<h3>SNS 링크</h3>
				<div class="form-group">
					<label>유튜브</label>
					<input type="url" name="sns_youtube" class="form-control"
									value="<?= htmlspecialchars($_POST['sns_youtube'] ?? $instructor['sns_youtube'] ?? '') ?>"
									placeholder="https://youtube.com/@...">
				</div>
				<div class="form-group">
					<label>인스타그램</label>
					<input type="url" name="sns_instagram" class="form-control"
									value="<?= htmlspecialchars($_POST['sns_instagram'] ?? $instructor['sns_instagram'] ?? '') ?>"
									placeholder="https://instagram.com/...">
				</div>
				<div class="form-group">
					<label>페이스북</label>
					<input type="url" name="sns_facebook" class="form-control"
									value="<?= htmlspecialchars($_POST['sns_facebook'] ?? $instructor['sns_facebook'] ?? '') ?>"
									placeholder="https://facebook.com/...">
				</div>
			</div>
		</div>
	</div>  

	<div class="form-actions">
			<button type="submit" class="btn-save"><?= $isEdit ? '수정' : '강사 등록' ?></button>
			<a href="/admin/instructors" class="btn-cancel">취소</a>
			<?php if ($isEdit): ?>
			<button type="button" class="btn-delete"
							onclick="if(confirm('강사를 삭제하시겠습니까?\n담당 강의가 있으면 삭제할 수 없습니다.')) document.getElementById('deleteInstructorForm').submit()">삭제</button>
			<?php endif; ?>
	</div>
</form>
<?php if ($isEdit): ?>
<form id="deleteInstructorForm" method="POST" action="/admin/instructors/<?= $instructor['instructor_idx'] ?>/delete" style="display:none">
	<input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken) ?>">
</form>
<?php endif; ?>

<style>
.item-list { list-style: none; margin: 0 0 8px; padding: 0; }
.item-list li { display: flex; align-items: center; gap: 8px; margin-bottom: 6px; }
.item-list li input { flex: 1; }
.item-del { flex-shrink: 0; background: none; border: 1px solid #e2e8f0; border-radius: 4px;
			padding: 4px 8px; cursor: pointer; color: #e53e3e; font-size: 13px; }
.item-del:hover { background: #fff5f5; }
.btn-item-add { background: none; border: 1px dashed #a0aec0; border-radius: 6px;
				padding: 6px 14px; color: #4a5568; font-size: 13px; cursor: pointer; }
.btn-item-add:hover { border-color: #667eea; color: #667eea; background: #f0f4ff; }
</style>
<script>
// ── 강사 소개 / 경력 항목 관리 ──────────────────────────────
const initIntros  = <?= json_encode(array_column($initIntros,  'content')) ?>;
const initCareers = <?= json_encode(array_column($initCareers, 'content')) ?>;

let intros  = [...initIntros];
let careers = [...initCareers];

function escHtml(str) {
	return String(str).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
}

function renderList(type) {
	const items = type === 'intro' ? intros : careers;
	const ul    = document.getElementById(type === 'intro' ? 'introList' : 'careerList');
	ul.innerHTML = '';
	items.forEach((val, i) => {
		const li = document.createElement('li');
		li.innerHTML = `
			<input type="text" class="form-control" value="${escHtml(val)}"
				   oninput="updateItem('${type}', ${i}, this.value)"
				   placeholder="${type === 'intro' ? '소개 문구를 입력하세요' : '경력 사항을 입력하세요'}">
			<button type="button" class="item-del" onclick="removeItem('${type}', ${i})">✕</button>`;
		ul.appendChild(li);
	});
}

function addItem(type) {
	if (type === 'intro') intros.push('');
	else careers.push('');
	renderList(type);
	// 새로 추가된 input에 포커스
	const ul = document.getElementById(type === 'intro' ? 'introList' : 'careerList');
	ul.querySelectorAll('input').forEach((el, i, arr) => { if (i === arr.length - 1) el.focus(); });
}

function removeItem(type, idx) {
	if (type === 'intro') intros.splice(idx, 1);
	else careers.splice(idx, 1);
	renderList(type);
}

function updateItem(type, idx, val) {
	if (type === 'intro') intros[idx] = val;
	else careers[idx] = val;
}

// ── 사진 미리보기 ────────────────────────────────────────────
document.getElementById('photoInput').addEventListener('change', function () {
	const file = this.files[0];
	if (!file) return;
	const preview = document.getElementById('photoPreview');
	const placeholder = document.getElementById('photoPlaceholder');
	preview.src = URL.createObjectURL(file);
	preview.style.display = 'block';
	if (placeholder) placeholder.style.display = 'none';
});

// ── 폼 제출 시 JSON 직렬화 ────────────────────────────────────
document.querySelector('form').addEventListener('submit', function () {
	document.getElementById('introsJson').value  = JSON.stringify(intros.filter(v => v.trim() !== ''));
	document.getElementById('careersJson').value = JSON.stringify(careers.filter(v => v.trim() !== ''));
});

// ── 초기 렌더링 ──────────────────────────────────────────────
renderList('intro');
renderList('career');
(function(){
	var u = sessionStorage.getItem('back_instructors');
	if (u) { var el = document.querySelector('.btn-cancel'); if (el) el.href = u; }
})();
</script>
