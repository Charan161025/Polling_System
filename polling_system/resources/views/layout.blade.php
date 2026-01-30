
<!DOCTYPE html>
<html>
<head>
<title>Polling</title>
<meta name="csrf-token" content="{{ csrf_token() }}">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</head>
<body class="container mt-4">
<div id="content">@yield('content')</div>
<script src="/js/app.js"></script>
</body>
</html>
