<!DOCTYPE html>
<html>
<head>
    <title>Login</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</head>
<body>

<h2>Login</h2>

<div id="msg" style="color:red;"></div>

<input type="email" id="email" placeholder="Email"><br><br>
<input type="password" id="password" placeholder="Password"><br><br>

<button onclick="login()">Login</button>

<script>
function login(){
  $.post('/login',{
    email: $('#email').val(),
    password: $('#password').val(),
    _token: $('meta[name="csrf-token"]').attr('content')
  }, function(res){
    if(res.success){
      window.location.href = '/polls';
    } else {
      $('#msg').text(res.error);
    }
  });
}
</script>

</body>
</html>
