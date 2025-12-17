@extends('frontend.master')
@section('main-content')

<div class="container py-4">
    <h2 class="mb-4">মডারেটর টিকেট তালিকা</h2>

    @php
        // Moderator কতগুলো টিকেট accept করেছে এবং তার user_ticket গুলোর যোগফল গণনা করা হচ্ছে
        $myTicketCount = \Illuminate\Support\Facades\DB::table('mela_ticket')
            ->where('moderator_id', auth()->id())
            ->sum('user_ticket');
    @endphp
    <p>আপনি মোটঃ <strong>{{ $myTicketCount }}</strong> টিকেট দিয়েছেন।</p>

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
                @foreach($tickets->sortByDesc('id') as $ticket)
                    @php
                        $user = \App\Models\User::find($ticket->user_id);
                        $moderator = $ticket->moderator_id ? \App\Models\User::find($ticket->moderator_id) : null;
                    @endphp
                    <tr>
                        <td>
                            {{ $user?->name ?? '-' }}
                        </td>
                        <td>
                            @if($user?->phone_number)
                                <a href="tel:{{ $user->phone_number }}">
                                    <small>{{ $user->phone_number }}</small>
                                </a>
                            @else
                                <small class="text-muted">-</small>
                            @endif
                        </td>
                        <td>
                            <span class="fw-bold">{{ $ticket->user_ticket }}</span>
                        </td>
                        <td>
                            @if($moderator)
                                <span class="badge bg-success">{{ $moderator->name }}</span>
                                <br>
                                <small>
                                    @if($moderator->phone_number)
                                        <a href="tel:{{ $moderator->phone_number }}" class="text-muted">{{ $moderator->phone_number }}</a>
                                    @else
                                        -
                                    @endif
                                </small>
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