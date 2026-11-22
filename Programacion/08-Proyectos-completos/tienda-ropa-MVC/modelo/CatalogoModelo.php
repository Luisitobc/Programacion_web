<?php
/**
 * modelo/CatalogoModelo.php
 * Consultas de apoyo: categorías y tallas (para los <select> y checkboxes).
 */
declare(strict_types=1);

class CatalogoModelo
{
    private PDO $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    public function categorias(): array
    {
        try {
            return $this->pdo
                ->query('SELECT id_categoria, nombre, descripcion FROM categoria ORDER BY nombre')
                ->fetchAll();
        } catch (PDOException $e) {
            error_log('[CatalogoModelo::categorias] ' . $e->getMessage());
            return [];
        }
    }

    public function tallas(): array
    {
        try {
            return $this->pdo
                ->query('SELECT id_talla, etiqueta FROM talla ORDER BY id_talla')
                ->fetchAll();
        } catch (PDOException $e) {
            error_log('[CatalogoModelo::tallas] ' . $e->getMessage());
            return [];
        }
    }
}
