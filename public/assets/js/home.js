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

  // 강의 슬라이더 공통 옵션
  const classOptions = {
    grabCursor: true,
    slidesPerView: 3,
    spaceBetween: 16,
    navigation: true,
    pagination: { clickable: true },
    breakpoints: {
      0:   { slidesPerView: 1 },
      480: { slidesPerView: 2 },
      768: { slidesPerView: 3 },
    },
  };

  if (document.getElementById('swiper-free')) {
    new Swiper('#swiper-free', classOptions);
  }
  if (document.getElementById('swiper-premium')) {
    new Swiper('#swiper-premium', classOptions);
  }

  // 강사 슬라이더
  if (document.getElementById('swiper-inst')) {
    new Swiper('#swiper-inst', {
      grabCursor: true,
      slidesPerView: 5,
      spaceBetween: 12,
      navigation: true,
      pagination: { clickable: true },
      breakpoints: {
        0:    { slidesPerView: 2 },
        480:  { slidesPerView: 3 },
        768:  { slidesPerView: 4 },
        1024: { slidesPerView: 5 },
      },
    });
  }

  // 이벤트 배너 슬라이더
  if (document.getElementById('swiper-banner')) {
    new Swiper('#swiper-banner', {
      grabCursor: true,
      loop: true,
      autoplay: { delay: 4000, disableOnInteraction: false },
      navigation: true,
      pagination: { clickable: true },
    });
  }
}

/* ============================================================
   메인 팝업 (커스텀 — 팝업 내부 슬라이더)
============================================================ */
const homePopup = (() => {
  const COOKIE  = 'uc_popup_hide';
  let current   = 0;

  function init() {
    if (typeof HOME_POPUPS_COUNT === 'undefined' || HOME_POPUPS_COUNT === 0) return;
    if (getCookie(COOKIE)) return;

    const overlay = document.getElementById('popup-overlay');
    if (!overlay) return;
    overlay.style.display = 'flex';

    overlay.addEventListener('click', e => {
      if (e.target === overlay) close(false);
    });
  }

  function slide(dir) { goTo(current + dir); }

  function goTo(index) {
    const track = document.getElementById('popup-track');
    if (!track) return;
    const count = track.children.length;
    current = (index + count) % count;
    track.style.transform = `translateX(-${current * 100}%)`;
    updateDots();
  }

  function updateDots() {
    const dotsEl = document.getElementById('popup-dots');
    if (!dotsEl) return;
    dotsEl.querySelectorAll('.popup-dot').forEach((d, i) => {
      d.classList.toggle('active', i === current);
    });
  }

  function close() {
    const chk = document.getElementById('popup-today-chk');
    if (chk && chk.checked) {
      setCookie(COOKIE, 1);
    }
    const overlay = document.getElementById('popup-overlay');
    if (overlay) overlay.style.display = 'none';
  }

  return { init, slide, goTo, close };
})();

/* ============================================================
   수강생 후기 모달
============================================================ */
function initReviewModal() {
  document.querySelectorAll('.home-review-card').forEach(card => {
    card.addEventListener('click', () => {
      const stars = '★'.repeat(parseInt(card.dataset.rating || '5', 10))
                  + '☆'.repeat(5 - parseInt(card.dataset.rating || '5', 10));

      document.getElementById('rm-avatar').textContent  = (card.dataset.name || '').charAt(0);
      document.getElementById('rm-name').textContent    = card.dataset.name    || '';
      document.getElementById('rm-stars').textContent   = stars;
      document.getElementById('rm-date').textContent    = card.dataset.date    || '';
      document.getElementById('rm-title').textContent   = card.dataset.title   || '';
      document.getElementById('rm-content').textContent = card.dataset.content || '';

      document.getElementById('review-modal').style.display = 'flex';
    });
  });
}

/* ============================================================
   DOMContentLoaded
============================================================ */
/* ============================================================
   히어로 포스터 페이드아웃
============================================================ */
function initHeroPoster() {
  const video  = document.getElementById('heroVideo');
  const poster = document.getElementById('heroPoster');
  if (!video || !poster) return;

  const fadeOut = () => poster.classList.add('fade-out');

  // 이미 재생 가능한 상태면 즉시 페이드 (캐시된 경우)
  if (video.readyState >= 3) {
    fadeOut();
  } else {
    video.addEventListener('canplay', fadeOut, { once: true });
  }
}

document.addEventListener('DOMContentLoaded', () => {
  initSwipers();
  homePopup.init();
  initReviewModal();
  initHeroPoster();
});
