<?php
// The list of one workout's exercises is GET /workouts/@id/exercises (WorkoutRoutes.php)

Flight::route('GET /workout-exercises/@id:[0-9]+', function ($id) {
    Flight::json(Flight::workout_exercise_service()->get_by_id($id));
});

/**
 * Body (weighted):   { "workout_id": 1, "exercise_id": 3, "sets": 5, "reps": 5, "weight_kg": 110 }
 * Body (bodyweight): { "workout_id": 4, "exercise_id": 14, "sets": 3, "duration_seconds": 60 }
 * Body (cardio):     { "workout_id": 5, "exercise_id": 16, "duration_seconds": 1800, "distance_km": 5 }
 */
Flight::route('POST /workout-exercises', function () {
    $data = Flight::request()->data->getData();
    Flight::json(Flight::workout_exercise_service()->add($data), 201);
});

Flight::route('PUT /workout-exercises/@id:[0-9]+', function ($id) {
    $data = Flight::request()->data->getData();
    Flight::json(Flight::workout_exercise_service()->update($data, $id));
});

Flight::route('DELETE /workout-exercises/@id:[0-9]+', function ($id) {
    Flight::workout_exercise_service()->delete($id);
    Flight::json(['message' => 'Workout exercise deleted.']);
});
