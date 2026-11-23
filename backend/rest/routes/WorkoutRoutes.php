<?php
// All workouts of all users (admin view). One user's workouts: GET /users/@id/workouts (UserRoutes.php)
Flight::route('GET /workouts', function () {
    Flight::json(Flight::workout_service()->get_all());
});

Flight::route('GET /workouts/@id:[0-9]+', function ($id) {
    Flight::json(Flight::workout_service()->get_by_id($id));
});

/** Body: { "user_id": 2, "workout_date": "2026-11-20", "workout_type": "Leg Day", "duration_minutes": 60, "calories": 450, "notes": "" } */
Flight::route('POST /workouts', function () {
    $data = Flight::request()->data->getData();
    Flight::json(Flight::workout_service()->add($data), 201);
});

Flight::route('PUT /workouts/@id:[0-9]+', function ($id) {
    $data = Flight::request()->data->getData();
    Flight::json(Flight::workout_service()->update($data, $id));
});

// Its exercises are deleted with it (ON DELETE CASCADE)
Flight::route('DELETE /workouts/@id:[0-9]+', function ($id) {
    Flight::workout_service()->delete($id);
    Flight::json(['message' => 'Workout deleted.']);
});

// The exercises of one workout
Flight::route('GET /workouts/@id:[0-9]+/exercises', function ($id) {
    Flight::json(Flight::workout_exercise_service()->get_by_workout($id));
});
