@extends('layouts.app')

@section('content')
<div class="max-w-6xl mx-auto p-6">
    <h1 class="text-2xl font-bold mb-4">All Transactions</h1>

    <a href="{{ route('transactions.create') }}" class="bg-blue-500 text-white px-4 py-2 rounded mb-4 inline-block">+ Add Transaction</a>

    @if(session('success'))
        <div class="bg-green-100 text-green-700 p-2 rounded mb-3">
            {{ session('success') }}
        </div>
    @endif

    <table class="w-full border-collapse border border-gray-300">
        <thead>
            <tr class="bg-gray-100">
                <th class="border p-2">#</th>
                <th class="border p-2">Type</th>
                <th class="border p-2">Amount (Rs.)</th>
                <th class="border p-2">Description</th>
                <th class="border p-2">Date</th>
                <th class="border p-2">Actions</th>
            </tr>
        </thead>
        <tbody>
            @foreach($transactions as $transaction)
            <tr>
                <td class="border p-2">{{ $transaction->id }}</td>
                <td class="border p-2">{{ ucfirst($transaction->type) }}</td>
                <td class="border p-2">{{ number_format($transaction->amount, 2) }}</td>
                <td class="border p-2">{{ $transaction->description }}</td>
                <td class="border p-2">{{ $transaction->created_at->format('Y-m-d') }}</td>
                <td class="border p-2">
                    <a href="{{ route('transactions.edit', $transaction->id) }}" class="text-blue-500">Edit</a> |
                    <form action="{{ route('transactions.destroy', $transaction->id) }}" method="POST" class="inline">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="text-red-500" onclick="return confirm('Delete this transaction?')">Delete</button>
                    </form>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <div class="mt-4">
        {{ $transactions->links() }}
    </div>
</div>
@endsection
