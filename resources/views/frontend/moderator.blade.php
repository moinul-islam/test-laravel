@extends('frontend.master')
@section('main-content')

<div class="container py-4">
    <h2 class="mb-4">মডারেটর টিকেট তালিকা</h2>

    @php
        // Moderator কতগুলো টিকেট accept করেছে এবং তার user_ticket গুলোর যোগফল গণনা করা হচ্ছে
        $myTicketCount = \Illuminate\Support\Facades\DB::table('mela_ticket')
            ->where('moderator_id', auth()->id())
            ->sum('user_ticket');
        // Moderator কতগুলো ইউজার create করছে (users table-e contributor te tar id)
        $myTicketSellCount = \App\Models\User::where('contributor', auth()->id())->count();

        // জিনিষটা: যেসব user গুলো contributor হিসেবে moderator (ei user) ke দেখায়, তাদের জন্য মোট টিকেট যোগ করি
        $myUserIds = \App\Models\User::where('contributor', auth()->id())->pluck('id');
        $mySellTotalTicket = 0;
        if ($myUserIds->count() > 0) {
            $mySellTotalTicket = \Illuminate\Support\Facades\DB::table('mela_ticket')
                ->whereIn('user_id', $myUserIds)
                ->sum('user_ticket');
        }
        $amount = $mySellTotalTicket * 20;
    @endphp
    <p>আপনি মোটঃ <strong>{{ $myTicketCount }}</strong> টিকেট দিয়েছেন।</p>
    <p>আপনি মোটঃ <strong>{{ $myTicketSellCount }}</strong> জন ইউজারকে টিকেট বিক্রি করেছেন।</p>
    <p>আপনার কাছে মোটঃ <strong>{{ $amount }}</strong> টাকা আছে।</p>
    <!-- Ticket Sell Button -->
    <button type="button" class="btn btn-primary mb-3" data-bs-toggle="modal" data-bs-target="#sellTicketModal">
        নতুন টিকেট বিক্রি করুন
    </button>

    <!-- Modal -->
    <div class="modal fade" id="sellTicketModal" tabindex="-1" aria-labelledby="sellTicketModalLabel" aria-hidden="true">
      <div class="modal-dialog">
        <form method="POST" action="{{ route('moderator.sellTicket') }}">
            @csrf
            <div class="modal-content">
              <div class="modal-header">
                <h5 class="modal-title" id="sellTicketModalLabel">নতুন টিকেট বিক্রি</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
              </div>
              <div class="modal-body">
                  <div class="mb-3">
                      <label for="name" class="form-label">নাম</label>
                      <input type="text" class="form-control" id="name" name="name" required>
                  </div>
                  <div class="mb-3">
                      <label for="phone_number" class="form-label">ফোন নম্বর</label>
                      <input type="text" class="form-control" id="phone_number" name="phone_number" required>
                  </div>
                  <div class="mb-3">
                      <label for="ticket_quantity" class="form-label">টিকেট সংখ্যা</label>
                      <input type="number" class="form-control" id="ticket_quantity" name="ticket_quantity" min="1" required>
                  </div>
              </div>
              <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">বাতিল করুন</button>
                <button type="submit" class="btn btn-success">সেভ করুন</button>
              </div>
            </div>
        </form>
      </div>
    </div>

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