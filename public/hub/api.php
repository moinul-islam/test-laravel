<?php
require_once 'config.php';
header('Content-Type: application/json');

$action = $_POST['action'] ?? $_GET['action'] ?? '';

// ─── Public actions ─────────────────────────────────────────────
if ($action === 'login') {
    $email = trim($_POST['email'] ?? '');
    $pass  = $_POST['password'] ?? '';
    if (!$email || !$pass) jsonResponse(['error' => 'Fill all fields'], 400);
    $db = getDB();
    $stmt = $db->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch();
    if (!$user || !password_verify($pass, $user['password'])) {
        jsonResponse(['error' => 'Invalid email or password'], 401);
    }
    $_SESSION['user_id'] = $user['id'];
    $_SESSION['user'] = ['id'=>$user['id'],'name'=>$user['name'],'avatar'=>$user['avatar'],
        'loc_country'=>$user['loc_country'],'loc_state'=>$user['loc_state'],
        'loc_city'=>$user['loc_city'],'loc_area'=>$user['loc_area']];
    jsonResponse(['success' => true]);
}

if ($action === 'register') {
    $name  = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $pass  = $_POST['password'] ?? '';
    if (!$name || !$email || !$pass) jsonResponse(['error' => 'Fill all fields'], 400);
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) jsonResponse(['error' => 'Invalid email'], 400);
    if (strlen($pass) < 6) jsonResponse(['error' => 'Password min 6 chars'], 400);
    $db = getDB();
    $check = $db->prepare("SELECT id FROM users WHERE email = ?");
    $check->execute([$email]);
    if ($check->fetch()) jsonResponse(['error' => 'Email already registered'], 409);

    $avatarPath = null;
    if (!empty($_FILES['avatar']['tmp_name'])) {
        $ext = 'jpg';
        $filename = uniqid('av_') . '.' . $ext;
        move_uploaded_file($_FILES['avatar']['tmp_name'], UPLOAD_DIR . $filename);
        $avatarPath = $filename;
    }

    $hash = password_hash($pass, PASSWORD_DEFAULT);
    $stmt = $db->prepare("INSERT INTO users (name, email, password, avatar) VALUES (?, ?, ?, ?)");
    $stmt->execute([$name, $email, $hash, $avatarPath]);
    $id = $db->lastInsertId();
    $_SESSION['user_id'] = $id;
    $_SESSION['user'] = ['id'=>$id,'name'=>$name,'avatar'=>$avatarPath,
        'loc_country'=>null,'loc_state'=>null,'loc_city'=>null,'loc_area'=>null];
    jsonResponse(['success' => true]);
}

if ($action === 'send_otp') {
    $email = trim($_POST['email'] ?? '');
    if (!$email) jsonResponse(['error' => 'Enter email'], 400);
    $db = getDB();
    $check = $db->prepare("SELECT id FROM users WHERE email = ?");
    $check->execute([$email]);
    if (!$check->fetch()) jsonResponse(['error' => 'Email not found'], 404);
    $otp = str_pad(rand(100000, 999999), 6, '0', STR_PAD_LEFT);
    $expires = date('Y-m-d H:i:s', strtotime('+10 minutes'));
    $db->prepare("INSERT INTO otps (email, otp, expires_at) VALUES (?, ?, ?)")->execute([$email, $otp, $expires]);
    sendOTP($email, $otp);
    jsonResponse(['success' => true]);
}

if ($action === 'reset_password') {
    $email = trim($_POST['email'] ?? '');
    $otp   = trim($_POST['otp'] ?? '');
    $pass  = $_POST['password'] ?? '';
    if (!$email || !$otp || !$pass) jsonResponse(['error' => 'Fill all fields'], 400);
    if (strlen($pass) < 6) jsonResponse(['error' => 'Password min 6 chars'], 400);
    $db = getDB();
    $stmt = $db->prepare("SELECT * FROM otps WHERE email=? AND otp=? AND used=0 AND expires_at > NOW() ORDER BY id DESC LIMIT 1");
    $stmt->execute([$email, $otp]);
    if (!$stmt->fetch()) jsonResponse(['error' => 'Invalid or expired OTP'], 400);
    $db->prepare("UPDATE otps SET used=1 WHERE email=? AND otp=?")->execute([$email, $otp]);
    $hash = password_hash($pass, PASSWORD_DEFAULT);
    $db->prepare("UPDATE users SET password=? WHERE email=?")->execute([$hash, $email]);
    jsonResponse(['success' => true]);
}

// ─── Authenticated actions ───────────────────────────────────────
if (!isLoggedIn()) jsonResponse(['error' => 'Unauthorized'], 401);
$uid = $_SESSION['user_id'];
$db  = getDB();

// Update location
if ($action === 'update_location') {
    $country = trim($_POST['country'] ?? '');
    $state   = trim($_POST['state'] ?? '');
    $city    = trim($_POST['city'] ?? '');
    $area    = trim($_POST['area'] ?? '');
    $db->prepare("UPDATE users SET loc_country=?, loc_state=?, loc_city=?, loc_area=? WHERE id=?")
       ->execute([$country ?: null, $state ?: null, $city ?: null, $area ?: null, $uid]);
    $_SESSION['user']['loc_country'] = $country ?: null;
    $_SESSION['user']['loc_state']   = $state ?: null;
    $_SESSION['user']['loc_city']    = $city ?: null;
    $_SESSION['user']['loc_area']    = $area ?: null;
    jsonResponse(['success' => true]);
}

// Get posts
if ($action === 'get_posts') {
    $country = trim($_POST['country'] ?? '');
    $state   = trim($_POST['state'] ?? '');
    $city    = trim($_POST['city'] ?? '');
    $profile_uid = intval($_POST['profile_uid'] ?? 0);
    $page    = max(1, intval($_POST['page'] ?? 1));
    $limit   = 15;
    $offset  = ($page - 1) * $limit;

    $where = ['1=1'];
    $params = [];

    if ($profile_uid > 0) {
        $where[] = 'p.user_id = ?';
        $params[] = $profile_uid;
    } else {
        if ($city) { $where[] = 'p.loc_city = ?'; $params[] = $city; }
        elseif ($state) { $where[] = 'p.loc_state = ?'; $params[] = $state; }
        elseif ($country) { $where[] = 'p.loc_country = ?'; $params[] = $country; }
    }

    $sql = "SELECT p.*, u.name AS uname, u.avatar AS uavatar,
        (SELECT COUNT(*) FROM likes l WHERE l.post_id = p.id) AS like_count,
        (SELECT COUNT(*) FROM comments c WHERE c.post_id = p.id) AS comment_count,
        (SELECT COUNT(*) FROM likes l2 WHERE l2.post_id = p.id AND l2.user_id = ?) AS liked
        FROM posts p JOIN users u ON u.id = p.user_id
        WHERE " . implode(' AND ', $where) . "
        ORDER BY p.created_at DESC LIMIT $limit OFFSET $offset";

    $stmt = $db->prepare($sql);
    $stmt->execute(array_merge([$uid], $params));
    $posts = $stmt->fetchAll();

    foreach ($posts as &$post) {
        $post['time_ago'] = timeAgo($post['created_at']);
        $post['liked'] = (bool)$post['liked'];
        $loc_parts = array_filter([$post['loc_city'] ?: $post['loc_state'] ?: $post['loc_country'], $post['loc_area']]);
        $post['location_label'] = implode(', ', $loc_parts);
    }

    jsonResponse(['posts' => $posts, 'page' => $page]);
}

// Create post
if ($action === 'create_post') {
    $content = trim($_POST['content'] ?? '');
    if (!$content) jsonResponse(['error' => 'Write something'], 400);
    $country = trim($_POST['country'] ?? '');
    $state   = trim($_POST['state'] ?? '');
    $city    = trim($_POST['city'] ?? '');
    $area    = trim($_POST['area'] ?? '');

    $imagePath = null;
    if (!empty($_FILES['image']['tmp_name'])) {
        $ext = 'jpg';
        $filename = uniqid('post_') . '.' . $ext;
        move_uploaded_file($_FILES['image']['tmp_name'], UPLOAD_DIR . $filename);
        $imagePath = $filename;
    }

    $stmt = $db->prepare("INSERT INTO posts (user_id, content, image, loc_country, loc_state, loc_city, loc_area) VALUES (?,?,?,?,?,?,?)");
    $stmt->execute([$uid, $content, $imagePath, $country ?: null, $state ?: null, $city ?: null, $area ?: null]);
    jsonResponse(['success' => true, 'post_id' => $db->lastInsertId()]);
}

// Toggle like
if ($action === 'toggle_like') {
    $post_id = intval($_POST['post_id'] ?? 0);
    $check = $db->prepare("SELECT id FROM likes WHERE user_id=? AND post_id=?");
    $check->execute([$uid, $post_id]);
    if ($check->fetch()) {
        $db->prepare("DELETE FROM likes WHERE user_id=? AND post_id=?")->execute([$uid, $post_id]);
        $liked = false;
    } else {
        $db->prepare("INSERT INTO likes (user_id, post_id) VALUES (?,?)")->execute([$uid, $post_id]);
        $liked = true;
        // Notify post owner
        $owner = $db->prepare("SELECT user_id FROM posts WHERE id=?");
        $owner->execute([$post_id]);
        $row = $owner->fetch();
        if ($row && $row['user_id'] != $uid) {
            $db->prepare("INSERT INTO notifications (user_id, from_user_id, type, post_id) VALUES (?,?,?,?)")
               ->execute([$row['user_id'], $uid, 'like', $post_id]);
        }
    }
    $cnt = $db->prepare("SELECT COUNT(*) as c FROM likes WHERE post_id=?");
    $cnt->execute([$post_id]);
    $count = $cnt->fetch()['c'];
    jsonResponse(['liked' => $liked, 'count' => $count]);
}

// Get comments
if ($action === 'get_comments') {
    $post_id = intval($_GET['post_id'] ?? 0);
    $stmt = $db->prepare("SELECT c.*, u.name AS uname, u.avatar AS uavatar FROM comments c JOIN users u ON u.id = c.user_id WHERE c.post_id=? AND c.parent_id IS NULL ORDER BY c.created_at ASC");
    $stmt->execute([$post_id]);
    $comments = $stmt->fetchAll();
    foreach ($comments as &$c) {
        $c['time_ago'] = timeAgo($c['created_at']);
        // get replies
        $rs = $db->prepare("SELECT c2.*, u2.name AS uname, u2.avatar AS uavatar FROM comments c2 JOIN users u2 ON u2.id = c2.user_id WHERE c2.parent_id=? ORDER BY c2.created_at ASC");
        $rs->execute([$c['id']]);
        $replies = $rs->fetchAll();
        foreach ($replies as &$r) { $r['time_ago'] = timeAgo($r['created_at']); }
        $c['replies'] = $replies;
    }
    jsonResponse(['comments' => $comments]);
}

// Add comment or reply
if ($action === 'add_comment') {
    $post_id   = intval($_POST['post_id'] ?? 0);
    $parent_id = intval($_POST['parent_id'] ?? 0) ?: null;
    $content   = trim($_POST['content'] ?? '');
    if (!$content) jsonResponse(['error' => 'Empty comment'], 400);

    $stmt = $db->prepare("INSERT INTO comments (post_id, user_id, parent_id, content) VALUES (?,?,?,?)");
    $stmt->execute([$post_id, $uid, $parent_id, $content]);
    $comment_id = $db->lastInsertId();

    // Notify post owner
    $owner = $db->prepare("SELECT user_id FROM posts WHERE id=?");
    $owner->execute([$post_id]);
    $row = $owner->fetch();
    if ($row && $row['user_id'] != $uid) {
        $type = $parent_id ? 'reply' : 'comment';
        $db->prepare("INSERT INTO notifications (user_id, from_user_id, type, post_id, comment_id) VALUES (?,?,?,?,?)")
           ->execute([$row['user_id'], $uid, $type, $post_id, $comment_id]);
    }

    // Notify parent comment owner if reply
    if ($parent_id) {
        $powner = $db->prepare("SELECT user_id FROM comments WHERE id=?");
        $powner->execute([$parent_id]);
        $prow = $powner->fetch();
        if ($prow && $prow['user_id'] != $uid) {
            $db->prepare("INSERT INTO notifications (user_id, from_user_id, type, post_id, comment_id) VALUES (?,?,?,?,?)")
               ->execute([$prow['user_id'], $uid, 'reply', $post_id, $comment_id]);
        }
    }

    // Parse @mentions
    preg_match_all('/@(\w+)/', $content, $matches);
    foreach ($matches[1] as $mention) {
        $mu = $db->prepare("SELECT id FROM users WHERE name = ?");
        $mu->execute([$mention]);
        $mrow = $mu->fetch();
        if ($mrow && $mrow['id'] != $uid) {
            $db->prepare("INSERT INTO notifications (user_id, from_user_id, type, post_id, comment_id) VALUES (?,?,?,?,?)")
               ->execute([$mrow['id'], $uid, 'mention', $post_id, $comment_id]);
        }
    }

    $user = $_SESSION['user'];
    jsonResponse(['success' => true, 'comment' => [
        'id' => $comment_id, 'content' => $content, 'parent_id' => $parent_id,
        'uname' => $user['name'], 'uavatar' => $user['avatar'],
        'time_ago' => 'just now', 'replies' => []
    ]]);
}

// Get notifications
if ($action === 'get_notifications') {
    $stmt = $db->prepare("SELECT n.*, u.name AS from_name, u.avatar AS from_avatar FROM notifications n JOIN users u ON u.id = n.from_user_id WHERE n.user_id=? ORDER BY n.created_at DESC LIMIT 30");
    $stmt->execute([$uid]);
    $notifs = $stmt->fetchAll();
    foreach ($notifs as &$n) { $n['time_ago'] = timeAgo($n['created_at']); }
    $unread = $db->prepare("SELECT COUNT(*) as c FROM notifications WHERE user_id=? AND is_read=0");
    $unread->execute([$uid]);
    $ucount = $unread->fetch()['c'];
    jsonResponse(['notifications' => $notifs, 'unread' => $ucount]);
}

// Mark notifications read
if ($action === 'mark_notifications_read') {
    $db->prepare("UPDATE notifications SET is_read=1 WHERE user_id=?")->execute([$uid]);
    jsonResponse(['success' => true]);
}

// Get user profile
if ($action === 'get_profile') {
    $profile_uid = intval($_GET['uid'] ?? $uid);
    $stmt = $db->prepare("SELECT id, name, avatar, loc_country, loc_state, loc_city, loc_area, created_at FROM users WHERE id=?");
    $stmt->execute([$profile_uid]);
    $user = $stmt->fetch();
    if (!$user) jsonResponse(['error' => 'User not found'], 404);
    $cnt = $db->prepare("SELECT COUNT(*) as c FROM posts WHERE user_id=?");
    $cnt->execute([$profile_uid]);
    $user['post_count'] = $cnt->fetch()['c'];
    $user['is_own'] = ($profile_uid == $uid);
    jsonResponse(['user' => $user]);
}

// Update profile
if ($action === 'update_profile') {
    $name = trim($_POST['name'] ?? '');
    if (!$name) jsonResponse(['error' => 'Name required'], 400);
    $avatarPath = $_SESSION['user']['avatar'];
    if (!empty($_FILES['avatar']['tmp_name'])) {
        $filename = uniqid('av_') . '.jpg';
        move_uploaded_file($_FILES['avatar']['tmp_name'], UPLOAD_DIR . $filename);
        $avatarPath = $filename;
    }
    $db->prepare("UPDATE users SET name=?, avatar=? WHERE id=?")->execute([$name, $avatarPath, $uid]);
    $_SESSION['user']['name'] = $name;
    $_SESSION['user']['avatar'] = $avatarPath;
    jsonResponse(['success' => true, 'name' => $name, 'avatar' => $avatarPath]);
}

// Logout
if ($action === 'logout') {
    session_destroy();
    jsonResponse(['success' => true]);
}

jsonResponse(['error' => 'Unknown action'], 400);
