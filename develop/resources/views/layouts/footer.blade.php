{{-- フッター用Blade --}}
{{-- @copyright © 2025 CodeSpaces --}}
@section('footer')
    <footer>
        {{-- copyright --}}
        <div class="copyright_text">© {{\Carbon\Carbon::now()->year}} CodeSpaces</div>
    </footer>

    <script src="{{ mix('js/app.js') }}"></script>
    <script src="{{ mix('js/sidebar.js') }}"></script>
    <script src="{{ mix('js/app_etc.js') }}"></script>
    <script src="{{ mix('js/common_index.js') }}"></script>
    <script src="{{ mix('js/common_create_edit.js') }}"></script>
    <script src="{{ mix('js/util.js') }}"></script>
@endsection
