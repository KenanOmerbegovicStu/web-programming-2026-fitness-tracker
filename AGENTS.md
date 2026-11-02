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
- database/: fitness_tracker.sql (schema and sample data; the database is created from this file)
- docs/: AI_LOG.md, ERD and other documentation

## Current milestone
Milestone 2: MySQL database and DAO layer.
- The schema is docs/erd.dbml; database/fitness_tracker.sql creates it with sample data and must stay in sync.
- backend/rest/dao/: BaseDao.php plus one DAO per table, and test.php that calls every DAO method.
- No services, routes or index.php yet (Milestone 3). The frontend stays static until Milestone 4.

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
