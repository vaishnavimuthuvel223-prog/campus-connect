<?php
// Security: Initialize session and security helper
require_once '../config/db.php';

$error = '';
$loginAttempted = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $loginAttempted = true;
    
    // ===== CSRF TOKEN VALIDATION =====
    if (!isset($_POST['csrf_token']) || !$security->verifyCSRFToken($_POST['csrf_token'])) {
        $error = '⚠️ Security error: Invalid request token. Please try again.';
    } else {
        $email = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';

        // ===== INPUT VALIDATION =====
        if (empty($email) || empty($password)) {
            $error = '❌ Email and password are required.';
        } elseif (!SecurityHelper::validateEmail($email)) {
            $error = '❌ Invalid email format.';
        } else {
            // ===== RATE LIMITING CHECK =====
            if (!$security->checkLoginAttempts($email)) {
                $error = '⛔ Too many login attempts. Please try again after 15 minutes.';
                $security->logLoginActivity(0, 'student', $email, false);
            } else {
                $stmt = $conn->prepare("SELECT * FROM students WHERE email = ?");
                $stmt->execute([$email]);
                $student = $stmt->fetch();

                // ===== PASSWORD VERIFICATION =====
                if ($student && password_verify($password, $student['password'])) {
                    // ===== PREVENT CONCURRENT SESSIONS =====
                    if (!$security->preventConcurrentSessions($student['student_id'], 'student')) {
                        $error = '⛔ You already have an active session. Please logout first.';
                        $security->recordFailedAttempt($email);
                    } else {
                        // ===== SUCCESSFUL LOGIN =====
                        $security->clearLoginAttempts($email);
                        
                        // Register login with security
                        $security->registerLogin($student['student_id'], 'student', $email);
                        
                        // Store additional student data
                        $_SESSION['name'] = $student['name'];
                        $_SESSION['dept_id'] = $student['dept_id'];
                        $_SESSION['verification_status'] = $student['verification_status'] ?? 'pending';
                        $_SESSION['reg_no'] = $student['reg_no'];
                        
                        // Redirect to dashboard
                        header('Location: dashboard.php', true, 302);
                        exit;
                    }
                } else {
                    // ===== FAILED LOGIN =====
                    $error = '❌ Invalid email or password.';
                    $security->recordFailedAttempt($email);
                    
                    // Log failed attempt
                    if (defined('LOG_FAILED_ATTEMPTS') && LOG_FAILED_ATTEMPTS) {
                        error_log("Failed login attempt for student: $email from IP: " . $security->getClientIP());
                    }
                }
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Student Login - Campus Connect</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
<style>
:root {
  --c1: #3b82f6;
  --c2: #06b6d4;
  --c3: #10b981;
  --ink: #0f172a;
  --muted: #64748b;
}
* { margin:0; padding:0; box-sizing:border-box; }
html { scroll-behavior:smooth; }
body {
  font-family:'Inter','Plus Jakarta Sans',sans-serif;
  min-height:100vh;
  display:flex;
  background:#f4f6fb;
  color:var(--ink);
  overflow-x:hidden;
}

/* ---------- Left brand panel ---------- */
.brand-panel {
  position:relative;
  flex:1 1 46%;
  min-height:100vh;
  display:flex;
  flex-direction:column;
  justify-content:space-between;
  padding:3rem 3.2rem;
  background:linear-gradient(155deg, var(--c1) 0%, var(--c2) 55%, var(--c3) 100%);
  background-size:220% 220%;
  animation:panelShift 14s ease infinite;
  overflow:hidden;
  color:#fff;
}
@keyframes panelShift {
  0%   { background-position:0% 30%; }
  50%  { background-position:100% 70%; }
  100% { background-position:0% 30%; }
}
.blob {
  position:absolute;
  border-radius:50%;
  filter:blur(4px);
  background:rgba(255,255,255,0.10);
  animation:drift 12s ease-in-out infinite;
}
.blob.b1 { width:260px; height:260px; top:-60px; right:-60px; animation-delay:0s; }
.blob.b2 { width:180px; height:180px; bottom:8%; left:-50px; animation-delay:2s; background:rgba(255,255,255,0.08); }
.blob.b3 { width:120px; height:120px; bottom:30%; right:12%; animation-delay:4s; background:rgba(255,255,255,0.12); }
@keyframes drift {
  0%,100% { transform:translateY(0) translateX(0) scale(1); }
  50% { transform:translateY(-24px) translateX(14px) scale(1.06); }
}
.grid-overlay {
  position:absolute; inset:0;
  background-image:linear-gradient(rgba(255,255,255,0.06) 1px, transparent 1px),
                    linear-gradient(90deg, rgba(255,255,255,0.06) 1px, transparent 1px);
  background-size:44px 44px;
  mask-image:radial-gradient(circle at 30% 20%, #000 0%, transparent 70%);
  pointer-events:none;
}
.brand-top { position:relative; z-index:2; display:flex; align-items:center; gap:0.85rem; animation:fadeDown .7s ease both; }
.brand-logo {
  width:48px; height:48px; border-radius:14px; background:rgba(255,255,255,0.16);
  display:flex; align-items:center; justify-content:center; backdrop-filter:blur(6px);
  border:1px solid rgba(255,255,255,0.35); overflow:hidden;
}
.brand-logo img { width:100%; height:100%; object-fit:cover; }
.brand-top span { font-weight:800; font-size:1.15rem; letter-spacing:0.2px; }

.brand-mid { position:relative; z-index:2; max-width:430px; animation:fadeUp .8s ease .15s both; }
.role-tag {
  display:inline-flex; align-items:center; gap:0.45rem;
  background:rgba(255,255,255,0.16); border:1px solid rgba(255,255,255,0.3);
  padding:0.4rem 0.9rem; border-radius:999px; font-size:0.78rem; font-weight:700;
  letter-spacing:0.5px; text-transform:uppercase; margin-bottom:1.4rem;
}
.brand-mid h1 { font-family:'Plus Jakarta Sans',sans-serif; font-size:2.5rem; line-height:1.15; font-weight:800; margin-bottom:1rem; letter-spacing:-0.5px; }
.brand-mid p { font-size:1rem; color:rgba(255,255,255,0.86); line-height:1.6; margin-bottom:2rem; }

.feature-list { display:flex; flex-direction:column; gap:0.9rem; }
.feature-list li { list-style:none; display:flex; align-items:center; gap:0.7rem; font-size:0.92rem; color:rgba(255,255,255,0.92); opacity:0; animation:fadeUp .6s ease forwards; }
.feature-list li:nth-child(1) { animation-delay:.35s; }
.feature-list li:nth-child(2) { animation-delay:.5s; }
.feature-list li:nth-child(3) { animation-delay:.65s; }
.feature-list .dot { width:26px; height:26px; border-radius:8px; background:rgba(255,255,255,0.18); display:flex; align-items:center; justify-content:center; flex:none; }
.feature-list svg { width:14px; height:14px; }

.brand-bottom { position:relative; z-index:2; font-size:0.8rem; color:rgba(255,255,255,0.7); animation:fadeUp .8s ease .2s both; }

/* ---------- Right form panel ---------- */
.form-panel {
  flex:1 1 54%;
  display:flex;
  align-items:center;
  justify-content:center;
  padding:2.5rem 1.5rem;
  background:#f4f6fb;
  position:relative;
}
.form-shell {
  width:100%;
  max-width:420px;
  animation:fadeUp .7s ease .1s both;
}
.form-shell-top { display:none; }

.form-card {
  background:#ffffff;
  border-radius:24px;
  padding:2.6rem 2.4rem;
  box-shadow:0 30px 60px -20px rgba(15,23,42,0.18), 0 4px 14px rgba(15,23,42,0.04);
  border:1px solid #eef1f6;
}
.form-card h2 { font-family:'Plus Jakarta Sans',sans-serif; font-size:1.65rem; font-weight:800; margin-bottom:0.35rem; color:var(--ink); }
.form-card .lead { font-size:0.92rem; color:var(--muted); margin-bottom:1.8rem; }

.alert {
  display:flex; align-items:center; gap:0.6rem;
  padding:0.85rem 1rem; border-radius:12px; font-size:0.86rem; font-weight:600;
  margin-bottom:1.4rem; animation:shake .4s ease;
}
.alert-danger { background:#fef2f2; color:#b91c1c; border:1px solid #fecaca; }
@keyframes shake {
  0%,100% { transform:translateX(0); }
  20% { transform:translateX(-6px); }
  40% { transform:translateX(6px); }
  60% { transform:translateX(-4px); }
  80% { transform:translateX(4px); }
}

.field { position:relative; margin-bottom:1.35rem; }
.field label { display:block; font-size:0.8rem; font-weight:700; color:var(--ink); margin-bottom:0.45rem; }
.field-wrap { position:relative; display:flex; align-items:center; }
.field input {
  width:100%;
  padding:0.85rem 2.7rem 0.85rem 1rem;
  border:1.6px solid #e2e8f0;
  border-radius:12px;
  font-size:0.95rem;
  font-family:inherit;
  color:var(--ink);
  background:#f8fafc;
  transition:border-color .25s ease, box-shadow .25s ease, background .25s ease;
}
.field input:focus {
  outline:none;
  border-color:var(--c2);
  background:#fff;
  box-shadow:0 0 0 4px rgba(6,182,212,0.18);
  box-shadow:0 0 0 4px color-mix(in srgb, var(--c2) 18%, transparent);
}
.field-icon {
  position:absolute; right:0.9rem; display:flex; color:#94a3b8; pointer-events:none;
}
.field-icon svg { width:18px; height:18px; }
.toggle-pass {
  position:absolute; right:0.9rem; background:none; border:none; cursor:pointer; color:#94a3b8; display:flex; padding:0;
}
.toggle-pass svg { width:18px; height:18px; }
.toggle-pass:hover { color:var(--c2); }

.field-options { display:flex; justify-content:flex-end; margin:-0.6rem 0 1.3rem; }
.field-options a { font-size:0.82rem; color:var(--c2); text-decoration:none; font-weight:600; }
.field-options a:hover { text-decoration:underline; }

.btn-submit {
  width:100%;
  padding:0.95rem;
  border:none;
  border-radius:12px;
  background:linear-gradient(135deg, var(--c1), var(--c2));
  color:#fff;
  font-size:0.95rem;
  font-weight:700;
  letter-spacing:0.3px;
  cursor:pointer;
  position:relative;
  overflow:hidden;
  display:flex; align-items:center; justify-content:center; gap:0.55rem;
  transition:transform .2s ease, box-shadow .2s ease;
  box-shadow:0 12px 24px -8px rgba(6,182,212,0.4);
  box-shadow:0 12px 24px -8px color-mix(in srgb, var(--c2) 55%, transparent);
}
.btn-submit:hover { transform:translateY(-2px); box-shadow:0 16px 30px -8px rgba(6,182,212,0.45); }
.btn-submit:hover { transform:translateY(-2px); box-shadow:0 16px 30px -8px color-mix(in srgb, var(--c2) 65%, transparent); }
.btn-submit:active { transform:translateY(0); }
.btn-submit svg { width:16px; height:16px; transition:transform .2s ease; }
.btn-submit:hover svg { transform:translateX(3px); }

.divider { display:flex; align-items:center; gap:0.8rem; margin:1.7rem 0 1.3rem; color:#cbd5e1; font-size:0.78rem; }
.divider::before, .divider::after { content:''; flex:1; height:1px; background:#e2e8f0; }

.form-footer { text-align:center; }
.form-footer p { font-size:0.87rem; color:var(--muted); margin-bottom:0.5rem; }
.form-footer a { color:var(--c2); font-weight:700; text-decoration:none; }
.form-footer a:hover { text-decoration:underline; }
.back-home { display:inline-flex; align-items:center; gap:0.35rem; font-size:0.82rem; color:var(--muted); text-decoration:none; margin-top:1.1rem; transition:gap .2s ease, color .2s ease; }
.back-home:hover { gap:0.55rem; color:var(--ink); }
.back-home svg { width:14px; height:14px; }

@keyframes fadeUp { from { opacity:0; transform:translateY(18px); } to { opacity:1; transform:translateY(0); } }
@keyframes fadeDown { from { opacity:0; transform:translateY(-14px); } to { opacity:1; transform:translateY(0); } }

@media (max-width: 940px) {
  .brand-panel { display:none; }
  .form-panel { flex:1 1 100%; min-height:100vh; background:linear-gradient(160deg, var(--c1), var(--c2)); }
  .form-shell-top {
    display:flex; align-items:center; gap:0.7rem; margin-bottom:1.4rem; color:#fff; animation:fadeDown .6s ease both;
  }
  .form-shell-top .brand-logo { width:38px; height:38px; border-radius:11px; }
  .form-shell-top span { font-weight:800; font-size:1.02rem; }
}
@media (max-width: 480px) {
  .form-card { padding:2rem 1.5rem; border-radius:20px; }
}
</style>
</head>
<body>

<div class="brand-panel">
  <div class="grid-overlay"></div>
  <div class="blob b1"></div>
  <div class="blob b2"></div>
  <div class="blob b3"></div>

  <div class="brand-top">
    <div class="brand-logo"><img src="../uploads/MKCE-Logo.jpg" alt="College Logo" onerror="this.parentElement.style.display='none'"></div>
    <span>Campus Connect</span>
  </div>

  <div class="brand-mid">
    <span class="role-tag"><svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" style="margin-right:2px"><circle cx="12" cy="8" r="4"/><path d="M4 21c0-4 3.6-6 8-6s8 2 8 6"/></svg>Student Portal</span>
    <h1>Your placement journey starts here.</h1>
    <p>Track eligible drives, apply in one click, and follow your application status in real time.</p>
    <ul class="feature-list">
      <li><span class="dot"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><polyline points="20 6 9 17 4 12"/></svg></span>Apply to drives you're eligible for</li>
      <li><span class="dot"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><polyline points="20 6 9 17 4 12"/></svg></span>Real-time status &amp; staff approval tracking</li>
      <li><span class="dot"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><polyline points="20 6 9 17 4 12"/></svg></span>Manage profile, resume &amp; skills</li>
    </ul>
  </div>

  <div class="brand-bottom">© 2026 Campus Connect · Placement Cell</div>
</div>

<div class="form-panel">
  <div class="form-shell">
    <div class="form-shell-top">
      <div class="brand-logo"><img src="../uploads/MKCE-Logo.jpg" alt="College Logo" onerror="this.parentElement.style.display='none'"></div>
      <span>Campus Connect</span>
    </div>

    <div class="form-card">
      <h2>Welcome back</h2>
      <p class="lead">Sign in to your student account to continue.</p>

      <?php if ($error): ?>
        <div class="alert alert-danger">
          <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
          <span><?= htmlspecialchars(str_replace(['⚠️','❌','⛔'], '', $error)) ?></span>
        </div>
      <?php endif; ?>

      <form method="POST" class="login-form" id="loginForm">
        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($security->generateCSRFToken()) ?>">

        <div class="field">
          <label for="email">Email Address</label>
          <div class="field-wrap">
            <input type="email" id="email" name="email" placeholder="your.email@college.edu" required autocomplete="email" value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">
            <span class="field-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="5" width="18" height="14" rx="2"/><polyline points="3 7 12 13 21 7"/></svg></span>
          </div>
        </div>
        <div class="field">
          <label for="password">Password</label>
          <div class="field-wrap">
            <input type="password" id="password" name="password" placeholder="Enter your password" required autocomplete="current-password">
            <button type="button" class="toggle-pass" data-target="password"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M1 12s4-7 11-7 11 7 11 7-4 7-11 7-11-7-11-7z"/><circle cx="12" cy="12" r="3"/></svg></button>
          </div>
        </div>

        <button type="submit" class="btn-submit" id="submitBtn">
          <span>Login to Dashboard</span>
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4"><line x1="5" y1="12" x2="19" y2="12"/><polyline points="12 5 19 12 12 19"/></svg>
        </button>
      </form>

      <div class="form-footer">
        <p>Don't have an account? <a href="register.php">Create one now</a></p>
        <a href="/campuss/" class="back-home">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4"><line x1="19" y1="12" x2="5" y2="12"/><polyline points="12 19 5 12 12 5"/></svg>
          Back to Home
        </a>
      </div>
    </div>
  </div>
</div>

<script>
document.querySelectorAll('.toggle-pass').forEach(function(btn) {
  btn.addEventListener('click', function() {
    var input = document.getElementById(btn.dataset.target);
    var isPass = input.type === 'password';
    input.type = isPass ? 'text' : 'password';
    btn.innerHTML = isPass
      ? '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17.94 17.94A10.94 10.94 0 0 1 12 19c-7 0-11-7-11-7a18.5 18.5 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 7 11 7a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"/><line x1="1" y1="1" x2="23" y2="23"/></svg>'
      : '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M1 12s4-7 11-7 11 7 11 7-4 7-11 7-11-7-11-7z"/><circle cx="12" cy="12" r="3"/></svg>';
  });
});

var form = document.getElementById('loginForm');
var btn = document.getElementById('submitBtn');
if (form) {
  form.addEventListener('submit', function() {
    btn.style.opacity = '0.75';
    btn.style.pointerEvents = 'none';
    btn.querySelector('span').textContent = 'Signing in…';
  });
}
</script>
</body>
</html>
