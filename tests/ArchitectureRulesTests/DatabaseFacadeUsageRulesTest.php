<?php

arch('DB-facade should only be used in Repository-style classes (repositories, ledgers, etc.)')
    ->expect('Illuminate\Support\Facades\DB')
    ->toOnlyBeUsedIn('App\Repositories');
