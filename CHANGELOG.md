# Changelog

Todos los cambios notables de este proyecto serán documentados en este archivo.

El formato está basado en [Keep a Changelog](https://keepachangelog.com/es-es/1.0.0/),
y este proyecto adhiere a [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased]

### Changed

- **23 de octubre de 2025** - Actualización para versión 2 del checkout de ePayco
  - Actualizado el endpoint de validación para usar el nuevo servicio `eks-checkout-service.epayco.io`
  - Mejorada la configuración del callback para compatibilidad con checkout v2
  - Optimizada la gestión de referencias de pago y validación de transacciones
  - Actualizada la URL de validación en `callback/epayco.php` para usar el nuevo endpoint
  - Corregida la integración con el sistema de validación de referencias

### Added

- **Nuevos endpoints y servicios:**
  - Integración con `eks-checkout-service.epayco.io/validation/v1/reference/` para validación mejorada
  - Soporte mejorado para el manejo de `ref_payco` y `x_ref_payco`
  - Sistema de redirección mejorado para respuestas de pago

### Technical Details

- Modificado `modules/gateways/callback/epayco.php`:
  - Actualizada la URL de validación de referencias de pago
  - Implementada lógica mejorada para manejo de errores en validación
  - Mejorado el sistema de logging para transacciones fallidas
  - Actualizada la gestión de respuestas HTTP y códigos de estado

### Security

- Implementado manejo robusto de errores en validaciones de API
- Mejorada la validación de respuestas antes de procesar datos de transacciones
- Fortalecido el sistema de logging para audit trail de transacciones

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
