<?php
require_once __DIR__ . '/BaseDao.php';

class ExerciseCategoryDao extends BaseDao
{
    public function __construct()
    {
        parent::__construct('exercise_categories', ['category_name', 'exercise_type', 'description']);
    }

    public function get_by_name($category_name)
    {
        return $this->query_unique(
            "SELECT * FROM exercise_categories WHERE category_name = :category_name",
            ['category_name' => $category_name]
        );
    }

    /** Number of exercises per category - LEFT JOIN keeps empty categories (count 0). */
    public function get_with_exercise_count()
    {
        return $this->query(
            "SELECT c.id, c.category_name, c.exercise_type, c.description, COUNT(e.id) AS exercise_count
             FROM exercise_categories c
             LEFT JOIN exercises e ON e.category_id = c.id
             GROUP BY c.id, c.category_name, c.exercise_type, c.description
             ORDER BY c.id"
        );
    }

    /**
     * How many logged sets and personal records use the exercises of this category.
     * The service blocks a change of exercise_type while this is above 0 (old rows would no longer fit the type).
     */
    public function count_usage($category_id)
    {
        $row = $this->query_unique(
            "SELECT (SELECT COUNT(*) FROM workout_exercises we JOIN exercises e ON e.id = we.exercise_id
                     WHERE e.category_id = :category_id_1)
                  + (SELECT COUNT(*) FROM personal_records pr JOIN exercises e ON e.id = pr.exercise_id
                     WHERE e.category_id = :category_id_2) AS total",
            ['category_id_1' => $category_id, 'category_id_2' => $category_id]
        );
        return (int) $row['total'];
    }
}
