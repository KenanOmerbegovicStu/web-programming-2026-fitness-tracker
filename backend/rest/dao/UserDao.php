<?php
require_once __DIR__ . '/BaseDao.php';

class UserDao extends BaseDao
{
    /** Never SELECT the password hash unless we really need it (login). */
    private const PUBLIC_COLUMNS = "id, email, full_name, fitness_goal, experience_level, role, created_at, updated_at";

    public function __construct()
    {
        parent::__construct('users', ['email', 'password_hash', 'full_name', 'fitness_goal', 'experience_level', 'role']);
    }

    public function get_all()
    {
        return $this->query("SELECT " . self::PUBLIC_COLUMNS . " FROM users ORDER BY id");
    }

    public function get_by_id($id)
    {
        return $this->query_unique("SELECT " . self::PUBLIC_COLUMNS . " FROM users WHERE id = :id", ['id' => $id]);
    }

    /** The inherited add() returns the inserted values, so the hash is removed before returning. */
    public function add($entity)
    {
        $user = parent::add($entity);
        unset($user['password_hash']);
        return $user;
    }

    /** Includes the password hash - used only to check a login (Milestone 4). */
    public function get_by_email($email)
    {
        return $this->query_unique("SELECT * FROM users WHERE email = :email", ['email' => $email]);
    }

    public function get_by_role($role)
    {
        return $this->query(
            "SELECT " . self::PUBLIC_COLUMNS . " FROM users WHERE role = :role ORDER BY full_name",
            ['role' => $role]
        );
    }
}
