# Naresh Portfolio Core

A WordPress plugin that adds portfolio features: a Projects custom post type, a Technology taxonomy, a `[latest_projects]` shortcode and a project link field.

## Features

- `Project` custom post type with title, content, excerpt and featured image
- `Technology` taxonomy for tagging projects
- Meta box for a project link, saved with a nonce check, a capability check and sanitisation
- Shortcode: `[latest_projects count="3"]`
- Rewrite rules are flushed on activation

## Installation

1. Copy this folder to `wp-content/plugins/`
2. Activate **Naresh Portfolio Core** under Plugins
3. Add projects under Projects, Add New

## Usage

Show the latest projects anywhere in a page or post:

```
[latest_projects count="3"]
```

## Author

Naresh Suthar