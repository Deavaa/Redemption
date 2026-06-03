#!/bin/bash
# ================================================================
# DEPLOYMENT PACKAGER for Shared Hosting
# ================================================================
# This script creates a deployment-ready ZIP file for uploading
# to free shared hosting (ByetHost, InfinityFree, etc.)
# ================================================================

set -e

PROJECT_DIR="/home/z/my-project/Redemption"
OUTPUT_DIR="/home/z/my-project/download"
TIMESTAMP=$(date +%Y%m%d_%H%M%S)
ZIP_NAME="Redemption_deploy_${TIMESTAMP}.zip"
ZIP_PATH="${OUTPUT_DIR}/${ZIP_NAME}"

echo "========================================="
echo "  Redemption Laravel Deployment Packager"
echo "========================================="
echo ""

# Step 1: Install production dependencies
echo "[1/7] Installing production Composer dependencies..."
cd "$PROJECT_DIR"
composer install --no-dev --optimize-autoloader --no-interaction 2>/dev/null || {
    echo "Warning: Composer install failed. Using existing vendor/ directory."
}

# Step 2: Clear caches
echo "[2/7] Clearing Laravel caches..."
php artisan config:clear 2>/dev/null || true
php artisan route:clear 2>/dev/null || true
php artisan view:clear 2>/dev/null || true

# Step 3: Ensure storage directories exist
echo "[3/7] Ensuring storage directories exist..."
mkdir -p storage/framework/sessions
mkdir -p storage/framework/views
mkdir -p storage/framework/cache
mkdir -p storage/framework/cache/data
mkdir -p storage/logs
mkdir -p bootstrap/cache

# Step 4: Create the ZIP file
echo "[4/7] Creating deployment ZIP..."
rm -f "$ZIP_PATH"

# Create zip excluding dev files
cd "$PROJECT_DIR"
zip -r "$ZIP_PATH" . \
    -x ".git/*" \
    -x ".gitignore" \
    -x "node_modules/*" \
    -x ".env" \
    -x "*.bak" \
    -x "phpunit.xml" \
    -x "tests/*" \
    -x ".phpstorm.meta.php" \
    -x "_ide_helper.php" \
    -x "_ide_helper_models.php" \
    -x "backup_before_fresh.sql" \
    -x "package.json" \
    -x "package-lock.json" \
    -x "vite.config.js" \
    -x "resources/css/*" \
    -x "resources/js/*" \
    -x "resources/js/*" \
    -x "deploy/*" \
    > /dev/null 2>&1

# Step 5: Add deployment files to the ZIP
echo "[5/7] Adding deployment files..."
cd "$PROJECT_DIR"

# Add the root .htaccess (routes to public/)
cp deploy/.htaccess "${OUTPUT_DIR}/temp_root_htaccess"
cd "$OUTPUT_DIR"
zip -u "$ZIP_NAME" "temp_root_htaccess" > /dev/null 2>&1

# Add the artisan runner
cd "$PROJECT_DIR"
zip -u "$ZIP_PATH" "deploy/artisan-runner.php" > /dev/null 2>&1

# Add the production .env template  
zip -u "$ZIP_PATH" "deploy/.env.production" > /dev/null 2>&1

# Clean up temp
rm -f "${OUTPUT_DIR}/temp_root_htaccess"

# Step 6: Calculate size
echo "[6/7] Calculating package size..."
SIZE=$(du -h "$ZIP_PATH" | cut -f1)

# Step 7: Done
echo "[7/7] Done!"
echo ""
echo "========================================="
echo "  Deployment Package Created!"
echo "========================================="
echo ""
echo "  File: $ZIP_PATH"
echo "  Size: $SIZE"
echo ""
echo "NEXT STEPS:"
echo "  1. Download the ZIP file"
echo "  2. Sign up at https://byet.host/free-hosting"
echo "  3. Upload ZIP via File Manager or FTP"
echo "  4. Follow the deployment guide"
echo ""
