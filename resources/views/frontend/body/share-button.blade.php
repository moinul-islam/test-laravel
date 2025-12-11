
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
document.addEventListener('DOMContentLoaded', function() {
    const shareButtons = document.querySelectorAll('.share-btn');
    const shareModal = new bootstrap.Modal(document.getElementById('shareModal'));
    const shareUrlInput = document.getElementById('shareUrl');
    const copyLinkBtn = document.getElementById('copyLinkBtn');
    
    let currentShareData = {};

    shareButtons.forEach(button => {
        button.addEventListener('click', async function() {
            const postId = this.dataset.postId;
            let rawUrl = this.dataset.postUrl || window.location.href;
            // Remove 'www.' from the URL if present
            const postUrl = rawUrl.replace('://www.', '://');
            const postTitle = this.dataset.postTitle || document.title;
            
            currentShareData = {
                title: postTitle,
                text: `Check out this post: ${postTitle}`,
                url: postUrl
            };

            // শুধুমাত্র native share API available থাকলেই চেষ্টা করবে
            // না থাকলে সরাসরি custom modal দেখাবে
            if (navigator.share) {
                try {
                    await navigator.share(currentShareData);
                    console.log('Shared successfully via native share');
                } catch (err) {
                    // User যদি cancel করে (AbortError) তাহলে কিছু করবে না
                    // কিন্তু অন্য কোনো error হলে custom modal দেখাবে
                    if (err.name !== 'AbortError') {
                        console.log('Native share failed, showing custom modal');
                        showCustomShareModal(postUrl);
                    }
                }
            } else {
                // Native share support নেই, custom modal দেখাও
                console.log('Native share not supported, showing custom modal');
                showCustomShareModal(postUrl);
            }
        });
    });

    function showCustomShareModal(url) {
        shareUrlInput.value = url;
        shareModal.show();
    }

    // Handle social media sharing
    document.querySelectorAll('.share-option-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            const platform = this.dataset.platform;
            const url = encodeURIComponent(currentShareData.url);
            const text = encodeURIComponent(currentShareData.text);
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

            if (shareUrl) {
                window.open(shareUrl, '_blank', 'width=600,height=400');
            }
        });
    });

    // Copy link functionality
    copyLinkBtn.addEventListener('click', function() {
        shareUrlInput.select();
        document.execCommand('copy');
        
        // Show feedback
        const originalText = this.innerHTML;
        this.innerHTML = '<i class="bi bi-check"></i> Copied!';
        this.classList.add('btn-success');
        this.classList.remove('btn-outline-secondary');
        
        setTimeout(() => {
            this.innerHTML = originalText;
            this.classList.remove('btn-success');
            this.classList.add('btn-outline-secondary');
        }, 2000);
    });
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