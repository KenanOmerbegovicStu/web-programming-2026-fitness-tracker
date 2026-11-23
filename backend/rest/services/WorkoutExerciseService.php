<?php
require_once __DIR__ . '/BaseService.php';
require_once __DIR__ . '/../dao/WorkoutExerciseDao.php';
require_once __DIR__ . '/../dao/WorkoutDao.php';
require_once __DIR__ . '/../dao/ExerciseDao.php';

class WorkoutExerciseService extends BaseService
{
    protected $entity_name = 'Workout exercise';
    private $workout_dao;
    private $exercise_dao;

    const OPTIONAL_FIELDS = ['sets', 'reps', 'weight_kg', 'duration_seconds', 'distance_km', 'notes'];

    public function __construct($dao = null, $workout_dao = null, $exercise_dao = null)
    {
        parent::__construct($dao ?? new WorkoutExerciseDao());
        $this->workout_dao = $workout_dao ?? new WorkoutDao();
        $this->exercise_dao = $exercise_dao ?? new ExerciseDao();
    }

    public function add($workout_exercise)
    {
        $workout_exercise = $this->empty_to_null($workout_exercise, self::OPTIONAL_FIELDS);
        $this->validate($workout_exercise);
        $created = $this->dao->add($this->clear_unused_columns($workout_exercise));
        return $this->get_by_id($created['id']);
    }

    public function update($workout_exercise, $id)
    {
        $workout_exercise = $this->empty_to_null($workout_exercise, self::OPTIONAL_FIELDS);
        $this->get_by_id($id);
        $this->validate($workout_exercise, $id);
        $this->dao->update($this->clear_unused_columns($workout_exercise), $id);
        return $this->get_by_id($id);
    }

    /** The exercises of one workout, with name and type. */
    public function get_by_workout($workout_id)
    {
        if (!$this->workout_dao->get_by_id($workout_id)) {
            throw new NotFoundException("Workout not found.");
        }
        return $this->dao->get_by_workout($workout_id);
    }

    protected function validate($workout_exercise, $id = null)
    {
        Validator::required($workout_exercise, ['workout_id', 'exercise_id']);
        Validator::integer($workout_exercise['workout_id'], 'workout_id', 1);
        Validator::integer($workout_exercise['exercise_id'], 'exercise_id', 1);
        if (!$this->workout_dao->get_by_id($workout_exercise['workout_id'])) {
            throw new ValidationException("The selected workout does not exist.");
        }
        $exercise = $this->exercise_dao->get_by_id($workout_exercise['exercise_id']);
        if (!$exercise) {
            throw new ValidationException("The selected exercise does not exist.");
        }

        // Rule: the fields that are required depend on the exercise type
        $name = $exercise['exercise_name'];
        switch ($exercise['exercise_type']) {
            case 'weighted':
                if (!$this->has_value($workout_exercise, 'sets') || !$this->has_value($workout_exercise, 'reps')
                    || !$this->has_value($workout_exercise, 'weight_kg')) {
                    throw new ValidationException("$name is a weighted exercise: sets, reps and weight_kg are required.");
                }
                break;
            case 'bodyweight':
                $has_reps = $this->has_value($workout_exercise, 'reps');
                $has_duration = $this->has_value($workout_exercise, 'duration_seconds');
                if (!$this->has_value($workout_exercise, 'sets') || $has_reps === $has_duration) {
                    throw new ValidationException("$name is a bodyweight exercise: sets and either reps or duration_seconds (a timed hold) are required, not both.");
                }
                break;
            case 'cardio':
                if (!$this->has_value($workout_exercise, 'duration_seconds')) {
                    throw new ValidationException("$name is a cardio exercise: duration_seconds is required.");
                }
                break;
        }

        // Ranges of the values that were sent (the type decides later which ones are kept)
        $this->check_range($workout_exercise, 'sets', 'integer', 1, 50);
        $this->check_range($workout_exercise, 'reps', 'integer', 1, 500);
        $this->check_range($workout_exercise, 'weight_kg', 'number', 0, 500);
        $this->check_range($workout_exercise, 'duration_seconds', 'integer', 1, 86400);
        $this->check_range($workout_exercise, 'distance_km', 'number', 0, 1000);
        if ($this->has_value($workout_exercise, 'notes')) {
            Validator::length($workout_exercise['notes'], 'notes', 0, 500);
            Validator::no_html($workout_exercise['notes'], 'notes');
        }
    }

    /** Columns that the exercise type does not use are saved as NULL, whatever the client sent. */
    private function clear_unused_columns($workout_exercise)
    {
        $exercise = $this->exercise_dao->get_by_id($workout_exercise['exercise_id']);
        $unused = [
            'weighted'   => ['duration_seconds', 'distance_km'],
            'bodyweight' => ['distance_km'],
            'cardio'     => ['sets', 'reps'],
        ][$exercise['exercise_type']];
        foreach (self::OPTIONAL_FIELDS as $field) {
            if (!array_key_exists($field, $workout_exercise) || in_array($field, $unused, true)) {
                $workout_exercise[$field] = null;
            }
        }
        return $workout_exercise;
    }

    private function check_range($entity, $field, $kind, $min, $max)
    {
        if ($this->has_value($entity, $field)) {
            Validator::$kind($entity[$field], $field, $min, $max);
        }
    }
}
