@include('errors.layout', [
    'code' => '403',
    'heading' => 'Acceso restringido',
    'subheading' => 'No tienes permisos para acceder a esta sección.',
    'helpText' => 'Solicita a un administrador que te asigne el rol o permiso correspondiente.',
    'detail' => trim($exception?->getMessage() ?? ''),
    'primaryUrl' => route('dashboard'),
    'primaryLabel' => 'Volver al Panel',
])
