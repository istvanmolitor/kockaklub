<footer class="relative overflow-hidden border-t-2 border-gray-100 bg-gradient-to-br from-accent-50 via-white to-accent3-50">
    <div class="mx-auto max-w-7xl px-4 py-10 sm:py-12">
        <div class="grid gap-8 sm:grid-cols-2">
            <div>
                <p class="text-lg font-black text-gray-900">{{ setting('company_name', 'Kockaklub') }}</p>
                <div class="mt-3 space-y-1.5 text-sm font-medium text-gray-500">
                    @if (setting('contact_address'))
                        <p>{{ setting('contact_address') }}</p>
                    @endif
                    @if (setting('contact_phone'))
                        <p><a href="tel:{{ setting('contact_phone') }}" class="transition hover:text-accent-600">{{ setting('contact_phone') }}</a></p>
                    @endif
                    @if (setting('contact_email'))
                        <p><a href="mailto:{{ setting('contact_email') }}" class="transition hover:text-accent-600">{{ setting('contact_email') }}</a></p>
                    @endif
                </div>
            </div>

            @if (setting('facebook_url') || setting('instagram_url') || setting('youtube_url'))
                <div class="sm:text-right">
                    <p class="text-lg font-black text-gray-900">Kövess minket</p>
                    <div class="mt-3 flex gap-3 sm:justify-end">
                        @if (setting('facebook_url'))
                            <a href="{{ setting('facebook_url') }}" target="_blank" rel="noopener" aria-label="Facebook"
                               class="flex h-11 w-11 items-center justify-center rounded-full bg-white text-gray-500 shadow-sm transition duration-200 hover:-translate-y-1 hover:text-accent-600 hover:shadow-md">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="currentColor" viewBox="0 0 24 24">
                                    <path d="M22 12.06C22 6.505 17.523 2 12 2S2 6.505 2 12.06c0 5.02 3.657 9.184 8.438 9.94v-7.03H7.898v-2.91h2.54V9.845c0-2.507 1.492-3.89 3.777-3.89 1.094 0 2.238.195 2.238.195v2.46h-1.26c-1.243 0-1.63.775-1.63 1.57v1.88h2.773l-.443 2.91h-2.33V22c4.78-.756 8.437-4.92 8.437-9.94z"/>
                                </svg>
                            </a>
                        @endif
                        @if (setting('instagram_url'))
                            <a href="{{ setting('instagram_url') }}" target="_blank" rel="noopener" aria-label="Instagram"
                               class="flex h-11 w-11 items-center justify-center rounded-full bg-white text-gray-500 shadow-sm transition duration-200 hover:-translate-y-1 hover:text-accent-600 hover:shadow-md">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75">
                                    <rect x="3" y="3" width="18" height="18" rx="5" />
                                    <circle cx="12" cy="12" r="4" />
                                    <circle cx="17.25" cy="6.75" r="0.75" fill="currentColor" stroke="none" />
                                </svg>
                            </a>
                        @endif
                        @if (setting('youtube_url'))
                            <a href="{{ setting('youtube_url') }}" target="_blank" rel="noopener" aria-label="Youtube"
                               class="flex h-11 w-11 items-center justify-center rounded-full bg-white text-gray-500 shadow-sm transition duration-200 hover:-translate-y-1 hover:text-accent-600 hover:shadow-md">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="currentColor" viewBox="0 0 24 24">
                                    <path d="M23.498 6.186a2.994 2.994 0 00-2.108-2.12C19.505 3.5 12 3.5 12 3.5s-7.505 0-9.39.566a2.994 2.994 0 00-2.108 2.12A31.33 31.33 0 000 12a31.33 31.33 0 00.502 5.814 2.994 2.994 0 002.108 2.12C4.495 20.5 12 20.5 12 20.5s7.505 0 9.39-.566a2.994 2.994 0 002.108-2.12A31.33 31.33 0 0024 12a31.33 31.33 0 00-.502-5.814zM9.75 15.5v-7l6.25 3.5-6.25 3.5z"/>
                                </svg>
                            </a>
                        @endif
                    </div>
                </div>
            @endif
        </div>

        <div class="mt-8 border-t border-accent-100 pt-6 text-sm font-medium text-gray-500">
            &copy; {{ now()->year }} {{ setting('company_name', 'Kockaklub') }}
        </div>
    </div>
</footer>
