@extends('layouts.app')

@section('title', 'Verify Your Mobile Number')

@section('content')
<div class="max-w-md mx-auto px-6 py-20">

    <div class="text-center mb-10">
        <h1 class="text-2xl font-semibold text-[#f5f0e8] tracking-wide">
            Verify Your Mobile Number
        </h1>
        <p class="text-[#7a6a55] text-sm mt-3 leading-relaxed">
            IconsHerald requires a verified Indian mobile number before you can
            submit an application. This is a one-time step.
        </p>
        <p class="text-[#4a4a4a] text-xs mt-2">
            Your number is never displayed on your profile.
        </p>
    </div>

    <div class="bg-[#111111] border border-[#2a2a2a] rounded-lg p-8">

        @if (session('otp_sent'))
            {{-- ── Step 2: Enter the OTP ── --}}
            <p class="text-[#8bc48b] text-sm mb-6 text-center">
                A 6-digit code was sent to <strong>{{ session('otp_mobile') }}</strong>.
                It expires in 10 minutes.
            </p>

            <form method="POST" action="{{ route('auth.otp.verify') }}">
                @csrf
                <div class="mb-6">
                    <label for="code" class="block text-[#a09070] text-sm mb-2">
                        Enter the 6-digit code
                    </label>
                    <input
                        type="text"
                        id="code"
                        name="code"
                        inputmode="numeric"
                        pattern="\d{6}"
                        maxlength="6"
                        autocomplete="one-time-code"
                        autofocus
                        class="w-full bg-[#1a1a1a] border border-[#333] text-[#f5f0e8] rounded px-4 py-3
                               text-center text-2xl tracking-widest focus:outline-none focus:border-[#c9a84c]
                               @error('code') border-red-700 @enderror"
                        placeholder="000000"
                        value="{{ old('code') }}"
                    >
                    @error('code')
                        <p class="text-red-400 text-xs mt-2">{{ $message }}</p>
                    @enderror
                </div>

                <button type="submit"
                        class="w-full bg-[#c9a84c] hover:bg-[#b8973b] text-black font-medium py-3 rounded transition-colors">
                    Verify Mobile Number
                </button>
            </form>

            <form method="POST" action="{{ route('auth.otp.resend') }}" class="mt-4 text-center">
                @csrf
                <button type="submit" class="text-[#7a6a55] hover:text-[#a09070] text-sm transition-colors underline">
                    Resend code
                </button>
            </form>

        @else
            {{-- ── Step 1: Enter mobile number ── --}}
            <form method="POST" action="{{ route('auth.otp.send') }}">
                @csrf
                <div class="mb-6">
                    <label for="mobile" class="block text-[#a09070] text-sm mb-2">
                        Mobile number
                    </label>
                    <div class="flex items-center gap-2">
                        <span class="bg-[#1a1a1a] border border-[#333] text-[#7a6a55] rounded px-3 py-3 text-sm whitespace-nowrap">
                            +91
                        </span>
                        <input
                            type="tel"
                            id="mobile"
                            name="mobile"
                            inputmode="numeric"
                            maxlength="10"
                            autofocus
                            class="flex-1 bg-[#1a1a1a] border border-[#333] text-[#f5f0e8] rounded px-4 py-3
                                   focus:outline-none focus:border-[#c9a84c]
                                   @error('mobile') border-red-700 @enderror"
                            placeholder="98765 43210"
                            value="{{ old('mobile') }}"
                        >
                    </div>
                    @error('mobile')
                        <p class="text-red-400 text-xs mt-2">{{ $message }}</p>
                    @enderror
                </div>

                <button type="submit"
                        class="w-full bg-[#c9a84c] hover:bg-[#b8973b] text-black font-medium py-3 rounded transition-colors">
                    Send Verification Code
                </button>
            </form>
        @endif

    </div>

    <p class="text-center text-[#4a4a4a] text-xs mt-6">
        Signed in as <span class="text-[#7a6a55]">{{ auth()->user()->email }}</span>.
        <form method="POST" action="{{ route('logout') }}" class="inline">
            @csrf
            <button type="submit" class="underline hover:text-[#7a6a55]">Sign out</button>
        </form>
    </p>

</div>
@endsection
