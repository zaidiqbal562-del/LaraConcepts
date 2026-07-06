<!doctype html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Register</title>
</head>
<body>
    <h1>Register</h1>

    @if($errors->any())
        <div style="color:red;">
            <ul>
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="/register">
        @csrf
        <div>
            <h4>Name</h4>
            <input type="text" name="name" value="{{ old('name') }}" required>
        </div>
        <div>
            <h4>Email</h4>
            <input type="email" name="email" value="{{ old('email') }}" required>
        </div>
        <div>
            <h4>Password</h4>
            <input type="password" name="password" required>
        </div>
        <div>
            <h4>Confirm Password</h4>
            <input type="password" name="password_confirmation" required>
        </div>
        <button type="submit" class="mt-6">Register</button>
    </form>

    <p>Already have an account? <a href="/login">Login</a></p>
</body>
</html>
