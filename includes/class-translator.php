<?php
/**
 * Translator class for multi-language support
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

class Miapg_Translator {
    
    /**
     * Instance
     */
    private static $instance = null;
    
    /**
     * Current language
     */
    private $current_language = 'es';
    
    /**
     * Translations array
     */
    private $translations = array();
    
    /**
     * Get instance
     */
    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    /**
     * Constructor
     */
    private function __construct() {
        $this->current_language = Miapg_Settings::get_interface_language();
        $this->load_translations();
    }
    
    /**
     * Load translations
     */
    private function load_translations() {
        $this->translations = array(
            'es' => array(
                'General Settings' => 'Configuración General',
                'AI Settings' => 'Configuración de IA',
                'Content Settings' => 'Configuración de Contenido',
                'Scheduling' => 'Programación',
                'Post Ideas' => 'Ideas de Posts',
                'Create Post' => 'Crear Post',
                'Interface Language' => 'Idioma de Interfaz',
                'Select the language for the interface and content generation' => 'Selecciona el idioma para la interfaz y generación de contenido',
                'AI Provider' => 'Proveedor de IA',
                'Select the AI provider you want to use' => 'Selecciona el proveedor de IA que quieres usar',
                'OpenAI API Key' => 'Clave API de OpenAI',
                'Get your API key from' => 'Obtén tu clave API desde',
                'DeepSeek API Key' => 'Clave API de DeepSeek',
                'Default Category' => 'Categoría por Defecto',
                'Tags (comma separated)' => 'Etiquetas (separadas por comas)',
                'Post Status' => 'Estado del Post',
                'Publish' => 'Publicar',
                'Draft' => 'Borrador',
                'Scheduled' => 'Programado',
                'OpenAI Model' => 'Modelo OpenAI',
                'DeepSeek Model' => 'Modelo DeepSeek',
                'Temperature (0.0 - 2.0)' => 'Temperatura (0.0 - 2.0)',
                'Controls creativity. Higher values = more creativity' => 'Controla la creatividad. Valores más altos = más creatividad',
                'Max Tokens' => 'Máximo de Tokens',
                'Maximum number of tokens in response' => 'Número máximo de tokens en la respuesta',
                'Settings saved' => 'Configuración guardada',
                'Error generating ideas. Please check your API configuration.' => 'Error generando ideas. Por favor revisa tu configuración de API.',
                'Please enter a main topic.' => 'Por favor ingresa un tema principal.',
                'Error generating ideas from article. Please check your API configuration.' => 'Error generando ideas desde el artículo. Por favor revisa tu configuración de API.',
                'Please enter a reference article.' => 'Por favor ingresa un artículo de referencia.',
                'Post generated from idea. ' => 'Post generado desde idea. ',
                'Do you want to delete this idea since it has been used to generate the post?' => '¿Quieres eliminar esta idea ya que se ha usado para generar el post?',
                'Delete Used Idea' => 'Eliminar Idea Usada',
                'Idea deleted successfully.' => 'Idea eliminada exitosamente.',
                'Error deleting idea.' => 'Error eliminando idea.',
                // Content Settings
                'Writing Style' => 'Estilo de Escritura',
                'Informativo' => 'Informativo',
                'Persuasivo' => 'Persuasivo',
                'Narrativo' => 'Narrativo',
                'Tutorial' => 'Tutorial',
                'Target Audience' => 'Audiencia Objetivo',
                'General' => 'General',
                'Principiantes' => 'Principiantes',
                'Intermedios' => 'Intermedios',
                'Expertos' => 'Expertos',
                'Tone' => 'Tono',
                'Profesional' => 'Profesional',
                'Amigable' => 'Amigable',
                'Formal' => 'Formal',
                'Casual' => 'Casual',
                'Word Count' => 'Número de Palabras',
                'Number of words for generated posts' => 'Número de palabras para posts generados',
                'Include FAQ' => 'Incluir FAQ',
                'Include Lists' => 'Incluir Listas',
                'SEO Focus' => 'Enfoque SEO',
                'Low' => 'Bajo',
                'Medium' => 'Medio',
                'High' => 'Alto',
                'Custom Instructions' => 'Instrucciones Personalizadas',
                'Additional instructions for content generation' => 'Instrucciones adicionales para generación de contenido',
                'Yes' => 'Sí',
                'No' => 'No',
                // Ideas Tab
                'Generate New Ideas' => 'Generar Nuevas Ideas',
                'Generate Ideas from Topic' => 'Generar Ideas desde Tema',
                'Main Topic' => 'Tema Principal',
                'e.g. Digital Marketing, WordPress, SEO' => 'ej. Marketing Digital, WordPress, SEO',
                'Number of Ideas' => 'Número de Ideas',
                'Content Type' => 'Tipo de Contenido',
                'General post ideas' => 'Ideas de posts generales',
                'Step-by-step tutorials' => 'Tutoriales paso a paso',
                'Lists and compilations' => 'Listas y compilaciones',
                'Comparisons and reviews' => 'Comparaciones y reseñas',
                'News and updates' => 'Noticias y actualizaciones',
                'Generate and Save Ideas' => 'Generar y Guardar Ideas',
                'Generate Ideas from Article' => 'Generar Ideas desde Artículo',
                'Reference Article' => 'Artículo de Referencia',
                'Paste the article content here...' => 'Pega el contenido del artículo aquí...',
                'Generation Type' => 'Tipo de Generación',
                'Related and complementary topics' => 'Temas relacionados y complementarios',
                'Expanded and deeper concepts' => 'Conceptos expandidos y más profundos',
                'Alternative approaches and perspectives' => 'Enfoques alternativos y perspectivas',
                'Practical applications and use cases' => 'Aplicaciones prácticas y casos de uso',
                'Saved Ideas' => 'Ideas Guardadas',
                'Select bulk action' => 'Seleccionar acción masiva',
                'Bulk Actions' => 'Acciones Masivas',
                'Delete Selected' => 'Eliminar Seleccionadas',
                'Generate Posts' => 'Generar Posts',
                'Add Keyword' => 'Añadir Palabra Clave',
                'Apply' => 'Aplicar',
                'Select All' => 'Seleccionar Todo',
                'Idea Title' => 'Título de Idea',
                'Original Topic' => 'Tema Original',
                'Keyword' => 'Palabra Clave',
                'Generated Date' => 'Fecha de Generación',
                'Actions' => 'Acciones',
                'Not defined' => 'No definido',
                'Edit this idea' => 'Editar esta idea',
                'Edit' => 'Editar',
                'Generate a post from this idea' => 'Generar un post desde esta idea',
                'Generate Post' => 'Generar Post',
                'Delete this idea' => 'Eliminar esta idea',
                'Delete' => 'Eliminar',
                'No ideas found. Generate some ideas using the form above.' => 'No se encontraron ideas. Genera algunas ideas usando el formulario de arriba.',
                'View All Ideas' => 'Ver Todas las Ideas',
                // Create Tab
                'Create New Post' => 'Crear Nuevo Post',
                'Post Details' => 'Detalles del Post',
                'Topic or Idea' => 'Tema o Idea',
                'e.g. How to optimize WordPress for SEO' => 'ej. Cómo optimizar WordPress para SEO',
                'Main Keyword' => 'Palabra Clave Principal',
                'e.g. SEO optimization, WordPress' => 'ej. optimización SEO, WordPress',
                'Main keyword to focus the content on' => 'Palabra clave principal para enfocar el contenido',
                'Category' => 'Categoría',
                'Select Category' => 'Seleccionar Categoría',
                'Tags' => 'Etiquetas',
                'tag1, tag2, tag3' => 'etiqueta1, etiqueta2, etiqueta3',
                'Separate tags with commas' => 'Separar etiquetas con comas',
                'Schedule Date' => 'Fecha de Programación',
                'Leave empty to publish immediately' => 'Dejar vacío para publicar inmediatamente',
                'Content Based on Article' => 'Contenido Basado en Artículo',
                'Paste an article to base the content on (optional)' => 'Pega un artículo para basar el contenido (opcional)',
                'If provided, the post will be based on this article while being unique' => 'Si se proporciona, el post se basará en este artículo siendo único',
                'Create Post Now' => 'Crear Post Ahora'
            ),
            'en' => array(
                'General Settings' => 'General Settings',
                'AI Settings' => 'AI Settings',
                'Content Settings' => 'Content Settings',
                'Scheduling' => 'Scheduling',
                'Post Ideas' => 'Post Ideas',
                'Create Post' => 'Create Post',
                'Interface Language' => 'Interface Language',
                'Select the language for the interface and content generation' => 'Select the language for the interface and content generation',
                'AI Provider' => 'AI Provider',
                'Select the AI provider you want to use' => 'Select the AI provider you want to use',
                'OpenAI API Key' => 'OpenAI API Key',
                'Get your API key from' => 'Get your API key from',
                'DeepSeek API Key' => 'DeepSeek API Key',
                'Default Category' => 'Default Category',
                'Tags (comma separated)' => 'Tags (comma separated)',
                'Post Status' => 'Post Status',
                'Publish' => 'Publish',
                'Draft' => 'Draft',
                'Scheduled' => 'Scheduled',
                'OpenAI Model' => 'OpenAI Model',
                'DeepSeek Model' => 'DeepSeek Model',
                'Temperature (0.0 - 2.0)' => 'Temperature (0.0 - 2.0)',
                'Controls creativity. Higher values = more creativity' => 'Controls creativity. Higher values = more creativity',
                'Max Tokens' => 'Max Tokens',
                'Maximum number of tokens in response' => 'Maximum number of tokens in response',
                'Settings saved' => 'Settings saved',
                'Error generating ideas. Please check your API configuration.' => 'Error generating ideas. Please check your API configuration.',
                'Please enter a main topic.' => 'Please enter a main topic.',
                'Error generating ideas from article. Please check your API configuration.' => 'Error generating ideas from article. Please check your API configuration.',
                'Please enter a reference article.' => 'Please enter a reference article.',
                'Post generated from idea. ' => 'Post generated from idea. ',
                'Do you want to delete this idea since it has been used to generate the post?' => 'Do you want to delete this idea since it has been used to generate the post?',
                'Delete Used Idea' => 'Delete Used Idea',
                'Idea deleted successfully.' => 'Idea deleted successfully.',
                'Error deleting idea.' => 'Error deleting idea.'
            ),
            'ru' => array(
                'General Settings' => 'Основные настройки',
                'AI Settings' => 'Настройки ИИ',
                'Content Settings' => 'Настройки контента',
                'Scheduling' => 'Планирование',
                'Post Ideas' => 'Идеи постов',
                'Create Post' => 'Создать пост',
                'Interface Language' => 'Язык интерфейса',
                'Select the language for the interface and content generation' => 'Выберите язык для интерфейса и генерации контента',
                'AI Provider' => 'Провайдер ИИ',
                'Select the AI provider you want to use' => 'Выберите провайдера ИИ, который хотите использовать',
                'OpenAI API Key' => 'API ключ OpenAI',
                'Get your API key from' => 'Получите ваш API ключ из',
                'DeepSeek API Key' => 'API ключ DeepSeek',
                'Default Category' => 'Категория по умолчанию',
                'Tags (comma separated)' => 'Теги (через запятую)',
                'Post Status' => 'Статус поста',
                'Publish' => 'Опубликовать',
                'Draft' => 'Черновик',
                'Scheduled' => 'Запланированный',
                'OpenAI Model' => 'Модель OpenAI',
                'DeepSeek Model' => 'Модель DeepSeek',
                'Temperature (0.0 - 2.0)' => 'Температура (0.0 - 2.0)',
                'Controls creativity. Higher values = more creativity' => 'Контролирует креативность. Более высокие значения = больше креативности',
                'Max Tokens' => 'Макс. токенов',
                'Maximum number of tokens in response' => 'Максимальное количество токенов в ответе',
                'Settings saved' => 'Настройки сохранены',
                'Error generating ideas. Please check your API configuration.' => 'Ошибка генерации идей. Пожалуйста, проверьте конфигурацию API.',
                'Please enter a main topic.' => 'Пожалуйста, введите основную тему.',
                'Error generating ideas from article. Please check your API configuration.' => 'Ошибка генерации идей из статьи. Пожалуйста, проверьте конфигурацию API.',
                'Please enter a reference article.' => 'Пожалуйста, введите справочную статью.',
                'Post generated from idea. ' => 'Пост создан из идеи. ',
                'Do you want to delete this idea since it has been used to generate the post?' => 'Хотите удалить эту идею, поскольку она была использована для создания поста?',
                'Delete Used Idea' => 'Удалить использованную идею',
                'Idea deleted successfully.' => 'Идея успешно удалена.',
                'Error deleting idea.' => 'Ошибка удаления идеи.'
            )
        );
    }
    
    /**
     * Get translation
     */
    public function get_translation($key) {
        if (isset($this->translations[$this->current_language][$key])) {
            return $this->translations[$this->current_language][$key];
        }
        
        // Fallback to Spanish if translation not found
        if (isset($this->translations['es'][$key])) {
            return $this->translations['es'][$key];
        }
        
        // Return the key itself if no translation found
        return $key;
    }
    
    /**
     * Translate function (global helper)
     */
    public static function translate($key) {
        return self::get_instance()->get_translation($key);
    }
    
    /**
     * Set current language
     */
    public function set_language($language) {
        $this->current_language = $language;
    }
}

// Global translation function
function miapg_translate($key) {
    return Miapg_Translator::translate($key);
}

// Alias for shorter calls
function miapg_t($key) {
    return Miapg_Translator::translate($key);
}