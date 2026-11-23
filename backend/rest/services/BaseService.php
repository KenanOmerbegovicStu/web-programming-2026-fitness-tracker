<?php
require_once __DIR__ . '/../../utils/exceptions.php';
require_once __DIR__ . '/../../utils/Validator.php';
require_once __DIR__ . '/../../utils/Roles.php';

class BaseService
{
    protected $dao;
    /** Used in error messages, e.g. "Workout not found." */
    protected $entity_name = 'Entity';

    /** The DAO is passed in from outside (dependency injection), so a test can pass a fake one. */
    public function __construct($dao)
    {
        $this->dao = $dao;
    }

    public function get_all()
    {
        return $this->dao->get_all();
    }

    public function get_by_id($id)
    {
        $entity = $this->dao->get_by_id($id);
        if (!$entity) {
            throw new NotFoundException("{$this->entity_name} not found.");
        }
        return $entity;
    }

    public function add($entity)
    {
        $this->validate($entity);
        $created = $this->dao->add($entity);
        return $this->dao->get_by_id($created['id']);
    }

    public function update($entity, $id)
    {
        $this->get_by_id($id);              // 404 if it does not exist
        $this->validate($entity, $id);
        $this->dao->update($entity, $id);
        return $this->dao->get_by_id($id);
    }

    public function delete($id)
    {
        $this->get_by_id($id);
        $this->dao->delete($id);
    }

    /**
     * Throw a ValidationException when $entity breaks a rule.
     * $id is null when adding and the id of the entity when updating.
     */
    protected function validate($entity, $id = null)
    {
    }

    /** An empty string from a form means "no value": turn it into NULL for optional columns. */
    protected function empty_to_null($entity, $fields)
    {
        foreach ($fields as $field) {
            if (array_key_exists($field, $entity) && is_string($entity[$field]) && trim($entity[$field]) === '') {
                $entity[$field] = null;
            }
        }
        return $entity;
    }

    /** Check an optional value only when it was sent (isset() is false for NULL). */
    protected function has_value($entity, $field)
    {
        return isset($entity[$field]) && trim((string) $entity[$field]) !== '';
    }
}
