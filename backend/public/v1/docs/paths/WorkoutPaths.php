<?php
use OpenApi\Attributes as OA;

/* Documents rest/routes/WorkoutRoutes.php */
class WorkoutPaths
{
    #[OA\Get(
        path: '/workouts',
        tags: ['workouts'],
        summary: 'List the workouts of all users',
        description: 'The workouts of one user: GET /users/{id}/workouts.',
        responses: [
            new OA\Response(response: 200, description: 'All workouts',
                content: new OA\JsonContent(type: 'array', items: new OA\Items(ref: '#/components/schemas/Workout')))
        ]
    )]
    public function getWorkouts() {}

    #[OA\Get(
        path: '/workouts/{id}',
        tags: ['workouts'],
        summary: 'Get one workout',
        parameters: [new OA\Parameter(name: 'id', in: 'path', required: true, example: 2, schema: new OA\Schema(type: 'integer'))],
        responses: [
            new OA\Response(response: 200, description: 'The workout', content: new OA\JsonContent(ref: '#/components/schemas/Workout')),
            new OA\Response(response: 404, description: 'Workout not found', content: new OA\JsonContent(ref: '#/components/schemas/Error'))
        ]
    )]
    public function getWorkoutsById() {}

    #[OA\Post(
        path: '/workouts',
        tags: ['workouts'],
        summary: 'Log a workout',
        description: 'The user must exist and the date cannot be in the future. Add its exercises with POST /workout-exercises.',
        requestBody: new OA\RequestBody(required: true, content: new OA\JsonContent(ref: '#/components/schemas/WorkoutInput')),
        responses: [
            new OA\Response(response: 201, description: 'The new workout', content: new OA\JsonContent(ref: '#/components/schemas/Workout')),
            new OA\Response(response: 400, description: 'Invalid data, unknown user or a date in the future', content: new OA\JsonContent(ref: '#/components/schemas/Error'))
        ]
    )]
    public function postWorkouts() {}

    #[OA\Put(
        path: '/workouts/{id}',
        tags: ['workouts'],
        summary: 'Update a workout',
        parameters: [new OA\Parameter(name: 'id', in: 'path', required: true, example: 2, schema: new OA\Schema(type: 'integer'))],
        requestBody: new OA\RequestBody(required: true, content: new OA\JsonContent(ref: '#/components/schemas/WorkoutInput')),
        responses: [
            new OA\Response(response: 200, description: 'The updated workout', content: new OA\JsonContent(ref: '#/components/schemas/Workout')),
            new OA\Response(response: 400, description: 'Invalid data, unknown user or a date in the future', content: new OA\JsonContent(ref: '#/components/schemas/Error')),
            new OA\Response(response: 404, description: 'Workout not found', content: new OA\JsonContent(ref: '#/components/schemas/Error'))
        ]
    )]
    public function putWorkoutsById() {}

    #[OA\Delete(
        path: '/workouts/{id}',
        tags: ['workouts'],
        summary: 'Delete a workout and its exercises',
        parameters: [new OA\Parameter(name: 'id', in: 'path', required: true, example: 7, schema: new OA\Schema(type: 'integer'))],
        responses: [
            new OA\Response(response: 200, description: 'Deleted', content: new OA\JsonContent(ref: '#/components/schemas/Message')),
            new OA\Response(response: 404, description: 'Workout not found', content: new OA\JsonContent(ref: '#/components/schemas/Error'))
        ]
    )]
    public function deleteWorkoutsById() {}

    #[OA\Get(
        path: '/workouts/{id}/exercises',
        tags: ['workouts', 'workout exercises'],
        summary: 'Exercises of one workout',
        parameters: [new OA\Parameter(name: 'id', in: 'path', required: true, example: 4, schema: new OA\Schema(type: 'integer'))],
        responses: [
            new OA\Response(response: 200, description: 'The exercises with name, type and muscle group',
                content: new OA\JsonContent(type: 'array', items: new OA\Items(ref: '#/components/schemas/WorkoutExercise'))),
            new OA\Response(response: 404, description: 'Workout not found', content: new OA\JsonContent(ref: '#/components/schemas/Error'))
        ]
    )]
    public function getWorkoutsByIdExercises() {}
}
