@extends('layouts.app')

@section('content')
<div class="max-w-3xl mx-auto p-6">
    <h1 class="text-2xl font-bold mb-4">Edit Transaction</h1>

    <form action="{{ route('transactions.update', $transaction->id) }}" method="POST" class="space-y-4">
        @csrf
        @method('PUT')

        <div>
            <label class="block mb-1">Type</label>
            <select name="type" class="w-full border rounded p-2" required>
                <option value="income" {{ $transaction->type == 'income' ? 'selected' : '' }}>Income</option>
                <option value="expense" {{ $transaction->type == 'expense' ? 'selected' : '' }}>Expense</option>
            </select>
        </div>

        <div>
            <label class="block mb-1">Amount (Rs.)</label>
            <input type="number" name="amount" step="0.01" class="w-full border rounded p-2" value="{{ $transaction->amount }}" required>
        </div>

        <div>
            <label class="block mb-1">Description</label>
            <input type="text" name="description" class="w-full border rounded p-2" value="{{ $transaction->description }}">
        </div>

        <button type="submit" class="bg-blue-500 text-white px-4 py-2 rounded">Update</button>
    </form>
</div>
@endsection
