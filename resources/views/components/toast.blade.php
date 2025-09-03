@if(toast()->hasToasts())
<div id="toast-container" class="fixed top-4 right-4 z-50 space-y-2">
    @foreach(toast()->getToasts() as $toast)
    <div 
        class="toast-item flex max-w-md rounded-lg shadow-lg overflow-hidden transform transition-all duration-300 ease-in-out"
        data-toast-id="{{ $toast['id'] }}"
        data-duration="{{ $toast['duration'] }}"
        style="animation: slideIn 0.3s ease-out;"
    >
        <!-- Toast Content -->
        <div class="flex-1 w-0 p-4 {{ $toast['type'] === 'success' ? 'bg-green-50' : 
            ($toast['type'] === 'error' ? 'bg-red-50' : 
             ($toast['type'] === 'warning' ? 'bg-yellow-50' : 'bg-blue-50')) }}">
            <div class="flex items-start">
                <!-- Icon -->
                <div class="flex-shrink-0">
                    @if($toast['type'] === 'success')
                    <svg class="h-6 w-6 text-green-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    @elseif($toast['type'] === 'error')
                    <svg class="h-6 w-6 text-red-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    @elseif($toast['type'] === 'warning')
                    <svg class="h-6 w-6 text-yellow-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z" />
                    </svg>
                    @else
                    <svg class="h-6 w-6 text-blue-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    @endif
                </div>
                
                <!-- Content -->
                <div class="ml-3 flex-1">
                    <p class="text-sm font-medium {{ $toast['type'] === 'success' ? 'text-green-800' : 
                        ($toast['type'] === 'error' ? 'text-red-800' : 
                         ($toast['type'] === 'warning' ? 'text-yellow-800' : 'text-blue-800')) }}">
                        {{ $toast['title'] }}
                    </p>
                    @if($toast['description'])
                    <p class="mt-1 text-sm {{ $toast['type'] === 'success' ? 'text-green-700' : 
                        ($toast['type'] === 'error' ? 'text-red-700' : 
                         ($toast['type'] === 'warning' ? 'text-yellow-700' : 'text-blue-700')) }}">
                        {{ $toast['description'] }}
                    </p>
                    @endif
                </div>
            </div>
        </div>
        
        <!-- Dismiss Button -->
        @if($toast['dismissible'])
        <div class="flex border-l {{ $toast['type'] === 'success' ? 'border-green-200' : 
            ($toast['type'] === 'error' ? 'border-red-200' : 
             ($toast['type'] === 'warning' ? 'border-yellow-200' : 'border-blue-200')) }}">
            <button 
                class="toast-dismiss flex items-center justify-center w-full border border-transparent rounded-none rounded-r-lg p-4 text-sm font-medium {{ $toast['type'] === 'success' ? 'text-green-500 hover:text-green-600 bg-green-50 hover:bg-green-100' : 
                    ($toast['type'] === 'error' ? 'text-red-500 hover:text-red-600 bg-red-50 hover:bg-red-100' : 
                     ($toast['type'] === 'warning' ? 'text-yellow-500 hover:text-yellow-600 bg-yellow-50 hover:bg-yellow-100' : 'text-blue-500 hover:text-blue-600 bg-blue-50 hover:bg-blue-100')) }} focus:outline-none focus:ring-2 focus:ring-offset-2 {{ $toast['type'] === 'success' ? 'focus:ring-green-500' : 
                    ($toast['type'] === 'error' ? 'focus:ring-red-500' : 
                     ($toast['type'] === 'warning' ? 'focus:ring-yellow-500' : 'focus:ring-blue-500')) }}"
                data-toast-id="{{ $toast['id'] }}"
            >
                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>
        @endif
    </div>
    @endforeach
</div>

<style>
@keyframes slideIn {
    from {
        transform: translateX(100%);
        opacity: 0;
    }
    to {
        transform: translateX(0);
        opacity: 1;
    }
}

@keyframes slideOut {
    from {
        transform: translateX(0);
        opacity: 1;
    }
    to {
        transform: translateX(100%);
        opacity: 0;
    }
}

.toast-exit {
    animation: slideOut 0.3s ease-in forwards;
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Auto-dismiss toasts
    document.querySelectorAll('.toast-item').forEach(function(toast) {
        const duration = parseInt(toast.dataset.duration) || 5000;
        const toastId = toast.dataset.toastId;
        
        if (duration > 0) {
            setTimeout(function() {
                dismissToast(toastId);
            }, duration);
        }
    });
    
    // Manual dismiss
    document.querySelectorAll('.toast-dismiss').forEach(function(button) {
        button.addEventListener('click', function() {
            const toastId = this.dataset.toastId;
            dismissToast(toastId);
        });
    });
    
    function dismissToast(toastId) {
        const toast = document.querySelector(`[data-toast-id="${toastId}"]`);
        if (toast) {
            toast.classList.add('toast-exit');
            setTimeout(function() {
                toast.remove();
                
                // Remove container if no toasts left
                const container = document.getElementById('toast-container');
                if (container && container.children.length === 0) {
                    container.remove();
                }
            }, 300);
        }
    }
});
</script>
@endif
