# School of Redemption — Native App (Android APK + iOS)

## What This Is
A Capacitor wrapper that turns your Laravel website into native Android (APK) and iOS apps.

## How to Build the APK (on your local machine)

### Step 1: Install Node.js
Download from https://nodejs.org (version 18+)

### Step 2: Install Android Studio
Download from https://developer.android.com/studio
- During setup, make sure to install Android SDK

### Step 3: Build the APK
```bash
cd native-app
npm install
npx cap add android
npx cap sync android
cd android
./gradlew assembleDebug
```

The APK will be at:
```
android/app/build/outputs/apk/debug/app-debug.apk
```

### Step 4: Copy APK to your Laravel project
```bash
cp android/app/build/outputs/apk/debug/app-debug.apk ../public/downloads/SchoolOfRedemption.apk
```

### Step 5: Upload to cPanel
Upload `public/downloads/SchoolOfRedemption.apk` to cPanel.
Users download it from: https://schoolofredemption.net/mobile-app

---

## How to Build iOS App (Mac only)

### Step 1: Install Xcode
Download from Mac App Store

### Step 2: Build
```bash
cd native-app
npm install
npx cap add ios
npx cap sync ios
cd ios
pod install
npx cap open ios
```

### Step 3: Archive in Xcode
- Xcode → Product → Archive
- Export IPA or upload to App Store

---

## Configuration
The app URL is set in `capacitor.config.ts`:
```typescript
server: {
  url: 'https://schoolofredemption.net',
  cleartext: true
}
```

Change this URL if your domain changes.

## Features
- ✅ Full-screen webview (no browser chrome)
- ✅ Native print support (Ctrl+P / menu print works)
- ✅ Native share/export
- ✅ Splash screen with school colors
- ✅ Status bar themed emerald green
- ✅ Offline loading screen
- ✅ Push notification ready (just add Firebase)
- ✅ Works with all existing Laravel features
