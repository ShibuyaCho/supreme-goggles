<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Verify Email</title>
  <link rel="stylesheet" href="/css/pos-enhancements.css" />
</head>
<body class="bg-gray-50">
  <div class="min-h-screen flex items-center justify-center p-6">
    <div class="bg-white shadow rounded-lg p-8 max-w-md w-full text-center">
      <h1 class="text-2xl font-semibold mb-2">Verify your email</h1>
      <p class="text-gray-600 mb-6">We have sent a verification link to your email address. Click the link to verify your account.</p>
      <button id="resend" class="px-4 py-2 rounded bg-cannabis-green text-white">Resend verification email</button>
      <p id="msg" class="text-sm text-gray-600 mt-4"></p>
    </div>
  </div>
  <script src="/public/lib/axios/axios.min.js"></script>
  <script>
    (function(){
      const btn = document.getElementById('resend');
      const msg = document.getElementById('msg');
      btn && btn.addEventListener('click', async function(){
        btn.disabled = true;
        msg.textContent = '';
        try {
          const res = await fetch('/api/auth/email/verification-notification', { method: 'POST', headers: { 'Accept': 'application/json', 'Authorization': (window.axios?.defaults?.headers?.common?.Authorization || '') } });
          if (res.ok) { msg.textContent = 'Verification email sent.'; }
          else { const j = await res.json().catch(() => ({})); msg.textContent = j?.message || j?.error || 'Failed to send email.'; }
        } catch(e){ msg.textContent = 'Failed to send email.'; }
        btn.disabled = false;
      });
    })();
  </script>
</body>
</html>
