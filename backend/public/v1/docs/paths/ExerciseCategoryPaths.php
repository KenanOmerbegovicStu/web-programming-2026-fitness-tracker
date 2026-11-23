<?php
use OpenApi\Attributes as OA;

/* Documents rest/routes/ExerciseCategoryRoutes.php */
class ExerciseCategoryPaths
{
    #[OA\Get(
        path: '/exercise-categories',
        tags: ['exercise categories'],
        summary: 'List all categories with their number of exercises',
        responses: [
            new OA\Response(response: 200, description: 'All categories',
                content: new OA\JsonContent(type: 'array', items: new OA\Items(ref: '#/components/schemas/ExerciseCategory')))
        ]
    )]
    public function getExerciseCategories() {}

    #[OA\Get(
        path: '/exercise-categories/{id}',
        tags: ['exercise categories'],
        summary: 'Get one category',
        parameters: [new OA\Parameter(name: 'id', in: 'path', required: true, example: 1, schema: new OA\Schema(type: 'integer'))],
        responses: [
            new OA\Response(response: 200, description: 'The category', content: new OA\JsonContent(ref: '#/components/schemas/ExerciseCategory')),
            new OA\Response(response: 404, description: 'Exercise category not found', content: new OA\JsonContent(ref: '#/components/schemas/Error'))
        ]
    )]
    public function getExerciseCategoriesById() {}

    #[OA\Post(
        path: '/exercise-categories',
        tags: ['exercise categories'],
        summary: 'Create a category',
        description: 'The name must be unique.',
        requestBody: new OA\RequestBody(required: true, content: new OA\JsonContent(ref: '#/components/schemas/ExerciseCategoryInput')),
        responses: [
            new OA\Response(response: 201, description: 'The new category', content: new OA\JsonContent(ref: '#/components/schemas/ExerciseCategory')),
            new OA\Response(response: 400, description: 'Invalid data or the name already exists', content: new OA\JsonContent(ref: '#/components/schemas/Error'))
        ]
    )]
    public function postExerciseCategories() {}

    #[OA\Put(
        path: '/exercise-categories/{id}',
        tags: ['exercise categories'],
        summary: 'Update a category',
        description: 'exercise_type cannot change while exercises of the category are used in logged workouts or personal records.',
        parameters: [new OA\Parameter(name: 'id', in: 'path', required: true, example: 4, schema: new OA\Schema(type: 'integer'))],
        requestBody: new OA\RequestBody(required: true, content: new OA\JsonContent(ref: '#/components/schemas/ExerciseCategoryInput')),
        responses: [
            new OA\Response(response: 200, description: 'The updated category', content: new OA\JsonContent(ref: '#/components/schemas/ExerciseCategory')),
            new OA\Response(response: 400, description: 'Invalid data, the name already exists, or the type is in use',
                content: new OA\JsonContent(ref: '#/components/schemas/Error')),
            new OA\Response(response: 404, description: 'Exercise category not found', content: new OA\JsonContent(ref: '#/components/schemas/Error'))
        ]
    )]
    public function putExerciseCategoriesById() {}

    #[OA\Delete(
        path: '/exercise-categories/{id}',
        tags: ['exercise categories'],
        summary: 'Delete a category',
        description: 'Only an empty category can be deleted.',
        parameters: [new OA\Parameter(name: 'id', in: 'path', required: true, example: 4, schema: new OA\Schema(type: 'integer'))],
        responses: [
            new OA\Response(response: 200, description: 'Deleted', content: new OA\JsonContent(ref: '#/components/schemas/Message')),
            new OA\Response(response: 400, description: 'The category still contains exercises', content: new OA\JsonContent(ref: '#/components/schemas/Error')),
            new OA\Response(response: 404, description: 'Exercise category not found', content: new OA\JsonContent(ref: '#/components/schemas/Error'))
        ]
    )]
    public function deleteExerciseCategoriesById() {}

    #[OA\Get(
        path: '/exercise-categories/{id}/exercises',
        tags: ['exercise categories', 'exercises'],
        summary: 'Exercises of one category',
        parameters: [new OA\Parameter(name: 'id', in: 'path', required: true, example: 2, schema: new OA\Schema(type: 'integer'))],
        responses: [
            new OA\Response(response: 200, description: 'The exercises (an empty list for an empty category)',
                content: new OA\JsonContent(type: 'array', items: new OA\Items(ref: '#/components/schemas/Exercise'))),
            new OA\Response(response: 404, description: 'Exercise category not found', content: new OA\JsonContent(ref: '#/components/schemas/Error'))
        ]
    )]
    public function getExerciseCategoriesByIdExercises() {}
}
