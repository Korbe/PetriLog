<template>
  <section class="relative">
    <div class="absolute top-0 left-1/2 -translate-x-1/2 -translate-y-1/2 -z-10" aria-hidden="true">
      <div class="w-80 h-80 rounded-full bg-linear-to-tr from-blue-500 to-gray-900 opacity-40 blur-[160px] will-change-[filter]"></div>
    </div>
    <div class="max-w-6xl mx-auto px-4 sm:px-6">
      <div class="py-12 md:py-28">

        <!-- Section header -->
        <div class="max-w-3xl mx-auto text-center pb-24 md:pb-14">
          <h2 class="text-3xl md:text-4xl font-bold mb-4">Dein Fischertagebuch – jederzeit griffbereit</h2>
          <p class="text-lg text-gray-700">Erfolge mit Freunden teilen – alles an einem Ort.</p>
        </div>

        <div class="max-w-3xl mx-auto" data-aos="zoom-y-out" data-aos-delay="600">
          <img src="/images/public/petrilog_catch.png" class="border-1 border-gray-300 rounded-md">
        </div>

        <!-- Grid -->
        <div
          class="grid lg:grid-cols-3 overflow-hidden border-y [border-image:linear-gradient(to_right,transparent,var(--color-slate-200),transparent)1] *:p-6 md:*:px-10 md:*:py-12 *:relative *:before:absolute *:before:bg-linear-to-b *:before:from-transparent *:before:via-gray-200 *:before:[block-size:100%] *:before:[inline-size:1px] *:before:[inset-inline-start:-1px] *:before:[inset-block-start:0]">
          <article>
            <h3 class="font-medium flex items-center space-x-2 mb-2">
              <svg class="fill-blue-500" xmlns="http://www.w3.org/2000/svg" width="16" height="16">
                <path
                  d="m15.447 6.605-.673-.336a6.973 6.973 0 0 0-.761-1.834l.238-.715a.999.999 0 0 0-.242-1.023l-.707-.707a.995.995 0 0 0-1.023-.242l-.715.238a6.96 6.96 0 0 0-1.834-.761L9.394.552A1 1 0 0 0 8.5-.001h-1c-.379 0-.725.214-.895.553l-.336.673a6.973 6.973 0 0 0-1.834.761l-.715-.238a.997.997 0 0 0-1.023.242l-.707.707a1.001 1.001 0 0 0-.242 1.023l.238.715a6.959 6.959 0 0 0-.761 1.834l-.673.336a1 1 0 0 0-.553.895v1c0 .379.214.725.553.895l.673.336c.167.653.425 1.268.761 1.834l-.238.715a.999.999 0 0 0 .242 1.023l.707.707a.997.997 0 0 0 1.023.242l.715-.238a6.959 6.959 0 0 0 1.834.761l.336.673a1 1 0 0 0 .895.553h1c.379 0 .725-.214.895-.553l.336-.673a6.973 6.973 0 0 0 1.834-.761l.715.238a1.001 1.001 0 0 0 1.023-.242l.707-.707c.268-.268.361-.664.242-1.023l-.238-.715a6.959 6.959 0 0 0 .761-1.834l.673-.336A1 1 0 0 0 16 8.5v-1c0-.379-.214-.725-.553-.895ZM8 13a5 5 0 1 1 .001-10.001 5 5 0 0 1 0 10.001Z" />
              </svg>
              <span>Erinnerungen bewahren</span>
            </h3>
            <p class="text-[15px] text-gray-700">Halte jeden Fang fest – so bleibt kein besonderer Moment verloren.</p>
          </article>
          <article>
            <h3 class="font-medium flex items-center space-x-2 mb-2">
              <svg class="fill-blue-500" xmlns="http://www.w3.org/2000/svg" width="16" height="12">
                <path
                  d="M2 0a1 1 0 0 0 0 2h6a1 1 0 0 0 0-2H2Zm0 7a1 1 0 0 1 1-1h10a1 1 0 0 1 1 1v2a1 1 0 0 1-1 1H3a1 1 0 0 1-1-1V7Zm1-3a3 3 0 0 0-3 3v2a3 3 0 0 0 3 3h10a3 3 0 0 0 3-3V7a3 3 0 0 0-3-3H3Z" />
              </svg>
              <span>Den besten Spot wiederfinden</span>
            </h3>
            <p class="text-[15px] text-gray-700">Speichere deine Lieblingsgewässer und finde sie immer wieder.</p>
          </article>
          <article>
            <h3 class="font-medium flex items-center space-x-2 mb-2">
              <svg class="fill-blue-500" xmlns="http://www.w3.org/2000/svg" width="16" height="16">
                <path
                  d="M14.75 2.5a1.25 1.25 0 1 0 0-2.5 1.25 1.25 0 0 0 0 2.5Zm0 13.5a1.25 1.25 0 1 0 0-2.5 1.25 1.25 0 0 0 0 2.5ZM2.5 14.75a1.25 1.25 0 1 1-2.5 0 1.25 1.25 0 0 1 2.5 0ZM1.25 2.5a1.25 1.25 0 1 0 0-2.5 1.25 1.25 0 0 0 0 2.5ZM4 8a4 4 0 1 1 8 0 4 4 0 0 1-8 0Zm4-6a6 6 0 1 0 0 12A6 6 0 0 0 8 2Z" />
              </svg>
              <span>Erfolge teilen</span>
            </h3>
            <p class="text-[15px] text-gray-700">Teile deine Fänge mit Freunden oder der Community – zeig, was du erreicht hast.</p>
          </article>
        </div>

      </div>
    </div>
  </section>
</template>