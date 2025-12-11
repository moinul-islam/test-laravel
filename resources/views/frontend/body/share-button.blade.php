
<!-- Custom Share Modal -->
<div class="modal fade" id="shareModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Share Post</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal">
                    <i class="bi bi-x-lg" style="font-size: 1.25rem; cursor: pointer;" data-bs-dismiss="modal" aria-label="Close"></i>
                </button>
            </div>
            <div class="modal-body">
                <div class="share-options">
                    <button class="share-option-btn" data-platform="facebook">
                        <i class="bi bi-facebook"></i> Facebook
                    </button>
                    <button class="share-option-btn" data-platform="twitter">
                        <i class="bi bi-twitter"></i> Twitter
                    </button>
                    <button class="share-option-btn" data-platform="whatsapp">
                        <i class="bi bi-whatsapp"></i> WhatsApp
                    </button>
                    <button class="share-option-btn" data-platform="linkedin">
                        <i class="bi bi-linkedin"></i> LinkedIn
                    </button>
                </div>
                <div class="mt-3">
                    <label class="form-label">Or copy link:</label>
                    <div class="input-group">
                        <input type="text" class="form-control" id="shareUrl" readonly>
                        <button class="btn btn-outline-secondary" type="button" id="copyLinkBtn">
                            <i class="bi bi-clipboard"></i> Copy
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<script>
document.addEventListener('click', async function(e) {

    /* ================================
       SHARE BUTTON CLICK HANDLER
       Lazy Load Compatible
    ================================= */

    let btn = e.target.closest('.share-btn');
    if (btn) {

        const postId = btn.dataset.postId;
        let rawUrl = btn.dataset.postUrl || window.location.href;

        // Remove "www." from URL
        const postUrl = rawUrl.replace('://www.', '://');

        const postTitle = btn.dataset.postTitle || document.title;

        window.currentShareData = {
            title: postTitle,
            text: `Check out this post: ${postTitle}`,
            url: postUrl
        };

        // If Native Share API exists
        if (navigator.share) {
            try {
                await navigator.share(window.currentShareData);
            } catch (err) {
                if (err.name !== 'AbortError') {
                    showCustomShareModal(postUrl);
                }
            }
        } else {
            showCustomShareModal(postUrl);
        }
    }


    /* ================================
       SOCIAL SHARE BUTTONS
    ================================= */
    let socialBtn = e.target.closest('.share-option-btn');
    if (socialBtn) {
        const platform = socialBtn.dataset.platform;
        const url = encodeURIComponent(window.currentShareData.url);
        const text = encodeURIComponent(window.currentShareData.text);

        let shareUrl = '';

        switch(platform) {
            case 'facebook':
                shareUrl = `https://www.facebook.com/sharer/sharer.php?u=${url}`;
                break;
            case 'twitter':
                shareUrl = `https://twitter.com/intent/tweet?url=${url}&text=${text}`;
                break;
            case 'whatsapp':
                shareUrl = `https://wa.me/?text=${text}%20${url}`;
                break;
            case 'linkedin':
                shareUrl = `https://www.linkedin.com/sharing/share-offsite/?url=${url}`;
                break;
        }

        if (shareUrl !== '') {
            window.open(shareUrl, '_blank', 'width=600,height=400');
        }
    }


    /* ================================
       COPY LINK BUTTON
    ================================= */
    if (e.target.id === 'copyLinkBtn' || e.target.closest('#copyLinkBtn')) {
        const copyBtn = document.getElementById('copyLinkBtn');
        const urlInput = document.getElementById('shareUrl');

        urlInput.select();
        document.execCommand('copy');

        const originalText = copyBtn.innerHTML;

        copyBtn.innerHTML = '<i class="bi bi-check"></i> Copied!';
        copyBtn.classList.add('btn-success');
        copyBtn.classList.remove('btn-outline-secondary');

        setTimeout(() => {
            copyBtn.innerHTML = originalText;
            copyBtn.classList.remove('btn-success');
            copyBtn.classList.add('btn-outline-secondary');
        }, 2000);
    }


    /* ================================
       SHOW SHARE MODAL FUNCTION
    ================================= */
    function showCustomShareModal(url) {
        document.getElementById('shareUrl').value = url;

        const shareModalEl = document.getElementById('shareModal');
        const shareModal = new bootstrap.Modal(shareModalEl);

        shareModal.show();
    }

});
</script>

<style>
.share-options {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 10px;
}

.share-option-btn {
    padding: 12px;
    border: 1px solid #ddd;
    background: #fff;
    border-radius: 8px;
    cursor: pointer;
    transition: all 0.3s ease;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    font-size: 14px;
}

.share-option-btn:hover {
    background: #f8f9fa;
    border-color: #adb5bd;
    transform: translateY(-2px);
}

.share-option-btn i {
    font-size: 20px;
}

.share-option-btn[data-platform="facebook"]:hover {
    background: #1877f2;
    color: white;
    border-color: #1877f2;
}

.share-option-btn[data-platform="twitter"]:hover {
    background: #1da1f2;
    color: white;
    border-color: #1da1f2;
}

.share-option-btn[data-platform="whatsapp"]:hover {
    background: #25d366;
    color: white;
    border-color: #25d366;
}

.share-option-btn[data-platform="linkedin"]:hover {
    background: #0077b5;
    color: white;
    border-color: #0077b5;
}

#shareUrl {
    font-size: 14px;
}
</style>