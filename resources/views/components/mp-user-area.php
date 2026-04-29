<?php
use App\Core\Auth;
$_mpSession    = Auth::member();
$_mpAvatarChar = mb_substr($_mpSession['mb_name'] ?? '?', 0, 1);
?>
<div class="mp-user-area">
  <div class="mp-user-avatar"><?= htmlspecialchars($_mpAvatarChar) ?></div>
  <div class="mp-user-info">
    <div class="mp-user-name"><?= htmlspecialchars($_mpSession['mb_name'] ?? '') ?></div>
    <div class="mp-user-id"><?= htmlspecialchars($_mpSession['mb_id'] ?? '') ?></div>
  </div>
</div>
