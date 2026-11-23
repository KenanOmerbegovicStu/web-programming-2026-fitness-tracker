<?php
require_once __DIR__ . '/BaseDao.php';

class ExerciseDao extends BaseDao
{
    /** Every exercise row also contains its category name and the category's exercise_type. */
    private const SELECT = "SELECT e.*, c.category_name, c.exercise_type
                            FROM exercises e
                            JOIN exercise_categories c ON c.id = e.category_id";

    public function __construct()
    {
        parent::__construct('exercises', ['category_id', 'exercise_name', 'muscle_group', 'description']);
    }

    public function get_all()
    {
        return $this->query(self::SELECT . " ORDER BY e.category_id, e.exercise_name");
    }

    public function get_by_id($id)
    {
        return $this->query_unique(self::SELECT . " WHERE e.id = :id", ['id' => $id]);
    }

    public function get_by_category($category_id)
    {
        return $this->query(
            self::SELECT . " WHERE e.category_id = :category_id ORDER BY e.exercise_name",
            ['category_id' => $category_id]
        );
    }

    public function get_by_muscle_group($muscle_group)
    {
        return $this->query(
            self::SELECT . " WHERE e.muscle_group = :muscle_group ORDER BY e.exercise_name",
            ['muscle_group' => $muscle_group]
        );
    }

    /** weighted, bodyweight or cardio (set on the category) - decides which fields the workout log uses */
    public function get_by_type($exercise_type)
    {
        return $this->query(
            self::SELECT . " WHERE c.exercise_type = :exercise_type ORDER BY e.exercise_name",
            ['exercise_type' => $exercise_type]
        );
    }

    /** Search box of the Exercise Library: name or muscle group contains the text. */
    public function search($text)
    {
        return $this->query(
            self::SELECT . " WHERE e.exercise_name LIKE :name OR e.muscle_group LIKE :muscle ORDER BY e.exercise_name",
            ['name' => '%' . $text . '%', 'muscle' => '%' . $text . '%']
        );
    }
}
