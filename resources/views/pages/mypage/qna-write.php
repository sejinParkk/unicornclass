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

<div class="mp-content-title"><?= $isEdit ? '문의 수정' : '문의 작성' ?></div>

<div class="qna-form-wrap">
  <div class="qna-form-title">
    <?= $isEdit ? '문의 내용을 수정해주세요.' : '궁금하신 내용을 남겨주시면 빠르게 답변드리겠습니다.' ?>
  </div>

  <form method="POST" action="/mypage/qna/write">
    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken) ?>">
    <?php if ($isEdit): ?>
    <input type="hidden" name="edit_idx" value="<?= $editIdx ?>">
    <?php endif; ?>

    <!-- 문의 분류 -->
    <div class="mp-form-field">
      <label class="mp-form-label">
        문의 분류 <span class="req">*</span>
      </label>
      <select name="category" class="mp-form-select <?= isset($errors['category']) ? 'error' : '' ?>">
        <option value="">분류를 선택해주세요</option>
        <?php foreach ($catOptions as $val => $label): ?>
        <option value="<?= $val ?>" <?= $old['category'] === $val ? 'selected' : '' ?>>
          <?= htmlspecialchars($label) ?>
        </option>
        <?php endforeach; ?>
      </select>
      <?php if (isset($errors['category'])): ?>
        <div style="font-size:11px;color:#c0392b;margin-top:4px"><?= htmlspecialchars($errors['category']) ?></div>
      <?php endif; ?>
    </div>

    <!-- 제목 -->
    <div class="mp-form-field">
      <label class="mp-form-label">
        제목 <span class="req">*</span>
      </label>
      <input type="text" name="title" class="mp-form-input <?= isset($errors['title']) ? 'error' : '' ?>"
             placeholder="문의 제목을 입력해주세요"
             value="<?= htmlspecialchars($old['title']) ?>"
             maxlength="200">
      <?php if (isset($errors['title'])): ?>
        <div style="font-size:11px;color:#c0392b;margin-top:4px"><?= htmlspecialchars($errors['title']) ?></div>
      <?php endif; ?>
    </div>

    <!-- 내용 -->
    <div class="mp-form-field">
      <label class="mp-form-label">
        내용 <span class="req">*</span>
      </label>
      <textarea name="content" class="mp-form-textarea <?= isset($errors['content']) ? 'error' : '' ?>"
                placeholder="문의 내용을 자세히 입력해주세요."><?= htmlspecialchars($old['content']) ?></textarea>
      <?php if (isset($errors['content'])): ?>
        <div style="font-size:11px;color:#c0392b;margin-top:4px"><?= htmlspecialchars($errors['content']) ?></div>
      <?php endif; ?>
    </div>

    <div style="display:flex;justify-content:flex-end;gap:8px;margin-top:8px">
      <a href="<?= $isEdit ? '/mypage/qna/' . $editIdx : '/mypage/qna' ?>"
         class="btn-qna-cancel">취소</a>
      <button type="submit" class="btn-qna-submit">
        <?= $isEdit ? '수정 완료' : '문의 접수' ?>
      </button>
    </div>
  </form>
</div>
