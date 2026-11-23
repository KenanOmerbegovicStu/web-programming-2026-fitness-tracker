<?php
Flight::route('GET /external/exercises', function () {
    $search = Flight::request()->query['search'] ?? '';
    Flight::json(Flight::wger_service()->search($search));
});
