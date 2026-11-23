<?php
use OpenApi\Attributes as OA;

/* Documents rest/routes/PersonalRecordRoutes.php */
class PersonalRecordPaths
{
    #[OA\Get(
        path: '/personal-records',
        tags: ['personal records'],
        summary: 'List the records of all users',
        description: 'The records of one user: GET /users/{id}/personal-records.',
        responses: [
            new OA\Response(response: 200, description: 'All records',
                content: new OA\JsonContent(type: 'array', items: new OA\Items(ref: '#/components/schemas/PersonalRecord')))
        ]
    )]
    public function getPersonalRecords() {}

    #[OA\Get(
        path: '/personal-records/{id}',
        tags: ['personal records'],
        summary: 'Get one record',
        parameters: [new OA\Parameter(name: 'id', in: 'path', required: true, example: 2, schema: new OA\Schema(type: 'integer'))],
        responses: [
            new OA\Response(response: 200, description: 'The record', content: new OA\JsonContent(ref: '#/components/schemas/PersonalRecord')),
            new OA\Response(response: 404, description: 'Personal record not found', content: new OA\JsonContent(ref: '#/components/schemas/Error'))
        ]
    )]
    public function getPersonalRecordsById() {}

    #[OA\Post(
        path: '/personal-records',
        tags: ['personal records'],
        summary: 'Add a personal record',
        description: 'The new record must be better than the current one of the user for this exercise. Weighted: more weight, or the same weight for more reps. Bodyweight: more added weight, or the same added weight for more reps or a longer hold. Cardio: a longer distance, or the same distance in less time; without a distance, a longer duration.',
        requestBody: new OA\RequestBody(required: true, content: new OA\JsonContent(ref: '#/components/schemas/PersonalRecordInput')),
        responses: [
            new OA\Response(response: 201, description: 'The new record', content: new OA\JsonContent(ref: '#/components/schemas/PersonalRecord')),
            new OA\Response(response: 400, description: 'Invalid data, fields that do not fit the exercise type, or not better than the current record',
                content: new OA\JsonContent(ref: '#/components/schemas/Error'))
        ]
    )]
    public function postPersonalRecords() {}

    #[OA\Put(
        path: '/personal-records/{id}',
        tags: ['personal records'],
        summary: 'Update a record (e.g. fix a typo)',
        description: 'Checks the fields, but not whether the record is better.',
        parameters: [new OA\Parameter(name: 'id', in: 'path', required: true, example: 2, schema: new OA\Schema(type: 'integer'))],
        requestBody: new OA\RequestBody(required: true, content: new OA\JsonContent(ref: '#/components/schemas/PersonalRecordInput')),
        responses: [
            new OA\Response(response: 200, description: 'The updated record', content: new OA\JsonContent(ref: '#/components/schemas/PersonalRecord')),
            new OA\Response(response: 400, description: 'Invalid data or fields that do not fit the exercise type', content: new OA\JsonContent(ref: '#/components/schemas/Error')),
            new OA\Response(response: 404, description: 'Personal record not found', content: new OA\JsonContent(ref: '#/components/schemas/Error'))
        ]
    )]
    public function putPersonalRecordsById() {}

    #[OA\Delete(
        path: '/personal-records/{id}',
        tags: ['personal records'],
        summary: 'Delete a record',
        parameters: [new OA\Parameter(name: 'id', in: 'path', required: true, example: 9, schema: new OA\Schema(type: 'integer'))],
        responses: [
            new OA\Response(response: 200, description: 'Deleted', content: new OA\JsonContent(ref: '#/components/schemas/Message')),
            new OA\Response(response: 404, description: 'Personal record not found', content: new OA\JsonContent(ref: '#/components/schemas/Error'))
        ]
    )]
    public function deletePersonalRecordsById() {}
}
