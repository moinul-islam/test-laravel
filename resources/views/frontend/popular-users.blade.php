@extends("frontend.master")
@section('main-content')

<div class="container mx-auto p-4">
    <h1 class="text-2xl font-bold mb-4">Top 100 Users by Points</h1>

    <table class="table-auto w-full border border-gray-300">
        <thead>
            <tr class="bg-gray-200">
                <th class="px-4 py-2 border">Rank</th>
                <th class="px-4 py-2 border">User</th>
                <th class="px-4 py-2 border">Points</th>
            </tr>
        </thead>
        <tbody>
            @foreach($users as $index => $user)
            <tr class="{{ $index % 2 == 0 ? 'bg-white' : 'bg-gray-100' }}">
                <td class="px-4 py-2 border">{{ $index + 1 }}</td>
                <td class="px-4 py-2 border">
                    <a href="{{ url('/' . $user->username) }}" class="text-primary hover:underline">
                        {{ $user->name }}
                    </a>
                </td>
                <td class="px-4 py-2 border">{{ $user->users_points }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endsection
