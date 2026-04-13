<?php
/**
 * 관리자 약관 관리
 * @var array       $termData   ['terms' => row, 'privacy' => row, ...]
 * @var array       $termTypes  ['terms' => '이용약관', ...]
 * @var string      $csrfToken
 * @var string|null $saved      저장된 type 키
 */
$activeTab = $saved ?? array_key_first($termTypes);
?>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<link href="https://cdn.jsdelivr.net/npm/summernote@0.8.20/dist/summernote-lite.min.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/summernote@0.8.20/dist/summernote-lite.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/summernote@0.8.20/dist/lang/summernote-ko-KR.min.js"></script>

<?php if ($saved && isset($termTypes[$saved])): ?>
<div class="toast-msg toast-success">✓ <?= htmlspecialchars($termTypes[$saved]) ?>이(가) 저장되었습니다.</div>
<?php endif; ?>
<?php if (isset($_GET['error'])): ?>
<div class="toast-msg toast-error"><?= htmlspecialchars($_GET['error']) ?></div>
<?php endif; ?>

<!-- 탭 -->
<div class="terms-tabs">
	<?php foreach ($termTypes as $key => $label): ?>
	<button class="terms-tab <?= $activeTab === $key ? 'active' : '' ?>"
		onclick="switchTab('<?= $key ?>', this)"><?= htmlspecialchars($label) ?></button>
	<?php endforeach; ?>
</div>

<!-- 패널 -->
<?php foreach ($termTypes as $key => $label):
	$row = $termData[$key] ?? null;
?>
<div id="panel-<?= $key ?>" class="terms-panel <?= $activeTab === $key ? 'active' : '' ?>">
	<form method="POST" action="/admin/terms/<?= $key ?>" id="form-<?= $key ?>">
		<input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken) ?>">
		<div class="form-card">
			<div class="form-group">
				<label>제목</label>
				<input type="text" name="title" class="form-control"
					value="<?= htmlspecialchars($row['title'] ?? $label) ?>">
			</div>
			<div class="form-group">
				<label>내용</label>
				<textarea id="editor-<?= $key ?>" name="content"><?= htmlspecialchars($row['content'] ?? '') ?></textarea>
			</div>
		</div>
		<div class="form-actions">
			<button type="submit" class="btn-save"><?= htmlspecialchars($label) ?> 저장</button>
		</div>
	</form>
</div>
<?php endforeach; ?>

<script>
$(document).ready(function() {
	var summernoteConfig = {
		height: 480,
		minHeight: null,
		maxHeight: null,
		focus: false,
		lang: 'ko-KR',
		toolbar: [
			['fontname', ['fontname']],
			['fontsize', ['fontsize']],
			['style', ['bold', 'italic', 'underline', 'strikethrough', 'clear']],
			['color', ['forecolor', 'color']],
			['table', ['table']],
			['para', ['ul', 'ol', 'paragraph']],
			['height', ['height']],
			['insert', ['picture', 'link', 'video']],
			['view', ['codeview']]
		]
	};

	<?php foreach (array_keys($termTypes) as $key): ?>
	$('#editor-<?= $key ?>').summernote(summernoteConfig);
	$('#form-<?= $key ?>').on('submit', function() {
		$('#editor-<?= $key ?>').val($('#editor-<?= $key ?>').summernote('code'));
	});
	<?php endforeach; ?>
});

function switchTab(type, btn) {
	document.querySelectorAll('.terms-tab').forEach(t => t.classList.remove('active'));
	document.querySelectorAll('.terms-panel').forEach(p => p.classList.remove('active'));
	btn.classList.add('active');
	document.getElementById('panel-' + type).classList.add('active');
}
</script>
