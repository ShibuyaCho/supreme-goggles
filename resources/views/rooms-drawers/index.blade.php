@extends('layouts.app')

@section('title', 'Rooms & Drawers Management')

@section('content')
<div class="min-h-screen bg-gray-50 p-6">
    <div class="mx-auto max-w-7xl">
        <!-- Header -->
        <div class="mb-8">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-3xl font-bold text-gray-900">Rooms & Drawers Management</h1>
                    <p class="mt-2 text-gray-600">Manage cannabis storage rooms, drawers, and METRC compliance zones</p>
                </div>
                <div class="flex space-x-3">
                    <button onclick="generateReport()" class="inline-flex items-center rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 shadow-sm hover:bg-gray-50">
                        <svg class="mr-2 h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                        </svg>
                        Generate Report
                    </button>
                    <button onclick="openAddRoomModal()" class="inline-flex items-center rounded-lg bg-green-600 px-4 py-2 text-sm font-medium text-white shadow-sm hover:bg-green-700">
                        <svg class="mr-2 h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                        </svg>
                        Create Room
                    </button>
                    <button onclick="openCreateDrawer()" class="inline-flex items-center rounded-lg bg-purple-600 px-4 py-2 text-sm font-medium text-white shadow-sm hover:bg-purple-700">
                        <svg class="mr-2 h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 4V2a1 1 0 011-1h8a1 1 0 011 1v2M4 7h16M6 11h12M9 15h6M9 19h3" />
                        </svg>
                        Create Drawer
                    </button>
                </div>
            </div>
        </div>

        <!-- Overview Stats -->
        <div class="mb-8 grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-4">
            <div class="rounded-lg bg-white p-6 shadow">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <div class="flex h-8 w-8 items-center justify-center rounded-md bg-green-500 text-white">
                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                            </svg>
                        </div>
                    </div>
                    <div class="ml-5 w-0 flex-1">
                        <dl>
                            <dt class="truncate text-sm font-medium text-gray-500">Total Rooms</dt>
                            <dd class="text-lg font-medium text-gray-900">{{ $stats['total_rooms'] ?? '12' }}</dd>
                        </dl>
                    </div>
                </div>
            </div>

            <div class="rounded-lg bg-white p-6 shadow">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <div class="flex h-8 w-8 items-center justify-center rounded-md bg-blue-500 text-white">
                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                            </svg>
                        </div>
                    </div>
                    <div class="ml-5 w-0 flex-1">
                        <dl>
                            <dt class="truncate text-sm font-medium text-gray-500">Storage Capacity</dt>
                            <dd class="text-lg font-medium text-gray-900">{{ $stats['storage_used'] ?? '78' }}%</dd>
                        </dl>
                    </div>
                </div>
            </div>

            <div class="rounded-lg bg-white p-6 shadow">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <div class="flex h-8 w-8 items-center justify-center rounded-md bg-yellow-500 text-white">
                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z" />
                            </svg>
                        </div>
                    </div>
                    <div class="ml-5 w-0 flex-1">
                        <dl>
                            <dt class="truncate text-sm font-medium text-gray-500">Compliance Issues</dt>
                            <dd class="text-lg font-medium text-gray-900">{{ $stats['compliance_issues'] ?? '2' }}</dd>
                        </dl>
                    </div>
                </div>
            </div>

            <div class="rounded-lg bg-white p-6 shadow">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <div class="flex h-8 w-8 items-center justify-center rounded-md bg-purple-500 text-white">
                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4" />
                            </svg>
                        </div>
                    </div>
                    <div class="ml-5 w-0 flex-1">
                        <dl>
                            <dt class="truncate text-sm font-medium text-gray-500">Recent Transfers</dt>
                            <dd class="text-lg font-medium text-gray-900">{{ $stats['recent_transfers'] ?? '47' }}</dd>
                        </dl>
                    </div>
                </div>
            </div>
        </div>

        <!-- Room Categories -->
        <div class="mb-8">
            <div class="flex space-x-1 rounded-lg bg-gray-100 p-1">
                <button class="room-category-tab flex-1 rounded-md bg-white px-3 py-2 text-sm font-medium text-gray-900 shadow-sm" data-category="all">
                    All Rooms
                </button>
                <button class="room-category-tab flex-1 rounded-md px-3 py-2 text-sm font-medium text-gray-500 hover:text-gray-700" data-category="cultivation">
                    Cultivation
                </button>
                <button class="room-category-tab flex-1 rounded-md px-3 py-2 text-sm font-medium text-gray-500 hover:text-gray-700" data-category="processing">
                    Processing
                </button>
                <button class="room-category-tab flex-1 rounded-md px-3 py-2 text-sm font-medium text-gray-500 hover:text-gray-700" data-category="packaging">
                    Packaging
                </button>
                <button class="room-category-tab flex-1 rounded-md px-3 py-2 text-sm font-medium text-gray-500 hover:text-gray-700" data-category="storage">
                    Storage
                </button>
                <button class="room-category-tab flex-1 rounded-md px-3 py-2 text-sm font-medium text-gray-500 hover:text-gray-700" data-category="sales">
                    Sales Floor
                </button>
            </div>
        </div>

        <!-- Rooms Grid -->
        <div id="rooms-grid" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            @foreach($rooms ?? $defaultRooms as $room)
            <div class="room-card rounded-lg bg-white p-6 shadow hover:shadow-lg transition-shadow" data-category="{{ $room['category'] }}">
                <!-- Room Header -->
                <div class="flex items-center justify-between mb-4">
                    <div class="flex items-center">
                        <div class="flex h-10 w-10 items-center justify-center rounded-lg 
                            {{ $room['category'] === 'cultivation' ? 'bg-green-100' : 
                               ($room['category'] === 'processing' ? 'bg-blue-100' : 
                                ($room['category'] === 'packaging' ? 'bg-purple-100' : 
                                 ($room['category'] === 'storage' ? 'bg-yellow-100' : 'bg-orange-100'))) }}">
                            <svg class="h-6 w-6 
                                {{ $room['category'] === 'cultivation' ? 'text-green-600' : 
                                   ($room['category'] === 'processing' ? 'text-blue-600' : 
                                    ($room['category'] === 'packaging' ? 'text-purple-600' : 
                                     ($room['category'] === 'storage' ? 'text-yellow-600' : 'text-orange-600'))) }}" 
                                fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                @if($room['category'] === 'cultivation')
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z" />
                                @elseif($room['category'] === 'processing')
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                                @elseif($room['category'] === 'packaging')
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                                @elseif($room['category'] === 'storage')
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 4V2a1 1 0 011-1h8a1 1 0 011 1v2m0 0V1a1 1 0 011-1h2a1 1 0 011 1v3M7 4H5a1 1 0 00-1 1v16a1 1 0 001 1h14a1 1 0 001-1V5a1 1 0 00-1-1h-2M7 4h10M9 9h6m-6 4h6m-3 4h3" />
                                @else
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" />
                                @endif
                            </svg>
                        </div>
                        <div class="ml-3">
                            <h3 class="text-lg font-medium text-gray-900">{{ $room['name'] }}</h3>
                            <p class="text-sm text-gray-500">{{ ucfirst($room['category']) }} Room</p>
                        </div>
                    </div>
                    <div class="flex items-center space-x-2">
                        @if($room['compliance_status'] === 'compliant')
                        <span class="inline-flex items-center rounded-full bg-green-100 px-2.5 py-0.5 text-xs font-medium text-green-800">
                            <svg class="-ml-0.5 mr-1.5 h-2 w-2 fill-current" viewBox="0 0 8 8">
                                <circle cx="4" cy="4" r="3" />
                            </svg>
                            Compliant
                        </span>
                        @else
                        <span class="inline-flex items-center rounded-full bg-red-100 px-2.5 py-0.5 text-xs font-medium text-red-800">
                            <svg class="-ml-0.5 mr-1.5 h-2 w-2 fill-current" viewBox="0 0 8 8">
                                <circle cx="4" cy="4" r="3" />
                            </svg>
                            Issue
                        </span>
                        @endif
                    </div>
                </div>

                <!-- Room Details -->
                <div class="space-y-3 mb-4">
                    <div class="flex justify-between text-sm">
                        <span class="text-gray-500">METRC ID:</span>
                        <span class="font-medium font-mono">{{ $room['metrc_id'] }}</span>
                    </div>
                    <div class="flex justify-between text-sm">
                        <span class="text-gray-500">Capacity:</span>
                        <span class="font-medium">{{ $room['current_items'] }}/{{ $room['max_capacity'] }} items</span>
                    </div>
                    <div class="flex justify-between text-sm">
                        <span class="text-gray-500">Square Footage:</span>
                        <span class="font-medium">{{ number_format($room['square_feet']) }} sq ft</span>
                    </div>
                    @if(isset($room['temperature']))
                    <div class="flex justify-between text-sm">
                        <span class="text-gray-500">Temperature:</span>
                        <span class="font-medium">{{ $room['temperature'] }}°F</span>
                    </div>
                    @endif
                    @if(isset($room['humidity']))
                    <div class="flex justify-between text-sm">
                        <span class="text-gray-500">Humidity:</span>
                        <span class="font-medium">{{ $room['humidity'] }}%</span>
                    </div>
                    @endif
                </div>

                <!-- Capacity Bar -->
                <div class="mb-4">
                    <div class="flex justify-between text-sm mb-1">
                        <span class="text-gray-500">Capacity Usage</span>
                        <span class="font-medium">{{ round(($room['current_items'] / $room['max_capacity']) * 100) }}%</span>
                    </div>
                    <div class="w-full bg-gray-200 rounded-full h-2">
                        @php
                            $usage_percent = ($room['current_items'] / $room['max_capacity']) * 100;
                            $color_class = $usage_percent > 90 ? 'bg-red-500' : ($usage_percent > 75 ? 'bg-yellow-500' : 'bg-green-500');
                        @endphp
                        <div class="{{ $color_class }} h-2 rounded-full transition-all duration-300" style="width: {{ $usage_percent }}%"></div>
                    </div>
                </div>

                <!-- Drawers Section -->
                @if(isset($room['drawers']) && count($room['drawers']) > 0)
                <div class="mb-4">
                    <h4 class="text-sm font-medium text-gray-900 mb-2">Drawers ({{ count($room['drawers']) }})</h4>
                    <div class="grid grid-cols-3 gap-2">
                        @foreach($room['drawers'] as $drawer)
                        <div class="p-2 border rounded text-center {{ $drawer['status'] === 'full' ? 'border-red-200 bg-red-50' : 
                            ($drawer['status'] === 'partial' ? 'border-yellow-200 bg-yellow-50' : 'border-green-200 bg-green-50') }}">
                            <div class="text-xs font-medium">{{ $drawer['name'] }}</div>
                            <div class="text-xs text-gray-500">{{ $drawer['items_count'] }} items</div>
                        </div>
                        @endforeach
                    </div>
                </div>
                @endif

                <!-- Actions -->
                <div class="flex space-x-2">
                    <button onclick="viewRoomDetails({{ $room['id'] }})" class="flex-1 bg-blue-600 text-white px-3 py-2 rounded-md text-sm hover:bg-blue-700">
                        View Details
                    </button>
                    <button onclick="transferToRoom({{ $room['id'] }})" class="flex-1 border border-gray-300 text-gray-700 px-3 py-2 rounded-md text-sm hover:bg-gray-50">
                        Transfer
                    </button>
                    <button onclick="openAddDrawerModal({{ $room['id'] }}, {{ @json($room['name']) }})" class="px-3 py-2 border border-green-300 text-green-700 rounded-md text-sm hover:bg-green-50" title="Add Drawer">
                        + Drawer
                    </button>
                    <button onclick="editRoom({{ $room['id'] }})" class="px-3 py-2 border border-gray-300 text-gray-700 rounded-md text-sm hover:bg-gray-50" title="Edit Room">
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                        </svg>
                    </button>
                </div>
            </div>
            @endforeach
        </div>

        <!-- Quick Actions -->
        <div class="mt-8 rounded-lg bg-white p-6 shadow">
            <h2 class="text-lg font-medium text-gray-900 mb-4">Quick Actions</h2>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <button onclick="bulkTransfer()" class="p-4 border-2 border-dashed border-gray-300 rounded-lg text-center hover:border-blue-500 hover:bg-blue-50">
                    <div class="text-blue-600 mb-2">
                        <svg class="h-8 w-8 mx-auto" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4" />
                        </svg>
                    </div>
                    <h3 class="text-sm font-medium text-gray-900">Bulk Transfer</h3>
                    <p class="text-xs text-gray-500 mt-1">Transfer multiple items between rooms</p>
                </button>

                <button onclick="complianceReport()" class="p-4 border-2 border-dashed border-gray-300 rounded-lg text-center hover:border-green-500 hover:bg-green-50">
                    <div class="text-green-600 mb-2">
                        <svg class="h-8 w-8 mx-auto" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a4 4 0 01-4-4V5a4 4 0 014-4h10a4 4 0 014 4v14a4 4 0 01-4 4z" />
                        </svg>
                    </div>
                    <h3 class="text-sm font-medium text-gray-900">Compliance Report</h3>
                    <p class="text-xs text-gray-500 mt-1">Generate METRC compliance report</p>
                </button>

                <button onclick="environmentalControls()" class="p-4 border-2 border-dashed border-gray-300 rounded-lg text-center hover:border-purple-500 hover:bg-purple-50">
                    <div class="text-purple-600 mb-2">
                        <svg class="h-8 w-8 mx-auto" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 100 4m0-4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 100 4m0-4v2m0-6V4" />
                        </svg>
                    </div>
                    <h3 class="text-sm font-medium text-gray-900">Environmental Controls</h3>
                    <p class="text-xs text-gray-500 mt-1">Monitor temperature and humidity</p>
                </button>
            </div>
        </div>
    </div>

    <!-- Cash Drawer Management -->
    <div class="mt-8 rounded-lg bg-white p-6 shadow">
        <div class="flex items-center justify-between mb-4">
            <h2 class="text-lg font-semibold text-gray-900">Cash Drawer Management</h2>
            <button id="rd-add-drawer" class="inline-flex items-center rounded-lg bg-purple-600 px-3 py-2 text-sm font-medium text-white shadow-sm hover:bg-purple-700">+ Add Drawer</button>
        </div>
        <div id="rd-drawers" class="space-y-3"></div>
    </div>

    <!-- Activity Log -->
    <div class="mt-8 rounded-lg bg-white p-6 shadow">
        <div class="flex items-center justify-between mb-2">
            <h2 class="text-lg font-semibold text-gray-900">Activity Log</h2>
            <button id="rd-clear-log" class="px-3 py-1 text-xs rounded border border-gray-300 text-gray-700 hover:bg-gray-50">Clear</button>
        </div>
        <div id="rd-activity" class="space-y-2 text-sm text-gray-700"></div>
    </div>
</div>

<!-- Count Drawer Modal -->
<div id="rd-count-modal" class="fixed inset-0 z-50 hidden items-center justify-center bg-black/50 p-4">
  <div class="bg-white rounded-lg shadow-xl w-full max-w-2xl">
    <div class="p-4 border-b flex items-center justify-between">
      <h3 class="text-lg font-semibold">Count Cash Drawer</h3>
      <button id="rd-count-close" class="text-gray-400 hover:text-gray-600">
        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
      </button>
    </div>
    <div class="p-4">
      <div class="grid grid-cols-2 gap-4">
        <div>
          <h4 class="font-medium text-gray-900 mb-2">Bills</h4>
          <div class="space-y-2">
            <div class="flex items-center justify-between"><label class="text-sm">$1</label><input data-denom="1" type="number" min="0" class="rd-denom w-24 text-right px-2 py-1 border rounded" placeholder="0"/></div>
            <div class="flex items-center justify-between"><label class="text-sm">$5</label><input data-denom="5" type="number" min="0" class="rd-denom w-24 text-right px-2 py-1 border rounded" placeholder="0"/></div>
            <div class="flex items-center justify-between"><label class="text-sm">$10</label><input data-denom="10" type="number" min="0" class="rd-denom w-24 text-right px-2 py-1 border rounded" placeholder="0"/></div>
            <div class="flex items-center justify-between"><label class="text-sm">$20</label><input data-denom="20" type="number" min="0" class="rd-denom w-24 text-right px-2 py-1 border rounded" placeholder="0"/></div>
            <div class="flex items-center justify-between"><label class="text-sm">$50</label><input data-denom="50" type="number" min="0" class="rd-denom w-24 text-right px-2 py-1 border rounded" placeholder="0"/></div>
            <div class="flex items-center justify-between"><label class="text-sm">$100</label><input data-denom="100" type="number" min="0" class="rd-denom w-24 text-right px-2 py-1 border rounded" placeholder="0"/></div>
          </div>
        </div>
        <div>
          <h4 class="font-medium text-gray-900 mb-2">Coins</h4>
          <div class="space-y-2">
            <div class="flex items-center justify-between"><label class="text-sm">1¢</label><input data-denom="0.01" type="number" min="0" class="rd-denom w-24 text-right px-2 py-1 border rounded" placeholder="0"/></div>
            <div class="flex items-center justify-between"><label class="text-sm">5¢</label><input data-denom="0.05" type="number" min="0" class="rd-denom w-24 text-right px-2 py-1 border rounded" placeholder="0"/></div>
            <div class="flex items-center justify-between"><label class="text-sm">10¢</label><input data-denom="0.10" type="number" min="0" class="rd-denom w-24 text-right px-2 py-1 border rounded" placeholder="0"/></div>
            <div class="flex items-center justify-between"><label class="text-sm">25¢</label><input data-denom="0.25" type="number" min="0" class="rd-denom w-24 text-right px-2 py-1 border rounded" placeholder="0"/></div>
          </div>
        </div>
      </div>
      <div class="mt-4 grid grid-cols-1 md:grid-cols-3 gap-4">
        <div class="p-3 rounded bg-gray-50 border"><div class="text-xs text-gray-500">Counted Total</div><div id="rd-counted" class="text-xl font-semibold">$0.00</div></div>
        <div class="p-3 rounded bg-gray-50 border"><div class="text-xs text-gray-500">Debit Total (this till)</div><div id="rd-debit" class="text-xl font-semibold">$0.00</div></div>
        <div class="p-3 rounded bg-gray-50 border"><div class="text-xs text-gray-500">Variance</div><div id="rd-variance" class="text-xl font-semibold">$0.00</div></div>
      </div>
      <div class="mt-4 grid grid-cols-1 md:grid-cols-2 gap-4">
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Counted By</label>
          <input id="rd-counted-by" type="text" class="w-full px-3 py-2 border rounded" placeholder="Employee name" />
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Approved By (Manager)</label>
          <input id="rd-approved-by" type="text" class="w-full px-3 py-2 border rounded" placeholder="Manager name" />
        </div>
      </div>
      <div class="mt-4 flex items-center justify-end gap-3">
        <button id="rd-count-cancel" class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-50">Cancel</button>
        <button id="rd-count-save" class="px-4 py-2 text-sm font-medium text-white bg-green-600 hover:bg-green-700 rounded-md">Save Count</button>
      </div>
    </div>
  </div>
</div>

<!-- Add Room Modal -->
<div id="add-room-modal" class="fixed inset-0 z-50 hidden items-center justify-center bg-black/50 p-4">
    <div class="bg-white rounded-lg shadow-xl w-full max-w-lg">
        <div class="p-6 border-b border-gray-200 flex items-center justify-between">
            <h3 class="text-lg font-semibold text-gray-900">Add Room</h3>
            <button id="add-room-close" class="text-gray-400 hover:text-gray-600">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>
        <div class="p-6 space-y-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Room Name</label>
                <input id="room-name" type="text" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-cannabis-green" placeholder="e.g. Storage Vault A">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Type</label>
                <select id="room-type" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-cannabis-green">
                    <option value="storage">Storage</option>
                    <option value="processing">Processing</option>
                    <option value="production">Production</option>
                    <option value="sales">Sales Floor</option>
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Max Capacity (optional)</label>
                <input id="room-capacity" type="number" min="0" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-cannabis-green" placeholder="e.g. 1000">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Description (optional)</label>
                <textarea id="room-description" rows="3" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-cannabis-green" placeholder="Notes about this room"></textarea>
            </div>
            <div class="flex items-center justify-end gap-3 pt-2">
                <button id="add-room-cancel" class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-50">Cancel</button>
                <button id="add-room-submit" class="px-4 py-2 text-sm font-medium text-white bg-green-600 hover:bg-green-700 rounded-md">Create Room</button>
            </div>
        </div>
    </div>
</div>

<!-- Add Drawer Modal -->
<div id="add-drawer-modal" class="fixed inset-0 z-50 hidden items-center justify-center bg-black/50 p-4">
    <div class="bg-white rounded-lg shadow-xl w-full max-w-md">
        <div class="p-6 border-b border-gray-200 flex items-center justify-between">
            <h3 class="text-lg font-semibold text-gray-900">Create Drawer</h3>
            <button id="add-drawer-close" class="text-gray-400 hover:text-gray-600">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>
        <div class="p-6 space-y-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Room</label>
                <select id="drawer-room-select" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-cannabis-green">
                    @foreach(($rooms ?? $defaultRooms) as $room)
                        <option value="{{ $room['id'] }}">{{ $room['name'] }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Drawer Name</label>
                <input id="drawer-name" type="text" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-cannabis-green" placeholder="e.g. Register 1">
            </div>
            <div class="flex items-center justify-end gap-3 pt-2">
                <button id="add-drawer-cancel" class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-50">Cancel</button>
                <button id="add-drawer-submit" class="px-4 py-2 text-sm font-medium text-white bg-green-600 hover:bg-green-700 rounded-md">Create Drawer</button>
            </div>
        </div>
    </div>
</div>

@php
    $defaultRooms = [
        [
            'id' => 1,
            'name' => 'Cultivation Room A',
            'category' => 'cultivation',
            'metrc_id' => 'CULT-A-001',
            'current_items' => 45,
            'max_capacity' => 50,
            'square_feet' => 1200,
            'temperature' => 72,
            'humidity' => 65,
            'compliance_status' => 'compliant',
            'drawers' => [
                ['name' => 'D1', 'items_count' => 12, 'status' => 'partial'],
                ['name' => 'D2', 'items_count' => 15, 'status' => 'partial'],
                ['name' => 'D3', 'items_count' => 18, 'status' => 'partial']
            ]
        ],
        [
            'id' => 2,
            'name' => 'Processing Lab',
            'category' => 'processing',
            'metrc_id' => 'PROC-001',
            'current_items' => 28,
            'max_capacity' => 30,
            'square_feet' => 800,
            'temperature' => 68,
            'humidity' => 45,
            'compliance_status' => 'compliant',
            'drawers' => [
                ['name' => 'P1', 'items_count' => 8, 'status' => 'partial'],
                ['name' => 'P2', 'items_count' => 10, 'status' => 'partial'],
                ['name' => 'P3', 'items_count' => 10, 'status' => 'partial']
            ]
        ],
        [
            'id' => 3,
            'name' => 'Packaging Room',
            'category' => 'packaging',
            'metrc_id' => 'PACK-001',
            'current_items' => 67,
            'max_capacity' => 75,
            'square_feet' => 600,
            'compliance_status' => 'compliant',
            'drawers' => [
                ['name' => 'PK1', 'items_count' => 20, 'status' => 'full'],
                ['name' => 'PK2', 'items_count' => 22, 'status' => 'full'],
                ['name' => 'PK3', 'items_count' => 25, 'status' => 'full']
            ]
        ],
        [
            'id' => 4,
            'name' => 'Storage Vault A',
            'category' => 'storage',
            'metrc_id' => 'STOR-A-001',
            'current_items' => 89,
            'max_capacity' => 100,
            'square_feet' => 1500,
            'temperature' => 65,
            'humidity' => 55,
            'compliance_status' => 'issue',
            'drawers' => [
                ['name' => 'S1', 'items_count' => 30, 'status' => 'full'],
                ['name' => 'S2', 'items_count' => 29, 'status' => 'full'],
                ['name' => 'S3', 'items_count' => 30, 'status' => 'full']
            ]
        ],
        [
            'id' => 5,
            'name' => 'Sales Floor Display',
            'category' => 'sales',
            'metrc_id' => 'SALES-001',
            'current_items' => 156,
            'max_capacity' => 200,
            'square_feet' => 2000,
            'compliance_status' => 'compliant',
            'drawers' => [
                ['name' => 'SF1', 'items_count' => 52, 'status' => 'partial'],
                ['name' => 'SF2', 'items_count' => 52, 'status' => 'partial'],
                ['name' => 'SF3', 'items_count' => 52, 'status' => 'partial']
            ]
        ],
        [
            'id' => 6,
            'name' => 'Cultivation Room B',
            'category' => 'cultivation',
            'metrc_id' => 'CULT-B-001',
            'current_items' => 23,
            'max_capacity' => 50,
            'square_feet' => 1200,
            'temperature' => 74,
            'humidity' => 62,
            'compliance_status' => 'compliant',
            'drawers' => [
                ['name' => 'D4', 'items_count' => 8, 'status' => 'empty'],
                ['name' => 'D5', 'items_count' => 7, 'status' => 'empty'],
                ['name' => 'D6', 'items_count' => 8, 'status' => 'empty']
            ]
        ]
    ];

    $stats = [
        'total_rooms' => count($defaultRooms),
        'storage_used' => 78,
        'compliance_issues' => 1,
        'recent_transfers' => 47
    ];
@endphp

<script>
document.addEventListener('DOMContentLoaded', function() {
    const categoryTabs = document.querySelectorAll('.room-category-tab');
    const roomCards = document.querySelectorAll('.room-card');

    // Activity Log state
    const activityEl = document.getElementById('rd-activity');
    const LOG_KEY = 'rd-activity-log';
    function readJson(k, d){ try { const v = localStorage.getItem(k); return v ? JSON.parse(v) : d; } catch(_) { return d; } }
    function loadActivity(){
      const a = readJson(LOG_KEY, []);
      const b = readJson('pos_activity_log', []);
      const merged = [...a, ...b].filter(Boolean);
      const seen = new Set();
      const out = [];
      for (let i=0;i<merged.length;i++){
        const m = merged[i]||{}; const key = `${m.at||''}|${m.title||''}|${m.type||''}`;
        if (seen.has(key)) continue; seen.add(key); out.push(m);
      }
      return out;
    }
    let activityLog = loadActivity();
    function saveActivity(){ try { localStorage.setItem(LOG_KEY, JSON.stringify(activityLog)); localStorage.setItem('pos_activity_log', JSON.stringify(activityLog)); } catch(_) {} }
    function currentUserName(){
      try { const n = window.posAuth?.getUser?.()?.name; if (n) return n; } catch(_) {}
      try { return @json(auth()->user()->name ?? 'User'); } catch(_) { return 'User'; }
    }
    function renderActivity(){
      if (!activityEl) return;
      activityEl.innerHTML = '';
      if (!activityLog.length){ activityEl.innerHTML = '<div class="text-gray-500">No activity yet</div>'; return; }
      const reversed = activityLog.slice().reverse();
      reversed.forEach((item, idx) => {
        const row = document.createElement('div');
        row.className = 'p-2 border rounded';
        const when = new Date(item.at).toLocaleString();
        const who = item.by || '';
        let detailsHtml = '';
        if (item.type === 'drawer_count' && item.data) {
          const d = item.data;
          detailsHtml = `<div class=\"mt-1 text-xs\">
            Drawer: <span class=\"font-semibold\">${d.drawerName||'-'}</span><br/>
            Counted: <span class=\"font-semibold\">$${(d.countedTotal||0).toFixed(2)}</span> • Expected Cash: <span class=\"font-semibold\">$${(d.expectedCashTotal||0).toFixed(2)}</span> • Debit: <span class=\"font-semibold\">$${(d.debitTotal||0).toFixed(2)}</span> • Variance: <span class=\"font-semibold\">$${(d.variance||0).toFixed(2)}</span><br/>
            By: ${d.countedBy||who||'-'} • Approved: ${d.approvedBy||'-'}
          </div>`;
        } else if (item.details) {
          detailsHtml = `<div class=\"text-xs mt-1\">${item.details}</div>`;
        }
        row.innerHTML = `<div class=\"flex items-start justify-between gap-3\">
          <div>
            <div class=\"font-medium\">${item.title}</div>
            <div class=\"text-xs text-gray-500\">${when} ${who?`• ${who}`:''}</div>
            ${detailsHtml}
          </div>
          ${item.type==='drawer_count' ? `<button class=\"px-2 py-1 text-xs bg-blue-600 text-white rounded\" data-print-idx=\"${idx}\">Print</button>` : ''}
        </div>`;
        activityEl.appendChild(row);
      });
      activityEl.querySelectorAll('button[data-print-idx]')?.forEach(btn => {
        btn.addEventListener('click', () => {
          const idx = parseInt(btn.getAttribute('data-print-idx'),10);
          const entry = activityLog.slice().reverse()[idx];
          if (entry && entry.type === 'drawer_count' && entry.data) printDrawerCount(entry);
        });
      });
    }
    async function addActivity(title, details=''){
      const entry = { at: new Date().toISOString(), by: currentUserName(), title };
      if (details && typeof details === 'object') { entry.type = details.type; entry.data = details.data; }
      else if (details) { entry.details = details; }
      activityLog.push(entry);
      saveActivity();
      try { const k='pos_activity_log'; const prev=JSON.parse(localStorage.getItem(k)||'[]'); prev.push(entry); localStorage.setItem(k, JSON.stringify(prev)); } catch(_) {}
      try { await fetch('/api/activity', { method:'POST', headers:{ 'Content-Type':'application/json', Accept:'application/json' }, body: JSON.stringify({ action:'rooms_drawers', entry }) }); } catch(_) {}
      renderActivity();
    }
    document.getElementById('rd-clear-log')?.addEventListener('click', ()=>{ activityLog = []; saveActivity(); renderActivity(); });
    renderActivity();
    (async function hydrateActivityFromServer(){
      try {
        const r = await fetch('/api/activity?action=rooms_drawers&limit=200', { headers: { Accept: 'application/json' } });
        if (!r.ok) return;
        const data = await r.json();
        const rows = Array.isArray(data?.logs) ? data.logs : [];
        if (!rows.length) return;
        const mapped = rows.map(x => {
          const p = x.payload || {}; const e = p.entry || {};
          const title = e.title || p.title || (p.action ? String(p.action).replace(/_/g,' ') : 'Activity');
          return { at: e.at || x.created_at || new Date().toISOString(), by: e.by || (p.user?.name || ''), title, type: e.type || p.type || undefined, data: e.data || p.data || undefined, details: e.details || p.details || undefined };
        });
        const combined = [...activityLog, ...mapped];
        const seen = new Set();
        activityLog = combined.filter(item => { const key = `${item.at||''}|${item.title||''}|${item.type||''}`; if (seen.has(key)) return false; seen.add(key); return true; });
        saveActivity();
        renderActivity();
      } catch (_) {}
    })();

    function printDrawerCount(entry){
      const d = entry?.data || {};
      const win = window.open('', 'print-drawer', 'width=600,height=800');
      if (!win) return;
      const rows = (d.breakdown||[])
        .slice()
        .sort((a,b)=> (a.denom||0) - (b.denom||0))
        .filter(x=>x.qty>0)
        .map(x=>`<tr><td style=\"padding:4px 8px;\">${x.denom>=1?`$${x.denom.toFixed(0)}`:`${(x.denom*100).toFixed(0)}¢`}</td><td style=\"padding:4px 8px; text-align:right;\">${x.qty}</td><td style=\"padding:4px 8px; text-align:right;\">$${(x.denom*x.qty).toFixed(2)}</td></tr>`)
        .join('');
      win.document.write(`<!doctype html><html><head><meta charset=\"utf-8\"/><title>Drawer Count - ${d.drawerName||''}</title><style>body{font-family:ui-sans-serif,system-ui,-apple-system,Segoe UI,Roboto,Helvetica,Arial; padding:16px;} h1{font-size:18px;margin:0 0 8px;} table{width:100%; border-collapse:collapse;} th,td{border-bottom:1px solid #e5e7eb;} .tot{font-weight:600;} .sign{margin-top:24px; display:flex; gap:24px;} .line{border-top:1px solid #111827; padding-top:4px; min-width:200px;}</style></head><body>
        <h1>Closed Drawer Count</h1>
        <div style=\"font-size:12px;color:#374151;\">Drawer: <strong>${d.drawerName||'-'}</strong><br/>When: ${new Date(d.at||entry.at).toLocaleString()}<br/>Counted By: ${d.countedBy||'-'} • Approved By: ${d.approvedBy||'-'}</div>
        <table style=\"margin-top:12px;\">
          <thead><tr><th style=\"text-align:left;padding:4px 8px;\">Denomination</th><th style=\"text-align:right;padding:4px 8px;\">Qty</th><th style=\"text-align:right;padding:4px 8px;\">Amount</th></tr></thead>
          <tbody>${rows || '<tr><td colspan=\"3\" style=\"padding:8px;color:#6b7280;\">No denominations entered</td></tr>'}</tbody>
          <tfoot>
            <tr><td></td><td class=\"tot\" style=\"padding:6px 8px; text-align:right;\">Cash Total</td><td class=\"tot\" style=\"padding:6px 8px; text-align:right;\">$${(d.countedTotal||0).toFixed(2)}</td></tr>
            <tr><td></td><td style=\"padding:6px 8px; text-align:right;\">Expected Cash</td><td style=\"padding:6px 8px; text-align:right;\">$${(d.expectedCashTotal||0).toFixed(2)}</td></tr>
            <tr><td></td><td style=\"padding:6px 8px; text-align:right;\">Debit Total</td><td style=\"padding:6px 8px; text-align:right;\">$${(d.debitTotal||0).toFixed(2)}</td></tr>
            <tr><td></td><td class=\"tot\" style=\"padding:6px 8px; text-align:right;\">Variance</td><td class=\"tot\" style=\"padding:6px 8px; text-align:right;\">$${(d.variance||0).toFixed(2)}</td></tr>
          </tfoot>
        </table>
        <div class=\"sign\">
          <div class=\"line\">Employee Signature</div>
          <div class=\"line\">Manager Signature</div>
        </div>
        <script>window.onload = () => { setTimeout(()=>window.print(), 50); }<\\/script>
      </body></html>`);
      try { win.document.close(); } catch(_) {}
    }

    // Extreme persistence for Rooms (merge server + localStorage and render)
    const serverRooms = @json($rooms ?? []);
    const ROOMS_KEYS = ['pos_rooms','rd-rooms'];
    function loadRooms(){
      for (const k of ROOMS_KEYS) {
        try { const raw = localStorage.getItem(k); if (raw) { const arr = JSON.parse(raw); if (Array.isArray(arr)) return arr; } } catch(_) {}
      }
      return [];
    }
    function saveRooms(list){
      for (const k of ROOMS_KEYS) { try { localStorage.setItem(k, JSON.stringify(list)); } catch(_) {} }
    }
    function normalizeRoom(r){
      return {
        id: r.id ?? null,
        name: r.name ?? r.room_name ?? '',
        type: r.type ?? r.category ?? 'storage',
        max_capacity: r.max_capacity ?? 0,
        room_id: r.room_id ?? null,
      };
    }
    let localRooms = Array.isArray(loadRooms()) ? loadRooms() : [];
    // Merge server rooms into local for resilience
    if (Array.isArray(serverRooms) && serverRooms.length){
      const byName = new Set(localRooms.map(r => (r.name||'').toLowerCase()));
      serverRooms.forEach(sr => { const nr = normalizeRoom(sr); if (!byName.has((nr.name||'').toLowerCase())) { localRooms.push(nr); byName.add((nr.name||'').toLowerCase()); } });
      saveRooms(localRooms);
    }
    // Also merge from Supabase via Node alias
    try {
      const r = await fetch('/api/rooms-open', { headers: { Accept: 'application/json' } });
      if (r.ok) {
        const data = await r.json();
        const list = Array.isArray(data?.rooms) ? data.rooms : [];
        const byName = new Set(localRooms.map(r => (r.name||'').toLowerCase()));
        list.forEach(sr => { const nr = normalizeRoom(sr); if (!byName.has((nr.name||'').toLowerCase())) { localRooms.push(nr); byName.add((nr.name||'').toLowerCase()); } });
        saveRooms(localRooms);
      }
    } catch(_) {}
    let roomsGrid = document.getElementById('rooms-grid');
    function ensureRoomsGrid(){ if (!roomsGrid) roomsGrid = document.getElementById('rooms-grid'); return !!roomsGrid; }
    function findRoomCardByName(n){
      try { return Array.from((roomsGrid||document).querySelectorAll('.room-card')).find(c => (c.querySelector('h3')?.textContent||'').trim().toLowerCase() === String(n||'').trim().toLowerCase()); } catch(_) { return null; }
    }
    function appendRoomCard(r){
      if (!ensureRoomsGrid() || !r || findRoomCardByName(r.name)) return;
      const usagePercent = 0;
      const html = `
        <div class="room-card rounded-lg bg-white p-6 shadow hover:shadow-lg transition-shadow" data-category="${r.type}">
          <div class="flex items-center justify-between mb-4">
            <div class="flex items-center">
              <div class="flex h-10 w-10 items-center justify-center rounded-lg ${r.type==='processing'?'bg-blue-100':r.type==='storage'?'bg-yellow-100':r.type==='production'?'bg-purple-100':'bg-orange-100'}"></div>
              <div class="ml-3">
                <h3 class="text-lg font-medium text-gray-900"></h3>
                <p class="text-sm text-gray-500">${r.type.charAt(0).toUpperCase()+r.type.slice(1)} Room</p>
              </div>
            </div>
            <span class="inline-flex items-center rounded-full bg-green-100 px-2.5 py-0.5 text-xs font-medium text-green-800">Compliant</span>
          </div>
          <div class="space-y-3 mb-4">
            <div class="flex justify-between text-sm"><span class="text-gray-500">METRC ID:</span><span class="font-medium font-mono">${r.room_id || '-'}</span></div>
            <div class="flex justify-between text-sm"><span class="text-gray-500">Capacity:</span><span class="font-medium">0/${r.max_capacity ?? 0} items</span></div>
          </div>
          <div class="mb-4">
            <div class="flex justify-between text-sm mb-1"><span class="text-gray-500">Capacity Usage</span><span class="font-medium">${usagePercent}%</span></div>
            <div class="w-full bg-gray-200 rounded-full h-2"><div class="bg-green-500 h-2 rounded-full" style="width:${usagePercent}%"></div></div>
          </div>
          <div class="flex space-x-2">
            <button class="flex-1 bg-blue-600 text-white px-3 py-2 rounded-md text-sm hover:bg-blue-700" onclick="viewRoomDetails(${JSON.stringify(r.id || r.name)})">View Details</button>
            <button class="flex-1 border border-gray-300 text-gray-700 px-3 py-2 rounded-md text-sm hover:bg-gray-50" onclick="transferToRoom(${JSON.stringify(r.id || r.name)})">Transfer</button>
            <button class="px-3 py-2 border border-green-300 text-green-700 rounded-md text-sm hover:bg-green-50" onclick="openAddDrawerModal(${JSON.stringify(r.id || '')}, ${JSON.stringify(r.name)})">+ Drawer</button>
          </div>
        </div>`;
      const wrap = document.createElement('div'); wrap.innerHTML = html.trim(); wrap.querySelector('h3').textContent = r.name; roomsGrid.prepend(wrap.firstElementChild);
    }
    // Render any local rooms not already present (with retries)
    function renderLocalRooms(){ try { (Array.isArray(localRooms)?localRooms:[]).forEach(appendRoomCard); } catch(_) {} }
    renderLocalRooms();
    setTimeout(renderLocalRooms, 50);
    setTimeout(renderLocalRooms, 300);
    window.addEventListener('load', renderLocalRooms);
    window.addEventListener('beforeunload', ()=>{ try { saveRooms(localRooms); } catch(_) {} });

    categoryTabs.forEach(tab => {
        tab.addEventListener('click', function() {
            const category = this.getAttribute('data-category');

            // Update active tab
            categoryTabs.forEach(t => {
                t.classList.remove('bg-white', 'text-gray-900', 'shadow-sm');
                t.classList.add('text-gray-500');
            });
            this.classList.remove('text-gray-500');
            this.classList.add('bg-white', 'text-gray-900', 'shadow-sm');

            // Filter rooms
            roomCards.forEach(card => {
                if (category === 'all' || card.getAttribute('data-category') === category) {
                    card.style.display = 'block';
                } else {
                    card.style.display = 'none';
                }
            });
        });
    });

    if (false) {
    // Cash Drawers state and helpers
    const drawersEl = document.getElementById('rd-drawers');
    const countModal = document.getElementById('rd-count-modal');
    let activeDrawerId = null;
    const cashDrawers = [
      { id: 1, name: 'Till #1', status: 'active', assignedEmployee: 'Cody', startingAmount: 220.00, currentAmount: 220.00, openedAt: null }
    ];
    function renderDrawers(){
      if (!drawersEl) return;
      drawersEl.innerHTML = '';
      cashDrawers.forEach(d => {
        const wrapper = document.createElement('div');
        wrapper.className = 'border rounded-lg p-4 flex items-center justify-between';
        wrapper.innerHTML = `
          <div>
            <div class="font-medium">${d.name} <span class="ml-2 text-xs ${d.status==='open'?'text-green-700':'text-gray-600'}">${d.status==='open'?'Open':'Closed'}</span></div>
            <div class="text-sm text-gray-600">Assigned To: <span class="font-medium">${d.assignedEmployee||'-'}</span></div>
            <div class="text-sm text-gray-600">Starting Amount: $${(d.startingAmount||0).toFixed(2)}</div>
            <div class="text-sm text-gray-600">Current Amount: $${(d.currentAmount||0).toFixed(2)}</div>
          </div>
          <div class="flex gap-2">
            <button data-act="open" data-id="${d.id}" class="px-3 py-1 text-sm rounded border ${d.status==='open'?'border-gray-300 text-gray-700 hover:bg-gray-50':'border-green-300 text-green-700 hover:bg-green-50'}">${d.status==='open'?'Open':'Open'}</button>
            <button data-act="close" data-id="${d.id}" class="px-3 py-1 text-sm rounded border border-gray-300 text-gray-700 hover:bg-gray-50">Close</button>
            <button data-act="count" data-id="${d.id}" class="px-3 py-1 text-sm rounded bg-blue-600 text-white hover:bg-blue-700">Count</button>
            <button data-act="delete" data-id="${d.id}" class="px-3 py-1 text-sm rounded border border-red-300 text-red-700 hover:bg-red-50">Delete Drawer</button>
            <button data-act="assign" data-id="${d.id}" class="px-3 py-1 text-sm rounded border border-purple-300 text-purple-700 hover:bg-purple-50">Assign Employee</button>
          </div>`;
        drawersEl.appendChild(wrapper);
      });
    }
    function getDrawer(id){ return cashDrawers.find(d=>String(d.id)===String(id)); }
    function openCountModal(drawer){
      activeDrawerId = drawer.id;
      countModal.classList.remove('hidden');
      countModal.classList.add('flex');
      // reset inputs
      countModal.querySelectorAll('.rd-denom').forEach(i=> i.value = '');
      updateCountSummary();
    }
    function closeCountModal(){ countModal.classList.add('hidden'); countModal.classList.remove('flex'); activeDrawerId = null; }
    function calcDebitTotalForDrawer(){
      try {
        const uid = window.posAuth?.getUser?.()?.id || null;
        const key = uid ? `cannabisPOS-sales-${uid}` : null;
        const list = key ? JSON.parse(localStorage.getItem(key) || '[]') : [];
        const total = list.filter(s=>s?.method==='debit').reduce((sum,s)=> sum + (parseFloat(s.amount)||parseFloat(s.total)||0), 0);
        return +(+total).toFixed(2);
      } catch(_) { return 0; }
    }
    function calculateCounted(){
      let total = 0;
      countModal.querySelectorAll('.rd-denom').forEach(input => {
        const denom = parseFloat(input.dataset.denom);
        const qty = parseInt(input.value||'0',10)||0;
        if (!isNaN(denom) && qty>0){ total += denom*qty; }
      });
      return +(+total).toFixed(2);
    }
    function updateCountSummary(){
      const d = getDrawer(activeDrawerId) || { currentAmount: 0 };
      const counted = calculateCounted();
      const debit = calcDebitTotalForDrawer();
      const variance = +(counted - (d.currentAmount||0)).toFixed(2);
      document.getElementById('rd-counted').textContent = `$${counted.toFixed(2)}`;
      document.getElementById('rd-debit').textContent = `$${debit.toFixed(2)}`;
      const varEl = document.getElementById('rd-variance');
      varEl.textContent = `${variance<0?'-':''}$${Math.abs(variance).toFixed(2)}`;
      varEl.className = `text-xl font-semibold ${variance<0?'text-red-600':variance>0?'text-green-600':'text-gray-900'}`;
    }
    drawersEl?.addEventListener('click', (e)=>{
      const btn = e.target.closest('button[data-act]');
      if (!btn) return;
      const id = btn.getAttribute('data-id');
      const act = btn.getAttribute('data-act');
      const d = getDrawer(id);
      if (!d) return;
      if (act==='open') { d.status='active'; d.openedAt = new Date().toISOString(); toast(`${d.name} opened`, 'success'); renderDrawers(); }
      if (act==='close') { d.status='closed'; toast(`${d.name} closed`, 'info'); renderDrawers(); }
      if (act==='count') { openCountModal(d); }
      if (act==='delete') { if (confirm('Delete this drawer?')) { const idx=cashDrawers.findIndex(x=>x.id===d.id); if (idx>=0) cashDrawers.splice(idx,1); toast('Drawer deleted','success'); renderDrawers(); } }
      if (act==='assign') { const name = prompt('Assign to employee name:'); if (name){ d.assignedEmployee = name; toast(`Assigned to ${name}`, 'success'); renderDrawers(); } }
    });
    document.getElementById('rd-add-drawer')?.addEventListener('click', ()=>{
      const name = prompt('Drawer name');
      const amt = parseFloat(prompt('Starting amount (e.g. 200)')||'0')||0;
      const nextId = Math.max(0,...cashDrawers.map(d=>d.id))+1;
      cashDrawers.push({ id: nextId, name: name||`Till #${nextId}`, status: 'active', assignedEmployee: '', startingAmount: amt, currentAmount: amt, openedAt: new Date().toISOString() });
      renderDrawers();
    });
    countModal?.querySelectorAll('.rd-denom').forEach(inp=> inp.addEventListener('input', updateCountSummary));
    document.getElementById('rd-count-close')?.addEventListener('click', closeCountModal);
    document.getElementById('rd-count-cancel')?.addEventListener('click', closeCountModal);
    document.getElementById('rd-count-save')?.addEventListener('click', ()=>{
      const d = getDrawer(activeDrawerId);
      if (!d) return closeCountModal();
      const counted = calculateCounted();
      d.currentAmount = counted;
      toast('Cash count saved','success');
      closeCountModal();
      renderDrawers();
    });
    renderDrawers();
    }

    // Add Room Modal controls
    const addRoomModal = document.getElementById('add-room-modal');
    function openAddRoomModal() {
        addRoomModal.classList.remove('hidden');
        addRoomModal.classList.add('flex');
    }
    function closeAddRoomModal() {
        addRoomModal.classList.add('hidden');
        addRoomModal.classList.remove('flex');
    }
    window.openAddRoomModal = openAddRoomModal;
    document.getElementById('add-room-close')?.addEventListener('click', closeAddRoomModal);
    document.getElementById('add-room-cancel')?.addEventListener('click', closeAddRoomModal);
    function toast(msg, type = 'info') {
        if (window.POS && typeof window.POS.showToast === 'function') return window.POS.showToast(msg, type);
        const el = document.createElement('div');
        el.className = `toast px-4 py-3 rounded-lg shadow text-white mb-2 ${type==='success'?'bg-green-600':type==='error'?'bg-red-600':type==='warning'?'bg-yellow-600':'bg-blue-600'}`;
        el.textContent = msg;
        document.body.appendChild(el);
        requestAnimationFrame(() => el.classList.add('show'));
        setTimeout(()=>{ el.classList.remove('show'); setTimeout(()=>el.remove(), 300); }, 2500);
    }

    document.getElementById('add-room-submit')?.addEventListener('click', async function() {
        const name = (document.getElementById('room-name').value || '').trim();
        const type = document.getElementById('room-type').value;
        const max_capacity = parseInt(document.getElementById('room-capacity').value || '0', 10) || null;
        const description = (document.getElementById('room-description').value || '').trim();
        if (!name) { toast('Room name is required', 'error'); return; }
        // LOCAL-FIRST: persist and render immediately
        const tempId = `local-${Date.now()}`;
        const localRoom = { id: tempId, name, type, max_capacity: max_capacity||0, room_id: null };
        try { localRooms.push(localRoom); saveRooms(localRooms); } catch(_) {}
        appendRoomCard(localRoom);
        addActivity('Room created', `${name} (${type})`);
        try { const k='rd-activity-log'; const prev=JSON.parse(localStorage.getItem(k)||'[]'); prev.push({ at:new Date().toISOString(), by: currentUserName(), title:'Room created', details:`${name} (${type})` }); localStorage.setItem(k, JSON.stringify(prev)); } catch(_) {}
        closeAddRoomModal();
        // Try Laravel in background
        try {
            const res = await fetch('/api/rooms-open', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-Store-ID': (window.SettingsClient && typeof SettingsClient.currentStoreId==='function') ? SettingsClient.currentStoreId() : 'default', 'X-Store-Name': (window.SettingsClient && typeof SettingsClient.currentStoreName==='function') ? SettingsClient.currentStoreName() : ''
                },
                body: JSON.stringify({ name, type, max_capacity, description, is_active: true })
            });
            let data = null;
            try { data = await res.json(); } catch(_) { data = null; }
            if (res.ok && data && data.room) {
                try {
                  const idx = localRooms.findIndex(r => r.id === tempId || (r.name||'').toLowerCase() === (name||'').toLowerCase());
                  const nr = { id: data.room.id, name: data.room.name, type: data.room.type, max_capacity: data.room.max_capacity ?? 0, room_id: data.room.room_id || null };
                  if (idx >= 0) localRooms[idx] = nr; else localRooms.push(nr);
                  saveRooms(localRooms);
                } catch(_) {}
                toast('Room created successfully', 'success');
            }
        } catch(_) {}
        // Also upsert to Supabase for persistence + realtime
        try {
          const base = String(window.__SUPABASE_URL||'').replace(/\/$/, '');
          const key = window.__SUPABASE_ANON_KEY||'';
          if (base && key) {
            const sid = (window.SettingsClient && typeof SettingsClient.currentStoreId==='function') ? SettingsClient.currentStoreId() : 'default';
            const sname = (window.SettingsClient && typeof SettingsClient.currentStoreName==='function') ? SettingsClient.currentStoreName() : '';
            const r = await fetch(`${base}/rest/v1/rooms?on_conflict=store_id,name`, {
              method:'POST',
              headers:{ 'Content-Type':'application/json', 'apikey': key, 'Authorization': `Bearer ${key}`, 'Prefer':'resolution=merge-duplicates,return=representation' },
              body: JSON.stringify([{ store_id: sid, store_name: sname||null, name, type, max_capacity: max_capacity||0, description, is_active: true, updated_at: new Date().toISOString() }])
            });
            if (r.ok) { const arr = await r.json(); const row = Array.isArray(arr)&&arr[0]?arr[0]:null; if (row && row.id){ const idx = localRooms.findIndex(x => x.id===tempId || (x.name||'').toLowerCase()===name.toLowerCase()); const nr = { id: row.id, name: row.name, type: row.type||'storage', max_capacity: row.max_capacity||0, room_id: row.room_id||null }; if (idx>=0){ localRooms[idx]=nr; saveRooms(localRooms);} } }
          }
        } catch(_) {}
    });

    // Add Drawer Modal controls
    let addDrawerRoomId = null;
    const addDrawerModal = document.getElementById('add-drawer-modal');

    // Cash Drawers state and helpers for this page
    const drawersEl = document.getElementById('rd-drawers');
    const countModal = document.getElementById('rd-count-modal');
    let activeDrawerId = null;
    const DRAWERS_KEY = 'pos_drawers';
    function loadDrawers(){ try { const raw = localStorage.getItem(DRAWERS_KEY); if (raw) { const arr = JSON.parse(raw); if (Array.isArray(arr)) return arr; } } catch(_) {} return []; }
    function saveDrawers(){ try { localStorage.setItem(DRAWERS_KEY, JSON.stringify(cashDrawers)); } catch(_) {} }
    let cashDrawers = loadDrawers();
    if (!Array.isArray(cashDrawers) || cashDrawers.length === 0) { cashDrawers = [ { id: 1, name: 'Till #1', status: 'open', assignedEmployee: 'Cody', startingAmount: 220.00, currentAmount: 220.00, openedAt: null } ]; saveDrawers(); }
    function renderDrawers(){
      if (!drawersEl) return;
      drawersEl.innerHTML = '';
      cashDrawers.forEach(d => {
        const wrapper = document.createElement('div');
        wrapper.className = 'border rounded-lg p-4 flex items-center justify-between';
        wrapper.innerHTML = `
          <div>
            <div class=\"font-medium\">${d.name} <span class=\"ml-2 text-xs ${d.status==='open'?'text-green-700':'text-gray-600'}\">${d.status==='open'?'Open':'Closed'}</span></div>
            <div class=\"text-sm text-gray-600\">Assigned To: <span class=\"font-medium\">${d.assignedEmployee||'-'}</span></div>
            <div class=\"text-sm text-gray-600\">Starting Amount: $${(d.startingAmount||0).toFixed(2)}</div>
            <div class=\"text-sm text-gray-600\">Current Amount: $${(d.currentAmount||0).toFixed(2)}</div>
          </div>
          <div class=\"flex gap-2\">
            <button data-act=\"open\" data-id=\"${d.id}\" class=\"px-3 py-1 text-sm rounded border ${d.status==='open'?'border-gray-300 text-gray-700 hover:bg-gray-50':'border-green-300 text-green-700 hover:bg-green-50'}\">Open</button>
            <button data-act=\"close\" data-id=\"${d.id}\" class=\"px-3 py-1 text-sm rounded border border-gray-300 text-gray-700 hover:bg-gray-50\">Close</button>
            <button data-act=\"count\" data-id=\"${d.id}\" class=\"px-3 py-1 text-sm rounded bg-blue-600 text-white hover:bg-blue-700\">Count</button>
            <button data-act=\"delete\" data-id=\"${d.id}\" class=\"px-3 py-1 text-sm rounded border border-red-300 text-red-700 hover:bg-red-50\">Delete Drawer</button>
            <button data-act=\"assign\" data-id=\"${d.id}\" class=\"px-3 py-1 text-sm rounded border border-purple-300 text-purple-700 hover:bg-purple-50\">Assign Employee</button>
          </div>`;
        drawersEl.appendChild(wrapper);
      });
    }
    function getDrawer(id){ return cashDrawers.find(d=>String(d.id)===String(id)); }
    function openCountModal(drawer){ activeDrawerId = drawer.id; countModal.classList.remove('hidden'); countModal.classList.add('flex'); countModal.querySelectorAll('.rd-denom').forEach(i=> i.value = ''); updateCountSummary(); }
    function closeCountModal(){ countModal.classList.add('hidden'); countModal.classList.remove('flex'); activeDrawerId = null; }
    function calcDebitTotalForDrawer(){ try { const uid = window.posAuth?.getUser?.()?.id || null; const key = uid ? `cannabisPOS-sales-${uid}` : null; const list = key ? JSON.parse(localStorage.getItem(key) || '[]') : []; const total = list.filter(s=>s?.method==='debit').reduce((sum,s)=> sum + (parseFloat(s.amount)||parseFloat(s.total)||0), 0); return +(+total).toFixed(2); } catch(_) { return 0; } }
    function calculateCounted(){ let total = 0; countModal.querySelectorAll('.rd-denom').forEach(input => { const denom = parseFloat(input.dataset.denom); const qty = parseInt(input.value||'0',10)||0; if (!isNaN(denom) && qty>0){ total += denom*qty; } }); return +(+total).toFixed(2); }
    function updateCountSummary(){ const d = getDrawer(activeDrawerId) || { currentAmount: 0 }; const counted = calculateCounted(); const debit = calcDebitTotalForDrawer(); const variance = +(counted - (d.currentAmount||0)).toFixed(2); document.getElementById('rd-counted').textContent = `$${counted.toFixed(2)}`; document.getElementById('rd-debit').textContent = `$${debit.toFixed(2)}`; const varEl = document.getElementById('rd-variance'); varEl.textContent = `${variance<0?'-':''}$${Math.abs(variance).toFixed(2)}`; varEl.className = `text-xl font-semibold ${variance<0?'text-red-600':variance>0?'text-green-600':'text-gray-900'}`; }
    drawersEl?.addEventListener('click', (e)=>{
      const btn = e.target.closest('button[data-act]');
      if (!btn) return;
      const id = btn.getAttribute('data-id');
      const act = btn.getAttribute('data-act');
      const d = getDrawer(id);
      if (!d) return;
      if (act==='open') { d.status='open'; d.openedAt = new Date().toISOString(); saveDrawers(); toast(`${d.name} opened`, 'success'); addActivity('Drawer opened', d.name); renderDrawers(); }
      if (act==='close') { d.status='closed'; saveDrawers(); toast(`${d.name} closed`, 'info'); addActivity('Drawer closed', d.name); renderDrawers(); }
      if (act==='count') { openCountModal(d); }
      if (act==='delete') { if (confirm('Delete this drawer?')) { const idx=cashDrawers.findIndex(x=>x.id===d.id); if (idx>=0) cashDrawers.splice(idx,1); saveDrawers(); toast('Drawer deleted','success'); addActivity('Drawer deleted', d.name); renderDrawers(); } }
      if (act==='assign') { const name = prompt('Assign to employee name:'); if (name){ d.assignedEmployee = name; saveDrawers(); toast(`Assigned to ${name}`, 'success'); addActivity('Drawer assigned', `${d.name} -> ${name}`); renderDrawers(); } }
    });
    document.getElementById('rd-add-drawer')?.addEventListener('click', ()=>{ const name = prompt('Drawer name'); const amt = parseFloat(prompt('Starting amount (e.g. 200)')||'0')||0; const nextId = Math.max(0,...cashDrawers.map(d=>Number(d.id)||0))+1; cashDrawers.push({ id: nextId, name: name||`Till #${nextId}`, status: 'open', assignedEmployee: '', startingAmount: amt, currentAmount: amt, openedAt: new Date().toISOString() }); saveDrawers(); addActivity('Drawer created', `${name||`Till #${nextId}`} starting $${amt.toFixed(2)}`); renderDrawers(); });
    countModal?.querySelectorAll('.rd-denom').forEach(inp=> inp.addEventListener('input', updateCountSummary));
    document.getElementById('rd-count-close')?.addEventListener('click', closeCountModal);
    document.getElementById('rd-count-cancel')?.addEventListener('click', closeCountModal);
    document.getElementById('rd-count-save')?.addEventListener('click', ()=>{ const d = getDrawer(activeDrawerId); if (!d) return closeCountModal(); const counted = calculateCounted(); const debit = calcDebitTotalForDrawer(); const expected = +(d.currentAmount||0); const variance = +(counted - expected).toFixed(2); d.currentAmount = counted; saveDrawers(); const countedBy = (document.getElementById('rd-counted-by')?.value||'').trim() || currentUserName(); const approvedBy = (document.getElementById('rd-approved-by')?.value||'').trim() || ''; const breakdown = Array.from(countModal.querySelectorAll('.rd-denom')).map(inp => ({ denom: parseFloat(inp.dataset.denom), qty: parseInt(inp.value||'0',10)||0 })); const payload = { type:'drawer_count', data: { drawerId: d.id, drawerName: d.name, countedTotal: counted, expectedCashTotal: expected, debitTotal: debit, variance, countedBy, approvedBy, breakdown, at: new Date().toISOString() } }; addActivity('Drawer counted', payload); toast('Cash count saved','success'); closeCountModal(); renderDrawers(); });
    renderDrawers();

    function openAddDrawerModal(roomId, roomName) {
        addDrawerRoomId = roomId || null;
        document.getElementById('drawer-name').value = '';
        const select = document.getElementById('drawer-room-select');
        if (roomId) { select.value = String(roomId); }
        addDrawerModal.classList.remove('hidden');
        addDrawerModal.classList.add('flex');
    }
    function openCreateDrawer(){ openAddDrawerModal(null, ''); }
    window.openCreateDrawer = openCreateDrawer;
    function closeAddDrawerModal() {
        addDrawerModal.classList.add('hidden');
        addDrawerModal.classList.remove('flex');
    }
    window.openAddDrawerModal = openAddDrawerModal;
    document.getElementById('add-drawer-close')?.addEventListener('click', closeAddDrawerModal);
    document.getElementById('add-drawer-cancel')?.addEventListener('click', closeAddDrawerModal);
    document.getElementById('add-drawer-submit')?.addEventListener('click', async function() {
        const name = (document.getElementById('drawer-name').value || '').trim();
        const roomId = addDrawerRoomId || document.getElementById('drawer-room-select').value;
        if (!roomId) { toast('Please select a room', 'error'); return; }
        if (!name) { toast('Drawer name is required', 'error'); return; }
        // Optimistically add drawer to the selected room card
        const roomCard = Array.from(document.querySelectorAll('.room-card')).find(card =>
          card.querySelector('button[onclick^="openAddDrawerModal("]')?.getAttribute('onclick')?.includes(`(${roomId},`)
        );
        if (roomCard) { addActivity('Drawer created', `${name} in room ${roomId}`);
            let grid = roomCard.querySelector('.grid.grid-cols-3');
            if (!grid) {
                const container = document.createElement('div');
                container.className = 'mb-4';
                container.innerHTML = `<h4 class="text-sm font-medium text-gray-900 mb-2">Drawers</h4><div class="grid grid-cols-3 gap-2"></div>`;
                roomCard.insertBefore(container, roomCard.querySelector('.flex.space-x-2'));
                grid = container.querySelector('.grid');
            }
            const drawerEl = document.createElement('div');
            drawerEl.className = 'p-2 border rounded text-center border-green-200 bg-green-50';
            drawerEl.innerHTML = `<div class="text-xs font-medium"></div><div class="text-xs text-gray-500">0 items</div>`;
            drawerEl.querySelector('div.text-xs.font-medium').textContent = name;
            grid.appendChild(drawerEl);
        }
        // Persist to Supabase
        try {
          const base = String(window.__SUPABASE_URL||'').replace(/\/$/, '');
          const key = window.__SUPABASE_ANON_KEY||'';
          if (base && key) {
            const sid = (window.SettingsClient && typeof SettingsClient.currentStoreId==='function') ? SettingsClient.currentStoreId() : 'default';
            const sname = (window.SettingsClient && typeof SettingsClient.currentStoreName==='function') ? SettingsClient.currentStoreName() : '';
            let supaRoomId = null;
            try {
              const sel = document.getElementById('drawer-room-select');
              const roomNameText = sel && sel.options ? (sel.options[sel.selectedIndex]?.textContent||'').trim() : '';
              if (roomNameText) {
                const rr = await fetch(`${base}/rest/v1/rooms?select=id,name&store_id=eq.${encodeURIComponent(sid)}&name=eq.${encodeURIComponent(roomNameText)}`, { headers:{ apikey:key, Authorization:`Bearer ${key}`, Accept:'application/json' } });
                if (rr.ok) { const arr = await rr.json(); const row = Array.isArray(arr)&&arr[0]?arr[0]:null; if (row && row.id) supaRoomId = row.id; }
              }
            } catch(_) {}
            await fetch('/api/drawers-open', { method:'POST', headers: { 'Content-Type':'application/json', Accept:'application/json', 'X-Store-ID': sid, 'X-Store-Name': sname||'' }, body: JSON.stringify({ room_id: supaRoomId, name, status:'open', starting_amount:0, current_amount:0, opened_at:new Date().toISOString() }) });
          }
        } catch(_) {}
        toast('Drawer created', 'success');
        closeAddDrawerModal();
    });

    window.viewRoomDetails = function(roomId) {
        addActivity('Room viewed', `Room ${roomId}`);
        alert(`View details for room ${roomId} - would show detailed room information`);
    };

    window.transferToRoom = function(roomId) {
        addActivity('Transfer initiated', `Target room ${roomId}`);
        alert(`Transfer to room ${roomId} - would open transfer modal`);
    };

    window.editRoom = function(roomId) {
        addActivity('Room edit opened', `Room ${roomId}`);
        alert(`Edit room ${roomId} - would open edit modal`);
    };

    window.generateReport = function() {
        addActivity('Report generated', 'Rooms & Drawers report');
        alert('Generate Report - would create comprehensive room compliance report');
    };

    window.bulkTransfer = function() {
        addActivity('Bulk transfer opened');
        alert('Bulk Transfer - would open bulk transfer modal');
    };

    window.complianceReport = function() {
        addActivity('Compliance report generated');
        alert('Compliance Report - would generate METRC compliance report');
    };

    window.environmentalControls = function() {
        addActivity('Environmental controls opened');
        alert('Environmental Controls - would open environmental monitoring dashboard');
    };
});
</script>
@endsection
