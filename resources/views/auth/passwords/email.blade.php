@php
    $style = 'style=background-image:url('.asset('assets/img/progress-hd.png').')';
    $hospitalSettingValue = getSuperAdminSettingValue();
@endphp
@extends('layouts.auth_app')
@section('title')
    {{  __('auth.reset_password.password_reset') }}
@endsection
@section('content')
    @include('flash::message')
    @php
        $style = 'style=background-image:url('.asset('assets/img/progress-hd.png').')';
        $hospitalSettingValue = getSuperAdminSettingValue();
        App::setLocale(checkLanguageSession());
    @endphp
    <!--begin::Authentication - Password reset -->
    <div class="auth-shell">
        <a href="{{ route('landing-home') }}" class="image auth-logo">
            <img alt="Self Care" src="{{ asset($hospitalSettingValue['app_logo']['value']) }}" class="img-fluid logo-fix-size">
        </a>
        <div class="auth-card bg-theme-white rounded-15 shadow-md width-540 px-5 px-sm-7 py-10">
        <div class="auth-card-toolbar">
            <ul class="nav nav-pills language-option">
                <li class="nav-item dropdown">
                    <a class="btn btn-primary indicator dropdown-toggle" data-bs-toggle="dropdown"
                        href="javascript:void(0)" role="button"
                        aria-expanded="false">{{ getCurrentLanguageName() }}</a>
                    <ul class="dropdown-menu">
                        @foreach (getLanguages() as $key => $value)
                            <li class="{{ checkLanguageSession() == $key ? 'active' : '' }}">
                                <a class="dropdown-item px-5 language-select {{ checkLanguageSession() == $key ? 'bg-primary text-white' : 'text-dark' }}"
                                    data-id="{{ $key }}">{{ $value }}</a>
                            </li>
                        @endforeach
                    </ul>
                </li>
            </ul>
        </div>

            @if ($errors->any())
                <div class="alert alert-danger">
                    <ul class="mb-0">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif
            @if (session('status'))
                <div class="alert alert-success">
                    {{ session('status') }}
                </div>
            @endif
            <h1 class="text-center mb-2">{{__('auth.login.forgot_password')}} ?</h1>
            <div class="text-center text-gray-400 fw-bold fs-5 mb-5">{{__('auth.forgot_password.title')}}</div>
            <form method="post" action="{{ url('/password/email') }}">
                @csrf
                <div class="mb-sm-7 mb-4">
                    <label for="formEmailInput" class="form-label">
                        {{__('auth.email')}}
                        <span class="required"></span>
                    </label>
                    <input type="email" class="form-control" name="email" id="formEmailInput"
                           value="{{ old('email') }}"
                           placeholder="{{__('auth.forgot_password.enter_email')}}" required>
                </div>
                <div class="d-grid">
                    <button type="submit" class="btn btn-primary">{{__('auth.forgot_password.send_pwd_reset')}}</button>
                </div>
            </form>
        </div>
    </div>
    <!--end::Authentication - Password reset-->
@endsection
