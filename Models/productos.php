<?php

class Productos
{
    private mysqli $db;

//Constructor

    public function __construct(mysqli $conexion) {$this->db = $conexion;}

// Cargar productos desde la base de datos

    public function obtenerProductos()
    {
        $sql = "SELECT
                    p.id_producto,
                    p.nombre_producto,
                    p.id_categoria,
                    c.nombre_categoria,
                    p.precio_venta,
                    COALESCE(i.stock_disponible, 0) AS stock_disponible
                FROM productos p
                INNER JOIN categorias c
                    ON p.id_categoria = c.id_categoria
                LEFT JOIN inventario i
                    ON p.id_producto = i.id_producto
                ORDER BY p.id_producto DESC";

        $resultado = $this->db->query($sql);

        return $resultado->fetch_all(MYSQLI_ASSOC);
    }

// Cargar categorías

    public function obtenerCategorias()
    {
        $sql = "SELECT
                    id_categoria,
                    nombre_categoria
                FROM categorias
                ORDER BY nombre_categoria ASC";

        $resultado = $this->db->query($sql);

        return $resultado->fetch_all(MYSQLI_ASSOC);
    }
// Validar si el producto existe

public function existeProducto(string $nombre_producto)
{
    $sql = "SELECT id_producto
            FROM productos
            WHERE LOWER(nombre_producto) = LOWER(?)";

    $stmt = $this->db->prepare($sql);

    $stmt->bind_param(
        "s",
        $nombre_producto
    );

    $stmt->execute();

    $resultado = $stmt->get_result();

    return $resultado->num_rows > 0;
}

   //Crear producto

    public function crearProducto(
        string $nombre_producto,
        int $id_categoria,
        float $precio_venta,
        int $stock_inicial
    ) {
        try {

            // Iniciar transacción
            $this->db->begin_transaction();


            // Insertar producto
            $sqlProducto = "INSERT INTO productos(nombre_producto, id_categoria, precio_venta) VALUES (?, ?, ?)";

            $stmtProducto = $this->db->prepare($sqlProducto);

            $stmtProducto->bind_param(
                "sid",
                $nombre_producto,
                $id_categoria,
                $precio_venta
            );

            $stmtProducto->execute();


            // Obtener ID del producto creado
            $id_producto = $this->db->insert_id;

            // Insertar inventario
            $sqlInventario = "INSERT INTO inventario ( id_producto, stock_disponible ) VALUES (?, ?)";
                             
            $stmtInventario = $this->db->prepare($sqlInventario);

            $stmtInventario->bind_param( "ii", $id_producto, $stock_inicial );

            $stmtInventario->execute();


            // Confirmar cambios
            $this->db->commit(); return true;

        } catch (Throwable $e) {

            // Deshacer cambios si ocurre un error
            $this->db->rollback(); return false;

        }
    }

// Editar producto
    public function editarProducto(
        int $id_producto,
        string $nombre_producto,
        int $id_categoria,
        float $precio_venta,
        int $stock_disponible
    ) {
        try {

            // Iniciar transacción
            $this->db->begin_transaction();


            // 1. ACTUALIZAR PRODUCTO
            $sqlProducto = "UPDATE productos
                            SET
                                nombre_producto = ?,
                                id_categoria = ?,
                                precio_venta = ?
                            WHERE id_producto = ?";

            $stmtProducto = $this->db->prepare($sqlProducto);

            $stmtProducto->bind_param(
                "sidi",
                $nombre_producto,
                $id_categoria,
                $precio_venta,
                $id_producto
            );

            $stmtProducto->execute();


            // 2. ACTUALIZAR INVENTARIO
            $sqlInventario = "UPDATE inventario
                              SET stock_disponible = ?
                              WHERE id_producto = ?";

            $stmtInventario = $this->db->prepare($sqlInventario);

            $stmtInventario->bind_param(
                "ii",
                $stock_disponible,
                $id_producto
            );

            $stmtInventario->execute();


            // Confirmar cambios
            $this->db->commit(); return true;

        } catch (Throwable $e) 
        {
            // Deshacer cambios si algo falla
            $this->db->rollback(); return false;
        }
    }

// Eliminar producto
    public function eliminarProducto(int $id_producto)
    {
        try {
            // Iniciar transacción
            $this->db->begin_transaction();

            // 1. ELIMINAR INVENTARIO
            $sqlInventario = "DELETE FROM inventario WHERE id_producto = ?";
            $stmtInventario = $this->db->prepare($sqlInventario);
            $stmtInventario->bind_param(
                "i",
                $id_producto
            );
            $stmtInventario->execute();

            // 2. ELIMINAR PRODUCTO
            $sqlProducto = "DELETE FROM productos
                            WHERE id_producto = ?";

            $stmtProducto = $this->db->prepare($sqlProducto);

            $stmtProducto->bind_param(
                "i",
                $id_producto
            );

            $stmtProducto->execute();

            // Confirmar cambios
            $this->db->commit();

            return true;

        } catch (Throwable $e) {

            // Deshacer cambios si algo falla
            $this->db->rollback();

            return false;
        }
    }
    
    /* =====================================================
       VERIFICAR SI UNA CATEGORÍA YA EXISTE
    ===================================================== */
    public function existeCategoria(string $nombre_categoria): bool
    {
        $sql = "SELECT id_categoria
                FROM categorias
                WHERE LOWER(nombre_categoria) = LOWER(?)";

        $stmt = $this->db->prepare($sql);
        $stmt->bind_param("s", $nombre_categoria);
        $stmt->execute();

        $resultado = $stmt->get_result();
        $existe = $resultado->num_rows > 0;

        $stmt->close();

        return $existe;
    }

    /* =====================================================
       AGREGAR NUEVA CATEGORÍA
    ===================================================== */
    public function crearCategoria(string $nombre_categoria): bool
    {
        $sql = "INSERT INTO categorias (nombre_categoria)
                VALUES (?)";

        $stmt = $this->db->prepare($sql);
        $stmt->bind_param("s", $nombre_categoria);

        $resultado = $stmt->execute();

        $stmt->close();

        return $resultado;
    }

    /* =====================================================
       VERIFICAR SI UNA CATEGORÍA TIENE PRODUCTOS
    ===================================================== */
    public function categoriaTieneProductos(int $id_categoria): bool
    {
        $sql = "SELECT id_producto
                FROM productos
                WHERE id_categoria = ?
                LIMIT 1";

        $stmt = $this->db->prepare($sql);
        $stmt->bind_param("i", $id_categoria);
        $stmt->execute();

        $resultado = $stmt->get_result();
        $tieneProductos = $resultado->num_rows > 0;

        $stmt->close();

        return $tieneProductos;
    }

    /* =====================================================
       ELIMINAR CATEGORÍA
    ===================================================== */
    public function eliminarCategoria(int $id_categoria): bool
    {
        $sql = "DELETE FROM categorias
                WHERE id_categoria = ?";

        $stmt = $this->db->prepare($sql);
        $stmt->bind_param("i", $id_categoria);

        $resultado = $stmt->execute();

        $stmt->close();

        return $resultado;
    }

}

?>