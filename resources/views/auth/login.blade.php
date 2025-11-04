@section('head')
  <script>window.IS_LOGIN_PAGE = true;</script>
@endsection
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Sign in – Cannabest POS</title>
    @vite(['resources/css/app.css','resources/js/app.js'])
    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
</head>
<body class="min-h-screen bg-gray-50 flex items-center justify-center">
<div class="w-full max-w-sm bg-white p-6 rounded-xl shadow" x-data="loginForm()">
    <h1 class="text-xl font-semibold mb-1">Sign in</h1>
    <p class="text-sm text-gray-500 mb-6">Use your admin credentials</p>

    <form @submit.prevent="submit">
        <label class="block text-sm mb-1">Email</label>
        <input type="email" x-model="email" class="w-full mb-3 px-3 py-2 border rounded-lg" required autofocus>

        <label class="block text-sm mb-1">Password</label>
        <input type="password" x-model="password" class="w-full mb-4 px-3 py-2 border rounded-lg" required>

        <button type="submit" class="w-full py-2 bg-cannabis-green text-white rounded-lg hover:bg-green-600">Sign in</button>
    </form>

    <p class="text-sm text-red-600 mt-3" x-text="error" x-show="error"></p>
</div>

<script>
function loginForm() {
  return {
    email: '',
    password: '',
    error: '',
    async submit() {
      this.error = '';
      try {
        const r = await fetch('/api/auth/login', {
          method: 'POST',
          headers: {'Content-Type': 'application/json','Accept':'application/json'},
          body: JSON.stringify({ email: this.email, password: this.password })
        });
        const data = await r.json().catch(() => ({}));
        if (!r.ok) {
          this.error = data.message || 'Login failed';
          return;
        }
        localStorage.setItem('api_token', data.token);
        // Redirect to app
        window.location.assign('/pos');
      } catch (e) {
        this.error = 'Network error';
      }
    }
  }
}
</script>
</body>
</html>
