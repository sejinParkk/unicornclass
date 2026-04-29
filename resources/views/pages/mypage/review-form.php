<?php
/**
 * 후기 작성 / 수정
 * 변수: $review (array|null), $class (array), $csrfToken, $errors
 * $editIdx, $classIdx, $reviewImages (array of ['image_path'=>...])
 */

$isEdit     = ($editIdx ?? 0) > 0;
$oldRating  = (int) ($_POST['rating']  ?? ($review['rating']  ?? 0));
$oldTitle   = htmlspecialchars($_POST['title']   ?? ($review['title']   ?? ''));
$oldContent = $_POST['content'] ?? ($review['content'] ?? '');
$reviewImages = $reviewImages ?? [];
$maxImages = 3;
$existingCount = count($reviewImages);
?>

<div class="sub_index profile_area">
  <div class="inner">
    <?php require VIEW_PATH . '/components/mp-user-area.php'; ?>
    <div class="sub_page_flex">
      <?php require VIEW_PATH . '/components/mp-subnav.php'; ?>
      <div class="sub_page_contents">
        <div class="page-section-title mgb12"><?= $isEdit ? '후기 수정' : '후기 작성' ?></div>
        <span class="page-section-title-desc">유료 결제 강의에 한해 작성 가능</span>

        <form id="review-form" method="POST" action="/mypage/reviews/write" enctype="multipart/form-data">
          <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken) ?>">
          <input type="hidden" name="class_idx" value="<?= (int)($classIdx ?? $class['class_idx']) ?>">
          <?php if ($isEdit): ?>
          <input type="hidden" name="edit_idx" value="<?= (int)$editIdx ?>">
          <?php endif; ?>

          <div class="apply-section mgt24">
            <div class="form-field">
              <label class="form-label">별점<span class="req">*</span></label>
              <div class="form-field-wrap">
                <div id="star-wrap" class="rv-star-picker">
                  <?php for ($i = 1; $i <= 5; $i++): ?>
                  <button type="button" class="rv-star-btn" data-val="<?= $i ?>">
                    <img src="/assets/img/<?= $i <= $oldRating ? 'star_on' : 'star_off' ?>.svg"
                         alt="<?= $i ?>점" class="rv-star-img" data-val="<?= $i ?>">
                  </button>
                  <?php endfor; ?>
                </div>
                <input type="hidden" name="rating" id="rating-input" value="<?= $oldRating ?>">
                <div data-ajax-err="rating" class="field-error" style="display:none;"></div>
              </div>
            </div>

            <div class="form-field">
              <label class="form-label">제목<span class="req">*</span></label>
              <div class="form-field-wrap">
                <input type="text" name="title" class="form-input <?= isset($errors['title']) ? 'error' : '' ?>"
                       placeholder="제목을 입력해주세요" value="<?= $oldTitle ?>" maxlength="200">
                <div data-ajax-err="title" class="field-error" style="display:none;"></div>
              </div>
            </div>

            <div class="form-field ver2">
              <label class="form-label">내용<span class="req">*</span></label>
              <div class="form-field-wrap">
                <div class="limit_text">
                  <textarea name="content" id="review-content" class="form-input form-textarea" placeholder="강의에 대한 솔직한 후기를 작성해 주세요 (최소 20자 이상)"
                        oninput="updateCount(this)"><?= htmlspecialchars($oldContent) ?></textarea>
                  <p class="limit_curr_text"><span id="content-count">0</span></p>
                </div>
                <div class="field-error" style="display:none;"></div>
              </div>
            </div>

            <div class="form-field ver2">
              <label class="form-label">
                이미지 첨부
                <span class="form-label-desc">jpg,png,webp · 각 5MB 이하</span>
              </label>
              <div class="form-field-wrap">                
                <div class="img-flex-area">                  
                  <!-- 파일 선택 버튼 -->
                  <label id="img-add-btn" class="img-add-box">
                    <p><img src="/assets/img/icon_camera.svg"></p>
                    <p class="img-add-text">사진 추가</p>
                    <span id="img-count-label"><?= $existingCount ?>/<?= $maxImages ?></span>
                    <input type="file" id="review-images-input" name="review_images[]" accept="image/jpeg,image/png,image/webp" multiple style="display:none">
                  </label>       

                  <!-- 기존 이미지 (수정 모드) -->
                  <?php if ($reviewImages): ?>
                  <div id="existing-images" class="new-preview">
                    <?php foreach ($reviewImages as $img): ?>
                    <div class="existing-img-item img-add-box" data-path="<?= htmlspecialchars($img['image_path']) ?>">
                      <img src="/uploads/review/<?= htmlspecialchars($img['image_path']) ?>">
                      <button type="button" onclick="removeExisting(this)" class="img-add-delete"></button>
                      <input type="hidden" name="delete_images[]" value="<?= htmlspecialchars($img['image_path']) ?>" disabled>
                    </div>
                    <?php endforeach; ?>
                  </div>
                  <?php endif; ?>
                  
                  <!-- 새 이미지 미리보기 -->
                  <div id="new-preview" class="new-preview"></div>
                </div>
                <div data-ajax-err="images" class="field-error" style="display:none;"></div>
              </div>
            </div>
          </div>

          <div class="profile_btn_box">
            <button type="submit" class="btn-next btn-save-profile"><?= $isEdit ? '수정하기' : '등록하기' ?></button>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>

<script>
(function () {
  const MAX = <?= $maxImages ?>;
  let existingKept = <?= $existingCount ?>;
  let newFiles = [];

  // 기존 이미지 삭제
  window.removeExisting = function (btn) {
    const item = btn.closest('.existing-img-item');
    item.querySelector('input[type=hidden]').disabled = false;
    item.style.opacity = '0.35';
    btn.disabled = true;
    existingKept--;
    updateCountLabel();
    updateAddBtnVisibility();
  };

  // 새 파일 선택
  const fileInput = document.getElementById('review-images-input');
  fileInput.addEventListener('change', function () {
    const selected = Array.from(this.files);
    const available = MAX - existingKept - newFiles.length;
    const toAdd = selected.slice(0, available);
    this.value = ''; // syncFileInput() 호출 전에 먼저 클리어
    toAdd.forEach(addPreview);
    updateCountLabel();
    updateAddBtnVisibility();
  });

  function addPreview(file) {
    newFiles.push(file);
    const reader = new FileReader();
    reader.onload = function (e) {
      const idx = newFiles.indexOf(file);
      const div = document.createElement('div');
      div.dataset.idx = idx;
      div.classList = 'img-add-box';
      //div.style.cssText = 'position:relative;width:90px;height:90px;border-radius:8px;overflow:hidden;border:1px solid #ddd';
      div.innerHTML = `<img src="${e.target.result}">
        <button type="button" onclick="removeNew(this)" class="img-add-delete"></button>`;
      document.getElementById('new-preview').appendChild(div);
    };
    reader.readAsDataURL(file);
    syncFileInput();
  }

  window.removeNew = function (btn) {
    const div = btn.closest('[data-idx]');
    const idx = parseInt(div.dataset.idx);
    newFiles[idx] = null;
    div.remove();
    updateCountLabel();
    updateAddBtnVisibility();
    syncFileInput();
  };

  function syncFileInput() {
    const dt = new DataTransfer();
    newFiles.filter(Boolean).forEach(f => dt.items.add(f));
    fileInput.files = dt.files;
  }

  function updateCountLabel() {
    const total = existingKept + newFiles.filter(Boolean).length;
    document.getElementById('img-count-label').textContent = total + '/' + MAX;
  }

  function updateAddBtnVisibility() {
    const total = existingKept + newFiles.filter(Boolean).length;
    document.getElementById('img-add-btn').style.display = total >= MAX ? 'none' : 'inline-flex';
  }

  updateAddBtnVisibility();
}());

document.getElementById('review-form').addEventListener('submit', function (e) {
  e.preventDefault();
  const textarea = document.getElementById('review-content');
  const content  = textarea.value.trim();
  const errorEl  = textarea.closest('.form-field-wrap').querySelector('.field-error');

  if (content.length < 20) {
    errorEl.textContent = '후기 내용은 20자 이상 입력해주세요.';
    errorEl.style.display = 'block';
    textarea.style.borderColor = '#c0392b';
    textarea.focus();
    return;
  }
  errorEl.textContent = '';
  errorEl.style.display = 'none';
  textarea.style.borderColor = '#ddd';
  ajaxSubmit(this);
});

function renderStars(val) {
  document.querySelectorAll('.rv-star-img').forEach(function(img) {
    const iv = parseInt(img.dataset.val);
    img.src = '/assets/img/' + (iv <= val ? 'star_on' : 'star_off') + '.svg';
  });
}

function setRating(val) {
  document.getElementById('rating-input').value = val;
  renderStars(val);
}

// 별점 클릭 / hover
document.querySelectorAll('.rv-star-btn').forEach(function(btn) {
  btn.addEventListener('click', function() {
    setRating(parseInt(this.dataset.val));
  });
  btn.addEventListener('mouseenter', function() {
    renderStars(parseInt(this.dataset.val));
  });
  btn.addEventListener('mouseleave', function() {
    renderStars(parseInt(document.getElementById('rating-input').value));
  });
});

function updateCount(el) {
  document.getElementById('content-count').textContent = el.value.length + '자';
  if (el.value.trim().length >= 20) {
    var errEl = el.closest('.form-field-wrap').querySelector('.field-error');
    if (errEl) { errEl.textContent = ''; errEl.style.display = 'none'; }
    el.style.borderColor = '';
  }
}

// 수정 모드 초기 카운트 반영
updateCount(document.getElementById('review-content'));
</script>

  </div><!-- /.mp-content -->
</div><!-- /.mp-wrap -->
