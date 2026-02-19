<?php
require_once 'config.php';
if (isLoggedIn()) { header('Location: index.php'); exit; }
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Wihima – Connect Locally</title>
<style>
*{box-sizing:border-box;margin:0;padding:0}
body{font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',sans-serif;background:#f0f2f5;min-height:100vh;display:flex;align-items:center;justify-content:center}
.auth-wrap{width:100%;max-width:400px;padding:20px}
.auth-logo{text-align:center;margin-bottom:28px}
.auth-logo h1{font-size:2rem;font-weight:800;color:#1a1a2e;letter-spacing:-0.5px}
.auth-logo p{color:#6b7280;font-size:.9rem;margin-top:4px}
.auth-card{background:#fff;border-radius:16px;padding:32px;box-shadow:0 2px 20px rgba(0,0,0,.08)}
.tab-bar{display:flex;gap:8px;margin-bottom:24px;background:#f0f2f5;border-radius:10px;padding:4px}
.tab-btn{flex:1;padding:8px;border:none;background:transparent;border-radius:8px;cursor:pointer;font-size:.9rem;font-weight:500;color:#6b7280;transition:.2s}
.tab-btn.active{background:#fff;color:#1a1a2e;box-shadow:0 1px 4px rgba(0,0,0,.1)}
.form-panel{display:none}
.form-panel.active{display:block}
.form-group{margin-bottom:16px}
label{display:block;font-size:.8rem;font-weight:600;color:#374151;margin-bottom:6px;text-transform:uppercase;letter-spacing:.5px}
input[type=text],input[type=email],input[type=password]{width:100%;padding:11px 14px;border:1.5px solid #e5e7eb;border-radius:10px;font-size:.95rem;outline:none;transition:.2s;background:#fafafa}
input:focus{border-color:#6366f1;background:#fff}
.avatar-upload{display:flex;align-items:center;gap:12px}
.avatar-preview{width:56px;height:56px;border-radius:50%;object-fit:cover;background:#e5e7eb;border:2px solid #e5e7eb}
.avatar-upload label.btn-upload{flex:1;padding:10px;background:#f0f2f5;border:1.5px dashed #d1d5db;border-radius:10px;text-align:center;cursor:pointer;font-size:.85rem;color:#6b7280;transition:.2s}
.avatar-upload label.btn-upload:hover{border-color:#6366f1;color:#6366f1}
.btn-primary{width:100%;padding:12px;background:linear-gradient(135deg,#6366f1,#8b5cf6);border:none;border-radius:10px;color:#fff;font-size:.95rem;font-weight:600;cursor:pointer;transition:.2s;margin-top:4px}
.btn-primary:hover{opacity:.9;transform:translateY(-1px)}
.forgot-link{text-align:right;margin-top:-8px;margin-bottom:12px}
.forgot-link a{font-size:.82rem;color:#6366f1;text-decoration:none}
.msg{padding:10px 14px;border-radius:8px;font-size:.85rem;margin-bottom:12px;display:none}
.msg.error{background:#fee2e2;color:#dc2626;display:block}
.msg.success{background:#d1fae5;color:#059669;display:block}
.otp-section{display:none}
.otp-section.show{display:block}
</style>
</head>
<body>
<div class="auth-wrap">
  <div class="auth-logo">
    <h1>🌍 Wihima</h1>
    <p>Connect with people around you</p>
  </div>
  <div class="auth-card">
    <div class="tab-bar" id="mainTabs">
      <button class="tab-btn active" onclick="switchTab('login')">Login</button>
      <button class="tab-btn" onclick="switchTab('register')">Register</button>
    </div>

    <!-- LOGIN -->
    <div class="form-panel active" id="panel-login">
      <div class="msg" id="login-msg"></div>
      <div class="form-group">
        <label>Email</label>
        <input type="email" id="l-email" placeholder="you@example.com">
      </div>
      <div class="form-group">
        <label>Password</label>
        <input type="password" id="l-pass" placeholder="••••••••">
      </div>
      <div class="forgot-link"><a href="#" onclick="switchTab('forgot')">Forgot password?</a></div>
      <button class="btn-primary" onclick="doLogin()">Login</button>
    </div>

    <!-- REGISTER -->
    <div class="form-panel" id="panel-register">
      <div class="msg" id="reg-msg"></div>
      <div class="form-group">
        <label>Profile Photo</label>
        <div class="avatar-upload">
          <img id="avatarPreview" class="avatar-preview" src="data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 100'%3E%3Ccircle cx='50' cy='40' r='20' fill='%23d1d5db'/%3E%3Cellipse cx='50' cy='80' rx='30' ry='20' fill='%23d1d5db'/%3E%3C/svg%3E">
          <label class="btn-upload" for="avatarInput">📷 Choose Photo</label>
          <input type="file" id="avatarInput" accept="image/*,.heic,.heif" style="display:none" onchange="previewAvatar(this)">
        </div>
      </div>
      <div class="form-group">
        <label>Full Name</label>
        <input type="text" id="r-name" placeholder="Your name">
      </div>
      <div class="form-group">
        <label>Email</label>
        <input type="email" id="r-email" placeholder="you@example.com">
      </div>
      <div class="form-group">
        <label>Password</label>
        <input type="password" id="r-pass" placeholder="At least 6 characters">
      </div>
      <button class="btn-primary" onclick="doRegister()">Create Account</button>
    </div>

    <!-- FORGOT PASSWORD -->
    <div class="form-panel" id="panel-forgot">
      <div class="msg" id="forgot-msg"></div>
      <p style="font-size:.85rem;color:#6b7280;margin-bottom:16px">Enter your email to receive an OTP.</p>
      <div class="form-group">
        <label>Email</label>
        <input type="email" id="f-email" placeholder="you@example.com">
      </div>
      <button class="btn-primary" onclick="sendOTP()">Send OTP</button>

      <div class="otp-section" id="otpSection" style="margin-top:20px">
        <div class="form-group">
          <label>OTP Code</label>
          <input type="text" id="f-otp" placeholder="6-digit code" maxlength="6">
        </div>
        <div class="form-group">
          <label>New Password</label>
          <input type="password" id="f-newpass" placeholder="New password">
        </div>
        <button class="btn-primary" onclick="resetPass()">Reset Password</button>
      </div>
      <p style="margin-top:16px;font-size:.85rem;text-align:center"><a href="#" onclick="switchTab('login')" style="color:#6366f1">← Back to Login</a></p>
    </div>
  </div>
</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/heic2any/0.0.4/heic2any.min.js"></script>
<script>
function switchTab(t) {
  document.querySelectorAll('.form-panel').forEach(p => p.classList.remove('active'));
  document.querySelectorAll('.tab-btn').forEach(b => b.classList.remove('active'));
  document.getElementById('panel-' + t).classList.add('active');
  if (t === 'login') document.querySelector('.tab-btn:nth-child(1)').classList.add('active');
  else if (t === 'register') document.querySelector('.tab-btn:nth-child(2)').classList.add('active');
}

function showMsg(id, msg, type) {
  const el = document.getElementById(id);
  el.className = 'msg ' + type;
  el.textContent = msg;
}

async function previewAvatar(input) {
  let file = input.files[0];
  if (!file) return;
  if (file.type === 'image/heic' || file.type === 'image/heif' || file.name.toLowerCase().endsWith('.heic')) {
    file = await heic2any({ blob: file, toType: 'image/jpeg', quality: 0.8 });
  }
  const reader = new FileReader();
  reader.onload = e => document.getElementById('avatarPreview').src = e.target.result;
  reader.readAsDataURL(file);
}

async function compressImage(file, maxW = 1200, quality = 0.82) {
  if (file.type === 'image/heic' || file.type === 'image/heif' || file.name.toLowerCase().endsWith('.heic')) {
    file = await heic2any({ blob: file, toType: 'image/jpeg', quality: 0.82 });
  }
  return new Promise(resolve => {
    const img = new Image();
    const url = URL.createObjectURL(file);
    img.onload = () => {
      let w = img.width, h = img.height;
      if (w > maxW) { h = Math.round(h * maxW / w); w = maxW; }
      const canvas = document.createElement('canvas');
      canvas.width = w; canvas.height = h;
      canvas.getContext('2d').drawImage(img, 0, 0, w, h);
      canvas.toBlob(blob => resolve(blob), 'image/jpeg', quality);
      URL.revokeObjectURL(url);
    };
    img.src = url;
  });
}

async function doLogin() {
  const email = document.getElementById('l-email').value.trim();
  const pass = document.getElementById('l-pass').value;
  if (!email || !pass) return showMsg('login-msg', 'Fill all fields', 'error');
  const fd = new FormData();
  fd.append('action', 'login');
  fd.append('email', email);
  fd.append('password', pass);
  const r = await fetch('api.php', { method: 'POST', body: fd });
  const d = await r.json();
  if (d.success) window.location.href = 'index.php';
  else showMsg('login-msg', d.error || 'Login failed', 'error');
}

async function doRegister() {
  const name = document.getElementById('r-name').value.trim();
  const email = document.getElementById('r-email').value.trim();
  const pass = document.getElementById('r-pass').value;
  const avatarInput = document.getElementById('avatarInput');
  if (!name || !email || !pass) return showMsg('reg-msg', 'Fill all fields', 'error');
  if (pass.length < 6) return showMsg('reg-msg', 'Password min 6 chars', 'error');
  const fd = new FormData();
  fd.append('action', 'register');
  fd.append('name', name);
  fd.append('email', email);
  fd.append('password', pass);
  if (avatarInput.files[0]) {
    const compressed = await compressImage(avatarInput.files[0], 400, 0.85);
    fd.append('avatar', compressed, 'avatar.jpg');
  }
  const r = await fetch('api.php', { method: 'POST', body: fd });
  const d = await r.json();
  if (d.success) window.location.href = 'index.php';
  else showMsg('reg-msg', d.error || 'Registration failed', 'error');
}

async function sendOTP() {
  const email = document.getElementById('f-email').value.trim();
  if (!email) return showMsg('forgot-msg', 'Enter email', 'error');
  const fd = new FormData();
  fd.append('action', 'send_otp');
  fd.append('email', email);
  const r = await fetch('api.php', { method: 'POST', body: fd });
  const d = await r.json();
  if (d.success) {
    showMsg('forgot-msg', 'OTP sent to your email', 'success');
    document.getElementById('otpSection').classList.add('show');
  } else showMsg('forgot-msg', d.error || 'Failed', 'error');
}

async function resetPass() {
  const email = document.getElementById('f-email').value.trim();
  const otp = document.getElementById('f-otp').value.trim();
  const pass = document.getElementById('f-newpass').value;
  if (!otp || !pass) return showMsg('forgot-msg', 'Fill all fields', 'error');
  const fd = new FormData();
  fd.append('action', 'reset_password');
  fd.append('email', email);
  fd.append('otp', otp);
  fd.append('password', pass);
  const r = await fetch('api.php', { method: 'POST', body: fd });
  const d = await r.json();
  if (d.success) { showMsg('forgot-msg', 'Password reset! Logging in...', 'success'); setTimeout(() => switchTab('login'), 1500); }
  else showMsg('forgot-msg', d.error || 'Failed', 'error');
}

document.addEventListener('keydown', e => {
  if (e.key === 'Enter') {
    const active = document.querySelector('.form-panel.active').id;
    if (active === 'panel-login') doLogin();
    else if (active === 'panel-register') doRegister();
  }
});
</script>
</body>
</html>
