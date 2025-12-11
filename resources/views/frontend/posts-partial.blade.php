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