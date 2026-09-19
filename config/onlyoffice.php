<?php

return [
    // Alamat Document Server yang dibuka BROWSER (harus bisa diakses user)
    'server_url' => env('ONLYOFFICE_SERVER_URL', 'http://localhost:8081'),

    // (Opsional) Alamat Document Server yang dijangkau LARAVEL, untuk mengunduh hasil edit.
    // Isi kalau Laravel tidak bisa memakai alamat publik di atas.
    'server_url_internal' => env('ONLYOFFICE_SERVER_URL_INTERNAL'),

    // (Opsional) Alamat aplikasi Laravel yang dijangkau DOCUMENT SERVER
    // (untuk mengunduh file & memanggil callback). Kosong = pakai url('/').
    'app_url' => env('ONLYOFFICE_APP_URL'),

    // Harus sama persis dengan JWT_SECRET di Document Server
    'secret' => env('ONLYOFFICE_JWT_SECRET'),
];