/**
 * LUMIÈRE - Main JavaScript File
 * Vanilla JavaScript for all interactive features
 */

document.addEventListener('DOMContentLoaded', function() {
    // Initialize all modules
    initMobileMenu();
    initFadeInAnimations();
    initNewsletterForm();
    initFormValidation();
    initImagePreview();
    initQuantitySelectors();
    initCategoryFilter();
    initLikeButtons();
    initSmoothScroll();
    initFlashMessages();
});

/**
 * Mobile Menu Toggle
 */
function initMobileMenu() {
    const toggle = document.getElementById('mobileMenuToggle');
    const menu = document.getElementById('navMenu');
    
    if (toggle && menu) {
        toggle.addEventListener('click', function() {
            menu.classList.toggle('active');
            toggle.classList.toggle('active');
        });
        
        // Close menu when clicking outside
        document.addEventListener('click', function(e) {
            if (!toggle.contains(e.target) && !menu.contains(e.target)) {
                menu.classList.remove('active');
                toggle.classList.remove('active');
            }
        });
    }
}

/**
 * Fade-in Animations on Scroll
 */
function initFadeInAnimations() {
    const fadeElements = document.querySelectorAll('.fade-in');
    
    if (fadeElements.length === 0) return;
    
    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.classList.add('visible');
                observer.unobserve(entry.target);
            }
        });
    }, {
        threshold: 0.1,
        rootMargin: '0px 0px -50px 0px'
    });
    
    fadeElements.forEach(el => observer.observe(el));
}

/**
 * Newsletter Form Submission
 */
function initNewsletterForm() {
    const forms = document.querySelectorAll('#newsletterForm, #footerNewsletter');
    
    forms.forEach(form => {
        if (!form) return;
        
        form.addEventListener('submit', async function(e) {
            e.preventDefault();
            
            const emailInput = form.querySelector('input[name="email"]');
            const email = emailInput.value.trim();
            const button = form.querySelector('button');
            const originalText = button.innerHTML;
            
            if (!validateEmail(email)) {
                showNotification('Please enter a valid email address', 'error');
                return;
            }
            
            button.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';
            button.disabled = true;
            
            try {
                const response = await fetch('ajax/newsletter.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: `email=${encodeURIComponent(email)}`
                });
                
                const data = await response.json();
                
                if (data.success) {
                    showNotification('Thank you for subscribing!', 'success');
                    emailInput.value = '';
                } else {
                    showNotification(data.message || 'An error occurred', 'error');
                }
            } catch (error) {
                showNotification('An error occurred. Please try again.', 'error');
            } finally {
                button.innerHTML = originalText;
                button.disabled = false;
            }
        });
    });
}

/**
 * Form Validation
 */
function initFormValidation() {
    const forms = document.querySelectorAll('#loginForm, #registerForm, #checkoutForm, #commentForm');
    
    forms.forEach(form => {
        if (!form) return;
        
        form.addEventListener('submit', function(e) {
            let isValid = true;
            const inputs = form.querySelectorAll('input[required], textarea[required]');
            
            // Clear previous errors
            form.querySelectorAll('.form-error').forEach(err => {
                if (!err.dataset.server) err.remove();
            });
            
            inputs.forEach(input => {
                const value = input.value.trim();
                let error = null;
                
                if (!value) {
                    error = `${getFieldLabel(input)} is required`;
                } else if (input.type === 'email' && !validateEmail(value)) {
                    error = 'Please enter a valid email address';
                } else if (input.name === 'password' && value.length < 8) {
                    error = 'Password must be at least 8 characters';
                } else if (input.name === 'confirm_password') {
                    const password = form.querySelector('input[name="password"]');
                    if (password && password.value !== value) {
                        error = 'Passwords do not match';
                    }
                }
                
                if (error) {
                    isValid = false;
                    showFieldError(input, error);
                }
            });
            
            if (!isValid) {
                e.preventDefault();
            }
        });
    });
}

/**
 * Image Preview Before Upload
 */
function initImagePreview() {
    // Avatar preview
    const avatarInput = document.getElementById('avatarInput');
    const avatarPreview = document.getElementById('avatarPreview');
    
    if (avatarInput && avatarPreview) {
        avatarInput.addEventListener('change', function() {
            previewImage(this, avatarPreview);
        });
    }
    
    // Community upload preview
    const communityInput = document.getElementById('communityImageInput');
    const communityPreview = document.getElementById('communityImagePreview');
    
    if (communityInput && communityPreview) {
        communityInput.addEventListener('change', function() {
            if (this.files && this.files[0]) {
                previewImage(this, communityPreview.querySelector('img') || communityPreview);
                communityPreview.style.display = 'block';
            }
        });
    }
    
    // Product image preview (admin)
    const productInput = document.getElementById('productImageInput');
    const productPreview = document.getElementById('productImagePreview');
    
    if (productInput && productPreview) {
        productInput.addEventListener('change', function() {
            previewImage(this, productPreview);
            productPreview.style.display = 'block';
        });
    }
    
    // Blog image preview (admin)
    const blogInput = document.getElementById('blogImageInput');
    const blogPreview = document.getElementById('blogImagePreview');
    
    if (blogInput && blogPreview) {
        blogInput.addEventListener('change', function() {
            previewImage(this, blogPreview);
            blogPreview.style.display = 'block';
        });
    }
}

function previewImage(input, preview) {
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = function(e) {
            if (preview.tagName === 'IMG') {
                preview.src = e.target.result;
            } else {
                let img = preview.querySelector('img');
                if (!img) {
                    img = document.createElement('img');
                    preview.appendChild(img);
                }
                img.src = e.target.result;
            }
        };
        reader.readAsDataURL(input.files[0]);
    }
}

/**
 * Quantity Selectors
 */
function initQuantitySelectors() {
    const quantityContainers = document.querySelectorAll('.quantity-input, .cart-quantity');
    
    quantityContainers.forEach(container => {
        const minusBtn = container.querySelector('button:first-child');
        const plusBtn = container.querySelector('button:last-child');
        const input = container.querySelector('input');
        
        if (!minusBtn || !plusBtn || !input) return;
        
        minusBtn.addEventListener('click', function() {
            const currentVal = parseInt(input.value) || 1;
            if (currentVal > 1) {
                input.value = currentVal - 1;
                input.dispatchEvent(new Event('change'));
            }
        });
        
        plusBtn.addEventListener('click', function() {
            const currentVal = parseInt(input.value) || 1;
            const max = parseInt(input.max) || 99;
            if (currentVal < max) {
                input.value = currentVal + 1;
                input.dispatchEvent(new Event('change'));
            }
        });
    });
}

/**
 * Category Filter (Shop Page)
 * Uses fetch/AJAX for no page reload
 */
function initCategoryFilter() {
    const filterButtons = document.querySelectorAll('.filter-btn');
    const productsGrid = document.getElementById('productsGrid');
    
    if (filterButtons.length === 0 || !productsGrid) return;
    
    filterButtons.forEach(btn => {
        btn.addEventListener('click', async function() {
            // Update active state
            filterButtons.forEach(b => b.classList.remove('active'));
            this.classList.add('active');
            
            const category = this.dataset.category;
            
            // Show loading state
            productsGrid.innerHTML = '<div class="spinner" style="margin: 40px auto;"></div>';
            
            try {
                const response = await fetch(`ajax/filter-products.php?category=${encodeURIComponent(category)}`);
                const data = await response.json();
                
                if (data.success) {
                    productsGrid.innerHTML = data.html;
                    // Reinitialize fade-in animations
                    initFadeInAnimations();
                } else {
                    productsGrid.innerHTML = '<p style="text-align: center; padding: 40px;">No products found.</p>';
                }
            } catch (error) {
                productsGrid.innerHTML = '<p style="text-align: center; padding: 40px; color: var(--color-error);">Error loading products. Please try again.</p>';
            }
        });
    });
}

/**
 * Like Button (Community Posts)
 * Uses fetch/AJAX for no page reload
 */
function initLikeButtons() {
    document.addEventListener('click', async function(e) {
        const likeBtn = e.target.closest('.like-btn');
        if (!likeBtn) return;
        
        e.preventDefault();
        
        const postId = likeBtn.dataset.postId;
        const icon = likeBtn.querySelector('i');
        const countSpan = likeBtn.querySelector('.like-count');
        
        if (!postId) return;
        
        try {
            const response = await fetch('ajax/like-post.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `post_id=${encodeURIComponent(postId)}`
            });
            
            const data = await response.json();
            
            if (data.success) {
                if (data.liked) {
                    likeBtn.classList.add('liked');
                    icon.classList.remove('far');
                    icon.classList.add('fas');
                } else {
                    likeBtn.classList.remove('liked');
                    icon.classList.remove('fas');
                    icon.classList.add('far');
                }
                countSpan.textContent = data.likes;
            } else if (data.message === 'Login required') {
                showNotification('Please login to like posts', 'warning');
                setTimeout(() => {
                    window.location.href = 'login.php';
                }, 1500);
            }
        } catch (error) {
            showNotification('An error occurred. Please try again.', 'error');
        }
    });
}

/**
 * Cart Update (Dynamic)
 */
async function updateCart(productId, quantity) {
    try {
        const response = await fetch('ajax/update-cart.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: `product_id=${encodeURIComponent(productId)}&quantity=${encodeURIComponent(quantity)}`
        });
        
        const data = await response.json();
        
        if (data.success) {
            // Update cart count in header
            const cartCount = document.querySelector('.cart-count');
            if (cartCount) {
                cartCount.textContent = data.cartCount;
                cartCount.style.display = data.cartCount > 0 ? 'flex' : 'none';
            }
            
            // Update totals
            if (data.subtotal !== undefined) {
                const subtotalEl = document.getElementById('cartSubtotal');
                const totalEl = document.getElementById('cartTotal');
                if (subtotalEl) subtotalEl.textContent = '$' + data.subtotal.toFixed(2);
                if (totalEl) totalEl.textContent = '$' + data.total.toFixed(2);
            }
            
            // Update line total
            const lineTotal = document.getElementById(`lineTotal-${productId}`);
            if (lineTotal) {
                lineTotal.textContent = '$' + data.lineTotal.toFixed(2);
            }
            
            // Remove row if quantity is 0
            if (quantity === 0) {
                const row = document.getElementById(`cartRow-${productId}`);
                if (row) {
                    row.remove();
                    // Check if cart is empty
                    const remainingRows = document.querySelectorAll('[id^="cartRow-"]');
                    if (remainingRows.length === 0) {
                        location.reload();
                    }
                }
            }
        }
    } catch (error) {
        showNotification('Error updating cart', 'error');
    }
}

/**
 * Smooth Scroll
 */
function initSmoothScroll() {
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function(e) {
            const href = this.getAttribute('href');
            if (href === '#') return;
            
            const target = document.querySelector(href);
            if (target) {
                e.preventDefault();
                target.scrollIntoView({
                    behavior: 'smooth',
                    block: 'start'
                });
            }
        });
    });
}

/**
 * Flash Messages Auto-hide
 */
function initFlashMessages() {
    const flash = document.getElementById('flashMessage');
    if (flash) {
        setTimeout(() => {
            flash.style.opacity = '0';
            flash.style.transform = 'translateX(-50%) translateY(-20px)';
            setTimeout(() => flash.remove(), 300);
        }, 5000);
    }
}

/**
 * Helper Functions
 */
function validateEmail(email) {
    return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email);
}

function getFieldLabel(input) {
    const label = input.closest('.form-group')?.querySelector('label');
    return label ? label.textContent : input.name.replace('_', ' ');
}

function showFieldError(input, message) {
    const formGroup = input.closest('.form-group');
    if (formGroup) {
        const error = document.createElement('span');
        error.className = 'form-error';
        error.textContent = message;
        formGroup.appendChild(error);
    }
}

function showNotification(message, type = 'success') {
    // Remove existing notifications
    document.querySelectorAll('.js-notification').forEach(n => n.remove());
    
    const notification = document.createElement('div');
    notification.className = `flash-message flash-${type} js-notification`;
    notification.innerHTML = `
        ${message}
        <button onclick="this.parentElement.remove()" class="flash-close">&times;</button>
    `;
    document.body.appendChild(notification);
    
    setTimeout(() => {
        notification.style.opacity = '0';
        notification.style.transform = 'translateX(-50%) translateY(-20px)';
        setTimeout(() => notification.remove(), 300);
    }, 4000);
}

// Export updateCart for inline use
window.updateCart = updateCart;
