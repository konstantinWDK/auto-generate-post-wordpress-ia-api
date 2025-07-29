# MaestrIA Post Generator

[![WordPress Plugin Version](https://img.shields.io/badge/WordPress-6.8+-blue.svg)](https://wordpress.org/)
[![PHP Version](https://img.shields.io/badge/PHP-7.4+-purple.svg)](https://php.net/)
[![License](https://img.shields.io/badge/License-GPLv2-green.svg)](https://www.gnu.org/licenses/gpl-2.0.html)
[![Plugin Check](https://img.shields.io/badge/Plugin%20Check-Compliant-brightgreen.svg)](https://wordpress.org/plugins/plugin-check/)

**🌐 Language**: [English](#) | [Español](README.es.md)

---

## Advanced AI-Powered WordPress Content Generator

MaestrIA Post Generator is the most advanced WordPress plugin for automated content creation. It supports multiple AI providers (OpenAI and DeepSeek), offers extensive customization options, automatic scheduling, idea management, and content generation based on reference articles.

### 🚀 What's New in Version 3.2.4

- **Plugin Check Compliant**: Full compliance with WordPress official validation tool
- **Performance Optimization**: Improved database queries and escape functions
- **Enhanced Internationalization**: Complete translator comments for all placeholders
- **Reinforced Security**: Optimized output escaping and improved nonce validations
- **Clean Code**: Resolution of all development warnings and best practices

---

## ✨ Key Features

### 🧠 **AI Integration**
- **OpenAI Support**: GPT-4, GPT-4 Turbo, GPT-3.5 Turbo
- **DeepSeek Integration**: DeepSeek Chat, DeepSeek Coder
- **Easy Provider Switching**: Switch between AI providers seamlessly
- **Advanced Parameters**: Temperature, tokens, penalties control

### 💡 **Advanced Idea Management System**
- **Custom Post Type**: Store and manage ideas as WordPress CPT
- **Individual Management**: Edit, delete, and organize each idea
- **Direct Generation**: Generate posts directly from stored ideas
- **Keyword Focus**: Assign specific keywords to each idea
- **Status Tracking**: Available, used, draft status management
- **Bulk Operations**: Mass delete, keyword assignment, post generation

### 📝 **Content Generation Options**
- **Free Topic Generation**: Create content from any topic
- **Article-Based Generation**: Generate content based on reference articles
- **Idea-Based Generation**: Use stored ideas for content creation
- **Keyword Optimization**: SEO-focused content with specific keywords
- **Category Selection**: Custom category assignment

### 🎨 **Content Customization**
- **Writing Styles**: Informative, Conversational, Technical, Creative, Academic
- **Target Audience**: General, Beginners, Intermediate, Experts, Professionals
- **Tone Options**: Professional, Friendly, Serious, Humorous, Inspiring
- **Word Count**: Customizable from 100-3000 words
- **Custom Instructions**: Personalized content prompts
- **SEO Focus Levels**: Low, Medium, High optimization

### 📅 **Scheduling & Automation**
- **Automatic Scheduling**: Daily, weekly, bi-weekly, monthly posting
- **Idea Prioritization**: Uses stored ideas before manual topics
- **Custom Timing**: Set specific days and hours for publishing
- **Auto-cleanup**: Optional automatic deletion of used ideas
- **Real-time Statistics**: Live idea statistics dashboard

### 🌐 **Multilingual Support**
- **Interface Languages**: Spanish, English, Russian
- **Content Generation**: Generate content in multiple languages
- **Translation Ready**: Full internationalization support
- **RTL Support**: Right-to-left language compatibility

---

## 🔧 Technical Requirements

- **WordPress**: 5.0 or higher (tested up to 6.8)
- **PHP**: 7.4 or higher
- **API Keys**: OpenAI and/or DeepSeek API keys required
- **Memory**: Recommended 128MB PHP memory limit
- **Database**: MySQL 5.6+ or MariaDB 10.1+

---

## 📦 Installation

### Automatic Installation
1. Go to your WordPress admin dashboard
2. Navigate to **Plugins > Add New**
3. Search for "MaestrIA Post Generator"
4. Click **Install Now** and then **Activate**

### Manual Installation
1. **Download** the plugin files
2. **Upload** to `/wp-content/plugins/miapg-post-generator/` directory
3. **Activate** the plugin through the WordPress 'Plugins' screen
4. **Configure** your AI provider and API key

### First Setup
1. Go to **Auto Post Generator** in your WordPress admin menu
2. Navigate to **General Settings** tab
3. Select your AI provider (OpenAI or DeepSeek)
4. Enter your API key
5. Configure default settings
6. Start generating content!

---

## ⚙️ Configuration Guide

### General Settings
- **AI Provider**: Choose between OpenAI or DeepSeek
- **API Keys**: Configure your OpenAI and/or DeepSeek API keys
- **Interface Language**: Select your preferred interface language
- **Default Category**: Set default category for generated posts
- **Default Tags**: Configure default tags (comma-separated)
- **Post Status**: Choose default post status (publish, draft, future)

### AI Settings
- **OpenAI Model**: Select from GPT-4, GPT-4 Turbo, GPT-3.5 Turbo
- **DeepSeek Model**: Choose between DeepSeek Chat, DeepSeek Coder
- **Temperature**: Control creativity (0.0-2.0)
- **Max Tokens**: Set maximum response length
- **Frequency Penalty**: Reduce repetition (-2.0 to 2.0)
- **Presence Penalty**: Encourage topic diversity (-2.0 to 2.0)

### Content Settings
- **Writing Style**: Choose your preferred writing approach
- **Target Audience**: Define your content's target readers
- **Tone**: Set the emotional tone of your content
- **SEO Focus**: Configure SEO optimization level
- **Word Count**: Set default word count for posts
- **Custom Instructions**: Add personalized prompts

### Scheduling Settings
- **Enable Scheduler**: Turn on/off automatic posting
- **Frequency**: Set posting frequency (daily, weekly, etc.)
- **Time**: Choose specific posting times
- **Days**: Select which days to post
- **Auto Delete Ideas**: Automatically remove used ideas

---

## 🔐 External Services

This plugin connects to external AI services for content generation:

### OpenAI API
- **Service Provider**: OpenAI Inc. (https://openai.com)
- **Purpose**: Content generation using GPT models
- **Data Transmitted**: 
  - Text prompts and content topics
  - API configuration parameters
  - Your personal OpenAI API key
- **When Data is Sent**:
  - When generating posts automatically
  - When generating post ideas
  - When creating article titles
  - When validating your OpenAI API key
- **Terms of Service**: https://openai.com/terms/
- **Privacy Policy**: https://openai.com/privacy/

### DeepSeek AI API
- **Service Provider**: DeepSeek AI (https://www.deepseek.com)
- **Purpose**: Alternative AI content generation
- **Data Transmitted**:
  - Text prompts and content topics
  - API configuration parameters
  - Your personal DeepSeek API key
- **When Data is Sent**:
  - When generating content with DeepSeek selected as provider
  - When generating ideas using DeepSeek
  - When creating titles with DeepSeek
  - When validating your DeepSeek API key
- **Terms of Service**: https://www.deepseek.com/terms
- **Privacy Policy**: https://www.deepseek.com/privacy

### Important Notice
**User Responsibility**: This plugin requires you to provide your own API keys for these services. Data is transmitted directly from your website to your selected AI providers. You are responsible for compliance with their terms of service and privacy policies. The plugin does not store or process this data; it simply acts as an intermediary for API requests.

---

## 🔄 Changelog

### Version 3.2.4 (Current)
- **CRITICAL**: Full Plugin Check compliance - WordPress official validation tool
- **CRITICAL**: Fixed all warnings and errors detected by Plugin Check
- **NEW**: Translator comments for all internationalization functions with placeholders
- **NEW**: Optimized output escaping for all variables in admin/class-admin.php
- **NEW**: Use of gmdate() instead of date() to avoid timezone issues
- **FIXED**: Escaping of $provider_status, $api_key_status, $params_valid and $capabilities_status
- **FIXED**: phpcs comments for error_log() conditioned by WP_DEBUG_LOG
- **FIXED**: Nonce verifications with phpcs comments for read-only cases
- **FIXED**: __() function in includes/class-post-generator.php with missing translator comment
- **IMPROVED**: Direct DB queries with phpcs comments justifying their necessity
- **IMPROVED**: Optimized meta_query in scheduler with explanatory comment
- **IMPROVED**: WordPress coding standards compliance
- **IMPROVED**: Code prepared for WordPress official directory approval

### Version 3.2.3
- **CRITICAL**: Complete nonce verification implementation for CSRF protection
- **CRITICAL**: Exhaustive user permission validation in all sensitive operations
- **NEW**: URL protection with idea_id through security nonces in create-tab.php
- **NEW**: Nonce verification in idea access for post generation
- **NEW**: Security reinforcement in idea deletion system
- **FIXED**: All URLs with ID parameters now include wp_nonce_url() verification
- **FIXED**: Separation of nonce verification logic from other conditions
- **FIXED**: Validation of specific permissions (edit_miapg_post_ideas, delete_miapg_post_ideas)
- **IMPROVED**: Security in admin-pages.php with dual nonce verification
- **IMPROVED**: Protection in class-post-ideas-cpt.php for all idea actions
- **IMPROVED**: Notification system with improved authentication in class-admin.php
- **IMPROVED**: Full compliance with WordPress directory security standards

[View complete changelog](CHANGELOG.md)

---

## 🤝 Contributing

We welcome contributions from the community! Here's how you can help:

### Development Setup
1. **Fork** the repository
2. **Clone** your fork locally
3. **Create** a new branch for your feature
4. **Install** development dependencies
5. **Make** your changes
6. **Test** thoroughly
7. **Submit** a pull request

### Coding Standards
- Follow WordPress coding standards
- Use proper documentation
- Include translator comments for internationalization
- Test with Plugin Check tool
- Ensure PHP 7.4+ compatibility

### Types of Contributions
- 🐛 Bug fixes
- ✨ New features
- 📚 Documentation improvements
- 🌐 Translations
- 🧪 Testing
- 💡 Feature suggestions

---

## 📞 Support

### Getting Help
If you encounter issues or have questions:

1. **Check the FAQ** section below
2. **Search existing issues** in this repository
3. **Visit our support page**: [WebDesignerK Support](https://webdesignerk.com/support)
4. **Create a new issue** with detailed information
5. **Contact us** through our website

### Bug Reports
When reporting bugs, please include:
- WordPress version
- PHP version  
- Plugin version
- Steps to reproduce
- Expected vs actual behavior
- Error messages (if any)

---

## 🙋‍♂️ Frequently Asked Questions

### How do I get API keys?
- **OpenAI**: Visit [OpenAI Platform](https://platform.openai.com/api-keys) and create an account
- **DeepSeek**: Visit [DeepSeek Platform](https://platform.deepseek.com/api_keys) and sign up

### Can I use both AI providers?
Yes! You can configure both API keys and switch between providers as needed for different types of content.

### Is the generated content SEO optimized?
Absolutely! The plugin includes multiple SEO focus levels and keyword optimization features to help your content rank better.

### Does it work with all WordPress themes?
Yes, the plugin generates clean, semantic HTML that's compatible with all properly coded WordPress themes.

### Can I customize the generated content?
Yes, you have extensive customization options including writing style, tone, target audience, word count, and custom instructions.

### How does the idea management system work?
Ideas are stored as a custom post type in WordPress, allowing you to manage them like regular posts - edit, delete, categorize, and generate content from them.

### Is my API key secure?
Your API keys are stored securely in your WordPress database and are only used to communicate with your chosen AI provider. We recommend using environment variables for additional security.

### Can I schedule automatic posting?
Yes! The plugin includes a comprehensive scheduling system with options for daily, weekly, bi-weekly, and monthly posting at specific times.

### What languages are supported?
The interface supports Spanish, English, and Russian. Content can be generated in any language supported by your chosen AI provider.

### How do I update the plugin?
The plugin can be updated through your WordPress admin dashboard when updates become available, just like any other WordPress plugin.

---

## 📸 Screenshots

![Main Dashboard](screenshots/dashboard.png)
*Main plugin dashboard with tabbed interface*

![Idea Management](screenshots/ideas-management.png)
*Advanced idea management system with bulk operations*

![Content Generation](screenshots/content-generation.png)
*Content generation interface with customization options*

![Settings Panel](screenshots/settings.png)
*Comprehensive settings and configuration panel*

![AI Configuration](screenshots/ai-settings.png)
*AI provider configuration and parameter settings*

![Scheduling System](screenshots/scheduling.png)
*Automatic posting scheduler configuration*

---

## 📄 License

This project is licensed under the **GNU General Public License v2.0 or later**.

```
MaestrIA Post Generator
Copyright (C) 2024 WebDesignerK

This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation; either version 2 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
GNU General Public License for more details.
```

[View full license](LICENSE)

---

## 🔗 Links & Resources

- **🌐 Official Website**: [WebDesignerK.com](https://webdesignerk.com/)
- **📧 Support**: [Support Page](https://webdesignerk.com/support)
- **📚 Documentation**: [Full Documentation](https://webdesignerk.com/docs/miapg-post-generator)
- **🐛 Issue Tracker**: [GitHub Issues](https://github.com/konstantinwdk/miapg-post-generator/issues)
- **💬 Community**: [WordPress.org Support Forum](https://wordpress.org/support/plugin/miapg-post-generator/)
- **⭐ Rate on WordPress.org**: [Plugin Directory](https://wordpress.org/plugins/miapg-post-generator/)

---

## 🏆 Credits

### Author(s)
- **konstantinWDK** - Lead Developer
- **WebDesignerK Team** - Design & Testing

### Special Thanks
- WordPress Community for feedback and suggestions
- OpenAI and DeepSeek teams for their excellent AI APIs
- All beta testers and contributors

### Third-Party Resources
- Icons by [Dashicons](https://developer.wordpress.org/resource/dashicons/)
- Badges by [Shields.io](https://shields.io/)

---

*Made with ❤️ by [WebDesignerK](https://webdesignerk.com/) - Empowering WordPress developers worldwide*

---

**⚡ Quick Start**: Install → Configure API → Generate Ideas → Create Content → Publish! 🚀