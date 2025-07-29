# Changelog

All notable changes to Auto Post Generator will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [3.2.1] - 2024-01-15

### Fixed
- **CRITICAL**: Compliance with WordPress standards for JavaScript and CSS inclusion
- Removed all inline JavaScript and CSS code embedded in HTML
- Scripts now load conditionally based on admin page
- Improved script dependency system and localization

### Added
- Proper implementation of wp_enqueue_script() and wp_enqueue_style()
- Separated inline code into dedicated files:
  - assets/js/ideas-manager.js - Ideas management functionality
  - assets/js/url-cleaner.js - URL cleaning functionality  
  - assets/js/ideas-tab.js - Ideas tab functionality
  - assets/js/page-redirecter.js - Page redirection functionality
  - assets/css/post-ideas-cpt.css - Custom Post Type styles

### Improved
- Performance optimization with conditional resource loading
- Better script dependency management
- Enhanced WordPress coding standards compliance
- Cleaner code organization and maintainability

## [3.1] - 2024-01-01

### Added
- Complete idea management system with Custom Post Type
- Article-based post generation functionality
- Article-based idea generation with multiple approaches
- Category selection for individual posts
- Keyword-focused content generation
- HTML output optimization (basic tags only)
- Individual idea deletion with confirmation
- Generate post button directly from ideas list
- Real-time idea statistics
- Advanced filtering for ideas management
- Automatic idea prioritization in scheduling
- Option to auto-delete used ideas
- Enhanced admin interface with improved navigation
- AJAX-powered idea generation
- API key validation
- Security improvements with proper nonce handling
- Responsive design for mobile devices
- Comprehensive error handling
- WordPress coding standards compliance
- Plugin structure reorganization
- Translation support with POT file

### Changed
- Reorganized plugin structure following WordPress best practices
- Enhanced user interface with better usability
- Improved content generation prompts
- Better error messages and user feedback
- Optimized database queries
- Enhanced security measures
- Improved code organization and maintainability

### Fixed
- Various bug fixes and performance improvements
- Better API error handling
- Improved content formatting
- Enhanced compatibility with WordPress themes

## [3.0] - 2023-12-01

### Added
- DeepSeek AI integration with full API support
- Advanced content customization (writing style, tone, audience)
- Automatic scheduling system with multiple frequency options
- Post ideas generator for creative brainstorming
- Tabbed user interface for better organization
- Advanced AI parameters control (temperature, tokens, penalties)
- Custom instructions for personalized content
- Configurable title length limits
- Enhanced SEO focus options

### Improved
- Better error handling and user feedback
- Security enhancements and code optimization
- Mobile-responsive admin interface

## [2.1] - 2023-11-01

### Added
- Schema FAQ generation for SEO
- Settings for controlling post word count and scheduling

### Improved
- API request handling and error messages
- Security with nonce checks for forms

## [2.0] - 2023-10-01

### Added
- Initial release
- Basic post generation functionality
- OpenAI GPT-4 integration
- Simple scheduling system
- Basic customization options