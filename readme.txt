=== MaestrIA post generator ===
Contributors: konstantinWDK
Link: https://webdesignerk.com/
Tags: OpenAI, DeepSeek, GPT-4, auto post, SEO
Requires at least: 5.0
Tested up to: 6.8
Stable tag: 3.2.6
Requires PHP: 7.4
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Advanced AI-powered content generator with idea management system and optimized HTML output for WordPress.

== Descripción ==

MaestrIA post generator es el plugin más avanzado de WordPress para la creación automatizada de contenido. Soporta múltiples proveedores de IA (OpenAI y DeepSeek), ofrece amplias opciones de personalización, programación automática, gestión de ideas y generación de contenido basado en artículos de referencia.

🚀 **NUEVO en la Versión 3.2.6:**
- **CRÍTICO**: Corrección de vulnerabilidades de seguridad en bulk actions
- **Mejora de Seguridad**: Validaciones de nonce mejoradas en bulk_action_admin_notice()
- **Verificaciones de Permisos**: Controles de acceso reforzados para bulk actions
- **Validación de Datos**: Verificación de rangos numéricos razonables en notificaciones
- **Contexto de Seguridad**: Verificación de tipo de pantalla antes de mostrar mensajes
- **Listo para Directorio**: Cumplimiento total con estándares de seguridad WordPress

🔥 **Versión 3.2.4:**
- **Plugin Check Compliant**: Cumplimiento total con la herramienta oficial Plugin Check de WordPress
- **Optimización de Rendimiento**: Mejoras en consultas de base de datos y funciones de escape
- **Internacionalización Mejorada**: Comentarios de traductores completos para todos los placeholders
- **Seguridad Reforzada**: Escape de salida optimizado y validaciones de nonce mejoradas
- **Código Limpio**: Resolución de todas las advertencias de desarrollo y mejores prácticas

🔥 **Características de la Versión 3.1:**
- **Gestión Completa de Ideas**: Sistema de Custom Post Type para almacenar y gestionar ideas
- **Generación desde Artículos**: Crea contenido basado en artículos de referencia
- **Selección de Categorías**: Selecciona categorías específicas al crear posts
- **Salida HTML Optimizada**: Contenido en texto plano con etiquetas HTML básicas
- **Ideas desde Artículos**: Genera ideas basadas en artículos existentes
- **Palabras Clave Focalizadas**: Enfoque SEO centrado en palabras clave específicas

🔥 **Características Principales:**

**Sistema de Ideas Avanzado:**
- Almacenamiento de ideas como Custom Post Type
- Gestión individual de cada idea
- Eliminación selectiva de ideas
- Botón directo para generar posts desde ideas
- Campo de palabra clave para cada idea
- Filtros y búsqueda avanzada

**Generación de Contenido:**
- Generación desde temas libres
- Generación desde artículos de referencia
- Generación desde ideas almacenadas
- Enfoque en palabras clave específicas
- Selección de categorías personalizada

**Proveedores de IA Soportados:**
- OpenAI (GPT-4, GPT-4 Turbo, GPT-3.5 Turbo)
- DeepSeek (DeepSeek Chat, DeepSeek Coder)
- Cambio fácil entre proveedores

**Personalización de Contenido:**
- Estilos de escritura: Informativo, Conversacional, Técnico, Creativo, Académico
- Audiencia objetivo: General, Principiantes, Intermedio, Expertos, Profesionales
- Opciones de tono: Profesional, Amigable, Serio, Humorístico, Inspirador
- Recuento de palabras personalizable (100-3000 palabras)
- Instrucciones personalizadas para contenido a medida

**Salida HTML Optimizada:**
- Texto plano con etiquetas HTML básicas
- Etiquetas permitidas: h2, h3, strong, em, p, ul, ol, li, br
- Sin divs, spans, clases, IDs o HTML complejo
- Formato limpio y compatible con todos los temas

**Programación y Automatización:**
- Programación automática de posts (diaria, semanal, quincenal, mensual)
- Prioriza ideas almacenadas sobre temas manuales
- Horarios y días de publicación personalizados
- Eliminación automática de ideas usadas (opcional)
- Estadísticas de ideas en tiempo real

**Generación de Ideas:**
- Ideas desde temas específicos
- Ideas desde artículos de referencia
- Múltiples enfoques: relacionados, expandidos, alternativos, prácticos
- Almacenamiento automático en sistema de gestión
- Contador de ideas generadas

**Experiencia de Usuario:**
- Interfaz intuitiva con pestañas organizadas
- Estadísticas detalladas en tiempo real
- Filtros avanzados para gestión de ideas
- Configuración en tiempo real
- Manejo integral de errores

== Servicios Externos ==

Este plugin se conecta a servicios de inteligencia artificial externos para generar contenido automáticamente. Es fundamental que los usuarios comprendan qué datos se envían y bajo qué condiciones.

= OpenAI API =

Este plugin utiliza la API de OpenAI para generar contenido de blog, ideas de posts y títulos automáticamente mediante modelos de inteligencia artificial como GPT-4 y GPT-3.5.

**¿Qué datos se envían?**
- Prompts de texto (temas, palabras clave, instrucciones de contenido)
- Parámetros de configuración de IA (temperatura, tokens máximos, penalizaciones)
- Tu clave API personal de OpenAI

**¿Cuándo se envían?**
- Cada vez que generas un post automáticamente
- Cada vez que generas ideas de posts
- Cada vez que generas títulos para artículos
- Al validar tu clave API de OpenAI

**Servicio proporcionado por:** OpenAI  
**Términos de servicio:** https://openai.com/terms/  
**Política de privacidad:** https://openai.com/privacy/

= DeepSeek AI API =

Este plugin utiliza la API de DeepSeek AI como alternativa para generar contenido de blog, ideas de posts y títulos mediante sus modelos de inteligencia artificial.

**¿Qué datos se envían?**
- Prompts de texto (temas, palabras clave, instrucciones de contenido)
- Parámetros de configuración de IA (temperatura, tokens máximos, penalizaciones)
- Tu clave API personal de DeepSeek

**¿Cuándo se envían?**
- Cada vez que generas contenido con DeepSeek seleccionado como proveedor
- Cada vez que generas ideas usando DeepSeek
- Cada vez que generas títulos con DeepSeek
- Al validar tu clave API de DeepSeek

**Servicio proporcionado por:** DeepSeek AI  
**Términos de servicio:** https://www.deepseek.com/terms  
**Política de privacidad:** https://www.deepseek.com/privacy

**IMPORTANTE:** Este plugin requiere que proporciones tus propias claves API de estos servicios. Los datos se transmiten directamente desde tu sitio web a los proveedores de IA seleccionados. Es tu responsabilidad cumplir con los términos de servicio y políticas de privacidad de estos servicios externos. El plugin no almacena ni procesa estos datos; simplemente actúa como intermediario para las solicitudes de API.

== Instalación ==

1. Sube los archivos del plugin al directorio `/wp-content/plugins/auto-post-generator` o instálalo directamente desde la pantalla de plugins de WordPress.
2. Activa el plugin a través de la pantalla 'Plugins' en WordPress.
3. Ve a "Auto Post Generator Pro" en tu panel de WordPress.
4. Configura tu proveedor de IA y clave API en la pestaña "General".
5. Personaliza la configuración de contenido en la pestaña "Contenido".
6. Genera ideas en la pestaña "Ideas de Posts".
7. Configura la programación en la pestaña "Programación" (opcional).
8. ¡Comienza a generar posts desde ideas o manualmente!

== Preguntas Frecuentes ==

= ¿Cómo funciona el nuevo sistema de ideas? =
Las ideas se almacenan como un Custom Post Type, permitiendo gestión individual, edición, eliminación y generación directa de posts. Cada idea puede tener su propia palabra clave y ser categorizada.

= ¿Puedo generar ideas desde artículos existentes? =
Sí, puedes pegar cualquier artículo y el sistema generará ideas relacionadas, expandidas, alternativas o prácticas basadas en el contenido original.

= ¿Cómo selecciono categorías específicas? =
En la pestaña "Crear Post" puedes seleccionar una categoría específica o usar la configuración por defecto. La selección se aplica al post generado.

= ¿Qué etiquetas HTML se permiten en la salida? =
Solo etiquetas básicas: h2, h3, strong, em, p, ul, ol, li, br. No se incluyen divs, spans, clases, IDs o HTML complejo para mantener compatibilidad universal.

= ¿Cómo funciona la generación desde artículos de referencia? =
Pegas un artículo en el campo correspondiente y el sistema crea contenido original basado en las ideas principales, pero con enfoque único y fresco.

= ¿Las ideas se eliminan automáticamente al usarlas? =
Tienes la opción de eliminar automáticamente las ideas usadas en la programación automática, o mantenerlas para uso futuro.

= ¿Puedo editar las ideas después de generarlas? =
Sí, las ideas se almacenan en un Custom Post Type editable donde puedes añadir palabras clave, modificar el título o eliminar ideas innecesarias.

= ¿Cómo funciona el enfoque en palabras clave? =
Cada idea puede tener una palabra clave específica que se usará estratégicamente en el título y contenido del post generado para mejor SEO.

== Capturas de Pantalla ==

1. Panel principal con interfaz de pestañas mejorada
2. Gestión de Custom Post Type de ideas
3. Generador de ideas desde artículos
4. Configuración de categorías y palabras clave
5. Salida HTML optimizada
6. Estadísticas de ideas en tiempo real
7. Programación automática inteligente

== Registro de Cambios ==

= 3.2.6 =
* CRÍTICO: Implementación completa de verificación nonce para todas las llamadas $_POST, $_GET, $_REQUEST
* NUEVO: Funciones helper de seguridad para acceso seguro a superglobales (miapg_get_request_param)
* NUEVO: Función de verificación integral de seguridad (miapg_verify_request_security)
* NUEVO: Función especializada para bulk actions (miapg_verify_bulk_action_security)
* CORREGIDO: Lógica de seguridad no bypaseable en bulk_action_admin_notice()
* CORREGIDO: Verificación de permisos antes de procesamiento de datos en todas las funciones AJAX
* MEJORADO: Cumplimiento 100% con estándares de seguridad WordPress para prevenir CSRF
* MEJORADO: Arquitectura de seguridad robusta y no vulnerable a bypass

= 3.2.5 =
* CRÍTICO: Corrección de vulnerabilidades de seguridad en acceso directo a $_REQUEST en bulk actions
* CRÍTICO: Implementación de verificaciones de permisos reforzadas en bulk_action_admin_notice()
* NUEVO: Validación de rangos numéricos para prevenir valores maliciosos en notificaciones
* NUEVO: Verificación de contexto de pantalla antes de mostrar mensajes de bulk actions
* NUEVO: Validación regex para IDs seleccionados en bulk actions de keywords
* CORREGIDO: Acceso no autenticado a parámetros de bulk actions en URLs
* CORREGIDO: Falta de verificación de permisos edit_miapg_post_ideas en mensajes
* CORREGIDO: Posible manipulación de contadores de éxito/fallo en bulk actions
* MEJORADO: Cumplimiento total con estándares de seguridad de WordPress Directory
* MEJORADO: Robustez de seguridad para aprobación en directorio oficial

= 3.2.4 =
* CRÍTICO: Cumplimiento completo con Plugin Check - herramienta oficial de WordPress
* CRÍTICO: Corrección de todas las advertencias y errores detectados por Plugin Check
* NUEVO: Comentarios de traductores para todas las funciones de internacionalización con placeholders
* NUEVO: Escape de salida optimizado para todas las variables en admin/class-admin.php
* NUEVO: Uso de gmdate() en lugar de date() para evitar problemas de zona horaria
* CORREGIDO: Escape de $provider_status, $api_key_status, $params_valid y $capabilities_status
* CORREGIDO: Comentarios phpcs para error_log() condicionados por WP_DEBUG_LOG
* CORREGIDO: Verificaciones de nonce con comentarios phpcs para casos de solo lectura
* CORREGIDO: Función __() en includes/class-post-generator.php con comentario de traductor faltante
* MEJORADO: Consultas directas a BD con comentarios phpcs justificando su necesidad
* MEJORADO: Meta_query optimizada en scheduler con comentario explicativo
* MEJORADO: Cumplimiento con estándares de codificación de WordPress
* MEJORADO: Código preparado para aprobación en directorio oficial de WordPress

= 3.2.3 =
* CRÍTICO: Implementación completa de verificaciones nonce para prevenir ataques CSRF
* CRÍTICO: Validación exhaustiva de permisos de usuario en todas las operaciones sensibles
* NUEVO: Protección de URLs con idea_id mediante nonces de seguridad en create-tab.php
* NUEVO: Verificación de nonce en acceso a ideas para generación de posts
* NUEVO: Refuerzo de seguridad en sistema de eliminación de ideas
* CORREGIDO: Todas las URLs con parámetros ID ahora incluyen verificación wp_nonce_url()
* CORREGIDO: Separación de lógica de verificación de nonce de otras condiciones
* CORREGIDO: Validación de permisos específicos (edit_miapg_post_ideas, delete_miapg_post_ideas)
* MEJORADO: Seguridad en admin-pages.php con verificación dual de nonces
* MEJORADO: Protección en class-post-ideas-cpt.php para todas las acciones de ideas
* MEJORADO: Sistema de notificaciones con autenticación mejorada en class-admin.php
* MEJORADO: Cumplimiento total con estándares de seguridad del directorio WordPress

= 3.2.2 =
* CRÍTICO: Cumplimiento con estándares de WordPress para documentación de servicios externos
* NUEVO: Sección completa "Servicios Externos" en readme.txt
* NUEVO: Documentación detallada del uso de OpenAI API y transmisión de datos
* NUEVO: Documentación detallada del uso de DeepSeek AI API y transmisión de datos
* NUEVO: Enlaces oficiales a Términos de Servicio y Políticas de Privacidad
* NUEVO: Explicación clara de qué datos se envían, cuándo y bajo qué condiciones
* NUEVO: Disclaimer legal sobre responsabilidad del usuario por cumplimiento de APIs
* MEJORADO: Transparencia mejorada para cumplimiento del directorio de WordPress
* MEJORADO: Mayor conciencia del usuario sobre transmisión de datos a servicios externos
* MEJORADO: Documentación integral de protección legal

= 3.2.1 =
* CRÍTICO: Cumplimiento con estándares de WordPress para inclusión de JavaScript y CSS
* NUEVO: Implementación correcta de wp_enqueue_script() y wp_enqueue_style()
* NUEVO: Separación de código JavaScript inline en archivos dedicados
* NUEVO: Archivos JS creados: ideas-manager.js, url-cleaner.js, ideas-tab.js, page-redirecter.js
* NUEVO: Archivo CSS creado: post-ideas-cpt.css para estilos del Custom Post Type
* CORREGIDO: Eliminado todo el código JavaScript y CSS inline embebido en HTML
* CORREGIDO: Scripts ahora se cargan condicionalmente según la página administrativa
* MEJORADO: Sistema de dependencias y localización de scripts mejorado
* MEJORADO: Rendimiento optimizado con carga condicional de recursos
* MEJORADO: Cumplimiento con las mejores prácticas de WordPress para desarrollo de plugins

= 3.2 =
* CRÍTICO: Solucionados problemas críticos de guardado de configuraciones en todas las pestañas
* CRÍTICO: Corregidos nombres de campos inconsistentes que impedían el guardado de ajustes
* NUEVO: Traducciones completas para desplegables en pestaña Content Settings
* NUEVO: Soporte completo de traducciones para español, inglés y ruso en todas las opciones
* NUEVO: Traducciones automáticas para estilos de escritura (Informativo, Persuasivo, Narrativo, Tutorial)
* NUEVO: Traducciones automáticas para audiencia objetivo (General, Principiantes, Intermedios, Expertos)
* NUEVO: Traducciones automáticas para tonos (Profesional, Amigable, Formal, Casual)
* NUEVO: Traducciones automáticas para opciones de SEO (Bajo, Medio, Alto)
* NUEVO: Traducciones automáticas para opciones Sí/No en todos los idiomas
* CORREGIDO: Coherencia en nomenclatura de campos entre formularios y configuraciones
* CORREGIDO: Problemas de persistencia de datos en pestañas General, AI, Content y Scheduling
* MEJORADO: Sistema de traducciones expandido con nuevas cadenas de texto
* MEJORADO: Consistencia de la experiencia multiidioma en toda la interfaz
* MEJORADO: Validación y sanitización de campos de configuración

= 3.1 =
* NUEVO: Sistema completo de gestión de ideas con Custom Post Type
* NUEVO: Generación de posts basados en artículos de referencia
* NUEVO: Generación de ideas desde artículos existentes
* NUEVO: Selección de categorías específicas para posts
* NUEVO: Sistema de palabras clave focalizadas para SEO
* NUEVO: Salida HTML optimizada con etiquetas básicas únicamente
* NUEVO: Botones directos para generar posts desde ideas
* NUEVO: Eliminación individual de ideas con confirmación
* NUEVO: Estadísticas detalladas de ideas en tiempo real
* NUEVO: Filtros avanzados para gestión de ideas
* NUEVO: Programación automática que prioriza ideas almacenadas
* NUEVO: Opción para eliminar automáticamente ideas usadas
* MEJORADO: Interfaz de usuario más intuitiva y organizada
* MEJORADO: Flujo de trabajo optimizado para gestión de contenido
* MEJORADO: Compatibilidad mejorada con todos los temas
* MEJORADO: Rendimiento optimizado para grandes volúmenes de ideas

= 3.0 =
* NUEVO: Integración DeepSeek AI con soporte completo de API
* NUEVO: Personalización avanzada de contenido (estilo de escritura, tono, audiencia)
* NUEVO: Sistema de programación automática con múltiples opciones de frecuencia
* NUEVO: Generador de ideas para posts para lluvia de ideas creativa
* NUEVO: Interfaz de usuario con pestañas para mejor organización
* NUEVO: Control avanzado de parámetros de IA (temperatura, tokens, penalizaciones)
* NUEVO: Instrucciones personalizadas para contenido personalizado
* NUEVO: Límites de longitud de título configurables
* NUEVO: Opciones mejoradas de enfoque SEO
* MEJORADO: Mejor manejo de errores y retroalimentación del usuario
* MEJORADO: Mejoras de seguridad y optimización de código
* MEJORADO: Interfaz de administración responsive para móviles

= 2.1 =
* Manejo mejorado de solicitudes API y mensajes de error
* Seguridad mejorada con verificaciones nonce para formularios
* Configuraciones agregadas para controlar el recuento de palabras y programación
* Generación de Schema FAQ agregada para SEO

= 2.0 =
* Lanzamiento inicial

== Aviso de Actualización ==

= 3.2.6 =
**ACTUALIZACIÓN DE SEGURIDAD CRÍTICA**: Esta versión implementa verificación nonce completa para TODAS las llamadas de entrada ($_POST, $_GET, $_REQUEST) para prevenir ataques CSRF. Se han agregado funciones helper de seguridad, verificación integral de permisos y lógica de seguridad no bypaseable. Cumplimiento 100% con estándares de seguridad WordPress. Actualizar inmediatamente para máxima protección.

= 3.2.5 =
**ACTUALIZACIÓN DE SEGURIDAD CRÍTICA**: Esta versión corrige vulnerabilidades de seguridad importantes en el sistema de bulk actions que permitían acceso no autorizado a parámetros de $_REQUEST. Se han implementado verificaciones de permisos reforzadas, validaciones de contexto de pantalla y controles de rangos numéricos. Esencial para cumplimiento con estándares de seguridad del directorio WordPress. Actualizar inmediatamente para proteger tu sitio.

= 3.2.4 =
**ACTUALIZACIÓN CRÍTICA PARA DIRECTORIO WORDPRESS**: Esta versión implementa el cumplimiento completo con Plugin Check, la herramienta oficial de validación de WordPress. Incluye correcciones críticas de escape de salida, optimizaciones de rendimiento, comentarios de traductores completos y mejoras en las mejores prácticas de código. Esencial para aprobación en el directorio oficial. Se recomienda actualizar inmediatamente para asegurar compatibilidad total con estándares WordPress 2025.

= 3.2.3 =
**ACTUALIZACIÓN DE SEGURIDAD CRÍTICA**: Esta versión corrige importantes vulnerabilidades de seguridad relacionadas con verificaciones nonce y permisos de usuario. Se recomienda encarecidamente actualizar inmediatamente para proteger tu sitio contra posibles ataques CSRF y accesos no autorizados. Todas las URLs con parámetros ID han sido aseguradas con verificaciones de seguridad apropiadas.

= 3.2 =
**ACTUALIZACIÓN CRÍTICA**: Esta versión soluciona problemas importantes de guardado de configuraciones que afectaban el funcionamiento del plugin. Se recomienda encarecidamente actualizar. Después de actualizar, verifica que todas tus configuraciones se guarden correctamente y disfruta de la interfaz completamente traducida en tu idioma preferido.

= 3.1 =
Actualización mayor con sistema completo de gestión de ideas, generación desde artículos, selección de categorías y salida HTML optimizada. Después de actualizar, explora la nueva pestaña "Ideas de Posts" para gestionar tu banco de contenido y aprovecha las nuevas capacidades de generación desde artículos de referencia.

== Flujo de Trabajo Recomendado ==

1. **Generar Ideas**: Usa la pestaña "Ideas de Posts" para generar ideas desde temas o artículos
2. **Organizar Ideas**: Asigna palabras clave y organiza tus ideas en el gestor
3. **Crear Contenido**: Usa los botones directos para generar posts desde ideas específicas
4. **Automatizar**: Configura la programación para usar automáticamente tus ideas almacenadas
5. **Gestionar**: Elimina o edita ideas según tus necesidades

== Licencia ==
Este plugin está licenciado bajo GPLv2 o posterior.

== Soporte ==
Para soporte, solicitudes de características o reportes de errores, por favor visita https://webdesignerk.com/support

== Política de Privacidad ==
Este plugin envía solicitudes de generación de contenido a tu proveedor de IA elegido (OpenAI o DeepSeek). Por favor revisa sus respectivas políticas de privacidad para información sobre el manejo de datos. Las ideas y contenido generado se almacenan localmente en tu base de datos de WordPress.