<h1>Hi {{ $user->name }}</h1>
<p>Please use the following code to reset your password:</p>
<br/>
<p style="font-weight: bold">{{ $user->forgot_password_code }}</p>
