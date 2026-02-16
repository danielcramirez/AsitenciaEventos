<?php
declare(strict_types=1);

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/logger.php';

/**
 * Sistema de Control de Permisos por Rol
 */
class PermissionManager {
    
    /**
     * Obtener rol del usuario actual
     */
    public static function getCurrentRole(): string {
        if (isset($_SESSION['user_id'])) {
            return $_SESSION['role'] ?? ROLE_GUEST;
        }
        return ROLE_GUEST;
    }

    /**
     * Obtener ID del usuario actual
     */
    public static function getCurrentUserId(): ?int {
        return $_SESSION['user_id'] ?? null;
    }

    /**
     * Verificar si el usuario está autenticado
     */
    public static function isAuthenticated(): bool {
        return isset($_SESSION['user_id']) && $_SESSION['user_id'] > 0;
    }

    /**
     * Verificar si el usuario tiene un rol específico
     */
    public static function hasRole(string $role): bool {
        return self::getCurrentRole() === $role;
    }

    /**
     * Verificar si el usuario es admin
     */
    public static function isAdmin(): bool {
        return self::hasRole(ROLE_ADMIN);
    }

    /**
     * Verificar si el usuario es operador
     */
    public static function isOperator(): bool {
        return self::hasRole(ROLE_OPERATOR);
    }

    /**
     * Verificar si tiene permiso para una acción
     */
    public static function hasPermission(string $action): bool {
        $role = self::getCurrentRole();
        
        if (!isset(ROLE_PERMISSIONS[$role])) {
            return false;
        }

        $allowed = ROLE_PERMISSIONS[$role][$action] ?? false;
        
        // Log de intento de acceso
        if (!$allowed) {
            Logger::getInstance()->warning("Acceso denegado", [
                'user_id' => self::getCurrentUserId(),
                'role' => $role,
                'action' => $action,
                'ip' => $_SERVER['REMOTE_ADDR'] ?? 'UNKNOWN',
            ]);
        }

        return $allowed;
    }

    /**
     * Require que el usuario tenga un rol específico
     */
    public static function requireRole(string $role): void {
        if (self::getCurrentRole() !== $role) {
            http_response_code(403);
            Logger::getInstance()->warning("Rol insuficiente", [
                'user_id' => self::getCurrentUserId(),
                'required_role' => $role,
                'current_role' => self::getCurrentRole(),
                'ip' => $_SERVER['REMOTE_ADDR'] ?? 'UNKNOWN',
            ]);
            exit('Acceso denegado: requiere rol ' . h($role));
        }
    }

    /**
     * Require que el usuario esté autenticado
     */
    public static function requireAuth(): void {
        if (!self::isAuthenticated()) {
            // Guardar URL solicitada para redirigir después del login
            $_SESSION['redirect_after_login'] = $_SERVER['REQUEST_URI'] ?? '/eventos';
            
            Logger::getInstance()->info("Redirect a login desde", [
                'requested_url' => $_SESSION['redirect_after_login'],
                'ip' => $_SERVER['REMOTE_ADDR'] ?? 'UNKNOWN',
            ]);
            
            header('Location: /eventos/login');
            exit;
        }
    }

    /**
     * Require que el usuario tenga permiso para una acción
     */
    public static function requirePermission(string $action): void {
        if (!self::hasPermission($action)) {
            http_response_code(403);
            exit('No tiene permiso para: ' . h($action));
        }
    }

    /**
     * Obtener todas las acciones permitidas para el rol actual
     */
    public static function getAllowedActions(): array {
        $role = self::getCurrentRole();
        $permissions = ROLE_PERMISSIONS[$role] ?? [];
        
        return array_keys(array_filter($permissions, fn($v) => $v === true));
    }

    /**
     * Verificar si el usuario es propietario del recurso
     * (para acceso restringido a datos propios)
     */
    public static function isResourceOwner(int $resourceUserId): bool {
        $currentUserId = self::getCurrentUserId();
        
        // Admin puede acceder a cualquier recurso
        if (self::isAdmin()) {
            return true;
        }

        // Otros roles solo pueden acceder a sus propios recursos
        return $currentUserId === $resourceUserId;
    }

    /**
     * Obtener lista de permisos para un rol
     */
    public static function getRolePermissions(string $role): array {
        return ROLE_PERMISSIONS[$role] ?? [];
    }

    /**
     * Verificar si el usuario puede administrar eventos
     */
    public static function canManageEvents(): bool {
        return self::isAdmin();
    }

    /**
     * Verificar si el usuario puede hacer check-in
     */
    public static function canCheckIn(): bool {
        return self::isAdmin() || self::isOperator();
    }

    /**
     * Verificar si el usuario puede ver reportes
     */
    public static function canViewReports(): bool {
        return self::isAuthenticated() && self::hasPermission('reporte');
    }

    /**
     * Verificar si el usuario puede exportar CSV
     */
    public static function canExportCsv(): bool {
        return self::canViewReports() && self::hasPermission('export_csv');
    }

    /**
     * Verificar si el usuario puede ver QR ajenos
     */
    public static function canViewOthersQr(): bool {
        return self::isAdmin();
    }
}

/**
 * Función global para verificar permisos fácilmente en controllers
 */
function require_permission(string $action): void {
    PermissionManager::requirePermission($action);
}

/**
 * Función global para verificar autenticación
 */
function require_auth(): void {
    PermissionManager::requireAuth();
}

/**
 * Función global para verificar rol
 */
function require_role(string $role): void {
    PermissionManager::requireRole($role);
}
