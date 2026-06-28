@extends('layouts.app')

@section('title', 'My Profile — IconsHerald')

@section('content')
<div class="max-w-4xl mx-auto px-6 py-16">

    <div class="mb-10">
        <h1 class="text-2xl font-semibold text-[#f5f0e8]">Welcome, {{ auth()->user()->name }}</h1>
        <p class="text-[#7a6a55] text-sm mt-2">
            This is your IconsHerald member area.
        </p>
    </div>

    {{-- Application status --}}
    @php $application = auth()->user()->applications()->latest()->first(); @endphp

    @if ($application)
        <div class="bg-[#111111] border border-[#2a2a2a] rounded-lg p-6 mb-6">
            <h2 class="text-[#c9a84c] text-sm font-medium uppercase tracking-widest mb-4">
                Your Application
            </h2>
            <div class="grid grid-cols-2 gap-4 text-sm">
                <div>
                    <span class="text-[#7a6a55]">Package</span>
                    <p class="text-[#f5f0e8] capitalize mt-1">{{ $application->package }}</p>
                </div>
                <div>
                    <span class="text-[#7a6a55]">Status</span>
                    <p class="text-[#f5f0e8] capitalize mt-1">{{ str_replace('_', ' ', $application->status) }}</p>
                </div>
                <div>
                    <span class="text-[#7a6a55]">Name on Application</span>
                    <p class="text-[#f5f0e8] mt-1">{{ $application->full_name }}</p>
                </div>
                <div>
                    <span class="text-[#7a6a55]">Submitted</span>
                    <p class="text-[#f5f0e8] mt-1">{{ $application->created_at->format('d M Y') }}</p>
                </div>
            </div>
        </div>
    @else
        <div class="bg-[#111111] border border-[#2a2a2a] rounded-lg p-6 mb-6 text-center">
            <p class="text-[#7a6a55] text-sm mb-4">
                You have not submitted an application yet.
            </p>
            <a href="{{ route('apply') }}"
               class="inline-block bg-[#c9a84c] hover:bg-[#b8973b] text-black text-sm font-medium px-6 py-2 rounded transition-colors">
                Apply for a Profile
            </a>
        </div>
    @endif

    {{-- Account details --}}
    <div class="bg-[#111111] border border-[#2a2a2a] rounded-lg p-6">
        <h2 class="text-[#c9a84c] text-sm font-medium uppercase tracking-widest mb-4">
            Account
        </h2>
        <div class="grid grid-cols-2 gap-4 text-sm">
            <div>
                <span class="text-[#7a6a55]">Email</span>
                <p class="text-[#f5f0e8] mt-1">{{ auth()->user()->email }}</p>
            </div>
            <div>
                <span class="text-[#7a6a55]">Mobile</span>
                <p class="text-[#f5f0e8] mt-1">
                    @if (auth()->user()->mobile)
                        +91 {{ auth()->user()->mobile }}
                        <span class="text-[#5a8a5a] text-xs ml-1">Verified</span>
                    @else
                        <a href="{{ route('auth.otp.index') }}" class="text-[#c9a84c] hover:underline">Verify now</a>
                    @endif
                </p>
            </div>
        </div>
    </div>

</div>
@endsection
