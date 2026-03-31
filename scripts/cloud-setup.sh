#!/bin/bash

set -e

echo "Downloading FFmpeg..."
mkdir -p bin/ffmpeg
cd bin/ffmpeg
curl --silent --show-error --fail -L -o ffmpeg.tar.xz "https://johnvansickle.com/ffmpeg/releases/ffmpeg-release-arm64-static.tar.xz"

echo "Extracting FFmpeg..."
tar -xf ffmpeg.tar.xz --strip-components=1
chmod +x ffmpeg ffprobe
rm -f ffmpeg.tar.xz

echo "FFmpeg installation complete!"
