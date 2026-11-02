-- FitnessTracker database: schema and sample data (Milestone 2)
-- Schema source: docs/erd.dbml. Every table has the primary key id INT AUTO_INCREMENT.
-- The file can be imported again at any time: it drops and recreates all tables.
--
-- Test accounts (passwords are stored as bcrypt hashes):
--   admin.test@example.com / Admin123!   (role admin)
--   keno.test@example.com  / User123!    (role user; all other sample users use User123! too)

CREATE DATABASE IF NOT EXISTS fitness_tracker CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE fitness_tracker;

-- Children first, so the foreign keys do not block the drops
DROP TABLE IF EXISTS personal_records;
DROP TABLE IF EXISTS workout_exercises;
DROP TABLE IF EXISTS workouts;
DROP TABLE IF EXISTS exercises;
DROP TABLE IF EXISTS exercise_categories;
DROP TABLE IF EXISTS users;

-- ---------------------------------------------------------------------------
-- Schema
-- ---------------------------------------------------------------------------

CREATE TABLE users (
    id               INT AUTO_INCREMENT PRIMARY KEY,
    email            VARCHAR(255) NOT NULL,
    password_hash    VARCHAR(255) NOT NULL,
    full_name        VARCHAR(100) NOT NULL,
    fitness_goal     VARCHAR(50)  NULL,
    experience_level VARCHAR(20)  NULL,
    role             VARCHAR(20)  NOT NULL DEFAULT 'user',
    created_at       DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at       DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT uq_users_email  UNIQUE (email),
    CONSTRAINT chk_users_role  CHECK (role IN ('admin', 'user'))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE exercise_categories (
    id            INT AUTO_INCREMENT PRIMARY KEY,
    category_name VARCHAR(50) NOT NULL,
    -- How the exercises of this category are measured (every exercise inherits it through category_id):
    --   weighted   = sets x reps x weight_kg
    --   bodyweight = sets x reps (or duration_seconds for holds like Plank), weight_kg = added weight
    --   cardio     = duration_seconds and distance_km, optional added weight_kg (vest, backpack)
    exercise_type VARCHAR(20) NOT NULL,
    description   TEXT        NULL,
    created_at    DATETIME    NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT uq_exercise_categories_name UNIQUE (category_name),
    CONSTRAINT chk_exercise_categories_type CHECK (exercise_type IN ('weighted', 'bodyweight', 'cardio'))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE exercises (
    id            INT AUTO_INCREMENT PRIMARY KEY,
    category_id   INT          NOT NULL,
    exercise_name VARCHAR(100) NOT NULL,
    muscle_group  VARCHAR(50)  NULL,
    description   TEXT         NULL,
    created_at    DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    -- RESTRICT: a category that still has exercises cannot be deleted
    CONSTRAINT fk_exercises_category FOREIGN KEY (category_id)
        REFERENCES exercise_categories(id) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE workouts (
    id               INT AUTO_INCREMENT PRIMARY KEY,
    user_id          INT         NOT NULL,
    workout_date     DATE        NOT NULL,
    workout_type     VARCHAR(50) NOT NULL,
    duration_minutes INT         NULL,
    calories         INT         NULL,
    notes            TEXT        NULL,
    created_at       DATETIME    NOT NULL DEFAULT CURRENT_TIMESTAMP,
    -- CASCADE: a workout belongs to its user and is deleted with the account
    CONSTRAINT fk_workouts_user FOREIGN KEY (user_id)
        REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE workout_exercises (
    id          INT AUTO_INCREMENT PRIMARY KEY,
    workout_id  INT          NOT NULL,
    exercise_id INT          NOT NULL,
    -- Which columns are used depends on the exercise_type of the category (the rules per type are checked in the services)
    sets             INT          NULL,  -- NULL for cardio
    reps             INT          NULL,  -- NULL for cardio and timed holds
    weight_kg        DECIMAL(5,2) NULL,  -- bodyweight and cardio: added weight (vest, plates, backpack), NULL = none
    duration_seconds INT          NULL,  -- cardio: total time; timed holds: time per set
    distance_km      DECIMAL(6,2) NULL,  -- cardio only
    notes            TEXT         NULL,
    -- CASCADE: the rows of a workout are deleted with the workout
    CONSTRAINT fk_workout_exercises_workout FOREIGN KEY (workout_id)
        REFERENCES workouts(id) ON DELETE CASCADE,
    -- RESTRICT: an exercise that is used in a workout cannot be deleted from the library
    CONSTRAINT fk_workout_exercises_exercise FOREIGN KEY (exercise_id)
        REFERENCES exercises(id) ON DELETE RESTRICT,
    CONSTRAINT chk_workout_exercises_sets     CHECK (sets IS NULL OR sets > 0),
    CONSTRAINT chk_workout_exercises_reps     CHECK (reps IS NULL OR reps > 0),
    CONSTRAINT chk_workout_exercises_weight   CHECK (weight_kg IS NULL OR weight_kg >= 0),
    CONSTRAINT chk_workout_exercises_duration CHECK (duration_seconds IS NULL OR duration_seconds > 0),
    CONSTRAINT chk_workout_exercises_distance CHECK (distance_km IS NULL OR distance_km >= 0),
    -- every row records something: reps (lifts) or a duration (cardio, timed holds)
    CONSTRAINT chk_workout_exercises_measured CHECK (reps IS NOT NULL OR duration_seconds IS NOT NULL)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE personal_records (
    id            INT AUTO_INCREMENT PRIMARY KEY,
    user_id       INT          NOT NULL,
    exercise_id   INT          NOT NULL,
    -- Same idea as workout_exercises: the columns used depend on the exercise_type of the category
    max_weight_kg    DECIMAL(5,2) NULL,  -- bodyweight: the added weight
    reps_achieved    INT          NULL,
    duration_seconds INT          NULL,  -- cardio: time; timed holds: longest hold
    distance_km      DECIMAL(6,2) NULL,  -- cardio only
    achieved_date    DATE         NOT NULL,
    notes         TEXT         NULL,
    created_at    DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_personal_records_user FOREIGN KEY (user_id)
        REFERENCES users(id) ON DELETE CASCADE,
    CONSTRAINT fk_personal_records_exercise FOREIGN KEY (exercise_id)
        REFERENCES exercises(id) ON DELETE RESTRICT,
    CONSTRAINT chk_personal_records_weight   CHECK (max_weight_kg IS NULL OR max_weight_kg >= 0),
    CONSTRAINT chk_personal_records_reps     CHECK (reps_achieved IS NULL OR reps_achieved > 0),
    CONSTRAINT chk_personal_records_duration CHECK (duration_seconds IS NULL OR duration_seconds > 0),
    CONSTRAINT chk_personal_records_distance CHECK (distance_km IS NULL OR distance_km >= 0),
    CONSTRAINT chk_personal_records_measured CHECK (reps_achieved IS NOT NULL OR duration_seconds IS NOT NULL OR distance_km IS NOT NULL)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------------
-- Sample data (matches the static data of the Milestone 1 views)
-- ---------------------------------------------------------------------------

-- Admin123! for the admin, User123! for every user
INSERT INTO users (id, email, password_hash, full_name, fitness_goal, experience_level, role, created_at) VALUES
(1, 'admin.test@example.com', '$2y$10$xo.bb/COwjBaMDm2pHowm.oASq24jjDkxaaddmUvGD.6KxfaUocG6', 'Admin Test', NULL,              NULL,           'admin', '2026-09-01 09:00:00'),
(2, 'keno.test@example.com',  '$2y$10$RFDFIddXN929/SaQsbuCvezVr.3s6eHRcz5S/PLD688GeW8j4yy02', 'Keno Test',  'strength',        'intermediate', 'user',  '2026-09-02 10:15:00'),
(3, 'lena.test@example.com',  '$2y$10$RFDFIddXN929/SaQsbuCvezVr.3s6eHRcz5S/PLD688GeW8j4yy02', 'Lena Test',  'muscle_gain',     'beginner',     'user',  '2026-09-04 18:30:00'),
(4, 'mark.test@example.com',  '$2y$10$RFDFIddXN929/SaQsbuCvezVr.3s6eHRcz5S/PLD688GeW8j4yy02', 'Mark Test',  'endurance',       'advanced',     'user',  '2026-09-09 07:45:00'),
(5, 'sara.test@example.com',  '$2y$10$RFDFIddXN929/SaQsbuCvezVr.3s6eHRcz5S/PLD688GeW8j4yy02', 'Sara Test',  'weight_loss',     'beginner',     'user',  '2026-09-20 12:00:00'),
(6, 'tom.test@example.com',   '$2y$10$RFDFIddXN929/SaQsbuCvezVr.3s6eHRcz5S/PLD688GeW8j4yy02', 'Tom Test',   'general_fitness', 'beginner',     'user',  '2026-10-18 20:10:00');

INSERT INTO exercise_categories (id, category_name, exercise_type, description) VALUES
(1, 'Strength',   'weighted',   'Barbell, dumbbell and machine exercises with external load.'),
(2, 'Bodyweight', 'bodyweight', 'Exercises that use your own body as resistance.'),
(3, 'Cardio',     'cardio',     'Endurance training for the heart and lungs.'),
(4, 'Mobility',   'bodyweight', 'Stretching and mobility work for joints and muscles.');

-- muscle_group uses the same values as the filter in the Exercise Library view
INSERT INTO exercises (id, category_id, exercise_name, muscle_group, description) VALUES
(1,  1, 'Bench Press',            'chest',     'Press a barbell from the chest while lying on a flat bench.'),
(2,  1, 'Incline Dumbbell Press', 'chest',     'Dumbbell press on a 30 degree incline bench, targets the upper chest.'),
(3,  1, 'Squat',                  'legs',      'Barbell back squat to parallel or below, keeping the chest up.'),
(4,  1, 'Romanian Deadlift',      'legs',      'Hip hinge with a barbell and soft knees, for hamstrings and glutes.'),
(5,  1, 'Leg Press',              'legs',      'Machine press for quads and glutes with a supported back.'),
(6,  1, 'Deadlift',               'back',      'Lift a barbell from the floor to hip height with a neutral spine.'),
(7,  1, 'Barbell Row',            'back',      'Bent-over row pulling the bar to the lower chest.'),
(8,  1, 'Overhead Press',         'shoulders', 'Standing barbell press from the shoulders to lockout overhead.'),
(9,  1, 'Lateral Raise',          'shoulders', 'Raise light dumbbells out to the sides up to shoulder height.'),
(10, 1, 'Barbell Curl',           'arms',      'Curl a barbell with elbows fixed at your sides, for the biceps.'),
(11, 1, 'Triceps Pushdown',       'arms',      'Cable pushdown with a straight bar or rope, for the triceps.'),
(12, 2, 'Push-up',                'chest',     'Classic push-up with a straight body line from head to heels.'),
(13, 2, 'Pull-up',                'back',      'Pull your chin over the bar from a dead hang, palms facing away.'),
(14, 2, 'Plank',                  'core',      'Hold a straight forearm plank while bracing the core.'),
(15, 2, 'Walking Lunge',          'legs',      'Alternate long forward lunges while walking, knee just above the floor.'),
(16, 3, 'Running',                'full_body', 'Steady outdoor or treadmill run at a conversational pace.'),
(17, 3, 'Rowing Machine',         'full_body', 'Drive with the legs first, then lean back and pull the handle in.'),
(18, 3, 'Cycling',                'legs',      'Stationary bike or road cycling, intervals or steady state.');
-- Category 4 (Mobility) has no exercises on purpose: it shows the empty-category case.
-- Mobility is measured like bodyweight: stretches are timed holds (sets x duration_seconds).

INSERT INTO workouts (id, user_id, workout_date, workout_type, duration_minutes, calories, notes) VALUES
(1, 2, '2026-10-23', 'Back Day',     70, 500, 'New deadlift PR!'),
(2, 2, '2026-10-21', 'Leg Day',      75, 560, 'Squat felt heavy but clean.'),
(3, 2, '2026-10-19', 'Chest Day',    70, 300, 'Bench 85 kg for 5, finally.'),
(4, 2, '2026-10-17', 'Full Body',    60, 600, NULL),
(5, 2, '2026-10-15', 'Cardio',       45, 700, '5 km run + 15 min rowing.'),
(6, 2, '2026-10-13', 'Shoulder Day', 55, 500, 'Overhead press PR.'),
(7, 2, '2026-10-10', 'Arm Day',      50, 590, NULL),
(8, 3, '2026-10-22', 'Leg Day',      60, 450, 'First time squatting 80 kg.');

-- weighted: sets, reps, weight_kg | bodyweight: sets, reps or duration_seconds, optional added weight_kg
-- cardio: duration_seconds and distance_km, optional added weight_kg, no sets or reps
INSERT INTO workout_exercises (workout_id, exercise_id, sets, reps, weight_kg, duration_seconds, distance_km, notes) VALUES
-- 1: Back Day
(1, 6,  3,    3,    140.00, NULL, NULL, 'PR'),
(1, 13, 4,    8,    NULL,   NULL, NULL, NULL),
(1, 7,  4,    8,    70.00,  NULL, NULL, NULL),
-- 2: Leg Day
(2, 3,  5,    5,    110.00, NULL, NULL, NULL),
(2, 4,  3,    8,    90.00,  NULL, NULL, NULL),
(2, 5,  3,    10,   180.00, NULL, NULL, NULL),
-- 3: Chest Day
(3, 1,  5,    5,    85.00,  NULL, NULL, NULL),
(3, 2,  3,    10,   30.00,  NULL, NULL, NULL),
(3, 12, 3,    15,   NULL,   NULL, NULL, NULL),
-- 4: Full Body (Plank: 3 holds of 60 seconds)
(4, 3,  3,    8,    90.00,  NULL, NULL, NULL),
(4, 1,  3,    8,    70.00,  NULL, NULL, NULL),
(4, 7,  3,    8,    60.00,  NULL, NULL, NULL),
(4, 14, 3,    NULL, NULL,   60,   NULL, NULL),
-- 5: Cardio (5 km run in 30 min + 15 min rowing)
(5, 16, NULL, NULL, NULL,   1800, 5.00, NULL),
(5, 17, NULL, NULL, NULL,   900,  NULL, NULL),
-- 6: Shoulder Day
(6, 8,  5,    5,    52.50,  NULL, NULL, NULL),
(6, 9,  3,    12,   10.00,  NULL, NULL, NULL),
-- 7: Arm Day
(7, 10, 3,    10,   35.00,  NULL, NULL, NULL),
(7, 11, 3,    12,   30.00,  NULL, NULL, NULL),
-- 8: Lena's Leg Day (Walking Lunge with two 8 kg dumbbells = 16 kg added weight)
(8, 3,  4,    6,    80.00,  NULL, NULL, NULL),
(8, 15, 3,    12,   16.00,  NULL, NULL, 'Holding two 8 kg dumbbells');

INSERT INTO personal_records (user_id, exercise_id, max_weight_kg, reps_achieved, duration_seconds, distance_km, achieved_date, notes) VALUES
(2, 6,  140.00, 3,    NULL, NULL, '2026-10-23', NULL),
(2, 3,  110.00, 5,    NULL, NULL, '2026-10-21', NULL),
(2, 1,  85.00,  5,    NULL, NULL, '2026-10-19', NULL),
(2, 8,  52.50,  5,    NULL, NULL, '2026-10-13', NULL),
(2, 7,  72.50,  8,    NULL, NULL, '2026-10-02', NULL),
(2, 13, 10.00,  6,    NULL, NULL, '2026-09-28', 'Added weight on a dip belt'),
(2, 4,  100.00, 8,    NULL, NULL, '2026-09-21', NULL),
(2, 16, NULL,   NULL, 1800, 5.00, '2026-10-15', 'First 5 km in 30 minutes'),
(3, 3,  80.00,  6,    NULL, NULL, '2026-10-22', NULL);
