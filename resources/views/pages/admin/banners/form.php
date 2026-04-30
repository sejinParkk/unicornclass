<?php
/**
 * 관리자 이벤트 배너 등록/수정 폼
 * @var array|null $banner    null이면 등록, 배열이면 수정
 * @var string     $csrfToken
 */
$isEdit = $banner !== null;
$action = $isEdit
    ? '/admin/banners/' . $banner['banner_idx']
    : '/admin/banners';
$h = fn(string $k, string $d = '') => htmlspecialchars($banner[$k] ?? $d);
?>

<?php if (isset($_GET['saved'])): ?>
<div class="toast-msg toast-success">✓ 저장되었습니다.</div>
<?php endif; ?>
<?php if (isset($_GET['error'])): ?>
<div class="toast-msg toast-error"><?= htmlspecialchars($_GET['error']) ?></div>
<?php endif; ?>

<form method="POST" action="<?= $action ?>" enctype="multipart/form-data" id="bannerForm">
    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken) ?>">

    <div class="form-card">

        <!-- PC 이미지 업로드 -->
        <div class="form-group">
            <label>PC 배너 이미지 <span style="color:#888;font-weight:400;font-size:12px;">jpg·png·webp, 최대 5MB (권장 1800×260px)</span></label>
            <?php if ($isEdit && !empty($banner['image_path'])): ?>
            <div style="margin-bottom:10px;">
                <img id="preview-img"
                     src="/uploads/banner/<?= htmlspecialchars($banner['image_path']) ?>"
                     alt="현재 PC 배너 이미지"
                     style="max-width:100%;max-height:120px;object-fit:cover;border-radius:6px;border:1px solid #eee;">
                <div style="font-size:11px;color:#aaa;margin-top:4px;">현재 이미지 — 새 파일 선택 시 교체됩니다.</div>
            </div>
            <?php else: ?>
            <div style="margin-bottom:10px;">
                <img id="preview-img" src="" alt=""
                     style="display:none;max-width:100%;max-height:120px;object-fit:cover;border-radius:6px;border:1px solid #eee;">
            </div>
            <?php endif; ?>
            <input type="file" name="image" id="imageInput" accept="image/jpeg,image/png,image/webp"
                   class="form-control" onchange="previewImage(this, 'preview-img')">
        </div>

        <!-- 모바일 이미지 업로드 -->
        <div class="form-group">
            <label>모바일 배너 이미지 <span style="color:#888;font-weight:400;font-size:12px;">jpg·png·webp, 최대 5MB (권장 750×400px) — 없으면 PC 이미지로 대체</span></label>
            <?php if ($isEdit && !empty($banner['mobile_image_path'])): ?>
            <div style="margin-bottom:10px;">
                <img id="preview-mobile-img"
                     src="/uploads/banner/<?= htmlspecialchars($banner['mobile_image_path']) ?>"
                     alt="현재 모바일 배너 이미지"
                     style="max-width:100%;max-height:160px;object-fit:cover;border-radius:6px;border:1px solid #eee;">
                <div style="font-size:11px;color:#aaa;margin-top:4px;">현재 모바일 이미지 — 새 파일 선택 시 교체됩니다.</div>
            </div>
            <?php else: ?>
            <div style="margin-bottom:10px;">
                <img id="preview-mobile-img" src="" alt=""
                     style="display:none;max-width:100%;max-height:160px;object-fit:cover;border-radius:6px;border:1px solid #eee;">
            </div>
            <?php endif; ?>
            <input type="file" name="mobile_image" id="mobileImageInput" accept="image/jpeg,image/png,image/webp"
                   class="form-control" onchange="previewImage(this, 'preview-mobile-img')">
        </div>

        <!-- 링크 URL -->
        <div class="form-group">
            <label>링크 URL <span style="color:#888;font-weight:400;font-size:12px;">클릭 시 이동 (없으면 빈칸)</span></label>
            <input type="url" name="link_url" class="form-control"
                   value="<?= $h('link_url') ?>" placeholder="https://...">
        </div>

        <!-- 링크 열기 방식 -->
        <div class="form-group">
            <label>링크 열기 방식</label>
            <div class="radio-row">
                <label class="radio-item">
                    <input type="radio" name="link_target" value="_blank"
                           <?= ($banner['link_target'] ?? '_blank') === '_blank' ? 'checked' : '' ?>>
                    새 탭으로 열기
                </label>
                <label class="radio-item">
                    <input type="radio" name="link_target" value="_self"
                           <?= ($banner['link_target'] ?? '') === '_self' ? 'checked' : '' ?>>
                    현재 탭에서 열기
                </label>
            </div>
        </div>

        <!-- 대체 텍스트 -->
        <div class="form-group">
            <label>이미지 대체 텍스트 <span style="color:#888;font-weight:400;font-size:12px;">스크린리더·SEO용</span></label>
            <input type="text" name="alt_text" class="form-control"
                   value="<?= $h('alt_text') ?>" placeholder="예: 런칭 기념 30% 할인 이벤트">
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
            <label>정렬 순서 <span style="color:#888;font-weight:400;font-size:12px;">숫자가 낮을수록 앞에 표시</span></label>
            <input type="number" name="sort_order" class="form-control" style="width:120px;"
                   value="<?= (int)($banner['sort_order'] ?? 0) ?>" min="0">
        </div>

        <!-- 노출 여부 -->
        <div class="form-group">
            <label>노출 여부</label>
            <div class="radio-row">
                <label class="radio-item">
                    <input type="radio" name="is_active" value="1"
                           <?= ($banner['is_active'] ?? 1) ? 'checked' : '' ?>>
                    활성 (노출)
                </label>
                <label class="radio-item">
                    <input type="radio" name="is_active" value="0"
                           <?= isset($banner) && !$banner['is_active'] ? 'checked' : '' ?>>
                    비활성 (숨김)
                </label>
            </div>
        </div>

    </div><!-- /.form-card -->

    <div class="form-actions">
        <a href="/admin/banners" class="btn-back">목록</a>
        <button type="submit" class="btn-save">저장</button>
        <?php if ($isEdit): ?>
        <button type="button" class="btn-delete"
                onclick="if(confirm('배너를 삭제하시겠습니까?\n이미지 파일도 함께 삭제됩니다.')) document.getElementById('deleteBannerForm').submit()">
            삭제
        </button>
        <?php endif; ?>
    </div>
</form>

<?php if ($isEdit): ?>
<form id="deleteBannerForm" method="POST"
      action="/admin/banners/<?= $banner['banner_idx'] ?>/delete" style="display:none">
    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken) ?>">
</form>
<?php endif; ?>

<script>
function previewImage(input, previewId) {
    const img = document.getElementById(previewId);
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = e => {
            img.src = e.target.result;
            img.style.display = 'block';
        };
        reader.readAsDataURL(input.files[0]);
    }
}

document.getElementById('bannerForm').addEventListener('submit', function(e) {
    e.preventDefault();
    ajaxSubmit(this);
});
</script>
