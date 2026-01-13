発生日時：{{ $notify_date }}　<br>
<br>
エラーが発生しています。<br>
以下をご確認ください。<br>
<br>
<hr>
[Message] {!! $error_info['message'] !!}<br><br>
[Status] {!! $error_info['status'] !!}<br>
[File] {!! $error_info['file'] !!}<br>
[Line] {!! $error_info['line'] !!}<br>
[URL] {!! $error_info['url'] !!}<br>

