<?php
// 컨트롤러에서 전달:
// $freeClasses, $premiumClasses, $instructors, $reviews
// $banners, $popups, $heroVideo, $kakaoChannelUrl

$isHome = true;

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

<!-- 인트로 스플래시 -->
<div id="site-intro">
  <img src="/assets/img/logo2.svg" alt="유니콘클래스">
</div>

<!-- ====================================================
  ① 히어로 배너
===================================================== -->
<section id="hero">
  <div class="hero_inner">
    <?php if (!empty($heroVideo)): ?>
    <video class="hero-video" id="heroVideo" autoplay muted loop playsinline preload="auto" src="/uploads/site/<?= htmlspecialchars($heroVideo) ?>"></video>
    <?php endif; ?>
    <div class="hero-overlay"></div>
    <div class="hero-content">
      <p class="hero-cont-star"><img src="/assets/img/hero_star.svg" alt=""></p>
      <p class="hero-cont-txt">당신의 인생을 바꾸는<br>No.1 수익화 플랫폼<br><span>유니콘 클래스</span></p>
    </div>
  </div>
</section>

<!-- ====================================================
  ② 무료강의 슬라이더
===================================================== -->
<?php if (!empty($freeClasses)): ?>
<section class="home-section" id="section-free">
  <div class="home-section-header">
    <div class="home_inner">
      <p class="home-section-title">
        <img src="/assets/img/hero_star.svg" alt="">
        <strong>BEST 무료 강의</strong>
        <img src="/assets/img/icon_fire.svg" alt="">
      </p>
      <p class="home-section-en">Webinar</p>    
    </div>
  </div>
  <div class="home_swiper_area">
    <div class="home_swp_controll">
      <div class="swiper-button-prev"></div>
      <div class="swiper-pagination"></div>      
      <div class="swiper-button-next"></div>
    </div>
    <div class="swiper home-class-swiper" id="swiper-free">
      <div class="swiper-wrapper">
        <?php foreach ($freeClasses as $c): ?>
        <div class="swiper-slide">
          <a href="/classes/<?= (int)$c['class_idx'] ?>" class="home-cl-card">
            <div class="card-thumb">
              <?php if (!empty($c['thumbnail'])): ?>
              <img src="/uploads/class/<?= htmlspecialchars($c['thumbnail']) ?>" alt="<?= htmlspecialchars($c['title']) ?>">
              <?php else: ?>
              <div class="card-thumb-ph"><img src="/assets/img/logo.svg"></div>
              <?php endif; ?>
              <p class="ctag ct-free">
                <img src="/assets/img/hero_star_fff.svg" alt="">
                <span>무료강의</span>
              </p>
            </div>
            <div class="card-info">
              <div class="card-tags">
                <?php if ($c['badge_hot']): ?><span class="ctags ct-hot">HOT</span><?php endif; ?>
                <?php if ($c['badge_new']): ?><span class="ctags ct-new">NEW</span><?php endif; ?>
                
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
    </div>
  </div>
  <div class="home-section-more-area">
    <div class="home_inner">
      <a href="/classes?type=free" class="home-section-more">
        <span>무료 강의 더보기</span>
        <img src="/assets/img/more_arr.svg" alt="">
      </a>
    </div>
  </div>
</section>
<?php endif; ?>

<!-- ====================================================
  ③ 프리미엄강의 슬라이더
===================================================== -->
<?php if (!empty($premiumClasses)): ?>
<section class="home-section" id="section-premium">
  <div class="home-section-header">
    <div class="home_inner">
      <p class="home-section-title">
        <img src="/assets/img/hero_star.svg" alt="">
        <strong>프리미엄 강의</strong>
        <img src="/assets/img/icon_goldmedal.svg" alt="">
      </p>
      <p class="home-section-en">Premium Class</p>    
    </div>
  </div>
  <div class="home_swiper_area">
    <div class="home_swp_controll">
      <div class="swiper-button-prev"></div>
      <div class="swiper-pagination"></div>      
      <div class="swiper-button-next"></div>
    </div>
    <div class="swiper home-class-swiper" id="swiper-premium">
      <div class="swiper-wrapper">
        <?php foreach ($premiumClasses as $c): ?>
        <div class="swiper-slide">
          <a href="/classes/<?= (int)$c['class_idx'] ?>" class="home-cl-card">
            <div class="card-thumb">
              <?php if (!empty($c['thumbnail'])): ?>
              <img src="/uploads/class/<?= htmlspecialchars($c['thumbnail']) ?>" alt="<?= htmlspecialchars($c['title']) ?>">              
              <?php else: ?>
              <div class="card-thumb-ph"><img src="/assets/img/logo.svg"></div>
              <?php endif; ?>
              <p class="ctag ct-premium">
                <img src="/assets/img/hero_star_fff.svg" alt="">
                <span>프리미엄 강의</span>
              </p>
            </div>
            <div class="card-info">
              <div class="card-tags">
                <?php if ($c['badge_hot']): ?><span class="ctags ct-hot">HOT</span><?php endif; ?>
                <?php if ($c['badge_new']): ?><span class="ctags ct-new">NEW</span><?php endif; ?>                
              </div>
              <div class="card-title"><?= htmlspecialchars($c['title']) ?></div>
              <div class="card-meta">
                <?= htmlspecialchars($c['instructor_name']) ?>
                <?= !empty($c['category_name']) ? ' · ' . htmlspecialchars($c['category_name']) : '' ?>
                <!-- <?php if ($c['price'] > 0): ?>
                &nbsp;|&nbsp; <?= number_format((int)$c['price']) ?>원
                <?php endif; ?> -->
              </div>
            </div>
          </a>
        </div>
        <?php endforeach; ?>
      </div>
    </div>
  </div>
  <div class="home-section-more-area">
    <div class="home_inner">      
      <a href="/classes?type=premium" class="home-section-more">
        <span>프리미엄 강의 더보기</span>
        <img src="/assets/img/more_arr.svg" alt="">
      </a>
    </div>
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
            <img src="/uploads/banner/<?= htmlspecialchars($b['image_path']) ?>" alt="<?= htmlspecialchars($b['alt_text']) ?>">
          </a>
          <?php else: ?>
          <img src="/uploads/banner/<?= htmlspecialchars($b['image_path']) ?>" alt="<?= htmlspecialchars($b['alt_text']) ?>">
          <?php endif; ?>
        <?php else: ?>
        <div class="home-banner-ph"><img src="/assets/img/logo.svg"></div>
        <?php endif; ?>
      </div>
      <?php endforeach; ?>
    </div>
    <?php if (count($banners) > 1): ?>
    <!-- <div class="swiper-pagination"></div>
    <div class="swiper-button-prev"></div>
    <div class="swiper-button-next"></div> -->
    <?php endif; ?>
  </div>
</section>
<?php endif; ?>

<!-- ====================================================
  ⑥ 수강생 후기
===================================================== -->
<?php if (!empty($reviews)): ?>
<section class="home-section" id="section-reviews">
  <div class="home-section-header">
    <div class="home_inner">
      <p class="home-section-title">
        <img src="/assets/img/hero_star.svg" alt="">
        <strong>수강생 후기</strong>
      </p>
      <p class="home-section-en">Reviews</p>    
    </div>
  </div>
  <div class="home_swiper_area">
    <div class="home_swp_controll">
      <div class="swiper-button-prev"></div>
      <div class="swiper-pagination"></div>
      <div class="swiper-button-next"></div>
    </div>
    <div class="swiper home-review-swiper" id="swiper-reviews">
      <div class="swiper-wrapper">
        <?php
          $avg = number_format(array_sum(array_column($reviews, 'rating')) / count($reviews), 1);
          $per = $avg * 20;
        ?>
        <?php foreach ($reviews as $rv): ?>
        <div class="swiper-slide">
          <button type="button" class="home-review-card"
                  data-name="<?= htmlspecialchars(maskName($rv['member_name'])) ?>"
                  data-rating="<?= (int)$rv['rating'] ?>"
                  data-title="<?= htmlspecialchars($rv['class_title']) ?>"
                  data-content="<?= htmlspecialchars($rv['content'] ?? '') ?>"
                  data-date="<?= htmlspecialchars(substr($rv['created_at'], 0, 10)) ?>"
                  data-thumbnail="<?= htmlspecialchars($rv['class_thumbnail'] ?? '') ?>"
                  data-images="<?= htmlspecialchars(json_encode($rv['images'] ?? [])) ?>">
            <div class="rv-class-title"><?= htmlspecialchars($rv['class_title']) ?></div>
            <div class="rv-head">                                          
              <div class="rv-stars">
                <!-- <?= starRating((int)$rv['rating']) ?> -->
                <p class="rv-star-front"><img src="/assets/img/star_avg.png" alt=""></p>
                <p class="rv-star-bar" style="width:<?= $per ?>%;"></p>
              </div>            
              <p class="rv-avg"><?= $avg ?></p>
              <p class="rv-bar"></p>
              <div class="rv-name"><?= htmlspecialchars(maskName($rv['member_name'])) ?></div>              
              <!-- <div class="rv-date"><?= htmlspecialchars(substr($rv['created_at'], 0, 10)) ?></div> -->
            </div>            
            <div class="rv-content"><?= htmlspecialchars($rv['content'] ?? '') ?></div>
            <div class="rv-class-info">
              <div class="rv-class-img">
                <?php if (!empty($rv['class_thumbnail'])): ?>
                <img src="/uploads/class/<?= htmlspecialchars($rv['class_thumbnail']) ?>" alt="<?= htmlspecialchars($rv['class_title']) ?>">
                <?php else: ?>
                <div class="rv-thumb-ph"><img src="/assets/img/logo.svg" alt=""></div>
                <?php endif; ?>
              </div>
              <p class="rv-class-name"><?= htmlspecialchars($rv['class_title']) ?></p>
            </div>
          </button>
        </div>
        <?php endforeach; ?>
      </div>
    </div>
  </div>
</section>
<?php endif; ?>

<!-- ====================================================
  ⑤ 강사진 슬라이더
===================================================== -->
<?php if (!empty($instructors)): ?>
<section class="home-section home-section--gray" id="section-inst">
  <div class="home-section-header">
    <div class="home_inner">
      <p class="home-section-title color_fff">
        <img src="/assets/img/hero_star.svg" alt="">
        <strong>유니콘 수익화 강사진</strong>
      </p>
      <p class="home-section-en">Unicorn Teachers</p>    
    </div>
  </div>
  <div class="swiper home-inst-swiper" id="swiper-inst">
    <div class="swiper-wrapper">
      <?php foreach ($instructors as $ins): ?>
      <div class="swiper-slide home-inst-card">
        <a href="/instructors/<?= (int)$ins['instructor_idx'] ?>" class="i-card-wrap">
          <div class="i-photo-wrap">
            <?php if (!empty($ins['photo'])): ?>
            <img src="/uploads/instructor/<?= htmlspecialchars($ins['photo']) ?>" alt="<?= htmlspecialchars($ins['name']) ?>" class="i-photo-img">
            <?php else: ?>
            <div class="i-photo-ph"><img src="/assets/img/logo.svg" alt=""></div>
            <?php endif; ?>
          </div>
          <div class="i-info">
            <div class="i-name"><?= htmlspecialchars($ins['name']) ?></div>
            <?php if (!empty($ins['intro'])): ?>
            <ul class="i-photo-desc">
              <li><?= htmlspecialchars($ins['intro']) ?></li>
            </ul>
            <?php endif; ?>
            <div class="i-social-fixed">
              <?php if (!empty($ins['sns_youtube'])): ?>
              <div class="i-social-icon" title="유튜브"><img src="/assets/img/inst_youtube.svg" alt=""></div>
              <?php endif; ?>
              <?php if (!empty($ins['sns_instagram'])): ?>
              <div class="i-social-icon" title="인스타그램"><img src="/assets/img/inst_insta.svg" alt=""></div>
              <?php endif; ?>
              <?php if (!empty($ins['sns_facebook'])): ?>
              <div class="i-social-icon" title="페이스북"><img src="/assets/img/inst_facebook.svg" alt=""></div>
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
  <div class="home-section-more-area">
    <div class="home_inner">      
      <a href="/instructors" class="home-section-more ver2">
        <span>강사진 전체보기</span>
        <img src="/assets/img/more_arr_fff.svg" alt="">
      </a>
    </div>
  </div>
</section>
<?php endif; ?>

<!-- ====================================================
  ⑦ 메인 팝업
===================================================== -->
<?php if (!empty($popups)): ?>
<div id="popup-overlay" class="popup-overlay" role="dialog" aria-modal="true">
  <div id="popup-modal" class="popup-modal">  
    <div class="popup-slider swiper" id="popup-swiper">
      <div class="swiper-wrapper">
        <?php foreach ($popups as $p): ?>
        <div class="swiper-slide popup-slide">
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
      <div class="swiper-pagination"></div>
      <div class="swiper-button-prev"></div>
      <div class="swiper-button-next"></div>
      <?php endif; ?>
    </div>
    <div class="popup-footer">
      <button class="popup-today" onclick="homePopup.closeToday()">오늘 하루 보지 않기</button>
      <button class="popup-close-text" onclick="homePopup.close()">닫기</button>
    </div>
  </div>
</div>
<?php endif; ?>

<!-- 후기 상세 모달 -->
<div id="review-modal" class="review-modal-overlay" style="display:none"
     onclick="if(event.target===this)closeReviewModal()">
  <div class="review-modal-box">
    <button class="review-modal-close" onclick="closeReviewModal()"></button>
    <div class="review-modal_wrap">
      <!-- 이미지 스와이퍼 (이미지 있을 때만 표시) -->
      <div id="rm-img-area" class="rm-img-area" style="display:none">
        <div class="swiper rm-img-swiper" id="rm-img-swiper">
          <div class="swiper-wrapper" id="rm-img-slides"></div>
          <div class="swiper-pagination"></div>
          <div class="swiper-button-prev"></div>
          <div class="swiper-button-next"></div>
        </div>
      </div>

      <div class="rm-body">
        <!-- 강의명 -->
        <div class="rv-class-title" id="rm-title"></div>

        <!-- 별점바 + 이름 -->
        <div class="rv-head">
          <div class="rv-stars">
            <p class="rv-star-front"><img src="/assets/img/star_avg.png" alt=""></p>
            <p class="rv-star-bar" id="rm-star-bar"></p>
          </div>
          <p class="rv-avg" id="rm-avg"></p>
          <p class="rv-bar"></p>
          <div class="rv-name" id="rm-name"></div>
        </div>

        <!-- 전체 후기 내용 -->
        <div class="rv-content rv-content--full" id="rm-content"></div>

        <!-- 강의 정보 -->
        <div class="rv-class-info">
          <div class="rv-class-img" id="rm-class-img"></div>
          <p class="rv-class-name" id="rm-class-name"></p>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- Swiper JS -->
<script src="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js"></script>
<script>const HOME_POPUPS_COUNT = <?= count($popups ?? []) ?>;</script>
<script src="/assets/js/home.js"></script>
