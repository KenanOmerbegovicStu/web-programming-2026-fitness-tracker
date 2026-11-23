# AI Log

## Milestone 1

### Tools and tasks
- Claude (claude.ai chat): analysed the old FitnessTracker project against the Milestone 1 requirements
  and drafted AGENTS.md, CLAUDE.md, .gitignore, the agent files and the draft ERD.
  Prompt: "ill give you a zip of the old project here so you can familiarise yourself and we can plan out
  everything before i start building with claude code"
  Kept: AGENTS.md, CLAUDE.md, .gitignore and the agent files as drafted.
  Changed: replaced the draft ERD with my own ERD made in dbdiagram.io (docs/erd.png, exported as
  docs/erd.dbml so the AI can read it).
- Claude Code (Claude Opus 5.5), planning: read AGENTS.md, CLAUDE.md, the Milestone 1 requirements and the
  frontend of my previous version of this project (my own project from last year, used as read-only
  reference as agreed with the professor). It listed the views, what to reuse and change per view,
  and the libraries for index.html.
  Prompt: "Read AGENTS.md and CLAUDE.md, the Milestone 1 section of the project requirements in ../guidelines,
  and the frontend of ../old-fitness-tracker. Then give me: 1. the list of views we need for M1, 2. for each
  view, what can be reused from the old view and what must change [...] 3. the libraries index.html should
  load, with versions. Do not create or change any files yet."
  My decisions: Bootstrap Icons instead of Font Awesome; Chart.js and DataTables moved to Milestone 4,
  plain Bootstrap tables with static rows for now.
- Claude Code (Claude Opus 5.5), implementation: built the SPA shell (index.html, js/app.js, css/style.css),
  copied jquery.spapp.js from my old project (with two documented changes: each view loads only once,
  safer lookup of sections), and built all 9 static views with sample data. Updated the README pages section.
  Prompt: "yes start, keep in mind [...] i am fully on board with visual improvements to the front end
  compared to the previous project"
  Kept: the layout, theme and views. Changed: the sample user name, and total volume (weight lifted)
  replaced with calories burned on the dashboard, workout log and workout history.

### Where the AI was wrong
1. Claude Code used a realistic invented person's name as the logged-in sample user. I noticed it could
   cause problems with the professors, so I had it replaced with an obvious test name ("Keno Test").
2. Claude Code chose total volume (kg lifted) as a main metric on the dashboard, workout log and workout
   history. I found that irrelevant for users and replaced it with calories burned (and the box icon with
   a flame). The ERD got a calories column in workouts.
3. The dashboard's weekly volume (12,450 kg) did not match the workouts in the history view. Claude Code
   noticed the mismatch while building the history view and corrected the number.
4. Claude Code first put data-bs-dismiss on the "Edit workout" link inside a modal. Bootstrap cancels the
   click on such links, so the link would never have opened the view. It noticed this itself and replaced
   it with one handler in app.js that closes open modals on every view change.

## Milestone 2

### Tools and tasks
- Claude Code (Claude Opus 5.5), planning: read the Lab 03 (DAO) notes, the Milestone 2 requirements and
  docs/erd.dbml, compared them with my old project, and wrote a plan for the database and DAO layer.
  Prompt: "okay so you can access week3 lab notes and the project requirments for milestone 2 just check it
  out and lets plan what is the best course of action for this"
  My decisions: keep the notes columns; use the lab's snake_case for PHP methods and variables (also
  practice for the lab-style exam tasks), written into AGENTS.md; use my local MySQL 8 server.
- Claude Code (Claude Opus 5.5), implementation: wrote database/fitness_tracker.sql (named foreign keys,
  ON DELETE rules, CHECK constraints, sample data matching the Milestone 1 views), BaseDao.php following
  Lab 03, one DAO per table with special queries (JOINs, GROUP BY statistics, search) and test.php that
  calls every DAO method. Updated AGENTS.md, the README and erd.dbml.
- Claude Code (Claude Opus 5.5), schema review: I asked it to review the three exercise groups
  (weight training, bodyweight, cardio) before going further.
  Prompt: "theres three primary groups when it comes to exercises body weight, cardio and weight training,
  body weight can have added weights, but measuring cardio is a whole different ball game lets review
  these three things before we procceed with changes"
  Result: duration_seconds and distance_km in workout_exercises and personal_records, and an
  exercise_type stored on exercise_categories, so every exercise inherits it through category_id.
  The per-type rules go into the services in Milestone 3.

### Where the AI was wrong
1. Claude Code assumed XAMPP's MySQL (root, no password) was running on port 3306 and tried to import the
   SQL file with XAMPP's mysql client. The import failed with a caching_sha2_password error. The port
   actually belonged to my separate MySQL Server 8.0. I moved XAMPP's MySQL to port 3307, put its root
   password in config.php and imported the file there.
2. Claude Code carried over the sets x reps x weight model from the Milestone 1 form and my old project
   and logged cardio as "1 set x 1 rep" with the distance hidden in notes, without pointing out that
   the schema could not store cardio or timed exercises like the plank. I noticed it when I reviewed the
   three exercise groups. We fixed it with a measurement type (exercise_type, stored on
   exercise_categories) and the duration_seconds and distance_km columns.
3. Claude Code recommended storing exercise_type on each exercise, without pointing out that in our data
   it duplicated the category (Strength = weighted, Bodyweight = bodyweight, Cardio = cardio). I noticed
   it when I checked the exercise_categories table and the exercises table side by side. We moved
   exercise_type to exercise_categories, so every exercise inherits it through category_id.

## Milestone 3

### Tools and tasks
- Claude Code (Claude Opus 5.5), planning: read the project requirements (Milestone 3 section) and the
  Lab 04 (services), Lab 05 (routes) and Lab 06 (OpenAPI) notes, checked my Milestone 2 DAOs and wrote a
  step-by-step plan: utils, services with the rules per entity, routes, wger, Swagger, docs.
  Prompt: "okay ive added the lab notes its time for milestone 3 check out the project requirments page, then
  go through the lab notes and lets plan out what needs to be done"
  My decisions: FlightPHP and swagger-php ^6.12 approved; a personal record that is not better than the
  current one is rejected with 400 (what "better" means per exercise type is written in PersonalRecordService);
  the milestone3 branch starts from milestone2, because the Milestone 2 PR is not merged yet.
- Claude Code (Claude Opus 5.5), implementation, one step at a time, each tested before the next:
  utils/ (exceptions, Validator, Roles, Logger), BaseService and one service per table (per-type rules for
  workout exercises and records, unique names, delete rules, last admin, hashed passwords), small count/lookup
  methods in the DAOs, rest/services/test.php, index.php with one error handler, one routes file per entity,
  WgerService with timeout and error logging, and the OpenAPI documentation (doc_setup.php, one Paths class
  per routes file, Swagger UI). Updated AGENTS.md and the README.
  Prompt: "i added the three lines to config listen just keep going unless theres something unclear or
  something that needs a decision imeditely"
- Checks it ran: the service test (every broken rule gives the expected 400/404), real HTTP requests through
  Apache for every route (success and failure), a 500 with the database unreachable and a 503 with wger
  unreachable (both logged), a script that compares every Flight::route with the OpenAPI document
  (35 routes, 35 documented operations), and a script that checks that every status the API really
  returned is listed in the documentation.

### Where the AI was wrong
1. Claude Code ran Composer with the PHP 8.4 that is installed on my PATH, so Composer picked package
   versions (Symfony 8) that need PHP 8.4. The CLI tests passed, but every request through Apache (XAMPP,
   PHP 8.2) ended in a fatal error "Composer detected issues in your platform". We saw it in the first HTTP
   test and fixed it with `"config": {"platform": {"php": "8.2.12"}}` in composer.json and `composer update`,
   which installed versions that work on PHP 8.2 (this also matters for deployment).
2. The plan assumed the wger search endpoint `/api/v2/exercise/search/?term=...`, which is what older wger
   examples use. The live API answered 404. Because the plan said to check the live API first, it was caught
   before any code was written: we listed the API root and switched to `/api/v2/exerciseinfo/?name__search=...`,
   which also returns muscles, equipment and images.
3. I asked Claude Code to remove the long explanation comments at the top of the new files. It wrote a PHP
   script that removed them with regular expressions and ran it on 10 files at once. One pattern had a typo,
   so `preg_replace()` returned null, and the script wrote that empty result into every file. All 10 files
   ended up with 0 bytes, and because they were not committed yet, Git could not restore them. It was noticed
   when the script printed the first lines of each file and they were empty. Claude Code rewrote the 10 files
   from the versions in the session, then re-ran all the checks (php -l, rest/services/test.php, the
   route/OpenAPI comparison and the status check), and all of them passed as before. Lesson: test a script
   that edits files on one file first, and commit before bulk edits.
4. The documentation said that GET /users/{id}/stats returns numbers, but the API returned
   `"total_minutes": "425"` (MySQL returns SUM() as a string). Found while writing the WorkoutStats schema
   from the real answer; fixed in WorkoutService by converting the totals to integers, so the code and the
   documentation agree.
