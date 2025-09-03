@php
    $method = $method ?? 'POST';
    $action = $action ?? '';
    $enctype = $enctype ?? '';
    $classes = cn('space-y-6', $class ?? '');
@endphp

<form 
    method="{{ $method === 'GET' ? 'GET' : 'POST' }}"
    @if($action) action="{{ $action }}" @endif
    @if($enctype) enctype="{{ $enctype }}" @endif
    {{ $attributes->merge(['class' => $classes]) }}
    data-form-validation
>
    @if($method !== 'GET' && $method !== 'POST')
        @method($method)
    @endif
    
    @if($method !== 'GET')
        @csrf
    @endif
    
    <div class="form-content space-y-6">
        {{ $slot }}
    </div>
</form>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Form validation functionality
    const forms = document.querySelectorAll('[data-form-validation]');
    
    forms.forEach(form => {
        const submitBtn = form.querySelector('button[type="submit"], input[type="submit"]');
        
        form.addEventListener('submit', function(e) {
            // Show loading state on submit button
            if (submitBtn) {
                submitBtn.disabled = true;
                const originalText = submitBtn.textContent;
                submitBtn.innerHTML = '<div class="inline-flex items-center"><div class="animate-spin mr-2 h-4 w-4 border-2 border-white border-t-transparent rounded-full"></div>Processing...</div>';
                
                // Reset after 30 seconds to prevent permanent disabled state
                setTimeout(() => {
                    submitBtn.disabled = false;
                    submitBtn.textContent = originalText;
                }, 30000);
            }
        });
        
        // Real-time validation
        const inputs = form.querySelectorAll('input, select, textarea');
        inputs.forEach(input => {
            input.addEventListener('blur', function() {
                validateField(this);
            });
            
            input.addEventListener('input', function() {
                clearFieldErrors(this);
            });
        });
    });
    
    function validateField(field) {
        const value = field.value.trim();
        const isRequired = field.hasAttribute('required');
        const type = field.type;
        
        clearFieldErrors(field);
        
        if (isRequired && !value) {
            showFieldError(field, 'This field is required');
            return false;
        }
        
        if (type === 'email' && value && !isValidEmail(value)) {
            showFieldError(field, 'Please enter a valid email address');
            return false;
        }
        
        if (type === 'tel' && value && !isValidPhone(value)) {
            showFieldError(field, 'Please enter a valid phone number');
            return false;
        }
        
        if (field.hasAttribute('minlength')) {
            const minLength = parseInt(field.getAttribute('minlength'));
            if (value.length < minLength) {
                showFieldError(field, `Must be at least ${minLength} characters`);
                return false;
            }
        }
        
        return true;
    }
    
    function showFieldError(field, message) {
        const container = field.closest('.space-y-2, .space-y-1, .form-field') || field.parentElement;
        
        // Remove existing error
        const existingError = container.querySelector('.field-error');
        if (existingError) {
            existingError.remove();
        }
        
        // Add error styling to field
        field.classList.add('border-red-300', 'focus:border-red-500', 'focus:ring-red-500');
        field.classList.remove('border-gray-300', 'focus:border-blue-500', 'focus:ring-blue-500');
        
        // Add error message
        const errorElement = document.createElement('p');
        errorElement.className = 'field-error text-sm text-red-600 mt-1';
        errorElement.textContent = message;
        container.appendChild(errorElement);
    }
    
    function clearFieldErrors(field) {
        const container = field.closest('.space-y-2, .space-y-1, .form-field') || field.parentElement;
        
        // Remove error message
        const existingError = container.querySelector('.field-error');
        if (existingError) {
            existingError.remove();
        }
        
        // Reset field styling
        field.classList.remove('border-red-300', 'focus:border-red-500', 'focus:ring-red-500');
        field.classList.add('border-gray-300', 'focus:border-blue-500', 'focus:ring-blue-500');
    }
    
    function isValidEmail(email) {
        const regex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        return regex.test(email);
    }
    
    function isValidPhone(phone) {
        const regex = /^[\+]?[1-9][\d]{0,15}$/;
        const cleaned = phone.replace(/\D/g, '');
        return cleaned.length >= 10;
    }
});
</script>
