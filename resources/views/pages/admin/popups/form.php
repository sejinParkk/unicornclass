<?php
/**
 * 관리자 팝업 등록/수정 폼
 * @var array|null $popup     null이면 등록, 배열이면 수정
 * @var string     $csrfToken
 */
$isEdit = $popup !== null;
$action = $isEdit
    ? '/admin/popups/' . $popup['popup_idx']
    : '/admin/popups';
$h = fn(string $k, string $d = '') => htmlspecialchars($popup[$k] ?? $d);
?>

<?php if (isset($_GET['saved'])): ?>
<div class="toast-msg toast-success">✓ 저장되었습니다.</div>
<?php endif; ?>
<?php if (isset($_GET['error'])): ?>
<div class="toast-msg toast-error"><?= htmlspecialchars($_GET['error']) ?></div>
<?php endif; ?>

<form method="POST" action="<?= $action ?>" enctype="multipart/form-data" id="popupForm">
    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken) ?>">

    <div class="form-card">

        <!-- 이미지 업로드 -->
        <div class="form-group">
            <label>팝업 이미지 <span style="color:#888;font-weight:400;font-size:12px;">jpg·png·webp, 최대 5MB (권장 380×380px)</span></label>
            <?php if ($isEdit && !empty($popup['image_path'])): ?>
            <div style="margin-bottom:10px;">
                <img id="preview-img"
                     src="/uploads/popup/<?= htmlspecialchars($popup['image_path']) ?>"
                     alt="현재 팝업 이미지"
                     style="max-width:380px;max-height:220px;object-fit:cover;border-radius:6px;border:1px solid #eee;display:block;">
                <div style="font-size:11px;color:#aaa;margin-top:4px;">현재 이미지 — 새 파일 선택 시 교체됩니다.</div>
            </div>
            <?php else: ?>
            <div style="margin-bottom:10px;">
                <img id="preview-img" src="" alt=""
                     style="display:none;max-width:380px;max-height:220px;object-fit:cover;border-radius:6px;border:1px solid #eee;">
            </div>
            <?php endif; ?>
            <input type="file" name="image" id="imageInput" accept="image/jpeg,image/png,image/webp"
                   class="form-control" onchange="previewImage(this)">
        </div>

        <!-- 링크 URL -->
        <div class="form-group">
            <label>링크 URL <span style="color:#888;font-weight:400;font-size:12px;">이미지 클릭 시 이동 (없으면 빈칸)</span></label>
            <input type="url" name="link_url" class="form-control"
                   value="<?= $h('link_url') ?>" placeholder="https://...">
        </div>

        <!-- 링크 열기 방식 -->
        <div class="form-group">
            <label>링크 열기 방식</label>
            <div class="radio-row">
                <label class="radio-item">
                    <input type="radio" name="link_target" value="_blank"
                           <?= ($popup['link_target'] ?? '_blank') === '_blank' ? 'checked' : '' ?>>
                    새 탭으로 열기
                </label>
                <label class="radio-item">
                    <input type="radio" name="link_target" value="_self"
                           <?= ($popup['link_target'] ?? '') === '_self' ? 'checked' : '' ?>>
                    현재 탭에서 열기
                </label>
            </div>
        </div>

        <!-- 노출 기간 -->
        <div class="form-group">
            <label>노출 기간 <span style="color:#888;font-weight:400;font-size:12px;">빈칸이면 제한없이 노출</span></label>
            <div style="display:flex;align-items:center;gap:10px;flex-wrap:wrap;">
                <input type="date" name="start_date" class="form-control" style="width:160px;"
                       value="<?= $h('start_date') ?>">
                <span style="color:#aaa;">~</span>
                <input type="date" name="end_date" class="form-control" style="width:160px;"
                       value="<?= $h('end_date') ?>">
            </div>
        </div>

        <!-- 정렬 순서 -->
        <div class="form-group">
            <label>슬라이더 순서 <span style="color:#888;font-weight:400;font-size:12px;">숫자가 낮을수록 팝업 슬라이더 앞에 표시</span></label>
            <input type="number" name="sort_order" class="form-control" style="width:120px;"
                   value="<?= (int)($popup['sort_order'] ?? 0) ?>" min="0">
        </div>

        <!-- 노출 여부 -->
        <div class="form-group">
            <label>노출 여부</label>
            <div class="radio-row">
                <label class="radio-item">
                    <input type="radio" name="is_active" value="1"
                           <?= ($popup['is_active'] ?? 1) ? 'checked' : '' ?>>
                    활성 (노출)
                </label>
                <label class="radio-item">
                    <input type="radio" name="is_active" value="0"
                           <?= isset($popup) && !$popup['is_active'] ? 'checked' : '' ?>>
                    비활성 (숨김)
                </label>
            </div>
        </div>

    </div><!-- /.form-card -->

    <div class="form-actions">
        <a href="/admin/popups" class="btn-back">목록</a>
        <button type="submit" class="btn-save">저장</button>
        <?php if ($isEdit): ?>
        <button type="button" class="btn-delete"
                onclick="if(confirm('팝업을 삭제하시겠습니까?\n이미지 파일도 함께 삭제됩니다.')) document.getElementById('deletePopupForm').submit()">
            삭제
        </button>
        <?php endif; ?>
    </div>
</form>

<?php if ($isEdit): ?>
<form id="deletePopupForm" method="POST"
      action="/admin/popups/<?= $popup['popup_idx'] ?>/delete" style="display:none">
    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken) ?>">
</form>
<?php endif; ?>

<script>
function previewImage(input) {
    const img = document.getElementById('preview-img');
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = e => {
            img.src = e.target.result;
            img.style.display = 'block';
        };
        reader.readAsDataURL(input.files[0]);
    }
}
</script>
