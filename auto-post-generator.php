<?php
/*
Plugin Name: Auto Post Generator Pro
Plugin URI: https://webdesignerk.com
Description: Advanced AI-powered content generator with support for OpenAI and DeepSeek APIs, automatic scheduling, detailed customization, and post ideas generation.
Version: 3.0
Author: konstantinWDK
Author URI: https://webdesignerk.com
*/

if (!defined('ABSPATH')) {
    exit; // Salir si se accede directamente
}

// Función para generar y publicar el post
function generate_and_publish_post($prompt, $category_id, $tags, $post_status, $post_date, $word_count, $ai_provider = 'openai', $custom_settings = []) {
    // Validar y sanitizar los parámetros de entrada
    $prompt = sanitize_text_field($prompt);
    $category_id = absint($category_id);
    $tags = array_map('sanitize_text_field', $tags);
    $post_status = sanitize_key($post_status);
    $post_date = sanitize_text_field($post_date);
    $word_count = absint($word_count);

    // Validar el estado del post
    $allowed_post_statuses = ['publish', 'draft', 'future'];
    if (!in_array($post_status, $allowed_post_statuses)) {
        $post_status = 'draft';
    }

    // Validar fecha de publicación
    if (!$post_date || strtotime($post_date) === false) {
        $post_date = current_time('mysql');
    } else {
        $post_date = date('Y-m-d H:i:s', strtotime($post_date));
    }

    // Configurar proveedor de IA
    $ai_provider = $ai_provider ?: get_option('ai_provider', 'openai');
    
    if ($ai_provider === 'deepseek') {
        $api_key = get_option('deepseek_api_key');
        $endpoint = 'https://api.deepseek.com/v1/chat/completions';
        $model = get_option('deepseek_model', 'deepseek-chat');
    } else {
        $api_key = get_option('openai_api_key');
        $endpoint = 'https://api.openai.com/v1/chat/completions';
        $model = get_option('openai_model', 'gpt-4');
    }
    
    if (!$api_key) {
        return 'No se ha proporcionado la clave API para ' . $ai_provider;
    }
    // Configuraciones avanzadas personalizables
    $writing_style = get_option('writing_style', 'informativo');
    $target_audience = get_option('target_audience', 'general');
    $tone = get_option('tone', 'profesional');
    $include_faq = get_option('include_faq', 'yes');
    $include_lists = get_option('include_lists', 'yes');
    $seo_focus = get_option('seo_focus', 'medium');
    $custom_instructions = get_option('custom_instructions', '');
    
    // Construir prompt personalizado
    $seo_prompt = "Actúa como un experto en SEO y redacción de contenido con estilo {$writing_style} y tono {$tone}. ";
    $seo_prompt .= "Audiencia objetivo: {$target_audience}. ";
    $seo_prompt .= "Crea un artículo de blog de aproximadamente {$word_count} palabras sobre el siguiente tema: {$prompt}. ";
    
    $seo_prompt .= "Estructura el contenido de la siguiente manera:\n";
    $seo_prompt .= "1. Introduce el tema con un párrafo atractivo.\n";
    $seo_prompt .= "2. Utiliza encabezados <h2> para las secciones principales.\n";
    $seo_prompt .= "3. Utiliza encabezados <h3> para subsecciones cuando sea necesario.\n";
    
    if ($include_lists === 'yes') {
        $seo_prompt .= "4. Incluye al menos una lista con viñetas (<ul><li>) o numerada (<ol><li>).\n";
    }
    
    $seo_prompt .= "5. Utiliza <strong> para negritas y <em> para cursivas cuando sea apropiado.\n";
    $seo_prompt .= "6. Concluye con un párrafo de resumen.\n";
    
    if ($include_faq === 'yes') {
        $seo_prompt .= "7. Añade un schema FAQ de 3 preguntas y respuestas relacionadas con el tema al final del artículo, utilizando el formato de schema.org.\n";
    }
    
    if ($custom_instructions) {
        $seo_prompt .= "Instrucciones adicionales: {$custom_instructions}\n";
    }
    
    $seo_prompt .= "Asegúrate de que el contenido sea informativo, atractivo y optimizado para SEO. Utiliza las etiquetas HTML apropiadas para dar formato al contenido. NO incluyas un título para el artículo.";

    // Configurar parámetros de IA personalizables
    $temperature = get_option('ai_temperature', 0.7);
    $max_tokens = get_option('ai_max_tokens', 2000);
    $top_p = get_option('ai_top_p', 1.0);
    $frequency_penalty = get_option('ai_frequency_penalty', 0.0);
    $presence_penalty = get_option('ai_presence_penalty', 0.0);
    
    $data = [
        'model' => $model,
        'messages' => [
            ['role' => 'system', 'content' => 'Eres un asistente experto en SEO que genera contenido para blogs con formato HTML.'],
            ['role' => 'user', 'content' => $seo_prompt]
        ],
        'max_tokens' => intval($max_tokens),
        'temperature' => floatval($temperature),
        'top_p' => floatval($top_p),
        'frequency_penalty' => floatval($frequency_penalty),
        'presence_penalty' => floatval($presence_penalty),
    ];

    $response = wp_remote_post($endpoint, [
        'headers' => [
            'Authorization' => 'Bearer ' . $api_key,
            'Content-Type' => 'application/json',
        ],
        'body' => wp_json_encode($data),
        'timeout' => 120, // Aumentar el tiempo de espera a 120 segundos
    ]);

    if (is_wp_error($response)) {
        return 'Error en la solicitud a OpenAI: ' . esc_html($response->get_error_message());
    }

    $body = wp_remote_retrieve_body($response);
    $result = json_decode($body, true);

    if (!isset($result['choices'][0]['message']['content'])) {
        return 'No se generó contenido. Respuesta de la API: ' . esc_html(print_r($result, true));
    }

    $post_content = $result['choices'][0]['message']['content'];

    // Generar título del post
    $title_length = get_option('title_max_length', 60);
    $title_prompt = "Genera un título SEO atractivo y conciso (máximo {$title_length} caracteres) para un artículo sobre: {$prompt}. No uses comillas en el título.";
    $title_data = [
        'model' => $model,
        'messages' => [
            ['role' => 'system', 'content' => 'Eres un asistente experto en SEO que genera títulos atractivos sin comillas.'],
            ['role' => 'user', 'content' => $title_prompt]
        ],
        'max_tokens' => 60,
        'temperature' => floatval($temperature),
    ];

    $title_response = wp_remote_post($endpoint, [
        'headers' => [
            'Authorization' => 'Bearer ' . $api_key,
            'Content-Type' => 'application/json',
        ],
        'body' => wp_json_encode($title_data),
        'timeout' => 30,
    ]);

    if (is_wp_error($title_response)) {
        $post_title = 'Título no generado';
    } else {
        $title_body = wp_remote_retrieve_body($title_response);
        $title_result = json_decode($title_body, true);
        $post_title = isset($title_result['choices'][0]['message']['content']) ? trim($title_result['choices'][0]['message']['content']) : 'Título no generado';
        // Eliminar cualquier comilla restante del título
        $post_title = str_replace('"', '', $post_title);
        $post_title = str_replace("'", '', $post_title);
    }

    // Crear post en WordPress
    $post_data = [
        'post_title'   => $post_title,
        'post_content' => $post_content,
        'post_status'  => $post_status,
        'post_author'  => get_current_user_id(),
        'post_category'=> [$category_id],
        'tags_input'   => $tags,
        'post_date'    => $post_date,
    ];

    // Desactivar temporalmente los filtros de contenido
    remove_filter('content_save_pre', 'wp_filter_post_kses');
    remove_filter('content_filtered_save_pre', 'wp_filter_post_kses');

    $post_id = wp_insert_post($post_data);

    // Reactivar los filtros de contenido
    add_filter('content_save_pre', 'wp_filter_post_kses');
    add_filter('content_filtered_save_pre', 'wp_filter_post_kses');

    if (is_wp_error($post_id)) {
        return "Error al crear el post: " . esc_html($post_id->get_error_message());
    }

    return "Post creado con ID: " . esc_html($post_id) . ", programado para: " . esc_html($post_date);
}

// Crear página de configuración en el backoffice
function my_plugin_settings_page() {
    add_options_page('Auto Post Generator Pro', 'Auto Post Generator Pro', 'manage_options', 'auto-post-generator-pro', 'my_plugin_settings_page_content');
}
add_action('admin_menu', 'my_plugin_settings_page');

// Contenido de la página de configuración
function my_plugin_settings_page_content() {
    if (!current_user_can('manage_options')) {
        wp_die(__('No tienes suficientes permisos para acceder a esta página.'));
    }

    // Comprobar si la configuración se actualizó
    if (isset($_GET['settings-updated'])) {
        add_settings_error('my_plugin_messages', 'my_plugin_message', __('Configuración guardada'), 'updated');
    }

    // Mostrar mensajes de error/actualización
    settings_errors('my_plugin_messages');
    
    // Obtener pestaña activa
    $active_tab = isset($_GET['tab']) ? $_GET['tab'] : 'general';
    ?>
    <div class="wrap">
        <h1><?php echo esc_html(get_admin_page_title()); ?></h1>
        
        <!-- Pestañas de navegación -->
        <nav class="nav-tab-wrapper">
            <a href="?page=auto-post-generator-pro&tab=general" class="nav-tab <?php echo $active_tab == 'general' ? 'nav-tab-active' : ''; ?>">Configuración General</a>
            <a href="?page=auto-post-generator-pro&tab=ai" class="nav-tab <?php echo $active_tab == 'ai' ? 'nav-tab-active' : ''; ?>">Configuración IA</a>
            <a href="?page=auto-post-generator-pro&tab=content" class="nav-tab <?php echo $active_tab == 'content' ? 'nav-tab-active' : ''; ?>">Configuración de Contenido</a>
            <a href="?page=auto-post-generator-pro&tab=scheduling" class="nav-tab <?php echo $active_tab == 'scheduling' ? 'nav-tab-active' : ''; ?>">Programación</a>
            <a href="?page=auto-post-generator-pro&tab=ideas" class="nav-tab <?php echo $active_tab == 'ideas' ? 'nav-tab-active' : ''; ?>">Ideas de Posts</a>
            <a href="?page=auto-post-generator-pro&tab=create" class="nav-tab <?php echo $active_tab == 'create' ? 'nav-tab-active' : ''; ?>">Crear Post</a>
        </nav>
        
        <?php if ($active_tab == 'general') { ?>
        <form method="post" action="options.php">
            <?php
            settings_fields('my_plugin_settings_group');
            do_settings_sections('my_plugin_settings_group');
            ?>
            <h2>Configuración General</h2>
            <table class="form-table">
                <tr valign="top">
                    <th scope="row">Proveedor de IA</th>
                    <td>
                        <select name="ai_provider">
                            <option value="openai" <?php selected(get_option('ai_provider'), 'openai'); ?>>OpenAI</option>
                            <option value="deepseek" <?php selected(get_option('ai_provider'), 'deepseek'); ?>>DeepSeek</option>
                        </select>
                        <p class="description">Selecciona el proveedor de IA que deseas usar</p>
                    </td>
                </tr>
                <tr valign="top">
                    <th scope="row">Clave API de OpenAI</th>
                    <td>
                        <input type="password" name="openai_api_key" value="<?php echo esc_attr(get_option('openai_api_key')); ?>" style="width: 400px;" />
                        <p class="description">Obtén tu clave API desde <a href="https://platform.openai.com/api-keys" target="_blank">OpenAI</a></p>
                    </td>
                </tr>
                <tr valign="top">
                    <th scope="row">Clave API de DeepSeek</th>
                    <td>
                        <input type="password" name="deepseek_api_key" value="<?php echo esc_attr(get_option('deepseek_api_key')); ?>" style="width: 400px;" />
                        <p class="description">Obtén tu clave API desde <a href="https://platform.deepseek.com/api_keys" target="_blank">DeepSeek</a></p>
                    </td>
                </tr>
                <tr valign="top">
                    <th scope="row">Categoría por defecto</th>
                    <td>
                        <?php
                        $categories = get_categories(['hide_empty' => false]);
                        $selected_category = get_option('auto_post_category', 1);
                        ?>
                        <select name="auto_post_category">
                            <?php foreach ($categories as $category) { ?>
                                <option value="<?php echo esc_attr($category->term_id); ?>" <?php selected($selected_category, $category->term_id); ?>>
                                    <?php echo esc_html($category->name); ?>
                                </option>
                            <?php } ?>
                        </select>
                    </td>
                </tr>
                <tr valign="top">
                    <th scope="row">Etiquetas (separadas por comas)</th>
                    <td><input type="text" name="auto_post_tags" value="<?php echo esc_attr(get_option('auto_post_tags', '')); ?>" style="width: 400px;" /></td>
                </tr>
                <tr valign="top">
                    <th scope="row">Estado del post</th>
                    <td>
                        <select name="auto_post_status">
                            <option value="publish" <?php selected(get_option('auto_post_status'), 'publish'); ?>>Publicar</option>
                            <option value="draft" <?php selected(get_option('auto_post_status'), 'draft'); ?>>Borrador</option>
                            <option value="future" <?php selected(get_option('auto_post_status'), 'future'); ?>>Programado</option>
                        </select>
                    </td>
                </tr>
            </table>
            <?php submit_button(); ?>
        </form>
        
        <?php } elseif ($active_tab == 'ai') { ?>
        <form method="post" action="options.php">
            <?php
            settings_fields('my_plugin_ai_settings_group');
            do_settings_sections('my_plugin_ai_settings_group');
            ?>
            <h2>Configuración de IA</h2>
            <table class="form-table">
                <tr valign="top">
                    <th scope="row">Modelo OpenAI</th>
                    <td>
                        <select name="openai_model">
                            <option value="gpt-4" <?php selected(get_option('openai_model'), 'gpt-4'); ?>>GPT-4</option>
                            <option value="gpt-4-turbo" <?php selected(get_option('openai_model'), 'gpt-4-turbo'); ?>>GPT-4 Turbo</option>
                            <option value="gpt-3.5-turbo" <?php selected(get_option('openai_model'), 'gpt-3.5-turbo'); ?>>GPT-3.5 Turbo</option>
                        </select>
                    </td>
                </tr>
                <tr valign="top">
                    <th scope="row">Modelo DeepSeek</th>
                    <td>
                        <select name="deepseek_model">
                            <option value="deepseek-chat" <?php selected(get_option('deepseek_model'), 'deepseek-chat'); ?>>DeepSeek Chat</option>
                            <option value="deepseek-coder" <?php selected(get_option('deepseek_model'), 'deepseek-coder'); ?>>DeepSeek Coder</option>
                        </select>
                    </td>
                </tr>
                <tr valign="top">
                    <th scope="row">Temperatura (0.0 - 2.0)</th>
                    <td>
                        <input type="number" name="ai_temperature" value="<?php echo esc_attr(get_option('ai_temperature', '0.7')); ?>" min="0" max="2" step="0.1" />
                        <p class="description">Controla la creatividad. Valores más altos = más creatividad</p>
                    </td>
                </tr>
                <tr valign="top">
                    <th scope="row">Máximo de tokens</th>
                    <td>
                        <input type="number" name="ai_max_tokens" value="<?php echo esc_attr(get_option('ai_max_tokens', '2000')); ?>" min="100" max="4000" />
                        <p class="description">Máximo número de tokens en la respuesta</p>
                    </td>
                </tr>
                <tr valign="top">
                    <th scope="row">Top P (0.0 - 1.0)</th>
                    <td>
                        <input type="number" name="ai_top_p" value="<?php echo esc_attr(get_option('ai_top_p', '1.0')); ?>" min="0" max="1" step="0.1" />
                        <p class="description">Controla la diversidad de respuestas</p>
                    </td>
                </tr>
                <tr valign="top">
                    <th scope="row">Penalización por frecuencia</th>
                    <td>
                        <input type="number" name="ai_frequency_penalty" value="<?php echo esc_attr(get_option('ai_frequency_penalty', '0.0')); ?>" min="-2" max="2" step="0.1" />
                        <p class="description">Reduce la repetición de palabras</p>
                    </td>
                </tr>
                <tr valign="top">
                    <th scope="row">Penalización por presencia</th>
                    <td>
                        <input type="number" name="ai_presence_penalty" value="<?php echo esc_attr(get_option('ai_presence_penalty', '0.0')); ?>" min="-2" max="2" step="0.1" />
                        <p class="description">Fomenta hablar sobre nuevos temas</p>
                    </td>
                </tr>
            </table>
            <?php submit_button(); ?>
        </form>
        
        <?php } elseif ($active_tab == 'content') { ?>
        <form method="post" action="options.php">
            <?php
            settings_fields('my_plugin_content_settings_group');
            do_settings_sections('my_plugin_content_settings_group');
            ?>
            <h2>Configuración de Contenido</h2>
            <table class="form-table">
                <tr valign="top">
                    <th scope="row">Prompt base</th>
                    <td>
                        <textarea name="auto_post_prompt" rows="4" cols="80"><?php echo esc_textarea(get_option('auto_post_prompt', 'Escribe un post sobre un tema relevante.')); ?></textarea>
                        <p class="description">Instrucciones base para generar el contenido</p>
                    </td>
                </tr>
                <tr valign="top">
                    <th scope="row">Número de palabras</th>
                    <td>
                        <input type="number" name="auto_post_word_count" value="<?php echo esc_attr(get_option('auto_post_word_count', '500')); ?>" min="100" max="3000" />
                        <p class="description">Número aproximado de palabras del artículo</p>
                    </td>
                </tr>
                <tr valign="top">
                    <th scope="row">Estilo de escritura</th>
                    <td>
                        <select name="writing_style">
                            <option value="informativo" <?php selected(get_option('writing_style'), 'informativo'); ?>>Informativo</option>
                            <option value="conversacional" <?php selected(get_option('writing_style'), 'conversacional'); ?>>Conversacional</option>
                            <option value="técnico" <?php selected(get_option('writing_style'), 'técnico'); ?>>Técnico</option>
                            <option value="creativo" <?php selected(get_option('writing_style'), 'creativo'); ?>>Creativo</option>
                            <option value="académico" <?php selected(get_option('writing_style'), 'académico'); ?>>Académico</option>
                        </select>
                    </td>
                </tr>
                <tr valign="top">
                    <th scope="row">Audiencia objetivo</th>
                    <td>
                        <select name="target_audience">
                            <option value="general" <?php selected(get_option('target_audience'), 'general'); ?>>General</option>
                            <option value="principiantes" <?php selected(get_option('target_audience'), 'principiantes'); ?>>Principiantes</option>
                            <option value="intermedio" <?php selected(get_option('target_audience'), 'intermedio'); ?>>Intermedio</option>
                            <option value="expertos" <?php selected(get_option('target_audience'), 'expertos'); ?>>Expertos</option>
                            <option value="profesionales" <?php selected(get_option('target_audience'), 'profesionales'); ?>>Profesionales</option>
                        </select>
                    </td>
                </tr>
                <tr valign="top">
                    <th scope="row">Tono</th>
                    <td>
                        <select name="tone">
                            <option value="profesional" <?php selected(get_option('tone'), 'profesional'); ?>>Profesional</option>
                            <option value="amigable" <?php selected(get_option('tone'), 'amigable'); ?>>Amigable</option>
                            <option value="serio" <?php selected(get_option('tone'), 'serio'); ?>>Serio</option>
                            <option value="humorístico" <?php selected(get_option('tone'), 'humorístico'); ?>>Humorístico</option>
                            <option value="inspirador" <?php selected(get_option('tone'), 'inspirador'); ?>>Inspirador</option>
                        </select>
                    </td>
                </tr>
                <tr valign="top">
                    <th scope="row">Incluir FAQ</th>
                    <td>
                        <select name="include_faq">
                            <option value="yes" <?php selected(get_option('include_faq'), 'yes'); ?>>Sí</option>
                            <option value="no" <?php selected(get_option('include_faq'), 'no'); ?>>No</option>
                        </select>
                    </td>
                </tr>
                <tr valign="top">
                    <th scope="row">Incluir listas</th>
                    <td>
                        <select name="include_lists">
                            <option value="yes" <?php selected(get_option('include_lists'), 'yes'); ?>>Sí</option>
                            <option value="no" <?php selected(get_option('include_lists'), 'no'); ?>>No</option>
                        </select>
                    </td>
                </tr>
                <tr valign="top">
                    <th scope="row">Enfoque SEO</th>
                    <td>
                        <select name="seo_focus">
                            <option value="low" <?php selected(get_option('seo_focus'), 'low'); ?>>Bajo</option>
                            <option value="medium" <?php selected(get_option('seo_focus'), 'medium'); ?>>Medio</option>
                            <option value="high" <?php selected(get_option('seo_focus'), 'high'); ?>>Alto</option>
                        </select>
                    </td>
                </tr>
                <tr valign="top">
                    <th scope="row">Longitud máxima del título</th>
                    <td>
                        <input type="number" name="title_max_length" value="<?php echo esc_attr(get_option('title_max_length', '60')); ?>" min="30" max="100" />
                        <p class="description">Caracteres máximos para el título SEO</p>
                    </td>
                </tr>
                <tr valign="top">
                    <th scope="row">Instrucciones personalizadas</th>
                    <td>
                        <textarea name="custom_instructions" rows="3" cols="80"><?php echo esc_textarea(get_option('custom_instructions', '')); ?></textarea>
                        <p class="description">Instrucciones adicionales para personalizar el contenido</p>
                    </td>
                </tr>
            </table>
            <?php submit_button(); ?>
        </form>

        <?php } elseif ($active_tab == 'scheduling') { ?>
        <form method="post" action="options.php">
            <?php
            settings_fields('my_plugin_scheduling_settings_group');
            do_settings_sections('my_plugin_scheduling_settings_group');
            ?>
            <h2>Configuración de Programación</h2>
            <table class="form-table">
                <tr valign="top">
                    <th scope="row">Activar programación automática</th>
                    <td>
                        <select name="auto_scheduling_enabled">
                            <option value="no" <?php selected(get_option('auto_scheduling_enabled'), 'no'); ?>>No</option>
                            <option value="yes" <?php selected(get_option('auto_scheduling_enabled'), 'yes'); ?>>Sí</option>
                        </select>
                    </td>
                </tr>
                <tr valign="top">
                    <th scope="row">Frecuencia de publicación</th>
                    <td>
                        <select name="posting_frequency">
                            <option value="daily" <?php selected(get_option('posting_frequency'), 'daily'); ?>>Diario</option>
                            <option value="weekly" <?php selected(get_option('posting_frequency'), 'weekly'); ?>>Semanal</option>
                            <option value="biweekly" <?php selected(get_option('posting_frequency'), 'biweekly'); ?>>Quincenal</option>
                            <option value="monthly" <?php selected(get_option('posting_frequency'), 'monthly'); ?>>Mensual</option>
                        </select>
                    </td>
                </tr>
                <tr valign="top">
                    <th scope="row">Hora de publicación</th>
                    <td>
                        <input type="time" name="posting_time" value="<?php echo esc_attr(get_option('posting_time', '09:00')); ?>" />
                    </td>
                </tr>
                <tr valign="top">
                    <th scope="row">Día de la semana (para semanal)</th>
                    <td>
                        <select name="posting_day">
                            <option value="monday" <?php selected(get_option('posting_day'), 'monday'); ?>>Lunes</option>
                            <option value="tuesday" <?php selected(get_option('posting_day'), 'tuesday'); ?>>Martes</option>
                            <option value="wednesday" <?php selected(get_option('posting_day'), 'wednesday'); ?>>Miércoles</option>
                            <option value="thursday" <?php selected(get_option('posting_day'), 'thursday'); ?>>Jueves</option>
                            <option value="friday" <?php selected(get_option('posting_day'), 'friday'); ?>>Viernes</option>
                            <option value="saturday" <?php selected(get_option('posting_day'), 'saturday'); ?>>Sábado</option>
                            <option value="sunday" <?php selected(get_option('posting_day'), 'sunday'); ?>>Domingo</option>
                        </select>
                    </td>
                </tr>
                <tr valign="top">
                    <th scope="row">Lista de temas para generación automática</th>
                    <td>
                        <textarea name="auto_topics_list" rows="5" cols="80" placeholder="Introduce temas separados por líneas"><?php echo esc_textarea(get_option('auto_topics_list', '')); ?></textarea>
                        <p class="description">Lista de temas que se usarán de forma rotatoria para la generación automática</p>
                    </td>
                </tr>
            </table>
            <?php submit_button(); ?>
        </form>
        
        <?php } elseif ($active_tab == 'ideas') { ?>
        <h2>Generador de Ideas para Posts</h2>
        <form method="post">
            <?php wp_nonce_field('generate_ideas', 'generate_ideas_nonce'); ?>
            <table class="form-table">
                <tr valign="top">
                    <th scope="row">Tema principal</th>
                    <td>
                        <input type="text" name="main_topic" value="<?php echo isset($_POST['main_topic']) ? esc_attr($_POST['main_topic']) : ''; ?>" style="width: 400px;" placeholder="Ej: Marketing Digital, Cocina Saludable, Tecnología" />
                    </td>
                </tr>
                <tr valign="top">
                    <th scope="row">Número de ideas</th>
                    <td>
                        <select name="ideas_count">
                            <option value="5" <?php selected(isset($_POST['ideas_count']) ? $_POST['ideas_count'] : '5', '5'); ?>>5 ideas</option>
                            <option value="10" <?php selected(isset($_POST['ideas_count']) ? $_POST['ideas_count'] : '10', '10'); ?>>10 ideas</option>
                            <option value="15" <?php selected(isset($_POST['ideas_count']) ? $_POST['ideas_count'] : '15', '15'); ?>>15 ideas</option>
                            <option value="20" <?php selected(isset($_POST['ideas_count']) ? $_POST['ideas_count'] : '20', '20'); ?>>20 ideas</option>
                        </select>
                    </td>
                </tr>
                <tr valign="top">
                    <th scope="row">Tipo de contenido</th>
                    <td>
                        <select name="content_type">
                            <option value="general" <?php selected(isset($_POST['content_type']) ? $_POST['content_type'] : 'general', 'general'); ?>>General</option>
                            <option value="tutorial" <?php selected(isset($_POST['content_type']) ? $_POST['content_type'] : 'tutorial', 'tutorial'); ?>>Tutorial</option>
                            <option value="lista" <?php selected(isset($_POST['content_type']) ? $_POST['content_type'] : 'lista', 'lista'); ?>>Lista</option>
                            <option value="comparacion" <?php selected(isset($_POST['content_type']) ? $_POST['content_type'] : 'comparacion', 'comparacion'); ?>>Comparación</option>
                            <option value="noticias" <?php selected(isset($_POST['content_type']) ? $_POST['content_type'] : 'noticias', 'noticias'); ?>>Noticias</option>
                        </select>
                    </td>
                </tr>
            </table>
            <input type="submit" name="generate_ideas" class="button-primary" value="Generar Ideas">
        </form>
        
        <?php
        if (isset($_POST['generate_ideas']) && check_admin_referer('generate_ideas', 'generate_ideas_nonce')) {
            $main_topic = sanitize_text_field($_POST['main_topic']);
            $ideas_count = absint($_POST['ideas_count']);
            $content_type = sanitize_text_field($_POST['content_type']);
            
            if ($main_topic) {
                $ideas = generate_post_ideas($main_topic, $ideas_count, $content_type);
                if ($ideas) {
                    echo '<div class="notice notice-success"><h3>Ideas generadas:</h3>' . $ideas . '</div>';
                } else {
                    echo '<div class="notice notice-error"><p>Error al generar ideas. Verifica tu configuración de API.</p></div>';
                }
            } else {
                echo '<div class="notice notice-warning"><p>Por favor, introduce un tema principal.</p></div>';
            }
        }
        ?>
        
        <?php } elseif ($active_tab == 'create') { ?>
        <h2>Crear post ahora</h2>
        <form method="post">
            <?php wp_nonce_field('create_post_now', 'create_post_nonce'); ?>
            <table class="form-table">
                <tr valign="top">
                    <th scope="row">Prompt personalizado</th>
                    <td>
                        <textarea name="custom_prompt" rows="3" cols="80" placeholder="Deja vacío para usar la configuración por defecto"><?php echo isset($_POST['custom_prompt']) ? esc_textarea($_POST['custom_prompt']) : ''; ?></textarea>
                    </td>
                </tr>
                <tr valign="top">
                    <th scope="row">Proveedor de IA</th>
                    <td>
                        <select name="ai_provider_custom">
                            <option value="" <?php selected(isset($_POST['ai_provider_custom']) ? $_POST['ai_provider_custom'] : '', ''); ?>>Usar configuración por defecto</option>
                            <option value="openai" <?php selected(isset($_POST['ai_provider_custom']) ? $_POST['ai_provider_custom'] : 'openai', 'openai'); ?>>OpenAI</option>
                            <option value="deepseek" <?php selected(isset($_POST['ai_provider_custom']) ? $_POST['ai_provider_custom'] : 'deepseek', 'deepseek'); ?>>DeepSeek</option>
                        </select>
                    </td>
                </tr>
                <tr valign="top">
                    <th scope="row">Fecha y hora de publicación</th>
                    <td>
                        <input type="datetime-local" name="post_date" value="<?php echo esc_attr(date('Y-m-d\TH:i')); ?>" />
                    </td>
                </tr>
                <tr valign="top">
                    <th scope="row">Estado del post</th>
                    <td>
                        <select name="post_status_custom">
                            <option value="" <?php selected(isset($_POST['post_status_custom']) ? $_POST['post_status_custom'] : '', ''); ?>>Usar configuración por defecto</option>
                            <option value="draft" <?php selected(isset($_POST['post_status_custom']) ? $_POST['post_status_custom'] : 'draft', 'draft'); ?>>Borrador</option>
                            <option value="publish" <?php selected(isset($_POST['post_status_custom']) ? $_POST['post_status_custom'] : 'publish', 'publish'); ?>>Publicar</option>
                            <option value="future" <?php selected(isset($_POST['post_status_custom']) ? $_POST['post_status_custom'] : 'future', 'future'); ?>>Programado</option>
                        </select>
                    </td>
                </tr>
            </table>
            <input type="submit" name="create_now" class="button-primary" value="Crear ahora">
        </form>
        
        <?php
        if (isset($_POST['create_now']) && check_admin_referer('create_post_now', 'create_post_nonce')) {
            $prompt = !empty($_POST['custom_prompt']) ? sanitize_textarea_field($_POST['custom_prompt']) : get_option('auto_post_prompt', 'Escribe un post sobre un tema relevante.');
            $category_id = get_option('auto_post_category', 1);
            $tags = explode(',', get_option('auto_post_tags', ''));
            $post_status = !empty($_POST['post_status_custom']) ? sanitize_text_field($_POST['post_status_custom']) : get_option('auto_post_status', 'publish');
            $word_count = get_option('auto_post_word_count', '500');
            $post_date = isset($_POST['post_date']) ? sanitize_text_field($_POST['post_date']) : current_time('mysql');
            $ai_provider = !empty($_POST['ai_provider_custom']) ? sanitize_text_field($_POST['ai_provider_custom']) : get_option('ai_provider', 'openai');

            $message = generate_and_publish_post($prompt, $category_id, $tags, $post_status, $post_date, $word_count, $ai_provider);
            echo "<div class='notice notice-info'><p>" . esc_html($message) . "</p></div>";
        }
        ?>
        
        <?php } ?>
    </div>
    
    <style>
    .nav-tab-wrapper {
        margin-bottom: 20px;
    }
    .form-table th {
        width: 200px;
    }
    .form-table td input[type="text"],
    .form-table td input[type="password"],
    .form-table td textarea {
        width: 400px;
    }
    .form-table td select {
        min-width: 200px;
    }
    .notice h3 {
        margin-top: 0;
    }
    </style>
    <?php
}

// Registrar configuraciones
function register_my_plugin_settings() {
    // Configuración general
    register_setting('my_plugin_settings_group', 'ai_provider', 'sanitize_text_field');
    register_setting('my_plugin_settings_group', 'openai_api_key', 'sanitize_text_field');
    register_setting('my_plugin_settings_group', 'deepseek_api_key', 'sanitize_text_field');
    register_setting('my_plugin_settings_group', 'auto_post_category', 'absint');
    register_setting('my_plugin_settings_group', 'auto_post_tags', 'sanitize_text_field');
    register_setting('my_plugin_settings_group', 'auto_post_status', 'sanitize_text_field');
    
    // Configuración IA
    register_setting('my_plugin_ai_settings_group', 'openai_model', 'sanitize_text_field');
    register_setting('my_plugin_ai_settings_group', 'deepseek_model', 'sanitize_text_field');
    register_setting('my_plugin_ai_settings_group', 'ai_temperature', 'sanitize_text_field');
    register_setting('my_plugin_ai_settings_group', 'ai_max_tokens', 'absint');
    register_setting('my_plugin_ai_settings_group', 'ai_top_p', 'sanitize_text_field');
    register_setting('my_plugin_ai_settings_group', 'ai_frequency_penalty', 'sanitize_text_field');
    register_setting('my_plugin_ai_settings_group', 'ai_presence_penalty', 'sanitize_text_field');
    
    // Configuración contenido
    register_setting('my_plugin_content_settings_group', 'auto_post_prompt', 'sanitize_textarea_field');
    register_setting('my_plugin_content_settings_group', 'auto_post_word_count', 'absint');
    register_setting('my_plugin_content_settings_group', 'writing_style', 'sanitize_text_field');
    register_setting('my_plugin_content_settings_group', 'target_audience', 'sanitize_text_field');
    register_setting('my_plugin_content_settings_group', 'tone', 'sanitize_text_field');
    register_setting('my_plugin_content_settings_group', 'include_faq', 'sanitize_text_field');
    register_setting('my_plugin_content_settings_group', 'include_lists', 'sanitize_text_field');
    register_setting('my_plugin_content_settings_group', 'seo_focus', 'sanitize_text_field');
    register_setting('my_plugin_content_settings_group', 'title_max_length', 'absint');
    register_setting('my_plugin_content_settings_group', 'custom_instructions', 'sanitize_textarea_field');
    
    // Configuración programación
    register_setting('my_plugin_scheduling_settings_group', 'auto_scheduling_enabled', 'sanitize_text_field');
    register_setting('my_plugin_scheduling_settings_group', 'posting_frequency', 'sanitize_text_field');
    register_setting('my_plugin_scheduling_settings_group', 'posting_time', 'sanitize_text_field');
    register_setting('my_plugin_scheduling_settings_group', 'posting_day', 'sanitize_text_field');
    register_setting('my_plugin_scheduling_settings_group', 'auto_topics_list', 'sanitize_textarea_field');
}
add_action('admin_init', 'register_my_plugin_settings');

// Función para generar ideas de posts
function generate_post_ideas($topic, $count, $content_type) {
    $ai_provider = get_option('ai_provider', 'openai');
    
    if ($ai_provider === 'deepseek') {
        $api_key = get_option('deepseek_api_key');
        $endpoint = 'https://api.deepseek.com/v1/chat/completions';
        $model = get_option('deepseek_model', 'deepseek-chat');
    } else {
        $api_key = get_option('openai_api_key');
        $endpoint = 'https://api.openai.com/v1/chat/completions';
        $model = get_option('openai_model', 'gpt-4');
    }
    
    if (!$api_key) {
        return false;
    }
    
    $content_type_instructions = [
        'general' => 'ideas generales de posts',
        'tutorial' => 'tutoriales paso a paso',
        'lista' => 'listas y compilaciones',
        'comparacion' => 'comparaciones y reseñas',
        'noticias' => 'noticias y actualizaciones'
    ];
    
    $instruction = $content_type_instructions[$content_type] ?? 'ideas generales de posts';
    
    $prompt = "Genera {$count} {$instruction} sobre el tema '{$topic}'. ";
    $prompt .= "Cada idea debe ser:";
    $prompt .= "1. Específica y atractiva";
    $prompt .= "2. Optimizada para SEO";
    $prompt .= "3. Útil para la audiencia";
    $prompt .= "4. Factible de escribir";
    $prompt .= "Presenta cada idea en una línea numerada con una breve descripción.";
    
    $data = [
        'model' => $model,
        'messages' => [
            ['role' => 'system', 'content' => 'Eres un experto en marketing de contenidos que genera ideas creativas para posts de blog.'],
            ['role' => 'user', 'content' => $prompt]
        ],
        'max_tokens' => 1000,
        'temperature' => 0.8,
    ];
    
    $response = wp_remote_post($endpoint, [
        'headers' => [
            'Authorization' => 'Bearer ' . $api_key,
            'Content-Type' => 'application/json',
        ],
        'body' => wp_json_encode($data),
        'timeout' => 60,
    ]);
    
    if (is_wp_error($response)) {
        return false;
    }
    
    $body = wp_remote_retrieve_body($response);
    $result = json_decode($body, true);
    
    if (!isset($result['choices'][0]['message']['content'])) {
        return false;
    }
    
    return '<div style="background: #f9f9f9; padding: 15px; border-radius: 5px; white-space: pre-line;">' . esc_html($result['choices'][0]['message']['content']) . '</div>';
}

// Programación automática de posts
function schedule_automatic_posts() {
    if (get_option('auto_scheduling_enabled') !== 'yes') {
        return;
    }
    
    $frequency = get_option('posting_frequency', 'weekly');
    $time = get_option('posting_time', '09:00');
    $day = get_option('posting_day', 'monday');
    
    // Calcular próxima fecha de publicación
    $next_post_time = calculate_next_post_time($frequency, $time, $day);
    
    // Programar evento de WordPress
    if (!wp_next_scheduled('auto_generate_post_hook')) {
        wp_schedule_event($next_post_time, 'auto_post_interval', 'auto_generate_post_hook');
    }
}

// Calcular próxima fecha de publicación
function calculate_next_post_time($frequency, $time, $day) {
    $current_time = current_time('timestamp');
    
    switch ($frequency) {
        case 'daily':
            $next_time = strtotime('tomorrow ' . $time);
            break;
        case 'weekly':
            $next_time = strtotime('next ' . $day . ' ' . $time);
            break;
        case 'biweekly':
            $next_time = strtotime('+2 weeks ' . $day . ' ' . $time);
            break;
        case 'monthly':
            $next_time = strtotime('+1 month ' . $time);
            break;
        default:
            $next_time = strtotime('tomorrow ' . $time);
    }
    
    return $next_time;
}

// Hook para generar post automáticamente
function auto_generate_post() {
    $topics_list = get_option('auto_topics_list', '');
    $topics = array_filter(explode("\n", $topics_list));
    
    if (empty($topics)) {
        return;
    }
    
    // Obtener tema aleatorio
    $random_topic = $topics[array_rand($topics)];
    
    // Generar post
    $category_id = get_option('auto_post_category', 1);
    $tags = explode(',', get_option('auto_post_tags', ''));
    $post_status = get_option('auto_post_status', 'publish');
    $word_count = get_option('auto_post_word_count', '500');
    $ai_provider = get_option('ai_provider', 'openai');
    
    generate_and_publish_post($random_topic, $category_id, $tags, $post_status, current_time('mysql'), $word_count, $ai_provider);
}
add_action('auto_generate_post_hook', 'auto_generate_post');

// Activar programación al activar el plugin
function activate_auto_post_scheduling() {
    schedule_automatic_posts();
}
register_activation_hook(__FILE__, 'activate_auto_post_scheduling');

// Desactivar programación al desactivar el plugin
function deactivate_auto_post_scheduling() {
    wp_clear_scheduled_hook('auto_generate_post_hook');
}
register_deactivation_hook(__FILE__, 'deactivate_auto_post_scheduling');
