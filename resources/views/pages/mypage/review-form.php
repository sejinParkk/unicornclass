<?php
/**
 * 후기 작성 / 수정
 * 변수: $review (array|null), $class (array), $csrfToken, $errors
 * $editIdx, $classIdx
 */

$isEdit  = ($editIdx ?? 0) > 0;
$oldRating  = (int) ($_POST['rating']  ?? ($review['rating']  ?? 5));
$oldContent = $_POST['content'] ?? ($review['content'] ?? '');
?>

<div class="mp-content-title">
  <?= $isEdit ? '후기 수정' : '후기 작성' ?>
  <span>유료 결제 강의에 한해 작성 가능</span>
</div>

<div style="background:#fff;border:1px solid #eee;border-radius:10px;padding:24px">

  <!-- 강의명 -->
  <div style="font-size:13px;font-weight:700;color:#333;margin-bottom:18px;padding-bottom:10px;border-bottom:2px solid #f0f0f0">
    <?= $isEdit ? '후기 수정' : '후기 작성' ?> —
    <span style="color:#c0392b"><?= htmlspecialchars($class['title']) ?></span>
  </div>

  <form method="POST" action="/mypage/reviews/write">
    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken) ?>">
    <input type="hidden" name="class_idx" value="<?= (int)($classIdx ?? $class['class_idx']) ?>">
    <?php if ($isEdit): ?>
    <input type="hidden" name="edit_idx" value="<?= (int)$editIdx ?>">
    <?php endif; ?>

    <!-- 별점 -->
    <div style="margin-bottom:18px">
      <div style="font-size:12px;color:#555;font-weight:600;margin-bottom:8px">
        별점 <span style="color:#c0392b">*</span>
      </div>
      <div style="display:flex;align-items:center;gap:8px">
        <div id="star-wrap" style="display:flex;gap:2px">
          <?php for ($i = 1; $i <= 5; $i++): ?>
          <span class="star-btn"
                data-val="<?= $i ?>"
                style="font-size:30px;cursor:pointer;color:<?= $i <= $oldRating ? '#f39c12' : '#ddd' ?>;transition:color .1s;line-height:1"
                onclick="setRating(<?= $i ?>)">★</span>
          <?php endfor; ?>
        </div>
        <span id="rating-label" style="font-size:12px;color:#888"><?= $oldRating ?>점 / 5점</span>
      </div>
      <input type="hidden" name="rating" id="rating-input" value="<?= $oldRating ?>">
      <?php if (isset($errors['rating'])): ?>
        <div style="font-size:11px;color:#c0392b;margin-top:4px"><?= htmlspecialchars($errors['rating']) ?></div>
      <?php endif; ?>
    </div>

    <!-- 내용 -->
    <div style="margin-bottom:18px">
      <div style="font-size:12px;color:#555;font-weight:600;margin-bottom:6px">
        내용 <span style="color:#c0392b">*</span>
        <span style="font-size:11px;color:#aaa;font-weight:400;margin-left:4px">최소 20자 이상</span>
      </div>
      <textarea name="content" rows="6"
                style="width:100%;border:1px solid <?= isset($errors['content']) ? '#c0392b' : '#ddd' ?>;border-radius:8px;padding:12px 14px;font-size:13px;font-family:inherit;resize:vertical;box-sizing:border-box;outline:none;color:#333"
                placeholder="강의에 대한 솔직한 후기를 작성해 주세요 (최소 20자 이상)"
                oninput="updateCount(this)"><?= htmlspecialchars($oldContent) ?></textarea>
      <div style="display:flex;justify-content:space-between;margin-top:4px">
        <?php if (isset($errors['content'])): ?>
          <span style="font-size:11px;color:#c0392b"><?= htmlspecialchars($errors['content']) ?></span>
        <?php else: ?>
          <span></span>
        <?php endif; ?>
        <span id="content-count" style="font-size:11px;color:#aaa"><?= mb_strlen($oldContent) ?>자</span>
      </div>
    </div>

    <!-- 버튼 -->
    <div style="display:flex;gap:8px;margin-top:4px">
      <a href="/mypage/reviews"
         style="flex:1;height:44px;background:#e0e0e0;color:#888;border-radius:8px;font-size:13px;font-weight:700;border:none;text-decoration:none;display:flex;align-items:center;justify-content:center">
        목록으로
      </a>
      <button type="submit"
              style="flex:2;height:44px;background:#c0392b;color:#fff;border-radius:8px;font-size:14px;font-weight:800;border:none;cursor:pointer">
        <?= $isEdit ? '수정 완료' : '후기 등록하기' ?>
      </button>
    </div>
  </form>

</div>

<script>
function setRating(val) {
  document.getElementById('rating-input').value = val;
  document.getElementById('rating-label').textContent = val + '점 / 5점';
  document.querySelectorAll('.star-btn').forEach(function(s) {
    s.style.color = parseInt(s.dataset.val) <= val ? '#f39c12' : '#ddd';
  });
}

function updateCount(el) {
  document.getElementById('content-count').textContent = el.value.length + '자';
}

// 별점 hover 효과
document.querySelectorAll('.star-btn').forEach(function(s) {
  s.addEventListener('mouseenter', function() {
    const val = parseInt(this.dataset.val);
    document.querySelectorAll('.star-btn').forEach(function(ss) {
      ss.style.color = parseInt(ss.dataset.val) <= val ? '#f8c940' : '#ddd';
    });
  });
  s.addEventListener('mouseleave', function() {
    const cur = parseInt(document.getElementById('rating-input').value);
    document.querySelectorAll('.star-btn').forEach(function(ss) {
      ss.style.color = parseInt(ss.dataset.val) <= cur ? '#f39c12' : '#ddd';
    });
  });
});
</script>
