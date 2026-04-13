<?php
// $faqs, $categories, $category 가 컨트롤러에서 전달됨
$currentCat = $category ?: 'all';
?>
<style>
.cs-banner{position:relative;height:140px;background:linear-gradient(135deg,#1a1a1a 0%,#2d2d2d 55%,#3a1a1a 100%);display:flex;align-items:center;overflow:hidden}
.cs-banner::before{content:'';position:absolute;inset:0;background:repeating-linear-gradient(-45deg,transparent,transparent 28px,rgba(192,57,43,.04) 28px,rgba(192,57,43,.04) 29px)}
.cs-banner::after{content:'';position:absolute;right:-50px;top:-50px;width:260px;height:260px;border-radius:50%;background:radial-gradient(circle,rgba(192,57,43,.18) 0%,transparent 70%)}
.cs-banner-inner{position:relative;z-index:1;padding:0 48px}
.cs-banner-en{font-size:10px;font-weight:500;letter-spacing:3px;color:#c0392b;margin-bottom:8px}
.cs-banner-title{font-size:26px;font-weight:700;color:#fff;letter-spacing:-.5px}
.cs-banner-title span{color:#c0392b}
.cs-subnav{background:#fff;border-bottom:1px solid #eee;display:flex;padding:0 48px}
.cs-subnav-item{position:relative;padding:0 20px;height:48px;line-height:48px;font-size:13.5px;font-weight:500;color:#888;cursor:pointer;border-bottom:2px solid transparent;transition:color .2s,border-color .2s;white-space:nowrap;text-decoration:none;display:block}
.cs-subnav-item:hover,.cs-subnav-item.active{color:#c0392b;border-bottom-color:#c0392b;font-weight:700}
.faq-wrap{max-width:900px;margin:0 auto;padding:32px 48px 60px}
.page-section-title{font-size:17px;font-weight:700;color:#1a1a1a;margin-bottom:20px}
.faq-filters{display:flex;gap:6px;flex-wrap:wrap;margin-bottom:24px}
.faq-filter-btn{padding:5px 14px;border-radius:20px;border:1px solid #ccc;background:#fff;font-size:12px;font-weight:500;color:#888;cursor:pointer;font-family:inherit;transition:all .15s;text-decoration:none}
.faq-filter-btn.active,.faq-filter-btn:hover{background:#c0392b;border-color:#c0392b;color:#fff}
.faq-list{border-top:2px solid #222}
.faq-item{border-bottom:1px solid #eee;overflow:hidden}
.faq-q{display:flex;align-items:center;gap:12px;padding:18px 0;cursor:pointer}
.faq-q:hover .faq-q-text{color:#c0392b}
.faq-q-badge{flex-shrink:0;width:22px;height:22px;border-radius:50%;background:#c0392b;color:#fff;font-size:10px;font-weight:700;display:flex;align-items:center;justify-content:center}
.faq-q-text{flex:1;font-size:13.5px;font-weight:500;color:#222;line-height:1.5}
.faq-item.open .faq-q-text{color:#c0392b}
.faq-toggle{flex-shrink:0;width:22px;height:22px;border:1px solid #ccc;border-radius:50%;display:flex;align-items:center;justify-content:center;transition:background .2s,border-color .2s}
.faq-item.open .faq-toggle{background:#c0392b;border-color:#c0392b}
.faq-toggle svg{transition:transform .3s}
.faq-item.open .faq-toggle svg{transform:rotate(45deg)}
.faq-a-wrap{max-height:0;overflow:hidden;transition:max-height .35s cubic-bezier(.4,0,.2,1)}
.faq-item.open .faq-a-wrap{max-height:400px}
.faq-a{display:flex;gap:12px;padding:0 0 20px}
.faq-a-badge{flex-shrink:0;width:22px;height:22px;border-radius:50%;border:1.5px solid #c0392b;color:#c0392b;font-size:10px;font-weight:700;display:flex;align-items:center;justify-content:center}
.faq-a-text{font-size:13px;color:#555;line-height:1.8}
.faq-a-text strong{color:#c0392b}
.faq-empty{padding:48px 0;text-align:center;color:#aaa;font-size:14px}
.faq-cta{background:#1a3a5c;border-radius:10px;padding:24px 28px;display:flex;align-items:center;justify-content:space-between;margin-top:40px;gap:16px}
.faq-cta-text h4{font-size:15px;font-weight:700;color:#fff;margin-bottom:6px}
.faq-cta-text p{font-size:12px;color:rgba(255,255,255,.6)}
.faq-cta-btn{flex-shrink:0;height:40px;padding:0 20px;background:#c0392b;border-radius:8px;color:#fff;font-size:13px;font-weight:700;border:none;cursor:pointer;font-family:inherit;white-space:nowrap;text-decoration:none;display:inline-flex;align-items:center}
</style>

<!-- 서브 배너 -->
<div class="cs-banner">
  <div class="cs-banner-inner">
    <div class="cs-banner-en">Customer Support</div>
    <div class="cs-banner-title">고객<span>센터</span></div>
  </div>
</div>

<!-- 서브 메뉴 -->
<div class="cs-subnav">
  <a href="/supports/faqs"    class="cs-subnav-item active">자주묻는질문</a>
  <a href="/supports/notices" class="cs-subnav-item">공지사항</a>
  <a href="/supports/terms"   class="cs-subnav-item">이용약관</a>
  <a href="/supports/privacy" class="cs-subnav-item">개인정보처리방침</a>
</div>

<div class="faq-wrap">
  <div class="page-section-title">자주 묻는 질문</div>

  <!-- 카테고리 필터 -->
  <div class="faq-filters">
    <?php foreach ($categories as $key => $label): ?>
    <a href="/supports/faqs?category=<?= htmlspecialchars($key) ?>"
       class="faq-filter-btn <?= $currentCat === $key ? 'active' : '' ?>">
      <?= htmlspecialchars($label) ?>
    </a>
    <?php endforeach; ?>
  </div>

  <!-- FAQ 목록 -->
  <div class="faq-list">
    <?php if (empty($faqs)): ?>
    <div class="faq-empty">등록된 FAQ가 없습니다.</div>
    <?php else: ?>
    <?php foreach ($faqs as $faq): ?>
    <div class="faq-item" data-faq-idx="<?= $faq['faq_idx'] ?>">
      <div class="faq-q" onclick="toggleFaq(this)">
        <span class="faq-q-badge">Q</span>
        <span class="faq-q-text"><?= htmlspecialchars($faq['question']) ?></span>
        <span class="faq-toggle">
          <svg width="10" height="10" viewBox="0 0 10 10" fill="none" stroke="#aaa" stroke-width="2">
            <line x1="5" y1="1" x2="5" y2="9"/>
            <line x1="1" y1="5" x2="9" y2="5"/>
          </svg>
        </span>
      </div>
      <div class="faq-a-wrap">
        <div class="faq-a">
          <span class="faq-a-badge">A</span>
          <p class="faq-a-text"><?= nl2br(htmlspecialchars($faq['answer'])) ?></p>
        </div>
      </div>
    </div>
    <?php endforeach; ?>
    <?php endif; ?>
  </div>

  <!-- 1:1 문의 유도 배너 -->
  <div class="faq-cta">
    <div class="faq-cta-text">
      <h4>원하시는 답변을 찾지 못하셨나요?</h4>
      <p>1:1 문의를 남겨주시면 빠르게 답변드리겠습니다.</p>
    </div>
    <a href="/mypage/qna" class="faq-cta-btn">1:1 문의하기</a>
  </div>
</div>

<script>
function toggleFaq(qEl) {
  const item = qEl.closest('.faq-item');
  const isOpen = item.classList.contains('open');
  // 모두 닫기
  document.querySelectorAll('.faq-item.open').forEach(el => el.classList.remove('open'));
  if (!isOpen) item.classList.add('open');
}
// URL 파라미터에 open 항목 자동 열기 지원 (선택)
</script>
