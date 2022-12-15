<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Spatie\Permission\PermissionRegistrar;

class CreatePermissionTables extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $tableNames = config('permission.table_names');
        $columnNames = config('permission.column_names');
        $teams = config('permission.teams');

        if (empty($tableNames)) {
            throw new \Exception('Error: config/permission.php not loaded. Run [php artisan config:clear] and try again.');
        }
        if ($teams && empty($columnNames['team_foreign_key'] ?? null)) {
            throw new \Exception('Error: team_foreign_key on config/permission.php not loaded. Run [php artisan config:clear] and try again.');
        }

        Schema::create($tableNames['permissions'], function (Blueprint $table) {
            $table->Increments('id');
            $table->string('name')->default('')->nullable()->comment('权限名称(路由别名)');   // For MySQL 8.0 use string('name', 125);
            $table->string('guard_name')->default('')->nullable()->comment('守卫'); // For MySQL 8.0 use string('guard_name', 125);
            $table->string('display_name', 50)->default('')->nullable()->comment('显示名称');
            $table->integer('module_id')->default(0)->nullable()->comment('模块id');
            $table->timestamps();

            $table->unique(['name', 'guard_name']);
        });
        $table = DB::getTablePrefix().'permissions';
        DB::statement("ALTER TABLE `{$table}` comment'模块权限表'"); // 表注释

        Schema::create($tableNames['roles'], function (Blueprint $table) use ($teams, $columnNames) {
            $table->Increments('id');
            if ($teams || config('permission.testing')) { // permission.testing is a fix for sqlite testing
                $table->unsignedBigInteger($columnNames['team_foreign_key'])->nullable();
                $table->index($columnNames['team_foreign_key'], 'roles_team_foreign_key_index');
            }
            $table->string('name')->default('')->nullable();       // For MySQL 8.0 use string('name', 125);
            $table->string('guard_name')->default('')->nullable(); // For MySQL 8.0 use string('guard_name', 125);
            $table->string('desc',200)->default('')->nullable()->comment('备注');
            $table->timestamps();
            if ($teams || config('permission.testing')) {
                $table->unique([$columnNames['team_foreign_key'], 'name', 'guard_name']);
            } else {
                $table->unique(['name', 'guard_name']);
            }
        });
        $table = DB::getTablePrefix().'roles';
        DB::statement("ALTER TABLE `{$table}` comment'角色表'"); // 表注释

        Schema::create($tableNames['model_has_permissions'], function (Blueprint $table) use ($tableNames, $columnNames, $teams) {
            $table->unsignedInteger(PermissionRegistrar::$pivotPermission);

            $table->string('model_type')->default('');
            //$table->unsignedBigInteger($columnNames['model_morph_key']);
            $table->char($columnNames['model_morph_key'], 32)->default('')->comment('用戶id');
            $table->index([$columnNames['model_morph_key'], 'model_type'], 'model_has_permissions_model_id_model_type_index');

            //干掉级联
//            $table->foreign(PermissionRegistrar::$pivotPermission)
//                ->references('id')
//                ->on($tableNames['permissions'])
//                ->onDelete('cascade');
            if ($teams) {
                $table->unsignedBigInteger($columnNames['team_foreign_key']);
                $table->index($columnNames['team_foreign_key'], 'model_has_permissions_team_foreign_key_index');

                $table->primary([$columnNames['team_foreign_key'], PermissionRegistrar::$pivotPermission, $columnNames['model_morph_key'], 'model_type'],
                    'model_has_permissions_permission_model_type_primary');
            } else {
                $table->primary([PermissionRegistrar::$pivotPermission, $columnNames['model_morph_key'], 'model_type'],
                    'model_has_permissions_permission_model_type_primary');
            }

        });
        $table = DB::getTablePrefix().'model_has_permissions';
        DB::statement("ALTER TABLE `{$table}` comment'用户权限表'"); // 表注释

        Schema::create($tableNames['model_has_roles'], function (Blueprint $table) use ($tableNames, $columnNames, $teams) {
            $table->unsignedInteger(PermissionRegistrar::$pivotRole);

            $table->string('model_type')->default('');
            //$table->unsignedBigInteger($columnNames['model_morph_key']);
            $table->char($columnNames['model_morph_key'], 32)->default('')->comment('用戶id');
            $table->index([$columnNames['model_morph_key'], 'model_type'], 'model_has_roles_model_id_model_type_index');

            //干掉级联
//            $table->foreign(PermissionRegistrar::$pivotRole)
//                ->references('id')
//                ->on($tableNames['roles'])
//                ->onDelete('cascade');
            if ($teams) {
                $table->unsignedBigInteger($columnNames['team_foreign_key']);
                $table->index($columnNames['team_foreign_key'], 'model_has_roles_team_foreign_key_index');

                $table->primary([$columnNames['team_foreign_key'], PermissionRegistrar::$pivotRole, $columnNames['model_morph_key'], 'model_type'],
                    'model_has_roles_role_model_type_primary');
            } else {
                $table->primary([PermissionRegistrar::$pivotRole, $columnNames['model_morph_key'], 'model_type'],
                    'model_has_roles_role_model_type_primary');
            }
        });
        $table = DB::getTablePrefix().'model_has_roles';
        DB::statement("ALTER TABLE `{$table}` comment'用户角色表'"); // 表注释

        Schema::create($tableNames['role_has_permissions'], function (Blueprint $table) use ($tableNames) {
            $table->unsignedInteger(PermissionRegistrar::$pivotPermission);
            $table->unsignedInteger(PermissionRegistrar::$pivotRole);

            //干掉级联
//            $table->foreign(PermissionRegistrar::$pivotPermission)
//                ->references('id')
//                ->on($tableNames['permissions'])
//                ->onDelete('cascade');
//
//            $table->foreign(PermissionRegistrar::$pivotRole)
//                ->references('id')
//                ->on($tableNames['roles'])
//                ->onDelete('cascade');

            $table->primary([PermissionRegistrar::$pivotPermission, PermissionRegistrar::$pivotRole], 'role_has_permissions_permission_id_role_id_primary');
        });
        $table = DB::getTablePrefix().'role_has_permissions';
        DB::statement("ALTER TABLE `{$table}` comment'角色权限表'"); // 表注释

        app('cache')
            ->store(config('permission.cache.store') != 'default' ? config('permission.cache.store') : null)
            ->forget(config('permission.cache.key'));
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        $tableNames = config('permission.table_names');

        if (empty($tableNames)) {
            throw new \Exception('Error: config/permission.php not found and defaults could not be merged. Please publish the package configuration before proceeding, or drop the tables manually.');
        }

        Schema::drop($tableNames['role_has_permissions']);
        Schema::drop($tableNames['model_has_roles']);
        Schema::drop($tableNames['model_has_permissions']);
        Schema::drop($tableNames['roles']);
        Schema::drop($tableNames['permissions']);
    }
}
