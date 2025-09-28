@extends('frontend.master')
@section('main-content')
<div class="py-4 ms-3 me-3">
    <div class="mb-4">
        <a href="/categories" class="btn btn-outline-success">Categories</a>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered table-striped">
                <thead>
                    <tr>
                        <!-- <th>Image</th> -->
                        <th>SN</th>                                  
                        <th>Name</th>                                  
                        <th>Time</th>                                  
                        <th>Job Title</th>
                        <th>Category</th>
                        <th>Country</th>
                        <th>City</th>
                        <th>Area</th>
                        <th>Email</th>
                        <th>Verified</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($users as $user)
                    @php
                        $rowClass = ($user->email_verified !== 0) ? 'table-danger' : '';
                    @endphp
                    <tr class="{{ $rowClass }}">
                        <td>{{ ($users->total() - ($users->firstItem() - 1)) - $loop->index }}</td>
                        <td>
                            <a href="/{{ $user->username }}">
                                {{ $user->name }} <span class="badge bg-primary">{{ $user->role }}</span>
                            </a>
                        </td>
                        <td style="width:110px;">{!! $user->created_at->timezone('Asia/Dhaka')->format('d M Y') !!} <br> {!! $user->created_at->timezone('Asia/Dhaka')->format('h:i A') !!}</td>
                        <td>{{ $user->job_title ?? 'N/A' }}</td>
                        <td>{{ $user->category->category_name ?? 'N/A' }}</td>
                        <td>{{ $user->country->name ?? 'N/A' }}</td>
                        <td>{{ $user->city->name ?? 'N/A' }}</td>
                        <td>{{ $user->area ?? 'N/A' }}</td>
                        <td>{{ $user->email ?? 'N/A' }}</td>
                        <td>
                            @if($user->email_verified === 0)
                                0
                            @else
                                {{ $user->email_verified ?? 'N/A' }}
                            @endif
                        </td>
                        <td>
                            <a href="#" class="btn btn-sm btn-info">
                                Edit
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="text-center">No users found</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        <!-- Pagination Links -->
        <div class="d-flex justify-content-center mt-3">
            {{ $users->links('pagination::bootstrap-4') }}
        </div>
    </div>
</div>
@endsection