# Argon Development Guidelines

## 🚀 Key Commands
• **Dev Server:** `npm run dev` (Laravel + Vite + Pail)
• **Build:** `npm run build`
• **Linting:** `npm run lint` (Frontend) | `vendor/bin/pint` (Backend)
• **Formatting:** `npm run format` (Prettier)
• **Testing:** `php artisan test` (Pest)
• **Single Test:** `vendor/bin/pest tests/Feature/YourTest.php` or `php artisan test --filter=YourTest`

## 🎨 Code Style & Conventions
• **Frontend:** Vue 3 `<script setup lang="ts">` with Naive UI components.
• **Imports:** Use `@/` alias for `resources/js`. Group by: 
  1. Vue/Inertia 2. UI Libraries 3. Internal Components 4. Utilities/Routes.
• **Backend:** DDD structure in `src/Domain`. Use `LaravelActions` for logic.
• **Data Handling:** Use Spatie `LaravelData` for DTOs and Type-safety.
• **Types:** Strict TS required for Vue; Return types & Type hinting for PHP.
• **Naming:** PascalCase for Components/Models, camelCase for JS, snake_case for DB.
• **Error Handling:** `vue-sonner` for UI; standardized Laravel Exceptions for API.
• **Tailwind:** Use Utility-first CSS (Tailwind v4). Favors custom colors like `moovin-lime`, `moovin-lila`.
