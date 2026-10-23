<?php

namespace Database\Seeders;

use App\Models\Permiso;
use Illuminate\Database\Seeder;

class PermisoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * Estructura modular: modulo.apartado.accion
     */
    public function run(): void
    {
        $permisos = [
            // ==========================================
            // 1. MÓDULO ADMINISTRACIÓN
            // ==========================================
            // Apartado Sucursales
            [
                'nombre' => 'administracion.sucursales.ver',
                'descripcion' => 'Ver listado y detalles de sucursales',
            ],
            [
                'nombre' => 'administracion.sucursales.crear',
                'descripcion' => 'Crear nuevas sucursales',
            ],
            [
                'nombre' => 'administracion.sucursales.editar',
                'descripcion' => 'Modificar sucursales existentes',
            ],
            [
                'nombre' => 'administracion.sucursales.eliminar',
                'descripcion' => 'Eliminar o desactivar sucursales',
            ],
            [
                'nombre' => 'administracion.sucursales.puntos-venta',
                'descripcion' => 'Gestionar puntos de venta y códigos CUIS/CUFD de sucursales',
            ],

            // Apartado Usuarios
            [
                'nombre' => 'administracion.usuarios.ver',
                'descripcion' => 'Ver listado y datos de usuarios',
            ],
            [
                'nombre' => 'administracion.usuarios.crear',
                'descripcion' => 'Registrar nuevos usuarios',
            ],
            [
                'nombre' => 'administracion.usuarios.editar',
                'descripcion' => 'Editar datos de usuarios',
            ],
            [
                'nombre' => 'administracion.usuarios.eliminar',
                'descripcion' => 'Eliminar o desactivar usuarios',
            ],
            [
                'nombre' => 'administracion.usuarios.permisos',
                'descripcion' => 'Asignar permisos específicos a usuarios',
            ],

            // Apartado Roles
            [
                'nombre' => 'administracion.roles.ver',
                'descripcion' => 'Ver listado de roles del sistema',
            ],
            [
                'nombre' => 'administracion.roles.crear',
                'descripcion' => 'Crear nuevos roles',
            ],
            [
                'nombre' => 'administracion.roles.editar',
                'descripcion' => 'Modificar roles existentes',
            ],
            [
                'nombre' => 'administracion.roles.eliminar',
                'descripcion' => 'Eliminar roles',
            ],

            // Apartado Clientes
            [
                'nombre' => 'administracion.clientes.ver',
                'descripcion' => 'Ver listado de clientes',
            ],
            [
                'nombre' => 'administracion.clientes.crear',
                'descripcion' => 'Registrar nuevos clientes',
            ],
            [
                'nombre' => 'administracion.clientes.editar',
                'descripcion' => 'Modificar datos de clientes',
            ],
            [
                'nombre' => 'administracion.clientes.eliminar',
                'descripcion' => 'Eliminar clientes',
            ],

            // Apartado Proveedores
            [
                'nombre' => 'administracion.proveedores.ver',
                'descripcion' => 'Ver catálogo de laboratorios y proveedores',
            ],
            [
                'nombre' => 'administracion.proveedores.crear',
                'descripcion' => 'Registrar nuevos proveedores',
            ],
            [
                'nombre' => 'administracion.proveedores.editar',
                'descripcion' => 'Editar información de proveedores',
            ],
            [
                'nombre' => 'administracion.proveedores.eliminar',
                'descripcion' => 'Eliminar proveedores',
            ],

            // ==========================================
            // 2. MÓDULO SINCRONIZACIÓN SIAT
            // ==========================================
            // Apartado Catálogos SIN
            [
                'nombre' => 'sincronizacion.catalogos.ver',
                'descripcion' => 'Ver estado de catálogos sincronizados del SIN',
            ],
            [
                'nombre' => 'sincronizacion.catalogos.sincronizar',
                'descripcion' => 'Ejecutar sincronización SOAP de catálogos paramétricos del SIAT',
            ],

            // Apartado Eventos Significativos (Contingencias)
            [
                'nombre' => 'sincronizacion.eventos.ver',
                'descripcion' => 'Ver registro de contingencias y eventos significativos',
            ],
            [
                'nombre' => 'sincronizacion.eventos.crear',
                'descripcion' => 'Registrar inicio de evento significativo ante el SIAT',
            ],
            [
                'nombre' => 'sincronizacion.eventos.cerrar',
                'descripcion' => 'Cerrar evento significativo y empaquetar facturas',
            ],
            [
                'nombre' => 'sincronizacion.paquetes.enviar',
                'descripcion' => 'Empaquetar y enviar facturas de contingencia al SIAT',
            ],
            [
                'nombre' => 'sincronizacion.paquetes.validar',
                'descripcion' => 'Consultar y validar estado de paquetes de contingencia en el SIAT',
            ],

            // ==========================================
            // 3. MÓDULO INVENTARIOS Y MEDICAMENTOS
            // ==========================================
            // Apartado Productos
            [
                'nombre' => 'inventario.productos.ver',
                'descripcion' => 'Ver catálogo de productos farmacéuticos',
            ],
            [
                'nombre' => 'inventario.productos.crear',
                'descripcion' => 'Dar de alta nuevos medicamentos y productos',
            ],
            [
                'nombre' => 'inventario.productos.editar',
                'descripcion' => 'Modificar ficha técnica y precios de medicamentos',
            ],
            [
                'nombre' => 'inventario.productos.eliminar',
                'descripcion' => 'Eliminar medicamentos del catálogo',
            ],

            // Apartado Lotes y Vencimientos (FEFO)
            [
                'nombre' => 'inventario.lotes.ver',
                'descripcion' => 'Ver trazabilidad de lotes y fechas de vencimiento',
            ],
            [
                'nombre' => 'inventario.lotes.crear',
                'descripcion' => 'Registrar nuevos lotes de compra/ingreso',
            ],
            [
                'nombre' => 'inventario.lotes.editar',
                'descripcion' => 'Modificar datos de lotes y precios por lote',
            ],
            [
                'nombre' => 'inventario.lotes.ajustar',
                'descripcion' => 'Ajustar existencias o transferir lotes entre sucursales',
            ],
            [
                'nombre' => 'inventario.lotes.bloquear',
                'descripcion' => 'Bloquear lotes por alerta sanitaria o caducidad',
            ],
            [
                'nombre' => 'inventario.lotes.eliminar',
                'descripcion' => 'Eliminar o dar de baja lotes físicamente',
            ],

            // Apartado Kardex de Movimientos
            [
                'nombre' => 'inventario.kardex.ver',
                'descripcion' => 'Consultar historial y kardex físico valorado de movimientos',
            ],
            [
                'nombre' => 'inventario.kardex.crear',
                'descripcion' => 'Registrar ajustes manuales de inventario y regularizaciones',
            ],

            // ==========================================
            // 4. MÓDULO CONTROL DE CAJAS
            // ==========================================
            [
                'nombre' => 'cajas.control.ver',
                'descripcion' => 'Ver estado y reportes de cajas',
            ],
            [
                'nombre' => 'cajas.control.abrir',
                'descripcion' => 'Realizar apertura de caja y turno',
            ],
            [
                'nombre' => 'cajas.control.cerrar',
                'descripcion' => 'Realizar arqueo y cierre de caja',
            ],
            [
                'nombre' => 'cajas.control.movimientos',
                'descripcion' => 'Registrar ingresos y egresos menores de caja',
            ],

            // ==========================================
            // 5. MÓDULO VENTAS Y POS FARMACIA
            // ==========================================
            [
                'nombre' => 'ventas.pos.ver',
                'descripcion' => 'Acceder a la pantalla del terminal de punto de venta (POS)',
            ],
            [
                'nombre' => 'ventas.pos.vender',
                'descripcion' => 'Cobrar y emitir facturas computarizadas en línea SIAT',
            ],
            [
                'nombre' => 'ventas.pos.descuentos',
                'descripcion' => 'Aplicar descuentos especiales en la venta',
            ],
            [
                'nombre' => 'ventas.facturas.ver',
                'descripcion' => 'Consultar historial de facturas emitidas',
            ],
            [
                'nombre' => 'ventas.facturas.imprimir',
                'descripcion' => 'Reimprimir tickets o facturas con código QR',
            ],
            [
                'nombre' => 'ventas.facturas.anular',
                'descripcion' => 'Anular facturas previamente emitidas ante el SIAT',
            ],
            [
                'nombre' => 'ventas.facturas.revertir_anulacion',
                'descripcion' => 'Revertir anulación de facturas ante el SIAT',
            ],
            // ==========================================
            // 6. MÓDULO SINCRONIZACIÓN Y SIAT
            // ==========================================
            [
                'nombre' => 'sincronizacion.catalogos.ver',
                'descripcion' => 'Ver catálogos sincronizados del SIAT',
            ],
            [
                'nombre' => 'sincronizacion.catalogos.sincronizar',
                'descripcion' => 'Sincronizar catálogos y listas desplegables del SIAT',
            ],
            [
                'nombre' => 'sincronizacion.eventos.ver',
                'descripcion' => 'Consultar eventos significativos y contingencias',
            ],
            [
                'nombre' => 'sincronizacion.eventos.crear',
                'descripcion' => 'Registrar y cerrar eventos significativos ante el SIAT',
            ],
            [
                'nombre' => 'sincronizacion.paquetes.enviar',
                'descripcion' => 'Empaquetar y transmitir facturas en contingencia al SIAT',
            ],
            [
                'nombre' => 'sincronizacion.paquetes.validar',
                'descripcion' => 'Validar paquetes de contingencia transmitidos ante el SIAT',
            ],
        ];

        foreach ($permisos as $p) {
            Permiso::updateOrCreate(
                ['nombre' => $p['nombre']],
                ['descripcion' => $p['descripcion']]
            );
        }
    }
}
