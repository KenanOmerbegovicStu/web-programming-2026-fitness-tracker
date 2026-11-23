<?php
use OpenApi\Attributes as OA;

#[OA\Info(
    title: 'FitnessTracker API',
    description: 'REST API of FitnessTracker - log workouts, browse the exercise library and track personal records. IT 2001 Web Programming. Until Milestone 4 (JWT) all endpoints are public.',
    version: '1.0'
)]
#[OA\Server(url: API_BASE_URL, description: 'This server')]

/* ---------- errors and messages: exactly what the error handler in index.php sends ---------- */
#[OA\Schema(
    schema: 'Error',
    properties: [
        new OA\Property(property: 'status', type: 'integer', example: 400),
        new OA\Property(property: 'error', type: 'string', example: "Field 'workout_type' is required.")
    ]
)]
#[OA\Schema(
    schema: 'Message',
    properties: [new OA\Property(property: 'message', type: 'string', example: 'Workout deleted.')]
)]

/* ---------- users ---------- */
#[OA\Schema(
    schema: 'UserInput',
    required: ['email', 'full_name'],
    properties: [
        new OA\Property(property: 'email', type: 'string', format: 'email', example: 'new.user@example.com'),
        new OA\Property(property: 'full_name', type: 'string', example: 'New User'),
        new OA\Property(property: 'password', type: 'string', format: 'password', example: 'Secret123',
            description: 'Required on POST (at least 8 characters, a letter and a number). Optional on PUT: only sent to change it. Stored as a bcrypt hash and never returned.'),
        new OA\Property(property: 'fitness_goal', type: 'string', nullable: true, example: 'strength',
            enum: ['strength', 'muscle_gain', 'endurance', 'weight_loss', 'general_fitness']),
        new OA\Property(property: 'experience_level', type: 'string', nullable: true, example: 'beginner',
            enum: ['beginner', 'intermediate', 'advanced']),
        new OA\Property(property: 'role', type: 'string', example: 'user', enum: ['admin', 'user'],
            description: 'Default user. The only admin cannot become a user.')
    ]
)]
#[OA\Schema(
    schema: 'User',
    description: 'A user as the API returns it - the password hash is never included.',
    properties: [
        new OA\Property(property: 'id', type: 'integer', example: 2),
        new OA\Property(property: 'email', type: 'string', example: 'keno.test@example.com'),
        new OA\Property(property: 'full_name', type: 'string', example: 'Keno Test'),
        new OA\Property(property: 'fitness_goal', type: 'string', nullable: true, example: 'strength'),
        new OA\Property(property: 'experience_level', type: 'string', nullable: true, example: 'intermediate'),
        new OA\Property(property: 'role', type: 'string', example: 'user'),
        new OA\Property(property: 'created_at', type: 'string', example: '2026-09-02 10:15:00'),
        new OA\Property(property: 'updated_at', type: 'string', nullable: true, example: '2026-11-02 01:18:39')
    ]
)]
#[OA\Schema(
    schema: 'WorkoutStats',
    properties: [
        new OA\Property(property: 'total_workouts', type: 'integer', example: 7),
        new OA\Property(property: 'total_minutes', type: 'integer', example: 425),
        new OA\Property(property: 'total_calories', type: 'integer', example: 3750)
    ]
)]

/* ---------- exercise categories ---------- */
#[OA\Schema(
    schema: 'ExerciseCategoryInput',
    required: ['category_name', 'exercise_type'],
    properties: [
        new OA\Property(property: 'category_name', type: 'string', example: 'Plyometrics'),
        new OA\Property(property: 'exercise_type', type: 'string', enum: ['weighted', 'bodyweight', 'cardio'], example: 'bodyweight',
            description: 'Decides which fields the workout log and the records of its exercises use.'),
        new OA\Property(property: 'description', type: 'string', nullable: true, example: 'Jumps and explosive bodyweight drills.')
    ]
)]
#[OA\Schema(
    schema: 'ExerciseCategory',
    allOf: [new OA\Schema(ref: '#/components/schemas/ExerciseCategoryInput')],
    properties: [
        new OA\Property(property: 'id', type: 'integer', example: 1),
        new OA\Property(property: 'exercise_count', type: 'integer', example: 11, description: 'Only in the list (GET /exercise-categories).'),
        new OA\Property(property: 'created_at', type: 'string', example: '2026-11-02 01:18:39', description: 'Only for one category.')
    ]
)]

/* ---------- exercises ---------- */
#[OA\Schema(
    schema: 'ExerciseInput',
    required: ['category_id', 'exercise_name'],
    properties: [
        new OA\Property(property: 'category_id', type: 'integer', example: 1),
        new OA\Property(property: 'exercise_name', type: 'string', example: 'Hip Thrust'),
        new OA\Property(property: 'muscle_group', type: 'string', nullable: true, example: 'legs'),
        new OA\Property(property: 'description', type: 'string', nullable: true, example: 'Drive the hips up with the upper back on a bench.')
    ]
)]
#[OA\Schema(
    schema: 'Exercise',
    allOf: [new OA\Schema(ref: '#/components/schemas/ExerciseInput')],
    properties: [
        new OA\Property(property: 'id', type: 'integer', example: 3),
        new OA\Property(property: 'category_name', type: 'string', example: 'Strength'),
        new OA\Property(property: 'exercise_type', type: 'string', example: 'weighted'),
        new OA\Property(property: 'created_at', type: 'string', example: '2026-11-02 01:18:39')
    ]
)]

/* ---------- workouts ---------- */
#[OA\Schema(
    schema: 'WorkoutInput',
    required: ['user_id', 'workout_date', 'workout_type'],
    properties: [
        new OA\Property(property: 'user_id', type: 'integer', example: 2, description: 'From Milestone 4 taken from the JWT token.'),
        new OA\Property(property: 'workout_date', type: 'string', format: 'date', example: '2026-11-20', description: 'Not in the future.'),
        new OA\Property(property: 'workout_type', type: 'string', example: 'Leg Day'),
        new OA\Property(property: 'duration_minutes', type: 'integer', nullable: true, example: 60, description: '1-600'),
        new OA\Property(property: 'calories', type: 'integer', nullable: true, example: 450, description: '0-5000'),
        new OA\Property(property: 'notes', type: 'string', nullable: true, example: 'Squat felt light today.')
    ]
)]
#[OA\Schema(
    schema: 'Workout',
    allOf: [new OA\Schema(ref: '#/components/schemas/WorkoutInput')],
    properties: [
        new OA\Property(property: 'id', type: 'integer', example: 2),
        new OA\Property(property: 'created_at', type: 'string', example: '2026-11-02 01:18:39'),
        new OA\Property(property: 'exercise_count', type: 'integer', example: 3, description: 'Only in the workouts of one user.')
    ]
)]

/* ---------- workout exercises ---------- */
#[OA\Schema(
    schema: 'WorkoutExerciseInput',
    description: 'Which fields are required depends on the exercise type: weighted = sets, reps, weight_kg; bodyweight = sets and either reps or duration_seconds (optional added weight_kg); cardio = duration_seconds (optional distance_km, weight_kg). Fields the type does not use are saved as null.',
    required: ['workout_id', 'exercise_id'],
    properties: [
        new OA\Property(property: 'workout_id', type: 'integer', example: 2),
        new OA\Property(property: 'exercise_id', type: 'integer', example: 3),
        new OA\Property(property: 'sets', type: 'integer', nullable: true, example: 5, description: '1-50'),
        new OA\Property(property: 'reps', type: 'integer', nullable: true, example: 5, description: '1-500'),
        new OA\Property(property: 'weight_kg', type: 'number', nullable: true, example: 110, description: '0-500; bodyweight and cardio: added weight'),
        new OA\Property(property: 'duration_seconds', type: 'integer', nullable: true, example: null, description: '1-86400'),
        new OA\Property(property: 'distance_km', type: 'number', nullable: true, example: null, description: '0-1000'),
        new OA\Property(property: 'notes', type: 'string', nullable: true, example: null)
    ]
)]
#[OA\Schema(
    schema: 'WorkoutExercise',
    allOf: [new OA\Schema(ref: '#/components/schemas/WorkoutExerciseInput')],
    properties: [
        new OA\Property(property: 'id', type: 'integer', example: 4),
        new OA\Property(property: 'exercise_name', type: 'string', example: 'Squat', description: 'Only in the exercises of one workout.'),
        new OA\Property(property: 'exercise_type', type: 'string', example: 'weighted', description: 'Only in the exercises of one workout.'),
        new OA\Property(property: 'muscle_group', type: 'string', example: 'legs', description: 'Only in the exercises of one workout.')
    ]
)]

/* ---------- personal records ---------- */
#[OA\Schema(
    schema: 'PersonalRecordInput',
    description: 'Fields by exercise type: weighted = max_weight_kg and reps_achieved; bodyweight = either reps_achieved or duration_seconds (optional added max_weight_kg); cardio = duration_seconds (optional distance_km, max_weight_kg).',
    required: ['user_id', 'exercise_id', 'achieved_date'],
    properties: [
        new OA\Property(property: 'user_id', type: 'integer', example: 2),
        new OA\Property(property: 'exercise_id', type: 'integer', example: 3),
        new OA\Property(property: 'max_weight_kg', type: 'number', nullable: true, example: 115),
        new OA\Property(property: 'reps_achieved', type: 'integer', nullable: true, example: 5),
        new OA\Property(property: 'duration_seconds', type: 'integer', nullable: true, example: null),
        new OA\Property(property: 'distance_km', type: 'number', nullable: true, example: null),
        new OA\Property(property: 'achieved_date', type: 'string', format: 'date', example: '2026-11-20', description: 'Not in the future.'),
        new OA\Property(property: 'notes', type: 'string', nullable: true, example: null)
    ]
)]
#[OA\Schema(
    schema: 'PersonalRecord',
    allOf: [new OA\Schema(ref: '#/components/schemas/PersonalRecordInput')],
    properties: [
        new OA\Property(property: 'id', type: 'integer', example: 2),
        new OA\Property(property: 'created_at', type: 'string', example: '2026-11-02 01:18:39'),
        new OA\Property(property: 'exercise_name', type: 'string', example: 'Squat', description: 'Only in the records of one user.'),
        new OA\Property(property: 'exercise_type', type: 'string', example: 'weighted', description: 'Only in the records of one user.')
    ]
)]

/* ---------- external API (wger) ---------- */
#[OA\Schema(
    schema: 'ExternalExercise',
    properties: [
        new OA\Property(property: 'wger_id', type: 'integer', example: 615),
        new OA\Property(property: 'name', type: 'string', example: 'Squats'),
        new OA\Property(property: 'category', type: 'string', nullable: true, example: 'Legs'),
        new OA\Property(property: 'muscles', type: 'array', items: new OA\Items(type: 'string'), example: ['Quads']),
        new OA\Property(property: 'equipment', type: 'array', items: new OA\Items(type: 'string'), example: ['Barbell']),
        new OA\Property(property: 'description', type: 'string', example: 'Place a barbell in a rack just below shoulder-height...'),
        new OA\Property(property: 'image', type: 'string', nullable: true, example: null),
        new OA\Property(property: 'in_library', type: 'boolean', example: false, description: 'true when our own library has an exercise with the same name')
    ]
)]
#[OA\Schema(
    schema: 'ExternalSearchResult',
    properties: [
        new OA\Property(property: 'search', type: 'string', example: 'squat'),
        new OA\Property(property: 'total', type: 'integer', example: 56, description: 'Matches at wger; at most 20 are returned'),
        new OA\Property(property: 'results', type: 'array', items: new OA\Items(ref: '#/components/schemas/ExternalExercise'))
    ]
)]
class OpenApiSpec {}
