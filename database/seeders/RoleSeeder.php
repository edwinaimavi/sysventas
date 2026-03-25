<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        //Roles creados por defecto
        $role1 = Role::firstOrCreate(['name' => 'Administrador']);
        /*    $role2 = Role::Create(['name' => 'vendedor']); */
        $role2 = Role::firstOrCreate(['name' => 'Cobrador']);


        Permission::firstOrCreate(['name' => 'admin.users.index', 'description' => 'Ver los Usuarios'])->syncRoles([$role1]);
        Permission::firstOrCreate(['name' => 'admin.users.store', 'description' => 'Crear Usuarios'])->syncRoles([$role1]);
        Permission::firstOrCreate(['name' => 'admin.users.update', 'description' => 'Actualizar Usuarios'])->syncRoles([$role1]);
        Permission::firstOrCreate(['name' => 'admin.users.destroy', 'description' => 'Eliminar Usuarios'])->syncRoles([$role1]);

        Permission::firstOrCreate(['name' => 'admin.roles.index', 'description' => 'Ver los Roles'])->syncRoles([$role1]);
        Permission::firstOrCreate(['name' => 'admin.roles.store', 'description' => 'Crear Roles'])->syncRoles([$role1]);
        Permission::firstOrCreate(['name' => 'admin.roles.update', 'description' => 'Actualizar Roles'])->syncRoles([$role1]);
        Permission::firstOrCreate(['name' => 'admin.roles.destroy', 'description' => 'Eliminar Roles'])->syncRoles([$role1]);

        Permission::firstOrCreate(['name' => 'admin.clientes.index', 'description' => 'Ver los Clientes'])->syncRoles([$role1]);
        Permission::firstOrCreate(['name' => 'admin.clientes.store', 'description' => 'Crear Clientes'])->syncRoles([$role1]);
        Permission::firstOrCreate(['name' => 'admin.clientes.update', 'description' => 'Actualizar Clientes'])->syncRoles([$role1]);
        Permission::firstOrCreate(['name' => 'admin.clientes.destroy', 'description' => 'Eliminar Clientes'])->syncRoles([$role1]);

        Permission::firstOrCreate(['name' => 'admin.guarantors.index', 'description' => 'Ver los garante'])->syncRoles([$role1]);
        Permission::firstOrCreate(['name' => 'admin.guarantors.store', 'description' => 'Crear garante'])->syncRoles([$role1]);
        Permission::firstOrCreate(['name' => 'admin.guarantors.update', 'description' => 'Actualizar garante'])->syncRoles([$role1]);
        Permission::firstOrCreate(['name' => 'admin.guarantors.destroy', 'description' => 'Eliminar garante'])->syncRoles([$role1]);

        Permission::firstOrCreate(['name' => 'admin.loans.index', 'description' => 'Ver los prestamo'])->syncRoles([$role1]);
        Permission::firstOrCreate(['name' => 'admin.loans.store', 'description' => 'Crear prestamo'])->syncRoles([$role1]);
        Permission::firstOrCreate(['name' => 'admin.loans.status', 'description' => 'Estado de Prestamo'])->syncRoles([$role1]);
        Permission::firstOrCreate(['name' => 'admin.loans.increments', 'description' => 'Incrementar Prestamo'])->syncRoles([$role1]);
        Permission::firstOrCreate(['name' => 'admin.loans.disbursements', 'description' => 'Desembolsar Prestamo'])->syncRoles([$role1]);
        Permission::firstOrCreate(['name' => 'admin.loans.refinance', 'description' => 'Refinanciar Prestamo'])->syncRoles([$role1]);
        Permission::firstOrCreate(['name' => 'admin.loans.update', 'description' => 'Actualizar prestamo'])->syncRoles([$role1]);
        Permission::firstOrCreate(['name' => 'admin.loans.destroy', 'description' => 'Eliminar prestamo'])->syncRoles([$role1]);

        Permission::firstOrCreate(['name' => 'admin.payments.index', 'description' => 'Ver los pago'])->syncRoles([$role1]);
        Permission::firstOrCreate(['name' => 'admin.payments.store', 'description' => 'Crear pago'])->syncRoles([$role1]);
        Permission::firstOrCreate(['name' => 'admin.payments.update', 'description' => 'Actualizar pago'])->syncRoles([$role1]);
        Permission::firstOrCreate(['name' => 'admin.payments.destroy', 'description' => 'Eliminar pago'])->syncRoles([$role1]);

        Permission::firstOrCreate(['name' => 'admin.reports.index', 'description' => 'Ver los reporte'])->syncRoles([$role1]);
        Permission::firstOrCreate(['name' => 'admin.reports.store', 'description' => 'Crear reporte'])->syncRoles([$role1]);
        Permission::firstOrCreate(['name' => 'admin.reports.update', 'description' => 'Actualizar reporte'])->syncRoles([$role1]);
        Permission::firstOrCreate(['name' => 'admin.reports.destroy', 'description' => 'Eliminar reporte'])->syncRoles([$role1]);

        Permission::firstOrCreate(['name' => 'admin.reminders.index', 'description' => 'Ver los recordatorio'])->syncRoles([$role1]);
        Permission::firstOrCreate(['name' => 'admin.reminders.store', 'description' => 'Crear recordatorio'])->syncRoles([$role1]);
        Permission::firstOrCreate(['name' => 'admin.reminders.update', 'description' => 'Actualizar recordatorio'])->syncRoles([$role1]);
        Permission::firstOrCreate(['name' => 'admin.reminders.destroy', 'description' => 'Eliminar recordatorio'])->syncRoles([$role1]);

        Permission::firstOrCreate(['name' => 'admin.branches.index', 'description' => 'Ver los sucursal'])->syncRoles([$role1]);
        Permission::firstOrCreate(['name' => 'admin.branches.store', 'description' => 'Crear sucursal'])->syncRoles([$role1]);
        Permission::firstOrCreate(['name' => 'admin.branches.update', 'description' => 'Actualizar sucursal'])->syncRoles([$role1]);
        Permission::firstOrCreate(['name' => 'admin.branches.destroy', 'description' => 'Eliminar sucursal'])->syncRoles([$role1]);

        Permission::firstOrCreate(['name' => 'admin.cashbox.index', 'description' => 'Ver Caja'])->syncRoles([$role1]);
        Permission::firstOrCreate(['name' => 'admin.cashbox.store', 'description' => 'Crear Caja'])->syncRoles([$role1]);
        Permission::firstOrCreate(['name' => 'admin.cashbox.replenishment', 'description' => 'Reposición Caja'])->syncRoles([$role1]);
        Permission::firstOrCreate(['name' => 'admin.cashbox.expense', 'description' => 'Retirar Caja'])->syncRoles([$role1]);
        Permission::firstOrCreate(['name' => 'admin.cashbox.close', 'description' => 'Cerrar Caja'])->syncRoles([$role1]);

    }
}
