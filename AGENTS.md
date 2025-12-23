# AGENTS.md

## General Principles For How Codex CLI Should Behave

- Unless instructed otherwise, assume that the task you have been given has a straightforward, idiomatic solution and does not require a novel solution.
- In all interactions with the person using Codex CLI, be extremely concise and sacrifice grammar for the sake of concision.
- When finishing a task, run `php artisan test` and `vendor/bin/phpstan analyse app`, to check whether the changes from the task caused errors.

## About This Codebase

### General Description

- This codebase contains the Laravel application for a warehouse management system (WMS).

### Codebase Status

- The application is in its infancy and is currently unfinished.

### Tech Stack

- **Language:** PHP 8.4
- **Framework:** Laravel
- **Frontend Architecture:** Inertia.js (Single-page app using server-side routing/controllers)
- **Frontend Library:** React 19 (w/ TypeScript)
- **Styling & Components:** Tailwind CSS, shadcn/ui
- **Build Tool:** Vite
- **Key Packages:** `spatie/laravel-data`

## Architectural and Coding Style Guidelines

### Backend Guidelines (Laravel)

- **Guiding Principle**: Backend-code should follow Spatie-like coding style (clean, readable code, strict types, and modern PHP 8.4 features, etc.).
- **Deliberate Non-idiomatic Choices**
  - **No usage of Eloquent besides the `User` model**: The default `User` model is Eloquent for authentication compatibility.
  - **Repositories And Non-Eloquent Domain Entities Used Instead Of Eloquent:** 
    - All database interaction for domain entities must happen inside Repository classes. 
      - Parameters and returns should be domain entities or Collections of domain entities.
      - Leverage `spatie/laravel-data` attributes (e.g., `#[MapInputName]`, `#[WithCast]`) to automatically map `DB::table` results to Data objects.
    - Domain entities must be **immutable** classes extending `Spatie\LaravelData\Data`.
      - Use `readonly` properties.
      - Use `public function with...()` methods returning `new self` for state changes.

### Frontend Guidelines (Inertia/React)
- **Guiding Principle:** Follow standard Inertia patterns.  
- Use Controllers to return `Inertia::render` responses.
- Do not build a separate REST API unless explicitly instructed.

## Progressive Disclosure of Further Guidelines Instructions For Specific Types Of Tasks/Sub-tasks

- **Running Tests:** Read `agent_docs/running-tests.md`.
- **Creating/editing Console Commands:** `agent_docs/creating-editing-console-commands.md`
- **Creating Database Migrations:** `agent_docs/creating-database-migrations.md`
