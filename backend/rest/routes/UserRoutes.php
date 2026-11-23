<?php
// Until JWT exists (Milestone 4) every route is public; M4 adds the auth and role middleware.

Flight::route('GET /users', function () {
    Flight::json(Flight::user_service()->get_all());
});

Flight::route('GET /users/@id:[0-9]+', function ($id) {
    Flight::json(Flight::user_service()->get_by_id($id));
});

/** Body: { "email": "...", "full_name": "...", "password": "...", "fitness_goal": "strength", "experience_level": "beginner" } */
Flight::route('POST /users', function () {
    $data = Flight::request()->data->getData();
    Flight::json(Flight::user_service()->add($data), 201);
});

Flight::route('PUT /users/@id:[0-9]+', function ($id) {
    $data = Flight::request()->data->getData();
    Flight::json(Flight::user_service()->update($data, $id));
});

Flight::route('DELETE /users/@id:[0-9]+', function ($id) {
    Flight::user_service()->delete($id);
    Flight::json(['message' => 'User deleted.']);
});

// The workouts of one user: /users/2/workouts, ?limit=5 (dashboard), ?from=2026-10-01&to=2026-10-31 (history filter)
Flight::route('GET /users/@id:[0-9]+/workouts', function ($id) {
    $query = Flight::request()->query;
    if (isset($query['limit'])) {
        Flight::json(Flight::workout_service()->get_recent($id, $query['limit']));
    } elseif (isset($query['from']) || isset($query['to'])) {
        Flight::json(Flight::workout_service()->get_by_date_range($id, $query['from'] ?? '', $query['to'] ?? ''));
    } else {
        Flight::json(Flight::workout_service()->get_by_user($id));
    }
});

// Totals for the dashboard: number of workouts, minutes and calories
Flight::route('GET /users/@id:[0-9]+/stats', function ($id) {
    Flight::json(Flight::workout_service()->get_stats_by_user($id));
});

Flight::route('GET /users/@id:[0-9]+/personal-records', function ($id) {
    Flight::json(Flight::personal_record_service()->get_by_user($id));
});
