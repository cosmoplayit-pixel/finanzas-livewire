# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Commands

```bash
# Start dev server (PHP + queue + Vite concurrently)
composer run dev

# Build frontend assets
npm run build

# Run all tests
composer run test
# or
php artisan test

# Run a single test file
php artisan test tests/Feature/ExampleTest.php

# Lint PHP with Pint
./vendor/bin/pint

# Run migrations
php artisan migrate
```

## Architecture Overview

This is a **Laravel 12 + Livewire 3** financial management SaaS with multi-tenant (multi-empresa) data isolation. The UI is built with **Livewire Flux** components, **Tailwind CSS 4**, and **Alpine.js**. Confirmations use **SweetAlert2**.

### Multi-tenant scoping

Every data model has an `empresa_id`. Users also have `empresa_id` via `User::empresa_id`. All queries for non-admin users **must** scope to `auth()->user()->empresa_id`. The role `Administrador` is the global admin with no empresa scope — check `$user->hasRole('Administrador')` before applying empresa filters.

### Auth & Permissions

- Auth: Laravel Fortify
- Permissions: Spatie Laravel Permission (roles + `*.view`, `*.create`, etc. permissions)
- Custom middleware `active` (`CheckUserActive`) logs out users whose account or role is deactivated. Role check is **file-cached for 5 minutes** — when deactivating a role, call `Cache::store('file')->forget("user_{$id}_role_check")`.
- Routes in `routes/web.php` use `->middleware(['permission:module.view'])` guards.

### Livewire Component Structure

Simple modules (one page, no modals): single class file `app/Livewire/Admin/ModuleName.php` + view `resources/views/livewire/admin/module-name.blade.php`.

Complex modules use a subdirectory pattern:
```
app/Livewire/Admin/ModuleName/
  Index.php          # main component
  Traits/            # trait files split from Index (e.g. WithCreateEdit, WithDetail)
  Modals/            # separate Livewire sub-components for heavy modals
resources/views/livewire/admin/module-name/
  index.blade.php
  ...
```

Traits are in `app/Livewire/Admin/ModuleName/Traits/` and mixed into `Index` via `use`.

### Services, Queries, Exports

- `app/Services/` — business logic classes (e.g. `FacturaService`, `TransferenciaBancariaService`). Injected into Livewire components or called directly.
- `app/Queries/` — reusable query builder objects (e.g. `FacturaIndexQuery`, `ProyectoResumenQuery`).
- `app/Exports/` — Maatwebsite Excel exports (Excel download from Livewire `export()` methods).
- `app/Models/BajaHerramienta.php` — uses `SoftDeletes` to preserve history when hardware is retired.

### Sidebar & Navigation Counts

`ViewServiceProvider` composes `$navCounts` (badge counts per nav item) into every render of the sidebar layout. Counts are scoped per `empresa_id` for non-admin users. Add new counts there when adding new modules.

### Event / SweetAlert Pattern

Confirmation dialogs follow a two-step pattern:
1. Livewire dispatches a named JS event (e.g. `swal:toggle-active-banco`) with the item data.
2. The global listener in `sidebar.blade.php` shows a SweetAlert2 dialog.
3. On confirm, the listener calls `Livewire.dispatch('doActionName', { id })`.
4. The Livewire component listens for `doActionName` in its `$listeners` array.

Toast notifications: `$this->dispatch('toast', type: 'success'|'error', message: '...')`.
Modal alerts: `$this->dispatch('swal:modal', ['type' => 'error', 'title' => '...', 'text' => '...'])`.

### Financial Number Formatting

Use the `WithFinancialFormatting` trait (`app/Livewire/Traits/WithFinancialFormatting.php`) in Livewire components that handle monetary input. It provides:
- `parseFormattedFloat(?string $value): float` — parses both European (`1.234,56`) and US (`1,234.56`) formats.
- `formatFloatValue(?float $value, int $decimals): string` — renders as European format (`1.234,56`) for display fields.

### Alpine.js Modal Stack

A global Alpine store `modalStack` (initialized in `sidebar.blade.php`) manages stacked modals. Use `$store.modalStack.open(id, closeFn)` / `close(id)` when building nested modals so that ESC / backdrop clicks close only the top-most modal.
