@php
    $items = $items ?? [];
    $allowMultiple = $allowMultiple ?? false;
    $id = $id ?? 'accordion-' . uniqid();
    
    $classes = cn('divide-y divide-gray-200', $class ?? '');
@endphp

<div {{ $attributes->merge(['class' => $classes]) }} data-accordion-id="{{ $id }}">
    @foreach($items as $index => $item)
        @php
            $itemId = $id . '-item-' . $index;
            $isOpen = isset($item['defaultOpen']) && $item['defaultOpen'];
        @endphp
        
        <div class="accordion-item" data-item-id="{{ $itemId }}">
            <!-- Accordion Trigger -->
            <button 
                type="button"
                class="accordion-trigger flex justify-between items-center w-full px-4 py-4 text-left text-sm font-medium text-gray-900 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
                data-accordion-id="{{ $id }}"
                data-item-id="{{ $itemId }}"
                aria-expanded="{{ $isOpen ? 'true' : 'false' }}"
            >
                <span>{{ $item['title'] }}</span>
                <svg 
                    class="accordion-icon h-5 w-5 text-gray-500 transform transition-transform duration-200 {{ $isOpen ? 'rotate-180' : '' }}" 
                    fill="none" 
                    viewBox="0 0 24 24" 
                    stroke="currentColor"
                >
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                </svg>
            </button>
            
            <!-- Accordion Content -->
            <div 
                class="accordion-content overflow-hidden transition-all duration-200 {{ $isOpen ? 'max-h-screen' : 'max-h-0' }}"
                data-item-id="{{ $itemId }}"
            >
                <div class="px-4 py-4 text-sm text-gray-700">
                    {!! $item['content'] !!}
                </div>
            </div>
        </div>
    @endforeach
    
    @if(count($items) === 0)
        {{ $slot }}
    @endif
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const accordion = document.querySelector('[data-accordion-id="{{ $id }}"]');
    if (!accordion) return;
    
    const allowMultiple = {{ $allowMultiple ? 'true' : 'false' }};
    
    accordion.querySelectorAll('.accordion-trigger').forEach(trigger => {
        trigger.addEventListener('click', function() {
            const itemId = this.getAttribute('data-item-id');
            const content = accordion.querySelector(`.accordion-content[data-item-id="${itemId}"]`);
            const icon = this.querySelector('.accordion-icon');
            const isExpanded = this.getAttribute('aria-expanded') === 'true';
            
            if (!allowMultiple) {
                // Close all other items
                accordion.querySelectorAll('.accordion-trigger').forEach(otherTrigger => {
                    if (otherTrigger !== this) {
                        const otherItemId = otherTrigger.getAttribute('data-item-id');
                        const otherContent = accordion.querySelector(`.accordion-content[data-item-id="${otherItemId}"]`);
                        const otherIcon = otherTrigger.querySelector('.accordion-icon');
                        
                        otherTrigger.setAttribute('aria-expanded', 'false');
                        otherContent.style.maxHeight = '0';
                        otherIcon.classList.remove('rotate-180');
                    }
                });
            }
            
            // Toggle current item
            if (isExpanded) {
                this.setAttribute('aria-expanded', 'false');
                content.style.maxHeight = '0';
                icon.classList.remove('rotate-180');
            } else {
                this.setAttribute('aria-expanded', 'true');
                content.style.maxHeight = content.scrollHeight + 'px';
                icon.classList.add('rotate-180');
            }
        });
    });
});
</script>
