@extends('layouts.app')

@section('content')
    <div class="project-page-container">
        <!-- Header -->
        <div class="page-header flex justify-between items-center p-2 mb-4">
            <h1 class="h2 text-glow mb-0">The Team Roster</h1>
            <a href="{{ route('projects.index') }}" class="button primary hollow mb-0">
                &larr; Back to Projects
            </a>
        </div>

        <div class="grid-container full">
            <!-- Intro Text -->
            <div class="grid-x grid-margin-x mb-4">
                <div class="cell large-12">
                    <div class="callout secondary text-center" style="background: rgba(30,30,30, 0.6); border: 1px solid #4a5568;">
                        <p class="lead mb-0" style="color: #a0a0a0;">
                            Meet the digital team powering the Factory Workbench Auto Pipeline. 
                            Each agent operates within explicit bounds to prevent idea drift and enforce a rigid Test-Yes-Ship loop.
                        </p>
                    </div>
                </div>
            </div>

            <!-- Agent Grid -->
            <div class="grid-x grid-margin-x">
                @forelse ($teamMembers as $member)
                    <div class="cell medium-6 large-4 mb-4">
                        <div class="callout h-100 flex flex-col justify-between" style="background: rgba(43,44,46, 0.8); border-top: 4px solid #4299e1; border-radius: 4px;">
                            
                            <div class="mb-4 text-center">
                                @if($member->profile_image_path)
                                    <img src="{{ asset($member->profile_image_path) }}" alt="{{ $member->name }} Portrait" style="width: 100px; height: 100px; border-radius: 50%; border: 2px solid #a0a0a0; object-fit: cover; margin-bottom: 1rem;">
                                @else
                                    <div style="width: 100px; height: 100px; border-radius: 50%; border: 2px dashed #4a5568; margin: 0 auto 1rem auto; display: flex; align-items: center; justify-content: center; background: #2d3748;">
                                        <span style="font-size: 2rem; color: #a0a0a0;">🤖</span>
                                    </div>
                                @endif
                                
                                <h3 style="color: #fff; margin-bottom: 0.25rem;">{{ $member->name }}</h3>
                                <div style="color: #4299e1; font-weight: bold; text-transform: uppercase; font-size: 0.85rem; letter-spacing: 1px;">
                                    {{ $member->role_title }}
                                </div>
                            </div>
                            
                            <div class="mb-4">
                                <p style="color: #ccd6f6; font-style: italic; font-size: 0.95rem; text-align: center;">
                                    "{{ $member->one_liner }}"
                                </p>
                            </div>
                            
                            <div class="text-center mt-auto">
                                <a href="{{ route('team.show', $member) }}" class="button primary fw-bold" style="width: 100%; box-shadow: 0 4px 15px rgba(66, 153, 225, 0.3);">
                                    View Agent Profile
                                </a>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="cell large-12 text-center py-5">
                        <h4 style="color: #fc8181;">The roster is completely empty.</h4>
                        <p style="color: #a0a0a0;">Run the database seeder to initialize the O.G. 5 agents.</p>
                    </div>
                @endforelse
            </div>
        </div>
    </div>
@endsection
