# MaestrIA Post Generator - Function Documentation

**Plugin Version:** 3.2  
**Author:** konstantinWDK  
**Last Updated:** July 2025

---

## Table of Contents

1. [Overview](#overview)
2. [Main Plugin Class](#main-plugin-class)
3. [Core Classes](#core-classes)
4. [Admin Classes](#admin-classes)
5. [Function Reference](#function-reference)
6. [Hooks and Filters](#hooks-and-filters)
7. [Database Schema](#database-schema)
8. [API Integration](#api-integration)

---

## Overview

The MaestrIA Post Generator is a WordPress plugin that uses AI (OpenAI/DeepSeek) to automatically generate blog posts. The plugin supports multiple languages (Spanish, English, Russian), idea management, post scheduling, and various content customization options.

### Key Features
- AI-powered content generation (OpenAI/DeepSeek)
- Post idea management system
- Automated post scheduling
- Multi-language support
- SEO optimization
- Custom post type for ideas
- AJAX-powered admin interface

---

## Main Plugin Class

### `Miapg_Main`

**Location:** `miapg-post-generator.php:32`

The main plugin class that initializes all components and handles plugin lifecycle.

#### Key Methods:

##### `get_instance()` - Static
**Returns:** `Miapg_Main` instance  
**Purpose:** Implements singleton pattern for the main plugin class.

##### `init()`
**Purpose:** Initializes the plugin after WordPress is fully loaded.  
**Hook:** `init`  
**Actions:** Loads dependencies and initializes components.

##### `load_dependencies()`
**Purpose:** Includes all required plugin files.  
**Files loaded:**
- Core classes (includes/*.php)
- Admin classes (admin/*.php - only in admin)

##### `init_components()`
**Purpose:** Initializes all plugin components using singleton pattern.

##### `activate()`
**Purpose:** Handles plugin activation.  
**Actions:**
- Checks system requirements
- Creates database tables
- Sets default options
- Registers custom post types
- Schedules cron events
- Flushes rewrite rules

##### `deactivate()`
**Purpose:** Handles plugin deactivation.  
**Actions:**
- Clears scheduled cron events
- Flushes rewrite rules

##### `uninstall()` - Static
**Purpose:** Completely removes plugin data.  
**Actions:**
- Removes all plugin options
- Deletes post ideas data
- Removes custom database tables

---

## Core Classes

### `Miapg_Post_Generator`

**Location:** `includes/class-post-generator.php:11`

Handles AI-powered post generation and WordPress post creation.

#### Key Methods:

##### `generate_and_publish_post()` - Static
**Parameters:**
- `$prompt` (string): Topic or idea for the post
- `$category_id` (int): WordPress category ID
- `$tags` (array): Array of tags
- `$post_status` (string): Post status (publish/draft/future)
- `$post_date` (string): Publication date
- `$word_count` (int): Target word count
- `$ai_provider` (string): AI provider (openai/deepseek)
- `$custom_settings` (array): Additional settings
- `$keyword` (string): SEO keyword
- `$source_article` (string): Reference article content
- `$is_from_idea` (bool): Whether generated from saved idea

**Returns:** `string` Success message or error  
**Purpose:** Main function for generating and publishing posts using AI.

##### `generate_content()` - Private Static
**Parameters:**
- `$prompt` (string): Content prompt
- `$word_count` (int): Target word count
- `$keyword` (string): SEO keyword
- `$source_article` (string): Reference article
- `$ai_settings` (array): AI provider settings

**Returns:** `string|WP_Error` Generated content or error  
**Purpose:** Generates post content using AI API.

##### `generate_title()` - Private Static
**Parameters:**
- `$prompt` (string): Post topic
- `$keyword` (string): SEO keyword
- `$ai_settings` (array): AI provider settings

**Returns:** `string|WP_Error` Generated title or error  
**Purpose:** Generates SEO-optimized titles using AI.

##### `build_content_prompt()` - Private Static
**Purpose:** Constructs detailed prompts for AI content generation including HTML formatting requirements and SEO instructions.

##### `create_post()` - Private Static
**Purpose:** Creates WordPress post with the generated content, handling content filters appropriately.

---

### `Miapg_Ideas_Generator`

**Location:** `includes/class-ideas-generator.php:11`

Manages AI-generated post ideas and saves them as custom post types.

#### Key Methods:

##### `generate_post_ideas()` - Static
**Parameters:**
- `$topic` (string): Main topic
- `$count` (int): Number of ideas to generate
- `$content_type` (string): Type of content (general/tutorial/list/comparison/news)

**Returns:** `string|false` Success message with link or false  
**Purpose:** Generates post ideas from a topic and saves them as custom posts.

##### `generate_ideas_from_article()` - Static
**Parameters:**
- `$article` (string): Source article content
- `$count` (int): Number of ideas to generate
- `$approach` (string): Generation approach (related/expanded/alternative/practical)

**Returns:** `string|false` Success message or false  
**Purpose:** Generates post ideas based on existing article content.

##### `get_post_ideas_stats()` - Static
**Returns:** `array` Statistics about saved ideas  
**Purpose:** Provides cached statistics for admin dashboard.  
**Cache:** 5-minute cache using `wp_cache_*` functions.

---

### `Miapg_Settings`

**Location:** `includes/class-settings.php:11`

Manages all plugin settings and configurations.

#### Key Methods:

##### `register_settings()`
**Purpose:** Registers all plugin settings with WordPress Settings API.  
**Hook:** `admin_init`

##### `get_setting()` - Static
**Parameters:**
- `$setting` (string): Setting name
- `$default` (mixed): Default value

**Returns:** `mixed` Setting value  
**Purpose:** Retrieves setting value with fallback to default.

##### `update_setting()` - Static
**Parameters:**
- `$setting` (string): Setting name
- `$value` (mixed): Setting value

**Returns:** `bool` Success status  
**Purpose:** Updates setting value.

##### `get_ai_provider_settings()` - Static
**Returns:** `array` AI provider configuration  
**Keys:** `api_key`, `endpoint`, `model`  
**Purpose:** Gets current AI provider settings based on selected provider.

##### `get_content_settings()` - Static
**Returns:** `array` Content generation settings  
**Purpose:** Retrieves all content-related settings (style, tone, audience, etc.).

##### `get_ai_parameters()` - Static
**Returns:** `array` AI API parameters  
**Keys:** `temperature`, `max_tokens`, `top_p`, `frequency_penalty`, `presence_penalty`  
**Purpose:** Gets AI model parameters for API calls.

##### `get_language_instructions()` - Static
**Parameters:**
- `$language` (string): Optional language code

**Returns:** `string` Language-specific instructions  
**Purpose:** Returns AI instructions in the specified language for content generation.

---

### `Miapg_Scheduler`

**Location:** `includes/class-scheduler.php:11`

Handles automated post generation using WordPress cron system.

#### Key Methods:

##### `schedule_posts()`
**Purpose:** Sets up WordPress cron events based on scheduling settings.  
**Hook:** `init`

##### `auto_generate_post()`
**Purpose:** Automatically generates and publishes posts using saved ideas or topic list.  
**Hook:** `miapg_generate_post_hook` (cron)  
**Logic:**
1. Tries to use saved post ideas first
2. Falls back to random topic from settings
3. Optionally deletes used ideas

##### `schedule_events()` - Static
**Purpose:** Sets up cron schedules during plugin activation.

##### `clear_scheduled_events()` - Static
**Purpose:** Clears all scheduled events during plugin deactivation.

##### `add_cron_intervals()` - Static
**Purpose:** Adds custom cron intervals (weekly, biweekly, monthly) to WordPress.  
**Hook:** `cron_schedules`

---

### `Miapg_Post_Ideas_CPT`

**Location:** `includes/class-post-ideas-cpt.php:11`

Manages the custom post type for post ideas with full admin interface.

#### Key Methods:

##### `register_post_type()`
**Purpose:** Registers the `miapg_post_idea` custom post type.  
**Hook:** `init`

##### `add_meta_boxes()`
**Purpose:** Adds custom meta boxes to idea edit screen.  
**Hook:** `add_meta_boxes`

##### `save_meta_data()`
**Purpose:** Saves custom meta data (keywords, etc.) for ideas.  
**Hook:** `save_post`

##### `custom_columns()`
**Purpose:** Customizes admin columns for ideas list table.  
**Hook:** `manage_miapg_post_idea_posts_columns`

##### `custom_column_content()`
**Purpose:** Populates custom columns with data.  
**Hook:** `manage_miapg_post_idea_posts_custom_column`

##### `register_bulk_actions()`
**Returns:** `array` Bulk actions  
**Purpose:** Adds custom bulk actions (Generate Posts, Add Keywords, Delete Ideas).  
**Hook:** `bulk_actions-edit-miapg_post_idea`

##### `handle_bulk_actions()`
**Purpose:** Processes bulk actions on selected ideas.  
**Hook:** `handle_bulk_actions-edit-miapg_post_idea`

##### `admin_filter()`
**Purpose:** Adds filter dropdowns to ideas admin page.  
**Hook:** `restrict_manage_posts`

##### `filter_query()`
**Purpose:** Modifies WP_Query based on admin filters.  
**Hook:** `pre_get_posts`

---

### `Miapg_Translator`

**Location:** `includes/class-translator.php:11`

Provides multi-language support for the plugin interface.

#### Key Methods:

##### `load_translations()`
**Purpose:** Loads translation arrays for all supported languages (es, en, ru).

##### `get_translation()`
**Parameters:**
- `$key` (string): Translation key

**Returns:** `string` Translated text  
**Purpose:** Returns translation for the current language with fallbacks.

##### `translate()` - Static
**Parameters:**
- `$key` (string): Translation key

**Returns:** `string` Translated text  
**Purpose:** Static method for getting translations.

##### `set_language()`
**Parameters:**
- `$language` (string): Language code

**Purpose:** Changes current language for translations.

---

## Admin Classes

### `Miapg_Admin`

**Location:** `admin/class-admin.php:11`

Main admin class that sets up WordPress admin interface.

#### Key Methods:

##### `add_admin_menu()`
**Purpose:** Adds plugin menu pages to WordPress admin.  
**Hook:** `admin_menu`

##### `enqueue_admin_scripts()`
**Parameters:**
- `$hook` (string): Current admin page hook

**Purpose:** Loads admin CSS and JavaScript files with localization.  
**Hook:** `admin_enqueue_scripts`

##### `admin_notices()`
**Purpose:** Shows admin notices for missing API keys.  
**Hook:** `admin_notices`

---

### `Miapg_Admin_Pages`

**Location:** `admin/class-admin-pages.php:11`

Renders all admin page content and handles form submissions.

#### Key Methods:

##### `render_main_page()`
**Purpose:** Renders the main admin page with tabbed interface.

##### `handle_form_submissions()`
**Parameters:**
- `$active_tab` (string): Current tab

**Purpose:** Processes all form submissions from admin pages.

##### `render_general_tab()`
**Purpose:** Renders general settings tab using WordPress Settings API.

##### `render_ai_tab()`
**Purpose:** Renders AI settings tab.

##### `render_content_tab()`
**Purpose:** Includes content settings view file.

##### `render_scheduling_tab()`
**Purpose:** Includes scheduling settings view file.

##### `render_ideas_tab()`
**Purpose:** Includes ideas management view file.

##### `render_create_tab()`
**Purpose:** Includes post creation view file.

---

### `Miapg_Admin_Ajax`

**Location:** `admin/class-admin-ajax.php:11`

Handles all AJAX requests from the admin interface.

#### Key Methods:

##### `generate_post_ideas()`
**Purpose:** Handles AJAX idea generation requests.  
**Hook:** `wp_ajax_generate_post_ideas`

##### `get_recent_ideas()`
**Purpose:** Returns HTML for recent ideas table via AJAX.  
**Hook:** `wp_ajax_get_recent_ideas`

##### `validate_api_key()`
**Purpose:** Validates AI provider API keys via AJAX.  
**Hook:** `wp_ajax_validate_api_key`

##### `save_setting()`
**Purpose:** Saves individual settings via AJAX.  
**Hook:** `wp_ajax_save_setting`

##### `delete_idea()`
**Purpose:** Deletes individual ideas via AJAX.  
**Hook:** `wp_ajax_delete_idea`

##### `bulk_ideas_action()`
**Purpose:** Handles bulk actions on ideas via AJAX.  
**Hook:** `wp_ajax_bulk_ideas_action`

##### `test_api_key()` - Private
**Parameters:**
- `$api_key` (string): API key to test
- `$provider` (string): AI provider

**Returns:** `bool` Validation result  
**Purpose:** Tests API key validity by making a minimal API call.

---

## Function Reference

### Global Functions

#### `miapg_translate($key)`
**Location:** `includes/class-translator.php:349`  
**Parameters:**
- `$key` (string): Translation key

**Returns:** `string` Translated text  
**Purpose:** Global function for translating interface strings.

#### `miapg_t($key)`
**Location:** `includes/class-translator.php:354`  
**Purpose:** Short alias for `miapg_translate()`.

---

## Hooks and Filters

### Action Hooks

#### Plugin Lifecycle
- `init` - Initialize plugin components
- `admin_init` - Register admin settings
- `admin_menu` - Add admin menu pages
- `admin_enqueue_scripts` - Load admin assets

#### Custom Post Type
- `add_meta_boxes` - Add idea meta boxes
- `save_post` - Save idea meta data
- `restrict_manage_posts` - Add admin filters
- `pre_get_posts` - Filter admin queries

#### AJAX Hooks
- `wp_ajax_generate_post_ideas`
- `wp_ajax_get_recent_ideas`
- `wp_ajax_validate_api_key`
- `wp_ajax_save_setting`
- `wp_ajax_delete_idea`
- `wp_ajax_bulk_ideas_action`

#### Cron Hooks
- `miapg_generate_post_hook` - Auto post generation

### Filters

#### WordPress Standard
- `manage_miapg_post_idea_posts_columns` - Custom columns
- `manage_miapg_post_idea_posts_custom_column` - Column content
- `manage_edit-miapg_post_idea_sortable_columns` - Sortable columns
- `post_row_actions` - Row actions
- `bulk_actions-edit-miapg_post_idea` - Bulk actions
- `handle_bulk_actions-edit-miapg_post_idea` - Handle bulk actions
- `post_updated_messages` - Update messages
- `cron_schedules` - Custom cron intervals

---

## Database Schema

### WordPress Options

#### General Settings
- `miapg_ai_provider` - AI provider selection (openai/deepseek)
- `miapg_openai_api_key` - OpenAI API key
- `miapg_deepseek_api_key` - DeepSeek API key
- `miapg_interface_language` - Interface language (es/en/ru)
- `miapg_post_category` - Default post category
- `miapg_post_tags` - Default post tags
- `miapg_post_status` - Default post status

#### AI Settings
- `miapg_openai_model` - OpenAI model selection
- `miapg_deepseek_model` - DeepSeek model selection
- `miapg_ai_temperature` - AI creativity parameter
- `miapg_ai_max_tokens` - Maximum tokens per request
- `miapg_ai_top_p` - AI top_p parameter
- `miapg_ai_frequency_penalty` - Frequency penalty
- `miapg_ai_presence_penalty` - Presence penalty

#### Content Settings
- `miapg_writing_style` - Content writing style
- `miapg_target_audience` - Target audience
- `miapg_tone` - Content tone
- `miapg_post_word_count` - Target word count
- `miapg_include_faq` - Include FAQ sections
- `miapg_include_lists` - Include lists in content
- `miapg_seo_focus` - SEO optimization level
- `miapg_title_max_length` - Maximum title length
- `miapg_custom_instructions` - Additional AI instructions

#### Scheduling Settings
- `miapg_scheduling_enabled` - Enable auto-posting
- `miapg_posting_frequency` - Posting frequency
- `miapg_posting_time` - Posting time
- `miapg_posting_day` - Posting day
- `miapg_topics_list` - Topics for auto-generation
- `miapg_delete_used_ideas` - Delete ideas after use

### Custom Post Type

#### Post Type: `miapg_post_idea`
**Purpose:** Stores generated post ideas

#### Meta Fields:
- `_miapg_idea_topic` - Original topic used for generation
- `_miapg_idea_keyword` - SEO keyword for the idea
- `_miapg_idea_content_type` - Type of content
- `_miapg_idea_generated_date` - Generation timestamp
- `_miapg_idea_source_article` - Source article (if applicable)

---

## API Integration

### OpenAI Integration

#### Endpoint: `https://api.openai.com/v1/chat/completions`

#### Supported Models:
- `gpt-4` - Latest GPT-4 model
- `gpt-4-turbo` - GPT-4 Turbo model
- `gpt-3.5-turbo` - GPT-3.5 Turbo model

#### Request Structure:
```php
array(
    'model' => $model,
    'messages' => array(
        array('role' => 'system', 'content' => $system_prompt),
        array('role' => 'user', 'content' => $user_prompt)
    ),
    'max_tokens' => $max_tokens,
    'temperature' => $temperature,
    'top_p' => $top_p,
    'frequency_penalty' => $frequency_penalty,
    'presence_penalty' => $presence_penalty
)
```

### DeepSeek Integration

#### Endpoint: `https://api.deepseek.com/v1/chat/completions`

#### Supported Models:
- `deepseek-chat` - General chat model
- `deepseek-coder` - Code-optimized model

#### Request Structure:
Same as OpenAI API (compatible interface)

### Error Handling

All API calls include:
- Timeout handling (30-120 seconds)
- Response validation
- Error logging (when WP_DEBUG_LOG is enabled)
- Fallback error messages
- WP_Error objects for error propagation

---

## Development Guidelines

### Code Standards
- Follows WordPress Coding Standards
- Uses PHPCS for code validation
- Implements WordPress nonce verification
- Proper input sanitization and output escaping
- Object-oriented design with singleton pattern

### Security Measures
- Nonce verification for all forms
- Capability checks for admin functions
- Input sanitization using WordPress functions
- Output escaping for all user data
- SQL injection prevention using WP_Query

### Performance Optimization
- Caching for statistics and repeated queries
- Minimal database queries
- Efficient post meta handling
- Proper use of WordPress transients
- AJAX for non-blocking operations

### Extensibility
- Hook system for custom functionality
- Filter system for modifying behavior
- Modular class structure
- Plugin settings API integration
- Custom post type for data storage

---

*This documentation is automatically maintained and should be updated with any significant changes to the plugin functionality.*