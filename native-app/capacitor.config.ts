import type { CapacitorConfig } from '@capacitor/cli';

const config: CapacitorConfig = {
  appId: 'net.schoolofredemption.app',
  appName: 'School of Redemption',
  webDir: 'www',
  server: {
    // Point to your live Laravel site — the app loads this URL
    url: 'https://schoolofredemption.net',
    cleartext: true
  },
  android: {
    allowMixedContent: true,
    backgroundColor: '#047857'
  },
  ios: {
    backgroundColor: '#047857'
  },
  plugins: {
    SplashScreen: {
      launchShowDuration: 2000,
      backgroundColor: '#047857',
      androidSplashResourceName: 'splash',
      iosSpinnerStyle: 'small',
      showSpinner: true,
      androidScaleType: 'CENTER_CROP'
    },
    StatusBar: {
      style: 'LIGHT',
      backgroundColor: '#047857'
    }
  }
};

export default config;
