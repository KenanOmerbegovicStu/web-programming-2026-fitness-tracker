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
- backend/: index.php (services, error handler, routes, Flight::start()), .htaccess, composer.json,
  rest/routes/, rest/services/, rest/dao/, utils/ (exceptions, Validator, Roles, Logger), middleware/,
  config/, logs/, public/v1/docs/ (Swagger UI, swagger.php, doc_setup.php, paths/)
- database/: fitness_tracker.sql (schema and sample data; the database is created from this file)
- docs/: AI_LOG.md, ERD and other documentation

## Current milestone
Milestone 3: services, REST routes, OpenAPI documentation and the external API (wger).
- One service per table in backend/rest/services/ (extends BaseService); rest/services/test.php tries every rule.
- One routes file per entity in backend/rest/routes/; one Paths class per routes file in public/v1/docs/paths/.
- External API: WgerService + GET /external/exercises. The frontend stays static until Milestone 4.
- No JWT and no middleware yet (Milestone 4): user_id is sent in the request body or URL for now.

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
- exercise_categories.exercise_type (weighted, bodyweight, cardio) is inherited by every exercise of the
  category (read it with a JOIN; exercises have no type column). It decides which columns of workout_exercises and
  personal_records are used: weighted = sets, reps, weight_kg; bodyweight = sets, reps or duration_seconds,
  optional added weight_kg; cardio = duration_seconds, distance_km, optional added weight_kg (vest, backpack).
  The database only blocks impossible rows; the rules per type are checked in the services (Milestone 3).
- Services check every rule and signal a broken one with an exception from utils/exceptions.php
  (ValidationException 400, NotFoundException 404, ExternalServiceException 503). They never write SQL.
- Routes only read the request, call one service method and answer with Flight::json(); 201 for POST.
  No SQL, no business rules and no try/catch in routes: the error handler in index.php answers every
  exception as {"status": ..., "error": "..."} and never sends stack traces, SQL or file paths.
- Path ids are written as @id:[0-9]+. URLs are plural nouns in kebab-case (/exercise-categories).
- When a route changes, its OpenAPI attribute in public/v1/docs/paths/ changes in the same commit; every status
  a route can return is documented.
- External API calls go only through WgerService: timeout from config, failures written to the error log,
  a friendly 503 to the client.
- Write logs only through utils/Logger.php.

## Conventions
- PHP classes: PascalCase (WorkoutService, WorkoutDao). Files are named after the class.
- PHP methods and variables: snake_case, as in the course labs (get_by_id(), $user_id). Array keys match the
  database columns, so the backend uses one style from the database to the JSON.
- Database tables and columns: snake_case.
- JavaScript variables and functions: camelCase. JSON keys from the API stay snake_case (database columns).
- View files and view ids: kebab-case (workout-history.html, #workout-history).

## Rules for AI assistants
- Do not add new libraries or frameworks without asking me first.
- Do not change the database schema without asking me first.
- Never put passwords, keys or secrets in code; they belong in backend/config/config.php,
  which is not committed. Update config.example.php instead.
- Do not edit files in backend/logs/.
- Make small changes, one view or feature at a time, and explain what you changed and why.
- Do not change files that the task does not mention.
