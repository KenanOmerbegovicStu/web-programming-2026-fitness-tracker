<?php
Flight::route('GET /exercise-categories', function () {
    Flight::json(Flight::exercise_category_service()->get_all());
});

Flight::route('GET /exercise-categories/@id:[0-9]+', function ($id) {
    Flight::json(Flight::exercise_category_service()->get_by_id($id));
});

/** Body: { "category_name": "Mobility", "exercise_type": "bodyweight", "description": "..." } */
Flight::route('POST /exercise-categories', function () {
    $data = Flight::request()->data->getData();
    Flight::json(Flight::exercise_category_service()->add($data), 201);
});

Flight::route('PUT /exercise-categories/@id:[0-9]+', function ($id) {
    $data = Flight::request()->data->getData();
    Flight::json(Flight::exercise_category_service()->update($data, $id));
});

Flight::route('DELETE /exercise-categories/@id:[0-9]+', function ($id) {
    Flight::exercise_category_service()->delete($id);
    Flight::json(['message' => 'Exercise category deleted.']);
});

// The exercises of one category
Flight::route('GET /exercise-categories/@id:[0-9]+/exercises', function ($id) {
    Flight::json(Flight::exercise_service()->get_by_category($id));
});
