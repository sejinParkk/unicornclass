<?php
// $class 가 컨트롤러에서 전달됨
?>
<div style="max-width:900px;margin:60px auto;padding:0 32px;text-align:center">
  <h2 style="font-size:20px;font-weight:700;margin-bottom:16px;color:#111"><?= htmlspecialchars($class['title']) ?></h2>
  <p style="color:#888;font-size:14px;margin-bottom:32px">영상 재생 화면은 준비 중입니다.</p>
  <a href="/classes/<?= $class['class_idx'] ?>"
     style="display:inline-block;background:#c0392b;color:#fff;padding:12px 28px;border-radius:6px;font-size:14px;font-weight:600;text-decoration:none">← 강의 상세로 돌아가기</a>
</div>
