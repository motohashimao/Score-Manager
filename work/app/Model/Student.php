<?php

require_once __DIR__ . '/../utils.php';

class StudentModel {
    private $pdo;
    private $uploadDir;
    private $fields = [
        'class', 'class_no', 'last_name', 'first_name',
        'last_name_kana', 'first_name_kana', 'gender', 'birth_date',
        'tel_number', 'email', 'parent_last_name', 'parent_first_name',
        'parent_tel_number', 'memo'
    ];

    public function __construct($pdo, $uploadDir = null) {
        $this->pdo = $pdo;
        $this->uploadDir = rtrim($uploadDir ?: __DIR__ . '/../../public/uploads', '/') . '/';
    }

    private function buildNameSearchSql(&$sql, &$params, $name) {
    $columns = [
        'last_name_kana', 'first_name_kana',
        'last_name', 'first_name',
        "REPLACE(CONCAT(last_name, first_name), ' ', '')",
        "REPLACE(CONCAT(first_name, last_name), ' ', '')",
        "REPLACE(CONCAT(last_name_kana, first_name_kana), ' ', '')",
        "REPLACE(CONCAT(first_name_kana, last_name_kana), ' ', '')"
    ];

    $sql .= " AND (";
    foreach ($columns as $i => $col) {
        $key = ":name" . ($i + 1);
        $sql .= ($i > 0 ? " OR " : "") . "$col LIKE $key";
        $params[$key] = "%$name%";
    }
    $sql .= ")";
    }

    public function buildInsertSql() {
        $columns = implode(', ', array_merge($this->fields, ['image', 'student_deleted', 'created_at', 'updated_at']));
        $placeholders = implode(', ', array_map(function($f){ return ":$f"; }, $this->fields));
        $placeholders .= ', :image, :student_deleted, NOW(), NOW()';
        return "INSERT INTO students ($columns) VALUES ($placeholders)";
    }

    public function buildUpdateSql() {
        $set = implode(', ', array_map(function($f){ return "$f = :$f"; }, $this->fields));
        $set .= ', updated_at = NOW()';
        return "UPDATE students SET $set WHERE id = :id";
    }

    public function collectStudentPostData() {
        $data = [];
        foreach ($this->fields as $field) {
            $data[$field] = isset($_POST[$field]) ? $_POST[$field] : '';
        }
        return $data;
    }

    public function prepareStudentInsertParams($data) {
        $params = [];
        foreach ($data as $key => $value) {
            $params[":$key"] = ($value === '') ? null : $value;
        }
        $params[':image'] = isset($data['image']) ? $data['image'] : 'no-image.png';
        $params[':student_deleted'] = 0;
        return $params;
    }

    public function prepareStudentUpdateParams($data, $id) {
        $params = [];
        foreach ($data as $key => $value) {
            $params[":$key"] = ($value === '') ? null : $value;
        }
        $params[':id'] = $id;
        return $params;
    }

    public function fetchStudentById(int $id): ?array {
        $stmt = $this->pdo->prepare("SELECT * FROM students WHERE id = :id");
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }

    // 件数取得（条件 optional）
    public function getStudentCount($class = '', $name = '') {
        $sql = "SELECT COUNT(*) FROM students WHERE 1=1";
        $params = array();

        if ($class !== '') {
            $sql .= " AND class = :class";
            $params[':class'] = $class;
        }

        if ($name !== '') {
            $name = preg_replace('/[\s　]+/u', '', $name);
            $this->buildNameSearchSql($sql, $params, $name);
        }

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return (int)$stmt->fetchColumn();
    }

    public function getPhotoPath($filename) {
        if (!$filename || $filename === 'noimage' || $filename === 'no-image.png') {
            return '/image/noimage.png';
        }
        return '/uploads/' . $filename;
    }

    public function uploadPhoto($student_id, $file) {
        if (!isset($file['tmp_name']) || $file['error'] !== UPLOAD_ERR_OK) return null;

        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        $filename = "student_{$student_id}." . $ext;
        $target = $this->uploadDir . $filename;

        if (move_uploaded_file($file['tmp_name'], $target)) {
            return $filename;
        }
        return null;
    }

    public function deleteStudentPhoto($student_id) {
        foreach (glob($this->uploadDir . "student_{$student_id}.*") as $file) {
            if (is_file($file)) unlink($file);
        }
    }

    // 成績管理
    public function updateTestScores(int $student_id, array $scores): void {
        $subjectMap = getSubjects();
        $stmt = $this->pdo->query("SELECT id, name FROM subjects");
        $subjectIds = [];
        foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
            $subjectIds[$row['name']] = $row['id'];
        }

        foreach ($scores as $test_id => $subjects) {
            foreach ($subjects as $sub_key => $score) {
                if (!isset($subjectMap[$sub_key])) continue;
                $subject_id = $subjectIds[$subjectMap[$sub_key]] ?? null;
                if (!$subject_id) continue;

                $stmtCheck = $this->pdo->prepare(
                    "SELECT id FROM scores WHERE student_id=? AND test_id=? AND subject_id=?"
                );
                $stmtCheck->execute([$student_id, $test_id, $subject_id]);
                $existingId = $stmtCheck->fetchColumn();

                $score = $score === '' ? 0 : (int)$score;
                if ($existingId) {
                    $stmtUpdate = $this->pdo->prepare("UPDATE scores SET score=? WHERE id=?");
                    $stmtUpdate->execute([$score, $existingId]);
                } else {
                    $stmtInsert = $this->pdo->prepare(
                        "INSERT INTO scores (student_id,test_id,subject_id,score) VALUES (?,?,?,?)"
                    );
                    $stmtInsert->execute([$student_id, $test_id, $subject_id, $score]);
                }
            }
        }
    }

    public function deleteSelectedScores(int $student_id, array $test_ids): void {
        foreach ($test_ids as $test_id) {
            $stmt = $this->pdo->prepare("DELETE FROM scores WHERE student_id=? AND test_id=?");
            $stmt->execute([$student_id, $test_id]);
        }
    }

    public function fetchScoresGroupedByTest(int $student_id): array {
        $sql = "
            SELECT t.id AS test_id, t.test_date, t.test_cd,
                s.id AS subject_id, s.name AS subject_name, s.sort,
                COALESCE(sc.score,0) AS score
            FROM tests t
            CROSS JOIN subjects s
            LEFT JOIN scores sc
                ON sc.test_id=t.id
            AND sc.subject_id=s.id
            AND sc.student_id=:student_id
            ORDER BY t.test_date DESC, s.sort ASC
        ";
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':student_id', $student_id, PDO::PARAM_INT);
        $stmt->execute();
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $subjectMap = getSubjects();
        $grouped = [];
        foreach ($rows as $r) {
            $key = $r['test_cd'] . '_' . $r['test_date'];
            if (!isset($grouped[$key])) {
                $grouped[$key] = [
                    'test_id'   => $r['test_id'],
                    'test_date' => $r['test_date'],
                    'test_cd'   => $r['test_cd'],
                ];
                foreach ($subjectMap as $k => $_) $grouped[$key][$k] = null;
            }
            $field = array_search($r['subject_name'], $subjectMap, true);
            if ($field !== false) {
                $grouped[$key][$field] = (int)$r['score'];
            }
        }
        return array_values($grouped);
    }

    // 生徒削除（写真・成績も削除）
    public function deleteStudent($student_id) {
        // スコア削除
        $tests = $this->fetchScoresGroupedByTest($student_id);
        $test_ids = array();
        foreach ($tests as $t) $test_ids[] = $t['test_id'];
        $this->deleteSelectedScores($student_id, $test_ids);

        // 写真削除
        $this->deleteStudentPhoto($student_id);

        // 生徒情報削除
        $stmt = $this->pdo->prepare("DELETE FROM students WHERE id = ?");
        $stmt->execute(array($student_id));
    }

    // 生徒一覧取得（条件付き、ページング対応）
    public function getStudents($class = '', $name = '', $limit = 30, $offset = 0) {
        $sql = "SELECT * FROM students WHERE 1=1";
        $params = array();

        if ($class !== '') {
            $sql .= " AND class = :class";
            $params[':class'] = $class;
        }

        if ($name !== '') {
            $name = preg_replace('/[\s　]+/u', '', $name);
            $this->buildNameSearchSql($sql, $params, $name);
        }

        $sql .= " ORDER BY class ASC, class_no ASC LIMIT :limit OFFSET :offset";

        $stmt = $this->pdo->prepare($sql);
        foreach ($params as $k => $v) {
            $stmt->bindValue($k, $v);
        }
        $stmt->bindValue(':limit', (int)$limit, PDO::PARAM_INT);
        $stmt->bindValue(':offset', (int)$offset, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
