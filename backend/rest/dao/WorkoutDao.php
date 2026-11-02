<?php
require_once __DIR__ . '/BaseDao.php';

class WorkoutDao extends BaseDao
{
    /** Workouts with the number of exercises in each (shown in Workout History). */
    private const SELECT_WITH_COUNT = "SELECT w.*, COUNT(we.id) AS exercise_count
                                       FROM workouts w
                                       LEFT JOIN workout_exercises we ON we.workout_id = w.id";

    public function __construct()
    {
        parent::__construct('workouts', ['user_id', 'workout_date', 'workout_type', 'duration_minutes', 'calories', 'notes']);
    }

    public function get_by_user($user_id)
    {
        return $this->query(
            self::SELECT_WITH_COUNT . " WHERE w.user_id = :user_id GROUP BY w.id ORDER BY w.workout_date DESC, w.id DESC",
            ['user_id' => $user_id]
        );
    }

    /** LIMIT cannot be a placeholder, so the number is cast to int and added to the SQL. */
    public function get_recent($user_id, $limit = 5)
    {
        return $this->query(
            self::SELECT_WITH_COUNT . " WHERE w.user_id = :user_id GROUP BY w.id ORDER BY w.workout_date DESC, w.id DESC
             LIMIT " . (int) $limit,
            ['user_id' => $user_id]
        );
    }

    public function get_by_date_range($user_id, $start_date, $end_date)
    {
        return $this->query(
            self::SELECT_WITH_COUNT . " WHERE w.user_id = :user_id AND w.workout_date BETWEEN :start_date AND :end_date
             GROUP BY w.id ORDER BY w.workout_date DESC, w.id DESC",
            ['user_id' => $user_id, 'start_date' => $start_date, 'end_date' => $end_date]
        );
    }

    /** Totals for the dashboard and progress views. COALESCE turns SUM of no rows (NULL) into 0. */
    public function get_stats_by_user($user_id)
    {
        return $this->query_unique(
            "SELECT COUNT(*) AS total_workouts,
                    COALESCE(SUM(duration_minutes), 0) AS total_minutes,
                    COALESCE(SUM(calories), 0) AS total_calories
             FROM workouts
             WHERE user_id = :user_id",
            ['user_id' => $user_id]
        );
    }
}
