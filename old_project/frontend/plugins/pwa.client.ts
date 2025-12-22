export default defineNuxtPlugin(() => {
    if ('serviceWorker' in navigator) {
        navigator.serviceWorker.ready.then((reg) => {
            console.log('✅ PWA Service Worker ready:', reg)
        })
    }
})
