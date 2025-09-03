@php
    $id = $id ?? 'dropdown-' . uniqid();
    $trigger = $trigger ?? 'Menu';
    $items = $items ?? [];
    $align = $align ?? 'left';
    
    $alignClasses = $align === 'right' ? 'right-0' : 'left-0';
@endphp

<div class="relative inline-block text-left" data-dropdown-id="{{ $id }}">
    <!-- Trigger Button -->
    <button 
        type="button" 
        class="dropdown-trigger inline-flex justify-center items-center w-full rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-sm font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
        data-dropdown-id="{{ $id }}"
        aria-expanded="false"
        aria-haspopup="true"
    >
        {{ $trigger }}
        <svg class="ml-2 -mr-1 h-5 w-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
            <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
        </svg>
    </button>

    <!-- Dropdown Menu -->
    <div 
        class="dropdown-menu hidden absolute {{ $alignClasses }} z-10 mt-2 w-56 rounded-md shadow-lg bg-white ring-1 ring-black ring-opacity-5 focus:outline-none"
        data-dropdown-id="{{ $id }}"
        role="menu"
        aria-orientation="vertical"
    >
        <div class="py-1" role="none">
            @if(count($items) > 0)
                @foreach($items as $item)
                    @if(is_array($item))
                        @if(isset($item['divider']) && $item['divider'])
                            <div class="border-t border-gray-100 my-1"></div>
                        @else
                            <a 
                                href="{{ $item['href'] ?? '#' }}" 
                                class="dropdown-item block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 hover:text-gray-900 {{ isset($item['disabled']) && $item['disabled'] ? 'opacity-50 cursor-not-allowed' : '' }}"
                                role="menuitem"
                                @if(isset($item['onclick'])) onclick="{{ $item['onclick'] }}" @endif
                            >
                                @if(isset($item['icon']))
                                    <svg class="inline-block mr-2 h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        {!! $item['icon'] !!}
                                    </svg>
                                @endif
                                {{ $item['label'] }}
                            </a>
                        @endif
                    @else
                        <span class="block px-4 py-2 text-sm text-gray-700">{{ $item }}</span>
                    @endif
                @endforeach
            @else
                {{ $slot }}
            @endif
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const dropdown = document.querySelector('[data-dropdown-id="{{ $id }}"]');
    if (!dropdown) return;
    
    const trigger = dropdown.querySelector('.dropdown-trigger');
    const menu = dropdown.querySelector('.dropdown-menu');
    
    function toggleDropdown() {
        const isHidden = menu.classList.contains('hidden');
        
        // Close all other dropdowns
        document.querySelectorAll('.dropdown-menu').forEach(otherMenu => {
            if (otherMenu !== menu) {
                otherMenu.classList.add('hidden');
            }
        });
        
        if (isHidden) {
            menu.classList.remove('hidden');
            trigger.setAttribute('aria-expanded', 'true');
        } else {
            menu.classList.add('hidden');
            trigger.setAttribute('aria-expanded', 'false');
        }
    }
    
    function closeDropdown() {
        menu.classList.add('hidden');
        trigger.setAttribute('aria-expanded', 'false');
    }
    
    // Toggle on trigger click
    trigger.addEventListener('click', function(e) {
        e.preventDefault();
        e.stopPropagation();
        toggleDropdown();
    });
    
    // Close on menu item click
    menu.querySelectorAll('.dropdown-item').forEach(item => {
        item.addEventListener('click', function() {
            closeDropdown();
        });
    });
    
    // Close on outside click
    document.addEventListener('click', function(e) {
        if (!dropdown.contains(e.target)) {
            closeDropdown();
        }
    });
    
    // Close on Escape key
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            closeDropdown();
        }
    });
});
</script>
