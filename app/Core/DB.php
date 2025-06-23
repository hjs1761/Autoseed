<?php
namespace App\Core;

use PDO;
use PDOException;
use Monolog\Logger;

/**
 * PDO 기반 데이터베이스 헬퍼 클래스
 * SQL 쿼리 실행과 트랜잭션 관리 기능 제공
 */
class DB
{
    protected PDO $pdo;
    protected Logger $logger;

    public function __construct(PDO $pdo, Logger $logger)
    {
        $this->pdo = $pdo;
        $this->logger = $logger;
    }

    /**
     * SELECT 쿼리 실행
     *
     * @param string $table 테이블명
     * @param mixed $where 조건 (배열 또는 문자열)
     * @param array $columns 조회할 컬럼 목록 (기본값: ['*'])
     * @param array $options 추가 옵션 (join, group_by, having, order_by, limit 등)
     * @return array 결과 레코드 배열 (연관배열)
     */
    public function select(string $table, $where = [], array $columns = ['*'], array $options = []): array
    {
        $colStr = implode(', ', $columns);
        $sql = "SELECT {$colStr} FROM {$table}";

        // JOIN 처리
        if (!empty($options['join']) && is_array($options['join'])) {
            foreach ($options['join'] as $join) {
                $type = $join['type'] ?? 'INNER';
                $joinTable = $join['table'] ?? '';
                $onClause  = $join['on'] ?? '';
                if ($joinTable && $onClause) {
                    $sql .= " {$type} JOIN {$joinTable} ON {$onClause}";
                }
            }
        }

        // WHERE 처리
        $whereClause = '';
        $params = [];
        if (!empty($where)) {
            if (is_array($where)) {
                $conditions = [];
                foreach ($where as $col => $val) {
                    // 조건이 배열인 경우(복합 조건) 예: ['column', 'operator', 'value']
                    if (is_array($val) && !isset($val['OR'])) {
                        if (count($val) >= 3) {
                            $column = $val[0];
                            $operator = $val[1];
                            $value = $val[2];
                            
                            $conditions[] = "{$column} {$operator} ?";
                            $params[] = $value;
                        }
                    }
                    // OR 조건 처리
                    else if (is_array($val) && isset($val['OR'])) {
                        $orConditions = [];
                        foreach ($val['OR'] as $orCond) {
                            if (is_array($orCond) && count($orCond) >= 3) {
                                $column = $orCond[0];
                                $operator = $orCond[1];
                                $value = $orCond[2];
                                
                                $orConditions[] = "{$column} {$operator} ?";
                                $params[] = $value;
                            }
                        }
                        if (count($orConditions) > 0) {
                            $conditions[] = "(" . implode(' OR ', $orConditions) . ")";
                        }
                    }
                    // 단순 조건 (컬럼 = 값)
                    else if ($val instanceof SQLValue) {
                        $conditions[] = "{$col} {$val->getValue()}";
                    } else {
                        $conditions[] = "{$col} = ?";
                        $params[] = $val;
                    }
                }
                if (count($conditions) > 0) {
                    $whereClause = " WHERE " . implode(' AND ', $conditions);
                }
            } elseif (is_string($where)) {
                $whereClause = " WHERE " . $where;
            }
        }
        $sql .= $whereClause;

        // GROUP BY 처리
        if (!empty($options['group_by'])) {
            $sql .= " GROUP BY " . $options['group_by'];
        }

        // HAVING 처리
        if (!empty($options['having'])) {
            if (is_array($options['having'])) {
                $clause = $options['having']['clause'] ?? '';
                $hvParams = $options['having']['params'] ?? [];
                if ($clause) {
                    $sql .= " HAVING " . $clause;
                    $params = array_merge($params, $hvParams);
                }
            } elseif (is_string($options['having'])) {
                $sql .= " HAVING " . $options['having'];
            }
        }

        // ORDER BY 처리
        if (!empty($options['order_by'])) {
            $sql .= " ORDER BY " . $options['order_by'];
        }

        // LIMIT 처리
        if (!empty($options['limit'])) {
            $sql .= " LIMIT " . $options['limit'];

            // OFFSET 처리
            if (!empty($options['offset'])) {
                $sql .= " OFFSET " . $options['offset'];
            }
        }

        $this->logger->debug("Select SQL: {$sql}", ['params' => $params]);

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    /**
     * INSERT 쿼리 실행
     *
     * @param string $table 테이블명
     * @param array $data 삽입할 데이터
     * @param bool $addLog 로그 기록 여부
     * @return int 삽입된 레코드의 ID
     * @throws PDOException 데이터가 비어있을 경우
     */
    public function insert(string $table, array $data, bool $addLog = true): int
    {
        if (empty($data)) {
            throw new PDOException("Insert data is empty");
        }

        $columns = array_keys($data);
        $placeholders = [];
        $params = [];

        foreach ($data as $val) {
            if ($val instanceof SQLValue) {
                $placeholders[] = $val->getValue();
            } else {
                $placeholders[] = '?';
                $params[] = $val;
            }
        }

        $colStr = implode(', ', $columns);
        $valStr = implode(', ', $placeholders);
        $sql = "INSERT INTO {$table} ({$colStr}) VALUES ({$valStr})";

        $this->logger->debug("Insert SQL: {$sql}", ['params' => $params]);

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        $insertId = (int)$this->pdo->lastInsertId();

        return $insertId;
    }

    /**
     * UPDATE 쿼리 실행
     *
     * @param string $table 테이블명
     * @param array $data 업데이트할 데이터
     * @param mixed $where 조건
     * @param bool $addLog 로그 기록 여부
     * @return bool 성공 여부
     * @throws PDOException 데이터나 조건이 비어있을 경우
     */
    public function update(string $table, array $data, $where, bool $addLog = true): bool
    {
        if (empty($data)) {
            throw new PDOException("Update data is empty");
        }
        if (empty($where)) {
            throw new PDOException("Update where condition cannot be empty (prevent full table update)");
        }

        $setParts = [];
        $params = [];

        foreach ($data as $col => $val) {
            if ($val instanceof SQLValue) {
                $setParts[] = "{$col} = {$val->getValue()}";
            } else {
                $setParts[] = "{$col} = ?";
                $params[] = $val;
            }
        }

        $whereClause = '';
        $whereParams = [];
        if (is_array($where)) {
            $conditions = [];
            foreach ($where as $col => $val) {
                if ($val instanceof SQLValue) {
                    $conditions[] = "{$col} = {$val->getValue()}";
                } else {
                    $conditions[] = "{$col} = ?";
                    $whereParams[] = $val;
                }
            }
            $whereClause = " WHERE " . implode(' AND ', $conditions);
        } elseif (is_string($where)) {
            $whereClause = " WHERE {$where}";
        }
        
        $allParams = array_merge($params, $whereParams);
        $sql = "UPDATE {$table} SET " . implode(', ', $setParts) . $whereClause;
        $this->logger->debug("Update SQL: {$sql}", ['params' => $allParams]);

        $stmt = $this->pdo->prepare($sql);
        $result = $stmt->execute($allParams);

        return $result;
    }

    /**
     * DELETE 쿼리 실행
     *
     * @param string $table 테이블명
     * @param mixed $where 조건
     * @param bool $addLog 로그 기록 여부
     * @return bool 성공 여부
     * @throws PDOException 조건이 비어있을 경우
     */
    public function delete(string $table, $where, bool $addLog = true): bool
    {
        if (empty($where)) {
            throw new PDOException("Delete where condition cannot be empty");
        }

        $params = [];
        $whereClause = '';

        if (is_array($where)) {
            $conditions = [];
            foreach ($where as $col => $val) {
                if ($val instanceof SQLValue) {
                    $conditions[] = "{$col} = {$val->getValue()}";
                } else {
                    $conditions[] = "{$col} = ?";
                    $params[] = $val;
                }
            }
            $whereClause = " WHERE " . implode(' AND ', $conditions);
        } elseif (is_string($where)) {
            $whereClause = " WHERE {$where}";
        }

        $sql = "DELETE FROM {$table}{$whereClause}";
        $this->logger->debug("Delete SQL: {$sql}", ['params' => $params]);

        $stmt = $this->pdo->prepare($sql);
        $result = $stmt->execute($params);

        return $result;
    }

    /**
     * raw query 실행
     * 
     * @param string $sql SQL 쿼리문
     * @param array $params 파라미터 배열
     * @return \PDOStatement PDOStatement 객체
     */
    public function query(string $sql, array $params = [])
    {
        $this->logger->debug("Raw Query: {$sql}", ['params' => $params]);
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt;
    }

    /**
     * 트랜잭션 시작
     * 
     * @return bool 성공 여부
     */
    public function beginTransaction(): bool
    {
        return $this->pdo->beginTransaction();
    }

    /**
     * 트랜잭션 커밋
     * 
     * @return bool 성공 여부
     */
    public function commit(): bool
    {
        return $this->pdo->commit();
    }

    /**
     * 트랜잭션 롤백
     * 
     * @return bool 성공 여부
     */
    public function rollback(): bool
    {
        return $this->pdo->rollBack();
    }

    /**
     * 트랜잭션 상태 확인
     * 
     * @return bool 트랜잭션 진행 중 여부
     */
    public function inTransaction(): bool
    {
        return $this->pdo->inTransaction();
    }
}
