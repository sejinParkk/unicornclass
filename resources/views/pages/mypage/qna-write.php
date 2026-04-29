<?php
/**
 * 1:1 문의 작성 / 수정
 * 변수: $qna (array|null), $csrfToken, $errors
 */

$isEdit    = $qna !== null;
$editIdx   = $isEdit ? (int)$qna['qna_idx'] : 0;

$old = [
    'category' => $_POST['category'] ?? ($qna['category'] ?? ''),
    'title'    => $_POST['title']    ?? ($qna['title']    ?? ''),
    'content'  => $_POST['content']  ?? ($qna['content']  ?? ''),
];

$catOptions = [
    'class'   => '강의 수강',
    'payment' => '결제/환불',
    'account' => '계정',
    'tech'    => '기술 문제',
    'etc'     => '기타',
];
?>

<div class="sub_index profile_area qna_area">
  <div class="inner">
    <?php require VIEW_PATH . '/components/mp-user-area.php'; ?>
    <div class="sub_page_flex">
      <?php require VIEW_PATH . '/components/mp-subnav.php'; ?>
      <div class="sub_page_contents">
        <div class="page-section-title"><?= $isEdit ? '문의 수정' : '문의하기' ?></div>

        <form id="qnaForm" method="POST" action="/mypage/qna/write" enctype="multipart/form-data">
          <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken) ?>">
          <?php if ($isEdit): ?>
          <input type="hidden" name="edit_idx" value="<?= $editIdx ?>">
          <?php endif; ?>
          <div class="apply-section">          
            <div class="form-field">
              <label class="form-label">문의 유형<span class="req">*</span></label>
              <div class="form-field-wrap">
                <select name="category" class="form-input form-select <?= isset($errors['category']) ? 'error' : '' ?>">
                  <option value="">분류를 선택해주세요</option>
                  <?php foreach ($catOptions as $val => $label): ?>
                  <option value="<?= $val ?>" <?= $old['category'] === $val ? 'selected' : '' ?>>
                    <?= htmlspecialchars($label) ?>
                  </option>
                  <?php endforeach; ?>
                </select>
                <div class="field-error" data-ajax-err="category" style="display:none;"></div>
              </div>
            </div>

            <div class="form-field">
              <label class="form-label">제목<span class="req">*</span></label>
              <div class="form-field-wrap">
                <input type="text" name="title" class="form-input <?= isset($errors['title']) ? 'error' : '' ?>"
                        placeholder="문의 제목을 입력해주세요"
                        value="<?= htmlspecialchars($old['title']) ?>"
                        maxlength="200">
                <div class="field-error" data-ajax-err="title" style="display:none;"></div>
              </div>
            </div>

            <div class="form-field ver2">
              <label class="form-label">내용<span class="req">*</span></label>
              <div class="form-field-wrap">
                <div class="limit_text">
                  <textarea name="content" id="qnaContent" class="form-input form-textarea <?= isset($errors['content']) ? 'error' : '' ?>"
                  placeholder="문의 내용을 자세히 입력해주세요."><?= htmlspecialchars($old['content']) ?></textarea>
                  <p class="limit_curr_text"><span id="curr_text">0</span>/2,000</p>
                </div>
                <div class="field-error" data-ajax-err="content" style="display:none;"></div>
              </div>
            </div>

            <div class="form-field ver2">
              <label class="form-label">첨부파일</label>
              <div class="form-field-wrap">                

                <input type="file" name="qna_file" id="qnaFileInput" accept="image/jpeg,image/png,image/webp,application/pdf" style="display:none">
                <div class="qna-file-zone" id="qnaFileZone">
                  <img src="/assets/img/qna_file_clip.svg" alt="">
                  <span id="qnaFileLabel">파일을 선택하거나 여기에 드래그하세요. (이미지/PDF, 최대 5MB)</span>
                </div>
                <div class="field-error" data-ajax-err="qna_file" style="display:none;"></div>

                <?php if ($isEdit && !empty($qna['file_path'])): ?>
                <div class="qna-file-current" id="qnaFileCurrent">
                  <a href="/uploads/qna/<?= htmlspecialchars($qna['file_path']) ?>" target="_blank" rel="noopener" class="qna-file-current-name" download><?= htmlspecialchars($qna['file_path']) ?></a>
                  <button type="button" class="qna-file-remove-btn" onclick="removeCurrentFile()">삭제</button>
                </div>
                <input type="hidden" name="remove_file" id="removeFileInput" value="">
                <?php endif; ?>

              </div>
            </div>
          </div>

          <div class="profile_btn_box">
            <a href="<?= $isEdit ? '/mypage/qna/' . $editIdx : '/mypage/qna' ?>" class="btn-next btn-save-profile btn-cancel">취소</a>
            <button type="submit" class="btn-next btn-save-profile"><?= $isEdit ? '수정하기' : '등록하기' ?></button>
          </div>
        </form>

      </div>
    </div>
  </div>
</div>

<script>
(function () {
  // ── 글자 수 카운터 ──
  var ta      = document.getElementById('qnaContent');
  var counter = document.getElementById('curr_text');
  var MAX     = 2000;

  function updateCount() {
    if (ta.value.length > MAX) ta.value = ta.value.substring(0, MAX);
    counter.textContent = ta.value.length;
  }
  updateCount();
  ta.addEventListener('input', updateCount);

  // ── 파일 업로드 ──
  var zone   = document.getElementById('qnaFileZone');
  var input  = document.getElementById('qnaFileInput');
  var label  = document.getElementById('qnaFileLabel');

  zone.addEventListener('click', function () { input.click(); });

  zone.addEventListener('dragover', function (e) {
    e.preventDefault();
    zone.classList.add('drag-over');
  });
  zone.addEventListener('dragleave', function () {
    zone.classList.remove('drag-over');
  });
  zone.addEventListener('drop', function (e) {
    e.preventDefault();
    zone.classList.remove('drag-over');
    if (e.dataTransfer.files.length) {
      setFile(e.dataTransfer.files[0]);
    }
  });

  input.addEventListener('change', function () {
    if (this.files.length) setFile(this.files[0]);
  });

  function setFile(file) {
    // DataTransfer로 input에 주입 (드래그 시)
    if (file !== input.files[0]) {
      try {
        var dt = new DataTransfer();
        dt.items.add(file);
        input.files = dt.files;
      } catch (e) {}
    }
    label.textContent = file.name;
    zone.classList.add('has-file');
  }

  <?php if ($isEdit && !empty($qna['file_path'])): ?>
  // ── 기존 파일 삭제 ──
  window.removeCurrentFile = function () {
    document.getElementById('qnaFileCurrent').style.display = 'none';
    document.getElementById('removeFileInput').value = '1';
  };
  <?php else: ?>
  window.removeCurrentFile = function () {};
  <?php endif; ?>
})();

document.getElementById('qnaForm').addEventListener('submit', function (e) {
  e.preventDefault();
  ajaxSubmit(this);
});
</script>
