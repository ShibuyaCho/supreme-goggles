@php
    $id = $id ?? 'dialog-' . uniqid();
    $title = $title ?? '';
    $size = $size ?? 'md';
    
    $sizeClasses = [
        'sm' => 'max-w-sm',
        'md' => 'max-w-md', 
        'lg' => 'max-w-lg',
        'xl' => 'max-w-xl',
        '2xl' => 'max-w-2xl',
        '3xl' => 'max-w-3xl',
        '4xl' => 'max-w-4xl',
        'full' => 'max-w-full mx-4',
    ];
    
    $modalClasses = cn('relative w-full', $sizeClasses[$size] ?? $sizeClasses['md']);
@endphp

<!-- Dialog Backdrop -->
<div 
    id="{{ $id }}" 
    class="dialog-backdrop fixed inset-0 z-50 hidden items-center justify-center p-4 bg-black bg-opacity-50"
    data-dialog-id="{{ $id }}"
>
    <!-- Dialog Content -->
    <div class="{{ $modalClasses }} bg-white rounded-lg shadow-xl max-h-[90vh] overflow-hidden">
        <!-- Dialog Header -->
        @if($title)
        <div class="flex items-center justify-between p-6 border-b border-gray-200">
            <h2 class="text-lg font-semibold text-gray-900">{{ $title }}</h2>
            <button 
                type="button" 
                class="dialog-close text-gray-400 hover:text-gray-600 p-1 rounded-md hover:bg-gray-100"
                data-dialog-id="{{ $id }}"
            >
                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>
        @endif
        
        <!-- Dialog Body -->
        <div class="p-6 overflow-y-auto max-h-[calc(90vh-8rem)]">
            {{ $slot }}
        </div>
        
        <!-- Dialog Footer (if provided) -->
        @if(isset($footer))
        <div class="flex items-center justify-end space-x-3 p-6 border-t border-gray-200 bg-gray-50">
            {{ $footer }}
        </div>
        @endif
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const dialog = document.getElementById('{{ $id }}');
    if (!dialog) return;
    
    // Close dialog function
    function closeDialog() {
        dialog.classList.add('hidden');
        dialog.classList.remove('flex');
        document.body.style.overflow = '';
    }
    
    // Close button listeners
    dialog.querySelectorAll('.dialog-close').forEach(button => {
        button.addEventListener('click', closeDialog);
    });
    
    // Close on backdrop click
    dialog.addEventListener('click', function(e) {
        if (e.target === this) {
            closeDialog();
        }
    });
    
    // Close on Escape key
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape' && !dialog.classList.contains('hidden')) {
            closeDialog();
        }
    });
    
    // Global function to open this dialog
    window['openDialog{{ ucfirst(str_replace('-', '', $id)) }}'] = function() {
        dialog.classList.remove('hidden');
        dialog.classList.add('flex');
        document.body.style.overflow = 'hidden';
    };
    
    // Global function to close this dialog
    window['closeDialog{{ ucfirst(str_replace('-', '', $id)) }}'] = closeDialog;
});
</script>
