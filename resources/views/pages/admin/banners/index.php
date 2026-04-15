<?php
/**
 * 관리자 이벤트 배너 목록
 * @var list<array> $banners
 * @var int         $total
 * @var int         $page
 * @var int         $totalPages
 * @var string      $csrfToken
 */
?>

<?php if (isset($_GET['deleted'])): ?>
<div class="toast-msg toast-success">✓ 배너가 삭제되었습니다.</div>
<?php endif; ?>

<div class="top-bar">
    <div class="total-label">전체 <strong><?= number_format($total) ?></strong>개</div>
    <a href="/admin/banners/create" class="btn-create">+ 배너 등록</a>
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
        <?php if (empty($banners)): ?>
            <tr class="empty-row"><td colspan="9">등록된 배너가 없습니다.</td></tr>
        <?php else: ?>
            <?php foreach ($banners as $i => $b): ?>
            <tr>
                <td><?= $total - ($page - 1) * 20 - $i ?></td>
                <td>
                    <?php if (!empty($b['image_path'])): ?>
                    <img src="/uploads/banner/<?= htmlspecialchars($b['image_path']) ?>"
                         alt="배너 이미지"
                         style="width:100px;height:40px;object-fit:cover;border-radius:4px;border:1px solid #eee;">
                    <?php else: ?>
                    <span style="color:#bbb;font-size:11px;">이미지 없음</span>
                    <?php endif; ?>
                </td>
                <td style="max-width:260px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">
                    <?php if (!empty($b['link_url'])): ?>
                    <a href="<?= htmlspecialchars($b['link_url']) ?>" target="_blank"
                       style="color:#1a3a5c;font-size:12px;">
                        <?= htmlspecialchars($b['link_url']) ?>
                    </a>
                    <?php else: ?>
                    <span style="color:#bbb;font-size:12px;">—</span>
                    <?php endif; ?>
                </td>
                <td style="font-size:12px;color:#555;">
                    <?= $b['link_target'] === '_blank' ? '새 탭' : '현재 탭' ?>
                </td>
                <td style="font-size:12px;">
                    <?= $b['start_date'] ? htmlspecialchars($b['start_date']) : '<span style="color:#bbb">제한없음</span>' ?>
                </td>
                <td style="font-size:12px;">
                    <?= $b['end_date'] ? htmlspecialchars($b['end_date']) : '<span style="color:#bbb">제한없음</span>' ?>
                </td>
                <td style="font-size:12px;"><?= (int)$b['sort_order'] ?></td>
                <td>
                    <span class="badge badge-<?= $b['is_active'] ? 'active' : 'inactive' ?>">
                        <?= $b['is_active'] ? '활성' : '비활성' ?>
                    </span>
                </td>
                <td>
                    <div class="act-btn-wrap">
                        <a href="/admin/banners/<?= $b['banner_idx'] ?>/edit" class="act-btn act-edit">수정</a>
                        <form method="POST" action="/admin/banners/<?= $b['banner_idx'] ?>/delete"
                              onsubmit="return confirm('배너를 삭제하시겠습니까?\n이미지 파일도 함께 삭제됩니다.')">
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
        <a href="/admin/banners?page=<?= $i ?>"><?= $i ?></a>
        <?php endif; ?>
    <?php endfor; ?>
</div>
<?php endif; ?>
