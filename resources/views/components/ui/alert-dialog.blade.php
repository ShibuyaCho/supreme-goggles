@php
    $id = $id ?? 'alert-dialog-' . uniqid();
    $title = $title ?? '';
    $description = $description ?? '';
    $variant = $variant ?? 'default';
    
    $variants = [
        'default' => 'text-gray-900',
        'destructive' => 'text-red-900',
        'warning' => 'text-yellow-900',
        'success' => 'text-green-900',
    ];
    
    $iconVariants = [
        'default' => 'text-blue-600',
        'destructive' => 'text-red-600',
        'warning' => 'text-yellow-600',
        'success' => 'text-green-600',
    ];
    
    $icons = [
        'default' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />',
        'destructive' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z" />',
        'warning' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z" />',
        'success' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />',
    ];
@endphp

<!-- Alert Dialog Backdrop -->
<div 
    id="{{ $id }}" 
    class="alert-dialog fixed inset-0 z-50 hidden items-center justify-center p-4 bg-black bg-opacity-50"
    data-alert-dialog-id="{{ $id }}"
>
    <!-- Alert Dialog Content -->
    <div class="relative w-full max-w-md bg-white rounded-lg shadow-xl">
        <!-- Alert Dialog Body -->
        <div class="p-6">
            <div class="flex items-start space-x-4">
                <!-- Icon -->
                <div class="flex-shrink-0">
                    <svg class="h-6 w-6 {{ $iconVariants[$variant] ?? $iconVariants['default'] }}" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        {!! $icons[$variant] ?? $icons['default'] !!}
                    </svg>
                </div>
                
                <!-- Content -->
                <div class="flex-1">
                    @if($title)
                    <h3 class="text-lg font-medium {{ $variants[$variant] ?? $variants['default'] }} mb-2">
                        {{ $title }}
                    </h3>
                    @endif
                    
                    @if($description)
                    <p class="text-sm text-gray-600 mb-4">
                        {{ $description }}
                    </p>
                    @endif
                    
                    @if(isset($slot) && $slot->isNotEmpty())
                    <div class="text-sm text-gray-600 mb-4">
                        {{ $slot }}
                    </div>
                    @endif
                </div>
            </div>
        </div>
        
        <!-- Alert Dialog Actions -->
        <div class="flex items-center justify-end space-x-3 px-6 py-4 bg-gray-50 rounded-b-lg">
            @if(isset($actions))
                {{ $actions }}
            @else
                <button 
                    type="button" 
                    class="alert-dialog-cancel px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
                    data-alert-dialog-id="{{ $id }}"
                >
                    Cancel
                </button>
                <button 
                    type="button" 
                    class="alert-dialog-confirm px-4 py-2 text-sm font-medium text-white rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2
                        {{ $variant === 'destructive' ? 'bg-red-600 hover:bg-red-700 focus:ring-red-500' : 'bg-blue-600 hover:bg-blue-700 focus:ring-blue-500' }}"
                    data-alert-dialog-id="{{ $id }}"
                >
                    {{ $variant === 'destructive' ? 'Delete' : 'Confirm' }}
                </button>
            @endif
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const alertDialog = document.getElementById('{{ $id }}');
    if (!alertDialog) return;
    
    let resolvePromise = null;
    
    // Close dialog function
    function closeDialog(result = false) {
        alertDialog.classList.add('hidden');
        alertDialog.classList.remove('flex');
        document.body.style.overflow = '';
        
        if (resolvePromise) {
            resolvePromise(result);
            resolvePromise = null;
        }
    }
    
    // Cancel button listeners
    alertDialog.querySelectorAll('.alert-dialog-cancel').forEach(button => {
        button.addEventListener('click', () => closeDialog(false));
    });
    
    // Confirm button listeners
    alertDialog.querySelectorAll('.alert-dialog-confirm').forEach(button => {
        button.addEventListener('click', () => closeDialog(true));
    });
    
    // Close on backdrop click
    alertDialog.addEventListener('click', function(e) {
        if (e.target === this) {
            closeDialog(false);
        }
    });
    
    // Close on Escape key
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape' && !alertDialog.classList.contains('hidden')) {
            closeDialog(false);
        }
    });
    
    // Global function to open this alert dialog
    window['openAlertDialog{{ ucfirst(str_replace('-', '', $id)) }}'] = function() {
        alertDialog.classList.remove('hidden');
        alertDialog.classList.add('flex');
        document.body.style.overflow = 'hidden';
        
        // Return a promise for async handling
        return new Promise((resolve) => {
            resolvePromise = resolve;
        });
    };
    
    // Global function to close this alert dialog
    window['closeAlertDialog{{ ucfirst(str_replace('-', '', $id)) }}'] = () => closeDialog(false);
});

// Global utility function for creating alert dialogs programmatically
window.showAlertDialog = function(options = {}) {
    const {
        title = 'Confirm Action',
        description = 'Are you sure you want to continue?',
        variant = 'default',
        confirmText = 'Confirm',
        cancelText = 'Cancel'
    } = options;
    
    return new Promise((resolve) => {
        // Create dynamic alert dialog
        const dialogId = 'dynamic-alert-' + Date.now();
        const dialog = document.createElement('div');
        dialog.id = dialogId;
        dialog.className = 'alert-dialog fixed inset-0 z-50 flex items-center justify-center p-4 bg-black bg-opacity-50';
        
        const iconColors = {
            default: 'text-blue-600',
            destructive: 'text-red-600',
            warning: 'text-yellow-600',
            success: 'text-green-600'
        };
        
        const buttonColors = {
            default: 'bg-blue-600 hover:bg-blue-700 focus:ring-blue-500',
            destructive: 'bg-red-600 hover:bg-red-700 focus:ring-red-500',
            warning: 'bg-yellow-600 hover:bg-yellow-700 focus:ring-yellow-500',
            success: 'bg-green-600 hover:bg-green-700 focus:ring-green-500'
        };
        
        dialog.innerHTML = `
            <div class="relative w-full max-w-md bg-white rounded-lg shadow-xl">
                <div class="p-6">
                    <div class="flex items-start space-x-4">
                        <div class="flex-shrink-0">
                            <svg class="h-6 w-6 ${iconColors[variant]}" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z" />
                            </svg>
                        </div>
                        <div class="flex-1">
                            <h3 class="text-lg font-medium text-gray-900 mb-2">${title}</h3>
                            <p class="text-sm text-gray-600">${description}</p>
                        </div>
                    </div>
                </div>
                <div class="flex items-center justify-end space-x-3 px-6 py-4 bg-gray-50 rounded-b-lg">
                    <button type="button" class="cancel-btn px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-50">
                        ${cancelText}
                    </button>
                    <button type="button" class="confirm-btn px-4 py-2 text-sm font-medium text-white rounded-md ${buttonColors[variant]}">
                        ${confirmText}
                    </button>
                </div>
            </div>
        `;
        
        document.body.appendChild(dialog);
        document.body.style.overflow = 'hidden';
        
        const closeDialog = (result) => {
            document.body.removeChild(dialog);
            document.body.style.overflow = '';
            resolve(result);
        };
        
        dialog.querySelector('.cancel-btn').addEventListener('click', () => closeDialog(false));
        dialog.querySelector('.confirm-btn').addEventListener('click', () => closeDialog(true));
        dialog.addEventListener('click', (e) => {
            if (e.target === dialog) closeDialog(false);
        });
    });
};
</script>
