# Creating/modifying Data Migration

- Do not add foreign key constraints to tables (no `foreignId`, `cascadeOnDelete`, etc.).
- Names of tables should be plural.
- Both names of tables and columns should be snake_case.
- Never actually implement the `down`-method in migrations. Instead the method should contain `throw new \Exception('Down-method disabled);`.
    
