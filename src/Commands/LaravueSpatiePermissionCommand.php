<?php

namespace Spatie\Permission\Commands;

use Illuminate\Console\Command;
use Spatie\Permission\Contracts\Permission as PermissionContract;

class LaravueSpatiePermissionCommand extends Command
{
    protected $signature = 'laravue:spatie-permission 
                {name : The name of the permission} 
                {--label= : The label of the permission} 
                {--guard= : The name of the guard}';

    protected $description = 'Create a permission';

    public function handle()
    {
        $permissionClass = app(PermissionContract::class);

        $name = $this->argument('name');
        $guardName = $this->option('guard') ?? 'api';

        $permission = $permissionClass::query()->updateOrCreate(['name' => $name], [
            'label' => $this->option('label'),
            'guard_name' =>  $guardName
        ]);

        $date = now();
        $this->info("{$date} - [ {$permission->name} ] >> Permission " . ($permission->wasRecentlyCreated ? 'created' : 'already exists'));
    }
}
