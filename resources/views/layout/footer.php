<?php
use App\Core\DB;

$_fc = [];
$_fcRows = DB::select(
	"SELECT config_key, config_value FROM lc_site_config
	WHERE config_key IN ('company_name','ceo_name','business_no','address','phone','email','footer_copy','sns_instagram','sns_youtube','sns_blog','sns_facebook','logo')"
);
foreach ($_fcRows as $_r) {
  $_fc[$_r['config_key']] = $_r['config_value'];
}

$_siteName2 = DB::selectOne("SELECT config_value FROM lc_site_config WHERE config_key = 'site_name'");
$_footerSiteName = $_siteName2['config_value'] ?? '유니콘클래스';
?>

</main>
<!-- ====== FOOTER ====== -->
<footer class="site-footer">
	<div class="footer-inner">
		<div class="footer-top">
			<!-- 로고 -->
			<div class="footer-logo">
				<?php if (!empty($_fc['logo'])): ?>
				<img src="/uploads/site/<?= htmlspecialchars($_fc['logo']) ?>" alt="<?= htmlspecialchars($_footerSiteName) ?>" style="height:28px;filter:brightness(0) invert(1);opacity:.7">
				<?php else: ?>
				<div class="footer-logo-icon"><span>UNICORN<br>CLASS</span></div>
				<?php endif; ?>
			</div>

			<!-- 사업자 정보 -->
			<div class="footer-info">
				<?php if (!empty($_fc['company_name'])): ?>
				<span><strong><?= htmlspecialchars($_fc['company_name']) ?></strong></span>
				<?php endif; ?>
				<?php if (!empty($_fc['ceo_name'])): ?>
				<span class="sep">|</span><span>대표 <?= htmlspecialchars($_fc['ceo_name']) ?></span>
				<?php endif; ?>
				<?php if (!empty($_fc['business_no'])): ?>
				<span class="sep">|</span><span>사업자등록번호 <?= htmlspecialchars($_fc['business_no']) ?></span>
				<?php endif; ?>
				<br>
				<?php if (!empty($_fc['address'])): ?>
				<span><?= htmlspecialchars($_fc['address']) ?></span>
				<?php endif; ?>
				<?php if (!empty($_fc['phone'])): ?>
				<span class="sep">|</span><span>TEL <?= htmlspecialchars($_fc['phone']) ?></span>
				<?php endif; ?>
				<?php if (!empty($_fc['email'])): ?>
				<span class="sep">|</span><span><?= htmlspecialchars($_fc['email']) ?></span>
				<?php endif; ?>
			</div>

			<!-- SNS -->
			<?php
			$snsList = [
					'sns_instagram' => ['label' => 'Instagram', 'svg' => '<svg width="14" height="14" viewBox="0 0 24 24" fill="currentColor"><path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zm0-2.163c-3.259 0-3.667.014-4.947.072-4.358.2-6.78 2.618-6.98 6.98-.059 1.281-.073 1.689-.073 4.948 0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98 1.281.058 1.689.072 4.948.072 3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98-1.281-.059-1.69-.073-4.949-.073zm0 5.838c-3.403 0-6.162 2.759-6.162 6.162s2.759 6.163 6.162 6.163 6.162-2.759 6.162-6.163c0-3.403-2.759-6.162-6.162-6.162zm0 10.162c-2.209 0-4-1.79-4-4 0-2.209 1.791-4 4-4s4 1.791 4 4c0 2.21-1.791 4-4 4zm6.406-11.845c-.796 0-1.441.645-1.441 1.44s.645 1.44 1.441 1.44c.795 0 1.439-.645 1.439-1.44s-.644-1.44-1.439-1.44z"/></svg>'],
					'sns_youtube'   => ['label' => 'YouTube',   'svg' => '<svg width="14" height="14" viewBox="0 0 24 24" fill="currentColor"><path d="M23.495 6.205a3.007 3.007 0 0 0-2.088-2.088c-1.87-.501-9.396-.501-9.396-.501s-7.507-.01-9.396.501A3.007 3.007 0 0 0 .527 6.205a31.247 31.247 0 0 0-.522 5.805 31.247 31.247 0 0 0 .522 5.783 3.007 3.007 0 0 0 2.088 2.088c1.868.502 9.396.502 9.396.502s7.506 0 9.396-.502a3.007 3.007 0 0 0 2.088-2.088 31.247 31.247 0 0 0 .5-5.783 31.247 31.247 0 0 0-.5-5.805zM9.609 15.601V8.408l6.264 3.602z"/></svg>'],
					'sns_blog'      => ['label' => 'Blog',      'svg' => '<svg width="14" height="14" viewBox="0 0 24 24" fill="currentColor"><path d="M3 3h18v18H3V3zm2 2v14h14V5H5zm2 3h10v2H7V8zm0 4h10v2H7v-2zm0 4h7v2H7v-2z"/></svg>'],
					'sns_facebook'  => ['label' => 'Facebook',  'svg' => '<svg width="14" height="14" viewBox="0 0 24 24" fill="currentColor"><path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/></svg>'],
			];
			$hasSns = false;
			foreach ($snsList as $key => $_) {
					if (!empty($_fc[$key])) { $hasSns = true; break; }
			}
			if ($hasSns): ?>
			<div class="footer-sns">
				<?php foreach ($snsList as $key => $meta): ?>
				<?php if (!empty($_fc[$key])): ?>
				<a href="<?= htmlspecialchars($_fc[$key]) ?>" target="_blank" rel="noopener" title="<?= $meta['label'] ?>"><?= $meta['svg'] ?></a>
				<?php endif; ?>
				<?php endforeach; ?>
			</div>
			<?php endif; ?>
		</div>

		<hr class="footer-divider">

		<div class="footer-bottom">
			<p class="footer-copy"><?= htmlspecialchars($_fc['footer_copy'] ?? "© {$_footerSiteName}. All rights reserved.") ?></p>
			<div class="footer-links">
				<a href="/supports/terms">이용약관</a>
				<a href="/supports/privacy">개인정보처리방침</a>
			</div>
		</div>
	</div>
</footer>

</body>
</html>
