<?php
require_once __DIR__ . '/BaseService.php';
require_once __DIR__ . '/../dao/WorkoutDao.php';
require_once __DIR__ . '/../dao/UserDao.php';

class WorkoutService extends BaseService
{
    protected $entity_name = 'Workout';
    private $user_dao;

    public function __construct($dao = null, $user_dao = null)
    {
        parent::__construct($dao ?? new WorkoutDao());
        $this->user_dao = $user_dao ?? new UserDao();
    }

    public function add($workout)
    {
        return parent::add($this->normalize($workout));
    }

    public function update($workout, $id)
    {
        return parent::update($this->normalize($workout), $id);
    }

    /** Workout History of one user, newest first. */
    public function get_by_user($user_id)
    {
        $this->check_user($user_id);
        return $this->dao->get_by_user($user_id);
    }

    /** Dashboard: the last few workouts. */
    public function get_recent($user_id, $limit)
    {
        $this->check_user($user_id);
        Validator::integer($limit, 'limit', 1, 20);
        return $this->dao->get_recent($user_id, $limit);
    }

    /** Workout History filter: from and to are both included. */
    public function get_by_date_range($user_id, $from, $to)
    {
        $this->check_user($user_id);
        Validator::date($from, 'from');
        Validator::date($to, 'to');
        if ($from > $to) {
            throw new ValidationException("The date 'from' must not be after the date 'to'.");
        }
        return $this->dao->get_by_date_range($user_id, $from, $to);
    }

    /** Totals for the dashboard and progress views. */
    public function get_stats_by_user($user_id)
    {
        $this->check_user($user_id);
        // SUM() comes back from MySQL as a string ("425"): the API always answers with numbers
        return array_map('intval', $this->dao->get_stats_by_user($user_id));
    }

    protected function validate($workout, $id = null)
    {
        Validator::required($workout, ['user_id', 'workout_date', 'workout_type']);
        Validator::integer($workout['user_id'], 'user_id', 1);
        if (!$this->user_dao->get_by_id($workout['user_id'])) {
            throw new ValidationException("The selected user does not exist.");
        }
        // Rule: you log a workout you have done, not one you plan
        Validator::date($workout['workout_date'], 'workout_date');
        Validator::not_in_future($workout['workout_date'], 'workout_date');
        Validator::length($workout['workout_type'], 'workout_type', 2, 50);
        Validator::no_html($workout['workout_type'], 'workout_type');
        if ($this->has_value($workout, 'duration_minutes')) {
            Validator::integer($workout['duration_minutes'], 'duration_minutes', 1, 600);
        }
        if ($this->has_value($workout, 'calories')) {
            Validator::integer($workout['calories'], 'calories', 0, 5000);
        }
        if ($this->has_value($workout, 'notes')) {
            Validator::length($workout['notes'], 'notes', 0, 1000);
            Validator::no_html($workout['notes'], 'notes');
        }
    }

    private function check_user($user_id)
    {
        if (!$this->user_dao->get_by_id($user_id)) {
            throw new NotFoundException("User not found.");
        }
    }

    private function normalize($workout)
    {
        return $this->empty_to_null($workout, ['duration_minutes', 'calories', 'notes']);
    }
}
