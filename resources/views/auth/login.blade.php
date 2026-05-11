<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Laras Laundry</title>
    <style>
        body { background: #0d1117; color: white; font-family: 'Inter', sans-serif; display: flex; justify-content: center; align-items: center; min-height: 100vh; margin: 0; }
        .login-card { background: #161b22; padding: 40px; border-radius: 12px; border: 1px solid #30363d; width: 350px; box-shadow: 0 10px 30px rgba(0,0,0,0.5); text-align: center; }
        h2 { color: #00d4aa; margin-bottom: 10px; font-size: 24px; font-family: 'Syne', sans-serif; cursor: default; user-select: none; }
        p { color: #8b949e; font-size: 14px; margin-bottom: 25px; }
        input { width: 100%; padding: 12px 15px; margin-bottom: 15px; background: #0d1117; border: 1px solid #30363d; border-radius: 8px; color: white; box-sizing: border-box; outline: none; }
        input:focus { border-color: #00d4aa; }
        button { width: 100%; padding: 12px; background: #00d4aa; border: none; border-radius: 8px; color: #0d1117; font-weight: bold; cursor: pointer; transition: 0.3s; margin-top: 5px; }
        button:hover { background: #00b894; transform: translateY(-2px); }
        .error { color: #ff7b72; font-size: 13px; margin-bottom: 15px; background: rgba(255, 123, 114, 0.1); padding: 10px; border-radius: 6px; border: 1px solid #ff7b72; }
        .success { color: #00d4aa; font-size: 13px; margin-bottom: 15px; background: rgba(0, 212, 170, 0.1); padding: 10px; border-radius: 6px; border: 1px solid #00d4aa; }
        #forgot-form { display: none; margin-top: 20px; border-top: 1px dashed #30363d; padding-top: 20px; }
    </style>
</head>
<body>
    <div class="login-card">
        <h2 id="logo-click">🧺 LARAS LAUNDRY</h2>
        <p>Silakan masuk ke akun Anda</p>

        @if(session('error'))
            <div class="error">{{ session('error') }}</div>
        @endif

        @if(session('success_logout'))
            <div class="success">{{ session('success_logout') }}</div>
        @endif

        @if(session('success_reset'))
            <div class="success">{{ session('success_reset') }}</div>
        @endif

        @if(session('error_reset'))
            <div class="error">{{ session('error_reset') }}</div>
        @endif

        <form action="{{ route('login.post') }}" method="POST" id="main-login">
            @csrf
            <input type="text" name="username" placeholder="Username" required autocomplete="off">
            <input type="password" name="password" placeholder="Password" required>
            <button type="submit">MASUK</button>
        </form>

        <div id="forgot-form">
            <h4 style="margin:0 0 15px 0; font-size:14px; color:#ff9f43;">Owner Recovery System</h4>
            <form action="{{ route('login.reset') }}" method="POST">
                @csrf
                <input type="text" name="username_reset" placeholder="Username Owner" required>
                <input type="password" name="kode_rahasia" placeholder="Kode Kunci" required>
                <input type="password" name="new_password" placeholder="Password Baru" required>
                <button type="submit" style="background:#ff9f43;">RESET PASSWORD</button>
                <a href="javascript:void(0)" onclick="hideForgot()" style="color:#8b949e; font-size:12px; display:block; margin-top:15px; text-decoration:none;">Batal</a>
            </form>
        </div>
    </div>

    <script>
        let clickCount = 0;
        const logo = document.getElementById('logo-click');
        logo.addEventListener('click', function() {
            clickCount++;
            if (clickCount === 3) {
                showForgot();
                clickCount = 0;
            }
        });
        function showForgot() {
            document.getElementById('main-login').style.display = 'none';
            document.getElementById('forgot-form').style.display = 'block';
        }
        function hideForgot() {
            document.getElementById('main-login').style.display = 'block';
            document.getElementById('forgot-form').style.display = 'none';
        }
    </script>
</body>
</html>
