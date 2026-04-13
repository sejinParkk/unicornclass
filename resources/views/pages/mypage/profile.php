<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>회원정보 수정 — 유니콘클래스</title>
    <link rel="stylesheet" href="/assets/css/noto-sans.css">
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', 'Noto Sans KR', sans-serif;
            background: #f7f8fa;
            min-height: 100vh;
            padding: 40px 20px;
            color: #1a202c;
        }
        .wrap { max-width: 560px; margin: 0 auto; }
        .page-header { margin-bottom: 28px; }
        .page-header h1 { font-size: 22px; font-weight: 700; }
        .page-header .back { display: inline-flex; align-items: center; gap: 4px; font-size: 13px;
            color: #718096; text-decoration: none; margin-bottom: 10px; }
        .page-header .back:hover { color: #4a5568; }

        .card { background: #fff; border-radius: 14px; padding: 28px 28px 24px;
            box-shadow: 0 2px 12px rgba(0,0,0,0.07); margin-bottom: 20px; }
        .card h2 { font-size: 15px; font-weight: 700; color: #2d3748; margin-bottom: 20px;
            padding-bottom: 12px; border-bottom: 1px solid #edf2f7; }

        .form-group { margin-bottom: 16px; }
        label { display: block; font-size: 12.5px; font-weight: 600; color: #4a5568; margin-bottom: 6px; }
        .req { color: #e53e3e; margin-left: 2px; }
        input[type=text], input[type=email], input[type=password] {
            width: 100%; padding: 10px 13px; border: 1px solid #e2e8f0; border-radius: 8px;
            font-size: 14px; color: #1a202c; outline: none; transition: border-color .15s;
        }
        input:focus { border-color: #667eea; box-shadow: 0 0 0 3px rgba(102,126,234,.12); }
        input.error { border-color: #e53e3e; }
        input[readonly] { background: #f7f8fa; color: #718096; cursor: not-allowed; }
        .error-msg { font-size: 12px; color: #e53e3e; margin-top: 4px; }
        .hint { font-size: 12px; color: #a0aec0; margin-top: 4px; }

        .check-group { display: flex; align-items: center; gap: 10px; flex-wrap: wrap; }
        .check-item { display: flex; align-items: center; gap: 6px; cursor: pointer; }
        .check-item input[type=checkbox] { width: 16px; height: 16px; cursor: pointer; accent-color: #667eea; }
        .check-item span { font-size: 13.5px; color: #4a5568; }

        .form-actions { display: flex; justify-content: flex-end; }
        .btn-save {
            background: #667eea; color: #fff; border: none; border-radius: 8px;
            padding: 10px 26px; font-size: 14px; font-weight: 600; cursor: pointer;
            transition: background .15s;
        }
        .btn-save:hover { background: #5a6fd6; }

        .alert { display: flex; align-items: center; gap: 8px; padding: 12px 14px;
            border-radius: 8px; font-size: 13.5px; margin-bottom: 16px; }
        .alert-success { background: #f0fff4; color: #276749; border: 1px solid #c6f6d5; }
        .alert-error   { background: #fff5f5; color: #9b2c2c; border: 1px solid #fed7d7; }

        .social-badge { display: inline-flex; align-items: center; gap: 5px; background: #edf2f7;
            border-radius: 6px; padding: 4px 10px; font-size: 12px; color: #4a5568; }
    </style>
</head>
<body>
<div class="wrap">
    <div class="page-header">
        <a href="/" class="back">← 홈으로</a>
        <h1>회원정보 수정</h1>
    </div>

    <?php
    $isSocial  = !empty($member['mb_password']) ? false : true;
    $pwError   = $_GET['pw_error'] ?? null;
    ?>

    <!-- ── 기본 정보 카드 ───────────────────────────────────── -->
    <div class="card">
        <h2>기본 정보</h2>

        <?php if ($saved): ?>
        <div class="alert alert-success">✓ 회원정보가 저장되었습니다.</div>
        <?php endif; ?>

        <form method="POST" action="/mypage/profile">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken) ?>">
            <input type="hidden" name="_action" value="profile">

            <div class="form-group">
                <label>아이디</label>
                <input type="text" value="<?= htmlspecialchars($member['mb_id']) ?>" readonly>
            </div>

            <?php if (!empty($member['signup_type']) && $member['signup_type'] !== 'email'): ?>
            <div class="form-group">
                <label>소셜 계정</label>
                <div style="margin-top:4px">
                    <span class="social-badge">
                        <?= $member['signup_type'] === 'kakao' ? '카카오' : '네이버' ?> 로그인
                    </span>
                </div>
            </div>
            <?php endif; ?>

            <div class="form-group">
                <label>이름 <span class="req">*</span></label>
                <input type="text" name="mb_name"
                    class="<?= isset($errors['mb_name']) ? 'error' : '' ?>"
                    value="<?= htmlspecialchars($_POST['mb_name'] ?? $member['mb_name'] ?? '') ?>"
                    placeholder="이름을 입력하세요" maxlength="20">
                <?php if (isset($errors['mb_name'])): ?>
                <div class="error-msg"><?= htmlspecialchars($errors['mb_name']) ?></div>
                <?php endif; ?>
            </div>

            <div class="form-group">
                <label>이메일</label>
                <input type="email" name="mb_email"
                    class="<?= isset($errors['mb_email']) ? 'error' : '' ?>"
                    value="<?= htmlspecialchars($_POST['mb_email'] ?? $member['mb_email'] ?? '') ?>"
                    placeholder="이메일 주소">
                <?php if (isset($errors['mb_email'])): ?>
                <div class="error-msg"><?= htmlspecialchars($errors['mb_email']) ?></div>
                <?php endif; ?>
            </div>

            <div class="form-group">
                <label>수신 동의</label>
                <div class="check-group" style="margin-top:6px">
                    <label class="check-item">
                        <input type="checkbox" name="mb_mailling" value="1"
                            <?= (isset($_POST['_action']) ? isset($_POST['mb_mailling']) : (int)($member['mb_mailling'] ?? 0)) ? 'checked' : '' ?>>
                        <span>이메일 수신 동의</span>
                    </label>
                    <label class="check-item">
                        <input type="checkbox" name="mb_sms" value="1"
                            <?= (isset($_POST['_action']) ? isset($_POST['mb_sms']) : (int)($member['mb_sms'] ?? 0)) ? 'checked' : '' ?>>
                        <span>SMS 수신 동의</span>
                    </label>
                </div>
            </div>

            <div class="form-actions">
                <button type="submit" class="btn-save">저장</button>
            </div>
        </form>
    </div>

    <!-- ── 비밀번호 변경 카드 ──────────────────────────────── -->
    <div class="card">
        <h2>비밀번호 변경</h2>

        <?php if ($pwChanged): ?>
        <div class="alert alert-success">✓ 비밀번호가 변경되었습니다.</div>
        <?php endif; ?>
        <?php if ($pwError): ?>
        <div class="alert alert-error">⚠ <?= htmlspecialchars($pwError) ?></div>
        <?php endif; ?>

        <?php if ($isSocial): ?>
        <p style="font-size:13.5px;color:#718096;">소셜 계정으로 가입하셨습니다. 비밀번호를 변경할 수 없습니다.</p>
        <?php else: ?>
        <form method="POST" action="/mypage/profile">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken) ?>">
            <input type="hidden" name="_action" value="password">

            <div class="form-group">
                <label>현재 비밀번호 <span class="req">*</span></label>
                <input type="password" name="current_password"
                    class="<?= isset($pwErrors['current_password']) ? 'error' : '' ?>"
                    placeholder="현재 비밀번호">
                <?php if (isset($pwErrors['current_password'])): ?>
                <div class="error-msg"><?= htmlspecialchars($pwErrors['current_password']) ?></div>
                <?php endif; ?>
            </div>

            <div class="form-group">
                <label>새 비밀번호 <span class="req">*</span></label>
                <input type="password" name="new_password"
                    class="<?= isset($pwErrors['new_password']) ? 'error' : '' ?>"
                    placeholder="8자 이상 입력하세요">
                <?php if (isset($pwErrors['new_password'])): ?>
                <div class="error-msg"><?= htmlspecialchars($pwErrors['new_password']) ?></div>
                <?php else: ?>
                <div class="hint">8자 이상 입력해주세요.</div>
                <?php endif; ?>
            </div>

            <div class="form-group">
                <label>새 비밀번호 확인 <span class="req">*</span></label>
                <input type="password" name="confirm_password"
                    class="<?= isset($pwErrors['confirm_password']) ? 'error' : '' ?>"
                    placeholder="새 비밀번호를 다시 입력하세요">
                <?php if (isset($pwErrors['confirm_password'])): ?>
                <div class="error-msg"><?= htmlspecialchars($pwErrors['confirm_password']) ?></div>
                <?php endif; ?>
            </div>

            <div class="form-actions">
                <button type="submit" class="btn-save">비밀번호 변경</button>
            </div>
        </form>
        <?php endif; ?>
    </div>
</div>
</body>
</html>
