@extends('layout')

@section('content')
<h2>Login</h2>

<div id="error" style="color:red;"></div>

<form id="loginForm">
  @csrf
  <input type="email" name="email" placeholder="Email" required><br><br>
  <input type="password" name="password" placeholder="Password" required><br><br>
  <button type="submit">Login</button>
</form>
@endsection
