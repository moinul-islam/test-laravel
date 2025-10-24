{{-- Review JavaScript --}}
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Show review form
    window.showReviewForm = function(itemId) {
        document.getElementById(`review-form-${itemId}`).style.display = 'block';
    };

    // Hide review form
    window.hideReviewForm = function(itemId) {
        document.getElementById(`review-form-${itemId}`).style.display = 'none';
    };

    // Edit review
    window.editReview = function(reviewId, rating, comment, itemId) {
        document.getElementById(`review-content-${reviewId}`).style.display = 'none';
        document.getElementById(`edit-review-form-${reviewId}`).style.display = 'block';
    };

    // Cancel edit review
    window.cancelEditReview = function(reviewId) {
        document.getElementById(`review-content-${reviewId}`).style.display = 'block';
        document.getElementById(`edit-review-form-${reviewId}`).style.display = 'none';
    };

    // Delete review
    window.confirmDeleteReview = function(reviewId) {
        if (confirm('Are you sure you want to delete this review?')) {
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = '/review/delete/' + reviewId;
            const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
            form.innerHTML = `
                <input type="hidden" name="_token" value="${csrfToken}">
                <input type="hidden" name="_method" value="DELETE">
            `;
            document.body.appendChild(form);
            form.submit();
        }
    };

    // Handle star ratings
    document.querySelectorAll('.rate-star').forEach(star => {
        const itemId = star.getAttribute('data-product');
        const ratingInput = document.getElementById(`selected-rating-${itemId}`);
        const stars = document.querySelectorAll(`.rate-star[data-product="${itemId}"]`);

        star.addEventListener('click', function() {
            const rating = parseInt(this.getAttribute('data-rating'));
            ratingInput.value = rating;
            updateStars(stars, rating);
        });

        star.addEventListener('mouseenter', function() {
            const rating = parseInt(this.getAttribute('data-rating'));
            updateStars(stars, rating);
        });

        star.addEventListener('mouseleave', function() {
            const currentRating = parseInt(ratingInput.value) || 0;
            updateStars(stars, currentRating);
        });
    });

    // Handle edit star ratings
    document.querySelectorAll('.edit-star').forEach(star => {
        const reviewId = star.getAttribute('data-review');
        const ratingInput = document.getElementById(`edit-rating-${reviewId}`);
        const stars = document.querySelectorAll(`.edit-star[data-review="${reviewId}"]`);

        star.addEventListener('click', function() {
            const rating = parseInt(this.getAttribute('data-rating'));
            ratingInput.value = rating;
            updateStars(stars, rating);
        });

        star.addEventListener('mouseenter', function() {
            const rating = parseInt(this.getAttribute('data-rating'));
            updateStars(stars, rating);
        });

        star.addEventListener('mouseleave', function() {
            const currentRating = parseInt(ratingInput.value) || 0;
            updateStars(stars, currentRating);
        });
    });

    function updateStars(stars, rating) {
        stars.forEach(star => {
            const starRating = parseInt(star.getAttribute('data-rating'));
            const icon = star.querySelector('i');
            if (starRating <= rating) {
                icon.className = 'bi bi-star-fill text-warning';
            } else {
                icon.className = 'bi bi-star';
            }
        });
    }
});
</script>

<style>
.review-form {
    background-color: #f8f9fa;
    padding: 15px;
    border-radius: 8px;
}

body.dark-mode .review-form {
    background-color: #2a2a2a;
}

.rating-stars {
    display: flex;
    gap: 5px;
}

.rate-star, .edit-star {
    transition: all 0.2s ease;
}

.rate-star:hover, .edit-star:hover {
    transform: scale(1.2);
}

body.dark-mode .modal-content {
    background-color: #1e1e1e;
    color: #e0e0e0;
}

body.dark-mode .review-summary {
    background-color: #2a2a2a !important;
}

body.dark-mode .review-item {
    border-bottom-color: #444 !important;
}

body.dark-mode .dropdown-menu {
    background-color: #2a2a2a;
}

body.dark-mode .dropdown-item {
    color: #e0e0e0;
}

body.dark-mode .dropdown-item:hover {
    background-color: #3a3a3a;
}
</style>