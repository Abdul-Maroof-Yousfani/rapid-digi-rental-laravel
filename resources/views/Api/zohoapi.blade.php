<form action="{{ url('refresh-access-token') }}" method="POST">
    @csrf
    <input type="text" name="code" value="{{ $code }}" id="">
    <input type="submit" value="button">
</form>

<p>{{$code}}</p>
