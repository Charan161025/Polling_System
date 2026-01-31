@extends('layout')
@section('content')
<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h4>Login</h4>
                </div>
                <div class="card-body">
                    <form id="loginForm" onsubmit="login(); return false;">
                        <div class="mb-3">
                            <label>Email</label>
                            <input type="email" id="email" class="form-control" value="" required autocomplete="email">
                        </div>
                        <div class="mb-3">
                            <label>Password</label>
                            <input type="password" id="password" class="form-control" value="" required autocomplete="current-password">
                        </div>
                        <button type="submit" class="btn btn-primary">Login</button>
                        <div id="msg" class="text-danger mt-2"></div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
