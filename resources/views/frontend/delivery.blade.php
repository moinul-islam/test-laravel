@extends('frontend.master')
@section('main-content')
<div class="container container-fluid px-2 py-4">

   @if(auth()->check() && auth()->user()->role === 'admin')
      @include('frontend.body.admin-nav')
   @endif
   <div class="row">
      <div class="col-12">
         <div class="d-flex justify-content-between align-items-center mb-4">
            <h3 class="mb-0">
               <i class="bi bi-truck me-2"></i>My Deliveries
            </h3>
            <!-- <div class="badge bg-primary fs-6">
               <span id="availableCount">{{ $orders->total() }}</span> All Orders | 
               <span id="availableCount">{{ $orders->where('status', 'confirmed')->count() }}</span> Available | 
               <span id="myOrdersCount">{{ $orders->where('delivery_person_id', auth()->id())->whereIn('status', ['processing', 'shipped'])->count() }}</span> Active | 
               <span id="completedCount">{{ $orders->where('delivery_person_id', auth()->id())->where('status', 'delivered')->count() }}</span> Completed |
               <span id="cancelledCount">{{ $orders->where('delivery_person_id', auth()->id())->where('status', 'cancelled')->count() }}</span> Cancelled
            </div> -->
         </div>
         @if($orders->count() > 0)
         <div class="mb-4 btn-group-wrapper">
            <div class="btn-group" role="group">
               <button type="button" class="btn btn-outline-primary active" onclick="filterOrders('all')">
               All Orders {{ $orders->total() }}
               </button>
               <button type="button" class="btn btn-outline-info" onclick="filterOrders('available')">
               Available {{ $orders->where('status', 'confirmed')->count() }}
               </button>
               <button type="button" class="btn btn-outline-warning" onclick="filterOrders('processing')">
               Processing {{ $orders->where('delivery_person_id', auth()->id())->whereIn('status', 'processing')->count() }}
               </button>
               <button type="button" class="btn btn-outline-success" onclick="filterOrders('shipped')">
               Ready {{ $orders->where('delivery_person_id', auth()->id())->whereIn('status', 'shipped')->count() }}
               </button>
               <button type="button" class="btn btn-outline-secondary" onclick="filterOrders('completed')">
               Completed {{ $orders->where('delivery_person_id', auth()->id())->where('status', 'delivered')->count() }}
               </button>
               <button type="button" class="btn btn-outline-danger" onclick="filterOrders('cancelled')">
               Cancelled {{ $orders->where('delivery_person_id', auth()->id())->where('status', 'cancelled')->count() }}
               </button>
            </div>
         </div>
         <div class="row">
            @foreach($orders as $order)
            <div class="col-12 mb-4 order-card" 
               data-order-id="{{ $order->id }}"
               data-status="{{ $order->status }}"
               data-delivery-person="{{ $order->delivery_person_id }}">
               <div class="card shadow-sm border-0">
                  <div class="card-header 
                     @if($order->status == 'confirmed') bg-info bg-opacity-10
                     @elseif($order->status == 'processing') bg-warning bg-opacity-10
                     @elseif($order->status == 'shipped') bg-success bg-opacity-10
                     @elseif($order->status == 'delivered') bg-secondary bg-opacity-10
                     @elseif($order->status == 'cancelled') bg-danger bg-opacity-10
                     @endif
                     d-flex justify-content-between align-items-center" data-bs-toggle="collapse" data-bs-target="#orderBody{{ $order->id }}">
                     <div>
                        <h6 class="mb-0">Order #{{ $order->id }}</h6>
                        <small class="text-muted">{{ $order->created_at->timezone('Asia/Dhaka')->format('d M Y - h:i A') }}</small>
                     </div>
                     <div class="d-flex align-items-center gap-2">
                        @if($order->status == 'confirmed')
                        <span class="badge bg-info">
                        <i class="bi bi-clock me-1"></i>Available
                        </span>
                        @elseif($order->status == 'processing')
                        <span class="badge bg-warning">
                        <i class="bi bi-box me-1"></i>Processing
                        </span>
                        @if($order->delivery_person_id == auth()->id())
                        <span class="badge bg-primary">My Order</span>
                        @endif
                        @elseif($order->status == 'shipped')
                        <span class="badge bg-success">
                        <i class="bi bi-truck me-1"></i>Ready to Deliver
                        </span>
                        @if($order->delivery_person_id == auth()->id())
                        <span class="badge bg-primary">My Order</span>
                        @endif
                        @elseif($order->status == 'delivered')
                        <span class="badge bg-secondary">
                        <i class="bi bi-check-circle-fill me-1"></i>Delivered
                        </span>
                        @if($order->delivery_person_id == auth()->id())
                        <span class="badge bg-success">Completed by Me</span>
                        @endif
                        @elseif($order->status == 'cancelled')
                        <span class="badge bg-danger">
                        <i class="bi bi-x-circle-fill me-1"></i>Cancelled
                        </span>
                        @if($order->delivery_person_id == auth()->id())
                        <span class="badge bg-warning text-dark">My Cancelled Order</span>
                        @endif
                        @endif
                        <span class="badge bg-dark">
                        ৳{{ number_format($order->total_amount, 2) }}
                        </span>
                     </div>
                  </div>
                  <div class="card-body collapse" id="orderBody{{ $order->id }}">
                     <div class="row">
                        <div class="col-md-8">
                           <!-- Customer Info -->
                           <div class="mb-3">
                              <h6 class="text-muted mb-2">
                                 <i class="bi bi-person-circle me-2"></i>Customer Information
                              </h6>
                              <div class="d-flex align-items-center p-2 border rounded">
                                 <img src="{{ asset('profile-image/' . ($order->user->image ?? 'default.png')) }}" 
                                    alt="Customer" class="rounded me-3" 
                                    style="width: 40px; height: 40px; object-fit: cover;">
                                 <div class="flex-grow-1">
                                    <h6 class="mb-1">{{ $order->user->name }}</h6>
                                    <div class="small text-muted">
                                       <div>
                                          <i class="bi bi-telephone-fill"></i> {{ $order->phone }} <i class="bi bi-geo-alt-fill"></i> {{ $order->shipping_address }}
                                       </div>
                                    </div>
                                 </div>
                              </div>
                           </div>
                           <!-- Vendor Info -->
                           <div class="mb-3">
                              <h6 class="text-muted mb-2">
                                 <i class="bi bi-shop me-2"></i>Vendor Information
                              </h6>
                              <div class="p-2 border rounded">
                                 <div class="d-flex align-items-center">
                                    <img src="{{ asset('profile-image/' . ($order->vendor->image ?? 'default.png')) }}" 
                                       alt="Vendor" class="rounded me-3" 
                                       style="width: 40px; height: 40px; object-fit: cover;">
                                    <div>
                                       <h6 class="mb-0">{{ $order->vendor->name }}</h6>
                                       <small class="text-muted"><i class="bi bi-telephone-fill"></i> {{ $order->vendor->phone_number }} <i class="bi bi-geo-alt-fill"></i> {{ $order->vendor->area }}</small>
                                    </div>
                                 </div>
                              </div>
                           </div>
                           <!-- Ordered Items -->
                           <div class="mb-3">
                              <h6 class="text-muted mb-2">
                                 <i class="bi bi-box-seam me-2"></i>Order Items ({{ count($order->post_ids) }} products)
                              </h6>
                              <div class="border rounded p-2">
                                 @foreach($order->getOrderedPostsWithDetails() as $post)
                                 <div class="d-flex align-items-center bg-white rounded">
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
                                       Qty: <strong>{{ $post->ordered_quantity }}</strong> × ৳{{ $post->price }}
                                       @if($post->service_time)
                                       | Service: {{ $post->service_time }}
                                       @endif
                                       </small>
                                    </div>
                                    <div class="text-end">
                                       <strong class="text-success">৳{{ $post->price * $post->ordered_quantity }}</strong>
                                    </div>
                                 </div>
                                 @endforeach
                              </div>
                           </div>
                        </div>
                        <div class="col-md-4">
                           <!-- Order Summary -->
                           <div class="bg-light rounded p-3 mb-3">
                              <h5 class="mb-3">Order Summary</h5>
                              <div class="d-flex justify-content-between mb-2">
                                 <span>Subtotal:</span>
                                 <strong>৳{{ number_format($order->total_amount, 2) }}</strong>
                              </div>
                              <div class="d-flex justify-content-between mb-2">
                                 <span>Delivery Fee:</span>
                                 <strong>৳0.00</strong>
                              </div>
                              <hr>
                              <div class="d-flex justify-content-between">
                                 <span class="h6">Total:</span>
                                 <strong class="h5 text-success">৳{{ number_format($order->total_amount, 2) }}</strong>
                              </div>
                           </div>
                           <!-- Action Buttons -->
                           <div class="d-grid gap-2">
                              @if($order->status == 'confirmed' && $order->delivery_person_id == null)
                              <!-- Available order - anyone can accept -->
                              <button class="btn btn-success btn-lg accept-order-btn" 
                                 onclick="acceptOrder({{ $order->id }})"
                                 data-order-id="{{ $order->id }}">
                              <i class="bi bi-check2-circle me-2"></i>Accept Order
                              </button>
                              @elseif($order->status == 'processing' && $order->delivery_person_id == auth()->id())
                              <!-- My processing order -->
                              <div class="alert alert-warning text-center mb-2">
                                 <i class="bi bi-clock-history me-2"></i>
                                 Waiting for vendor to prepare order
                              </div>
                              @elseif($order->status == 'shipped' && $order->delivery_person_id == auth()->id())
                              <!-- Ready to deliver - show delivery done button -->
                              <button class="btn btn-success btn-lg delivery-done-btn" 
                                 onclick="completeDelivery({{ $order->id }})"
                                 data-order-id="{{ $order->id }}">
                              <i class="bi bi-check-circle-fill me-2"></i>Delivery Done
                              </button>
                              @elseif($order->status == 'delivered' && $order->delivery_person_id == auth()->id())
                              <!-- Completed order -->
                              <div class="alert alert-success text-center mb-2">
                                 <i class="bi bi-check-circle-fill me-2"></i>
                                 Order Delivered Successfully
                                 @if($order->delivered_at)
                                 <br><small>{{ $order->delivered_at->format('M d, Y - h:i A') }}</small>
                                 @endif
                              </div>
                              @elseif($order->status == 'cancelled' && $order->delivery_person_id == auth()->id())
                              <!-- Cancelled order -->
                              <div class="alert alert-danger text-center mb-2">
                                 <i class="bi bi-x-circle-fill me-2"></i>
                                 Order Cancelled
                                 @if($order->cancellation_reason)
                                 <br><small>Reason: {{ $order->cancellation_reason }}</small>
                                 @endif
                              </div>
                              @elseif($order->delivery_person_id != auth()->id() && $order->delivery_person_id != null)
                              <!-- Someone else's order -->
                              <div class="alert alert-secondary text-center">
                                 <i class="bi bi-person-check me-2"></i>
                                 Assigned to another delivery person
                              </div>
                              @endif
                              <!-- Common buttons for my orders (not for completed/cancelled) -->
                              @if($order->delivery_person_id == auth()->id() && !in_array($order->status, ['delivered', 'cancelled']))
                              <a href="tel:{{ $order->phone }}" class="btn btn-outline-primary">
                              <i class="bi bi-telephone-fill me-2"></i>Call Customer
                              </a>
                              <a href="tel:{{ $order->vendor->phone_number ?? '' }}" class="btn btn-outline-secondary">
                              <i class="bi bi-telephone me-2"></i>Call Vendor
                              </a>
                              @endif
                           </div>
                        </div>
                     </div>
                  </div>
               </div>
            </div>
            @endforeach
         </div>
         <!-- Pagination -->
         <div class="d-flex justify-content-center mt-4">
            {{ $orders->links('pagination::bootstrap-4') }}
         </div>
         @else
         <div class="text-center py-5">
            <div class="mb-3">
               <i class="bi bi-truck" style="font-size: 4rem; color: #6c757d;"></i>
            </div>
            <h5 class="text-muted">No Orders Available</h5>
            <p class="text-muted">There are no orders available at this moment.</p>
            <button class="btn btn-primary" onclick="location.reload()">
            <i class="bi bi-arrow-clockwise me-2"></i>Refresh
            </button>
         </div>
         @endif
      </div>
   </div>
</div>
<style>
   .order-card {
   transition: all 0.3s ease;
   }
   .order-card.accepted {
   opacity: 0.5;
   pointer-events: none;
   }
   .accept-order-btn:hover, .delivery-done-btn:hover {
   transform: translateY(-2px);
   box-shadow: 0 5px 15px rgba(0,0,0,0.2);
   }
   .accept-order-btn:disabled, .delivery-done-btn:disabled {
   cursor: not-allowed;
   opacity: 0.6;
   }
   .badge {
   font-weight: 500;
   }
   @media (max-width: 768px) {
   .card-header {
   flex-direction: column;
   align-items: start !important;
   gap: 10px;
   }
   }
</style>
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
   
   function filterOrders(filter) {
       const orderCards = document.querySelectorAll('.order-card');
       const filterButtons = document.querySelectorAll('.btn-group button');
       const currentUserId = {{ auth()->id() }};
       
       // Update active button
       filterButtons.forEach(btn => btn.classList.remove('active'));
       event.target.classList.add('active');
       
       // Show/hide orders
       orderCards.forEach(card => {
           const status = card.getAttribute('data-status');
           const deliveryPersonId = parseInt(card.getAttribute('data-delivery-person') || 0);
           
           if (filter === 'all') {
               card.style.display = 'block';
           } else if (filter === 'available') {
               // Show only confirmed orders without delivery person
               if (status === 'confirmed' && deliveryPersonId === 0) {
                   card.style.display = 'block';
               } else {
                   card.style.display = 'none';
               }
           } else if (filter === 'processing') {
               // Show only my processing orders
               if (status === 'processing' && deliveryPersonId === currentUserId) {
                   card.style.display = 'block';
               } else {
                   card.style.display = 'none';
               }
           } else if (filter === 'shipped') {
               // Show only my shipped orders
               if (status === 'shipped' && deliveryPersonId === currentUserId) {
                   card.style.display = 'block';
               } else {
                   card.style.display = 'none';
               }
           } else if (filter === 'completed') {
               // Show only my delivered orders
               if (status === 'delivered' && deliveryPersonId === currentUserId) {
                   card.style.display = 'block';
               } else {
                   card.style.display = 'none';
               }
           } else if (filter === 'cancelled') {
               // Show only my cancelled orders
               if (status === 'cancelled' && deliveryPersonId === currentUserId) {
                   card.style.display = 'block';
               } else {
                   card.style.display = 'none';
               }
           }
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
   
   // Auto-refresh page every 30 seconds to check for new orders (skip for completed and cancelled tab)
   // setInterval(() => {
   //     const activeButton = document.querySelector('.btn-group button.active');
   //     if (activeButton && activeButton.textContent.trim() !== 'Completed' && activeButton.textContent.trim() !== 'Cancelled') {
   //         location.reload();
   //     }
   // }, 30000);
</script>
@endsection
