<?php
require_once __DIR__ . '/BaseService.php';
require_once __DIR__ . '/ExerciseCategoryService.php';
require_once __DIR__ . '/../dao/ExerciseDao.php';
require_once __DIR__ . '/../dao/ExerciseCategoryDao.php';
require_once __DIR__ . '/../dao/WorkoutExerciseDao.php';
require_once __DIR__ . '/../dao/PersonalRecordDao.php';

class ExerciseService extends BaseService
{
    protected $entity_name = 'Exercise';
    private $category_dao;
    private $workout_exercise_dao;
    private $personal_record_dao;

    public function __construct($dao = null, $category_dao = null, $workout_exercise_dao = null, $personal_record_dao = null)
    {
        parent::__construct($dao ?? new ExerciseDao());
        $this->category_dao = $category_dao ?? new ExerciseCategoryDao();
        $this->workout_exercise_dao = $workout_exercise_dao ?? new WorkoutExerciseDao();
        $this->personal_record_dao = $personal_record_dao ?? new PersonalRecordDao();
    }

    public function add($exercise)
    {
        return parent::add($this->empty_to_null($exercise, ['muscle_group', 'description']));
    }

    public function update($exercise, $id)
    {
        return parent::update($this->empty_to_null($exercise, ['muscle_group', 'description']), $id);
    }

    /** Rule: an exercise that is used in workouts or records cannot be deleted (it would erase history). */
    public function delete($id)
    {
        $this->get_by_id($id);
        $sets = $this->workout_exercise_dao->count_by_exercise($id);
        $records = $this->personal_record_dao->count_by_exercise($id);
        if ($sets + $records > 0) {
            throw new ValidationException("This exercise is used in $sets logged set(s) and $records personal record(s) and cannot be deleted.");
        }
        $this->dao->delete($id);
    }

    /** Search box of the Exercise Library. */
    public function search($text)
    {
        $text = trim((string) $text);
        Validator::length($text, 'search', 2, 50);
        return $this->dao->search($text);
    }

    public function get_by_category($category_id)
    {
        if (!$this->category_dao->get_by_id($category_id)) {
            throw new NotFoundException("Exercise category not found.");
        }
        return $this->dao->get_by_category($category_id);
    }

    public function get_by_muscle_group($muscle_group)
    {
        $muscle_group = trim((string) $muscle_group);
        Validator::length($muscle_group, 'muscle_group', 2, 50);
        return $this->dao->get_by_muscle_group($muscle_group);
    }

    public function get_by_type($exercise_type)
    {
        Validator::one_of($exercise_type, 'type', ExerciseCategoryService::EXERCISE_TYPES);
        return $this->dao->get_by_type($exercise_type);
    }

    protected function validate($exercise, $id = null)
    {
        Validator::required($exercise, ['category_id', 'exercise_name']);
        Validator::integer($exercise['category_id'], 'category_id', 1);
        if (!$this->category_dao->get_by_id($exercise['category_id'])) {
            throw new ValidationException("The selected category does not exist.");
        }
        Validator::length($exercise['exercise_name'], 'exercise_name', 2, 100);
        Validator::no_html($exercise['exercise_name'], 'exercise_name');
        if ($this->has_value($exercise, 'muscle_group')) {
            Validator::length($exercise['muscle_group'], 'muscle_group', 2, 50);
            Validator::no_html($exercise['muscle_group'], 'muscle_group');
        }
        if ($this->has_value($exercise, 'description')) {
            Validator::length($exercise['description'], 'description', 0, 1000);
            Validator::no_html($exercise['description'], 'description');
        }

        // Rule: exercise names are unique in the library
        $existing = $this->dao->get_by_name(trim($exercise['exercise_name']));
        if ($existing && $existing['id'] != $id) {
            throw new ValidationException("An exercise named '{$exercise['exercise_name']}' already exists.");
        }
    }
}
