# 🏥 ITZAM - Sistema Web de Gestión Clínica

![Estado](https://img.shields.io/badge/Estado-Producci%C3%B3n-success)
![Versión](https://img.shields.io/badge/Versi%C3%B3n-1.0.0-blue)
![PHP](https://img.shields.io/badge/PHP-8.x-777BB4?logo=php&logoColor=white)
![MySQL](https://img.shields.io/badge/MySQL-Database-4479A1?logo=mysql&logoColor=white)

**ITZAM** es un sistema web integral diseñado para la administración y consulta de información clínica. Optimiza los flujos de trabajo de unidades médicas, desde el registro de pacientes y consultas, hasta el control de inventario y generación de órdenes de laboratorio. 

Destaca por su sólida arquitectura de seguridad, un motor de control de acceso basado en roles (RBAC) y una interfaz de usuario (UI/UX) premium, totalmente responsiva y adaptada para dispositivos móviles.

---

## ✨ Características Principales

* 🔐 **Seguridad de Nivel Empresarial:** * Encriptación de contraseñas con BCrypt.
  * Protección contra inyecciones SQL mediante consultas preparadas (PDO/MySQLi).
  * Escudos anti-IDOR (Insecure Direct Object Reference) en el backend.
  * Control estricto de sesiones con cierre automático por inactividad y validación reCAPTCHA.
* 👥 **Control de Acceso Basado en Roles (RBAC):** Vistas y permisos dinámicos según el perfil del usuario (Administrador, Médico, Enfermería, Administrativo).
* 📱 **Diseño 100% Responsivo (Mobile-First):** * Interfaces construidas con CSS Grid y Flexbox.
  * Menú de navegación tipo hamburguesa adaptativo.
  * Formularios multi-paso optimizados para pantallas táctiles.
* 📊 **Gestión Avanzada de Datos:** * Tablas interactivas con `DataTables` (búsqueda, paginación, ordenamiento).
  * Adaptabilidad móvil extrema (`DataTables Responsive`).
  * Exportación nativa de reportes a **PDF** y **CSV**.
* 🎨 **Identidad Visual Consistente:** Paleta de colores institucional basada en estándares PANTONE (Verde 626 C, Dorado 1255 C) y código CSS modular/minificado.

---

## 🧩 Módulos del Sistema

1. **Dashboard y Estadísticas:** KPIs en tiempo real de consultas, recetas, pacientes e inventario.
2. **Pacientes y Expedientes:** Registro clínico y consulta del historial médico completo.
3. **Consultas Médicas:** Formularios multi-paso (Triage, Síntomas, Diagnóstico, Tratamiento) con contadores dinámicos de caracteres.
4. **Asesorías:** Registro de atenciones rápidas e intervenciones de enfermería.
5. **Recetas y Laboratorios:** Generación de prescripciones médicas y órdenes de estudios.
6. **Inventario:** Control de entradas de medicamentos, insumos y equipo médico.
7. **Administración y Catálogos:** Gestión de personal, unidades médicas y catálogos del sistema (solo Administrador).

---

## 🛠️ Stack Tecnológico

**Frontend:**
* HTML5 (Semántico)
* CSS3 (Grid/Flexbox, Variables CSS, Minificado)
* JavaScript (ES6, Vanilla JS para interacciones DOM)
* jQuery & DataTables (Renderizado y exportación de tablas)

**Backend:**
* PHP (Lógica de negocio, gestión de sesiones, enrutamiento seguro)
* MySQL / MariaDB (Base de datos relacional)

---
