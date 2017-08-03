<h2>Password Reset</h2>

<p>A request to reset your password was recently received. If this request was not made by you, please alert a senior member of your division leadership.</p>

<p>Click here to reset your password: {{ url(config('app.url').route('password.reset', $token, false)) }}</p>
