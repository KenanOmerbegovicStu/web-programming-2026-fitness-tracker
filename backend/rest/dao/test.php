<?php
// Calls every DAO method at least once. Run from the backend folder:  php rest/dao/test.php
// Everything the script adds is deleted again, so it can be run as often as you like.
require_once __DIR__ . '/UserDao.php';
require_once __DIR__ . '/ExerciseCategoryDao.php';
require_once __DIR__ . '/ExerciseDao.php';
require_once __DIR__ . '/WorkoutDao.php';
require_once __DIR__ . '/WorkoutExerciseDao.php';
require_once __DIR__ . '/PersonalRecordDao.php';

function names($rows, $column)
{
    return implode(', ', array_column($rows, $column));
}

/** One workout exercise as text; which columns are used depends on the category's exercise_type. */
function describe($row)
{
    if ($row['exercise_type'] === 'cardio') {
        return "{$row['exercise_name']} " . ($row['duration_seconds'] / 60) . " min"
             . ($row['distance_km'] !== null ? " {$row['distance_km']} km" : "");
    }
    $amount = $row['reps'] !== null ? "{$row['sets']}x{$row['reps']}" : "{$row['sets']}x{$row['duration_seconds']}s";
    if ($row['weight_kg'] === null) {
        return "{$row['exercise_name']} $amount bodyweight";
    }
    $weight = $row['exercise_type'] === 'bodyweight' ? "+{$row['weight_kg']} kg" : "{$row['weight_kg']} kg";
    return "{$row['exercise_name']} $amount $weight";
}

$users      = new UserDao();
$categories = new ExerciseCategoryDao();
$exercises  = new ExerciseDao();
$workouts   = new WorkoutDao();
$items      = new WorkoutExerciseDao();
$records    = new PersonalRecordDao();

echo "=== READ ===\n";
echo "Users: " . count($users->get_all()) . ", categories: " . count($categories->get_all())
   . ", exercises: " . count($exercises->get_all()) . "\n";

$all_users = $users->get_all();
echo "get_all() of users has password_hash: " . (isset($all_users[0]['password_hash']) ? "YES (BAD)" : "no") . "\n";
echo "get_by_id(2): " . $users->get_by_id(2)['full_name'] . ", password_hash: "
   . (array_key_exists('password_hash', $users->get_by_id(2)) ? "YES (BAD)" : "no") . "\n";
echo "get_by_email(keno): hash present for login check: "
   . (password_verify('User123!', $users->get_by_email('keno.test@example.com')['password_hash']) ? "yes, User123! matches" : "NO") . "\n";
echo "get_by_role('admin'): " . names($users->get_by_role('admin'), 'full_name') . "\n";

echo "get_by_name('Cardio'): id " . $categories->get_by_name('Cardio')['id'] . "\n";
foreach ($categories->get_with_exercise_count() as $c) {
    echo "  {$c['category_name']} ({$c['exercise_type']}): {$c['exercise_count']} exercises\n";
}

echo "Exercise get_by_id(1): " . $exercises->get_by_id(1)['exercise_name'] . " (" . $exercises->get_by_id(1)['category_name'] . ")\n";
echo "get_by_category(3): " . names($exercises->get_by_category(3), 'exercise_name') . "\n";
echo "get_by_muscle_group('back'): " . names($exercises->get_by_muscle_group('back'), 'exercise_name') . "\n";
echo "get_by_type('cardio'): " . names($exercises->get_by_type('cardio'), 'exercise_name') . "\n";
echo "get_by_type('bodyweight'): " . names($exercises->get_by_type('bodyweight'), 'exercise_name') . "\n";
echo "search('press'): " . names($exercises->search('press'), 'exercise_name') . "\n";
echo "search(\"' OR 1=1 -- \"): " . count($exercises->search("' OR 1=1 -- ")) . " row(s)\n";

echo "Workouts of user 2: " . count($workouts->get_by_user(2)) . "\n";
foreach ($workouts->get_recent(2, 3) as $w) {
    echo "  {$w['workout_date']} {$w['workout_type']} - {$w['exercise_count']} exercises\n";
}
echo "get_by_date_range(2, 2026-10-15, 2026-10-21): " . names($workouts->get_by_date_range(2, '2026-10-15', '2026-10-21'), 'workout_type') . "\n";
$stats = $workouts->get_stats_by_user(2);
echo "get_stats_by_user(2): {$stats['total_workouts']} workouts, {$stats['total_minutes']} min, {$stats['total_calories']} kcal\n";
$stats = $workouts->get_stats_by_user(6);
echo "get_stats_by_user(6): {$stats['total_workouts']} workouts, {$stats['total_minutes']} min, {$stats['total_calories']} kcal\n";

foreach ([1, 4, 5, 8] as $workout_id) {
    echo "get_by_workout($workout_id): " . implode('; ', array_map('describe', $items->get_by_workout($workout_id))) . "\n";
}

echo "Records of user 2: " . count($records->get_by_user(2)) . "\n";
$best = $records->get_by_user_and_exercise(2, 6);
echo "get_by_user_and_exercise(2, 6): {$best['exercise_name']} {$best['max_weight_kg']} kg x {$best['reps_achieved']}\n";
$best = $records->get_by_user_and_exercise(2, 13);
echo "get_by_user_and_exercise(2, 13): {$best['exercise_name']} +{$best['max_weight_kg']} kg x {$best['reps_achieved']} ({$best['exercise_type']})\n";
$best = $records->get_by_user_and_exercise(2, 16);
echo "get_by_user_and_exercise(2, 16): {$best['exercise_name']} {$best['distance_km']} km in " . ($best['duration_seconds'] / 60) . " min ({$best['exercise_type']})\n";
echo "get_recent(2, 3): " . names($records->get_recent(2, 3), 'exercise_name') . "\n";

echo "\n=== CREATE / UPDATE / DELETE (ExerciseCategoryDao) ===\n";
// "hacker" is NOT a column of exercise_categories, so only_columns() drops it
$category = $categories->add(['category_name' => 'Test category', 'exercise_type' => 'weighted', 'description' => 'Created by test.php', 'hacker' => 'DROP TABLE']);
echo "Added: #{$category['id']} {$category['category_name']}, hacker key: " . (isset($category['hacker']) ? "PRESENT (BAD)" : "dropped") . "\n";
$changed = $categories->update(['description' => 'Updated description'], $category['id']);
echo "Updated: $changed row(s), description now: " . $categories->get_by_id($category['id'])['description'] . "\n";
$categories->delete($category['id']);
echo "Deleted: get_by_id() returns " . var_export($categories->get_by_id($category['id']), true) . "\n";

echo "\n=== CRUD over related tables ===\n";
$user = $users->add([
    'email' => 'crud.test@example.com',
    'password_hash' => password_hash('Test123!', PASSWORD_BCRYPT),
    'full_name' => 'Crud Test',
    'role' => 'user'
]);
echo "UserDao add(): #{$user['id']}, password_hash returned: " . (isset($user['password_hash']) ? "YES (BAD)" : "no") . "\n";
$users->update(['experience_level' => 'beginner'], $user['id']);
echo "UserDao update(): experience_level = " . $users->get_by_id($user['id'])['experience_level'] . "\n";

$workout = $workouts->add(['user_id' => $user['id'], 'workout_date' => '2026-11-01', 'workout_type' => 'Test Day', 'duration_minutes' => 30]);
$item = $items->add(['workout_id' => $workout['id'], 'exercise_id' => 1, 'sets' => 3, 'reps' => 10, 'weight_kg' => 40]);
$items->update(['reps' => 12], $item['id']);
echo "WorkoutExerciseDao update(): reps = " . $items->get_by_id($item['id'])['reps'] . "\n";
$record = $records->add(['user_id' => $user['id'], 'exercise_id' => 1, 'max_weight_kg' => 40, 'reps_achieved' => 10, 'achieved_date' => '2026-11-01']);
$records->update(['max_weight_kg' => 42.5], $record['id']);
echo "PersonalRecordDao update(): max_weight_kg = " . $records->get_by_id($record['id'])['max_weight_kg'] . "\n";

$exercise = $exercises->add(['category_id' => 4, 'exercise_name' => 'Test Stretch', 'muscle_group' => 'legs']);
$exercises->update(['muscle_group' => 'back'], $exercise['id']);
echo "ExerciseDao update(): muscle_group = " . $exercises->get_by_id($exercise['id'])['muscle_group'] . "\n";
$exercises->delete($exercise['id']);
echo "ExerciseDao delete(): " . var_export($exercises->get_by_id($exercise['id']), true) . "\n";

$items->delete($item['id']);
echo "WorkoutExerciseDao delete(): " . var_export($items->get_by_id($item['id']), true) . "\n";
$item = $items->add(['workout_id' => $workout['id'], 'exercise_id' => 3, 'sets' => 3, 'reps' => 5, 'weight_kg' => 60]);
// cardio: duration and distance, no sets or reps
$run = $items->add(['workout_id' => $workout['id'], 'exercise_id' => 16, 'duration_seconds' => 1200, 'distance_km' => 3.5]);
$run_row = $items->get_by_id($run['id']);
echo "Cardio row: sets " . var_export($run_row['sets'], true) . ", reps " . var_export($run_row['reps'], true)
   . ", {$run_row['duration_seconds']} s, {$run_row['distance_km']} km\n";
// timed hold: sets and duration, no reps
$hold = $items->add(['workout_id' => $workout['id'], 'exercise_id' => 14, 'sets' => 2, 'duration_seconds' => 45]);
echo "Plank row: " . $items->get_by_id($hold['id'])['sets'] . " x " . $items->get_by_id($hold['id'])['duration_seconds'] . " s\n";
$cardio_pr = $records->add(['user_id' => $user['id'], 'exercise_id' => 16, 'duration_seconds' => 1200, 'distance_km' => 3.5, 'achieved_date' => '2026-11-01']);
echo "Cardio PR: max_weight_kg " . var_export($records->get_by_id($cardio_pr['id'])['max_weight_kg'], true) . ", "
   . $records->get_by_id($cardio_pr['id'])['distance_km'] . " km\n";
$workouts->update(['calories' => 250], $workout['id']);
echo "WorkoutDao update(): calories = " . $workouts->get_by_id($workout['id'])['calories'] . "\n";

// ON DELETE CASCADE: deleting the user also deletes the workout, its exercises and the record
$users->delete($user['id']);
echo "UserDao delete() + CASCADE: user " . var_export($users->get_by_id($user['id']), true)
   . ", workout " . var_export($workouts->get_by_id($workout['id']), true)
   . ", workout exercise " . var_export($items->get_by_id($item['id']), true)
   . ", record " . var_export($records->get_by_id($record['id']), true)
   . ", cardio row " . var_export($items->get_by_id($run['id']), true) . "\n";

echo "\n=== CONSTRAINTS ===\n";
try {
    $exercises->add(['category_id' => 999, 'exercise_name' => 'Ghost exercise']);
    echo "FK check: NOT refused (BAD)\n";
} catch (PDOException $e) {
    echo "FK check: refused (category 999 does not exist)\n";
}
try {
    $categories->delete(1);
    echo "RESTRICT check: NOT refused (BAD)\n";
} catch (PDOException $e) {
    echo "RESTRICT check: refused (category 1 still has exercises)\n";
}
try {
    $users->add(['email' => 'keno.test@example.com', 'password_hash' => 'x', 'full_name' => 'Duplicate']);
    echo "UNIQUE check: NOT refused (BAD)\n";
} catch (PDOException $e) {
    echo "UNIQUE check: refused (e-mail already exists)\n";
}
try {
    $items->add(['workout_id' => 1, 'exercise_id' => 6, 'sets' => 3, 'weight_kg' => 100]);
    echo "CHECK reps-or-duration: NOT refused (BAD)\n";
} catch (PDOException $e) {
    echo "CHECK reps-or-duration: refused (row has neither reps nor duration)\n";
}
try {
    $categories->add(['category_name' => 'Wrong type', 'exercise_type' => 'yoga']);
    echo "CHECK exercise_type: NOT refused (BAD)\n";
} catch (PDOException $e) {
    echo "CHECK exercise_type: refused ('yoga' is not weighted, bodyweight or cardio)\n";
}
