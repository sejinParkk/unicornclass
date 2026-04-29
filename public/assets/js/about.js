(function () {
  'use strict';

  // ── 카운팅 설정 ──────────────────────────────────────────
  // 그룹 내 최대 target 기준으로 각 카운터의 duration을 비례 계산
  // → 모든 숫자가 같은 "속도감"으로 올라가며, 각자 자기 목표에서 자연스럽게 종료
  var BASE_DURATION = 1800; // 그룹 내 가장 큰 숫자의 duration (ms)

  var GROUPS = [
    {
      selector: '.intro3',
      counters: [
        { id: 'cnt1', target: 6,   decimals: 0 },
        { id: 'cnt2', target: 14,  decimals: 0 },
        { id: 'cnt3', target: 13,  decimals: 0 },
        { id: 'cnt4', target: 50,  decimals: 0 },
      ]
    },
    {
      selector: '.intro6',
      counters: [
        { id: 'cnt5', target: 6,   decimals: 0 },
        { id: 'cnt6', target: 4.6, decimals: 1 },
      ]
    }
  ];

  // 그룹별 최대값 기준으로 duration 계산
  GROUPS.forEach(function (group) {
    var maxTarget = Math.max.apply(null, group.counters.map(function (c) { return c.target; }));
    group.counters.forEach(function (c) {
      c.duration = Math.round((c.target / maxTarget) * BASE_DURATION);
    });
  });

  // ── easeOutCubic ─────────────────────────────────────────
  function easeOut(t) {
    return 1 - Math.pow(1 - t, 3);
  }

  function animateCount(el, target, decimals, duration) {
    var start = performance.now();
    function step(now) {
      var progress = Math.min((now - start) / duration, 1);
      var value    = easeOut(progress) * target;
      el.textContent = decimals > 0
        ? value.toFixed(decimals)
        : Math.floor(value).toString();
      if (progress < 1) requestAnimationFrame(step);
    }
    requestAnimationFrame(step);
  }

  // ── IntersectionObserver ─────────────────────────────────
  var observer = new IntersectionObserver(function (entries) {
    entries.forEach(function (entry) {
      if (!entry.isIntersecting) return;
      observer.unobserve(entry.target);

      var section = entry.target;
      GROUPS.forEach(function (group) {
        if (!section.matches(group.selector)) return;
        group.counters.forEach(function (cfg) {
          var el = section.querySelector('#' + cfg.id);
          if (el) animateCount(el, cfg.target, cfg.decimals, cfg.duration);
        });
      });
    });
  }, { threshold: 0.3 });

  document.addEventListener('DOMContentLoaded', function () {
    GROUPS.forEach(function (group) {
      var el = document.querySelector(group.selector);
      if (el) observer.observe(el);
    });
  });
})();
