<?php

use App\Livewire\Admin\Entidades;
use App\Models\Empresa;
use App\Models\Entidad;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

uses(Tests\TestCase::class, RefreshDatabase::class);

beforeEach(function () {
    // Generate roles and permissions required for the test if they don't exist
    $permissions = ['entidades.view', 'entidades.create', 'entidades.update', 'entidades.toggle'];
    foreach ($permissions as $p) {
        Permission::firstOrCreate(['name' => $p, 'guard_name' => 'web']);
    }

    $managerRole = Role::firstOrCreate(['name' => 'Empresa_Manager', 'guard_name' => 'web']);
    $managerRole->syncPermissions($permissions);

    Role::firstOrCreate(['name' => 'Administrador', 'guard_name' => 'web']); // No permissions to Entidades
});

it('blocks Administrador from accessing the Entidades component', function () {
    $admin = User::factory()->create(['is_root' => true, 'empresa_id' => null, 'active' => true]);
    $admin->assignRole('Administrador');

    $this->actingAs($admin)
        ->get('/entidades') // Assuming this is the route, or directly testing the component
        ->assertForbidden(); // If the route has middleware
});

it('allows Empresa_Manager to access their Entidades and see responsive UI', function () {
    $empresa = Empresa::create(['nombre' => 'Mi Empresa', 'nit' => '123', 'active' => true]);
    $manager = User::factory()->create(['empresa_id' => $empresa->id]);
    $manager->assignRole('Empresa_Manager');

    $this->actingAs($manager);

    Livewire::test(Entidades::class)
        ->assertStatus(200)
        ->assertSee('md:hidden') // Mobile format
        ->assertSee('hidden md:block'); // Desktop format
});

it('isolates Entidades so a Manager only sees their own company entities', function () {
    $empresa1 = Empresa::create(['nombre' => 'E1', 'nit' => '1', 'active' => true]);
    $empresa2 = Empresa::create(['nombre' => 'E2', 'nit' => '2', 'active' => true]);

    $manager1 = User::factory()->create(['empresa_id' => $empresa1->id]);
    $manager1->assignRole('Empresa_Manager');

    $ent1 = Entidad::create(['empresa_id' => $empresa1->id, 'nombre' => 'Entidad 1', 'active' => true]);
    $ent2 = Entidad::create(['empresa_id' => $empresa2->id, 'nombre' => 'Entidad 2', 'active' => true]);

    $this->actingAs($manager1);

    Livewire::test(Entidades::class)
        ->assertSee('Entidad 1')
        ->assertDontSee('Entidad 2');
});

it('prevents Manager from seeing or editing another company entity', function () {
    $empresa1 = Empresa::create(['nombre' => 'E1', 'nit' => '1', 'active' => true]);
    $empresa2 = Empresa::create(['nombre' => 'E2', 'nit' => '2', 'active' => true]);

    $manager1 = User::factory()->create(['empresa_id' => $empresa1->id]);
    $manager1->assignRole('Empresa_Manager');

    $ent2 = Entidad::create(['empresa_id' => $empresa2->id, 'nombre' => 'Entidad 2', 'active' => true]);

    $this->actingAs($manager1);

    Livewire::test(Entidades::class)
        ->call('openEdit', $ent2->id)
        ->assertForbidden();
});

it('can open create modal successfully', function () {
    $empresa = Empresa::create(['nombre' => 'Mi Empresa', 'nit' => '123', 'active' => true]);
    $manager = User::factory()->create(['empresa_id' => $empresa->id]);
    $manager->assignRole('Empresa_Manager');

    $this->actingAs($manager);

    Livewire::test(Entidades::class)
        ->assertSet('openModal', false)
        ->call('openCreate')
        ->assertSet('openModal', true)
        ->assertSet('entidadId', null);
});

it('can create a new Entidad for the managers company', function () {
    $empresa = Empresa::create(['nombre' => 'Mi Empresa', 'nit' => '123', 'active' => true]);
    $manager = User::factory()->create(['empresa_id' => $empresa->id]);
    $manager->assignRole('Empresa_Manager');

    $this->actingAs($manager);

    Livewire::test(Entidades::class)
        ->call('openCreate')
        ->set('nombre', 'Nueva Entidad')
        ->set('sigla', 'NE')
        ->call('save')
        ->assertHasNoErrors()
        ->assertSet('openModal', false); // Closes on success

    $this->assertDatabaseHas('entidades', [
        'empresa_id' => $empresa->id,
        'nombre' => 'Nueva Entidad',
        'sigla' => 'NE'
    ]);
});

it('shows validation errors if fields are missing or duplicated for same company', function () {
    $empresa = Empresa::create(['nombre' => 'Mi Empresa', 'nit' => '123', 'active' => true]);
    $manager = User::factory()->create(['empresa_id' => $empresa->id]);
    $manager->assignRole('Empresa_Manager');

    Entidad::create(['empresa_id' => $empresa->id, 'nombre' => 'Duplicada', 'active' => true]);

    $this->actingAs($manager);

    Livewire::test(Entidades::class)
        ->call('save')
        ->assertHasErrors(['nombre' => 'required']);

    Livewire::test(Entidades::class)
        ->set('nombre', 'Duplicada')
        ->call('save')
        ->assertHasErrors(['nombre' => 'unique']); // Formatted as unique mostly
});
