@php
    $disabled = $disabled ?? false;
    $required = $required ?? false;
    $error = $error ?? false;
    
    $baseClasses = 'flex min-h-[80px] w-full rounded-md border px-3 py-2 text-sm ring-offset-background placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50';
    
    $borderClasses = $error 
        ? 'border-red-300 focus-visible:ring-red-500' 
        : 'border-gray-300 focus-visible:ring-blue-500';
    
    $classes = cn($baseClasses, $borderClasses);
    
    if (isset($class)) {
        $classes = cn($classes, $class);
    }
@endphp

<textarea 
    {{ $disabled ? 'disabled' : '' }}
    {{ $required ? 'required' : '' }}
    {{ $attributes->merge(['class' => $classes]) }}
>{{ $slot }}</textarea>
