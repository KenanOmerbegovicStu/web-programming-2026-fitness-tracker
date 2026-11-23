<?php
require __DIR__ . '/vendor/autoload.php';     // loads FlightPHP (installed by Composer)

require_once __DIR__ . '/utils/Logger.php';
require_once __DIR__ . '/rest/services/UserService.php';
require_once __DIR__ . '/rest/services/ExerciseCategoryService.php';
require_once __DIR__ . '/rest/services/ExerciseService.php';
require_once __DIR__ . '/rest/services/WorkoutService.php';
require_once __DIR__ . '/rest/services/WorkoutExerciseService.php';
require_once __DIR__ . '/rest/services/PersonalRecordService.php';
require_once __DIR__ . '/rest/services/WgerService.php';

// Register every service ONCE - every route can then call e.g. Flight::workout_service()
Flight::register('user_service', 'UserService');
Flight::register('exercise_category_service', 'ExerciseCategoryService');
Flight::register('exercise_service', 'ExerciseService');
Flight::register('workout_service', 'WorkoutService');
Flight::register('workout_exercise_service', 'WorkoutExerciseService');
Flight::register('personal_record_service', 'PersonalRecordService');
Flight::register('wger_service', 'WgerService');

/* ONE place that turns every exception into a JSON answer: {"status": 404, "error": "Workout not found."} */
Flight::map('error', function (Throwable $e) {
    if ($e instanceof HttpException) {          // thrown by a service: 400, 404, 503 ...
        $status = $e->get_status();
        $message = $e->getMessage();
    } else {                                    // PDOException, a bug ...
        $status = 500;
        $message = 'Something went wrong on the server. Please try again later.';
        // The details go only to the error log - never to the client
        $request = Flight::request();
        Logger::error("{$request->method} " . strtok($request->url, '?') . " 500 " . get_class($e) . ": "
            . $e->getMessage() . " (" . basename($e->getFile()) . ":" . $e->getLine() . ")");
    }
    Flight::response()
        ->clearBody()
        ->status($status)
        ->header('Content-Type', 'application/json')
        ->write(json_encode(['status' => $status, 'error' => $message]))
        ->send();
});

/* A URL that matches no route, e.g. GET /workouts/abc */
Flight::map('notFound', function () {
    $request = Flight::request();
    $endpoint = $request->method . ' ' . strtok($request->url, '?');
    Flight::response()
        ->clearBody()
        ->status(404)
        ->header('Content-Type', 'application/json')
        ->write(json_encode(['status' => 404, 'error' => "Endpoint $endpoint does not exist."]))
        ->send();
});

Flight::route('GET /', function () {
    Flight::json(['app' => 'FitnessTracker API', 'message' => 'The backend is running.']);
});

require_once __DIR__ . '/rest/routes/UserRoutes.php';
require_once __DIR__ . '/rest/routes/ExerciseCategoryRoutes.php';
require_once __DIR__ . '/rest/routes/ExerciseRoutes.php';
require_once __DIR__ . '/rest/routes/WorkoutRoutes.php';
require_once __DIR__ . '/rest/routes/WorkoutExerciseRoutes.php';
require_once __DIR__ . '/rest/routes/PersonalRecordRoutes.php';
require_once __DIR__ . '/rest/routes/ExternalRoutes.php';

Flight::start();                                // always the LAST line
