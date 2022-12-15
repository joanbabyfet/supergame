<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
</head>
<body>
<p>姓名：【 {{ $realname ?? '-' }} 】</p>
<p>账号：【 {{ $username ?? '-' }} 】</p>
<p>验证码：【 {{ $code ?? '-' }} 】</p>
</body>
</html>
