<?php
/**
 * 파일: app/Core/Model.php
 * 
 * 이 파일은 모든 모델 클래스의 기본이 되는 추상 클래스를 정의합니다.
 * 데이터베이스 테이블과 상호작용하는 기본적인 CRUD 기능을 제공하며,
 * 모든 구체적인 모델 클래스는 이 추상 클래스를 상속받아 구현됩니다.
 * 
 * @package App\Core
 */

namespace App\Core;

/**
 * 기본 모델 추상 클래스
 * 
 * 모든 모델 클래스의 기본 CRUD 기능을 제공합니다.
 */
abstract class Model
{
    protected static $table;
    protected static $primaryKey = 'id';
    protected $db;
    protected $attributes = [];

    /**
     * 모델 인스턴스를 초기화합니다.
     */
    public function __construct()
    {
        $this->db = DB::getInstance();
    }

    /**
     * 모델 속성에 접근합니다.
     * 
     * @param string $name 속성 이름
     * @return mixed|null 속성 값 또는 null
     */
    public function __get($name)
    {
        if (array_key_exists($name, $this->attributes)) {
            return $this->attributes[$name];
        }
        return null;
    }

    /**
     * 모델 속성을 설정합니다.
     * 
     * @param string $name 속성 이름
     * @param mixed $value 속성 값
     */
    public function __set($name, $value)
    {
        $this->attributes[$name] = $value;
    }

    /**
     * ID로 모델을 조회합니다.
     * 
     * @param int $id 모델 ID
     * @return static|null 모델 인스턴스 또는 null
     */
    public static function find($id)
    {
        $db = DB::getInstance();
        $sql = "SELECT * FROM " . static::$table . " WHERE " . static::$primaryKey . " = :id LIMIT 1";
        $stmt = $db->prepare($sql);
        $stmt->bindValue(':id', $id);
        $result = $db->execute($stmt);
        
        if ($result && $row = $result->fetch(\PDO::FETCH_ASSOC)) {
            $model = new static();
            $model->attributes = $row;
            return $model;
        }
        
        return null;
    }

    /**
     * 조건에 맞는 모든 모델을 조회합니다.
     * 
     * @param array $conditions 조회 조건
     * @param string|null $orderBy 정렬 조건
     * @param int|null $limit 최대 조회 개수
     * @param int|null $offset 조회 시작 위치
     * @return array 모델 인스턴스 배열
     */
    public static function all($conditions = [], $orderBy = null, $limit = null, $offset = null)
    {
        $db = DB::getInstance();
        $sql = "SELECT * FROM " . static::$table;
        
        $whereClause = [];
        $params = [];
        
        foreach ($conditions as $key => $value) {
            $whereClause[] = "$key = :$key";
            $params[":$key"] = $value;
        }
        
        if (!empty($whereClause)) {
            $sql .= " WHERE " . implode(' AND ', $whereClause);
        }
        
        if ($orderBy) {
            $sql .= " ORDER BY $orderBy";
        }
        
        if ($limit) {
            $sql .= " LIMIT $limit";
            if ($offset) {
                $sql .= " OFFSET $offset";
            }
        }
        
        $stmt = $db->prepare($sql);
        
        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value);
        }
        
        $result = $db->execute($stmt);
        $items = [];
        
        if ($result) {
            while ($row = $result->fetch(\PDO::FETCH_ASSOC)) {
                $model = new static();
                $model->attributes = $row;
                $items[] = $model;
            }
        }
        
        return $items;
    }

    /**
     * 모델을 저장합니다. (신규 생성 또는 업데이트)
     * 
     * @return bool 저장 성공 여부
     */
    public function save()
    {
        if (isset($this->attributes[static::$primaryKey]) && $this->attributes[static::$primaryKey]) {
            return $this->update();
        }
        
        return $this->insert();
    }

    /**
     * 새 모델을 데이터베이스에 삽입합니다.
     * 
     * @return bool 삽입 성공 여부
     */
    protected function insert()
    {
        $db = $this->db;
        $fields = array_keys($this->attributes);
        $placeholders = array_map(function($field) { 
            return ":$field"; 
        }, $fields);
        
        $sql = "INSERT INTO " . static::$table . " (" . implode(", ", $fields) . ") 
                VALUES (" . implode(", ", $placeholders) . ")";
        
        $stmt = $db->prepare($sql);
        
        foreach ($this->attributes as $key => $value) {
            $stmt->bindValue(":$key", $value);
        }
        
        $result = $db->execute($stmt);
        
        if ($result) {
            $this->attributes[static::$primaryKey] = $db->lastInsertId();
            return true;
        }
        
        return false;
    }

    /**
     * 기존 모델을 데이터베이스에서 업데이트합니다.
     * 
     * @return bool 업데이트 성공 여부
     */
    protected function update()
    {
        $db = $this->db;
        $fields = array_keys($this->attributes);
        $updateFields = [];
        
        foreach ($fields as $field) {
            if ($field != static::$primaryKey) {
                $updateFields[] = "$field = :$field";
            }
        }
        
        $sql = "UPDATE " . static::$table . " SET " . implode(", ", $updateFields) . 
               " WHERE " . static::$primaryKey . " = :" . static::$primaryKey;
        
        $stmt = $db->prepare($sql);
        
        foreach ($this->attributes as $key => $value) {
            $stmt->bindValue(":$key", $value);
        }
        
        $result = $db->execute($stmt);
        
        return $result ? true : false;
    }

    /**
     * ID로 모델을 삭제합니다.
     * 
     * @param int $id 삭제할 모델 ID
     * @return bool 삭제 성공 여부
     */
    public static function delete($id)
    {
        $db = DB::getInstance();
        $sql = "DELETE FROM " . static::$table . " WHERE " . static::$primaryKey . " = :id";
        $stmt = $db->prepare($sql);
        $stmt->bindValue(':id', $id);
        $result = $db->execute($stmt);
        
        return $result ? true : false;
    }

    /**
     * 모델 필드 유효성 검증
     * 
     * @param array $rules 유효성 검증 규칙
     * @return array ['isValid' => bool, 'errors' => array]
     */
    public function validateFields(array $rules): array
    {
        return validate($this->attributes, $rules);
    }
    
    /**
     * 유효성 검증 후 저장
     * 
     * @param array $rules 유효성 검증 규칙
     * @return bool 저장 성공 여부
     * @throws Exception 유효성 검사 실패 시
     */
    public function validateAndSave(array $rules): bool
    {
        $validation = $this->validateFields($rules);
        validateOrThrow($validation);
        return $this->save();
    }
} 