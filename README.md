# ReadHub - PDF Library Manager

ReadHub is a web-based PDF library management system that allows users to organize, read, and track their PDF documents.

## Features

- PDF file upload and management
- Category-based organization
- Built-in PDF viewer with progress tracking
- Responsive design for mobile and desktop
- Progress tracking for each PDF
- Category management system

## Requirements

- PHP 7.4 or higher
- Web server (Apache/Nginx)
- Modern web browser
- Write permissions for storage directories

## Installation

1. Clone the repository:
```bash
git clone https://github.com/ademcaniyik/read_hub.git
```

2. Configure your web server to point to the project directory.

3. Ensure write permissions for these directories:
- `categories/`
- `uploads/`
- `metadata/`

4. Create the database:
- Import the `database/schema.sql` file to your MySQL server
- Update database credentials in `includes/config.php`

## Directory Structure

```
read_hub/
├── assets/
│   ├── css/
│   └── js/
├── categories/      # PDF files organized by category
├── includes/        # PHP include files
├── metadata/        # JSON files for progress tracking
├── uploads/         # Temporary upload directory
└── database/        # Database schema and migrations
```

## Usage

1. Create categories to organize your PDFs
2. Upload PDF files to specific categories
3. View PDFs with built-in viewer
4. Progress is automatically saved

## Contributing

1. Fork the repository
2. Create your feature branch
3. Commit your changes
4. Push to the branch
5. Create a new Pull Request

## License

MIT License - see LICENSE file for details
