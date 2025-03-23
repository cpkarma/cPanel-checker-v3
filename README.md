# Mass cPanel Checker v3

This is a simple PHP-based API that checks the validity of cPanel login credentials by attempting to log in and analyzing the response. When valid credentials are found, they are saved to a `found.txt` file.

## Features
- Validates cPanel credentials in the format `https://domain.com:2083|username|password`.
- Returns JSON responses with status and debug information.
- Saves working credentials to `v3_*.txt`.
- Handles CORS for cross-origin requests.

## Requirements
- PHP 7.x or 8.x with cURL extension enabled.
- Write permissions for the directory containing `v3_*.txt`.

## Installation
1. Clone or download this repository:
   ```bash
   git clone https://github.com/yourusername/cpanel-checker-api.git```
2. Host the **web_interface.php** to your website or localhost
3. If you're good with only php then run the **cli.php**

## Web Interface
![Alt text](https://raw.githubusercontent.com/cpkarma/img/main/v3_1.jpg)

## PHP Cli (Terminal)
![Alt text](https://raw.githubusercontent.com/cpkarma/img/main/v3_2.jpg)
