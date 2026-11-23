<?php
use OpenApi\Attributes as OA;

/* Documents rest/routes/ExternalRoutes.php */
class ExternalPaths
{
    #[OA\Get(
        path: '/external/exercises',
        tags: ['external API (wger)'],
        summary: 'Discover exercises in the wger exercise database',
        description: 'Searches the public wger Workout Manager API (https://wger.de/api/v2/) by exercise name and returns at most 20 English results. Used by "Discover exercises" in the Exercise Library. If wger is slow (timeout 5 s) or not reachable, the failure is logged and the answer is 503 with a friendly message.',
        parameters: [
            new OA\Parameter(name: 'search', in: 'query', required: true, description: 'Exercise name to search (2-50 characters)',
                example: 'squat', schema: new OA\Schema(type: 'string'))
        ],
        responses: [
            new OA\Response(response: 200, description: 'The matching exercises', content: new OA\JsonContent(ref: '#/components/schemas/ExternalSearchResult')),
            new OA\Response(response: 400, description: 'Missing or invalid search text', content: new OA\JsonContent(ref: '#/components/schemas/Error')),
            new OA\Response(response: 503, description: 'The wger API is not available', content: new OA\JsonContent(ref: '#/components/schemas/Error'))
        ]
    )]
    public function getExternalExercises() {}
}
