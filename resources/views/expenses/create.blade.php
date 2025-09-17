@extends('layouts.app')

@section('content')
<div class="container mx-auto p-4">
    <h1 class="text-xl font-bold mb-4">Add Expense</h1>

    @if(session('duplicate_warning'))
    <div class="bg-yellow-100 border-l-4 border-yellow-500 text-yellow-700 p-4 mb-4">
        {{ session('duplicate_warning') }}
        <div class="mt-2">
            <form method="POST" action="{{ route('expenses.store') }}" enctype="multipart/form-data">
                @csrf
                {{-- Re-submit hidden flag to bypass server side duplicate warning if user confirms --}}
                <input type="hidden" name="force_confirm" value="1">
                <button type="submit" class="px-3 py-2 bg-red-600 text-white rounded">Yes, add anyway</button>
                <a href="{{ route('expenses.create') }}" class="ml-2 px-3 py-2 bg-gray-300 rounded">Cancel</a>
            </form>
        </div>
    </div>
    @endif

    <form method="POST" action="{{ route('expenses.store') }}" enctype="multipart/form-data" id="expenseForm">
        @csrf
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label>Project</label>
                <select name="project_id" id="project_id" class="w-full border p-2 rounded">
                    <option value="">Select project</option>
                    @foreach($projects as $p)
                        <option value="{{ $p->id }}">{{ $p->name }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label>Employee (optional)</label>
                <select name="employee_id" id="employee_id" class="w-full border p-2 rounded">
                    <option value="">Select employee</option>
                    @foreach($employees as $emp)
                        <option value="{{ $emp->id }}">{{ $emp->name }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label>Type</label>
                <select name="type" class="w-full border p-2 rounded">
                    <option value="labour">Labour</option>
                    <option value="material">Material</option>
                    <option value="travel">Travel</option>
                    <option value="other">Other</option>
                </select>
            </div>

            <div>
                <label>Date</label>
                <input type="date" name="date" value="{{ date('Y-m-d') }}" class="w-full border p-2 rounded">
            </div>

            <div>
                <label>Amount (Rs.)</label>
                <input type="text" name="amount" id="amount" class="w-full border p-2 rounded" placeholder="20000">
            </div>

            <div>
                <label>Receipt (photo)</label>
                <input type="file" name="receipt" class="w-full border p-2 rounded">
            </div>

            <div class="md:col-span-2">
                <label>Description</label>
                <textarea name="description" id="description" rows="3" class="w-full border p-2 rounded" placeholder="Paid for labour..."></textarea>
            </div>

            <div class="md:col-span-2 flex items-center gap-2">
                <button type="button" id="voiceBtn" class="px-4 py-2 bg-green-600 text-white rounded">🎤 Voice</button>
                <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded">Save Expense</button>
                <a href="{{ route('expenses.index') }}" class="px-4 py-2 bg-gray-300 rounded">Cancel</a>
            </div>
        </div>
    </form>

    <div id="voiceHelp" class="mt-3 text-sm text-gray-600">
        Tip: press the Voice button and say: "Paid 20000 to Ramesh for labour" — app will try to fill amount and description.
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Duplicate check on employee change
    const employeeSelect = document.getElementById('employee_id');
    const projectSelect = document.getElementById('project_id');

    async function checkDuplicate() {
        const emp = employeeSelect.value;
        const proj = projectSelect.value;
        if (!emp || !proj) return;
        const url = "{{ route('api.check-duplicate') }}?project_id="+proj+"&employee_id="+emp;
        try {
            const res = await fetch(url);
            const data = await res.json();
            if (data.found) {
                alert("Note: This employee was paid Rs. " + data.last_amount + " on " + data.last_date + " ("+data.days+" days ago).");
            }
        } catch(err) {
            console.error(err);
        }
    }
    employeeSelect && employeeSelect.addEventListener('change', checkDuplicate);
    projectSelect && projectSelect.addEventListener('change', checkDuplicate);

    // Voice input using Web Speech API
    const btn = document.getElementById('voiceBtn');
    if (!('webkitSpeechRecognition' in window) && !('SpeechRecognition' in window)) {
        btn.disabled = true;
        document.getElementById('voiceHelp').innerText = "Voice not supported in this browser. Use Chrome on desktop or Android.";
    } else {
        const SpeechRecognition = window.SpeechRecognition || window.webkitSpeechRecognition;
        const recognition = new SpeechRecognition();
        recognition.lang = 'en-IN';
        recognition.interimResults = false;
        recognition.maxAlternatives = 1;

        btn.addEventListener('click', () => {
            recognition.start();
            btn.innerText = 'Listening... 🎙️';
        });

        recognition.onresult = (e) => {
            const text = e.results[0][0].transcript;
            parseVoice(text);
            btn.innerText = '🎤 Voice';
        }
        recognition.onerror = (e) => {
            console.error(e);
            btn.innerText = '🎤 Voice';
        }
    }

    function parseVoice(text) {
        // small parser: find amount and employee name and rest as description
        // amount: first number found
        const amountMatch = text.match(/(\d{1,3}(?:[,\s]\d{3})*(?:\.\d+)?|\d+)/);
        if (amountMatch) {
            const amt = amountMatch[0].replace(/[, ]/g,'');
            document.getElementById('amount').value = amt;
        }
        // find "to <name>"
        const toMatch = text.match(/to\s([a-zA-Z ]+)/i);
        if (toMatch) {
            const empName = toMatch[1].trim();
            // try to select existing employee by name
            const options = employeeSelect.options;
            for (let i=0;i<options.length;i++) {
                if (options[i].text.toLowerCase().includes(empName.toLowerCase())) {
                    employeeSelect.value = options[i].value;
                    break;
                }
            }
        }
        // rest put into description
        document.getElementById('description').value = text;
    }
});
</script>
@endsection
