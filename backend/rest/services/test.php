<?php
require_once __DIR__ . '/UserService.php';
require_once __DIR__ . '/ExerciseCategoryService.php';
require_once __DIR__ . '/ExerciseService.php';
require_once __DIR__ . '/WorkoutService.php';
require_once __DIR__ . '/WorkoutExerciseService.php';
require_once __DIR__ . '/PersonalRecordService.php';

/** Run one action and print OK or the exception it threw. */
function attempt($description, $action)
{
    echo str_pad($description, 55, '.') . ' ';
    try {
        $result = $action();
        echo "OK" . (isset($result['id']) ? " (id {$result['id']})" : '') . "\n";
        return $result;
    } catch (HttpException $e) {
        echo get_class($e) . " [{$e->get_status()}]: {$e->getMessage()}\n";
        return null;
    }
}

$users = new UserService();
$categories = new ExerciseCategoryService();
$exercises = new ExerciseService();
$workouts = new WorkoutService();
$workout_exercises = new WorkoutExerciseService();
$records = new PersonalRecordService();
$tomorrow = date('Y-m-d', strtotime('+1 day'));
$today = date('Y-m-d');

echo "=== UserService ===\n";
attempt('add: no password', fn() => $users->add(['email' => 'new.test@example.com', 'full_name' => 'New Test']));
attempt('add: password "123"', fn() => $users->add(['email' => 'new.test@example.com', 'full_name' => 'New Test', 'password' => '123']));
attempt('add: email "not-an-email"', fn() => $users->add(['email' => 'not-an-email', 'full_name' => 'New Test', 'password' => 'Secret123']));
attempt('add: email "Keno.Test@Example.com" (exists)', fn() => $users->add(['email' => 'Keno.Test@Example.com', 'full_name' => 'New Test', 'password' => 'Secret123']));
attempt('add: role "superuser"', fn() => $users->add(['email' => 'new.test@example.com', 'full_name' => 'New Test', 'password' => 'Secret123', 'role' => 'superuser']));
attempt('add: experience_level "expert"', fn() => $users->add(['email' => 'new.test@example.com', 'full_name' => 'New Test', 'password' => 'Secret123', 'experience_level' => 'expert']));
$new_user = attempt('add: valid user (sends password_hash "x" too)', fn() => $users->add(['email' => ' New.Test@Example.com ', 'full_name' => 'New Test', 'password' => 'Secret123', 'password_hash' => 'x', 'fitness_goal' => 'strength']));
if ($new_user) {
    echo "    stored email: {$new_user['email']}, role: {$new_user['role']}, hash returned: " . (isset($new_user['password_hash']) ? 'YES (bad)' : 'no') . "\n";
    $hash = (new UserDao())->get_by_email('new.test@example.com')['password_hash'];
    echo "    password is a bcrypt hash: " . (password_verify('Secret123', $hash) ? 'yes' : 'NO (bad)') . "\n";
}
attempt('update #2: keeps its own email', fn() => $users->update(['email' => 'keno.test@example.com', 'full_name' => 'Keno Test', 'fitness_goal' => 'strength', 'experience_level' => 'intermediate'], 2));
attempt('update #1: the only admin becomes a user', fn() => $users->update(['email' => 'admin.test@example.com', 'full_name' => 'Admin Test', 'role' => 'user'], 1));
attempt('delete #1: the only admin', fn() => $users->delete(1));
attempt('get user #999', fn() => $users->get_by_id(999));

echo "\n=== ExerciseCategoryService ===\n";
attempt('add: no name', fn() => $categories->add(['exercise_type' => 'cardio']));
attempt('add: type "yoga"', fn() => $categories->add(['category_name' => 'Yoga', 'exercise_type' => 'yoga']));
attempt('add: "Strength" (exists)', fn() => $categories->add(['category_name' => 'Strength', 'exercise_type' => 'weighted']));
attempt('add: description with <script>', fn() => $categories->add(['category_name' => 'Yoga', 'exercise_type' => 'bodyweight', 'description' => '<script>alert(1)</script>']));
attempt('update #1: keeps its own name', fn() => $categories->update(['category_name' => 'Strength', 'exercise_type' => 'weighted', 'description' => 'Barbell, dumbbell and machine exercises with external load.'], 1));
attempt('update #1: weighted -> cardio (used in logs)', fn() => $categories->update(['category_name' => 'Strength', 'exercise_type' => 'cardio'], 1));
attempt('delete #1 (has exercises)', fn() => $categories->delete(1));
$new_category = attempt('add: "Test Category"', fn() => $categories->add(['category_name' => 'Test Category', 'exercise_type' => 'bodyweight', 'description' => '']));
echo "    categories with exercise_count: " . count($categories->get_all()) . "\n";

echo "\n=== ExerciseService ===\n";
attempt('add: category #999', fn() => $exercises->add(['category_id' => 999, 'exercise_name' => 'Test Exercise']));
attempt('add: "Squat" (exists)', fn() => $exercises->add(['category_id' => 1, 'exercise_name' => 'Squat']));
attempt('delete #3 Squat (used in logs and records)', fn() => $exercises->delete(3));
attempt('search "a"', fn() => $exercises->search('a'));
echo str_pad('search "  press  "', 55, '.') . ' ' . implode(', ', array_column($exercises->search('  press  '), 'exercise_name')) . "\n";
attempt('by type "yoga"', fn() => $exercises->get_by_type('yoga'));
attempt('by category #999', fn() => $exercises->get_by_category(999));
$new_exercise = $new_category ? attempt('add: "Test Hold" in Test Category', fn() => $exercises->add(['category_id' => $new_category['id'], 'exercise_name' => 'Test Hold', 'muscle_group' => 'core'])) : null;
if ($new_category) {
    attempt('delete Test Category (still has Test Hold)', fn() => $categories->delete($new_category['id']));
}

echo "\n=== WorkoutService ===\n";
attempt('add: user #999', fn() => $workouts->add(['user_id' => 999, 'workout_date' => $today, 'workout_type' => 'Leg Day']));
attempt('add: date "2026-02-30"', fn() => $workouts->add(['user_id' => 2, 'workout_date' => '2026-02-30', 'workout_type' => 'Leg Day']));
attempt('add: tomorrow', fn() => $workouts->add(['user_id' => 2, 'workout_date' => $tomorrow, 'workout_type' => 'Leg Day']));
attempt('add: 900 minutes', fn() => $workouts->add(['user_id' => 2, 'workout_date' => $today, 'workout_type' => 'Leg Day', 'duration_minutes' => 900]));
$new_workout = attempt('add: valid workout today', fn() => $workouts->add(['user_id' => 2, 'workout_date' => $today, 'workout_type' => 'Test Day', 'duration_minutes' => 45, 'calories' => '', 'notes' => '']));
attempt('recent: limit 50', fn() => $workouts->get_recent(2, 50));
attempt('range: from after to', fn() => $workouts->get_by_date_range(2, '2026-10-30', '2026-10-01'));
echo str_pad('range: Keno 2026-10-15 .. 2026-10-21', 55, '.') . ' ' . count($workouts->get_by_date_range(2, '2026-10-15', '2026-10-21')) . " workout(s)\n";
attempt('stats: user #999', fn() => $workouts->get_stats_by_user(999));

echo "\n=== WorkoutExerciseService ===\n";
if ($new_workout) {
    $w = $new_workout['id'];
    attempt('add: Squat without weight', fn() => $workout_exercises->add(['workout_id' => $w, 'exercise_id' => 3, 'sets' => 3, 'reps' => 5]));
    attempt('add: Plank with reps AND duration', fn() => $workout_exercises->add(['workout_id' => $w, 'exercise_id' => 14, 'sets' => 3, 'reps' => 10, 'duration_seconds' => 60]));
    attempt('add: Running without duration', fn() => $workout_exercises->add(['workout_id' => $w, 'exercise_id' => 16, 'distance_km' => 5]));
    attempt('add: Squat with 0 sets', fn() => $workout_exercises->add(['workout_id' => $w, 'exercise_id' => 3, 'sets' => 0, 'reps' => 5, 'weight_kg' => 100]));
    attempt('add: exercise #999', fn() => $workout_exercises->add(['workout_id' => $w, 'exercise_id' => 999, 'sets' => 3]));
    attempt('add: Squat 3x5 100 kg', fn() => $workout_exercises->add(['workout_id' => $w, 'exercise_id' => 3, 'sets' => 3, 'reps' => 5, 'weight_kg' => 100]));
    $run = attempt('add: Running 30 min 5 km (sends sets 4)', fn() => $workout_exercises->add(['workout_id' => $w, 'exercise_id' => 16, 'duration_seconds' => 1800, 'distance_km' => 5, 'sets' => 4]));
    if ($run) {
        echo "    sets saved for Running: " . var_export($run['sets'], true) . "\n";
    }
    echo str_pad('exercises of the new workout', 55, '.') . ' ' . implode(', ', array_column($workout_exercises->get_by_workout($w), 'exercise_name')) . "\n";
}
attempt('by workout #999', fn() => $workout_exercises->get_by_workout(999));

echo "\n=== PersonalRecordService ===\n";
attempt('add: Squat 100 kg x 5 (current: 110 kg x 5)', fn() => $records->add(['user_id' => 2, 'exercise_id' => 3, 'max_weight_kg' => 100, 'reps_achieved' => 5, 'achieved_date' => $today]));
attempt('add: Squat 110 kg x 5 (same as current)', fn() => $records->add(['user_id' => 2, 'exercise_id' => 3, 'max_weight_kg' => 110, 'reps_achieved' => 5, 'achieved_date' => $today]));
attempt('add: Running 5 km in 1900 s (slower)', fn() => $records->add(['user_id' => 2, 'exercise_id' => 16, 'duration_seconds' => 1900, 'distance_km' => 5, 'achieved_date' => $today]));
attempt('add: Squat without reps', fn() => $records->add(['user_id' => 2, 'exercise_id' => 3, 'max_weight_kg' => 120, 'achieved_date' => $today]));
attempt('add: Squat tomorrow', fn() => $records->add(['user_id' => 2, 'exercise_id' => 3, 'max_weight_kg' => 120, 'reps_achieved' => 5, 'achieved_date' => $tomorrow]));
$new_records = [];
$new_records[] = attempt('add: Squat 110 kg x 6 (more reps)', fn() => $records->add(['user_id' => 2, 'exercise_id' => 3, 'max_weight_kg' => 110, 'reps_achieved' => 6, 'achieved_date' => $today]));
$new_records[] = attempt('add: Running 5 km in 1750 s (faster)', fn() => $records->add(['user_id' => 2, 'exercise_id' => 16, 'duration_seconds' => 1750, 'distance_km' => 5, 'achieved_date' => $today]));
$new_records[] = attempt('add: first Plank record, 90 s', fn() => $records->add(['user_id' => 2, 'exercise_id' => 14, 'duration_seconds' => 90, 'achieved_date' => $today]));
attempt('records of user #999', fn() => $records->get_by_user(999));

echo "\n=== clean-up ===\n";
foreach ($new_records as $record) {
    if ($record) attempt("delete record #{$record['id']}", fn() => $records->delete($record['id']));
}
// Deleting the workout also deletes its exercises (ON DELETE CASCADE)
if ($new_workout) attempt('delete the test workout', fn() => $workouts->delete($new_workout['id']));
if ($new_exercise) attempt('delete Test Hold (not used)', fn() => $exercises->delete($new_exercise['id']));
if ($new_category) attempt('delete Test Category (now empty)', fn() => $categories->delete($new_category['id']));
if ($new_user) attempt('delete the test user', fn() => $users->delete($new_user['id']));
attempt('get the deleted test user', fn() => $users->get_by_id($new_user['id'] ?? 0));
