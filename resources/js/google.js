const GA_ID = 'G-54ZZQRJ2RT'
const MAPS_KEY = 'AIzaSyDz9ywPxkkW1oOy70Rab2oqnhF02DLe5MA'

export function initAnalytics() {
    console.log('initAnalytics 1');
    if (window.gtag)
        return;
    console.log('initAnalytics 2');
    // Script laden
    const script = document.createElement('script')
    script.async = true
    script.src = `https://www.googletagmanager.com/gtag/js?id=${GA_ID}`

    document.head.appendChild(script)

    // Google Analytics Setup
    window.dataLayer = window.dataLayer || []
    function gtag() {
        window.dataLayer.push(arguments)
    }
    window.gtag = gtag

    gtag('js', new Date())

    gtag('config', GA_ID, {
        anonymize_ip: true
    })
}

export function initGoogleMaps() {
    console.log('initGoogleMaps 1');
    if (window.google?.maps)
        return;
    console.log('initGoogleMaps 2');
    const script = document.createElement('script')

    script.src =
        `https://maps.googleapis.com/maps/api/js?key=${MAPS_KEY}&libraries=marker&loading=async`

    script.async = true
    script.defer = true

    document.head.appendChild(script)
}