<?php
/*
Plugin Name: Auto Post Generator Pro
Plugin URI: https://webdesignerk.com
Description: This plugin generates and publishes SEO-optimized posts automatically using OpenAI's GPT-4 API with customizable prompts, scheduling, and length control.
Version: 2.1
Author: konstantinWDK
Author URI: https://webdesignerk.com
*/

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

// Function to generate and publish post
function generate_and_publish_post($prompt, $category_id, $tags, $post_status, $post_date, $word_count) {
    $api_key = get_option('openai_api_key');
    if (!$api_key) {
        return 'No se ha proporcionado la clave API';
    }

    $endpoint = 'https://api.openai.com/v1/chat/completions';
    $seo_prompt = "Actúa como un experto en SEO y redacción de contenido. Crea un artículo de blog de aproximadamente {$word_count} palabras sobre el siguiente tema: {$prompt}. 
    Estructura el contenido de la siguiente manera:
    1. Introduce el tema con un párrafo atractivo.
    2. Utiliza encabezados <h2> para las secciones principales.
    3. Utiliza encabezados <h3> para subsecciones cuando sea necesario.
    4. Incluye al menos una lista con viñetas (<ul><li>) o numerada (<ol><li>).
    5. Utiliza <strong> para negritas y <em> para cursivas cuando sea apropiado.
    6. Concluye con un párrafo de resumen.
    7. Añade un schema FAQ de 3 preguntas y respuestas relacionadas con el tema al final del artículo, utilizando el formato de schema.org.
    
    Asegúrate de que el contenido sea informativo, atractivo y optimizado para SEO. Utiliza las etiquetas HTML apropiadas para dar formato al contenido. NO incluyas un título para el artículo.";

    $data = [
        'model' => 'gpt-4',
        'messages' => [
            ['role' => 'system', 'content' => 'Eres un asistente experto en SEO que genera contenido para blogs con formato HTML.'],
            ['role' => 'user', 'content' => $seo_prompt]
        ],
        'max_tokens' => 2000,
        'temperature' => 0.7,
    ];

    $response = wp_remote_post($endpoint, [
        'headers' => [
            'Authorization' => 'Bearer ' . $api_key,
            'Content-Type' => 'application/json',
        ],
        'body' => json_encode($data),
        'timeout' => 120, // Aumentar el tiempo de espera a 120 segundos
    ]);

    if (is_wp_error($response)) {
        return 'Error en la solicitud a OpenAI: ' . $response->get_error_message();
    }

    $body = wp_remote_retrieve_body($response);
    $result = json_decode($body, true);

    if (!isset($result['choices'][0]['message']['content'])) {
        return 'No se generó contenido. Respuesta de la API: ' . print_r($result, true);
    }

    $post_content = $result['choices'][0]['message']['content'];

    // Generar título del post
    $title_prompt = "Genera un título SEO atractivo y conciso (máximo 60 caracteres) para un artículo sobre: {$prompt}. No uses comillas en el título.";
    $title_data = [
        'model' => 'gpt-4',
        'messages' => [
            ['role' => 'system', 'content' => 'Eres un asistente experto en SEO que genera títulos atractivos sin comillas.'],
            ['role' => 'user', 'content' => $title_prompt]
        ],
        'max_tokens' => 60,
        'temperature' => 0.7,
    ];

    $title_response = wp_remote_post($endpoint, [
        'headers' => [
            'Authorization' => 'Bearer ' . $api_key,
            'Content-Type' => 'application/json',
        ],
        'body' => json_encode($title_data),
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
        'post_title' => $post_title,
        'post_content' => $post_content,
        'post_status' => $post_status,
        'post_author' => 1,
        'post_category' => [$category_id],
        'tags_input' => $tags,
        'post_date' => $post_date,
    ];

    // Desactivar temporalmente los filtros de contenido
    remove_filter('content_save_pre', 'wp_filter_post_kses');
    remove_filter('content_filtered_save_pre', 'wp_filter_post_kses');

    $post_id = wp_insert_post($post_data);

    // Reactivar los filtros de contenido
    add_filter('content_save_pre', 'wp_filter_post_kses');
    add_filter('content_filtered_save_pre', 'wp_filter_post_kses');

    if (is_wp_error($post_id)) {
        return "Error al crear el post: " . $post_id->get_error_message();
    }

    return "Post creado con ID: $post_id, programado para: $post_date";
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
    ?>
    <div class="wrap">
        <h1><?php echo esc_html(get_admin_page_title()); ?></h1>
        <form method="post" action="options.php">
            <?php
            settings_fields('my_plugin_settings_group');
            do_settings_sections('my_plugin_settings_group');
            ?>
            <table class="form-table">
                <tr valign="top">
                    <th scope="row">Clave API de OpenAI</th>
                    <td><input type="password" name="openai_api_key" value="<?php echo esc_attr(get_option('openai_api_key')); ?>" /></td>
                </tr>
                <tr valign="top">
                    <th scope="row">Prompt (Instrucciones del post)</th>
                    <td><textarea name="auto_post_prompt" rows="4" cols="50"><?php echo esc_textarea(get_option('auto_post_prompt', 'Escribe un post sobre un tema relevante.')); ?></textarea></td>
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
                    <td><input type="text" name="auto_post_tags" value="<?php echo esc_attr(get_option('auto_post_tags', '')); ?>" /></td>
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
                <tr valign="top">
                    <th scope="row">Número aproximado de palabras</th>
                    <td>
                        <input type="number" name="auto_post_word_count" value="<?php echo esc_attr(get_option('auto_post_word_count', '500')); ?>" min="100" max="2000" />
                    </td>
                </tr>
            </table>
            <?php submit_button(); ?>
        </form>

        <h2>Crear post ahora</h2>
        <form method="post">
            <?php wp_nonce_field('create_post_now', 'create_post_nonce'); ?>
            <table class="form-table">
                <tr valign="top">
                    <th scope="row">Fecha y hora de publicación</th>
                    <td>
                        <input type="datetime-local" name="post_date" value="<?php echo date('Y-m-d\TH:i'); ?>" />
                    </td>
                </tr>
            </table>
            <input type="submit" name="create_now" class="button-primary" value="Crear ahora">
        </form>

        <?php
        if (isset($_POST['create_now']) && check_admin_referer('create_post_now', 'create_post_nonce')) {
            $prompt = get_option('auto_post_prompt', 'Escribe un post sobre un tema relevante.');
            $category_id = get_option('auto_post_category', 1);
            $tags = explode(',', get_option('auto_post_tags', ''));
            $post_status = get_option('auto_post_status', 'publish');
            $word_count = get_option('auto_post_word_count', '500');
            $post_date = isset($_POST['post_date']) ? $_POST['post_date'] : current_time('mysql');

            $message = generate_and_publish_post($prompt, $category_id, $tags, $post_status, $post_date, $word_count);
            echo "<div class='notice notice-info'><p>" . esc_html($message) . "</p></div>";
        }
        ?>
    </div>
    <?php
}

// Registrar configuraciones
function register_my_plugin_settings() {
    register_setting('my_plugin_settings_group', 'openai_api_key', 'sanitize_text_field');
    register_setting('my_plugin_settings_group', 'auto_post_prompt', 'sanitize_textarea_field');
    register_setting('my_plugin_settings_group', 'auto_post_category', 'absint');
    register_setting('my_plugin_settings_group', 'auto_post_tags', 'sanitize_text_field');
    register_setting('my_plugin_settings_group', 'auto_post_status', 'sanitize_text_field');
    register_setting('my_plugin_settings_group', 'auto_post_word_count', 'absint');
    
}
add_action('admin_init', 'register_my_plugin_settings');