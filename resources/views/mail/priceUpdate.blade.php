<html>
<div>
    <p>Hello,</p>
    @if (count(Session::get($key2)) > 0)
    <p>
        <p>We completed the updated for the Subscription Contracts'price list you provided 🎉</p>
    </p>
    <ol>
        @foreach (Session::get($key2) as $rk => $rv)
            <li>{{ $rv }}</li>
        @endforeach
    </ol>
    @endif
    @if (count(Session::get($key)) > 0)
        <p>
        <h3>Attention here the following Subscription Contracts'price were not updated:</h3>
        </p>
        <ol>
            @foreach (Session::get($key) as $rk => $rv)
                <li>{{ $rv }}</li>
            @endforeach
        </ol>
    @endif

    <p>Thanks,<br>
    Simplee Memberships</p>
</div>

</html>
