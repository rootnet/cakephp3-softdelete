<?php
namespace SoftDelete\Model\Table;

use Cake\ORM\RulesChecker;
use Cake\Datasource\EntityInterface;
use Cake\ORM\Query as CakeQuery;
use SoftDelete\Error\MissingColumnException;
use SoftDelete\ORM\Query;

trait SoftDeleteTrait {

    /**
     * Get the configured deletion field
     *
     * @return string
     * @throws \SoftDelete\Error\MissingFieldException
     */
    public function getSoftDeleteField()
    {
        if (isset($this->softDeleteField)) {
            $field = $this->softDeleteField;
        } else {
            $field = 'deleted';
        }

        if ($this->getSchema()->getColumn($field) === null) {
            throw new MissingColumnException(
                __('Configured field `{0}` is missing from the table `{1}`.',
                    $field,
                    $this->getAlias()
                )
            );
        }

        return $field;
    }

    public function query(): CakeQuery
    {
        return new Query($this->getConnection(), $this);
    }

    /**
     * Perform the delete operation.
     *
     * Will soft delete the entity provided. Will remove rows from any
     * dependent associations, and clear out join tables for BelongsToMany associations.
     *
     * @param \Cake\DataSource\EntityInterface $entity The entity to soft delete.
     * @param \ArrayObject $options The options for the delete.
     * @throws \InvalidArgumentException if there are no primary key values of the
     * passed entity
     * @return bool success
     */
    protected function _processDelete(EntityInterface $entity, \ArrayObject $options): bool
    {
        if ($entity->isNew()) {
            return false;
        }

        $primaryKey = (array)$this->getPrimaryKey();
        if (!$entity->has($primaryKey)) {
            $msg = 'Deleting requires all primary key values.';
            throw new \InvalidArgumentException($msg);
        }

        if ($options['checkRules'] && !$this->checkRules($entity, RulesChecker::DELETE, $options)) {
            return false;
        }

        $event = $this->dispatchEvent('Model.beforeDelete', [
            'entity' => $entity,
            'options' => $options
        ]);

        if ($event->isStopped()) {
            return $event->result;
        }

        $this->_associations->cascadeDelete(
            $entity,
            ['_primary' => false] + $options->getArrayCopy()
        );

        $query = $this->query();
        $conditions = (array)$entity->extract($primaryKey);
        $statement = $query->update()
            ->set([$this->getSoftDeleteField() => date('Y-m-d H:i:s')])
            ->where($conditions)
            ->execute();

        $success = $statement->rowCount() > 0;
        if (!$success) {
            return $success;
        }

        $this->dispatchEvent('Model.afterDelete', [
            'entity' => $entity,
            'options' => $options
        ]);

        return $success;
    }

    /**
     * Soft deletes all records matching `$conditions`.
     * @param $conditions
     * @return int number of affected rows.
     */
    public function deleteAll($conditions): int
    {
        $query = $this->query()
            ->update()
            ->set([$this->getSoftDeleteField() => date('Y-m-d H:i:s')])
            ->where($conditions)
            ->andWhere(['deleted IS' => null]);
        $statement = $query->execute();
        $statement->closeCursor();
        return $statement->rowCount();
    }

    /**
     * Hard deletes the given $entity.
     * @return bool true in case of success, false otherwise.
     */
    public function hardDelete(EntityInterface $entity): bool
    {
        if(!$this->delete($entity)) {
            return false;
        }
        $primaryKey = (array)$this->getPrimaryKey();
        $query = $this->query();
        $conditions = (array)$entity->extract($primaryKey);
        $statement = $query->delete()
            ->where($conditions)
            ->execute();

        $success = $statement->rowCount() > 0;
        if (!$success) {
            return $success;
        }

        return $success;
    }

    /**
     * Hard deletes all records that were soft deleted before a given date.
     * @param \DateTime $until Date until which soft deleted records must be hard deleted.
     * @return int number of affected rows.
     */
    public function hardDeleteAll(\Datetime $until): int
    {
        $query = $this->query()
            ->delete()
            ->where([
                $this->getSoftDeleteField() . ' IS NOT NULL',
                $this->getSoftDeleteField() . ' <=' => $until->format('Y-m-d H:i:s')
            ]);
        $statement = $query->execute();
        $statement->closeCursor();
        return $statement->rowCount();
    }

    /**
     * Restore a soft deleted entity into an active state.
     * @param EntityInterface $entity Entity to be restored.
     * @return bool true in case of success, false otherwise.
     */
    public function restore(EntityInterface $entity): bool
    {
        $softDeleteField = $this->getSoftDeleteField();
        $entity->$softDeleteField = null;

        return $this->save($entity) !== false;
    }

    /**
     * Restore multiple soft deleted records into an active state
     *
     * @param string|array|\Cake\Database\ExpressionInterface|callable|null $conditions The conditions to filter on
     * @return int number of affected rows
     */
    public function restoreAll($conditions = []): int
    {
        $query = $this->query()
            ->update()
            ->set([$this->getSoftDeleteField() => null])
            ->where([$this->getSoftDeleteField() . ' IS NOT NULL'])
            ->andWhere($conditions);
        $statement = $query->execute();
        $statement->closeCursor();

        return $statement->rowCount();
    }
}
