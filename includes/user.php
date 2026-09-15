<?php
/**
 * Classe para gerenciar usuários
 */

class User {
    private $conn;
    
    public function __construct($connection) {
        $this->conn = $connection;
    }
    
    /**
     * Registra um novo usuário (sem username, usa email como login)
     */
    public function register($email, $first_name, $last_name, $password) {
        // Verifica se email já existe
        $stmt = $this->conn->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        
        if ($stmt->get_result()->num_rows > 0) {
            return ['success' => false, 'message' => 'Este email já está registrado'];
        }
        
        // Validar domínio de email
        if (!preg_match('/@mcmtocantins\.com$/', $email)) {
            return ['success' => false, 'message' => 'Apenas emails @mcmtocantins.com são permitidos'];
        }
        
        // Hash da senha
        $hashed_password = password_hash($password, PASSWORD_BCRYPT);
        
        // Gera token de verificação
        $verification_token = bin2hex(random_bytes(32));
        
        // Gera um username único baseado no email
        $username = explode('@', $email)[0]; // Pega a parte antes do @
        
        // Insere novo usuário
        $stmt = $this->conn->prepare(
            "INSERT INTO users (username, email, first_name, last_name, password, verification_token, email_verified, created_at) 
             VALUES (?, ?, ?, ?, ?, ?, FALSE, NOW())"
        );
        $stmt->bind_param("ssssss", $username, $email, $first_name, $last_name, $hashed_password, $verification_token);
        
        if ($stmt->execute()) {
            // Envia email de verificação (sem falhar se não conseguir)
            $email_sent = false;
            try {
                // Carrega autoload do composer
                if (file_exists(__DIR__ . '/../vendor/autoload.php')) {
                    require_once __DIR__ . '/../vendor/autoload.php';
                }
                
                if (file_exists(__DIR__ . '/EmailManager.php')) {
                    require_once __DIR__ . '/EmailManager.php';
                    $email_manager = new EmailManager();
                    $email_result = $email_manager->sendVerificationEmail($email, $first_name, $verification_token);
                    $email_sent = $email_result['success'] ?? false;
                }
            } catch (Exception $e) {
                // Log do erro mas não falha o registro
                error_log("Erro ao enviar email de verificação: " . $e->getMessage());
            }
            
            return [
                'success' => true, 
                'message' => 'Usuário registrado com sucesso! Um email de confirmação foi enviado para ' . $email . '. Clique no link do email para ativar sua conta.',
                'verification_token' => $verification_token,
                'email_sent' => $email_sent
            ];
        } else {
            $error_msg = $stmt->error ? $stmt->error : 'Erro desconhecido ao registrar usuário';
            return ['success' => false, 'message' => 'Erro ao registrar usuário: ' . $error_msg];
        }
    }
    
    
    /**
     * Valida login do usuário por EMAIL (não mais por username)
     */
    public function login($email, $password) {
        // Normalizar email: minúsculas
        $email = strtolower(trim($email));
        
        $stmt = $this->conn->prepare("SELECT id, username, first_name, last_name, email, password, email_verified, access_level, can_import, id_funcao, id_filial FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        
        $result = $stmt->get_result();
        
        if ($result->num_rows === 1) {
            $user = $result->fetch_assoc();
            
            // Verifica a senha
            if (password_verify($password, $user['password'])) {
                // Atualiza último acesso
                $update_stmt = $this->conn->prepare("UPDATE users SET last_login = NOW() WHERE id = ?");
                $update_stmt->bind_param("i", $user['id']);
                $update_stmt->execute();
                
                return [
                    'success' => true,
                    'user_id' => $user['id'],
                    'username' => $user['username'],
                    'first_name' => $user['first_name'],
                    'last_name' => $user['last_name'],
                    'email' => $user['email'],
                    'access_level' => $user['access_level'],
                    'can_import' => $user['can_import'],
                    'id_funcao' => $user['id_funcao'],
                    'id_filial' => $user['id_filial']
                ];
            }
        }
        
        return ['success' => false, 'message' => 'Email ou senha inválidos'];
    }
    
    
    /**
     * Busca um usuário por ID
     */
    public function getById($id) {
        $stmt = $this->conn->prepare("SELECT id, email, first_name, last_name, created_at, last_login FROM users WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        
        return $stmt->get_result()->fetch_assoc();
    }
    
    /**
     * Verifica email usando token
     */
    public function verifyEmail($token) {
        $stmt = $this->conn->prepare("SELECT id, email FROM users WHERE verification_token = ? AND email_verified = FALSE");
        $stmt->bind_param("s", $token);
        $stmt->execute();
        
        $result = $stmt->get_result();
        
        if ($result->num_rows === 1) {
            $user = $result->fetch_assoc();
            
            // Marca email como verificado
            $update_stmt = $this->conn->prepare("UPDATE users SET email_verified = TRUE, verification_token = NULL WHERE id = ?");
            $update_stmt->bind_param("i", $user['id']);
            
            if ($update_stmt->execute()) {
                return ['success' => true, 'message' => 'Email verificado com sucesso! Você já pode fazer login.', 'user_id' => $user['id']];
            } else {
                return ['success' => false, 'message' => 'Erro ao verificar email'];
            }
        }
        
        return ['success' => false, 'message' => 'Token inválido ou expirado'];
    }
}
