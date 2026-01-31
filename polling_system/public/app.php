<?php
session_start();
$db = new PDO('sqlite:' . __DIR__ . '/../database/database.sqlite');
$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);


function view($template, $data = []) {
    extract($data);
    ob_start();
    include __DIR__ . '/../resources/views/' . $template . '.blade.php';
    $content = ob_get_clean();
    
    // Process @extends and @section
    if (preg_match('/@extends\([\'"](.+?)[\'"]\)/', $content, $extends)) {
        $layout = $extends[1];
        
        
        preg_match('/@section\([\'"]content[\'"]\)(.+?)@endsection/s', $content, $section);
        $sectionContent = isset($section[1]) ? trim($section[1]) : '';
        
        
        ob_start();
        include __DIR__ . '/../resources/views/' . $layout . '.blade.php';
        $layoutContent = ob_get_clean();
        
        
        $content = preg_replace('/@yield\([\'"]content[\'"]\)/', $sectionContent, $layoutContent);
    }
    
   
    $content = preg_replace_callback('/@foreach\((.+?) as (.+?)\)(.+?)@endforeach/s', function($matches) use ($data) {
        $array = null;
        eval('$array = ' . $matches[1] . ';');
        $varName = trim($matches[2]);
        $template = $matches[3];
        $output = '';
        
        if (is_array($array)) {
            foreach ($array as $item) {
                $temp = $template;
                // Replace variables like {{ $p['id'] }}
                $temp = preg_replace_callback('/\{\{\s*' . preg_quote($varName) . '\[[\'"](.*?)[\'"]\]\s*\}\}/', function($m) use ($item) {
                    return isset($item[$m[1]]) ? htmlspecialchars($item[$m[1]]) : '';
                }, $temp);
                $output .= $temp;
            }
        }
        return $output;
    }, $content);
    
  
    $content = preg_replace_callback('/\{\{\s*\$(\w+)\[[\'"](.*?)[\'"]\]\s*\}\}/', function($matches) use ($data) {
        $var = $matches[1];
        $key = $matches[2];
        return isset($data[$var][$key]) ? htmlspecialchars($data[$var][$key]) : '';
    }, $content);
    
    return $content;
}

$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);


if ($uri === '/' && $_SERVER['REQUEST_METHOD'] === 'GET') {
    echo view('login');
    exit;
}


if ($uri === '/login' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    header('Content-Type: application/json');
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
    header('Location: /');
    exit;
}


if ($uri === '/polls') {
    $polls = $db->query("SELECT * FROM polls WHERE status='active'")
                ->fetchAll(PDO::FETCH_ASSOC);
    echo view('polls', ['polls' => $polls]);
    exit;
}


if (preg_match('#^/poll/(\d+)$#', $uri, $m)) {
    $pollId = (int)$m[1];
    $poll = $db->query("SELECT * FROM polls WHERE id=$pollId")->fetch(PDO::FETCH_ASSOC);
    $options = $db->query("SELECT * FROM options WHERE poll_id=$pollId")
                  ->fetchAll(PDO::FETCH_ASSOC);
    echo view('poll_view', ['poll' => $poll, 'options' => $options]);
    exit;
}


if ($uri === '/vote' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    header('Content-Type: application/json');
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
    header('Content-Type: application/json');
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
        "SELECT * FROM vote_history ORDER BY timestamp DESC"
    )->fetchAll(PDO::FETCH_ASSOC);
    echo view('admin', ['votes' => $votes, 'history' => $history]);
    exit;
}


if ($uri === '/release' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    header('Content-Type: application/json');
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
