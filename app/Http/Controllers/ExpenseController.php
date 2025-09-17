<?php

namespace App\Http\Controllers;

use id;
use Carbon\Carbon;
use App\Models\Expense;
use App\Models\Project;
use App\Models\Employee;
use Illuminate\Http\Request;
use Illuminate\Container\Attributes\Auth;

class ExpenseController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $projects = Project::all();
        $employees = Employee::all();

        $query = Expense::with(['project', 'employee']);

        //filters
        if ($request->project_id) $query->where('project_id', $request->project_id);
        if ($request->employee_id) $query->where('employee_id', $request->employee_id);
        if ($request->from) $query->whereDate('date', '>=', $request->from);
        if ($request->to) $query->whereDate('date', '<=', $request->to);

        $expenses = $query->orderBy('date', 'desc')->paginate(20);
        return view('expenses.index', compact('expenses', 'projects', 'employees'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $projects = Project::all();
        $employees = Employee::all();
        return view('expenses.create', compact('projects', 'employees'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'project_id' => 'required|exists:projects,id',
            'employee_id' => 'required|exists:employees,id',
            'type' => 'required|string',
            'amount' => 'required|numeric',
            'date' => 'required|date',
            'description' => 'nullable|string',
            'receipt' => 'nullable|file|max:5120', // max 5MB
        ]);

        // check duplicate (server side safety) - 14 days window
        if (!empty($data['employee_id'])) {
            $last = Expense::where('project_id', $data['project_id'])
                ->where('employee_id', $data['employee_id'])
                ->orderBy('created_at', 'desc')
                ->first();

            if ($last) {
                $days = Carbon::parse($last->date)->diffInDays(Carbon::parse($data['date']));
                if ($days <= 14) {
                    return back()->withInput()->with('duplicate_warning', "Warning: Employee was paid {$last->amount} on {$last->date->toDateString()} ({$days} days ago). If you want to continue, submit again.");
                }
            }
        }


        if ($request->hasFile('receipt')) {
            $path = $request->file('receipt')->store('receipts', 'public');
            $data['receipt_path'] = $path;
        }

        $data['user_id'] = Auth::id();
        Expense::create($data);

        return redirect()->route('expenses.index')->with('success', 'Expense added.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Expense $expense)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Expense $expense)
    {
        $projects = Project::all();
        $employees = Employee::all();
        return view('expenses.edit', compact('expense', 'projects', 'employees'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Expense $expense)
    {
        $data = $request->validate([
            'project_id' => 'required|exists:projects,id',
            'employee_id' => 'nullable|exists:employees,id',
            'type' => 'required|string',
            'amount' => 'required|numeric',
            'date' => 'required|date',
            'description' => 'nullable|string',
            'receipt' => 'nullable|file|max:5120', // max 5MB
        ]);

        if ($request->hasFile('receipt')) {
            $path = $request->file('receipt')->store('receipts', 'public');
            $data['receipt_path'] = $path;
        }

        $expense->update($data);
        return redirect()->route('expenses.index')->with('success', 'Expense updated.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Expense $expense)
    {
        $expense->delete();
        return back()->with('success', 'Deleted');
    }

    // AJAX endpoint for duplicate check
    public function checkDuplicate(Request $request)
    {
        $project_id = $request->query('project_id');
        $employee_id = $request->query('employee_id');

        if (!$project_id || !$employee_id) {
            return response()->json(['found' => false]);
        }

        $last = Expense::where('project_id', $project_id)
            ->where('employee_id', $employee_id)
            ->orderBy('date', 'desc')->first();

        if (!$last) {
            return response()->json(['found' => false]);
        }

        $days = Carbon::now()->diffInDays(Carbon::parse($last->date));
        return response()->json([
            'found' => true,
            'last_date' => $last->date->toDateString(),
            'last_amount' => $last->amount,
            'days' => $days
        ]);
    }

    // simple export (we will create real Excel export later)
    public function export(Request $request)
    {
        // quick CSV
        $query = Expense::query();
        if ($request->project_id) $query->where('project_id', $request->project_id);
        if ($request->from) $query->whereDate('date', '>=', $request->from);
        if ($request->to) $query->whereDate('date', '<=', $request->to);

        $rows = $query->get()->map(function ($e) {
            return [
                'project' => $e->project->name,
                'employee' => $e->employee ? $e->employee->name : '-',
                'type' => $e->type,
                'amount' => $e->amount,
                'date' => $e->date->toDateString(),
                'description' => $e->description,
            ];
        })->toArray();

        $filename = 'expenses_export_' . date('Ymd_His') . '.csv';
        $handle = fopen(storage_path("app/{$filename}"), 'w+');
        fputcsv($handle, array_keys($rows[0] ?? ['project', 'employee', 'type', 'amount', 'date', 'description']));
        foreach ($rows as $row) fputcsv($handle, $row);
        fclose($handle);

        return response()->download(storage_path("app/{$filename}"))->deleteFileAfterSend(true);
    }
}
