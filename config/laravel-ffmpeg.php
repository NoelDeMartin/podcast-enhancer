<?php

return [
    'ffmpeg' => [
        'binaries' => env('FFMPEG_BINARIES', base_path('bin/ffmpeg/ffmpeg')),

        'threads' => (int) env('FFMPEG_THREADS', 1),
    ],

    'ffprobe' => [
        'binaries' => env('FFPROBE_BINARIES', base_path('bin/ffmpeg/ffprobe')),
    ],

    'timeout' => 3600,

    'log_channel' => env('LOG_CHANNEL', 'stack'),   // set to false to completely disable logging

    'temporary_files_root' => env('FFMPEG_TEMPORARY_FILES_ROOT', sys_get_temp_dir()),

    'temporary_files_encrypted_hls' => env('FFMPEG_TEMPORARY_ENCRYPTED_HLS', env('FFMPEG_TEMPORARY_FILES_ROOT', sys_get_temp_dir())),
];
