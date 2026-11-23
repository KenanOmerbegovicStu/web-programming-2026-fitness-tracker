<?php
require_once __DIR__ . '/BaseService.php';
require_once __DIR__ . '/../dao/ExerciseCategoryDao.php';
require_once __DIR__ . '/../dao/ExerciseDao.php';

class ExerciseCategoryService extends BaseService
{
    protected $entity_name = 'Exercise category';
    private $exercise_dao;

    /** weighted = sets x reps x weight, bodyweight = reps or a timed hold, cardio = time and distance */
    const EXERCISE_TYPES = ['weighted', 'bodyweight', 'cardio'];

    public function __construct($dao = null, $exercise_dao = null)
    {
        parent::__construct($dao ?? new ExerciseCategoryDao());
        $this->exercise_dao = $exercise_dao ?? new ExerciseDao();
    }

    /** The list also shows how many exercises each category has. */
    public function get_all()
    {
        return $this->dao->get_with_exercise_count();
    }

    public function add($category)
    {
        return parent::add($this->empty_to_null($category, ['description']));
    }

    public function update($category, $id)
    {
        $category = $this->empty_to_null($category, ['description']);
        $current = $this->get_by_id($id);
        $this->validate($category, $id);

        // Rule: the type cannot change while logged sets or records use it - they were measured the old way
        if ($category['exercise_type'] !== $current['exercise_type']) {
            $used = $this->dao->count_usage($id);
            if ($used > 0) {
                throw new ValidationException("The exercises of this category are used in $used logged set(s) or record(s), so its exercise type cannot change.");
            }
        }

        $this->dao->update($category, $id);
        return $this->dao->get_by_id($id);
    }

    /** Rule: a category that still contains exercises cannot be deleted. */
    public function delete($id)
    {
        $this->get_by_id($id);
        $exercises = $this->exercise_dao->count_by_category($id);
        if ($exercises > 0) {
            throw new ValidationException("This category still contains $exercises exercise(s). Move or delete them first.");
        }
        $this->dao->delete($id);
    }

    protected function validate($category, $id = null)
    {
        Validator::required($category, ['category_name', 'exercise_type']);
        Validator::length($category['category_name'], 'category_name', 2, 50);
        Validator::no_html($category['category_name'], 'category_name');
        Validator::one_of($category['exercise_type'], 'exercise_type', self::EXERCISE_TYPES);
        if ($this->has_value($category, 'description')) {
            Validator::length($category['description'], 'description', 0, 1000);
            Validator::no_html($category['description'], 'description');
        }

        // Rule: category names are unique
        $existing = $this->dao->get_by_name(trim($category['category_name']));
        if ($existing && $existing['id'] != $id) {
            throw new ValidationException("A category named '{$category['category_name']}' already exists.");
        }
    }
}
