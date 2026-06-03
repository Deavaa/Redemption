#!/bin/bash
# Setup script to deploy the School of Redemption mobile app APK to the live server
# Run this on the LIVE SERVER after pulling the latest code

echo "=== School of Redemption - APK Deployment Setup ==="
echo ""

# Step 1: Pull latest code
echo "Step 1: Pulling latest code from git..."
cd "$(dirname "$0")"
git pull origin main

# Step 2: Create the downloads directory in storage
echo ""
echo "Step 2: Creating storage directory..."
mkdir -p storage/app/public/downloads

# Step 3: Check if APK already exists
if [ -f "storage/app/public/downloads/SchoolOfRedemption.apk" ]; then
    echo "APK already exists in storage. Current size:"
    ls -lh storage/app/public/downloads/SchoolOfRedemption.apk
    echo ""
    read -p "Do you want to replace it? (y/n): " replace
    if [ "$replace" != "y" ]; then
        echo "Keeping existing APK."
    fi
fi

# Step 4: Check for APK in public/downloads (old location)
if [ -f "public/downloads/SchoolOfRedemption.apk" ]; then
    echo "Found APK in public/downloads/ - moving to storage..."
    mv public/downloads/SchoolOfRedemption.apk storage/app/public/downloads/
    echo "APK moved successfully."
else
    echo ""
    echo "=== MANUAL APK UPLOAD REQUIRED ==="
    echo "The APK file needs to be uploaded manually."
    echo ""
    echo "Option A: Copy from the build machine"
    echo "  scp SchoolOfRedemption.apk user@yourserver:/path/to/Redemption/storage/app/public/downloads/"
    echo ""
    echo "Option B: Build on the server (requires Android SDK)"
    echo "  See /home/z/my-project/RedemptionMobile/ for the Capacitor project"
    echo ""
    echo "Option C: Download from the build machine's download folder"
    echo "  The APK is available at: /home/z/my-project/download/SchoolOfRedemption.apk"
    echo ""
    echo "Target location: $(pwd)/storage/app/public/downloads/SchoolOfRedemption.apk"
fi

# Step 5: Ensure storage link exists
echo ""
echo "Step 3: Ensuring storage link..."
php artisan storage:link 2>/dev/null || echo "Storage link may already exist."

# Step 6: Clear cache
echo ""
echo "Step 4: Clearing Laravel cache..."
php artisan route:clear
php artisan config:clear
php artisan cache:clear

echo ""
echo "=== Setup Complete ==="
echo ""
echo "The APK download is now available at:"
echo "  https://schoolofredemption.net/app/download/apk"
echo ""
echo "The download page is at:"
echo "  https://schoolofredemption.net/app"
