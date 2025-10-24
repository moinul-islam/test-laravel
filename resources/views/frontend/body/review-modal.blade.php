{{-- Review Modal for each item --}}
              <div class="modal fade" id="reviewModal{{ $item->id }}" tabindex="-1" aria-hidden="true">
                  <div class="modal-dialog modal-dialog-scrollable">
                      <div class="modal-content">
                          <div class="modal-header">
                              <h5 class="modal-title">
                                  {{ $isUserProfile ? 'Reviews for ' . $item->name : 'Reviews for ' . $item->title }}
                              </h5>
                              <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                          </div>
                          <div class="modal-body">
                              {{-- Review Summary --}}
                              <!-- <div class="review-summary mb-4 p-3 bg-light rounded">
                                  <div class="d-flex align-items-center gap-3">
                                      <div class="text-center">
                                          <h2 class="mb-0">{{ number_format($item->averageRating(), 1) }}</h2>
                                          <div class="text-warning">
                                              @for($i = 1; $i <= 5; $i++)
                                                  @if($i <= floor($item->averageRating()))
                                                      <i class="bi bi-star-fill"></i>
                                                  @elseif($i - 0.5 <= $item->averageRating())
                                                      <i class="bi bi-star-half"></i>
                                                  @else
                                                      <i class="bi bi-star"></i>
                                                  @endif
                                              @endfor
                                          </div>
                                          <small class="text-muted">{{ $item->reviewCount() }} reviews</small>
                                      </div>
                                  </div>
                              </div> -->

                             
                              {{-- Reviews List --}}
                              <div class="reviews-list">
                                  @if($item->reviews->isEmpty())
                                      <div class="text-center py-4 text-muted">
                                          <i class="bi bi-chat-quote" style="font-size: 3rem;"></i>
                                          <p class="mt-2">No reviews yet. Be the first to leave a review!</p>
                                      </div>
                                  @else
                                      @foreach($item->reviews as $review)
                                          <div class="review-item border-bottom pb-3 mb-3">
                                              <div class="d-flex gap-2 mb-2">
                                              <img src="{{ asset('profile-image/' . $review->user->image) }}"
                                             class="rounded-circle"
                                             style="width: 40px; height: 40px; object-fit: cover;">
                                                  <div class="flex-grow-1">
                                                      <div class="d-flex justify-content-between align-items-start">
                                                          <div>
                                                              <h6 class="mb-0">{{ $review->user ? $review->user->name : 'Anonymous' }}</h6>
                                                              <div class="text-warning small">
                                                                  @for($i = 1; $i <= 5; $i++)
                                                                      @if($i <= $review->rating)
                                                                          <i class="bi bi-star-fill"></i>
                                                                      @else
                                                                          <i class="bi bi-star"></i>
                                                                      @endif
                                                                  @endfor
                                                              </div>
                                                              <small class="text-muted">{{ $review->created_at->format('M d, Y') }}</small>
                                                          </div>
                                                          
                                                          @auth
                                                              @if(Auth::id() == $review->user_id)
                                                                  <div class="dropdown">
                                                                      <button class="btn btn-sm btn-link text-muted" type="button" data-bs-toggle="dropdown">
                                                                          <i class="bi bi-three-dots-vertical"></i>
                                                                      </button>
                                                                      <ul class="dropdown-menu">
                                                                          <li>
                                                                              <a class="dropdown-item" href="#" onclick="editReview('{{ $review->id }}', '{{ $review->rating }}', '{{ addslashes($review->comment) }}', '{{ $item->id }}')">
                                                                                  <i class="bi bi-pencil"></i> Edit
                                                                              </a>
                                                                          </li>
                                                                          <li>
                                                                              <a class="dropdown-item text-danger" href="#" onclick="confirmDeleteReview('{{ $review->id }}')">
                                                                                  <i class="bi bi-trash"></i> Delete
                                                                              </a>
                                                                          </li>
                                                                      </ul>
                                                                  </div>
                                                              @endif
                                                          @endauth
                                                      </div>
                                                      
                                                      <div id="review-content-{{ $review->id }}" class="mt-2">
                                                          {{ $review->comment }}
                                                      </div>
                                                      
                                                      {{-- Edit Review Form --}}
                                                      @auth
                                                          @if(Auth::id() == $review->user_id)
                                                              <div id="edit-review-form-{{ $review->id }}" style="display: none;">
                                                                  <form action="{{ route('review.update', $review->id) }}" method="POST">
                                                                      @csrf
                                                                      <div class="rating-selector mb-2">
                                                                          <div class="rating-stars">
                                                                              @for($i = 1; $i <= 5; $i++)
                                                                                  <span class="edit-star" data-rating="{{ $i }}" data-review="{{ $review->id }}" style="cursor: pointer; font-size: 24px;">
                                                                                      <i class="bi bi-star{{ $i <= $review->rating ? '-fill' : '' }}"></i>
                                                                                  </span>
                                                                              @endfor
                                                                          </div>
                                                                          <input type="hidden" name="rating" id="edit-rating-{{ $review->id }}" value="{{ $review->rating }}">
                                                                      </div>
                                                                      <textarea name="comment" class="form-control mb-2" rows="3" id="edit-comment-{{ $review->id }}">{{ $review->comment }}</textarea>
                                                                      <div class="d-flex gap-2">
                                                                          <button type="button" class="btn btn-secondary btn-sm" onclick="cancelEditReview('{{ $review->id }}')">Cancel</button>
                                                                          <button type="submit" class="btn btn-primary btn-sm">Update</button>
                                                                      </div>
                                                                  </form>
                                                              </div>
                                                          @endif
                                                      @endauth
                                                  </div>
                                              </div>
                                          </div>
                                      @endforeach
                                  @endif
                              </div>
                          </div>
                      </div>
                  </div>
              </div>
