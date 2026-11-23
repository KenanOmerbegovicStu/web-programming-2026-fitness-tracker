<?php
use OpenApi\Attributes as OA;

/* Documents rest/routes/ExerciseRoutes.php */
class ExercisePaths
{
    #[OA\Get(
        path: '/exercises',
        tags: ['exercises'],
        summary: 'List, search or filter exercises',
        description: 'Use at most one query parameter. search wins over muscle_group, muscle_group over type.',
        parameters: [
            new OA\Parameter(name: 'search', in: 'query', required: false, description: 'Text in the name or muscle group (2-50 characters)',
                example: 'press', schema: new OA\Schema(type: 'string')),
            new OA\Parameter(name: 'muscle_group', in: 'query', required: false, description: 'Only this muscle group',
                example: 'legs', schema: new OA\Schema(type: 'string')),
            new OA\Parameter(name: 'type', in: 'query', required: false, description: 'Only this exercise type',
                example: 'cardio', schema: new OA\Schema(type: 'string', enum: ['weighted', 'bodyweight', 'cardio']))
        ],
        responses: [
            new OA\Response(response: 200, description: 'The exercises with category name and type',
                content: new OA\JsonContent(type: 'array', items: new OA\Items(ref: '#/components/schemas/Exercise'))),
            new OA\Response(response: 400, description: 'Invalid search text, muscle group or type', content: new OA\JsonContent(ref: '#/components/schemas/Error'))
        ]
    )]
    public function getExercises() {}

    #[OA\Get(
        path: '/exercises/{id}',
        tags: ['exercises'],
        summary: 'Get one exercise',
        parameters: [new OA\Parameter(name: 'id', in: 'path', required: true, example: 3, schema: new OA\Schema(type: 'integer'))],
        responses: [
            new OA\Response(response: 200, description: 'The exercise', content: new OA\JsonContent(ref: '#/components/schemas/Exercise')),
            new OA\Response(response: 404, description: 'Exercise not found', content: new OA\JsonContent(ref: '#/components/schemas/Error'))
        ]
    )]
    public function getExercisesById() {}

    #[OA\Post(
        path: '/exercises',
        tags: ['exercises'],
        summary: 'Add an exercise to the library',
        description: 'The category must exist and the name must be unique.',
        requestBody: new OA\RequestBody(required: true, content: new OA\JsonContent(ref: '#/components/schemas/ExerciseInput')),
        responses: [
            new OA\Response(response: 201, description: 'The new exercise', content: new OA\JsonContent(ref: '#/components/schemas/Exercise')),
            new OA\Response(response: 400, description: 'Invalid data, unknown category or the name already exists',
                content: new OA\JsonContent(ref: '#/components/schemas/Error'))
        ]
    )]
    public function postExercises() {}

    #[OA\Put(
        path: '/exercises/{id}',
        tags: ['exercises'],
        summary: 'Update an exercise',
        parameters: [new OA\Parameter(name: 'id', in: 'path', required: true, example: 3, schema: new OA\Schema(type: 'integer'))],
        requestBody: new OA\RequestBody(required: true, content: new OA\JsonContent(ref: '#/components/schemas/ExerciseInput')),
        responses: [
            new OA\Response(response: 200, description: 'The updated exercise', content: new OA\JsonContent(ref: '#/components/schemas/Exercise')),
            new OA\Response(response: 400, description: 'Invalid data, unknown category or the name already exists',
                content: new OA\JsonContent(ref: '#/components/schemas/Error')),
            new OA\Response(response: 404, description: 'Exercise not found', content: new OA\JsonContent(ref: '#/components/schemas/Error'))
        ]
    )]
    public function putExercisesById() {}

    #[OA\Delete(
        path: '/exercises/{id}',
        tags: ['exercises'],
        summary: 'Delete an exercise',
        description: 'An exercise that is used in logged workouts or personal records cannot be deleted.',
        parameters: [new OA\Parameter(name: 'id', in: 'path', required: true, example: 18, schema: new OA\Schema(type: 'integer'))],
        responses: [
            new OA\Response(response: 200, description: 'Deleted', content: new OA\JsonContent(ref: '#/components/schemas/Message')),
            new OA\Response(response: 400, description: 'The exercise is in use', content: new OA\JsonContent(ref: '#/components/schemas/Error')),
            new OA\Response(response: 404, description: 'Exercise not found', content: new OA\JsonContent(ref: '#/components/schemas/Error'))
        ]
    )]
    public function deleteExercisesById() {}
}
