<?php
/**
 * 관리자 약관 버전 목록
 * @var string $type
 * @var string $typeName
 * @var array  $versions
 * @var string $csrfToken
 */
?>

<?php if (isset($_GET['saved'])): ?>
<div class="toast-msg toast-success">✓ 저장되었습니다.</div>
<?php endif; ?>
<?php if (isset($_GET['current'])): ?>
<div class="toast-msg toast-success">✓ 현재 버전으로 변경되었습니다.</div>
<?php endif; ?>
<?php if (isset($_GET['deleted'])): ?>
<div class="toast-msg toast-success">✓ 버전이 삭제되었습니다.</div>
<?php endif; ?>
<?php if (isset($_GET['error'])): ?>
<div class="toast-msg toast-error"><?= htmlspecialchars($_GET['error']) ?></div>
<?php endif; ?>

<div class="top-bar">
  <div class="total-label">
    <a href="/admin/terms" style="color:#888;text-decoration:none">약관 관리</a>
    &nbsp;›&nbsp; <?= htmlspecialchars($typeName) ?>
    &nbsp;— 전체 <strong><?= count($versions) ?></strong>개 버전
  </div>
  <a href="/admin/terms/<?= htmlspecialchars($type) ?>/create" class="btn-create">+ 새 버전 등록</a>
</div>

<div class="tbl-wrap">
  <table class="data-table">
    <colgroup>
      <col width="5%">
      <col width="">
      <col width="12%">
      <col width="10%">
      <col width="12%">
      <col width="18%">
    </colgroup>
    <thead>
      <tr>
        <th>NO</th>
        <th>제목</th>
        <th>시행일</th>
        <th>현재 버전</th>
        <th>최종 수정일</th>
        <th>관리</th>
      </tr>
    </thead>
    <tbody>
    <?php if (empty($versions)): ?>
      <tr class="empty-row"><td colspan="6">등록된 버전이 없습니다.</td></tr>
    <?php else: ?>
    <?php foreach ($versions as $i => $v): ?>
      <tr>
        <td><?= count($versions) - $i ?></td>
        <td style="font-weight:<?= $v['is_current'] ? '600' : '400' ?>">
          <?= htmlspecialchars($v['title']) ?>
        </td>
        <td><?= date('Y.m.d', strtotime($v['effective_at'])) ?></td>
        <td>
          <?php if ($v['is_current']): ?>
            <span class="badge badge-active">현재</span>
          <?php else: ?>
            <span class="badge badge-inactive">이전</span>
          <?php endif; ?>
        </td>
        <td><?= $v['updated_at'] ? date('Y.m.d', strtotime($v['updated_at'])) : '-' ?></td>
        <td>
          <div class="act-btn-wrap">
            <a href="/admin/terms/v/<?= $v['terms_idx'] ?>/edit" class="act-btn act-edit">수정</a>
            <?php if (!$v['is_current']): ?>
            <form method="POST" action="/admin/terms/v/<?= $v['terms_idx'] ?>/current"
                  onsubmit="return confirm('이 버전을 현재 버전으로 설정하시겠습니까?')">
              <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken) ?>">
              <button type="submit" class="act-btn" style="background:#ebf8ff;color:#2b6cb0;border-color:#bee3f8">현재로</button>
            </form>
            <form method="POST" action="/admin/terms/v/<?= $v['terms_idx'] ?>/delete"
                  onsubmit="return confirm('이 버전을 삭제하시겠습니까?')">
              <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken) ?>">
              <button type="submit" class="act-btn act-del">삭제</button>
            </form>
            <?php endif; ?>
          </div>
        </td>
      </tr>
    <?php endforeach; ?>
    <?php endif; ?>
    </tbody>
  </table>
</div>
