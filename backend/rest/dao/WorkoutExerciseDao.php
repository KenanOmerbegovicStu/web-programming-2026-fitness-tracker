<?php
require_once __DIR__ . '/BaseDao.php';

class WorkoutExerciseDao extends BaseDao
{
    public function __construct()
    {
        parent::__construct('workout_exercises', ['workout_id', 'exercise_id', 'sets', 'reps', 'weight_kg', 'duration_seconds', 'distance_km', 'notes']);
    }

    /** All exercises of one workout, with name, type (which columns are used) and muscle group. */
    public function get_by_workout($workout_id)
    {
        return $this->query(
            "SELECT we.*, e.exercise_name, c.exercise_type, e.muscle_group
             FROM workout_exercises we
             JOIN exercises e ON e.id = we.exercise_id
             JOIN exercise_categories c ON c.id = e.category_id
             WHERE we.workout_id = :workout_id
             ORDER BY we.id",
            ['workout_id' => $workout_id]
        );
    }
}
