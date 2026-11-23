<?php
// Query parameters: ?search=press, ?muscle_group=legs or ?type=cardio (one at a time)
Flight::route('GET /exercises', function () {
    $query = Flight::request()->query;
    if (isset($query['search'])) {
        Flight::json(Flight::exercise_service()->search($query['search']));
    } elseif (isset($query['muscle_group'])) {
        Flight::json(Flight::exercise_service()->get_by_muscle_group($query['muscle_group']));
    } elseif (isset($query['type'])) {
        Flight::json(Flight::exercise_service()->get_by_type($query['type']));
    } else {
        Flight::json(Flight::exercise_service()->get_all());
    }
});

Flight::route('GET /exercises/@id:[0-9]+', function ($id) {
    Flight::json(Flight::exercise_service()->get_by_id($id));
});

/** Body: { "category_id": 1, "exercise_name": "Hip Thrust", "muscle_group": "legs", "description": "..." } */
Flight::route('POST /exercises', function () {
    $data = Flight::request()->data->getData();
    Flight::json(Flight::exercise_service()->add($data), 201);
});

Flight::route('PUT /exercises/@id:[0-9]+', function ($id) {
    $data = Flight::request()->data->getData();
    Flight::json(Flight::exercise_service()->update($data, $id));
});

Flight::route('DELETE /exercises/@id:[0-9]+', function ($id) {
    Flight::exercise_service()->delete($id);
    Flight::json(['message' => 'Exercise deleted.']);
});
