<?php
session_start();

$db = new PDO('sqlite:' . __DIR__ . '/../database/database.sqlite');
$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

function view($template, $data = []) {
    extract($data);
    $viewPath = __DIR__ . '/../resources/views/' . $template . '.blade.php';
    
    if (!file_exists($viewPath)) return "View not found: $template";
    
    ob_start();
    include $viewPath;
    $content = ob_get_clean();
    
    if (preg_match('/@extends\([\'"](.+?)[\'"]\)/', $content, $extends)) {
        $layout = $extends[1];
        preg_match('/@section\([\'"]content[\'"]\)(.*?)@endsection/s', $content, $section);
        $sectionContent = isset($section[1]) ? trim($section[1]) : '';
        
        ob_start();
        include __DIR__ . '/../resources/views/' . $layout . '.blade.php';
        $layoutContent = ob_get_clean();
        $content = preg_replace('/@yield\([\'"]content[\'"]\)/', $sectionContent, $layoutContent);
    }
    
    $content = preg_replace_callback('/@foreach\s*\(\s*\$(.+?)\s+as\s+\$(.+?)\s*\)(.*?)@endforeach/s', function($matches) use ($data) {
        $arrayKey = trim($matches[1]); // "polls"
        $itemName = trim($matches[2]);  // "p"
        $innerTemplate = $matches[3];
        
        $targetArray = isset($data[$arrayKey]) ? $data[$arrayKey] : [];
        $output = '';
        
        if (is_array($targetArray)) {
            foreach ($targetArray as $item) {
                $temp = $innerTemplate;
                // Matches {{ $p['key'] }} or {{{ $p['key'] }}}
                $pattern = '/\{{2,3}\s*\$' . preg_quote($itemName) . '\[[\'"](.*?)[\'"]\]\s*\}{2,3}/';
                $temp = preg_replace_callback($pattern, function($m) use ($item) {
                    return isset($item[$m[1]]) ? htmlspecialchars($item[$m[1]]) : '';
                }, $temp);
                $output .= $temp;
            }
        }
        return $output;
    }, $content);

    $content = preg_replace_callback('/\{{2,3}\s*\$(\w+)\[[\'"](.*?)[\'"]\]\s*\}{2,3}/', function($m) use ($data) {
        return isset($data[$m[1]][$m[2]]) ? htmlspecialchars($data[$m[1]][$m[2]]) : '';
    }, $content);
    
    return $content;
}

$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

if ($uri === '/' || $uri === '/login') {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        header('Content-Type: application/json');
        $stmt = $db->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->execute([$_POST['email'] ?? '']);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($user && password_verify($_POST['password'] ?? '', $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false, 'error' => 'Invalid email or password']);
        }
        exit;
    }
    echo view('login');
    exit;
}

if (!isset($_SESSION['user_id'])) {
    header('Location: /');
    exit;
}


if ($uri === '/polls') {
    $polls = $db->query("SELECT * FROM polls WHERE status='active'")->fetchAll(PDO::FETCH_ASSOC);
    echo view('polls', ['polls' => $polls]);
    exit;
}


if (preg_match('#^/poll/(\d+)$#', $uri, $m)) {
    $pollId = (int)$m[1];
    $poll = $db->query("SELECT * FROM polls WHERE id=$pollId")->fetch(PDO::FETCH_ASSOC);
    $options = $db->query("SELECT * FROM options WHERE poll_id=$pollId")->fetchAll(PDO::FETCH_ASSOC);
    echo view('poll_view', ['poll' => $poll, 'options' => $options]);
    exit;
}

if ($uri === '/vote' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    header('Content-Type: application/json');
    $ip = $_SERVER['REMOTE_ADDR'];
    $pollId = (int)$_POST['poll_id'];
    $optionId = (int)$_POST['option_id'];
    
    $stmt = $db->prepare("SELECT COUNT(*) FROM votes WHERE poll_id=? AND ip_address=?");
    $stmt->execute([$pollId, $ip]);
    
    if ($stmt->fetchColumn() > 0) {
        echo json_encode(['error' => 'You already voted from this IP']);
        exit;
    }
    
    $db->prepare("INSERT INTO votes (poll_id, option_id, ip_address, voted_at) VALUES (?, ?, ?, datetime('now'))")->execute([$pollId, $optionId, $ip]);
    echo json_encode(['success' => true]);
    exit;
}

http_response_code(404);
echo "404 Not Found";
