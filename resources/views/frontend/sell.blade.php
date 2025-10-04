@extends('frontend.master')
@section('main-content')
<div class="container container-fluid px-2 py-4">
   <div class="row">
      <div class="col-12">
         <div class="d-flex justify-content-between align-items-center mb-4">
            <h3 class="mb-0">
               <i class="bi bi-shop me-2"></i>My Sells
            </h3>
            <div class="badge bg-success fs-6" id="orderCount">
               {{ $orders->total() }} Total Orders
            </div>
         </div>
         @if($orders->count() > 0)
         <!-- Order Status Filter -->
         <div class="mb-4">
            <div class="btn-group-wrapper">
               <div class="btn-group" role="group" aria-label="Order Status Filter">
                  <button type="button" class="btn btn-outline-primary active" onclick="filterOrders('all')" data-count="{{ $orders->total() }}">All</button>
                  <button type="button" class="btn btn-outline-warning" onclick="filterOrders('pending')" data-count="{{ $orders->where('status', 'pending')->count() }}">Pending</button>
                  <button type="button" class="btn btn-outline-info" onclick="filterOrders('confirmed')" data-count="{{ $orders->where('status', 'confirmed')->count() }}">Confirmed</button>
                  <button type="button" class="btn btn-outline-primary" onclick="filterOrders('processing')" data-count="{{ $orders->where('status', 'processing')->count() }}">Processing</button>
                  <button type="button" class="btn btn-outline-secondary" onclick="filterOrders('shipped')" data-count="{{ $orders->where('status', 'shipped')->count() }}">Shipped</button>
                  <button type="button" class="btn btn-outline-success" onclick="filterOrders('delivered')" data-count="{{ $orders->where('status', 'delivered')->count() }}">Delivered</button>
                  <button type="button" class="btn btn-outline-danger" onclick="filterOrders('cancelled')" data-count="{{ $orders->where('status', 'cancelled')->count() }}">Cancelled</button>
               </div>
            </div>
         </div>
         <div class="row">
            @foreach($orders as $order)
            <a href="#{{ $order->id }}">
            <div class="col-12 mb-4 order-card" data-status="{{ $order->status }}">
               <div class="card shadow-sm border-0">
                  <div class="card-header bg-light d-flex justify-content-between align-items-center" data-bs-toggle="collapse" data-bs-target="#orderBody{{ $order->id }}">
                     <div>
                        <h6 class="mb-0">Order #{{ $order->id }}</h6>
                        <small class="text-muted">{{ $order->created_at->timezone('Asia/Dhaka')->format('d M Y - h:i A') }}</small>
                     </div>
                     <div class="d-flex align-items-center gap-2">
                        <span class="badge 
                           @if($order->status == 'pending') bg-warning
                           @elseif($order->status == 'confirmed') bg-info
                           @elseif($order->status == 'processing') bg-primary
                           @elseif($order->status == 'shipped') bg-secondary
                           @elseif($order->status == 'delivered') bg-success
                           @elseif($order->status == 'cancelled') bg-danger
                           @endif">
                        {{ ucfirst($order->status) }}
                        </span>
                     </div>
                  </div>
                  <div class="card-body collapse" id="orderBody{{ $order->id }}">
                     <div class="row">
                        <div class="col-md-8">
                           <!-- Customer Info -->
                           <div class="d-flex align-items-center mb-3 p-2 border rounded">
                              <img src="{{ asset('profile-image/' . ($order->user->image ?? 'default.png')) }}" 
                                 alt="Customer" class="rounded me-3" 
                                 style="width: 40px; height: 40px; object-fit: cover;">
                              <div>
                                 <h6 class="mb-1">{{ $order->user->name }}</h6>
                                 <!-- <small class="text-muted">@einfo.{{ $order->user->username }}</small> -->
                                 <div class="mt-1">
                                    <i class="bi bi-telephone"></i> {{ $order->phone }} <i class="bi bi-geo-alt"> </i>{{ $order->shipping_address }}
                                 </div>
                              </div>
                           </div>
                           <!-- Ordered Items -->
                           <div>
                              <h6>Ordered Items ({{ count($order->post_ids) }} products):</h6>
                              @foreach($order->getOrderedPostsWithDetails() as $post)
                              <div class="d-flex align-items-center mb-2 p-2 border rounded">
                                 @php
                                 $images = $post->images ? json_decode($post->images, true) : [];
                                 $postImage = $post->image ?? ($images[0] ?? null);
                                 @endphp
                                 @if($postImage)
                                 <img src="{{ asset('uploads/' . $postImage) }}" 
                                    alt="Product" class="me-3 rounded" 
                                    style="width: 40px; height: 40px; object-fit: cover;">
                                 @else
                                 <div class="bg-light me-3 rounded d-flex align-items-center justify-content-center" 
                                    style="width: 50px; height: 50px;">
                                    <i class="bi bi-image text-muted"></i>
                                 </div>
                                 @endif
                                 <div class="flex-grow-1">
                                    <h6 class="mb-0">{{ $post->name }}</h6>
                                    <small class="text-muted">
                                    Qty: {{ $post->ordered_quantity }} × {{ $post->price }}
                                    @if($post->service_time)
                                    | Service: {{ $post->service_time }}
                                    @endif
                                    </small>
                                 </div>
                                 <div class="text-end">
                                    <strong>৳{{ $post->price * $post->ordered_quantity }}</strong>
                                 </div>
                              </div>
                              @endforeach
                           </div>
                           <!-- Show cancellation reason if order is cancelled -->
                           @if($order->status == 'cancelled')
                           @php
                           $cancelReason = null;
                           if (is_array($order->post_ids)) {
                           foreach($order->post_ids as $item) {
                           if (isset($item['cancel_reason'])) {
                           $cancelReason = $item['cancel_reason'];
                           break;
                           }
                           }
                           }
                           @endphp
                           @if($cancelReason)
                           <div class="alert alert-danger mt-3">
                              <i class="bi bi-x-circle me-2"></i>
                              <strong>Cancellation Reason:</strong> {{ $cancelReason }}
                           </div>
                           @endif
                           @endif
                        </div>
                        <div class="col-md-4 text-end">
                           <div class="mb-3">
                              <h4 class="text-success mb-0">৳{{ number_format($order->total_amount, 2) }}</h4>
                              <small class="text-muted">Total Amount</small>
                           </div>
                           <div class="d-grid gap-2">
                              <a href="tel:{{ $order->phone }}" class="btn btn-outline-success btn-sm">
                              <i class="bi bi-telephone me-1"></i>Call Customer
                              </a>
                              @if(!in_array($order->status, ['cancelled', 'delivered']))
                              <button class="btn btn-outline-danger btn-sm" onclick="showVendorCancelModal({{ $order->id }})">
                              <i class="bi bi-x-circle me-1"></i>Cancel Order
                              </button>
                              @endif
                              <!-- Add this to your sell.blade.php or relevant blade file -->
                              <!-- Update the confirm button to call the modal -->
                              @if($order->status == 'pending')
                              @php
                              // Find the first post for this order to check its category type
                              $firstPost = $order->getOrderPosts()->first();
                              $catType = $firstPost && $firstPost->category ? $firstPost->category->cat_type : null;
                              @endphp
                              @if($catType === 'service')
                              <button class="btn btn-success btn-sm" onclick="confirmOrderWithDelivery('self', {{ $order->id }})">
                              <i class="bi bi-check-circle me-1"></i>Confirm Order
                              </button>
                              @else
                              <button class="btn btn-success btn-sm" onclick="showDeliveryOptionsModal({{ $order->id }})">
                              <i class="bi bi-check-circle me-1"></i>Confirm Order
                              </button>
                              @endif
                              @endif
                              <!-- Processing order এর জন্য "Mark as Shipped" button -->
                              @if($order->status == 'processing')
                              <button class="btn btn-info btn-sm" onclick="markAsShipped({{ $order->id }})">
                              <i class="bi bi-truck me-1"></i>Mark as Shipped
                              </button>
                              @endif
                              @if($order->status == 'confirmed' && $order->delivery_person_id == auth()->id())
                              <button class="btn btn-info btn-sm" onclick="markAsShipped({{ $order->id }})">
                              <i class="bi bi-truck me-1"></i>Ready to Deliver
                              </button>
                              @endif
                              @if($order->status == 'shipped' && $order->delivery_person_id == auth()->id())
                              <button class="btn btn-success btn-sm" onclick="completeDelivery({{ $order->id }})">
                              <i class="bi bi-check-circle-fill me-1"></i>Delivery Done
                              </button>
                              @endif
                           </div>
                        </div>
                     </div>
                  </div>
               </div>
            </div>
            </a>
            @endforeach
         </div>
         <!-- Pagination -->
         <div class="d-flex justify-content-center mt-4">
            {{ $orders->links('pagination::bootstrap-4') }}
         </div>
         @else
         <div class="text-center py-5">
            <div class="mb-3">
               <i class="bi bi-shop-window" style="font-size: 4rem; color: #6c757d;"></i>
            </div>
            <h5 class="text-muted">No Orders Received Yet</h5>
            <p class="text-muted">You haven't received any orders yet. Share your products to get more orders!</p>
            <a href="/" class="btn btn-success">
            <i class="bi bi-plus-circle me-2"></i>Add Products
            </a>
         </div>
         @endif
      </div>
   </div>
</div>
<!-- Vendor Cancel Order Modal -->
<div class="modal fade" id="vendorCancelOrderModal" tabindex="-1" aria-labelledby="vendorCancelOrderModalLabel" aria-hidden="true">
   <div class="modal-dialog">
      <div class="modal-content">
         <div class="modal-header">
            <h5 class="modal-title" id="vendorCancelOrderModalLabel">
               <i class="bi bi-x-circle-fill text-danger me-2"></i>Cancel Order (Vendor)
            </h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
         </div>
         <div class="modal-body">
            <div class="alert alert-warning">
               <i class="bi bi-exclamation-triangle me-2"></i>
               Are you sure you want to cancel this order as vendor?
            </div>
            <div class="mb-3">
               <label for="vendorCancelReason" class="form-label">Cancellation Reason <span class="text-danger">*</span></label>
               <textarea class="form-control" id="vendorCancelReason" rows="3" 
                  placeholder="Please provide a reason for cancelling this order..." 
                  maxlength="255"></textarea>
               <div class="form-text">Maximum 255 characters</div>
               <div class="invalid-feedback" id="vendorReasonError">
                  Please provide a cancellation reason.
               </div>
            </div>
         </div>
         <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
            <i class="bi bi-arrow-left me-1"></i>Keep Order
            </button>
            <button type="button" class="btn btn-danger" onclick="confirmVendorCancelOrder()">
            <i class="bi bi-x-circle me-1"></i>Cancel Order
            </button>
         </div>
      </div>
   </div>
</div>
<!-- Add this modal at the end of your blade file, before closing body tag -->
<div class="modal fade" id="deliveryOptionsModal" tabindex="-1" aria-labelledby="deliveryOptionsModalLabel" aria-hidden="true">
   <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content">
         <div class="modal-header">
            <h5 class="modal-title" id="deliveryOptionsModalLabel">Select Delivery Method</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
         </div>
         <div class="modal-body text-center">
            <p class="mb-4">Please select how you want to handle the delivery for Order #<span id="modalOrderId"></span></p>
            <div class="d-grid gap-3">
               <button type="button" class="btn btn-primary btn-lg" onclick="confirmOrderWithDelivery('self')">
               <i class="bi bi-person-badge me-2"></i>Self Delivery
               <small class="d-block mt-1">You will handle delivery yourself</small>
               </button>
               <button type="button" class="btn btn-success btn-lg" onclick="confirmOrderWithDelivery('einfo')">
               <i class="bi bi-truck me-2"></i>Einfo Delivery
               <small class="d-block mt-1">Assign to delivery person</small>
               </button>
            </div>
         </div>
         <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
         </div>
      </div>
   </div>
</div>
<script>
   function markAsShipped(orderId) {
       const button = event.target;
       
       // Show loading state
       const originalText = button.innerHTML;
       button.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>Processing...';
       button.disabled = true;
       
       // Send request
       fetch(`/orders/${orderId}/mark-shipped`, {
           method: 'POST',
           headers: {
               'Content-Type': 'application/json',
               'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
               'Accept': 'application/json'
           }
       })
       .then(response => {
           if (!response.ok) {
               throw new Error(`HTTP error! status: ${response.status}`);
           }
           return response.json();
       })
       .then(data => {
           if (data.success) {
               // Reload page
               window.location.reload();
           } else {
               throw new Error(data.message || 'Failed to update order');
           }
       })
       .catch(error => {
           // Restore button
           button.innerHTML = originalText;
           button.disabled = false;
           
           // Show error
           alert('Failed to mark as shipped. Please try again.');
           console.error('Error:', error);
       });
   }
   
   
   let currentOrderId = null;
   
   function updateOrderStatus(orderId, status) {
       const confirmBtn = event.target;
       const originalText = confirmBtn.innerHTML;
       confirmBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>Confirming...';
       confirmBtn.disabled = true;
   
       fetch(`/orders/${orderId}/status`, {
           method: 'PATCH',
           headers: {
               'Content-Type': 'application/json',
               'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
           },
           body: JSON.stringify({ status: status })
       })
       .then(response => response.json())
       .then(data => {
           if (data.success) {
               window.location.reload();
           } else {
               alert('Failed to update order status');
           }
       })
       .catch(error => {
           alert('An error occurred');
       })
       .finally(() => {
           confirmBtn.innerHTML = originalText;
           confirmBtn.disabled = false;
       });
   }
   
   function showVendorCancelModal(orderId) {
       currentOrderId = orderId;
       document.getElementById('vendorCancelReason').value = '';
       document.getElementById('vendorCancelReason').classList.remove('is-invalid');
       
       // Show the modal
       const modal = new bootstrap.Modal(document.getElementById('vendorCancelOrderModal'));
       modal.show();
   }
   
   function confirmVendorCancelOrder() {
       const reason = document.getElementById('vendorCancelReason').value.trim();
       const reasonInput = document.getElementById('vendorCancelReason');
       const errorDiv = document.getElementById('vendorReasonError');
       
       // Validate reason
       if (!reason) {
           reasonInput.classList.add('is-invalid');
           errorDiv.textContent = 'Please provide a cancellation reason.';
           return;
       }
       
       if (reason.length > 255) {
           reasonInput.classList.add('is-invalid');
           errorDiv.textContent = 'Reason must be less than 255 characters.';
           return;
       }
       
       reasonInput.classList.remove('is-invalid');
       
       // Show loading state
       const cancelBtn = document.querySelector('#vendorCancelOrderModal .btn-danger');
       const originalText = cancelBtn.innerHTML;
       cancelBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>Cancelling...';
       cancelBtn.disabled = true;
       
       // Send cancel request (vendor cancellation uses the regular status update)
       fetch(`/orders/${currentOrderId}/status`, {
           method: 'PATCH',
           headers: {
               'Content-Type': 'application/json',
               'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
               'Accept': 'application/json'
           },
           body: JSON.stringify({ 
               status: 'cancelled',
               cancel_reason: reason + ' -vendor'
           })
       })
       .then(response => {
           if (!response.ok) {
               throw new Error(`HTTP error! status: ${response.status}`);
           }
           return response.json();
       })
       .then(data => {
           if (data.success) {
               // Hide modal and reload immediately
               const modal = bootstrap.Modal.getInstance(document.getElementById('vendorCancelOrderModal'));
               if (modal) {
                   modal.hide();
               }
               window.location.reload();
           } else {
               alert('Failed to cancel order: ' + (data.message || 'Unknown error'));
           }
       })
       .catch(error => {
           alert('Failed to cancel order. Please try again.');
           window.location.reload();
       })
       .finally(() => {
           // Restore button state
           if (cancelBtn) {
               cancelBtn.innerHTML = originalText;
               cancelBtn.disabled = false;
           }
       });
   }
   
   function filterOrders(status) {
       const orderCards = document.querySelectorAll('.order-card');
       const filterButtons = document.querySelectorAll('.btn-group button');
       const orderCountBadge = document.getElementById('orderCount');
       
       // Update active button
       filterButtons.forEach(btn => btn.classList.remove('active'));
       event.target.classList.add('active');
       
       // Get count from button's data attribute
       const count = event.target.getAttribute('data-count');
       
       // Update the counter
       if (status === 'all') {
           orderCountBadge.textContent = `${count} Total Orders`;
       } else {
           orderCountBadge.textContent = `${count} ${status.charAt(0).toUpperCase() + status.slice(1)} Orders`;
       }
       
       // Show/hide orders
       orderCards.forEach(card => {
           if (status === 'all' || card.getAttribute('data-status') === status) {
               card.style.display = 'block';
           } else {
               card.style.display = 'none';
           }
       });
   }
   
   // Close modal on escape key
   document.addEventListener('keydown', function(event) {
       if (event.key === 'Escape') {
           const modal = bootstrap.Modal.getInstance(document.getElementById('vendorCancelOrderModal'));
           if (modal) {
               modal.hide();
           }
       }
   });
</script>
<!-- JavaScript - Add this in your script section or before closing body tag -->
<script>
   let selectedOrderId = null;
   
   // Function to show the delivery options modal
   function showDeliveryOptionsModal(orderId) {
       selectedOrderId = orderId;
       document.getElementById('modalOrderId').textContent = orderId;
       const modal = new bootstrap.Modal(document.getElementById('deliveryOptionsModal'));
       modal.show();
   }
   
   // Function to confirm order with selected delivery method
   function confirmOrderWithDelivery(deliveryType, orderId = null) {
       const orderIdToUse = orderId || selectedOrderId;
       if (!orderIdToUse) return;
       
       // Close modal if exists
       const modalElement = document.getElementById('deliveryOptionsModal');
       const modal = bootstrap.Modal.getInstance(modalElement);
       if (modal) {
           modal.hide();
       }
       
       // Get the button that was clicked (for direct calls)
       const confirmBtn = orderId ? event.target : null;
       
       let data = {
           status: 'confirmed',
           delivery_type: deliveryType
       };
       
       // Show loading state
       if (confirmBtn) {
           confirmBtn.disabled = true;
           confirmBtn.innerHTML = '<i class="bi bi-hourglass-split me-1"></i>Processing...';
       }
       
       fetch(`/orders/${orderIdToUse}/status`, {
           method: 'PATCH',
           headers: {
               'Content-Type': 'application/json',
               'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
               'Accept': 'application/json'
           },
           body: JSON.stringify(data)
       })
       .then(response => response.json())
       .then(data => {
           if (data.success) {
               if (typeof toastr !== 'undefined') {
                   toastr.success(data.message || 'Order confirmed successfully!');
               } else {
                   alert(data.message || 'Order confirmed successfully!');
               }
               setTimeout(() => {
                   location.reload();
               }, 1000);
           } else {
               if (typeof toastr !== 'undefined') {
                   toastr.error(data.message || 'Something went wrong!');
               } else {
                   alert(data.message || 'Something went wrong!');
               }
               
               // Restore button
               if (confirmBtn) {
                   confirmBtn.disabled = false;
                   confirmBtn.innerHTML = '<i class="bi bi-check-circle me-1"></i>Confirm Order';
               }
           }
       })
       .catch(error => {
           console.error('Error:', error);
           
           if (typeof toastr !== 'undefined') {
               toastr.error('Failed to update order status!');
           } else {
               alert('Failed to update order status!');
           }
           
           // Restore button
           if (confirmBtn) {
               confirmBtn.disabled = false;
               confirmBtn.innerHTML = '<i class="bi bi-check-circle me-1"></i>Confirm Order';
           }
       });
   }
   
   // If you have an existing updateOrderStatus function, modify it
   function updateOrderStatus(orderId, status) {
       if (status === 'confirmed') {
           // Show modal for confirmation
           showDeliveryOptionsModal(orderId);
       } else {
           // Handle other status updates normally
           fetch(`/vendor/orders/${orderId}/status`, {
               method: 'PATCH',
               headers: {
                   'Content-Type': 'application/json',
                   'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                   'Accept': 'application/json'
               },
               body: JSON.stringify({ status: status })
           })
           .then(response => response.json())
           .then(data => {
               if (data.success) {
                   if (typeof toastr !== 'undefined') {
                       toastr.success(data.message);
                   }
                   setTimeout(() => location.reload(), 1000);
               } else {
                   if (typeof toastr !== 'undefined') {
                       toastr.error(data.message);
                   }
               }
           })
           .catch(error => {
               console.error('Error:', error);
               if (typeof toastr !== 'undefined') {
                   toastr.error('Failed to update order status!');
               }
           });
       }
   }
   
   
    function completeDelivery(orderId) {
          const button = event.target;
          
          // Confirmation dialog
          if (!confirm('Have you delivered this order to the customer?')) {
              return;
          }
          
          // Show loading state
          const originalText = button.innerHTML;
          button.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Completing...';
          button.disabled = true;
          
          // Send request to complete delivery
          fetch(`/orders/${orderId}/complete-delivery`, {
              method: 'POST',
              headers: {
                  'Content-Type': 'application/json',
                  'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                  'Accept': 'application/json'
              }
          })
          .then(response => {
              if (!response.ok) {
                  throw new Error(`HTTP error! status: ${response.status}`);
              }
              return response.json();
          })
          .then(data => {
              if (data.success) {
                  showNotification('success', 'Order delivered successfully!');
                  // Reload page after a short delay
                  setTimeout(() => {
                      window.location.reload();
                  }, 1500);
              } else {
                  throw new Error(data.message || 'Failed to complete delivery');
              }
          })
          .catch(error => {
              // Restore button state
              button.innerHTML = originalText;
              button.disabled = false;
              
              // Show error message
              showNotification('error', error.message || 'Failed to complete delivery. Please try again.');
          });
      }
</script>
<script>
   function acceptOrder(orderId) {
       const button = event.target;
       const card = document.querySelector(`.order-card[data-order-id="${orderId}"]`);
       
       // Confirmation dialog
       if (!confirm('Are you sure you want to accept this order for delivery?')) {
           return;
       }
       
       // Show loading state
       const originalText = button.innerHTML;
       button.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Accepting...';
       button.disabled = true;
       
       // Send request to accept order
       fetch(`/orders/${orderId}/accept-delivery`, {
           method: 'POST',
           headers: {
               'Content-Type': 'application/json',
               'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
               'Accept': 'application/json'
           }
       })
       .then(response => {
           if (!response.ok) {
               throw new Error(`HTTP error! status: ${response.status}`);
           }
           return response.json();
       })
       .then(data => {
           if (data.success) {
               showNotification('success', 'Order accepted successfully! The order is now being processed.');
               // Reload page to refresh the list
               setTimeout(() => {
                   window.location.reload();
               }, 1500);
           } else {
               throw new Error(data.message || 'Failed to accept order');
           }
       })
       .catch(error => {
           // Restore button state
           button.innerHTML = originalText;
           button.disabled = false;
           
           // Show error message
           showNotification('error', error.message || 'Failed to accept order. Please try again.');
       });
   }
   
   
   
   
   
   function showNotification(type, message) {
       // Create notification element
       const notification = document.createElement('div');
       notification.className = `alert alert-${type === 'success' ? 'success' : 'danger'} position-fixed top-0 start-50 translate-middle-x mt-3`;
       notification.style.zIndex = '9999';
       notification.style.minWidth = '300px';
       notification.innerHTML = `
           <div class="d-flex align-items-center">
               <i class="bi bi-${type === 'success' ? 'check-circle' : 'exclamation-circle'} me-2"></i>
               ${message}
           </div>
       `;
       
       document.body.appendChild(notification);
       
       // Remove notification after 3 seconds
       setTimeout(() => {
           notification.remove();
       }, 3000);
   }
   
</script>
<script>
   function confirmServiceOrder(orderId) {
   // Set the order ID for service orders
   selectedOrderId = orderId;
   
   // Show loading state
   const confirmBtn = event.target;
   const originalText = confirmBtn.innerHTML;
   confirmBtn.innerHTML = '<i class="bi bi-hourglass-split me-1"></i>Processing...';
   confirmBtn.disabled = true;
   
   // Confirm with self delivery (no modal needed for services)
   confirmOrderWithDelivery('self');
   }
</script>
@endsection
