'use strict';

/* ============================================================
   유틸
============================================================ */
function getCookie(name) {
  return document.cookie.split(';').some(c => c.trim().startsWith(name + '='));
}
function setCookie(name, days) {
  const d = new Date();
  d.setTime(d.getTime() + days * 24 * 60 * 60 * 1000);
  document.cookie = `${name}=1;expires=${d.toUTCString()};path=/`;
}

/* ============================================================
   Swiper 초기화
============================================================ */
function initSwipers() {

  // 강의 슬라이더 — pagination/navigation이 swiper 밖 .home_swp_controll에 있으므로 개별 초기화
  function initClassSwiper(id) {
    const swiperEl = document.getElementById(id);
    if (!swiperEl) return;
    const area = swiperEl.closest('.home_swiper_area');
    new Swiper('#' + id, {
      grabCursor: true,
      slidesPerView: 'auto',
      spaceBetween: 20,
      navigation: {
        prevEl: area.querySelector('.swiper-button-prev'),
        nextEl: area.querySelector('.swiper-button-next'),
      },
      pagination: {
        el: area.querySelector('.swiper-pagination'),
        clickable: true,
      },
    });
  }

  initClassSwiper('swiper-free');
  initClassSwiper('swiper-premium');

  // 강사 슬라이더
  if (document.getElementById('swiper-inst')) {
    new Swiper('#swiper-inst', {
      grabCursor: true,
      slidesPerView: 'auto',
      spaceBetween: 30,
      navigation: true,
      pagination: { clickable: true },
      // breakpoints: {
      //   0:    { slidesPerView: 2 },
      //   480:  { slidesPerView: 3 },
      //   768:  { slidesPerView: 4 },
      //   1024: { slidesPerView: 5 },
      // },
    });
  }

  // 수강생 후기 슬라이더
  if (document.getElementById('swiper-reviews')) {
    const reviewEl = document.getElementById('swiper-reviews');
    const reviewArea = reviewEl.closest('.home_swiper_area');
    new Swiper('#swiper-reviews', {
      grabCursor: true,
      slidesPerView: 'auto',
      spaceBetween: 20,
      navigation: {
        prevEl: reviewArea.querySelector('.swiper-button-prev'),
        nextEl: reviewArea.querySelector('.swiper-button-next'),
      },
      pagination: {
        el: reviewArea.querySelector('.swiper-pagination'),
        clickable: true,
      },
    });
  }

  // 이벤트 배너 슬라이더
  if (document.getElementById('swiper-banner')) {
    new Swiper('#swiper-banner', {
      grabCursor: true,
      loop: true,
      spaceBetween: 20,
      // autoplay: { delay: 5000, disableOnInteraction: false },
      navigation: true,
      pagination: { clickable: true },
    });
  }
}

/* ============================================================
   메인 팝업 (Swiper — 5초 자동슬라이드 + 드래그)
============================================================ */
const homePopup = (() => {
  const COOKIE = 'uc_popup_hide';
  let swiper = null;

  function init() {
    if (typeof HOME_POPUPS_COUNT === 'undefined' || HOME_POPUPS_COUNT === 0) return;
    if (getCookie(COOKIE)) return;

    const overlay = document.getElementById('popup-overlay');
    if (!overlay) return;
    overlay.style.display = 'flex';

    overlay.addEventListener('click', e => {
      if (e.target === overlay) close();
    });

    if (HOME_POPUPS_COUNT > 1) {
      swiper = new Swiper('#popup-swiper', {
        loop: true,
        grabCursor: true,
        autoplay: { delay: 5000, disableOnInteraction: false },
        pagination: { el: '#popup-swiper .swiper-pagination', clickable: true },
        navigation: {
          prevEl: '#popup-swiper .swiper-button-prev',
          nextEl: '#popup-swiper .swiper-button-next',
        },
      });
    }
  }

  function close() {
    if (swiper) swiper.autoplay.stop();
    const overlay = document.getElementById('popup-overlay');
    if (overlay) overlay.style.display = 'none';
  }

  function closeToday() {
    setCookie(COOKIE, 1);
    close();
  }

  return { init, close, closeToday };
})();

/* ============================================================
   수강생 후기 모달
============================================================ */
let _rmImgSwiper = null;

function closeReviewModal() {
  document.getElementById('review-modal').style.display = 'none';
}

function initReviewModal() {
  document.querySelectorAll('.home-review-card').forEach(card => {
    card.addEventListener('click', () => {
      const rating    = parseInt(card.dataset.rating || '5', 10);
      const images    = JSON.parse(card.dataset.images || '[]');
      const thumbnail = card.dataset.thumbnail || '';

      // 별점바
      document.getElementById('rm-star-bar').style.width = (rating * 20) + '%';
      document.getElementById('rm-avg').textContent      = rating + '.0';
      document.getElementById('rm-name').textContent     = card.dataset.name    || '';
      document.getElementById('rm-title').textContent    = card.dataset.title   || '';
      document.getElementById('rm-content').textContent  = card.dataset.content || '';
      document.getElementById('rm-class-name').textContent = card.dataset.title || '';

      // 강의 썸네일
      const classImgEl = document.getElementById('rm-class-img');
      if (thumbnail) {
        classImgEl.innerHTML = `<img src="/uploads/class/${thumbnail}" alt="" style="min-width:100%;min-height:100%;width:auto;height:auto;">`;
      } else {
        classImgEl.innerHTML = `<div class="rv-thumb-ph"><img src="/assets/img/logo.svg" alt=""></div>`;
      }

      // 이미지 스와이퍼
      const imgArea   = document.getElementById('rm-img-area');
      const imgSlides = document.getElementById('rm-img-slides');

      if (_rmImgSwiper) { _rmImgSwiper.destroy(true, true); _rmImgSwiper = null; }

      if (images.length > 0) {
        imgSlides.innerHTML = images.map(f =>
          `<div class="swiper-slide"><img src="/uploads/review/${f}" alt="후기 이미지"></div>`
        ).join('');
        imgArea.style.display = 'block';

        _rmImgSwiper = new Swiper('#rm-img-swiper', {
          loop: images.length > 1,
          grabCursor: true,
          pagination: { el: '#rm-img-swiper .swiper-pagination', clickable: true },
          navigation: {
            prevEl: '#rm-img-swiper .swiper-button-prev',
            nextEl: '#rm-img-swiper .swiper-button-next',
          },
        });
      } else {
        imgArea.style.display = 'none';
        imgSlides.innerHTML   = '';
      }

      document.getElementById('review-modal').style.display = 'flex';
    });
  });
}

/* ============================================================
   DOMContentLoaded
============================================================ */
/* ============================================================
   인트로 스플래시
============================================================ */
function initSiteIntro() {
  const intro = document.getElementById('site-intro');
  if (!intro) return;

  const video = document.getElementById('heroVideo');
  let dismissed = false;

  function dismiss() {
    if (dismissed) return;
    dismissed = true;
    intro.classList.add('fade-out');
    intro.addEventListener('transitionend', () => intro.remove(), { once: true });
  }

  // 최대 1.5초 후 강제 해제 (영상 없을 때 포함)
  const timer = setTimeout(dismiss, 1500);

  if (video) {
    if (video.readyState >= 3) {
      clearTimeout(timer);
      dismiss();
    } else {
      video.addEventListener('canplay', () => {
        clearTimeout(timer);
        dismiss();
      }, { once: true });
    }
  }
}

document.addEventListener('DOMContentLoaded', () => {
  initSwipers();
  homePopup.init();
  initReviewModal();
  initSiteIntro();
});
