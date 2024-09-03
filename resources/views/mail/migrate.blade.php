<html>
<div>
    <p>Hello,</p>
    <p>We completed the migration for the member list you provided 🎉</p>
    @if(count(Session::get($key)) > 0)
    	<p><h3>The following members were not migrated:</h3></p>
    	<ol>
	    @foreach(Session::get($key) as $rk=>$rv)
	        <li>{{$rv}}</li>
	    @endforeach
		</ol>
    @endif

    <p>Thanks,<br>
        Simplee Memberships</p>
</div>
</html>