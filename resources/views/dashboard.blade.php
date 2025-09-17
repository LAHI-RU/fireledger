@extends('layouts.app')

@section('content')
<div class="container mx-auto p-4">
    <h1 class="text-2xl font-bold mb-4">FireLedger Dashboard</h1>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
        <div class="p-4 bg-white rounded shadow">
            <div class="text-sm text-gray-500">Total Income</div>
            <div class="text-2xl font-bold">Rs. {{ number_format($totalIncome,2) }}</div>
        </div>
        <div class="p-4 bg-white rounded shadow">
            <div class="text-sm text-gray-500">Total Expense</div>
            <div class="text-2xl font-bold">Rs. {{ number_format($totalExpense,2) }}</div>
        </div>
        <div class="p-4 rounded shadow" :class="{'bg-green-50': {{ $profit }} >= 0}">
            <div class="text-sm text-gray-500">Profit</div>
            <div class="text-2xl font-bold">Rs. {{ number_format($profit,2) }}</div>
        </div>
    </div>

    <div class="bg-white p-4 rounded shadow">
        <form method="GET" action="{{ route('dashboard') }}" class="flex gap-2 items-center">
            <select name="project_id" class="border p-2 rounded">
                <option value="">All Projects</option>
                @foreach($projects as $p)
                    <option value="{{ $p->id }}" {{ (old('project_id',$project_id)==$p->id) ? 'selected':'' }}>
                        {{ $p->name }} ({{ $p->status }})
                    </option>
                @endforeach
            </select>
            <button class="px-3 py-2 bg-blue-600 text-white rounded">Filter</button>
        </form>
    </div>

    <div class="mt-6 grid md:grid-cols-2 gap-4">
        <div class="bg-white p-4 rounded shadow">
            <h3 class="font-semibold mb-2">Recent Expenses</h3>
            <ul>
                @foreach($recentExpenses as $e)
                <li class="border-b py-2">
                    <div class="flex justify-between">
                        <div>
                            <div class="text-sm text-gray-500">{{ $e->project->name }} — {{ $e->employee?->name }}</div>
                            <div>{{ $e->description }}</div>
                        </div>
                        <div class="font-bold">Rs. {{ number_format($e->amount,2) }}</div>
                    </div>
                </li>
                @endforeach
            </ul>
        </div>

        <div class="bg-white p-4 rounded shadow">
            <h3 class="font-semibold mb-2">Recent Incomes</h3>
            <ul>
                @foreach($recentIncomes as $inc)
                <li class="border-b py-2">
                    <div class="flex justify-between">
                        <div>
                            <div class="text-sm text-gray-500">{{ $inc->project->name }}</div>
                            <div>{{ $inc->description }}</div>
                        </div>
                        <div class="font-bold">Rs. {{ number_format($inc->amount,2) }}</div>
                    </div>
                </li>
                @endforeach
            </ul>
        </div>
    </div>
</div>
@endsection
