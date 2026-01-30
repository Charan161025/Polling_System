@extends('layout')

@section('content')
<h2>Login</h2>

<div id="msg" style="color:red;"></div>

<input type="email" id="email" placeholder="Email"><br><br>
<input type="password" id="password" placeholder="Password"><br><br>

<button onclick="login()">Login</button>
@endsection
