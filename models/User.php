<?php
require_once __DIR__ . '/Model.php';

class User extends Model {
    protected $table = 'users';
    
    public function authenticate($username, $password) {
        $username = $this->sanitize($username);
        
        // Check if account is locked
        $stmt = $this->db->prepare(
            "SELECT * FROM {$this->table} 
             WHERE username = :username AND is_active = 1 LIMIT 1"
        );
        $stmt->execute([':username' => $username]);
        $user = $stmt->fetch();
        
        if (!$user) {
            $this->logAudit('login_failed', 'user', null, "Username not found: $username", null);
            return false;
        }
        
        // Check if account is locked due to brute force attempts
        if ($this->isAccountLocked($user)) {
            $this->logAudit('login_blocked', 'user', $user['id'], 'Account locked due to multiple failed attempts');
            return ['error' => 'account_locked', 'lockout_until' => $user['lockout_until']];
        }
        
        // Verify password
        if (!password_verify($password, $user['password_hash'])) {
            $this->incrementFailedAttempts($user['id']);
            $this->logAudit('login_failed', 'user', $user['id'], 'Invalid password');
            return false;
        }
        
        // Successful login - reset failed attempts
        $this->resetFailedAttempts($user['id']);
        $this->logAudit('login_success', 'user', $user['id'], 'Successful login');
        
        return $user;
    }
    
    private function isAccountLocked($user) {
        if ($user['lockout_until'] && strtotime($user['lockout_until']) > time()) {
            return true;
        }
        
        // Reset lockout if time has passed
        if ($user['lockout_until'] && strtotime($user['lockout_until']) <= time()) {
            $this->resetFailedAttempts($user['id']);
        }
        
        return false;
    }
    
    private function incrementFailedAttempts($userId) {
        $stmt = $this->db->prepare(
            "UPDATE {$this->table} 
             SET failed_login_attempts = failed_login_attempts + 1,
                 last_failed_login = NOW(),
                 lockout_until = CASE 
                     WHEN failed_login_attempts + 1 >= :max_attempts 
                     THEN DATE_ADD(NOW(), INTERVAL :lockout_time SECOND)
                     ELSE lockout_until
                 END
             WHERE id = :user_id"
        );
        
        $stmt->execute([
            ':user_id' => $userId,
            ':max_attempts' => MAX_LOGIN_ATTEMPTS,
            ':lockout_time' => LOGIN_LOCKOUT_TIME
        ]);
    }
    
    private function resetFailedAttempts($userId) {
        $stmt = $this->db->prepare(
            "UPDATE {$this->table} 
             SET failed_login_attempts = 0, 
                 last_failed_login = NULL,
                 lockout_until = NULL 
             WHERE id = :user_id"
        );
        $stmt->execute([':user_id' => $userId]);
    }
    
    public function create($data) {
        $data = $this->sanitize($data);
        
        // Validate required fields
        if (empty($data['username']) || empty($data['password']) || empty($data['email'])) {
            return ['error' => 'missing_fields'];
        }
        
        // Validate email
        if (!$this->validateEmail($data['email'])) {
            return ['error' => 'invalid_email'];
        }
        
        // Check if username or email already exists
        $stmt = $this->db->prepare(
            "SELECT id FROM {$this->table} WHERE username = :username OR email = :email"
        );
        $stmt->execute([':username' => $data['username'], ':email' => $data['email']]);
        
        if ($stmt->fetch()) {
            return ['error' => 'user_exists'];
        }
        
        // Hash password
        $passwordHash = password_hash($data['password'], PASSWORD_DEFAULT);
        
        // Insert user
        $stmt = $this->db->prepare(
            "INSERT INTO {$this->table} (username, email, password_hash, role, full_name, is_active) 
             VALUES (:username, :email, :password_hash, :role, :full_name, :is_active)"
        );
        
        $result = $stmt->execute([
            ':username' => $data['username'],
            ':email' => $data['email'],
            ':password_hash' => $passwordHash,
            ':role' => $data['role'] ?? 'asistente',
            ':full_name' => $data['full_name'],
            ':is_active' => $data['is_active'] ?? 1
        ]);
        
        if ($result) {
            $userId = $this->db->lastInsertId();
            $this->logAudit('user_created', 'user', $userId, "User created: {$data['username']}");
            return ['success' => true, 'id' => $userId];
        }
        
        return ['error' => 'creation_failed'];
    }
    
    public function update($id, $data) {
        $data = $this->sanitize($data);
        $fields = [];
        $params = [':id' => $id];
        
        if (!empty($data['email'])) {
            if (!$this->validateEmail($data['email'])) {
                return ['error' => 'invalid_email'];
            }
            $fields[] = "email = :email";
            $params[':email'] = $data['email'];
        }
        
        if (!empty($data['full_name'])) {
            $fields[] = "full_name = :full_name";
            $params[':full_name'] = $data['full_name'];
        }
        
        if (!empty($data['role'])) {
            $fields[] = "role = :role";
            $params[':role'] = $data['role'];
        }
        
        if (isset($data['is_active'])) {
            $fields[] = "is_active = :is_active";
            $params[':is_active'] = $data['is_active'];
        }
        
        if (!empty($data['password'])) {
            $fields[] = "password_hash = :password_hash";
            $params[':password_hash'] = password_hash($data['password'], PASSWORD_DEFAULT);
        }
        
        if (empty($fields)) {
            return ['error' => 'no_fields_to_update'];
        }
        
        $sql = "UPDATE {$this->table} SET " . implode(', ', $fields) . " WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        
        if ($stmt->execute($params)) {
            $this->logAudit('user_updated', 'user', $id, "User updated");
            return ['success' => true];
        }
        
        return ['error' => 'update_failed'];
    }
    
    public function getUsersByRole($role) {
        $stmt = $this->db->prepare(
            "SELECT id, username, email, full_name, is_active, created_at 
             FROM {$this->table} WHERE role = :role"
        );
        $stmt->execute([':role' => $role]);
        return $stmt->fetchAll();
    }
}
