/**
 * Thinkflow Frontend JavaScript
 * Theme toggle, likes, comments, UI interactions
 */

// ===== Theme Toggle =====
(function() {
    const toggle = document.getElementById('themeToggle');
    const html = document.documentElement;
    const saved = localStorage.getItem('cms-theme') || 'dark';
    html.setAttribute('data-theme', saved);

    if (toggle) {
        toggle.addEventListener('click', () => {
            const current = html.getAttribute('data-theme');
            const next = current === 'dark' ? 'light' : 'dark';
            html.setAttribute('data-theme', next);
            localStorage.setItem('cms-theme', next);
        });
    }
})();

// ===== Base Path Detection =====
const scriptTag = document.querySelector('script[src*="script.js"]');
const BASE = scriptTag ? scriptTag.src.replace('assets/js/script.js', '') : '../';

/**
 * Toggle like on a post
 */
function likePost(postId, btn) {
    fetch(BASE + 'api/like.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: 'post_id=' + postId
    })
    .then(res => res.text())
    .then(data => {
        const countEl = document.getElementById('like-count-' + postId);
        let count = parseInt(countEl.innerText) || 0;

        if (data === "liked") {
            count++;
            if (btn) btn.classList.add('liked');
        } else if (data === "unliked") {
            count--;
            if (btn) btn.classList.remove('liked');
        } else if (data === "login_required") {
            alert("Please login to like posts.");
            return;
        }
        countEl.innerText = count;
    })
    .catch(err => console.error('Like error:', err));
}

/**
 * Add a comment to a post (inline feed)
 */
function addComment(postId) {
    const input = document.getElementById('comment-input-' + postId);
    const comment = input.value.trim();
    if (!comment) return;

    fetch(BASE + 'api/comment.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: 'post_id=' + postId + '&comment=' + encodeURIComponent(comment)
    })
    .then(res => res.text())
    .then(data => {
        if (data === "success") {
            const list = document.getElementById('comment-list-' + postId);
            const div = document.createElement('div');
            div.className = 'comment-item';
            div.innerHTML = `
                <div class="comment-avatar-sm">${(document.querySelector('.navbar-brand')?.textContent?.trim()[0] || 'Y').toUpperCase()}</div>
                <div>
                    <div class="comment-author">You</div>
                    <div class="comment-text">${escapeHtml(comment)}</div>
                    <div class="comment-date">Just now</div>
                </div>
            `;
            list.prepend(div);
            input.value = '';
            
            // Update comment count
            const countEl = document.getElementById('comment-count-' + postId);
            if (countEl) countEl.innerText = parseInt(countEl.innerText || 0) + 1;
        } else if (data === "login_required") {
            alert("Please login to comment.");
        }
    })
    .catch(err => console.error('Comment error:', err));
}

/**
 * Post comment on single post view page
 */
function postComment(postId) {
    const textarea = document.getElementById('new-comment');
    const comment = textarea.value.trim();
    if (!comment) return;

    fetch(BASE + 'api/comment.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: 'post_id=' + postId + '&comment=' + encodeURIComponent(comment)
    })
    .then(res => res.text())
    .then(data => {
        if (data === "success") {
            location.reload();
        } else {
            alert("Error: " + data);
        }
    })
    .catch(err => console.error('Comment error:', err));
}

/**
 * Confirm before deleting
 */
function confirmDelete(url) {
    if (confirm('Are you sure you want to delete this? This action cannot be undone.')) {
        window.location.href = url;
    }
}

/**
 * Escape HTML to prevent XSS
 */
function escapeHtml(text) {
    const div = document.createElement('div');
    div.appendChild(document.createTextNode(text));
    return div.innerHTML;
}
