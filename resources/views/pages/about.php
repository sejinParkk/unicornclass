<?php
// 회사소개 페이지
// 퍼블리싱 작업 후 필요한 데이터는 AboutController에서 전달됩니다.

$isAbout = true;
?>

<div class="intro_area">

  <div class="intro intro1">
    <p class="intro1_txt1">ABOUT UNICORN CLASS</p>
    <p class="intro1_txt2">당신의 미래에<br>우리가 한 발 더 가까워집니다.</p>
    <p class="intro1_txt3">유니콘클래스는 실전 기반의 N잡 교육으로<br>누구나 원하는 수익을 만들 수 있도록 돕습니다.</p>
  </div>

  <div class="intro intro2">
    <p class="intro2_img"><img src="/assets/img/intro2_img.jpg" alt=""></p>
    <div class="intro2_flex">
      <div class="intro2_left">
        <p class="intro2_logo"><img src="/assets/img/logo2.svg" alt=""></p>
        <p class="intro1_txt3 ver2">대한민국 No.1<br>수익화 교육 플랫폼</p>
      </div>
      <div class="intro2_right">
        <p class="intro2_txt1">
          유니콘클래스는 강의를 중심으로 성장을 추구하는 교육 플랫폼입니다.<br>
          다년간 쌓은 노하우와 검증된 커리큘럼으로 <b>수강생 한 명 한 명의 성공을 지원합니다.</b>
        </p>
        <p class="intro2_txt2">
          유니콘 클래스의 임직원들은 "수강생들이 삶에서 실제로 변화를 만들어낼 수 있도록" 매일 최선을 다하고 있습니다.<br>
          No.1 수익화 교육 플랫폼으로서 지속적으로 성장하며, 강의를 통해 가능성을 열어드리는 유니콘클래스가 되겠습니다.
        </p>
      </div>
    </div>
    <p class="intro2_sign"><img src="/assets/img/intro2_sign.svg" alt=""></p>
  </div>

  <div class="intro3">
    <div class="intro3_wrap">
      <ul class="intro3_count">
        <li>
          <p class="intro3_cnt"><b id="cnt1">6</b>만<span>+</span></p>
          <p class="intro3_txt">누적 수강생</p>
        </li>
        <li>
          <p class="intro3_cnt"><b id="cnt2">14</b>만<span>+</span></p>
          <p class="intro3_txt">강의 수강 완료</p>
        </li>
        <li>
          <p class="intro3_cnt"><b id="cnt3">13</b>년<span>+</span></p>
          <p class="intro3_txt">교육 운영 연차</p>
        </li>
        <li>
          <p class="intro3_cnt"><b id="cnt4">50</b><span>+</span></p>
          <p class="intro3_txt">파트너 강사</p>
        </li>
      </ul>
      <p class="intro3_line"></p>
      <p class="intro3_txt2">
        유니콘클래스는 <span>교육이 인생을 바꾼다는 신념</span> 아래,<br>
        가치 있는 지식과 전문인을 누구나 함께 성공할 수 있도록 지원합니다.
      </p>
      <ul class="intro3_keyword">
        <li>실전 중심 커리큘럼</li>
        <li>업계 최고 강사진</li>
        <li>맞춤형 케어 시스템</li>
        <li>고퀄리티 무료강의</li>
      </ul>
    </div>
  </div>

  <div class="intro intro4">
    <div class="intro4_left">
      <img src="/assets/img/hero_star.svg" alt="">
      <p class="intro4_title">Teachers</p>
      <p class="intro1_txt3 ver2">각 분야 최고의 전문가들이<br>검증된 수익화 노하우를 공개합니다</p>
    </div>
    <div class="intro4_right">
      <?php if (!empty($instructorGroups)): ?>
        <?php foreach ($instructorGroups as $group): ?>
        <div class="intro4_area">
          <p class="intro4_cate"><?= htmlspecialchars($group['category_name']) ?></p>
          <div class="intro-grid">
            <?php foreach ($group['instructors'] as $ins): ?>
            <a href="/instructors/<?= $ins['instructor_idx'] ?>" class="i-card-wrap">
              <div class="i-card">
                <div class="i-photo-wrap">
                  <?php if (!empty($ins['photo'])): ?>
                  <img src="/uploads/instructor/<?= htmlspecialchars($ins['photo']) ?>"
                       alt="<?= htmlspecialchars($ins['name']) ?>" class="i-photo-img">
                  <?php else: ?>
                  <div class="i-photo-ph">
                    <div class="person-icon">👤</div>
                    <small><?= htmlspecialchars($ins['name']) ?></small>
                  </div>
                  <?php endif; ?>
                </div>
                <div class="i-info ver2">
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
              </div>
            </a>
            <?php endforeach; ?>
          </div>
        </div>
        <?php endforeach; ?>
      <?php endif; ?>
    </div>
  </div>

  <div class="intro intro4 intro5">
    <div class="intro4_left">
      <img src="/assets/img/hero_star.svg" alt="">
      <p class="intro4_title">Reviews</p>
      <p class="intro1_txt3 ver2">실제로 변화를 만든<br>수강생들의 이야기를 확인해 보세요</p>
    </div>
    <div class="intro4_right">
      <div class="intro4_area">
        <p class="intro2_img intro5_img"><img src="/assets/img/intro5_img.png" alt=""></p>
        <p class="intro2_txt1">
          <strong>
          처음엔 반신반의했는데,<br>
          실전에 바로 적용할 수 있게 알려주니까 금방 감이 잡히더라고요.
          </strong>
        </p>
        <p class="intro2_txt2">
          시간이 조금 지나면서 실제 결과도 나오기 시작했고, 지금은 꾸준히 수익이 발생하고 있습니다.<br>
          처음에는 저도 믿기 어려웠지만, 직접 해보니까 왜 가능한지 알겠더라고요.
        </p>
      </div>
    </div>
  </div>

  <div class="intro intro6">
    <div class="intro6_left">
      <p class="intro6_txt1">
        <img src="/assets/img/hero_star.svg" alt="">
        <span>UNICORN CLASS</span>
      </p>
      <p class="intro6_img"><img src="/assets/img/intro6_img.png" alt=""></p>
      <p class="intro6_txt2">
        유니콘 클래스는<br>
        실전에서 쓰는<br>
        <span>진짜 기술</span>만 가르칩니다.
      </p>
    </div>
    <div class="intro6_right">
      <div class="intro6_box">
        <p class="intro6_txt3">누적 결제 수강생</p>
        <p class="intro6_txt4">유니콘 클래스를 선택한 수강생 수</p>
        <p class="intro3_cnt intro6_count">
          <b id="cnt5">6</b>만<span>+</span>
        </p>
      </div>
      <div class="intro6_box">
        <p class="intro6_txt3">누적 강의 후기</p>
        <p class="intro6_txt4">수강 후 직접 남긴 수강생 후기 수</p>
        <p class="intro3_cnt intro6_count">
          <b id="cnt6">4.6</b>만<span>+</span>
        </p>
      </div>
    </div>
  </div>

  <div class="intro intro7">
    <p class="intro7_txt">모든 위대한 성취는,<br>아주 작은 행동에서 시작됩니다.</p>
    <p class="intro2_txt2">강의를 둘러보고 지금 바로 첫 걸음을 내딛어 보세요.</p>
    <p class="intro7_btn"><a href="/classes">강의 보러가기 →</a></p>
  </div>
</div>

<script src="/assets/js/about.js"></script>