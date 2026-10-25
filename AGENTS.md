# AGENTS.md

## Project
FitnessTracker: a single-page web application where users log their workouts (exercises, sets, reps, weight),
browse an exercise library, track personal records and see their progress. Admins manage the exercise
library, the exercise categories and the users.

Roles: admin, user.
Entities: users, exercise_categories, exercises, workouts, workout_exercises, personal_records.

External API (planned for Milestone 3): wger Workout Manager API (https://wger.de/api/v2/), shown in the
"Discover exercises" section of the Exercise Library view, so users can search exercises that are not yet
in our own library.

## Technology stack
- Frontend: HTML, CSS, JavaScript, jQuery, Bootstrap 5.3, single-page application (SPA) with jquery.spapp
- Backend: PHP 8.2 with FlightPHP (REST API), PDO, MySQL
- Authentication: JWT (from Milestone 4)
- Local server: XAMPP

## Folder structure
- frontend/: index.html (SPA shell), css/, js/, views/ (one HTML file per view), assets/ (images, icons)
- backend/: index.php, rest/routes/, rest/services/, rest/dao/, middleware/, config/, logs/
- docs/: AI_LOG.md, ERD and other documentation

## Current milestone
Milestone 1: project structure, static SPA frontend, draft ERD, README draft.
- The frontend is static: no AJAX calls and no backend code yet. Use realistic sample data in the HTML.
- Do not create PHP files in backend/ during Milestone 1, except config/config.example.php.

## Frontend rules
- Each view is in its own file in frontend/views/ and is registered once in frontend/js/app.js.
- index.html loads all common CSS and JS files once. Views do not contain <script> or <link> tags.
- Use Bootstrap 5 classes and components first. Custom styling goes only into frontend/css/style.css.
- No inline styles (style="...") and no onclick attributes; use classes and jQuery event handlers.
- Every page must work on mobile (check the navbar, tables and forms at small widths).
- Admin-only links and views are marked so they can be hidden by role later (class "admin-only").
- From Milestone 4: only files in frontend/js/services/ talk to the backend; views never contain AJAX.

## Backend rules (from Milestone 2)
- Requests go: route -> service -> DAO -> database.
- Only services call DAO classes. Routes never call DAO classes directly.
- Every DAO extends BaseDao. Every table has the primary key id INT AUTO_INCREMENT.
- All SQL uses PDO prepared statements with named placeholders. Never glue values into SQL strings.
- Routes return JSON with correct HTTP status codes (200, 201, 400, 401, 403, 404, 500).
- Never return password hashes from the API.

## Conventions
- PHP classes: PascalCase (WorkoutService, WorkoutDao). Files are named after the class.
- JavaScript and PHP variables: camelCase. Database tables and columns: snake_case.
- View files and view ids: kebab-case (workout-history.html, #workout-history).

## Rules for AI assistants
- Do not add new libraries or frameworks without asking me first.
- Do not change the database schema without asking me first.
- Never put passwords, keys or secrets in code; they belong in backend/config/config.php,
  which is not committed. Update config.example.php instead.
- Do not edit files in backend/logs/.
- Make small changes, one view or feature at a time, and explain what you changed and why.
- Do not change files that the task does not mention.
