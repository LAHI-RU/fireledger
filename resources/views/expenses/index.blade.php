@extends('layouts.app')

@section('content')
<div class="container mx-auto p-4">
    <div class="flex justify-between items-center mb-4">
        <h1 class="text-xl font-bold">Expenses</h1>
        <a href="{{ route('expenses.create') }}" class="px-4 py-2 bg-blue-600 text-white rounded">+ Add Expense</a>
    </div>

    <form class="mb-4 grid grid-cols-1 md:grid-cols-4 gap-2">
        <select name="project_id" class="border p-2 rounded" onchange="this.form.submit()">
            <option value="">All Projects</option>
            @foreach($projects as $p)<option value="{{ $p->id }}" {{ request('project_id')==$p->id ? 'selected' : '' }}>{{ $p->name }}</option>@endforeach
        </select>

        <select name="employee_id" class="border p-2 rounded" onchange="this.form.submit()">
            <option value="">All Employees</option>
            @foreach($employees as $emp)<option value="{{ $emp->id }}" {{ request('employee_id')==$emp->id ? 'selected' : '' }}>{{ $emp->name }}</option>@endforeach
        </select>

        <input type="date" name="from" value="{{ request('from') }}" class="border p-2 rounded" onchange="this.form.submit()">
        <input type="date" name="to" value="{{ request('to') }}" class="border p-2 rounded" onchange="this.form.submit()">
    </form>

    <div class="bg-white rounded shadow">
        <table class="w-full">
            <thead class="bg-gray-100">
                <tr>
                    <th class="p-2 text-left">Date</th>
                    <th class="p-2 text-left">Project</th>
                    <th class="p-2 text-left">Employee</th>
                    <th class="p-2 text-left">Type</th>
                    <th class="p-2 text-right">Amount</th>
                    <th class="p-2 text-left">Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach($expenses as $e)
                <tr class="border-t">
                    <td class="p-2">{{ $e->date->toDateString() }}</td>
                    <td class="p-2">{{ $e->project->name }}</td>
                    <td class="p-2">{{ $e->employee?->name }}</td>
                    <td class="p-2">{{ ucfirst($e->type) }}</td>
                    <td class="p-2 text-right">Rs. {{ number_format($e->amount,2) }}</td>
                    <td class="p-2">
                        <a href="{{ route('expenses.edit',$e) }}" class="text-blue-600">Edit</a>
                        <form action="{{ route('expenses.destroy',$e) }}" method="POST" class="inline-block" onsubmit="return confirm('Delete?')">
                            @csrf @method('DELETE')
                            <button class="text-red-600 ml-2">Delete</button>
                        </form>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>

        <div class="p-4">
            {{ $expenses->links() }}
        </div>
    </div>
</div>
@endsection
