@extends('layouts.app')

@section('title', 'IconsHerald — Herald Yourself.')
@section('meta_description', 'IconsHerald writes and publishes permanent professional profiles for doctors, lawyers, scientists, architects, and others whose careers deserve a proper record.')

@section('content')
<div class="max-w-7xl mx-auto px-6 py-24 text-center">

    {{-- Hero --}}
    <h1 class="text-4xl md:text-5xl font-semibold text-[#f5f0e8] leading-tight mb-6 max-w-3xl mx-auto">
        A career that matters deserves a record that lasts.
    </h1>

    <p class="text-[#a09070] text-lg leading-relaxed max-w-2xl mx-auto mb-10">
        IconsHerald writes a short, true account of your career — your achievements,
        your contributions, the things a résumé can't show. We compose it, you approve it,
        and it stays online for as long as you want it to.
    </p>

    <p class="text-[#7a6a55] text-sm mb-3">Not a directory. Not a social network.</p>

    <div class="flex flex-col sm:flex-row gap-4 justify-center mt-6">
        <a href="{{ route('apply') }}"
           class="bg-[#c9a84c] hover:bg-[#b8973b] text-black font-medium px-8 py-3 rounded transition-colors">
            Apply for a Profile
        </a>
        <a href="{{ route('register.index') }}"
           class="text-[#c9a84c] hover:text-[#b8973b] font-medium px-8 py-3 border border-[#2a2a2a] rounded transition-colors flex items-center gap-2">
            Explore the Icons →
        </a>
    </div>

    {{-- Phase 2 will add specimen profile cards, pricing preview, and In Memoriam section here --}}

</div>
@endsection
