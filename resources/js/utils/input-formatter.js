/**
 * Utility to replace dot with comma in text inputs globally.
 */
document.addEventListener('keydown', function(e) {
    if (e.key === '.' && e.target.tagName === 'INPUT' && (e.target.type === 'text' || e.target.type === 'search')) {
        e.preventDefault();
        const input = e.target;
        const start = input.selectionStart;
        const end = input.selectionEnd;
        const oldValue = input.value;

        input.value = oldValue.substring(0, start) + ',' + oldValue.substring(end);
        input.setSelectionRange(start + 1, start + 1);

        // Dispatch input event for Livewire/Alpine synchronization
        input.dispatchEvent(new Event('input', {
            bubbles: true
        }));
    }
});
// El evento de "input" global se ha eliminado para no interferir con los puntos (.) 
// que el servidor envía como separadores de miles (ej: 100.000,00).
// Solo mantenemos el reemplazo cuando el usuario presiona la tecla punto físicamente.
