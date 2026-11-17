<?php
date_default_timezone_set('Asia/Jakarta');
define('BASEURL', 'http://localhost/nnaco.sa/public');

// DB Configuration
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'nnaco_sa');

// AI Configuration
define('AI_API_KEY', 'ISI_GROQ_API_KEY_DISINI');
define('AI_PROVIDER_URL', 'https://api.groq.com/openai/v1/chat/completions');
define('AI_MODEL', 'llama-3.3-70b-versatile');
define('AI_ENCRYPTION_KEY', 'ISI_ENCRYPTION_KEY_DISINI');