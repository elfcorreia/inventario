<?php

class Query {

    private static array $metas = [];

    private string $className;
    private array $from = [];
    private array $where = [];
    private array $args = [];

    public function __construct(string $className) {
        $this->className = $className;
        $this->registerMeta($className);
        $meta = self::$metas[$className];
        $this->from[] = [
            'table' => $meta['table'],
            'fields' => array_merge(
                $meta['pk'],
                array_keys($meta['fields']),
            )
        ];
    }

    public function __call($name, $args) {
        static $ops = [
            'Exact' => "= %s",
            'Like' => "like concat('%%', cast(%s as text), '%%')",
        ];
        if (strpos($name, 'filterBy') === 0) {
            $cmd = lcfirst(substr($name, 8));
            $op = 'Exact';
            $n = strlen($cmd);
            foreach (array_keys($ops) as $op) {
                $m = strlen($op);
                if (strpos($op, $cmd) === $n -  $m) {
                    $op = $op;
                    $cmd = substr($cmd, -$m);
                    break;
                }
            }            
            $filterName = 'F'.(count($this->where) + 1);
            $terms = [];
            foreach (explode('Or', $cmd) as $field) {
                $terms[] = $field.' '.sprintf($ops[$op], ':'.$filterName);
            }
            $this->where[] = implode(' OR ', $terms);            
            $this->args[$filterName] = $args[0];
            return $this;
        }
        throw new BadMethodCallException($name);
    }

    private function registerMeta(string $className) {
        if (!isset(self::$metas[$className])) {
            self::$metas[$className] = $className::getMeta();
        }
    }

    public function getSQL() {
        ob_start();
        echo 'SELECT ';
        $allFields = array_merge(...array_column($this->from, 'fields'));        
        $n = count($allFields);
        if ($n > 0) {
            echo $allFields[0];
        }
        for ($i = 1; $i < $n; $i++) {
            echo ', '.$allFields[$i];
        }
        echo ' FROM ';
        foreach ($this->from as $f) {
            if (isset($f['table'])) {
                echo $f['table'];
            }
        }
        if (!empty($this->where)) {
            echo ' WHERE ';
            echo implode(' AND ', $this->where);
        }
        $s = ob_get_contents();
        ob_end_clean();
        return $s;
    }

    public function getArgs() {
        return $this->args;
    }
}

trait PDOActiveRecord {

    public static function getMeta() {
        static $meta = null;
        if ($meta === null) {
            $meta = self::makeMeta();
            if (!array_key_exists('fields', $meta)) {
                $r = new ReflectionClass(self::class);
                $meta['fields'] = [];
                foreach ($r->getProperties(ReflectionProperty::IS_PRIVATE) as $p) {
                    if (array_search($p->name, $meta['pk']) === false) {
                        $meta['fields'][$p->name] = strval($p->getType());
                    }
                }
            }
        }        
        return $meta;
    }

    public static function query() {
        return new Query(self::class);
    }

    protected function getUpdateSQL() {
        static $sql = null;
        $meta = $this->getMeta();
        if ($sql === null) {
            ob_start();
            echo 'UPDATE ';
            echo $meta['table'];
            echo ' SET ';
            $fields = array_keys($meta['fields']);
            $n = count($fields);
            if ($n > 0) {
                echo $fields[0].' = :'.$fields[0];
            }
            for ($i = 1; $i < $n; $i++) {
                echo ', '.$fields[0].' = :'.$fields[0];
            }
            echo ' WHERE ';
            $pks = array_keys($meta['pk']);
            $n = count($pks);
            if ($n > 0) {
                echo $pks[0].' = :'.$pks[0];
            }
            for ($i = 1; $i < $n; $i++) {
                echo ' AND '.$pks[$i].' = :'.$pks[$i];
            }
            $sql = ob_get_contents();
            ob_end_clean();
        }
        return $sql;
    }

    protected function getInsertSQL() {
        static $sql = null;
        $meta = $this->getMeta();
        if ($sql === null) {
            ob_start();
            echo 'INSERT INTO ';
            echo $meta['table'];
            $fields = array_keys($meta['fields']);
            $n = count($fields);
            echo ' (';
            if ($n > 0) {
                echo $fields[0];
            }
            for ($i = 1; $i < $n; $i++) {
                echo ', '.$fields[$i];
            }
            echo ') VALUES (';            
            if ($n > 0) {
                echo ':'.$fields[0];
            }
            for ($i = 1; $i < $n; $i++) {
                echo ', :'.$fields[$i];
            }
            echo ') RETURNING *';            
            $sql = ob_get_contents();            
            ob_end_clean();
        }
        return $sql;
    }

    protected function getDeleteSQL() {
        static $sql = null;
        $meta = $this->getMeta();
        if ($sql === null) {
            ob_start();
            echo 'DELETE FROM ';
            echo $meta['table'];
            echo ' WHERE ';
            $pks = array_keys($meta['pk']);
            $n = count($pks);
            if ($n > 0) {
                echo $pks[0].' = :'.$pks[0];
            }
            for ($i = 1; $i < $n; $i++) {
                echo ' AND '.$pks[$i].' = :'.$pks[$i];
            }
            $sql = ob_get_contents();
            ob_end_clean();
        }
        return $sql;
    }

    public function toArray(?array $fields = null) {
        $meta = $this->getMeta();
        $a = array();
        $keys = $fields === null ? array_merge(
            $meta['pk'],
            $meta['fields']
        ) : $fields;        
        foreach ($keys as $k) {
            $v = $this->$k;
            if ($v instanceof \DateTime) {
                $v = $v->format(DATE_ATOM);
            }
            $a[$k] = $v;
        }
        return $a;
    }

    public function update(array $values) {
        foreach ($values as $k => $v) {
            $this->$k = $v;
        }
    }

    public function save(\PDO $pdo) {
        $meta = $this->getMeta();
        $has_all_pks = true;
        $pks = $meta['pk'];
        foreach ($pks as $pk) {
            if ($this->$pk === null) {
                $has_all_pks = false;
                break;
            }
        }

        if ($has_all_pks) {            
            $stmt = $pdo->prepare($this->getUpdateSQL());
            return $stmt->execute($this, $this->toArray());
        } else {
            $stmt = $pdo->prepare($this->getInsertSQL());
            $args = $this->toArray(array_keys($meta['fields']));
            $stmt->execute($args);
            $this->update($stmt->fetch());
        }
    }

    public function delete(\PDO $pdo) {
        $stmt = $pdo->prepare($this->getDeleteSQL());
        return $stmt->execute($this->toArray($meta['pk']));
    }

}

class Objeto {
    use PDOActiveRecord;

    private ?int $id = null;
    private ?string $nome = null;
    private ?string $tag = null;
    private ?\DateTime $createdAt = null;
    private ?\DateTime $modifiedAt = null;
    
    private static function makeMeta() {
        return ['table' => 'objetos', 'pk' => ['id']];
    }
    
    public function getCreatedAt() { 
        return $this->createdAt; 
    }
    public function setCreatedAt(\DateTime $value) { 
        return $this->createdAt = $value; 
    }
    public function getModifiedAt() { 
        return $this->modifiedAt; 
    }
    public function setModifiedAt(\DateTime $value) { 
        return $this->modifiedAt = $value;
    }

}