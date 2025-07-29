# MaestrIA Post Generator

[![Versión Plugin WordPress](https://img.shields.io/badge/WordPress-6.8+-blue.svg)](https://wordpress.org/)
[![Versión PHP](https://img.shields.io/badge/PHP-7.4+-purple.svg)](https://php.net/)
[![Licencia](https://img.shields.io/badge/Licencia-GPLv2-green.svg)](https://www.gnu.org/licenses/gpl-2.0.html)
[![Plugin Check](https://img.shields.io/badge/Plugin%20Check-Cumple-brightgreen.svg)](https://wordpress.org/plugins/plugin-check/)

**🌐 Idioma**: [English](README.md) | [Español](#)

---

## Generador Avanzado de Contenido WordPress con IA

MaestrIA Post Generator es el plugin más avanzado de WordPress para la creación automatizada de contenido. Soporta múltiples proveedores de IA (OpenAI y DeepSeek), ofrece amplias opciones de personalización, programación automática, gestión de ideas y generación de contenido basado en artículos de referencia.

### 🚀 Novedades en la Versión 3.2.4

- **Cumplimiento Plugin Check**: Conformidad total con la herramienta oficial de validación de WordPress
- **Optimización de Rendimiento**: Consultas de base de datos mejoradas y funciones de escape
- **Internacionalización Mejorada**: Comentarios de traductores completos para todos los placeholders
- **Seguridad Reforzada**: Escape de salida optimizado y validaciones nonce mejoradas
- **Código Limpio**: Resolución de todas las advertencias de desarrollo y mejores prácticas

---

## ✨ Características Principales

### 🧠 **Integración con IA**
- **Soporte OpenAI**: GPT-4, GPT-4 Turbo, GPT-3.5 Turbo
- **Integración DeepSeek**: DeepSeek Chat, DeepSeek Coder
- **Cambio Fácil de Proveedor**: Alterna entre proveedores de IA sin problemas
- **Parámetros Avanzados**: Control de temperatura, tokens, penalizaciones

### 💡 **Sistema Avanzado de Gestión de Ideas**
- **Custom Post Type**: Almacena y gestiona ideas como CPT de WordPress
- **Gestión Individual**: Edita, elimina y organiza cada idea por separado
- **Generación Directa**: Genera posts directamente desde ideas almacenadas
- **Enfoque de Palabras Clave**: Asigna palabras clave específicas a cada idea
- **Seguimiento de Estado**: Gestión de estados disponible, usado, borrador
- **Operaciones en Lote**: Eliminación masiva, asignación de palabras clave, generación de posts

### 📝 **Opciones de Generación de Contenido**
- **Generación por Tema Libre**: Crea contenido desde cualquier tema
- **Generación Basada en Artículos**: Genera contenido basado en artículos de referencia
- **Generación Desde Ideas**: Usa ideas almacenadas para crear contenido
- **Optimización de Palabras Clave**: Contenido enfocado en SEO con palabras clave específicas
- **Selección de Categorías**: Asignación personalizada de categorías

### 🎨 **Personalización de Contenido**
- **Estilos de Escritura**: Informativo, Conversacional, Técnico, Creativo, Académico
- **Audiencia Objetivo**: General, Principiantes, Intermedio, Expertos, Profesionales
- **Opciones de Tono**: Profesional, Amigable, Serio, Humorístico, Inspirador
- **Recuento de Palabras**: Personalizable de 100-3000 palabras
- **Instrucciones Personalizadas**: Prompts de contenido personalizados
- **Niveles de Enfoque SEO**: Optimización baja, media, alta

### 📅 **Programación y Automatización**
- **Programación Automática**: Publicación diaria, semanal, quincenal, mensual
- **Priorización de Ideas**: Usa ideas almacenadas antes que temas manuales
- **Horarios Personalizados**: Establece días y horas específicas para publicar
- **Limpieza Automática**: Eliminación automática opcional de ideas usadas
- **Estadísticas en Tiempo Real**: Panel de estadísticas de ideas en vivo

### 🌐 **Soporte Multiidioma**
- **Idiomas de Interfaz**: Español, Inglés, Ruso
- **Generación de Contenido**: Genera contenido en múltiples idiomas
- **Listo para Traducir**: Soporte completo de internacionalización
- **Soporte RTL**: Compatibilidad con idiomas de derecha a izquierda

---

## 🔧 Requisitos Técnicos

- **WordPress**: 5.0 o superior (probado hasta 6.8)
- **PHP**: 7.4 o superior
- **Claves API**: Se requieren claves API de OpenAI y/o DeepSeek
- **Memoria**: Recomendado límite de memoria PHP de 128MB
- **Base de Datos**: MySQL 5.6+ o MariaDB 10.1+

---

## 📦 Instalación

### Instalación Automática
1. Ve al panel de administración de WordPress
2. Navega a **Plugins > Añadir Nuevo**
3. Busca "MaestrIA Post Generator"
4. Haz clic en **Instalar Ahora** y luego **Activar**

### Instalación Manual
1. **Descarga** los archivos del plugin
2. **Sube** al directorio `/wp-content/plugins/miapg-post-generator/`
3. **Activa** el plugin a través de la pantalla 'Plugins' de WordPress
4. **Configura** tu proveedor de IA y clave API

### Configuración Inicial
1. Ve a **Auto Post Generator** en tu menú de administración de WordPress
2. Navega a la pestaña **Configuración General**
3. Selecciona tu proveedor de IA (OpenAI o DeepSeek)
4. Introduce tu clave API
5. Configura los ajustes por defecto
6. ¡Comienza a generar contenido!

---

## ⚙️ Guía de Configuración

### Configuración General
- **Proveedor de IA**: Elige entre OpenAI o DeepSeek
- **Claves API**: Configura tus claves API de OpenAI y/o DeepSeek
- **Idioma de Interfaz**: Selecciona tu idioma de interfaz preferido
- **Categoría por Defecto**: Establece la categoría por defecto para posts generados
- **Etiquetas por Defecto**: Configura etiquetas por defecto (separadas por comas)
- **Estado del Post**: Elige el estado por defecto del post (publicar, borrador, futuro)

### Configuración de IA
- **Modelo OpenAI**: Selecciona entre GPT-4, GPT-4 Turbo, GPT-3.5 Turbo
- **Modelo DeepSeek**: Elige entre DeepSeek Chat, DeepSeek Coder
- **Temperatura**: Controla la creatividad (0.0-2.0)
- **Tokens Máximos**: Establece la longitud máxima de respuesta
- **Penalización de Frecuencia**: Reduce repetición (-2.0 a 2.0)
- **Penalización de Presencia**: Fomenta diversidad de temas (-2.0 a 2.0)

### Configuración de Contenido
- **Estilo de Escritura**: Elige tu enfoque de escritura preferido
- **Audiencia Objetivo**: Define los lectores objetivo de tu contenido
- **Tono**: Establece el tono emocional de tu contenido
- **Enfoque SEO**: Configura el nivel de optimización SEO
- **Recuento de Palabras**: Establece el recuento por defecto de palabras para posts
- **Instrucciones Personalizadas**: Añade prompts personalizados

### Configuración de Programación
- **Habilitar Programador**: Activa/desactiva publicación automática
- **Frecuencia**: Establece frecuencia de publicación (diaria, semanal, etc.)
- **Hora**: Elige horas específicas de publicación
- **Días**: Selecciona qué días publicar
- **Eliminar Ideas Automáticamente**: Remueve automáticamente ideas usadas

---

## 🔐 Servicios Externos

Este plugin se conecta a servicios de IA externos para generar contenido:

### API de OpenAI
- **Proveedor del Servicio**: OpenAI Inc. (https://openai.com)
- **Propósito**: Generación de contenido usando modelos GPT
- **Datos Transmitidos**: 
  - Prompts de texto y temas de contenido
  - Parámetros de configuración de API
  - Tu clave API personal de OpenAI
- **Cuándo se Envían Datos**:
  - Al generar posts automáticamente
  - Al generar ideas de posts
  - Al crear títulos de artículos
  - Al validar tu clave API de OpenAI
- **Términos de Servicio**: https://openai.com/terms/
- **Política de Privacidad**: https://openai.com/privacy/

### API de DeepSeek AI
- **Proveedor del Servicio**: DeepSeek AI (https://www.deepseek.com)
- **Propósito**: Generación alternativa de contenido con IA
- **Datos Transmitidos**:
  - Prompts de texto y temas de contenido
  - Parámetros de configuración de API
  - Tu clave API personal de DeepSeek
- **Cuándo se Envían Datos**:
  - Al generar contenido con DeepSeek seleccionado como proveedor
  - Al generar ideas usando DeepSeek
  - Al crear títulos con DeepSeek
  - Al validar tu clave API de DeepSeek
- **Términos de Servicio**: https://www.deepseek.com/terms
- **Política de Privacidad**: https://www.deepseek.com/privacy

### Aviso Importante
**Responsabilidad del Usuario**: Este plugin requiere que proporciones tus propias claves API para estos servicios. Los datos se transmiten directamente desde tu sitio web a los proveedores de IA seleccionados. Eres responsable del cumplimiento con sus términos de servicio y políticas de privacidad. El plugin no almacena ni procesa estos datos; simplemente actúa como intermediario para solicitudes de API.

---

## 🔄 Registro de Cambios

### Versión 3.2.4 (Actual)
- **CRÍTICO**: Cumplimiento completo con Plugin Check - herramienta oficial de validación de WordPress
- **CRÍTICO**: Corrección de todas las advertencias y errores detectados por Plugin Check
- **NUEVO**: Comentarios de traductores para todas las funciones de internacionalización con placeholders
- **NUEVO**: Escape de salida optimizado para todas las variables en admin/class-admin.php
- **NUEVO**: Uso de gmdate() en lugar de date() para evitar problemas de zona horaria
- **CORREGIDO**: Escape de $provider_status, $api_key_status, $params_valid y $capabilities_status
- **CORREGIDO**: Comentarios phpcs para error_log() condicionados por WP_DEBUG_LOG
- **CORREGIDO**: Verificaciones de nonce con comentarios phpcs para casos de solo lectura
- **CORREGIDO**: Función __() en includes/class-post-generator.php con comentario de traductor faltante
- **MEJORADO**: Consultas directas a BD con comentarios phpcs justificando su necesidad
- **MEJORADO**: Meta_query optimizada en scheduler con comentario explicativo
- **MEJORADO**: Cumplimiento con estándares de codificación de WordPress
- **MEJORADO**: Código preparado para aprobación en directorio oficial de WordPress

### Versión 3.2.3
- **CRÍTICO**: Implementación completa de verificaciones nonce para prevenir ataques CSRF
- **CRÍTICO**: Validación exhaustiva de permisos de usuario en todas las operaciones sensibles
- **NUEVO**: Protección de URLs con idea_id mediante nonces de seguridad en create-tab.php
- **NUEVO**: Verificación de nonce en acceso a ideas para generación de posts
- **NUEVO**: Refuerzo de seguridad en sistema de eliminación de ideas
- **CORREGIDO**: Todas las URLs con parámetros ID ahora incluyen verificación wp_nonce_url()
- **CORREGIDO**: Separación de lógica de verificación de nonce de otras condiciones
- **CORREGIDO**: Validación de permisos específicos (edit_miapg_post_ideas, delete_miapg_post_ideas)
- **MEJORADO**: Seguridad en admin-pages.php con verificación dual de nonces
- **MEJORADO**: Protección en class-post-ideas-cpt.php para todas las acciones de ideas
- **MEJORADO**: Sistema de notificaciones con autenticación mejorada en class-admin.php
- **MEJORADO**: Cumplimiento total con estándares de seguridad del directorio WordPress

[Ver registro completo de cambios](CHANGELOG.md)

---

## 🤝 Contribuir

¡Damos la bienvenida a las contribuciones de la comunidad! Así es como puedes ayudar:

### Configuración de Desarrollo
1. **Haz Fork** del repositorio
2. **Clona** tu fork localmente
3. **Crea** una nueva rama para tu característica
4. **Instala** dependencias de desarrollo
5. **Realiza** tus cambios
6. **Prueba** exhaustivamente
7. **Envía** un pull request

### Estándares de Codificación
- Sigue los estándares de codificación de WordPress
- Usa documentación apropiada
- Incluye comentarios de traductor para internacionalización
- Prueba con la herramienta Plugin Check
- Asegura compatibilidad con PHP 7.4+

### Tipos de Contribuciones
- 🐛 Corrección de errores
- ✨ Nuevas características
- 📚 Mejoras de documentación
- 🌐 Traducciones
- 🧪 Pruebas
- 💡 Sugerencias de características

---

## 📞 Soporte

### Obtener Ayuda
Si encuentras problemas o tienes preguntas:

1. **Revisa la sección FAQ** más abajo
2. **Busca problemas existentes** en este repositorio
3. **Visita nuestra página de soporte**: [WebDesignerK Soporte](https://webdesignerk.com/support)
4. **Crea un nuevo problema** con información detallada
5. **Contáctanos** a través de nuestro sitio web

### Reportes de Errores
Al reportar errores, por favor incluye:
- Versión de WordPress
- Versión de PHP
- Versión del plugin
- Pasos para reproducir
- Comportamiento esperado vs real
- Mensajes de error (si los hay)

---

## 🙋‍♂️ Preguntas Frecuentes

### ¿Cómo obtengo las claves API?
- **OpenAI**: Visita [OpenAI Platform](https://platform.openai.com/api-keys) y crea una cuenta
- **DeepSeek**: Visita [DeepSeek Platform](https://platform.deepseek.com/api_keys) y regístrate

### ¿Puedo usar ambos proveedores de IA?
¡Sí! Puedes configurar ambas claves API y cambiar entre proveedores según sea necesario para diferentes tipos de contenido.

### ¿El contenido generado está optimizado para SEO?
¡Absolutamente! El plugin incluye múltiples niveles de enfoque SEO y características de optimización de palabras clave para ayudar a que tu contenido tenga mejor ranking.

### ¿Funciona con todos los temas de WordPress?
Sí, el plugin genera HTML limpio y semántico que es compatible con todos los temas de WordPress codificados apropiadamente.

### ¿Puedo personalizar el contenido generado?
Sí, tienes opciones extensas de personalización incluyendo estilo de escritura, tono, audiencia objetivo, recuento de palabras e instrucciones personalizadas.

### ¿Cómo funciona el sistema de gestión de ideas?
Las ideas se almacenan como un custom post type en WordPress, permitiéndote gestionarlas como posts regulares - editar, eliminar, categorizar y generar contenido desde ellas.

### ¿Mi clave API está segura?
Tus claves API se almacenan de forma segura en tu base de datos de WordPress y solo se usan para comunicarse con tu proveedor de IA elegido. Recomendamos usar variables de entorno para seguridad adicional.

### ¿Puedo programar publicación automática?
¡Sí! El plugin incluye un sistema de programación completo con opciones para publicación diaria, semanal, quincenal y mensual en horarios específicos.

### ¿Qué idiomas están soportados?
La interfaz soporta español, inglés y ruso. El contenido puede generarse en cualquier idioma soportado por tu proveedor de IA elegido.

### ¿Cómo actualizo el plugin?
El plugin puede actualizarse a través de tu panel de administración de WordPress cuando estén disponibles las actualizaciones, igual que cualquier otro plugin de WordPress.

---

## 📸 Capturas de Pantalla

![Panel Principal](screenshots/dashboard.png)
*Panel principal del plugin con interfaz de pestañas*

![Gestión de Ideas](screenshots/ideas-management.png)
*Sistema avanzado de gestión de ideas con operaciones en lote*

![Generación de Contenido](screenshots/content-generation.png)
*Interfaz de generación de contenido con opciones de personalización*

![Panel de Configuración](screenshots/settings.png)
*Panel completo de configuración y ajustes*

![Configuración de IA](screenshots/ai-settings.png)
*Configuración de proveedores de IA y ajustes de parámetros*

![Sistema de Programación](screenshots/scheduling.png)
*Configuración del programador de publicación automática*

---

## 📄 Licencia

Este proyecto está licenciado bajo la **Licencia Pública General GNU v2.0 o posterior**.

```
MaestrIA Post Generator
Copyright (C) 2024 WebDesignerK

Este programa es software libre; puedes redistribuirlo y/o modificarlo
bajo los términos de la Licencia Pública General GNU como se publica por
la Free Software Foundation; ya sea la versión 2 de la Licencia, o
(a tu opción) cualquier versión posterior.

Este programa se distribuye con la esperanza de que sea útil,
pero SIN NINGUNA GARANTÍA; sin siquiera la garantía implícita de
COMERCIABILIDAD o APTITUD PARA UN PROPÓSITO PARTICULAR. Ver la
Licencia Pública General GNU para más detalles.
```

[Ver licencia completa](LICENSE)

---

## 🔗 Enlaces y Recursos

- **🌐 Sitio Web Oficial**: [WebDesignerK.com](https://webdesignerk.com/)
- **📧 Soporte**: [Página de Soporte](https://webdesignerk.com/support)
- **📚 Documentación**: [Documentación Completa](https://webdesignerk.com/docs/miapg-post-generator)
- **🐛 Seguimiento de Problemas**: [GitHub Issues](https://github.com/konstantinwdk/miapg-post-generator/issues)
- **💬 Comunidad**: [Foro de Soporte WordPress.org](https://wordpress.org/support/plugin/miapg-post-generator/)
- **⭐ Calificar en WordPress.org**: [Directorio de Plugins](https://wordpress.org/plugins/miapg-post-generator/)

---

## 🏆 Créditos

### Autor(es)
- **konstantinWDK** - Desarrollador Principal
- **Equipo WebDesignerK** - Diseño y Pruebas

### Agradecimientos Especiales
- Comunidad WordPress por comentarios y sugerencias
- Equipos de OpenAI y DeepSeek por sus excelentes APIs de IA
- Todos los beta testers y contribuidores

### Recursos de Terceros
- Iconos por [Dashicons](https://developer.wordpress.org/resource/dashicons/)
- Badges por [Shields.io](https://shields.io/)

---

*Hecho con ❤️ por [WebDesignerK](https://webdesignerk.com/) - Empoderando desarrolladores WordPress en todo el mundo*

---

**⚡ Inicio Rápido**: Instalar → Configurar API → Generar Ideas → Crear Contenido → ¡Publicar! 🚀