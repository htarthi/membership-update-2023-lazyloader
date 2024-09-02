<html>
    <body>
        <p>Hello, chris</p>
        <p><b>{{ $topic }}</b> webhook was submitted for {{ $shop }}.</p>

        @if($topic == 'customers/redact' || $topic == 'customers/data_request')
        	<p><b>Body :</b> {{ $body }}</p>
        @endif
    </body>
</html>
