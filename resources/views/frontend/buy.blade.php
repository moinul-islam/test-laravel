@extends('frontend.master')
@section('main-content')
<div class="container container-fluid px-2 py-4">
   <div class="row">
      <div class="col-12">
         <div class="d-flex justify-content-between align-items-center mb-4">
            <h3 class="mb-0">
               <i class="bi bi-bag-check me-2"></i>My Orders
            </h3>
            <div class="badge bg-primary fs-6">
               {{ $orders->total() }} Total Orders
            </div>
         </div>
         @if($orders->count() > 0)
         <div class="row">
            @foreach($orders as $order)
            <div class="col-12 mb-4">
               <div class="card shadow-sm border-0">
                  <div class="card-header bg-light d-flex justify-content-between align-items-center" data-bs-toggle="collapse" data-bs-target="#orderBody{{ $order->id }}">
                     <div>
                        <h6 class="mb-0">Order #{{ $order->id }}</h6>
                        <small class="text-muted">{{ $order->created_at->timezone('Asia/Dhaka')->format('d M Y - h:i A') }}</small>
                     </div>
                     <div class="text-end">
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
                           <div class="d-flex align-items-center mb-2 border p-2 rounded">
                              <img src="{{ asset('profile-image/' . ($order->vendor->image ?? 'default.png')) }}" 
                                 alt="Vendor" class="rounded me-3" 
                                 style="width: 40px; height: 40px; object-fit: cover;">
                              <div>
                                 <h6 class="mb-0">{{ $order->vendor->name }}</h6>
                                 <small class="text-muted"><i class="bi bi-telephone-fill"></i> <a href="tel:{{ $order->vendor->phone_number }}">{{ $order->vendor->phone_number }}</a> <i class="bi bi-geo-alt-fill"></i> {{ $order->vendor->area }}</small>
                              </div>
                           </div> 
                           <div class="d-flex align-items-center mb-2 border p-2 rounded">
                              <img src="{{ asset('profile-image/' . ($order->user->image ?? 'default.png')) }}" 
                                 alt="Vendor" class="rounded me-3" 
                                 style="width: 40px; height: 40px; object-fit: cover;">
                              <div>
                                 <h6 class="mb-0">{{ $order->user->name }}</h6>
                                 <small class="text-muted"><i class="bi bi-telephone-fill"></i> <a href="tel:{{ $order->phone }}">{{ $order->phone }}</a> <i class="bi bi-geo-alt-fill"></i> {{ $order->shipping_address }}</small>
                              </div>
                           </div>
                           @if($order->delivery_person_id)
                           <div class="d-flex align-items-center mb-2 border p-2 rounded">
                              <img src="{{ asset('profile-image/' . ($order->deliveryman->image ?? 'default.png')) }}" 
                                 alt="Vendor" class="rounded me-3" 
                                 style="width: 40px; height: 40px; object-fit: cover;">
                              <div>
                                 <h6 class="mb-0">{{ $order->deliveryman->name }}</h6>
                                 <small class="text-muted"><i class="bi bi-telephone-fill"></i> <a href="tel:{{ $order->deliveryman->phone_number }}">{{ $order->deliveryman->phone_number }}</a> <span>(Delivery Man)</span>
                                 </small>
                              </div>
                           </div>
                           @endif
                           
                           <!-- Ordered Items -->
                           <div class="mt-3">
                              <h6>Ordered Items: {{ count($order->post_ids) }} products</h6>
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
                                    <strong>{{ $post->price * $post->ordered_quantity }}</strong>
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
                              <h4 class="text-primary mb-0">৳{{ number_format($order->total_amount, 2) }}</h4>
                              <small class="text-muted">Total Amount</small>
                           </div>
                           <div class="d-grid gap-2">                                                
                              @if($order->status == 'pending')
                              <button class="btn btn-outline-danger btn-sm" onclick="showCancelModal({{ $order->id }})">
                              <i class="bi bi-x-circle me-1"></i>Cancel Order
                              </button>
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
               <i class="bi bi-bag-x" style="font-size: 4rem; color: #6c757d;"></i>
            </div>
            <h5 class="text-muted">No Orders Yet</h5>
            <p class="text-muted">You haven't placed any orders. Start shopping to see your orders here!</p>
            <a href="/" class="btn btn-primary">
            <i class="bi bi-shop me-2"></i>Start Shopping
            </a>
         </div>
         @endif
      </div>
   </div>
</div>
<!-- Cancel Order Modal -->
<div class="modal fade" id="cancelOrderModal" tabindex="-1" aria-labelledby="cancelOrderModalLabel" aria-hidden="true">
   <div class="modal-dialog">
      <div class="modal-content">
         <div class="modal-header">
            <h5 class="modal-title" id="cancelOrderModalLabel">
               <i class="bi bi-x-circle-fill text-danger me-2"></i>Cancel Order
            </h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
         </div>
         <div class="modal-body">
            <div class="alert alert-warning">
               <i class="bi bi-exclamation-triangle me-2"></i>
               Are you sure you want to cancel this order?
            </div>
            <div class="mb-3">
               <label for="cancelReason" class="form-label">Cancellation Reason <span class="text-danger">*</span></label>
               <textarea class="form-control" id="cancelReason" rows="3" 
                  placeholder="Please provide a reason for cancelling this order..." 
                  maxlength="255"></textarea>
               <div class="form-text">Maximum 255 characters</div>
               <div class="invalid-feedback" id="reasonError">
                  Please provide a cancellation reason.
               </div>
            </div>
         </div>
         <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
            <i class="bi bi-arrow-left me-1"></i>Keep Order
            </button>
            <button type="button" class="btn btn-danger" onclick="confirmCancelOrder()">
            <i class="bi bi-x-circle me-1"></i>Cancel Order
            </button>
         </div>
      </div>
   </div>
</div>
<script>
   let currentOrderId = null;
   
   function showCancelModal(orderId) {
       currentOrderId = orderId;
       document.getElementById('cancelReason').value = '';
       document.getElementById('cancelReason').classList.remove('is-invalid');
       
       // Show the modal
       const modal = new bootstrap.Modal(document.getElementById('cancelOrderModal'));
       modal.show();
   }
   
   function confirmCancelOrder() {
       const reason = document.getElementById('cancelReason').value.trim();
       const reasonInput = document.getElementById('cancelReason');
       const errorDiv = document.getElementById('reasonError');
       
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
       const cancelBtn = document.querySelector('#cancelOrderModal .btn-danger');
       const originalText = cancelBtn.innerHTML;
       cancelBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>Cancelling...';
       cancelBtn.disabled = true;
       
       // Send cancel request
       fetch(`/orders/${currentOrderId}/cancel`, {
           method: 'PATCH',
           headers: {
               'Content-Type': 'application/json',
               'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
               'Accept': 'application/json'
           },
           body: JSON.stringify({ 
               status: 'cancelled',
               cancel_reason: reason 
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
               const modal = bootstrap.Modal.getInstance(document.getElementById('cancelOrderModal'));
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
   
   // Close modal on escape key
   document.addEventListener('keydown', function(event) {
       if (event.key === 'Escape') {
           const modal = bootstrap.Modal.getInstance(document.getElementById('cancelOrderModal'));
           if (modal) {
               modal.hide();
           }
       }
   });
</script>
@endsection
