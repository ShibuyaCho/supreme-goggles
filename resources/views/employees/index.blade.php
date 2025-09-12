@extends('layouts.app')

@section('title', 'Employee Management - Cannabis POS')

@section('content')
<div class="min-h-screen bg-gray-50">
    <!-- Header -->
    <div class="bg-white shadow-sm border-b border-gray-200">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between h-16">
                <h1 class="text-2xl font-bold text-gray-900">Employee Management</h1>
                
                <div class="flex items-center space-x-4">
                    <!-- Add Employee Button -->
                    <button onclick="showAddEmployeeModal()" class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg text-sm font-medium transition-colors">
                        <svg class="w-4 h-4 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                        </svg>
                        Add Employee
                    </button>
                    
                    <!-- Export Button -->
                    <button onclick="exportEmployees()" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg text-sm font-medium transition-colors">
                        Export
                    </button>
                    <!-- Open Permissions Button -->
                    <button id="open-permissions" class="bg-purple-600 hover:bg-purple-700 text-white px-4 py-2 rounded-lg text-sm font-medium transition-colors">
                        Permissions
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Search and Filters -->
    <div class="bg-white border-b border-gray-200">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-4">
            <div class="flex flex-col lg:flex-row gap-4">
                <!-- Search -->
                <div class="flex-1">
                    <div class="relative">
                        <input 
                            type="text" 
                            id="employee-search"
                            placeholder="Search employees by name, email, or employee ID..." 
                            class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent"
                            value="{{ request('search') }}"
                        >
                        <svg class="absolute left-3 top-2.5 h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m21 21-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                        </svg>
                    </div>
                </div>

                <!-- Filters -->
                <div class="flex gap-3">
                    <select id="role-filter" class="border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-green-500">
                        <option value="all">All Roles</option>
                        <option value="admin">Administrator</option>
                        <option value="manager">Manager</option>
                        <option value="budtender">Budtender</option>
                        <option value="cashier">Cashier</option>
                    </select>

                    <select id="status-filter" class="border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-green-500">
                        <option value="all">All Status</option>
                        <option value="active">Active</option>
                        <option value="inactive">Inactive</option>
                        <option value="on_leave">On Leave</option>
                    </select>

                    <select id="department-filter" class="border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-green-500">
                        <option value="all">All Departments</option>
                        <option value="sales">Sales</option>
                        <option value="management">Management</option>
                        <option value="inventory">Inventory</option>
                        <option value="security">Security</option>
                    </select>
                </div>
            </div>
        </div>
    </div>

    <!-- Tab Navigation -->
    <div class="bg-white border-b border-gray-200">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <nav class="flex space-x-8">
                <button class="employee-tab py-4 px-1 border-b-2 font-medium text-sm border-green-500 text-green-600" 
                        data-tab="employees">
                    Employees
                </button>
                <button class="employee-tab py-4 px-1 border-b-2 font-medium text-sm border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300" 
                        data-tab="performance">
                    Performance
                </button>
                <button class="employee-tab py-4 px-1 border-b-2 font-medium text-sm border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300" 
                        data-tab="schedules">
                    Schedules
                </button>
                <button class="employee-tab py-4 px-1 border-b-2 font-medium text-sm border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300"
                        data-tab="permissions">
                    Permissions
                </button>
                <button class="employee-tab py-4 px-1 border-b-2 font-medium text-sm border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300"
                        data-tab="timeclock">
                    Time Clock
                </button>
            </nav>
        </div>
    </div>

    <!-- Content -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <!-- Time Clock Tab -->
        <div id="timeclock-tab" class="tab-content hidden">
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-4 mb-6">
                <div class="grid grid-cols-1 md:grid-cols-4 gap-3">
                    <div>
                        <label class="block text-xs text-gray-600 mb-1">Start Date</label>
                        <input type="date" id="tc-start-date" class="w-full border rounded px-3 py-2 text-sm" />
                    </div>
                    <div>
                        <label class="block text-xs text-gray-600 mb-1">End Date</label>
                        <input type="date" id="tc-end-date" class="w-full border rounded px-3 py-2 text-sm" />
                    </div>
                    <div>
                        <label class="block text-xs text-gray-600 mb-1">Employee</label>
                        <select id="tc-employee" class="w-full border rounded px-3 py-2 text-sm">
                            <option value="">All</option>
                            @foreach($employees as $e)
                                <option value="{{ $e->id }}">{{ $e->full_name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="flex items-end">
                        <button id="tc-refresh" class="w-full bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded text-sm">Load Entries</button>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow-sm border border-gray-200">
                <div class="flex items-center justify-between p-4 border-b">
                    <h3 class="text-lg font-semibold text-gray-900">Employee Time Entries</h3>
                    <button id="tc-add" class="bg-blue-600 hover:bg-blue-700 text-white px-3 py-2 rounded text-sm">Add Entry</button>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Employee</th>
                                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Clock In</th>
                                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Clock Out</th>
                                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Duration</th>
                                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Notes</th>
                                <th class="px-4 py-2"></th>
                            </tr>
                        </thead>
                        <tbody id="tc-body" class="divide-y divide-gray-200"></tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Employees Tab -->
        <div id="employees-tab" class="tab-content">
            <!-- Employee Cards -->
            <div class="grid gap-6 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
                @forelse($employees as $employee)
                <div class="bg-white rounded-lg shadow-sm border border-gray-200 hover:shadow-md transition-shadow" data-employee-id="{{ $employee->id }}">
                    <div class="p-6">
                        <!-- Employee Header -->
                        <div class="flex items-center space-x-4 mb-4">
                            <div class="w-16 h-16 rounded-full flex items-center justify-center {{ $employee->department === 'management' ? 'bg-blue-100' : ($employee->department === 'inventory' ? 'bg-purple-100' : ($employee->department === 'security' ? 'bg-orange-100' : 'bg-green-100')) }}">
                                <svg class="w-8 h-8 {{ $employee->department === 'management' ? 'text-blue-600' : ($employee->department === 'inventory' ? 'text-purple-600' : ($employee->department === 'security' ? 'text-orange-600' : 'text-green-600')) }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                                </svg>
                            </div>
                            <div>
                                <h3 class="text-lg font-semibold text-gray-900">{{ $employee->full_name }}</h3>
                                <p class="text-sm text-gray-600">{{ ucfirst($employee->position) }}</p>
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium {{ $employee->status === 'active' ? 'bg-green-100 text-green-800' : ($employee->status === 'on_leave' ? 'bg-yellow-100 text-yellow-800' : 'bg-gray-100 text-gray-800') }}">
                                    {{ ucfirst(str_replace('_', ' ', $employee->status ?? 'active')) }}
                                </span>
                            </div>
                        </div>

                        <!-- Employee Info -->
                        <div class="space-y-2 mb-4">
                            <div class="flex items-center text-sm text-gray-600">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 4.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                                </svg>
                                {{ $employee->email }}
                            </div>
                            <div class="flex items-center text-sm text-gray-600">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/>
                                </svg>
                                {{ $employee->phone }}
                            </div>
                            <div class="flex items-center text-sm text-gray-600">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                                </svg>
                                {{ $employee->employee_id }}
                            </div>
                        </div>

                        <!-- Performance Stats -->
                        <div class="bg-gray-50 rounded-lg p-3 mb-4">
                            <div class="grid grid-cols-2 gap-3">
                                <div class="text-center">
                                    <div class="text-lg font-bold text-gray-900">${{ number_format($employee->total_sales ?? 0, 2) }}</div>
                                    <div class="text-xs text-gray-500">Total Sales</div>
                                </div>
                                <div class="text-center">
                                    <div class="text-lg font-bold text-gray-900">{{ $employee->total_transactions ?? 0 }}</div>
                                    <div class="text-xs text-gray-500">Transactions</div>
                                </div>
                            </div>
                        </div>

                        <!-- Permissions -->
                        <div class="mb-4">
                            <div class="flex flex-wrap gap-1">
                                @foreach(($employee->permissions ?? []) as $perm)
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-blue-100 text-blue-800">{{ ucwords(str_replace(['_', ':'], ' ', $perm)) }}</span>
                                @endforeach
                            </div>
                        </div>

                        <!-- Action Buttons -->
                        <div class="flex space-x-2">
                            <button type="button" class="flex-1 bg-gray-100 hover:bg-gray-200 text-gray-700 px-3 py-2 rounded-lg text-sm font-medium transition-colors" onclick="EmployeeUI.openEdit({{ $employee->id }})">View</button>
                            <button type="button" class="flex-1 bg-blue-100 hover:bg-blue-200 text-blue-700 px-3 py-2 rounded-lg text-sm font-medium transition-colors" onclick="EmployeeUI.openEdit({{ $employee->id }})">Edit</button>
                            <button type="button" class="bg-purple-100 hover:bg-purple-200 text-purple-700 px-3 py-2 rounded-lg text-sm font-medium transition-colors" onclick="resetEmployeePassword({{ $employee->id }})">Reset Password</button>
                            <button type="button" class="bg-yellow-100 hover:bg-yellow-200 text-yellow-700 px-3 py-2 rounded-lg text-sm font-medium transition-colors" onclick="resetEmployeePin({{ $employee->id }})">Reset PIN</button>
                            <a href="{{ route('employees.performance', $employee) }}" class="bg-green-100 hover:bg-green-200 text-green-700 px-3 py-2 rounded-lg text-sm font-medium transition-colors">Performance</a>
                        </div>
                    </div>
                </div>
                @empty
                @include('partials.empty-state', ['title' => 'No employees found', 'description' => 'Use the Add Employee button to create your first employee.'])
                @endforelse
            </div>

            @if(method_exists($employees, 'links'))
            <div class="mt-6">{{ $employees->links() }}</div>
            @endif
        </div>

        <!-- Performance Tab -->
        <div id="performance-tab" class="tab-content hidden">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
                <!-- Top Performer -->
                <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-gray-600">Top Performer</p>
                            <p class="text-2xl font-bold text-green-600">Sarah Johnson</p>
                            <p class="text-sm text-gray-500">$12,450 this month</p>
                        </div>
                        <div class="p-3 bg-green-100 rounded-full">
                            <svg class="w-8 h-8 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z"/>
                            </svg>
                        </div>
                    </div>
                </div>

                <!-- Total Team Sales -->
                <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-gray-600">Team Sales</p>
                            <p class="text-3xl font-bold text-blue-600">$68,240</p>
                            <p class="text-sm text-gray-500">This month</p>
                        </div>
                        <div class="p-3 bg-blue-100 rounded-full">
                            <svg class="w-8 h-8 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1"/>
                            </svg>
                        </div>
                    </div>
                </div>

                <!-- Average Performance -->
                <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-gray-600">Avg Per Employee</p>
                            <p class="text-3xl font-bold text-purple-600">$8,530</p>
                            <p class="text-sm text-gray-500">This month</p>
                        </div>
                        <div class="p-3 bg-purple-100 rounded-full">
                            <svg class="w-8 h-8 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012-2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                            </svg>
                        </div>
                    </div>
                </div>

                <!-- Active Employees -->
                <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-gray-600">Active Staff</p>
                            <p class="text-3xl font-bold text-orange-600">8</p>
                            <p class="text-sm text-gray-500">On duty today</p>
                        </div>
                        <div class="p-3 bg-orange-100 rounded-full">
                            <svg class="w-8 h-8 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                            </svg>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Performance Leaderboard -->
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Monthly Performance Leaderboard</h3>
                <div class="space-y-4">
                    <div class="flex items-center justify-between p-4 bg-yellow-50 border border-yellow-200 rounded-lg">
                        <div class="flex items-center space-x-4">
                            <div class="w-8 h-8 bg-yellow-500 text-white rounded-full flex items-center justify-center font-bold">1</div>
                            <div>
                                <div class="font-medium text-gray-900">Sarah Johnson</div>
                                <div class="text-sm text-gray-600">Senior Budtender</div>
                            </div>
                        </div>
                        <div class="text-right">
                            <div class="font-semibold text-gray-900">$12,450</div>
                            <div class="text-sm text-gray-600">142 transactions</div>
                        </div>
                    </div>
                    <div class="flex items-center justify-between p-4 bg-gray-50 border border-gray-200 rounded-lg">
                        <div class="flex items-center space-x-4">
                            <div class="w-8 h-8 bg-gray-400 text-white rounded-full flex items-center justify-center font-bold">2</div>
                            <div>
                                <div class="font-medium text-gray-900">Alex Thompson</div>
                                <div class="text-sm text-gray-600">Budtender</div>
                            </div>
                        </div>
                        <div class="text-right">
                            <div class="font-semibold text-gray-900">$9,830</div>
                            <div class="text-sm text-gray-600">118 transactions</div>
                        </div>
                    </div>
                    <div class="flex items-center justify-between p-4 bg-orange-50 border border-orange-200 rounded-lg">
                        <div class="flex items-center space-x-4">
                            <div class="w-8 h-8 bg-orange-500 text-white rounded-full flex items-center justify-center font-bold">3</div>
                            <div>
                                <div class="font-medium text-gray-900">Jamie Lee</div>
                                <div class="text-sm text-gray-600">Cashier</div>
                            </div>
                        </div>
                        <div class="text-right">
                            <div class="font-semibold text-gray-900">$8,920</div>
                            <div class="text-sm text-gray-600">95 transactions</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Schedules Tab -->
        <div id="schedules-tab" class="tab-content hidden">
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Weekly Schedule</h3>
                <div class="text-center py-12">
                    <svg class="mx-auto h-12 w-12 text-gray-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3a2 2 0 012-2h4a2 2 0 012 2v4m-6 0V3a2 2 0 012-2h4a2 2 0 012 2v4m-6 0h8a2 2 0 012 2v9a2 2 0 01-2 2H8a2 2 0 01-2-2v-9a2 2 0 012-2z"/>
                    </svg>
                    <p class="text-gray-600">Schedule management coming soon</p>
                </div>
            </div>
        </div>

        <!-- Permissions Tab -->
        <div id="permissions-tab" class="tab-content hidden">
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-semibold text-gray-900">Role Permissions</h3>
                    <div class="flex items-center gap-2">
                        <label class="text-sm text-gray-600">Role</label>
                        <select id="role-perm-selector" class="border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-green-500">
                            <option value="admin">Administrator</option>
                            <option value="manager">Manager</option>
                            <option value="inventory">Inventory</option>
                            <option value="budtender">Budtender</option>
                            <option value="cashier">Cashier</option>
                        </select>
                    </div>
                </div>

                <div id="role-perm-container" class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="space-y-4">
                        <div>
                            <h4 class="font-medium text-gray-900 mb-2">POS</h4>
                            <label class="flex items-center gap-2 mb-1"><input type="checkbox" class="perm" value="pos:access"> <span>POS Access</span></label>
                            <label class="flex items-center gap-2 mb-1"><input type="checkbox" class="perm" value="pos:sales"> <span>Process Sales</span></label>
                            <label class="flex items-center gap-2 mb-1"><input type="checkbox" class="perm" value="pos:scanner_only"> <span>Require scanner to add to cart</span></label>
                        </div>
                        <div>
                            <h4 class="font-medium text-gray-900 mb-2">Products</h4>
                            <label class="flex items-center gap-2 mb-1"><input type="checkbox" class="perm" value="products:read"> <span>View Products</span></label>
                            <label class="flex items-center gap-2 mb-1"><input type="checkbox" class="perm" value="products:write"> <span>Create/Update Products</span></label>
                            <label class="flex items-center gap-2 mb-1"><input type="checkbox" class="perm" value="products:print"> <span>Print Barcodes/Labels</span></label>
                            <label class="flex items-center gap-2 mb-1"><input type="checkbox" class="perm" value="products:transfer"> <span>Transfer Rooms</span></label>
                            <label class="flex items-center gap-2 mb-1"><input type="checkbox" class="perm" value="products:delete"> <span>Delete Products</span></label>
                        </div>
                        <div>
                            <h4 class="font-medium text-gray-900 mb-2">Sales</h4>
                            <label class="flex items-center gap-2 mb-1"><input type="checkbox" class="perm" value="sales:read"> <span>View Sales</span></label>
                            <label class="flex items-center gap-2 mb-1"><input type="checkbox" class="perm" value="sales:create"> <span>Create Sales</span></label>
                            <label class="flex items-center gap-2 mb-1"><input type="checkbox" class="perm" value="sales:manage"> <span>Manage/Refund/Void</span></label>
                        </div>
                    </div>
                    <div class="space-y-4">
                        <div>
                            <h4 class="font-medium text-gray-900 mb-2">Customers</h4>
                            <label class="flex items-center gap-2 mb-1"><input type="checkbox" class="perm" value="customers:read"> <span>View Customers</span></label>
                            <label class="flex items-center gap-2 mb-1"><input type="checkbox" class="perm" value="customers:write"> <span>Create/Update Customers</span></label>
                        </div>
                        <div>
                            <h4 class="font-medium text-gray-900 mb-2">Analytics & Reports</h4>
                            <label class="flex items-center gap-2 mb-1"><input type="checkbox" class="perm" value="analytics:read"> <span>View Analytics</span></label>
                            <label class="flex items-center gap-2 mb-1"><input type="checkbox" class="perm" value="reports:read"> <span>View Reports</span></label>
                            <label class="flex items-center gap-2 mb-1"><input type="checkbox" class="perm" value="reports:export"> <span>Export Reports</span></label>
                        </div>
                        <div>
                            <h4 class="font-medium text-gray-900 mb-2">Deals</h4>
                            <label class="flex items-center gap-2 mb-1"><input type="checkbox" class="perm" value="deals:read"> <span>View Deals</span></label>
                            <label class="flex items-center gap-2 mb-1"><input type="checkbox" class="perm" value="deals:apply"> <span>Apply Deals</span></label>
                            <label class="flex items-center gap-2 mb-1"><input type="checkbox" class="perm" value="deals:manage"> <span>Create/Manage Deals</span></label>
                        </div>
                        <div>
                            <h4 class="font-medium text-gray-900 mb-2">Employees & METRC</h4>
                            <label class="flex items-center gap-2 mb-1"><input type="checkbox" class="perm" value="employees:read"> <span>View Employees</span></label>
                            <label class="flex items-center gap-2 mb-1"><input type="checkbox" class="perm" value="employees:manage"> <span>Create/Manage Employees</span></label>
                            <label class="flex items-center gap-2 mb-1"><input type="checkbox" class="perm" value="metrc:access"> <span>METRC Access</span></label>
                            <label class="flex items-center gap-2 mb-1"><input type="checkbox" class="perm" value="metrc:sync"> <span>Sync Inventory</span></label>
                            <label class="flex items-center gap-2 mb-1"><input type="checkbox" class="perm" value="metrc:create"> <span>Create Packages/Receipts</span></label>
                            <label class="flex items-center gap-2 mb-1"><input type="checkbox" class="perm" value="metrc:sales"> <span>Push Sales</span></label>
                        </div>
                    </div>
                </div>

                <div class="mt-6 flex items-center justify-between">
                    <div class="text-sm text-gray-600" id="role-perm-summary"></div>
                    <button id="save-role-perms" class="inline-flex items-center rounded-lg bg-green-600 px-4 py-2 text-sm font-medium text-white shadow-sm hover:bg-green-700">Save Changes</button>
                </div>
            </div>
        </div>
    </div>
</div>

@include('employees.modals.add-employee')
@include('employees.modals.edit-employee')
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Preserve current filters
    const currentRole = @json(request('role', 'all'));
    const currentStatus = @json(request('status', 'all'));
    const currentDepartment = @json(request('department', 'all'));
    const roleEl = document.getElementById('role-filter');
    const statusEl = document.getElementById('status-filter');
    const deptEl = document.getElementById('department-filter');
    if (roleEl) roleEl.value = currentRole || 'all';
    if (statusEl) statusEl.value = currentStatus || 'all';
    if (deptEl) deptEl.value = currentDepartment || 'all';

    // Tab switching
    document.querySelectorAll('.employee-tab').forEach(tab => {
        tab.addEventListener('click', function() {
            const targetTab = this.dataset.tab;
            switchTab(targetTab);
        });
    });
    // Quick open permissions
    const openPermBtn = document.getElementById('open-permissions');
    if (openPermBtn) openPermBtn.addEventListener('click', () => switchTab('permissions'));

    // Open tab by query param ?tab=permissions
    try {
        const params = new URLSearchParams(window.location.search);
        const tab = params.get('tab');
        if (tab) switchTab(tab);
    } catch (_) {}

    // Search functionality
    let searchTimeout;
    document.getElementById('employee-search').addEventListener('input', function() {
        clearTimeout(searchTimeout);
        searchTimeout = setTimeout(() => {
            applyFilters();
        }, 300);
    });

    // Filter functionality
    document.getElementById('role-filter').addEventListener('change', applyFilters);
    document.getElementById('status-filter').addEventListener('change', applyFilters);
    document.getElementById('department-filter').addEventListener('change', applyFilters);
    // Role permissions UI logic
    const roleSelect = document.getElementById('role-perm-selector');
    const permInputs = Array.from(document.querySelectorAll('#role-perm-container input.perm'));
    const summaryEl = document.getElementById('role-perm-summary');
    let rolePerms = {};

    async function loadRolePerms() {
        try {
            const res = await (window.axios || axios).get('/api/settings/pos');
            const settings = res?.data?.settings || {};
            rolePerms = settings.role_permissions || {
                admin: ['*'],
                manager: ['pos:*','products:*','customers:*','sales:*','analytics:read','deals:*','employees:read','metrc:access','metrc:sync','reports:read','reports:export'],
                inventory: ['products:*','metrc:access','metrc:sync','analytics:read'],
                budtender: ['pos:*','products:read','customers:read','sales:create','analytics:read'],
                cashier: ['pos:*','products:read','sales:create','products:print','analytics:read','pos:scanner_only']
            };
            renderPerms();
        } catch (e) {
            console.warn('Failed to load role permissions', e);
            rolePerms = rolePerms || {};
            renderPerms();
        }
    }

    function renderPerms(){
        const role = roleSelect?.value || 'cashier';
        const current = new Set(rolePerms[role] || []);
        permInputs.forEach(cb => { cb.checked = current.has(cb.value); });
        updateSummary();
    }

    function updateSummary(){
        const role = roleSelect?.value || '';
        const selected = permInputs.filter(cb => cb.checked).map(cb => cb.value);
        summaryEl.textContent = `${role.toUpperCase()}: ${selected.length} permissions selected`;
    }

    roleSelect?.addEventListener('change', renderPerms);
    permInputs.forEach(cb => cb.addEventListener('change', updateSummary));

    document.getElementById('save-role-perms')?.addEventListener('click', async function(){
        const role = roleSelect?.value || 'cashier';
        const selected = permInputs.filter(cb => cb.checked).map(cb => cb.value);
        rolePerms[role] = selected;
        try {
            const res = await (window.axios || axios).post('/api/settings/pos', {
                role_permissions: rolePerms
            });
            const ok = (res?.data?.success === true) || (res?.status && res.status >= 200 && res.status < 300);
            if (window.POS?.showToast) POS.showToast(ok ? 'Permissions saved' : 'Failed to save', ok ? 'success' : 'error');
        } catch (e) {
            if (window.POS?.showToast) POS.showToast('Failed to save permissions', 'error');
        }
    });

    loadRolePerms();
});

const EmployeeUI = {
    data: @json((method_exists($employees, 'items') ? $employees->items() : $employees) ?? []),
    getById(id){ return this.data.find(e => e.id === id); },
    openEdit(id){
        const emp = this.getById(id);
        if (!emp) return;
        window.currentEditingEmployeeId = id;
        const modal = document.getElementById('edit-employee-modal');
        if (!modal) return;
        // Populate form fields
        const form = document.getElementById('edit-employee-form');
        if (form){
            form.querySelector('[name="first_name"]').value = emp.first_name || '';
            form.querySelector('[name="last_name"]').value = emp.last_name || '';
            form.querySelector('[name="email"]').value = emp.email || '';
            form.querySelector('[name="phone"]').value = emp.phone || '';
            form.querySelector('[name="role"]').value = (emp.position || '').toLowerCase();
            form.querySelector('[name="status"]').value = (emp.status || 'active');
            form.querySelector('[name="employee_id"]').value = emp.employee_id || '';
            form.querySelector('[name="department"]').value = (emp.department || 'sales');
            form.querySelector('[name="hire_date"]').value = (emp.hire_date ? String(emp.hire_date).slice(0,10) : '');
            // Permissions
            const perms = Array.isArray(emp.permissions) ? emp.permissions : [];
            form.querySelectorAll('input[name="permissions[]"]').forEach(cb => {
                cb.checked = perms.includes(cb.value);
            });
        }
        modal.classList.remove('hidden');
        modal.classList.add('flex');
    },
};

function switchTab(tabName) {
    // Hide all tabs
    document.querySelectorAll('.tab-content').forEach(content => {
        content.classList.add('hidden');
    });
    
    // Remove active state from all tabs
    document.querySelectorAll('.employee-tab').forEach(tab => {
        tab.classList.remove('border-green-500', 'text-green-600');
        tab.classList.add('border-transparent', 'text-gray-500');
    });
    
    // Show target tab
    document.getElementById(tabName + '-tab').classList.remove('hidden');
    
    // Set active state on clicked tab
    const activeTab = document.querySelector(`[data-tab="${tabName}"]`);
    activeTab.classList.remove('border-transparent', 'text-gray-500');
    activeTab.classList.add('border-green-500', 'text-green-600');
}

function applyFilters() {
    const search = document.getElementById('employee-search').value;
    const role = document.getElementById('role-filter').value;
    const status = document.getElementById('status-filter').value;
    const department = document.getElementById('department-filter').value;
    
    const params = new URLSearchParams();
    if (search) params.set('search', search);
    if (role !== 'all') params.set('role', role);
    if (status !== 'all') params.set('status', status);
    if (department !== 'all') params.set('department', department);
    
    window.location.href = `{{ route('employees.index') }}?${params.toString()}`;
}

function showAddEmployeeModal() {
    document.getElementById('add-employee-modal').classList.remove('hidden');
    document.getElementById('add-employee-modal').classList.add('flex');
}

function viewEmployee(employeeId) {
    window.location.href = `/employees/${employeeId}`;
}

function editEmployee(employeeId) {
    EmployeeUI.openEdit(Number(employeeId));
}

async function resetEmployeePassword(employeeId){
    if (!employeeId) return;
    if (!confirm('Send password reset email to this employee?')) return;
    try {
        const csrf = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
        const res = await (window.axios || axios).post(`/employees/${employeeId}/reset-password`, {}, { headers: { 'Accept':'application/json', 'X-CSRF-TOKEN': csrf } });
        const msg = res?.data?.message || 'Reset email sent';
        window.POS?.showToast?.(msg, 'success');
    } catch (err){
        const msg = err?.response?.data?.message || err?.response?.data?.error || 'Failed to send reset email';
        window.POS?.showToast?.(msg, 'error');
        alert(msg);
    }
}

async function resetEmployeePin(employeeId){
    if (!employeeId) return;
    if (!confirm('Reset this employee\'s PIN and email the new PIN?')) return;
    try {
        const csrf = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
        const res = await (window.axios || axios).post(`/employees/${employeeId}/reset-pin`, {}, { headers: { 'Accept':'application/json', 'X-CSRF-TOKEN': csrf } });
        const msg = res?.data?.message || 'PIN reset and email sent';
        window.POS?.showToast?.(msg, 'success');
    } catch (err){
        const msg = err?.response?.data?.message || err?.response?.data?.error || 'Failed to reset PIN';
        window.POS?.showToast?.(msg, 'error');
        alert(msg);
    }
}

function viewPerformance(employeeId) {
    window.location.href = `/employees/${employeeId}/performance`;
}

function exportEmployees() {
    const search = document.getElementById('employee-search').value;
    const role = document.getElementById('role-filter').value;
    const status = document.getElementById('status-filter').value;
    const department = document.getElementById('department-filter').value;
    
    const params = new URLSearchParams();
    if (search) params.set('search', search);
    if (role !== 'all') params.set('role', role);
    if (status !== 'all') params.set('status', status);
    if (department !== 'all') params.set('department', department);
    
    window.location.href = `{{ route('employees.export') }}?${params.toString()}`;
}
</script>
@endpush

@push('scripts')
<script>
(function(){
  function qs(id){return document.getElementById(id)}
  function fmtDuration(sec){ sec = Number(sec||0); const h=Math.floor(sec/3600); const m=Math.floor((sec%3600)/60); return `${h}h ${m}m`; }
  function isoLocal(dt){ const d = new Date(dt); const off = d.getTimezoneOffset(); const local = new Date(d.getTime() - off*60000); return local.toISOString().slice(0,16); }
  function toISOFromLocalInput(v){ if(!v) return null; const d = new Date(v); return new Date(d.getTime()).toISOString(); }

  // Tabs control
  document.querySelectorAll('.employee-tab').forEach(btn=>{
    btn.addEventListener('click', ()=>{
      const tab = btn.getAttribute('data-tab');
      document.querySelectorAll('.tab-content').forEach(el=>el.classList.add('hidden'));
      qs(tab+'-tab')?.classList.remove('hidden');
      document.querySelectorAll('.employee-tab').forEach(b=>b.classList.remove('border-green-500','text-green-600'));
      btn.classList.add('border-green-500','text-green-600');
    });
  });

  // Defaults for date filters
  const start = qs('tc-start-date');
  const end = qs('tc-end-date');
  const today = new Date();
  const twoWeeksAgo = new Date(Date.now()-13*86400000);
  start.value = twoWeeksAgo.toISOString().slice(0,10);
  end.value = today.toISOString().slice(0,10);

  async function loadEntries(){
    const params = new URLSearchParams();
    if (start.value) params.set('start_date', start.value);
    if (end.value) params.set('end_date', end.value);
    const emp = qs('tc-employee').value;
    if (emp) params.set('employee_id', emp);
    try {
      const res = await (window.axios||axios).get('/api/employees/time-entries?'+params.toString());
      const rows = Array.isArray(res.data?.entries) ? res.data.entries : [];
      const body = qs('tc-body');
      body.innerHTML = '';
      rows.forEach((r)=>{
        const tr = document.createElement('tr');
        tr.innerHTML = `
          <td class="px-4 py-2 text-sm text-gray-900">${(r.employee?.first_name||'')+' '+(r.employee?.last_name||'')}</td>
          <td class="px-4 py-2 text-sm"><input type="datetime-local" class="tc-in border rounded px-2 py-1" value="${isoLocal(r.clock_in)}" data-id="${r.id}"></td>
          <td class="px-4 py-2 text-sm"><input type="datetime-local" class="tc-out border rounded px-2 py-1" value="${r.clock_out ? isoLocal(r.clock_out) : ''}" data-id="${r.id}"></td>
          <td class="px-4 py-2 text-sm text-gray-700">${fmtDuration(r.duration_seconds || (r.clock_out ? ( (new Date(r.clock_out)-new Date(r.clock_in))/1000 ) : 0))}</td>
          <td class="px-4 py-2 text-sm"><input type="text" class="tc-notes border rounded px-2 py-1 w-56" value="${r.notes||''}" data-id="${r.id}"></td>
          <td class="px-4 py-2 text-right"><button class="tc-save bg-blue-600 hover:bg-blue-700 text-white text-xs px-3 py-1 rounded" data-id="${r.id}">Save</button></td>
        `;
        body.appendChild(tr);
      });
    } catch(e) {
      (window.POS?.showToast||alert)('Failed to load time entries','error');
    }
  }

  qs('tc-refresh').addEventListener('click', loadEntries);

  // Save handler (delegated)
  document.addEventListener('click', async function(e){
    const btn = e.target.closest('.tc-save');
    if (!btn) return;
    const id = btn.getAttribute('data-id');
    const row = btn.closest('tr');
    const clock_in = toISOFromLocalInput(row.querySelector('.tc-in').value);
    const outEl = row.querySelector('.tc-out');
    const clock_out = outEl && outEl.value ? toISOFromLocalInput(outEl.value) : null;
    const notes = row.querySelector('.tc-notes').value;
    try {
      const res = await (window.axios||axios).put('/api/employees/time-entries/'+id, { clock_in, clock_out, notes });
      if (res.status>=200 && res.status<300) {
        (window.POS?.showToast||alert)('Entry updated','success');
        loadEntries();
      } else {
        (window.POS?.showToast||alert)('Failed to update entry','error');
      }
    } catch(err) {
      const msg = err?.response?.data?.error || 'Failed to update entry';
      (window.POS?.showToast||alert)(msg,'error');
    }
  });

  // Add entry
  qs('tc-add').addEventListener('click', async function(){
    const emp = qs('tc-employee').value;
    if (!emp) { (window.POS?.showToast||alert)('Select an employee filter to add an entry','warning'); return; }
    const st = prompt('Clock In (YYYY-MM-DD HH:MM) local time:');
    if (!st) return;
    const en = prompt('Clock Out (optional, YYYY-MM-DD HH:MM):');
    try {
      const payload = { employee_id: emp, clock_in: new Date(st).toISOString() };
      if (en) payload.clock_out = new Date(en).toISOString();
      const res = await (window.axios||axios).post('/api/employees/time-entries', payload);
      if (res.status>=200 && res.status<300) {
        (window.POS?.showToast||alert)('Entry created','success');
        loadEntries();
      } else {
        (window.POS?.showToast||alert)('Failed to create entry','error');
      }
    } catch(err) {
      const msg = err?.response?.data?.error || 'Failed to create entry';
      (window.POS?.showToast||alert)(msg,'error');
    }
  });

  // Auto-load when tab opened
  document.querySelector('[data-tab="timeclock"]').addEventListener('click', loadEntries);
})();
</script>
@endpush
