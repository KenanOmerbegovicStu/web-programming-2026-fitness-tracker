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
}
