<div></div>
<script>
    document.addEventListener('livewire:init', () => {
        Livewire.on('confirmDeleteMovimiento', (movimientoId) => {
            Swal.fire({
                title: '¿Eliminar movimiento?',
                text: 'Esta acción revertirá capital (y si fue pago, debe revertir banco).',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Eliminar',
                cancelButtonText: 'Cancelar',
                confirmButtonColor: '#dc2626',
            }).then((result) => {
                if (result.isConfirmed) {
                    Livewire.dispatch('deleteMovimientoConfirmed', {
                        id: movimientoId
                    });
                }
            });
        });
    });
</script>
