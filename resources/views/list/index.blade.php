<form action="/check-age" method="POST">
    {{-- instead of the above we can also use the route name like below
    <form action="{{ route('check.age') }}" method="POST"> --}}
    @csrf

    <input type="number" name="age" placeholder="Enter Age">
    <button type="submit">Check</button>
</form>

@if(isset($message)){
    <h1>{{$message}}</h1>
}
@endif