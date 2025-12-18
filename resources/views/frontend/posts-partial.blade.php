@if(isset($posts) && $posts->count() > 0)
@foreach($posts as $post)
{{-- শুধুমাত্র cat_type = 'post' যেগুলোর --}}
@if($post->category && $post->category->cat_type == 'post')
    @include('frontend.post-card-only')
@endif
@endforeach
@else
<div class="alert alert-info text-center my-4">
   <i class="bi bi-info-circle me-2"></i>
   <strong>No posts available</strong> at the moment.
</div>
@endif
    @include('frontend.post-card-script')
    <script>
                        document.addEventListener('DOMContentLoaded', function () {
                            const video = document.querySelector('#mixedMediaCarousel-{{ $post->id }} .carousel-item.active video.post-carousel-video');
                            if (video) {
                                // Autoplay when the slide is shown
                                video.play().catch(()=>{});
                            }
                        
                            let observer;
                            // Auto-pause when scrolled out of view
                            setTimeout(function () {
                                const vid = document.querySelector('#mixedMediaCarousel-{{ $post->id }} .carousel-item.active video.post-carousel-video');
                                if (!vid) return;
                                observer = new IntersectionObserver((entries) => {
                                    entries.forEach(entry => {
                                        if (entry.isIntersecting) {
                                            vid.play().catch(()=>{});
                                        } else {
                                            vid.pause();
                                        }
                                    });
                                }, { threshold: 0.5 }); // 50% visible threshold
                                observer.observe(vid);
                            }, 400);
                        
                            // When slide changes, play new, pause previous
                            const carousel = document.getElementById('mixedMediaCarousel-{{ $post->id }}');
                            if (carousel) {
                                carousel.addEventListener('slid.bs.carousel', function(event) {
                                    const videos = carousel.querySelectorAll('video.post-carousel-video');
                                    videos.forEach((v, idx) => v.pause());
                                    const newActive = carousel.querySelector('.carousel-item.active video.post-carousel-video');
                                    if (newActive) {
                                        newActive.play().catch(()=>{});
                                        if (observer) observer.disconnect();
                                        observer = new IntersectionObserver((entries) => {
                                            entries.forEach(entry => {
                                                if (entry.isIntersecting) {
                                                    newActive.play().catch(()=>{});
                                                } else {
                                                    newActive.pause();
                                                }
                                            });
                                        }, { threshold: 0.5 });
                                        observer.observe(newActive);
                                    }
                                });
                            }
                        });
                     </script>
                      <script>
                           // Unified mute/unmute handling for all videos
                           document.addEventListener('DOMContentLoaded', function () {
                               let globalMutePref = localStorage.getItem('globalVideoMuted');
                               if (globalMutePref === null) globalMutePref = "true"; // default: muted
                           
                               function setAllVideosMuted(muted) {
                                   document.querySelectorAll('video.post-carousel-video').forEach(video => {
                                       video.muted = muted;
                                       // For some browsers, changing mute requires reloading playback state
                                       if (!video.paused && !muted && video.readyState >= 2) {
                                           video.play().catch(()=>{});
                                       }
                                   });
                               }
                           
                               // Set the mute state initially
                               setAllVideosMuted(globalMutePref === "true");
                           
                               // Listen for mute/unmute actions on any .post-carousel-video
                               let listening = false;
                               if (!window._global_video_mute_listener) {
                                   window._global_video_mute_listener = true;
                                   document.addEventListener('volumechange', function(event) {
                                       let target = event.target;
                                       if (target && target.classList && target.classList.contains('post-carousel-video')) {
                                           localStorage.setItem('globalVideoMuted', target.muted ? "true" : "false");
                                           setAllVideosMuted(target.muted);
                                       }
                                   }, true);
                                   // For browsers that do not propagate 'volumechange' outside video, use event delegation
                                   document.body.addEventListener('click', function(e) {
                                       if (e.target && e.target.tagName === 'VIDEO' && e.target.classList.contains('post-carousel-video')) {
                                           setTimeout(() => {
                                               localStorage.setItem('globalVideoMuted', e.target.muted ? "true" : "false");
                                               setAllVideosMuted(e.target.muted);
                                           }, 50);
                                       }
                                   }, true);
                               }
                           });
                        </script>