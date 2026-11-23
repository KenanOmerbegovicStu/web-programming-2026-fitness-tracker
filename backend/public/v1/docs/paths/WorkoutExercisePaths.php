<?php
use OpenApi\Attributes as OA;

/* Documents rest/routes/WorkoutExerciseRoutes.php */
class WorkoutExercisePaths
{
    #[OA\Get(
        path: '/workout-exercises/{id}',
        tags: ['workout exercises'],
        summary: 'Get one exercise entry of a workout',
        parameters: [new OA\Parameter(name: 'id', in: 'path', required: true, example: 4, schema: new OA\Schema(type: 'integer'))],
        responses: [
            new OA\Response(response: 200, description: 'The entry', content: new OA\JsonContent(ref: '#/components/schemas/WorkoutExercise')),
            new OA\Response(response: 404, description: 'Workout exercise not found', content: new OA\JsonContent(ref: '#/components/schemas/Error'))
        ]
    )]
    public function getWorkoutExercisesById() {}

    #[OA\Post(
        path: '/workout-exercises',
        tags: ['workout exercises'],
        summary: 'Add an exercise to a workout',
        description: 'The required fields depend on the exercise type (see WorkoutExerciseInput). Fields the type does not use are saved as null.',
        requestBody: new OA\RequestBody(required: true, content: new OA\JsonContent(ref: '#/components/schemas/WorkoutExerciseInput')),
        responses: [
            new OA\Response(response: 201, description: 'The new entry', content: new OA\JsonContent(ref: '#/components/schemas/WorkoutExercise')),
            new OA\Response(response: 400, description: 'Unknown workout or exercise, or the fields do not fit the exercise type',
                content: new OA\JsonContent(ref: '#/components/schemas/Error'))
        ]
    )]
    public function postWorkoutExercises() {}

    #[OA\Put(
        path: '/workout-exercises/{id}',
        tags: ['workout exercises'],
        summary: 'Update an exercise entry of a workout',
        parameters: [new OA\Parameter(name: 'id', in: 'path', required: true, example: 4, schema: new OA\Schema(type: 'integer'))],
        requestBody: new OA\RequestBody(required: true, content: new OA\JsonContent(ref: '#/components/schemas/WorkoutExerciseInput')),
        responses: [
            new OA\Response(response: 200, description: 'The updated entry', content: new OA\JsonContent(ref: '#/components/schemas/WorkoutExercise')),
            new OA\Response(response: 400, description: 'Unknown workout or exercise, or the fields do not fit the exercise type',
                content: new OA\JsonContent(ref: '#/components/schemas/Error')),
            new OA\Response(response: 404, description: 'Workout exercise not found', content: new OA\JsonContent(ref: '#/components/schemas/Error'))
        ]
    )]
    public function putWorkoutExercisesById() {}

    #[OA\Delete(
        path: '/workout-exercises/{id}',
        tags: ['workout exercises'],
        summary: 'Remove an exercise from a workout',
        parameters: [new OA\Parameter(name: 'id', in: 'path', required: true, example: 4, schema: new OA\Schema(type: 'integer'))],
        responses: [
            new OA\Response(response: 200, description: 'Deleted', content: new OA\JsonContent(ref: '#/components/schemas/Message')),
            new OA\Response(response: 404, description: 'Workout exercise not found', content: new OA\JsonContent(ref: '#/components/schemas/Error'))
        ]
    )]
    public function deleteWorkoutExercisesById() {}
}
