<?php
/**
 * 관리자 약관 관리 — 유형별 현재 버전 요약
 * @var array  $summary    type => {terms_idx, title, effective_at, updated_at, version_count}
 * @var array  $termTypes  type => 한글명
 * @var string $csrfToken
 */
?>

<?php if (isset($_GET['error'])): ?>
<div class="toast-msg toast-error"><?= htmlspecialchars($_GET['error']) ?></div>
<?php endif; ?>

<div class="top-bar">
  <div class="total-label">약관 유형 <strong><?= count($termTypes) ?></strong>종</div>
</div>

<div class="tbl-wrap">
  <table class="data-table">
    <colgroup>
      <col width="18%">
      <col width="">
      <col width="10%">
      <col width="12%">
      <col width="14%">
      <col width="14%">
    </colgroup>
    <thead>
      <tr>
        <th>약관 유형</th>
        <th>현재 버전 제목</th>
        <th>버전 수</th>
        <th>시행일</th>
        <th>최종 수정일</th>
        <th>관리</th>
      </tr>
    </thead>
    <tbody>
    <?php foreach ($termTypes as $type => $label):
      $row = $summary[$type] ?? null;
    ?>
      <tr>
        <td><span class="badge badge-default"><?= htmlspecialchars($label) ?></span></td>
        <td><?= $row ? htmlspecialchars($row['title']) : '<span style="color:#aaa">미등록</span>' ?></td>
        <td style="text-align:center"><?= $row ? number_format((int)$row['version_count']) : 0 ?></td>
        <td><?= $row ? date('Y.m.d', strtotime($row['effective_at'])) : '-' ?></td>
        <td><?= $row ? date('Y.m.d', strtotime($row['updated_at'] ?? $row['effective_at'])) : '-' ?></td>
        <td>
          <div class="act-btn-wrap">
            <a href="/admin/terms/<?= $type ?>/versions" class="act-btn act-edit">버전 관리</a>
            <a href="/admin/terms/<?= $type ?>/create" class="act-btn act-create">새 버전</a>
          </div>
        </td>
      </tr>
    <?php endforeach; ?>
    </tbody>
  </table>
</div>
