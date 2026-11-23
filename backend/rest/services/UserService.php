<?php
require_once __DIR__ . '/BaseService.php';
require_once __DIR__ . '/../dao/UserDao.php';

class UserService extends BaseService
{
    protected $entity_name = 'User';

    const FITNESS_GOALS = ['strength', 'muscle_gain', 'endurance', 'weight_loss', 'general_fitness'];
    const EXPERIENCE_LEVELS = ['beginner', 'intermediate', 'advanced'];

    public function __construct($dao = null)
    {
        parent::__construct($dao ?? new UserDao());
    }

    public function add($user)
    {
        $user = $this->normalize($user);
        // Rule: a new account is a regular user unless a role is given
        $user['role'] = $user['role'] ?? Roles::USER;
        $this->validate($user);

        // The password is checked BEFORE hashing: a bcrypt hash is always 60 characters long
        Validator::required($user, ['password']);
        Validator::password($user['password']);

        // Rule: passwords are NEVER stored in plain text
        $user['password_hash'] = password_hash($user['password'], PASSWORD_BCRYPT);
        unset($user['password']);

        $created = $this->dao->add($user);
        return $this->dao->get_by_id($created['id']);   // get_by_id() never selects the hash
    }

    public function update($user, $id)
    {
        $user = $this->normalize($user);
        $current = $this->get_by_id($id);
        // A missing role means "keep the current role"
        $user['role'] = $user['role'] ?? $current['role'];
        $this->validate($user, $id);

        // Rule: the last admin cannot become a regular user (nobody could manage the app any more)
        if ($current['role'] === Roles::ADMIN && $user['role'] !== Roles::ADMIN
            && $this->dao->count_by_role(Roles::ADMIN) <= 1) {
            throw new ValidationException("This is the only admin account. Make another user an admin first.");
        }

        // The password is optional on update: only a new one is checked and hashed
        if ($this->has_value($user, 'password')) {
            Validator::password($user['password']);
            $user['password_hash'] = password_hash($user['password'], PASSWORD_BCRYPT);
        }
        unset($user['password']);

        $this->dao->update($user, $id);
        return $this->dao->get_by_id($id);
    }

    /** Rule: the last admin cannot be deleted. Workouts and records are deleted with the user (ON DELETE CASCADE). */
    public function delete($id)
    {
        $user = $this->get_by_id($id);
        if ($user['role'] === Roles::ADMIN && $this->dao->count_by_role(Roles::ADMIN) <= 1) {
            throw new ValidationException("This is the only admin account and cannot be deleted.");
        }
        $this->dao->delete($id);
    }

    protected function validate($user, $id = null)
    {
        Validator::required($user, ['email', 'full_name']);
        Validator::email($user['email']);
        Validator::length($user['email'], 'email', 5, 255);
        Validator::length($user['full_name'], 'full_name', 2, 100);
        Validator::no_html($user['full_name'], 'full_name');
        Validator::one_of($user['role'], 'role', Roles::ALL);
        if ($this->has_value($user, 'fitness_goal')) {
            Validator::one_of($user['fitness_goal'], 'fitness_goal', self::FITNESS_GOALS);
        }
        if ($this->has_value($user, 'experience_level')) {
            Validator::one_of($user['experience_level'], 'experience_level', self::EXPERIENCE_LEVELS);
        }

        // Rule: one account per email address
        $existing = $this->dao->get_by_email($user['email']);
        if ($existing && $existing['id'] != $id) {
            throw new ValidationException("An account with this email already exists.");
        }
    }

    private function normalize($user)
    {
        // The client can never write the hash directly - only through 'password'
        unset($user['password_hash']);
        if (isset($user['email'])) {
            // Stored in lowercase, so Keno@Example.com and keno@example.com are the same account
            $user['email'] = strtolower(trim($user['email']));
        }
        return $this->empty_to_null($user, ['fitness_goal', 'experience_level', 'role']);
    }
}
