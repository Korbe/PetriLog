// Gemeinsame Formatierungen für Listen und Tabellen (österreichisches Format)

export const formatNumber = (value) => Number(value ?? 0).toLocaleString('de-AT')

export const formatDate = (date) => date
    ? new Date(date).toLocaleDateString('de-AT', { day: '2-digit', month: '2-digit', year: 'numeric' })
    : '–'

/**
 * „vor 3 Std.“, „gestern“, „vor 5 Tagen“ – ab einem Monat das Datum.
 */
export function relativeTime(date) {
    if (!date) return '–'
    const minutes = Math.round((Date.now() - new Date(date).getTime()) / 60000)
    if (minutes < 1) return 'gerade eben'
    if (minutes < 60) return `vor ${minutes} Min.`
    const hours = Math.round(minutes / 60)
    if (hours < 24) return `vor ${hours} Std.`
    const days = Math.round(hours / 24)
    if (days === 1) return 'gestern'
    if (days < 30) return `vor ${days} Tagen`
    return formatDate(date)
}

export const initials = (name) => (name ?? '')
    .split(/\s+/)
    .filter(Boolean)
    .slice(0, 2)
    .map(part => part[0].toUpperCase())
    .join('') || '?'

// Groß-/Kleinschreibung und Umlaute ignorieren („Wörthersee“ findet „worther“)
export const normalize = (text) => (text ?? '').toString().toLocaleLowerCase('de').normalize('NFD').replace(/[̀-ͯ]/g, '')
