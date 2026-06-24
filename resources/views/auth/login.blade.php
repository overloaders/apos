@extends('layouts.auth')
@section('title', 'Login')

@section('content')
<form method="POST" action="{{ route('login') }}">
    @csrf
    <div class="form-group">
        <label><i class="fas fa-user mr-1"></i> Username atau Email</label>
        <input type="text" class="form-control @error('username') is-invalid @enderror" name="username" placeholder="Masukkan username atau email" value="{{ old('username') }}" required autofocus>
        @error('username')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>
    <div class="form-group">
        <label><i class="fas fa-lock mr-1"></i> Password</label>
        <input type="password" class="form-control @error('password') is-invalid @enderror" name="password" placeholder="Masukkan password" required>
        @error('password')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>
    <div class="custom-control custom-checkbox mb-3">
        <input class="custom-control-input" type="checkbox" name="remember" id="remember">
        <label class="custom-control-label" for="remember">Ingat saya</label>
    </div>
    <button type="submit" class="btn btn-primary btn-block btn-login">
        <i class="fas fa-sign-in-alt mr-1"></i> Masuk
    </button>
</form>
@endsection