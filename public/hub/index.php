<?php
require_once 'config.php';
authRequired();
$user = $_SESSION['user'];
$db = getDB();
$stmt = $db->prepare("SELECT * FROM users WHERE id=?");
$stmt->execute([$user['id']]);
$dbUser = $stmt->fetch();
$_SESSION['user'] = ['id'=>$dbUser['id'],'name'=>$dbUser['name'],'avatar'=>$dbUser['avatar'],
    'loc_country'=>$dbUser['loc_country'],'loc_state'=>$dbUser['loc_state'],
    'loc_city'=>$dbUser['loc_city'],'loc_area'=>$dbUser['loc_area']];
$user = $_SESSION['user'];

function locLabel($u) {
    if ($u['loc_city']) return $u['loc_city'];
    if ($u['loc_state']) return $u['loc_state'];
    if ($u['loc_country']) return $u['loc_country'];
    return 'International';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Wihima</title>
<link rel="stylesheet" href="css/style.css">
</head>
<body>

<!-- NAVBAR -->
<nav class="navbar">
  <div class="nav-left">
    <a href="index.php" class="logo">Wihima</a>
  </div>
  <div class="nav-center">
    <button class="location-btn" id="navLocBtn" onclick="openLocationModal()">
      <span class="loc-icon">📍</span>
      <span id="navLocLabel"><?= htmlspecialchars(locLabel($user)) ?></span>
      <span class="loc-arrow">▾</span>
    </button>
  </div>
  <div class="nav-right">
    <button class="notif-btn" onclick="toggleNotifDropdown()">
      <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"/><path d="M13.73 21a2 2 0 0 1-3.46 0"/></svg>
      <span class="notif-badge" id="notifBadge" style="display:none">0</span>
    </button>
    <button class="user-btn" onclick="toggleUserMenu()">
      <img id="navAvatar" src="<?= $user['avatar'] ? 'uploads/'.htmlspecialchars($user['avatar']) : "data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 40 40'%3E%3Ccircle cx='20' cy='16' r='8' fill='%23d1d5db'/%3E%3Cellipse cx='20' cy='36' rx='14' ry='10' fill='%23d1d5db'/%3E%3C/svg%3E" ?>" alt="Me">
    </button>
    <div class="user-menu" id="userMenu">
      <div class="user-menu-header">
        <img src="<?= $user['avatar'] ? 'uploads/'.htmlspecialchars($user['avatar']) : "data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 40 40'%3E%3Ccircle cx='20' cy='16' r='8' fill='%23d1d5db'/%3E%3Cellipse cx='20' cy='36' rx='14' ry='10' fill='%23d1d5db'/%3E%3C/svg%3E" ?>" alt="">
        <div><strong><?= htmlspecialchars($user['name']) ?></strong><small><?= htmlspecialchars($user['loc_country'] ?? 'No location') ?></small></div>
      </div>
      <a href="#" class="menu-item" onclick="openCreatePost();closeMenus()">✏️ Create Post</a>
      <a href="#" class="menu-item" onclick="openProfile(<?= $user['id'] ?>);closeMenus()">👤 My Profile</a>
      <a href="#" class="menu-item" onclick="openNotifications();closeMenus()">🔔 Notifications</a>
      <div class="menu-divider"></div>
      <a href="#" class="menu-item danger" onclick="doLogout()">🚪 Logout</a>
    </div>
  </div>
</nav>

<!-- NOTIFICATION DROPDOWN -->
<div class="notif-dropdown" id="notifDropdown">
  <div class="notif-header">
    <h3>Notifications</h3>
    <button onclick="markAllRead()" class="mark-read-btn">Mark all read</button>
  </div>
  <div id="notifList" class="notif-list"><p class="empty-state">No notifications</p></div>
</div>

<!-- MAIN FEED -->
<main class="main-wrap">
  <!-- FEED VIEW -->
  <div class="feed-container" id="feedView">
    <div id="postsList"></div>
    <div id="loadMoreWrap" style="text-align:center;padding:20px;display:none">
      <button class="btn-load-more" onclick="loadPosts(true)">Load more</button>
    </div>
    <div id="feedLoader" class="loader-wrap"><div class="spinner"></div></div>
  </div>

  <!-- PROFILE VIEW -->
  <div class="feed-container" id="profileView" style="display:none">
    
    <div id="profilePostsList"></div>
    <div id="profileLoadMoreWrap" style="text-align:center;padding:20px;display:none">
      <button class="btn-load-more" onclick="loadProfilePosts(true)">Load more</button>
    </div>
  </div>
</main>


<!-- ═══ LOCATION MODAL ═══ -->
<div class="modal-overlay" id="locationModal" onclick="closeLocationModal(event)">
  <div class="modal-box" onclick="event.stopPropagation()">
    <div class="modal-header">
      <h3>📍 Choose Location</h3>
      <button class="modal-close" onclick="closeLocationModal()">✕</button>
    </div>
    <div class="modal-body">
     

      <div class="form-group">
        <label>Country</label>
        <select id="locCountry" onchange="onLocCountryChange('loc')">
          <option value="">🌍 International</option>
        </select>
      </div>

      <div class="form-group" id="locStateGroup" style="display:none">
        <label>State / Division</label>
        <select id="locState" onchange="onLocStateChange('loc')">
          <option value="">All States</option>
        </select>
      </div>

      <div class="form-group" id="locCityGroup" style="display:none">
        <label>City</label>
        <select id="locCity">
          <option value="">All Cities</option>
        </select>
      </div>

      <div class="form-group" id="locAreaGroup" style="display:none">
        <input type="text" id="locArea" placeholder="Neighborhood, area..." hidden>
      </div>

      <div class="modal-actions">
        <button class="btn-outline" onclick="clearLocation()">Clear</button>
        <button class="btn-primary" onclick="saveLocation()">Save & Apply</button>
      </div>
    </div>
  </div>
</div>

<!-- ═══ CREATE POST MODAL ═══ -->
<div class="modal-overlay" id="createPostModal" onclick="closeCreatePost(event)">
  <div class="modal-box post-modal" onclick="event.stopPropagation()">
    <div class="modal-header">
      <h3>✏️ Create Post</h3>
      <button class="modal-close" onclick="closeCreatePost()">✕</button>
    </div>
    <div class="modal-body">
      <div class="post-user-info">
        <img id="postAvatar" src="" alt="">
        <div>
          <strong><?= htmlspecialchars($user['name']) ?></strong>
          <div class="post-location-row">
            <span class="loc-icon-sm">📍</span>
            <span id="postLocLabel">International</span>
            <button class="btn-change-loc" onclick="openPostLocationChange()">Change</button>
          </div>
        </div>
      </div>

      <!-- Post location editor -->
      <div class="post-loc-editor" id="postLocEditor" style="display:none">
        <div class="form-group">
          <select id="pLocCountry" onchange="onLocCountryChange('p')">
            <option value="">International</option>
          </select>
        </div>
        <div class="form-row">
          <div class="form-group" id="pLocStateGroup" style="display:none">
            <select id="pLocState" onchange="onLocStateChange('p')">
              <option value="">All States</option>
            </select>
          </div>
          <div class="form-group" id="pLocCityGroup" style="display:none">
            <select id="pLocCity">
              <option value="">All Cities</option>
            </select>
          </div>
        </div>
        <div class="form-group"><input type="text" id="pLocArea" placeholder="Area (optional)"></div>
        <div style="display:flex;gap:8px">
          <button class="btn-sm" onclick="applyPostLocation()">Apply</button>
          <button class="btn-sm btn-ghost" onclick="cancelPostLocationChange()">Cancel</button>
        </div>
      </div>

      <textarea id="postContent" placeholder="What's on your mind?" rows="4" oninput="this.style.height='auto';this.style.height=this.scrollHeight+'px'"></textarea>

      <div id="imagePreviewWrap" style="display:none;margin-bottom:12px;position:relative">
        <img id="imagePreview" style="width:100%;border-radius:10px;max-height:300px;object-fit:cover">
        <button onclick="removeImage()" style="position:absolute;top:8px;right:8px;background:rgba(0,0,0,.5);color:#fff;border:none;border-radius:50%;width:28px;height:28px;cursor:pointer;font-size:14px">✕</button>
      </div>

      <div class="post-actions-bar">
        <label class="img-upload-btn" for="postImageInput">
          <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="18" height="18" rx="2"/><circle cx="8.5" cy="8.5" r="1.5"/><polyline points="21 15 16 10 5 21"/></svg>
          Photo
        </label>
        <input type="file" id="postImageInput" accept="image/*,.heic,.heif" style="display:none" onchange="previewPostImage(this)">
        <button class="btn-primary" onclick="submitPost()">Publish</button>
      </div>
      <div class="msg" id="postMsg"></div>
    </div>
  </div>
</div>

<!-- ═══ COMMENTS MODAL ═══ -->
<div class="modal-overlay" id="commentsModal" onclick="closeComments(event)">
  <div class="modal-box comments-modal" onclick="event.stopPropagation()">
    <div class="modal-header">
      <h3>💬 Comments</h3>
      <button class="modal-close" onclick="closeComments()">✕</button>
    </div>
    <div class="modal-body">
      <div id="commentsList" class="comments-list"></div>
      <div class="comment-input-wrap">
        <img id="commentAvatar" src="" alt="">
        <div class="comment-input-box">
          <div id="replyingTo" style="display:none;font-size:.78rem;color:#6b7280;margin-bottom:4px"></div>
          <input type="text" id="commentInput" placeholder="Add a comment..." onkeydown="if(event.key==='Enter')submitComment()">
          <button onclick="submitComment()">Send</button>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- ═══ SHARE MODAL ═══ -->
<div class="modal-overlay" id="shareModal" onclick="closeShareModal(event)">
  <div class="modal-box share-modal" onclick="event.stopPropagation()">
    <div class="modal-header">
      <h3>🔗 Share Post</h3>
      <button class="modal-close" onclick="closeShareModal()">✕</button>
    </div>
    <div class="modal-body">
      <div class="share-link-box">
        <input type="text" id="shareLink" readonly>
        <button onclick="copyShareLink()">Copy</button>
      </div>
      <div class="share-btns">
        <a id="shareWhatsApp" href="#" target="_blank" class="share-btn whatsapp">WhatsApp</a>
        <a id="shareFB" href="#" target="_blank" class="share-btn facebook">Facebook</a>
        <a id="shareTwitter" href="#" target="_blank" class="share-btn twitter">X</a>
      </div>
    </div>
  </div>
</div>

<div class="overlay-bg" id="overlayBg" onclick="closeMenus()" style="display:none"></div>

<script>
const ME = {
  id: <?= $user['id'] ?>,
  name: <?= json_encode($user['name']) ?>,
  avatar: <?= json_encode($user['avatar'] ? 'uploads/'.$user['avatar'] : null) ?>,
  loc_country: <?= json_encode($user['loc_country']) ?>,
  loc_state:   <?= json_encode($user['loc_state']) ?>,
  loc_city:    <?= json_encode($user['loc_city']) ?>,
  loc_area:    <?= json_encode($user['loc_area']) ?>
};
</script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/heic2any/0.0.4/heic2any.min.js"></script>
<script src="js/locations.js"></script>
<script src="js/app.js"></script>
</body>
</html>
