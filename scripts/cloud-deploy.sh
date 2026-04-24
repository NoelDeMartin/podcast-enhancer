#!/bin/bash

set -e

## Run migrations
php artisan migrate --force
