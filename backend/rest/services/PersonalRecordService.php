<?php
require_once __DIR__ . '/BaseService.php';
require_once __DIR__ . '/../dao/PersonalRecordDao.php';
require_once __DIR__ . '/../dao/UserDao.php';
require_once __DIR__ . '/../dao/ExerciseDao.php';

class PersonalRecordService extends BaseService
{
    protected $entity_name = 'Personal record';
    private $user_dao;
    private $exercise_dao;

    const OPTIONAL_FIELDS = ['max_weight_kg', 'reps_achieved', 'duration_seconds', 'distance_km', 'notes'];

    public function __construct($dao = null, $user_dao = null, $exercise_dao = null)
    {
        parent::__construct($dao ?? new PersonalRecordDao());
        $this->user_dao = $user_dao ?? new UserDao();
        $this->exercise_dao = $exercise_dao ?? new ExerciseDao();
    }

    public function add($record)
    {
        $record = $this->empty_to_null($record, self::OPTIONAL_FIELDS);
        $this->validate($record);
        $record = $this->clear_unused_columns($record);

        // Rule: a new personal record must beat the current one
        $current = $this->dao->get_by_user_and_exercise($record['user_id'], $record['exercise_id']);
        if ($current && !$this->is_better($record, $current)) {
            throw new ValidationException("This is not better than your current record ("
                . $this->describe($current) . " on {$current['achieved_date']}).");
        }

        $created = $this->dao->add($record);
        return $this->get_by_id($created['id']);
    }

    /** Editing a record (e.g. fixing a typo) only checks the fields, not "better". */
    public function update($record, $id)
    {
        $record = $this->empty_to_null($record, self::OPTIONAL_FIELDS);
        $this->get_by_id($id);
        $this->validate($record, $id);
        $this->dao->update($this->clear_unused_columns($record), $id);
        return $this->get_by_id($id);
    }

    public function get_by_user($user_id)
    {
        if (!$this->user_dao->get_by_id($user_id)) {
            throw new NotFoundException("User not found.");
        }
        return $this->dao->get_by_user($user_id);
    }

    protected function validate($record, $id = null)
    {
        Validator::required($record, ['user_id', 'exercise_id', 'achieved_date']);
        Validator::integer($record['user_id'], 'user_id', 1);
        Validator::integer($record['exercise_id'], 'exercise_id', 1);
        if (!$this->user_dao->get_by_id($record['user_id'])) {
            throw new ValidationException("The selected user does not exist.");
        }
        $exercise = $this->exercise_dao->get_by_id($record['exercise_id']);
        if (!$exercise) {
            throw new ValidationException("The selected exercise does not exist.");
        }
        Validator::date($record['achieved_date'], 'achieved_date');
        Validator::not_in_future($record['achieved_date'], 'achieved_date');

        // Rule: the fields that are required depend on the exercise type
        $name = $exercise['exercise_name'];
        switch ($exercise['exercise_type']) {
            case 'weighted':
                if (!$this->has_value($record, 'max_weight_kg') || !$this->has_value($record, 'reps_achieved')) {
                    throw new ValidationException("$name is a weighted exercise: max_weight_kg and reps_achieved are required.");
                }
                break;
            case 'bodyweight':
                if ($this->has_value($record, 'reps_achieved') === $this->has_value($record, 'duration_seconds')) {
                    throw new ValidationException("$name is a bodyweight exercise: either reps_achieved or duration_seconds is required, not both.");
                }
                break;
            case 'cardio':
                if (!$this->has_value($record, 'duration_seconds')) {
                    throw new ValidationException("$name is a cardio exercise: duration_seconds is required.");
                }
                break;
        }

        $this->check_range($record, 'max_weight_kg', 'number', 0, 500);
        $this->check_range($record, 'reps_achieved', 'integer', 1, 500);
        $this->check_range($record, 'duration_seconds', 'integer', 1, 86400);
        $this->check_range($record, 'distance_km', 'number', 0, 1000);
        if ($this->has_value($record, 'notes')) {
            Validator::length($record['notes'], 'notes', 0, 500);
            Validator::no_html($record['notes'], 'notes');
        }
    }

    /**
     * What counts as "better" depends on the type:
     *   weighted   - more weight, or the same weight for more reps
     *   bodyweight - more added weight, or the same added weight for more reps / a longer hold
     *   cardio     - a longer distance, or the same distance in less time;
     *                without a distance (e.g. rowing for time) a longer duration
     * Two records that were measured differently (reps vs. a timed hold) cannot be compared, so the new one counts.
     */
    private function is_better($new, $current)
    {
        $type = $this->exercise_dao->get_by_id($new['exercise_id'])['exercise_type'];
        $new_weight = (float) ($new['max_weight_kg'] ?? 0);
        $current_weight = (float) ($current['max_weight_kg'] ?? 0);

        if ($type === 'weighted' || $type === 'bodyweight') {
            if ($new_weight != $current_weight) {
                return $new_weight > $current_weight;
            }
            if ($new['reps_achieved'] !== null && $current['reps_achieved'] !== null) {
                return $new['reps_achieved'] > $current['reps_achieved'];
            }
            if ($new['duration_seconds'] !== null && $current['duration_seconds'] !== null) {
                return $new['duration_seconds'] > $current['duration_seconds'];
            }
            return true;
        }

        // cardio
        if ($new['distance_km'] !== null && $current['distance_km'] !== null) {
            if ((float) $new['distance_km'] != (float) $current['distance_km']) {
                return (float) $new['distance_km'] > (float) $current['distance_km'];
            }
            return $new['duration_seconds'] < $current['duration_seconds'];
        }
        if ($new['distance_km'] === null && $current['distance_km'] === null) {
            return $new['duration_seconds'] > $current['duration_seconds'];
        }
        return true;
    }

    /** A record as readable text, e.g. "140 kg x 3 reps" or "5 km in 1800 s". */
    private function describe($record)
    {
        $parts = [];
        if ($record['max_weight_kg'] !== null) $parts[] = ((float) $record['max_weight_kg']) . ' kg';
        if ($record['reps_achieved'] !== null) $parts[] = $record['reps_achieved'] . ' reps';
        if ($record['distance_km'] !== null)   $parts[] = ((float) $record['distance_km']) . ' km';
        $text = implode(' x ', $parts);
        if ($record['duration_seconds'] !== null) {
            $text .= ($text === '' ? '' : ' in ') . $record['duration_seconds'] . ' s';
        }
        return $text;
    }

    /** Columns that the exercise type does not use are saved as NULL, whatever the client sent. */
    private function clear_unused_columns($record)
    {
        $exercise = $this->exercise_dao->get_by_id($record['exercise_id']);
        $unused = [
            'weighted'   => ['duration_seconds', 'distance_km'],
            'bodyweight' => ['distance_km'],
            'cardio'     => ['reps_achieved'],
        ][$exercise['exercise_type']];
        foreach (self::OPTIONAL_FIELDS as $field) {
            if (!array_key_exists($field, $record) || in_array($field, $unused, true)) {
                $record[$field] = null;
            }
        }
        return $record;
    }

    private function check_range($entity, $field, $kind, $min, $max)
    {
        if ($this->has_value($entity, $field)) {
            Validator::$kind($entity[$field], $field, $min, $max);
        }
    }
}
