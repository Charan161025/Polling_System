<?php

session_start();


$db = new PDO('sqlite:' . __DIR__ . '/../database/database.sqlite');
$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);


if ($uri === '/' && $_SERVER['REQUEST_METHOD'] === 'GET') {
    include __DIR__ . '/../resources/views/login.blade.php';
    exit;
}

if ($uri === '/login' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $stmt = $db->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->execute([$_POST['email'] ?? '']);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user && password_verify($_POST['password'] ?? '', $user['password'])) {
        $_SESSION['user_id'] = $user['id'];
        echo json_encode(['success' => true]);
    } else {
        echo json_encode([
            'success' => false,
            'error' => 'Invalid email or password'
        ]);
    }
    exit;
}


if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}


if ($uri === '/polls') {
    $polls = $db->query("SELECT * FROM polls WHERE status='active'")
                ->fetchAll(PDO::FETCH_ASSOC);
    include __DIR__ . '/../resources/views/polls.blade.php';
    exit;
}


if (preg_match('#^/poll/(\d+)$#', $uri, $m)) {
    $pollId = (int)$m[1];
    $poll = $db->query("SELECT * FROM polls WHERE id=$pollId")->fetch();
    $options = $db->query("SELECT * FROM options WHERE poll_id=$pollId")
                  ->fetchAll(PDO::FETCH_ASSOC);
    include __DIR__ . '/../resources/views/poll_view.blade.php';
    exit;
}


if ($uri === '/vote' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $ip = $_SERVER['REMOTE_ADDR'];
    $pollId = (int)$_POST['poll_id'];
    $optionId = (int)$_POST['option_id'];

    $stmt = $db->prepare(
        "SELECT COUNT(*) FROM votes WHERE poll_id=? AND ip_address=?"
    );
    $stmt->execute([$pollId, $ip]);

    if ($stmt->fetchColumn() > 0) {
        echo json_encode(['error' => 'You already voted from this IP']);
        exit;
    }

    $db->prepare(
        "INSERT INTO votes (poll_id, option_id, ip_address, voted_at)
         VALUES (?, ?, ?, datetime('now'))"
    )->execute([$pollId, $optionId, $ip]);

    $db->prepare(
        "INSERT INTO vote_history (poll_id, option_id, ip_address, action, timestamp)
         VALUES (?, ?, ?, 'voted', datetime('now'))"
    )->execute([$pollId, $optionId, $ip]);

    echo json_encode(['success' => true]);
    exit;
}


if (preg_match('#^/results/(\d+)$#', $uri, $m)) {
    $pollId = (int)$m[1];
    $data = $db->query("
        SELECT options.option_text, COUNT(votes.id) AS total
        FROM options
        LEFT JOIN votes ON options.id = votes.option_id
        WHERE options.poll_id = $pollId
        GROUP BY options.id
    ")->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode($data);
    exit;
}


if ($uri === '/admin') {
    $votes = $db->query("SELECT * FROM votes")->fetchAll(PDO::FETCH_ASSOC);
    $history = $db->query(
        "SELECT * FROM vote_history ORDER BY timestamp"
    )->fetchAll(PDO::FETCH_ASSOC);
    include __DIR__ . '/../resources/views/admin.blade.php';
    exit;
}


if ($uri === '/release' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $voteId = (int)$_POST['vote_id'];

    $vote = $db->query("SELECT * FROM votes WHERE id=$voteId")
               ->fetch(PDO::FETCH_ASSOC);

    if ($vote) {
        $db->exec("DELETE FROM votes WHERE id=$voteId");

        $db->prepare(
            "INSERT INTO vote_history (poll_id, option_id, ip_address, action, timestamp)
             VALUES (?, ?, ?, 'released', datetime('now'))"
        )->execute([
            $vote['poll_id'],
            $vote['option_id'],
            $vote['ip_address']
        ]);
    }

    echo json_encode(['success' => true]);
    exit;
}


http_response_code(404);
echo "404 Not Found";
