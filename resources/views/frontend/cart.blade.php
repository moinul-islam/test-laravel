<!-- Floating Cart Icon -->
<div class="floating-cart" id="floatingCart" style="display: none;">
    <div class="cart-icon">
        <i class="bi bi-cart3"></i>
        <span class="cart-count" id="cartCount">0</span>
    </div>
</div>

<!-- Cart Modal -->
<div class="modal fade" id="cartModal" tabindex="-1" aria-labelledby="cartModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="cartModalLabel">Shopping Cart</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" id="cartModalBody">
                <div id="cartItemsList">
                    <!-- Cart items will be inserted here -->
                </div>
                <div class="text-center text-muted py-5" id="emptyCartMessage" style="display: none;">
                    <i class="bi bi-cart-x fs-1"></i>
                    <p>Your cart is empty</p>
                </div>
            </div>
            <div class="modal-footer">
                <div class="w-100">
                    <div class="d-flex justify-content-between mb-3">
                        <strong>Total: <span id="cartTotalAmount">0.00</span></strong>
                    </div>
                    <button type="button" class="btn btn-primary w-100" id="proceedOrderBtn">
                        Place Order
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Service Time Selection Modal -->
<div class="modal fade" id="serviceBookingModal" tabindex="-1" aria-labelledby="serviceBookingModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="serviceBookingModalLabel">Select Service Time</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label">Service Name:</label>
                    <p id="selectedServiceName" class="fw-bold text-primary"></p>
                </div>
                <div class="mb-3">
                    <label for="bookingDate" class="form-label">Select Date *</label>
                    <input type="date" class="form-control" id="bookingDate" required>
                </div>
                <div class="mb-3">
                    <label for="bookingTimeSlot" class="form-label">Select Time *</label>
                    <select class="form-control" id="bookingTimeSlot" required>
                        <option value="">Choose time slot</option>
                        <option value="09:00">09:00 AM</option>
                        <option value="10:00">10:00 AM</option>
                        <option value="11:00">11:00 AM</option>
                        <option value="12:00">12:00 PM</option>
                        <option value="13:00">01:00 PM</option>
                        <option value="14:00">02:00 PM</option>
                        <option value="15:00">03:00 PM</option>
                        <option value="16:00">04:00 PM</option>
                        <option value="17:00">05:00 PM</option>
                    </select>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" id="confirmServiceBookingBtn">Confirm Booking</button>
            </div>
        </div>
    </div>
</div>

<!-- Order Form Modal -->
<div class="modal fade" id="orderFormModal" tabindex="-1" aria-labelledby="orderFormModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="orderFormModalLabel">Complete Your Order</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="finalOrderForm">
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="orderPhoneNumber" class="form-label">Phone Number *</label>
                        <input type="tel" class="form-control" id="orderPhoneNumber" placeholder="01xxxxxxxxx" required>
                    </div>
                    <div class="mb-3">
                        <label for="orderShippingAddress" class="form-label">Shipping Address *</label>
                        <textarea class="form-control" id="orderShippingAddress" rows="3" placeholder="Enter your full address" required></textarea>
                    </div>
                    <div class="border p-3 bg-light rounded">
                        <h6 class="mb-3">Order Summary:</h6>
                        <div id="finalOrderSummary">
                            <!-- Order items will be listed here -->
                        </div>
                        <hr>
                        <div class="d-flex justify-content-between">
                            <strong>Total Amount: <span id="finalOrderTotal">0.00</span></strong>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-success" id="finalSubmitOrderBtn">
                        <i class="bi bi-check-circle"></i> Confirm Order
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- CSS Styles -->
<style>
.floating-cart {
    position: fixed;
    bottom: 20px;
    right: 20px;
    z-index: 1000;
    background: #007bff;
    border-radius: 50%;
    width: 60px;
    height: 60px;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    box-shadow: 0 4px 15px rgba(0, 123, 255, 0.3);
    transition: all 0.3s ease;
}

.floating-cart:hover {
    transform: scale(1.1);
    box-shadow: 0 6px 20px rgba(0, 123, 255, 0.4);
}

.cart-icon {
    position: relative;
    color: white;
    font-size: 24px;
}

.cart-count {
    position: absolute;
    top: 0;
    right: 0;
    background: #dc3545;
    color: white;
    border-radius: 50%;
    width: 20px;
    height: 20px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 12px;
    font-weight: bold;
    opacity: 0;
    transform: scale(0);
    transition: all 0.3s ease;
}

.cart-count.show {
    opacity: 1;
    transform: scale(1);
}

.cart-animate {
    animation: cartBounce 0.6s ease;
}

@keyframes cartBounce {
    0%, 20%, 60%, 100% { transform: translateY(0); }
    40% { transform: translateY(-10px); }
    80% { transform: translateY(-5px); }
}

.cart-item-row {
    display: flex;
    align-items: center;
    gap: 15px;
    padding: 15px 0;
    border-bottom: 1px solid #eee;
    transition: all 0.3s ease;
}

.cart-item-row:last-child {
    border-bottom: none;
}

.cart-item-row.removing {
    opacity: 0.3;
    transform: translateX(-10px);
}

.cart-item-image {
    width: 60px;
    height: 60px;
    object-fit: cover;
    border-radius: 8px;
    flex-shrink: 0;
}

.cart-item-details {
    flex: 1;
    min-width: 0;
}

.cart-item-name {
    font-weight: 600;
    margin-bottom: 5px;
    color: #333;
}

.cart-item-price {
    color: #28a745;
    font-weight: 500;
    font-size: 14px;
}

.cart-service-time {
    font-size: 12px;
    color: #6c757d;
    margin-top: 4px;
}

.quantity-controls-wrapper {
    display: flex;
    align-items: center;
    gap: 8px;
    margin-top: 10px;
}

.quantity-control-btn {
    width: 32px;
    height: 32px;
    border: 1px solid #ddd;
    background: white;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    border-radius: 4px;
    transition: all 0.2s ease;
    font-size: 16px;
    font-weight: bold;
}

.quantity-control-btn:hover {
    background: #f8f9fa;
    border-color: #007bff;
    color: #007bff;
}

.quantity-control-btn:active {
    transform: scale(0.95);
}

.quantity-input-field {
    width: 60px;
    height: 32px;
    border: 1px solid #ddd;
    text-align: center;
    border-radius: 4px;
    font-weight: 500;
}

.quantity-input-field:focus {
    border-color: #007bff;
    outline: none;
    box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.25);
}

.cart-item-actions {
    display: flex;
    flex-direction: column;
    align-items: flex-end;
    gap: 8px;
}

.cart-item-total {
    font-weight: bold;
    color: #333;
    font-size: 16px;
}

.remove-item-btn {
    background: none;
    border: none;
    color: #dc3545;
    cursor: pointer;
    font-size: 12px;
    padding: 4px 8px;
    border-radius: 4px;
    transition: all 0.2s ease;
}

.remove-item-btn:hover {
    background: #f8f9fa;
    color: #c82333;
}

.service-badge {
    background: #17a2b8;
    color: white;
    padding: 4px 8px;
    border-radius: 4px;
    font-size: 11px;
    font-weight: 500;
}

.toast-notification {
    position: fixed;
    top: 20px;
    right: 20px;
    z-index: 9999;
    min-width: 300px;
    animation: slideInRight 0.3s ease;
}

@keyframes slideInRight {
    from {
        transform: translateX(100%);
        opacity: 0;
    }
    to {
        transform: translateX(0);
        opacity: 1;
    }
}

.fade-out {
    animation: fadeOut 0.5s ease forwards;
}

@keyframes fadeOut {
    to {
        opacity: 0;
        transform: translateX(100%);
    }
}

/* Hide floating cart when empty */
.floating-cart.hidden {
    display: none !important;
}
</style>

<!-- Updated JavaScript -->
<script>
// Complete Cart System - All Features Restored
class CompletCartSystem {
    constructor() {
        this.cart = JSON.parse(localStorage.getItem('shopping_cart')) || [];
        this.currentServiceData = null;
        this.isProcessing = false;
        this.modalInstances = {};
        this.initialize();
    }

    initialize() {
        this.setupEventListeners();
        this.updateAllCartDisplays();
        this.setDateConstraints();
        console.log('Cart system initialized with', this.cart.length, 'items');
    }

    setDateConstraints() {
        setTimeout(() => {
            const dateInput = document.getElementById('bookingDate');
            if (dateInput) {
                const today = new Date().toISOString().split('T')[0];
                dateInput.min = today;
            }
        }, 500);
    }

    setupEventListeners() {
        // Floating cart click
        const floatingCart = document.getElementById('floatingCart');
        if (floatingCart) {
            floatingCart.addEventListener('click', () => this.openCartModal());
        }

        // Order button click
        const proceedBtn = document.getElementById('proceedOrderBtn');
        if (proceedBtn) {
            proceedBtn.addEventListener('click', () => this.openOrderForm());
        }

        // Service booking confirmation
        const confirmServiceBtn = document.getElementById('confirmServiceBookingBtn');
        if (confirmServiceBtn) {
            confirmServiceBtn.addEventListener('click', () => this.confirmServiceBooking());
        }

        // Order form submission
        const orderForm = document.getElementById('finalOrderForm');
        if (orderForm) {
            orderForm.addEventListener('submit', (e) => this.submitFinalOrder(e));
        }

        // Event delegation for cart controls
        document.addEventListener('click', (e) => {
            const target = e.target;
            
            if (target.hasAttribute('data-increase-qty')) {
                e.preventDefault();
                const itemId = target.getAttribute('data-increase-qty');
                this.changeQuantity(itemId, 1);
            }
            
            if (target.hasAttribute('data-decrease-qty')) {
                e.preventDefault();
                const itemId = target.getAttribute('data-decrease-qty');
                this.changeQuantity(itemId, -1);
            }
            
            if (target.hasAttribute('data-remove-item')) {
                e.preventDefault();
                const itemId = target.getAttribute('data-remove-item');
                this.removeItem(itemId);
            }
        });

        document.addEventListener('input', (e) => {
            if (e.target.hasAttribute('data-qty-input')) {
                const itemId = e.target.getAttribute('data-qty-input');
                const newQty = parseInt(e.target.value) || 1;
                this.setQuantity(itemId, newQty);
            }
        });
    }

    // Check authentication - RESTORED
    checkAuth() {
        const authMeta = document.querySelector('meta[name="user-authenticated"]');
        if (authMeta && authMeta.getAttribute('content') === 'true') {
            return true;
        }
        
        if (typeof window.userAuthenticated !== 'undefined') {
            return window.userAuthenticated === true || window.userAuthenticated === 'true';
        }
        
        if (typeof window.Laravel !== 'undefined' && window.Laravel.user && window.Laravel.user.id) {
            return true;
        }
        
        return false;
    }

    addToCart(productId, productName, productPrice, productImage, categoryType = 'product') {
        if (this.isProcessing) return;
        this.isProcessing = true;
        
        setTimeout(() => this.isProcessing = false, 1000);

        if (categoryType === 'service') {
            this.currentServiceData = {
                productId,
                productName,
                productPrice: parseFloat(productPrice) || 0,
                productImage,
                categoryType
            };
            
            this.showServiceBookingModal(productName);
            return;
        }

        this.addItemToCart(productId, productName, productPrice, productImage, categoryType);
    }

    addItemToCart(productId, productName, productPrice, productImage, categoryType, serviceDateTime = null) {
        let existingItem = null;
        
        if (categoryType === 'product') {
            existingItem = this.cart.find(item => 
                item.id === productId && item.type === 'product'
            );
        }

        if (existingItem) {
            existingItem.quantity += 1;
        } else {
            // CORRECT STRUCTURE for Laravel
            const newItem = {
                id: productId,           // Laravel expects 'id'
                name: productName,       // Laravel expects 'name'
                price: parseFloat(productPrice) || 0, // Laravel expects 'price'
                image: productImage,     // Laravel expects 'image'
                quantity: 1,            // Laravel expects 'quantity'
                type: categoryType      // Laravel expects 'type'
            };

            if (serviceDateTime) {
                newItem.service_time = serviceDateTime; // Laravel expects 'service_time'
            }

            this.cart.push(newItem);
        }

        this.saveCartData();
        this.updateAllCartDisplays();
        this.showCartAnimation(); // RESTORED
        this.showNotification(`${productName} added to cart!`, 'success');
    }

    removeItem(itemId) {
        // RESTORED: Add removing animation
        const itemElement = document.querySelector(`[data-cart-item-id="${itemId}"]`);
        if (itemElement) {
            itemElement.classList.add('removing');
        }

        setTimeout(() => {
            const itemIndex = this.cart.findIndex(item => item.id == itemId);
            if (itemIndex !== -1) {
                const removedItem = this.cart.splice(itemIndex, 1)[0];
                this.saveCartData();
                this.updateAllCartDisplays();
                this.showNotification(`${removedItem.name} removed from cart`, 'info');
            }
        }, 200); // RESTORED: Animation delay
    }

    changeQuantity(itemId, change) {
        const item = this.cart.find(item => item.id == itemId);
        if (item) {
            const newQuantity = item.quantity + change;
            if (newQuantity <= 0) {
                this.removeItem(itemId);
            } else {
                item.quantity = newQuantity;
                this.saveCartData();
                this.updateAllCartDisplays();
            }
        }
    }

    setQuantity(itemId, quantity) {
        if (quantity <= 0) {
            this.removeItem(itemId);
            return;
        }
        
        const item = this.cart.find(item => item.id == itemId);
        if (item) {
            item.quantity = quantity;
            this.saveCartData();
            this.updateAllCartDisplays();
        }
    }

    saveCartData() {
        localStorage.setItem('shopping_cart', JSON.stringify(this.cart));
    }

    updateAllCartDisplays() {
        this.updateCartCounter();
        this.updateCartModal();
        this.updateFloatingCartVisibility();
    }

    updateFloatingCartVisibility() {
        const floatingCart = document.getElementById('floatingCart');
        if (floatingCart) {
            const totalItems = this.cart.reduce((sum, item) => sum + item.quantity, 0);
            
            if (totalItems > 0) {
                floatingCart.style.display = 'flex';
                floatingCart.classList.remove('hidden');
            } else {
                floatingCart.style.display = 'none';
                floatingCart.classList.add('hidden');
            }
        }
    }

    updateCartCounter() {
        const cartCounter = document.getElementById('cartCount');
        if (cartCounter) {
            const totalItems = this.cart.reduce((sum, item) => sum + item.quantity, 0);
            cartCounter.textContent = totalItems;
            
            if (totalItems > 0) {
                cartCounter.classList.add('show');
            } else {
                cartCounter.classList.remove('show');
            }
        }
    }

    updateCartModal() {
        const cartItemsList = document.getElementById('cartItemsList');
        const emptyMessage = document.getElementById('emptyCartMessage');
        const totalAmount = document.getElementById('cartTotalAmount');
        
        if (!cartItemsList || !emptyMessage || !totalAmount) return;

        if (this.cart.length === 0) {
            cartItemsList.innerHTML = '';
            emptyMessage.style.display = 'block';
            totalAmount.textContent = '0.00';
            return;
        }

        emptyMessage.style.display = 'none';
        
        let cartHTML = '';
        let total = 0;

        this.cart.forEach(item => {
            const itemTotal = item.price * item.quantity;
            total += itemTotal;

            const serviceTimeDisplay = item.service_time ? 
                `<div class="cart-service-time">
                    <i class="bi bi-calendar-event"></i> ${new Date(item.service_time).toLocaleString()}
                </div>` : '';

            const quantityControls = item.type === 'service' ? 
                `<div class="service-badge">Service Booking</div>` :
                `<div class="quantity-controls-wrapper">
                    <button class="quantity-control-btn" data-decrease-qty="${item.id}" type="button">-</button>
                    <input type="number" class="quantity-input-field" value="${item.quantity}" 
                           data-qty-input="${item.id}" min="1">
                    <button class="quantity-control-btn" data-increase-qty="${item.id}" type="button">+</button>
                </div>`;

            cartHTML += `
                <div class="cart-item-row" data-cart-item-id="${item.id}">
                    <img src="${item.image}" alt="${item.name}" class="cart-item-image">
                    <div class="cart-item-details">
                        <div class="cart-item-name">${item.name}</div>
                        <div class="cart-item-price">${item.price.toFixed(2)} each</div>
                        ${serviceTimeDisplay}
                        ${quantityControls}
                    </div>
                    <div class="cart-item-actions">
                        <div class="cart-item-total">${itemTotal.toFixed(2)}</div>
                        <button class="remove-item-btn" data-remove-item="${item.id}" type="button">
                            <i class="bi bi-trash"></i> Remove
                        </button>
                    </div>
                </div>
            `;
        });

        cartItemsList.innerHTML = cartHTML;
        totalAmount.textContent = total.toFixed(2);
    }

    openCartModal() {
        this.updateCartModal();
        const cartModal = document.getElementById('cartModal');
        if (cartModal) {
            const modal = new bootstrap.Modal(cartModal);
            modal.show();
            this.modalInstances.cart = modal; // RESTORED: Modal instance tracking
        }
    }

    showServiceBookingModal(serviceName) {
        const serviceNameEl = document.getElementById('selectedServiceName');
        if (serviceNameEl) {
            serviceNameEl.textContent = serviceName;
        }
        
        const serviceModal = document.getElementById('serviceBookingModal');
        if (serviceModal) {
            const modal = new bootstrap.Modal(serviceModal);
            modal.show();
            this.modalInstances.service = modal; // RESTORED: Modal instance tracking
        }
    }

    confirmServiceBooking() {
        const dateInput = document.getElementById('bookingDate');
        const timeInput = document.getElementById('bookingTimeSlot');
        
        if (!dateInput || !timeInput) {
            this.showNotification('Booking form not found!', 'error');
            return;
        }

        const selectedDate = dateInput.value;
        const selectedTime = timeInput.value;
        
        if (!selectedDate || !selectedTime) {
            this.showNotification('Please select both date and time!', 'error');
            return;
        }

        const serviceDateTime = `${selectedDate} ${selectedTime}:00`;
        
        if (this.currentServiceData) {
            this.addItemToCart(
                this.currentServiceData.productId,
                this.currentServiceData.productName,
                this.currentServiceData.productPrice,
                this.currentServiceData.productImage,
                'service',
                serviceDateTime
            );
            
            // RESTORED: Proper modal closing
            if (this.modalInstances.service) {
                this.modalInstances.service.hide();
            }
            
            dateInput.value = '';
            timeInput.value = '';
            this.currentServiceData = null;
        }
    }

    openOrderForm() {
        if (this.cart.length === 0) {
            this.showNotification('Your cart is empty!', 'error');
            return;
        }

        // Check authentication
        if (!this.checkAuth()) {
            this.showNotification('Please login to place your order', 'error');
            
            // RESTORED: Close cart modal properly
            if (this.modalInstances.cart) {
                this.modalInstances.cart.hide();
            }
            
            setTimeout(() => {
                window.location.href = '/login';
            }, 1500);
            return;
        }

        this.updateOrderSummary();
        
        // RESTORED: Proper modal management
        if (this.modalInstances.cart) {
            this.modalInstances.cart.hide();
        }
        
        const orderModal = document.getElementById('orderFormModal');
        if (orderModal) {
            const modal = new bootstrap.Modal(orderModal);
            modal.show();
            this.modalInstances.order = modal;
        }
    }

    updateOrderSummary() {
        const summaryEl = document.getElementById('finalOrderSummary');
        const totalEl = document.getElementById('finalOrderTotal');
        
        if (!summaryEl || !totalEl) return;

        let summaryHTML = '';
        let total = 0;

        this.cart.forEach(item => {
            const itemTotal = item.price * item.quantity;
            total += itemTotal;

            const serviceInfo = item.service_time ? 
                `<small class="text-muted"> (${new Date(item.service_time).toLocaleString()})</small>` : '';

            summaryHTML += `
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <span>${item.name} × ${item.quantity}${serviceInfo}</span>
                    <span class="fw-bold">${itemTotal.toFixed(2)}</span>
                </div>
            `;
        });

        summaryEl.innerHTML = summaryHTML;
        totalEl.textContent = total.toFixed(2);
    }

    async submitFinalOrder(e) {
        e.preventDefault();
        
        if (this.cart.length === 0) {
            this.showNotification('Cart is empty!', 'error');
            return;
        }

        const phoneInput = document.getElementById('orderPhoneNumber');
        const addressInput = document.getElementById('orderShippingAddress');
        const submitBtn = document.getElementById('finalSubmitOrderBtn');
        
        const phone = phoneInput.value.trim();
        const address = addressInput.value.trim();
        
        if (!phone || !address) {
            this.showNotification('Please fill in all required fields!', 'error');
            return;
        }

        // Show loading
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Processing Order...';

        try {
            // CORRECT DATA STRUCTURE - Laravel expects this exact structure
            const orderData = {
                phone: phone,
                shipping_address: address,
                total_amount: this.cart.reduce((sum, item) => sum + (item.price * item.quantity), 0),
                cart_items: this.cart // Contains: id, name, price, image, quantity, type, service_time
            };

            console.log('Sending to Laravel:', orderData); // Debug

            const response = await fetch('/orders/store', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
                },
                body: JSON.stringify(orderData)
            });

            const result = await response.json();
            console.log('Laravel response:', result); // Debug

            if (result.success) {
                // Success - clear cart
                this.cart = [];
                this.saveCartData();
                this.updateAllCartDisplays();
                
                // RESTORED: Proper modal closing
                if (this.modalInstances.order) {
                    this.modalInstances.order.hide();
                }
                
                // Reset form
                document.getElementById('finalOrderForm').reset();
                
                this.showNotification('Order placed successfully!', 'success');
                
            } else {
                this.showNotification(result.message || 'Order failed!', 'error');
            }

        } catch (error) {
            console.error('Order error:', error);
            this.showNotification('Network error! Please try again.', 'error');
        } finally {
            submitBtn.disabled = false;
            submitBtn.innerHTML = '<i class="bi bi-check-circle"></i> Confirm Order';
        }
    }

    // RESTORED: Cart animation
    showCartAnimation() {
        const cartIcon = document.querySelector('.cart-icon');
        if (cartIcon) {
            cartIcon.classList.add('cart-animate');
            setTimeout(() => cartIcon.classList.remove('cart-animate'), 600);
        }
    }

    // RESTORED: Original toast timing (3 seconds)
    showNotification(message, type = 'success') {
        const alertClass = type === 'success' ? 'alert-success' : 
                          type === 'error' ? 'alert-danger' : 'alert-info';
        
        const notificationHTML = `
            <div class="alert ${alertClass} alert-dismissible toast-notification" role="alert">
                ${message}
                <button type="button" class="btn-close" onclick="this.parentElement.remove()"></button>
            </div>
        `;
        
        document.body.insertAdjacentHTML('beforeend', notificationHTML);
        
        // RESTORED: Original 3 second timing
        setTimeout(() => {
            const notifications = document.querySelectorAll('.toast-notification');
            if (notifications.length > 0) {
                const lastNotification = notifications[notifications.length - 1];
                lastNotification.classList.add('fade-out');
                setTimeout(() => lastNotification.remove(), 500);
            }
        }, 3000);
    }

    // RESTORED: Clear cart with message
    clearCart() {
        this.cart = [];
        this.saveCartData();
        this.updateAllCartDisplays();
        this.showNotification('Cart cleared', 'info'); // RESTORED: Success message
    }

    // RESTORED: Get cart count method
    getCartCount() {
        return this.cart.reduce((sum, item) => sum + item.quantity, 0);
    }
}

// Global functions
function addToCart(productId, productName, productPrice, productImage, categoryType = 'product') {
    if (window.cartManager) {
        window.cartManager.addToCart(productId, productName, productPrice, productImage, categoryType);
    }
}

// RESTORED: Clear cart on logout with success message
function clearCartOnLogout() {
    if (window.cartManager) {
        window.cartManager.clearCart(); // This will show "Cart cleared" message
    }
    localStorage.removeItem('shopping_cart');
}

// Initialize
document.addEventListener('DOMContentLoaded', function() {
    window.cartManager = new CompletCartSystem();
    console.log('Complete cart system initialized. Auth:', window.cartManager.checkAuth());
    console.log('Current cart items:', window.cartManager.getCartCount());
});
</script>