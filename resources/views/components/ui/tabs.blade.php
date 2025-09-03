@php
    $tabs = $tabs ?? [];
    $defaultTab = $defaultTab ?? (count($tabs) > 0 ? array_keys($tabs)[0] : '');
    $id = $id ?? 'tabs-' . uniqid();
    
    $classes = cn('w-full', $class ?? '');
@endphp

<div {{ $attributes->merge(['class' => $classes]) }} data-tabs-container="{{ $id }}">
    <!-- Tab Navigation -->
    <div class="border-b border-gray-200">
        <nav class="-mb-px flex space-x-8">
            @foreach($tabs as $key => $tab)
                @php
                    $isActive = $key === $defaultTab;
                    $tabLabel = is_array($tab) ? $tab['label'] : $tab;
                @endphp
                <button 
                    class="tab-trigger py-2 px-1 border-b-2 font-medium text-sm transition-colors duration-200
                        {{ $isActive ? 'border-blue-500 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }}"
                    data-tab="{{ $key }}"
                    data-tabs-container="{{ $id }}"
                >
                    {{ $tabLabel }}
                </button>
            @endforeach
        </nav>
    </div>

    <!-- Tab Content -->
    <div class="tab-content mt-4">
        @foreach($tabs as $key => $tab)
            @php
                $isActive = $key === $defaultTab;
                $tabContent = is_array($tab) ? $tab['content'] : '';
            @endphp
            <div 
                class="tab-panel {{ $isActive ? '' : 'hidden' }}"
                data-tab="{{ $key }}"
                data-tabs-container="{{ $id }}"
            >
                @if(is_array($tab) && isset($tab['content']))
                    {!! $tab['content'] !!}
                @else
                    {{ $slot }}
                @endif
            </div>
        @endforeach
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Initialize tabs for container {{ $id }}
    const container = document.querySelector('[data-tabs-container="{{ $id }}"]');
    if (!container) return;
    
    const triggers = container.querySelectorAll('.tab-trigger');
    const panels = container.querySelectorAll('.tab-panel');
    
    triggers.forEach(trigger => {
        trigger.addEventListener('click', function() {
            const targetTab = this.getAttribute('data-tab');
            
            // Remove active state from all triggers
            triggers.forEach(t => {
                t.classList.remove('border-blue-500', 'text-blue-600');
                t.classList.add('border-transparent', 'text-gray-500');
            });
            
            // Add active state to clicked trigger
            this.classList.remove('border-transparent', 'text-gray-500');
            this.classList.add('border-blue-500', 'text-blue-600');
            
            // Hide all panels
            panels.forEach(p => {
                p.classList.add('hidden');
            });
            
            // Show target panel
            const targetPanel = container.querySelector(`.tab-panel[data-tab="${targetTab}"]`);
            if (targetPanel) {
                targetPanel.classList.remove('hidden');
            }
        });
    });
});
</script>
