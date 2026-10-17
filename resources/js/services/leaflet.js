// Leaflet greift beim Import auf window zu – deshalb nur im Browser und erst bei Bedarf laden (SSR-sicher).
let leafletPromise = null

export function loadLeaflet() {
    if (!leafletPromise) {
        leafletPromise = (async () => {
            const { default: L } = await import('leaflet')
            await import('leaflet/dist/leaflet.css')
            // Die Plugins erweitern das globale L
            window.L = L
            await Promise.all([
                import('leaflet.heat'),
                import('leaflet.markercluster'),
                import('leaflet.markercluster/dist/MarkerCluster.css'),
            ])
            return L
        })()
    }
    return leafletPromise
}

// basemap.at: offizielle Verwaltungsgrundkarte Österreichs (Open Data, CC BY 4.0, kein API-Key)
const BASEMAP_ATTRIBUTION = 'Grundkarte: <a href="https://basemap.at" target="_blank" rel="noopener">basemap.at</a>'

export function baseLayers(L) {
    return {
        Karte: L.tileLayer('https://mapsneu.wien.gv.at/basemap/geolandbasemap/normal/google3857/{z}/{y}/{x}.png', {
            maxZoom: 19,
            attribution: BASEMAP_ATTRIBUTION,
        }),
        Luftbild: L.tileLayer('https://mapsneu.wien.gv.at/basemap/bmaporthofoto30cm/normal/google3857/{z}/{y}/{x}.jpeg', {
            maxZoom: 19,
            attribution: BASEMAP_ATTRIBUTION,
        }),
        // Außerhalb Österreichs liefert basemap.at keine Kacheln
        OpenStreetMap: L.tileLayer('https://tile.openstreetmap.org/{z}/{x}/{y}.png', {
            maxZoom: 19,
            attribution: '&copy; <a href="https://www.openstreetmap.org/copyright" target="_blank" rel="noopener">OpenStreetMap</a>',
        }),
    }
}

// Mitte Österreichs als Startansicht ohne Daten
export const AUSTRIA_CENTER = [47.6, 13.8]
