## Approach

* **Explicit Mapping:** Manually map data in both directions (Database to Domain and Domain to Persistence). Do not use `Spatie\LaravelData\Data` for mapping within a repository.
* **No DocBlocks for Mapping:** The `mapToDomain` method should not include a DocBlock.
* **Return IDs on Create:** The `add` method must return the ID of the newly created record rather than the object itself.
* **Standardized Naming:** Use consistent verbs for method names: `get`, `find`, `add`, `update`, and `delete`.
* **Avoid Premature Casting:** Do not cast database columns to scalar types unless strictly necessary; doing so can mask underlying data integrity issues or errors.
* **PHPStan Property Access:** When mapping from a generic $dbRow object, use inline `// @phpstan-ignore property.notFound` comments for each property access to satisfy static analysis.

## Idiomatic, Generic Example Of A Repository Class

```PHP
<?php declare(strict_types=1);

namespace App\Repositories;

use App\Domain\Article;
use Carbon\CarbonImmutable;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class ArticleRepository	 
{
    public function get(int $id): Article
    {
        $dbRow = DB::table('articles')
            ->where(['id' => $id])
            ->sole();

        return self::mapToDomain($dbRow);
    }

    /**
     * @return Collection<int, Article>
     */
    public function findAll(): Collection
    {
        return DB::table('articles')
            ->get()
            ->map(fn($dbRow) => self::mapToDomain($dbRow));
    }

    /**
     * @return Collection<int, Article>
     */
    public function findByAuthor(int $authorId): Collection
    {
        return DB::table('articles')
            ->where('author_id', $authorId)
            ->get()
            ->map(fn($dbRow) => self::mapToDomain($dbRow));
    }

    public function add(Article $article): int
    {
        return DB::table('articles')->insertGetId(self::mapToPersistence($article));
    }

    public function update(Article $article): void
    {
        DB::table('articles')
            ->where(['id' => $article->id])
            ->update(self::mapToPersistence($article));
    }

    private static function mapToDomain(object $dbRow): Article
    {
        return new Article(
            uuid: $dbRow->uuid, // @phpstan-ignore property.notFound
            authorId: $dbRow->author_id, // @phpstan-ignore property.notFound
            title: $dbRow->title, // @phpstan-ignore property.notFound
            publishedAt: $dbRow->published_at ? CarbonImmutable::createFromFormat('Y-m-d H:i:s', $dbRow->published_at) : NULL // @phpstan-ignore property.notFound
        );
    }


    /**
     * @return array<string, bool|int|string|CarbonImmutable>
     */
    private static function mapToPersistence(Article $article): array
    {
        return [
            'uuid' => $article->uuid,
            'author_id' => $article->authorId,
            'title' => $article->title,
            'published_at' => $article->publishedAt?->format('Y-m-d H:i:s'),
        ];
    }
}
```
