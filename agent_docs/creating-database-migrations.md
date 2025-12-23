# Creating/modifying Database Migrations

- Do not add foreign key constraints to tables (no `foreignId`, `cascadeOnDelete`, etc.).
- Both names of tables and columns should be snake_case. Names of tables should be plural.
- Never actually implement the `down`-method in migrations. Instead the method should contain `throw new \Exception('Down-method disabled);`.
- Never make changes to migrations that have already been run.

## Obtaining Database Schema

- Use `php artisan information-for-agent:list-database-tables` to get list of tables.
- Use `php artisan information-for-agent:show-schemas-for-database-tables {tableNamesSeparatedByComma}` to get schemas for one or several tables.
- Use `php artisan information-for-agent:show-database-migrations-that-have-been-run` to get list of migrations have already been run and therefore should be considered locked.

## Codex CLI user interaction
- If instructions from the user do not include all indices that likely will be required, suggest indices to add and ask the user to confirm (number the suggestions so the user can easily choose which should be added).
- Always ask the Codex CLI user, before running `php artisan migrate`.
