# Creating/modifying Database Migrations

- Do not add foreign key constraints to tables (no `foreignId`, `cascadeOnDelete`, etc.).
- Both names of tables and columns should be snake_case. Names of tables should be plural.
- Never actually implement the `down`-method in migrations. Instead the method should contain `throw new \Exception('Down-method disabled);`.

## Obtaining Database Schema
- Use `php artisan information-for-agent:list-database-tables` to get list of tables.
- Use `php artisan information-for-agent:show-schemas-for-database-tables {tableNamesSeparatedByComma}` to get schemas for one or several tables.
