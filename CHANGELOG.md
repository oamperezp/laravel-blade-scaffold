# Changelog

All notable changes to `laravel-blade-scaffold` will be documented in this file.

## [1.0.0] - 2026-01-10

### Added

- Initial release
- Automatic CRUD view generation (index, create, edit, show, form)
- Smart field type inference based on field names
- Foreign key detection and automatic select generation
- Reusable Blade components (form-group, label-group, input, textarea, select, checkbox)
- Interactive prompts for asset publishing
- Support for `--only` and `--exclude` options
- Support for `--force` option to overwrite existing files
- Confirmation prompts for overwriting existing files
- Detection of existing custom components
- Automatic detection of fillable fields from models
- Support for custom view paths
- Tailwind CSS styling for all components
- Laravel 8, 9, 10, 11 compatibility
- PHP 7.4, 8.0, 8.1, 8.2, 8.3 compatibility

### Features

- Smart Type Detection: Automatically infers field types
- Foreign Key Support: Auto-generates relationship selects for fields ending in \_id
- Flexible Publishing: Separate tags for templates and components
- Interactive CLI: User-friendly prompts and confirmations
- Error Handling: Comprehensive error messages and suggestions
- Customizable Templates: Easy to publish and modify
- Reusable Components: Clean, maintainable Blade components
