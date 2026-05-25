<div x-data="{ payrollTab: 'payrolls' }">
    <div class="mb-6 flex space-x-4 border-b border-gray-200 dark:border-gray-700">
        <button @click="payrollTab = 'payrolls'" :class="{ 'border-b-2 border-mango text-gray-900 dark:text-white': payrollTab === 'payrolls', 'text-gray-500 hover:text-gray-700': payrollTab !== 'payrolls' }" class="pb-2 font-bold px-2">Payrolls</button>
        <button @click="payrollTab = 'employees'" :class="{ 'border-b-2 border-mango text-gray-900 dark:text-white': payrollTab === 'employees', 'text-gray-500 hover:text-gray-700': payrollTab !== 'employees' }" class="pb-2 font-bold px-2">Employees</button>
        <button @click="payrollTab = 'attendance'" :class="{ 'border-b-2 border-mango text-gray-900 dark:text-white': payrollTab === 'attendance', 'text-gray-500 hover:text-gray-700': payrollTab !== 'attendance' }" class="pb-2 font-bold px-2">Attendance</button>
        <button @click="payrollTab = 'advances'" :class="{ 'border-b-2 border-mango text-gray-900 dark:text-white': payrollTab === 'advances', 'text-gray-500 hover:text-gray-700': payrollTab !== 'advances' }" class="pb-2 font-bold px-2">Advances</button>
    </div>

    <!-- Payrolls Tab -->
    <div x-show="payrollTab === 'payrolls'" class="space-y-6">
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Generate Payroll Form -->
            <div class="bg-white dark:bg-gray-900 p-6 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-800">
                <h3 class="text-lg font-black mb-4">Generate Payroll</h3>
                <form action="{{ route('accounting.generatePayroll') }}" method="POST" class="space-y-4">
                    @csrf
                    <div>
                        <label class="block text-sm font-bold text-gray-700 dark:text-gray-300 mb-1">Employee</label>
                        <select name="employee_id" required class="w-full rounded-xl border-gray-300 dark:border-gray-700 dark:bg-gray-800 focus:ring-mango focus:border-mango">
                            <option value="">Select Employee</option>
                            @foreach($data['employees']->where('status', 'active') as $emp)
                                <option value="{{ $emp->id }}">{{ $emp->name }} (Rs. {{ number_format($emp->base_salary) }})</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-bold text-gray-700 dark:text-gray-300 mb-1">Month (1-12)</label>
                            <input type="number" name="month" value="{{ date('n') }}" min="1" max="12" required class="w-full rounded-xl border-gray-300 dark:border-gray-700 dark:bg-gray-800 focus:ring-mango focus:border-mango">
                        </div>
                        <div>
                            <label class="block text-sm font-bold text-gray-700 dark:text-gray-300 mb-1">Year</label>
                            <input type="number" name="year" value="{{ date('Y') }}" min="2000" max="2100" required class="w-full rounded-xl border-gray-300 dark:border-gray-700 dark:bg-gray-800 focus:ring-mango focus:border-mango">
                        </div>
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-bold text-gray-700 dark:text-gray-300 mb-1">Bonus (+)</label>
                            <input type="number" name="bonus" value="0" min="0" step="0.01" class="w-full rounded-xl border-gray-300 dark:border-gray-700 dark:bg-gray-800 focus:ring-mango focus:border-mango">
                        </div>
                        <div>
                            <label class="block text-sm font-bold text-gray-700 dark:text-gray-300 mb-1">Incentives (+)</label>
                            <input type="number" name="incentives" value="0" min="0" step="0.01" class="w-full rounded-xl border-gray-300 dark:border-gray-700 dark:bg-gray-800 focus:ring-mango focus:border-mango">
                        </div>
                    </div>
                    <p class="text-xs text-gray-500">Note: Absent deductions and pending advances will be automatically calculated and deducted.</p>
                    <button type="submit" class="w-full bg-mango text-gray-900 font-bold py-3 rounded-xl hover:bg-[#ffb020] transition-colors">
                        Generate Slip
                    </button>
                </form>
            </div>

            <!-- Payroll History -->
            <div class="lg:col-span-2 bg-white dark:bg-gray-900 p-6 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-800 overflow-hidden">
                <h3 class="text-lg font-black mb-4">Payroll Slips</h3>
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-sm whitespace-nowrap">
                        <thead class="text-xs text-gray-500 uppercase border-b border-gray-100 dark:border-gray-800">
                            <tr>
                                <th class="pb-3 font-bold">Month/Year</th>
                                <th class="pb-3 font-bold">Employee</th>
                                <th class="pb-3 font-bold">Base / Net</th>
                                <th class="pb-3 font-bold">Status</th>
                                <th class="pb-3 font-bold text-right">Action</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                            @foreach($data['payrolls'] as $pr)
                            <tr>
                                <td class="py-4">{{ $pr->month }}/{{ $pr->year }}</td>
                                <td class="py-4 font-bold">{{ $pr->employee->name }}</td>
                                <td class="py-4">
                                    <div class="text-xs text-gray-500">Base: Rs.{{ number_format($pr->base_salary) }}</div>
                                    <div class="font-bold text-gray-900 dark:text-white">Net: Rs.{{ number_format($pr->net_payable) }}</div>
                                </td>
                                <td class="py-4">
                                    @if($pr->status === 'paid')
                                        <span class="px-2 py-1 bg-green-100 text-green-700 rounded-full text-xs font-bold">Paid on {{ \Carbon\Carbon::parse($pr->payment_date)->format('M d') }}</span>
                                    @else
                                        <span class="px-2 py-1 bg-yellow-100 text-yellow-700 rounded-full text-xs font-bold">Unpaid</span>
                                    @endif
                                </td>
                                <td class="py-4 text-right">
                                    @if($pr->status === 'unpaid')
                                    <form action="{{ route('accounting.payPayroll') }}" method="POST" class="inline-flex items-center gap-2">
                                        @csrf
                                        <input type="hidden" name="payroll_id" value="{{ $pr->id }}">
                                        <input type="date" name="date" value="{{ date('Y-m-d') }}" required class="rounded-lg border-gray-300 text-xs py-1.5 dark:bg-gray-800 dark:border-gray-700 w-32">
                                        <select name="account_id" required class="rounded-lg border-gray-300 text-xs py-1.5 dark:bg-gray-800 dark:border-gray-700 w-24">
                                            @foreach($data['accounts'] as $acc)
                                                <option value="{{ $acc->id }}">{{ $acc->name }}</option>
                                            @endforeach
                                        </select>
                                        <button type="submit" class="bg-gray-900 dark:bg-white text-white dark:text-gray-900 px-3 py-1.5 rounded-lg text-xs font-bold hover:bg-gray-800">Pay</button>
                                    </form>
                                    @endif
                                </td>
                            </tr>
                            @endforeach
                            @if($data['payrolls']->isEmpty())
                            <tr>
                                <td colspan="5" class="py-8 text-center text-gray-500">No payrolls generated yet.</td>
                            </tr>
                            @endif
                        </tbody>
                    </table>
                </div>
                <div class="mt-4">
                    {{ $data['payrolls']->appends(['tab' => 'payroll'])->links() }}
                </div>
            </div>
        </div>
    </div>

    <!-- Employees Tab -->
    <div x-show="payrollTab === 'employees'" class="space-y-6" style="display: none;">
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <div class="bg-white dark:bg-gray-900 p-6 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-800">
                <h3 class="text-lg font-black mb-4">Add Employee</h3>
                <form action="{{ route('accounting.storeEmployee') }}" method="POST" class="space-y-4">
                    @csrf
                    <div>
                        <label class="block text-sm font-bold text-gray-700 dark:text-gray-300 mb-1">Name</label>
                        <input type="text" name="name" required class="w-full rounded-xl border-gray-300 dark:border-gray-700 dark:bg-gray-800 focus:ring-mango focus:border-mango">
                    </div>
                    <div>
                        <label class="block text-sm font-bold text-gray-700 dark:text-gray-300 mb-1">Designation</label>
                        <input type="text" name="designation" class="w-full rounded-xl border-gray-300 dark:border-gray-700 dark:bg-gray-800 focus:ring-mango focus:border-mango">
                    </div>
                    <div>
                        <label class="block text-sm font-bold text-gray-700 dark:text-gray-300 mb-1">Base Salary</label>
                        <input type="number" name="base_salary" min="0" step="0.01" required class="w-full rounded-xl border-gray-300 dark:border-gray-700 dark:bg-gray-800 focus:ring-mango focus:border-mango">
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-bold text-gray-700 dark:text-gray-300 mb-1">Phone</label>
                            <input type="text" name="phone" class="w-full rounded-xl border-gray-300 dark:border-gray-700 dark:bg-gray-800 focus:ring-mango focus:border-mango">
                        </div>
                        <div>
                            <label class="block text-sm font-bold text-gray-700 dark:text-gray-300 mb-1">Join Date</label>
                            <input type="date" name="join_date" class="w-full rounded-xl border-gray-300 dark:border-gray-700 dark:bg-gray-800 focus:ring-mango focus:border-mango">
                        </div>
                    </div>
                    <div>
                        <label class="block text-sm font-bold text-gray-700 dark:text-gray-300 mb-1">Status</label>
                        <select name="status" class="w-full rounded-xl border-gray-300 dark:border-gray-700 dark:bg-gray-800 focus:ring-mango focus:border-mango">
                            <option value="active">Active</option>
                            <option value="inactive">Inactive</option>
                        </select>
                    </div>
                    <button type="submit" class="w-full bg-gray-900 dark:bg-white text-white dark:text-gray-900 font-bold py-3 rounded-xl hover:bg-gray-800 transition-colors">
                        Save Employee
                    </button>
                </form>
            </div>
            
            <div class="lg:col-span-2 bg-white dark:bg-gray-900 p-6 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-800 overflow-hidden">
                <h3 class="text-lg font-black mb-4">Employee Directory</h3>
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-sm whitespace-nowrap">
                        <thead class="text-xs text-gray-500 uppercase border-b border-gray-100 dark:border-gray-800">
                            <tr>
                                <th class="pb-3 font-bold">Name</th>
                                <th class="pb-3 font-bold">Designation</th>
                                <th class="pb-3 font-bold">Base Salary</th>
                                <th class="pb-3 font-bold">Status</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                            @foreach($data['employees'] as $emp)
                            <tr>
                                <td class="py-4">
                                    <div class="font-bold text-gray-900 dark:text-white">{{ $emp->name }}</div>
                                    <div class="text-xs text-gray-500">{{ $emp->phone ?? 'No Phone' }}</div>
                                </td>
                                <td class="py-4">{{ $emp->designation ?? '-' }}</td>
                                <td class="py-4 font-bold">Rs. {{ number_format($emp->base_salary, 2) }}</td>
                                <td class="py-4">
                                    @if($emp->status === 'active')
                                        <span class="px-2 py-1 bg-green-100 text-green-700 rounded-full text-xs font-bold">Active</span>
                                    @else
                                        <span class="px-2 py-1 bg-gray-100 text-gray-700 rounded-full text-xs font-bold">Inactive</span>
                                    @endif
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Attendance Tab -->
    <div x-show="payrollTab === 'attendance'" class="space-y-6" style="display: none;">
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <div class="bg-white dark:bg-gray-900 p-6 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-800">
                <h3 class="text-lg font-black mb-4">Mark Attendance</h3>
                <form action="{{ route('accounting.storeAttendance') }}" method="POST" class="space-y-4">
                    @csrf
                    <div>
                        <label class="block text-sm font-bold text-gray-700 dark:text-gray-300 mb-1">Date</label>
                        <input type="date" name="date" value="{{ date('Y-m-d') }}" required class="w-full rounded-xl border-gray-300 dark:border-gray-700 dark:bg-gray-800 focus:ring-mango focus:border-mango">
                    </div>
                    <div>
                        <label class="block text-sm font-bold text-gray-700 dark:text-gray-300 mb-1">Employee</label>
                        <select name="employee_id" required class="w-full rounded-xl border-gray-300 dark:border-gray-700 dark:bg-gray-800 focus:ring-mango focus:border-mango">
                            @foreach($data['employees']->where('status', 'active') as $emp)
                                <option value="{{ $emp->id }}">{{ $emp->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-bold text-gray-700 dark:text-gray-300 mb-1">Status</label>
                        <select name="status" class="w-full rounded-xl border-gray-300 dark:border-gray-700 dark:bg-gray-800 focus:ring-mango focus:border-mango">
                            <option value="present">Present</option>
                            <option value="absent">Absent</option>
                            <option value="half_day">Half Day</option>
                            <option value="leave">Leave (Paid)</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-bold text-gray-700 dark:text-gray-300 mb-1">Notes (Optional)</label>
                        <input type="text" name="notes" class="w-full rounded-xl border-gray-300 dark:border-gray-700 dark:bg-gray-800 focus:ring-mango focus:border-mango">
                    </div>
                    <button type="submit" class="w-full bg-gray-900 dark:bg-white text-white dark:text-gray-900 font-bold py-3 rounded-xl hover:bg-gray-800 transition-colors">
                        Mark Attendance
                    </button>
                </form>
            </div>
            
            <div class="bg-blue-50 dark:bg-blue-900/20 p-6 rounded-2xl border border-blue-100 dark:border-blue-800">
                <h3 class="text-lg font-black text-blue-800 dark:text-blue-300 mb-2">How Attendance Works</h3>
                <ul class="list-disc list-inside text-sm text-blue-700 dark:text-blue-400 space-y-2">
                    <li><strong>Present:</strong> Normal working day, full salary.</li>
                    <li><strong>Absent:</strong> Unpaid leave. Will deduct 1 day's worth of salary during payroll generation.</li>
                    <li><strong>Half Day:</strong> Will deduct 0.5 day's worth of salary.</li>
                    <li><strong>Leave:</strong> Paid leave. Treated as present for salary calculations.</li>
                </ul>
            </div>
        </div>
    </div>

    <!-- Advances Tab -->
    <div x-show="payrollTab === 'advances'" class="space-y-6" style="display: none;">
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <div class="bg-white dark:bg-gray-900 p-6 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-800">
                <h3 class="text-lg font-black mb-4">Record Advance Payment</h3>
                <form action="{{ route('accounting.storeAdvance') }}" method="POST" class="space-y-4">
                    @csrf
                    <div>
                        <label class="block text-sm font-bold text-gray-700 dark:text-gray-300 mb-1">Employee</label>
                        <select name="employee_id" required class="w-full rounded-xl border-gray-300 dark:border-gray-700 dark:bg-gray-800 focus:ring-mango focus:border-mango">
                            @foreach($data['employees']->where('status', 'active') as $emp)
                                <option value="{{ $emp->id }}">{{ $emp->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-bold text-gray-700 dark:text-gray-300 mb-1">Amount</label>
                        <input type="number" name="amount" min="1" step="0.01" required class="w-full rounded-xl border-gray-300 dark:border-gray-700 dark:bg-gray-800 focus:ring-mango focus:border-mango">
                    </div>
                    <div>
                        <label class="block text-sm font-bold text-gray-700 dark:text-gray-300 mb-1">Date</label>
                        <input type="date" name="date" value="{{ date('Y-m-d') }}" required class="w-full rounded-xl border-gray-300 dark:border-gray-700 dark:bg-gray-800 focus:ring-mango focus:border-mango">
                    </div>
                    <div>
                        <label class="block text-sm font-bold text-gray-700 dark:text-gray-300 mb-1">Pay From Account</label>
                        <select name="account_id" required class="w-full rounded-xl border-gray-300 dark:border-gray-700 dark:bg-gray-800 focus:ring-mango focus:border-mango">
                            @foreach($data['accounts'] as $acc)
                                <option value="{{ $acc->id }}">{{ $acc->name }} (Rs.{{ number_format($acc->balance) }})</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-bold text-gray-700 dark:text-gray-300 mb-1">Notes</label>
                        <input type="text" name="description" class="w-full rounded-xl border-gray-300 dark:border-gray-700 dark:bg-gray-800 focus:ring-mango focus:border-mango">
                    </div>
                    <button type="submit" class="w-full bg-gray-900 dark:bg-white text-white dark:text-gray-900 font-bold py-3 rounded-xl hover:bg-gray-800 transition-colors">
                        Record Advance
                    </button>
                </form>
            </div>
            
            <div class="lg:col-span-2 bg-white dark:bg-gray-900 p-6 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-800 overflow-hidden">
                <h3 class="text-lg font-black mb-4">Pending Advances (To be deducted)</h3>
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-sm whitespace-nowrap">
                        <thead class="text-xs text-gray-500 uppercase border-b border-gray-100 dark:border-gray-800">
                            <tr>
                                <th class="pb-3 font-bold">Date</th>
                                <th class="pb-3 font-bold">Employee</th>
                                <th class="pb-3 font-bold">Amount</th>
                                <th class="pb-3 font-bold">Notes</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                            @foreach($data['advances'] as $adv)
                            <tr>
                                <td class="py-4">{{ \Carbon\Carbon::parse($adv->date)->format('M d, Y') }}</td>
                                <td class="py-4 font-bold">{{ $adv->employee->name }}</td>
                                <td class="py-4 font-bold text-red-600">Rs. {{ number_format($adv->amount, 2) }}</td>
                                <td class="py-4 text-gray-500">{{ $adv->description }}</td>
                            </tr>
                            @endforeach
                            @if($data['advances']->isEmpty())
                            <tr>
                                <td colspan="4" class="py-8 text-center text-gray-500">No pending advances.</td>
                            </tr>
                            @endif
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
