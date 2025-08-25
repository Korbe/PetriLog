<template>
    <div class="min-h-screen flex flex-col sm:justify-center items-center pt-24 sm:pt-0 bg-gray-100">
        <div class="w-[90%] sm:max-w-md mt-6 px-6 py-4 md:bg-white md:shadow-md overflow-hidden rounded-lg">
            <div class="w-full items-center flex flex-col">
                <slot name="logo" />
            </div>

            <slot />
        </div>
    </div>
</template>
