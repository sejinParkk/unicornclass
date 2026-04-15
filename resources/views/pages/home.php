<?php
// 컨트롤러에서 전달:
// $freeClasses, $premiumClasses, $instructors, $reviews
// $banners, $popups, $heroVideo, $kakaoChannelUrl

function maskName(string $name): string
{
    $len = mb_strlen($name);
    if ($len <= 1) return $name;
    if ($len === 2) return mb_substr($name, 0, 1) . '*';
    return mb_substr($name, 0, 1) . str_repeat('*', $len - 2) . mb_substr($name, -1, 1);
}

function starRating(int $rating): string
{
    return str_repeat('★', $rating) . str_repeat('☆', 5 - $rating);
}
?>

<main>

<!-- ====================================================
  ① 히어로 배너
===================================================== -->
<section id="hero">
  <?php if (!empty($heroVideo)): ?>
  <video class="hero-video" id="heroVideo" autoplay muted loop playsinline preload="auto"
         src="/uploads/site/<?= htmlspecialchars($heroVideo) ?>"></video>
  <?php endif; ?>
  <?php if (!empty($heroPoster)): ?>
  <div class="hero-poster" id="heroPoster">
    <img src="/uploads/site/<?= htmlspecialchars($heroPoster) ?>" alt="">
  </div>
  <?php endif; ?>
  <div class="hero-overlay"></div>
  <div class="hero-content">
    <div class="hero-title">유니콘클래스</div>
    <div class="hero-sub">당신의 인생을 바꾸는 No.1 수익화 플랫폼</div>
    <a href="/classes" class="hero-cta">강의 둘러보기 →</a>
  </div>
</section>

<!-- ====================================================
  ② 무료강의 슬라이더
===================================================== -->
<?php if (!empty($freeClasses)): ?>
<section class="home-section" id="section-free">
  <div class="home-section-header">
    <span class="home-section-icon">🐦</span>
    <span class="home-section-title">꿀팁 대방출, 무료강의</span>
    <span class="home-section-en">Webinar</span>
    <a href="/classes?type=free" class="home-section-more">더보기 →</a>
  </div>
  <div class="swiper home-class-swiper" id="swiper-free">
    <div class="swiper-wrapper">
      <?php foreach ($freeClasses as $c): ?>
      <div class="swiper-slide">
        <a href="/classes/<?= (int)$c['class_idx'] ?>" class="home-cl-card">
          <div class="card-thumb">
            <?php if (!empty($c['thumbnail'])): ?>
            <img src="/uploads/class/<?= htmlspecialchars($c['thumbnail']) ?>"
                 alt="<?= htmlspecialchars($c['title']) ?>">
            <?php else: ?>
            <div class="card-thumb-ph"></div>
            <?php endif; ?>
          </div>
          <div class="card-info">
            <div class="card-tags">
              <?php if ($c['badge_hot']): ?><span class="ctag ct-hot">HOT</span><?php endif; ?>
              <?php if ($c['badge_new']): ?><span class="ctag ct-new">NEW</span><?php endif; ?>
              <span class="ctag ct-free">무료</span>
            </div>
            <div class="card-title"><?= htmlspecialchars($c['title']) ?></div>
            <div class="card-meta">
              <?= htmlspecialchars($c['instructor_name']) ?>
              <?= !empty($c['category_name']) ? ' · ' . htmlspecialchars($c['category_name']) : '' ?>
            </div>
          </div>
        </a>
      </div>
      <?php endforeach; ?>
    </div>
    <div class="swiper-pagination"></div>
    <div class="swiper-button-prev"></div>
    <div class="swiper-button-next"></div>
  </div>
</section>
<?php endif; ?>

<!-- ====================================================
  ③ 프리미엄강의 슬라이더
===================================================== -->
<?php if (!empty($premiumClasses)): ?>
<section class="home-section" id="section-premium">
  <div class="home-section-header">
    <span class="home-section-icon">🎁</span>
    <span class="home-section-title">프리미엄 강의</span>
    <span class="home-section-en">Premium Class</span>
    <a href="/classes?type=premium" class="home-section-more">더보기 →</a>
  </div>
  <div class="swiper home-class-swiper" id="swiper-premium">
    <div class="swiper-wrapper">
      <?php foreach ($premiumClasses as $c): ?>
      <div class="swiper-slide">
        <a href="/classes/<?= (int)$c['class_idx'] ?>" class="home-cl-card">
          <div class="card-thumb">
            <?php if (!empty($c['thumbnail'])): ?>
            <img src="/uploads/class/<?= htmlspecialchars($c['thumbnail']) ?>"
                 alt="<?= htmlspecialchars($c['title']) ?>">
            <?php else: ?>
            <div class="card-thumb-ph"></div>
            <?php endif; ?>
          </div>
          <div class="card-info">
            <div class="card-tags">
              <?php if ($c['badge_hot']): ?><span class="ctag ct-hot">HOT</span><?php endif; ?>
              <?php if ($c['badge_new']): ?><span class="ctag ct-new">NEW</span><?php endif; ?>
              <span class="ctag ct-premium">프리미엄</span>
            </div>
            <div class="card-title"><?= htmlspecialchars($c['title']) ?></div>
            <div class="card-meta">
              <?= htmlspecialchars($c['instructor_name']) ?>
              <?= !empty($c['category_name']) ? ' · ' . htmlspecialchars($c['category_name']) : '' ?>
              <?php if ($c['price'] > 0): ?>
              &nbsp;|&nbsp; <?= number_format((int)$c['price']) ?>원
              <?php endif; ?>
            </div>
          </div>
        </a>
      </div>
      <?php endforeach; ?>
    </div>
    <div class="swiper-pagination"></div>
    <div class="swiper-button-prev"></div>
    <div class="swiper-button-next"></div>
  </div>
</section>
<?php endif; ?>

<!-- ====================================================
  ④ 이벤트/공지 배너 슬라이더
===================================================== -->
<?php if (!empty($banners)): ?>
<section class="home-banner-section" id="section-banner">
  <div class="swiper home-banner-swiper" id="swiper-banner">
    <div class="swiper-wrapper">
      <?php foreach ($banners as $b): ?>
      <div class="swiper-slide home-banner-slide">
        <?php if (!empty($b['image_path'])): ?>
          <?php if (!empty($b['link_url'])): ?>
          <a href="<?= htmlspecialchars($b['link_url']) ?>"
             target="<?= htmlspecialchars($b['link_target']) ?>">
            <img src="/uploads/banner/<?= htmlspecialchars($b['image_path']) ?>"
                 alt="<?= htmlspecialchars($b['alt_text']) ?>">
          </a>
          <?php else: ?>
          <img src="/uploads/banner/<?= htmlspecialchars($b['image_path']) ?>"
               alt="<?= htmlspecialchars($b['alt_text']) ?>">
          <?php endif; ?>
        <?php else: ?>
        <div class="home-banner-ph">이벤트 / 공지 배너</div>
        <?php endif; ?>
      </div>
      <?php endforeach; ?>
    </div>
    <?php if (count($banners) > 1): ?>
    <div class="swiper-pagination"></div>
    <div class="swiper-button-prev"></div>
    <div class="swiper-button-next"></div>
    <?php endif; ?>
  </div>
</section>
<?php endif; ?>

<!-- ====================================================
  ⑤ 강사진 슬라이더
===================================================== -->
<?php if (!empty($instructors)): ?>
<section class="home-section home-section--gray" id="section-inst">
  <div class="home-section-header">
    <span class="home-section-icon">🏅</span>
    <span class="home-section-title">압도적인 수익화 전문가, 유니콘 강사진</span>
    <span class="home-section-en">Unicorn Teachers</span>
    <a href="/instructors" class="home-section-more">더보기 →</a>
  </div>
  <div class="swiper home-inst-swiper" id="swiper-inst">
    <div class="swiper-wrapper">
      <?php foreach ($instructors as $ins): ?>
      <div class="swiper-slide">
        <a href="/instructors/<?= (int)$ins['instructor_idx'] ?>" class="home-inst-card">
          <div class="i-photo-wrap">
            <?php if (!empty($ins['photo'])): ?>
            <img src="/uploads/instructor/<?= htmlspecialchars($ins['photo']) ?>"
                 alt="<?= htmlspecialchars($ins['name']) ?>" class="i-photo-img">
            <?php else: ?>
            <div class="i-photo-ph">
              <div class="person-icon">👤</div>
            </div>
            <?php endif; ?>
          </div>
          <div class="i-info">
            <div class="i-name"><?= htmlspecialchars($ins['name']) ?></div>
            <?php if (!empty($ins['intro'])): ?>
            <ul class="i-desc-list">
              <li><?= htmlspecialchars($ins['intro']) ?></li>
            </ul>
            <?php endif; ?>
            <div class="i-social-fixed">
              <?php if (!empty($ins['sns_youtube'])): ?>
              <div class="i-social-icon" title="유튜브">▶</div>
              <?php endif; ?>
              <?php if (!empty($ins['sns_instagram'])): ?>
              <div class="i-social-icon" title="인스타그램">📷</div>
              <?php endif; ?>
              <?php if (!empty($ins['sns_facebook'])): ?>
              <div class="i-social-icon" title="페이스북">f</div>
              <?php endif; ?>
            </div>
          </div>
        </a>
      </div>
      <?php endforeach; ?>
    </div>
    <div class="swiper-pagination"></div>
    <div class="swiper-button-prev"></div>
    <div class="swiper-button-next"></div>
  </div>
</section>
<?php endif; ?>

<!-- ====================================================
  ⑥ 수강생 후기
===================================================== -->
<?php if (!empty($reviews)): ?>
<section class="home-section" id="section-reviews">
  <div class="home-section-header">
    <span class="home-section-icon">⭐</span>
    <span class="home-section-title">수강생 후기</span>
    <span class="home-section-en">Reviews</span>
  </div>
  <div class="home-review-grid">
    <?php foreach ($reviews as $rv): ?>
    <button type="button" class="home-review-card"
            data-name="<?= htmlspecialchars(maskName($rv['member_name'])) ?>"
            data-rating="<?= (int)$rv['rating'] ?>"
            data-title="<?= htmlspecialchars($rv['class_title']) ?>"
            data-content="<?= htmlspecialchars($rv['content'] ?? '') ?>"
            data-date="<?= htmlspecialchars(substr($rv['created_at'], 0, 10)) ?>">
      <div class="rv-head">
        <div class="rv-avatar"><?= htmlspecialchars(mb_substr(maskName($rv['member_name']), 0, 1)) ?></div>
        <div>
          <div class="rv-name"><?= htmlspecialchars(maskName($rv['member_name'])) ?></div>
          <div class="rv-stars"><?= starRating((int)$rv['rating']) ?></div>
        </div>
        <div class="rv-date"><?= htmlspecialchars(substr($rv['created_at'], 0, 10)) ?></div>
      </div>
      <div class="rv-class-title"><?= htmlspecialchars($rv['class_title']) ?></div>
      <div class="rv-content"><?= htmlspecialchars($rv['content'] ?? '') ?></div>
      <div class="rv-more">클릭하여 전체 보기 →</div>
    </button>
    <?php endforeach; ?>
  </div>
</section>
<?php endif; ?>

<!-- ====================================================
  ⑦ 메인 팝업
===================================================== -->
<?php if (!empty($popups)): ?>
<div id="popup-overlay" class="popup-overlay" role="dialog" aria-modal="true">
  <div id="popup-modal" class="popup-modal">
    <button class="popup-close" onclick="homePopup.close()" aria-label="닫기">✕</button>
    <div class="popup-slider">
      <div class="popup-slider-track" id="popup-track">
        <?php foreach ($popups as $p): ?>
        <div class="popup-slide">
          <?php if (!empty($p['image_path'])): ?>
            <?php if (!empty($p['link_url'])): ?>
            <a href="<?= htmlspecialchars($p['link_url']) ?>"
               target="<?= htmlspecialchars($p['link_target']) ?>">
              <img src="/uploads/popup/<?= htmlspecialchars($p['image_path']) ?>" alt="팝업 이미지">
            </a>
            <?php else: ?>
            <img src="/uploads/popup/<?= htmlspecialchars($p['image_path']) ?>" alt="팝업 이미지">
            <?php endif; ?>
          <?php else: ?>
          <div class="popup-img-ph">
            <div class="pi-icon">🎉</div>
            <div class="pi-text">유니콘클래스</div>
          </div>
          <?php endif; ?>
        </div>
        <?php endforeach; ?>
      </div>
      <?php if (count($popups) > 1): ?>
      <button class="popup-sl-btn prev" onclick="homePopup.slide(-1)">‹</button>
      <button class="popup-sl-btn next" onclick="homePopup.slide(1)">›</button>
      <div class="popup-dots" id="popup-dots">
        <?php foreach ($popups as $i => $_): ?>
        <span class="popup-dot <?= $i === 0 ? 'active' : '' ?>"
              onclick="homePopup.goTo(<?= $i ?>)"></span>
        <?php endforeach; ?>
      </div>
      <?php endif; ?>
    </div>
    <div class="popup-footer">
      <label class="popup-today">
        <input type="checkbox" id="popup-today-chk"> 오늘 하루 보지 않기
      </label>
      <button class="popup-close-text" onclick="homePopup.close()">닫기</button>
    </div>
  </div>
</div>
<?php endif; ?>

<!-- ====================================================
  ⑧ 카카오 플로팅 버튼
===================================================== -->
<?php if (!empty($kakaoChannelUrl)): ?>
<div id="kakao-float">
  <a href="<?= htmlspecialchars($kakaoChannelUrl) ?>" target="_blank"
     rel="noopener" class="kakao-float-btn" title="카카오 채널 문의">
    <svg viewBox="0 0 24 24" fill="#3c1e1e" xmlns="http://www.w3.org/2000/svg">
      <path d="M12 3C6.477 3 2 6.692 2 11.25c0 2.9 1.696 5.454 4.25 6.98L5.18 21.07a.5.5 0 00.72.555l4.23-2.47c.61.08 1.23.12 1.87.12 5.523 0 10-3.692 10-8.25C22 6.692 17.523 3 12 3z"/>
    </svg>
  </a>
  <div class="kakao-float-label">카카오 채널</div>
</div>
<?php endif; ?>

<!-- 후기 상세 모달 -->
<div id="review-modal" class="review-modal-overlay" style="display:none"
     onclick="if(event.target===this)this.style.display='none'">
  <div class="review-modal-box">
    <button class="review-modal-close"
            onclick="document.getElementById('review-modal').style.display='none'">✕</button>
    <div class="review-modal-head">
      <div class="rv-avatar" id="rm-avatar"></div>
      <div>
        <div class="rv-name"  id="rm-name"></div>
        <div class="rv-stars" id="rm-stars"></div>
      </div>
      <div class="rv-date" id="rm-date"></div>
    </div>
    <div class="review-modal-class"   id="rm-title"></div>
    <div class="review-modal-content" id="rm-content"></div>
  </div>
</div>

</main>

<!-- Swiper JS -->
<script src="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js"></script>
<script>const HOME_POPUPS_COUNT = <?= count($popups ?? []) ?>;</script>
<script src="/assets/js/home.js"></script>
