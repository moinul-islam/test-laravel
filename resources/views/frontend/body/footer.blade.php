<footer class="py-4 mt-5 border-top">
    <div class="container text-center">
        
        <ul class="list-inline mb-2">
            <li class="list-inline-item">
                <a href="{{ url('/about-us') }}" class="text-muted text-decoration-none">
                    About
                </a>
            </li>
            <li class="list-inline-item">.</li>
            <li class="list-inline-item">
                <a href="{{ url('/privacy-policy') }}" class="text-muted text-decoration-none">
                    Privacy
                </a>
            </li>
            <li class="list-inline-item">.</li>
            <li class="list-inline-item">
                <a href="{{ url('/terms-and-condition') }}" class="text-muted text-decoration-none">
                    Terms
                </a>
            </li>
        </ul>

        <!-- Language Switcher -->
        <form method="POST" action="{{ route('set-locale') }}" class="d-inline">
            @csrf
            <select name="locale" onchange="this.form.submit()">
                <option value="en" {{ Session::get('locale', 'en') == 'en' ? 'selected' : '' }}>English</option>
                <option value="bn" {{ Session::get('locale', 'en') == 'bn' ? 'selected' : '' }}>বাংলা</option>
            </select>
        </form>

        <p class="mb-0 text-muted mt-2">eINFO &copy; {{ date('Y') }}</p>
    </div>
</footer>