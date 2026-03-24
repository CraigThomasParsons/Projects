<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\TeamMember;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Manages the display of the Factory Workbench Auto Pipeline "Team Page".
 * 
 * This controller handles rendering the agent roster and their individually
 * bounded capability profiles.
 */
class TeamMemberController extends Controller
{
    /**
     * Display a listing of the active digital team members.
     *
     * @return View
     */
    public function index(): View
    {
        $activeTeamMembers = TeamMember::where('status', 'Active')
            ->orderBy('id')
            ->get();
            
        return view('team.index', [
            'teamMembers' => $activeTeamMembers
        ]);
    }
    
    /**
     * Display the specified digital team member's specific bounded persona.
     *
     * @param TeamMember $teamMember
     * @return View
     */
    public function show(TeamMember $teamMember): View
    {
        return view('team.show', [
            'teamMember' => $teamMember
        ]);
    }

    /**
     * Handle the uploading of a new profile image for the agent.
     *
     * @param Request $request
     * @param TeamMember $teamMember
     * @return \Illuminate\Http\RedirectResponse
     */
    public function uploadImage(Request $request, TeamMember $teamMember)
    {
        $validatedData = $request->validate([
            'profile_image' => 'required|image|mimes:jpeg,png,jpg,gif|max:10240', // Increased to 10MB
        ]);

        if ($request->hasFile('profile_image')) {
            try {
                $imageFile = $request->file('profile_image');
                
                // Generate a safe unique filename
                $fileName = $teamMember->id . '_' . time() . '.' . $imageFile->getClientOriginalExtension();
                
                // Store the file in the public disk
                $filePath = $imageFile->storeAs('team_profiles', $fileName, 'public');
                
                // Update the model with the new relative storage path
                $teamMember->profile_image_path = 'storage/' . $filePath;
                $teamMember->save();
            } catch (\Exception $e) {
                // If storing the file triggers a 500 error, we catch it and display it explicitly
                return redirect()->route('team.show', $teamMember)->withErrors(['profile_image' => 'Storage Error: ' . $e->getMessage()]);
            }
        }

        return redirect()->route('team.show', $teamMember)->with('success', 'Profile image updated successfully.');
    }
}
