@extends("frontend.master")
@section('main-content')
<div class="container">
    <h2>Send SMS</h2>

    @if(session('status'))
        <div style="color: green; margin-bottom: 15px;">
            {{ session('status') }}
        </div>
    @endif

    <form method="POST" action="{{ route('sms.send') }}">
        @csrf

        <div style="margin-bottom: 10px;">
            <label for="phone">Phone Number:</label><br>
            <input type="text" name="phone" id="phone" placeholder="+8801XXXXXXXXX" required style="width: 300px; padding:5px;">
        </div>

        <div style="margin-bottom: 10px;">
            <label for="message">Message:</label><br>
            <textarea name="message" id="message" rows="4" placeholder="Enter your SMS text here..." required style="width: 300px; padding:5px;"></textarea>
        </div>

        <button type="submit" style="padding:8px 15px; background:blue; color:white; border:none; cursor:pointer;">
            Send SMS
        </button>
    </form>
</div>
@endsection
