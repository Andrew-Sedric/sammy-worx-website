<?php
// Cleaned file header to avoid hidden BOM/whitespace before PHP output.
// Database Session Handler for Vercel compatibility
class DatabaseSessionHandler implements SessionHandlerInterface {
    private $conn;
    private $session_lifetime;

    public function __construct(mysqli $conn) {
        $this->conn = $conn;
        $this->session_lifetime = 86400; // 24 hours
    }

    public function open(string $save_path, string $session_name): bool {
        return true;
    }

    public function close(): bool {
        return true;
    }

    public function read(string $session_id): string {
        $stmt = $this->conn->prepare("SELECT user_id, username, login_time FROM sessions WHERE session_id = ? AND last_activity > DATE_SUB(NOW(), INTERVAL ? SECOND)");
        $stmt->bind_param("si", $session_id, $this->session_lifetime);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $session_data = $result->fetch_assoc();
            // Update last activity
            $update_stmt = $this->conn->prepare("UPDATE sessions SET last_activity = NOW() WHERE session_id = ?");
            $update_stmt->bind_param("s", $session_id);
            $update_stmt->execute();
            $update_stmt->close();

            // Return session data as serialized array
            return serialize([
                'admin_logged_in' => true,
                'user_id' => $session_data['user_id'],
                'username' => $session_data['username'],
                'login_time' => strtotime($session_data['login_time'])
            ]);
        }

        $stmt->close();
        return '';
    }

    public function write(string $session_id, string $session_data): bool {
        $data = @unserialize($session_data);

        if (!is_array($data) || !isset($data['admin_logged_in']) || !$data['admin_logged_in']) {
            // Not a valid admin session, don't store
            return true;
        }

        $user_id = $data['user_id'];
        $username = $data['username'];
        $ip_address = $_SERVER['REMOTE_ADDR'] ?? '';
        $user_agent = $_SERVER['HTTP_USER_AGENT'] ?? '';

        // Insert or update session
        $stmt = $this->conn->prepare("INSERT INTO sessions (session_id, user_id, username, ip_address, user_agent)
                                     VALUES (?, ?, ?, ?, ?)
                                     ON DUPLICATE KEY UPDATE last_activity = NOW(), ip_address = VALUES(ip_address), user_agent = VALUES(user_agent)");
        $stmt->bind_param("sisss", $session_id, $user_id, $username, $ip_address, $user_agent);
        $result = $stmt->execute();
        $stmt->close();

        return $result;
    }

    public function destroy(string $session_id): bool {
        $stmt = $this->conn->prepare("DELETE FROM sessions WHERE session_id = ?");
        $stmt->bind_param("s", $session_id);
        $result = (bool) $stmt->execute();
        $stmt->close();
        return $result;
    }

    public function gc(int $maxlifetime): int|false {
        $stmt = $this->conn->prepare("DELETE FROM sessions WHERE last_activity < DATE_SUB(NOW(), INTERVAL ? SECOND)");
        $stmt->bind_param("i", $this->session_lifetime);
        $result = $stmt->execute();
        $deleted = $stmt->affected_rows;
        $stmt->close();
        return $result ? $deleted : false;
    }

    public function create_session($user_id, $username) {
        $session_id = session_create_id();
        $ip_address = $_SERVER['REMOTE_ADDR'] ?? '';
        $user_agent = $_SERVER['HTTP_USER_AGENT'] ?? '';

        $stmt = $this->conn->prepare("INSERT INTO sessions (session_id, user_id, username, ip_address, user_agent) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("sisss", $session_id, $user_id, $username, $ip_address, $user_agent);
        $result = $stmt->execute();
        $stmt->close();

        if ($result) {
            session_id($session_id);
            return $session_id;
        }

        return false;
    }

    public function validate_session($session_id) {
        $stmt = $this->conn->prepare("SELECT user_id, username FROM sessions WHERE session_id = ? AND last_activity > DATE_SUB(NOW(), INTERVAL ? SECOND)");
        $stmt->bind_param("si", $session_id, $this->session_lifetime);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $session_data = $result->fetch_assoc();
            // Update last activity
            $update_stmt = $this->conn->prepare("UPDATE sessions SET last_activity = NOW() WHERE session_id = ?");
            $update_stmt->bind_param("s", $session_id);
            $update_stmt->execute();
            $update_stmt->close();

            $stmt->close();
            return $session_data;
        }

        $stmt->close();
        return false;
    }
}