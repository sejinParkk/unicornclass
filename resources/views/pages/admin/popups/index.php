<?php
/**
 * 관리자 팝업 목록
 * @var list<array> $popups
 * @var int         $total
 * @var int         $page
 * @var int         $totalPages
 * @var string      $csrfToken
 */
?>

<?php if (isset($_GET['deleted'])): ?>
<div class="toast-msg toast-success">✓ 팝업이 삭제되었습니다.</div>
<?php endif; ?>

<div class="top-bar">
    <div class="total-label">전체 <strong><?= number_format($total) ?></strong>개</div>
    <a href="/admin/popups/create" class="btn-create">+ 팝업 등록</a>
</div>

<!-- 안내 문구 -->
<div style="margin-bottom:16px;padding:12px 16px;background:#fef9e7;border:1px solid #f9e79f;border-radius:6px;font-size:12px;color:#7d6608;line-height:1.7;">
    <strong>팝업 노출 규칙</strong><br>
    · 노출 기간 내 + 활성 상태인 팝업이 메인 페이지에 슬라이더 형태로 표시됩니다.<br>
    · 정렬 순서가 낮을수록 슬라이더 앞에 배치됩니다.<br>
    · 방문자가 "오늘 하루 보지 않기" 체크 시 24시간 동안 팝업이 표시되지 않습니다.
</div>

<div class="tbl-wrap">
    <table class="data-table">
        <colgroup>
            <col width="5%">
            <col width="120px">
            <col width="">
            <col width="8%">
            <col width="10%">
            <col width="10%">
            <col width="6%">
            <col width="6%">
            <col width="10%">
        </colgroup>
        <thead>
            <tr>
                <th>NO</th>
                <th>이미지</th>
                <th>링크 URL</th>
                <th>열기</th>
                <th>시작일</th>
                <th>종료일</th>
                <th>순서</th>
                <th>상태</th>
                <th>관리</th>
            </tr>
        </thead>
        <tbody>
        <?php if (empty($popups)): ?>
            <tr class="empty-row"><td colspan="9">등록된 팝업이 없습니다.</td></tr>
        <?php else: ?>
            <?php foreach ($popups as $i => $p): ?>
            <tr>
                <td><?= $total - ($page - 1) * 20 - $i ?></td>
                <td>
                    <?php if (!empty($p['image_path'])): ?>
                    <img src="/uploads/popup/<?= htmlspecialchars($p['image_path']) ?>"
                         alt="팝업 이미지"
                         style="width:80px;height:60px;object-fit:cover;border-radius:4px;border:1px solid #eee;">
                    <?php else: ?>
                    <span style="color:#bbb;font-size:11px;">이미지 없음</span>
                    <?php endif; ?>
                </td>
                <td style="max-width:260px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">
                    <?php if (!empty($p['link_url'])): ?>
                    <a href="<?= htmlspecialchars($p['link_url']) ?>" target="_blank"
                       style="color:#1a3a5c;font-size:12px;">
                        <?= htmlspecialchars($p['link_url']) ?>
                    </a>
                    <?php else: ?>
                    <span style="color:#bbb;font-size:12px;">—</span>
                    <?php endif; ?>
                </td>
                <td style="font-size:12px;color:#555;">
                    <?= $p['link_target'] === '_blank' ? '새 탭' : '현재 탭' ?>
                </td>
                <td style="font-size:12px;">
                    <?= $p['start_date'] ? htmlspecialchars($p['start_date']) : '<span style="color:#bbb">제한없음</span>' ?>
                </td>
                <td style="font-size:12px;">
                    <?= $p['end_date'] ? htmlspecialchars($p['end_date']) : '<span style="color:#bbb">제한없음</span>' ?>
                </td>
                <td style="font-size:12px;"><?= (int)$p['sort_order'] ?></td>
                <td>
                    <span class="badge badge-<?= $p['is_active'] ? 'active' : 'inactive' ?>">
                        <?= $p['is_active'] ? '활성' : '비활성' ?>
                    </span>
                </td>
                <td>
                    <div class="act-btn-wrap">
                        <a href="/admin/popups/<?= $p['popup_idx'] ?>/edit" class="act-btn act-edit">수정</a>
                        <form method="POST" action="/admin/popups/<?= $p['popup_idx'] ?>/delete"
                              onsubmit="return confirm('팝업을 삭제하시겠습니까?\n이미지 파일도 함께 삭제됩니다.')">
                            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken) ?>">
                            <button type="submit" class="act-btn act-del">삭제</button>
                        </form>
                    </div>
                </td>
            </tr>
            <?php endforeach; ?>
        <?php endif; ?>
        </tbody>
    </table>
</div>

<?php if ($totalPages > 1): ?>
<div class="pagination">
    <?php for ($i = 1; $i <= $totalPages; $i++): ?>
        <?php if ($i === $page): ?>
        <span class="active"><?= $i ?></span>
        <?php else: ?>
        <a href="/admin/popups?page=<?= $i ?>"><?= $i ?></a>
        <?php endif; ?>
    <?php endfor; ?>
</div>
<?php endif; ?>
