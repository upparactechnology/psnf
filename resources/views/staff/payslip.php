<?php
$layout = false; // No layout for printable view
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payslip - <?= e($item['first_name'] . ' ' . $item['last_name']) ?> - <?= e($run['month_year']) ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        @media print {
            body { -webkit-print-color-adjust: exact; print-color-adjust: exact; }
            .no-print { display: none !important; }
        }
    </style>
</head>
<body class="bg-slate-100 text-slate-800 font-sans antialiased py-8">

    <div class="max-w-3xl mx-auto bg-white shadow-xl min-h-[800px] p-10 relative">
        <!-- Print Button -->
        <button onclick="window.print()" class="no-print absolute top-4 right-4 bg-indigo-600 text-white px-4 py-2 rounded shadow hover:bg-indigo-700 text-sm font-bold flex items-center gap-2">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
            Print Payslip
        </button>

        <!-- Header -->
        <div class="border-b-2 border-indigo-600 pb-6 mb-6 flex items-center justify-between">
            <div>
                <h1 class="text-3xl font-black text-indigo-900 tracking-tight">P.S.N.F PUBLIC SCHOOL</h1>
                <p class="text-sm text-slate-500 font-medium mt-1">123 Education Lane, Learning District, City</p>
                <p class="text-sm text-slate-500 font-medium">Contact: admin@psnf.edu | +91 98765 43210</p>
            </div>
            <div class="text-right">
                <h2 class="text-2xl font-bold text-slate-800 uppercase tracking-widest text-slate-300">PAYSLIP</h2>
                <p class="text-lg font-bold text-indigo-600 mt-1"><?= e($run['month_year']) ?></p>
                <p class="text-xs text-slate-400 mt-1">Generated: <?= date('d M, Y', strtotime($run['created_at'])) ?></p>
            </div>
        </div>

        <!-- Employee Details -->
        <div class="bg-slate-50 rounded-lg p-5 mb-8 border border-slate-200 grid grid-cols-2 gap-x-8 gap-y-4 text-sm">
            <div>
                <span class="text-slate-500 block text-xs uppercase tracking-wider mb-1">Employee Name</span>
                <span class="font-bold text-slate-800 text-lg"><?= e($item['first_name'] . ' ' . $item['last_name']) ?></span>
            </div>
            <div>
                <span class="text-slate-500 block text-xs uppercase tracking-wider mb-1">Employee ID</span>
                <span class="font-bold text-slate-800 text-lg font-mono"><?= e($item['emp_code']) ?></span>
            </div>
            <div>
                <span class="text-slate-500 block text-xs uppercase tracking-wider mb-1">Designation & Dept</span>
                <span class="font-semibold text-slate-700"><?= e($item['designation_title'] ?? 'Staff') ?> - <?= e($item['department_name'] ?? 'General') ?></span>
            </div>
            <div>
                <span class="text-slate-500 block text-xs uppercase tracking-wider mb-1">Date of Joining</span>
                <span class="font-semibold text-slate-700"><?= !empty($item['joining_date']) ? date('d M, Y', strtotime($item['joining_date'])) : 'N/A' ?></span>
            </div>
        </div>

        <!-- Attendance Summary -->
        <h3 class="text-sm font-bold text-slate-900 uppercase tracking-wider mb-3 border-b border-slate-200 pb-2">Attendance Summary</h3>
        <div class="grid grid-cols-4 gap-4 mb-8 text-center">
            <div class="p-3 bg-slate-50 rounded border border-slate-200">
                <span class="block text-xl font-bold text-slate-800"><?= (int)$item['working_days'] ?></span>
                <span class="block text-xs text-slate-500 uppercase mt-1">Working Days</span>
            </div>
            <div class="p-3 bg-emerald-50 rounded border border-emerald-100">
                <span class="block text-xl font-bold text-emerald-600"><?= (int)$item['present_days'] ?></span>
                <span class="block text-xs text-emerald-600 uppercase mt-1">Present</span>
            </div>
            <div class="p-3 bg-red-50 rounded border border-red-100">
                <span class="block text-xl font-bold text-red-600"><?= (int)$item['working_days'] - (int)$item['present_days'] ?></span>
                <span class="block text-xs text-red-600 uppercase mt-1">Absent</span>
            </div>
            <div class="p-3 bg-amber-50 rounded border border-amber-100">
                <span class="block text-xl font-bold text-amber-600"><?= (int)$item['late_days'] ?></span>
                <span class="block text-xs text-amber-600 uppercase mt-1">Late/Half-Day</span>
            </div>
        </div>

        <!-- Earnings & Deductions -->
        <div class="grid grid-cols-2 gap-8 mb-8">
            <!-- Earnings -->
            <div>
                <h3 class="text-sm font-bold text-slate-900 uppercase tracking-wider mb-3 border-b border-slate-200 pb-2">Earnings</h3>
                <div class="flex justify-between py-2 text-sm">
                    <span class="text-slate-600">Basic Salary</span>
                    <span class="font-mono font-bold text-slate-800">₹<?= number_format((float)$item['gross_salary'], 2) ?></span>
                </div>
                <!-- Additional earnings can go here in future -->
            </div>
            <!-- Deductions -->
            <div>
                <h3 class="text-sm font-bold text-slate-900 uppercase tracking-wider mb-3 border-b border-slate-200 pb-2">Deductions</h3>
                <div class="flex justify-between py-2 text-sm text-red-600">
                    <span>Absent Deduction</span>
                    <span class="font-mono font-bold">-₹<?= number_format((float)$item['absent_deduction'], 2) ?></span>
                </div>
                <div class="flex justify-between py-2 text-sm text-red-600">
                    <span>Late/Half-Day Penalty</span>
                    <span class="font-mono font-bold">-₹<?= number_format((float)$item['late_deduction'], 2) ?></span>
                </div>
            </div>
        </div>

        <!-- Totals -->
        <div class="bg-indigo-50 p-6 rounded-lg border border-indigo-100 mb-8">
            <div class="flex justify-between items-center text-lg mb-2">
                <span class="font-semibold text-indigo-900">Total Earnings</span>
                <span class="font-mono font-bold text-indigo-900">₹<?= number_format((float)$item['gross_salary'], 2) ?></span>
            </div>
            <div class="flex justify-between items-center text-lg mb-4 text-red-600">
                <span class="font-semibold">Total Deductions</span>
                <span class="font-mono font-bold">-₹<?= number_format((float)$item['absent_deduction'] + (float)$item['late_deduction'], 2) ?></span>
            </div>
            <div class="border-t-2 border-indigo-200 pt-4 mt-2 flex justify-between items-center">
                <span class="text-2xl font-black text-indigo-900 uppercase">Net Salary Payable</span>
                <span class="text-3xl font-mono font-black text-emerald-600">₹<?= number_format((float)$item['net_salary'], 2) ?></span>
            </div>
        </div>

        <!-- Signatures -->
        <div class="mt-20 grid grid-cols-2 text-center text-sm font-semibold text-slate-500">
            <div>
                <div class="border-t border-slate-400 w-48 mx-auto pt-2">Employee Signature</div>
            </div>
            <div>
                <div class="border-t border-slate-400 w-48 mx-auto pt-2">Authorized Signatory</div>
            </div>
        </div>
        
        <p class="text-center text-xs text-slate-400 mt-12 italic">This is a computer-generated document and requires no physical signature for validity unless strictly required.</p>

    </div>

</body>
</html>
