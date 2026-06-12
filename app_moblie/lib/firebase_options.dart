
import 'package:firebase_core/firebase_core.dart' show FirebaseOptions;
import 'package:flutter/foundation.dart'
    show defaultTargetPlatform, kIsWeb, TargetPlatform;

/// Default [FirebaseOptions] for use with your Firebase apps.
///
/// Example:
/// ```dart
/// import 'firebase_options.dart';
/// // ...
/// await Firebase.initializeApp(
///   options: DefaultFirebaseOptions.currentPlatform,
/// );
/// ```
class DefaultFirebaseOptions {
  static FirebaseOptions get currentPlatform {
    if (kIsWeb) {
      return web;
    }
    switch (defaultTargetPlatform) {
      case TargetPlatform.android:
        return android;
      case TargetPlatform.iOS:
        return ios;
      case TargetPlatform.macOS:
        return macos;
      case TargetPlatform.windows:
        return windows;
      case TargetPlatform.linux:
        throw UnsupportedError(
          'DefaultFirebaseOptions have not been configured for linux - '
          'you can reconfigure this by running the FlutterFire CLI again.',
        );
      default:
        throw UnsupportedError(
          'DefaultFirebaseOptions are not supported for this platform.',
        );
    }
  }

  static const FirebaseOptions web = FirebaseOptions(
    apiKey: 'AIzaSyDYc6fdfHVIioQ62l86qYxuYNcsR6cMTmo',
    appId: '1:281744185927:web:a65b54bc90715e025f6f62',
    messagingSenderId: '281744185927',
    projectId: 'wms-app-b95e5',
    authDomain: 'wms-app-b95e5.firebaseapp.com',
    storageBucket: 'wms-app-b95e5.firebasestorage.app',
    measurementId: 'G-4ZNM0BKS7L',
  );

  static const FirebaseOptions android = FirebaseOptions(
    apiKey: 'AIzaSyB3jirOJjOBmgPb9prw9PnYmH6YN6ZCeV4',
    appId: '1:281744185927:android:546e3c53c792e4c15f6f62',
    messagingSenderId: '281744185927',
    projectId: 'wms-app-b95e5',
    storageBucket: 'wms-app-b95e5.firebasestorage.app',
  );

  static const FirebaseOptions ios = FirebaseOptions(
    apiKey: 'AIzaSyCrRuiaw2BC9drCfCN7aYmTWn50IPM51Ds',
    appId: '1:281744185927:ios:d95af94515f77d645f6f62',
    messagingSenderId: '281744185927',
    projectId: 'wms-app-b95e5',
    storageBucket: 'wms-app-b95e5.firebasestorage.app',
    iosBundleId: 'com.example.doAn1',
  );

  static const FirebaseOptions macos = FirebaseOptions(
    apiKey: 'AIzaSyCrRuiaw2BC9drCfCN7aYmTWn50IPM51Ds',
    appId: '1:281744185927:ios:d95af94515f77d645f6f62',
    messagingSenderId: '281744185927',
    projectId: 'wms-app-b95e5',
    storageBucket: 'wms-app-b95e5.firebasestorage.app',
    iosBundleId: 'com.example.doAn1',
  );

  static const FirebaseOptions windows = FirebaseOptions(
    apiKey: 'AIzaSyDYc6fdfHVIioQ62l86qYxuYNcsR6cMTmo',
    appId: '1:281744185927:web:063c178907712d3c5f6f62',
    messagingSenderId: '281744185927',
    projectId: 'wms-app-b95e5',
    authDomain: 'wms-app-b95e5.firebaseapp.com',
    storageBucket: 'wms-app-b95e5.firebasestorage.app',
    measurementId: 'G-L6R83D8F4G',
  );
}
