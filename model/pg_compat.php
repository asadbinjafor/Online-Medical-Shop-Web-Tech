<?php
// Keep the application's existing MySQLi-style result API while using PostgreSQL.
// All values still go through PDO parameter binding, never SQL interpolation.
class PgResult {
    public $num_rows;
    private $rows;
    private $position = 0;

    public function __construct(array $rows) {
        $this->rows = $rows;
        $this->num_rows = count($rows);
    }

    public function fetch_assoc() {
        return $this->rows[$this->position++] ?? null;
    }
}

class PgStatement {
    public $affected_rows = 0;
    private $connection;
    private $statement;
    private $sql;
    private $types = '';
    private $parameters = [];
    private $result;

    public function __construct(PgConnection $connection, string $sql) {
        $this->connection = $connection;
        $this->sql = $sql;
        $this->statement = $connection->pdo()->prepare($sql);
    }

    public function bind_param(string $types, &...$parameters): bool {
        if (strlen($types) !== count($parameters)) {
            throw new InvalidArgumentException('Parameter types do not match values.');
        }
        $this->types = $types;
        $this->parameters = [];
        foreach ($parameters as &$parameter) {
            $this->parameters[] =& $parameter;
        }
        return true;
    }

    public function execute(): bool {
        foreach ($this->parameters as $index => $value) {
            $type = $this->types[$index];
            $pdoType = $value === null ? PDO::PARAM_NULL : ($type === 'i' ? PDO::PARAM_INT : PDO::PARAM_STR);
            $this->statement->bindValue($index + 1, $value, $pdoType);
        }
        $ok = $this->statement->execute();
        $this->affected_rows = $this->statement->rowCount();
        $this->result = new PgResult($this->statement->columnCount() ? $this->statement->fetchAll(PDO::FETCH_ASSOC) : []);
        if ($ok && preg_match('/^\s*INSERT\b/i', $this->sql)) {
            $this->connection->recordInsertId();
        }
        return $ok;
    }

    public function get_result(): PgResult {
        if ($this->result === null) {
            throw new LogicException('Statement has not been executed.');
        }
        return $this->result;
    }
}

class PgConnection {
    public $connect_error = null;
    public $insert_id = 0;
    private $pdo;

    public function __construct(PDO $pdo) {
        $this->pdo = $pdo;
    }

    public function pdo(): PDO {
        return $this->pdo;
    }

    public function prepare(string $sql): PgStatement {
        return new PgStatement($this, $sql);
    }

    public function query(string $sql): PgResult {
        return new PgResult($this->pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC));
    }

    public function recordInsertId(): void {
        // PostgreSQL identity columns use sequences. LASTVAL() is connection-local.
        $this->insert_id = (int)$this->pdo->query('SELECT LASTVAL()')->fetchColumn();
    }

    public function begin_transaction(): bool {
        return $this->pdo->beginTransaction();
    }

    public function commit(): bool {
        return $this->pdo->commit();
    }

    public function rollback(): bool {
        return $this->pdo->rollBack();
    }

    public function close(): void {
        // PDO closes when the last reference is released.
    }
}
