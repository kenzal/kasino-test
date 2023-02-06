<?php

namespace Database\Traits;

use Illuminate\Database\Connection;
use Illuminate\Support\Facades\Schema;

trait AutoUpdateUpdatedAt
{
    protected function autoUpdateUpdatedAt(string $table, Connection $connection=null): void {
        $connection ??= Schema::getConnection();
        $connection->unprepared(/** @lang PostgreSQL */ <<<SQL
            CREATE OR REPLACE TRIGGER {$table}_update_updated_at_column BEFORE UPDATE
            ON {$table} FOR EACH ROW EXECUTE PROCEDURE
            update_updated_at_column();
        SQL );
    }
}
