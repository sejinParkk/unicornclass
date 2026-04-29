<?php
/**
 * 팝업형 약관 (팝업 전용 — 내부 페이지 없음)
 * @var array|null $term         현재 버전
 * @var string     $policyTitle  약관 한글명
 */
?>
<div class="policy-wrap">
  <?php if ($term && !empty($term['content'])): ?>
  <div class="policy-meta">시행일: <?= date('Y년 m월 d일', strtotime($term['effective_at'])) ?></div>
  <div class="policy-body">
    <?= $term['content'] ?>
  </div>
  <?php else: ?>
  <div class="policy-empty"><?= htmlspecialchars($policyTitle) ?> 내용이 아직 등록되지 않았습니다.</div>
  <?php endif; ?>
</div>
