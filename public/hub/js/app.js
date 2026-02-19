// ─── App State ─────────────────────────────────────────────────────
let currentPage    = 1;
let isLoadingPosts = false;
let hasMorePosts   = true;
let profilePage    = 1;
let hasMoreProfile = true;
let profileUID     = null;

let filterLoc = { country: ME.loc_country||'', state: ME.loc_state||'', city: ME.loc_city||'' };
let postLoc   = { country: ME.loc_country||'', state: ME.loc_state||'', city: ME.loc_city||'', area: ME.loc_area||'' };

let activeCommentPostId = null;
let replyingToId   = null;
let replyingToName = null;
let postImageFile  = null;

// ─── Init ───────────────────────────────────────────────────────────
document.addEventListener('DOMContentLoaded', () => {
  populateSelect('locCountry',  COUNTRIES, '🌍 International');
  populateSelect('pLocCountry', COUNTRIES, 'International');
  restoreLocationUI();
  setPostAvatar();
  updatePostLocLabel();
  loadPosts();
  loadNotificationBadge();
});

// ─── Helpers ────────────────────────────────────────────────────────
function defaultAvatar() {
  return "data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 40 40'%3E%3Ccircle cx='20' cy='16' r='8' fill='%23d1d5db'/%3E%3Cellipse cx='20' cy='36' rx='14' ry='10' fill='%23d1d5db'/%3E%3C/svg%3E";
}
function avatarSrc(av) { return av ? 'uploads/' + av : defaultAvatar(); }
function esc(s) {
  if (!s) return '';
  return String(s).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
}
function linkify(t) {
  t = t.replace(/(https?:\/\/[^\s<]+)/g, '<a href="$1" target="_blank" rel="noopener">$1</a>');
  t = t.replace(/@(\w+)/g, '<span class="mention">@$1</span>');
  return t;
}
function setPostAvatar() {
  const el = document.getElementById('postAvatar');
  if (el) el.src = ME.avatar || defaultAvatar();
}

// ─── Dropdown Helpers ────────────────────────────────────────────────
function populateSelect(id, items, placeholder) {
  const sel = document.getElementById(id);
  if (!sel) return;
  // Keep first option, rebuild rest
  while (sel.options.length > 1) sel.remove(1);
  items.forEach(item => {
    const opt = document.createElement('option');
    opt.value = item; opt.textContent = item;
    sel.appendChild(opt);
  });
}

function setSelectValue(id, val) {
  const el = document.getElementById(id);
  if (el) el.value = val || '';
}

// prefix: 'loc' = filter modal, 'p' = post editor
function onLocCountryChange(prefix) {
  const country = document.getElementById(prefix + 'Country').value;
  const states  = country && LOC_DATA[country] ? Object.keys(LOC_DATA[country]).sort() : [];

  populateSelect(prefix + 'State', states, 'All States');
  populateSelect(prefix + 'City',  [], 'All Cities');

  if (prefix === 'loc') {
    document.getElementById('locStateGroup').style.display = country ? '' : 'none';
    document.getElementById('locCityGroup').style.display  = 'none';
    document.getElementById('locAreaGroup').style.display  = country ? '' : 'none';
  } else {
    // post editor
    document.getElementById('pLocStateGroup').style.display = country ? '' : 'none';
    document.getElementById('pLocCityGroup').style.display  = 'none';
  }
}

function onLocStateChange(prefix) {
  const country = document.getElementById(prefix + 'Country').value;
  const state   = document.getElementById(prefix + 'State').value;
  const cities  = (country && state && LOC_DATA[country]?.[state])
    ? LOC_DATA[country][state].sort() : [];

  populateSelect(prefix + 'City', cities, 'All Cities');

  if (prefix === 'loc') {
    document.getElementById('locCityGroup').style.display = state ? '' : 'none';
  } else {
    document.getElementById('pLocCityGroup').style.display = state ? '' : 'none';
  }
}

// ─── Restore location UI ──────────────────────────────────────────────
function restoreLocationUI() {
  // Restore country
  setSelectValue('locCountry', filterLoc.country);
  onLocCountryChange('loc');

  // Restore state
  if (filterLoc.country && filterLoc.state) {
    setSelectValue('locState', filterLoc.state);
    onLocStateChange('loc');
  }
  // Restore city
  if (filterLoc.city) {
    setSelectValue('locCity', filterLoc.city);
  }
  updateNavLocLabel();
}

function updateNavLocLabel() {
  const label = filterLoc.city || filterLoc.state || filterLoc.country || 'International';
  document.getElementById('navLocLabel').textContent = label;
}

// ─── Location Modal ───────────────────────────────────────────────────
function openLocationModal() {
  document.getElementById('locationModal').classList.add('open');
  // Sync current filter into modal
  setSelectValue('locCountry', filterLoc.country);
  onLocCountryChange('loc');
  if (filterLoc.state) {
    setSelectValue('locState', filterLoc.state);
    onLocStateChange('loc');
  }
  if (filterLoc.city) setSelectValue('locCity', filterLoc.city);
}
function closeLocationModal(e) {
  if (e && e.target !== document.getElementById('locationModal')) return;
  document.getElementById('locationModal').classList.remove('open');
}

function clearLocation() {
  setSelectValue('locCountry', '');
  onLocCountryChange('loc');
  document.getElementById('locStateGroup').style.display = 'none';
  document.getElementById('locCityGroup').style.display  = 'none';
  document.getElementById('locAreaGroup').style.display  = 'none';
}

async function saveLocation() {
  const country = document.getElementById('locCountry').value;
  const state   = document.getElementById('locState')?.value || '';
  const city    = document.getElementById('locCity')?.value  || '';
  const area    = document.getElementById('locArea')?.value  || '';

  filterLoc = { country, state, city };

  const fd = new FormData();
  fd.append('action', 'update_location');
  fd.append('country', country); fd.append('state', state);
  fd.append('city', city); fd.append('area', area);
  await fetch('api.php', { method: 'POST', body: fd });

  ME.loc_country = country; ME.loc_state = state;
  ME.loc_city = city; ME.loc_area = area;
  postLoc = { country, state, city, area };
  updatePostLocLabel();

  document.getElementById('locationModal').classList.remove('open');
  updateNavLocLabel();
  currentPage = 1; hasMorePosts = true;
  document.getElementById('postsList').innerHTML = '';
  loadPosts();
}

// ─── Posts ─────────────────────────────────────────────────────────────
async function loadPosts(append = false) {
  if (isLoadingPosts || !hasMorePosts) return;
  isLoadingPosts = true;
  const loader = document.getElementById('feedLoader');
  if (loader) loader.style.display = 'flex';
  if (!append) { currentPage = 1; hasMorePosts = true; }

  const fd = new FormData();
  fd.append('action', 'get_posts');
  fd.append('country', filterLoc.country);
  fd.append('state',   filterLoc.state);
  fd.append('city',    filterLoc.city);
  fd.append('page',    currentPage);

  try {
    const r = await fetch('api.php', { method: 'POST', body: fd });
    const d = await r.json();
    const list = document.getElementById('postsList');
    if (!append) list.innerHTML = '';
    if (d.posts && d.posts.length > 0) {
      d.posts.forEach(p => list.insertAdjacentHTML('beforeend', renderPost(p)));
      currentPage++;
      hasMorePosts = d.posts.length === 15;
    } else if (!append) {
      list.innerHTML = '<div class="empty-state" style="padding:48px 0">🌐 No posts here yet. Be the first to post!</div>';
      hasMorePosts = false;
    } else {
      hasMorePosts = false;
    }
  } catch(e) {}

  if (loader) loader.style.display = 'none';
  document.getElementById('loadMoreWrap').style.display = hasMorePosts ? 'block' : 'none';
  isLoadingPosts = false;
}

function renderPost(p) {
  const avatar  = avatarSrc(p.uavatar);
  const loc     = p.location_label ? `<span class="post-loc-tag">📍 ${esc(p.location_label)}</span>` : '';
  const img     = p.image ? `<img class="post-image" src="uploads/${esc(p.image)}" alt="" loading="lazy">` : '';
  const content = linkify(esc(p.content));
  const liked   = p.liked;

  return `<div class="post-card" id="post-${p.id}">
    <div class="post-header">
      <img class="post-avatar" src="${avatar}" alt="" onclick="openProfile(${p.user_id})">
      <div class="post-meta">
        <a class="post-author" onclick="openProfile(${p.user_id})">${esc(p.uname)}</a>
        <div class="post-info-row">
          <span>${esc(p.time_ago)}</span>
          ${loc}
        </div>
      </div>
    </div>
    <div class="post-body">
      <div class="post-content">${content}</div>
    </div>
    ${img}
    <div class="post-footer">
      <button class="action-btn${liked?' liked':''}" id="like-btn-${p.id}" onclick="toggleLike(${p.id})">
        <svg viewBox="0 0 24 24" fill="${liked?'currentColor':'none'}" stroke="currentColor" stroke-width="2"><path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"/></svg>
        <span class="count" id="like-count-${p.id}">${p.like_count}</span>
      </button>
      <button class="action-btn" onclick="openComments(${p.id})">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg>
        <span class="count" id="comment-count-${p.id}">${p.comment_count}</span>
      </button>
      <button class="action-btn" onclick="openShare(${p.id})">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="18" cy="5" r="3"/><circle cx="6" cy="12" r="3"/><circle cx="18" cy="19" r="3"/><line x1="8.59" y1="13.51" x2="15.42" y2="17.49"/><line x1="15.41" y1="6.51" x2="8.59" y2="10.49"/></svg>
      </button>
    </div>
  </div>`;
}

// ─── Like ───────────────────────────────────────────────────────────────
async function toggleLike(postId) {
  const fd = new FormData();
  fd.append('action', 'toggle_like');
  fd.append('post_id', postId);
  const r = await fetch('api.php', { method: 'POST', body: fd });
  const d = await r.json();
  const btn = document.getElementById('like-btn-' + postId);
  const cnt = document.getElementById('like-count-' + postId);
  if (btn) {
    btn.classList.toggle('liked', d.liked);
    btn.querySelector('svg').setAttribute('fill', d.liked ? 'currentColor' : 'none');
  }
  if (cnt) cnt.textContent = d.count;
}

// ─── Comments ────────────────────────────────────────────────────────────
async function openComments(postId) {
  activeCommentPostId = postId;
  replyingToId = null; replyingToName = null;
  document.getElementById('replyingTo').style.display = 'none';
  document.getElementById('commentInput').value = '';
  document.getElementById('commentAvatar').src = ME.avatar || defaultAvatar();
  document.getElementById('commentsModal').classList.add('open');
  document.getElementById('commentsList').innerHTML = '<div class="loader-wrap"><div class="spinner"></div></div>';

  const r = await fetch(`api.php?action=get_comments&post_id=${postId}`);
  const d = await r.json();
  renderComments(d.comments || []);
}
function closeComments(e) {
  if (e && e.target !== document.getElementById('commentsModal')) return;
  document.getElementById('commentsModal').classList.remove('open');
  activeCommentPostId = null;
}
function renderComments(comments) {
  const list = document.getElementById('commentsList');
  if (!comments.length) { list.innerHTML = '<p class="empty-state">No comments yet. Be first!</p>'; return; }
  list.innerHTML = comments.map(c => renderComment(c)).join('');
}
function renderComment(c) {
  const av      = avatarSrc(c.uavatar);
  const content = linkify(esc(c.content));
  const repliesHTML = c.replies && c.replies.length
    ? `<div class="replies-wrap">${c.replies.map(r => renderReply(r, c.id)).join('')}</div>` : '';
  return `<div class="comment-item" id="comment-${c.id}">
    <img class="comment-avatar" src="${av}" alt="" onclick="openProfile(${c.user_id})">
    <div class="comment-body">
      <div class="comment-bubble">
        <strong onclick="openProfile(${c.user_id})">${esc(c.uname)}</strong>
        <div class="comment-content">${content}</div>
      </div>
      <div class="comment-meta">
        <span>${esc(c.time_ago)}</span>
        <button onclick="startReply(${c.id},'${esc(c.uname).replace(/'/g,"\\'")}')">Reply</button>
      </div>
      ${repliesHTML}
    </div>
  </div>`;
}
function renderReply(r, parentId) {
  const av      = avatarSrc(r.uavatar);
  const content = linkify(esc(r.content));
  return `<div class="reply-item" id="comment-${r.id}">
    <img class="reply-avatar" src="${av}" alt="" onclick="openProfile(${r.user_id})">
    <div class="comment-body">
      <div class="comment-bubble">
        <strong onclick="openProfile(${r.user_id})">${esc(r.uname)}</strong>
        <div class="comment-content">${content}</div>
      </div>
      <div class="comment-meta">
        <span>${esc(r.time_ago)}</span>
        <button onclick="startReply(${parentId},'${esc(r.uname).replace(/'/g,"\\'")}')">Reply</button>
      </div>
    </div>
  </div>`;
}
function startReply(commentId, name) {
  replyingToId = commentId; replyingToName = name;
  const rt = document.getElementById('replyingTo');
  rt.style.display = '';
  rt.innerHTML = `Replying to <strong>@${esc(name)}</strong> <button onclick="cancelReply()" style="background:none;border:none;color:#9ca3af;cursor:pointer;font-size:.75rem">✕</button>`;
  const input = document.getElementById('commentInput');
  input.value = `@${name} `;
  input.focus();
}
function cancelReply() {
  replyingToId = null; replyingToName = null;
  document.getElementById('replyingTo').style.display = 'none';
  document.getElementById('commentInput').value = '';
}
async function submitComment() {
  const input   = document.getElementById('commentInput');
  const content = input.value.trim();
  if (!content || !activeCommentPostId) return;

  const fd = new FormData();
  fd.append('action',  'add_comment');
  fd.append('post_id', activeCommentPostId);
  fd.append('content', content);
  if (replyingToId) fd.append('parent_id', replyingToId);

  input.value = '';
  const r = await fetch('api.php', { method: 'POST', body: fd });
  const d = await r.json();
  if (d.success) {
    const c    = d.comment;
    const list = document.getElementById('commentsList');
    const empty = list.querySelector('.empty-state');
    if (empty) empty.remove();

    if (c.parent_id && document.getElementById('comment-' + c.parent_id)) {
      const parentEl = document.getElementById('comment-' + c.parent_id);
      let wrap = parentEl.querySelector('.replies-wrap');
      if (!wrap) {
        wrap = document.createElement('div');
        wrap.className = 'replies-wrap';
        parentEl.querySelector('.comment-body').appendChild(wrap);
      }
      wrap.insertAdjacentHTML('beforeend', renderReply(c, c.parent_id));
    } else {
      c.replies = [];
      list.insertAdjacentHTML('beforeend', renderComment(c));
    }

    const cnt = document.getElementById('comment-count-' + activeCommentPostId);
    if (cnt) cnt.textContent = parseInt(cnt.textContent || 0) + 1;
    cancelReply();
    list.scrollTop = list.scrollHeight;
  }
}

// ─── Create Post ─────────────────────────────────────────────────────────
function openCreatePost() {
  document.getElementById('createPostModal').classList.add('open');
  document.getElementById('postContent').value = '';
  document.getElementById('imagePreviewWrap').style.display = 'none';
  document.getElementById('postLocEditor').style.display = 'none';
  document.getElementById('postMsg').className = 'msg';
  postImageFile = null;
  postLoc = { country: ME.loc_country||'', state: ME.loc_state||'', city: ME.loc_city||'', area: ME.loc_area||'' };
  updatePostLocLabel();
  setTimeout(() => document.getElementById('postContent').focus(), 100);
}
function closeCreatePost(e) {
  if (e && e.target !== document.getElementById('createPostModal')) return;
  document.getElementById('createPostModal').classList.remove('open');
}
function updatePostLocLabel() {
  const label = postLoc.city || postLoc.state || postLoc.country || 'International';
  const el = document.getElementById('postLocLabel');
  if (el) el.textContent = label;
}

function openPostLocationChange() {
  document.getElementById('postLocEditor').style.display = '';
  setSelectValue('pLocCountry', postLoc.country);
  onLocCountryChange('p');
  if (postLoc.state) {
    setSelectValue('pLocState', postLoc.state);
    onLocStateChange('p');
  }
  if (postLoc.city) setSelectValue('pLocCity', postLoc.city);
  document.getElementById('pLocArea').value = postLoc.area || '';
}
function cancelPostLocationChange() {
  document.getElementById('postLocEditor').style.display = 'none';
}
function applyPostLocation() {
  postLoc.country = document.getElementById('pLocCountry').value;
  postLoc.state   = document.getElementById('pLocState')?.value || '';
  postLoc.city    = document.getElementById('pLocCity')?.value  || '';
  postLoc.area    = document.getElementById('pLocArea').value   || '';
  updatePostLocLabel();
  cancelPostLocationChange();
}

async function previewPostImage(input) {
  let file = input.files[0];
  if (!file) return;
  if (/heic|heif/i.test(file.type + file.name)) {
    file = await heic2any({ blob: file, toType: 'image/jpeg', quality: 0.82 });
  }
  postImageFile = await compressImage(file);
  const reader = new FileReader();
  reader.onload = e => {
    document.getElementById('imagePreview').src = e.target.result;
    document.getElementById('imagePreviewWrap').style.display = '';
  };
  reader.readAsDataURL(postImageFile);
}
function removeImage() {
  postImageFile = null;
  document.getElementById('imagePreviewWrap').style.display = 'none';
  document.getElementById('postImageInput').value = '';
}

async function compressImage(file, maxW = 1200, quality = 0.82) {
  return new Promise(resolve => {
    const img = new Image();
    const url = URL.createObjectURL(file);
    img.onload = () => {
      let w = img.width, h = img.height;
      if (w > maxW) { h = Math.round(h * maxW / w); w = maxW; }
      const canvas = document.createElement('canvas');
      canvas.width = w; canvas.height = h;
      canvas.getContext('2d').drawImage(img, 0, 0, w, h);
      canvas.toBlob(blob => { resolve(blob); URL.revokeObjectURL(url); }, 'image/jpeg', quality);
    };
    img.onerror = () => resolve(file);
    img.src = url;
  });
}

async function submitPost() {
  const content = document.getElementById('postContent').value.trim();
  if (!content) { showPostMsg('Write something first!', 'error'); return; }

  const fd = new FormData();
  fd.append('action',  'create_post');
  fd.append('content', content);
  fd.append('country', postLoc.country);
  fd.append('state',   postLoc.state);
  fd.append('city',    postLoc.city);
  fd.append('area',    postLoc.area);
  if (postImageFile) fd.append('image', postImageFile, 'post.jpg');

  const btn = document.querySelector('#createPostModal .btn-primary');
  btn.textContent = 'Publishing...'; btn.disabled = true;
  const r = await fetch('api.php', { method: 'POST', body: fd });
  const d = await r.json();
  btn.textContent = 'Publish'; btn.disabled = false;

  if (d.success) {
    closeCreatePost();
    currentPage = 1; hasMorePosts = true;
    document.getElementById('postsList').innerHTML = '';
    loadPosts();
  } else showPostMsg(d.error || 'Failed to post', 'error');
}
function showPostMsg(msg, type) {
  const el = document.getElementById('postMsg');
  el.className = 'msg ' + type; el.textContent = msg;
  setTimeout(() => el.className = 'msg', 3000);
}

// ─── Share ──────────────────────────────────────────────────────────────
function openShare(postId) {
  const url = `${location.origin}${location.pathname}?post=${postId}`;
  document.getElementById('shareLink').value = url;
  document.getElementById('shareWhatsApp').href = `https://wa.me/?text=${encodeURIComponent(url)}`;
  document.getElementById('shareFB').href      = `https://www.facebook.com/sharer/sharer.php?u=${encodeURIComponent(url)}`;
  document.getElementById('shareTwitter').href = `https://twitter.com/intent/tweet?url=${encodeURIComponent(url)}`;
  document.getElementById('shareModal').classList.add('open');
}
function closeShareModal(e) {
  if (e && e.target !== document.getElementById('shareModal')) return;
  document.getElementById('shareModal').classList.remove('open');
}
function copyShareLink() {
  const input = document.getElementById('shareLink');
  navigator.clipboard.writeText(input.value).catch(() => { input.select(); document.execCommand('copy'); });
  const btn = document.querySelector('.share-link-box button');
  btn.textContent = 'Copied!'; setTimeout(() => btn.textContent = 'Copy', 1500);
}

// ─── Notifications ──────────────────────────────────────────────────────
async function loadNotificationBadge() {
  try {
    const r = await fetch('api.php?action=get_notifications');
    const d = await r.json();
    const badge = document.getElementById('notifBadge');
    if (d.unread > 0) { badge.textContent = d.unread; badge.style.display = 'flex'; }
    else badge.style.display = 'none';
  } catch(e) {}
}
function toggleNotifDropdown() {
  const dd = document.getElementById('notifDropdown');
  const isOpen = dd.classList.contains('open');
  closeMenus();
  if (!isOpen) { dd.classList.add('open'); loadNotifications(); }
}
function openNotifications() { toggleNotifDropdown(); }
async function loadNotifications() {
  const r = await fetch('api.php?action=get_notifications');
  const d = await r.json();
  const list = document.getElementById('notifList');
  if (!d.notifications || !d.notifications.length) {
    list.innerHTML = '<p class="empty-state">No notifications yet</p>'; return;
  }
  const types = { like:'❤️ liked your post', comment:'💬 commented on your post', reply:'↩️ replied to a comment', mention:'📣 mentioned you' };
  list.innerHTML = d.notifications.map(n =>
    `<div class="notif-item ${n.is_read?'':'unread'}" onclick="openComments(${n.post_id||0});closeMenus()">
      <img src="${avatarSrc(n.from_avatar)}" alt="">
      <div>
        <div class="ni-text"><strong>${esc(n.from_name)}</strong> ${types[n.type]||n.type}</div>
        <div class="ni-time">${esc(n.time_ago)}</div>
      </div>
    </div>`
  ).join('');
  const fd = new FormData(); fd.append('action','mark_notifications_read');
  fetch('api.php', { method:'POST', body:fd });
  document.getElementById('notifBadge').style.display = 'none';
}
async function markAllRead() {
  const fd = new FormData(); fd.append('action','mark_notifications_read');
  await fetch('api.php', { method:'POST', body:fd });
  document.getElementById('notifBadge').style.display = 'none';
  document.querySelectorAll('.notif-item').forEach(el => el.classList.remove('unread'));
}

// ─── Profile ─────────────────────────────────────────────────────────────
async function openProfile(uid) {
  closeMenus();
  profileUID = uid; profilePage = 1; hasMoreProfile = true;
  document.getElementById('feedView').style.display    = 'none';
  document.getElementById('profileView').style.display = '';

  const r = await fetch(`api.php?action=get_profile&uid=${uid}`);
  const d = await r.json();
  if (d.user) {
    document.getElementById('navLocLabel').textContent = d.user.name;
    document.getElementById('navLocBtn').onclick = goHome;
  }
  document.getElementById('profilePostsList').innerHTML = '<div class="loader-wrap"><div class="spinner"></div></div>';
  loadProfilePosts();
}

async function loadProfilePosts(append = false) {
  if (!append) profilePage = 1;
  const fd = new FormData();
  fd.append('action',      'get_posts');
  fd.append('profile_uid', profileUID);
  fd.append('page',        profilePage);
  const r = await fetch('api.php', { method:'POST', body:fd });
  const d = await r.json();
  const list = document.getElementById('profilePostsList');
  if (!append) list.innerHTML = '';
  if (d.posts && d.posts.length) {
    d.posts.forEach(p => list.insertAdjacentHTML('beforeend', renderPost(p)));
    profilePage++;
    hasMoreProfile = d.posts.length === 15;
  } else if (!append) {
    list.innerHTML = '<div class="empty-state" style="padding:40px 0">No posts yet</div>';
    hasMoreProfile = false;
  } else { hasMoreProfile = false; }
  document.getElementById('profileLoadMoreWrap').style.display = hasMoreProfile ? 'block' : 'none';
}

function goHome() {
  document.getElementById('profileView').style.display = 'none';
  document.getElementById('feedView').style.display    = '';
  profileUID = null;
  document.getElementById('navLocLabel').textContent = filterLoc.city || filterLoc.state || filterLoc.country || 'International';
  document.getElementById('navLocBtn').onclick = openLocationModal;
}

// ─── Menu ─────────────────────────────────────────────────────────────────
function toggleUserMenu() {
  const menu = document.getElementById('userMenu');
  const isOpen = menu.classList.contains('open');
  closeMenus();
  if (!isOpen) {
    menu.classList.add('open');
    document.getElementById('overlayBg').style.display = 'block';
  }
}
function closeMenus() {
  document.getElementById('userMenu').classList.remove('open');
  document.getElementById('notifDropdown').classList.remove('open');
  document.getElementById('overlayBg').style.display = 'none';
}
async function doLogout() {
  const fd = new FormData(); fd.append('action','logout');
  await fetch('api.php', { method:'POST', body:fd });
  location.href = 'auth.php';
}

// ─── Keyboard & Scroll ────────────────────────────────────────────────────
document.addEventListener('keydown', e => {
  if (e.key === 'Escape') {
    document.querySelectorAll('.modal-overlay.open').forEach(m => m.classList.remove('open'));
    closeMenus();
  }
});
window.addEventListener('scroll', () => {
  if (window.innerHeight + window.scrollY >= document.body.offsetHeight - 200) {
    const feedHidden = document.getElementById('feedView').style.display === 'none';
    if (!feedHidden) loadPosts(true);
    else if (profileUID) loadProfilePosts(true);
  }
});
