// firebase-messaging-sw.js
importScripts('https://www.gstatic.com/firebasejs/8.10.1/firebase-app.js');
importScripts('https://www.gstatic.com/firebasejs/8.10.1/firebase-messaging.js');

// Firebase configuration
const firebaseConfig = {
    apiKey: "AIzaSyAuFHcuEyq070sM7Pgt4JyriybPnNEq6M4",
    authDomain: "einfo-e95ba.firebaseapp.com",
    projectId: "einfo-e95ba",
    storageBucket: "einfo-e95ba.firebasestorage.app",
    messagingSenderId: "438009665395",
    appId: "1:438009665395:web:d74475efa497609b58d706",
    measurementId: "G-DT4NHYZG47"
};

// Initialize Firebase
firebase.initializeApp(firebaseConfig);
const messaging = firebase.messaging();

// Handle background messages
messaging.onBackgroundMessage(function(payload) {
    console.log('[firebase-messaging-sw.js] Received background message ', payload);
   
    const notificationTitle = payload.notification.title || 'New Notification';
    const notificationOptions = {
        body: payload.notification.body || 'You have a new message',
        icon: payload.notification.icon || 'https://einfo.site/logo.png',
        badge: 'https://einfo.site/logo.png',
        tag: 'firebase-notification',
        requireInteraction: true,
        actions: [
            {
                action: 'view',
                title: 'View Order'
            },
            {
                action: 'close',
                title: 'Close'
            }
        ],
        data: {
            click_action: payload.notification.click_action || payload.data?.click_action || '/',
            order_id: payload.data?.order_id || null
        }
    };
    
    self.registration.showNotification(notificationTitle, notificationOptions);
});

// Handle notification clicks
self.addEventListener('notificationclick', function(event) {
    console.log('[Service Worker] Notification click received.');
   
    event.notification.close();
   
    if (event.action === 'close') {
        return;
    }
   
    // Get the URL to open from notification data
    const urlToOpen = event.notification.data?.click_action || '/';
   
    // Open the specific page when notification is clicked
    event.waitUntil(
        clients.matchAll({type: 'window'}).then(function(clientList) {
            // Check if there's already a window/tab open
            for (let i = 0; i < clientList.length; i++) {
                const client = clientList[i];
                if (client.url === urlToOpen && 'focus' in client) {
                    return client.focus();
                }
            }
            // If no window/tab is open, open new one
            if (clients.openWindow) {
                return clients.openWindow(urlToOpen);
            }
        })
    );
});