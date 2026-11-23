# FitnessTracker

IT 2001 Web Programming project, International Burch University, 2026/2027.

FitnessTracker is a single-page web application for logging workouts. Users record the exercises, sets,
reps and weight of each workout, browse the exercise library, track their personal records and follow
their progress over time. Admins manage the exercise library, exercise categories and users.

> Status: Milestone 2 (MySQL database and DAO layer). Services, routes and the connected frontend follow in the next milestones.

## Technology
- Frontend: HTML, CSS, JavaScript, jQuery, Bootstrap 5, jquery.spapp (SPA)
- Backend: PHP, FlightPHP, PDO, MySQL, JWT
- Local server: XAMPP

## Entities and relationships

![ERD](docs/erd.png)

| Entity | Description | Relationships |
|---|---|---|
| users | accounts with a role (admin or user) | one user has many workouts and many personal records |
| exercise_categories | groups of exercises (strength, bodyweight, cardio, mobility); each category has a measurement type (weighted, bodyweight or cardio) that all its exercises use | one category has many exercises |
| exercises | the exercise library, managed by the admin, with the muscle group of each exercise | belongs to one category; used in many workout_exercises and personal_records |
| workouts | one training session of a user on a date | belongs to one user; has many workout_exercises |
| workout_exercises | one exercise inside a workout: sets, reps and weight for lifts (added weight for bodyweight), sets and duration for holds like the plank, duration and distance for cardio | links workouts and exercises (many-to-many) |
| personal_records | a user's best lift for an exercise | belongs to one user and one exercise |

## Roles
| Role | Can do |
|---|---|
| admin | manage users, exercises and exercise categories; see all data |
| user | log, edit and delete own workouts; manage own personal records and profile; browse the exercise library |

Test accounts (created by `database/fitness_tracker.sql`; login works from Milestone 4):

| Role | E-mail | Password |
|---|---|---|
| admin | admin.test@example.com | Admin123! |
| user | keno.test@example.com | User123! |

## External API
wger Workout Manager API (https://wger.de/api/v2/): the "Discover exercises" section of the Exercise Library
lets users search exercises that are not in our library yet. Implemented in Milestone 3.

## Pages (SPA views)
The frontend is a single-page application built with jquery.spapp. Each view is a separate file in
`frontend/views/` and is loaded into `index.html` on its first visit, without a page reload.
In Milestone 1 all views are static and show sample data.

| View | Hash | Who | What it shows |
|---|---|---|---|
| Login | `#login` | everyone | sign-in form |
| Register | `#register` | everyone | sign-up form with fitness goal and experience level |
| Dashboard | `#dashboard` | user | weekly stats, recent workouts, weekly goal, latest personal records |
| Log Workout | `#workout-log` | user | workout details and exercises with sets, reps and weight |
| Workout History | `#workout-history` | user | table of own workouts with details, edit and delete |
| Exercise Library | `#exercise-library` | user | exercises grouped by category; **Discover exercises** tab (wger API) |
| Progress | `#progress` | user | statistics, strength chart (Milestone 4), personal records CRUD |
| Profile | `#profile` | user | edit profile, change password, delete account |
| Admin Panel | `#admin-panel` | admin | manage users, exercises and exercise categories |

## Running locally
1. Clone the repository into `xampp/htdocs/` and start Apache in XAMPP.
2. Open `http://localhost/<repository-folder>/frontend/`.

The app must be opened through `http://localhost`, not by double-clicking `index.html`: the views are
loaded with AJAX, and browsers block those requests for `file://` pages.

### Database and configuration
3. Start MySQL and import `database/fitness_tracker.sql` (MySQL Workbench / DBeaver: open the file and run
   the whole script; phpMyAdmin: Import). It creates the database `fitness_tracker` with all tables and
   sample data, and can be imported again at any time to reset the data.
4. Copy `backend/config/config.example.php` to `backend/config/config.php` and set your database values
   (host, port, name, user, password). `config.php` is in `.gitignore` and is never committed.
5. Test the DAO layer from the `backend` folder: `php rest/dao/test.php`. It calls every DAO method and
   deletes everything it adds.

## Links
- Swagger documentation: added in Milestone 3
- Deployed application: added in Milestone 5
