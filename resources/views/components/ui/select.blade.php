@php
    $disabled = $disabled ?? false;
    $required = $required ?? false;
    $error = $error ?? false;
    
    $baseClasses = 'flex h-10 w-full items-center justify-between rounded-md border px-3 py-2 text-sm ring-offset-background placeholder:text-muted-foreground focus:outline-none focus:ring-2 focus:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50';
    
    $borderClasses = $error 
        ? 'border-red-300 focus:ring-red-500' 
        : 'border-gray-300 focus:ring-blue-500';
    
    $classes = cn($baseClasses, $borderClasses);
    
    if (isset($class)) {
        $classes = cn($classes, $class);
    }
@endphp

<select 
    {{ $disabled ? 'disabled' : '' }}
    {{ $required ? 'required' : '' }}
    {{ $attributes->merge(['class' => $classes]) }}
>
    {{ $slot }}
</select>
