<?php
$_csUri   = strtok($_SERVER['REQUEST_URI'], '?');
$_csItems = [
    '/supports/faqs'    => 'FAQ',
    '/supports/notices' => '공지사항',
    '/supports/terms'   => '이용약관',
    '/supports/privacy' => '개인정보처리방침',
];
?>
<div class="cs-subnav">
  <?php $i = 0; foreach ($_csItems as $_csPath => $_csLabel): $i++; ?>
  <a href="<?= $_csPath ?>" class="cs-subnav-item <?= str_starts_with($_csUri, $_csPath) ? 'active' : '' ?>">
    <img src="/assets/img/cs_subnav_<?php echo $i?>_<?= str_starts_with($_csUri, $_csPath) ? 'on' : 'off' ?>.svg" alt="">
    <span><?= $_csLabel ?></span>
  </a>
  <?php endforeach; ?>
</div>
