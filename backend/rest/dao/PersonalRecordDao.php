<?php
require_once __DIR__ . '/BaseDao.php';

class PersonalRecordDao extends BaseDao
{
    /** Every record row also contains the exercise name. */
    private const SELECT = "SELECT pr.*, e.exercise_name, c.exercise_type
                            FROM personal_records pr
                            JOIN exercises e ON e.id = pr.exercise_id
                            JOIN exercise_categories c ON c.id = e.category_id";

    public function __construct()
    {
        parent::__construct('personal_records', ['user_id', 'exercise_id', 'max_weight_kg', 'reps_achieved', 'duration_seconds', 'distance_km', 'achieved_date', 'notes']);
    }

    public function get_by_user($user_id)
    {
        return $this->query(
            self::SELECT . " WHERE pr.user_id = :user_id ORDER BY pr.achieved_date DESC, pr.id DESC",
            ['user_id' => $user_id]
        );
    }

    /**
     * The latest (current) record of one user for one exercise, or null.
     * What counts as "better" depends on the exercise type and is decided in the service (Milestone 3).
     */
    public function get_by_user_and_exercise($user_id, $exercise_id)
    {
        return $this->query_unique(
            self::SELECT . " WHERE pr.user_id = :user_id AND pr.exercise_id = :exercise_id
             ORDER BY pr.achieved_date DESC, pr.id DESC LIMIT 1",
            ['user_id' => $user_id, 'exercise_id' => $exercise_id]
        );
    }

    public function get_recent($user_id, $limit = 3)
    {
        return $this->query(
            self::SELECT . " WHERE pr.user_id = :user_id ORDER BY pr.achieved_date DESC, pr.id DESC LIMIT " . (int) $limit,
            ['user_id' => $user_id]
        );
    }
}
