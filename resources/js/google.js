const GA_ID = 'G-54ZZQRJ2RT'

export function initAnalytics() {
    if (window.gtag)
        return;
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
