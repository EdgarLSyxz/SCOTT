<a href="{{ route('login') }}" class="flex items-center mb-3 text-2xl font-semibold text-gray-900 dark:text-white">
    <div class="relative w-12 h-12 mr-2">
        <img id="logo1"
            class="absolute inset-0 w-12 h-12 rounded-md object-center object-contain transition-opacity duration-700 opacity-100"
            src="https://play-lh.googleusercontent.com/f6dYwg3khIfRRVnsnpOLkfPvuINkGaSvDdxJhEtcRc5TQv8rrKLrkPipAnaVLxEM1Cc=w240-h480-rw"
            alt="Logotipo 1">
        <img id="logo2"
            class="absolute inset-0 w-12 h-12 rounded-md object-center object-contain opacity-0 duration-700"
            src="https://play-lh.googleusercontent.com/jOOiW93I9MPZMhqz4V-5PAYxs-nfs9BsrKwaINg-NW6PB8_NzsN6I4GI52A8IXQWgg=w240-h480-rw"
            alt="Logotipo 2">
    </div>
    <div class="flex flex-col text-left ms-2">
        {{ config('app.name', 'Laravel') }}
        <span class="text-sm font-normal text-gray-600 dark:text-gray-400">
            {{ __('OTT •  DTH Communications System') }}
        </span>
    </div>
</a>
<script>
    let showingLogo1 = true;
    setInterval(() => {
        const logo1 = document.getElementById('logo1');
        const logo2 = document.getElementById('logo2');
        if (showingLogo1) {
            logo1.style.opacity = 0;
            logo2.style.opacity = 1;
            logo2.style.objectPosition = 'center 30%';
        } else {
            logo1.style.opacity = 1;
            logo2.style.opacity = 0;
        }
        showingLogo1 = !showingLogo1;
    }, 6000);
</script>
