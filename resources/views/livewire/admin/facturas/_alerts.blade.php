 {{-- ALERTAS (LIGHT/DARK) --}}
 @if (session('success'))
     <div class="p-3 rounded bg-green-100 text-green-800 dark:bg-green-500/15 dark:text-green-200">
         {{ session('success') }}
     </div>
 @endif
 @if (session('error'))
     <div class="p-3 rounded bg-red-100 text-red-800 dark:bg-red-500/15 dark:text-red-200">
         {{ session('error') }}
     </div>
 @endif
