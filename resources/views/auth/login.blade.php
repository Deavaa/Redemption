<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Login - School of Redemption</title>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.min.css">
<style>
*{margin:0;padding:0;box-sizing:border-box;}
body{min-height:100vh;display:flex;align-items:center;justify-content:center;background:linear-gradient(135deg,#2c3e50,#3498db);font-family:Segoe UI,Tahoma,sans-serif;}
.login-box{background:#fff;padding:40px;border-radius:12px;box-shadow:0 10px 40px rgba(0,0,0,.3);width:400px;max-width:90%;}
.login-box h2{text-align:center;color:#2c3e50;margin-bottom:5px;}
.login-box p{text-align:center;color:#888;margin-bottom:25px;font-size:14px;}
.login-box .icon{text-align:center;font-size:50px;color:#3498db;margin-bottom:15px;}
.form-group{margin-bottom:18px;}
.form-group label{display:block;font-weight:600;margin-bottom:5px;color:#555;font-size:14px;}
.form-group input{width:100%;padding:12px;border:1px solid #ddd;border-radius:6px;font-size:15px;transition:border .3s;}
.form-group input:focus{outline:none;border-color:#3498db;box-shadow:0 0 0 3px rgba(52,152,219,.15);}
.btn-login{width:100%;padding:12px;background:linear-gradient(135deg,#3498db,#2c3e50);color:#fff;border:none;border-radius:6px;font-size:16px;font-weight:600;cursor:pointer;transition:opacity .3s;}
.btn-login:hover{opacity:.9;}
.alert{background:#f8d7da;color:#721c24;padding:10px 15px;border-radius:5px;margin-bottom:15px;font-size:14px;}
</style>
</head>
<body>
<div class="login-box">
  <div class="icon"><i class="bi bi-mortarboard-fill"></i></div>
  <h2>School of Redemption</h2>
  <p>Sign in to your account</p>
  @if(session('error'))
  <div class="alert">{{ session('error') }}</div>
  @endif
  @if($errors->any())
  <div class="alert">{{ $errors->first('email') }}</div>
  @endif
  <form method="POST" action="{{ route('login') }}">
    @csrf
    <div class="form-group">
      <label><i class="bi bi-person"></i> Email / ID Number / Phone</label>
      <input type="text" name="login" value="{{ old('login') }}" required autofocus placeholder="Enter your email, ID number, or phone">
    </div>
    <div class="form-group">
      <label><i class="bi bi-lock"></i> Password</label>
      <input type="password" name="password" required placeholder="Enter your password">
    </div>
    <button type="submit" class="btn-login"><i class="bi bi-box-arrow-in-right"></i> Login</button>
  </form>
</div>
</body>
</html>
