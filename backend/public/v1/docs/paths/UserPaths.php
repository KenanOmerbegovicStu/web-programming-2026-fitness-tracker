<?php
use OpenApi\Attributes as OA;

/* Documents rest/routes/UserRoutes.php - one empty method per route. */
class UserPaths
{
    #[OA\Get(
        path: '/users',
        tags: ['users'],
        summary: 'List all users',
        responses: [
            new OA\Response(response: 200, description: 'All users (without password hashes)',
                content: new OA\JsonContent(type: 'array', items: new OA\Items(ref: '#/components/schemas/User')))
        ]
    )]
    public function getUsers() {}

    #[OA\Get(
        path: '/users/{id}',
        tags: ['users'],
        summary: 'Get one user',
        parameters: [new OA\Parameter(name: 'id', in: 'path', required: true, example: 2, schema: new OA\Schema(type: 'integer'))],
        responses: [
            new OA\Response(response: 200, description: 'The user', content: new OA\JsonContent(ref: '#/components/schemas/User')),
            new OA\Response(response: 404, description: 'User not found', content: new OA\JsonContent(ref: '#/components/schemas/Error'))
        ]
    )]
    public function getUsersById() {}

    #[OA\Post(
        path: '/users',
        tags: ['users'],
        summary: 'Create a user',
        description: 'The password is required and stored as a bcrypt hash. The email must be unique (case does not matter).',
        requestBody: new OA\RequestBody(required: true, content: new OA\JsonContent(ref: '#/components/schemas/UserInput')),
        responses: [
            new OA\Response(response: 201, description: 'The new user', content: new OA\JsonContent(ref: '#/components/schemas/User')),
            new OA\Response(response: 400, description: 'Invalid data, weak password or the email already exists',
                content: new OA\JsonContent(ref: '#/components/schemas/Error'))
        ]
    )]
    public function postUsers() {}

    #[OA\Put(
        path: '/users/{id}',
        tags: ['users'],
        summary: 'Update a user',
        description: 'Send password only to change it. Without role the current role is kept. The only admin cannot become a user.',
        parameters: [new OA\Parameter(name: 'id', in: 'path', required: true, example: 2, schema: new OA\Schema(type: 'integer'))],
        requestBody: new OA\RequestBody(required: true, content: new OA\JsonContent(ref: '#/components/schemas/UserInput')),
        responses: [
            new OA\Response(response: 200, description: 'The updated user', content: new OA\JsonContent(ref: '#/components/schemas/User')),
            new OA\Response(response: 400, description: 'Invalid data, the email already exists, or the last admin would lose the role',
                content: new OA\JsonContent(ref: '#/components/schemas/Error')),
            new OA\Response(response: 404, description: 'User not found', content: new OA\JsonContent(ref: '#/components/schemas/Error'))
        ]
    )]
    public function putUsersById() {}

    #[OA\Delete(
        path: '/users/{id}',
        tags: ['users'],
        summary: 'Delete a user',
        description: 'Their workouts and personal records are deleted too. The only admin cannot be deleted.',
        parameters: [new OA\Parameter(name: 'id', in: 'path', required: true, example: 6, schema: new OA\Schema(type: 'integer'))],
        responses: [
            new OA\Response(response: 200, description: 'Deleted', content: new OA\JsonContent(ref: '#/components/schemas/Message')),
            new OA\Response(response: 400, description: 'The only admin cannot be deleted', content: new OA\JsonContent(ref: '#/components/schemas/Error')),
            new OA\Response(response: 404, description: 'User not found', content: new OA\JsonContent(ref: '#/components/schemas/Error'))
        ]
    )]
    public function deleteUsersById() {}

    #[OA\Get(
        path: '/users/{id}/workouts',
        tags: ['users', 'workouts'],
        summary: 'Workouts of one user (all, the latest, or a date range)',
        description: 'Without query parameters: all workouts, newest first. With limit: the latest ones (dashboard). With from and to: a date range (both included).',
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, example: 2, schema: new OA\Schema(type: 'integer')),
            new OA\Parameter(name: 'limit', in: 'query', required: false, description: 'Only the latest workouts (1-20)', example: 5,
                schema: new OA\Schema(type: 'integer')),
            new OA\Parameter(name: 'from', in: 'query', required: false, description: 'First date (YYYY-MM-DD), used together with to',
                example: '2026-10-15', schema: new OA\Schema(type: 'string', format: 'date')),
            new OA\Parameter(name: 'to', in: 'query', required: false, description: 'Last date (YYYY-MM-DD), used together with from',
                example: '2026-10-21', schema: new OA\Schema(type: 'string', format: 'date'))
        ],
        responses: [
            new OA\Response(response: 200, description: 'The workouts with their exercise_count',
                content: new OA\JsonContent(type: 'array', items: new OA\Items(ref: '#/components/schemas/Workout'))),
            new OA\Response(response: 400, description: 'Invalid limit or dates', content: new OA\JsonContent(ref: '#/components/schemas/Error')),
            new OA\Response(response: 404, description: 'User not found', content: new OA\JsonContent(ref: '#/components/schemas/Error'))
        ]
    )]
    public function getUsersByIdWorkouts() {}

    #[OA\Get(
        path: '/users/{id}/stats',
        tags: ['users', 'workouts'],
        summary: 'Workout totals of one user (dashboard)',
        parameters: [new OA\Parameter(name: 'id', in: 'path', required: true, example: 2, schema: new OA\Schema(type: 'integer'))],
        responses: [
            new OA\Response(response: 200, description: 'Number of workouts, minutes and calories',
                content: new OA\JsonContent(ref: '#/components/schemas/WorkoutStats')),
            new OA\Response(response: 404, description: 'User not found', content: new OA\JsonContent(ref: '#/components/schemas/Error'))
        ]
    )]
    public function getUsersByIdStats() {}

    #[OA\Get(
        path: '/users/{id}/personal-records',
        tags: ['users', 'personal records'],
        summary: 'Personal records of one user, newest first',
        parameters: [new OA\Parameter(name: 'id', in: 'path', required: true, example: 2, schema: new OA\Schema(type: 'integer'))],
        responses: [
            new OA\Response(response: 200, description: 'The records with exercise name and type',
                content: new OA\JsonContent(type: 'array', items: new OA\Items(ref: '#/components/schemas/PersonalRecord'))),
            new OA\Response(response: 404, description: 'User not found', content: new OA\JsonContent(ref: '#/components/schemas/Error'))
        ]
    )]
    public function getUsersByIdPersonalRecords() {}
}
