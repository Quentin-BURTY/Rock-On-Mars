<?php

namespace App\Model;

class MemberManager extends AbstractManager
{
    public const TABLE = 'member';


    /**
     * Insert new member in database
     */
    public function insert(array $member)
    {
        $statement = $this->pdo->prepare("INSERT INTO " . self::TABLE . "
        (`name`, `password`, `favorite_activity`, `bio`)
         VALUES (:name, :password, :favorite_activity, :bio)");
        $statement->bindValue('name', $member['name'], \PDO::PARAM_STR);
        $statement->bindValue('password', $member['password'], \PDO::PARAM_STR);
        // $statement->bindValue('image', $member['image'], \PDO::PARAM_STR);
        // $statement->bindValue('is_logged', $member['is_logged'], \PDO::PARAM_BOOL);
        $statement->bindValue('favorite_activity', $member['favorite_activity'], \PDO::PARAM_STR);
        $statement->bindValue('bio', $member['bio'], \PDO::PARAM_STR);

        return $statement->execute();
        // return (int)$this->pdo->lastInsertId();
    }

    /**
     * Select one member with his name
     */
    public function selectOneByName($name)
    {
        $statement = $this->pdo->prepare("SELECT * FROM " . static::TABLE . " WHERE name=:name");
        $statement->bindValue('name', $name, \PDO::PARAM_STR);
        $statement->execute();

        return $statement->fetch();
    }

    public function selectOnlyNameById(int $id)
    {

        $statement = $this->pdo->prepare("SELECT name FROM " . static::TABLE . " WHERE id=:id");
        $statement->bindValue('id', $id, \PDO::PARAM_STR);
        $statement->execute();

        return $statement->fetchColumn();
    }
}
