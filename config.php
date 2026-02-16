<?php
declare(strict_types=1);

const DB_HOST = '127.0.0.1';
const DB_NAME = 'eventos';
const DB_USER = 'root';
const DB_PASS = ''; // SOLO LOCAL

const APP_NAME = 'Control de Eventos';
const BASE_URL = '/eventos'; // ruta de la carpeta del proyecto

const LOGIN_MAX_ATTEMPTS = 5;
const LOGIN_BLOCK_MINUTES = 15;

const QR_RATE_LIMIT_MAX = 5;
const QR_RATE_LIMIT_WINDOW = 60; // seconds

const TOKEN_MIN_LEN = 20;
