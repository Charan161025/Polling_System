<?php

$db = new PDO('sqlite:' . __DIR__ . '/../database/database.sqlite');
$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);


if ($uri === '/') {
    include __DIR__ . '/../resources/views/login.blade.php';
    exit;
}

if ($uri === '/login' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $stmt = $db->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->execute([$_POST['email']]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user && password_verify($_POST['password'], $user['password'])) {
        header("Location: /polls");
        exit;
    }
    echo "Invalid login";
    exit;
}

if ($uri === '/polls') {
    $polls = $db->query("SELECT * FROM polls")->fetchAll(PDO::FETCH_ASSOC);
    include __DIR__ . '/../resources/views/polls.blade.php';
    exit;
}

if (preg_match('#^/poll/(\d+)$#', $uri, $m)) {
    $pollId = $m[1];
    $poll = $db->query("SELECT * FROM polls WHERE id=$pollId")->fetch();
    $options = $db->query("SELECT * FROM options WHERE poll_id=$pollId")->fetchAll();
    include __DIR__ . '/../resources/views/poll_view.blade.php';
    exit;
}

if ($uri === '/vote' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $ip = $_SERVER['REMOTE_ADDR'];

    $stmt = $db->prepare("SELECT COUNT(*) FROM votes WHERE poll_id=? AND ip_address=?");
    $stmt->execute([$_POST['poll_id'], $ip]);

    if ($stmt->fetchColumn() > 0) {
        echo json_encode(['error' => 'Already voted']);
        exit;
    }

    $stmt = $db->prepare("INSERT INTO votes (poll_id, option_id, ip_address, voted_at)
                           VALUES (?, ?, ?, datetime('now'))");
    $stmt->execute([$_POST['poll_id'], $_POST['option_id'], $ip]);

    echo json_encode(['success' => true]);
    exit;
}

if (preg_match('#^/results/(\d+)$#', $uri, $m)) {
    $pollId = $m[1];
    $data = $db->query("
        SELECT options.option_text, COUNT(votes.id) total
        FROM options
        LEFT JOIN votes ON options.id = votes.option_id
        WHERE options.poll_id = $pollId
        GROUP BY options.id
    ")->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode($data);
    exit;
}

http_response_code(404);
echo "404 Not Found";
