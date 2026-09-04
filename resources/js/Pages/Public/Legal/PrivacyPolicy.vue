<template>
    <Layout>

        <Head title="Datenschutzerklärung" />

        <section class="relative">

            <PageIllustration />

            <div class="max-w-6xl mx-auto px-4 sm:px-6">

                <!-- Hero content -->
                <div class="pt-32 pb-12 md:pt-10 md:pb-20">

                    <!-- Section header -->
                    <div class="pb-12 md:pb-16">
                        <h1 class="text-center text-3xl xs:text-4xl sm:text-5xl md:text-6xl font-bold mb-4 break-words">
                            Datenschutzerklärung</h1>
                        <p class="text-center text-lg text-gray-700 max-w-2xl mx-auto">
                            Der Schutz deiner persönlichen Daten ist uns wichtig. Diese Erklärung informiert dich über
                            Art, Umfang und Zweck der Verarbeitung deiner Daten in <strong>PetriLog</strong>.
                        </p>
                    </div>

                    <div class="max-w-4xl mx-auto">

                        <!-- Verantwortlicher -->
                        <div class="rounded-xl bg-white border border-gray-200 shadow-sm p-6 md:p-8 mb-12 md:mb-16">
                            <div class="inline-flex items-center justify-center w-10 h-10 rounded-lg bg-primary-50 mb-4">
                                <UserCircleIcon class="w-5 h-5 text-primary-500" />
                            </div>
                            <h2 class="font-medium text-lg mb-2">Verantwortlicher</h2>
                            <p class="text-[15px] text-gray-700 leading-relaxed">
                                Ing. Lukas Korbitsch<br>
                                Kiesweg 19, Villach, Kärnten, Österreich<br>
                                <a href="mailto:info@korbitsch.at"
                                    class="text-primary-500 hover:underline">info@korbitsch.at</a>
                            </p>
                        </div>

                        <!-- Sections -->
                        <div>
                            <h2 class="text-2xl font-semibold mt-8 mb-4 flex items-center gap-2">
                                <IdentificationIcon class="w-5 h-5 text-primary-500" />
                                Erhebung und Verarbeitung personenbezogener Daten
                            </h2>
                            <p class="mb-4 text-gray-700">
                                Wir erheben personenbezogene Daten nur, wenn du diese aktiv angibst, z. B. bei der
                                Registrierung oder beim Eintragen deiner Fänge. Dazu gehören:
                            </p>
                            <ul class="list-disc ml-6 mb-4 text-gray-700 space-y-1">
                                <li>Name und E-Mail-Adresse</li>
                                <li>Fangdaten: Fischart, Gewicht, Länge, Fangdatum, Standort, Fotos und weitere Details
                                </li>
                                <li>Passwort (verschlüsselt gespeichert)</li>
                            </ul>

                            <h2 class="text-2xl font-semibold mt-8 mb-4 flex items-center gap-2">
                                <ClipboardDocumentListIcon class="w-5 h-5 text-primary-500" />
                                Zweck der Datenverarbeitung
                            </h2>
                            <ul class="list-disc ml-6 mb-4 text-gray-700 space-y-1">
                                <li>Bereitstellung der Funktionen von PetriLog (Fangbuch, Medien-Upload,
                                    Kartenintegration)</li>
                                <li>Verbesserung der Benutzererfahrung und Funktionalität der App</li>
                                <li>Kommunikation im Rahmen der Nutzung (z. B. Passwort-Reset, E-Mail-Benachrichtigungen)
                                </li>
                            </ul>

                            <h2 class="text-2xl font-semibold mt-8 mb-4 flex items-center gap-2">
                                <GlobeAltIcon class="w-5 h-5 text-primary-500" />
                                Cookies, lokaler Speicher &amp; externe Dienste
                            </h2>
                            <p class="mb-4 text-gray-700">
                                PetriLog verwendet technisch notwendige Cookies bzw. lokalen Speicher (Local Storage),
                                um grundlegende Funktionen wie die Anmeldung und deine Cookie-Einwilligung zu
                                ermöglichen. Erst nachdem du dem im Cookie-Banner ausdrücklich zugestimmt hast, werden
                                zusätzlich folgende externe Dienste geladen:
                            </p>
                            <ul class="list-disc ml-6 mb-4 text-gray-700 space-y-1">
                                <li><strong>Google Analytics</strong> – zur anonymisierten Analyse der Nutzung (IP-
                                    Anonymisierung aktiviert), Anbieter: Google Ireland Limited</li>
                                <li><strong>Google Maps</strong> – zur Darstellung von Gewässern und Fangorten,
                                    Anbieter: Google Ireland Limited</li>
                            </ul>
                            <p class="mb-4 text-gray-700">
                                Lehnst du im Cookie-Banner ab, werden diese Dienste nicht geladen – einzelne Funktionen
                                wie die Kartenansicht stehen dann nicht zur Verfügung. Deine Wahl kannst du jederzeit
                                über die Löschung der Browserdaten zurücksetzen. Bei Nutzung dieser Dienste können Daten
                                an Google in die USA übermittelt werden.
                            </p>

                            <h2 class="text-2xl font-semibold mt-8 mb-4 flex items-center gap-2">
                                <ArrowsRightLeftIcon class="w-5 h-5 text-primary-500" />
                                Weitergabe von Daten
                            </h2>
                            <p class="mb-4 text-gray-700">
                                Eine Weitergabe deiner Daten an Dritte erfolgt nur, wenn dies gesetzlich erlaubt ist
                                oder du zuvor ausdrücklich eingewilligt hast. Daten können z. B. an Hosting-Anbieter
                                oder Cloud-Dienste für die Speicherung von Fotos übermittelt werden.
                            </p>

                            <h2 class="text-2xl font-semibold mt-8 mb-4 flex items-center gap-2">
                                <ShieldCheckIcon class="w-5 h-5 text-primary-500" />
                                Deine Rechte
                            </h2>
                            <div class="grid sm:grid-cols-2 gap-4 mb-4">
                                <div v-for="right in rights" :key="right.title"
                                    class="flex items-start gap-3 rounded-xl bg-white border border-gray-200 shadow-sm p-4">
                                    <component :is="right.icon" class="w-5 h-5 text-primary-500 shrink-0 mt-0.5" />
                                    <div>
                                        <p class="font-medium text-[15px]">{{ right.title }}</p>
                                        <p class="text-[14px] text-gray-600">{{ right.text }}</p>
                                    </div>
                                </div>
                            </div>

                            <h2 class="text-2xl font-semibold mt-8 mb-4 flex items-center gap-2">
                                <LockClosedIcon class="w-5 h-5 text-primary-500" />
                                Datensicherheit
                            </h2>
                            <p class="mb-4 text-gray-700">
                                Wir setzen technische und organisatorische Maßnahmen ein, um deine Daten vor Verlust,
                                Missbrauch oder unberechtigtem Zugriff zu schützen. Dazu gehören z. B. verschlüsselte
                                Speicherung von Passwörtern und SSL-Verschlüsselung bei der Datenübertragung.
                            </p>

                            <h2 class="text-2xl font-semibold mt-8 mb-4 flex items-center gap-2">
                                <ArrowPathIcon class="w-5 h-5 text-primary-500" />
                                Änderungen der Datenschutzerklärung
                            </h2>
                            <p class="mb-4 text-gray-700">
                                Wir behalten uns vor, diese Datenschutzerklärung anzupassen, damit sie stets den
                                aktuellen rechtlichen Anforderungen entspricht oder um Änderungen unserer Dienste
                                umzusetzen. Bitte besuche diese Seite regelmäßig.
                            </p>

                            <p class="mt-8 text-gray-500 text-sm">
                                Stand: September 2026
                            </p>
                        </div>

                    </div>
                </div>
            </div>
        </section>
    </Layout>
</template>
<script setup>
import Layout from '@/Layouts/Public/Layout.vue';
import { Head } from '@inertiajs/vue3';
import PageIllustration from '../PageIllustration.vue';
import {
    UserCircleIcon,
    IdentificationIcon,
    ClipboardDocumentListIcon,
    GlobeAltIcon,
    ArrowsRightLeftIcon,
    ShieldCheckIcon,
    LockClosedIcon,
    ArrowPathIcon,
    EyeSlashIcon,
    PencilSquareIcon,
    TrashIcon,
    ArrowDownTrayIcon,
} from '@heroicons/vue/24/solid';

const rights = [
    { icon: EyeSlashIcon, title: 'Auskunft', text: 'Über die zu deiner Person gespeicherten Daten.' },
    { icon: PencilSquareIcon, title: 'Berichtigung', text: 'Unrichtige Daten korrigieren lassen.' },
    { icon: TrashIcon, title: 'Löschung', text: 'Deine personenbezogenen Daten löschen lassen.' },
    { icon: ArrowDownTrayIcon, title: 'Datenübertragbarkeit', text: 'Export deiner Daten in einem gängigen Format.' },
]
</script>
