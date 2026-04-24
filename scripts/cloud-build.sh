#!/bin/bash

set -e

## Install FFmpeg
echo "Downloading FFmpeg..."
mkdir -p bin/ffmpeg
cd bin/ffmpeg
curl --silent --show-error --fail -L -o ffmpeg.tar.xz "https://johnvansickle.com/ffmpeg/releases/ffmpeg-release-arm64-static.tar.xz"

echo "Extracting FFmpeg..."
tar -xf ffmpeg.tar.xz --strip-components=1
chmod +x ffmpeg ffprobe
rm -f ffmpeg.tar.xz

echo "FFmpeg installation complete!"

cd -

## Install Vite+
echo "Installing Vite+..."
curl -fsSL https://vite.plus | VP_HOME="$PWD/.vite-plus" bash

## Install dependencies & Build
echo "Installing dependencies..."
composer install --no-dev --no-interaction --prefer-dist --optimize-autoloader
./.vite-plus/bin/vp install

echo "Building assets..."
php artisan wayfinder:generate --with-form
./.vite-plus/bin/vp run build
