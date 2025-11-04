@php
    /** @var array<int,array{id:string,message:string,type:string,timeout:int}> $toasts */
    $toasts = session('toasts', []);
@endphp

<div
    x-data="{
        items: @js($toasts),
        remove(id){ this.items = this.items.filter(i => i.id !== id) },
        typeClass(t){
          return {
            'success':'bg-green-600 text-white',
            'error':'bg-red-600 text-white',
            'warning':'bg-yellow-500 text-black',
            'info':'bg-slate-800 text-white',
          }[t.type || 'info'];
        }
    }"
    class="pointer-events-none fixed inset-0 z-50 flex flex-col items-end gap-2 p-4 sm:p-6"
    aria-live="polite" aria-atomic="true"
>
    <template x-for="t in items" :key="t.id">
        <div
            class="pointer-events-auto w-full max-w-sm overflow-hidden rounded-lg shadow-lg"
            :class="typeClass(t)"
            x-init="setTimeout(() => remove(t.id), t.timeout || 3000)"
        >
            <div class="p-4 text-sm" x-text="t.message"></div>
        </div>
    </template>
</div>
