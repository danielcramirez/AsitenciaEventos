#!/bin/bash
# Installation and Testing Script for AsitenciaEventos

echo "=================================="
echo "AsitenciaEventos - Installation"
echo "=================================="
echo ""

# Check PHP version
echo "Checking PHP version..."
php --version | head -n 1
if [ $? -ne 0 ]; then
    echo "ERROR: PHP not found. Please install PHP 8.0 or higher."
    exit 1
fi

# Check MySQL
echo ""
echo "Checking MySQL..."
mysql --version
if [ $? -ne 0 ]; then
    echo "ERROR: MySQL not found. Please install MySQL 8.0 or higher."
    exit 1
fi

# Create database
echo ""
echo "Creating database..."
mysql -u root -p -e "CREATE DATABASE IF NOT EXISTS asistencia_eventos CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
if [ $? -eq 0 ]; then
    echo "✓ Database created successfully"
else
    echo "ERROR: Could not create database. Please check MySQL credentials."
    exit 1
fi

# Import schema
echo ""
echo "Importing database schema..."
mysql -u root -p asistencia_eventos < database/schema.sql
if [ $? -eq 0 ]; then
    echo "✓ Schema imported successfully"
else
    echo "ERROR: Could not import schema."
    exit 1
fi

# Verify installation
echo ""
echo "Verifying installation..."
mysql -u root -p asistencia_eventos -e "SELECT COUNT(*) as users FROM users;"
if [ $? -eq 0 ]; then
    echo "✓ Installation verified"
else
    echo "ERROR: Could not verify installation."
    exit 1
fi

echo ""
echo "=================================="
echo "Installation Complete!"
echo "=================================="
echo ""
echo "To start the development server:"
echo "  php -S localhost:8000"
echo ""
echo "Then visit: http://localhost:8000"
echo ""
echo "Default credentials:"
echo "  Admin: admin / admin123"
echo "  Operador: operador1 / admin123"
echo "  Asistente: asistente1 / admin123"
echo ""
