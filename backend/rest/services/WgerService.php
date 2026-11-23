<?php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../utils/exceptions.php';
require_once __DIR__ . '/../../utils/Validator.php';
require_once __DIR__ . '/../../utils/Logger.php';
require_once __DIR__ . '/../dao/ExerciseDao.php';

class WgerService
{
    const ENGLISH = 2;          // wger language id of English
    const MAX_RESULTS = 20;
    const FRIENDLY_ERROR = 'The exercise database is not available right now. Please try again later.';

    private $exercise_dao;

    public function __construct($exercise_dao = null)
    {
        $this->exercise_dao = $exercise_dao ?? new ExerciseDao();
    }

    /** Search wger by exercise name and return a clean list (never the raw wger JSON). */
    public function search($text)
    {
        $text = trim((string) $text);
        Validator::length($text, 'search', 2, 50);

        $data = $this->get('/exerciseinfo/', [
            'name__search' => $text,
            'language' => self::ENGLISH,
            'limit' => self::MAX_RESULTS,
        ]);

        // Mark the exercises that are already in our own library (same name, case does not matter)
        $own_names = array_map('mb_strtolower', array_column($this->exercise_dao->get_all(), 'exercise_name'));

        $results = [];
        foreach ($data['results'] ?? [] as $exercise) {
            $mapped = $this->map_exercise($exercise);
            if ($mapped === null) {
                continue;       // no English name - nothing to show
            }
            $mapped['in_library'] = in_array(mb_strtolower($mapped['name']), $own_names, true);
            $results[] = $mapped;
        }

        return [
            'search' => $text,
            'total' => (int) ($data['count'] ?? count($results)),
            'results' => $results,
        ];
    }

    /** One wger exercise -> only the fields the Exercise Library shows. */
    private function map_exercise($exercise)
    {
        $english = null;
        foreach ($exercise['translations'] ?? [] as $translation) {
            if (($translation['language'] ?? null) == self::ENGLISH) {
                $english = $translation;
                break;
            }
        }
        if ($english === null || trim($english['name'] ?? '') === '') {
            return null;
        }

        // The wger description is HTML: keep plain text only, shortened for a card
        $description = trim(preg_replace('/\s+/', ' ', strip_tags($english['description'] ?? '')));
        if (mb_strlen($description) > 200) {
            $description = rtrim(mb_substr($description, 0, 197)) . '...';
        }

        $main_image = null;
        foreach ($exercise['images'] ?? [] as $image) {
            if ($main_image === null || !empty($image['is_main'])) {
                $main_image = $image['image'] ?? null;
            }
        }

        return [
            'wger_id' => $exercise['id'],
            'name' => trim($english['name']),
            'category' => $exercise['category']['name'] ?? null,
            'muscles' => array_map(fn($m) => $m['name_en'] ?: $m['name'], $exercise['muscles'] ?? []),
            'equipment' => array_column($exercise['equipment'] ?? [], 'name'),
            'description' => $description,
            'image' => $main_image,
        ];
    }

    /** GET request to wger with a timeout. Returns the decoded JSON or throws ExternalServiceException (503). */
    private function get($path, $params)
    {
        $url = rtrim(Config::WGER_BASE_URL(), '/') . $path . '?' . http_build_query($params);
        $headers = ['Accept: application/json'];
        if (Config::WGER_API_KEY() !== '') {
            $headers[] = 'Authorization: Token ' . Config::WGER_API_KEY();
        }

        $curl = curl_init($url);
        curl_setopt_array($curl, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CONNECTTIMEOUT => Config::WGER_TIMEOUT(),  // time to connect
            CURLOPT_TIMEOUT => Config::WGER_TIMEOUT(),         // time for the whole request
            CURLOPT_HTTPHEADER => $headers,
        ]);
        $started = microtime(true);
        $body = curl_exec($curl);
        $status = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        $curl_error = curl_error($curl);
        curl_close($curl);
        $ms = round((microtime(true) - $started) * 1000);
        $call = "GET $path?" . http_build_query($params);

        if ($body === false) {
            $this->fail("wger API failed: $curl_error ($call, {$ms}ms)");
        }
        if ($status !== 200) {
            $this->fail("wger API failed: HTTP $status ($call, {$ms}ms)");
        }
        $data = json_decode($body, true);
        if (!is_array($data)) {
            $this->fail("wger API failed: the answer is not valid JSON ($call, {$ms}ms)");
        }

        Logger::info("wger API call $call 200 {$ms}ms");
        return $data;
    }

    private function fail($log_message)
    {
        Logger::error($log_message);
        throw new ExternalServiceException(self::FRIENDLY_ERROR);
    }
}
