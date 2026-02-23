# Changelog

Todos los cambios notables de este proyecto serán documentados en este archivo.

El formato está basado en [Keep a Changelog](https://keepachangelog.com/es-es/1.0.0/),
y este proyecto adhiere a [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [8.4.0] - 23 de febrero de 2026

### Changed

- **Actualización para versión 2 del checkout de ePayco**

  - Mejorada la configuración del callback para compatibilidad con checkout v2
  - Optimizada la gestión de referencias de pago y validación de transacciones
  - Actualizada la URL de validación en `callback/epayco.php` para usar el nuevo endpoint
  - Corregida la integración con el sistema de validación de referencias
                                                                       - **Manejo de moneda (Oculta en configuración):**
  - La moneda se toma únicamente desde la factura
  - Se oculta la opción de moneda en la interfaz de configuración
  - Eliminada la capacidad de seleccionar moneda manualmente

- **Formato de valores mejorado:**
  - Normalización a 2 decimales exactos
  - Eliminación de separadores de miles
  - Garantizada compatibilidad con validaciones de la pasarela

### Added


- **Gestión mejorada de inventario:**
  - Aceptado → Reduce inventario
  - Fallida → Restaura inventario
  - Pendiente → Restaura inventario
  - Cancelada → Restaura inventario
  - Rechazada → Restaura inventario
  - Abandonada → Restaura inventario
  - Reintento de pago → Restaura inventario antes de generar nueva transacción

- **Prevención de pagos duplicados:**
  - Validación de `transaction_id` único en cada transacción
  - Se ignoran callbacks repetidos (idempotencia)
  - No se duplica el registro del pago ni el movimiento de inventario
  - Sistema de detección de transacciones previamente procesadas

- **Sanitización mejorada de datos:**
  - Se limita la longitud del nombre del producto
  - Se limita la longitud de la descripción
  - Se eliminan caracteres inválidos y etiquetas HTML
  - Prevención de inyección de datos maliciosos

- **Manejo mejorado de errores en checkout:**
  - Se muestran errores de sesión al usuario de forma clara
  - Evita recargas silenciosas de la página
  - Facilita el diagnóstico de fallos
  - Mensajes de error informativos para el usuario final

### Technical Details

- Modificado `modules/gateways/callback/epayco.php`:
  - Actualizada la URL de validación de referencias de pago
  - Implementada lógica mejorada para manejo de errores en validación
  - Mejorado el sistema de logging para transacciones fallidas
  - Actualizada la gestión de respuestas HTTP y códigos de estado

- **Implementación de control de inventario:**
  - Lógica de gestión de inventario basada en estados de transacción
  - Sistema de transacciones de inventario reversibles
  - Tracking de cambios de inventario en auditoría

- **Implementación de idempotencia:**
  - Sistema de detección y almacenamiento de `transaction_id`
  - Tabla de transacciones procesadas para evitar duplicados
  - Validación de integridad de datos en callbacks

- **Sanitización y validación de datos:**
  - Filtrado y validación de longitudes de strings
  - Eliminación de caracteres especiales y HTML
  - Implementación de regex para caracteres válidos

- **Mejoras en formato de moneda:**
  - Función de normalización de decimales
  - Validación de formato antes de envío a pasarela

### Security

- Implementado manejo robusto de errores en validaciones de API
- Mejorada la validación de respuestas antes de procesar datos de transacciones
- Fortalecido el sistema de logging para audit trail de transacciones

- **Seguridad en procesamiento de transacciones:**
  - Prevención de ataques de replay mediante validación de `transaction_id`
  - Protección contra inyección de datos en campos de producto y descripción
  - Validación de límites de datos para prevenir buffer overflow
  - Implementación de checksums para integridad de datos

## [Unreleased]

## [Previous Versions]

### [8.2.2] - Última versión estable

#### Funcionalidades Base

- Funcionalidades base del plugin ePayco para WHMCS
- Integración completa con API de ePayco para procesamiento de pagos
- Gestión automática de órdenes y estados de pago en WHMCS
- Sistema de validación de firmas para seguridad de transacciones

#### Características Principales

- Soporte para múltiples métodos de pago (tarjetas de crédito, débito, PSE, efectivo, etc.)
- Manejo automático de estados de facturas en WHMCS
- Sistema de callbacks para confirmación automática de pagos
- Configuración de URLs de confirmación y respuesta personalizables
- Soporte para modo de pruebas y producción
- Validación de llaves públicas y privadas
- Manejo de múltiples monedas (COP, USD)
- Sistema de logs integrado para debugging

#### Implementación Técnica

- Clase principal `EpaycoConfig` para gestión del módulo WHMCS
- Sistema de gestión de base de datos con tabla personalizada `bapp_epayco`
- Manejo de excepciones y errores robusto
- Validación de firmas para seguridad de transacciones
- API endpoints para confirmación y validación de pagos
- Sistema de hooks para integración con eventos de WHMCS
- Gestión de idiomas y localización

#### Características de Seguridad

- Validación de firmas digitales ePayco
- Manejo seguro de credenciales y tokens
- Sanitización de datos de entrada
- Protección contra acceso directo a archivos
- Validación de referencias de pago y datos de transacción
- Sistema de logging para auditoría de transacciones

#### Gestión de Transacciones

- Tabla personalizada para tracking de transacciones
- Sistema de confirmación automática via callbacks
- Manejo de referencias únicas de pago (`ref_payco`)
- Gestión de estados: pendiente, pagado, cancelado, fallido
- Sistema de notificaciones automáticas al cliente
- Integración con sistema de facturación WHMCS

#### Compatibilidad

- Compatible con WHMCS v6.2.0 a v8.2.2
- Soporte para PHP 7.0+
- Integración con base de datos MySQL/MariaDB
- Compatible con temas personalizados de WHMCS
- Soporte para instalaciones multi-idioma

### [8.2.1] - Versión anterior

#### Correcciones

- Correcciones menores en validación de transacciones
- Mejoras en el manejo de errores de conexión
- Optimizaciones en el sistema de logs

#### Nuevas funcionalidades

- Nuevas funcionalidades de configuración avanzada
- Soporte mejorado para métodos de pago adicionales
- Sistema de reportes de transacciones

#### Optimizaciones

- Optimizaciones en consultas de base de datos
- Mejoras en el sistema de cache
- Actualización de dependencias

#### Reestructuración

- Reestructuración completa del código base
- Nuevo sistema de hooks y eventos
- Interfaz de administración mejorada

#### Cambios importantes

- Cambios en la estructura de la base de datos
- Nuevos requisitos de configuración
- Actualización obligatoria de credenciales

#### Compatibilidad WHMCS 7.10

- Soporte para WHMCS 7.10.x
- Mejoras en compatibilidad
- Correcciones de bugs menores

#### Migración a WHMCS 7.x

- Soporte inicial para WHMCS 7.x
- Modernización del código base
- Mejoras en seguridad

#### Soporte WHMCS 6.2

- Soporte para WHMCS 6.2.0
- Funcionalidades básicas de pago
- Sistema de confirmación manual

#### Primera implementación

- Primera versión del plugin
- Funcionalidades básicas de integración
- Soporte para pagos con tarjeta de crédito
