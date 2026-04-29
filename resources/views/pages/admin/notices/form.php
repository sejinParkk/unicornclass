<?php
/**
 * 관리자 공지사항 등록/수정 폼
 * @var array|null $notice   null이면 등록, 배열이면 수정
 * @var string     $csrfToken
 */
$isEdit  = $notice !== null;
$action  = $isEdit ? '/admin/notices/' . $notice['notice_idx'] : '/admin/notices';
$h       = fn(string $k, string $d = '') => htmlspecialchars($notice[$k] ?? $d);
?>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<link href="https://cdn.jsdelivr.net/npm/summernote@0.8.20/dist/summernote-lite.min.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/summernote@0.8.20/dist/summernote-lite.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/summernote@0.8.20/dist/lang/summernote-ko-KR.min.js"></script>

<?php if (isset($_GET['saved'])): ?>
<div class="toast-msg toast-success">✓ 저장되었습니다.</div>
<?php endif; ?>
<?php if (isset($_GET['error'])): ?>
<div class="toast-msg toast-error"><?= htmlspecialchars($_GET['error']) ?></div>
<?php endif; ?>


	
		<form method="POST" action="<?= $action ?>" id="noticeForm">
			<input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken) ?>">
			<div class="form-card">
				<div class="form-group">
					<label>제목 <span style="color:#c0392b">*</span></label>
					<input type="text" name="title" class="form-control"
									value="<?= $h('title') ?>" placeholder="공지 제목 입력">
				</div>
				<div class="form-group">
					<label>내용</label>
					<textarea id="notice-editor" name="content"><?= htmlspecialchars($notice['content'] ?? '') ?></textarea>
				</div>
				<div class="form-group">
					<label>공개 상태</label>
					<div class="radio-row">
						<label class="radio-item">
							<input type="radio" name="is_active" value="1" <?= ($notice['is_active'] ?? 1) ? 'checked' : '' ?>>
							활성 (공개)
						</label>
						<label class="radio-item">
							<input type="radio" name="is_active" value="0" <?= isset($notice) && !$notice['is_active'] ? 'checked' : '' ?>>
							비활성 (숨김)
						</label>
					</div>
				</div>
				<div class="form-group">
					<label>공지 유형</label>
					<?php
					$noticeType = 'none';
					if (!empty($notice['is_maintenance'])) $noticeType = 'maintenance';
					elseif (!empty($notice['is_pinned']))   $noticeType = 'pinned';
					?>
					<div class="radio-row">
						<label class="radio-item">
							<input type="radio" name="notice_type" value="none" <?= $noticeType === 'none' ? 'checked' : '' ?>>
							없음
						</label>
						<label class="radio-item">
							<input type="radio" name="notice_type" value="pinned" <?= $noticeType === 'pinned' ? 'checked' : '' ?>>
							상단고정
						</label>
						<label class="radio-item">
							<input type="radio" name="notice_type" value="maintenance" <?= $noticeType === 'maintenance' ? 'checked' : '' ?>>
							점검
						</label>
					</div>
				</div>
			</div>
			<div class="form-actions">
				<a href="/admin/notices" class="btn-back">목록</a>
				<button type="submit" class="btn-save">저장</button>
				<?php if ($isEdit): ?>
				<button type="button" class="btn-delete"
								onclick="if(confirm('공지사항을 삭제하시겠습니까?\n삭제된 공지사항은 복구할 수 없습니다.')) document.getElementById('deleteNoticeForm').submit()">삭제</button>
				<?php endif; ?>
			</div>
		</form>
		
		<?php if ($isEdit): ?>
		<form id="deleteNoticeForm" method="POST" action="/admin/notices/<?= $notice['notice_idx'] ?>/delete" style="display:none">
				<input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken) ?>">
		</form>
		<?php endif; ?> 

<script>
$(document).ready(function() {
	// 1. 썸머노트 실행
	$('#notice-editor').summernote({
		height: 420,                 
		minHeight: null,             
		maxHeight: null,             
		focus: false,                
		lang: 'ko-KR',               
		toolbar: [
			['fontname', ['fontname']],
			['fontsize', ['fontsize']],
			['style', ['bold', 'italic', 'underline','strikethrough', 'clear']],
			['color', ['forecolor','color']],
			['table', ['table']],
			['para', ['ul', 'ol', 'paragraph']],
			['height', ['height']],
			//['insert',['picture','link','video']],
			['insert',['picture']],
			['view', ['codeview']]
		]
	});

	// 2. 저장 버튼: 써머노트 동기화 후 AJAX 제출
	$('#noticeForm').on('submit', function(e) {
		e.preventDefault();
		$('#notice-editor').val($('#notice-editor').summernote('code'));
		ajaxSubmit(document.getElementById('noticeForm'));
	});
});
</script>
<script>
(function(){
	var u = sessionStorage.getItem('back_notices');
	if (u) { var el = document.querySelector('.btn-back'); if (el) el.href = u; }
})();
</script>