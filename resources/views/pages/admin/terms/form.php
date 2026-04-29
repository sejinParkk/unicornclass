<?php
/**
 * 관리자 약관 버전 등록/수정 폼
 * @var string     $type
 * @var string     $typeName
 * @var array|null $version   null=신규, 배열=수정
 * @var string     $csrfToken
 */
$isEdit = $version !== null;
$action = $isEdit
    ? '/admin/terms/v/' . $version['terms_idx']
    : '/admin/terms/' . $type;
?>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<link href="https://cdn.jsdelivr.net/npm/summernote@0.8.20/dist/summernote-lite.min.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/summernote@0.8.20/dist/summernote-lite.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/summernote@0.8.20/dist/lang/summernote-ko-KR.min.js"></script>

<?php if (isset($_GET['error'])): ?>
<div class="toast-msg toast-error"><?= htmlspecialchars($_GET['error']) ?></div>
<?php endif; ?>

<div style="margin-bottom:16px">
  <a href="/admin/terms" style="color:#888;text-decoration:none">약관 관리</a>
  &nbsp;›&nbsp;
  <a href="/admin/terms/<?= htmlspecialchars($type) ?>/versions" style="color:#888;text-decoration:none"><?= htmlspecialchars($typeName) ?></a>
  &nbsp;›&nbsp; <?= $isEdit ? '버전 수정' : '새 버전 등록' ?>
</div>

<form method="POST" action="<?= $action ?>" id="termsForm">
  <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken) ?>">

  <div class="form-card">
    <div class="form-row">
      <div class="form-group">
        <label>제목 <span class="req">*</span></label>
        <input type="text" name="title" class="form-control"
               value="<?= htmlspecialchars($version['title'] ?? $typeName) ?>"
               placeholder="약관 제목">
      </div>
      <div class="form-group">
        <label>시행일 <span class="req">*</span></label>
        <input type="date" name="effective_at" class="form-control"
               value="<?= htmlspecialchars($version['effective_at'] ?? date('Y-m-d')) ?>">
      </div>
    </div>

    <?php if (!$isEdit): ?>
    <div class="form-group">
      <label>현재 버전 설정</label>
      <div class="radio-row">
        <label class="radio-item">
          <input type="checkbox" name="is_current" value="1" checked>
          저장 즉시 현재 버전으로 설정
        </label>
      </div>
    </div>
    <?php endif; ?>

    <div class="form-group">
      <label>내용</label>
      <textarea id="terms-editor" name="content"><?= htmlspecialchars($version['content'] ?? '') ?></textarea>
    </div>
  </div>

  <div class="form-actions">
    <a href="/admin/terms/<?= htmlspecialchars($type) ?>/versions" class="btn-back">목록</a>
    <button type="submit" class="btn-save">저장</button>
  </div>
</form>

<script>
$(document).ready(function () {
  $('#terms-editor').summernote({
    height: 500,
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
  });

  $('#termsForm').on('submit', function (e) {
    e.preventDefault();
    $('#terms-editor').val($('#terms-editor').summernote('code'));
    ajaxSubmit(document.getElementById('termsForm'));
  });
});
</script>
