<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\Expense;
use App\Models\Income;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $projects = Project::all();
        $project_id = $request->get('project_id');

        $incomeQuery = Income::query();
        $expenseQuery = Expense::query();

        if ($project_id) {
            $incomeQuery->where('project_id', $project_id);
            $expenseQuery->where('project_id', $project_id);
        }

        $totalIncome = $incomeQuery->sum('amount');
        $totalExpense = $expenseQuery->sum('amount');
        $profit = $totalIncome - $totalExpense;

        // recent transactions
        $recentExpenses = Expense::with('project', 'employee')->latest()->limit(6)->get();
        $recentIncomes = Income::with('project')->latest()->limit(6)->get();

        return view('dashboard', compact('projects', 'project_id', 'totalIncome', 'totalExpense', 'profit', 'recentExpenses', 'recentIncomes'));
    }
}
