<footer class="bg-body-tertiary text-center text-lg-start">
    <div class="text-center p-3" style="background-color: rgba(0, 0, 0, 0.05);">
        © {{ now()->year }} Copyright:
        <a class="text-body" href="https://bernd-armaturenbau.de">{{ isset($title) ? $title : config('app.name') }}</a>
    </div>
</footer>
