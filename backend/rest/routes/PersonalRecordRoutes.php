<?php
// All records of all users (admin view). One user's records: GET /users/@id/personal-records (UserRoutes.php)
Flight::route('GET /personal-records', function () {
    Flight::json(Flight::personal_record_service()->get_all());
});

Flight::route('GET /personal-records/@id:[0-9]+', function ($id) {
    Flight::json(Flight::personal_record_service()->get_by_id($id));
});

/** Body: { "user_id": 2, "exercise_id": 3, "max_weight_kg": 115, "reps_achieved": 5, "achieved_date": "2026-11-20" } */
Flight::route('POST /personal-records', function () {
    $data = Flight::request()->data->getData();
    Flight::json(Flight::personal_record_service()->add($data), 201);
});

Flight::route('PUT /personal-records/@id:[0-9]+', function ($id) {
    $data = Flight::request()->data->getData();
    Flight::json(Flight::personal_record_service()->update($data, $id));
});

Flight::route('DELETE /personal-records/@id:[0-9]+', function ($id) {
    Flight::personal_record_service()->delete($id);
    Flight::json(['message' => 'Personal record deleted.']);
});
