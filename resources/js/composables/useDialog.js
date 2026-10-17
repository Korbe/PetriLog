import { reactive } from 'vue'

/**
 * App-weiter Ersatz für window.confirm() und window.alert().
 * Die Darstellung übernimmt <AppDialog />, das einmal im Layout eingebunden ist.
 *
 *   if (await confirmDialog({ title: 'Spot löschen?', message: '…', confirmText: 'Löschen', tone: 'danger' })) { … }
 *   await alertDialog({ title: 'Standort nicht verfügbar', message: '…', tone: 'warning' })
 */
export const dialogState = reactive({
    open: false,
    type: 'confirm', // confirm | alert
    title: '',
    message: '',
    confirmText: '',
    cancelText: '',
    tone: 'primary', // primary | danger | warning | success
    resolve: null,
})

function openDialog(type, options) {
    // Ein noch offener Dialog gilt als abgebrochen
    dialogState.resolve?.(false)

    return new Promise((resolve) => {
        Object.assign(dialogState, {
            type,
            title: options.title ?? '',
            message: options.message ?? '',
            confirmText: options.confirmText ?? (type === 'alert' ? 'Verstanden' : 'Bestätigen'),
            cancelText: options.cancelText ?? 'Abbrechen',
            tone: options.tone ?? (type === 'alert' ? 'warning' : 'primary'),
            resolve,
            open: true,
        })
    })
}

export function confirmDialog(options = {}) {
    return openDialog('confirm', options)
}

export function alertDialog(options = {}) {
    return openDialog('alert', options).then(() => undefined)
}

export function settleDialog(result) {
    const resolve = dialogState.resolve
    dialogState.open = false
    dialogState.resolve = null
    resolve?.(result)
}
