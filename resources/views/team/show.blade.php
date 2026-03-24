@extends('layouts.app')

@section('content')
    <div class="project-page-container">
        
        <!-- Navigation Header -->
        <div class="page-header flex justify-between items-center p-2 mb-4">
            <div>
                <a href="{{ route('team.index') }}" class="button secondary hollow mb-0">
                    &larr; Back to Roster
                </a>
            </div>
            <div style="color: #a0a0a0; font-weight: bold; text-transform: uppercase;">
                Agent Profile // {{ $teamMember->status }}
            </div>
        </div>

        <div class="grid-container full">
            <div class="grid-x grid-margin-x justify-center">
                <div class="cell large-10">
                    
                    <!-- Profile Header Card -->
                    <div class="callout mb-4 flex align-center" style="background: rgba(43,44,46, 0.8); border: 1px solid #4a5568; border-left: 6px solid #4299e1;">
                        <div class="pr-4 mr-4" style="border-right: 1px solid #4a5568; text-align: center;">
                            @if($teamMember->profile_image_path)
                                <img src="{{ asset($teamMember->profile_image_path) }}" alt="Avatar" style="width: 120px; height: 120px; border-radius: 8px; border: 2px solid #a0a0a0; object-fit: cover; margin-bottom: 1rem;">
                            @else
                                <div style="width: 120px; height: 120px; border-radius: 8px; border: 2px dashed #4a5568; margin: 0 auto 1rem auto; display: flex; align-items: center; justify-content: center; background: #2d3748;">
                                    <span style="font-size: 3rem; color: #a0a0a0;">🤖</span>
                                </div>
                            @endif

                            <!-- Image Upload Form -->
                            <form action="{{ route('team.upload', $teamMember) }}" method="POST" enctype="multipart/form-data" class="mt-2 text-center">
                                @csrf
                                <label for="profile_image_{{ $teamMember->id }}" class="button hollow tiny secondary mb-0" style="font-size: 0.7rem; padding: 0.25rem 0.5rem; cursor: pointer;">
                                    Upload Photo
                                </label>
                                <input type="file" id="profile_image_{{ $teamMember->id }}" name="profile_image" class="show-for-sr" onchange="this.form.submit()">
                            </form>
                            @error('profile_image')
                                <div style="color: #fc8181; font-size: 0.75rem; margin-top: 0.5rem;">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <div style="flex-grow: 1;">
                            <h1 class="mb-0" style="color: #fff; font-size: 2.5rem;">{{ $teamMember->name }}</h1>
                            <h4 style="color: #4299e1; text-transform: uppercase; font-size: 1rem; letter-spacing: 2px; margin-bottom: 1rem;">
                                {{ $teamMember->role_title }}
                            </h4>
                            <p class="lead mb-0" style="color: #ccd6f6; font-style: italic;">
                                "{{ $teamMember->one_liner }}"
                            </p>
                        </div>
                    </div>
                    
                    <!-- Agent Biography -->
                    @if($teamMember->bio)
                        <div class="callout secondary mb-4" style="background: rgba(30, 30, 30, 0.6); border: 1px solid #4a5568;">
                            <h4 style="color: #fff; border-bottom: 1px solid #4a5568; padding-bottom: 0.5rem; margin-bottom: 1rem;">Biography</h4>
                            <div style="color: #a0a0a0; line-height: 1.6;">
                                {!! nl2br(e($teamMember->bio)) !!}
                            </div>
                        </div>
                    @endif

                    <div class="grid-x grid-margin-x">
                        
                        <!-- Strengths & Responsibilities -->
                        <div class="cell medium-6 flex-col">
                            
                            <div class="callout mb-4 flex-grow-1" style="background: rgba(43,44,46, 0.8); border-top: 3px solid #48bb78;">
                                <h4 style="color: #48bb78; margin-bottom: 1rem; display: flex; align-items: center;">
                                    <span style="margin-right: 0.5rem;">✅</span> Core Responsibilities
                                </h4>
                                <div style="color: #cbd5e0; line-height: 1.6; white-space: pre-wrap;">{{ $teamMember->responsibilities ?? 'None explicitly defined.' }}</div>
                            </div>
                            
                            <div class="callout mb-4 flex-grow-1" style="background: rgba(43,44,46, 0.8); border-top: 3px solid #ed8936;">
                                <h4 style="color: #ed8936; margin-bottom: 1rem; display: flex; align-items: center;">
                                    <span style="margin-right: 0.5rem;">⚡</span> Strengths
                                </h4>
                                <div style="color: #cbd5e0; line-height: 1.6; white-space: pre-wrap;">{{ $teamMember->strengths ?? 'Unknown.' }}</div>
                            </div>
                            
                        </div>

                        <!-- Crucial Bound Limitations -->
                        <div class="cell medium-6">
                            
                            <div class="callout mb-4 h-100" style="background: rgba(60, 20, 20, 0.6); border: 1px solid #e53e3e; box-shadow: 0 0 15px rgba(229, 62, 62, 0.1);">
                                <h4 style="color: #fc8181; margin-bottom: 1rem; display: flex; align-items: center; border-bottom: 1px solid #e53e3e; padding-bottom: 0.5rem;">
                                    <span style="margin-right: 0.5rem;">🛑</span> Hard Limitations
                                </h4>
                                <p style="color: #fc8181; font-size: 0.85rem; font-style: italic; margin-bottom: 1rem;">
                                    To prevent idea drift, this agent is strictly forbidden from violating the following boundaries:
                                </p>
                                <div style="color: #fff; line-height: 1.6; font-weight: 500; font-family: monospace; font-size: 1rem; white-space: pre-wrap;">{{ $teamMember->limitations ?? 'WARNING: No limitations defined. High risk of idea drift.' }}</div>
                            </div>
                            
                        </div>
                    </div>

                    <!-- Tools Used -->
                    @if($teamMember->tools_used)
                        <div class="callout mb-4" style="background: rgba(43,44,46, 0.8); border: 1px solid #4a5568;">
                            <h4 style="color: #a0aec0; margin-bottom: 1rem;">⚙️ Tooling & Integrations</h4>
                            <div style="color: #cbd5e0; line-height: 1.6; white-space: pre-wrap;">{{ $teamMember->tools_used }}</div>
                        </div>
                    @endif

                </div>
            </div>
        </div>
    </div>
@endsection
