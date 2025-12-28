# Guidelines For Routes And Controller Classes

## Approach

- Use route parameter format constraints (e.g. `whereNumber`, `whereAlpha`, etc.) when applicable.

## Idiomatic, Generic Example Of A Route And A Controller Class

### Example Route

```PHP
...

Route::get('authors/{id}', [AuthorController::class, 'show'])
    ->name('authors.show')
    ->whereNumber('id');

...
```
### Example Controller

```PHP
<?php

namespace App\Http\Controllers;

use App\Repositories\ArticleRepository;
use Inertia\Inertia;
use Inertia\Response;

class ArticleController extends Controller
{
    public function show(int $id, ArticleRepository $articleRepository): Response
    {
        $tenantId = 1;
        
        $item = $articleRepository->getById($tenantId, $id);

        return Inertia::render('articles/show', [
            'article' => $item->toArray(),
        ]);
    }
}
```
