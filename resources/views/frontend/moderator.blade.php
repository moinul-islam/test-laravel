@extends('frontend.master')
@section('main-content')

<div class="container py-4">
    <h2 class="mb-4">মডারেটর টিকেট তালিকা</h2>
    <div class="table-responsive">
        <table class="table table-bordered table-striped align-middle">
            <thead class="table-dark">
                <tr>
                    <th>ইউজার নাম</th>
                    <th>ফোন</th>
                    <th>টিকেট সংখ্যা</th>
                    <th>অর্বতমান মডারেটর</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                @php
                    // mela_ticket টেবিল থেকে সকল টিকেটের এন্ট্রি
                    $tickets = \Illuminate\Support\Facades\DB::table('mela_ticket')->get();
                @endphp
                @foreach($tickets as $ticket)
                    @php
                        $user = \App\Models\User::find($ticket->user_id);
                        $moderator = $ticket->moderator_id ? \App\Models\User::find($ticket->moderator_id) : null;
                    @endphp
                    <tr>
                        <td>
                            {{ $user?->name ?? '-' }}
                        </td>
                        <td>
                            <small class="text-muted">{{ $user?->phone_number }}</small>
                        </td>
                        <td>
                            <span class="fw-bold">{{ $ticket->user_ticket }}</span>
                        </td>
                        <td>
                            @if($moderator)
                                <span class="badge bg-success">{{ $moderator->name }}</span>
                                <br>
                                <small>{{ $moderator->phone_number }}</small>
                            @else
                                <span class="text-danger">None</span>
                            @endif
                        </td>
                        <td>
                            @if(!$ticket->moderator_id)
                                <form action="{{ route('mela_ticket.accept') }}" method="POST" style="display:inline;">
                                    @csrf
                                    <input type="hidden" name="ticket_id" value="{{ $ticket->id }}">
                                    <button type="submit" class="btn btn-sm btn-primary" onclick="return confirm('আপনি কি এই টিকেট গ্রহণ করতে চান?');">
                                        <i class="bi bi-telephone"></i> Accept
                                    </button>
                                </form>
                            @else
                                <span class="badge bg-secondary">Accepted</span>
                            @endif
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>



@endsection